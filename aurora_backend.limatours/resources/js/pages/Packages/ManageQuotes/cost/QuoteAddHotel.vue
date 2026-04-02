<template>

    <div class="container-fluid">

        <div class="col-12" style="padding-left: 0; margin-bottom: 0">
            <button @click="back()" class="btn btn-success" type="button">
                <font-awesome-icon :icon="['fas', 'angle-left']"
                                   style="margin-left: 5px;"/>
                Atrás
            </button>
        </div>

        <div class="b-form-group form-group bottom0">
            <div class="form-row">

                <div class="col-sm-4">
                    <label class="col-form-label">Destino</label>
                    <v-select :options="ubigeos"
                              @input="ubigeoChange"
                              :value="this.ubigeo_id"
                              v-model="ubigeoSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                              autocomplete="true"></v-select>
                </div>

                <div class="col-2">
                    <div class="col-sm-2"><label class="col-form-label">Inicio</label></div>
                    <div class="input-group col-12">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            id="date_from"
                            autocomplete="off"
                            name="date_from" ref="datePickerFrom"
                            v-model="date_from" v-validate="'required'"
                        >
                        </date-picker>

                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"
                                           v-show="errors.has('date_from')"/>
                        <span v-show="errors.has('date_from')">{{ errors.first('date_from') }}</span>
                    </div>
                </div>

                <div class="col-2">
                    <div class="col-sm-2"><label class="col-form-label">Fin</label></div>
                    <div class="input-group col-12">
                        <date-picker
                            :config="datePickerToOptions"
                            id="date_to"
                            name="date_to" ref="datePickerTo"
                            autocomplete="off"
                            v-model="date_to" v-validate="'required'">
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"
                                           v-show="errors.has('date_to')"/>
                        <span v-show="errors.has('date_to')">{{ errors.first('date_to') }}</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <img src="/images/loading.svg" class="right" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;"/>
                    <button @click="search" class="btn btn-success right" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>

            </div>

            <div class="form-row">

                <div class="col-md-12" style="text-align: right; line-height: 42px" v-show="(reEntryServices.length)>0">

                    <label style="margin-top: 6px; margin-right: 6px;">Opciones disponibles para re-ingreso:</label>

                    <div class="col-3 reEntry" v-for="( reEntryS, reKey ) in reEntryServices" v-show="showReEntrys">
                        {{ ubigeo.label }}, día: {{ reEntryS.date }}

                        <c-switch :value="true" class="mx-1" color="success"
                                  v-model="reEntryS.status" @change="useDay(reEntryS, reKey)"
                                  variant="pill">
                        </c-switch>

                    </div>

                    <!--                    <label class="col-form-label">Re-Ingreso-->
                    <!--                        <b-form-checkbox style="margin: 11px 10px 0;"-->
                    <!--                                         :checked="checkboxChecked(reEntry)"-->
                    <!--                                         id="checkbox_re_entry"-->
                    <!--                                         name="checkbox_re_entry"-->
                    <!--                                         switch>-->
                    <!--                        </b-form-checkbox>-->
                    <!--                    </label>-->
                </div>
            </div>

        </div>

        <div class="b-form-group form-group">
            <div class="form-row">
                <b-tabs style="width: 100%;">
                    <b-tab :key="cat.id" :title="cat.category.translations[0].value"
                           ref="tabcategory" @click="searchByParts(cat.type_class_id)"
                           v-for="(cat, catKey) in categories">

                        <div class="col-12 sectionShare" v-show="!(cat.shareMode) && hotelForShare.id>0">
                            <div class="col-12">
                                {{ hotelForShare.date_in | formatDate }} - {{ hotelForShare.date_out | formatDate }} |
                                <strong>
                                        <span v-if="hotelForShare.hotel && hotelForShare.hotel.channel != null">
                                            [{{hotelForShare.hotel.channel[0].code }}]
                                        </span>
                                    <span v-if="hotelForShare.hotel">{{ hotelForShare.hotel.name }}</span>
                                </strong>
                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Cancelar
                                </button>

                                <button @click="shareHere(cat.id)" style="margin-right: 10px"
                                        class="btn btn-sm btn-info right" type="submit">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Compartir aquí
                                </button>
                            </div>
                        </div>

                        <div class="col-12 sectionShare" v-show="!(cat.shareMode) && hotelForShareOptional.id>0">
                            <div class="col-12">
                                {{ hotelForShareOptional.date_in | formatDate }} - {{ hotelForShareOptional.date_out | formatDate }} |
                                <strong>
                                        <span v-if="hotelForShareOptional.hotel && hotelForShareOptional.hotel.channel != null">
                                            [{{hotelForShareOptional.hotel.channel[0].code }}]
                                        </span>
                                    <span v-if="hotelForShareOptional.hotel">{{ hotelForShareOptional.hotel.name }}</span>
                                </strong>
                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Cancelar
                                </button>

                                <button @click="shareHereOptional(cat.id)" style="margin-right: 10px"
                                        class="btn btn-sm btn-info right" type="submit">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Compartir opcional aquí
                                </button>
                            </div>
                        </div>

                        <div class="col-12 sectionShare marginB20" v-show="cat.shareMode">
                            <div class="form-row">
                                <h5>
                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                    Diríjase a la pestaña de la(s) categoría(s) que desea compartir
                                </h5>
                            </div>

                            <div class="col-12" v-if="hotelForShare.id > 0">
                                {{ hotelForShare.date_in | formatDate }} - {{ hotelForShare.date_out | formatDate }} |
                                <strong>
                                        <span v-if="hotelForShare.hotel && hotelForShare.hotel.channel != null">
                                            [{{hotelForShare.hotel.channel[0].code }}]
                                        </span>
                                    <span v-if="hotelForShare.hotel">{{ hotelForShare.hotel.name }}</span>
                                </strong>

                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Cancelar
                                </button>
                            </div>

                            <div class="col-12" v-if="hotelForShareOptional.id > 0">
                                {{ hotelForShareOptional.date_in | formatDate }} - {{ hotelForShareOptional.date_out | formatDate }} |
                                <strong>
                                        <span v-if="hotelForShareOptional.hotel && hotelForShareOptional.hotel.channel != null">
                                            [{{hotelForShareOptional.hotel.channel[0].code }}]
                                        </span>
                                    <span v-if="hotelForShareOptional.hotel">{{ hotelForShareOptional.hotel.name }}</span>
                                </strong>

                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                    Cancelar
                                </button>
                            </div>

                        </div>

                        <div class="col-12 sectionResult marginB20" v-show="!(cat.shareMode)">
                            <div class="form-row">
                                <h5>
                                    <font-awesome-icon :icon="['fas', 'hotel']"/>
                                    Hoteles asignados:
                                </h5>
                            </div>
                            <div class="form-row" v-if="cat.currentHotels.length == 0">
                                <label>Ningún hotel agregado en esta categoría</label>
                            </div>

                            <div :class="'col-12 row2_' + (hkey%2)" v-for="(hotel, hkey) in cat.currentHotels"
                                 v-if="cat.currentHotels.length > 0">
                                <button @click="showModalDelete(hotel.id)" :disabled="loading"
                                        class="btn btn-sm btn-danger"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'trash']"/>
                                </button>
                                {{ hotel.date_in | formatDate }} - {{ hotel.date_out | formatDate }} |
                                <strong>
                                    <span v-if="hotel.hotel.channel != null">[{{hotel.hotel.channel[0].code }}] </span>
                                    {{ hotel.hotel.name }}
                                </strong>
                                <span style="padding: 4px 7px;" class="btn-warning"
                                      v-if="hotel.service_rooms_hyperguest.length == 0 && hotel.service_rooms.length == 0">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"/> Por asignar
                                    </span>

                                <button @click="filterSimilar(hotel)" :disabled="loading"
                                        class="btn btn-sm btn-success right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'search']"/>
                                </button>

                                <button @click="willShare(hotel)" style="margin-right: 10px"
                                        class="btn btn-sm btn-info right" :disabled="loading"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>
                                </button>

                            </div>
                        </div>
