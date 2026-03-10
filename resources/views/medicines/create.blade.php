@extends('layouts.app')

@section('title', __('medicines.add_medicine') . ' - PrimaCare')

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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ __('medicines.add_medicine') }}</h4>
            <a href="{{ route('medicines.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-right me-1"></i>{{ __('medicines.back') }}
            </a>
        </div>

        <div class="card position-relative">
            <div class="loading-overlay" id="loading" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="card-body">
                <form id="medicine-form">
                    <div class="mb-3">
                        <label class="form-label">{{ __('medicines.name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" />
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('medicines.description') }}</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="error-description"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="ti ti-check me-1"></i>{{ __('medicines.save') }}
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
        document.getElementById('medicine-form').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();

            let btn = document.getElementById('submit-btn');
            btn.disabled = true;
            document.getElementById('loading').style.display = 'flex';

            let payload = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value
            };

            pcFetch('{{ route("medicines.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
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
                        window.location.href = '{{ route("medicines.index") }}';
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
