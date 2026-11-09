@extends('layouts.auth')

@section('title', 'Log in - Techcare')

@section('content')
    <h4 class="mb-1"><div>Welcome back,</div>
        <span>Please log in to your account.</span></h4>
    <hr>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="form-control @error('email') is-invalid @enderror" placeholder="name@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" required
                   class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Log in</button>
    </form>

    <div class="text-center mt-3">
        Don't have an account? <a href="{{ route('register') }}">Sign up as a customer</a>
    </div>
@endsection
