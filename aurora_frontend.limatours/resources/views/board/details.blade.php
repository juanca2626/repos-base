@extends('layouts.app')
@section('content')
<section class="page-board">
    <div class="container">
        <h4 class="m-0">Mis pendientes</h4>
        <h2> Detalles del itinerario
            <span class="text-muted ml-3"> File @{{ file }} <i class="icon-star"></i></span>
            <span class="text-muted ml-3"> Pendientes <i class="icon-filter-fire"></i></span>
            <span class="text-muted ml-3"> Positano x2 / Voyage Privé </span>
        </h2>
        <!------- Cabecera ------->
        <div class="row filtros">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-8">
                        <ol class="box filter">
                            <li class="box-item">Cambiar vista:</li>
                            <li class="box-item">
                                <a href="javascript:void(0);" v-on:click="view=0;changeView(view);">
                                    <i class="icon-trello" v-bind:class="[(view==0)?'font-weight-bold':'']"></i>
                                </a>
                            </li>
                            <li class="box-item">
                                <a href="javascript:void(0);" v-on:click="view=1;changeView(view);">
                                    <i class="icon-calendar" v-bind:class="[(view==1)?'font-weight-bold':'']"></i>
                                </a>
                            </li>
                        </ol>
                    </div>
                    <!--
                        <div class="col-5">
                            <ol class="box">
                                <li class="box-item"><a href="#" class="li-icon"><i class="icon-plus-circle mr-2"></i>Extensión</a></li>
                                <li class="box-item"><a href="#" class="li-icon"><i class="icon-plus-circle mr-2"></i>Servicio</a></li>
                                <li class="box-item"><a href="#" class="li-icon"><i class="icon-plus-circle mr-2"></i>Hotel</a></li>
                            </ol>
                        </div>
                        -->
                    <div class="col-4">
                        <!--<button class="btn btn-primary">Guardar cambios</button>-->
                        <a href="/board?return=1" class="btn btn-primary">Atrás</a>
                    </div>
                </div>
            </div>
        </div>
        <!------- End cabecera ------->
        <!------- Timeline ------->
        <div class="container" v-if="view==0">
            <div class="row">
                <div class="col-auto">
                    <div class="timeline">
                        <ul class="steps">
                            <template v-for="(v, k) in itinerX">
                                <li v-if="differentCity(k)" class='milestone' v-bind:data-date='getInfo(k, "month")' v-bind:data-description='getInfo(k, "city")'></li>
                                <li class='event' v-bind:data-date='getInfo(k, "dayWithNumber")' v-bind:data-description='getInfo(k, "dayWithNumberComplete")'>
                                    <div class="col" v-for="(v1, k1) in v.events">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-1 text-center icon-card py-3">
                                                    <i class="fas fa-2x" v-bind:class='v1.icon'></i>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="card-body text-left py-4">
                                                        <h4 class="m-0 card-title">@{{ v1.descri }} <a href="#"><i class="icon-edit"></i></a></h4>
                                                        <p class="card-text"><!--<small class="text-muted">arribo</small>--></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-1 px-2 py-3">
                                                    <div class="text-time small">@{{ v1.start.split(' ')[1] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>


                            <!--
                                <li class='milestone' data-date='AGOSTO' data-description='Cusco'></li>
                                <li class='event' data-date="Martes 13" data-description="Martes 13 de agosto">
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-hotel fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <div class="float-right text-time small">8:30 AM</div>
                                                        <h4 class="m-0">Hotel <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class='event' data-date="Miercoles 14" data-description="Miercoles 14 de agosto">
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-hotel fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <div class="float-right text-time small">8:30 AM</div>
                                                        <h4 class="m-0">Hotel <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class='event' data-date="Jueves 15" data-description="Juevees 15 de agosto">
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-plane-arrival fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <h4 class="m-0">Aeropuerto Internacional Alejandro Velas <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col py-2">
                                        <div class="card mb-3">
                                            <div class="row no-gutters">
                                                <div class="col-md-3 text-center p-4 ">
                                                    <i class="fas fa-hotel fa-2x"></i>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="card-body text-left">
                                                        <div class="float-right text-time small">8:30 AM</div>
                                                        <h4 class="m-0">Hotel <a href=""><i class="far fa-edit"></i></a> </h4>
                                                        <p class="card-text"><small class="text-muted">arribo</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!------- End Timeline ------->

        <!-- Se espera un if else para el cambio de vista -->
        <!-- @{{ eventsX }} -->
        <!------- calender ------->
        <div class="container calender" v-if="view==1">
            <vue-cal :selected-date="dayNow"
                :time-from="5 * 60"
                :time-to="24 * 60"
                :disable-views="['years', 'year', 'month']"
                :locale="locale" resize-x
                :events="eventsX">
            </vue-cal>
        </div>

        <!-- hide-weekends selected-date="2019-09-01" -->
        <!------- End calender ------->
    </div>
</section>
@endsection
@section('css')
<style>
    .steps {
        display: flex;
        flex-wrap: wrap;
        list-style-type: none;
        margin-left: 70px;
    }

    /* the steps (li) */
    .steps>li {
        padding: 35px 0 0 15px;
        flex: 1 1 100%;
        position: relative;
        border-left: 2px solid;
        font-style: normal;
        font-weight: bold;
    }

    .steps>li,
    .steps>li::after {
        border-color: #BBBDBF;
    }

    .steps>li::after {
        background-color: #fff;
    }

    .steps>li::before {
        content: attr(data-date);
        position: absolute;
        left: -110px;
        width: 100px;
        font-style: italic;
        color: #A71B20;
        font-size: 12px;
    }

    .steps>li.event::before {
        top: 40px;
    }

    .steps>li.milestone::before {
        top: 15px;
    }

    .steps>li.milestone::after {
        content: attr(data-description);
        position: absolute;
        top: 15px;
        left: 28px;
        font-size: 1.4rem;
        background-color: #4c5054;
        color: #fff;
        padding: 1.2rem 3rem;
        border-radius: 3px 18px 18px 3px;
    }

    /* the bubble with the number in it */
    .steps>li.event::after {
        position: absolute;
        top: 40px;
        left: -12px;
        border-radius: 20px;
        width: 20px;
        height: 20px;
        display: block;
        text-align: center;
        font-weight: 400;
        line-height: 26px;
        content: '';
        border-color: #aaa;
        background-color: #A71B20;
        z-index: 2;
    }

    .steps .event .content {
        position: relative;
    }

    .steps .event .content::before {
        content: '';
        width: 0;
        height: 0;
        border-top: 1em solid #d9edf7;
        background-color: #d9edf7;
        border-left: 1em solid transparent;
        left: -8px;
        transform: rotate(-135deg);
        position: absolute;
    }

    .icon-card {
        -webkit-box-align: center !important;
        align-items: center !important
    }

    .card-title {
        font-size: 15px !important;
    }
</style>
@endsection
@section('js')
<script>
    new Vue({
        el: '#app',
        data: {
            locale: 'es',
            update_menu: 1,
            itinerX: [],
            allKeysItinerX: [],
            itinerXCityPrev: '',
            eventsX: [],
            dayNow: '',
            file: '',
            eventsY: [{
                "start": "2019-09-01 15:11",
                "end": "2019-09-02 ",
                "title": "",
                "content": "2019-09-02",
                "class": "user"
            }, {
                "start": "2019-09-02 07:00",
                "end": "2019-09-02 ",
                "title": "",
                "content": "2019-09-02",
                "class": "user"
            }, {
                "start": "2019-09-02 11:12",
                "end": "2019-09-02 16.00",
                "title": "",
                "content": "2019-09-02",
                "class": "user"
            }, {
                "start": "2019-09-03 09:00",
                "end": "2019-09-03 ",
                "title": "",
                "content": "2019-09-03",
                "class": "user"
            }, {
                "start": "2019-09-03 12:00",
                "end": "2019-09-03 ",
                "title": "",
                "content": "2019-09-03",
                "class": "user"
            }, {
                "start": "2019-09-04 09:00",
                "end": "2019-09-04 ",
                "title": "",
                "content": "2019-09-04",
                "class": "user"
            }, {
                "start": "2019-09-04 09:01",
                "end": "2019-09-04 ",
                "title": "",
                "content": "2019-09-04",
                "class": "user"
            }, {
                "start": "2019-09-04 12:12",
                "end": "2019-09-05 ",
                "title": "",
                "content": "2019-09-05",
                "class": "user"
            }, {
                "start": "2019-09-05 10:00",
                "end": "2019-09-05 11:00",
                "title": "",
                "content": "2019-09-05",
                "class": "user"
            }, {
                "start": "2019-09-05 12:30",
                "end": "2019-09-05 ",
                "title": "",
                "content": "2019-09-05",
                "class": "user"
            }, {
                "start": "2019-09-05 17:30",
                "end": "2019-09-05 ",
                "title": "",
                "content": "2019-09-05",
                "class": "user"
            }, {
                "start": "2019-09-06 09:00",
                "end": "2019-09-06 ",
                "title": "",
                "content": "2019-09-06",
                "class": "user"
            }, {
                "start": "2019-09-06 15:00",
                "end": "2019-09-06 ",
                "title": "",
                "content": "2019-09-06",
                "class": "user"
            }],
            events: [{
                    start: '2019-08-13 06:30',
                    end: '2019-08-13 07:40',
                    title: '',
                    content: 'Inicio 7:45',
                    class: 'initation'
                },
                {
                    start: '2019-08-13 07:45',
                    end: '2019-08-15 08:10',
                    title: 'Aeropuerto Internacional',
                    content: 'Arribo a Cusco - 7:45',
                    class: 'user'
                },
                {
                    start: '2019-08-13 09:20',
                    end: '2019-08-13 10:00',
                    title: '<i class="fas fa-car-side"></i> Privado',
                    content: '20 min',
                    class: 'transfer'
                },
                {
                    start: '2019-08-13 10:30',
                    end: '2019-08-13 11:30',
                    title: 'Hotel Los Laureles',
                    content: 'Check-in 9:00',
                    class: 'hotel'
                },
                {
                    start: '2019-08-13 12:10',
                    end: '2019-08-13 15:30',
                    title: '<i class="fas fa-hiking"></i> Nombre de actividad',
                    content: 'Actividad 12:00am',
                    class: 'activity'
                },
                {
                    start: '2019-08-13 16:00',
                    end: '2019-08-13 15:30',
                    title: 'Se requiere traslado',
                    content: '',
                    class: 'required'
                },
            ],
            months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            days: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            loading: true,
            view: 0,
        },
        created: function() {
            //this.getInit({{$file}});
        },
        mounted() {
            this.showLoader('Cargando...')
            this.getInit("{{ $file }}");
        },
        computed: {},
        methods: {
            getInit: function(file) {
                this.loading = true;
                axios.get(`${baseURL}board/detailsWS/${file}`)
                    .then((result) => {
                        const data = result.data;

                        if (data.success) {
                            this.file = file;

                            this.itinerX = data.data.itiner;
                            this.allKeysItinerX = Object.keys(this.itinerX);

                            this.eventsX = data.data.events;
                            this.dayNow = this.eventsX[0].start;
                        }

                        this.hideLoader();
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader();
                    })
            },
            changeView: function(view) {
                console.log(view)
            },
            showLoader(texto) {
                this.loading = true;
                var $backdrop = $(".backdrop-banners"),
                    timeLoading = 250

                $backdrop.css({
                    display: 'block'
                }).animate({
                    opacity: 1
                }, timeLoading, function() {
                    $backdrop.prepend('<div id="spinner-loader"><div class="spinner"><span class="logo"></span></div>' +
                        '<div class="spinner-text">' + texto + '<small>Por favor espere.</small></div></div>')
                })
            },
            getInfo(key, opt) {
                var data = key.split('|')
                var date = new Date(data[1].replace(/-+/g, '/'))
                if (opt == 'month') {
                    return this.months[date.getMonth()].toUpperCase()
                } else if (opt == 'dayWithNumber') {
                    var onlyDay = data[1].split('-')
                    return this.days[date.getDay()] + ' ' + onlyDay[2]
                } else if (opt == 'dayWithNumberComplete') {
                    var onlyDay = data[1].split('-')
                    return this.days[date.getDay()] + ' ' + onlyDay[2] + ' de ' + this.months[date.getMonth()]
                } else {
                    return data[0]
                }
            },
            differentCity(key) {
                var data = key.split('|')
                var iCurrent = this.allKeysItinerX.indexOf(key)
                var result = false;
                if (iCurrent > 0) {
                    const data = key.split('|')
                    const dataCurrent = this.allKeysItinerX[iCurrent].split('|')
                    if (this.itinerXCityPrev !== dataCurrent[0]) {
                        //this.itinerXCityPrev = dataCurrent[0]
                        result = true
                    } else {
                        result = false
                    }
                } else {
                    this.itinerXCityPrev = data[0]
                    result = true
                }
                return result;
            },
            hideLoader() {
                this.loading = false
                var $backdrop = $(".backdrop-banners"),
                    timeLoading = 250
                $backdrop.css({
                    display: 'none'
                }).animate({
                    opacity: 0
                }, timeLoading, function() {
                    $backdrop.html('');
                });
            }
        }
    })
</script>
@endsection