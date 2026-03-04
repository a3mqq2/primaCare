<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-skin="flat">
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
                    if (config.dir !== serverDir) {
                        config.dir = serverDir;
                        sessionStorage.setItem("__THEME_CONFIG__", JSON.stringify(config));
                    }
                }
                document.documentElement.setAttribute("dir", serverDir);
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
            body {
                font-family: "IBM Plex Sans Arabic", sans-serif !important;
            }
            .employee-navbar {
                background: var(--bs-body-bg);
                border-bottom: 1px solid var(--bs-border-color);
                padding: 0.75rem 0;
            }
            .employee-content {
                min-height: calc(100vh - 130px);
            }
        </style>

        @stack('css')
    </head>

    <body>
        @include('layouts.partials.impersonation-bar')
        <nav class="employee-navbar">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('medical-records.index') }}">
                        <img src="{{ asset('assets/images/logo-black.png') }}" alt="logo" height="36" class="d-none" id="logo-light" />
                        <img src="{{ asset('assets/images/logo-black.png') }}" alt="logo" height="36" class="d-none" id="logo-dark" />
                    </a>

                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" type="button">
                                <i class="ti ti-sun d-none" id="emp-theme-light"></i>
                                <i class="ti ti-moon d-none" id="emp-theme-dark"></i>
                                <i class="ti ti-sun-moon d-none" id="emp-theme-system"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" data-thememode="dropdown">
                                <label class="dropdown-item cursor-pointer">
                                    <input class="form-check-input" type="radio" name="data-bs-theme" value="light" style="display: none" />
                                    <i class="ti ti-sun align-middle me-1"></i>
                                    <span class="align-middle">{{ __('dashboard.light') }}</span>
                                </label>
                                <label class="dropdown-item cursor-pointer">
                                    <input class="form-check-input" type="radio" name="data-bs-theme" value="dark" style="display: none" />
                                    <i class="ti ti-moon align-middle me-1"></i>
                                    <span class="align-middle">{{ __('dashboard.dark') }}</span>
                                </label>
                                <label class="dropdown-item cursor-pointer">
                                    <input class="form-check-input" type="radio" name="data-bs-theme" value="system" style="display: none" />
                                    <i class="ti ti-sun-moon align-middle me-1"></i>
                                    <span class="align-middle">{{ __('dashboard.system') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" type="button">
                                @if(app()->getLocale() === 'ar')
                                    <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="AR" class="rounded-circle" height="16" />
                                @else
                                    <img src="{{ asset('assets/images/flags/us.svg') }}" alt="EN" class="rounded-circle" height="16" />
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{{ route('locale.change', 'en') }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                                    <img src="{{ asset('assets/images/flags/us.svg') }}" alt="English" class="me-1 rounded-circle" height="16" />
                                    English
                                </a>
                                <a href="{{ route('locale.change', 'ar') }}" class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                                    <img src="{{ asset('assets/images/flags/sa.svg') }}" alt="Arabic" class="me-1 rounded-circle" height="16" />
                                    العربية
                                </a>
                            </div>
                        </div>

                        <span class="btn btn-light btn-sm pe-none">
                            <i class="ti ti-user me-1"></i>{{ Auth::user()->name ?? '' }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="ti ti-logout me-1"></i>{{ __('dashboard.log_out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="employee-content">
            <div class="container py-4">
                @yield('content')
            </div>
        </div>

        <footer class="py-3 border-top text-center text-muted">
            <small><script>document.write(new Date().getFullYear())</script> &copy; تنفيذ مكتب تقنيات الصحة بوزارة الصحة الليبية</small>
        </footer>

        <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>

        <script>
            (function() {
                var theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                function updateLogo(t) {
                    var light = document.getElementById('logo-light');
                    var dark = document.getElementById('logo-dark');
                    if (t === 'dark') {
                        light.classList.remove('d-none');
                        dark.classList.add('d-none');
                    } else {
                        light.classList.add('d-none');
                        dark.classList.remove('d-none');
                    }
                }
                function updateThemeIcon(t) {
                    document.getElementById('emp-theme-light').classList.add('d-none');
                    document.getElementById('emp-theme-dark').classList.add('d-none');
                    document.getElementById('emp-theme-system').classList.add('d-none');
                    if (t === 'dark') document.getElementById('emp-theme-dark').classList.remove('d-none');
                    else if (t === 'system') document.getElementById('emp-theme-system').classList.remove('d-none');
                    else document.getElementById('emp-theme-light').classList.remove('d-none');
                }
                var obs = new MutationObserver(function(m) {
                    var t = document.documentElement.getAttribute('data-bs-theme') || 'light';
                    updateLogo(t);
                });
                obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
                setTimeout(function() {
                    var t = document.documentElement.getAttribute('data-bs-theme') || 'light';
                    updateLogo(t);
                    var stored = sessionStorage.getItem("__THEME_CONFIG__");
                    if (stored) {
                        var config = JSON.parse(stored);
                        updateThemeIcon(config.theme || 'light');
                    } else {
                        updateThemeIcon(t);
                    }
                }, 100);
            })();
        </script>

        @stack('js')
    </body>
</html>