<!--                        <div class="col-12 sectionResult marginB20" v-show="!(cat.shareMode)">-->
<!--                            <div class="form-row">-->
<!--                                <h5>-->
<!--                                    <font-awesome-icon :icon="['fas', 'hotel']"/>-->
<!--                                    Hoteles opcionales asignados:-->
<!--                                </h5>-->
<!--                            </div>-->
<!--                            <div class="form-row" v-if="cat.currentHotelOptionals.length === 0">-->
<!--                                <label>Ningún hotel opcional agregado en esta categoría</label>-->
<!--                            </div>-->

<!--                            <div :class="'col-12 row2_' + (hkey%2)" v-for="(hotel, hkey) in cat.currentHotelOptionals"-->
<!--                                 v-if="cat.currentHotelOptionals.length > 0">-->
<!--                                <button @click="showModalOptionalDelete(hotel.id)" :disabled="loading"-->
<!--                                        class="btn btn-sm btn-danger"-->
<!--                                        type="button">-->
<!--                                    <font-awesome-icon :icon="['fas', 'trash']"/>-->
<!--                                </button>-->
<!--                                {{ hotel.date_in | formatDate }} - {{ hotel.date_out | formatDate }} |-->
<!--                                <strong>-->
<!--                                    <span v-if="hotel.hotel.channel != null">[{{hotel.hotel.channel[0].code }}] </span>-->
<!--                                    {{ hotel.hotel.name }}-->
<!--                                </strong>-->
<!--                                <span style="padding: 4px 7px;" class="btn-warning"-->
<!--                                      v-if="hotel.service_rooms.length == 0">-->
<!--                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"/> Por asignar-->
<!--                                    </span>-->

<!--                                <button @click="filterSimilar(hotel)" :disabled="loading"-->
<!--                                        class="btn btn-sm btn-success right"-->
<!--                                        type="button">-->
<!--                                    <font-awesome-icon :icon="['fas', 'search']"/>-->
<!--                                </button>-->

<!--                                <button @click="willShareOptional(hotel)" style="margin-right: 10px"-->
<!--                                        class="btn btn-sm btn-info right" :disabled="loading"-->
<!--                                        type="button">-->
<!--                                    <font-awesome-icon :icon="['fas', 'share-alt']"/>-->
<!--                                </button>-->

<!--                            </div>-->
<!--                        </div>-->

