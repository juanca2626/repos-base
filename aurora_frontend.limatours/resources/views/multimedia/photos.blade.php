@extends('layouts.app')
@section('content')
    <section class="page-multimedia">
        <loading-component v-show="blockPage"></loading-component>
        <div class="container-fluid">
            <div class="image-background">
                <div class="col-xs-12 section-small null-padding-bottom null-padding-side bg-gray-primary">
                    <div class="container text-center null-padding-side" style="max-width:1100px">
                        <div class="content-box">
                            <div class="col-xs-12 null-padding">
                                <div class="col-xs-12 buscador-home-box null-padding-side">
                                    <div class="container null-padding" style="max-width:400px">
                                        <div class="content-box-form text-center null-padding">
                                            <form role="form" class="bg-white null-padding-side"
                                                  id="buscador_form">
                                                <div class="row">
                                                    <div class="col-10 form-group destinos-fotos dropdown mb-0">
                                                        <button class="form-control" id="dropdownExpenience"
                                                                data-toggle="dropdown"
                                                                aria-haspopup="true"
                                                                aria-expanded="true">
                                                                <span v-if="check_all_destinations">
                                                                      {{trans('multimedia.label.all_destinations')}}
                                                                 </span>
                                                            <span v-else>
                                                                    @{{ label_select_destinations | truncate(30,'...') }}
                                                                </span>
                                                        </button>
                                                        <div aria-labelledby="dropdownExpenience"
                                                             class="dropdown dropdown-menu"
                                                             style="width: 283px; font-size: 14px; margin-top: 18px;">
                                                            <div style="overflow-y: scroll;">
                                                                <div
                                                                    class="container_quantity_persons_rooms_selects quantity-persons-rooms "
                                                                    style="height: 170px;">
                                                                    <div class="row col-md-12 pr-0">
                                                                        <div class="col-md-12 mb-3">
                                                                            <div class="form-check">
                                                                                <input style="margin-top: 14px;"
                                                                                       class="form-check-input"
                                                                                       type="checkbox"
                                                                                       id="check_all_destinations"
                                                                                       v-model="check_all_destinations"
                                                                                       @change="checkAllDestinations">
                                                                                <label class="form-check-label"
                                                                                       for="check_all_destinations"
                                                                                       style="padding-left: 7px;">
                                                                                    <span>{{trans('multimedia.label.all_destinations')}}</span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 mb-3">
                                                                            <div class="form-check"
                                                                                 v-for="destination in destinations">
                                                                                <input style="margin-top: 14px;"
                                                                                       class="form-check-input"
                                                                                       v-model="destination.status"
                                                                                       :id="'checkbox_'+destination.name"
                                                                                       type="checkbox"
                                                                                       @change="checkDestinations">
                                                                                <label class="form-check-label"
                                                                                       :for="'checkbox_'+destination.name"
                                                                                       style="padding-left: 7px;">
                                                                                    @{{ destination.name }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-2 null-padding">
                                                        <button type="button"
                                                                class="button button-primary button-medium col-xs-12"
                                                                @click="search"
                                                                style="float: right;margin-right: 15px;">
                                                            {{trans('global.label.search')}}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" style="margin-top: 70px;max-width: 80%;">
            <div class="row mb-5">
                <div class="col-md-3"
                     v-if="interests.length > 0 || composition.length > 0 || mediaType.length > 0 || typeService.length > 0">

                </div>
                <div :class="{'col-md-9': interests.length > 0 || composition.length > 0 || mediaType.length > 0 || typeService.length > 0,
                'col-md-12': interests.length == 0 && composition.length == 0 && mediaType.length == 0 && typeService.length == 0}">
                    <b>@{{ total_images }}</b> {{trans('multimedia.label.images_found')}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3"
                     v-if="interests.length > 0 || composition.length > 0 || mediaType.length > 0 || typeService.length > 0">
                    <div class="col-12">
                        <div class="form-group">
                            <b-input-group class="mt-3">
                                <b-form-input v-model="filter" placeholder="{{trans('global.label.search')}}"></b-form-input>
                                <b-input-group-append>
                                    <b-button class="btn btn-primary" variant="info" @click="search">
                                        <i class="fas fa-search"></i>
                                    </b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </div>
                    </div>
                    <div class="col-12" v-show="interests.length > 0">
                        <div class="form-group intereses">
                            <v-select :options="interests"
                                      v-model="interest_select"
                                      @input="changeFilter"
                                      placeholder="{{trans('multimedia.label.interests')}}"
                                      class="form-control"></v-select>
                        </div>
                    </div>
                    <div class="col-12" v-show="composition.length > 0">
                        <div class="form-group composicion">
                            <v-select :options="composition"
                                      v-model="composition_select"
                                      @input="changeFilter"
                                      placeholder="{{trans('multimedia.label.composition')}}"
                                      class="form-control"></v-select>
                        </div>
                    </div>
                    <div class="col-12" v-show="mediaType.length > 0">
                        <div class="form-group tipo-medio">
                            <v-select :options="mediaType"
                                      v-model="mediaType_select"
                                      @input="changeFilter"
                                      placeholder="{{trans('multimedia.label.media_type')}}"
                                      class="form-control"></v-select>
                        </div>
                    </div>
                    <div class="col-12" v-show="typeService.length > 0">
                        <div class="form-group tipo-servicio">
                            <v-select :options="typeService"
                                      v-model="typeService_select"
                                      @input="changeFilter"
                                      placeholder="{{trans('multimedia.label.type_of_service')}}"
                                      class="form-control"></v-select>
                        </div>
                    </div>
                </div>
                <div :class="{'col-md-9': interests.length > 0 || composition.length > 0 || mediaType.length > 0 || typeService.length > 0,
                'col-md-12': interests.length == 0 && composition.length == 0 && mediaType.length == 0 && typeService.length == 0}">
                    <div class="row">
                        <div class="col-3 mb-3" v-for="(image,index) in images">
                            <div class="card image-multimedia">
                                <img @click="openModal(image,index)"
                                     :src="image.url"
                                     class="card-img-top"
                                     style="background-color: #a71b20;min-height: 225px; cursor: pointer;">
                                <div class="card-body">
                                    <h5 class="card-title">(# @{{ index + 1 }}) - @{{ image.filename }}</h5>
                                    <b-dropdown size="sm" variant="outline-danger" text="{{trans('multimedia.label.download')}}" v-if="!image.download">
                                        <b-dropdown-item @click="downloadMultimedia(0,image)">{{trans('multimedia.label.low')}}</b-dropdown-item>
                                        <b-dropdown-item @click="downloadMultimedia(1,image)">{{trans('multimedia.label.medium')}}</b-dropdown-item>
                                        <b-dropdown-item @click="downloadMultimedia(2,image)">{{trans('multimedia.label.high')}}</b-dropdown-item>
                                    </b-dropdown>
                                    <span v-else>
                                        <i class="fas fa-spinner fa-pulse"></i>
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                    <infinite-loading :identifier="infiniteId" @infinite="infiniteHandler" ref="infiniteLoading"
                                      v-if="isTriggerFirstLoad">
                            <span slot="no-more" class="p-5">
                                {{trans('service.label.no_more_data')}}
                            </span>
                    </infinite-loading>
                </div>
            </div>
        </div>

    </section>
    <div class="modal fade show modal-photo" tabindex="-1" role="dialog"
         aria-labelledby="myLargeModalLabel" aria-hidden="true" ref="vuemodal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <b-carousel
                            id="carousel-1"
                            v-model="slide"
                            :interval="0"
                            controls
                            indicators
                            background="#ababab"
                            img-width="1024"
                            img-height="480"
                            style="text-shadow: 1px 1px 2px #333;"
                            @sliding-start="onSlideStart"
                            @sliding-end="onSlideEnd"
                        >
                            <!-- Slides with custom text -->
                            <b-carousel-slide :img-src="image.secure_url" v-for="(image,index) in images">
                                <b-dropdown size="sm" dropup variant="primary" text="{{trans('multimedia.label.download')}}" v-if="!image.download" id="downloadModal">
                                    <b-dropdown-item @click="downloadMultimedia(0,image)">{{trans('multimedia.label.low')}}</b-dropdown-item>
                                    <b-dropdown-item @click="downloadMultimedia(1,image)">{{trans('multimedia.label.medium')}}</b-dropdown-item>
                                    <b-dropdown-item @click="downloadMultimedia(2,image)">{{trans('multimedia.label.high')}}</b-dropdown-item>
                                </b-dropdown>
                                <span v-else>
                                    <i class="fas fa-spinner fa-pulse"></i>
                                </span>
                            </b-carousel-slide>

                            <!-- Slides with image only -->
                        </b-carousel>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('css')
    <style>
        .dropdown-menu:before{
            display: none !important;
        }
        .backdrop-banners {
            display: none;
            opacity: 0;
        }

        /*Estilos Range Slider*/
        .vue-slider-dot-handle {
            background-color: #8e0b07;
        }

        .vue-slider-process {
            background-color: #8e0b07;
        }

        .vue-slider-dot-tooltip-inner {
            min-width: 35px;
            border-color: #db3453;
            background-color: #dc3545;
        }

        .vue-slider-dot-handle-focus {
            box-shadow: 0.5px 0.5px 2px 1px rgba(0, 0, 0, 0.32);
        }

        /*End*/
        /* Estilos de Popup Bottom de Filtro por Precio*/
        .tooltip_filter_price {
            position: relative;
            display: inline-block;
        }

        .tooltip_filter_price .tooltip_filter_price_container {
            visibility: hidden;
            width: 250px;
            height: 190px;
            background-color: white;
            color: white;
            text-align: center;
            border-radius: 3px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;

            left: 58%;
            margin-left: -60px;
            -webkit-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            -moz-box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
            box-shadow: 0px 0px 13px -2px rgba(0, 0, 0, 0.75);
        }

        .tooltip_filter_price .tooltip_filter_price_container::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 20%;
            margin-left: -5px;
            border-width: 10px;
            border-style: solid;
            border-color: transparent transparent white transparent;
        }

        .tooltip_filter_price:hover .tooltip_filter_price_container {
            visibility: visible;
        }

        /*end*/
        /*estilos de galeria*/
        .slides {
            top: 0;
            width: 100%;
            height: 100px;
            display: block;
            position: absolute;
        }


    </style>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                blockPage: false,
                check_all_destinations: true,
                label_select_destinations: '',
                images: [],
                download_img: [],
                experiencesFilter: [],
                destinations_paths: [],
                destinations_all_paths: [],
                destinations: [],
                interests: [],
                composition: [],
                mediaType: [
                    { code: 'image', label: 'Imagen' },
                    { code: 'video', label: 'Video' },
                ],
                typeService: [],
                interest_select: '',
                composition_select: '',
                mediaType_select: '',
                typeService_select: '',
                next_page: '',
                filter: '',
                total_images: 0,
                total_images_response: 0,
                infiniteId: +new Date(),
                isTriggerFirstLoad: false,
                slide: 0,
                sliding: null
            },
            created: function () {

            },
            mounted () {
                this.getDestinations()
                this.getFilters()
            },
            methods: {
                search: function () {
                    this.blockPage = true
                    this.next_page = ''
                    this.total_images = 0
                    this.total_images_response = 0
                    this.images = []
                    console.log(this.interest_select)
                    let data = {
                        lang: localStorage.getItem('lang'),
                        next_page: this.next_page,
                        destinations: (this.check_all_destinations) ? this.destinations_all_paths : this.destinations_paths,
                        interests: (this.interest_select !== null) ? this.interest_select.code : '',
                        mediaType: (this.mediaType_select !== null) ? this.mediaType_select.code : '',
                        composition: (this.composition_select !== null) ? this.composition_select.code : '',
                        typeService: (this.typeService_select !== null) ? this.typeService_select.code : '',
                        filter: this.filter,
                    }
                    axios.post(
                        'api/destinations/search',
                        data
                    ).then((result) => {
                        if (result.data.success === true && result.data.data.length > 0) {
                            this.infiniteId += 1
                            this.images = result.data.data
                            this.next_page = result.data.next_page
                            this.total_images = result.data.total
                            this.total_images_response += result.data.data.length
                            console.log(this.total_images_response)
                            this.isTriggerFirstLoad = true
                        } else {
                            this.images = []
                            this.next_page = ''
                            this.total_images = 0
                        }
                        this.blockPage = false
                    }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                getDestinations: function () {
                    this.blockPage = true
                    axios.get('api/multimedia/destinations?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.blockPage = false
                            if (result.data.success) {
                                this.destinations = result.data.data
                                this.getDestinationsAll()
                                this.search()
                            }
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                getFilters: function () {
                    this.blockPage = true
                    axios.get('api/multimedia/filters?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.blockPage = false
                            if (result.data.success) {
                                result.data.data.interests.forEach((element) => {
                                    this.interests.push({
                                        code: element.tag,
                                        label: (element.translations.length > 0) ? element.translations[0].value : ' - '
                                    })
                                })
                                result.data.data.composition.forEach((element) => {
                                    this.composition.push({
                                        code: element.tag,
                                        label: (element.translations.length > 0) ? element.translations[0].value : ' - '
                                    })
                                })
                                result.data.data.type_of_service.forEach((element) => {
                                    this.typeService.push({
                                        code: element.tag,
                                        label: (element.translations.length > 0) ? element.translations[0].value : ' - '
                                    })
                                })
                                result.data.data.media_type.forEach((element) => {
                                    this.mediaType.push({
                                        code: element.tag,
                                        label: (element.translations.length > 0) ? element.translations[0].value : ' - '
                                    })
                                })
                            }
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                checkAllDestinations: function () {
                    this.label_select_experiences = ''
                    for (let i = 0; i < this.destinations.length; i++) {
                        if (this.check_all_destinations) {
                            this.destinations[i].status = true
                        } else {
                            this.destinations[i].status = false
                        }
                    }
                },
                getDestinationsAll: function () {
                    let checkDestinations = []
                    for (let i = 0; i < this.destinations.length; i++) {
                        checkDestinations.push(this.destinations[i].path)
                    }
                    this.destinations_all_paths = checkDestinations
                },
                checkDestinations: function () {
                    let checkAll = true
                    let checkDestinations = []
                    let checkDestinationsName = []
                    for (let i = 0; i < this.destinations.length; i++) {
                        if (!this.destinations[i].status) {
                            checkAll = false
                        } else {
                            checkDestinations.push(this.destinations[i].path)
                            checkDestinationsName.push(this.destinations[i].name)
                        }
                    }
                    this.label_select_destinations = checkDestinationsName.join(', ')
                    this.destinations_paths = checkDestinations
                    this.check_all_destinations = checkAll
                },
                infiniteHandler: function ($state) {
                    let data = {
                        lang: localStorage.getItem('lang'),
                        next_page: this.next_page,
                        destinations: (this.check_all_destinations) ? this.destinations_all_paths : this.destinations_paths,
                        interests: (this.interest_select !== null) ? this.interest_select.code : '',
                        mediaType: (this.mediaType_select !== null) ? this.mediaType_select.code : '',
                        composition: (this.composition_select !== null) ? this.composition_select.code : '',
                        typeService: (this.typeService_select !== null) ? this.typeService_select.code : '',
                        filter: this.filter,
                    }
                    axios.post(
                        'api/destinations/search',
                        data
                    ).then((result) => {
                        if (result.data.success === true && result.data.data.length > 0) {
                            this.next_page = result.data.next_page
                            if (result.data.total === this.total_images_response) {
                                $state.complete()
                            } else {
                                result.data.data.forEach(item => {
                                    this.images.push(item)
                                })
                                this.total_images_response += result.data.data.length
                                $state.loaded()
                            }
                        } else {
                            this.images = []
                            $state.complete()
                            this.next_page = ''
                            this.total_images = 0
                        }
                        this.blockPage = false
                    }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                downloadMultimedia: function (type,image) {
                    image.download = true
                    let donwload = ''
                    if(type == 0){
                        donwload = image.resizes.low
                    }
                    if(type == 1){
                        donwload = image.resizes.medium
                    }
                    if(type == 2){
                        donwload = image.secure_url
                    }
                    axios.get(
                        donwload,
                        { responseType: 'blob' }
                    ).then((response) => {
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', image.filename + '.' + image.format)
                        document.body.appendChild(fileLink)
                        fileLink.click()
                        image.download = false
                    }).catch((e) => {
                        image.download = false
                        console.log(e)
                    })
                },
                changeFilter: function () {
                    this.search()
                },
                openModal: function (image, index) {
                    this.slide = index
                    $('.modal-photo').modal()
                },
                onSlideStart (slide) {
                    console.log(slide)
                    this.sliding = true
                },
                onSlideEnd (slide) {
                    this.sliding = false
                    console.log(slide)
                }
            }
        })
    </script>
@endsection
