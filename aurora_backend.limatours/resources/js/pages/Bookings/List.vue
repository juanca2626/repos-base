<template>
    <div class="container-fluid">
        <div class="b-form-group form-group">
            <div class="form-row">
                <div class="col-sm-2">
                    <label class="col-form-label"># File o # Booking</label>
                    <input class="form-control" id="service_name" name="service_name"
                           placeholder="# File o Número de booking"
                           type="text" v-model="file_code">
                </div>
                <div class="col-sm-3">
                    <label class="col-form-label">Cliente</label>
                    <v-select :options="clients"
                              :value="client_id"
                              @input="clientChange"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Filtro por nombre ó ID del cliente"
                              v-model="clientSelected" name="clients" id="clients" style="height: 35px;">
                        <template slot="option" slot-scope="option">
                            <div class="d-center">
                                <span>{{option.label}}</span>
                            </div>
                        </template>
                        <template slot="selected-option" slot-scope="option">
                            <div class="selected d-center">
                                <span>{{option.label}}</span>
                            </div>
                        </template>
                    </v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Fechas Creación</label>
                    <div class="input-group">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            id="date_from"
                            autocomplete="off"
                            name="date_from" ref="datePickerFrom"
                            v-model="date"
                        >
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Estado de la reserva</label>
                    <select class="form-control" name="status" id="status" v-model="selectedStatus">
                        <option value="">Seleccione el estado</option>
                        <option value="1">Procesando Datos de facturación</option>
                        <option value="2">Procesando File</option>
                        <option value="3">Procesando Asiento contable</option>
                        <option value="9">Procesado</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Error</label>
                    <select class="form-control" name="status_error" id="status_error" v-model="selectedStatusError">
                        <option value="">Seleccione el estado</option>
                        <option value="1">Error</option>
                        <option value="2">Notificación de error a TI</option>
                    </select>
                </div>
                <div class="col-sm-1">
                    <img src="/images/loading.svg" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;"/>
                    <button @click="search" class="btn btn-success" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive-sm vld-parent">
            <loading :active.sync="loading" :isFullPage="false" :can-cancel="false" color="#BD0D12"></loading>
            <table-server :columns="table.columns" :options="tableOptions" :url="urlServices" class="text-center"
                          ref="table">
                <div slot="id" slot-scope="props" class="m-2" style="padding: 5px;">
                    <b-dropdown id="dropdown-left" :text="props.row.id.toString()" variant="primary" class="m-2"
                                v-if="$can('update', 'bookings') || $can('delete', 'bookings')">
                        <b-dropdown-item href="#" @click="update(props.row.id)"
                                         v-if="props.row.status_cron_job_reservation_stella != 9 && props.row.status_cron_job_error != 0 && $can('update', 'bookings')">
                            <i class="fas fa-retweet text-success"></i> Volver a Generar
                        </b-dropdown-item>
                        <b-dropdown-item href="#" @click="will_remove(props.row)"
                                         v-if="props.row.status_cron_job_error != 0 && $can('delete', 'bookings')">
                            <i class="fas fa-trash text-danger"></i> Eliminar
                        </b-dropdown-item>
                    </b-dropdown>
                    <span v-else>
                    {{props.row.id}}
                </span>
                </div>
                <div slot="source" slot-scope="props" class="m-2" style="padding: 5px;">
                <span class="badge badge-primary" v-if="props.row.entity == 'Package'">
                    Paquete <span v-if="props.row.object_id">- {{props.row.object_id}}</span>
                </span>
                    <span class="badge badge-primary" v-if="props.row.entity == 'Quote'">
                    Cotización <span v-if="props.row.object_id">- {{props.row.object_id}}</span>
                </span>

                    <span class="badge badge-primary" v-if="props.row.entity == 'Quote' && props.row.order_number">
                    Pedido - {{props.row.order_number}}
                </span>
                    <span class="badge badge-primary" v-if="props.row.entity == 'Stella'">
                    Stella
                </span>

                    <span class="badge badge-primary" v-if="props.row.entity == 'Cart'">
                    Carrito
                </span>
                </div>
                <div slot="executive_name" slot-scope="props" class="m-2" style="padding: 5px;">
                    <span v-if="props.row.executive">[{{props.row.executive.code}}] - </span>{{props.row.executive_name}}
                    <br>
                    <div v-if="props.row.executive">
                        <a :href="'mailto:'+props.row.executive.email">{{props.row.executive.email}}</a><br>
                        <span class="badge badge-primary" v-if="props.row.executive.markets.length > 0">
                        {{props.row.executive.markets[0].name}}
                    </span>
                    </div>

                </div>
                <div slot="child_row" slot-scope="props" class="m-2" style="padding: 5px;">
                    <b-card no-body>
                        <b-tabs card>
                            <b-navbar toggleable="lg" type="dark" variant="light"
                                      v-if="(props.row.status_cron_job_reservation_stella == 9 && props.row.status_cron_job_send_email == 9) || (props.row.status_cron_job_error === 2) && $can('update', 'bookings')">
                                <button type="button" class="btn btn-success mr-2"
                                        @click="openModalAdminFile(props.row)">
                                    <i class="fas fa-file-signature"></i> Administrar File
                                </button>
                                <button type="button" class="btn btn-success" @click="openModalRensedEmail(props.row)" v-if="props.row.status_cron_job_error === 0">
                                    <i class="fas fa-mail-bulk"></i> Reenviar correos de reserva
                                </button>
                            </b-navbar>
                            <b-tab v-if="props.row.billing">
                                <template #title>
                                    <i class="fas fa-file-invoice"></i> Datos de facturación
                                </template>
                                <b-card-text>
                                    <div class="row">
                                        <div class="col-md-2 text-left">
                                            <label class="form-label font-weight-bold">Codigo cliente(Stella):</label>
                                            <label class="form-control">
                                                {{props.row.billing.client_stella_code}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <label class="form-label font-weight-bold">Nombre:</label>
                                            <label class="form-control">
                                                {{props.row.billing.name}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <label class="form-label font-weight-bold">Apellidos:</label>
                                            <label class="form-control">
                                                {{props.row.billing.surnames}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <label class="form-label font-weight-bold">Telefono:</label>
                                            <label class="form-control">
                                                {{props.row.billing.phone}}
                                            </label>
                                        </div>
                                        <div class="col-md-3 text-left">
                                            <label class="form-label font-weight-bold">Email:</label>
                                            <label class="form-control">
                                                {{props.row.billing.email}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left" v-if="props.row.billing.document_type">
                                            <label class="form-label font-weight-bold">Tipo de documento:</label>
                                            <label class="form-control">
                                                ({{props.row.billing.document_type_id}}) -
                                                {{props.row.billing.document_type.translations[0].value}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left">
                                            <label class="form-label font-weight-bold">Numero documento:</label>
                                            <label class="form-control">
                                                {{props.row.billing.document_number}}
                                            </label>
                                        </div>
                                        <div class="col-md-4 text-left">
                                            <label class="form-label font-weight-bold">Dirección:</label>
                                            <label class="form-control">
                                                {{props.row.billing.address}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left" v-if="props.row.billing.country">
                                            <label class="form-label font-weight-bold">País:</label>
                                            <label class="form-control">
                                                ({{props.row.billing.country_id}}) -
                                                {{props.row.billing.country.translations[0].value}}
                                            </label>
                                        </div>
                                        <div class="col-md-2 text-left" v-if="props.row.billing.state">
                                            <label class="form-label font-weight-bold">Estado:</label>
                                            <label class="form-control">
                                                ({{props.row.billing.state_id}}) -
                                                {{props.row.billing.state.translations[0].value}}
                                            </label>
                                        </div>
                                    </div>
                                </b-card-text>
                            </b-tab>
                            <b-tab active>
                                <template #title>
                                    <i class="fas fa-concierge-bell"></i> Servicios
                                    ({{props.row.reservations_service.length}})
                                </template>
                                <b-card-text>
                                    <table-client :columns="table_service.columns"
                                                  :data="props.row.reservations_service"
                                                  :options="tableOptionsService" id="dataTable"
                                                  theme="bootstrap4">
                                        <div class="table-translations" slot="id" slot-scope="props">
                                            {{props.row.id }}
                                        </div>
                                        <div class="table-translations" slot="service" slot-scope="props">
                                            {{props.row.service_id }} - [{{props.row.service_code}}] {{
                                            props.row.service_name }}
                                            <span class="badge badge-secondary"
                                                  v-if="props.row.optional">Opcional</span>
                                            <br>
                                            <hr>
                                            ({{props.row.service_rate_id }})
                                            {{props.row.reservations_service_rates_plans[0].service_rate_name }} /
                                            <span class="badge badge-light">
                                            {{props.row.executive_code }} - {{props.row.executive_name }}
                                        </span>
                                        </div>
                                        <div class="table-translations" slot="tipo" slot-scope="props">
                                            {{props.row.type_service }}
                                        </div>
                                        <div class="table-translations" slot="adultos" slot-scope="props">
                                            {{props.row.adult_num }}
                                        </div>
                                        <div class="table-translations" slot="ninos" slot-scope="props">
                                            {{props.row.child_num }}
                                        </div>
                                        <div class="table-translations" slot="fecha" slot-scope="props">
                                            {{props.row.date | formatDate}}<br>
                                            <span class="badge badge-light">
                                            <i class="far fa-clock"></i> {{props.row.time}}
                                        </span>
                                        </div>
                                        <div class="table-translations" slot="total" slot-scope="props"
                                             style="width: 100px;">
                                            {{props.row.total_amount | currency}}
                                        </div>
                                        <div class="table-translations" slot="status_email" slot-scope="props"
                                             style="width: 100px;">
                                        <span class="text-success" v-if="props.row.status_email == 1">
                                           <i class="far fa-check-circle fa-1x"></i> Enviado
                                        </span>
                                            <span class="text-warning" v-if="props.row.status_email == 0">
                                            <i class="far fa-clock fa-1x"></i> Procesando
                                        </span>
                                        </div>
                                    </table-client>
                                </b-card-text>
                            </b-tab>
                            <b-tab>
                                <template #title>
                                    <i class="fas fa-hotel"></i> Hoteles ({{props.row.reservations_hotel.length}})
                                </template>
                                <b-card-text>
                                    <table-client :columns="table_hotel.columns" :data="props.row.reservations_hotel"
                                                  :options="tableOptionsHotel" id="dataTable"
                                                  theme="bootstrap4">
                                        <div slot="child_row" slot-scope="props" class="m-2" style="padding: 5px;">
                                            <table-client :columns="table_hotel_rooms.columns"
                                                          :data="props.row.reservations_hotel_rooms"
                                                          :options="tableOptionsHotelRooms" id="dataTable"
                                                          theme="bootstrap4">
                                                <div class="table-translations" slot="id" slot-scope="props">
                                                    {{props.row.id }}
                                                </div>
                                                <div class="table-translations" slot="channel" slot-scope="props">
                                                    {{props.row.channel_code }} <br />
                                                    <span class="badge badge-success"  v-if="props.row.channel_reservation_code_master && props.row.channel_id === 6  && props.row.onRequest == 1 && !props.row.bocking_updated_at">
                                                        <span> Channel Hypergues : {{ props.row.channel_reservation_code_master }} </span>
                                                    </span>
                                                    <span class="badge badge-warning"  v-if="props.row.channel_reservation_code_master && props.row.channel_id === 1  && props.row.onRequest == 1 && props.row.bocking_updated_at">
                                                        <span v-b-tooltip.hover.html="'Actualizado desde aurora el día ' + props.row.bocking_updated_at"> Channel Aurora : {{ props.row.channel_reservation_code_master }} </span>
                                                    </span>
                                                </div>
                                                <div class="table-translations" slot="check_in" slot-scope="props">
                                                    {{props.row.check_in | formatDate}}
                                                </div>
                                                <div class="table-translations" slot="check_out" slot-scope="props">
                                                    {{props.row.check_out | formatDate}}
                                                </div>
                                                <div class="table-translations" slot="adultos" slot-scope="props">
                                                    {{props.row.adult_num }}
                                                </div>
                                                <div class="table-translations" slot="ninos" slot-scope="props">
                                                    {{props.row.child_num }}
                                                </div>
                                                <div class="table-translations" slot="noches" slot-scope="props">
                                                    {{props.row.nights }}
                                                </div>
                                                <div class="table-translations" slot="rate" slot-scope="props">
                                                    ({{props.row.rates_plan_id }}){{props.row.rate_plan_name }}
                                                    <span class="badge badge-light">
                                                    {{props.row.executive_name}}
                                                </span>
                                                </div>
                                                <div class="table-translations" slot="room_name" slot-scope="props">
                                                    (<span
                                                    title="rates_plans_room_id">{{props.row.rates_plans_room_id }}</span>
                                                    <span title="room_id">/ {{props.row.room_id }}</span>)<br>
                                                    {{props.row.room_name }}
                                                </div>
                                                <div class="table-translations" slot="total_amount" slot-scope="props">
                                                    {{props.row.total_amount | currency}}
                                                </div>
                                                <div class="table-translations" slot="on_request" slot-scope="props">
                                                    <span class="badge badge-success" v-if="props.row.onRequest == 1">OK</span>
                                                    <span class="badge badge-danger" v-if="props.row.onRequest == 0">RQ</span>
                                                </div>
                                                <div class="table-translations" slot="status" slot-scope="props">
                                                    <span class="badge badge-success" v-if="props.row.status == 1">Activo</span>
                                                    <span class="badge badge-danger" v-if="props.row.status == 0"  v-b-tooltip.hover.html="props.row.cancel_details" >Cancelado</span>
                                                </div>
                                                <div class="table-translations" slot="total" slot-scope="props">
                                                    {{props.row.total_amount | currency}}
                                                </div>
                                                <div slot="child_row" slot-scope="props" class="m-2"
                                                     style="padding: 5px;">
                                                    <table-client :columns="table_hotel_rooms_calendars.columns"
                                                                  :data="props.row.reservations_hotels_calendarys"
                                                                  :options="tableOptionsHotelRoomsCalendars"
                                                                  id="dataTable"
                                                                  theme="bootstrap4">
                                                        <div class="table-translations" slot="id" slot-scope="props"
                                                             style="padding: 10px">
                                                            {{props.row.id}}
                                                        </div>
                                                        <div class="table-translations" slot="date" slot-scope="props">
                                                            {{props.row.date | formatDate}}
                                                        </div>
                                                        <div class="table-translations" slot="rate" slot-scope="props">
                                                            ({{props.row.rates_plan_id }}) {{props.row.rate_plan_name }}

                                                        </div>
                                                        <div class="table-translations" slot="total_amount_base"
                                                             slot-scope="props">
                                                            {{props.row.total_amount_base | currency}}
                                                        </div>
                                                        <div class="table-translations" slot="total_amount"
                                                             slot-scope="props">
                                                            {{props.row.total_amount | currency}}
                                                        </div>
                                                        <div class="table-translations" slot="hotel_cancel_policies"
                                                             slot-scope="props">
                                                            ({{ props.row.policies_rates.id }}){{
                                                            props.row.policies_rates.name }}
                                                        </div>
                                                        <div slot="child_row" slot-scope="props" class="m-2"
                                                             style="padding: 5px;">
                                                            <table-client
                                                                :columns="table_hotel_rooms_calendar_rate.columns"
                                                                :data="props.row.reservation_hotel_room_date_rate"
                                                                :options="tableOptionsHotelRoomsCalendarRate"
                                                                id="dataTable"
                                                                theme="bootstrap4">
                                                                <div class="table-translations" slot="id"
                                                                     slot-scope="props" style="padding: 10px">
                                                                    {{props.row.id}}
                                                                </div>
                                                                <div class="table-translations" slot="price_adult"
                                                                     slot-scope="props">
                                                                    {{props.row.price_adult | currency}}
                                                                </div>
                                                                <div class="table-translations" slot="price_adult_base"
                                                                     slot-scope="props">
                                                                    {{props.row.price_adult_base | currency}}
                                                                </div>

                                                                <div class="table-translations" slot="price_child"
                                                                     slot-scope="props">
                                                                    {{props.row.price_child | currency}}
                                                                </div>
                                                                <div class="table-translations" slot="price_child_base"
                                                                     slot-scope="props">
                                                                    {{props.row.price_child_base | currency}}
                                                                </div>

                                                                <div class="table-translations" slot="price_infant"
                                                                     slot-scope="props">
                                                                    {{props.row.price_infant | currency}}
                                                                </div>
                                                                <div class="table-translations" slot="price_infant_base"
                                                                     slot-scope="props">
                                                                    {{props.row.price_infant_base | currency}}
                                                                </div>

                                                                <div class="table-translations" slot="price_extra"
                                                                     slot-scope="props">
                                                                    {{props.row.price_extra | currency}}
                                                                </div>
                                                                <div class="table-translations" slot="price_extra_base"
                                                                     slot-scope="props">
                                                                    {{props.row.price_extra_base | currency}}
                                                                </div>


                                                            </table-client>


                                                        </div>


                                                    </table-client>


                                                </div>
                                            </table-client>


                                        </div>
                                        <div class="table-translations" slot="id" slot-scope="props"
                                             style="padding: 15px">
                                            {{props.row.id }}
                                        </div>
                                        <div class="table-translations" slot="hotel" slot-scope="props">
                                            {{props.row.hotel_id }} - [{{props.row.hotel_code}}] {{ props.row.hotel_name
                                            }}
                                        </div>
                                        <div class="table-translations" slot="check_in" slot-scope="props">
                                            {{props.row.check_in | formatDate}}
                                        </div>
                                        <div class="table-translations" slot="check_out" slot-scope="props">
                                            {{props.row.check_out | formatDate}}
                                        </div>
                                        <div class="table-translations" slot="noches" slot-scope="props">
                                            {{props.row.nights }}
                                        </div>
                                        <div class="table-translations" slot="total" slot-scope="props">
                                            {{props.row.total_amount | currency}}
                                        </div>
                                        <div class="table-translations" slot="status" slot-scope="props">
                                        <span class="badge badge-warning" v-if="props.row.status === 3">
                                            SIN CONFIRMAR
                                        </span>
                                            <span class="badge badge-success"
                                                  v-if="props.row.status === 1 || props.row.status === 2">
                                            CONFIRMADO
                                        </span>
                                            <span class="badge badge-danger" v-if="props.row.status === 0">
                                            CANCELADO
                                        </span>
                                        </div>
                                        <div class="table-translations" slot="status_email" slot-scope="props"
                                             style="width: 100px;">
                                         <span class="text-success" v-if="props.row.status_email == 1">
                                           <i class="far fa-check-circle fa-1x"></i> Enviado
                                        </span>
                                            <span class="text-warning" v-if="props.row.status_email == 0">
                                            <i class="far fa-clock fa-1x"></i> Procesando
                                        </span>
                                        </div>
                                    </table-client>
                                </b-card-text>
                            </b-tab>
                            <b-tab>
                                <template #title>
                                    <i class="fas fa-plane-departure"></i> Vuelos
                                    ({{props.row.reservations_flight.length}})
                                </template>
                                <b-card-text>
                                    <table-client :columns="table_flight.columns" :data="props.row.reservations_flight"
                                                  :options="tableOptionsflights" id="dataTable"
                                                  theme="bootstrap4">
                                        <div class="table-translations" slot="id" slot-scope="props"
                                             style="padding: 15px">
                                            {{props.row.id }}
                                        </div>
                                        <div class="table-translations" slot="fecha" slot-scope="props">
                                            {{props.row.date | formatDate}}
                                        </div>
                                        <div class="table-translations" slot="adultos" slot-scope="props">
                                            {{props.row.adult_num }}
                                        </div>
                                        <div class="table-translations" slot="ninos" slot-scope="props">
                                            {{props.row.child_num }}
                                        </div>
                                        <div class="table-translations" slot="status_email" slot-scope="props"
                                             style="width: 100px;">
                                            <i class="far fa-check-circle text-success"
                                               v-if="props.row.status_email == 1"></i>
                                            <i class="far fa-clock text-warning" v-if="props.row.status_email == 0"></i>
                                        </div>
                                    </table-client>
                                </b-card-text>
                            </b-tab>
                            <b-tab v-if="props.row.reservations_package.length > 0">
                                <template #title>
                                    <i class="fas fa-route"></i> Paquetes ({{props.row.reservations_package.length}})
                                </template>
                                <div class="row">
                                    <div class="col-md-4 text-left">
                                        <label class="form-label font-weight-bold">Nombre del paquete:</label>
                                        <label class="form-control">
                                            ({{props.row.reservations_package[0].package_id}})
                                            {{props.row.reservations_package[0].package.translations[0].tradename}}
                                        </label>
                                    </div>
                                    <div class="col-md-2 text-left">
                                        <label class="form-label font-weight-bold">Categoria:</label>
                                        <label class="form-control">
                                            {{props.row.reservations_package[0].type_class.translations[0].value}}
                                        </label>
                                    </div>
                                    <div class="col-md-2 text-left">
                                        <label class="form-label font-weight-bold">Tipo:</label>
                                        <label class="form-control">
                                            {{props.row.reservations_package[0].service_type.translations[0].value}}
                                        </label>
                                    </div>
                                    <div class="col-md-2 text-left">
                                        <label class="form-label font-weight-bold">Cantidad Adultos:</label>
                                        <label class="form-control">
                                            {{props.row.reservations_package[0].quantity_adults}}
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 text-left">
                                        <label class="form-label font-weight-bold">Cantidad Niño con cama:</label>
                                        <label class="form-control">
                                            {{props.row.reservations_package[0].quantity_child_with_bed}}
                                        </label>
                                    </div>
                                    <div class="col-md-3 text-left">
                                        <label class="form-label font-weight-bold">Cantidad Niño sin cama:</label>
                                        <label class="form-control">
                                            {{props.row.reservations_package[0].quantity_child_without_bed}}
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-left">
                                        <label class="form-label font-weight-bold">Acomodación:</label>
                                        <hr>
                                    </div>
                                    <div class="col-md-6 text-left">
                                        <label class="form-label font-weight-bold">Precios:</label>
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 text-left">
                                        <div class="col-md-12 mt-3">
                                            <div class="row">
                                                <label class="col-sm-2 col-form-label font-weight-bold">Simple:</label>
                                                <div class="col-sm-10">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].quantity_sgl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-2 col-form-label font-weight-bold">Doble:</label>
                                                <div class="col-sm-10">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].quantity_dbl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-2 col-form-label font-weight-bold">Triple:</label>
                                                <div class="col-sm-10">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].quantity_tpl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-left">
                                        <div class="col-md-12 mt-3">
                                            <div class="row">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Adulto en
                                                    simple:</label>
                                                <div class="col-sm-8">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].price_per_adult_sgl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Adulto en
                                                    doble:</label>
                                                <div class="col-sm-8">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].price_per_adult_dbl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Adulto en
                                                    triple:</label>
                                                <div class="col-sm-8">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].price_per_adult_tpl}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Niño con
                                                    cama:</label>
                                                <div class="col-sm-8">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].price_per_child_with_bed}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <div class="row">
                                                <label class="col-sm-4 col-form-label font-weight-bold">Niño sin
                                                    cama:</label>
                                                <div class="col-sm-8">
                                                    <label class="form-control">
                                                        {{props.row.reservations_package[0].price_per_child_without_bed}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </b-tab>
                            <b-tab>
                                <template #title>
                                    <i class="fas fa-users"></i> Pasajeros ({{props.row.reservations_passenger.length}})
                                </template>
                                <b-card-text>
                                    <table-client :columns="table_passengers.columns"
                                                  :data="props.row.reservations_passenger"
                                                  :options="tableOptionsPassengers" id="dataTable"
                                                  theme="bootstrap4">
                                        <div class="table-translations" slot="id" slot-scope="props"
                                             style="padding: 15px">
                                            {{props.row.id }}
                                        </div>
                                        <div class="table-translations" slot="date_birth" slot-scope="props">
                                            {{props.row.date_birth | formatDate}}
                                        </div>
                                        <div class="table-translations" slot="document_url" slot-scope="props">

                                            <template v-if="props.row.document_url">

                                                <a :href="props.row.document_url" v-if="['jpg', 'png', 'jpeg'].includes(getFileExtension(props.row.document_url))" target="_blank">
                                                    <img :src="props.row.document_url" width="50"  />
                                                </a>
                                                <a :href="props.row.document_url"  target="_blank" v-else>
                                                    <img src="/images/document.jpg" width="50" />
                                                </a>

                                            </template>

                                        </div>

                                    </table-client>
                                </b-card-text>
                            </b-tab>
                            <b-tab>
                                <template #title>
                                    <i class="fas fa-mail-bulk"></i> Email Logs
                                    ({{props.row.reservations_email_logs.length}})
                                </template>
                                <b-card-text>
                                    <table-client :columns="table_email_logs.columns"
                                                  :data="props.row.reservations_email_logs"
                                                  :options="tableOptionsEmailLogs" id="dataTable"
                                                  theme="bootstrap4">
                                        <div class="table-translations" slot="id" slot-scope="props">
                                            {{props.row.id }}
                                        </div>
                                        <div class="table-translations" slot="email_type" slot-scope="props">
                                        <span class="badge badge-success"
                                              v-if="props.row.email_type == 'confirmation' ">CONFIRMACIÓN DE RESERVA</span>
                                            <span class="badge badge-danger"
                                                  v-if="props.row.email_type == 'cancellation' ">CANCELACIÓN DE RESERVA</span>
                                            <span class="badge badge-danger"
                                                  v-if="props.row.email_type == 'cancellation_partial' ">CANCELACIÓN DE RESERVA X HABITACIÓN</span>
                                        </div>

                                        <div class="table-translations" slot="email_to" slot-scope="props">
                                            <span class="font-weight-bold" v-if="props.row.email_to == 'executive' ">EJECUTIVAS</span>
                                            <span class="font-weight-bold"
                                                  v-if="props.row.email_to == 'client' ">CLIENTE</span>
                                            <span class="font-weight-bold"
                                                  v-if="props.row.email_to == 'hotel' ">HOTEL</span>
                                            <span class="font-weight-bold"
                                                  v-if="props.row.email_to == 'kam' ">KAM</span>
                                        </div>
                                        <div class="table-translations" slot="emails" slot-scope="props">
                                            <div class="row">
                                                <div class="col-6"
                                                     v-if="props.row.emails && props.row.emails.to && props.row.emails.to.length > 0">
                                                    <p class="font-weight-bold text-left mb-1">Enviado a:</p>
                                                    <p class="mb-1 text-left" v-for="email in props.row.emails.to"><i
                                                        class="fas fa-user-check text-success"></i> {{email}}</p>
                                                </div>
                                                <div class="col-6"
                                                     v-if="props.row.emails && props.row.emails.cc && props.row.emails.cc.length > 0">
                                                    <p class="font-weight-bold text-left mb-1">Copiado a:</p>
                                                    <p class="mb-1 text-left" v-for="email in props.row.emails.cc"><i
                                                        class="fas fa-user-check text-success"></i> {{email}}</p>
                                                </div>
                                            </div>

                                        </div>
                                    </table-client>
                                </b-card-text>
                            </b-tab>
                            <b-tab>
                                <template #title>
                                    <i class="fas fa-clipboard-list"></i> Logs
                                </template>
                                <div class="accordion" role="tablist">
                                    <div class="vld-parent">
                                        <loading :active.sync="loading_logs" :can-cancel="false"
                                                 color="#BD0D12"></loading>
                                        <div class="row">
                                            <div class="col mb-3">
                                                <button type="button" @click="getLogReservations(props.row)"
                                                        class="btn btn-danger float-left">
                                                    Cargar archivos de log <br>
                                                    <small>(Solo se cargaran los 5 ultimos)</small>
                                                </button>
                                            </div>
                                        </div>
                                        <b-card no-body class="mb-1" v-for="(log,index) in props.row.logs" :key="index">
                                            <b-card-header header-tag="header" class="p-1" role="tab">
                                                <b-button block v-b-toggle="'accordion-'+index" variant="default">
                                                <span v-if="log.method_name == 'RequestCreateFile'"
                                                      class="text-danger font-weight-bold">
                                                    Request - Crear el File (FrontEnd)
                                                </span>
                                                    <span v-if="log.method_name == 'CreandoCliente'"
                                                          class="text-danger font-weight-bold">
                                                    Request/Response - Crear el Cliente en Stella
                                                </span>
                                                    <span v-if="log.method_name == 'CreandoFile'"
                                                          class="text-danger font-weight-bold">
                                                    Request/Response - Crear el File en Stella
                                                </span>
                                                    <span v-if="log.method_name == 'CancelaFile'"
                                                          class="text-danger font-weight-bold">
                                                    Request/Response - Cancelación File en Stella
                                                </span>
                                                    <br>
                                                    {{log.log_directory}} - {{log.created_at}}
                                                </b-button>
                                            </b-card-header>
                                            <b-collapse :id="'accordion-'+index" visible accordion="my-accordion"
                                                        role="tabpanel">
                                                <b-card-body>
                                                    <b-tabs content-class="mt-3">
                                                        <b-tab title="Request" active>
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <button type="button"
                                                                            class="btn btn-success float-left mr-2"
                                                                            @click="copy(log.log_request)">
                                                                        <i class="fas fa-copy"></i> Copiar
                                                                    </button>
                                                                    <button type="button"
                                                                            class="btn btn-success float-left mr-2"
                                                                            @click="downloadJson(log.log_request,'request')">
                                                                        <i class="fas fa-file-download"></i> Descargar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <vue-json-pretty :data="log.log_request" :deep="2"
                                                                             :showLength="true"></vue-json-pretty>
                                                        </b-tab>
                                                        <b-tab title="Response"
                                                               v-if="log.method_name != 'RequestCreateFile'">
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <button type="button"
                                                                            class="btn btn-success float-left mr-2"
                                                                            @click="copy(log.log_response)">
                                                                        <i class="fas fa-copy"></i> Copiar
                                                                    </button>
                                                                    <button type="button"
                                                                            class="btn btn-success float-left mr-2"
                                                                            @click="downloadJson(log.log_response,'response')">
                                                                        <i class="fas fa-file-download"></i> Descargar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <vue-json-pretty :data="log.log_response"></vue-json-pretty>
                                                        </b-tab>
                                                    </b-tabs>
                                                </b-card-body>
                                            </b-collapse>
                                        </b-card>
                                    </div>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </b-card>
                </div>
                <div class="table-service_ubigeo" slot="file_code" slot-scope="props" style="font-size: 0.9em;">
                    <span class="font-weight-bold text-primary"> {{props.row.file_code}}</span><br>
                    <span title="Version" class="badge badge-info">({{props.row.version}})</span><br>
                    <span title="Contiene datos de facturación" class="badge badge-pill badge-warning"
                          v-if="props.row.billing">
                    <i class="fas fa-file-invoice"></i>
                </span>
                </div>
                <div class="table-service_ubigeo" slot="client_code" slot-scope="props" style="font-size: 0.9em">
                    <span class="font-weight-bold">{{props.row.client_code}}</span> -
                    <span>{{props.row.client.name}}</span>
                </div>
                <div class="table-service_ubigeo" slot="reservator_type" slot-scope="props" style="font-size: 0.9em">
                <span class="badge badge-primary" style="font-size: 12px"
                      v-if="props.row.reservator_type == 'excecutive'"> EJECUTIVO </span>
                    <span class="badge badge-secondary" style="font-size: 12px"
                          v-if="props.row.reservator_type == 'client'"> CLIENTE </span>
                    <br>
                    <span v-if="props.row.reservator_type == 'client'" style="font-size: 10px">
                    {{props.row.create_user.code}} - {{props.row.create_user.name}}
                </span>
                </div>
                <div class="table-service_ubigeo" slot="total_amount" slot-scope="props" style="font-size: 0.9em">
                    <span> {{props.row.total_amount | currency}}</span>
                </div>
                <div class="table-service_date_init" slot="date_init" slot-scope="props"
                     style="font-size: 0.9em;with:67px;">
                    <span> {{props.row.date_init | formatDate}}</span>
                </div>
                <div class="table-status" slot="status_file" slot-scope="props" style="font-size: 0.9em;padding: 5px;">
                    <div class="badge font-badge bg-warning text-dark"
                         v-if="props.row.status_cron_job_reservation_stella == 1 && props.row.status_cron_job_error == 0">
                        <i class="fa fa-spin fa-spinner"></i> Procesando <br>Datos facturación...
                    </div>
                    <div class="badge font-badge bg-warning text-dark"
                         v-if="props.row.status_cron_job_reservation_stella == 2 && props.row.status_cron_job_error == 0">
                        <i class="fa fa-spin fa-spinner"></i> Procesando File...
                    </div>
                    <div class="badge font-badge bg-warning text-dark"
                         v-if="props.row.status_cron_job_reservation_stella == 3 && props.row.status_cron_job_error == 0">
                        <i class="fa fa-spin fa-spinner"></i> Procesando <br>Asiento contable...
                    </div>
                    <div class="badge font-badge bg-success"
                         v-if="props.row.status_cron_job_reservation_stella == 9 && props.row.status_cron_job_error == 0">
                        <i class="fas fa-check-circle"></i> Procesado
                    </div>
                    <div class="badge font-badge bg-danger" v-if="props.row.status_cron_job_error == 1">
                        <i class="fas fa-bomb"></i> Error al procesar <br>
                        <span
                            v-if="props.row.status_cron_job_reservation_stella == 1"> (Creación datos facturacion)</span>
                        <span v-if="props.row.status_cron_job_reservation_stella == 2"> (Creación File)</span>
                        <span
                            v-if="props.row.status_cron_job_reservation_stella == 3"> (Creación Asiento Contable)</span>
                    </div>
                    <div class="badge font-badge bg-danger" v-if="props.row.status_cron_job_error == 2">
                        <i class="fas fa-check-double"></i> Se envío el error <br>
                        <span
                            v-if="props.row.status_cron_job_reservation_stella == 1"> (Creación datos facturacion)</span>
                        <span v-if="props.row.status_cron_job_reservation_stella == 2"> (Creación File)</span>
                        <span
                            v-if="props.row.status_cron_job_reservation_stella == 3"> (Creación Asiento Contable)</span>
                    </div>
                </div>
                <div class="table-status" slot="status_email" slot-scope="props" style="font-size: 0.9em;padding: 5px;">
                    <div class="badge font-badge bg-warning text-dark"
                         v-if="props.row.status_cron_job_send_email == 0">
                        <i class="fa fa-spin fa-spinner"></i> En espera...
                    </div>
                    <div class="badge font-badge bg-warning text-dark"
                         v-if="props.row.status_cron_job_send_email == 1">
                        <i class="fa fa-spin fa-spinner"></i> Procesando...
                    </div>
                    <div class="badge font-badge bg-success text-white"
                         v-if="props.row.status_cron_job_send_email == 9">
                        <i class="fas fa-check-circle"></i> Enviado
                    </div>
                </div>
            </table-server>
        </div>
        <b-modal :title="reservationName" centered ref="my-modal" size="sm">
            <p class="text-center">¿Desea eliminar la reserva?</p>
            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal :title="reservationName" centered ref="my-modal-resend-email" size="xl">
            <div class="alert alert-info mb-3">Seleccione los servicios/hoteles que desea reenviar.</div>
            <b-card no-body>
                <b-tabs card>
                    <b-tab active>
                        <template #title>
                            <i class="fas fa-concierge-bell"></i> Servicios
                            ({{reservation_resend.reservations_service.length}})
                        </template>
                        <b-card-text>
                            <table-client :columns="table_service_opt.columns"
                                          :data="reservation_resend.reservations_service"
                                          :options="tableOptionsServiceOpt" id="dataTable" theme="bootstrap4">
                                <div class="table-translations" slot="id" slot-scope="props">
                                    {{props.row.id }}
                                </div>
                                <div class="table-translations" slot="service" slot-scope="props">
                                    {{props.row.service_id }} - [{{props.row.service_code}}] {{
                                    props.row.service_name }}
                                    <span class="badge badge-secondary" v-if="props.row.optional">Opcional</span>
                                </div>
                                <div class="table-translations" slot="adultos" slot-scope="props">
                                    {{props.row.adult_num }}
                                </div>
                                <div class="table-translations" slot="ninos" slot-scope="props">
                                    {{props.row.child_num }}
                                </div>
                                <div class="table-translations" slot="fecha" slot-scope="props">
                                    {{props.row.date | formatDate}}<br>
                                    <span class="badge badge-light">
                                            <i class="far fa-clock"></i> {{props.row.time}}
                                        </span>
                                </div>
                                <div class="table-translations" slot="total" slot-scope="props"
                                     style="width: 100px;">
                                    {{props.row.total_amount | currency}}
                                </div>
                                <b-form-checkbox
                                    v-model="resend_email_services"
                                    :checked="props.row.resend_email_enable"
                                    :id="'checkbox_service_'+props.row.id"
                                    :name="'checkbox_service_'+props.row.id"
                                    :value="props.row.id"
                                    :key="props.row.id"
                                    slot-scope="props"
                                    slot="option"
                                    switch>
                                </b-form-checkbox>
                            </table-client>
                        </b-card-text>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <i class="fas fa-hotel"></i> Hoteles ({{reservation_resend.reservations_hotel.length}})
                        </template>
                        <b-card-text>
                            <table-client :columns="table_hotel_opt.columns"
                                          :data="reservation_resend.reservations_hotel"
                                          :options="tableOptionsHotelOpt" id="dataTable"
                                          theme="bootstrap4">
                                <div class="table-translations" slot="id" slot-scope="props" style="padding: 15px">
                                    {{props.row.id }}
                                </div>
                                <div class="table-translations" slot="hotel" slot-scope="props">
                                    {{props.row.hotel_id }} - [{{props.row.hotel_code}}] {{ props.row.hotel_name }}
                                </div>
                                <div class="table-translations" slot="check_in" slot-scope="props">
                                    {{props.row.check_in | formatDate}}
                                </div>
                                <div class="table-translations" slot="check_out" slot-scope="props">
                                    {{props.row.check_out | formatDate}}
                                </div>
                                <div class="table-translations" slot="noches" slot-scope="props">
                                    {{props.row.nights }}
                                </div>
                                <div class="table-translations" slot="total" slot-scope="props">
                                    {{props.row.total_amount | currency}}
                                </div>
                                <div class="table-translations" slot="status" slot-scope="props">
                                        <span class="badge badge-warning" v-if="props.row.status === 3">
                                            SIN CONFIRMAR
                                        </span>
                                    <span class="badge badge-success"
                                          v-if="props.row.status === 1 || props.row.status === 2">
                                            CONFIRMADO
                                        </span>
                                    <span class="badge badge-danger" v-if="props.row.status === 0">
                                            CANCELADO
                                        </span>
                                </div>
                                <b-form-checkbox
                                    v-model="resend_email_hotels"
                                    :checked="props.row.resend_email_enable"
                                    :id="'checkbox_hotel_'+props.row.id"
                                    :name="'checkbox_hotel_'+props.row.id"
                                    :value="props.row.id"
                                    :key="props.row.id"
                                    slot-scope="props"
                                    slot="option"
                                    switch>
                                </b-form-checkbox>
                            </table-client>
                        </b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div slot="modal-footer">
                <button @click="processResendEmail()" class="btn btn-success" :disabled="loading_resend_emails">
                    <span v-if="loading_resend_emails">
                        <i class="fa fa-spin fa-spinner"></i> Procesando...
                    </span>
                    <span v-else>
                        <i class="fas fa-paper-plane"></i> Enviar
                    </span>
                </button>
                <button @click="hideModalResendEmail()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal :title="reservationName" centered ref="my-modal-admin-file" size="xl">
            <b-card no-body>
                <b-tabs card>
                    <b-tab active>
                        <template #title>
                            <i class="fas fa-file-signature"></i> File
                        </template>
                        <b-card-text v-if="reservations">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>FILE:</strong> {{reservations.file_code}}</p>
                                </div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4 text-right">
                                    <p><strong>FEC. CREACIÓN:</strong> {{reservations.created_at}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-3">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="font-weight-bold mb-1">ID/CODIGO/CLIENTE:</p>
                                    <p v-if="!formEditClient"> {{reservations.client_id}} /
                                        {{reservations.client_code}} / {{reservations.client.name}}
                                        <button type="button" class="btn btn-success btn-sm ml-1"
                                                @click="willEditFile('client')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </p>
                                    <div v-show="formEditClient">
                                        <v-select :options="form_clients"
                                                  :value="form_client_id"
                                                  @input="formClientChange"
                                                  label="name" :filterable="false" @search="onSearch"
                                                  placeholder="Filtro por nombre ó codigo del cliente"
                                                  v-model="form_clientSelected" name="edit_clients" id="edit_clients"
                                                  autocomplete="true">
                                            <template slot="option" slot-scope="option">
                                                <div class="d-center">
                                                    <span>{{option.label}}</span>
                                                </div>
                                            </template>
                                            <template slot="selected-option" slot-scope="option">
                                                <div class="selected d-center">
                                                    <span>{{option.label}}</span>
                                                </div>
                                            </template>
                                        </v-select>
                                        <div class="col-12 mt-2 p-0">
                                            <button type="button" class="btn btn-success btn-sm"
                                                    :disabled="loading_edit"
                                                    @click="editFile('client')">
                                             <span v-if="loading_edit">
                                                <i class="fa fa-spin fa-spinner"></i> Procesando...
                                            </span>
                                                <span v-else>
                                                <i class="fas fa-save"></i> Guardar
                                            </span>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm ml-1"
                                                    :disabled="loading_edit"
                                                    @click="cancelFile('client')">
                                                <i class="fas fa-times-circle"></i> Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <p class="font-weight-bold mb-1">ID/CODIGO/EJEC.:</p>
                                    <p v-if="!formEditExecutive">{{reservations.executive_id}} /
                                        {{reservations.executive.code}} / {{reservations.executive_name}}
                                        <button type="button" class="btn btn-success btn-sm ml-1"
                                                @click="willEditFile('executive')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </p>
                                    <div v-show="formEditExecutive">
                                        <v-select :options="executives"
                                                  :value="executive_id"
                                                  @input="executivesChange"
                                                  label="name" :filterable="false" @search="onSearchExecutives"
                                                  placeholder="Filtro por nombre ó ID del ejecutivo"
                                                  v-model="executiveSelected" name="executives" id="executives"
                                                  autocomplete="true"
                                                  style="height: 35px;">
                                            <template slot="option" slot-scope="option">
                                                <div class="d-center">
                                                    <span>{{option.label}}</span>
                                                </div>
                                            </template>
                                            <template slot="selected-option" slot-scope="option">
                                                <div class="selected d-center">
                                                    <span>{{option.label}}</span>
                                                </div>
                                            </template>
                                        </v-select>
                                        <div class="col-12 mt-2 p-0">
                                            <button type="button" class="btn btn-success btn-sm"
                                                    :disabled="loading_edit"
                                                    @click="editFile('executive')">
                                             <span v-if="loading_edit">
                                                <i class="fa fa-spin fa-spinner"></i> Procesando...
                                            </span>
                                                <span v-else>
                                                <i class="fas fa-save"></i> Guardar
                                            </span>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm ml-1"
                                                    :disabled="loading_edit"
                                                    @click="cancelFile('executive')">
                                                <i class="fas fa-times-circle"></i> Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <p class="font-weight-bold mb-1">NOMBRE/GRUPO:</p>
                                    <p>{{reservations.customer_name}}</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <p class="font-weight-bold mb-1">FECHA INICIO FILE:</p>
                                    <p>{{reservations.date_init}}</p>
                                </div>
                            </div>
                        </b-card-text>
                    </b-tab>
                </b-tabs>
            </b-card>

            <div slot="modal-footer">
                <button @click="hideModalAdminFile()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import { API } from './../../api'
    import TableServer from '../../components/TableServer'
    import TableClient from '../../components/TableClient'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import VueJsonPretty from 'vue-json-pretty'
    import 'vue-json-pretty/lib/styles.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import moment from 'moment'

    export default {
        components: {
            'table-server': TableServer,
            'table-client': TableClient,
            vSelect,
            VueJsonPretty,
            Loading,
            datePicker,
            BModal
        },
        data: () => {
            return {
                services: [],
                clients: [],
                form_clients: [],
                executives: [],
                clientSelected: [],
                form_clientSelected: null,
                executiveSelected: null,
                reservation_resend: [],
                resend_email_services: [],
                resend_email_hotels: [],
                table_hotel: {
                    columns: ['id', 'hotel', 'noches', 'check_in', 'check_out', 'created_at', 'total', 'status', 'status_email']
                },
                table_hotel_opt: {
                    columns: ['id', 'hotel', 'noches', 'check_in', 'check_out', 'total', 'status', 'option']
                },
                table_hotel_rooms: {
                    columns: ['id', 'channel', 'noches', 'adultos', 'ninos', 'check_in', 'check_out', 'rate', 'room_name', 'on_request', 'status', 'created_at', 'total']
                },
                table_service: {
                    columns: ['id', 'service', 'tipo', 'adultos', 'ninos', 'fecha', 'created_at', 'total', 'status_email']
                },
                table_service_opt: {
                    columns: ['id', 'service', 'adultos', 'ninos', 'fecha', 'total', 'option']
                },
                table_flight: {
                    columns: ['id', 'code_flight', 'origin', 'destiny', 'adultos', 'ninos', 'created_at', 'fecha', 'status_email', 'created_at']
                },
                table_passengers: {
                    columns: ['id', 'doctype_iso', 'name', 'surnames', 'date_birth', 'type', 'genre', 'email', 'phone', 'country_iso', 'city_iso', 'dietary_restrictions', 'medical_restrictions', 'document_url', 'created_at']
                },
                table_email_logs: {
                    columns: ['id', 'email_type', 'email_to', 'emails', 'created_at']
                },
                table_hotel_rooms_calendars: {
                    columns: ['id', 'date', 'total_amount_base', 'total_amount', 'hotel_cancel_policies']
                },
                table_hotel_rooms_calendar_rate: {
                    columns: ['id', 'price_adult', 'price_adult_base', 'price_child', 'price_child_base', 'price_infant', 'price_infant_base', 'price_extra', 'price_extra_base']
                },
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    locale: localStorage.getItem('lang'),
                },
                loading: false,
                loading_logs: false,
                loading_resend_emails: false,
                client_id: '',
                executive_id: '',
                form_client_id: '',
                date: '',
                selectedStatus: '',
                selectedStatusError: '',
                urlServices: '',
                file_code: '',
                fileImport: '',
                reservation_id: '',
                reservationName: '',
                table: {
                    columns: ['id', 'reservator_type', 'source', 'booking_code', 'file_code', 'client_code', 'executive_name', 'date_init', 'created_at', 'total_amount', 'status_email', 'status_file'],
                },
                statusSelected: [],
                csrf: '',
                loadFile: false,
                loading_edit: false,
                formEditExecutive: false,
                formEditClient: false,
                response: '',
                enabled_retry_email: [],
                reservations: null,
                request: ''
            }
        },
        computed: {
            tableOptionsHotel: function () {
                return {
                    headings: {
                        id: 'ID',
                        hotel: 'Hotel',
                        noches: 'Noches',
                        check_in: 'Check In',
                        check_out: 'Check Out',
                        created_at: 'Fec. Creación',
                        total: 'Total',
                        status: 'Estado',
                        status_email: 'Email',
                    },
                    filterable: [],
                }
            },
            tableOptionsHotelOpt: function () {
                return {
                    headings: {
                        id: 'ID',
                        hotel: 'Hotel',
                        noches: 'Noches',
                        check_in: 'Check In',
                        check_out: 'Check Out',
                        total: 'Total',
                        status: 'Estado',
                        option: 'Opción',
                    },
                    filterable: [],
                }
            },
            tableOptionsHotelRooms: function () {
                return {
                    headings: {
                        id: 'ID',
                        channel: 'Channel',
                        noches: 'Noches',
                        adultos: 'Adultos',
                        ninos: 'Niños',
                        check_in: 'Check In',
                        check_out: 'Check Out',
                        rate: 'Tarifa',
                        room_name: 'Habitacion',
                        total_amount: 'Total',
                        on_request: 'OK/RQ',
                        status: 'Estado',
                        created_at: 'Fec. Creación',
                        total: 'Total',
                    },
                    filterable: [],
                }
            },
            tableOptionsHotelRoomsCalendars: function () {
                return {
                    headings: {
                        id: 'ID',
                        date: 'Fecha',
                        total_amount_base: 'Total Costo',
                        total_amount: 'Total Venta',
                        hotel_cancel_policies: 'Políticas de cancelación'
                    },
                    filterable: [],
                }
            },
            tableOptionsHotelRoomsCalendarRate: function () {
                return {
                    headings: {
                        id: 'ID',
                        price_adult: 'Precio Adulto (Venta)',
                        price_adult_base: 'Precio Adulto (Costo)',
                        price_child: 'Precio Niño (Venta)',
                        price_child_base: 'Precio Niño (Costo)',
                        price_infant: 'Precio Infante (Venta)',
                        price_infant_base: 'Precio Infante (Costo)',
                        price_extra: 'Precio Extra (Venta)',
                        price_extra_base: 'Precio Extra (Costo)',
                    },
                    filterable: [],
                }
            },
            tableOptionsService: function () {
                return {
                    headings: {
                        id: 'ID',
                        service: 'Servicio',
                        tipo: 'Tipo',
                        adultos: 'Adultos',
                        ninos: 'Niños',
                        fecha: 'Fecha',
                        total: 'Total',
                        created_at: 'Fec. Creación',
                        status_email: 'Email',
                    },
                    filterable: [],
                }
            },
            tableOptionsServiceOpt: function () {
                return {
                    headings: {
                        id: 'ID',
                        service: 'Servicio',
                        adultos: 'Adultos',
                        ninos: 'Niños',
                        fecha: 'Fecha',
                        total: 'Total',
                        option: 'Opción',
                    },
                    filterable: [],
                }
            },
            tableOptionsflights: function () {
                return {
                    headings: {
                        id: 'ID',
                        code_flight: '# de Vuelo',
                        origin: 'Origen',
                        destiny: 'Destino',
                        adultos: 'Adultos',
                        ninos: 'Niños',
                        fecha: 'Fecha',
                        status_email: 'Email',
                        created_at: 'Fec. Creación'
                    },
                    filterable: [],
                }
            },
            tableOptionsPassengers: function () {
                return {
                    headings: {
                        id: 'ID',
                        doctype_iso: 'Tipo doc.',
                        name: 'Nombres',
                        surnames: 'Apellidos',
                        date_birth: 'Fecha nac.',
                        type: 'Tipo',
                        genre: 'Sexo',
                        email: 'Email',
                        phone: 'Telef.',
                        country_iso: 'País',
                        city_iso: 'Ciudad',
                        created_at: 'Fec. Creación',
                        dietary_restrictions: 'Restric. Dietarias',
                        medical_restrictions: 'Restric. Medicas',
                        document_url: 'Doc'
                    },
                    filterable: [],
                }
            },
            tableOptionsEmailLogs: function () {
                return {
                    headings: {
                        id: 'ID',
                        email_type: 'Tipo de email',
                        email_to: 'Enviado a',
                        emails: 'Emails',
                        created_at: 'Fec. Creación',
                    },
                    filterable: [],
                }
            },
            tableOptions: function () {
                let self = this;
                return {
                    headings: {
                        id: 'ID',
                        source: 'Origen',
                        booking_code: 'Booking Code',
                        reservator_type: 'Reservado por',
                        file_code: 'File #',
                        client_code: 'Cliente',
                        executive_name: 'Ejecutiva',
                        date_init: 'Fec. Inicio',
                        created_at: 'Fec. Creación',
                        total_amount: 'Total',
                        status_email: 'Estado de email',
                        status_file: 'Estado de la reserva',
                    },
                    sortable: [],
                    filterable: [],
                    responseAdapter ({ data }) {
                        return {
                            data: data.data,
                            count: data.count
                        }
                    },
                    params: {
                        'file_code': this.file_code,
                        'selected_client': this.client_id,
                        'create_date': (this.date == '') ? '' : moment(this.date, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                        'status_reserve': this.selectedStatus,
                        'status_error': this.selectedStatusError,
                    },
                    requestFunction: function (data) {
                        self.loading = true;
                        let url = '/reservations?token=' + window.localStorage.getItem('access_token') + '&lang='
                            + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).then(function (response) {
                            self.loading = false;
                            return response;
                        }).catch(function (e) {
                            self.loading = false;
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            },
        },
        created () {
            document.body.classList.add('brand-minimized')
            document.body.classList.add('sidebar-minimized')
            this.csrf = window.Laravel.csrfToken
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            getFileExtension: function (filename){
                if(filename){
                    return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
                }
                return "";
            },
            setDateFrom (e) {
                if (e.date == false) {
                    this.date = ''
                }
            },
            search () {
                this.$refs.table.$refs.tableserver.getData()
            },
            copy: function (copy) {
                this.response = copy
                this.$copyText(JSON.stringify(this.response)).then(function (e) {
                    alert('Copiado')
                }, function (e) {
                    alert('No se pudo copiar')
                })
            },
            downloadJson: function (copy, exportName) {
                var dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(copy))
                var downloadAnchorNode = document.createElement('a')
                downloadAnchorNode.setAttribute('href', dataStr)
                downloadAnchorNode.setAttribute('download', exportName + '.json')
                document.body.appendChild(downloadAnchorNode) // required for firefox
                downloadAnchorNode.click()
                downloadAnchorNode.remove()
            },
            getLogReservations (reservation) {
                if (reservation.logs.length === 0) {
                    this.loading_logs = true
                    API.get('/reservations/' + reservation.id + '/logs'
                    ).then((result) => {
                        this.loading_logs = false
                        reservation.logs = result.data
                    }).catch((e) => {
                        this.loading_logs = false
                        console.log(e)
                    })
                }
            },
            will_remove (reservation) {
                this.reservation_id = reservation.id
                this.reservationName = 'File: ' + reservation.file_code
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            onUpdate () {
                this.urlServices = '/api/reservations?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'reservations/' + this.reservation_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Reservas',
                                text: this.$t('global.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            update (id) {
                API({
                    method: 'PUT',
                    url: 'reservations/' + id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Reservas',
                                text: this.$t('global.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            clientChange: function (value) {
                let select = value
                if (select != null) {
                    this.client_id = select.id
                } else {
                    this.client_id = ''
                }
            },
            formClientChange: function (value) {
                let select = value
                if (select != null) {
                    this.form_client_id = select.id
                } else {
                    this.form_client_id = ''
                }
            },
            executivesChange: function (value) {
                let select = value
                if (select != null) {
                    this.executive_id = select.id
                } else {
                    this.executive_id = ''
                }
            },
            formatDate: function (_date) {
                if (_date == undefined) {
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },
            onSearch (search, loading) {
                loading(true)
                this.clients = []
                API.get('/client/search?lang=' + localStorage.getItem('lang') + '&queryCustom=' + search).then((result) => {
                    loading(false)
                    let clients = result.data.data
                    let _clients = []
                    clients.forEach((clients) => {
                        _clients.push({ label: '(' + clients.code + ') ' + clients.name, id: clients.id })
                    })
                    this.clients = _clients
                    this.form_clients = _clients
                }).catch(() => {
                    loading(false)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: this.$t('global.error.messages.information_error'),
                    })
                })
            },
            onSearchExecutives (search, loading) {
                if (search.length >= 2) {
                    this.executives = []
                    loading(true)
                    API.get('/user/search/executive/filter?lang=' + localStorage.getItem('lang') + '&queryCustom=' + search).then((result) => {
                        loading(false)
                        let executives = result.data.data
                        let _executives = []
                        executives.forEach((executive) => {
                            _executives.push({ label: '(' + executive.code + ') ' + executive.name, id: executive.id })
                        })
                        this.executives = _executives
                    }).catch(() => {
                        loading(false)
                    })
                }
            },
            openModalAdminFile: function (reservation) {
                this.reservationName = 'File: ' + reservation.file_code
                this.reservations = reservation
                console.log(reservation)
                this.$refs['my-modal-admin-file'].show()
            },
            openModalRensedEmail: function (reservation) {
                this.resend_email_services = []
                this.resend_email_hotels = []
                this.reservationName = 'File: ' + reservation.file_code
                this.reservation_resend = reservation
                this.$refs['my-modal-resend-email'].show()
            },
            hideModalAdminFile () {
                this.$refs['my-modal-admin-file'].hide()
            },
            hideModalResendEmail () {
                this.reservation_resend = []
                this.resend_email_services = []
                this.resend_email_hotels = []
                this.$refs['my-modal-resend-email'].hide()
            },
            processResendEmail: function () {
                if (this.resend_email_services.length === 0 && this.resend_email_hotels.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: 'Debe seleccionar al menos un servicio u hotel',
                    })
                } else {
                    this.loading_resend_emails = true
                    API({
                        method: 'PUT',
                        data: {
                            'services': this.resend_email_services,
                            'hotels': this.resend_email_hotels,
                        },
                        url: 'reservations/' + this.reservation_resend.id + '/resend_emails'
                    })
                        .then((result) => {
                            this.loading_resend_emails = false
                            if (result.data.success === true) {
                                this.onUpdate()
                                this.hideModalResendEmail()
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Reservas',
                                    text: this.$t('global.error.messages.service_delete')
                                })
                            }
                        }).catch(() => {
                        this.loading_resend_emails = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Reservas',
                            text: this.$t('global.error.messages.connection_error')
                        })
                    })
                }
            },
            willEditFile: function (edit_field) {
                if (edit_field === 'executive') {
                    this.formEditExecutive = true
                }
                if (edit_field === 'client') {
                    this.formEditClient = true
                }
            },
            cancelFile: function (edit_field) {
                this.loading_edit = false
                if (edit_field === 'executive') {
                    this.formEditExecutive = false
                    this.executiveSelected = []
                    this.executives = []
                }
                if (edit_field === 'client') {
                    this.formEditClient = false
                    this.form_clientSelected = []
                    this.form_clients = []
                }
            },
            editFile: function (edit_field) {
                if (this.executiveSelected == null && edit_field === 'executive') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: 'Debe seleccionar una ejecutiva'
                    })
                    return
                }

                if (this.form_clientSelected == null && edit_field === 'client') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: 'Debe seleccionar un cliente'
                    })
                    return
                }
                this.loading_edit = true
                let data = {}
                if (edit_field === 'executive') {
                    data = {
                        'executive_id': this.executiveSelected.id,
                    }

                }
                if (edit_field === 'client') {
                    data = {
                        'client_id': this.form_clientSelected.id,
                    }
                }

                API({
                    method: 'PUT',
                    data: data,
                    url: 'reservations/' + this.reservations.id + '/' + edit_field
                })
                    .then((result) => {
                        this.loading_edit = false
                        if (result.data.success === true) {
                            this.cancelFile()
                            this.hideModalAdminFile()
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Reservas',
                                text: this.$t('global.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.loading_edit = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Reservas',
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            }
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
            currency: function (value) {
                return 'USD $.' + parseFloat(value).toFixed(2)
            },
            pretty: function (value) {
                return JSON.stringify(JSON.parse(value), null, 2)
            }
        }
    }
</script>

<style lang="stylus">


    /* style 2 */
    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .inputfile + label {
        max-width: 80%;
        font-size: 0.875rem;
        font-weight: 400;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        background-color: #bd0d12;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        line-height: 1.5;
        border: 1px solid transparent;
    }

    .inputfile + label {
        color: #fff;
    }

    .font-badge {
        font-size 12px;
        padding 10px
    }

    .tooltip-inner{
        min-width: 200px!important;
        text-align: left!important;
    }
</style>