<!--                        <div class="form-row">-->
<!--                            <div class="col-12">-->
<!--                                <img src="/images/loading.svg" class="right" v-if="loading" width="40px"-->
<!--                                     style="float: right; margin-top: 35px;"/>-->
<!--                                <button @click="addOptional()"-->
<!--                                        class="btn btn-success left" type="submit" v-if="!loading"-->
<!--                                        style="">-->
<!--                                    <font-awesome-icon v-if="optional" :icon="['fas', 'check-square']"/>-->
<!--                                    <font-awesome-icon v-if="!optional" :icon="['fas', 'square']"/>-->
<!--                                    Agregar como opcional-->
<!--                                </button>-->
<!--                            </div>-->
<!--                        </div>-->

                        <div class="form-row" v-show="!(cat.shareMode)">
                            <div class="col-12">
                                <img src="/images/loading.svg" class="right" v-if="loading" width="40px"
                                     style="float: right; margin-top: 35px;"/>
                                <button @click="viewHiddens()"
                                        class="btn btn-info right" type="submit" v-if="!loading"
                                        style="">
                                    <font-awesome-icon v-if="viewHiddenscheck" :icon="['fas', 'check-square']"/>
                                    <font-awesome-icon v-if="!viewHiddenscheck" :icon="['fas', 'square']"/>
                                    Ver vacíos
                                </button>
                            </div>
                        </div>


                        <div class="col-12 sectionResult" v-show="!(cat.shareMode)">
                            <!-- Loading unificado para ambas búsquedas -->
                            <div v-if="loading" class="unified-loading">
                                <div class="loading-content">
                                    <img src="/images/loading.svg" width="50px" alt="Cargando..."/>
                                    <p class="loading-message">Procesando...</p>
                                </div>
                            </div>

                            <div v-else>
                                <!-- Hoteles unificados (Aurora + Hyperguest) -->
                                <div v-if="cat.hotels.length > 0">
                                    <div :class="'col-12 row_' + (hkey % 2)" v-for="(hotel, hkey) in cat.hotels"
                                         v-if="(cat.hotels.length > 0 && viewHiddenscheck) ||
                                                    (cat.hotels.length > 0 && !(viewHiddenscheck) && hotel.countRates>0)">
                                        <div>
                                            <div class="accordion" @click="showContentHotel(hotel)">
                                                <h5 style="padding: 7px 7px 0;" class=" d-flex justify-content-between">
                                                    <strong>
                                                        <font-awesome-icon :icon="['fas', 'hotel']"/>
                                                        {{ hotel.name }} ({{ hotel.id }})
                                                    </strong>
                                                    <font-awesome-icon v-if="!(hotel.viewContent) && hotel.rooms.length>0"
                                                                    class="right" :icon="['fas', 'plus']"/>
                                                    <font-awesome-icon v-if="hotel.viewContent && hotel.rooms.length>0"
                                                                    class="right" :icon="['fas', 'minus']"/>
                                                </h5>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-12" v-show="hotel.viewContent">
                                            <!-- Rooms de Aurora (hyperguest_pull = false) -->
                                            <div v-for="(room, rkey) in hotel.rooms"
                                                 v-if="(room.hyperguest_pull === false || room.hyperguest_pull === 0) &&
                                                       ((room.rates_plan_room && room.rates_plan_room.length>0 && !(viewHiddenscheck) && room.countCalendars>0) || viewHiddenscheck)">
                                                <div class="rooms-table row canSelectText">
                                                    <div class="col-4 my-auto">
                                                        <strong>Nombre {{ hotel.id }}: </strong>{{ room.translations && room.translations[0] ? room.translations[0].value : '' }}<br>
                                                        <strong>Descripción: </strong>{{ room.translations && room.translations[1] ? room.translations[1].value : '' }}
                                                        <br>
                                                        <strong v-if="room.room_type && room.room_type.occupation">Ocupación: </strong>{{ room.room_type.occupation }}
                                                    </div>
                                                    <div class="col-8 my-auto">
                                                        <div
                                                            v-for="(rate, raKey) in room.rates_plan_room"
                                                            :class="'col-12 rateRow rateChoosed_' + (isHyperguestPullRate(rate) ? hyperguestCheckboxs[hotel.id + '_' + (rate.ratePlanId || rate.id) + '_' + (room.room_id || room.id)] : checkboxs[hotel.id + '_' + rate.id])"
                                                            v-if="rate.calendarys && rate.calendarys.length > 0"
                                                        >
                                                            <!-- Caso HYPERGUEST (por tarifa: channel_id=6, type=2) -->
                                                            <template v-if="isHyperguestPullRate(rate)">
                                                                <label
                                                                    style="display: block;"
                                                                    :for="'checkbox_' + hotel.id + '_' + (rate.ratePlanId || rate.id) + '_' + (room.room_id || room.id)"
                                                                >
                                                                    <strong>
                                                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                                                        <span v-if="rate != null">{{ rate.name || (rate.rate_plan ? rate.rate_plan.name : '') }}:</span>
                                                                        <span v-else>---</span>
                                                                        <span
                                                                            class="badge"
                                                                            :class="getRateBadgeClass(rate)"
                                                                            style="margin-right:5px;"
                                                                        >
                                                                            {{ getRateBadgeLabel(rate) }}
                                                                        </span>
                                                                    </strong>
                                                                    <b-form-checkbox
                                                                        style="float: right;"
                                                                        :id="'checkbox_' + hotel.id + '_' + (rate.ratePlanId || rate.id) + '_' + (room.room_id || room.id)"
                                                                        :name="'checkbox_' + hotel.id + '_' + (rate.ratePlanId || rate.id) + '_' + (room.room_id || room.id)"
                                                                        v-model="hyperguestCheckboxs[hotel.id + '_' + (rate.ratePlanId || rate.id) + '_' + (room.room_id || room.id)]"
                                                                        @change="selectHyperguestRate(catKey, hotel, room, rate)"
                                                                        :disabled="loading"
                                                                    />
                                                                </label>
                                                                <div style="margin-left: 30px;">
                                                                    <span v-if="rate.calendarys[0].status == 1">
                                                                        <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                                                    </span>
                                                                    <span v-else>
                                                                        <font-awesome-icon :icon="['fas', 'times-circle']"/>
                                                                    </span>
                                                                    {{ rate.calendarys[0].date | formatDate }}
                                                                    <strong>
                                                                        $ <span v-if="rate.calendarys[0].rate && rate.calendarys[0].rate[0]">
                                                                            {{ rateProcess(rate.calendarys[0].rate, room.max_adults, rate.channel_id) | formatPrice }}
                                                                        </span>
                                                                    </strong>
                                                                    <span v-if="rate.calendarys.length > 1">
                                                                        <a href="javascript:;" v-show="!rate.showAllRates" @click="toggleViewRates(rate)">
                                                                            <font-awesome-icon :icon="['fas', 'plus']"/>
                                                                        </a>
                                                                        <a href="javascript:;" v-show="rate.showAllRates" @click="toggleViewRates(rate)">
                                                                            <font-awesome-icon :icon="['fas', 'minus']"/>
                                                                        </a>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    style="margin-left: 30px;"
                                                                    v-for="(calendar, cKey) in rate.calendarys"
                                                                    v-show="rate.showAllRates"
                                                                >
                                                                    <span v-if="cKey > 0">
                                                                        <span v-if="calendar.status == 1">
                                                                            <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                                                        </span>
                                                                        <span v-else>
                                                                            <font-awesome-icon :icon="['fas', 'times-circle']"/>
                                                                        </span>
                                                                        {{ calendar.date | formatDate }}
                                                                        <strong>
                                                                            $ <span v-if="calendar.rate && calendar.rate[0]">
                                                                                {{ rateProcess(calendar.rate, room.max_adults, rate.channel_id) | formatPrice }}
                                                                            </span>
                                                                        </strong>
                                                                    </span>
                                                                </div>
                                                            </template>

                                                            <!-- Caso AURORA (todas las demás tarifas) -->
                                                            <template v-else>
                                                                <label
                                                                    style="display: block;"
                                                                    :for="'checkbox_' + hotel.id + '_' + rate.id"
                                                                >
                                                                    <strong>
                                                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                                                        <span v-if="rate.rate_plan != null">{{ rate.rate_plan.name }}:</span>
                                                                        <span v-else>---</span>
                                                                        <span
                                                                            class="badge"
                                                                            :class="getRateBadgeClass(rate)"
                                                                            style="margin-right:5px;"
                                                                        >
                                                                            {{ getRateBadgeLabel(rate) }}
                                                                        </span>
                                                                    </strong>
                                                                    <b-form-checkbox
                                                                        style="float: right;"
                                                                        :id="'checkbox_' + hotel.id + '_' + rate.id"
                                                                        :name="'checkbox_' + hotel.id + '_' + rate.id"
                                                                        v-model="checkboxs[hotel.id + '_' + rate.id]"
                                                                        @change="chooseRoom(catKey, rate, hotel.id, rate.id)"
                                                                        :disabled="loading"
                                                                    />
                                                                </label>
                                                                <div style="margin-left: 30px;">
                                                                    <span v-if="rate.calendarys[0].status == 1">
                                                                        <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                                                    </span>
                                                                    <span v-else>
                                                                        <font-awesome-icon :icon="['fas', 'times-circle']"/>
                                                                    </span>
                                                                    {{ rate.calendarys[0].date | formatDate }}
                                                                    <strong>
                                                                        $ <span v-if="rate.calendarys[0].rate && rate.calendarys[0].rate[0]">
                                                                            {{ rateProcess(rate.calendarys[0].rate, room.max_adults, rate.channel_id) | formatPrice }}
                                                                        </span>
                                                                    </strong>
                                                                    <span v-if="rate.calendarys.length > 1">
                                                                        <a href="javascript:;" v-show="!rate.showAllRates" @click="toggleViewRates(rate)">
                                                                            <font-awesome-icon :icon="['fas', 'plus']"/>
                                                                        </a>
                                                                        <a href="javascript:;" v-show="rate.showAllRates" @click="toggleViewRates(rate)">
                                                                            <font-awesome-icon :icon="['fas', 'minus']"/>
                                                                        </a>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    style="margin-left: 30px;"
                                                                    v-for="(calendar, cKey) in rate.calendarys"
                                                                    v-show="rate.showAllRates"
                                                                >
                                                                    <span v-if="cKey > 0">
                                                                        <span v-if="calendar.status == 1">
                                                                            <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                                                        </span>
                                                                        <span v-else>
                                                                            <font-awesome-icon :icon="['fas', 'times-circle']"/>
                                                                        </span>
                                                                        {{ calendar.date | formatDate }}
                                                                        <strong>
                                                                            $ <span v-if="calendar.rate && calendar.rate[0]">
                                                                                {{ rateProcess(calendar.rate, room.max_adults, rate.channel_id) | formatPrice }}
                                                                            </span>
                                                                        </strong>
                                                                    </span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr v-if="(rkey + 1 ) < hotel.rooms.length || (room.hyperguest_pull === true || room.hyperguest_pull === 1)">
                                            </div>

                                            <!-- Rooms de Hyperguest (hyperguest_pull = true) -->
                                            <div v-for="(room, rkey) in hotel.rooms"
                                                 v-if="(room.hyperguest_pull === true || room.hyperguest_pull === 1) &&
                                                       (room.rates && room.rates.length > 0)">
                                                <div class="rooms-table row canSelectText">
                                                    <div class="col-4 my-auto">
                                                        <strong>Nombre {{ hotel.id }}: </strong>{{ room.name || (room.translations && room.translations[0] ? room.translations[0].value : '') }}<br>
                                                        <strong>Descripción: </strong>{{ room.name || (room.translations && room.translations[1] ? room.translations[1].value : '') }}
                                                        <br v-if="room.room_type && room.room_type.occupation">
                                                        <strong v-if="room.room_type && room.room_type.occupation">Ocupación: </strong>{{ room.room_type.occupation }}
                                                    </div>
                                                    <div class="col-8 my-auto">
                                                        <div v-for="(rate, raKey) in room.rates"
                                                             :class="'col-12 rateRow rateChoosed_' + hyperguestCheckboxs[hotel.id + '_'+ rate.ratePlanId +'_'+ (room.room_id || room.id)]"
                                                             v-if="rate.rate && rate.rate.length > 0">
                                                            <label style="display: block;" :for="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ (room.room_id || room.id)">
                                                                <strong>
                                                                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                                                    <span v-if="rate!=null">{{ rate.name }}:</span>
                                                                    <span v-if="rate==null">---</span>
                                                                    <span
                                                                        class="badge"
                                                                        :class="getRateBadgeClass(rate)"
                                                                        style="margin-right:5px;"
                                                                    >
                                                                        {{ getRateBadgeLabel(rate) }}
                                                                    </span>
                                                                </strong>
                                                                <b-form-checkbox
                                                                    style="float: right;"
                                                                    :id="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ (room.room_id || room.id)"
                                                                    :name="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ (room.room_id || room.id)"
                                                                    v-model="hyperguestCheckboxs[hotel.id + '_'+ rate.ratePlanId +'_'+ (room.room_id || room.id)]"
                                                                    @change="selectHyperguestRate(catKey, hotel , room, rate)"
                                                                    :disabled="loading"
                                                                    >
                                                                </b-form-checkbox>
                                                            </label>
                                                            <div style="margin-left: 30px;">
                                                                <span v-if="rate.available > 0"><font-awesome-icon
                                                                    :icon="['fas', 'check-circle']"/></span>
                                                                <span v-if="rate.available <= 0"><font-awesome-icon
                                                                    :icon="['fas', 'times-circle']"/></span>
                                                                <span v-if="rate.amount_days && rate.amount_days[0]">{{ rate.amount_days[0].date | formatDate }}</span>
                                                                <strong>$ {{ getHyperguestBestPrice(rate) | formatPrice }} </strong>
                                                                <span v-if="rate.amount_days && rate.amount_days.length > 1">
                                                                    <a href="javascript:;" v-show="!(rate.showAllRates)"
                                                                        @click="toggleHyperguestViewRates(rate)"><font-awesome-icon
                                                                        :icon="['fas', 'plus']"/></a>
                                                                    <a href="javascript:;" v-show="rate.showAllRates"
                                                                        @click="toggleHyperguestViewRates(rate)"><font-awesome-icon
                                                                        :icon="['fas', 'minus']"/></a>
                                                                </span>
                                                            </div>
                                                            <div style="margin-left: 30px;" v-for="(amount_day, aKey) in rate.amount_days" v-show="rate.showAllRates">
                                                                <span v-if="aKey > 0">
                                                                    <span><font-awesome-icon
                                                                        :icon="['fas', 'check-circle']"/></span>
                                                                    {{ amount_day.date | formatDate }}
                                                                    <strong>$ <span v-if="amount_day.total_adult_base"> {{ amount_day.total_adult_base | formatPrice }} </span></strong>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr v-if="(rkey + 1 ) < hotel.rooms.length">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sin resultados -->
                                <div class="form-row" v-if="cat.hotels.length == 0">
                                    <label>Ningún resultado en esta categoría</label>
                                </div>
                            </div>

                        </div>

                    </b-tab>
                </b-tabs>
            </div>
        </div>

        <div class="col-12" style="padding-left: 0;">
            <button @click="back()" class="btn btn-success" type="button">
                <font-awesome-icon :icon="['fas', 'angle-left']"
                                   style="margin-left: 5px;"/>
                Atrás
            </button>
        </div>

        <b-modal :title="modalDeleteName" centered ref="my-modal-delete" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="removeHotel()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="modalDeleteName" centered ref="my-modal-optional-delete" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="removeHotelOptional()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModalOptional()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>

