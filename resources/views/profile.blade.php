@extends('layouts.app')

@section('title', __('profile.title') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: .375rem;
    }
    [data-bs-theme="dark"] .loading-overlay {
        background: rgba(0,0,0,0.5);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-3">{{ __('profile.title') }}</h4>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card position-relative">
            <div class="loading-overlay" id="profile-loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('profile.personal_info') }}</h5>
            </div>
            <div class="card-body">
                <form id="profile-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.name') }} <span class="text-danger">*</span></label>
                        <input type="text" id="profile-name" class="form-control" value="{{ $user->name }}" />
                        <div class="invalid-feedback" id="profile-error-name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.email') }} <span class="text-danger">*</span></label>
                        <input type="email" id="profile-email" class="form-control" value="{{ $user->email }}" />
                        <div class="invalid-feedback" id="profile-error-email"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.username') }}</label>
                        <input type="text" class="form-control" value="{{ $user->username }}" disabled />
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('profile.role') }}</label>
                            <input type="text" class="form-control" value="{{ __('profile.' . $user->role) }}" disabled />
                        </div>
                        @if($user->center)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('profile.center') }}</label>
                            <input type="text" class="form-control" value="{{ $user->center->name }}" disabled />
                        </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-primary" id="save-profile-btn">
                        <i class="ti ti-check me-1"></i>{{ __('profile.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card position-relative">
            <div class="loading-overlay" id="password-loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('profile.change_password') }}</h5>
            </div>
            <div class="card-body">
                <form id="password-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.current_password') }} <span class="text-danger">*</span></label>
                        <input type="password" id="current_password" class="form-control" />
                        <div class="invalid-feedback" id="password-error-current_password"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" id="new_password" class="form-control" />
                        <div class="invalid-feedback" id="password-error-new_password"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('profile.confirm_new_password') }} <span class="text-danger">*</span></label>
                        <input type="password" id="new_password_confirmation" class="form-control" />
                    </div>
                    <button type="button" class="btn btn-primary" id="save-password-btn">
                        <i class="ti ti-lock me-1"></i>{{ __('profile.update_password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('save-profile-btn').addEventListener('click', saveProfile);
        document.getElementById('save-password-btn').addEventListener('click', savePassword);
    });

    function saveProfile() {
        clearErrors('profile-form');
        let btn = document.getElementById('save-profile-btn');
        btn.disabled = true;
        document.getElementById('profile-loading').style.display = 'flex';

        let payload = {
            name: document.getElementById('profile-name').value,
            email: document.getElementById('profile-email').value
        };

        pcFetch('{{ route("profile.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(({status, body}) => {
            btn.disabled = false;
            document.getElementById('profile-loading').style.display = 'none';

            if (status === 422) {
                showErrors(body.errors, 'profile');
                return;
            }

            if (body.success) {
                Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('profile-loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function savePassword() {
        clearErrors('password-form');
        let btn = document.getElementById('save-password-btn');
        btn.disabled = true;
        document.getElementById('password-loading').style.display = 'flex';

        let payload = {
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            new_password_confirmation: document.getElementById('new_password_confirmation').value
        };

        pcFetch('{{ route("profile.password") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(({status, body}) => {
            btn.disabled = false;
            document.getElementById('password-loading').style.display = 'none';

            if (status === 422) {
                showErrors(body.errors, 'password');
                return;
            }

            if (body.success) {
                document.getElementById('password-form').reset();
                Swal.fire({ icon: 'success', title: body.message, showConfirmButton: false, timer: 1500 });
            }
        })
        .catch(() => {
            btn.disabled = false;
            document.getElementById('password-loading').style.display = 'none';
            Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
        });
    }

    function clearErrors(formId) {
        document.querySelectorAll(`#${formId} .is-invalid`).forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll(`#${formId} .invalid-feedback`).forEach(el => el.textContent = '');
    }

    function showErrors(errors, prefix) {
        for (let field in errors) {
            let input = document.getElementById(prefix === 'profile' ? 'profile-' + field : field);
            let errorEl = document.getElementById(prefix + '-error-' + field);
            if (input) input.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errors[field][0];
        }
    }
</script>
@endpush
