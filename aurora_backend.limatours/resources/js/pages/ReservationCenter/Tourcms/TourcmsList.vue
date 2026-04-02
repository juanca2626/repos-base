<template>
    <div class="container-fluid">
        <div class="container-fluid vld-parent">
            <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>

            <div class="row justify-content-end">
                <div class="col-sm-12" style="padding: 0; margin: 0">
                    <button @click="showOptions=!showOptions" class="btn btn-info" type="button"style="line-height: 0px;">
                        Opciones
                        <font-awesome-icon :icon="['fas', 'angle-up']" v-show="showOptions"/>
                        <font-awesome-icon :icon="['fas', 'angle-down']" v-show="!showOptions"/>
                    </button>
                </div>
            </div>
            <div class="row card justify-content-end" v-show="showOptions">
                <div class="col-sm-12">
                    <div class="b-form-group form-group" style="margin-top: 13px;">
                        <div class="form-row">

                            <div class="col-2">
                                <button class="btn btn-success" @click="newFile(0)" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <font-awesome-icon :icon="['fas', 'circle']"/> Nuevo File
                                </button>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-success" @click="addFile()" type="button" style="line-height: 0px;" :disabled="!(componentsChoosed.length)">
                                    <font-awesome-icon :icon="['fas', 'plus']"/> Agregar File
                                </button>
                            </div>
                            <div class="col-2" v-if="componentsChoosed.length > 0">
                                <button class="btn btn-info" @click="viewServicesChoosed" type="button" style="line-height: 0px;">
                                    <font-awesome-icon :icon="['fas', 'bars']"/> {{ componentsChoosed.length }} Seleccionados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-end" style="margin-top: 10px;">
                <div class="col-sm-12" style="padding: 0; margin: 0">
                    <button @click="showFilters=!showFilters" class="btn btn-info" type="button"style="line-height: 0px;">
                        Filtros
                        <font-awesome-icon :icon="['fas', 'angle-up']" v-show="showFilters"/>
                        <font-awesome-icon :icon="['fas', 'angle-down']" v-show="!showFilters"/>
                    </button>
                </div>
            </div>
            <div class="row card justify-content-end" v-show="showFilters">

                <div class="col-sm-12">
                    <div class="b-form-group form-group">
                        <div class="form-row">

                            <div class="col-4">
                                <label class="col-form-label">{{ $t('dates') }} {{ $t('of') }}:</label>
                                <v-select :options="date_types"
                                          :value="date_type_id"
                                          autocomplete="true"
                                          data-vv-as="date_type"
                                          data-vv-name="date_type"
                                          :on-change="changeDateType()"
                                          name="date_type"
                                          v-model="dateTypeSelected">
                                </v-select>
                            </div>

                            <div class="col-4" v-show="toggleDate">
                                <label class="col-12 col-form-label">{{ $t('choose_date') }}</label>
                                <div class="input-group col-12">
                                    <date-picker
                                            :config="datePickerOptions"
                                            id="date"
                                            name="date" ref="datePicker"
                                            v-model="date">
                                    </date-picker>

                                    <div class="input-group-append">
                                        <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                                type="button">
                                            <i class="far fa-calendar"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-4" v-show="!toggleDate">
                                <label class="col-12 col-form-label">{{ $t('from') }}</label>
                                <div class="input-group col-12">
                                    <date-picker
                                            :config="datePickerFromOptions"
                                            @dp-change="setDateFrom"
                                            id="date_from"
                                            autocomplete="off"
                                            name="date_from" ref="datePickerFrom"
                                            v-model="date_from">
                                    </date-picker>

                                    <div class="input-group-append">
                                        <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                                type="button">
                                            <i class="far fa-calendar"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-4" v-show="!toggleDate">
                                <label class="col-12 col-form-label">{{ $t('to') }}</label>
                                <div class="input-group col-12">
                                    <date-picker
                                            :config="datePickerToOptions"
                                            id="date_to"
                                            autocomplete="off"
                                            name="date_to" ref="datePickerTo"
                                            v-model="date_to">
                                    </date-picker>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                                type="button">
                                            <i class="far fa-calendar"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>

                </div>

                <div class="col-sm-12">
                    <div class="b-form-group form-group">
                        <div class="form-row">

                            <div class="col-4">
                                <label class="col-form-label">{{ $t('status') }}:</label>
                                <v-select :options="status"
                                          :value="status_id"
                                          autocomplete="true"
                                          data-vv-as="status"
                                          data-vv-name="status"
                                          name="status"
                                          v-model="statusSelected">
                                </v-select>
                            </div>

                            <div class="col-3">
                                <label class="col-form-label">{{ $t('final_check') }}:</label>
                                <v-select :options="final_check"
                                          :value="final_check_id"
                                          autocomplete="true"
                                          data-vv-as="final_check"
                                          data-vv-name="final_check"
                                          name="final_check"
                                          v-model="finalCheckSelected">
                                </v-select>
                            </div>

                            <div class="col-3">
                                <label class="col-form-label">{{ $t('search_by_client_surname') }}:</label>
                                <input :class="{'form-control':true }"
                                       id="filter_client_name" name="filter_client_name"
                                       type="text"
                                       ref="filter_client_name" v-model="filter_client_name">
                            </div>

                            <div class="col-2">
                                <button @click="onUpdate()" class="btn btn-danger btn-search" type="button" v-show="!loading" style="line-height: 0px;">
                                    <font-awesome-icon :icon="['fas', 'search']"/> Buscar
                                </button>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                    </div>

                </div>


                <div class="col-sm-12">
                    <div class="b-form-group form-group">
                        <div class="form-row">

                            <div class="col-4">
                                <label class="col-form-label">Agente:</label>
                                <v-select :options="agents"
                                          :value="agent_id"
                                          autocomplete="true"
                                          data-vv-as="agents"
                                          data-vv-name="agents"
                                          name="agents"
                                          v-model="agentSelected">
                                </v-select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
            </div>


            <table-server :columns="table.columns" style="margin-top: 10px" :options="tableOptions" @loaded="onLoaded"
                          :url="urlbookings" class="text-center canSelectText" ref="table">

                <div class="table-dmade_date_time" slot="made_date_time" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.made_date_time | formatDate}}
                </div>

                <div class="table-dates" slot="dates" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.start_date | formatDate}} - {{props.row.end_date | formatDate}}
                </div>

                <div class="table-sales_display" slot="sales_display" slot-scope="props" style="font-size: 0.9em">
                    <span v-html="props.row.sales_revenue_display"></span>
                </div>

                <div class="table-sales_display" slot="agent" slot-scope="props" style="font-size: 0.9em">
                    <span v-html="props.row.agent_name"></span>
                </div>

                <div class="table-actions text-center" slot="actions" slot-scope="props" style="padding: 5px">

