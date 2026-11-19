<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Models\Laptop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Laptops and their service progress.
 *  - index/show: admin sees every laptop, a customer only their own.
 *  - create/store/edit/update/destroy: customers (own laptops only) and admin
 *    (any laptop, and may choose which customer owns it).
 */
class LaptopController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $laptops = Laptop::query()
            ->with(['owner', 'latestBooking.latestUpdate'])
            ->when(! $user->hasRole('admin'), fn ($q) => $q->where('user_id', $user->id))
            ->when($user->hasRole('admin') && $request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q')->trim();
                $q->where(fn ($w) => $w
                    ->where('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%")
                    ->orWhere('serial_number', 'like', "%{$term}%")
                    ->orWhereHas('owner', fn ($o) => $o->where('name', 'like', "%{$term}%")));
            })
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('laptops.index', compact('laptops'));
    }

    public function show(Request $request, Laptop $laptop)
    {
        $user = $request->user();

        // Customers must not learn that other people's laptops exist.
        abort_unless($user->hasRole('admin') || $laptop->user_id === $user->id, 404);

        $laptop->load('owner');
        $bookings = $laptop->bookings()->with('latestUpdate')->get();

        $booking = $request->filled('booking')
            ? $bookings->firstWhere('id', (int) $request->query('booking'))
            : $bookings->first();
        abort_if($request->filled('booking') && ! $booking, 404);

        $updates = $booking
            ? $booking->updates()->with('author')->get()->reverse()->values()   // newest first
            : collect();

        $current = $booking?->currentStatus();

        return view('laptops.show', [
            'laptop' => $laptop,
            'bookings' => $bookings,
            'booking' => $booking,
            'updates' => $updates,
            'current' => $current,
            'lastUpdateId' => $booking?->latestUpdate?->id,
            'statusOptions' => ServiceStatus::selectable(),
            'suggested' => $current?->suggestedNext(),
        ]);
    }

    // ---------- Customer: manage own laptops ----------

    public function create(Request $request)
    {
        return view('laptops.form', $this->formData($request, new Laptop()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules($request));
        $data['photo'] = $request->hasFile('photo') ? $request->file('photo')->store('laptops', 'public') : null;
        $data['user_id'] = $this->ownerId($request, $data);

        $laptop = Laptop::create($data);

        return redirect()->route('laptops.show', $laptop)->with('success', 'Laptop added. You can now book a service for it.');
    }

    public function edit(Request $request, Laptop $laptop)
    {
        $this->canManageOrFail($request, $laptop);

        return view('laptops.form', $this->formData($request, $laptop));
    }

    public function update(Request $request, Laptop $laptop): RedirectResponse
    {
        $this->canManageOrFail($request, $laptop);

        $data = $request->validate($this->rules($request));
        $data['user_id'] = $this->ownerId($request, $data, $laptop);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($laptop);
            $data['photo'] = $request->file('photo')->store('laptops', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($laptop);
            $data['photo'] = null;
        } else {
            unset($data['photo']);
        }

        $laptop->update($data);

        return redirect()->route('laptops.show', $laptop)->with('success', 'Laptop details updated.');
    }

    public function destroy(Request $request, Laptop $laptop): RedirectResponse
    {
        $this->canManageOrFail($request, $laptop);

        // Deleting would also erase every service booking and progress timeline.
        if ($laptop->bookings()->exists()) {
            return redirect()->route('laptops.show', $laptop)
                ->with('error', 'This laptop has a service history, so it cannot be deleted.');
        }

        $this->deletePhoto($laptop);
        $laptop->delete();

        return redirect()->route('laptops.index')->with('success', 'Laptop deleted.');
    }

    /** Admin may manage any laptop; a customer only their own (404 so others' laptops stay hidden). */
    private function canManageOrFail(Request $request, Laptop $laptop): void
    {
        $user = $request->user();

        abort_unless($user->hasRole(User::ROLE_ADMIN) || $laptop->user_id === $user->id, 404);
    }

    /** Admin may pick the owner; everyone else always owns what they create. */
    private function ownerId(Request $request, array $data, ?Laptop $existing = null): int
    {
        if ($request->user()->hasRole(User::ROLE_ADMIN) && ! empty($data['user_id'])) {
            return (int) $data['user_id'];
        }

        return $existing?->user_id ?? $request->user()->id;
    }

    private function formData(Request $request, Laptop $laptop): array
    {
        $owners = $request->user()->hasRole(User::ROLE_ADMIN)
            ? User::whereIn('role', [User::ROLE_CUSTOMER, User::ROLE_ADMIN])->orderBy('name')->get(['id', 'name', 'email'])
            : collect();

        return ['laptop' => $laptop, 'brands' => Laptop::BRANDS, 'owners' => $owners];
    }

    private function deletePhoto(Laptop $laptop): void
    {
        if ($laptop->photo) {
            Storage::disk('public')->delete($laptop->photo);
        }
    }

    private function rules(Request $request): array
    {
        // Only admin may choose an owner; for customers the field is rejected outright.
        $ownerRule = $request->user()->hasRole(User::ROLE_ADMIN)
            ? ['nullable', Rule::exists('users', 'id')->whereIn('role', [User::ROLE_CUSTOMER, User::ROLE_ADMIN])]
            : ['prohibited'];

        return [
            'user_id' => $ownerRule,
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'purchase_year' => ['nullable', 'integer', 'between:1990,'.now()->year],
            'processor' => ['nullable', 'string', 'max:100'],
            'ram_gb' => ['nullable', 'integer', 'between:1,512'],
            'storage' => ['nullable', 'string', 'max:100'],
            'gpu' => ['nullable', 'string', 'max:100'],
            'screen_size' => ['nullable', 'numeric', 'between:7,30'],
            'os' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
