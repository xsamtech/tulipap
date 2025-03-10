<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@lang('miscellaneous.app_description')">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicons -->
        <link rel="icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('assets/img/favicon/android-chrome-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512"  href="{{ asset('assets/img/favicon/android-chrome-512x512.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- Font Icons Files -->
        <link rel="stylesheet" href="{{ asset('assets/icons/bootstrap-icons/bootstrap-icons.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/icons/boxicons/css/boxicons.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/icons/remixicon/remixicon.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/icons/xsam-font-icons/style.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">

        <!-- Addons CSS Files -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/arsha/aos/aos.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/custom/bootstrap/css/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/custom/mdb/css/mdb.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/arsha/glightbox/css/glightbox.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/addons/arsha/swiper/swiper-bundle.min.css') }}">
        <!-- Arsha CSS File -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.arsha.css') }}">
        <!-- Custom CSS File -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.custom.css') }}">

        <title>
@if (Route::current()->getName() == 'login')
            @lang('miscellaneous.login_title1')
@endif

@if (Route::current()->getName() == 'register')
            @lang('miscellaneous.register_title1')
@endif

@if (Route::current()->getName() == 'register.entity')
    @if ($entity == 'admin')
            @lang('miscellaneous.register_title1')
    @endif

    @if ($entity == 'superadmin')
            @lang('miscellaneous.register_title1')
    @endif
@endif
        </title>
    </head>

    <body>
        <div id="auth">
@yield('auth-content')
        </div>

        <!-- Addons JS Files -->
        <script src="{{ asset('assets/addons/custom/jquery/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/aos/aos.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/mdb/js/mdb.min.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/glightbox/js/glightbox.min.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/isotope-layout/isotope.pkgd.min.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/waypoints/noframework.waypoints.js') }}"></script>
        <script src="{{ asset('assets/addons/arsha/php-email-form/validate.js') }}"></script>

        <!-- Arsha JS File -->
        <script src="{{ asset('assets/js/script.arsha.js') }}"></script>

        <!-- Custom JS File -->
        <script src="{{ asset('assets/js/script.custom.js') }}"></script>
    </body>
</html>