<!--                    :data-id="'pop' + props.row.booking_id"-->
                    <button @click="closeOthersPopovers(props.row)" class="btn btn-info" :id="'pop' + props.row.booking_id"
                            type="button" title="Ver más información de la reserva">
                        <font-awesome-icon :icon="['fas', 'list-alt']"/>
                    </button>

<!--                    :data-target="'pop' + props.row.booking_id"-->
                    <b-popover class="canSelectText" :show.sync="props.row.popShow" :target="'pop' + props.row.booking_id" title="Datos de Reserva" triggers="hover focus">
                        <strong>Fecha y hora de creación:</strong>
                        <br>{{ props.row.made_date_time | formatDate}}<br>
                        <strong>Nombre:</strong> <br>{{ props.row.booking_name }}<br>
                        <strong>Fechas:</strong><br>
                        <strong>Inicio:</strong> {{ props.row.start_date}} | <strong>Fin:</strong> {{ props.row.end_date | formatDate}}<br>
<!--                        <strong>Status:</strong> {{ props.row.status_text }}<br>-->
                        <strong>Cliente:</strong> {{ props.row.lead_customer_name }} | <strong>Total clientes:</strong> {{ props.row.customer_count }}<br>
                        <strong>Venta:</strong> <span v-html="props.row.sales_revenue_display"></span><br>
                        <span v-if="props.row.cancel_reason != 0">
                            <strong>Motivo de cancelación:</strong> {{ props.row.cancel_reason }}<br>
                            <strong>Estado de cancelación:</strong> {{ props.row.cancel_text }}<br>
                        </span>
<!--                        <strong>Final check realizado:</strong>-->
<!--                        <span v-if="props.row.final_check == 0">No</span>-->
<!--                        <span v-if="props.row.final_check == 1">Si</span>-->
<!--                        <br>-->
<!--                        <strong>Comisión:</strong> <span v-html="props.row.commission_display"></span><br>-->
<!--                        <strong>Impuesto de la comisión:</strong> <span v-html="props.row.commission_tax_display"></span><br>-->
                        <br>
                        <strong>Datos de Agente:</strong> <br>
                        <strong>Referencia: </strong>{{ props.row.agent_ref }}<br>
<!--                        <strong>Tipo:</strong> {{ props.row.agent_type }}<br>-->
                        <strong>Nombre:</strong> {{ props.row.agent_name }}<br>
                        <strong>Código:</strong> {{ props.row.agent_code }}<br>
                        <br>
