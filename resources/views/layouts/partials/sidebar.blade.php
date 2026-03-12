<div class="sidenav-menu">
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo logo-light">
            <span class="logo-lg"><img src="{{ asset('assets/images/primacare-ar.png') }}" alt="logo" /></span>
            <span class="logo-sm"><img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo" /></span>
        </span>

        <span class="logo logo-dark">
            <span class="logo-lg"><img src="{{ asset('assets/images/primacare-ar.png') }}" style="height: 50px;" alt="dark logo" /></span>
            <span class="logo-sm"><img src="{{ asset('assets/images/favicon.ico') }}" alt="small logo" /></span>
        </span>
    </a>

    <button class="button-on-hover">
        <span class="btn-on-hover-icon"></span>
    </button>

    <button class="button-close-offcanvas">
        <i class="ti ti-menu-4 align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar="">
        <div id="user-profile-settings" class="sidenav-user" style="background: url({{ asset('assets/images/user-bg-pattern.svg') }})">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="#!" class="link-reset">
                        <img src="{{ asset('assets/images/users/user-1.jpg') }}" alt="user-image" class="rounded-circle mb-2 avatar-md" />
                        <span class="sidenav-user-name fw-bold">{{ Auth::user()->name ?? 'User' }}</span>
                    </a>
                </div>
                <div>
                    <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon" data-bs-toggle="dropdown" data-bs-offset="0,12" href="#!" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                    </a>

                    <div class="dropdown-menu">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">{{ __('dashboard.welcome_back') }}</h6>
                        </div>

                        <a href="{{ route('profile.index') }}" class="dropdown-item">
                            <i class="ti ti-user-circle me-1 fs-lg align-middle"></i>
                            <span class="align-middle">{{ __('dashboard.profile') }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-semibold">
                                <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                                <span class="align-middle">{{ __('dashboard.log_out') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="sidenav-menu">
            <ul class="side-nav">
                <li class="side-nav-title mt-2">{{ __('dashboard.main') }}</li>
                <li class="side-nav-item">
                    <a href="{{ route('dashboard') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="menu-text">{{ __('dashboard.dashboard') }}</span>
                    </a>
                </li>
                @if(auth()->user()->isSystemAdmin() || auth()->user()->isCenterManager())
                <li class="side-nav-item">
                    <a href="{{ route('centers.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-building-hospital"></i></span>
                        <span class="menu-text">{{ __('dashboard.centers') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('users.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text">{{ __('dashboard.users') }}</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->isSystemAdmin())
                <li class="side-nav-item">
                    <a href="{{ route('cities.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-map-pin"></i></span>
                        <span class="menu-text">{{ __('dashboard.cities') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('medicines.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-pill"></i></span>
                        <span class="menu-text">{{ __('dashboard.medicines') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('admin.medical-records.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-report-medical"></i></span>
                        <span class="menu-text">{{ __('dashboard.admin_medical_records') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('admin.dispensings.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-vaccine"></i></span>
                        <span class="menu-text">{{ __('dashboard.dispensing_log') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('admin.statistics.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
                        <span class="menu-text">{{ __('dashboard.statistics') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('admin.activity-logs.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-history"></i></span>
                        <span class="menu-text">{{ __('dashboard.activity_logs') }}</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->isCenterEmployee())
                <li class="side-nav-item">
                    <a href="{{ route('medical-records.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-file-medical"></i></span>
                        <span class="menu-text">{{ __('dashboard.medical_records') }}</span>
                    </a>
                </li>
                @endif

                <li class="side-nav-title mt-2">{{ __('dashboard.account_settings') }}</li>
                <li class="side-nav-item">
                    <a href="{{ route('profile.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-user-circle"></i></span>
                        <span class="menu-text">{{ __('dashboard.profile') }}</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                        @csrf
                        <a href="javascript:void(0);" class="side-nav-link text-danger" onclick="document.getElementById('sidebar-logout-form').submit();">
                            <span class="menu-icon"><i class="ti ti-logout"></i></span>
                            <span class="menu-text">{{ __('dashboard.log_out') }}</span>
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
