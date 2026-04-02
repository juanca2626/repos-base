@extends('layouts.app')
@section('content')
    <section class="page-central">
        <div class="container">
            <div class="row">
                <div id="_overlay"></div>

                <nav-central-bookings></nav-central-bookings>

                <div class="col-12 mb-5">
                    <form class="form">
                        <!-- Opciones -->
                        <div class="mt-4 mb-3 d-flex justify-content-around" v-show="showOptions">
                            <div class="">
                                <button class="btn btn-success" @click="newFile(0)" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <i class="fa fa-dot-circle mr-3"></i>Nuevo File
                                </button>
                            </div>
                            <div class="">
                                <button class="btn btn-success" @click="addFile()" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <i class="fa fa-plus mr-3"></i>Agregar File
                                </button>
                            </div>
                            <div class="" v-if="componentsChoosed.length > 0">
                                <button class="btn btn-info" @click="viewServicesChoosed" type="button" style="line-height: 0px;">
                                    <i class="fa fa-bars"></i> @{{ componentsChoosed.length }} Seleccionados
                                </button>
                            </div>
                        </div>
                        <hr>
                        <!--Filtros -->
                        <div class="" v-show="showFilters">
                            <div class="d-flex">
                                <div class="form-group mx-3 col">
                                    <label class="col-form-label">Fechas de:</label>
                                    <v-select class="form-control"
                                              :options="date_types"
                                              :value="date_type_id"
                                              autocomplete="true"
                                              data-vv-as="date_type"
                                              data-vv-name="date_type"
                                              :on-change="changeDateType()"
                                              name="date_type"
                                              v-model="dateTypeSelected">
                                    </v-select>
                                </div>
                                <div class="form-group mx-3 col" v-show="toggleDate">
                                    <label class="col-form-label">Elige fecha</label>
                                    <div class="">
                                        <date-picker
                                                class="form-control"
                                                :config="datePickerOptions"
                                                id="date" autocomplete="off"
                                                name="date" ref="datePicker"
                                                v-model="date">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="form-group mx-3 col" v-show="!toggleDate">
                                    <label class="col-form-label">Desde</label>
                                    <div class="">
                                        <date-picker
                                                :config="datePickerFromOptions"
                                                id="date_from"
                                                autocomplete="off"
                                                name="date_from" ref="datePickerFrom"
                                                v-model="date_from">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="form-group mx-3 col" v-show="!toggleDate">
                                    <label class="col-form-label">Hasta</label>
                                    <div class="">
                                        <date-picker
                                                :config="datePickerToOptions"
                                                id="date_to"
                                                autocomplete="off"
                                                name="date_to" ref="datePickerTo"
                                                v-model="date_to">
                                        </date-picker>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="form-group mx-3 col">
                                    <label class="col-form-label">Estado:</label>
                                    <v-select :options="status"
                                              :value="status_id"
                                              autocomplete="true"
                                              data-vv-as="status"
                                              data-vv-name="status"
                                              name="status"
                                              v-model="statusSelected"
                                              class="form-control">
                                    </v-select>
                                </div>
                                <div class="form-group mx-3 col">
                                    <label class="col-form-label">Final check:</label>
                                    <v-select :options="final_check"
                                              :value="final_check_id"
                                              autocomplete="true"
                                              data-vv-as="final_check"
                                              data-vv-name="final_check"
                                              name="final_check"
                                              v-model="finalCheckSelected"
                                              class="form-control">
                                    </v-select>
                                </div>
                                <div class="form-group mx-3 col-auto">
                                    <label class="col-form-label">Buscar por apellido del cliente:</label>
                                    <input :class="{'form-control':true }"
                                           id="filter_client_name" name="filter_client_name"
                                           type="text"
                                           ref="filter_client_name" v-model="filter_client_name">
                                </div>
                                <div class="form-group mx-3 col" >
                                    <label class="col-form-label">Agente:</label>
                                    <v-select :options="agents"
                                              :value="agent_id"
                                              autocomplete="true"
                                              data-vv-as="agents"
                                              data-vv-name="agents"
                                              name="agents"
                                              v-model="agentSelected"
                                              class="form-control">
                                    </v-select>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="form-group mx-3 col">
                                    <button @click="viewReservesPassed()" class="btn"
                                            type="button" :disabled="loading">
                                        <i class="fa fa-check mr-2" v-show="checkViewPassed"></i>
                                        <i class="fa fa-square mr-2" v-show="!checkViewPassed"></i>
                                        <span style="color: #890005;font-weight: 600;font-size: 11px;"> Incluir Reservas Pasadas </span>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <button @click="willOnUpdate()" class="btn btn-primary w-25"
                                    type="button" :disabled="loading" style="margin-top: 32px;">
                                    <i :class="{'fa':true, 'fa-search':!loading, 'fa-spin fa-spinner':loading }"></i> Buscar
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="col-12 my-5" style="margin-top: 20px;">
                    <div class="right">
                        <select class="form-control-lg" v-model="limit" @change="onUpdate()">
                            <option :value="l" v-for="l in limits">@{{ l }}</option>
                        </select> Registros.
                    </div>
                    <div class="table-responsive-sm">
                        <table class="table text-center canSelectText">
                            <thead>
                            <tr>
                                <th scope="col" class="text-muted">ID</th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('made_date_time')">
                                    Fecha de Creación <i :class="{'fa':true,'fa-angle-down':filter_made_date_time,'fa-angle-up':!filter_made_date_time}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('start_date')">
                                    Inicio-Fin <i :class="{'fa':true,'fa-angle-down':filter_start_date,'fa-angle-up':!filter_start_date}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('lead_customer_name')">
                                    Cliente <i :class="{'fa':true,'fa-angle-down':filter_lead_customer_name,'fa-angle-up':!filter_lead_customer_name}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('agent_name')">
                                    Agente <i :class="{'fa':true,'fa-angle-down':filter_agent_name,'fa-angle-up':!filter_agent_name}"></i>
                                </th>
                                <th scope="col" class="text-muted cursor-pointer" @click="filterBy('booking_name')">
                                    Servicio <i :class="{'fa':true,'fa-angle-down':filter_booking_name,'fa-angle-up':!filter_booking_name}"></i>
                                </th>
                                <th scope="col" class="text-muted">Estado</th>
                                <th scope="col" class="text-muted">Ventas</th>
                                <th scope="col" class="text-muted">Acciones</th>
                                <th scope="col" class="text-muted">N°Files</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            <tr v-for="booking in bookings">
                                <td class="td-body">@{{ booking.id}}</td>
                                <td class="td-body">@{{ booking.made_date_time | formatDate}}</td>
                                <td class="td-body">@{{ booking.start_date | formatDate}} - @{{booking.end_date | formatDate}}</td>
                                <td class="td-body">@{{ booking.lead_customer_name}}</td>
                                <td class="td-body"><span v-html="booking.agent_name"></span></td>
                                <td class="td-body">@{{ booking.booking_name}}</td>
                                <td class="td-body">
                                    <span class="btn btn-estado" v-if="flag_status != booking.id"
                                        v-on:click="flag_status = booking.id">@{{ booking.status_text}}</span>
                                    <template v-if="flag_status == booking.id">
                                        <v-select class="form-control px-3 p-0" style="height:auto; width:150px;" v-model="flag_booking_status"
                                            :options="booking_status" label="name" v-on:input="saveBookingStatus(booking.id)">
                                        </v-select>
                                    </template>
                                </td>
                                <td class="td-body" v-if="parseInt( booking.booking_has_net_price ) === 1">
                                    @{{ booking.sale_currency }} @{{ booking.components[0].net_price_inc_tax_total }}
                                </td>
                                <td class="td-body" v-else>
                                    @{{ booking.sale_currency }} @{{ booking.sales_revenue - booking.commission }}
                                </td>
                                <td>