<!--                        <strong>Status de pago:</strong> {{ props.row.payment_status_text }}<br>-->
<!--                        <strong>Saldo adeudado por:</strong>-->
<!--                        <span v-if="props.row.balance_owed_by == 'C'">Cliente</span>-->
<!--                        <span v-if="props.row.balance_owed_by == 'A'">Agente</span>-->
<!--                        <br>-->
<!--                        <strong>Restante a pagar:</strong> <span v-html="props.row.balance_display"></span><br>-->

                    </b-popover>

                    <button @click="viewServices(props.row)" class="btn btn-danger"
                            type="button" style="margin-left: 5px; margin-right: 5px" title="Abrir y ver servicios">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>


                    <button class="btn-sm btn-warning" @click="toggleStatus(props.row.id, props.row.status)" v-if="props.row.status" title="PENDIENTE, (Click para cerrar reserva)">
                        P
                    </button>
                    <button class="btn-sm btn-danger" @click="toggleStatus(props.row.id, props.row.status)" v-if="!(props.row.status)" title="CERRADO, (Click para re-abrir reserva)">
                        C
                    </button>

                </div>

            </table-server>

            <b-modal :title="modalTitle" ref="my-modal" size="lg">

                <div class="col-md-12 text-center" v-show="loadingModal">
                    <img src="/images/loading.svg" alt="loading"/>
                </div>

                <div class="col-sm-12 canSelectText" v-show="!loadingModal">

                    <div v-show="showContentViewServices">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <div class="col-auto">
                                    <span><strong>Creado el: </strong>{{viewBooking.made_date_time | formatDate}} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Fecha de inicio: </strong>{{viewBooking.start_date | formatDate}} </span>
                                    <span><strong>Fecha final: </strong>{{viewBooking.end_date | formatDate}} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong><a target="_blank" :href="viewBooking.voucher_url">Ver voucher</a> </strong> </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr/>
                        </div>

                        <div class="form-row canSelectText">
                            <div class="col-auto">
                                <h4><strong>Información de Cliente: </strong> </h4>
                            </div>
                            <div class="col-auto">
                                <button @click="showCustomers=!(showCustomers)" class="btn btn-info"
                                        type="button" style="line-height: 0px;">
                                    {{ viewBooking.customer_count }} <font-awesome-icon :icon="['fas', 'search']"/>
                                </button>
                            </div>
                        </div>

                        <div class="b-form-group form-group canSelectText">
                            <div class="form-row">
                                <div class="col-auto">
                                    <span><strong>Nombre: </strong>{{viewBooking.lead_customer_name }} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Email: </strong>{{viewBooking.lead_customer_email}} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Teléf.: </strong>{{viewBooking.lead_customer_tel_home}} <span v-if="getSizeObj( viewBooking.lead_customer_tel_mobile ) > 0">- {{viewBooking.lead_customer_tel_mobile}}</span></span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Nota: </strong>{{viewBooking.lead_customer_contact_note}} </span>
                                </div>
                                <div class="col-auto">
                                    <span><strong>Viaja en la reserva: </strong>
                                        <span v-if="(viewBooking.lead_customer_travelling)">Sí</span>
                                        <span v-if="!(viewBooking.lead_customer_travelling)">No</span>
                                    </span>
                                </div>

                            </div>
                        </div>

                        <div class="rooms-table canSelectText row rooms-table-headers" v-show="showCustomers">
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

                        <div class="rooms-table row canSelectText" v-show="showCustomers" v-for="customer in viewBooking.customers">
                            <div class="col-2 my-auto">
                                {{ customer.customer_name }}
                            </div>
                            <div class="col-1 my-auto text-center">
                                {{ customer.firstname }}
                            </div>
                            <div class="col-1 my-auto text-center">
                                {{ customer.surname }}
                            </div>
                            <div class="col-2 my-auto text-center" style="font-size: 12px;">
                                {{ customer.customer_email }}
                            </div>
                            <div class="col-2 my-auto">
                                {{ customer.customer_tel_home }} | {{ customer.customer_tel_mobile }}
                            </div>
                            <div class="col-1 my-auto">
                                {{ customer.gender }}
                            </div>
                            <div class="col-1 my-auto">
                                {{ customer.agecat_text }}
                            </div>
                            <div class="col-2 my-auto text-right">
                                {{ customer.customer_contact_note }}
                            </div>
                        </div>


                        <div class="col-12">
                            <hr/>
                        </div>

                        <div class="form-row canSelectText">
                            <div class="col-auto">
                                <h4><strong>Información de Ingresos y Depósitos: </strong> </h4>
                            </div>
                        </div>

                        <div class="b-form-group form-group canSelectText">
                            <div class="form-row">
                                <div class="col-auto">
                                    <span><strong>Cantidad de Ingresos: </strong><span v-html="viewBooking.sales_revenue_display"></span> </span> |
                                </div>
<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Deposito del importe: </strong><span v-html="viewBooking.deposit_display"></span> </span>-->
<!--                                </div>-->
                            </div>
                        </div>

                        <div class="form-row canSelectText">
                            <div class="col-auto">
                                <h4><strong>Estado de Reserva: </strong> </h4>
                            </div>
                        </div>

                        <div class="b-form-group form-group canSelectText">
                            <div class="form-row">
                                <div class="col-auto">
                                    <span><strong>Estado: </strong>{{viewBooking.status_text }} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Cancelación: </strong>{{viewBooking.cancel_text}} </span> |
                                </div>
                                <div class="col-auto">
                                    <span><strong>Final check: </strong>
                                        <span v-if="(viewBooking.final_check)">Sí verificado</span>
                                        <span v-if="!(viewBooking.final_check)">No verificado</span>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <span><strong>Fecha de expiración: </strong>{{viewBooking.expiry_date | formatDate }} </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row canSelectText">
                            <div class="col-auto">
                                <h4><strong>Información del Agente: </strong> </h4>
                            </div>
                        </div>

                        <div class="b-form-group form-group canSelectText">
                            <div class="form-row">

<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Comisión: </strong><span v-html="viewBooking.commission_tax_display"></span> </span>-->
<!--                                </div>-->
<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Impuesto de comisión: </strong><span v-html="viewBooking.commission_display"></span> </span>-->
<!--                                </div>-->
                                <div class="col-auto" v-if="getSizeObj(viewBooking.agent_ref)>0 || getSizeObj(viewBooking.agent_ref_components)>0">
                                    <span><strong>Referencia: </strong>{{ viewBooking.agent_ref }} {{ viewBooking.agent_ref_components }} </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row canSelectText">
                            <div class="col-auto">
                                <h4><strong>Resumen de Pago: </strong> </h4>
                            </div>
                        </div>

                        <div class="b-form-group form-group canSelectText">
                            <div class="form-row">

