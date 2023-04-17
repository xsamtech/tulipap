<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@lang('miscellaneous.app_description')">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="tlpp-devref" content="">

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
@if (Route::current()->getName() == 'home')
            @lang('miscellaneous.customer.home.title')
@endif

@if (Route::current()->getName() == 'about_us.home')
            @lang('miscellaneous.customer.about_us.title')
@endif

@if (Route::current()->getName() == 'about_us.help')
            @lang('miscellaneous.customer.help.title')
@endif
        </title>
    </head>

    <body>
        <!-- ======= Header ======= -->
        <header id="header" class="fixed-top {{ Route::current()->getName() != 'home' ? 'header-inner-pages' : '' }}">
            <div class="container d-flex align-items-center">
                <a href="{{ route('home') }}" class="logo me-auto"><img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="" width="150"></a>

                <nav id="navbar" class="navbar shadow-0">
                    <ul>
                        <li><a class="nav-link scrollto {{ Route::current()->getName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
                        <li><a class="nav-link scrollto {{ Route::current()->getName() == 'about_us.home' ? 'active' : '' }}" href="{{ route('about_us.home') }}">@lang('miscellaneous.menu.customer.about')</a></li>
                        <li><a class="nav-link scrollto {{ Route::current()->getName() == 'about_us.help' ? 'active' : '' }}" href="{{ route('about_us.help') }}">@lang('miscellaneous.menu.customer.help')</a></li>
                        <li class="dropdown dropend">
                            <a role="button" id="dropdownLanguage" class="dropdown-toggle hidden-arrow" href="#">
                                <i class="bi bi-translate fs-5"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownLanguage">
@foreach ($available_locales as $locale_name => $available_locale)
    @if ($available_locale != $current_locale)
                                <li>
                                    <a class="dropdown-item" href="{{ route('change_language', ['locale' => $available_locale]) }}">
                                        {{ $locale_name }}
        @switch($available_locale)
            @case('ln')
                                        <span class="fi fi-cd"></span>
                @break
            @case('en')
                                        <span class="fi fi-us"></span>
                @break
            @default
                                        <span class="fi fi-{{ $available_locale }}"></span>
        @endswitch
                                    </a>
                                </li>
    @endif
@endforeach
                            </ul>
                        </li>
                        <li><a class="getstarted scrollto" href="{{ route('login') }}">@lang('miscellaneous.menu.customer.login')</a></li>
                    </ul>

                    <i class="bi bi-list mobile-nav-toggle"></i>
                </nav><!-- .navbar -->
            </div>
        </header>
        <!-- ======= End Header ======= -->

@if (Route::current()->getName() == 'home')
        <!-- ======= Hero Section ======= -->
        <section id="hero" class="d-flex align-items-center">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 d-flex flex-column justify-content-center pt-4 pt-lg-0 order-2 order-lg-1" data-aos="fade-up" data-aos-delay="200">
                        <h1 class="mb-4">@lang('miscellaneous.customer.home.slide1.title')</h1>
                        <h2 class="mb-4">@lang('miscellaneous.customer.home.slide1.content')</h2>
                        <h5 class="mb-4 text-light">@lang('miscellaneous.customer.home.slide1.comment')</h5>
                        <div class="d-flex justify-content-center justify-content-lg-start">
                            <a href="{{ route('invoice.home') }}" class="btn-get-started scrollto">@lang('miscellaneous.customer.home.slide1.link1')</a>
                            <a href="{{ route('provider.home') }}" class="btn-get-purchase scrollto">@lang('miscellaneous.customer.home.slide1.link2')</a>
                        </div>
                    </div>
                    <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-in" data-aos-delay="200">
                        <div class="bg-image">
                            <img src="{{ asset('assets/img/ads/ad-003.png') }}" class="img-fluid animated" alt="">
                            <div class="mask"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ======= End Hero Section ======= -->
@endif

        <!-- ======= Main ======= -->
        <main id="main">