{{--                                    <button @click="closeOthersPopovers(booking)" class="btn btn-info" :id="'pop' + booking.booking_id"--}}
{{--                                            type="button" title="Ver más información de la reserva">--}}
{{--                                        <i class="fa fa-list-alt"></i>--}}
{{--                                    </button>--}}

{{--                                    <!--                    :data-target="'pop' + booking.booking_id"-->--}}
{{--                                    <b-popover class="canSelectText" :show.sync="booking.popShow" :target="'pop' + booking.booking_id" title="Datos de Reserva" triggers="hover focus">--}}
{{--                                        <strong>Fecha y hora de creación:</strong>--}}
{{--                                        <br>@{{ booking.made_date_time | formatDate}}<br>--}}
{{--                                        <strong>Nombre:</strong> <br>@{{ booking.booking_name }}<br>--}}
{{--                                        <strong>Fechas:</strong><br>--}}
{{--                                        <strong>Inicio:</strong> @{{ booking.start_date}} | <strong>Fin:</strong> @{{ booking.end_date | formatDate}}<br>--}}
{{--                                    <!--                        <strong>Status:</strong> @{{ booking.status_text }}<br>-->--}}
{{--                                        <strong>Cliente:</strong> @{{ booking.lead_customer_name }} | <strong>Total clientes:</strong> @{{ booking.customer_count }}<br>--}}
{{--                                        <strong>Venta:</strong> @{{ booking.sale_currency }}--}}
{{--                                        <span v-if="parseInt( booking.booking_has_net_price ) === 1">--}}
{{--                                            @{{ booking.balance }}--}}
{{--                                        </span>--}}
{{--                                        <span v-else>--}}
{{--                                            @{{ booking.sales_revenue - booking.commission }}--}}
{{--                                        </span>--}}
{{--                                        <br>--}}
{{--                                        <span v-if="booking.cancel_reason != 0">--}}
{{--                                            <strong>Motivo de cancelación:</strong> @{{ booking.cancel_reason }}<br>--}}
{{--                                            <strong>Estado de cancelación:</strong> @{{ booking.cancel_text }}<br>--}}
{{--                                        </span>--}}
{{--                                        <!--                        <strong>Final check realizado:</strong>-->--}}
{{--                                        <!--                        <span v-if="booking.final_check == 0">No</span>-->--}}
{{--                                        <!--                        <span v-if="booking.final_check == 1">Si</span>-->--}}
{{--                                        <!--                        <br>-->--}}
{{--                                        <!--                        <strong>Comisión:</strong> <span v-html="booking.commission_display"></span><br>-->--}}
{{--                                        <!--                        <strong>Impuesto de la comisión:</strong> <span v-html="booking.commission_tax_display"></span><br>-->--}}
{{--                                        <br>--}}
{{--                                        <strong>Datos de Agente:</strong> <br>--}}
{{--                                        <strong>Referencia: </strong>@{{ booking.agent_ref }}<br>--}}
{{--                                    <!--                        <strong>Tipo:</strong> @{{ booking.agent_type }}<br>-->--}}
{{--                                        <strong>Nombre:</strong> @{{ booking.agent_name }}<br>--}}
{{--                                        <strong>Código:</strong> @{{ booking.agent_code }}<br>--}}
{{--                                        <br>--}}
{{--                                    <!--                        <strong>Status de pago:</strong> @{{ booking.payment_status_text }}<br>-->--}}
{{--                                        <!--                        <strong>Saldo adeudado por:</strong>-->--}}
{{--                                        <!--                        <span v-if="booking.balance_owed_by == 'C'">Cliente</span>-->--}}
{{--                                        <!--                        <span v-if="booking.balance_owed_by == 'A'">Agente</span>-->--}}
{{--                                        <!--                        <br>-->--}}
{{--                                        <!--                        <strong>Restante a pagar:</strong> <span v-html="booking.balance_display"></span><br>-->--}}

