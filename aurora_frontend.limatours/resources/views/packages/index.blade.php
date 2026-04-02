@extends('layouts.app')
@section('content')
    <section class="page-hotel">
        <loading-component v-show="blockPage"></loading-component>
        <!-- Pedido de Marketing -->
        <div class="container">
            <div class="motor-busqueda">
                <h2>{{trans('package.label.programs_extensions')}}</h2>
                <div class="container">
                    <div class="tabs-board tabs">
                        <ul class="nav nav-primary text-center">
                            <li class="nav-item">
                                <a v-bind:class="[(tab == 'packages') ? 'active' : '', 'nav-link' ]" href="javascript:;"
                                   @click="toggleView('packages')">
                                    <i class="icon-chevron-down"></i>{{ trans('package.label.my_packages') }}</a>
                            </li>
                            <li class="nav-item">
                                <a v-bind:class="[(tab == 'extensions') ? 'active' : '', 'nav-link' ]"
                                   href="javascript:;" @click="toggleView('extensions')">
                                    <i class="icon-chevron-down"></i>{{ trans('package.label.my_extensions') }}</a>
                            </li>
                            <li class="nav-item">
                                <a v-bind:class="[(tab == 'exclusive') ? 'active' : '', 'nav-link' ]"
                                   href="javascript:;" @click="toggleView('exclusive')">
                                    <i class="icon-chevron-down"></i>{{ trans('package.label.my_exclusives') }}</a>
                            </li>
                        </ul>
                        <hr>
                    </div>
                </div>
                <div class="form" v-show="tab !== 'exclusive'">
                    <div class="form-row">
                        <div class="form-group fecha">
                            <date-picker :name="'picker'"
                                         v-model="date"
                                         :config="options"></date-picker>

                        </div>
                        <div class="form-group pasajeros dropdown">
                            <button class="form-control" id="dropdownHab" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true">
                                <span><strong>@{{ adults }}</strong> {{trans('global.label.adults')}}</span>
{{--                                <span><strong>@{{ child }}</strong> {{trans('global.label.children')}}</span>--}}
                            </button>
                            <div aria-labelledby="dropdownHab" class="dropdown dropdown-menu"
                                 :class="class_container_rooms">
                                <div class="container_quantity_persons_rooms_selects quantity-persons-rooms">
                                    <div class="form-group"
                                         :class="class_container_select">
                                        <label>{{trans('global.label.adults')}}</label>
                                        <select class="form-control" v-model="adults"
                                                @change="changeQuantityAdult($event)">
                                            <option v-for="num_adults in 10" :value="num_adults">@{{ num_adults }}
                                            </option>
                                        </select>
                                    </div>
{{--                                    <div class="form-group"--}}
{{--                                         :class="class_container_select">--}}
{{--                                        <label>{{trans('global.label.children')}}</label>--}}
{{--                                        <select class="form-control" v-model="child"--}}
{{--                                                @change="changeQuantityChild($event)">--}}
{{--                                            <option v-for="num_child in 6" :value="num_child - 1">@{{ num_child - 1 }}--}}
{{--                                            </option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                    <div class="form-group"--}}
{{--                                         :class="class_container_select"--}}
{{--                                         v-for="(age_child_slot,index_age_child) in quantity_persons[0].age_childs"--}}
{{--                                         v-if="child >=1">--}}
{{--                                        <label>{{trans('global.label.age')}}</label>--}}
{{--                                        <select class="form-control"--}}
{{--                                                v-model="quantity_persons[0].age_childs[index_age_child].age">--}}
{{--                                            <option v-for="age_child in 17" :value="age_child">@{{ age_child }}</option>--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
                                </div>
                            </div>

                        </div>
                        <div class="form-group fecha">
                            <select name="service_type_id" id="" v-model="service_type_id" class="form-control">
                                <option :value="service_type.id" v-for="service_type in service_types">
                                    @{{ service_type.translations[0].value }} - @{{ service_type.code }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="class_hotel" id="" v-model="typeclass_id" class="form-control">
                                <option value="all">{{ trans('hotel.label.all_categories') }}</option>
                                <option :value="hotel.class_id" v-for="hotel in classes_hotel">
                                    @{{ hotel.class_name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary"
                                    @click="getPackages()">
                                <span v-if="tab == 'packages'">{{trans('package.label.search_packages')}}</span>
                                <span v-else>{{trans('package.label.search_extensions')}}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr v-show="tab !== 'exclusive'">
        </div>
        <!-- FIN // Pedido de Marketing -->
        <div class="container">
            <div class="row">
                {{--Inicio Filtros--}}
                <section class="col-12 control-filtros" v-if="packages.length > 0">
                    <div class="col-12 d-flex justify-content-start filtros">
                        <div class="p-2 pr-4 mr-4 d-inline-flex align-items-center cambiar-orden">
                            <label class="m-0 mr-4 text-nowrap">{{trans('global.label.sort_by')}}</label>
                            <select class="form-control" @change="sortPackages($event)">
                                <option value="1">{{trans('global.label.recommendation')}}</option>
                                <option value="2">{{trans('global.label.price_ower')}}</option>
                                <option value="3">{{trans('global.label.price_higher')}}</option>
                                <option value="4">{{trans('global.label.duration_less')}}</option>
                                <option value="5">{{trans('global.label.duration_longer')}}</option>
                            </select>
                        </div>
                        <div class="p-2 d-inline-flex align-items-center cambiar-filtros">
                            <label class="m-0 mr-4 text-nowrap"> {{trans('global.label.filters')}} </label>
                            <!--Filtro por experiencia de paquete-->
                            <div class="dropdown filtro-group">
                                <a href="#" role="button" id="dropdownMenuLink2" data-toggle="dropdown"
                                   aria-haspopup="false" title="{{trans('package.label.experiences')}}"
                                   aria-expanded="false">
                                    <i class="btn-icon fas fa-tag"></i> </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                    <div class="subtitulo">{{trans('package.label.experiences')}}</div>
                                    <div class="form-group">
                                        <label class="form-check form-check-all">
                                            <input class="form-check-input" type="checkbox" id="all_the_experiences"
                                                   @change="filterByCategoryAll()" checked>
                                            <span>{{trans('package.label.all_the_experiences')}}</span>
                                        </label>
                                        <label class="form-check"
                                               v-for="(category,index_category) in filter_by_category">
                                            <input class="form-check-input" type="checkbox" :id="index_category"
                                                   v-model="category.status" @change="filterByCategory(index_category)">
                                            <span>@{{ category.tag_name }} <small>(@{{ category.count }})</small></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!--Filtro por Zonas de paquetes-->
                            <div class="dropdown filtro-group">
                                <a href="#" role="button" id="dropdownMenuLink3"
                                   data-toggle="dropdown" aria-haspopup="false" aria-expanded="false"
                                   title="{{trans('package.label.destinations')}}">
                                    <i class="btn-icon fas fa-map-marker-alt"></i>

                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink3">
                                    <div class="subtitulo">{{trans('package.label.destinations')}}</div>
                                    <div class="form-group">
                                        <label class="form-check form-check-all">
                                            <input class="form-check-input" type="checkbox" id="all_destinations"
                                                   @change="filterByDestinyAll()" checked>
                                            <span>{{trans('package.label.all_destinations')}}</span>
                                        </label>
                                        <label class="form-check"
                                               v-for="(destiny,index_destiny) in filter_by_destiny">
                                            <input class="form-check-input" type="checkbox" :id="index_destiny"
                                                   v-model="destiny.status" @change="filterByDestiny(index_destiny)">
                                            <span>@{{ destiny.name }} <small>(@{{ destiny.count }})</small></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!--Filtro por Duracion-->
                            <div class="dropdown filtro-group"><a href="#" role="button" id="dropdownMenuLink1"
                                                                  data-toggle="dropdown" aria-haspopup="false"
                                                                  aria-expanded="true"
                                                                  title="{{trans('package.label.duration')}}"> <i
                                        class="btn-icon fas fa-moon"></i> </a>
                                <div id="dropdownMenuLink1Container" class="dropdown-menu"
                                     aria-labelledby="dropdownMenuLink1">
                                    <div class="subtitulo">{{trans('package.label.duration')}}</div>
                                    <div class="form-group">
                                        <label class="form-check form-check-all">
                                            <input class="form-check-input" type="checkbox"
                                                   id="all_itineraries" @change="filterByNightsAll()" checked>
                                            <span>{{trans('package.label.all_itineraries')}}</span>
                                        </label>
                                        <div v-for="(option,index_nights) in filter_by_nights">
                                            <label class="form-check" v-show="option.option == 1 && option.count > 0">
                                                <input class="form-check-input" type="checkbox" v-model="option.status"
                                                       @change="filterByNights(index_nights)" :id="index_nights">
                                                <span>{{trans('package.label.up_to_3_nights')}} <small>(@{{ option.count }})</small></span>
                                            </label>
                                            <label class="form-check" v-show="option.option == 2 && option.count > 0">
                                                <input class="form-check-input" type="checkbox" v-model="option.status"
                                                       @change="filterByNights(index_nights)" :id="index_nights">
                                                <span>{{trans('package.label.4_to_6_nights')}} <small>(@{{ option.count }})</small></span>
                                            </label>
                                            <label class="form-check" v-show="option.option == 3 && option.count > 0">
                                                <input class="form-check-input" type="checkbox" v-model="option.status"
                                                       @change="filterByNights(index_nights)" :id="index_nights">
                                                <span>{{trans('package.label.7_to_10_nights')}} <small>(@{{ option.count }})</small></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--Filtro por Nombre de Hotel-->
                            <div class="dropdown filtro-group"><a href="#" role="button" id="dropdownMenuLink4"
                                                                  data-toggle="dropdown" aria-haspopup="false"
                                                                  aria-expanded="false"
                                                                  title="{{trans('package.label.search_by_name')}}"> <i
                                        class="btn-icon fas fa-search"></i> </a>
                                <div class="dropdown-menu dropdown-search" aria-labelledby="dropdownMenuLink4">
                                    <div class="subtitulo">{{trans('global.label.search_by_name')}}</div>
                                    <div class="form-group">
                                        <!-- Placeholder dinámico (?) -->
                                        <div class="btn btn-icon"><i class="fas fa-upload"></i></div>
                                        <input type="text" class="form-control"
                                               placeholder="{{trans('package.label.search_by_name_example')}}"
                                               v-model="filter_by_name" @keyup.enter="filterByNamePackage">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="p-2 d-inline-flex align-items-center ml-auto cambiar-vista">
                            <label class="m-0 mr-4 text-nowrap"> {{trans('global.label.type_of_view')}} </label>
                            <div id="filterView_0" class="vista ctaction active" data-vista="normal"
                                 @click="filterView(0,this)"><i
                                    class="btn-icon fas fa-th-large"></i></div>
                            <!--<div id="filterView_1" class="vista ctaction" data-vista="grilla" @click="filterView(1,this)"><i
                                    class="btn-icon fas fa-th"></i></div>-->
                            <div id="filterView_2" class="vista ctaction" data-vista="lista"
                                 @click="filterView(2,this)"><i
                                    class="btn-icon fas fa-list"></i></div>
                        </div>
                    </div>
                    <div class="col-12 d-none justify-content-start filtros-aplicados">
                        <div class="p-2 pr-4 d-inline-flex align-items-center">
                            <label class="m-0 mr-4 text-nowrap">{{trans('global.label.applied_filters')}}</label>
                            <!-- <span class="aplicado" data-id="chck16">Cusco </span> -->
                        </div>
                    </div>
                </section>
                {{--Fin Filtros--}}
                {{--Inicio resultados paquetes--}}
                <section class="col-12 resultado-programas">
                    <div class="col-12 d-flex resultados flex-wrap" data-vista="normal" data-fx="true">
                        <!-- Paquetes 1 -->
                        <div class="container-programa" v-for="package in packages">
                            <div class="programa">
                                <div class="programa-detalle">
                                    <div class="bloque-info">
                                        <div class="duracion">@{{ package.nights + 1 }}D/@{{ package.nights }}N</div>
                                        <div class="nombre">
                                            <span v-html="package.translations[0].name"></span>
                                        </div>
                                        <div class="ruta">@{{ package.destinations }}</div>
                                        <div class="clasificacion">
                                            <span class="tipo" style="--tipo-color:#04B5AA">@{{ package.tag.translations[0].value }}</span>
                                            <span class="tipo" style="--tipo-color:#fcf91b"
                                                  v-if="package.recommended > 0">{{trans('package.label.recommended')}}</span>
                                        </div>
                                    </div>
                                    <div class="bloque-precio">
                                        <div class="precio">
                                            <small>{{trans('package.label.from')}}</small>
                                            <span class="currency">$</span>
                                            <span class="valor">@{{ roundLito(package.price) }}</span>
                                        </div>
                                        <div class="botones ctaction d-flex">
                                            <a :href="package.translations[0].itinerary_link" type="button"
                                               class="btn btn-descargar" data-alert="notificacion"
                                               v-if="package.translations[0].itinerary_link != '' || package.translations[0].itinerary_link != null"
                                               data-title="Descargando Archivo" data-icon="fas fa-download"
                                               download title="{{trans('global.label.download_itinerary')}}">
                                                <i class="fas fa-download"></i></a>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#modalExtension" @click="openModal(package)"
                                                    :disabled="package.price == 0">
                                                {{trans('global.label.to_select')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="programa-foto">
                                    <img :src="package.galleries[0]" alt="Image Package"
                                         onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                         v-if="package.galleries.length > 0">
                                    <img :src="package.image_link" alt="Image Package"
                                         v-else-if="package.image_link && package.image_link != '' && package.image_link != null">
                                    <img
                                        src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                        alt="Image Package" v-else>
                                </div>
                            </div>
                        </div>
                        <!-- Extensiones 1 -->
                        <div class="col-md-12 text-center" v-if="packages.length == 0">
                            <h4><i class="far fa-frown"></i> {{trans('package.label.not_results')}}</h4>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
    @include('packages.modal')

@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                blockPage: false,
                loadSearch: false,
                item: 0,
                page: 1,
                checkLoaded: false,
                packages: [],
                packages_search_category: [],
                package_selected: [
                    null,
                    null,
                    null
                ],
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    defaultDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                    minDate: moment().add(1, 'days').format('YYYY-MM-DD'),
                },
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                date: moment().add(1, 'days').format('DD/MM/YYYY'),
                adults: 2,
                num_adults: 2,
                child: 0,
                num_child: 0,
                quantity_adults: 2,
                quantity_child: 0,
                package_destinations: [],
                quantity_persons: [{
                    adults: 2,
                    child_with_bed: 0,
                    child_without_bed: 0,
                    age_childs: [
                        {
                            age: 1
                        }
                    ]
                }],
                class_container_rooms: 'container_quantity_persons_rooms width_default_container',
                class_container_select: 'container_quantity_persons_rooms_select width_default_select',
                locale_data: {
                    direction: 'ltr',
                    format: moment.localeData().postformat('ddd D MMM'),
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                extensions: [],
                exclusive: [],
                service_types: [],
                classes_hotel: [],
                typeclass_id: 'all',
                service_type_id: 1,
                packages_original: [],
                extensions_original: [],
                exclusive_original: [],
                category_packages: [],
                filter_by_category: [],
                filter_by_destiny: [],
                sort_package: 1,
                filter_by_name: '',
                filter_by_nights: [
                    {
                        option: 1,
                        count: 0,
                        status: false
                    },
                    {
                        option: 2,
                        count: 0,
                        status: false
                    },
                    {
                        option: 3,
                        count: 0,
                        status: false
                    }
                ],
                mensaje: 'falso',
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                },
                tab: 'packages',
            },
            created: function () {
                this.getExtensions(1)
                this.getExtensions(2)
            },
            mounted () {
                this.restore_filters_from_storage()
                let $state = null
                this.setTranslations()
                this.getServicesTypes()
                this.getClassHotelByClientId()
                this.getPackages()
                // JQUERY + VUE: DRAG
                this.$nextTick(() => {
                    //this.mensaje =
                    // this.checkVue("$nextTick");

                    var $draggable = $('.owl-carousel .item'),
                        $active1, $active2, $extension

                    $('body')
                        .on('dragstart', '.owl-carousel .item', function (event) {
                            $extension = $(this)
                            // this.checkVue("dragstart");
                        })
                        .on('drop', '.block-extension', function (event) {
                            var $hermano = $(this).siblings('.block-extension')
                            // revisar si ya ha sido asignado y reubicarlo (bloque).
                            if ($extension.attr('data-extension') === $hermano.attr('data-extension')) {
                                $('.item[data-extension]').removeClass('selected')
                                $hermano.empty()
                                    .attr('data-extension', '0')
                                    .html('<div class=\'extension\'></div>')
                            }
                            // revisar si no tiene nada asignado (bloque) y desactivar anterior (lista)
                            else if ($(this).attr('data-extension') !== '0') {
                                $('.item[data-extension=\'' + $(this).attr('data-extension') + '\']').removeClass('selected')
                            }
                            // copiar (bloque) + seleccionar (lista)
                            $(this)
                                .html($extension.html())
                                .attr('data-extension', $extension.attr('data-extension'))
                            $('.item.extension[data-extension=\'' + $(this).attr('data-extension') + '\']').addClass('selected')
                            // detener drag
                            event.preventDefault()
                            $(this).removeClass('drop-able')
                            // this.checkVue("drop");
                        })
                        .on('dragover', '.block-extension', function (event) {
                            event.preventDefault()
                            $(this).addClass('drop-able')
                            // this.checkVue("dragover");
                        })
                        .on('dragenter', '.block-extension', function (event) {
                            $(this).addClass('drop-able')
                            // this.checkVue("dragenter");
                        })
                        .on('dragleave', '.block-extension', function (event) {
                            $(this).removeClass('dragleave')
                        })
                        .on('click', '.block-extension', function () {
                            console.log('remove click')
                            // revisar si tiene extension + remover extension
                            if ($(this).attr('data-extension') !== '0') {
                                // des-seleccionar (lista)
                                $('.item.extension[data-extension=\'' + $(this).attr('data-extension') + '\']').removeClass('selected')
                                // vaciar (bloque)
                                $(this).empty().attr('data-extension', '0').html('<div class=\'extension\'></div>')
                            }
                            $(this).removeClass('click')
                        })

                    $('.modal').on('hidden.bs.modal', function (event) {
                        $('.owl-carousel').trigger('destroy.owl.carousel')
                        $(this).removeClass('destroy carousel on modal.close')
                    })
                })
            },
            computed: {},
            methods: {
                restore_filters_from_storage () {
                    let restore_date = localStorage.getItem('m_package_date')
                    if (restore_date === null || restore_date === undefined || restore_date === '') {
                        return
                    }
                    this.date = restore_date

                    this.quantity_persons = JSON.parse(localStorage.getItem('m_package_quantity_persons'))
                    this.adults = this.quantity_persons[0].adults
                    this.service_type_id = (!(localStorage.getItem('m_package_service_type_id'))) ? 1 : localStorage.getItem('m_package_service_type_id')
                    this.typeclass_id = localStorage.getItem('m_package_typeclass_id')

                },
                setValuesByDefault () {

                    // if (!localStorage.getItem('m_package_date')) {
                    //     this.date = moment().format('DD/MM/YYYY')
                    // } else {
                    //     this.date = localStorage.getItem('m_package_date')
                    // }
                    if (!localStorage.getItem('m_package_quantity_persons') || !localStorage.getItem('verify_package_quantity_persons')) {
                        this.quantity_persons = [{
                            adults: 2,
                            child_with_bed: 0,
                            child_without_bed: 0,
                            age_childs: [
                                {
                                    age: 1
                                }
                            ]
                        }]
                        localStorage.setItem('m_package_quantity_persons', JSON.stringify(this.quantity_persons))
                    } else {

                        this.quantity_persons = JSON.parse(localStorage.getItem('m_package_quantity_persons'))
                        this.adults = this.quantity_persons[0].adults
                        this.num_adults = this.quantity_persons[0].adults
                        this.quantity_adults = this.quantity_persons[0].adults
                        this.child = this.quantity_persons[0].child_with_bed + this.quantity_persons[0].child_without_bed
                        this.num_child = this.quantity_persons[0].child_with_bed + this.quantity_persons[0].child_without_bed
                        this.quantity_child = this.quantity_persons[0].child_with_bed + this.quantity_persons[0].child_without_bed
                    }
                    if (!localStorage.getItem('m_package_service_type_id')) {
                        this.service_type_id = 1
                    } else {
                        this.service_type_id = localStorage.getItem('m_package_service_type_id')
                    }
                    if (!localStorage.getItem('m_package_typeclass_id')) {
                        this.typeclass_id = 'all'
                    } else {
                        this.typeclass_id = localStorage.getItem('m_package_typeclass_id')
                    }
                },
                setTranslations () {
                    axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                        this.translations = data.data
                    })
                },
                roundLito: function (num) {
                    num = parseFloat(num)
                    num = (num).toFixed(2)

                    if (num != null) {
                        var res = String(num).split('.')
                        var nEntero = parseInt(res[0])
                        var nDecimal = 0
                        if (res.length > 1)
                            nDecimal = parseInt(res[1])

                        var newDecimal
                        if (nDecimal < 25) {
                            newDecimal = 0
                        } else if (nDecimal >= 25 && nDecimal < 75) {
                            newDecimal = 5
                        } else if (nDecimal >= 75) {
                            nEntero = nEntero + 1
                            newDecimal = 0
                        }

                        return parseFloat(String(nEntero) + '.' + String(newDecimal))
                    }
                },
                changeQuantityAdult: function (event) {
                    this.quantity_persons[0].adults = parseInt(event.target.value)
                },
                changeQuantityChild: function (event) {
                    this.quantity_persons[0].child = parseInt(event.target.value)
                    this.quantity_persons[0].age_childs.splice(1, 4)
                    for (let i = 1; i < event.target.value; i++) {
                        this.quantity_persons[0].age_childs.splice((i + 1), 0, { age: 1 })
                    }
                },
                initCarousel () {
                    $('.owl-carousel').owlCarousel({
                        margin: 10,
                        nav: true,
                        navText: ['', ''],
                        dots: false,
                        loop: false,
                        mouseDrag: false,
                        //touchDrag: false,
                        responsive: {
                            0: {
                                items: 1
                            },
                            320: {
                                items: 2
                            },
                            480: {
                                items: 3
                            },
                            768: {
                                items: 4
                            },
                            1024: {
                                items: 4
                            }
                        }
                    })
                    //console.log("Creando Carrusel!")
                },
                getExtensions: function (type) {
                    axios.post(baseExternalURL + 'api/extensions', {
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        date: (this.date === '') ? moment().add(1, 'days').format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.quantity_persons,
                        service_type: this.service_type_id,
                        type_class: this.typeclass_id,
                        type_package: [type]
                    }).then((result) => {
                        if (type === 1) {
                            this.extensions_original = result.data
                            this.extensions = this.sortRecommendedExntesions()
                        }
                        if (type === 2) {
                            this.exclusive_original = result.data
                            this.exclusive = this.sortRecommendedExclusive()
                        }

                    }).catch((e) => {
                        console.log(e)
                    })
                },
                sortRecommendedExntesions: function () {
                    let packages_new = this.extensions_original.filter(function (package) {
                        return package.recommended !== 0
                    })
                    packages_new.sort((packagea, packageb) => packagea.recommended - packageb.recommended)
                    let packages_not_recommended = this.extensions_original.filter(function (package) {
                        return package.recommended === 0
                    })
                    return packages_new.concat(packages_not_recommended)
                },
                sortRecommendedExclusive: function () {
                    let packages_new = this.exclusive_original.filter(function (package) {
                        return package.recommended !== 0
                    })
                    packages_new.sort((packagea, packageb) => packagea.recommended - packageb.recommended)
                    let packages_not_recommended = this.exclusive_original.filter(function (package) {
                        return package.recommended === 0
                    })
                    return packages_new.concat(packages_not_recommended)
                },
                savePackagesSelected: function () {
                    if ($('#ext1').attr('data-extension') !== '0') {
                        let extension_id = $('#ext1').attr('data-extension')
                        for (let i = 0; i < this.extensions.length; i++) {
                            if (this.extensions[i]['id'] == parseInt(extension_id)) {
                                this.package_selected[0] = this.extensions[i]
                                console.log(this.package_selected[0])
                            }
                        }
                    }
                    if ($('#ext2').attr('data-extension') !== '0') {
                        let extension_id = $('#ext2').attr('data-extension')
                        for (let i = 0; i < this.extensions.length; i++) {
                            if (this.extensions[i]['id'] == parseInt(extension_id)) {
                                this.package_selected[2] = this.extensions[i]
                            }
                        }
                    }

                    if (this.package_selected[1].extension === '2' && this.package_selected[1].fixed_outputs.length === 0) {
                        let date = moment(this.package_selected[1].plan_rates[0].date_from, 'YYYY-MM-DD').format('DD/MM/YYYY')
                        // let date = moment('2020-04-05','YYYY-MM-DD').format('DD/MM/YYYY')
                        if (moment(date, 'DD/MM/YYYY').isSameOrAfter(moment())) {
                            console.log('la fecha es mayor')
                            this.date = date
                        } else {
                            console.log('la fecha es menor')
                        }
                    }
                    let data = {
                        lang: localStorage.getItem('lang'),
                        packages_selected: this.package_selected,
                        params: {
                            date: (this.date === '') ? moment().add(1, 'days').format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                            quantity_persons: this.quantity_persons,
                            service_type: this.service_type_id,
                            type_class: this.typeclass_id
                        }
                    }
                    axios.post(baseExternalURL + 'api/packages/selected', data).then((result) => {
                        if (result.data.success) {
                            // localStorage.setItem('m_package_date', this.date)
                            window.location = baseURL + 'packages/details'
                        } else {
                            this.$toast.error(result.data.message, {
                                position: 'top-right'
                            })
                        }
                    })
                },
                openModal: function (package_selected) {
                    this.getExtensionRecommended(package_selected)
                    this.package_selected[0] = null
                    this.package_selected[1] = package_selected
                    this.package_selected[2] = null
                    $('.modal-extensiones').modal()
                    this.initCarousel()
                    this.$forceUpdate()
                },
                getExtensionRecommended: function (package_selected) {
                    let extensionRecommended = package_selected.extension_recommended
                    for (var e = 0; e < this.extensions.length; e++) {
                        for (var i = 0; i < extensionRecommended.length; i++) {
                            if (extensionRecommended[i].extension_id === this.extensions[e].id) {
                                this.extensions[e].recommended = 1
                            }
                        }
                    }
                    // console.log(this.extensions)
                },
                sortPackages: function (event) {
                    //Recomendados
                    if (event.target.value === '1') {
                        this.sort_package = 1
                        this.packages = this.sortRecommended()
                    }
                    //precio menor
                    if (event.target.value === '2') {
                        this.sort_package = 2
                        this.packages.sort((packagea, packageb) => packagea.price - packageb.price)
                    }
                    //precio mayor
                    if (event.target.value === '3') {
                        this.sort_package = 3
                        this.packages.sort((packagea, packageb) => packageb.price - packagea.price)
                    }
                    //duracion menor
                    if (event.target.value === '4') {
                        this.sort_package = 4
                        this.packages.sort((packagea, packageb) => packagea.nights - packageb.nights)
                    }
                    //duracion mayor
                    if (event.target.value === '5') {
                        this.sort_package = 5
                        this.packages.sort((packagea, packageb) => packageb.nights - packagea.nights)
                    }
                },
                sortPackagesBy: function (packages_new) {
                    //Recomendados
                    if (this.sort_package === 1) {
                        packages_new = this.sortRecommended()
                    }
                    //precio menor
                    if (this.sort_package === 2) {
                        packages_new.sort((packagea, packageb) => packagea.price - packageb.price)
                    }
                    //precio mayor
                    if (this.sort_package === 3) {
                        packages_new.sort((packagea, packageb) => packageb.price - packagea.price)
                    }
                    //duracion menor
                    if (this.sort_package === 4) {
                        packages_new.sort((packagea, packageb) => packagea.nights - packageb.nights)
                    }
                    //duracion mayor
                    if (this.sort_package === 5) {
                        packages_new.sort((packagea, packageb) => packageb.nights - packagea.nights)
                    }

                    this.packages = packages_new
                },
                sortRecommended: function () {
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    let packages_new = packages_original.filter(function (package) {
                        return package.recommended !== 0
                    })

                    packages_new.sort((packagea, packageb) => packagea.recommended - packageb.recommended)
                    let packages_not_recommended = packages_original.filter(function (package) {
                        return package.recommended === 0
                    })
                    return packages_new.concat(packages_not_recommended)
                },
                unCheckAllCategories: function () {
                    $('#all_the_experiences').prop('checked', true)
                    for (let i = 0; i < this.filter_by_category.length; i++) {
                        if (this.filter_by_category[i].status) {
                            $('#all_the_experiences').prop('checked', false)
                        }
                    }
                },
                filterByCategoryAll: function () {
                    for (let i = 0; i < this.filter_by_category.length; i++) {
                        if (this.filter_by_category[i].status) {
                            this.filter_by_category[i].status = false
                        }
                    }

                    if (this.tab === 'extensions') {
                        this.packages = this.extensions_original
                    } else if (this.tab === 'exclusive') {
                        this.packages = this.exclusive_original
                    } else {
                        this.packages = this.packages_original
                    }
                    this.packages_search_category = []
                    this.getDestinationsPackages()
                    this.generateFilterByNights()
                },
                filterByCategory: function (index_category) {
                    this.unCheckAllCategories()
                    let packages_new = []
                    let check_status = false
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    for (let i = 0; i < this.filter_by_category.length; i++) {
                        if (this.filter_by_category[i].status && index_category === i) {
                            check_status = true
                            let package_new = packages_original.filter(this.checkCategoryId.bind(this, this.filter_by_category[i].tag_id))
                            if (packages_new.length === 0) {
                                packages_new = package_new
                            } else {
                                packages_new = packages_new.concat(package_new)
                            }
                        } else {
                            this.filter_by_category[i].status = false
                        }
                    }
                    if (check_status) {
                        this.packages = packages_new
                        this.packages_search_category = packages_new
                        this.getDestinationsPackages()
                        this.generateFilterByNights()
                    } else {
                        if (this.tab === 'extensions') {
                            this.packages = this.extensions_original
                        } else if (this.tab === 'exclusive') {
                            this.packages = this.exclusive_original
                        } else {
                            this.packages = this.packages_original
                        }
                        this.packages_search_category = []
                    }
                },
                unCheckAllDestiny: function () {
                    $('#all_destinations').prop('checked', true)
                    for (let i = 0; i < this.filter_by_destiny.length; i++) {
                        if (this.filter_by_destiny[i].status) {
                            $('#all_destinations').prop('checked', false)
                        }
                    }
                },
                filterByDestinyAll: function () {
                    for (let i = 0; i < this.filter_by_destiny.length; i++) {
                        if (this.filter_by_destiny[i].status) {
                            this.filter_by_destiny[i].status = false
                        }
                    }
                    if (this.tab === 'extensions') {
                        this.packages = this.extensions_original
                    } else if (this.tab === 'exclusive') {
                        this.packages = this.exclusive_original
                    } else {
                        this.packages = this.packages_original
                    }
                    if (this.packages_search_category.length > 0) {
                        this.packages = this.packages_search_category
                    }
                },
                filterByDestiny: function (index_destiny) {
                    this.unCheckAllDestiny()
                    let packages_new = []
                    let check_status = false
                    for (let i = 0; i < this.filter_by_destiny.length; i++) {
                        if (this.filter_by_destiny[i].status && index_destiny === i) {
                            check_status = true
                            let package = this.filterDestiny(this.filter_by_destiny[i].state_id)
                            packages_new = package
                        } else {
                            this.filter_by_destiny[i].status = false
                        }
                    }
                    this.sortPackagesBy(packages_new)
                    if (check_status) {
                        this.packages = packages_new
                    } else {
                        if (this.tab === 'extensions') {
                            this.packages = this.extensions_original
                        } else if (this.tab === 'exclusive') {
                            this.packages = this.exclusive_original
                        } else {
                            this.packages = this.packages_original
                        }
                    }

                },
                filterDestiny: function (state_id) {
                    let array_package = []
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    if (this.packages_search_category.length > 0) {
                        packages_original = this.packages_search_category
                    }

                    for (let i = 0; i < packages_original.length; i++) {
                        for (let d = 0; d < packages_original[i].package_destinations.length; d++) {
                            if (state_id === packages_original[i].package_destinations[d].state_id) {
                                array_package.push(packages_original[i])
                            }
                        }
                    }
                    return array_package
                },
                getPackages: function ($state) {
                    if (localStorage.getItem('client_id') == '') {
                        this.$toast.warning(this.translations.label.select_a_customer, {
                            position: 'top-right'
                        })
                        return
                    }
                    this.blockPage = true
                    let type_package = 0
                    if (this.tab === 'extensions') {
                        type_package = 1
                    } else if (this.tab === 'exclusives') {
                        type_package = 2
                    }
                    let data = {
                        lang: localStorage.getItem('lang'),
                        client_id: localStorage.getItem('client_id'),
                        date: (this.date === '') ? moment().add(1, 'days').format('YYYY-MM-DD') : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        quantity_persons: this.quantity_persons,
                        service_type: this.service_type_id,
                        type_class: this.typeclass_id,
                        type_package: [type_package]
                    }
                    console.log(data.date)
                    axios.post(baseExternalURL + 'api/packages/active', data)
                        .then((result) => {
                            this.package_selected[0] = null
                            this.package_selected[1] = result.data[0]
                            this.package_selected[2] = null
                            if (this.tab === 'packages') {
                                this.packages_original = result.data
                            } else {
                                this.extensions_original = result.data
                                // this.extensions = this.sortRecommended()
                            }
                            this.packages = this.sortRecommended()
                            this.getCategoryPackages()
                            this.getDestinationsPackages()
                            this.blockPage = false
                            // localStorage.setItem('m_package_date', this.date)
                            localStorage.setItem('m_package_quantity_persons', JSON.stringify(this.quantity_persons))
                            localStorage.setItem('m_package_service_type_id', this.service_type_id)
                            localStorage.setItem('m_package_typeclass_id', this.typeclass_id)
                            localStorage.setItem('verify_package_quantity_persons', true)
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })

                },
                getCategoryPackages: function () {
                    axios.get(baseExternalURL + 'api/tags/selectBox?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            this.category_packages = result.data.data
                            this.generateFilterByCategory()
                            this.generateFilterByNights()
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                unCheckAllNights: function () {
                    $('#all_itineraries').prop('checked', true)
                    for (let i = 0; i < this.filter_by_nights.length; i++) {
                        if (this.filter_by_nights[i].status) {
                            $('#all_itineraries').prop('checked', false)
                        }
                    }
                },
                filterByNightsAll: function () {
                    for (let i = 0; i < this.filter_by_nights.length; i++) {
                        if (this.filter_by_nights[i].status) {
                            this.filter_by_nights[i].status = false
                        }
                    }
                    if (this.tab === 'extensions') {
                        this.packages = this.extensions_original
                    } else if (this.tab === 'exclusive') {
                        this.packages = this.exclusive_original
                    } else {
                        this.packages = this.packages_original
                    }
                    if (this.packages_search_category.length > 0) {
                        this.packages = this.packages_search_category
                    }

                },
                filterByNights: function (index_nights) {
                    this.unCheckAllNights()
                    let packages_news = []
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    if (this.packages_search_category.length > 0) {
                        packages_original = this.packages_search_category
                    }
                    let check_status = false
                    for (let i = 0; i < this.filter_by_nights.length; i++) {
                        if (this.filter_by_nights[i].option == 1 && this.filter_by_nights[i].status && index_nights == i) {
                            check_status = true
                            let packages_new_3 = packages_original.filter(function (package) {
                                return package.nights >= 1 && package.nights <= 3
                            })
                            if (packages_news.length === 0) {
                                packages_news = packages_new_3
                            } else {
                                packages_news = packages_news.concat(packages_new_3)
                            }
                        }

                        if (this.filter_by_nights[i].option == 2 && this.filter_by_nights[i].status && index_nights == i) {
                            check_status = true
                            let packages_new_4_6 = packages_original.filter(function (package) {
                                return package.nights >= 4 && package.nights <= 6
                            })
                            if (packages_news.length === 0) {
                                packages_news = packages_new_4_6
                            } else {
                                packages_news = packages_news.concat(packages_new_4_6)
                            }
                        }

                        if (this.filter_by_nights[i].option == 3 && this.filter_by_nights[i].status && index_nights == i) {
                            check_status = true
                            let packages_new_7_10 = packages_original.filter(function (package) {
                                return package.nights >= 7 && package.nights <= 10
                            })
                            if (packages_news.length === 0) {
                                packages_news = packages_new_7_10
                            } else {
                                packages_news = packages_news.concat(packages_new_7_10)
                            }
                        }

                    }

                    for (let i = 0; i < this.filter_by_nights.length; i++) {
                        if (this.filter_by_nights[i].status && index_nights != i) {
                            this.filter_by_nights[i].status = false
                        }
                    }

                    this.sortPackagesBy(packages_news)
                    if (check_status) {
                        this.packages = packages_news

                    } else {
                        if (this.tab === 'extensions') {
                            this.packages = this.extensions_original
                        } else if (this.tab === 'exclusive') {
                            this.packages = this.exclusive_original
                        } else {
                            this.packages = this.packages_original
                        }
                    }
                },

                generateFilterByNights: function () {
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    // this.filter_by_nights = []
                    if (this.packages.length > 0) {
                        packages_original = this.packages
                    }

                    let packages_news = []
                    for (let i = 0; i < this.filter_by_nights.length; i++) {

                        if (this.filter_by_nights[i].option == 1) {
                            let packages_new_3 = packages_original.filter(function (package) {

                                return package.nights >= 1 && package.nights <= 3

                            })
                            this.$set(this.filter_by_nights[i], 'count', packages_new_3.length)
                            if (packages_news.length === 0) {
                                packages_news = packages_new_3
                            } else {
                                packages_news = packages_news.concat(packages_new_3)
                            }
                        }
                        if (this.filter_by_nights[i].option == 2) {
                            let packages_new_4_6 = packages_original.filter(function (package) {

                                return package.nights >= 4 && package.nights <= 6
                            })
                            this.$set(this.filter_by_nights[i], 'count', packages_new_4_6.length)
                            if (packages_news.length === 0) {
                                packages_news = packages_new_4_6
                            } else {
                                packages_news = packages_news.concat(packages_new_4_6)
                            }
                        }
                        if (this.filter_by_nights[i].option == 3) {
                            let packages_new_7_10 = packages_original.filter(function (package) {

                                return package.nights >= 7 && package.nights <= 10
                            })
                            this.$set(this.filter_by_nights[i], 'count', packages_new_7_10.length)
                            if (packages_news.length === 0) {
                                packages_news = packages_new_7_10
                            } else {
                                packages_news = packages_news.concat(packages_new_7_10)
                            }
                        }
                    }

                    // this.packages = packages_news
                },
                generateFilterByCategory: function () {
                    this.filter_by_category = []
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    for (let i = 0; i < this.category_packages.length; i++) {
                        let packages = packages_original.filter(this.checkCategoryId.bind(this, this.category_packages[i].id))
                        // if(this.category_packages[i].id === 13){
                        //   console.log(packages)
                        // }
                        if (packages.length > 0) {
                            this.filter_by_category.push({
                                tag_id: this.category_packages[i].id,
                                tag_name: this.category_packages[i].translations[0].value,
                                count: packages.length,
                                status: false
                            })
                        }
                    }
                },
                getDestinationsPackages: function () {
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }

                    this.filter_by_destiny = []
                    if (this.packages.length > 0) {
                        packages_original = this.packages
                    }
                    let filter_by_destiny = []
                    for (let i = 0; i < packages_original.length; i++) {
                        for (let d = 0; d < packages_original[i].package_destinations.length; d++) {
                            filter_by_destiny.push({
                                state_id: packages_original[i].package_destinations[d].state_id,
                                name: packages_original[i].package_destinations[d].state.translations[0].value,
                                count: 0,
                                status: false
                            })
                        }
                    }
                    // console.log(filter_by_destiny)
                    for (let i = 0; i < filter_by_destiny.length; i++) {
                        let packages = filter_by_destiny.filter(this.checkDestinyId.bind(this, filter_by_destiny[i].state_id))
                        if (packages.length > 0) {
                            this.filter_by_destiny.push({
                                state_id: filter_by_destiny[i].state_id,
                                name: filter_by_destiny[i].name,
                                count: packages.length,
                                status: false
                            })
                        }
                    }
                    this.filter_by_destiny = this.removeDuplicates(this.filter_by_destiny, 'state_id')

                },
                removeDuplicates: function (originalArray, prop) {
                    var newArray = []
                    var lookupObject = {}

                    for (var i in originalArray) {
                        lookupObject[originalArray[i][prop]] = originalArray[i]
                    }
                    for (i in lookupObject) {
                        newArray.push(lookupObject[i])
                    }
                    return newArray
                },
                checkCategoryId: function (category_id, package) {
                    // console.log(package.tag_id,category_id)
                    if (package.tag_id === category_id) {
                        return true
                    }
                },
                checkDestinyId: function (state, package_destination) {
                    if (package_destination.state_id === state) {
                        return true
                    }
                },
                checkNamePackage: function (package_name, package) {
                    if (package.translations[0].name.toLowerCase().includes(package_name.toLowerCase())) {
                        return true
                    }
                },
                filterByNamePackage: function () {
                    let packages_original = this.packages_original
                    if (this.tab === 'extensions') {
                        packages_original = this.extensions_original
                    }
                    if (this.tab === 'exclusive') {
                        packages_original = this.exclusive_original
                    }
                    let packages_new = packages_original.filter(this.checkNamePackage.bind(this, this.filter_by_name))
                    if (this.filter_by_name != '') {
                        this.packages = packages_new
                    } else {
                        if (this.tab === 'extensions') {
                            this.packages = this.extensions_original
                        } else if (this.tab === 'exclusive') {
                            this.packages = this.exclusive_original
                        } else {
                            this.packages = this.packages_original
                        }
                    }
                },
                getServicesTypes: function () {
                    axios.get(baseExternalURL + 'api/service_types/selectBox?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            let services_types = []
                            for (let i = 0; i < result.data.data.length; i++) {
                                if (result.data.data[i].code !== 'NA') {
                                    services_types.push(
                                        {
                                            id: result.data.data[i].id,
                                            code: result.data.data[i].code,
                                            abbreviation: result.data.data[i].abbreviation,
                                            translations: result.data.data[i].translations,
                                        }
                                    )
                                }
                            }
                            if (!localStorage.getItem('m_package_service_type_id')) {
                                this.service_type_id = 1
                            } else {
                                this.service_type_id = localStorage.getItem('m_package_service_type_id')
                            }
                            this.service_types = services_types
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                getClassHotelByClientId: function () {
                    axios.get('api/client_hotels/classes?lang=' + localStorage.getItem('lang'))
                        .then((result) => {
                            if (result.data.success) {
                                this.classes_hotel = result.data.data
                                this.setValuesByDefault()
                            }
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                filterView: function (view, x) {
                    let filterView = document.getElementById('filterView_' + view)
                    $('.cambiar-vista div').removeClass('active')
                    filterView.setAttribute('class', 'col lg-2 font text-center active')
                    var $resultados = $('.resultado-programas .resultados')

                    if (view == 0) {
                        $resultados.attr('data-vista', 'normal')
                    } else if (view == 1) {
                        $resultados.attr('data-vista', 'grilla')
                    } else {
                        $resultados.attr('data-vista', 'lista')
                    }
                },
                toggleView: function (t) {
                    if (t === 'packages') {
                        this.packages = this.packages_original
                    }

                    if (t === 'extensions') {
                        this.packages = this.extensions
                    }

                    if (t === 'exclusive') {
                        this.packages = this.exclusive
                    }
                    this.tab = t
                    this.generateFilterByCategory()
                    this.getDestinationsPackages()
                    this.generateFilterByNights()
                },
            }
        })
    </script>
@endsection
