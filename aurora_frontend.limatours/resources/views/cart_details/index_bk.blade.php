@extends('layouts.app')
@section('content')
    <div class="container">
        <loading-component v-show="blockPage"></loading-component>
        <div class="cart-view cart" v-show="!blockPage">
            <h2>{{ trans('cart_view.label.title_cart') }}</h2>
            <h3>{{ trans('cart_view.label.you_have') }} @{{ cart_content.quantity_items
                }} {{ trans('cart_view.label.products_your_shopping_cart') }}</h3>
            <div class="shopping-cart">
                {{-- Hotel--}}
                <div class="hotel-content-shopping" v-for="(hotel,index) in cart_content.hotels">
                    <div class="img-shopping">
                        <div class="category" :style="'background-color:'+hotel.hotel.color_class+' '">@{{
                            hotel.hotel.class }}
                        </div>
                        <div class="img">
                            <img :src="hotel.hotel.galleries[0]" alt="Image Hotel"
                                              onerror="this.src = baseURL + 'images/hotel-default.jpg'"></div>
                    </div>
                    <div class="content-shopping">
                        <h4>@{{ hotel.hotel.name }}
                            <span class="icon-star" v-for="n in parseInt(hotel.hotel.category)"></span>
{{--                            <span class="tipo" style="display: none">[Cuz111]</span>--}}
                        </h4>
                        <div class="date-shopping">
                            <b>{{ trans('hotel.label.date') }}</b>
                            <span>@{{ formatDate(hotel.date_from) }}</span>
                            <span>@{{ formatDate(hotel.date_to) }}</span>
                            <span><b
                                    class="size">@{{ hotel.quantity_rooms }}</b> {{ trans('hotel.label.rooms') }}</span>
                            <span><b
                                    class="size">@{{hotel.quantity_adults }}</b> {{ trans('hotel.label.adults') }}</span>
                            <span v-if="hotel.quantity_child >0"><b
                                    class="size">@{{ hotel.quantity_child }}</b> {{ trans('hotel.label.child') }}</span>
                        </div>
                        @include('cart_details.hotel_upselling')
                    </div>
                    <div class="price-content">
                        <div class="price">USD<span>$</span><b>@{{ roundLito(hotel.total_hotel) }}</b>
                            <i class="icon-trash-2" @click="cancelRates(hotel.cart_items_id)"></i>
                        </div>
                    </div>
                    <div :id="'car-room'+index_room" class="car-room" v-for="(room,index_room) in hotel.rooms">
                        <div class="image">
                            <img :src="room.room.gallery[0]" alt="Image Room"
                                 onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                        </div>
                        <div class="content-car-room">
                            <h5>
                                <span class="fa fa-circle" v-bind:class="{circle_red : room.rate.onRequest ==0}"></span>@{{
                                room.room_name }}
                                <span class="text">@{{ room.rate.name }}</span>
                                <button class="btn btn-success" v-if="room.rate.onRequest ==1"
                                        style="border-radius: 20px;">OK
                                </button>
                                <button class="btn btn-danger" v-if="room.rate.onRequest ==0"
                                        style="border-radius: 20px;">RQ
                                </button>

                            </h5>
                            <div>@{{ room.rate.meal_name }}
                                <span></span>@{{ room.rate.political.cancellation.name }}
                            </div>
                            <div class="quantities">
                                <div>
                                    <span><b>@{{ room.quantity_adults }}</b> {{ trans('hotel.label.adults') }}</span>
                                    <span
                                        v-if="room.quantity_child > 0">@{{ room.quantity_child }} {{ trans('hotel.label.child') }}</span>
                                    <span class="total_amount">$ @{{ roundLito(parseFloat(room.total_room - room.rate.supplements.total_amount - room.rate.total_taxes_and_services).toFixed(2))}}</span>


                                    <span v-if="countSuplements(room.rate.supplements.supplements_optional) < 1"
                                          class="suple"><i class="icon-plus-square"></i> (0)</span>

                                    <span class="suple collapsed" data-toggle="collapse"
                                          :data-target="'#suple'+index+'_'+index_room" aria-expanded="false"
                                          aria-controls="navbar" v-else><i class="icon-plus-square"></i> (@{{ countSuplements(room.rate.supplements.supplements_optional) }})</span>

                                    <i class="icon-trash-2" @click="cancelRates(room.cart_items_id)"></i>
                                </div>
                                <div id="addSuplements">
                                    <div v-for="(supplement,index_supplement) in room.rate.supplements.supplements">
                                        <span data-toggle="collapse"
                                              :data-target="'#addSuplementsItem_'+index+'_'+index_room+'_'+index_supplement"
                                              aria-expanded="false"
                                              aria-controls="navbar">{{ trans('cart_view.label.supplement') }}: <b>@{{ supplement.supplement }}</b><i
                                                class="icon-chevron-down"></i></span>
                                        <span class="total_amount">$@{{ roundLito(parseFloat(supplement.total_amount).toFixed(2)) }}</span>

                                        <span class="suple add"><i></i></span>
                                        <i class="icon-trash-2" v-if="supplement.type_req_opt=='optional'"
                                           @click="deleteSupplement(room.cart_items_id[0],index_supplement,room.rate.supplements.supplements_optional)"></i>
                                        <i v-else></i>
                                        <div :id="'addSuplementsItem_'+index+'_'+index_room+'_'+index_supplement"
                                             class="collapse addSuplementsItem_collapse">
                                            <div class="firts">
                                                <div v-for="(calendar,index_calendar) in supplement.calendars">
                                                    <span class="date"><b>@{{ formatDate(calendar.date) }}</b></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div :id="'suple'+index+'_'+index_room" class="collapse">
                                <div class="content-suple">
                                    <p>
                                        <i class="icon-plus-square"></i> {{ trans('cart_view.label.select_supplements') }}
                                    </p>
                                    <a class="close" data-toggle="collapse" :data-target="'#suple'+index+'_'+index_room"
                                       aria-expanded="false"
                                       aria-controls="navbar"> {{ trans('hotel.label.Close') }} <i
                                            class="fa fa-times"></i></a>


                                    <ul>
                                        <li v-for="(supplement, index_suple) in room.rate.supplements.supplements_optional">
                                            <div class="form-check">
                                                <label :id="'checkbox_'+index+'_'+index_room+'_'+index_suple"
                                                       :for="'checkbox_'+index+'_'+index_room+'_'+index_suple"
                                                       class="form-check-label">
                                                    <input v-if="supplement.totals.length > 0" checked="checked"
                                                           type="checkbox"
                                                           :id="'checkbox_'+index+'_'+index_room+'_'+index_suple"
                                                           class="form-check-input icon-check">
                                                    <input type="checkbox"
                                                           :id="'checkbox_'+index+'_'+index_room+'_'+index_suple"
                                                           class="form-check-input icon-check" v-else>

                                                    <date-picker
                                                        :name="'picker'+index+'_'+index_room+'_'+index_suple"
                                                        :id="'picker'+index+'_'+index_room+'_'+index_suple"
                                                        v-model="supplement.date" :config="supplement.options"
                                                        @dp-hide="addSupplement(supplement,index,index_room,index_suple)"
                                                        :key="supplement.key"
                                                        :auto-apply="true">
                                                    </date-picker>
                                                    <span class="spacing"></span>
                                                    @{{ supplement.supplement }}

                                                    <!--<span class="count" v-if="supplement.totals.length > 0">(x@{{ supplement.totals.length }})</span>
                                                    @{{ supplement.totals.length }}
                                                    <span class="count" v-if="supplement.supplement_dates_selected !== undefined && supplement.supplement_dates_selected.length > 0 && supplement.totals.length == 0" >(x@{{ supplement.supplement_dates_selected.length }})</span>-->
                                                    <span :total="supplement.totals.length" class="count supleTota1">(x@{{ supplement.totals.length }})</span>
                                                    <span :id="'suple_'+index+'_'+index_room+'_'+index_suple"
                                                          :total="supplement.totals.length" class="count supleTota2">(x@{{ supplement.totals.length }})</span>

                                                    <span
                                                        v-if="supplement.amount_extra ==1 && supplement.calendars.length>0 ">$@{{ roundLito(parseFloat(supplement.total_amount / supplement.calendars.length).toFixed(2)) }}</span>
                                                    <span
                                                        v-if="supplement.amount_extra ==0 && supplement.calendars.length>0 ">$0</span>

                                                </label>
                                                <ul class="list">
                                                    <li v-for="(date_selected,index_date_selected) in supplement.supplement_dates_selected">
                                                        @{{ date_selected.date }} @{{
                                                        roundLito(date_selected.total_amount) }}<br>
                                                        <i class="icon-trash"
                                                           @click="addEnabledDate(date_selected.date,supplement,index_date_selected)"></i>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <button
                                        @click="addCartSupplementsOptionalSelected(room.cart_items_id[0],room.rate.supplements.supplements_optional,(room.rate.rate[0].quantity_adults_total + room.rate.rate[0].quantity_extras_total) ,room.rate.rate[0].quantity_child_total)">
                                        {{ trans('cart_view.label.update_rate') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Servicios--}}
                <div class="hotel-content-shopping" v-for="(service,index) in cart_content.services">
                    <div class="img-shopping">
                        <div class="category" :style="'background-color:'+exp.color+' '"
                             v-for="exp in service.service.experiences">@{{ exp.name }}
                        </div>
                        <div class="img">
                            <img v-if="service.service.galleries.length > 0" :src="service.service.galleries[0].url"
                                 onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                 class="object-fit_cover backup_picture_service" alt="service">
                            <img class="object-fit_cover backup_picture_service"
                                 src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                 alt="Image Service" v-else>
                        </div>
                    </div>
                    <div class="content-shopping">
                        <h4>@{{ service.service_name }}
                            <span class="tipo">[@{{ service.service.code }}]</span>
                            <span class="badge badge-success" v-if="service.service.on_request == 0">OK</span>
                            <span class="badge badge-danger" v-if="service.service.on_request == 1">RQ</span>
                        </h4>
                        <div class="date-shopping">
                            <b>{{ trans('hotel.label.date') }}</b>
                            <span>@{{ formatDate(service.date_from) }}</span>
                        </div>
                        <div class="destiny-shopping">
                            <i class="icon-map-pin-in"></i>
                            <span>@{{ service.service.origin.country }}, @{{ service.service.origin.state}}</span>
                            <span
                                v-if="service.service.origin.city !== null">, @{{ service.service.origin.city }}</span>
                            <span
                                v-if="service.service.origin.zone !== null">, @{{ service.service.origin.zone }}</span>
                            <i class="icon-arrow-right"></i>
                            <i class="icon-map-pin-out"></i>
                            <span> @{{ service.service.destiny.country }}, @{{ service.service.destiny.state }}</span>
                            <span
                                v-if="service.service.destiny.city !== null">, @{{ service.service.destiny.city }}</span>
                            <span
                                v-if="service.service.destiny.zone !== null">, @{{ service.service.destiny.zone }}</span>
                        </div>
                        {{--                        <span style="font-size: 12px">@{{ service.service.rate.rate_plans[0].political.cancellation.penalties[0].message }}</span>--}}
                    </div>
                    <div class="price-content">
                        <div class="price">USD<span>$</span><b>@{{ service.total_service }}</b>
                            <i class="icon-trash-2" @click="cancelRates(service.cart_items_id)"></i>
                        </div>
                    </div>
                </div>
                <div class="no-gutters total">
                    <div class="price">
                        {{ trans('cart_view.label.total_to_pay') }} <span>USD<span>$</span></span> <b>@{{ cart_content.total_cart }}</b>
                    </div>
                </div>
                <div class="text-right">
                    <a class="btn btn-primary btn-car"
                       href="/reservations/personal-data">{{ trans('cart_view.label.reserve') }}</a>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                blockPage: false,
                msg: "{{ trans('hotel.label.please_loading') }}",
                cart_content: [],
                up_selling: [],
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
            },
            created: function () {
                this.getCartContent()
            },
            mounted () {
            },
            computed: {},
            methods: {
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
                showPriceAlternatives: function (hotel, price_my_selection) {
                    let price = 0
                    if (parseFloat(hotel.price) > parseFloat(price_my_selection)) {
                        price = (parseFloat(hotel.price).toFixed(2) - parseFloat(price_my_selection).toFixed(2)).toFixed(2)

                        price += ' +'
                    }
                    if (parseFloat(hotel.price) < parseFloat(price_my_selection)) {
                        price = (parseFloat(price_my_selection).toFixed(2) - parseFloat(hotel.price).toFixed(2)).toFixed(2)

                        price += ' -'
                    }
                    return price
                },
                showNameRooms: function (hotel) {
                    if (hotel.best_options.legth > 0) {
                        let name_rooms = ''
                        for (let i = 0; i < hotel.best_options.rooms.length; i++) {
                            if (i == 0) {

                                name_rooms += hotel.best_options.rooms[i].name + ' '
                            } else {
                                name_rooms += ' + ' + hotel.best_options.rooms[i].name + ' '
                            }
                        }
                        return name_rooms
                    }
                },
                countSuplements: function (suplements) {
                    let j = 0
                    for (let i = 0; i < suplements.length; i++) {
                        j++
                    }
                    return j
                },
                deleteSupplement: function (cart_item_id, index_supplement, supplements_optional, index, index_room, nombre) {
                    axios.post(baseURL + 'cart/delete/item', {
                        cart_item_id: cart_item_id,
                        index_supplement: index_supplement
                    })
                        .then((result) => {
                            this.updateMenu()
                            this.getCartContent()

                            for (var i = 0; i < document.getElementsByClassName('supleTota1').length; i++) {
                                document.getElementsByClassName('supleTota1')[i].style.display = 'inline-block'
                            }

                            for (var i = 0; i < document.getElementsByClassName('supleTota2').length; i++) {
                                document.getElementsByClassName('supleTota2')[i].style.display = 'none'
                            }

                        }).catch((e) => {
                        console.log(e)
                    })
                },
                deleteSupplementService: function (cart_item_id, supplements_optional, quantity_adult, quantity_child) {
                    axios.post(baseURL + 'cart/delete/item/service', {
                        cart_item_id: cart_item_id,
                        supplements_optional: supplements_optional,
                        quantity_adult: quantity_adult,
                        quantity_child: quantity_child,
                    })
                        .then((result) => {
                            this.updateMenu()
                            this.getCartContent()
                            for (var i = 0; i < document.getElementsByClassName('supleTota1').length; i++) {
                                document.getElementsByClassName('supleTota1')[i].style.display = 'inline-block'
                            }
                            for (var i = 0; i < document.getElementsByClassName('supleTota2').length; i++) {
                                document.getElementsByClassName('supleTota2')[i].style.display = 'none'
                            }

                        }).catch((e) => {
                        console.log(e)
                    })
                },
                addCartSupplementsOptionalSelected: function (cart_item_id, supplements_optional, quantity_adult, quantity_child) {
                    axios.post(baseURL + 'cart/update/item', {
                        quantity_adult: quantity_adult,
                        quantity_child: quantity_child,
                        cart_item_id: cart_item_id,
                        supplements_optional: supplements_optional
                    })
                        .then((result) => {
                            this.updateMenu()
                            this.getCartContent()

                        }).catch((e) => {
                        console.log(e)
                    })
                },

                addCartSupplementsOptionalSelectedService: function (cart_item_id, supplements_optional, quantity_adult, quantity_child) {
                    let supplement_selected = []
                    supplements_optional.forEach(function (supplement) {
                        if (supplement.selected) {
                            supplement_selected.push(supplement)
                        }
                    })

                    if (supplement_selected.length > 0) {
                        axios.post(baseURL + 'cart/update/item/service', {
                            quantity_adult: quantity_adult,
                            quantity_child: quantity_child,
                            cart_item_id: cart_item_id,
                            supplements_optional: supplement_selected
                        })
                            .then((result) => {
                                this.updateMenu()
                                this.getCartContent()

                            }).catch((e) => {
                            console.log(e)
                        })
                    }
                },
                addSupplement: function (supplement, index, index_room, index_suple) {
                    let date_selected = supplement.date
                    if (date_selected != null && date_selected != 'undefined') {

                        for (var i = 0; i < document.getElementsByClassName('supleTota1').length; i++) {
                            document.getElementsByClassName('supleTota1')[i].style.display = 'none'
                        }

                        for (var i = 0; i < document.getElementsByClassName('supleTota2').length; i++) {
                            document.getElementsByClassName('supleTota2')[i].style.display = 'inline-block'
                        }

                        let suple = document.getElementById('suple_' + index + '_' + index_room + '_' + index_suple)
                        let check = document.getElementById('checkbox_' + index + '_' + index_room + '_' + index_suple)
                        for (let i = 0; i < supplement.options.enabledDates.length; i++) {
                            if (date_selected === moment(supplement.options.enabledDates[i]).format('L')) {
                                for (let j = 0; j < supplement.calendars.length; j++) {
                                    if (supplement.calendars[j].date == supplement.options.enabledDates[i]) {
                                        check.setAttribute('checked', 'checked')
                                        supplement.supplement_dates_selected.push(supplement.calendars[j])
                                        break
                                    }
                                }
                                supplement.options.enabledDates.splice(i, 1)
                                date_selected = null
                                if (supplement.options.enabledDates.length === 0) {
                                    check.setAttribute('checked', '')
                                    supplement.options.daysOfWeekDisabled = [0, 1, 2, 3, 4, 5, 6]
                                }
                                break
                            }
                        }

                        supplement.date = null
                        supplement.key += 1
                        let totalSupleItem = parseInt(suple.getAttribute('total')) + parseInt(1)

                        suple.innerHTML = '(x' + totalSupleItem + ')'
                        suple.setAttribute('total', totalSupleItem)
                    }
                },
                transformDate: function (date) {
                    let array = date.split('/')
                    return '' + array[2] + '-' + array[1] + '-' + array[0]
                },
                addEnabledDate: function (date, supplement, index) {
                    delete supplement.options.daysOfWeekDisabled
                    supplement.options.enabledDates.push(date)
                    supplement.supplement_dates_selected.splice(index, 1)
                    supplement.key += 1
                },
                formatDate: function (starDate) {
                    return moment(starDate).format('ddd D MMM')
                },
                updateMenu: function () {
                    this.$emit('updateMenu')
                },
                getCartContent: function () {
                    axios.get(baseURL + 'cart_details/service')
                        .then((result) => {
                            if (result.data.success) {
                                this.cart_content = result.data.cart
                            }
                            this.blockPage = false
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                cancelRates: function (cart_items_id) {
                    this.blockPage = true
                    axios.post(
                        baseURL + 'cart/cancel/rates', {
                            cart_items_id: cart_items_id
                        }
                    )
                        .then((result) => {
                            if (result.data.success) {
                                this.updateMenu()
                                this.blockPage = false
                                this.getCartContent()
                            }
                        }).catch((e) => {
                        this.blockPage = false
                        console.log(e)
                    })
                },
                showAlterna: function (index_hotel, token_search_frontend, token_search, hotel_id, option) {

                    let container_hotel = document.getElementById('container_hotel' + index_hotel)

                    if (option) {
                        this.blockPage = true
                        axios.post('services/hotels/up-selling', {
                            token_search_frontend: token_search_frontend,
                            hotel_id: hotel_id
                        }).then((result) => {
                            this.returList(token_search + '_' + hotel_id)
                            this.up_selling = result.data.data[0].city
                            this.blockPage = false
                            if (this.up_selling.hotels.length > 0) {
                                container_hotel.style.display = 'block'
                            } else {
                                alert("{{ trans('cart_view.label.hotels_found_apply_upgrade') }}")
                            }
                        }).catch((e) => {
                            this.blockPage = false
                            console.log(e)
                        })
                    } else {
                        container_hotel.style.display = 'none'
                    }
                },
                comparar: function (index, index_alt) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let mieleccionRooms = mieleccion.getElementsByClassName('rooms-alternativa')[0]
                    // El elemendo alterno que reemplazara
                    let alternativa = document.getElementById(index_alt)
                    let roomsAlternativa = alternativa.getElementsByClassName('rooms-alternativa')[0]
                    // Los botones de seleccion del la alternativa
                    let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                    let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]
                    // Los controles del pop-up actual
                    //let contentBtn = mieleccion.parentElement.getElementsByClassName('contentBtn')[0];
                    let returList = mieleccion.parentElement.getElementsByClassName('returList')[0]
                    // Coleccion de elemendos alternativos
                    let alternativasClass = document.getElementsByClassName('row-alternativa')

                    // Se esconden todos los alternativos
                    for (var i = 0; i < alternativasClass.length; i++) {
                        alternativasClass[i].style.display = 'none' // depending on what you're doing
                    }

                    // El elemento seleccionado en el carrito de compras
                    mieleccion.style.display = 'block'
                    mieleccionRooms.style.display = 'block'
                    // El elemendo alterno que reemplazara
                    alternativa.style.display = 'block'
                    roomsAlternativa.style.display = 'block'
                    // Los botones de seleccion del elemento alterno
                    btnComprar.style.display = 'none'
                    btnCambiar.style.display = 'block'
                    // Los controles del pop-up actual
                    //contentBtn.style.display = "block";
                    returList.style.display = 'block'
                },
                cambiar: function (index, index_alt) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    // Los botones de seleccion en el carrito de compras
                    let btnEleccionMiEleccion = mieleccion.getElementsByClassName('btn-eleccion')[0]
                    let btnComprarEleccion = mieleccion.getElementsByClassName('btn-comprar')[0]
                    let btnCambiarEleccion = mieleccion.getElementsByClassName('btn-cambiar')[0]

                    let contentBtn = mieleccion.parentElement.getElementsByClassName('contentBtn')[0]

                    if (!index_alt) {
                        index_alt = mieleccion.getAttribute('alternative-selected')
                        let alternativa = document.getElementById(index_alt)
                        // Los botones de seleccion del la alternativa
                        let btnEleccion = alternativa.getElementsByClassName('btn-eleccion')[0]
                        let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                        let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]

                        mieleccion.classList.add('eleccion')
                        alternativa.classList.remove('eleccion')

                        btnEleccionMiEleccion.style.display = 'block'
                        btnComprarEleccion.style.display = 'none'
                        btnCambiarEleccion.style.display = 'none'

                        btnEleccion.style.display = 'none'
                        btnComprar.style.display = 'none'
                        btnCambiar.style.display = 'block'

                        contentBtn.style.display = 'none'

                        mieleccion.setAttribute('alternative-selected', '')
                    } else {
                        let alternativa = document.getElementById(index_alt)
                        // Los botones de seleccion del la alternativa
                        let btnEleccion = alternativa.getElementsByClassName('btn-eleccion')[0]
                        let btnComprar = alternativa.getElementsByClassName('btn-comprar')[0]
                        let btnCambiar = alternativa.getElementsByClassName('btn-cambiar')[0]

                        mieleccion.classList.remove('eleccion')
                        alternativa.classList.add('eleccion')

                        btnEleccionMiEleccion.style.display = 'none'
                        btnComprarEleccion.style.display = 'none'
                        btnCambiarEleccion.style.display = 'block'

                        btnEleccion.style.display = 'block'
                        btnComprar.style.display = 'none'
                        btnCambiar.style.display = 'none'

                        contentBtn.style.display = 'block'

                        mieleccion.setAttribute('alternative-selected', index_alt)
                    }
                },
                cambiar_eleccion: function (index, index_container_hotel) {
                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let cart_items_id = mieleccion.getAttribute('cart_items_id')
                    let index_alt = mieleccion.getAttribute('alternative-selected')

                    if (!index_alt) {
                        return false
                    }

                    let alternativa = document.getElementById(index_alt)
                    let upselling_index = alternativa.getAttribute('upselling-index')
                    let upselling_item = this.up_selling.hotels[upselling_index]

                    this.msg = "{{ trans('cart_view.label.label.updating_car') }}..."
                    this.blockPage = true
                    axios.post(baseURL + 'cart/content/change/item', {
                        token_search: this.up_selling.token_search,
                        token_search_frontend: this.up_selling.token_search_frontend,
                        date_from: this.up_selling.search_parameters.date_from,
                        date_to: this.up_selling.search_parameters.date_to,
                        cart_items_id: cart_items_id,
                        upselling_item: upselling_item
                    }).then((result) => {
                        this.msg = ''
                        let container_hotel = document.getElementById('container_hotel' + index_container_hotel)
                        container_hotel.style.display = 'none'
                        // this.returList(index);
                        this.updateMenu()
                        this.getCartContent()
                        this.blockPage = false
                    }).catch((e) => {
                        this.msg = ''
                        this.blockPage = false
                        console.log(e)
                    })
                },
                returList: function (index) {
                    let alternativasClass = document.getElementsByClassName('row-alternativa')
                    let mieleccionRoomsAlternativa = document.getElementsByClassName('rooms-alternativa')

                    let contentBtn = document.getElementsByClassName('contentBtn')
                    let returList = document.getElementsByClassName('returList')
                    let btnEleccion = document.getElementsByClassName('btn-eleccion')
                    let btnComprar = document.getElementsByClassName('btn-comprar')
                    let btnCambiar = document.getElementsByClassName('btn-cambiar')

                    for (var i = 0; i < alternativasClass.length; i++) {
                        alternativasClass[i].style.display = 'block'
                        alternativasClass[i].classList.remove('eleccion')
                    }
                    for (var i = 0; i < mieleccionRoomsAlternativa.length; i++) {
                        mieleccionRoomsAlternativa[i].style.display = 'none'
                    }
                    for (var i = 0; i < contentBtn.length; i++) {
                        contentBtn[i].style.display = 'none'
                        returList[i].style.display = 'none'
                        btnComprar[i].style.display = 'block'
                        btnCambiar[i].style.display = 'none'
                    }
                    for (var i = 0; i < btnComprar.length; i++) {
                        btnEleccion[i].style.display = 'none'
                        btnComprar[i].style.display = 'block'
                        btnCambiar[i].style.display = 'none'
                    }

                    // El elemento seleccionado en el carrito de compras
                    let mieleccion = document.getElementById(index)
                    let btnEleccionMiEleccion = mieleccion.getElementsByClassName('btn-eleccion')[0]
                    let btnComprarEleccion = mieleccion.getElementsByClassName('btn-comprar')[0]

                    btnEleccionMiEleccion.style.display = 'block'
                    btnComprarEleccion.style.display = 'none'
                    mieleccion.classList.add('eleccion')
                    mieleccion.setAttribute('alternative-selected', '')
                },
            }
        })
    </script>
    <style>
        .circle_red {
            color: #dc3545 !important;
        }
    </style>
@endsection
