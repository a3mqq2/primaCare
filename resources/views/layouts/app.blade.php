<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-skin="flat" data-menu-color="light">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title', 'PrimaCare')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />

        <script>
            (function() {
                var serverDir = "{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}";
                var stored = sessionStorage.getItem("__THEME_CONFIG__");
                if (stored) {
                    var config = JSON.parse(stored);
                    var changed = false;
                    if (config.dir !== serverDir) { config.dir = serverDir; changed = true; }
                    if (config.skin !== "flat") { config.skin = "flat"; changed = true; }
                    if (config["sidenav-color"] !== "light") { config["sidenav-color"] = "light"; changed = true; }
                    if (changed) sessionStorage.setItem("__THEME_CONFIG__", JSON.stringify(config));
                }
                document.documentElement.setAttribute("dir", serverDir);
                document.documentElement.setAttribute("data-skin", "flat");
                document.documentElement.setAttribute("data-menu-color", "light");
            })();
        </script>

        <script src="{{ asset('assets/js/config.js') }}"></script>
        <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css" />
        <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
        <style>
            :root {
                --theme-font-sans-serif: "IBM Plex Sans Arabic", sans-serif !important;
            }
            .sidenav-menu,
            .sidenav-menu *:not(i):not([class*="ti"]) {
                font-family: "IBM Plex Sans Arabic", sans-serif !important;
            }
        </style>

        @stack('css')
    </head>

    <body>
        @include('layouts.partials.impersonation-bar')
        <div class="wrapper">
            <header class="app-topbar">
                <div class="container-fluid topbar-menu">
                    <div class="d-flex align-items-center gap-2">
                        <div class="logo-topbar">
                            <a href="{{ route('dashboard') }}" class="logo-light">
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/primacare-ar.png') }}" alt="logo" />
                                </span>
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo" />
                                </span>
                            </a>

                            <a href="{{ route('dashboard') }}" class="logo-dark">
                                <span class="logo-lg">
                                    <img src="{{ asset('assets/images/primacare-ar.png') }}" alt="dark logo" />
                                </span>
                                <span class="logo-sm">
                                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="small logo" />
                                </span>
                            </a>
                        </div>

                        <button class="sidenav-toggle-button btn btn-primary btn-icon">
                            <i class="ti ti-menu-4"></i>
                        </button>

                        <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu">
                            <i class="ti ti-menu-4"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div id="theme-dropdown" class="topbar-item d-none d-sm-flex">
                            <div class="dropdown">
                                <button class="topbar-link" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                                    <i class="ti ti-sun topbar-link-icon d-none" id="theme-icon-light"></i>
                                    <i class="ti ti-moon topbar-link-icon d-none" id="theme-icon-dark"></i>
                                    <i class="ti ti-sun-moon topbar-link-icon d-none" id="theme-icon-system"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" data-thememode="dropdown">
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="light" style="display: none" />
                                        <i class="ti ti-sun align-middle me-1 fs-16"></i>
                                        <span class="align-middle">{{ __('dashboard.light') }}</span>
                                    </label>
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="dark" style="display: none" />
                                        <i class="ti ti-moon align-middle me-1 fs-16"></i>
                                        <span class="align-middle">{{ __('dashboard.dark') }}</span>
                                    </label>
                                    <label class="dropdown-item cursor-pointer">
                                        <input class="form-check-input" type="radio" name="data-bs-theme" value="system" style="display: none" />
                                        <i class="ti ti-sun-moon align-middle me-1 fs-16"></i>
                                        <span class="align-middle">{{ __('dashboard.system') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="notification-dropdown-people" class="topbar-item">
                            <div class="dropdown">
                                <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
                                    <i class="ti ti-bell topbar-link-icon animate-ring"></i>
                                </button>

                                <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                    <div class="px-3 py-2 border-bottom">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h6 class="m-0 fs-md fw-semibold">{{ __('dashboard.notifications') }}</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="max-height: 300px" data-simplebar="">
                                        <div class="text-center py-4 text-muted">
                                            {{ __('dashboard.no_notifications') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="fullscreen-toggler" class="topbar-item d-none d-md-flex">
                            <button class="topbar-link" type="button" data-toggle="fullscreen">
                                <i class="ti ti-maximize topbar-link-icon"></i>
                                <i class="ti ti-minimize topbar-link-icon d-none"></i>
                            </button>
                        </div>

                        <div id="language-selector-rounded" class="topbar-item">
                            <div class="dropdown">
                                <button class="topbar-link fw-bold" data-bs-toggle="dropdown" type="button" aria-haspopup="false" aria-expanded="false">
                                    @if(app()->getLocale() === 'ar')
                                        <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="Arabic" class="rounded-circle me-2" height="18" />
                                        <span>AR</span>
                                    @else
                                        <img src="{{ asset('assets/images/flags/us.svg') }}" alt="English" class="rounded-circle me-2" height="18" />
                                        <span>EN</span>
                                    @endif
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ route('locale.change', 'en') }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                                        <img src="{{ asset('assets/images/flags/us.svg') }}" alt="English" class="me-1 rounded-circle" height="18" />
                                        <span class="align-middle">English</span>
                                    </a>
                                    <a href="{{ route('locale.change', 'ar') }}" class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                                        <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="Arabic" class="me-1 rounded-circle" height="18" />
                                        <span class="align-middle">العربية</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div id="user-dropdown-detailed" class="topbar-item nav-user">
                            <div class="dropdown">
                                <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" href="#!" aria-haspopup="false" aria-expanded="false">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'U') }}&background=4e73df&color=fff&size=64&font-size=0.45&rounded=true" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image" />
                                    <div class="d-lg-flex align-items-center gap-1 d-none">
                                        <span>
                                            <h5 class="my-0 lh-1 pro-username">{{ Auth::user()->name ?? 'User' }}</h5>
                                        </span>
                                        <i class="ti ti-chevron-down align-middle"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-header noti-title">
                                        <h6 class="text-overflow m-0">{{ __('dashboard.welcome_back') }}</h6>
                                    </div>

                                    <a href="{{ route('profile.index') }}" class="dropdown-item">
                                        <i class="ti ti-user-circle me-1 fs-lg align-middle"></i>
                                        <span class="align-middle">{{ __('dashboard.profile') }}</span>
                                    </a>

                                    <div class="dropdown-divider"></div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item fw-semibold">
                                            <i class="ti ti-logout me-1 fs-lg align-middle"></i>
                                            <span class="align-middle">{{ __('dashboard.log_out') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @include('layouts.partials.sidebar')

            <div class="content-page" style="margin-top: 30px!important;">
                <div class="container-fluid">
                    @yield('content')
                </div>

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 text-center text-md-start">
                                <script>document.write(new Date().getFullYear())</script>
                                &copy; تنفيذ مكتب تقنية المعلومات الصحية بوزارة الصحة الليبية
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>

        <script>
            window.PrimaCare = {
                csrfToken: document.querySelector('meta[name="csrf-token"]').content,
                locale: '{{ app()->getLocale() }}',
                messages: {
                    error: '{{ __("common.error_occurred") }}',
                    sessionExpired: '{{ __("common.session_expired") }}'
                }
            };

            window.pcFetch = function(url, options) {
                options = options || {};
                options.headers = Object.assign({
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }, options.headers || {});

                if (options.method && options.method !== 'GET') {
                    options.headers['X-CSRF-TOKEN'] = window.PrimaCare.csrfToken;
                }

                return fetch(url, options).then(function(response) {
                    if (response.status === 419) {
                        Swal.fire({
                            icon: 'warning',
                            title: window.PrimaCare.messages.sessionExpired,
                            allowOutsideClick: false
                        }).then(function() { location.reload(); });
                        return Promise.reject('session_expired');
                    }
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return Promise.reject('unauthenticated');
                    }
                    return response;
                });
            };
        </script>

        @stack('js')
    </body>
</html>
