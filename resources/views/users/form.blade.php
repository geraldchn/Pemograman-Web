@extends('layouts.app')

@php($isEdit = $user->exists)
@php($isSelf = $isEdit && $user->is(auth()->user()))

@section('title', ($isEdit ? 'Edit User' : 'Add User').' - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-user icon-gradient bg-mean-fruit"></i></div>
                <div>
                    {{ $isEdit ? 'Edit User' : 'Add User' }}
                    <div class="page-title-subheading">{{ $isEdit ? $user->email : 'Create a new account.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3" style="max-width:760px">
        <div class="card-body">
            <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="role">Role</label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" @disabled($isSelf)>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                    {{ ucfirst($role === 'teknisi' ? 'technician' : $role) }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isSelf)
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <div class="form-text">You cannot change your own role.</div>
                        @endif
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Phone / WhatsApp No.</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" name="password" type="password" @required(! $isEdit) autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">{{ $isEdit ? 'Leave blank to keep the current password.' : 'At least 8 characters.' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               autocomplete="new-password" class="form-control">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="tc-btn tc-btn-primary">{{ $isEdit ? 'Save changes' : 'Create user' }}</button>
                    <a href="{{ route('users.index') }}" class="tc-btn tc-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