{{--                                    </b-popover>--}}

                                    <button @click="viewServices(booking)" class="btn btn-info"
                                            type="button" style="margin-left: 5px; margin-right: 5px"
                                            title="Abrir y ver servicios">
                                        <i class="fa fa-search"></i>
                                    </button>


                                    <button class="btn btn-info" @click="toggleStatus(booking.id, booking.status)"
                                        v-if="booking.status" title="PENDIENTE, (Click para cerrar reserva)">
                                        P
                                    </button>
                                    <button class="btn btn-info" @click="toggleStatus(booking.id, booking.status)"
                                        v-if="!(booking.status)" title="CERRADO, (Click para re-abrir reserva)">
                                        C
                                    </button>

                                </td>
                                <td>
                                    <label disabled="true" style="float: left;" class="btn btn-xs btn-nrofile"
                                        v-if="booking.reserves.length>0" title="Nro.File">
                                        @{{ booking.reserves[ booking.reserves.length - 1 ].reserve_file.file_number }}
                                    </label>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                            </tbody>


                        </table>

                        <nav aria-label="page navigation" v-if="booking_pages>1">
                            <ul class="pagination d-flex justify-content-center">
                                <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                    @click="setPage(1)">
                                    <a class="page-link" href="javascript:;">Primero</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==1)}"
                                    @click="setPage(pageChosen-1)">
                                    <a class="page-link" href="javascript:;">Anterior</a>
                                </li>

                                <li v-for="(page, p) in booking_pages" @click="setPage(page)"
                                    v-if="show_pages[p]"
                                    :class="{'page-item':true,'active':(page==pageChosen) }">
                                    <a class="page-link" href="javascript:;">@{{ page }}</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==booking_pages)}"
                                    @click="setPage(pageChosen+1)">
                                    <a class="page-link" href="javascript:;">Siguiente</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(pageChosen==booking_pages)}"
                                    @click="setPage(booking_pages)">
                                    <a class="page-link" href="javascript:;">Último</a>
                                </li>
                            </ul>
                        </nav>

                        <div class="tbl--cotizacion__content" v-if="bookings.length==1">
                            <div class="row no-gutters align-items-center">
                                <div class="col px-12 text-center">
                                    Ninguna reserva para mostrar
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <b-modal ref="my-modal" size="lg" class="modal-central">
                    <h1><i class="far fa-calendar-check mr-2"></i> @{{ modalTitle }}</h1>
                    <hr>
                    <div class="col-md-12 text-center" v-show="loadingModal">
                        <i class="spinner-grow"></i>
                    </div>

                    <div class="col-sm-12 canSelectText" v-show="!loadingModal">
                        <div v-show="showContentViewServices">
                            <div class="d-flex justify-content-between mx-5 mt-5">
                                <div class="">
                                    <span><strong>Creado el: </strong>@{{viewBooking.made_date_time | formatDate}} </span>
                                </div>
                                <div class="">
                                    <span><strong>Fecha de inicio: </strong>@{{viewBooking.start_date | formatDate}} </span> -
                                    <span><strong>Fecha final: </strong>@{{viewBooking.end_date | formatDate}} </span>
                                </div>
                                <div class="">
                                    <span><strong><a target="_blank" :href="viewBooking.voucher_url"><i class="icon-link mr-1"></i>Ver voucher</a> </strong> </span>
                                </div>
                            </div>
                            <div class="canSelectText mt-5">
                                <div class="d-flex">
                                    <h4><strong><i class="fas fa-hand-holding-heart mr-3"></i>Información de Cliente</strong> </h4>
                                    <button @click="showCustomers=!(showCustomers)" class="btn btn-info mx-2"
                                            type="button" style="line-height: 0px;">
                                        @{{ viewBooking.customer_count }} <i class="fa fa-search"></i>
                                    </button>
                                </div>

                                <div class="d-flex justify-content-between canSelectText mx-5 ">
                                    <div class="">
                                        <span><strong>Nombre: </strong>@{{viewBooking.lead_customer_name }} </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Email: </strong>@{{viewBooking.lead_customer_email}} </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Teléf.: </strong>@{{viewBooking.lead_customer_tel_home}} <span v-if="getSizeObj( viewBooking.lead_customer_tel_mobile ) > 0">- @{{viewBooking.lead_customer_tel_mobile}}</span></span>
                                    </div>
                                    <div class="">
                                        <span><strong>Nota: </strong>@{{viewBooking.lead_customer_contact_note}} </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Viaja en la reserva: </strong>
                                            <span v-if="(viewBooking.lead_customer_travelling)">Sí</span>
                                            <span v-if="!(viewBooking.lead_customer_travelling)">No</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="table mt-4">
                                <div class="rooms-table canSelectText row rooms-table-headers mx-4" v-show="showCustomers">
                                    <div class="col-2 my-auto">
                                        Nombre Completo
                                    </div>
                                    <div class="col-1 my-auto">
                                        Nombre
                                    </div>
                                    <div class="col-1 my-auto">
                                        Apellido
                                    </div>
                                    <div class="col-2 my-auto">
                                        E-mail
                                    </div>
                                    <div class="col-2 my-auto">
                                        Teléfonos
                                    </div>
                                    <div class="col-1 my-auto">
                                        Género
                                    </div>
                                    <div class="col-1 my-auto">
                                        Categoría
                                    </div>
                                    <div class="col-2 my-auto">
                                        Nota
                                    </div>
                                </div>
                                <div class="rooms-table row canSelectText mx-4" v-show="showCustomers" v-for="customer in viewBooking.customers">
                                    <div class="col-2 my-auto">
                                        @{{ customer.customer_name }}
                                    </div>
                                    <div class="col-1 my-auto text-center">
                                        @{{ customer.firstname }}
                                    </div>
                                    <div class="col-1 my-auto text-center">
                                        @{{ customer.surname }}
                                    </div>
                                    <div class="col-2 my-auto text-center" style="font-size: 12px;">
                                        @{{ customer.customer_email }}
                                    </div>
                                    <div class="col-2 my-auto">
                                        @{{ customer.customer_tel_home }} | @{{ customer.customer_tel_mobile }}
                                    </div>
                                    <div class="col-1 my-auto">
                                        @{{ customer.gender }}
                                    </div>
                                    <div class="col-1 my-auto">
                                        @{{ customer.agecat_text }}
                                    </div>
                                    <div class="col-2 my-auto text-right">
                                        @{{ customer.customer_contact_note }}
                                    </div>
                                </div>
                            </div>
                            <div class="canSelectText mt-5">
                                <div class="d-flex">
                                    <h4><strong><i class="fas fa-hand-holding-usd mr-3"></i>Información de Ingresos y Depósitos</strong> </h4>
                                </div>
                                <div class="d-flex mx-5">
                                    <span><strong>Cantidad de Ingresos: </strong>@{{ viewBooking.sale_currency }}
                                        <span v-if="parseInt( viewBooking.booking_has_net_price ) === 1">
                                            @{{ viewBooking.balance }}
                                        </span>
                                        <span v-else>
                                            @{{ viewBooking.sales_revenue - viewBooking.commission }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div class="canSelectText mt-5">
                                <div class="d-flex">
                                    <h4><strong> <i class="fas fa-hand-holding mr-3"></i>Estado de Reserva </strong> </h4>
                                </div>
                                <div class="d-flex justify-content-between mx-5">
                                    <div class="">
                                        <span><strong>Estado: </strong>@{{viewBooking.status_text }} </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Cancelación: </strong>@{{viewBooking.cancel_text}} </span>
                                    </div>
                                    <div class="">
                                    <span><strong>Final check: </strong>
                                        <span v-if="(viewBooking.final_check)">Sí verificado</span>
                                        <span v-if="!(viewBooking.final_check)">No verificado</span>
                                    </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Fecha de expiración: </strong>@{{viewBooking.expiry_date | formatDate }} </span>
                                    </div>
                                </div>

                            </div>
                            <div class="canSelectText mt-5">
                                <div class="d-flex">
                                    <h4><strong> <i class="fas fa-hand-holding mr-3"></i>Información del Agente </strong></h4>
                                </div>
                                <div class="d-flex justify-content-between mx-5">
                                    <!--                                <div class="col-auto">-->
                                    <!--                                    <span><strong>Comisión: </strong><span v-html="viewBooking.commission_tax_display"></span> </span>-->
                                    <!--                                </div>-->
                                    <!--                                <div class="col-auto">-->
                                    <!--                                    <span><strong>Impuesto de comisión: </strong><span v-html="viewBooking.commission_display"></span> </span>-->
                                    <!--                                </div>-->
                                    <div class="" v-if="getSizeObj(viewBooking.agent_ref)>0 || getSizeObj(viewBooking.agent_ref_components)>0">
                                        <span><strong>Referencia: </strong>@{{ viewBooking.agent_ref }} @{{ viewBooking.agent_ref_components }} </span>
                                    </div>
                                </div>
                            </div>
                            <div class="canSelectText mt-5">
                                <div class="d-flex">
                                    <h4><strong><i class="fas fa-hand-holding-usd mr-3"></i>Resumen de Pago</strong> </h4>
                                </div>
                                <div class="d-flex mx-5">
                                    <span><strong>Componentes: </strong>
                                        <button @click="showComponents =!(showComponents)" class="btn btn-info" type="button" style="line-height: 0px;">
                                            @{{ viewBooking.components.length }} <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div style="padding: 25px;">
                                <div :class="'row' + (key%2)" v-show="showComponents"
                                     v-for="(component, key) in viewBooking.components" >

                                    <b-form-checkbox style="float: right;"
                                                     :id="'checkbox_'+component.component_id"
                                                     :name="'checkbox_'+component.component_id"
                                                     v-model="checkboxs['check_'+component.component_id]"
                                                     @change="chooseService(key, component, viewBooking)">
                                    </b-form-checkbox>

                                    <div class="rooms-table row canSelectText">
                                        <div class="col-6 my-auto">
                                            <strong>Nombre: </strong>@{{ component.component_name }} <strong v-if="getSizeObj( component.product_code ) > 0">Cod: @{{ component.product_code }}</strong>
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Tipo: </strong>@{{ product_types[component.product_type] }} | @{{ component.date_type }}-->
                                        <!--                                </div>-->
                                        <div class="col-2 my-auto">
                                            <strong>Cantidad: </strong>@{{ component.sale_quantity }}
                                        </div>
                                    </div>
                                    <div class="rooms-table row canSelectText">
                                        <div class="col-6 my-auto">
                                            <strong>F/Hr.Inicio-Fin: </strong>@{{ component.start_date | formatDate }} <span v-if="component.start_time != 'NOTSET' && getSizeObj(component.start_time)>0">@{{ component.start_time }}</span> -
                                            @{{ component.end_date | formatDate }} <span v-if="component.end_time != 'NOTSET' && getSizeObj(component.end_time)>0">@{{ component.end_time }}</span>
                                        </div>
                                        <div class="col-6 my-auto">
                                            <span v-if="component.voucher_redemption_status == 0">El componente aun no se ha canjeado</span>
                                            <span v-if="component.voucher_redemption_status == 1">El componente ya se ha canjeado</span>
                                        </div>
                                    </div>
                                    <!--                            <div class="rooms-table row canSelectText">-->
                                    <!--                                <div class="col-6 my-auto">-->
                                    <!--                                    <strong>Nota: </strong> @{{ component.product_note }}-->
                                    <!--                                </div>-->
                                    <!--                            </div>-->
                                    <div :class="'rooms-table row canSelectText rowTitle rowTitleChoosed-' + checkboxs['check_'+component.component_id]">
                                        <div class="col-12 my-auto">
                                            <strong>DETALLES DE PRECIOS </strong>
                                        </div>
                                    </div>

                                    <div class="rooms-table row canSelectText">
                                        <div class="col-4 my-auto">
                                            <strong>Nota de Tarifa: </strong>@{{ component.rate_description }}
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Tarifa Breakdown: </strong>@{{ component.rate_breakdown }} <i class="fa fa-info-circle" id="popHelpRate"></i>-->
                                        <!--                                    <b-popover ref="popover" target="popHelpRate">-->
                                        <!--                                        <strong>Para excursiones ...</strong><br>-->
                                        <!--                                        por ejemplo, r1 | a significa tarifa 1 / adulto-->
                                        <!--                                        (s-senior, a-adult, y-youth, c-child, i-infant)-->
                                        <!--                                        (r1 es el rate_id de show tour )-->
                                        <!--                                        <br>-->
                                        <!--                                        <strong>Para hoteles ...</strong><br>-->
                                        <!--                                        Adultos mayores | Adultos | Niños | Bebés-->
                                        <!--                                    </b-popover>-->
                                        <!--                                </div>-->
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Impuestos: </strong>-->
                                        <!--                                    <span v-if="component.sale_tax_inclusive == 0">Agregados al precio(V)</span>-->
                                        <!--                                    <span v-if="component.sale_tax_inclusive == 1">Incluídos</span>-->
                                        <!--                                </div>-->
                                    </div>

                                    <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                        <div class="col-4 my-auto">
                                            <strong>Precio(V): </strong><span v-html="component.sale_currency"></span> @{{ component.sale_price }} (PER @{{ component.sale_quantity_rule }})
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Impuesto(V): </strong> @{{ component.sale_tax_percentage }}% = <span v-html="component.sale_currency"></span> @{{ component.tax_total }}-->
                                        <!--                                </div>-->
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Total(V): </strong> <span v-html="component.sale_currency"></span>@{{ component.sale_price_inc_tax_total }} Inc.Impuesto.-->
                                        <!--                                </div>-->
                                    </div>

                                    <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)==1">
                                        <div class="col-4 my-auto">
                                            <strong>Precio Neto: </strong><span v-html="component.sale_currency"></span> @{{ component.net_price }} (PER @{{ component.net_quantity_rule }})
                                        </div>
                                        <div class="col-4 my-auto">
                                            <strong>Total Neto: </strong> <span v-html="component.sale_currency"></span> <span class="back_resalt">@{{ component.net_price_inc_tax_total }}</span> Inc.Impuesto.
                                        </div>
                                    </div>

                                    <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                        <div class="col-4 my-auto">
                                            <strong>Precio(V) Base: </strong><span v-html="component.currency_base"></span> @{{ component.sale_price_base }} (PER @{{ component.sale_quantity_rule }})
                                        </div>
                                        <div class="col-8 my-auto">
                                            <strong>Tipo de Cambio: </strong> @{{ component.sale_exchange_rate }} (De: <span v-html="component.sale_currency "></span> a: <span v-html="component.currency_base"></span>)
                                        </div>
                                    </div>


                                    <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> @{{ component.tax_total_base }}-->
                                        <!--                                </div>-->
                                        <div class="col-8 my-auto">
                                            <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span>
                                            <span class="back_resalt">@{{ component.sale_price_inc_tax_total_with_commission }}</span> Inc.Impuesto y commission (@{{ component.commission_ }}).
                                        </div>
                                    </div>

                                    <div :class="'rooms-table row canSelectText rowTitle rowTitleChoosed-' + checkboxs['check_'+component.component_id]" >
                                        <div class="col-12 my-auto">
                                            <strong>DATOS DE OPERADOR TURÍSTICO </strong>
                                        </div>
                                    </div>

                                    <div class="rooms-table row canSelectText">
                                        <div class="col-4 my-auto">
                                            <strong>Cantidad: </strong>@{{ component.cost_quantity }}
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Impuestos: </strong>-->
                                        <!--                                    <span v-if="component.cost_tax_inclusive == 0">Agregados al precio(C)</span>-->
                                        <!--                                    <span v-if="component.cost_tax_inclusive == 1">Incluídos</span>-->
                                        <!--                                </div>-->
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Nota: </strong>@{{ component.operational_note }}-->
                                        <!--                                </div>-->
                                    </div>


                                    <div class="rooms-table row canSelectText">
                                        <div class="col-4 my-auto">
                                            <strong>Precio(C): </strong><span v-html="component.cost_currency"></span> @{{ component.cost_price }} (PER @{{ component.cost_quantity_rule }})
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Impuesto(C): </strong> @{{ component.cost_tax_percentage }}% = <span v-html="component.cost_currency"></span> @{{ component.cost_tax_total }}-->
                                        <!--                                </div>-->
                                        <div class="col-4 my-auto">
                                            <strong>Total(C): </strong> <span v-html="component.cost_currency"></span>@{{ component.cost_price_inc_tax_total }} Inc.Impuesto.
                                        </div>
                                    </div>

                                    <div class="rooms-table row canSelectText">
                                        <div class="col-4 my-auto">
                                            <strong>Precio(C) Base: </strong><span v-html="component.currency_base"></span> @{{ component.cost_price_base }} (PER @{{ component.cost_quantity_rule }})
                                        </div>
                                        <div class="col-8 my-auto">
                                            <strong>Tipo de Cambio: </strong> @{{ component.cost_exchange_rate }} (De: <span v-html="component.cost_currency  "></span> a: <span v-html="component.currency_base"></span>)
                                        </div>
                                    </div>

                                    <div class="rooms-table row canSelectText">
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> @{{ component.cost_tax_total_base }}-->
                                        <!--                                </div>-->
                                        <!--                                <div class="col-8 my-auto">-->
                                        <!--                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> @{{ component.cost_price_inc_tax_total_base }} Inc.Impuesto.-->
                                        <!--                                </div>-->
                                    </div>

                                    <div class="rooms-table row canSelectText">
                                        <div class="col-4 my-auto">
                                            <strong>Agregado en la fecha: </strong>@{{ component.component_added_datetime | formatDate }}
                                        </div>
                                        <div class="col-4 my-auto">
                                            <strong>Si fue adicional (Nombre del usuario): </strong> @{{ component.upsell_username }}
                                        </div>
                                        <!--                                <div class="col-4 my-auto">-->
                                        <!--                                    <strong>Proveedor: </strong>@{{ component.supplier_name }} (@{{ component.supplier_tour_code }})-->
                                        <!--                                </div>-->
                                    </div>

                                </div>
                            </div>
                            <div class="canSelectText mt-5"
                                 v-if="getSizeObj(viewBooking.customer_special_request) > 0 || getSizeObj(viewBooking.important_note) > 0">
                                <div class="d-flex">
                                    <h4><strong><i class="icon-grid mr-3"></i>Datos Adicionales </strong> </h4>
                                </div>
                                <div class="d-flex justify-content-between mx-5">
                                    <div class="">
                                        <span><strong>Petición especial del cliente: </strong>@{{ viewBooking.customer_special_request }} </span>
                                    </div>
                                    <div class="">
                                        <span><strong>Nota importante: </strong><span v-html="viewBooking.important_note"></span> </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div v-show="showContentViewServicesChoosed" style="padding: 25px;">
                            <div :class="'row' + (key%2)" v-show="showComponents" style="margin-bottom: 30px;"
                                 v-for="(component, key) in componentsChoosed" >

                                <button class="btn btn-danger" @click="deleteServiceChoosed(key, component)" style="float: right;">
                                    <i class="far fa-trash-alt"></i>
                                </button>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-6 my-auto">
                                        <strong>Nombre: </strong>@{{ component.component_name }} <strong v-if="getSizeObj( component.product_code ) > 0">Cod: @{{ component.product_code }}</strong>
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Tipo: </strong>@{{ product_types[component.product_type] }} | @{{ component.date_type }}-->
                                    <!--                                </div>-->
                                    <div class="col-2 my-auto">
                                        <strong>Cantidad: </strong>@{{ component.sale_quantity }}
                                    </div>
                                </div>
                                <div class="rooms-table row canSelectText">
                                    <div class="col-6 my-auto">
                                        <strong>F/Hr.Inicio-Fin: </strong>@{{ component.start_date | formatDate }} <span v-if="component.start_time != 'NOTSET' && getSizeObj(component.start_time)>0">@{{ component.start_time }}</span> -
                                        @{{ component.end_date | formatDate }} <span v-if="component.end_time != 'NOTSET' && getSizeObj(component.end_time)>0">@{{ component.end_time }}</span>
                                    </div>
                                    <div class="col-6 my-auto">
                                        <span v-if="component.voucher_redemption_status == 0">El componente aun no se ha canjeado</span>
                                        <span v-if="component.voucher_redemption_status == 1">El componente ya se ha canjeado</span>
                                    </div>
                                </div>
                                <!--                            <div class="rooms-table row canSelectText">-->
                                <!--                                <div class="col-6 my-auto">-->
                            <!--                                    <strong>Nota: </strong> @{{ component.product_note }}-->
                                <!--                                </div>-->
                                <!--                            </div>-->
                                <div class="rooms-table row canSelectText rowTitle rowTitleChoosed-true">
                                    <div class="col-12 my-auto">
                                        <strong>DETALLES DE PRECIOS </strong>
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-4 my-auto">
                                        <strong>Nota de Tarifa: </strong>@{{ component.rate_description }}
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Tarifa Breakdown: </strong>@{{ component.rate_breakdown }} <i class="fa fa-info-circle" id="popHelpRate"></i>-->
                                    <!--                                    <b-popover ref="popover" target="popHelpRate">-->
                                    <!--                                        <strong>Para excursiones ...</strong><br>-->
                                    <!--                                        por ejemplo, r1 | a significa tarifa 1 / adulto-->
                                    <!--                                        (s-senior, a-adult, y-youth, c-child, i-infant)-->
                                    <!--                                        (r1 es el rate_id de show tour )-->
                                    <!--                                        <br>-->
                                    <!--                                        <strong>Para hoteles ...</strong><br>-->
                                    <!--                                        Adultos mayores | Adultos | Niños | Bebés-->
                                    <!--                                    </b-popover>-->
                                    <!--                                </div>-->
                                    <!--                                <div class="col-4 my-auto">-->
                                    <!--                                    <strong>Impuestos: </strong>-->
                                    <!--                                    <span v-if="component.sale_tax_inclusive == 0">Agregados al precio(V)</span>-->
                                    <!--                                    <span v-if="component.sale_tax_inclusive == 1">Incluídos</span>-->
                                    <!--                                </div>-->
                                </div>

                                <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                    <div class="col-4 my-auto">
                                        <strong>Precio(V): </strong><span v-html="component.sale_currency"></span> @{{ component.sale_price }} (PER @{{ component.sale_quantity_rule }})
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Impuesto(V): </strong> @{{ component.sale_tax_percentage }}% = <span v-html="component.sale_currency"></span> @{{ component.tax_total }}-->
                                    <!--                                </div>-->
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Total(V): </strong> <span v-html="component.sale_currency"></span>@{{ component.sale_price_inc_tax_total }} Inc.Impuesto.-->
                                    <!--                                </div>-->
                                </div>

                                <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)==1">
                                    <div class="col-4 my-auto">
                                        <strong>Precio Neto: </strong><span v-html="component.sale_currency"></span> @{{ component.net_price }} (PER @{{ component.net_quantity_rule }})
                                    </div>
                                    <div class="col-4 my-auto">
                                        <strong>Total Neto: </strong> <span v-html="component.sale_currency"></span> <span class="back_resalt">@{{ component.net_price_inc_tax_total }}</span> Inc.Impuesto.
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                    <div class="col-4 my-auto">
                                        <strong>Precio(V) Base: </strong><span v-html="component.currency_base"></span> @{{ component.sale_price_base }} (PER @{{ component.sale_quantity_rule }})
                                    </div>
                                    <div class="col-8 my-auto">
                                        <strong>Tipo de Cambio: </strong> @{{ component.sale_exchange_rate }} (De: <span v-html="component.sale_currency "></span> a: <span v-html="component.currency_base"></span>)
                                    </div>
                                </div>


                                <div class="rooms-table row canSelectText" v-if="parseInt(component.booking_has_net_price)!=1">
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> @{{ component.tax_total_base }}-->
                                    <!--                                </div>-->
                                    <div class="col-8 my-auto">
                                        <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span>
                                        <span class="back_resalt">@{{ component.sale_price_inc_tax_total_with_commission }}</span> Inc.Impuesto y commission (@{{ component.commission_ }}).
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText rowTitle rowTitleChoosed-true" >
                                    <div class="col-12 my-auto">
                                        <strong>DATOS DE OPERADOR TURÍSTICO </strong>
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-4 my-auto">
                                        <strong>Cantidad: </strong>@{{ component.cost_quantity }}
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                    <!--                                    <strong>Impuestos: </strong>-->
                                    <!--                                    <span v-if="component.cost_tax_inclusive == 0">Agregados al precio(C)</span>-->
                                    <!--                                    <span v-if="component.cost_tax_inclusive == 1">Incluídos</span>-->
                                    <!--                                </div>-->
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Nota: </strong>@{{ component.operational_note }}-->
                                    <!--                                </div>-->
                                </div>


                                <div class="rooms-table row canSelectText">
                                    <div class="col-4 my-auto">
                                        <strong>Precio(C): </strong><span v-html="component.cost_currency"></span> @{{ component.cost_price }} (PER @{{ component.cost_quantity_rule }})
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Impuesto(C): </strong> @{{ component.cost_tax_percentage }}% = <span v-html="component.cost_currency"></span> @{{ component.cost_tax_total }}-->
                                    <!--                                </div>-->
                                    <div class="col-4 my-auto">
                                        <strong>Total(C): </strong> <span v-html="component.cost_currency"></span>@{{ component.cost_price_inc_tax_total }} Inc.Impuesto.
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-4 my-auto">
                                        <strong>Precio(C) Base: </strong><span v-html="component.currency_base"></span> @{{ component.cost_price_base }} (PER @{{ component.cost_quantity_rule }})
                                    </div>
                                    <div class="col-8 my-auto">
                                        <strong>Tipo de Cambio: </strong> @{{ component.cost_exchange_rate }} (De: <span v-html="component.cost_currency  "></span> a: <span v-html="component.currency_base"></span>)
                                    </div>
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> @{{ component.cost_tax_total_base }}-->
                                    <!--                                </div>-->
                                    <!--                                <div class="col-8 my-auto">-->
                                <!--                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> @{{ component.cost_price_inc_tax_total_base }} Inc.Impuesto.-->
                                    <!--                                </div>-->
                                </div>

                                <div class="rooms-table row canSelectText">
                                    <div class="col-4 my-auto">
                                        <strong>Agregado en la fecha: </strong>@{{ component.component_added_datetime | formatDate }}
                                    </div>
                                    <div class="col-4 my-auto">
                                        <strong>Si fue adicional (Nombre del usuario): </strong> @{{ component.upsell_username }}
                                    </div>
                                    <!--                                <div class="col-4 my-auto">-->
                                <!--                                    <strong>Proveedor: </strong>@{{ component.supplier_name }} (@{{ component.supplier_tour_code }})-->
                                    <!--                                </div>-->
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button @click="chooseAll()" class="btn btn-seleccionar w-25 mx-2" v-show="showContentViewServices">Seleccionar todo</button>

                        <button class="btn btn-success" style="margin: 15px 20px 4px;" @click="newFile(0)" type="button"
                                :disabled="!(componentsChoosed.length)" v-show="showContentViewServicesChoosed">
                            <i class="fa fa-circle"></i> Nuevo File
                        </button>
                        <button class="btn btn-success" style="margin: 15px 20px 4px;" @click="addFile()" type="button"
                                :disabled="!(componentsChoosed.length)" v-show="showContentViewServicesChoosed">
                            <i class="fa fa-plus"></i> Agregar a un File
                        </button>

                        <button @click="hideModal()" class="btn btn-cancelar w-25 mx-2">Cancelar</button>
                    </div>

                    <div slot="modal-footer">
                    </div>
                </b-modal>

                <b-modal centered ref="my-modal-add-to-file" size="md">

                    <h1> <i class="far fa-calendar-plus mr-2"></i>Agregar a un File</h1>
                    <hr>
                    <p class="">Seleccione el file:</p>

                    <v-select class="form-control mb-4"
                              :options="files"
                              :value="nrofile"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Buscar por pasajero o Nº de file"
                              v-model="fileSelected" name="file" id="file" style="padding-top: 5px;">
                        <template slot="option" slot-scope="option">
                            <div class="d-center">
                                @{{ option.NROREF }} - @{{ option.NOMBRE }}
                            </div>
                        </template>
                        <template slot="selected-option" slot-scope="option">
                            <div class="selected d-center">
                                @{{ option.NROREF }} - @{{ option.NOMBRE }}
                            </div>
                        </template>
                    </v-select>
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="btn btn-success" @click="newFile(1)" type="button">
                            <i class="fa fa-plus mr-2"></i> Agregar File
                        </button>
                        <button @click="hideModal()" class="btn btn-cancelar w-25">Cancelar</button>
                    </div>

                    <div slot="modal-footer">

                    </div>
                </b-modal>

            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        let componentsChoosed = []

        new Vue({
        el: '#app',
        data: {
            loading:false,
            loadingModal:false,
            showFilters:true,
            showOptions:true,
            showCustomers:false,
            showComponents:false,
            files:[],
            fileSelected:[],
            nrofile:null,
            showContentViewServices:false,
            showContentViewServicesChoosed:false,
            modalTitle:'',
            contentModal:'',
            bookingId:'',
            urlbookings: '',
            date_type_id:"",
            date: '',
            status_id:'',
            agent_id:'',
            final_check_id:'',
            filter_client_name:'',
            checkboxs:[],
            checkViewPassed:0,
            agents:[],
            agentSelected:[],
            componentsChoosed:[],
            dateTypeSelected:{
                code : 0,
                label : "Booking Window",
                param : "made"
            },
            date_types:[
                {
                    code : 0,
                    label : "Booking Window",
                    param : "made"
                },
                {
                    code : 1,
                    label : "Travel Window",
                    param : "start"
                },
            ],
            statusSelected:{
                code : 1,
                label : "Pendientes",
                param : "1"
            },
            status:[
                {
                    code : 2,
                    label : "Todos",
                    param : ""
                },
                {
                    code : 1,
                    label : "Pendientes",
                    param : "1"
                },
                {
                    code : 0,
                    label : "Cerradas",
                    param : "0"
                }
            ],
            finalCheckSelected:{
                code : 0,
                label : "Todos",
                param : ""
            },
            final_check:[
                {
                    code : 0,
                    label : "Todos",
                    param : ""
                },
                {
                    code : 1,
                    label : "Completados",
                    param : "1"
                },
                {
                    code : 2,
                    label : "Incompletos",
                    param : "0"
                }
            ],
            product_types : [
                "Accommod",
                "Transfer",
                "Tour/Cruise",
                "Day tour/Trip",
                "Tailor made",
                "Event",
                "Other",
                "Training/education",
                "Restaurant/Male"
            ],
            toggleDate : 0,
            datePickerOptions: {
                format: 'DD/MM/YYYY',
                useCurrent: false,
                locale: localStorage.getItem('lang')
            },
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
            form:{
                service_id:null,
                booking_id:null
            },
            my_date_to: '',
            my_date_from: '',
            viewBooking : {
                customers : {
                    customer : ''
                },
                components : {
                    component : []
                }
            },
            bookings : [],
            pageChosen : 1,
            limits : [5, 10, 15, 20, 25, 30],
            limit : 15,
            booking_pages : 0,
            filter_made_date_time : '',
            filter_start_date : '',
            filter_lead_customer_name : '',
            filter_agent_name : '',
            filter_booking_name : '',
            _filter:'',
            _order:'',
            view_pages: 15,
            show_pages: {},
            pages: 0,
            page: 0,
            booking_status: [
                { code: 2, name: 'Confirmed' },
                { code: 2, name: 'Canceled'},
                { code: 1, name: 'Provisional' },
            ],
            flag_booking_status: {},
            flag_status: false,
        },
        created() {
            this.date_from = moment().format('DD/MM/YYYY')
            this.date_to = ''
        },
        mounted() {
            if (localStorage.getItem('servicesTourcms')) {
                try {
                    this.componentsChoosed = JSON.parse(localStorage.getItem('servicesTourcms'))
                } catch(e) {
                    localStorage.removeItem('servicesTourcms')
                }
            }
            this.searchAgents()
            this.onUpdate()
        },
        computed: {
        },
        methods: {
            saveBookingStatus: function (booking_id) {
                console.log(this.flag_booking_status)

                this.loading = true
                axios.post('/api/channel/tourcms/'+booking_id+'/status_external', this.flag_booking_status)
                    .then((result) => {
                        if(result.data.success){
                            this.flag_status=''
                            this.onUpdate()
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                })
            },
            validatePagination: function () {
                this.view_pages = 15
                this.pages = this.booking_pages
                this.page = this.pageChosen

                for(let p=0;p<this.pages;p++)
                {
                    this.show_pages[p] = false
                
                    if(this.page < 5)
                    {
                        if(this.view_pages > 0)
                        {
                            this.view_pages -= 1
                            this.show_pages[p] = true
                        }
                    }
                    else
                    {
                        if(this.page >= (this.pages - (this.view_pages) / 2))
                        {
                            if(p >= (this.pages - this.view_pages))
                            {
                                this.show_pages[p] = true
                            }
                        }
                        else
                        {
                            if(p >= parseFloat(this.page - parseFloat(this.view_pages / 2)) && p <= parseFloat(this.page + parseFloat(this.view_pages / 2)))
                            {
                                this.show_pages[p] = true
                            }
                        }
                    }
                }
            },
            filterBy(_filter){
                if( _filter == 'made_date_time' ){
                    this.filter_made_date_time = !(this.filter_made_date_time)
                    this._order = this.filter_made_date_time
                }
                if( _filter == 'start_date' ){
                    this.filter_start_date = !(this.filter_start_date)
                    this._order = this.filter_start_date
                }
                if( _filter == 'lead_customer_name' ){
                    this.filter_lead_customer_name = !(this.filter_lead_customer_name)
                    this._order = this.filter_lead_customer_name
                }
                if( _filter == 'agent_name' ){
                    this.filter_agent_name = !(this.filter_agent_name)
                    this._order = this.filter_agent_name
                }
                if( _filter == 'booking_name' ){
                    this.filter_booking_name = !(this.filter_booking_name)
                    this._order = this.filter_booking_name
                }
                this._filter = _filter
                this.onUpdate()
            },
            toggleStatus(id, status) {
                this.loading = true
                axios.post('/api/channel/tourcms/'+id+'/status', { status : status })
                    .then((result) => {
                        if(result.data.success){
                            this.onUpdate()
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                })
            },
            viewReservesPassed(){
                console.log(this.checkViewPassed)
                this.checkViewPassed = (this.checkViewPassed==0) ? 1 : 0
                this.onUpdate()
            },
            searchAgents(){
                // agents
                axios.get('/api/channel/tourcms/bookings/agents')
                    .then((result) => {
                        this.agents = result.data
                    }).catch((e) => {
                        console.log(e)
                    })
            },
            onSearch(search, loading) {
                loading(true)
                let data = { query : search }
                axios.post('/api/channel/tourcms/booking/files', data)
                    .then((result) => {
                        loading(false)
                        this.files = result.data
                    }).catch(() => {
                    loading(false)
                })
            },
            saveLocalStorage(){
                const parsed = JSON.stringify(this.componentsChoosed);
                localStorage.setItem('servicesTourcms', parsed);
            },
            onLoaded ($event) {
                console.log('My event caught in global event bus', $event)
            },
            changeDateType(){
                if( this.dateTypeSelected.code == 3 ){
                    this.toggleDate = 1;
                } else {
                    this.toggleDate = 0;
                }
            },
            setPage(page){
                if( page < 1 || page > this.booking_pages ){
                    return;
                }
                this.pageChosen = page
                this.onUpdate("","")
            },
            willOnUpdate () {
                this.pageChosen = 1
                this.onUpdate()
            },
            onUpdate () {

                let myTypeDate = this.dateTypeSelected.param

                if( myTypeDate == 'component_start_date' && ( this.date == '' || this.date == null ) ){
                    this.$toast.error("La fecha no es correcta", {
                        position: 'top-right'
                    })
                } else {
                    this.loading = true
                    let myDateFrom = moment(this.date_from,'DD/MM/YYYY').format('YYYY-MM-DD')
                    let myDateTo = moment(this.date_to,'DD/MM/YYYY').format('YYYY-MM-DD')
                    this.urlbookings = 'api/channel/tourcms/bookings?token=' + localStorage.getItem('access_token') +
                        '&type_date=' + myTypeDate + '&date_from=' + myDateFrom + '&date_to=' + myDateTo +
                        '&date=' + this.date + '&active=' + this.statusSelected.param +
                        '&agent_id=' + this.agentSelected.code +
                        '&final_check=' + this.finalCheckSelected.param + '&lead_customer_surname=' +  this.filter_client_name+
                        '&page='+this.pageChosen+'&limit='+this.limit+
                        '&filter='+this._filter+'&order='+this._order+'&reserve_passed='+this.checkViewPassed

                    axios.get(baseExternalURL + this.urlbookings)
                        .then((result) => {
                            console.log(result)
                            result.data.data.forEach( b =>{
                                b.components.forEach( c =>{
                                    c.sale_price_inc_tax_total_with_commission = c.sale_price_inc_tax_total - b.commission
                                    c.commission_ = b.commission
                                    c.booking_has_net_price = b.booking_has_net_price
                                } )
                            } )
                            this.bookings = result.data.data
                            this.loading = false
                            this.booking_pages = Math.ceil(result.data.count / this.limit)
                            this.validatePagination()
                        }).catch((e) => {
                            console.log(e)
                            this.loading = false
                        })
                }
            },
            viewServices(me){

                this.modalTitle = ''
                this.showContentViewServices = true
                this.showContentViewServicesChoosed = false
                this.loadingModal = true
                this.showModal()

                this.viewBooking = me
                this.modalTitle = this.viewBooking.booking_name + ' | ' + this.viewBooking.status_text
                this.loadingModal = false

            },
            chooseAll:function(){
                this.viewBooking.components.forEach( component => {
                    let exist=0
                    this.componentsChoosed.forEach( componentChoosed => {
                        if( component.component_id == componentChoosed.component_id ) {
                            exist++;
                        }
                    })
                    if( !exist ){
                        let canadu=0
                        let canchd=0
                        let caninf=0
                        this.viewBooking.customers.forEach( customer =>{
                            if( customer.agecat == 'i' ){
                                caninf++
                            } else if(customer.agecat == 'c'){
                                canchd++
                            }else{
                                canadu++
                            }
                        } )
                        component.canadu = canadu
                        component.booking_id = this.viewBooking.booking_id
                        component.channel_id = this.viewBooking.channel_id
                        component.canchd = canchd
                        component.caninf = caninf
                        component.paxdes = this.viewBooking.lead_customer_name
                        component.paxsData = []
                        this.viewBooking.customers.forEach( p=>{
                            let gender = ''
                            if( typeof p.gender == 'string' ){
                                gender = p.gender
                            }
                            component.paxsData.push({
                                nombre : p.customer_name,
                                genero : gender,
                                edad : "",
                                nac : "",
                            })
                        } )
                        this.componentsChoosed.push(component)
                        this.checkboxs['check_'+component.component_id] = true
                    }
                })
                this.showComponents = false
                this.showComponents = true
                this.saveLocalStorage()
            },
            viewServicesChoosed() {

                this.modalTitle = 'Servicios Elegidos para crear/agregar file'
                this.showContentViewServices = false
                this.showContentViewServicesChoosed = true
                this.loadingModal = true
                this.showModal()
                this.loadingModal = false
                this.showComponents = false
                this.showComponents = true
            },
            closeOthersPopovers: function () {
                this.$root.$emit('bv::hide::popover')
            },
            showModal () {
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-add-to-file'].hide()
            },
            chooseService: function (key, me, booking) {

                let count = 0
                this.componentsChoosed.forEach( (component, keyC) => {
                    if( component.component_id == me.component_id ){
                        if( this.checkboxs['check_'+component.component_id] ){
                            console.log('quitar')
                            this.componentsChoosed.splice(keyC,1)
                        }
                        count++
                    }
                } )

                if( !(this.checkboxs['check_'+me.component_id]) && count==0 ){
                    console.log('poner')
                    let canadu=0
                    let canchd=0
                    let caninf=0
                    booking.customers.forEach( customer =>{
                        if( customer.agecat == 'i' ){
                            caninf++
                        } else if(customer.agecat == 'c'){
                            canchd++
                        }else{
                            canadu++
                        }
                    } )
                    me.canadu = canadu
                    me.canchd = canchd
                    me.caninf = caninf
                    me.paxdes = booking.lead_customer_name
                    me.booking_id = booking.booking_id
                    me.channel_id = booking.channel_id
                    me.paxsData = []
                    booking.customers.forEach( p=>{
                        let gender = ''
                        if( typeof p.gender == 'string' ){
                            gender = p.gender
                        }
                        me.paxsData.push({
                            nombre : p.customer_name,
                            genero : gender,
                            edad : "",
                            nac : "",
                        })
                    } )
                    this.componentsChoosed.push(me)
                }
                console.log( this.componentsChoosed )
                this.saveLocalStorage()
            },
            newFile : function(typeCreation){

                let nrofile=''
                let services = []
                let err=0

                if( typeCreation == 1 ){
                    if(this.fileSelected.NROREF == undefined) {
                        err++
                    } else {
                        nrofile=this.fileSelected.NROREF
                    }
                }

                if( err==0 ){
                    this.componentsChoosed.forEach( comp =>{

                        if( typeof comp.product_code == 'object' ){
                            this.$toast.error('Error al obtener el código de un servicio', {
                                position: 'top-right'
                            })
                            err++
                            return
                        }

                        // Revisar que existan los campos
                        let total = 0
                        if( parseInt( comp.booking_has_net_price ) === 1 ){
                            total = comp.net_price_inc_tax_total
                        } else {
                            total = comp.sale_price_inc_tax_total_with_commission
                        }

                        services.push({
                            tourcms_header_id : comp.tourcms_header_id,
                            code : comp.product_code,
                            start_date : comp.start_date,
                            end_date : comp.end_date,
                            paxdes : comp.paxdes,
                            total : total, // Precio(V) Total: EUR 50.00 Inc.Impuesto. // comp.sale_price_inc_tax_total
                            currency : comp.currency_base,
                            canadu : comp.canadu,
                            canchd : comp.canchd,
                            caninf : comp.caninf,
                            paxsData : comp.paxsData,
                            booking_id : comp.booking_id,
                            channel_id : comp.channel_id
                        })

                    } )

                    // console.log(services)
                    // return

                    if( err == 0 ){

                        this.loading = true
                        this.loadingModal = true
                        let data = {
                            services : services,
                            nrofile : nrofile
                        }
                        axios({
                            method: 'post',
                            url: '/api/channel/tourcms/booking',
                            data: data
                        })
                            .then((result) => {
                                console.log(result.data)
                                this.loadingModal = false
                                this.loading = false
                                // Si es successfull
                                if( result.data.success ){
                                    this.$toast.success('Reserva generada con el N°: '+ result.data.detail, {
                                        position: 'top-right'
                                    })
                                    this.componentsChoosed = []
                                    localStorage.setItem('servicesTourcms', '')
                                    this.hideModal()
                                    this.onUpdate()
                                } else {
                                    this.$toast.error(result.data.detail, {
                                        position: 'top-right'
                                    })
                                }


                            }).catch((e) => {
                            this.loadingModal = false
                            this.loading = false
                            console.log(e)

                            this.$toast.error('Error al generar la reserva', {
                                position: 'top-right'
                            })
                        })
                    } else {
                        this.loadingModal = false
                        this.loading = false

                        this.$toast.error('No se encontró el código de servicio en uno o más servicios', {
                            position: 'top-right'
                        })
                    }
                } else {
                    this.loadingModal = false
                    this.loading = false
                    this.$toast.error('Por favor seleccione el file a agregar', {
                        position: 'top-right'
                    })
                }
            },
            addFile(){
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-add-to-file'].show()
            },
            deleteServiceChoosed : function(key, component){
                this.checkboxs['check_'+component.component_id] = false
                this.componentsChoosed.splice(key,1)
                this.saveLocalStorage()
            },
            getSizeObj : function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            }
        },
        filters:{
            formatDate: function (_date) {
                if( _date == undefined ){
                    // console.log('fecha no parseada: ' + _date)
                    return;
                }
                let secondPartDate = ''

                if( _date.length > 10 ){
                    secondPartDate = _date.substr(10, _date.length )
                    _date = _date.substr(0,10)
                }

                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date + secondPartDate
            },
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
      })
    </script>
@endsection
@section('css')
    <style>
    .custom-control-input:checked ~ .custom-control-label::before {
    border-color: #3ea662;
    background-color: #3a9d5d;
    }
    .v-select input{
    height: 25px;
    }
    .btn-search{
        margin-top: 19px;
    }
    .right, .btn-search{
        float: right;
    }
    .modal-backdrop {
    background-color: #00000052;
    }
    .canSelectText{
    user-select: text;
    }

    .rooms-table{
    padding: 5px;
    }
    .rowTitle {
        text-align: center;
        background: #e9ecef;
        border: solid 1px #8a9b9b;
        margin: 3px -6px;
        color: #8e8e8e;
        font-weight: 800;
    }

    .datepickerbutton{
        z-index: 1 !important;
    }
    .cursor-pointer{
        cursor: pointer;
    }
    .back_resalt{
        background: #fbff00;
    }
    .btn-nrofile{
        float: left;
        background: #ffe8e8;
        margin-top: 5px;
        color: #b24343;
    }
    </style>
@endsection
