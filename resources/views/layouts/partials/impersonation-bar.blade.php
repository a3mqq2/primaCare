@if(session()->has('impersonator_id'))
<div style="background:#dc3545;color:#fff;padding:10px 16px;text-align:center;font-size:14px;font-weight:500;z-index:9999;position:relative;">
    <i class="ti ti-alert-circle me-1"></i>
    {{ __('users.impersonation_notice', ['name' => Auth::user()->name]) }}
    <form method="POST" action="{{ route('impersonate.leave') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-sm btn-light ms-2" style="font-size:13px;">
            <i class="ti ti-arrow-back-up me-1"></i>{{ __('users.impersonation_leave') }}
        </button>
    </form>
</div>
@endif