</template>

<script>
    import { API } from '../../../../api'
    import { Switch as cSwitch } from '@coreui/vue'
    import BTab from 'bootstrap-vue/es/components/tabs/tab'
    import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
    import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import Loading from 'vue-loading-overlay'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import HyperguestListHotelSearch from './Hyperguest/ListHotelSearch.vue'

    export default {
        components: {
            BModal,
            Loading,
            BTabs,
            BTab,
            cSwitch,
            VueBootstrapTypeahead,
            BFormCheckbox,
            BFormCheckboxGroup,
            BInputNumber,
            vSelect,
            datePicker,
            HyperguestListHotelSearch
        },
        data: () => {
            return {
                loading: false,
                optional: false,
                hotels: [],
                reEntryServices: [],
                reEntryServicesDates: [],
                checkboxs: [],
                categories: [],
                tmpubigeos: [],
                hotelForShare: [],
                hotelForShareOptional: [],
                ubigeos: [],
                ubigeo: null,
                ubigeoSelected: [],
                plan_rate_id: '',
                ubigeo_id: '',
                date_from: '',
                date_to: '',
                paxSgl: '',
                paxDbl: '',
                paxTpl: '',
                reEntry: true,
                viewHiddenscheck: false,
                showReEntrys: true,
                doSearchReEntrys: true,
                package_service_id: null,
                package_service_optional_id: null,
                modalDeleteName: '',
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                tabCategoryActiveId: '',
                hyperguestData: null,
                deleteItemsHyperguest: null,
                hyperguestHotels: [],
                hyperguestCheckboxs: [],
                hyperguestSelectedRates: {},
                categoryActive: null,
            }
        },
        mounted: function () {

            this.$i18n.locale = localStorage.getItem('lang')

            API.get('/package/plan_rates/' + this.plan_rate_id + '?lang=' + localStorage.getItem('lang')).then((result) => {
                result.data.data.plan_rate_categories.forEach(oCategs => {
                    if (oCategs.category.code != 'X') {
                        this.categories.push(oCategs)
                    }
                })
                this.categories.forEach(c => {
                    c.hotels = []
                    c.currentHotels = []
                    c.currentHotelOptionals = []
                    c.shareMode = false
                })
                this.setCurrentHotels()
            }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                    text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                })
            })

        },
        created () {
            this.plan_rate_id = this.$route.params.package_plan_rate_id
            this.category_id = this.$route.params.category_id
            this.categoryActive = { id: this.category_id };
            console.log("categoryActive", this.categoryActive);
            this.loadubigeo()
        },
        methods: {
            searchByParts (type_class_id) {
                this.tabCategoryActiveId = type_class_id
                this.categoryActive = this.categories.find(c => c.type_class_id === type_class_id);

                if (!(this.ubigeo)) {
                    return
                }

                if (this.date_from == '') {
                    return
                }
                this.search()
            },
            showContentHotel (hotel) {
                this.loading = true

                this.categories.forEach(c => {
                    c.hotels.forEach(h => {
                        if (hotel.id == h.id) {
                            h.viewContent = !(h.viewContent)
                        } else {
                            h.viewContent = false
                        }
                    })
                })

                this.loading = false
            },
            viewHiddens () {
                this.loading = true
                this.viewHiddenscheck = !(this.viewHiddenscheck)
                this.loading = false
            },
            hideModal () {
                this.$refs['my-modal-delete'].hide()
            },
            hideModalOptional () {
                this.$refs['my-modal-optional-delete'].hide()
            },
            showModalDelete (package_service_id) {
                this.package_service_id = package_service_id
                this.modalDeleteName = 'Hotel n°: ' + this.package_service_id
                this.$refs['my-modal-delete'].show()
            },
            showModalOptionalDelete (package_service_id) {
                this.package_service_optional_id = package_service_id
                this.modalDeleteName = 'Hotel n°: ' + this.package_service_optional_id
                this.$refs['my-modal-optional-delete'].show()
            },
            removeHotel () {
                API.delete('/package/service/' + this.package_service_id).then((result) => {
                    if (result.data.success === true) {
                        this.setCurrentHotels()
                        this.hideModal()
                        this.updateRates()
                        this.updateDestinations()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al eliminar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            removeHotelOptional () {
                API.delete('/package/service/optional/' + this.package_service_optional_id).then((result) => {
                    if (result.data.success === true) {
                        this.setCurrentHotels()
                        this.hideModalOptional()
                        this.updateRates()
                        this.updateDestinations()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al eliminar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            willShare (h) {
                this.hotelForShare = h
                this.categories.forEach(c => {
                    if (c.id == h.package_plan_rate_category_id) {
                        c.shareMode = true
                    } else {
                        c.shareMode = false
                    }
                })
            },
            willShareOptional (h) {
                this.hotelForShareOptional = h
                this.categories.forEach(c => {
                    if (c.id == h.package_plan_rate_category_id) {
                        c.shareMode = true
                    } else {
                        c.shareMode = false
                    }
                })
            },
            cancelShare () {
                this.hotelForShare = []
                this.hotelForShareOptional = []
                this.categories.forEach(c => {
                    c.shareMode = false
                })
            },
            shareHereOptional (category_id) {
                this.loading = true
                let data = {
                    category_id: category_id,
                    package_service_id: this.hotelForShareOptional.id
                }
                API.post('/package/package_plan_rate_category/hotel/share/optional', data).then((result) => {
                    console.log(result)
                    if (!(result.data.success)) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                            text: result.data.text
                        })
                        this.loading = false
                    } else {
                        this.setCurrentHotels()
                    }

                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                        text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                    })
                })
            },
            shareHere (category_id) {
                this.loading = true
                let data = {
                    category_id: category_id,
                    package_service_id: this.hotelForShare.id
                }
                API.post('/package/package_plan_rate_category/hotel/share', data).then((result) => {
                    console.log(result)
                    if (!(result.data.success)) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                            text: result.data.text
                        })
                        this.loading = false
                    } else {
                        this.setCurrentHotels()
                    }

                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                        text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                    })
                })
            },
            filterSimilar (h) {
                this.date_from = this.formatDate(h.date_in)
                this.date_to = this.formatDate(h.date_out)
                let _ubigeo = {
                    code: h.hotel.country.iso + ',' + h.hotel.state.iso + ',' + h.hotel.city_id,
                    label: h.hotel.country.translations[0].value + ',' + h.hotel.state.translations[0].value + ',' +
                        h.hotel.city.translations[0].value
                }
                console.log(h.hotel)
                this.ubigeo = _ubigeo
                this.ubigeoSelected = _ubigeo
                // this.ubigeoChange()
                this.search()
            },
            setCurrentHotels () {
                this.loading = true
                let package_plan_rate_categories_ids = []
                this.categories.forEach(pCategs => {
                    package_plan_rate_categories_ids.push(pCategs.id)
                })
                let data = {
                    plan_rate_categories: package_plan_rate_categories_ids
                }
                API.post('/package/package_plan_rate_category/hotel/searchByCategories', data).then((result) => {

                    this.categories.forEach(c => {
                        c.currentHotels = []
                        c.currentHotelOptionals = []
                        result.data.data.services.forEach(h => {
                            if (h.plan_rate_category.id == c.id) {
                                c.currentHotels.push(h)
                            }
                        })
                        result.data.data.optional.forEach(h => {
                            if (h.plan_rate_category.id == c.id) {
                                c.currentHotelOptionals.push(h)
                            }
                        })
                    })

                    console.log("array de categorias", this.categories);
                    this.loading = false;
                    // Inicializar checkboxes de hyperguest para hoteles existentes
                    this.initializeHyperguestExistingCheckboxes();
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packagesmanagepackagetexts.error.messages.name'),
                        text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
                    })
                })
            },
            rateProcess(rates, occupation, channel){
                if(channel == '1'){
                    return rates[0].price_adult;
                }else{
                    let rateValue = 0;
                    rates.forEach(rate => {
                        if(rate.num_adult == occupation){
                            rateValue = rate.price_adult
                        }
                    })
                    if(rateValue == 0){
                        rateValue = rates[0].price_total>0 ? rates[0].price_total: rates[0].price_adult ;
                    }
                    return rateValue;
                }
            },
            // Determina si una tarifa es Hyperguest pull según la propia tarifa
            isHyperguestPullRate (rate) {
                if (!rate) return false
                return (
                    rate.channel_id == 6 &&
                    (rate.channel_type == 2 || rate.type_channel == 2)
                )
            },
            // Determina si una tarifa es Hyperguest push (mismo canal, otro type)
            isHyperguestPushRate (rate) {
                if (!rate) return false
                return (
                    rate.channel_id == 6 &&
                    (rate.channel_type == 1 || rate.type_channel == 1)
                )
            },
            // Clase de badge según tipo de tarifa
            getRateBadgeClass (rate) {
                if (this.isHyperguestPullRate(rate)) {
                    return 'badge badge-success'
                }
                if (this.isHyperguestPushRate(rate)) {
                    return 'badge badge-info'
                }
                return 'badge badge-danger'
            },
            // Texto de badge según tipo de tarifa
            getRateBadgeLabel (rate) {
                if (this.isHyperguestPullRate(rate)) {
                    return 'Hyperguest Pull'
                }
                if (this.isHyperguestPushRate(rate)) {
                    return 'Hyperguest Push'
                }
                return 'Aurora'
            },
            toggleViewRates (rate) {
                this.loading = true
                rate.showAllRates = !(rate.showAllRates)
                this.loading = false
            },
            checkboxChecked: function (status) {
                return !!status
            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            loadubigeo () {
                API.get(window.origin + '/services/hotels/quotes/destinations?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        this.ubigeos = result.data
                    }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.error.messages.name'),
                        text: this.$t('hotels.error.messages.connection_error')
                    })
                })
            },

            ubigeoChange: function (value) {
                this.ubigeo = value
                if (this.ubigeo != null) {
                    if (this.ubigeo_id != this.ubigeo.code) {
                    }
                    this.ubigeo_id = this.ubigeo.code
                } else {
                    this.ubigeo_id = ''
                }
            },

            getUnique (arr, comp) {
                //store the comparison  values in array
                const unique = arr.map(e => e[comp]).// store the keys of the unique objects
                    map((e, i, final) => final.indexOf(e) === i && i)
                    // eliminate the dead keys & return unique objects
                    .filter((e) => arr[e]).map(e => arr[e])
                return unique
            },

            convertDate: function (_date, charFrom, charTo) {
                _date = _date.split(charFrom)
                _date = _date[2] + charTo + _date[1] + charTo + _date[0]
                return _date
            },

            search () {
                this.loading = true

                let type_classes = []
                if (this.tabCategoryActiveId == '') {
                    type_classes.push(this.categories[0].type_class_id)
                } else {
                    type_classes.push(this.tabCategoryActiveId)
                }

                if (!(this.ubigeo)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: 'Debe seleccionar una ciudad de destino'
                    })
                    this.loading = false
                    return
                }

                if (this.date_from == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: 'Debe ingresar una fecha de inicio'
                    })
                    this.loading = false
                    return
                }

                if (this.date_to == '') {
                    this.date_to = this.date_from
                }

                let data = {
                    'type_classes': type_classes,
                    'destiny': this.ubigeo,
                    'date_from': this.convertDate(this.date_from, '/', '-'),
                    'date_to': this.convertDate(this.date_to, '/', '-')
                }
                console.log(data)
                API({
                    method: 'POST',
                    url: window.origin + '/services/hotels/services',
                    data: data
                })
                    .then((result) => {
                        console.log(result);
                        if (result.data.success) {
                            // Limpiar arrays de hoteles antes de procesar nuevos resultados
                            this.hotels = [];
                            this.hyperguestHotels = [];

                            // Limpiar hoteles de todas las categorías
                            this.categories.forEach(c => {
                                c.hotels = [];
                            });

                            // Procesar todos los hoteles unificados (ya vienen con rooms mezcladas)
                            const allHotels = result.data.data;

                            // Procesar y normalizar todas las rooms según su hyperguest_pull
                            this.hotels = this.processUnifiedHotels(allHotels);

                            this.hotelsInTabs();
                            this.editServiceRooms();

                            // Inicializar checkboxes de Hyperguest
                            this.initializeHyperguestCheckboxes();

                            this.loading = false;
                        }else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: result.data.message
                            })
                            this.loading = false;
                        }
                    }).catch((e) => {
                        this.loading = false
                        console.log(e)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Paquetes - Cotizador',
                            text: this.$t('global.error.messages.connection_error')
                        })
                })
            },

            hotelsInTabs: function () {
                this.categories.forEach(c => {
                    c.hotels = []
                    this.hotels.forEach(h => {
                        h.viewContent = false
                        if (h.typeclass_id == c.type_class_id) {

                            h.countRates = 0
                            // Ordenar rooms por ocupación (room_type.occupation) ascendente
                            if (h.rooms && h.rooms.length > 0) {
                                h.rooms.sort((a, b) => {
                                    const occA = a.room_type && a.room_type.occupation ? parseInt(a.room_type.occupation) : 0
                                    const occB = b.room_type && b.room_type.occupation ? parseInt(b.room_type.occupation) : 0
                                    return occA - occB
                                })
                            }

                            for (let r = 0; r < h.rooms.length; r++) {
                                h.rooms[r].countCalendars = 0

                                // Procesar rooms de Aurora (rates_plan_room)
                                if (h.rooms[r].rates_plan_room && h.rooms[r].rates_plan_room.length > 0) {
                                    for (let r_p_r = 0; r_p_r < h.rooms[r].rates_plan_room.length; r_p_r++) {
                                        if (typeof (h.rooms[r].rates_plan_room[r_p_r].showAllRates) === 'undefined') {
                                            h.rooms[r].rates_plan_room[r_p_r].showAllRates = 0
                                        }
                                        h.countRates++
                                        for (let r_p_r_c = 0; r_p_r_c < h.rooms[r].rates_plan_room[r_p_r].calendarys.length; r_p_r_c++) {
                                            h.rooms[r].countCalendars++
                                        }
                                    }
                                }

                                // Procesar rooms de Hyperguest (rates)
                                if (h.rooms[r].rates && h.rooms[r].rates.length > 0) {
                                    for (let r_h = 0; r_h < h.rooms[r].rates.length; r_h++) {
                                        if (typeof (h.rooms[r].rates[r_h].showAllRates) === 'undefined') {
                                            h.rooms[r].rates[r_h].showAllRates = false
                                        }
                                        h.countRates++
                                    }
                                }
                            }

                            c.hotels.push(h)
                        }
                    })
                })
            },

            processUnifiedHotels: function (allHotels) {
                return allHotels.map(hotel => {
                    // Normalizar las rooms para que todas tengan la misma estructura
                    const normalizedRooms = hotel.rooms ? hotel.rooms.map(room => {
                        const normalizedRoom = {
                            ...room,
                            // Mantener ambas estructuras según el tipo
                            rates_plan_room: room.hyperguest_pull === true || room.hyperguest_pull === 1
                                ? []
                                : (room.rates_plan_room || []),
                            rates: room.hyperguest_pull === true || room.hyperguest_pull === 1
                                ? (room.rates || [])
                                : []
                        };

                        // Asegurar que showAllRates esté inicializado en rates de Hyperguest
                        if (normalizedRoom.rates && normalizedRoom.rates.length > 0) {
                            normalizedRoom.rates = normalizedRoom.rates.map(rate => ({
                                ...rate,
                                showAllRates: rate.showAllRates || false
                            }));
                        }

                        return normalizedRoom;
                    }) : [];

                    return {
                        ...hotel,
                        rooms: normalizedRooms,
                        viewContent: false
                    };
                });
            },

            useDay: function (reEntryS, rKey) {

                if (reEntryS.countUse) {
                    this.showReEntrys = false

                    this.reEntryServices.forEach((rEnServ, rEnKey) => {
                        if (rEnKey != rKey) {
                            this.reEntryServices[rEnKey].status = false
                        }
                    })
                    this.showReEntrys = true

                    let _date_in = this.convertDate(this.date_from, '/', '-')
                    let _date_to = this.convertDate(this.date_to, '/', '-')
                    let _city = this.ubigeo.code
                    _city = _city.split(',')
                    let _country_id = _city[0]
                    let _state_id = _city[1]
                    let data = {
                        action: this.reEntryServices[rKey].status,
                        plan_rate_categories: [],
                        date_in: _date_in,
                        date_out: _date_to,
                        state_id: _state_id,
                        country_id: _country_id,
                        single: this.paxSgl,
                        double: this.paxDbl,
                        triple: this.paxTpl,
                        rooms: reEntryS
                    }
                    this.categories.forEach(c => {
                        data.plan_rate_categories.push(c.id)
                    })

                    API({
                        method: 'POST',
                        url: '/package/package_plan_rate_category/updateRates',
                        data: data
                    })
                        .then((result) => {
                            console.log(result)
                            this.doSearchReEntrys = false
                            this.search()
                        }).catch((e) => {
                        console.log(e)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Paquetes - Cotizador',
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })

                } else {
                    reEntryS.countUse = 0
                }
                reEntryS.countUse++
            },

            editServiceRooms: function () {

                this.checkboxs = []
                let date_in = this.convertDate(this.date_from, '/', '-')
                let _city = this.ubigeo.code
                _city = _city.split(',')
                let _country_id = _city[0]
                let _state_id = _city[1]
                let data = {
                    plan_rate_categories: []
                }
                this.categories.forEach(c => {
                    data.plan_rate_categories.push(c.id)
                })

                API({
                    method: 'POST',
                    url: '/package/package_plan_rate_category/hotel/searchByCategories',
                    data: data
                })
                    .then((result) => {

                        // Despues separar los que sean fechas menores a al actual date_in, misma ciudad y agrupar por fechas en botones
                        // Este boton que acciona, debe mandar un arreglo de los rooms involucrados y identificar el package_service nuevo id (o existente a guardar)
                        let tmpServicesReEntry = []
                        result.data.data.services.forEach(service => {
                            if (service.date_in == date_in) {
                                service.service_rooms_hyperguest.forEach(r => {
                                    this.$set(this.hyperguestCheckboxs, service.hotel.id + '_' + r.rate_plan_id + '_' + r.room_id, true);
                                })

                                service.service_rooms.forEach(r => {
                                    this.checkboxs[service.hotel.id + '_' + r.rate_plan_room_id] = true
                                })
                            }

                            if (Date.parse(service.date_in) < Date.parse(date_in) &&
                                service.hotel.country_id == _country_id &&
                                service.hotel.state_id == _state_id) {
                                tmpServicesReEntry.push(service)
                            }
                        })
                        console.log(this.hyperguestCheckboxs);
                        console.log(this.checkboxs)

                        if (this.doSearchReEntrys) {

                            this.reEntryServices = []
                            this.reEntryServicesDates = []
                            let tmpDates = []
                            tmpServicesReEntry.forEach(tmpS => {
                                if (!(this.reEntryServicesDates[tmpS.date_in])) {
                                    tmpDates.push(tmpS.date_in)
                                    this.reEntryServicesDates[tmpS.date_in] = 1
                                }
                            })

                            let n = 0
                            tmpDates.forEach(tmpDate => {
                                this.reEntryServices[n] = {
                                    date: tmpDate,
                                    status: false,
                                    rooms: []
                                }
                                tmpServicesReEntry.forEach(tmpS => {
                                    if (tmpS.date_in == tmpDate) {
                                        tmpS.service_rooms.forEach(room => {
                                            this.reEntryServices[n].rooms.push(room)
                                        })
                                    }
                                })
                                n++
                            })
                            if (this.reEntryServices.length > 0) {
                                this.showReEntrys = true
                            }
                        } else {
                            this.doSearchReEntrys = true
                        }

                        this.loading = false

                    }).catch((e) => {
                    console.log(e)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes - Cotizador',
                        text: this.$t('global.error.messages.connection_error')
                    })
                })

            },

            chooseRoom: function (catKey, rates_plan_room, hotel_id, rate_id) {
                this.loading = true

                let method = 'DELETE'
                let data = {
                    hotel_id: hotel_id,
                    date_in: this.convertDate(this.date_from, '/', '-'),
                    rate_plan_room_id: rates_plan_room.id
                }

                console.log("checkboxs", this.checkboxs[hotel_id + '_' + rate_id]);
                if (!(this.checkboxs[hotel_id + '_' + rate_id])) {
                    console.log(this.checkboxs)
                    method = 'POST'
                    data.date_out = this.convertDate(this.date_to, '/', '-')
                    data.re_entry = (this.reEntry) ? 1 : 0
                    this.checkboxs[hotel_id + '_' + rate_id] = true;
                }else {
                    this.checkboxs[hotel_id + '_' + rate_id] = false;
                }

                let url = 'package/package_plan_rate_category/' + this.categories[catKey].id + '/hotel/room'
                if(this.optional){
                    url = 'package/package_plan_rate_category_optional/' + this.categories[catKey].id + '/hotel/room'
                }
                API({
                    method: method,
                    url: url,
                    data: data
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            if (method === 'POST') {
                                result.data.delete_rooms.forEach(r => {
                                    if(r.hyperguest){
                                        this.$set(this.hyperguestCheckboxs, r.hotel_id + '_' + r.rate_plan_id + '_' + r.room_id, false);
                                    }else{
                                        this.checkboxs[r.hotel_id + '_' + r.rate_plan_room_id] = false;
                                    }
                                });
                            }
                            this.updateRates()
                            this.updateDestinations()
                            this.setCurrentHotels()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Paquetes - Cotizador',
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            },

            // Inicializar checkboxes de Hyperguest en la estructura unificada
            initializeHyperguestCheckboxes() {
                // Inicializar checkboxes para todas las rooms de Hyperguest en hoteles unificados
                this.categories.forEach(cat => {
                    cat.hotels.forEach(hotel => {
                        if (hotel.rooms && hotel.rooms.length > 0) {
                            hotel.rooms.forEach(room => {
                                if ((room.hyperguest_pull === true || room.hyperguest_pull === 1) && room.rates) {
                                    room.rates.forEach(rate => {
                                        const checkboxKey = hotel.id + '_' + rate.ratePlanId + '_' + (room.room_id || room.id);
                                        if (!this.hyperguestCheckboxs.hasOwnProperty(checkboxKey)) {
                                            this.$set(this.hyperguestCheckboxs, checkboxKey, false);
                                        }
                                    });
                                }
                            });
                        }
                    });
                });
            },

            initializeHyperguestExistingCheckboxes() {
                console.log("initializeHyperguestExistingCheckboxes: Marcando hoteles existentes");

                this.categories.forEach(cat => {
                    cat.currentHotels.forEach((e) => {
                        if(e.hyperguest_pull == 1){
                            e.service_rooms_hyperguest.forEach((s) => {
                                this.$set(this.hyperguestCheckboxs, e.hotel.id + '_' + s.rate_plan_id + '_' + s.room_id, true);
                            });
                        }
                    });
                });

                // También inicializar checkboxes para las rooms de Hyperguest en la búsqueda actual
                this.initializeHyperguestCheckboxes();
            },

            processHyperguestDeleteItems(data) {
                // console.log("checkbox antes de eliminar", this.hyperguestCheckboxs);
                if(data && data.length > 0){
                    console.log("Procesando items para eliminar:", data);
                    data.forEach(e => {
                        if(e.hyperguest){
                            const checkboxKey = e.hotel_id + '_' + e.rate_plan_id + '_' + e.room_id;
                            console.log(`Desmarcando checkbox: ${checkboxKey}`);
                            this.$set(this.hyperguestCheckboxs, checkboxKey, false);
                        }else{
                            const checkboxKey = e.hotel_id + '_' + e.rate_plan_room_id;
                            console.log(`Desmarcando checkbox: ${checkboxKey}`);
                            // this.$set(this.checkboxs, checkboxKey, false);
                            this.checkboxs[checkboxKey] = false;
                        }
                    });
                    this.$forceUpdate();
                }
                // console.log("checkbox despues de eliminar", this.hyperguestCheckboxs);
            },

            showHyperguestContentHotel(hotel) {
                hotel.viewContent = !hotel.viewContent;
            },

            toggleHyperguestViewRates(rate) {
                rate.showAllRates = !rate.showAllRates;
            },

            getHyperguestBestPrice(rate) {
                if (!rate.rate || rate.rate.length === 0) return 0;
                // Encontrar el precio más bajo entre las opciones de tarifa
                return Math.min(...rate.rate.map(r => (parseFloat(r.total_amount_base) / r.amount_days.length) || 0));
            },

            selectHyperguestRate(catKey, hotel, room, rate) {
                // rateId y roomId deben funcionar tanto para rooms Hyperguest como para rooms Aurora con tarifas Hyperguest
                const rateId = rate.ratePlanId || rate.id
                const roomId = room.room_id || room.id
                const checkboxKey = hotel.id + '_' + rateId + '_' + roomId;
                console.log("1. Checkbox key:", checkboxKey);

                // Leer el estado ANTES del cambio
                const wasSelected = this.hyperguestCheckboxs[checkboxKey];
                console.log("2. Estado ANTES del cambio:", wasSelected);

                // Usar $nextTick para procesar después de que Vue actualice el DOM
                this.$nextTick(() => {
                    const isNowSelected = this.hyperguestCheckboxs[checkboxKey];
                    console.log("3. Estado DESPUÉS del cambio:", isNowSelected);
                    this.$set(this.hyperguestCheckboxs, checkboxKey, isNowSelected);

                    // Determinar la acción basada en el estado anterior
                    const shouldSelect = !wasSelected; // Si no estaba seleccionado, ahora debería seleccionarse
                    console.log("Debería seleccionar:", shouldSelect);

                    this.processHyperguestRateSelection(catKey, hotel, room, rate, shouldSelect);
                });
            },

            processHyperguestRateSelection(catKey, hotel, room, rate, isSelected) {
                console.log("isSelected", isSelected);
                console.log("rate", rate);
                // Obtener los detalles de la tarifa seleccionada
                const rateDetail = rate && rate.amount_days.length > 0 ? rate : null;

                console.log("rateDetail", rateDetail);
                // precio adults
                const price_adult = rateDetail.amount_days[0].total_adult_base || 0;

                console.log("price_adult", price_adult);

                if (rateDetail) {
                    const selectedData = {
                        hotel: {
                            id: hotel.id,
                            name: hotel.name,
                            code: hotel.code,
                            country: hotel.country,
                            category: hotel.category
                        },
                        room: {
                            // Enviar siempre el room_id correcto (Hyperguest o Aurora)
                            id: room.room_id || room.id,
                            name: room.name,
                            type: room.room_type,
                            max_adults: room.max_adults,
                            max_child: room.max_child,
                            description: room.description
                        },
                        rate: {
                            id: rate.ratePlanId,
                            name: rate.name,
                            plan: rate.name_commercial,
                            price_adult: price_adult,
                            price_amount_base: rateDetail.total_amount_base,
                            price_amount_total: rateDetail.total_amount,
                            quantity_adults: rateDetail.quantity_adults_total,
                            available: rate.available
                        },
                        dates: {
                            from: this.date_from,
                            to: this.date_to
                        },
                        category: { id: this.categories[catKey].id }
                    };

                    if(isSelected){
                        this.onHyperguestHotelSelected(selectedData);
                    }else{
                        this.onHyperguestHotelDeselected(selectedData);
                    }
                }
            },

            updateRates () {
                API.get(window.origin + '/prices?category_id=' + this.category_id).then((result) => {
                    console.log('Tarifas actualizadas')
                }).catch((e) => {
                    console.log(e)
                })
            },
            updateDestinations () {
                API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
                    console.log('Destinos actualizados')
                }).catch((e) => {
                    console.log(e)
                })
            },
            back: function () {
                this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
                    this.plan_rate_id + '/category/' + this.category_id)
            },
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },
            addOptional () {
                this.optional = !this.optional
            },

            async onHyperguestHotelSelected(selectedData) {

                // console.log('Hotel de Hyperguest seleccionado:', selectedData);

                this.loading = true;

                const request_data = {
                    'hyperguest' : true, // Siempre true para hoteles hyperguest
                    'hotel_id' : selectedData.hotel.id,
                    'date_in' : this.convertDate(this.date_from, '/', '-'),
                    'date_out': this.convertDate(this.date_to, '/', '-'),
                    're_entry' :  (this.reEntry) ? 1 : 0,
                    'rate_plan_id' : selectedData.rate.id,
                    'room_id' : selectedData.room.id,
                    'num_adult' : selectedData.rate.quantity_adults,
                    'price_adult': selectedData.rate.price_adult,
                    'price_amount_base': selectedData.rate.price_amount_base,
                    'price_amount_total': selectedData.rate.price_amount_total
                };

                const url = 'package/package_plan_rate_category/' + this.categoryActive.id + '/hotel/room';

                try{
                    const response = await API({
                        method: 'POST',
                        url: url,
                        data: request_data,
                    });

                    if ( response.data.success ) {
                        this.deleteItemsHyperguest = response.data.delete_rooms;
                        this.processHyperguestDeleteItems(response.data.delete_rooms);
                        this.updateRates();
                        this.updateDestinations();
                        this.setCurrentHotels();
                    }
                }
                catch(e) {
                    console.log(e);
                }
                finally{
                    this.loading = false;
                }
            },

            async onHyperguestHotelDeselected(selectedData){
                this.loading = true;
                const url = 'package/package_plan_rate_category/' + this.categoryActive.id + '/hotel/room';
                const method = 'DELETE';

                const request_data = {
                    'hyperguest' : true, // Siempre true para hoteles hyperguest
                    'hotel_id' : selectedData.hotel.id,
                    'date_in' : this.convertDate(this.date_from, '/', '-'),
                    'date_out': this.convertDate(this.date_to, '/', '-'),
                    're_entry' :  (this.reEntry) ? 1 : 0,
                    'rate_plan_id' : selectedData.rate.id,
                    'room_id' : selectedData.room.id
                };

                try{
                    const response = await API({
                        method: method,
                        url: url,
                        data: request_data,
                    });

                    if ( response.data.success ) {
                        this.updateRates();
                        this.updateDestinations();
                        this.setCurrentHotels();
                    }
                }
                catch(e){
                    console.log(e);
                }
                finally{
                    this.loading = false;
                }
            },
        },
        filters: {
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },

            formatPrice: function (price) {
                return parseFloat(price).toFixed(2)
            }
        }
    }
