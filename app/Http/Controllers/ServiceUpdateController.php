<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Models\ServiceBooking;
use App\Models\ServiceUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin only (enforced in routes/web.php).
 * Rules: add updates freely until the service is Completed/Cancelled;
 * only the LAST update can be edited (note only) or deleted.
 */
class ServiceUpdateController extends Controller
{
    public function store(Request $request, ServiceBooking $booking): RedirectResponse
    {
        if ($booking->currentStatus()->isTerminal()) {
            return $this->back($booking)->with('error',
                'This service is already '.strtolower($booking->currentStatus()->label()).'. Delete the last update first if it was a mistake.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(array_map(fn ($s) => $s->value, ServiceStatus::selectable()))],
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $booking->updates()->create($data + ['user_id' => $request->user()->id]);

        return $this->back($booking)->with('success', 'Progress update added.');
    }

    public function update(Request $request, ServiceBooking $booking, ServiceUpdate $update): RedirectResponse
    {
        if (! $this->isLast($booking, $update)) {
            return $this->back($booking)->with('error', 'Only the latest update can be edited.');
        }

        $data = $request->validateWithBag('editNote', [
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $update->update($data);

        return $this->back($booking)->with('success', 'Note updated.');
    }

    public function destroy(ServiceBooking $booking, ServiceUpdate $update): RedirectResponse
    {
        if (! $this->isLast($booking, $update)) {
            return $this->back($booking)->with('error', 'Only the latest update can be deleted.');
        }

        $update->delete();

        return $this->back($booking)->with('success', 'Latest update deleted.');
    }

    private function isLast(ServiceBooking $booking, ServiceUpdate $update): bool
    {
        return $booking->updates()->max('id') === $update->id;
    }

    private function back(ServiceBooking $booking): RedirectResponse
    {
        $params = ['laptop' => $booking->laptop_id, 'booking' => $booking->id];
        if (request()->query('from') === 'tracking') {
            $params['from'] = 'tracking';
        }

        return redirect()->route('laptops.show', $params);
    }
}