<!--                                <div class="col-auto">-->
<!--                                    <span><strong v-if="getSizeObj( viewBooking.payment_status_text ) > 0">Status: </strong>{{ viewBooking.payment_status_text }} </span>-->
<!--                                </div>-->

<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Saldo adeudado por: </strong>-->
<!--                                        <span v-if="viewBooking.balance_owed_by == 'C'">Cliente</span>-->
<!--                                        <span v-if="viewBooking.balance_owed_by == 'A'">Agente</span>-->
<!--                                    </span>-->
<!--                                </div>-->
<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Restante a pagar: </strong><span v-html="viewBooking.balance_display"></span> </span>-->
<!--                                </div>-->
<!--                                <div class="col-auto">-->
<!--                                    <span><strong>Fecha debido: </strong>{{ viewBooking.balance_due | formatDate }}</span>-->
<!--                                </div>-->
                                <div class="col-auto">
                                    <span><strong>Componentes: </strong>
                                        <button @click="showComponents =!(showComponents)" class="btn btn-info" type="button" style="line-height: 0px;">
                                            {{ viewBooking.components.length }} <font-awesome-icon :icon="['fas', 'search']"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

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
                                    <strong>Nombre: </strong>{{ component.component_name }} <strong v-if="getSizeObj( component.product_code ) > 0">Cod: {{ component.product_code }}</strong>
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Tipo: </strong>{{ product_types[component.product_type] }} | {{ component.date_type }}-->
<!--                                </div>-->
                                <div class="col-2 my-auto">
                                    <strong>Cantidad: </strong>{{ component.sale_quantity }}
                                </div>
                            </div>
                            <div class="rooms-table row canSelectText">
                                <div class="col-6 my-auto">
                                    <strong>F/Hr.Inicio-Fin: </strong>{{ component.start_date | formatDate }} <span v-if="component.start_time != 'NOTSET' && getSizeObj(component.start_time)>0">{{ component.start_time }}</span> -
                                    {{ component.end_date | formatDate }} <span v-if="component.end_time != 'NOTSET' && getSizeObj(component.end_time)>0">{{ component.end_time }}</span>
                                </div>
                                <div class="col-6 my-auto">
                                    <span v-if="component.voucher_redemption_status == 0">El componente aun no se ha canjeado</span>
                                    <span v-if="component.voucher_redemption_status == 1">El componente ya se ha canjeado</span>
                                </div>
                            </div>
