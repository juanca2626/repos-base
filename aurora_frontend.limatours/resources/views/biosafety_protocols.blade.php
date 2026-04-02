@extends('layouts.app')
@section('content')
    <section class="protocols">
        <div class="hero__primary">
            <div class="container">
                <h1 class="text-center">{{trans('biosafety_protocols.label.biosafety_protocols')}}</h1>
                <div class="pt-0" >
                    <p>{{trans('biosafety_protocols.label.travel_safely_with_us')}}</p>
                </div>
            </div>
        </div>
    </section>
    <section class="container protocols">
        <div class="content-title mt-5">
            <h2 class="title">{{trans('biosafety_protocols.label.our_biosafety_protocols')}}</h2>
            <a class="btn-primary" href="{{route('home')}}">{{trans('biosafety_protocols.label.return')}}</a>
        </div>
        <div class="content-cards">
            <div class="grid">
                <div class="grid__item">
                    <div class="card">
                        <img class="card__img" src="../images/protocolo-1.png" alt="protocolo1" />
                        <div class="card__content">
                            <div class="d-flex justify-content-between">
                                <h2 class="card__header">{{trans('biosafety_protocols.label.limaTours_protocols')}}</h2>
                            </div>
                            <p class="card__text">
                                {{trans('biosafety_protocols.label.limaTours_protocols_text')}}
                            </p>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'es'" id="es">
                                <a class="card__link" target="_blank" href="https://content.limatours.com.pe/es/preguntas-frecuentes-covid">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'en'" id="en">
                                <a class="card__link" target="_blank" href="https://content.limatours.com.pe/frequently-asked-questions-covid">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'pt'" id="pt">
                                <a class="card__link" target="_blank" href="https://content.limatours.com.pe/es/preguntas-frecuentes-covid">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid__item">
                    <div class="card">
                        <img class="card__img" src="../images/protocolo-2.png" alt="protocolo2" />
                        <div class="card__content">
                            <div class="d-flex justify-content-between">
                                <h2 class="card__header">{{trans('biosafety_protocols.label.protocols_machu_picchu')}}</h2>
                            </div>
                            <p class="card__text">
                                {{trans('biosafety_protocols.label.protocols_machu_picchu_text')}}.
                            </p>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'es'" id="es">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/1kaJAUSwUbRBh8DZFWwdfYfCCmefLCZM9/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'en'" id="en">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/1qUXo91aWxU3eOtKfUp6FQWURs9qgJLYr/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'pt'" id="pt">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/18osaSMqhlniVQuAUFGIE3dCZlJKSBTFP/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="grid__item">
                    <div class="card">
                        <img class="card__img" src="../images/protocolo-3.png" alt="protocolo3" />
                        <div class="card__content">
                            <div class="d-flex justify-content-between">
                                <h2 class="card__header">{{trans('biosafety_protocols.label.protocols_adventure_services')}}</h2>
                            </div>
                            <p class="card__text">
                                {{trans('biosafety_protocols.label.protocols_adventure_services_text')}}.
                            </p>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'es'" id="es">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/1TwsQAXG3Ep7q2yq4Q5NE2ntDlmdtqQID/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'en'" id="en">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/1wnfHZvpytvv9EskFFE94cJloZ3EXcBmc/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                            <div class="card__price d-flex justify-content-between" v-if="lang === 'pt'" id="pt">
                                <a class="card__link" target="_blank" href="https://drive.google.com/file/d/1_RuG1FFHlBLRcbFcpj2mys06BT4zjNqx/view?usp=sharing">{{trans('biosafety_protocols.label.see_more')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="aurora__video d-flex justify-content-between align-items-center pt-0">
                <div class="aurora__description mr-3">
                    <h2 class="mb-5">{{trans('biosafety_protocols.label.we_are_ready_to_welcome_you')}}</h2>
                    <p>{{trans('biosafety_protocols.label.we_are_ready_to_welcome_you_text')}}.</p>
                </div>
                <video controls crossorigin playsinline poster="https://res.cloudinary.com/litomarketing/image/upload/v1619538578/Thumbnails/Protocolos%20de%20bioseguridad/Protocolos.jpg" v-if="lang === 'es'" id="es">
                    <!-- Video files -->
                    <source
                        src="https://res.cloudinary.com/litomarketing/video/upload/v1615838267/vimeo/Protocolos/Protocolos_Lito_ESP.mp4"
                        type="video/mp4" size="720">
                </video>
                <video controls crossorigin playsinline poster="https://res.cloudinary.com/litomarketing/image/upload/v1619538578/Thumbnails/Protocolos%20de%20bioseguridad/Protocolos.jpg" v-else id="en">
                    <!-- Video files -->
                    <source
                        src="https://res.cloudinary.com/litomarketing/video/upload/v1615863307/vimeo/Protocolos/Limatours_Protocols_ENG.mp4"
                        type="video/mp4" size="720">
                </video>
            </div>
        </div>
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
            },
            created: function () {
                this.client_id = localStorage.getItem('client_id')
            },
            computed: {},
            methods: {

            },
            mounted: function () {
                this.lang = localStorage.getItem('lang')
            },

        })
    </script>
@endsection
