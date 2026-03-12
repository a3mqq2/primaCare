<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title', 'PrimaCare')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="author" content="PrimaCare" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
        <!-- Set direction before theme config -->
        <script>
            (function() {
                var dir = '{{ app()->getLocale() === "ar" ? "rtl" : "ltr" }}';
                var config = sessionStorage.getItem('__THEME_CONFIG__');
                if (config) {
                    config = JSON.parse(config);
                    config.dir = dir;
                    sessionStorage.setItem('__THEME_CONFIG__', JSON.stringify(config));
                }
                document.documentElement.setAttribute('dir', dir);
            })();
        </script>
        <!-- Theme Config Js -->
        <script src="{{ asset('assets/js/config.js') }}"></script>

        <!-- Vendor css -->
        <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- App css -->
        <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

        @if(app()->getLocale() === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'IBM Plex Sans Arabic', sans-serif; }
        </style>
        @endif
    </head>

    <body class="bg-light">
        <div class="d-flex min-vh-100 align-items-center justify-content-center">
            <div class="card shadow-sm border-0" style="width: 100%; max-width: 450px;">
                <div class="card-body p-4">
                    <div class="text-end mb-2">
                        @if(app()->getLocale() === 'ar')
                            <a href="{{ route('locale.change', 'en') }}" class="btn btn-sm btn-light">English</a>
                        @else
                            <a href="{{ route('locale.change', 'ar') }}" class="btn btn-sm btn-light">عربي</a>
                        @endif
                    </div>

                    <div class="text-center mb-3">
                        <a href="/">
                            <img src="{{ asset('assets/images/primacare-ar.png') }}" alt="logo" style="height: 80px;" />
                        </a>
                    </div>

                    @yield('content')

                    <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.8rem;">
                        &copy; {{ date('Y') }} تنفيذ قسم تقنية المعلومات وزارة الصحة بحكومة الوحدة الوطنية
                    </p>
                </div>
            </div>
        </div>

        <!-- Vendor js -->
        <script src="{{ asset('assets/js/vendors.min.js') }}"></script>

        <!-- App js -->
        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>
</html>
