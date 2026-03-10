@extends('layouts.app')

@section('title', __('users.add_user') . ' - PrimaCare')

@push('css')
<link href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/choices/choices.min.css') }}" rel="stylesheet" />
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
    .choices { margin-bottom: 0; }
    .choices .choices__inner {
        min-height: 38px;
        padding: 4px 8px;
        border-color: var(--bs-border-color);
        background-color: var(--bs-body-bg);
        border-radius: var(--bs-border-radius);
    }
    .choices .choices__input { background-color: transparent; color: var(--bs-body-color); }
    .choices .choices__list--dropdown { border-color: var(--bs-border-color); background-color: var(--bs-body-bg); }
    .choices .choices__list--dropdown .choices__item { color: var(--bs-body-color); }
    .choices .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: var(--bs-primary); color: #fff; }
    .choices .choices__list--single .choices__item { color: var(--bs-body-color); }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('users.add_user') }}</h4>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-right me-1"></i>{{ __('users.back') }}
            </a>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <form id="user-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" />
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" dir="ltr" />
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.password') }} <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" />
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.password_confirmation') }} <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
                        </div>
                    </div>

                    @if(auth()->user()->isSystemAdmin())
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('users.role') }} <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select">
                                <option value="">{{ __('users.select_role') }}</option>
                                <option value="system_admin">{{ __('users.roles.system_admin') }}</option>
                                <option value="center_employee">{{ __('users.roles.center_employee') }}</option>
                            </select>
                            <div class="invalid-feedback" id="error-role"></div>
                        </div>
                        <div class="col-md-6 mb-3" id="center-field" style="display:none;">
                            <label class="form-label">{{ __('users.center') }} <span class="text-danger">*</span></label>
                            <select name="center_id" id="center_id" class="form-select">
                                <option value="">{{ __('users.select_center') }}</option>
                            </select>
                            <div class="invalid-feedback" id="error-center_id"></div>
                        </div>
                    </div>

                    <div class="mb-3" id="manager-field" style="display:none;">
                        <div class="form-check">
                            <input type="checkbox" name="is_center_manager" id="is_center_manager" class="form-check-input" value="1" />
                            <label class="form-check-label" for="is_center_manager">{{ __('users.is_center_manager') }}</label>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="role" id="role" value="center_employee" />
                    <input type="hidden" name="center_id" id="center_id" value="{{ auth()->user()->center_id }}" />
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_center_manager" id="is_center_manager" class="form-check-input" value="1" />
                            <label class="form-check-label" for="is_center_manager">{{ __('users.is_center_manager') }}</label>
                        </div>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="ti ti-check me-1"></i>{{ __('users.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/choices/choices.min.js') }}"></script>
<script>
    let centerChoices = null;

    function initCenterChoices(selectEl) {
        let choices = new Choices(selectEl, {
            searchEnabled: true,
            removeItemButton: true,
            shouldSort: false,
            placeholderValue: '',
            itemSelectText: '',
            noResultsText: '',
            noChoicesText: ''
        });

        function loadCenters(search) {
            pcFetch('/centers/search?search=' + encodeURIComponent(search || ''))
                .then(r => r.json())
                .then(centers => {
                    let locale = window.PrimaCare.locale;
                    choices.clearChoices();
                    choices.setChoices(
                        centers.map(c => ({ value: String(c.id), label: locale === 'ar' ? c.name_ar : c.name_en })),
                        'value', 'label', true
                    );
                });
        }
        loadCenters('');

        selectEl.addEventListener('search', function(e) {
            loadCenters(e.detail.value);
        });

        return choices;
    }

    document.addEventListener('DOMContentLoaded', function() {
        let roleSelect = document.getElementById('role');
        let centerField = document.getElementById('center-field');
        let managerField = document.getElementById('manager-field');

        let centerEl = document.getElementById('center_id');
        if (centerEl && centerEl.type !== 'hidden') {
            centerChoices = initCenterChoices(centerEl);
        }

        if (centerField && managerField) {
            roleSelect.addEventListener('change', function() {
                toggleCenterFields(this.value);
            });
        }

        function toggleCenterFields(role) {
            if (role === 'center_employee') {
                centerField.style.display = '';
                managerField.style.display = '';
            } else {
                centerField.style.display = 'none';
                managerField.style.display = 'none';
                if (centerChoices) {
                    centerChoices.removeActiveItems();
                    centerChoices.setChoiceByValue('');
                }
                document.getElementById('is_center_manager').checked = false;
            }
        }

        document.getElementById('user-form').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();

            let btn = document.getElementById('submit-btn');
            btn.disabled = true;
            document.getElementById('loading').style.display = 'flex';

            let payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value,
                role: document.getElementById('role').value,
                center_id: document.getElementById('center_id').value || null,
                is_center_manager: document.getElementById('is_center_manager').checked ? 1 : 0
            };

            pcFetch('{{ route("users.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(({status, body}) => {
                btn.disabled = false;
                document.getElementById('loading').style.display = 'none';

                if (status === 422) {
                    showErrors(body.errors);
                    return;
                }

                if (body.success) {
                    Swal.fire({
                        icon: 'success',
                        title: body.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '{{ route("users.index") }}';
                    });
                }
            })
            .catch(() => {
                btn.disabled = false;
                document.getElementById('loading').style.display = 'none';
                Swal.fire({ icon: 'error', title: window.PrimaCare.messages.error });
            });
        });
    });

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    function showErrors(errors) {
        for (let field in errors) {
            let input = document.querySelector(`[name="${field}"]`);
            let errorEl = document.getElementById(`error-${field}`);
            if (input) input.classList.add('is-invalid');
            if (errorEl) errorEl.textContent = errors[field][0];
        }
    }
</script>
@endpush
