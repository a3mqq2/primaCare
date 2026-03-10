@extends('layouts.auth')

@section('title', __('auth.forgot_password_title') . ' | PrimaCare')

@section('content')
    <div class="mt-auto">
        <div class="text-center">
            <h4 class="fw-bold text-dark">{{ __('auth.forgot_password_title') }}</h4>
            <p class="text-muted w-lg-75 mx-auto">{{ __('auth.forgot_password_subtitle') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success text-center">
                {{ session('status') }}
            </div>
        @endif

        <form class="mt-4" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">
                    {{ __('auth.email') }}
                    <span class="text-danger">{{ __('auth.required') }}</span>
                </label>
                <div class="app-search">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('auth.email_placeholder') }}" required />
                    <i class="ti ti-mail app-search-icon text-muted"></i>
                </div>
                @error('email')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold py-2">{{ __('auth.send_reset_link') }}</button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted">{{ __('auth.back_to_login') }}</a>
            </div>
        </form>
    </div>
@endsection
