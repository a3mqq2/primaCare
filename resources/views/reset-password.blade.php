@extends('layouts.auth')

@section('title', __('auth.reset_password') . ' | PrimaCare')

@section('content')
    <div class="mt-auto">
        <div class="text-center">
            <h4 class="fw-bold text-dark">{{ __('auth.reset_password') }}</h4>
            <p class="text-muted w-lg-75 mx-auto">{{ __('auth.reset_password_subtitle') }}</p>
        </div>

        <form class="mt-4" method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="mb-3">
                <label for="email" class="form-label">
                    {{ __('auth.email') }}
                    <span class="text-danger">{{ __('auth.required') }}</span>
                </label>
                <div class="app-search">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" placeholder="{{ __('auth.email_placeholder') }}" required />
                    <i class="ti ti-mail app-search-icon text-muted"></i>
                </div>
                @error('email')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    {{ __('auth.new_password') }}
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

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    {{ __('auth.confirm_password') }}
                    <span class="text-danger">{{ __('auth.required') }}</span>
                </label>
                <div class="app-search">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('auth.password_placeholder') }}" required />
                    <i class="ti ti-lock-password app-search-icon text-muted"></i>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold py-2">{{ __('auth.reset_password_btn') }}</button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted">{{ __('auth.back_to_login') }}</a>
            </div>
        </form>
    </div>
@endsection
