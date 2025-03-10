@extends('layouts.auth')

@section('auth-content')

            <div class="row g-0 h-100">
                <div class="col-sm-6 d-lg-inline-block d-none p-sm-5 pt-5 px-3 bg-dark-blue">
                    <div class="bg-image d-inline-block mb-5 text-start">
                        <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" width="250">
                        <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                    </div>

    @if (Route::current()->getName() == 'register')
                    <h1 class="h1 mb-4 fw-bold text-white" data-aos="fade-down" data-aos-delay="100">@lang('miscellaneous.register_title2')</h1>
                    <h5 class="h5 mb-4 text-white opacity-75 line-height-1_4" data-aos="fade-up" data-aos-delay="100">@lang('miscellaneous.register_customer_description')</h5>
                    <div class="bg-image text-center" data-aos="zoom-in" data-aos-delay="200">
                        <img src="{{ asset('assets/img/ads/ad-007.png') }}" alt="logo"  width="340">
                        <div class="mask"></div>
                    </div>
                </div>

                <div class="col-sm-6 mx-auto">
        @include('include.switch_language')

                    <div class="row d-lg-none d-block">
                        <div class="col-sm-7 col-7 mx-auto">
                            <div class="bg-image mt-sm-4 mt-3 mb-4">
                                <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" class="img-fluid">
                                <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7 mx-auto">
                            <div class="card shadow-0">
                                <div class="card-body">
                                    <form>
                                        <h2 class="h2 mb-lg-5 mb-4 text-center fw-bold text-uppercase">@lang('miscellaneous.register_title1')</h2>

                                        <!-- Email or Phone number -->
                                        <div class="form-outline mb-4">
                                            <input type="text" id="register_username" name="register_username" class="form-control" autofocus/>
                                            <label class="form-label" for="register_username">@lang('miscellaneous.login_username')</label>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-outline mb-4">
                                            <input type="password" id="register_password" name="register_password" class="form-control" />
                                            <label class="form-label" for="register_password">@lang('miscellaneous.password.label')</label>
                                        </div>

                                        <!-- Submit button -->
                                        <button type="button" class="btn btn-primary btn-block mb-4">@lang('miscellaneous.connection')</button>
                                    </form>

                                    <p class="text-center"><a href="{{ route('login') }}">@lang('miscellaneous.go_login')</a></p>
    @endif

    @if (Route::current()->getName() == 'register.entity')
        @if ($entity == 'admin')
                    <h1 class="h1 mb-4 fw-bold text-white" data-aos="fade-down" data-aos-delay="100">@lang('miscellaneous.register_title2')</h1>
                    <h5 class="h5 mb-4 text-white opacity-75 line-height-1_4" data-aos="fade-up" data-aos-delay="100">@lang('miscellaneous.register_company_description')</h5>
                    <div class="bg-image text-center" data-aos="zoom-in" data-aos-delay="200">
                        <img src="{{ asset('assets/img/ads/ad-008.png') }}" alt="logo"  width="340">
                        <div class="mask"></div>
                    </div>
                </div>

                <div class="col-sm-6 mx-auto">
            @include('include.switch_language')

                    <div class="row d-lg-none d-block">
                        <div class="col-sm-7 col-7 mx-auto">
                            <div class="bg-image mt-sm-4 mt-3 mb-4">
                                <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" class="img-fluid">
                                <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7 mx-auto">
                            <div class="card shadow-0">
                                <div class="card-body">
                                    <form>
                                        <h2 class="h2 mb-lg-5 mb-4 text-center fw-bold text-uppercase">@lang('miscellaneous.register_title1')</h2>

                                        <!-- Email or Phone number -->
                                        <div class="form-outline mb-4">
                                            <input type="text" id="register_username" name="register_username" class="form-control" autofocus/>
                                            <label class="form-label" for="register_username">@lang('miscellaneous.login_username')</label>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-outline mb-4">
                                            <input type="password" id="register_password" name="register_password" class="form-control" />
                                            <label class="form-label" for="register_password">@lang('miscellaneous.password.label')</label>
                                        </div>

                                        <!-- Submit button -->
                                        <button type="button" class="btn btn-primary btn-block mb-4">@lang('miscellaneous.connection')</button>
                                    </form>

                                    <p class="text-center"><a href="{{ route('login') }}">@lang('miscellaneous.go_login')</a></p>
        @endif

        @if ($entity == 'superadmin')
                    <h1 class="h1 mb-4 fw-bold text-white" data-aos="fade-down" data-aos-delay="100">@lang('miscellaneous.register_title2')</h1>
                    <h5 class="h5 mb-4 text-white opacity-75 line-height-1_4" data-aos="fade-up" data-aos-delay="100">@lang('miscellaneous.register_superadmin_description')</h5>
                    <div class="bg-image text-center" data-aos="zoom-in" data-aos-delay="200">
                        <img src="{{ asset('assets/img/ads/ad-002.png') }}" alt="logo"  width="340">
                        <div class="mask"></div>
                    </div>
                </div>

                <div class="col-sm-6 mx-auto">
            @include('include.switch_language')

                    <div class="row d-lg-none d-block">
                        <div class="col-sm-7 col-7 mx-auto">
                            <div class="bg-image mt-sm-4 mt-3 mb-4">
                                <img src="{{ asset('assets/img/logo/logo_text_01_1.png') }}" alt="logo" class="img-fluid">
                                <div class="mask"><a href="{{ route('home') }}" class="stretched-link"></a></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="card shadow-0">
                                <div class="card-body">
                                    <form method="POST" action="{{ route('register') }}">
                                        <h2 class="h2 mb-lg-5 mb-4 text-center fw-bold text-uppercase">@lang('miscellaneous.register_title1')</h2>

                                        <!-- Hidden inputs :
                                            - Object (To ensure this is the super admin registration)
                                            - Role (Register "Super administrateur" role)
                                            - Service (Necessary for the phone number)
                                            - User status
                                            - Email status
                                            - Phone status
                                            - Phone code (The phone code to associate with the phone number)
                                        -->
                                        <input type="hidden" name="object" value="superadmin">
                                        <input type="hidden" name="role_id" value="{{ $superadmin_role_id }}">
                                        <input type="hidden" name="service_id" value="{{ $m_pesa_service_id }}">
                                        <input type="hidden" name="user_status_id" value="{{ $activated_status_id }}">
                                        <input type="hidden" name="email_status_id" value="{{ $main_status_id }}">
                                        <input type="hidden" name="phone_status_id" value="{{ $main_status_id }}">
                                        <input type="hidden" name="register_phone_code" value="+243">

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <!-- First name -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_firstname" name="register_firstname" class="form-control" aria-describedby="firstname_error_message"{{ !empty($request->register_firstname) ? 'value="' . $request->register_firstname . '"' : '' }} autofocus/>
                                                    <label class="form-label" for="register_firstname">@lang('miscellaneous.firstname')</label>
                                                </div>
            @if ($errors->has('register_firstname'))
                                                <p id="firstname_error_message" class="mb-4 text-danger small">{{ __('validation.required') }}</p>
            @endif
                                            </div>

                                            <div class="col-sm-6">
                                                <!-- Last name -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_lastname" name="register_lastname" class="form-control"/>
                                                    <label class="form-label" for="register_lastname">@lang('miscellaneous.lastname')</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <!-- Surname -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_surname" name="register_surname" class="form-control"/>
                                                    <label class="form-label" for="register_surname">@lang('miscellaneous.surname')</label>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <!-- Birth date -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_birthdate" name="register_birthdate" class="form-control"/>
                                                    <label class="form-label" for="register_birthdate">@lang('miscellaneous.birth_date.label')</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gender -->
                                        <div class="form-outline mb-4 text-center">
                                            <p class="mb-1 form-label">{{ __('miscellaneous.gender_title') }}</p>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" id="gender1" name="register_gender" value="M" />
                                                <label class="form-check-label" for="gender1">{{ __('miscellaneous.gender1') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" class="form-check-input" id="gender2" name="register_gender" value="F" />
                                                <label class="form-check-label" for="gender2">{{ __('miscellaneous.gender2') }}</label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <!-- Phone number -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_phone" name="register_phone" class="form-control" aria-describedby="phone_error_message" {{ !empty($request->register_phone) ? 'value="' . $request->register_phone . '"' : '' }} {{ $errors->has('register_phone') ? 'autofocus' : '' }}/>
                                                    <label class="form-label" for="register_phone">@lang('miscellaneous.phone')</label>
                                                </div>
            @if ($errors->has('register_phone'))
                                                <p id="phone_error_message" class="mb-4 text-danger small">{{ __('validation.custom.phone.incorrect') }}</p>
            @endif
                                            </div>

                                            <div class="col-sm-6">
                                                <!-- Email -->
                                                <div class="form-outline mb-4">
                                                    <input type="text" id="register_email" name="register_email" class="form-control" aria-describedby="email_error_message" {{ !empty($request->register_email) ? 'value="' . $request->register_email . '"' : '' }} {{ $errors->has('register_email') ? 'autofocus' : '' }}/>
                                                    <label class="form-label" for="register_email">@lang('miscellaneous.email')</label>
                                                </div>
            @if ($errors->has('register_email'))
                                                <p id="email_error_message" class="mb-4 text-danger small">{{ __('validation.custom.email.incorrect') }}</p>
            @endif
                                            </div>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-outline mb-4">
                                            <input type="password" id="register_password" name="register_password" class="form-control" aria-describedby="password_error_message" {{ $errors->has('register_password') ? 'autofocus' : '' }} />
                                            <label class="form-label" for="register_password">@lang('miscellaneous.password.label')</label>
                                        </div>
            @if ($errors->has('register_password'))
                                        <p id="password_error_message" class="mb-4 text-danger small">{{ __('validation.password.error') }}</p>
            @endif

                                        <!-- Password confirmation -->
                                        <div class="form-outline mb-5">
                                            <input type="password" id="register_password_confirmation" name="register_password_confirmation" class="form-control" aria-describedby="password_confirmation_error_message" {{ $errors->has('confirmed') ? 'autofocus' : '' }} />
                                            <label class="form-label" for="register_password_confirmation">@lang('miscellaneous.confirm_password.label')</label>
                                        </div>
            @if ($errors->has('confirmed'))
                                        <p id="password_confirmation_error_message" class="mb-4 text-danger small">{{ __('validation.confirm_password.error') }}</p>
            @endif

                                        <!-- Submit button -->
                                        <button type="button" class="btn btn-primary btn-block mb-4">@lang('miscellaneous.connection')</button>
                                    </form>
        @endif
    @endif

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
