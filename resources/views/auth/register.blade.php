@extends('layouts.auth')

@section('title', 'Sign up - Techcare')

@section('content')
    <h4 class="mb-1"><div>Create your Techcare account,</div>
        <span>Sign up to book laptop service.</span></h4>
    <hr>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-md-7 mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5 mb-3">
                <label for="phone" class="form-label">Phone / WhatsApp No.</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                       class="form-control @error('phone') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password" required
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">At least 8 characters.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Sign up</button>
    </form>

    <div class="text-center mt-3">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </div>
@endsection