</script>

<style lang="stylus">
    .rateChoosed_true {
        background: #deffe2;
        border: solid 1px #39573b;
    }

    .rateRow {
        border-radius: 3px;
        padding: 4px 0 4px 6px;
    }

    .bottom0 {
        padding-bottom: 0px !important;
        margin-bottom: 0px !important;
    }

    hr {
        margin: 0 !important;
    }

    .row_0 {
        background: #e3fcff;
        padding: 5px;
        border-radius: 4px;
        line-height: 22px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 13px;
        margin-bottom: 10px !important
    }

    .row_1 {
        background: #ffeeff;
        padding: 5px;
        border-radius: 4px;
        line-height: 22px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 13px;
        margin-bottom: 10px !important;
    }

    .reEntry {
        float: right;
        border: solid 1px #3ea662;
        border-radius: 5px;
        margin: 5px;
    }

    .switch {
        margin-top: 11px;
        margin-bottom: -6px;
    }

    .sectionResult {
        padding: 14px;
        line-height: 29px;
        border: solid 1px #ffc500;
    }

    .marginB20 {
        margin-bottom: 20px;
    }

    .sectionShare {
        padding: 14px;
        line-height: 29px;
        background: #afecff;
        border: solid 1px #82adeb;
    }

    button:not(:disabled), [type=button]:not(:disabled), [type=reset]:not(:disabled), [type=submit]:not(:disabled) {
        outline: none;
    }

    .panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.2s ease-out;
    }

    .unified-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 300px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 20px 0;
        border: 1px solid #e9ecef;
    }

    .loading-content {
        text-align: center;
        padding: 40px;
    }

    .loading-message {
        margin-top: 20px;
        font-size: 18px;
        color: #495057;
        font-weight: 500;
        margin-bottom: 0;
    }

    .loading-content img {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>



