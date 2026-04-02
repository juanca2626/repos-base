@extends('layouts.app')
@section('content')
    <section class="protocols">
        <div class="hero__primary">
            <div class="container">
                <h1 class="text-center">{{ trans('travel_recommendations.label.travel_recommendations') }}</h1>
                <div class="pt-0" >
                    <p>{{ trans('travel_recommendations.label.useful_information_peru') }}</p>
                </div>
            </div>
        </div>
    </section>
    {{--Recomendaciones--}}
    <div class="" style="background:#FFFFFF;">
        <section class="container policies py-5 border-bottom">
            <h2 class="title-section my-5 "><strong>{{ trans('travel_recommendations.label.recommendations_for_your_trip') }}</strong></h2>
            <div class="d-flex py-5">
                <div class="col pl-0 pr-4">
                    <h3 class="font-weight-bold">{{ trans('travel_recommendations.label.it_is_recommended') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.it_is_recommended_text') }}.
                    </p>
                </div>
                <div class="col pr-0 pl-4">
                    <h3 class="font-weight-bold">{{ trans('travel_recommendations.label.not_recommended') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.not_recommended_text') }}.
                    </p>
                </div>
            </div>
            <h4 class="my-5 font-weight-bold">{{ trans('travel_recommendations.label.recommendations_luggage_transfer') }}</h4>
            <div class="d-flex py-5">
                <div class="pr-4">
                    <h3 class="condition_title mt-0 font-weight-bold">{{ trans('travel_recommendations.label.train_to_machu_picchu') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.train_to_machu_picchu_text') }}.
                    </p>
                    <table>
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('travel_recommendations.label.quantity') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.weight') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.size') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Cantidad">1 {{ trans('travel_recommendations.label.bag_or_backpack') }}</td>
                            <td data-label="Peso">5kg / 11lb</td>
                            <td data-label="Tamaño">62’’ / 157 cm</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pl-4">
                    <h3 class="condition_title mt-0 font-weight-bold">{{ trans('travel_recommendations.label.by_bus_within_the_country') }}</h3>
                    <p class="">
                        {{ trans('travel_recommendations.label.by_bus_within_the_country_text') }}.
                    </p>
                    <table>
                        <thead>
                        <tr>
                            <th scope="col">{{ trans('travel_recommendations.label.quantity') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.weight') }}</th>
                            <th scope="col">{{ trans('travel_recommendations.label.size') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Cantidad">{{ trans('travel_recommendations.label.hold_baggage') }}</td>
                            <td data-label="Peso">20 kg</td>
                            <td data-label="Tamaño">-</td>
                        </tr>
                        <tr>
                            <td data-label="Cantidad">{{ trans('travel_recommendations.label.hand_baggage') }}</td>
                            <td data-label="Peso">5 kg</td>
                            <td data-label="Tamaño">-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="packages__details py-5 container border-bottom">
            <div class="container px-0">
                <div class="d-flex justify-content-between align-items-center pt-0">
                    <div class="aurora__description pr-5">
                        <h2 class="title-section mb-5">{{ trans('travel_recommendations.label.entrance_to_peru') }}</h2>
                        <p>{{ trans('travel_recommendations.label.entrance_to_peru_text') }}. </p>
                    </div>
                    <div>
                        <img class="" src="../../images/aeropuerto.png" alt="aeropuerto" width="500"/>
                    </div>

                </div>
            </div>
        </section>
        <section class="packages__details py-5 container">
            <div class="container">
                <div class="aurora__video d-flex justify-content-between align-items-center pt-0">
                    <video controls crossorigin playsinline
                           poster="https://res.cloudinary.com/litomarketing/image/upload/v1619539204/Thumbnails/Gu%C3%ADa%20esencial%20de%20viaje%20a%20Per%C3%BA/Gui%CC%81a_de_viaje_ESP.jpg"
                           v-if="lang === 'es'" id="es">
                        <!-- Video files -->
                        <source
                                src="https://res.cloudinary.com/litomarketing/video/upload/v1527182682/vimeo/Your%20Ultimate%20Guide%20to%20Peru/Gui%CC%81a_esencial_de_Viaje_al_Peru%CC%81-_720.mp4"
                                type="video/mp4" size="720">
                    </video>
                    <video controls crossorigin playsinline
                           poster="https://res.cloudinary.com/litomarketing/image/upload/v1619539204/Thumbnails/Gu%C3%ADa%20esencial%20de%20viaje%20a%20Per%C3%BA/Gui%CC%81a_de_viaje_ENG.jpg"
                           v-else id="en">
                        <!-- Video files -->
                        <source
                                src="https://res.cloudinary.com/litomarketing/video/upload/v1527182674/vimeo/Your%20Ultimate%20Guide%20to%20Peru/Your_Ultimate_Guide_to_Peru_-_720.mp4"
                                type="video/mp4" size="720">
                    </video>
                    <div class="aurora__description mr-3">
                        <h2 class="title-section mb-5">{{ trans('travel_recommendations.label.essential_peru_travel_guide') }}</h2>
                        <p>{{ trans('travel_recommendations.label.essential_peru_travel_guide_text') }}.</p>
                        <a class="font-weight-bold" href="/biosafety-protocols"><span
                                    class="icon-ac-alert-circle mr-1"></span>{{ trans('travel_recommendations.label.review_our_biosafety_protocols') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <section-write-us-component></section-write-us-component>
@endsection
@section('js')
    <script>
			new Vue({
				el: '#app',
				data: {
					lang: 'en',
				},
				created: function () {
					this.client_id = localStorage.getItem('client_id')
				},
				computed: {},
				methods: {
					goBiosafetyProtocols () {
						window.location.href = '/biosafety-protocols'
                    },
				},
				mounted: function () {
                    this.lang = localStorage.getItem('lang')
				},

			})
    </script>
@endsection
