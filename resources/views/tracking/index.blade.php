@extends('layouts.app')

@php
    $isAdmin = auth()->user()->hasRole('admin');
@endphp

@section('title', 'Service Tracking - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-tools icon-gradient bg-mean-fruit"></i></div>
                <div>
                    Service Tracking
                    <div class="page-title-subheading">
                        {{ $isAdmin ? 'Monitor and update the repair progress of every laptop in service.' : 'Follow the repair progress of your laptops.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <form method="GET" action="{{ route('tracking.index') }}" class="row g-2 mb-3">
            <div class="col-md-6 col-lg-5">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Search laptop, serial number, or owner">
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="tc-btn tc-btn-primary" type="submit">Search</button>
                @if (request()->filled('q'))
                    <a href="{{ route('tracking.index') }}" class="tc-btn tc-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    @endif

    <div class="row g-3">
        @forelse ($laptops as $laptop)
            @php
                $booking = $laptop->latestBooking;
                $status = $booking->currentStatus();
                $last = $booking->latestUpdate;
            @endphp
            <div class="col-md-6 col-xl-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-0">{{ $laptop->displayName() }}</h5>
                                <div class="text-muted small">
                                    {{ $laptop->serial_number ? 'S/N '.$laptop->serial_number : 'No serial number' }}
                                </div>
                            </div>
                            @if ($laptop->photoUrl())
                                <img src="{{ $laptop->photoUrl() }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:var(--tc-radius-sm)">
                            @else
                                <i class="pe-7s-note2 fs-2 text-muted"></i>
                            @endif
                        </div>

                        @if ($isAdmin)
                            <div class="small mb-2"><i class="pe-7s-user me-1"></i>{{ $laptop->owner->name }}</div>
                        @endif

                        <div class="mb-2">
                            <span class="tc-badge {{ $status->badgeClass() }}"><i class="fa {{ $status->icon() }} me-1"></i>{{ $status->label() }}</span>
                        </div>
                        <div class="small text-muted mb-1">{{ $booking->code() }} &middot; {{ \Illuminate\Support\Str::limit($booking->issue, 60) }}</div>
                        <div class="small mb-3">
                            @if ($last)
                                <span class="text-muted">Last update {{ $last->created_at->diffForHumans() }}:</span>
                                {{ \Illuminate\Support\Str::limit($last->note, 70) }}
                            @else
                                <span class="text-muted">Waiting for the laptop to be dropped off.</span>
                            @endif
                        </div>

                        <a href="{{ route('laptops.show', ['laptop' => $laptop, 'from' => 'tracking']) }}"
                           class="tc-btn tc-btn-primary mt-auto align-self-start">View progress</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5">
                    @if ($isAdmin && request()->filled('q'))
                        <span class="text-muted">No laptops found.</span>
                    @elseif ($isAdmin)
                        <span class="text-muted">No laptop has a service booking yet.</span>
                    @else
                        <p class="text-muted mb-3">Nothing is being serviced right now. Once you book a service, its progress will show up here.</p>
                        <a href="{{ route('laptops.index') }}" class="tc-btn tc-btn-outline">Go to My Laptops</a>
                    @endif
                </div></div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $laptops->links() }}</div>
@endsection
