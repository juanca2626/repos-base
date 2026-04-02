<div class="row-fluid col-lg-12 list-rooms">
    <div v-if="cart_quantity_items > 0" class="list-rooms-car cart">
        <a id="dropdownMainCar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-shopping-cart"></i>
            <span class="count">@{{cart.quantity_items}}</span>
        </a>
        <!--------- Carrito ------>
        <div class="dropdown dropdown-menu menu-cart" aria-labelledby="dropdownMainCar"
             v-if="cart.hotels.length > 0 || cart.services.length > 0">
            <h2>{{ trans('hotel.label.your_shopping_cart') }}</h2>
            <h3>{{ trans('hotel.label.you_have') }} @{{ (cart.hotels.length + cart.services.length)
                }} {{ trans('hotel.label.products_your_cart') }}</h3>
            <div class="shopping-cart">
                <div class="scroll-cart scrollbar-project">
                    <div class="card-body">
                        <div :id="'hotel-content-shopping'+index" class="hotel-content-shopping"
                             v-for="(hotel,index) in cart.hotels">
                            <div class="img-shopping">
                                <img :src="baseExternalURL+'images/'+hotel.hotel.galleries[0]"
                                     alt="Image Hotel"
                                     onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                            </div>
                            <div class="content-shopping">
                                <span class="tipo">@{{ hotel.hotel.class }}</span>
                                <h3 class="text-left">
                                    @{{ hotel.hotel_name }}
                                    <span class="icon-star"></span>
                                    <div class="price">$<b>@{{ hotel.total_hotel }}</b></div>
                                </h3>
                                <div class="date-shopping">
                                    <i class="icon-calendar"></i>
                                    <span>@{{ formatDate(hotel.date_from) }}</span>
                                    <span>@{{ formatDate(hotel.date_to) }}</span>

                                    <div class="total-rooms">

                                        <button type="button" class="collapsed btn btn-secondary">
                                                        <span class="fa fa-circle"
                                                              v-for="room in hotel.rooms"></span>
                                            @{{ hotel.rooms.length }} Hab
                                        </button>


                                    </div>
                                </div>
                            </div>

                        </div>
                        <div :id="'service-content-shopping'+index" class="hotel-content-shopping"
                             v-for="(service,index) in cart.services">
                            <div class="img-shopping">
                                <img v-if="service.service.galleries.length > 0"
                                     :src="service.galleries[0].url"
                                     onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                     class="object-fit_cover backup_picture_service" alt="service">
                                <img class="object-fit_cover backup_picture_service"
                                     src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                     alt="Image Service" v-else>
                            </div>
                            <div class="content-shopping">
                                <h3 class="text-left">
                                    <div style="width: 340px; font-weight: 600; font-size: 1.4rem;">@{{
                                        service.service_name }} - [@{{service.service.code}}]
                                    </div>
                                    <div class="price">$<b>@{{ service.total_service }}</b></div>
                                </h3>
                                <div class="date-shopping">
                                    {{--  Multiservicesañadidos  --}}
                                    <span class="ml-0 mb-2"
                                          v-for="multiservicio in service.service.components">
                                        <span class="ml-0 icon-folder-plus"
                                              v-if="service.service.components.length>0"></span>[@{{ multiservicio.code }}] @{{ multiservicio.descriptions.name }}
                                        <br>
                                    </span>
                                    <div class="mt-2">
                                        <i class="icon-calendar"></i>
                                        <span>@{{ formatDate(service.date_from) }}</span><br>
                                        <i class="icon-map-pin"></i>
                                        <span> @{{ service.service.origin.country }}, @{{ service.service.origin.state }}</span><span
                                            v-if="service.service.origin.city !== null">, @{{ service.service.origin.city }}</span><span
                                            v-if="service.service.origin.zone !== null">, @{{ service.service.origin.zone }}</span>
                                        <i class="icon-arrow-right"></i>
                                        <span> @{{ service.service.destiny.country }}, @{{ service.service.destiny.state }}</span><span
                                            v-if="service.service.destiny.city !== null">, @{{ service.service.destiny.city }}</span><span
                                            v-if="service.service.destiny.zone !== null">, @{{ service.service.destiny.zone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="no-gutters total">
                    <h3>{{ trans('hotel.label.total_pay') }}</h3>
                    <div class="price">USD <b>@{{ cart.total_cart }}</b></div>
                </div>
            </div>
            <a class="btn btn-primary btn-car"
               href="/cart_details/view">{{ trans('hotel.label.go_to_cart') }}</a>
        </div>

    </div>
</div>