@if (Route::current()->getName() != 'home')

            <!-- ======= Breadcrumbs ======= -->
            <section id="breadcrumbs" class="breadcrumbs">
                <div class="container">
    @if (Route::current()->getName() == 'about_us.home')

                    <ol>
                        <li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
                        <li>@lang('miscellaneous.menu.customer.about')</li>
                    </ol>

                    <h2>A propos de nous</h2>

    @endif

    @if (Route::current()->getName() == 'about_us.help')

                    <ol>
                        <li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
                        <li><a href="{{ route('about_us.home') }}">@lang('miscellaneous.menu.customer.about')</a></li>
                        <li>@lang('miscellaneous.menu.customer.help')</li>
                    </ol>

                    <h2>Centre d'aide</h2>

    @endif
                </div>
            </section>
            <!-- ======= End Breadcrumbs ======= -->

            <section class="inner-page">
                <div class="container">

    @yield('customer-content')

                </div>
            </section>
@else

    @yield('customer-content')

@endif
        </main>
        <!-- ======= End Main ======= -->

        <!-- ======= Footer ======= -->
        <footer id="footer">
            <div class="footer-newsletter">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <h4>@lang('miscellaneous.customer.home.newsletter.title')</h4>
                            <p>@lang('miscellaneous.customer.home.newsletter.content')</p>
                            <form action="" method="post">
                                <input type="email" name="email" placeholder="@lang('miscellaneous.customer.home.newsletter.placeholder')">
                                <input type="submit" value="@lang('miscellaneous.customer.home.newsletter.subscribe')">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-top">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 footer-contact text-sm-start text-center">
                            <div class="bg-image d-sm-block d-flex justify-content-center mb-4">
                                <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="Tulipap" width="230">
                                <div class="mask"></div>
                            </div>
                            <p class="fs-5 mb-4">
                                @lang('miscellaneous.app_description')
                            </p>
                        </div>

                        <div class="col-lg-3 col-md-4 ms-auto px-sm-0 px-4 footer-links">
                            <h4>@lang('miscellaneous.customer.footer.useful_links')</h4>
                            <ul>
                                <li><i class="bx bx-chevron-right"></i> <a href="{{ route('about_us.home') }}">@lang('miscellaneous.menu.customer.about')</a></li>
                                <li><i class="bx bx-chevron-right"></i> <a href="{{ route('about_us.help') }}">@lang('miscellaneous.menu.customer.help')</a></li>
                                <li><i class="bx bx-chevron-right"></i> <a href="{{ route('notification.home') }}">@lang('miscellaneous.menu.notifications')</a></li>
                                <li><i class="bx bx-chevron-right"></i> <a href="{{ route('cart.home') }}">@lang('miscellaneous.menu.customer.cart')</a></li>
                                <li><i class="bx bx-chevron-right"></i> <a href="{{ route('invoice.home') }}">@lang('miscellaneous.menu.customer.invoice')</a></li>
                            </ul>
                        </div>

                        <div class="col-lg-4 col-md-6 px-sm-0 px-4 footer-links">
                            <h4>@lang('miscellaneous.customer.footer.social_network.title')</h4>
                            <p>@lang('miscellaneous.customer.footer.social_network.content')</p>
                            <div class="social-links mt-3">
                                <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                                <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container footer-bottom d-flex justify-content-center">
                <div class="copyright">
                    &copy; Copyright {{ date('Y') }} <span class="d-inline-block mx-1"><a href="https://xsam-tech.dev:1443/">Xsam Technologies</a>.</span> <br class="d-sm-none d-block">@lang('miscellaneous.all_right_reserved').
                </div>
            </div>
        </footer>
        <!-- ======= End Footer ======= -->

        <div id="preloader"></div>
        <button class="back-to-top d-flex align-items-center justify-content-center border-0"><i class="bi bi-arrow-up-short"></i></button>

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
