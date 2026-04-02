@extends('layouts.app')
@section('content')
    <section class="policies">
        <div class="hero__primary">
            <div class="container">
                <h1 class="text-center">{{trans('cancellation_policies.label.cancellation_policies')}}</h1>
                <div class="mt-5" style="font-weight: 600;">
                    <a href="#terms_and_conditions">{{trans('cancellation_policies.label.terms_and_conditions')}}</a>
                    <span>|</span>
                    <a href="#cancelation_conditions">{{trans('cancellation_policies.label.cancelation_conditions')}}</a>
                    <span>|</span>
                    <a href="#disclaimer">{{trans('biosafety_protocols.label.disclaimer')}}</a>
                </div>
            </div>
        </div>
    </section>
    <section class="policies" id="terms_and_conditions">
        <main>
            <div class="container content-title mt-5">
                <h2 class="title">{{trans('cancellation_policies.label.terms_and_conditions')}}</h2>
                <!-- <a class="btn-primary" href="{{route('home')}}">{{trans('biosafety_protocols.label.return')}}</a> -->
            </div>
            <section class="contents">
                <!-- COLUMNA 1 -->
                <article>
                    <!-- Sub col -->
                    <section class="article">
                        <div>
                            <h3 class="article_title">1. {{trans('cancellation_policies.label.rates')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.rates_text')}}.
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">2. {{trans('cancellation_policies.label.prices')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.prices_text')}}.
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">3. {{trans('cancellation_policies.label.rates_do_not_apply')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.rates_do_not_apply_text')}}.
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">4. {{trans('cancellation_policies.label.reservations')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.reservations_text')}}
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">5. {{trans('cancellation_policies.label.hotels')}}</h3>
                            <p class="article_paragraph mb-0">
                                {{trans('cancellation_policies.label.hotels_text')}}.
                            </p>
                        </div>
                    </section>
                </article>
                <!-- COLUMNA 2 -->
                <article>
                    <!-- Sub col -->
                    <section class="article">
                        <div class="mt-article">
                            <h3 class="article_title">6. {{trans('cancellation_policies.label.arrivals')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.arrivals_text')}}.
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">7. {{trans('cancellation_policies.label.accommodation_fees')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.accommodation_fees_text')}}.
                            </p>
                        </div>
                        <div>
                            <h3 class="article_title">8. {{trans('cancellation_policies.label.trains_to_machu_picchu')}}</h3>
                            <p class="article_paragraph">
                                {{trans('cancellation_policies.label.trains_to_machu_picchu_text')}}.
                            </p>
                        </div>
                    </section>
                </article>
            </section>
            <div class="container text-center my-5 link-up">
                <a href="" @click="scrollTop" v-show="visible"><span class="icon-ac-arrow-up font-weight-bold" ></span>
                    {{trans('cancellation_policies.label.go_up')}}
                </a>
            </div>
        </main>
    </section>
    <section class="policies" id="cancelation_conditions">
        <main>
            <hr>
            <div class="container mt-5 text-center">
                <h2 class="title">{{trans('cancellation_policies.label.cancelation_conditions')}}</h2>
                <p class="text-policies">
                    {{trans('cancellation_policies.label.for_services_cancellation')}}:
                </p>
            </div>
            <div class="container">
                <div class="pd-table">
                    <table>
                        <thead>
                        <tr>
                            <th scope="col">{{trans('cancellation_policies.label.number_of_passengers')}}</th>
                            <th scope="col">{{trans('cancellation_policies.label.notification_date')}}</th>
                            <th scope="col">{{trans('cancellation_policies.label.cancellation_expense')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Cantidad de pasajeros">{{trans('cancellation_policies.label.passengers_range_1')}}</td>
                            <td data-label="Fecha de aviso">{{trans('cancellation_policies.label.days_range_1')}}</td>
                            <td data-label="Gasto de cancelación">100%</td>
                        </tr>
                        <tr>
                            <td data-label="Cantidad de pasajeros">{{trans('cancellation_policies.label.passengers_range_2')}}</td>
                            <td data-label="Fecha de aviso">{{trans('cancellation_policies.label.days_range_2')}}</td>
                            <td data-label="Gasto de cancelación">100%</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="condition">
                     <span class="condition_mini">* {{trans('cancellation_policies.label.policy_applicable')}}.</span>
                </div>
            </div>
            <div class="container text-center my-5 link-up">
                <a href="" @click="scrollTop"><span class="icon-ac-arrow-up font-weight-bold" ></span> {{trans('cancellation_policies.label.go_up')}}</a>
            </div>
        </main>
    </section>
    @include('layouts.partials.disclaimer')
    <section-write-us-component></section-write-us-component>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                lang: 'en',
                visible: true,
            },
            created: function () {
                this.client_id = localStorage.getItem('client_id')
            },
            computed: {},
            methods: {
	            goBiosafetyProtocols () {
		            window.location.href = '/biosafety-protocols'
	            },
                scrollTop: function () {
                    this.intervalId = setInterval(() => {
                        if (window.pageYOffset === 0) {
                            clearInterval(this.intervalId)
                        }
                        window.scroll(0, window.pageYOffset - 50)
                    }, 20)
                },
                scrollListener: function (e) {
                    this.visible = window.scrollY > 150
                }
            },
            mounted: function () {
                this.lang = localStorage.getItem('lang')
                window.addEventListener('scroll', this.scrollListener)
            },
            beforeDestroy: function () {
                window.removeEventListener('scroll', this.scrollListener)
            },
        })
    </script>
@endsection
