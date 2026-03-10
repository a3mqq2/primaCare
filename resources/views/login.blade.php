@extends('layouts.auth')

@section('title', __('auth.sign_in') . ' | PrimaCare')

@section('content')
    <div class="mt-auto">
        <div class="text-center">
            <h4 class="fw-bold text-dark">{{ __('auth.sign_in_title') }}</h4>
            <p class="text-muted w-lg-75 mx-auto">{{ __('auth.sign_in_subtitle') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success text-center mt-3">
                {{ session('status') }}
            </div>
        @endif

        <form class="mt-4" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="login" class="form-label">
                    {{ __('auth.login_label') }}
                    <span class="text-danger">{{ __('auth.required') }}</span>
                </label>
                <div class="app-search">
                    <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}" placeholder="{{ __('auth.login_placeholder') }}" required />
                    <i class="ti ti-user app-search-icon text-muted"></i>
                </div>
                @error('login')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    {{ __('auth.password') }}
                    <span class="text-danger">{{ __('auth.required') }}</span>
                </label>
                <div class="app-search">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('auth.password_placeholder') }}" required />
                    <i class="ti ti-lock-password app-search-icon text-muted"></i>
                </div>
                @error('password')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input form-check-input-light fs-14" type="checkbox" checked name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label" for="remember">{{ __('auth.keep_signed_in') }}</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-muted small">{{ __('auth.forgot_password') }}</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold py-2">{{ __('auth.sign_in') }}</button>
            </div>
        </form>
    </div>
@endsection
