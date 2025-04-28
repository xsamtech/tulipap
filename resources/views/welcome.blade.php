@extends('layouts.customer', ['page_title' => __('miscellaneous.customer.home.title')])

@section('customer-content')

            <!-- ======= Partners ======= -->
            <section id="clients" class="clients section-bg px-sm-0 px-2">
                <div class="container">
                    <div class="row" data-aos="zoom-in">
                        {{-- <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
                            <img src="assets/img/clients/client-1.png" class="img-fluid" alt="">
                        </div> --}}
                    </div>
                </div>
            </section>
            <!-- ======= End Partners ======= -->


            <!-- ======= About Invoice Payment ======= -->
            <section id="about" class="about px-sm-0 px-2">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>@lang('miscellaneous.customer.home.invoice_how_to.title')</h2>
                    </div>

                    <div class="row g-4 content">
                        <div class="col-lg-6">
                            <p>
                                <strong class="text-uppercase"><span class="text-blue">Tuli</span><span class="text-yellow">pap</span></strong> @lang('miscellaneous.customer.home.invoice_how_to.content1')
                            </p>
                            <ul>
                                <li><i class="ri-check-double-line"></i> @lang('miscellaneous.customer.home.invoice_how_to.step1')</li>
                                <li><i class="ri-check-double-line"></i> @lang('miscellaneous.customer.home.invoice_how_to.step2')</li>
                                <li><i class="ri-check-double-line"></i> @lang('miscellaneous.customer.home.invoice_how_to.step3')</li>
                            </ul>
                        </div>

                        <div class="col-lg-6 pt-4 pt-lg-0">
                            <p class="mb-4">@lang('miscellaneous.customer.home.invoice_how_to.content2')</p>

                            <div class="row g-sm-0 mb-3">
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/AfriMoney.png') }}" alt="Afrimoney" height="30"> Afrimoney
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/AirtelMoney.png') }}" alt="Airtel money" height="30"> Airtel money
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/Fyatu.png') }}" alt="Fyatu" height="30"> Fyatu
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/MasterCard.png') }}" alt="MasterCard" height="30"> MasterCard
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/MPesa.png') }}" alt="MPesa" height="30"> M-Pesa
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/OrangeMoney.png') }}" alt="Orange money" height="30"> Orange money
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/PayPal.png') }}" alt="PayPal" height="30"> PayPal
                                </div>
                                <div class="col-lg-4 col-6 pb-2">
                                    <img src="{{ asset('assets/img/services/Visa.png') }}" alt="Visa" height="30"> Visa
                                </div>
                            </div>

                            <a href="{{ route('invoice.home') }}" class="btn-learn-more rounded-pill text-uppercase">@lang('miscellaneous.customer.home.invoice_how_to.link')</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- ======= End About Invoice Payment ======= -->

            <!-- ======= Some Provider Card ======= -->
            <section id="services" class="services section-bg">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>@lang('miscellaneous.customer.home.buying_card.title')</h2>
                        <p class="mb-2">@lang('miscellaneous.customer.home.buying_card.content')</p>
                        <h5 class="text-uppercase">@lang('miscellaneous.customer.home.buying_card.comment')</h5>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-sm-6 d-flex align-items-stretch mt-4 mt-md-0" data-aos="zoom-in" data-aos-delay="200">
                            <div class="icon-box text-center">
                                <div class="bg-image mb-3">
                                    <img src="{{ asset('assets/img/samples/providers/provider-001.png') }}" alt="" width="70" class="img-thumbnail rounded-circle">
                                    <div class="mask"></div>
                                </div>

                                <h5 class="mb-3">Société Nationale d'Electricité</h5>
                                <p class="text-center">
                                    <a href="{{ route('provider.prepaid_card.home', ['provider_id' => 1]) }}" class="btn btn-outline-blue rounded-pill shadow-0">
                                        @lang('miscellaneous.customer.home.buying_card.see_cards')
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 d-flex align-items-stretch mt-4 mt-md-0" data-aos="zoom-in" data-aos-delay="200">
                            <div class="icon-box text-center">
                                <div class="bg-image mb-3">
                                    <img src="{{ asset('assets/img/samples/providers/provider-022.png') }}" alt="" width="70" class="img-thumbnail rounded-circle">
                                    <div class="mask"></div>
                                </div>

                                <h5 class="mb-3">Trade Power S.A.</h5>
                                <p class="text-center">
                                    <a href="{{ route('provider.prepaid_card.home', ['provider_id' => 2]) }}" class="btn btn-outline-blue rounded-pill shadow-0">
                                        @lang('miscellaneous.customer.home.buying_card.see_cards')
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 d-flex align-items-stretch mt-4 mt-xl-0" data-aos="zoom-in" data-aos-delay="300">
                            <div class="icon-box text-center">
                                <div class="bg-image mb-3">
                                    <img src="{{ asset('assets/img/samples/providers/provider-009.png') }}" alt="" width="70" class="img-thumbnail rounded-circle">
                                    <div class="mask"></div>
                                </div>

                                <h5 class="mb-3">Energie du Kasaï Central</h5>
                                <p class="text-center">
                                    <a href="{{ route('provider.prepaid_card.home', ['provider_id' => 3]) }}" class="btn btn-outline-blue rounded-pill shadow-0">
                                        @lang('miscellaneous.customer.home.buying_card.see_cards')
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 d-flex align-items-stretch mt-4 mt-xl-0" data-aos="zoom-in" data-aos-delay="400">
                            <div class="card mb-4 h-100">
                                <div class="card-body d-flex justify-content-center align-items-center bg-blue rounded-0 text-center">
                                    <p class="lead m-0 text-white">
                                        <i class="bi bi-arrow-right-circle display-3 mb-3"></i><br>
                                        @lang('miscellaneous.customer.home.buying_card.see_all_providers')
                                    </p>
                                </div>
                                <a href="{{ route('provider.home') }}" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- ======= End Some Provider Card ======= -->

            <!-- ======= Partnership Section ======= -->
            <section id="cta" class="cta" style="background: linear-gradient(rgba(40, 58, 90, 0.8), rgba(40, 58, 90, 0.8)), url('{{ asset('assets/img/ads/ad-004.jpg') }}') fixed top center; background-size: cover;">
                <div class="container" data-aos="zoom-in">
                    <div class="row">
                        <div class="col-lg-6 text-center text-lg-start">
                            <h2 class="h2">@lang('miscellaneous.customer.home.partnership.title')</h2>
                            <p class="lead">@lang('miscellaneous.customer.home.partnership.content')</p>
                        </div>

                        <div class="col-lg-6 cta-btn-container text-center d-sm-flex justify-content-center d-inline-block">
                            <a class="cta-btn align-middle" href="https://xsamtech.com/partnership">@lang('miscellaneous.customer.home.partnership.link1')</a>
                            <a class="cta-btn align-middle" href="https://xsamtech.com/products/tulipap/sponsorship">@lang('miscellaneous.customer.home.partnership.link2')</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- ======= End Partnership Section ======= -->

            <!-- ======= Providers Section ======= -->
            <section id="skills" class="skills">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2 class="h2 fw-bold">@lang('miscellaneous.customer.home.provider.title')</h2>
                    </div>

                    <div class="row">
                        <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                            <img src="{{ asset('assets/img/ads/ad-005.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="col-lg-6 pt-4 pt-lg-0 content" data-aos="fade-left" data-aos-delay="100">
                            <p>
                                <strong class="text-uppercase"><span class="text-blue">Tuli</span><span class="text-yellow">pap</span></strong> 
                                @lang('miscellaneous.customer.home.provider.content')
                            </p>
                            <ul>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task1')</li>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task2')</li>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task3')</li>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task4')</li>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task5')</li>
                                <li><i class="ri-check-double-line align-middle"></i> @lang('miscellaneous.customer.home.provider.task6')</li>
                            </ul>
                            <a href="{{ route('company.home') }}" class="btn btn-lg btn-outline-blue rounded-pill text-uppercase">@lang('miscellaneous.customer.home.provider.link')</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- ======= End Providers Section ======= -->
      
@endsection
