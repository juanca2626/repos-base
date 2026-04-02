<template>
    <div class="container-fluid">

        <div class="form-row" style="padding-bottom: 0px;">
            <label class="col-2 col-form-label right">{{ $t('package.copy_from_category') }}</label>
            <div class="col-3 right">
                <v-select :options="categoriesWithServices"
                          :value="categoriesWithServicesId"
                          autocomplete="true"
                          data-vv-as="categories_services"
                          data-vv-name="categories_services"
                          name="categories_services"
                          @input="categoriesServicesChange"
                          v-model="categoriesServicesSelected">
                </v-select>
            </div>
            <div class="col-1 right">
                <button @click="willCopyCategory()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
            </div>
            <div class="col-4 right">
                <span class="legend trService"/> Servicio <br>
                <span class="legend trHotel1"/> Hotel <br>
                <span v-show="legendHotelSecondary"><span class="legend trHotel"/> Hotel (No incluido al calculo de tarifas)</span>
            </div>
            <div class="col-2 right">
                <button v-show="!loading" @click="exportExcel()" class="btn btn-success" :disabled="disabled_export">
                    <font-awesome-icon :icon="['fas', 'file-excel']" /> Exportar
                </button>
                <img v-show="loading" src="/images/loading.svg" v-if="loading" width="40px"/>
            </div>
        </div>

        <div class="form-row" style="padding-bottom: 25px;">
            <label class="col-2 col-form-label right">Cambiar fecha de inicio</label>
            <div class="col-3 right">
                <date-picker
                        :config="datePickerOptions"
                        @dp-change="setDateFrom"
                        id="new_date_in"
                        name="new_date_in"
                        placeholder="DD/MM/YYYY"
                        ref="datePickerNewDateIn"
                        v-model="new_date_in">
                </date-picker>
            </div>
            <div class="col-1 right">
                <button @click="change_date_in()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
            </div>
        </div>

        <table-client
            :columns="table.columns"
            :data.sync="servicesAndHotels"
            :loading="loading"
            :draggable="true"
            @rows-reordered="handleRowsReordered"
            :options="tableOptions" id="dataTable"
            theme="bootstrap4">
            <div class="d-flex table-date" slot="date" slot-scope="props" style="font-size: 0.9em;padding: 10px; gap: 5px;" :class="{'error-selection':(props.row.error_rates)}">
                <template v-if="!props.row.flag_editing_date">
                    <button type="button" class="btn btn-xs btn-white" @click="toggleInputDate(props)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                        </svg>
                    </button>
                    <span>{{props.row.date_in | formatDate }}</span><template v-if="props.row.type === 'hotel'"><span>-</span><span>{{props.row.date_out | formatDate }}</span></template>
                </template>
                <template v-else>
                    <input type="date" :value="props.row.date_in" @change="updateDate(props, $event)"
                        class="form-control form-control-sm p-1" />
                    <button type="button" class="btn btn-xs btn-secondary" title="Cancelar" @click="toggleInputDate(props)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </button>
                </template>
            </div>
            <div class="table-code" slot="code" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <span v-if="props.row.type=='hotel'">
                    <div v-if="props.row.hotel.deleted_at!=='' && props.row.hotel.deleted_at!==null" class="strong-message">ESTE HOTEL FUE ELIMINADO</div>
                    <div v-if="!props.row.hotel.status" class="strong-message">ESTE HOTEL FUE DESACTIVADO</div>
                    {{ props.row.hotel.channel[0].code }}
                </span>
                <span v-if="props.row.type=='service'">
                    <div v-if="props.row.service.deleted_at!=='' && props.row.service.deleted_at!==null" class="strong-message">ESTE SERVICIO FUE ELIMINADO</div>
                    <div v-if="!props.row.service.status" class="strong-message">ESTE SERVICIO FUE DESACTIVADO</div>
                    {{ props.row.service.aurora_code }}
                    <font-awesome-icon v-if="props.row.service.service_equiv_association_count > 0"
                        class="text-danger change"
                        @click="willChangeCategoryService(props.row.id,props.row.service.id)"
                        :icon="['fas', 'exchange-alt']"/>
                </span>
                <span v-if="props.row.type=='flight'">{{props.row.code_flight }}</span>
            </div>
            <div class="table-description" slot="description" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <span v-if="props.row.type=='hotel'"><font-awesome-icon :icon="['fas', 'bed']"/> {{props.row.hotel.name }}</span>
                <span v-if="props.row.type=='service'"><font-awesome-icon :icon="['fas', 'bars']"/> {{props.row.service.name }}</span>
                <span v-if="props.row.type=='flight'">
                    <span v-if="props.row.origin != null && props.row.origin != ''">
                        Origen: {{ props.row.origin }}
                    </span> / <span v-if="props.row.destiny != null && props.row.destiny != ''">
                        Destino: {{ props.row.destiny }}
                    </span>
                </span>
                <br>
                <span style="padding: 1px;" class="alert alert-warning" v-if="props.row.type == 'service' && props.row.service.pax_min != ''">
                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"/>
                    El servicio tiene como mínimo {{ props.row.service.pax_min }}
                    {{ props.row.service.pax_min == 1 ? 'pasajero' : 'pasajeros' }}
                </span> <span v-if="props.row.type == 'service' && props.row.service.notes" class="ml-2">
                    <a href="#" @click.prevent="showModalRemark(props.row.service.notes)" style="font-size: 0.85em; text-decoration: underline; color: #007bff;">
                        Remarks
                    </a>
                </span>
            </div>
            <div class="table-rate_plan center" slot="rate_plan" slot-scope="props"
                 style="font-size: 0.9em; min-width: 350px;text-align: center;">

                <div class="col-12" v-if="props.row.type=='hotel'">
                    <div class="col-10 left" style="line-height: 28px;">

                        <span v-for="(service_room, srKey) in props.row.service_rooms" v-if="srKey < 3 && props.row.service_rooms.length >= 1"
                             :class="{'strong-sub-message': ( (service_room.rate_plan_room.room.deleted_at!==''
                                                                && service_room.rate_plan_room.room.deleted_at!==null)
                                                                || !service_room.rate_plan_room.room.state ) ||
                                                             ( !(service_room.rate_plan_room.rate_plan.status) ||
                                                                (service_room.rate_plan_room.rate_plan.deleted_at!==''
                                                                && service_room.rate_plan_room.rate_plan.deleted_at!==null) )}">

                            <strong v-if="service_room.rate_plan_room.room.deleted_at!=='' && service_room.rate_plan_room.room.deleted_at!==null">
                                (HAB. ELIMINADA)
                            </strong>
                            <strong v-if="!service_room.rate_plan_room.room.state">
                                (HAB. DESACTIVADA)
                            </strong>

                            <strong v-if="service_room.rate_plan_room.rate_plan.deleted_at!=='' && service_room.rate_plan_room.rate_plan.deleted_at!==null">
                                (TARIFA ELIMINADA)
                            </strong>
                            <strong v-if="!(service_room.rate_plan_room.rate_plan.status)">
                                (TARIFA DESACTIVADA)
                            </strong>

                            <span v-if="service_room.rate_plan_room.room.room_type.translations[0]">
                                {{ service_room.rate_plan_room.room.room_type.translations[0].value }}
                            </span>
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 1">
                                <!-- (${{ service_room.rate_plan_room.first_rate[0].price_adult | formatPrice }}) -->
                                (${{ rateProcessADL(service_room.rate_plan_room.first_rate, 1, service_room.rate_plan_room.channel_id) | formatPrice }})
                                <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span><br>

                            </span>
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 2">
                                <!-- (${{(service_room.rate_plan_room.first_rate[0].price_adult / 2) | formatPrice }}) -->
                                (${{ rateProcessADL(service_room.rate_plan_room.first_rate, 2, service_room.rate_plan_room.channel_id) | formatPrice }})
                                 <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span><br>
                            </span>
                            <span
                                v-if="service_room.rate_plan_room.first_rate.length>0 && service_room.rate_plan_room.room.room_type.occupation == 3">
                                <!-- (${{(service_room.rate_plan_room.first_rate[0].price_adult/3) | formatPrice }}) -->
                                (${{ rateProcessADL(service_room.rate_plan_room.first_rate, 3, service_room.rate_plan_room.channel_id) | formatPrice }})
                                <span v-if="parseFloat(service_room.rate_plan_room.first_rate[0].price_extra) > 0"> + (${{(service_room.rate_plan_room.first_rate[0].price_extra/3) | formatPrice }})</span>
                                 <span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span><br>
                            </span>
                            <span v-if="service_room.rate_plan_room.first_rate.length==0">
                                ($-)<span @click="deleteServiceRoomHotel(service_room)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span><br>
                            </span>
                        </span>
                        <span v-for="(service_room_hyperguest, srKey) in props.row.service_rooms_hyperguest" v-if="srKey < 3 && props.row.service_rooms_hyperguest.length >= 1" :class="{'strong-sub-message': ( (!service_room_hyperguest.room.deleted_at!=='' && service_room_hyperguest.room.deleted_at!==null) || !service_room_hyperguest.room.state) || (!(service_room_hyperguest.rate_plan.status) || (service_room_hyperguest.rate_plan.deleted_at!=='' && service_room_hyperguest.rate_plan.deleted_at!==null)) }">
                            <strong v-if="!service_room_hyperguest.room.deleted_at!=='' && service_room_hyperguest.room.deleted_at!==null">
                                (HAB. ELIMINADA)
                            </strong>

                            <strong v-if="!service_room_hyperguest.room.state">
                                (HAB. DESACTIVADA)
                            </strong>

                            <strong v-if="service_room_hyperguest.rate_plan.deleted_at!=='' && service_room_hyperguest.rate_plan.deleted_at!==null">
                                (TARIFA ELIMINADA)
                            </strong>

                            <strong v-if="!(service_room_hyperguest.rate_plan.status)">
                                (TARIFA DESACTIVADA)
                            </strong>

                            <span>
                                {{ service_room_hyperguest.room.translations[0].value }} (${{ service_room_hyperguest.price_adult / service_room_hyperguest.room.room_type.occupation | formatPrice }})
                                <span @click="deleteServiceRoomHotelHyperguest(service_room_hyperguest)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span>
                            </span>
                            <br>
                        </span>
                        <span v-if="props.row.service_rooms.length > 3"> (y {{ props.row.service_rooms.length - 3 }} más) </span>
                    </div>
                    <!-- <div class="col-10 left" style="line-height: 28px;" v-if="props.row.service_rooms_hyperguest.length > 0">
                        <span v-for="(service_room_hyperguest, srKey) in props.row.service_rooms_hyperguest" v-if="srKey < 3" :class="{'strong-sub-message': ( (!service_room_hyperguest.room.deleted_at!=='' && service_room_hyperguest.room.deleted_at!==null) || !service_room_hyperguest.room.state) || (!(service_room_hyperguest.rate_plan.status) || (service_room_hyperguest.rate_plan.deleted_at!=='' && service_room_hyperguest.rate_plan.deleted_at!==null)) }">
                            <strong v-if="!service_room_hyperguest.room.deleted_at!=='' && service_room_hyperguest.room.deleted_at!==null">
                                (HAB. ELIMINADA)
                            </strong>

                            <strong v-if="!service_room_hyperguest.room.state">
                                (HAB. DESACTIVADA)
                            </strong>

                            <strong v-if="service_room_hyperguest.rate_plan.deleted_at!=='' && service_room_hyperguest.rate_plan.deleted_at!==null">
                                (TARIFA ELIMINADA)
                            </strong>

                            <strong v-if="!(service_room_hyperguest.rate_plan.status)">
                                (TARIFA DESACTIVADA)
                            </strong>

                            <span>
                                {{ service_room_hyperguest.room.translations[0].value }} (${{ service_room_hyperguest.price_adult / service_room_hyperguest.room.room_type.occupation | formatPrice }})
                                <span @click="deleteServiceRoomHotelHyperguest(service_room_hyperguest)" style="cursor:pointer;"><font-awesome-icon :icon="['fas', 'trash']"/></span>
                            </span>
                            <br>
                        </span>
                        <span v-if="props.row.service_rooms_hyperguest.length > 3"> (y {{ props.row.service_rooms_hyperguest.length - 3 }} más) </span>
                    </div> -->
                    <div class="col-10 left btn-warning" style="line-height: 25px; padding: 4px 10px;"
                         v-if="props.row.service_rooms.length == 0 && props.row.service_rooms_hyperguest.length == 0">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"/>
                        Por asignar
                    </div>
                    <div class="col-2 right" v-if="props.row.service_rooms.length > 0 || props.row.service_rooms_hyperguest.length > 0"
                         :class="'paddingByCount paddingByCount'+(props.row.service_rooms.length+props.row.service_rooms_hyperguest.length)">

                        <img class="ico-error" v-show="props.row.error_rates" src="/images/status-error.png" alt="">

                        <button @click="viewHotelRates(props.row)" style="float: left;"
                                class="btn btn-sm btn-info"
                                type="button"
                                :disabled="loadingHotelRates">
                            <img v-if="loadingHotelRates" src="/images/loading.svg" width="16px" style="display: inline-block;"/>
                            <font-awesome-icon v-else :icon="['fas', 'money-bill']"/>
                        </button>
                    </div>

                    <!-- <div class="col-2 right" v-if="props.row.service_rooms_hyperguest.length > 0"
                         :class="'paddingByCount paddingByCount'+props.row.service_rooms_hyperguest.length">

                        <button @click="viewHotelRates(props.row)" style="float: left;"
                                class="btn btn-sm btn-info"
                                type="button"
                                :disabled="loadingHotelRates">
                            <img v-if="loadingHotelRates" src="/images/loading.svg" width="16px" style="margin-right: 5px;"/>
                            <font-awesome-icon v-else :icon="['fas', 'money-bill']"/>
                        </button>
                    </div> -->
                </div>

                <div class="col-12" style="padding: 10px;" v-if="props.row.type=='service'">

                    <div class="col-10 left" v-if="props.row.service_rates.length == 0">
                        <select class="form-control mr-0 ml-0" @change="saveRateServiceSelected($event,props.row)"
                                :name="props.row.id" :key="props.row.id"
                                v-model="props.row.service_rate_id_selected =''">
                            <option value="">Seleccione Tarifa</option>
                            <option :value="rate.id" :disabled="rate.price_from == ''"
                                    v-for="rate in props.row.service.service_rate">{{ rate.name }}
                                <span v-if="rate.price_from != ''">
                                    | (${{ rate.price_from }} x {{ rate.price_from_pax }})
                                </span>
                            </option>
                        </select>
                    </div>

                    <div class="col-10 left" v-if="props.row.service_rates.length > 0">
                        <select class="form-control mr-0 ml-0" @change="saveRateServiceSelected($event,props.row)"
                                :name="props.row.id" :key="props.row.id"
                                v-model="props.row.service_rate_id_selected = props.row.service_rates[0].service_rate_id">
                            <option value="">Seleccione Tarifa</option>
                            <option :value="rate.id" :disabled="rate.price_from == ''"
                                    v-for="rate in props.row.service.service_rate">{{ rate.name }}
                                <span v-if="rate.price_from != ''">
                                    | (${{ rate.price_from }} x {{ rate.price_from_pax }})
                                </span>
                            </option>
                        </select>
                    </div>

                    <div class="col-2 right" style="padding-top: 5px;" v-if="props.row.service_rates.length > 0">
                        <button @click="viewRatesServices(props.row)" style="float: left;"
                                class="btn btn-sm btn-info"
                                type="button">
                            <font-awesome-icon :icon="['fas', 'money-bill']"/>
                        </button>
                    </div>

                </div>

            </div>
            <div class="table-check" slot="check" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <span @click="toggleChecked(props)" style="cursor:pointer;" v-if="props.row.type === 'service'">
                    <template v-if="!props.row.checked">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square" viewBox="0 0 16 16">
                            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                        </svg>
                    </template>
                    <template v-else>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-square" viewBox="0 0 16 16">
                            <path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5z"/>
                            <path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0"/>
                        </svg>
                    </template>
                </span>
            </div>
            <div class="table-order" slot="order" slot-scope="props" style="font-size: 0.9em;padding: 10px">
                <button @click="orderServices(props.row, -1)"
                        class="btn btn-sm btn-info" type="button" style="margin-bottom:5px;"
                        :disabled="props.row.disabled_order_up">
                    <font-awesome-icon :icon="['fas', 'sort-up']"/>
                </button>
                <button @click="orderServices(props.row, 1)"
                        class="btn btn-sm btn-info" type="button"
                        :disabled="props.row.disabled_order_down">
                    <font-awesome-icon :icon="['fas', 'sort-down']"/>
                </button>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 10px">

                <template v-if="props.row.type != 'flight'">
                    <button v-if="props.row.calculation_included" @click="calculationIncluded(props.row.id, 0)"
                            style="margin-top: 13px; margin-right: 5px"
                            class="btn btn-sm btn-success" title="No incluir markup en el cálculo tarifario"
                            type="button">
                        <font-awesome-icon :icon="['fas', 'money-bill']"/>
                    </button>

                    <button v-if="!(props.row.calculation_included)" @click="calculationIncluded(props.row.id, 1)"
                            style="margin-top: 13px; margin-right: 5px; background-color: #ffc107"
                            class="btn btn-sm btn-danger" title="Incluir markup en el cálculo tarifario"
                            type="button">
                        <font-awesome-icon :icon="['fas', 'money-bill']"/>
                    </button>
                </template>

                <button @click="showModal(props.row.id, props.row.hyperguest_pull)" style="margin-top: 13px;"
                        class="btn btn-sm btn-danger"
                        type="button">
                    <font-awesome-icon :icon="['fas', 'trash']"/>
                </button>

            </div>
            <div class="table-loading text-center" slot="loading">
                <img alt="loading" height="51px" src="/images/loading.svg"/>
            </div>
        </table-client>

        <div class="VuePagination row col-md-12 justify-content-center" v-if="pages_.length > 1">
            <nav class="text-center">
                <ul class="pagination_ VuePagination__pagination" style="">
                    <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                        'disabled':(pageChosen==1 || loading)}" @click="setPage(pageChosen-1)">
                        <a href="javascript:void(0);" :disabled="(pageChosen==1 || loading)" class="page-link">&lt;</a>
                    </li>
                    <li v-for="page in pages_" @click="setPage(page)"
                        :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosen), 'disabled':loading }">
                        <a href="javascript:void(0)" class="page-link active" role="button">
                            <img class="ico-error-pages" v-show="page_error[page]" src="/images/status-error.png" alt="">
                            {{ page }}
                        </a>
                    </li>
                    <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                        'disabled':(pageChosen==pages_.length || loading)}" @click="setPage(pageChosen+1)">
                        <a href="javascript:void(0);" :disabled="(pageChosen==pages_.length || loading)" class="page-link">&gt;</a>
                    </li>
                </ul>
            </nav>
        </div>

         <b-modal :title="modalName" centered ref="my-modal-remark" size="md">
            <p class="text-center" v-html="remarks"></p>
            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="title_rates_plan" centered ref="my-modal-rates-services" size="lg">
            <div class="row">
                <div class="col-12">
                    <table-client :columns="table.columnsRates"
                                  :data="data_rates_plans"
                                  :loading="false"
                                  :options="tableRatesOptions"
                                  id="rooms-table"
                                  ref="roomsTable"
                                  theme="bootstrap4">

                        <div class="table-range text-center" slot="range"
                             slot-scope="props">
                            {{ props.row.pax_from }} - {{ props.row.pax_to }}
                        </div>
                        <div class="table-period" slot="period"
                             slot-scope="props">
                            {{ props.row.date_from | formatDate }} - {{ props.row.date_to | formatDate }}
                        </div>
                        <div class="table-price_adult" slot="price_adult"
                             slot-scope="props">
                            {{ props.row.price_adult }}
                        </div>
                        <div class="table-price_child" slot="price_child"
                             slot-scope="props">
                            {{ props.row.price_child }}
                        </div>
                        <div class="table-price_infant" slot="price_infant"
                             slot-scope="props">
                            {{ props.row.price_infant }}
                        </div>
                        <div class="table-price_guide" slot="price_guide"
                             slot-scope="props">
                            {{ props.row.price_guide }}
                        </div>
                    </table-client>
                </div>
            </div>
            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="modalName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal title="Copiar servicios" centered ref="my-modal-confirm" size="sm">
            <p class="text-center">La categoría actual ya tiene servicios/hoteles, desea reemplazarlos?</p>

            <div slot="modal-footer">
                <button @click="copyCategory()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal :title="title_rates_hotel" centered ref="my-modal-select-hotel-rates" size="lg">

            <!-- Rooms normales (hyperguest_pull == false o no existe) -->
            <div v-if="hotelSwapRates.normal == 1">
                <div v-for="(room, rkey) in (hotelSwapRates?.rooms || [])" v-if="(room?.rates_plan_room?.length || 0) > 0" :key="'normal-room-' + rkey">

                    <div class="rooms-table row canSelectText" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #dee2e6;">
                        <div class="col-4 my-auto">
                            <strong>Nombre: </strong>{{ room.translations?.[0]?.value || '' }}<br>
                            <strong>Descripción: </strong>{{ room.translations?.[1]?.value || '' }}<br>
                            <strong>Ocupación: </strong>{{ room.occupation || room.room_type?.occupation || '' }}
                        </div>
                        <div class="col-8 my-auto">
                            <div v-for="(rate, raKey) in room.rates_plan_room"
                                 :class="'col-12 rateRow rateChoosed_' + checkboxs[ '_' + rate.id ]"
                                 v-if="rate.calendarys && rate.calendarys.length > 0">

                                <label style="display: block;" :for="'checkbox_' + rkey + '_' + raKey" :class="{'error-selection' : (rate.error_in_calendarys) || !(rate.rate_plan.status) || (rate.rate_plan.deleted_at!=='' && rate.rate_plan.deleted_at!==null)}">
                                    <strong>
                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                        {{ rate.rate_plan.name }}:
                                    </strong>
                                </label>

                                <div style="margin-left: 30px;">
                                    <span v-if="rate.calendarys[0].status == 1"><font-awesome-icon
                                        :icon="['fas', 'check-circle']"/></span>
                                    <span v-if="rate.calendarys[0].status != 1"><font-awesome-icon
                                        :icon="['fas', 'times-circle']"/></span>
                                    {{ rate.calendarys[0].date | formatDate }}
                                    <strong>$ <span v-if="rate.calendarys[0].rate && rate.calendarys[0].rate[0]">
                                        <!-- {{ rate.calendarys[0].rate[0].price_adult | formatPrice }}  -->
                                        {{ rateProcess(rate.calendarys[0].rate, room.max_adults, rate.channel_id) | formatPrice }}

                                    </span>
                                    </strong>
                                    <span v-if="rate.calendarys.length>1">
                                        <a href="javascript:;" v-show="!(rate.showAllRates)" @click="toggleViewRates(rate)"><font-awesome-icon
                                            :icon="['fas', 'plus']"/></a>
                                        <a href="javascript:;" v-show="rate.showAllRates" @click="toggleViewRates(rate)"><font-awesome-icon
                                            :icon="['fas', 'minus']"/></a>
                                    </span>
                                </div>
                                <div style="margin-left: 30px;" v-for="( calendar, cKey) in rate.calendarys"
                                     v-show="rate.showAllRates">
                                    <span v-if="cKey > 0">
                                        <span v-if="calendar.status == 1"><font-awesome-icon
                                            :icon="['fas', 'check-circle']"/></span>
                                        <span v-if="calendar.status != 1"><font-awesome-icon
                                            :icon="['fas', 'times-circle']"/></span>
                                        {{ calendar.date | formatDate }}
                                        <strong>$ <span v-if="calendar.rate && calendar.rate[0]">
                                            <!-- {{ calendar.rate[0].price_adult | formatPrice }}  -->
                                            {{ rateProcess(calendar.rate, room.max_adults, rate.channel_id) | formatPrice }}
                                        </span></strong>
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms hyperguest (hyperguest_pull == 1) -->
            <div v-if="hotelSwapRates.hyperguest == 1">
                <div v-for="(room, rkey) in (hotelSwapRates.rooms_hyperguest || [])" :key="'hyperguest-room-' + rkey">
                    <div class="rooms-table row canSelectText" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #dee2e6;">
                        <div class="col-4 my-auto">
                            <strong>Nombre: </strong>{{ room.name || '' }}<br>
                            <strong>Ocupación: </strong>{{ room.occupation || room.room_type?.occupation || '' }}
                        </div>
                        <div class="col-8 my-auto">
                            <div v-for="(rate, raKey) in room.rates" :class="'col-12 rateRow rateChoosed_' + (rate.selected || false)">
                                <label style="display: block;" :for="'checkbox_' + rkey + '_' + raKey">
                                    <strong>
                                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                        {{ rate.name }}:
                                    </strong>
                                </label>

                                <div style="margin-left: 30px;">
                                    <span>
                                        <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                        {{ rate.amount_days[0].date | formatDate }}
                                        <strong>$ <span>{{ rate.amount_days[0].rate[0].total_adult | formatPrice }}</span></strong>
                                    </span>
                                    <span v-if="rate.amount_days?.length || 0 > 1">
                                        <a href="javascript:;" v-show="!(rate?.showAllRates || false)" @click="toggleViewRates(rate)"><font-awesome-icon
                                            :icon="['fas', 'plus']"/></a>
                                        <a href="javascript:;" v-show="rate?.showAllRates || false" @click="toggleViewRates(rate)"><font-awesome-icon
                                            :icon="['fas', 'minus']"/></a>
                                    </span>
                                    <span v-if="rate.amount_days?.length || 0 > 1" v-for="(amount_day, key_amount_day) in rate.amount_days" v-show="rate?.showAllRates || false">
                                        <span v-if="key_amount_day > 0">
                                            <font-awesome-icon :icon="['fas', 'check-circle']"/>
                                            {{ amount_day.date | formatDate }}
                                            <strong>$ <span>{{ amount_day.rate[0].total_adult | formatPrice }}</span></strong>
                                        </span>
                                        <br>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay habitaciones con tarifas -->
            <div v-if="(hotelSwapRates.normal == 0 || !hotelSwapRates.rooms || hotelSwapRates.rooms.length === 0) &&
                       (hotelSwapRates.hyperguest == 0 || !hotelSwapRates.rooms_hyperguest || hotelSwapRates.rooms_hyperguest.length === 0)"
                 class="text-center" style="padding: 20px;">
                <p>No hay habitaciones con tarifas disponibles para este hotel.</p>
            </div>

            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

        <b-modal title="Elija un servicio:" centered ref="my-modal-service-category" size="lg">
            <table class="table table-bordered">
                <thead class="thead-light text-center">
                <tr>
                    <th>Servicio</th>
                    <th>Tarifa</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(equivalence,indexParent) in equivalences" :key="indexParent">
                    <th class="text-center">
                        <div class="col-md-12 font-weight-normal">
                            [{{equivalence.service.aurora_code}} - {{equivalence.service.equivalence_aurora}}] -
                            {{equivalence.service.name}}
                        </div>
                    </th>
                    <th class="text-center" style="width: 250px">
                        <v-select :options="equivalence.service.service_rate"
                                  track-by="id"
                                  label="name"
                                  :value="rateSelected"
                                  @input="addRate"
                                  placeholder="Seleccione una tarifa"
                                  name="rate"
                                  autocomplete="true"
                                  key="id"
                                  v-model="serviceByRates[indexParent]">
                        </v-select>
                    </th>
                    <th class="text-center">
                        <button @click="changeCategoryService(package_service_id,equivalence.service_equivalence_id)"
                                class="btn btn-sm btn-success mb-sm-1"
                                type="button">
                            <font-awesome-icon :icon="['fas', 'exchange-alt']"/>
                        </button>
                    </th>
                </tr>
                </tbody>
            </table>
        </b-modal>
        <block-page></block-page>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import TableClient from './.././../../../components/TableClient'
    import Loading from 'vue-loading-overlay'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import BlockPage from '../../../../components/BlockPage'
    import datePicker from 'vue-bootstrap-datetimepicker'

    export default {
        name: 'CostRates',
        components: {
            'table-client': TableClient,
            datePicker,
            BModal,
            Loading,
            vSelect,
            BlockPage
        },
        data: () => {
            return {
                loading: false,
                loadingHotelRates: false,
                checkboxs: [],
                package_service_id: '',
                serviceByRates: [{
                    id: ''
                }],
                rateSelected: '',
                title_rates_plan: '',
                title_rates_hotel: '',
                data_rates_plans: [],
                tmpServDates: [],
                equivalences: [],
                hotelSwapRates: [],
                modalName: '',
                categoriesWithServices: [],
                categoriesWithServicesId: '',
                servicesAndHotels: [],
                categoriesServicesSelected: [],
                legendHotelSecondary: 0,
                table: {
                    columns: ['check', 'date', 'code', 'description', 'rate_plan', 'actions'],
                    columnsRates: ['range', 'period', 'price_adult', 'price_child', 'price_infant', 'price_guide']
                },
                pageChosen: 1,
                limit: 1000,
                pages_: [],
                page_error: [],
                datePickerOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang')
                },
                new_date_in : "",
                disabled_export : false,
                remarks: '',
                hyperguest_item: false,
            }
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        check: '',
                        order: 'Orden',
                        date: 'Fecha',
                        code: 'Código',
                        description: 'Descripción',
                        rate_plan: 'Plan Tarifario',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                    perPage: this.limit,
                    rowClassCallback: function (item) {

                        if( (item.type === 'service' &&
                                ( (item.service.deleted_at !== null && item.service.deleted_at !== '') ||
                                    (!(item.service.status)) ) )){
                            return 'tr-error-opacity'
                        }

                        if( (item.type === 'hotel' &&
                                ( (item.hotel.deleted_at !== null && item.hotel.deleted_at !== '') ||
                                    (!(item.hotel.status)) ) )){
                            return 'tr-error-opacity'
                        }

                        if (!item) return
                        if (item.type === 'service' && item.service_rates.length === 0) {
                            return 'alert trServiceEmptyRate'
                        } else if (item.type === 'service' && item.service_rates.length > 0) {
                            if (item.service.service_rate[0].price_from === '') {
                                return 'alert trServiceEmptyRate'
                            }
                        } else if (item.type === 'service') {
                            return 'trService'
                        } else {
                            return 'trHotel ' + 'trHotel' + item.firstHotel
                        }
                    }
                }
            },
            tableRatesOptions: function () {
                return {
                    headings: {
                        range: 'Rango',
                        period: 'Periodo',
                        price_adult: 'Adulto US$',
                        price_child: 'Niño US$',
                        price_infant: 'Infante US$',
                        price_guide: 'Guía US$'
                    },
                    sortable: [],
                    filterable: false,
                    perPageValues: []
                }
            }
        },
        mounted: function () {
            this.category_id = this.$route.params.category_id
            this.$root.$on('updateCategoryListServices', (payload) => {
                this.category_id = payload.categoryId
                this.search()
            })
            this.search()
            this.verify_errors_rates_hotels_per_pages()
            this.searchCategoriesWithCountServices()
        },
        methods: {
            refactorServicesAndHotels: async function () {
                this.tmpServDates = []
                this.tmpServDatesNormal = []
                this.servicesAndHotels = this.servicesAndHotels.slice().sort((a, b) => a.order - b.order)

                this.servicesAndHotels.forEach((serv, s) => {
                    this.servicesAndHotels[s].disabled_order_up = false
                    this.servicesAndHotels[s].disabled_order_down = false
                })

                this.servicesAndHotels.forEach((serv, s) => {
                    this.servicesAndHotels[s].error_rates = this.verify_rate_errors(serv)

                    if (!(this.tmpServDates[serv.date_in])) {
                        this.tmpServDates[serv.date_in] = []
                        this.tmpServDatesNormal.push(serv.date_in)

                        // BLOCK UP
                        let n = 0
                        let m = 0
                        this.servicesAndHotels.forEach((servJoinDate, key) => {
                            if (servJoinDate.date_in == serv.date_in) {
                                m = (key > m) ? key : m
                                this.tmpServDates[serv.date_in].push(key)
                                if (n == 0) {
                                    this.servicesAndHotels[key].disabled_order_up = true
                                }
                                n++
                                // servJoinDate.order = n
                            }
                        })
                        // BLOCK DOWN
                        if (n == this.tmpServDates[serv.date_in].length) {
                            this.servicesAndHotels[m].disabled_order_down = true
                        }
                    }
                })

                this.tmpServDatesNormal.forEach(arrayDatesNormal => {
                    let nByDate = 0
                    this.tmpServDates[arrayDatesNormal].forEach(keyServsHots => {

                        if (this.servicesAndHotels[keyServsHots].type == 'hotel' && nByDate > 0) {
                            this.legendHotelSecondary = 1
                        }

                        if (this.servicesAndHotels[keyServsHots].type == 'hotel' && nByDate == 0) {
                            this.servicesAndHotels[keyServsHots].firstHotel = 1
                            nByDate++
                        }
                    })
                })

                const codes = JSON.parse(localStorage.getItem('services_selected') ?? '[]')
                this.servicesAndHotels.forEach(item => {
                    if (item.type === 'service' && codes.includes(item?.service?.aurora_code)) {
                        item.checked = true;
                    }
                });
            },
            rateProcessADL(rates, occupation, channel){
                if(channel == '1'){
                    return rates[0].price_adult / occupation;
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

                    return rateValue / occupation;
                }
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
            verify_errors_rates_hotels_per_pages(){
                API.get('/package/package_plan_rate_category/' + this.category_id + '/hotels/rates/errors?limit=' + this.limit)
                    .then((result) => {
                        if( result.data.success ){
                            if( result.data.data.length > 0 ){

                                result.data.data.forEach( h=>{
                                    this.page_error[h.page] = true
                                })

                                this.disabled_export = true
                                this.$notify({
                                    group: 'main',
                                    type: 'warning',
                                    title: this.$t('global.modules.package'),
                                    text: "Por favor revisar su cotización contiene errores."
                                })
                            } else {
                                this.disabled_export = false
                            }
                        }
                })
            },
            change_date_in(){
                if( this.new_date_in === '' ) {
                  return
                }
                this.loading = true

                API({
                    method: 'PUT',
                    data: {date_from:this.new_date_in},
                    url:'/package/package_plan_rate_category/'+this.$route.params.category_id+'/services/date_in'
                }).then((response)=>{
                    if( response.data.success ){
                        this.search()
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: "Actualizado correctamente"
                        })
                    }
                    this.loading = false

                }).catch((e) => {
                    console.log(e)
                    this.loading = false
                })

            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            deleteServiceRoomHotel:function(service_room){

                API({
                    method: 'DELETE',
                    data: {
                        service_room_id:service_room.id,
                        hyperguest:this.hyperguest_item
                    },
                    url:'/package/delete/service_room'
                }).then((response)=>{
                    console.log("service room eliminado")
                        this.search()
                        this.hyperguest_item = false;
                }).catch((e) => {
                    console.log(e)
                })
            },
            deleteServiceRoomHotelHyperguest:function(service_room){

                API({
                    method: 'DELETE',
                    data: {
                        service_room_id:service_room.id,
                        hyperguest: true
                    },
                    url:'/package/delete/service_room'
                }).then((response)=>{
                    console.log("service room eliminado")
                        this.search()
                }).catch((e) => {
                    console.log(e)
                })
            },
            exportExcel(){
                this.loading = true

                let _name_category = ''
                this.categoriesWithServices.forEach( c=>{
                    if( c.code == this.$route.params.category_id ){
                        _name_category = c.label
                    }
                } )

                let title = localStorage.getItem('packagenamemanage') + ' - ' + _name_category

                API({
                    method: 'GET',
                    url: 'package/package_plan_rate_category/'+ this.category_id +'/export/passengers' + '?lang=' +
                        localStorage.getItem('lang') + '&quantity_pax=2',
                    responseType: 'blob',
                })
                    .then((response) => {

                        console.log(response)

                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'TARIFAS - '+ title + '.xlsx')
                        document.body.appendChild(fileLink)
                        fileLink.click()

                        this.loading = false

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
            reCalculateMarkupRateSale: function () {
                this.$root.$emit('blockPage', { message: 'Actualizando tablas tarifarias de venta Por favor espere' })
                API({
                    method: 'get',
                    url: 'package/rates/sales/markup/recalculate?package_plan_rate_id=' + this.$route.params.package_plan_rate_id
                })
                    .then((result) => {
                        this.search()
                        this.$root.$emit('unlockPage')
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Tablas Tarifarias de venta',
                            text: result.data.message
                        })
                    }).catch((error) => {
                    this.$root.$emit('unlockPage')
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error en actualizacion de tablas Tarifarias de venta',
                        text: ''
                    })
                })
            },
            calculationIncluded (id, _value) {
                API({
                    method: 'POST',
                    url: 'package/service/calculation_included',
                    data: {
                        package_service_id: id,
                        _value: _value
                    }
                })
                    .then((result) => {
                        this.search()
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: 'Guardado Correctamente'
                        })
                    }).catch((e) => {
                    console.log(e)
                })
            },
            viewRatesServices (me) {
                let find_service_rate_id = me.service_rates[0].service_rate_id
                this.title_rates_plan = 'Tarifas | ' + me.service.name + '<b>(' + me.service.aurora_code + ')</b>'
                me.service.service_rate.forEach(r => {
                    if (r.id == find_service_rate_id) {
                        this.data_rates_plans = r.service_rate_plans
                        this.$refs['my-modal-rates-services'].show()
                        return
                    }
                })
            },
            saveRateServiceSelected: function (event, service) {
                if (event.target.value != '') {
                    if (service.service_rates.length == 0) {
                        API({
                            method: 'POST',
                            url: 'package/service/rate/selected',
                            data: {
                                service_id: service.id,
                                service_rate_id: event.target.value,
                            }
                        })
                            .then((result) => {
                                this.search()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: result.data.message
                                })
                            }).catch((e) => {
                            console.log(e)
                        })
                    }
                    if (service.service_rates.length > 0) {
                        API({
                            method: 'POST',
                            url: 'package/service/rate/selected',
                            data: {
                                service_id: service.id,
                                service_rate_id: event.target.value,
                                package_service_rate_id: service.service_rates[0].id
                            }
                        })
                            .then((result) => {
                                this.search()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: result.data.message
                                })
                            }).catch((e) => {
                            console.log(e)
                        })
                    }

                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: 'Debe Seleccionar una Tarifa'
                    })
                }

            },
            viewHotelRates (me) {
                console.log(me);
                this.loadingHotelRates = true;
                this.hotelSwapRates = [];
                let data = {
                    'hotel_id': me.object_id,
                    'date_from': me.date_in,
                    'date_to': me.date_out,
                    'admin': 1
                }

                data.hotels_search_code = me.hotel.channel.find(e => e.type == '2').code;
                data.destiny = {};
                data.destiny.code =  `${me.hotel.country.iso},${me.hotel.state.iso},${me.hotel.city_id}`;
                data.destiny.label = `${me.hotel.country.translations[0].value},${me.hotel.state.translations[0].value},${me.hotel.city.translations[0].value}`;
                data.destiny.typeclass_id = "all";
                data.quantity_rooms = 1;
                data.zero_rates = true;

                const url = window.origin + '/services/hotels/services';
                API({
                    method: 'POST',
                    url: url,
                    data: data
                })
                    .then((result) => {
                        // Los rooms vienen de result.data.data
                        let hotelData = result.data.data;
                        let hotels = Array.isArray(hotelData) ? hotelData : [hotelData];
                        let selectedHotel = hotels.find(e => e.id == me.hotel.id);

                        if (!selectedHotel) {
                            selectedHotel = hotelData;
                        }

                        // Separar rooms por hyperguest_pull (dentro de cada room)
                        let normalRooms = [];
                        let hyperguestRooms = [];

                        if (selectedHotel.rooms && selectedHotel.rooms.length > 0) {
                            selectedHotel.rooms.forEach(room => {
                                // Verificar hyperguest_pull dentro de cada room, si no existe es false
                                const isHyperguest = room.hyperguest_pull === 1 || room.hyperguest_pull === true;

                                if (isHyperguest) {
                                    hyperguestRooms.push(room);
                                } else {
                                    normalRooms.push(room);
                                }
                            });
                        }

                        // Inicializar estructura
                        this.hotelSwapRates = {
                            hyperguest: hyperguestRooms.length > 0 ? 1 : 0,
                            normal: normalRooms.length > 0 ? 1 : 0,
                            rooms: []
                        };

                        // Procesar rooms normales
                        if (normalRooms.length > 0) {
                            this.hotelSwapRates.rooms = normalRooms;

                            // Procesar tarifas para rooms normales
                            for (let r = 0; r < this.hotelSwapRates.rooms.length; r++) {
                                if (this.hotelSwapRates.rooms[r].rates_plan_room && this.hotelSwapRates.rooms[r].rates_plan_room.length > 0) {
                                    for (let r_p_r = 0; r_p_r < this.hotelSwapRates.rooms[r].rates_plan_room.length; r_p_r++) {
                                        if (typeof (this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].showAllRates) === 'undefined') {
                                            this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].showAllRates = 0
                                        }

                                        this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].error_in_calendarys = false
                                        let date_in = moment( me.date_in )
                                        let date_out = moment( me.date_out )
                                        let nights_ = date_out.diff(date_in, 'days')
                                        let errors_ = 0

                                        for(let i=0; i<nights_; i++){
                                            let date_find = date_in.add((i>0)?1:0, 'days').format('YYYY-MM-DD')
                                            let coin = 0
                                            if (this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].calendarys) {
                                                this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].calendarys.forEach( c=>{
                                                    if( c.date == date_find ){
                                                        coin++
                                                    }
                                                })
                                            }
                                            if( coin === 0 ){
                                                errors_++
                                            }
                                        }
                                        if( errors_ > 0 ){
                                            this.hotelSwapRates.rooms[r].rates_plan_room[r_p_r].error_in_calendarys = true
                                        }
                                    }
                                }
                            }

                            this.checkboxs = []
                            me.service_rooms.forEach(s_rooms => {
                                if (s_rooms.rate_plan_room_id) {
                                    this.checkboxs['_' + s_rooms.rate_plan_room_id] = true
                                }
                            })
                        }

                        // Procesar rooms hyperguest
                        if (hyperguestRooms.length > 0) {
                            // Marcar rooms seleccionados para hyperguest
                            let rate_ids = [];
                            if (me.service_rooms_hyperguest && me.service_rooms_hyperguest.length > 0) {
                                rate_ids = me.service_rooms_hyperguest.map(e => {
                                    return { 'ratePlanId' : e.rate_plan_id, 'roomId': e.room.id };
                                });
                            }

                            this.hotelSwapRates.rooms_hyperguest = this.markSelectedRooms(hyperguestRooms, rate_ids);
                        }

                        this.title_rates_hotel = 'Tarifas de Hotel: [' + me.hotel.channel[0].code + '] - ' + me.hotel.name
                        this.$refs['my-modal-select-hotel-rates'].show()
                        this.loadingHotelRates = false;

                    }).catch((e) => {
                    console.log(e)
                    this.loadingHotelRates = false;
                })
            },

            markSelectedRooms(hotelData, selectedItems) {
                return hotelData.map(room => {
                    // Verificar si este room_id está en la lista de seleccionados
                    const roomSelected = selectedItems.some(item => item.roomId === room.room_id);

                    if (roomSelected) {
                        // Si la habitación está seleccionada, procesar sus rates
                        const updatedRates = room.rates.map(rate => {
                            // Verificar si este ratePlanId está en la lista de seleccionados para este roomId
                            let ratePlanId = rate.ratePlanId;
                            const isSelected = selectedItems.some(item =>
                                item.roomId === room.room_id && item.ratePlanId == ratePlanId
                            );

                            // Retornar el rate con el campo selected agregado
                            return {
                                ...rate,
                                selected: isSelected
                            };
                        });

                        // Retornar la habitación con los rates actualizados
                        return {
                            ...room,
                            rates: updatedRates
                        };
                    } else {
                        // Si la habitación no está seleccionada, mantener todo igual con selected: false
                        const updatedRates = room.rates.map(rate => ({
                            ...rate,
                            selected: false
                        }));

                        return {
                            ...room,
                            rates: updatedRates
                        };
                    }
                });
            },
            willCopyCategory () {
                // VALIDAR SI ESTA VACIO
                if (this.categoriesServicesSelected.code == undefined) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('packagesquote.selectcategory')
                    })
                    return
                }
                // VALIDAR SI YA TIENE SERVICIOS
                if (this.servicesAndHotels.length == 0) {
                    this.copyCategory()
                } else {
                    this.$refs['my-modal-confirm'].show()
                }
            },
            copyCategory () {
                this.loading = true
                API({
                    method: 'POST',
                    url: 'package/plan_rates/categories/copy',
                    data: {
                        plan_rate_category_id_from: this.categoriesServicesSelected.code,
                        plan_rate_category_id_to: this.$route.params.category_id
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            // this.reCalculateMarkupRateSale()
                            this.loading = false
                            this.hideModal()
                        } else {

                            this.loading = false
                            if (result.data.success === false && result.data.type == 0) {

                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: 'La categoría que seleccionó no tiene servicios/hoteles'
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.error.messages.connection_error')
                                })
                            }
                        }
                    })
            },
            categoriesServicesChange () {
                this.categoriesWithServicesId = this.categoriesServicesSelected.code
            },
            searchCategoriesWithCountServices () {
                API({
                    method: 'get',
                    url: '/package/plan_rates/' + this.$route.params.package_plan_rate_id +
                        '?lang=' + localStorage.getItem('lang')
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            let categories = result.data.data.plan_rate_categories
                            for (let i = 0; i < categories.length; i++) {
                                // if (this.$route.params.category_id != categories[i].id) {
                                this.categoriesWithServices.push({
                                    code: categories[i].id,
                                    label: categories[i].category.translations[0].value,
                                })
                                // }
                            }
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            showModal (package_service_id, hyperguest = false) {
                this.package_service_id = package_service_id;
                this.modalName = 'Servicio n°: ' + this.package_service_id;
                this.hyperguest_item = hyperguest;
                this.$refs['my-modal'].show()
            },
            showModalRemark(notes) {
                this.modalName = 'Remarks';
                this.remarks = notes;
                this.$refs['my-modal-remark'].show();
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-confirm'].hide()
                this.$refs['my-modal-service-category'].hide()
                this.$refs['my-modal-select-hotel-rates'].hide()
                this.$refs['my-modal-rates-services'].hide()
                this.$refs['my-modal-remark'].hide();
            },
            setPage(page){
                if( page < 1 || page > this.pages_.length ){
                    return;
                }
                this.pageChosen = page
                this.search()
            },
            /**
             * @param serv
             * @returns {boolean} | true -> si tiene errores
             */
            verify_rate_errors(serv){
                // http://aurora_backend.test/#/packages/769/quotes/cost/196/category/612
                if( serv.type === 'hotel' ){

                    // moment().add(5, 'years')
                    let date_in = moment( serv.date_in )
                    let date_out = moment( serv.date_out )
                    let nights_ = date_out.diff(date_in, 'days')

                    let errors = 0

                    for(let i=0; i<nights_; i++){
                        let date_find = date_in.add((i>0)?1:0, 'days').format('YYYY-MM-DD')

                        serv.service_rooms.forEach( sr=>{

                            let coin = 0
                            sr.rate_plan_room.calendarys_in_dates.forEach( c=>{
                                if( c.date == date_find ){
                                    coin++
                                }
                            })

                            if( coin === 0 ){
                                if( serv.id === 29934 ){
                                    console.log(sr.id)
                                }
                                errors++
                            }
                        })
                    }

                    if( errors > 0 ){
                        return true // si hay errores
                    } else {
                        return false // no hay errores
                    }
                } else { // Services
                    return false // no hay errores
                }
            },
            search() {
                this.loading = true
                this.servicesAndHotels = []

                API.get('/package/package_plan_rate_category/' + this.category_id +
                    '?page=' + this.pageChosen + '&limit=' + this.limit + '&with_trashed=1')
                    .then((result) => {
                    let vm = this
                    this.loading = false

                    this.pages_ = []
                    for( let i=0; i<(result.data.count/this.limit); i++){
                        this.page_error[i+1] = ( this.page_error[i+1] )
                        this.pages_.push(i+1)
                    }

                    this.servicesAndHotels = result.data.data
                    this.servicesAndHotels.forEach((item, i) => {
                        vm.$set(item, 'order', (parseInt(i) + 1))
                    })

                    this.refactorServicesAndHotels()

                }).catch(() => {
                    localStorage.setItem('flag_search', 0 )
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })


            },
            updateDestinations () {
                API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
                    console.log('Destinos actualizados')
                }).catch((e) => {
                    console.log(e)
                })
            },
            removeService: function (package_service_id) {
                API.delete('/package/service/' + package_service_id).then((result) => {
                    if (result.data.success === true) {
                        // this.reCalculateMarkupRateSale()
                        this.search()
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
            remove: function () {
                this.removeService(this.package_service_id)
            },
            orderServices: function (servPick, nDirectionOrder) {
                let data = {
                    newOrders: []
                }

                this.tmpServDates[servPick.date_in].forEach(servDate => {
                    if (this.servicesAndHotels[servDate].id == servPick.id) {
                        localStorage.setItem('order_service_date', servDate)
                        localStorage.setItem('order_a', this.servicesAndHotels[servDate].order)
                        localStorage.setItem('order_b', this.servicesAndHotels[servDate + nDirectionOrder].order)

                        // UP -1
                        data.newOrders.push(
                            {
                                id: this.servicesAndHotels[servDate].id,
                                order: this.servicesAndHotels[servDate + nDirectionOrder].order
                            },
                            {
                                id: this.servicesAndHotels[servDate + nDirectionOrder].id,
                                order: this.servicesAndHotels[servDate].order
                            })
                    }
                })

                let service_date = parseInt(localStorage.getItem('order_service_date'))
                this.servicesAndHotels[service_date].order = localStorage.getItem('order_b')
                this.servicesAndHotels[service_date + nDirectionOrder].order = localStorage.getItem('order_a')

                API.post('/package/service/orders', data).then((result) => {
                    if (result.data.success === true) {
                        // this.reCalculateMarkupRateSale()
                        this.refactorServicesAndHotels()

                        setTimeout(() => {
                            localStorage.removeItem('order_service_date')
                            localStorage.removeItem('order_a')
                            localStorage.removeItem('order_b')
                        }, 10)

                        if (servPick.type == 'hotel' && this.legendHotelSecondary) {
                            API.get(window.origin + '/prices?category_id=' + this.category_id).then((result) => {
                                console.log('Tarifas actualizadas')
                                console.log(result)
                            }).catch((e) => {
                                console.log(e)
                            })
                        }
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al reordenar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch((e) => {
                    console.log(e)
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            willChangeCategoryService: function (id, service_id) {
                this.equivalences = []
                this.rateSelected = ''
                this.serviceByRates = [{
                    id: ''
                }]
                this.package_service_id = id
                API.get('service/' + service_id + '/equivalence_associations').then((result) => {
                    if (result.data.success === true) {
                        this.equivalences = result.data.data
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
                this.$refs['my-modal-service-category'].show()
            },
            changeCategoryService: function (package_service_id, service_id) {
                if (this.rateSelected === '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: 'Debe seleccionar una tarifa'
                    })
                } else {
                    let data = {
                        'id': package_service_id,
                        'service_id': service_id,
                        'service_rate_id': this.rateSelected,
                    }
                    API.post('/package/service/change_service', data).then((result) => {
                        if (result.data.success === true) {
                            this.$refs['my-modal-service-category'].hide()
                            this.search()
                            this.updateRates()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error al cambiar el servicio',
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
                }
            },
            updateRates () {
                API.get(window.origin + '/prices?category_id=' + this.$route.params.category_id).then((result) => {
                    // this.reCalculateMarkupRateSale()
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('global.modules.package'),
                        text: this.$t('packages.rates_updated')
                    })
                    console.log('Tarifas actualizadas')
                    this.loadingUpdate = 0
                    console.log(result)
                }).catch((e) => {
                    console.log(e)
                })
            },
            addRate: function (rateSelected) {
                this.rateSelected = rateSelected.id
            },
            toggleViewRates (rate) {
                this.loading = true
                rate.showAllRates = !(rate.showAllRates)
                this.loading = false
            },
            updateDate (props, $event) {
                const newDate = $event.target.value
                const data = {
                    date: newDate,
                    service: props.row,
                }

                API.post('/package/service/date_in', data).then((result) => {
                    if (result.data.success) {
                        const service = result.data.service

                        this.$set(props, 'row', service)
                        this.$set(props.row, 'flag_editing_date', false)
                        const index = this.servicesAndHotels.findIndex(item => item.id === props.row.id)
                        if (index !== -1) {
                            this.$set(this.servicesAndHotels, index, service)
                        }

                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Fecha modificada',
                            text: 'Actualización realizada correctamente'
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch((e) => {
                    console.log(e)
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            toggleInputDate (props) {
                this.$set(props.row, 'flag_editing_date', !props.row.flag_editing_date)
                const index = this.servicesAndHotels.findIndex(item => item.id === props.row.id)
                if (index !== -1) {
                    this.$set(this.servicesAndHotels, index, {
                        ...this.servicesAndHotels[index],
                        flag_editing_date: !this.servicesAndHotels[index].flag_editing_date
                    })
                }
            },
            toggleChecked(props) {
                this.$set(props.row, 'checked', !props.row.checked)
                const index = this.servicesAndHotels.findIndex(item => item.id === props.row.id)
                if (index !== -1) {
                    this.$set(this.servicesAndHotels, index, {
                        ...this.servicesAndHotels[index],
                        checked: !this.servicesAndHotels[index].checked
                    })
                }

                const codes = this.servicesAndHotels.filter((item) => item.checked).map((item) => item.service.aurora_code ?? '')
                localStorage.setItem('services_selected', JSON.stringify(codes))
            },
            handleRowsReordered(event) {
                const data = this.servicesAndHotels.map((item, s) => ({
                    ...item,
                    order: s + 1  // parseInt no es necesario ya que s es número
                })).map((item) => {
                    return {
                        id: item.id,
                        order: item.order,
                    }
                })

                API.post('/package/service/orders', {
                    newOrders: data,
                }).then((result) => {
                    if (result.data.success) {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Cotización modificada',
                            text: 'Órdenes actualizadas correctamente'
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error al reordenar ',
                            text: 'Por favor inténtelo más tarde'
                        })
                    }
                }).catch((e) => {
                    console.log(e)
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            }
        },
        filters: {
            formatDate: function (_date) {
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

<style>
    .legend {
        height: 15px;
        width: 15px;
        display: block;
        float: left;
        margin: 2px;
    }

    .VuePagination__count{
        display: none;
    }

    .container-fluid {
        margin-top: 20px;
    }

    .trService, .trService > th, .trService > td {
        background-color: #dcfeff;
    }

    .trHotel, .trHotel > th, .trHotel > td {
        background-color: #edece7;
    }

    .trService:hover, .trService:hover > th, .trService:hover > td {
        background-color: #c5fcff;
    }

    .trHotel1, .trHotel1 > th, .trHotel1 > td {
        background-color: #fffbd1;
    }

    .change {
        cursor: pointer;
    }

    .left {
        float: left;
    }

    .right {
        float: right;
    }

    .paddingByCount {
        padding-top: 49px;
        padding-left: 18px;
    }

    .paddingByCount1 {
        padding-top: 13px !important;
    }

    .paddingByCount2 {
        padding-top: 21px !important;
    }

    .paddingByCount3 {
        padding-top: 34px !important;
    }

    .rateChoosed_true {
        background: #deffe2;
        border: solid 1px #39573b;
    }

    .rateRow {
        border-radius: 3px;
        padding: 4px 0 4px 6px;
    }

    .trServiceEmptyRate {
        color: #813838;
        background-color: #fee2e1 !important;
        border-color: #fdd6d6 !important;
    }
    .ico-error{
        position: absolute;
        top: 4px;
        left: 17px;
        height: 35px;
    }
    .ico-error-pages{
        position: absolute;
        top: -26px;
        left: 1px;
        height: 30px;
    }
    .error-selection{
        background: #ce3232;
        color: white;
    }
    .tr-error-opacity{
        background-color: #ffdfdf;
        opacity: 0.5;
    }
    .strong-message{
        background: red;
        color: white;
        font-size: 16px;
        position: absolute;
        padding: 0 15px;
        margin-top: -28px;
    }
    .strong-sub-message{
        background: red;
        color: white;
    }
</style>
