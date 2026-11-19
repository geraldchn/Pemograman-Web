@extends('layouts.app')

@php
    $isAdmin = auth()->user()->hasRole('admin');
@endphp

@section('title', ($isAdmin ? 'Laptops' : 'My Laptops').' - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-mean-fruit"></i></div>
                <div>
                    {{ $isAdmin ? 'Laptops' : 'My Laptops' }}
                    <div class="page-title-subheading">
                        {{ $isAdmin ? 'Register and manage the laptops of every customer.' : 'Register your laptops and keep their details up to date.' }}
                    </div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('laptops.create') }}" class="tc-btn tc-btn-accent"><i class="fa fa-plus"></i> Add laptop</a>
            </div>
        </div>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if (session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    @if ($isAdmin)
        <form method="GET" action="{{ route('laptops.index') }}" class="row g-2 mb-3">
            <div class="col-md-6 col-lg-5">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Search laptop, serial number, or owner">
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="tc-btn tc-btn-primary" type="submit">Search</button>
                @if (request()->filled('q'))
                    <a href="{{ route('laptops.index') }}" class="tc-btn tc-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    @endif

    <div class="row g-3">
        @forelse ($laptops as $laptop)
            @php
                $booking = $laptop->latestBooking;
                $status = $booking?->currentStatus();
            @endphp
            <div class="col-md-6 col-xl-4 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-0">{{ $laptop->displayName() }}</h5>
                                <div class="text-muted small">
                                    {{ $laptop->serial_number ? 'S/N '.$laptop->serial_number : 'No serial number' }}
                                    @if ($laptop->purchase_year) &middot; {{ $laptop->purchase_year }} @endif
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

                        @if ($laptop->specSummary())
                            <div class="small text-muted mb-2"><i class="fa fa-microchip me-1"></i>{{ $laptop->specSummary() }}</div>
                        @else
                            <div class="small text-muted mb-2">No specifications added yet.</div>
                        @endif

                        <div class="small mb-3">
                            @if ($booking)
                                <span class="text-muted">Service:</span>
                                <span class="tc-badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
                            @else
                                <span class="text-muted">No service yet.</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('laptops.show', $laptop) }}" class="tc-btn tc-btn-outline">Details</a>
                            <a href="{{ route('laptops.edit', $laptop) }}" class="tc-btn tc-btn-primary"><i class="fa fa-pen"></i> Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center py-5">
                    @if ($isAdmin && request()->filled('q'))
                        <span class="text-muted">No laptops found.</span>
                    @elseif ($isAdmin)
                        <p class="text-muted mb-3">No laptops have been registered yet.</p>
                        <a href="{{ route('laptops.create') }}" class="tc-btn tc-btn-accent"><i class="fa fa-plus"></i> Add a laptop</a>
                    @else
                        <p class="text-muted mb-3">You have not registered any laptop yet. Add your laptop to start booking services.</p>
                        <a href="{{ route('laptops.create') }}" class="tc-btn tc-btn-accent"><i class="fa fa-plus"></i> Add your first laptop</a>
                    @endif
                </div></div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $laptops->links() }}</div>
@endsection
