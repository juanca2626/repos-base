<div class="alternativas">
    <a href="/#/" @click.prevent="showAlterna(index,hotel.token_search_frontend,hotel.token_search,hotel.hotel_id,1)">
        {{ trans('cart_view.label.see_alternatives') }}
    </a>
</div>
<div class="container-hotel-alterna" :id="'container_hotel'+index" style="display: none">
    <div class="content-close">
        <div @click.prevent="showAlterna(index)" class="close">{{ trans('hotel.label.Close') }} <i class="fa fa-times"></i></div>
    </div>
    <div class="container-hotel-alterna-list">
        <a class="returList" @click.prevent="returList(hotel.token_search + '_' + hotel.hotel_id)"><i class="icon-arrow-left"></i>
            {{ trans('cart_view.label.return_list') }}
        </a>
        <h4>{{ trans('cart_view.label.search_alternatives') }}</h4>
        <div class="text-options">
            {{ trans('cart_view.label.found_these_options') }} <b>@{{ up_selling.destiny }}</b>
            <span></span> @{{ formatDate(hotel.date_from) }} @{{ formatDate(hotel.date_to) }}
            <span></span> <b>@{{ hotel.quantity_rooms }}</b> {{ trans('hotel.label.rooms') }}
            <span></span> <b>@{{ hotel.quantity_adults }}</b> {{ trans('hotel.label.adults') }}
            <span></span> <b>@{{ hotel.quantity_child }}</b> {{ trans('hotel.label.child') }}
        </div>
        <div :id="hotel.token_search + '_' + hotel.hotel_id" :cart_items_id="hotel.cart_items_id" class="eleccion row-alternativa">
            <div class="image">
                <div class="img"><img :src="hotel.hotel.galleries[0]" alt="Image Hotel" onerror="this.src = baseURL + '/images/room.alter.png'"></div>
                <div class="category" :style="'background-color:'+hotel.hotel.color_class"> @{{
                    hotel.hotel.class }}
                </div>
            </div>
            <div class="descrip">
                <h5>@{{ hotel.hotel.name }} <span></span>
                    <i class="icon-star" v-for="n in parseInt(hotel.hotel.category)"></i>
                </h5>
                <i class="fa fa-circle"></i> <i class="fa fa-circle"></i>
                <span></span> @{{ showNameRooms(hotel.hotel) }}
                <div class="price">
                    {{ trans('cart_view.label.base_price') }} <span>$ <b>@{{ (hotel.hotel.price) }}</b></span>
                </div>
                <a class="btn btn-eleccion">{{ trans('cart_view.label.my_choice') }} </a>
                <a @click.prevent="comparar(hotel.token_search + '_' + hotel.hotel_id)" class="btn btn-comprar" style="display: none">
                    {{ trans('cart_view.label.compare') }} </a>
                <a @click.prevent="cambiar(hotel.token_search + '_' + hotel.hotel_id)" class="btn btn-cambiar" style="display: none"> {{ trans('cart_view.label.change') }} </a>
            </div>
            <div class="rooms-alternativa">
                <div class="item-rooms-alternativa" v-for="(room,index_room) in hotel.rooms">
                    <div class="image-rooms-alternativa">
                        <img :src="room.room.gallery[0]" alt="Image Room" onerror="this.src = baseURL + '/images/alternativa.png'">
                    </div>
                    <div class="text-rooms-alternativa">
                        <h6>@{{ room.room_name }}</h6>
                        <div>
                            <label><b>@{{ room.quantity_adults }}</b> {{ trans('hotel.label.adults') }}</label>
                            <label><b>@{{ room.quantity_child }}</b> {{ trans('hotel.label.child') }}</label>
                            <img src="/images/cama.png">
                            <template v-if="room.rate.meal_id == 1">
                                <img src="/images/tasa.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                                <img src="/images/tenedor.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                                <img src="/images/timbre.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                            </template>
                            <template v-if="room.rate.meal_id == 2">
                                <img src="/images/tasa.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                            </template>
                            <template v-if="room.rate.meal_id == 3">
                                <img src="/images/tasa.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                                <img src="/images/tenedor.png" :alt="room.rate.meal_name"  :title="room.rate.meal_name">
                            </template>

                        </div>
                        <div>@{{ room.rate.political.cancellation.name }}</div>
                        <div>
                            <a class="ok">Ok</a><b class="politicy-name">@{{ room.rate.name }}</b>
                            $<b>@{{ (room.total_room) }}</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div :id="up_selling.token_search + '_' + hotel_alt.id" :upselling-index="upselling_index" class="row-alternativa"
             v-for="(hotel_alt, upselling_index) in up_selling.hotels">
            <div class="image">
                <div class="img"><img :src="hotel_alt.galleries[0]" alt="Image Hotel" onerror="this.src = baseURL + '/images/room.alter.png'"></div>
                <div class="category" :style="'background-color:'+hotel_alt.color_class">
                    @{{ hotel_alt.class }}
                </div>
            </div>
            <div class="descrip">
                <h5>@{{ hotel_alt.name }} <span></span>
                    <i class="icon-star" v-for="n in parseInt(hotel_alt.category)"></i>
                </h5>
                <i class="fa fa-circle"></i> <i class="fa fa-circle"></i>
                @{{ showNameRooms(hotel_alt) }}
                <div class="price" v-if="parseFloat(hotel_alt.price) > parseFloat(hotel.hotel.price)">
                    <span>$ <b>@{{ (showPriceAlternatives(hotel_alt,hotel.hotel.price)) }}</b></span>
                </div>
                <div class="price" v-if="parseFloat(hotel_alt.price) < parseFloat(hotel.hotel.price)">
                    {{ trans('cart_view.label.best_price') }}
                    <span>$ <b>@{{ (showPriceAlternatives(hotel_alt,hotel.hotel.price)) }}</b></span>
                </div>
                <a class="btn btn-eleccion" style="display: none"> {{ trans('cart_view.label.my_choice') }} </a>
                <a @click.prevent="comparar(hotel.token_search + '_' + hotel.hotel_id, up_selling.token_search + '_' + hotel_alt.id)"
                   class="btn btn-comprar"> {{ trans('cart_view.label.compare') }} </a>
                <a @click.prevent="cambiar(hotel.token_search + '_' + hotel.hotel_id, up_selling.token_search + '_' + hotel_alt.id)"
                   class="btn btn-cambiar" style="display: none"> {{ trans('cart_view.label.change') }} </a>
            </div>
            <div class="rooms-alternativa">
                <div class="item-rooms-alternativa"
                     v-for="room_alt in hotel_alt.best_options.rooms">
                    <div class="image-rooms-alternativa">
                        <img :src="room_alt.gallery[0]" alt="Image Room" onerror="this.src = baseURL + '/images/alternativa.png'">
                    </div>
                    <div class="text-rooms-alternativa">
                        <h6>@{{ room_alt.name }}</h6>
                        <div>
                            <label><b>@{{ room_alt.quantity_adults }}</b> {{ trans('hotel.label.adults') }}</label>
                            <label><b>@{{ room_alt.quantity_child }}</b> {{ trans('hotel.label.child') }}</label>
                            <template v-if="room_alt.rates[0].meal_id == 1">
                                <img src="/images/tasa.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                                <img src="/images/tenedor.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                                <img src="/images/timbre.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                            </template>
                            <template v-if="room_alt.rates[0].meal_id == 2">
                                <img src="/images/tasa.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                            </template>
                            <template v-if="room_alt.rates[0].meal_id == 3">
                                <img src="/images/tasa.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                                <img src="/images/tenedor.png" :alt="room_alt.rates[0].meal_name"  :title="room_alt.rates[0].meal_name">
                            </template>
                        </div>
                        <div>@{{ room_alt.rates[0].political.cancellation.name }}</div>
                        <div>
                            <a class="ok">Ok</a>
                            <b class="politicy-name">@{{ room_alt.rates[0].name }}</b>
                            $<b>@{{ (room_alt.total_amount) }}</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="contentBtn">
            <div>
                {{ trans('cart_view.label.remember_change_hotel_will_lose') }} <span>{{ trans('cart_view.label.previously_selected_supplements') }}</span>
            </div>
            <button class="btn-primary" @click.prevent="cambiar_eleccion(hotel.token_search + '_' + hotel.hotel_id , index)">{{ trans('cart_view.label.apply_upGrade') }}</button>
        </div>
    </div>
</div>
