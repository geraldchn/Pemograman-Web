@extends('layouts.app')

@php
    $isEdit = $laptop->exists;
@endphp

@section('title', ($isEdit ? 'Edit Laptop' : 'Add Laptop').' - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-mean-fruit"></i></div>
                <div>
                    {{ $isEdit ? 'Edit Laptop' : 'Add Laptop' }}
                    <div class="page-title-subheading">
                        {{ $isEdit ? $laptop->displayName() : 'Register your laptop so you can book a service for it.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $isEdit ? route('laptops.update', $laptop) : route('laptops.store') }}" style="max-width:860px">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- Basic info --}}
        <div class="card mb-3">
            <div class="card-header">Basic information</div>
            <div class="card-body">
                @if ($owners->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label" for="user_id">Owner</label>
                        <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            @foreach ($owners as $o)
                                <option value="{{ $o->id }}" @selected((int) old('user_id', $laptop->user_id ?? auth()->id()) === $o->id)>
                                    {{ $o->name }} ({{ $o->email }}){{ $o->id === auth()->id() ? ' - you' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Defaults to you. Choose a customer to register their laptop on their behalf.</div>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="brand">Brand <span class="text-danger">*</span></label>
                        <input id="brand" name="brand" type="text" list="brand-list" value="{{ old('brand', $laptop->brand) }}" required
                               class="form-control @error('brand') is-invalid @enderror" placeholder="e.g. ASUS" autocomplete="off">
                        <datalist id="brand-list">
                            @foreach ($brands as $b)<option value="{{ $b }}">@endforeach
                        </datalist>
                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="model">Model <span class="text-danger">*</span></label>
                        <input id="model" name="model" type="text" value="{{ old('model', $laptop->model) }}" required
                               class="form-control @error('model') is-invalid @enderror" placeholder="e.g. ROG Strix G15">
                        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="serial_number">Serial number</label>
                        <input id="serial_number" name="serial_number" type="text" value="{{ old('serial_number', $laptop->serial_number) }}"
                               class="form-control @error('serial_number') is-invalid @enderror">
                        <div class="form-text">Usually on a sticker under the laptop. Helps us identify it.</div>
                        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="purchase_year">Purchase year</label>
                        <input id="purchase_year" name="purchase_year" type="number" min="1990" max="{{ now()->year }}"
                               value="{{ old('purchase_year', $laptop->purchase_year) }}"
                               class="form-control @error('purchase_year') is-invalid @enderror" placeholder="{{ now()->year }}">
                        @error('purchase_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Specs --}}
        <div class="card mb-3">
            <div class="card-header">Specifications <span class="text-muted ms-2 fw-normal" style="text-transform:none">Optional, but helps the technician</span></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="processor">Processor</label>
                        <input id="processor" name="processor" type="text" value="{{ old('processor', $laptop->processor) }}"
                               class="form-control @error('processor') is-invalid @enderror" placeholder="e.g. Intel Core i5-12500H">
                        @error('processor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="ram_gb">RAM (GB)</label>
                        <input id="ram_gb" name="ram_gb" type="number" min="1" max="512" value="{{ old('ram_gb', $laptop->ram_gb) }}"
                               class="form-control @error('ram_gb') is-invalid @enderror" placeholder="16">
                        @error('ram_gb')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="screen_size">Screen (inch)</label>
                        <input id="screen_size" name="screen_size" type="number" step="0.1" min="7" max="30"
                               value="{{ old('screen_size', $laptop->screen_size) }}"
                               class="form-control @error('screen_size') is-invalid @enderror" placeholder="15.6">
                        @error('screen_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="storage">Storage</label>
                        <input id="storage" name="storage" type="text" value="{{ old('storage', $laptop->storage) }}"
                               class="form-control @error('storage') is-invalid @enderror" placeholder="e.g. 512 GB NVMe SSD">
                        @error('storage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="gpu">Graphics card</label>
                        <input id="gpu" name="gpu" type="text" value="{{ old('gpu', $laptop->gpu) }}"
                               class="form-control @error('gpu') is-invalid @enderror" placeholder="e.g. NVIDIA RTX 3050">
                        @error('gpu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="os">Operating system</label>
                        <input id="os" name="os" type="text" value="{{ old('os', $laptop->os) }}"
                               class="form-control @error('os') is-invalid @enderror" placeholder="e.g. Windows 11">
                        @error('os')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Photo & notes --}}
        <div class="card mb-3">
            <div class="card-header">Photo &amp; condition</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="photo">Photo</label>
                    @if ($isEdit && $laptop->photoUrl())
                        <div class="mb-2 d-flex align-items-center gap-3">
                            <img src="{{ $laptop->photoUrl() }}" alt="Current photo" style="height:80px;border-radius:var(--tc-radius-sm)">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="remove_photo">
                                <label class="form-check-label" for="remove_photo">Remove current photo</label>
                            </div>
                        </div>
                    @endif
                    <input id="photo" name="photo" type="file" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                    <div class="form-text">JPG/PNG/WebP, max 2 MB. Optional.</div>
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-0">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="1000"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Existing damage, previous repairs, accessories you will bring, etc.">{{ old('notes', $laptop->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="tc-btn tc-btn-accent">{{ $isEdit ? 'Save changes' : 'Add laptop' }}</button>
            <a href="{{ $isEdit ? route('laptops.show', $laptop) : route('laptops.index') }}" class="tc-btn tc-btn-outline">Cancel</a>
        </div>
    </form>
@endsection
