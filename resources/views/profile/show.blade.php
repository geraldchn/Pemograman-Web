@extends('layouts.app')

@section('title', 'Profile - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="pe-7s-user icon-gradient bg-mean-fruit"></i>
                </div>
                <div>
                    My Profile
                    <div class="page-title-subheading">Manage your account details and password.</div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Summary card --}}
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $user->avatarUrl() }}" alt="Profile photo" class="rounded-circle mb-3"
                         style="width:120px;height:120px;object-fit:cover">
                    <h5 class="mb-0">{{ $user->name }}</h5>
                    <div class="text-muted mb-2">{{ $user->email }}</div>
                    <span class="badge bg-primary">{{ $user->roleLabel() }}</span>
                    <hr>
                    <div class="text-start small">
                        <div class="mb-1"><i class="pe-7s-call me-2"></i>{{ $user->phone ?: '-' }}</div>
                        <div><i class="pe-7s-map-marker me-2"></i>{{ $user->address ?: '-' }}</div>
                    </div>
                    <div class="text-muted small mt-3">Joined {{ $user->created_at->translatedFormat('d F Y') }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Edit personal info --}}
            <div class="card mb-3">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="name">Full name</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone / WhatsApp No.</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                                   class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="address">Address</label>
                            <textarea id="address" name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="avatar">Profile photo</label>
                            <input id="avatar" name="avatar" type="file" accept="image/*"
                                   class="form-control @error('avatar') is-invalid @enderror">
                            @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">JPG/PNG/WebP, max 2 MB.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>

            {{-- Change password --}}
            <div class="card mb-3">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <input id="current_password" name="current_password" type="password"
                                   class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="new_password">New password</label>
                                <input id="new_password" name="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="password_confirmation">Confirm new password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                       class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Change password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
