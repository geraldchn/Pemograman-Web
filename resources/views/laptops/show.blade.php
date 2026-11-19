@extends('layouts.app')

@php
    $isAdmin = auth()->user()->hasRole('admin');
    $canManage = $isAdmin || $laptop->user_id === auth()->id();
    $fromQuery = request('from') === 'tracking' ? ['from' => 'tracking'] : [];
    $backUrl = $fromQuery ? route('tracking.index') : route('laptops.index');
    $stepLabels = \App\Enums\ServiceStatus::stepLabels();
    $curStep = $current?->step();
    $cancelled = $current === \App\Enums\ServiceStatus::Cancelled;
    $finished = $current === \App\Enums\ServiceStatus::Completed;
@endphp

@section('title', $laptop->displayName().' - Service Progress')

@push('styles')
<style>
    /* ---- 3D stage card ---- */
    .tc-stage { overflow: hidden; border: 0; background: radial-gradient(120% 90% at 50% 0%, #1b2233 0%, #0a0a0a 70%); }
    .tc-stage-body { padding: 0 .5rem; }
    .tc-stage-hint { text-align: center; font-size: .72rem; color: rgba(255,255,255,.5); padding: 0 0 .75rem; }
    /* ---- Progress stepper ---- */
    .tc-stepper { display: flex; list-style: none; margin: 0; padding: 0; }
    .tc-stepper li { flex: 1; text-align: center; position: relative; font-size: .8rem; color: var(--tc-text-muted); }
    .tc-stepper li::before { content: ""; position: absolute; top: 15px; left: -50%; width: 100%; height: 3px; background: var(--tc-border); z-index: 0; }
    .tc-stepper li:first-child::before { display: none; }
    .tc-stepper .dot { width: 32px; height: 32px; border-radius: 50%; margin: 0 auto .4rem; display: flex; align-items: center;
                       justify-content: center; background: var(--tc-surface); border: 3px solid var(--tc-border); position: relative; z-index: 1; font-size: .8rem; }
    .tc-stepper li.done { color: var(--tc-text); }
    .tc-stepper li.done::before { background: var(--tc-text); }
    .tc-stepper li.done .dot { background: var(--tc-primary); border-color: var(--tc-primary); color: var(--tc-secondary); }
    .tc-stepper li.current { color: var(--tc-accent); font-weight: 700; }
    .tc-stepper li.current::before { background: var(--tc-text); }
    .tc-stepper li.current .dot { background: var(--tc-fill-accent); border-color: var(--tc-fill-accent); color: #fff;
                                  box-shadow: 0 0 0 5px rgba(var(--tc-accent-rgb), .22); }
    .tc-stepper.is-cancelled { opacity: .45; filter: grayscale(1); }

    /* ---- Timeline ---- */
    .tc-timeline { list-style: none; margin: 0; padding: 0 0 0 1.75rem; position: relative; }
    .tc-timeline::before { content: ""; position: absolute; left: 11px; top: 6px; bottom: 6px; width: 2px; background: var(--tc-border); }
    .tc-timeline > li { position: relative; padding-bottom: 1.5rem; }
    .tc-timeline > li:last-child { padding-bottom: 0; }
    .tc-timeline .t-dot { position: absolute; left: -1.75rem; top: 0; width: 24px; height: 24px; border-radius: 50%;
                          display: flex; align-items: center; justify-content: center; font-size: .65rem; color: #fff;
                          background: var(--tc-fill-accent); border: 3px solid var(--tc-surface); box-shadow: 0 0 0 1px var(--tc-border); }
    .tc-timeline .t-dot.c-warning { background: var(--tc-warning); }
    .tc-timeline .t-dot.c-success { background: var(--tc-success); }
    .tc-timeline .t-dot.c-danger { background: var(--tc-danger); }
    .tc-timeline .t-dot.c-muted { background: var(--tc-gray-500); }
    .tc-timeline .t-note { background: var(--tc-bg); border: 1px solid var(--tc-border); border-radius: var(--tc-radius-sm);
                           padding: .6rem .8rem; margin-top: .4rem; white-space: pre-line; }
    .tc-timeline .t-latest .t-note { border-color: var(--tc-accent); }
    @media (max-width: 575px) { .tc-stepper li { font-size: .68rem; } .tc-stepper .dot { width: 26px; height: 26px; } .tc-stepper li::before { top: 12px; } }
</style>
@endpush

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-mean-fruit"></i></div>
                <div>
                    {{ $laptop->displayName() }}
                    <div class="page-title-subheading">
                        {{ $laptop->serial_number ? 'S/N '.$laptop->serial_number : 'No serial number' }}
                        @if ($isAdmin) &middot; Owner: {{ $laptop->owner->name }} @endif
                    </div>
                </div>
            </div>
            <div class="page-title-actions d-flex gap-2">
                @if ($canManage)
                    <a href="{{ route('laptops.edit', $laptop) }}" class="tc-btn tc-btn-primary"><i class="fa fa-pen"></i> Edit laptop</a>
                @endif
                <a href="{{ $backUrl }}" class="tc-btn tc-btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
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

    @if (! $booking)
        <div class="row">
            <div class="col-lg-4">
@include('laptops._stage')
@php
    $specs = $laptop->specs();
@endphp
<div class="card mb-3">
    <div class="card-header">Laptop details</div>
    @if ($laptop->photoUrl())
        <img src="{{ $laptop->photoUrl() }}" alt="{{ $laptop->displayName() }}" style="width:100%;max-height:220px;object-fit:cover">
    @endif
    <div class="card-body">
        @if ($specs)
            <dl class="row mb-0 small">
                @foreach ($specs as $label => $value)
                    <dt class="col-5 text-muted fw-normal">{{ $label }}</dt>
                    <dd class="col-7 mb-2">{{ $value }}</dd>
                @endforeach
            </dl>
        @else
            <div class="text-muted small">No specifications added.</div>
        @endif
        @if ($laptop->notes)
            <hr>
            <div class="small text-muted">Notes</div>
            <div class="small" style="white-space:pre-line">{{ $laptop->notes }}</div>
        @endif
        @if ($canManage)
            <hr>
            @if ($bookings->isEmpty())
                <form method="POST" action="{{ route('laptops.destroy', $laptop) }}"
                      onsubmit="return confirm('Delete {{ e($laptop->displayName()) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Delete laptop</button>
                </form>
            @else
                <div class="small text-muted"><i class="fa fa-lock me-1"></i>This laptop has a service history, so it cannot be deleted.</div>
            @endif
        @endif
    </div>
</div>
            </div>
            <div class="col-lg-8"><div class="card mb-3"><div class="card-body text-center text-muted py-5">
                This laptop has no service booking yet.
            </div></div></div>
        </div>
    @else
    <div class="row">
        {{-- ===== Left: service history ===== --}}
        <div class="col-lg-4 mb-3">
@include('laptops._stage')
@php
    $specs = $laptop->specs();
@endphp
<div class="card mb-3">
    <div class="card-header">Laptop details</div>
    @if ($laptop->photoUrl())
        <img src="{{ $laptop->photoUrl() }}" alt="{{ $laptop->displayName() }}" style="width:100%;max-height:220px;object-fit:cover">
    @endif
    <div class="card-body">
        @if ($specs)
            <dl class="row mb-0 small">
                @foreach ($specs as $label => $value)
                    <dt class="col-5 text-muted fw-normal">{{ $label }}</dt>
                    <dd class="col-7 mb-2">{{ $value }}</dd>
                @endforeach
            </dl>
        @else
            <div class="text-muted small">No specifications added.</div>
        @endif
        @if ($laptop->notes)
            <hr>
            <div class="small text-muted">Notes</div>
            <div class="small" style="white-space:pre-line">{{ $laptop->notes }}</div>
        @endif
        @if ($canManage)
            <hr>
            @if ($bookings->isEmpty())
                <form method="POST" action="{{ route('laptops.destroy', $laptop) }}"
                      onsubmit="return confirm('Delete {{ e($laptop->displayName()) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Delete laptop</button>
                </form>
            @else
                <div class="small text-muted"><i class="fa fa-lock me-1"></i>This laptop has a service history, so it cannot be deleted.</div>
            @endif
        @endif
    </div>
</div>
            <div class="card">
                <div class="card-header">Service history</div>
                <div class="list-group list-group-flush">
                    @foreach ($bookings as $b)
                        @php $bs = $b->currentStatus(); @endphp
                        <a href="{{ route('laptops.show', ['laptop' => $laptop, 'booking' => $b->id] + $fromQuery) }}"
                           class="list-group-item list-group-item-action {{ $b->id === $booking->id ? 'active-booking' : '' }}"
                           style="{{ $b->id === $booking->id ? 'border-left:4px solid var(--tc-accent);background:var(--tc-accent-soft)' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>{{ $b->code() }}</strong>
                                <span class="tc-badge {{ $bs->badgeClass() }}">{{ $bs->label() }}</span>
                            </div>
                            <div class="small text-muted">{{ $b->created_at->format('d M Y') }}</div>
                            <div class="small">{{ \Illuminate\Support\Str::limit($b->issue, 50) }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Right: selected service ===== --}}
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $booking->code() }}</h5>
                            <div class="text-muted small">Booked {{ $booking->created_at->format('d M Y, H:i') }}
                                @if ($booking->scheduled_at) &middot; Drop-off {{ $booking->scheduled_at->format('d M Y, H:i') }} @endif
                            </div>
                        </div>
                        <span class="tc-badge {{ $current->badgeClass() }}" style="font-size:.9rem;padding:.35rem .8rem">
                            <i class="fa {{ $current->icon() }} me-1"></i>{{ $current->label() }}
                        </span>
                    </div>

                    <div class="mb-4"><span class="text-muted small d-block">Issue reported</span>{{ $booking->issue }}</div>

                    @if ($cancelled)
                        <div class="alert alert-danger py-2"><i class="fa fa-ban me-1"></i> This service was cancelled.</div>
                    @endif

                    <ol class="tc-stepper {{ $cancelled ? 'is-cancelled' : '' }}" aria-label="Repair progress">
                        @foreach ($stepLabels as $i => $label)
                            @php
                                if ($cancelled) {
                                    $state = '';
                                } elseif ($finished || ($curStep !== null && $i < $curStep)) {
                                    $state = 'done';
                                } elseif ($i === $curStep) {
                                    $state = 'current';
                                } else {
                                    $state = '';
                                }
                            @endphp
                            <li class="{{ $state }}" @if ($state === 'current') aria-current="step" @endif>
                                <div class="dot">@if ($state === 'done')<i class="fa fa-check"></i>@else {{ $i + 1 }} @endif</div>
                                {{ $label }}
                            </li>
                        @endforeach
                    </ol>
                    @if ($current === \App\Enums\ServiceStatus::WaitingParts)
                        <div class="text-center small text-muted mt-3"><i class="fa fa-hourglass-half"></i> Repair is paused while waiting for parts.</div>
                    @endif
                </div>
            </div>

            {{-- ===== Admin: add update ===== --}}
            @if ($isAdmin)
                <div class="card mb-3">
                    <div class="card-header">Add progress update</div>
                    <div class="card-body">
                        @if ($current->isTerminal())
                            <div class="text-muted">
                                This service is {{ strtolower($current->label()) }}, so no more updates can be added.
                                If the last update was a mistake, delete it below.
                            </div>
                        @else
                            <form method="POST" action="{{ route('updates.store', array_merge([$booking], $fromQuery)) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label" for="status">Status</label>
                                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            @foreach ($statusOptions as $opt)
                                                <option value="{{ $opt->value }}" @selected(old('status', $suggested->value) === $opt->value)>{{ $opt->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label" for="note">Note for the customer</label>
                                        <textarea id="note" name="note" rows="3" maxlength="1000" required
                                                  class="form-control @error('note') is-invalid @enderror"
                                                  placeholder="e.g. Fan replaced, running stress test.">{{ old('note') }}</textarea>
                                        @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <button type="submit" class="tc-btn tc-btn-accent"><i class="fa fa-plus"></i> Add update</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ===== Timeline ===== --}}
            <div class="card mb-3">
                <div class="card-header">Progress timeline</div>
                <div class="card-body">
                    <ul class="tc-timeline">
                        @foreach ($updates as $u)
                            @php $isLast = $u->id === $lastUpdateId; @endphp
                            <li class="{{ $isLast ? 't-latest' : '' }}">
                                <span class="t-dot c-{{ $u->status->color() }}"><i class="fa {{ $u->status->icon() }}"></i></span>
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <strong>{{ $u->status->label() }}</strong>
                                        @if ($isLast)<span class="tc-badge tc-badge-accent ms-1">Latest</span>@endif
                                    </div>
                                    <div class="text-muted small" title="{{ $u->created_at->format('d M Y, H:i') }}">
                                        {{ $u->created_at->format('d M Y, H:i') }} &middot; {{ $u->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="t-note">{{ $u->note }}</div>
                                <div class="small text-muted mt-1">
                                    @if ($u->author) by {{ $u->author->name }} @endif
                                    @if ($u->wasEdited()) &middot; edited {{ $u->updated_at->diffForHumans() }} @endif
                                </div>

                                @if ($isAdmin && $isLast)
                                    <div class="d-flex gap-2 mt-2">
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#edit-note" aria-expanded="{{ $errors->editNote->any() ? 'true' : 'false' }}">
                                            <i class="fa fa-pen"></i> Edit note
                                        </button>
                                        <form method="POST" action="{{ route('updates.destroy', array_merge([$booking, $u], $fromQuery)) }}"
                                              onsubmit="return confirm('Delete the latest update ({{ e($u->status->label()) }})? The status will go back to the previous update.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                    <div class="collapse mt-2 {{ $errors->editNote->any() ? 'show' : '' }}" id="edit-note">
                                        <form method="POST" action="{{ route('updates.update', array_merge([$booking, $u], $fromQuery)) }}">
                                            @csrf @method('PUT')
                                            <textarea name="note" rows="3" maxlength="1000" required
                                                      class="form-control mb-2 {{ $errors->editNote->has('note') ? 'is-invalid' : '' }}">{{ old('note', $u->note) }}</textarea>
                                            @if ($errors->editNote->has('note'))
                                                <div class="invalid-feedback d-block mb-2">{{ $errors->editNote->first('note') }}</div>
                                            @endif
                                            <button class="tc-btn tc-btn-primary" type="submit">Save note</button>
                                        </form>
                                    </div>
                                @endif
                            </li>
                        @endforeach

                        {{-- origin of every timeline --}}
                        <li>
                            <span class="t-dot c-muted"><i class="fa {{ \App\Enums\ServiceStatus::Booked->icon() }}"></i></span>
                            <strong>Booking created</strong>
                            <div class="text-muted small">{{ $booking->created_at->format('d M Y, H:i') }}</div>
                            @if ($updates->isEmpty())
                                <div class="t-note text-muted">Waiting for the laptop to be dropped off and checked in.</div>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
