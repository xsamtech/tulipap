@extends('layouts.auth')

@section('auth-content')

            <div class="row g-0 h-100">
                <div class="col-sm-6 d-lg-inline-block d-none p-sm-5 pt-5 px-3 bg-dark-blue">
                    <div class="bg-image d-inline-block mb-5 text-start">
                        <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" width="250">
                        <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                    </div>

                    <h1 class="h1 mb-4 fw-bold text-white" data-aos="fade-down" data-aos-delay="100">@lang('miscellaneous.login_title2')</h1>
                    <h5 class="h5 mb-4 text-white opacity-75 line-height-1_4" data-aos="fade-up" data-aos-delay="100">@lang('miscellaneous.login_description')</h5>
                    <div class="bg-image text-center" data-aos="zoom-in" data-aos-delay="200">
                        <img src="{{ asset('assets/img/ads/ad-006.png') }}" alt="logo"  width="340">
                        <div class="mask"></div>
                    </div>
                </div>
                <div class="col-sm-6 mx-auto">
                    <div class="row">
                        <div class="col-12">
                            <div class="dropdown mt-2">
                                <a href="#" role="button" id="dropdownLanguage" class="px-3 dropdown-toggle hidden-arrow text-dark" data-mdb-toggle="dropdown" aria-expanded="false" title="@lang('miscellaneous.your_language')">
                                    <i class="bi bi-translate fs-4"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownLanguage">
    @foreach ($available_locales as $locale_name => $available_locale)
        @if ($available_locale != $current_locale)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('change_language', ['locale' => $available_locale]) }}">
            @switch($available_locale)
                @case('ln')
                                            <span class="fi fi-cd me-2"></span>
                    @break
                @case('en')
                                            <span class="fi fi-us me-2"></span>
                    @break
                @default
                                            <span class="fi fi-{{ $available_locale }} me-2"></span>
            @endswitch
                                            {{ $locale_name }}
                                        </a>
                                    </li>
        @endif
    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row d-lg-none d-block">
                        <div class="col-sm-7 col-7 mx-auto">
                            <div class="bg-image mt-sm-4 mt-3">
                                <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" class="img-fluid">
                                <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7 mx-auto">
                            <div class="card shadow-0">
                                <div class="card-body">
                                    <form class="mb-4">
                                        <h2 class="h2 mb-lg-5 mb-4 text-center fw-bold text-uppercase">@lang('miscellaneous.login_title1')</h2>

                                        <!-- Email or Phone number -->
                                        <div class="form-outline mb-4">
                                            <input type="text" id="login_username" name="login_username" class="form-control" autofocus/>
                                            <label class="form-label" for="login_username">@lang('miscellaneous.login_username')</label>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-outline mb-4">
                                            <input type="password" id="login_password" name="login_password" class="form-control" />
                                            <label class="form-label" for="login_password">@lang('miscellaneous.password.label')</label>
                                        </div>

                                        <!-- 2 column grid layout for inline styling -->
                                        <div class="row small mb-4">
                                            <div class="col d-flex justify-content-center">
                                                <!-- Checkbox -->
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="" id="remember_me" name="remember_me" />
                                                    <label class="form-check-label" for="remember_me"> @lang('miscellaneous.remember_me') </label>
                                                </div>
                                            </div>

                                            <div class="col">
                                                <!-- Simple link -->
                                                <a href="{{ route('password.request') }}">@lang('miscellaneous.forgotten_password')</a>
                                            </div>
                                        </div>

                                        <!-- Submit button -->
                                        <button type="button" class="btn btn-primary btn-block mb-4">@lang('miscellaneous.connection')</button>

                                        <!-- Register buttons -->
                                        <div class="text-center">
                                            <p>
                                                @lang('miscellaneous.not_member') 
                                                <a href="{{ route('register') }}">@lang('miscellaneous.register_title1')</a>
                                            </p>

                                            <p>@lang('miscellaneous.signup_with')</p>

                                            <button type="button" class="btn btn-floating btn-secondary mx-2 pt-1 shadow-0">
                                                <i class="bx bxl-facebook fs-4"></i>
                                            </button>
                                            <button type="button" class="btn btn-floating btn-secondary mx-2 pt-1 shadow-0">
                                                <i class="bx bxl-google fs-4"></i>
                                            </button>
                                            <button type="button" class="btn btn-floating btn-secondary mx-2 pt-1 shadow-0">
                                                <i class="bx bxl-twitter fs-4"></i>
                                            </button>
                                        </div>
                                    </form>

                                    <hr>

                                    <p class="small text-center">
                                        &copy; Copyright {{ date('Y') }} <span class="d-inline-block mx-1"><a href="https://xsam-tech.dev:1443/">Xsam Technologies</a>.</span> <br>@lang('miscellaneous.all_right_reserved').
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

@endsection
