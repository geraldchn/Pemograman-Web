<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use Illuminate\Http\Request;

/**
 * Service Tracking: which laptops are in (or have been through) service, and where each stands.
 * Admin sees every laptop with a booking; a customer only their own.
 * Registering/editing laptops lives in the Laptops menu (LaptopController).
 */
class ServiceTrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $laptops = Laptop::query()
            ->whereHas('bookings')
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
            // most recently active service first
            ->orderByDesc(
                \App\Models\ServiceBooking::select('id')->whereColumn('laptop_id', 'laptops.id')->latest('id')->limit(1)
            )
            ->paginate(9)
            ->withQueryString();

        return view('tracking.index', compact('laptops'));
    }
}