<!--                            <div class="rooms-table row canSelectText">-->
<!--                                <div class="col-6 my-auto">-->
<!--                                    <strong>Nota: </strong> {{ component.product_note }}-->
<!--                                </div>-->
<!--                            </div>-->
                            <div :class="'rooms-table row canSelectText rowTitle rowTitleChoosed-' + checkboxs['check_'+component.component_id]">
                                <div class="col-12 my-auto">
                                    <strong>DETALLES DE PRECIOS </strong>
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Nota de Tarifa: </strong>{{ component.rate_description }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Tarifa Breakdown: </strong>{{ component.rate_breakdown }} <i class="fa fa-info-circle" id="popHelpRate"></i>-->
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

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(V): </strong><span v-html="component.sale_currency"></span> {{ component.sale_price }} (PER {{ component.sale_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto(V): </strong> {{ component.sale_tax_percentage }}% = <span v-html="component.sale_currency"></span> {{ component.tax_total }}-->
<!--                                </div>-->
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total(V): </strong> <span v-html="component.sale_currency"></span>{{ component.sale_price_inc_tax_total }} Inc.Impuesto.-->
<!--                                </div>-->
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio Neto: </strong><span v-html="component.sale_currency"></span> {{ component.net_price }} (PER {{ component.net_price_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto Neto Total: </strong> <span v-html="component.sale_currency"></span> {{ component.net_price_tax_total }}-->
<!--                                </div>-->
                                <div class="col-4 my-auto">
                                    <strong>Total Neto: </strong> <span v-html="component.sale_currency"></span>{{ component.net_price_inc_tax_total }} Inc.Impuesto.
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(V) Base: </strong><span v-html="component.currency_base"></span> {{ component.sale_price_base }} (PER {{ component.sale_quantity_rule }})
                                </div>
                                <div class="col-8 my-auto">
                                    <strong>Tipo de Cambio: </strong> {{ component.sale_exchange_rate }} (De: <span v-html="component.sale_currency "></span> a: <span v-html="component.currency_base"></span>)
                                </div>
                            </div>


                            <div class="rooms-table row canSelectText">
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> {{ component.tax_total_base }}-->
<!--                                </div>-->
                                <div class="col-8 my-auto">
                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> {{ component.sale_price_inc_tax_total_base }} Inc.Impuesto.
                                </div>
                            </div>

                            <div :class="'rooms-table row canSelectText rowTitle rowTitleChoosed-' + checkboxs['check_'+component.component_id]" >
                                <div class="col-12 my-auto">
                                    <strong>DATOS DE OPERADOR TURÍSTICO </strong>
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Cantidad: </strong>{{ component.cost_quantity }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuestos: </strong>-->
<!--                                    <span v-if="component.cost_tax_inclusive == 0">Agregados al precio(C)</span>-->
<!--                                    <span v-if="component.cost_tax_inclusive == 1">Incluídos</span>-->
<!--                                </div>-->
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Nota: </strong>{{ component.operational_note }}-->
<!--                                </div>-->
                            </div>


                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(C): </strong><span v-html="component.cost_currency"></span> {{ component.cost_price }} (PER {{ component.cost_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto(C): </strong> {{ component.cost_tax_percentage }}% = <span v-html="component.cost_currency"></span> {{ component.cost_tax_total }}-->
<!--                                </div>-->
                                <div class="col-4 my-auto">
                                    <strong>Total(C): </strong> <span v-html="component.cost_currency"></span>{{ component.cost_price_inc_tax_total }} Inc.Impuesto.
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(C) Base: </strong><span v-html="component.currency_base"></span> {{ component.cost_price_base }} (PER {{ component.cost_quantity_rule }})
                                </div>
                                <div class="col-8 my-auto">
                                    <strong>Tipo de Cambio: </strong> {{ component.cost_exchange_rate }} (De: <span v-html="component.cost_currency  "></span> a: <span v-html="component.currency_base"></span>)
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> {{ component.cost_tax_total_base }}-->
<!--                                </div>-->
<!--                                <div class="col-8 my-auto">-->
<!--                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> {{ component.cost_price_inc_tax_total_base }} Inc.Impuesto.-->
<!--                                </div>-->
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Agregado en la fecha: </strong>{{ component.component_added_datetime | formatDate }}
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>Si fue adicional (Nombre del usuario): </strong> {{ component.upsell_username }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Proveedor: </strong>{{ component.supplier_name }} ({{ component.supplier_tour_code }})-->
<!--                                </div>-->
                            </div>

                        </div>

                        <div class="form-row canSelectText"
                             v-if="getSizeObj(viewBooking.customer_special_request) > 0 || getSizeObj(viewBooking.important_note) > 0">
                            <div class="col-auto">
                                <h4><strong>Datos Adicionales: </strong> </h4>
                            </div>
                        </div>


                        <div class="b-form-group form-group canSelectText" style="margin-top: 10px"
                             v-if="getSizeObj(viewBooking.customer_special_request) > 0 || getSizeObj(viewBooking.important_note) > 0">
                            <div class="form-row">

                                <div class="col-auto">
                                    <span><strong>Petición especial del cliente: </strong>{{ viewBooking.customer_special_request }} </span>
                                </div>
                                <div class="col-auto">
                                    <span><strong>Nota importante: </strong><span v-html="viewBooking.important_note"></span> </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-show="showContentViewServicesChoosed">
                        <div :class="'row' + (key%2)" v-show="showComponents" style="margin-bottom: 10px;"
                             v-for="(component, key) in componentsChoosed" >

                            <button class="btn btn-danger" @click="deleteServiceChoosed(key, component)" style="float: right;">
                                <font-awesome-icon :icon="['fas', 'trash']"/>
                            </button>

                            <div class="rooms-table row canSelectText">
                                <div class="col-6 my-auto">
                                    <strong>Nombre: </strong>{{ component.component_name }} <strong v-if="getSizeObj( component.product_code ) > 0">Cod: {{ component.product_code }}</strong>
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Tipo: </strong>{{ product_types[component.product_type] }} | {{ component.date_type }}-->
<!--                                </div>-->
                                <div class="col-2 my-auto">
                                    <strong>Cantidad: </strong>{{ component.sale_quantity }}
                                </div>
                            </div>
                            <div class="rooms-table row canSelectText">
                                <div class="col-6 my-auto">
                                    <strong>F/Hr.Inicio-Fin: </strong>{{ component.start_date | formatDate }} <span v-if="component.start_time != 'NOTSET' && getSizeObj(component.start_time)>0">{{ component.start_time }}</span> -
                                    {{ component.end_date | formatDate }} <span v-if="component.end_time != 'NOTSET' && getSizeObj(component.end_time)>0">{{ component.end_time }}</span>
                                </div>
                                <div class="col-6 my-auto">
                                    <span v-if="component.voucher_redemption_status == 0">El componente aun no se ha canjeado</span>
                                    <span v-if="component.voucher_redemption_status == 1">El componente ya se ha canjeado</span>
                                </div>
                            </div>
<!--                            <div class="rooms-table row canSelectText">-->
<!--                                <div class="col-6 my-auto">-->
<!--                                    <strong>Nota: </strong> {{ component.product_note }}-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="rooms-table row canSelectText rowTitle rowTitleChoosed-true">
                                <div class="col-12 my-auto">
                                    <strong>DETALLES DE PRECIOS </strong>
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Nota de Tarifa: </strong>{{ component.rate_description }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Tarifa Breakdown: </strong>{{ component.rate_breakdown }} <i class="fa fa-info-circle" id="popHelpRate"></i>-->
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

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(V): </strong><span v-html="component.sale_currency"></span> {{ component.sale_price }} (PER {{ component.sale_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto(V): </strong> {{ component.sale_tax_percentage }}% = <span v-html="component.sale_currency"></span> {{ component.tax_total }}-->
<!--                                </div>-->
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total(V): </strong> <span v-html="component.sale_currency"></span>{{ component.sale_price_inc_tax_total }} Inc.Impuesto.-->
<!--                                </div>-->
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio Neto: </strong><span v-html="component.sale_currency"></span> {{ component.net_price }} (PER {{ component.net_price_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto Neto Total: </strong> <span v-html="component.sale_currency"></span> {{ component.net_price_tax_total }}-->
<!--                                </div>-->
                                <div class="col-4 my-auto">
                                    <strong>Total Neto: </strong> <span v-html="component.sale_currency"></span>{{ component.net_price_inc_tax_total }} Inc.Impuesto.
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(V) Base: </strong><span v-html="component.currency_base"></span> {{ component.sale_price_base }} (PER {{ component.sale_quantity_rule }})
                                </div>
                                <div class="col-8 my-auto">
                                    <strong>Tipo de Cambio: </strong> {{ component.sale_exchange_rate }} (De: <span v-html="component.sale_currency "></span> a: <span v-html="component.currency_base"></span>)
                                </div>
                            </div>


                            <div class="rooms-table row canSelectText">
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> {{ component.tax_total_base }}-->
<!--                                </div>-->
                                <div class="col-8 my-auto">
                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> {{ component.sale_price_inc_tax_total_base }} Inc.Impuesto.
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText rowTitle rowTitleChoosed-true" >
                                <div class="col-12 my-auto">
                                    <strong>DATOS DE OPERADOR TURÍSTICO </strong>
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Cantidad: </strong>{{ component.cost_quantity }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuestos: </strong>-->
<!--                                    <span v-if="component.cost_tax_inclusive == 0">Agregados al precio(C)</span>-->
<!--                                    <span v-if="component.cost_tax_inclusive == 1">Incluídos</span>-->
<!--                                </div>-->
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Nota: </strong>{{ component.operational_note }}-->
<!--                                </div>-->
                            </div>


                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(C): </strong><span v-html="component.cost_currency"></span> {{ component.cost_price }} (PER {{ component.cost_quantity_rule }})
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Impuesto(C): </strong> {{ component.cost_tax_percentage }}% = <span v-html="component.cost_currency"></span> {{ component.cost_tax_total }}-->
<!--                                </div>-->
                                <div class="col-4 my-auto">
                                    <strong>Total(C): </strong> <span v-html="component.cost_currency"></span>{{ component.cost_price_inc_tax_total }} Inc.Impuesto.
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Precio(C) Base: </strong><span v-html="component.currency_base"></span> {{ component.cost_price_base }} (PER {{ component.cost_quantity_rule }})
                                </div>
                                <div class="col-8 my-auto">
                                    <strong>Tipo de Cambio: </strong> {{ component.cost_exchange_rate }} (De: <span v-html="component.cost_currency  "></span> a: <span v-html="component.currency_base"></span>)
                                </div>
                            </div>

                            <div class="rooms-table row canSelectText">
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Total de impuestos: </strong><span v-html="component.currency_base"></span> {{ component.cost_tax_total_base }}-->
<!--                                </div>-->
<!--                                <div class="col-8 my-auto">-->
<!--                                    <strong>Precio(V) Total: </strong> <span v-html="component.currency_base"></span> {{ component.cost_price_inc_tax_total_base }} Inc.Impuesto.-->
<!--                                </div>-->
                            </div>

                            <div class="rooms-table row canSelectText">
                                <div class="col-4 my-auto">
                                    <strong>Agregado en la fecha: </strong>{{ component.component_added_datetime | formatDate }}
                                </div>
                                <div class="col-4 my-auto">
                                    <strong>Si fue adicional (Nombre del usuario): </strong> {{ component.upsell_username }}
                                </div>
<!--                                <div class="col-4 my-auto">-->
<!--                                    <strong>Proveedor: </strong>{{ component.supplier_name }} ({{ component.supplier_tour_code }})-->
<!--                                </div>-->
                            </div>

                        </div>
                    </div>

                </div>

                <div slot="modal-footer">
                    <button @click="chooseAll()" class="btn btn-success" v-show="showContentViewServices">{{$t('buttons.chooseAll')}}</button>

                    <button class="btn btn-success" @click="newFile(0)" type="button"
                            :disabled="!(componentsChoosed.length)" v-show="showContentViewServicesChoosed">
                        <font-awesome-icon :icon="['fas', 'circle']"/> Nuevo File
                    </button>
                    <button class="btn btn-success" @click="addFile()" type="button"
                            :disabled="!(componentsChoosed.length)" v-show="showContentViewServicesChoosed">
                        <font-awesome-icon :icon="['fas', 'plus']"/> Agregar a un File
                    </button>

                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

            <b-modal title="Agregar a un File" centered ref="my-modal-add-to-file" size="md">
                <p class="text-center">Seleccione el file:</p>

                <v-select :options="files"
                          :value="nrofile"
                          label="name" :filterable="false" @search="onSearch"
                          placeholder="Buscar por pasajero o Nº de file"
                          v-model="fileSelected" name="file" id="file" style="height: 35px;">
                    <template slot="option" slot-scope="option">
                        <div class="d-center">
                            {{ option.NROREF }} - {{ option.NOMBRE }}
                        </div>
                    </template>
                    <template slot="selected-option" slot-scope="option">
                        <div class="selected d-center">
                            {{ option.NROREF }} - {{ option.NOMBRE }}
                        </div>
                    </template>
                </v-select>

                <div slot="modal-footer">
                    <button class="btn btn-success" @click="newFile(1)" type="button">
                        <font-awesome-icon :icon="['fas', 'circle']"/> Agregar File
                    </button>
                    <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
                </div>
            </b-modal>

        </div>
    </div>
</template>
<script>
  import TableServer from '../../../components/TableServer'
  import { API } from '../../../api'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BPopover from 'bootstrap-vue/es/components/popover/popover'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import Loading from 'vue-loading-overlay'
  import Vue from 'vue'
  import { Event } from 'vue-tables-2'
  Vue.use(Event)

  export default {
    components: {
      'table-server': TableServer,
      'b-popover': BPopover,
      BFormCheckbox,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal,
      vSelect,
      datePicker,
      Loading
    },
    data: () => {
      return {
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
        agents:[],
        agentSelected:[],
        componentsChoosed:[],
        dateTypeSelected:{
          code : 0,
          label : "Creación",
          param : "made"
        },
        date_types:[
          {
            code : 0,
            label : "Creación",
            param : "made"
          },
          {
            code : 1,
            label : "Inicio",
            param : "start"
          },
          {
            code : 2,
            label : "Termino",
            param : "end"
          },
          {
            code : 3,
            label : "Una específica",
            param : "component_start_date"
          }
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
        table: {
          columns: ['booking_id','made_date_time', 'dates', 'lead_customer_name', 'agent', 'booking_name', 'status_text', 'sales_display', 'actions']
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
        tmpBookings : []
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
      if (localStorage.getItem('servicesTourcms')) {
        try {
          this.componentsChoosed = JSON.parse(localStorage.getItem('servicesTourcms'))
        } catch(e) {
          localStorage.removeItem('servicesTourcms')
        }
      }
      this.searchAgents()
    },
    created () {

      let myDateFrom = this.date_from
      let myDateTo = this.date_to
      this.urlbookings = '/api/channel/tourcms/bookings?token=' + window.localStorage.getItem('access_token') +
        '&type_date=' + this.dateTypeSelected.param + '&date_from=' + myDateFrom + '&date_to=' + myDateTo +
        '&date=' + this.date + '&active=' + this.statusSelected.param +
        '&agent_id=' + this.agentSelected.code +
        '&final_check=' + this.finalCheckSelected.param + '&lead_customer_surname=' +  this.filter_client_name

      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
      Event.$on('vue-tables.loaded', function (data) {
        // console.log('My event has been triggered', data)
        this.loading = false
        // console.log(this.loading)
      })
    },
    computed: {
      date_from:{
        get : function(){
          let date = new Date()
          let week = 1000 * 60 * 60 * 24 * 7
          let myDate = new Date(date.getTime() - week)

          let day = myDate.getDate()
          let month = myDate.getMonth() + 1
          let year = myDate.getFullYear()

          let myDateFormat
          if(month < 10){
            myDateFormat = `${day}/0${month}/${year}`
          }else{
            myDateFormat = `${day}/${month}/${year}`
          }
          this.my_date_from = myDateFormat
          return myDateFormat
        },
        set : function(newDate){
          this.my_date_from = newDate
          return newDate
        }
      },
      date_to:{
        get : function(){
          let date = new Date()
          let day = date.getDate()
          let month = date.getMonth() + 1
          let year = date.getFullYear()

          let today
          if(month < 10){
            today = `${day}/0${month}/${year}`
          }else{
            today = `${day}/${month}/${year}`
          }
          this.my_date_to = today
          return today
        },
        set : function(newDate){
          this.my_date_to = newDate
          return newDate
        }
      },
      tableOptions: function () {
        return {
          headings: {
            booking_id: 'ID',
            made_date_time: this.$i18n.t('creation_date'),
            dates: this.$i18n.t('start') + ' - ' + this.$i18n.t('end'),
            booking_name: this.$i18n.t('service'),
            lead_customer_name: this.$i18n.t('customer'),
            agent: 'Agente',
            status_text: this.$i18n.t('status'),
            sales_display: this.$i18n.t('sales'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: false
        }
      }
    },
    methods: {
        toggleStatus(id, status) {
            this.loading = true
            API.post('/channel/tourcms/'+id+'/status', { status : status })
                .then((result) => {
                    if(result.data.success){
                        this.onUpdate()
                    }
                    this.loading = false
                }).catch(() => {
                this.loading = false
            })
        },
        searchAgents(){
            // agents
            API.get('/channel/tourcms/bookings/agents')
                .then((result) => {
                    this.agents = result.data
                }).catch((e) => {
                    console.log(e)
            })
        },
      onSearch(search, loading) {
        loading(true)
        API.post('/channel/tourcms/booking/files', { query : search })
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
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      onUpdate () {
        let myTypeDate = this.dateTypeSelected.param

        if( myTypeDate == 'component_start_date' && ( this.date == '' || this.date == null ) ){
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('bookingName'),
            text: this.$t('error.messages.choose_date')
          })
        } else {
            this.loading = true
            let myDateFrom = this.my_date_from
            let myDateTo = this.my_date_to
            this.urlbookings = '/api/channel/tourcms/bookings?token=' + window.localStorage.getItem('access_token') +
              '&type_date=' + myTypeDate + '&date_from=' + myDateFrom + '&date_to=' + myDateTo +
              '&date=' + this.date + '&active=' + this.statusSelected.param +
                '&agent_id=' + this.agentSelected.code +
              '&final_check=' + this.finalCheckSelected.param + '&lead_customer_surname=' +  this.filter_client_name

            this.$refs.table.$refs.tableserver.refresh()
            this.loading = false
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

        // if( this.tmpBookings[ me.booking_id ] ){
        //   this.viewBooking = this.tmpBookings[ me.booking_id ]
        //   this.modalTitle = this.viewBooking.booking_name + ' | ' + this.viewBooking.status_text
        //   this.loadingModal = false
        // } else {
        //     API({
        //       method: 'GET',
        //       url: '/channel/tourcms/booking/' + me.booking_id
        //     })
        //       .then((result) => {
        //         this.tmpBookings[ me.booking_id ] = result.data.booking
        //
        //         this.tmpBookings[ me.booking_id ].components.forEach( (component, key) => {
        //           this.componentsChoosed.forEach( componentChoosed => {
        //                 if( componentChoosed.component_id == component.component_id ){
        //                     this.checkboxs['check_'+component.component_id] = true
        //                 } else {
        //                     this.checkboxs['check_'+component.component_id] = false
        //                 }
        //             } )
        //         } )
        //         this.viewBooking = result.data.booking
        //         this.modalTitle = this.viewBooking.booking_name + ' | ' + this.viewBooking.status_text
        //         this.loadingModal = false
        //
        //       }).catch((e) => {
        //         console.log(e)
        //           this.$notify({
        //             group: 'main',
        //             type: 'error',
        //             title: 'Error al obtener booking:' + me.booking_id,
        //             text: this.$t('error.messages.connection_error')
        //           })
        //     })
        // }
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
                  edad : p.agecat_text,
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
              edad : p.agecat_text,
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
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: 'Error al obtener el código de un servicio',
                  text: this.$t('error.messages.connection_error')
                })
                err++
                return
              }

              services.push({
                code : comp.product_code,
                start_date : comp.start_date,
                end_date : comp.end_date,
                paxdes : comp.paxdes,
                total : comp.sale_price_inc_tax_total, // Precio(V) Total: EUR 50.00 Inc.Impuesto.
                currency : comp.currency_base,
                canadu : comp.canadu,
                canchd : comp.canchd,
                caninf : comp.caninf,
                paxsData : comp.paxsData,
                booking_id : comp.booking_id,
                channel_id : comp.channel_id
              })

            } )

            if( err == 0 ){

                this.loading = true
                this.loadingModal = true
                let data = {
                    services : services,
                    nrofile : nrofile
                }
                API({
                  method: 'post',
                  url: '/channel/tourcms/booking/',
                  data: data
                })
                  .then((result) => {
                    console.log(result.data)
                    this.loadingModal = false
                    this.loading = false
                    // Si es successfull
                      if( result.data.success ){
                          this.$notify({
                              group: 'main',
                              type: 'success',
                              title: "Satisfactorio",
                              text: 'Reserva generada con el N°: '+ result.data.detail
                          })
                          this.componentsChoosed = []
                          localStorage.setItem('servicesTourcms', '')
                          this.hideModal()
                      } else {
                          this.$notify({
                              group: 'main',
                              type: 'error',
                              title: 'Error en el ws',
                              text: result.data.detail
                          })
                      }


                  }).catch((e) => {
                    this.loadingModal = false
                    this.loading = false
                        console.log(e)
                      this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error al generar la reserva',
                        text: this.$t('error.messages.connection_error')
                      })
                })
            } else {
                this.loadingModal = false
                this.loading = false
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error al generar la reserva',
                text: 'No se encontró el código de servicio en uno o más servicios'
              })
            }
        } else {
            this.loadingModal = false
            this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error al agregar a un File',
            text: 'Por favor seleccione el file a agregar'
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
    filters: {
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
  }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
    .v-select input{
        height: 25px;
    }
    .btn-search{
        margin-top: 34px;
        float: right;
    }
    .modal-backdrop {
        background-color: #00000052;
    }
    .canSelectText{
        user-select: text;
    }
    .row0{
        background: #e3fcff;
        padding: 5px;
        border-radius: 9px;
        line-height: 16px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 11px;
    }
    .row1{
        background: #ffeeff;
        padding: 5px;
        border-radius: 9px;
        line-height: 16px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 11px;
    }
    .rooms-table{
        padding: 5px;
    }
    .rowTitle{
        text-align: center;
        background: white;
        border: solid 1px #8a9b9b;
        margin: 3px -6px;
    }
    .rowTitleChoosed-true{
        background: #ddffd2;
    }
</style>

<i18n src="./tourcms.json"></i18n>
