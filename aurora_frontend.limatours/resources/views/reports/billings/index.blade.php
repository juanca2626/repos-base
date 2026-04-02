@extends('layouts.app')
@section('content')
<div>
    <section class="page-board">
        <div class="container-fluid">
            <!-- div class="container">
                    {{ trans('board.label.order_report') }}
                </div -->

            <!-------- Reporte de facturacion -------->
            <div class="reporte-facturacion container">
                <div class="form">
                    <div class="form-row justify-content-between">
                        <div class="form-group mx-4">
                            <!-- input type="text" class="form-control client" placeholder="Seleccionar un cliente" v-model="customer" required="required" -->
                            <div class="d-flex mt-3">
                                <div class="txt-filter mx-5 text-muted">{{ trans('board.label.filter') }}: </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="check" v-model="check" id="fecini" value="1" />
                                    <label class="form-check-label" for="fecini">{{ trans('board.label.startdate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="check" v-model="check" id="fecfin" value="2">
                                    <label class="form-check-label" for="fecfin">{{ trans('board.label.endingdate') }}</label>
                                </div>
                            </div>
                            <div class="d-flex mt-3">
                                <div class="txt-filter mx-5 text-muted">{{ trans('board.label.show') }}: </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" v-model="type" id="all" value="4" />
                                    <label class="form-check-label" for="all">{{ trans('board.label.all') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" v-model="type" id="sta_pend" value="2" />
                                    <label class="form-check-label" for="sta_pend">{{ trans('board.label.pendings') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" v-model="type" id="sta_afav" value="3" />
                                    <label class="form-check-label" for="sta_afav">{{ trans('board.label.infavor') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mx-4 fecha">
                            <date-range-picker
                                :locale-data="locale_data"
                                :time-picker24-hour="timePicker24Hour"
                                :show-week-numbers="showWeekNumbers"
                                :auto-apply="true"
                                :ranges="false"
                                :auto-apply="false"
                                v-model="dateRange">
                            </date-range-picker>
                        </div>
                        <div class="form-group mx-4 reporte-boton">
                            <button class="btn btn-primary" v-on:click="showDetailBilling()">
                                {{ trans('board.btn.search') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                        <p class="mb-0">{{ trans('board.label.loading') }}</p>
                    </div>
                    <div class="alert alert-warning" v-if="quantityBillings == 0 && !loading">
                        <p class="mb-0">{{ trans('board.label.no_data') }}</p>
                    </div>
                    <table class="table text-center table-facturacion" v-if="quantityBillings > 0 && !loading">
                        <thead>
                            <tr>
                                <th scope="col">{{ trans('board.th.customer') }}</th>
                                <th scope="col">QRV</th>
                                <th scope="col">{{ trans('board.th.file') }}</th>
                                <th scope="col">{{ trans('board.th.group_name') }}</th>
                                <th scope="col">{{ trans('board.th.number_guests') }}</th>
                                <th scope="col">{{ trans('board.th.service_startdate') }}</th>
                                <th scope="col">{{ trans('board.th.enddate_of_the_service') }}</th>
                                <th scope="col">{{ trans('board.th.amount_must') }}</th>
                                <th scope="col">{{ trans('board.th.debit') }}</th>
                                <th scope="col">{{ trans('board.th.payment_amount') }}</th>
                                <th scope="col">{{ trans('board.th.balance') }}</th>
                                <!-- th scope="col">{{ trans('board.th.pay') }}</th -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(billing, k) in billings">
                                <td>@{{ billing.codcli }}</td>
                                <td>@{{ billing.codope }}</td>
                                <td>@{{ billing.nroref }}</td>
                                <td>@{{ billing.descri }}</td>
                                <td>@{{ billing.canpax }}</td>
                                <td>@{{ billing.diain }}</td>
                                <td>@{{ billing.diaout }}</td>
                                <td>@{{ billing.stdebe }}</td>
                                <td>@{{ billing.ncdebe }}</td>
                                <td>@{{ billing.impago }}</td>
                                <td>@{{ billing.saldo }}</td>
                                <!-- td><button v-if="billing.saldo > 0" type="button" class="btn btn-danger" v-on:click="showBilling(k)"><i class="icon-shopping-cart"></i></button></td -->
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" v-on:click="downloadExcel('billings')" v-if="quantityBillings > 0 && !loading" class="btn btn-primary btn-md">Descargar datos en Excel</button>
                </div>
                <div class="row d-flex align-items-center justify-content-between mt-5" v-if="quantityBillings > 0 && !loading">
                    <div id="graphic-pie">
                        <pie-chart :data="dataBillings" :colors="['#FE5065','#1BE484']" suffix="%" :legend="true" legend="bottom"></pie-chart>
                    </div>
                    <div class="row box-pago d-flex justify-content-around align-items-center text-center">
                        <div class="col">
                            <div>
                                <h4>{{ trans('board.label.issued_invoices') }}</h4>
                                <p class="cantidad">@{{ quantityBillings }}</p>
                            </div>
                            <div>
                                <h4>{{ trans('board.label.unpaid_bills') }}</h4>
                                <p class="cantidad">@{{ quantityBillingsFailed }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-------- End Repor. facturacion -------->
        </div>
    </section>
    <div>
        <!------- Modal Calendario ------->
        <!-- b-modal id="calendar" size="md" hide-footer v-model="formPay" v-if="billings[billingSelected] != undefined">
                <div class="d-block" style="margin:-20px;">
                    <div>
                        <h2><strong>{{ trans('board.th.pay') }} {{ trans('board.th.file') }}: #@{{ billings[billingSelected].nroref }}</strong></h2>
                    </div>
                    <hr />
                    <div>
                        <p class="pago">{{ trans('board.label.total_amount') }}: $ @{{ billings[billingSelected].saldo }}</p>
                        <form method="POST" action="{{ url('board/payment') }}" v-bind:id="['formPay_' + billingSelected]">
                            {{ csrf_field() }}
                            <input type="hidden" name="nrofile" v-model="billings[billingSelected].nroref" />
                            <input type="text" name="mount" v-model="mount" class="form-control mb-3" />
                            <button type="button" v-bind:disabled="!mount > 0" v-on:click="payBilling(billingSelected)" class="btn btn-success">{{ trans('board.btn.pay_bill') }}</button> <button type="button" v-on:click="cancelPay()" class="btn btn-danger">{{ trans('board.btn.cancel') }}</button>
                        </form>
                    </div>
                </div>
            </b-modal -->
    </div>
</div>
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js" integrity="sha512-wT7uPE7tOP6w4o28u1DN775jYjHQApdBnib5Pho4RB0Pgd9y7eSkAV1BTqQydupYDB9GBhTcQQzyNMPMV3cAew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    new Vue({
        el: '#app',
        data: {
            _module: 'billing_report',
            update_menu: 1,
            quote_date: '',
            icon: 'icon-filter-fire',
            options: {
                format: 'DD/MM/YYYY',
                useCurrent: true,
            },
            loadingModal: false,
            loading: false,
            dataURL: '{!! $dataURL !!}',
            dataBillings: [],
            billings: [],
            quantityBillings: 0,
            quantityBillingsFailed: 0,
            detailBillings: [],
            timePicker24Hour: false,
            showWeekNumbers: false,
            singleDatePicker: true,
            startDate: moment().add('days', 2).format('Y-MM-DD'),
            minDate: moment().add('days', 2).format('Y-MM-DD'),
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
            check: 1, // para las fechas de facturacion..
            type: 4, // todos - facturacion..
            dateRange: '',
            billingSelected: 0,
            formPay: 0,
            mount: 0,
            visible: 0,
            nrofile: '',
        },
        created: function() {

        },
        mounted() {
            const flag_search = "<?php echo @$_GET['flag_search']; ?>";

            if (flag_search === "true") {
                localStorage.setItem('client_code', "<?php echo @$_GET['client_code']; ?>");
                localStorage.setItem('client_id', "<?php echo @$_GET['client_id']; ?>");
                this.nrofile = '<?php echo @$_GET['file_code'] ?>';
                this.dateRange = '';

                this.showDetailBilling();
            }
        },
        computed: {},
        methods: {
            downloadExcel: function(_type) {
                window.location = baseURL + 'export_excel?type=' + _type + '&table=';
            },
            showDetailBilling: function() {
                const flag_search = "<?php echo @$_GET['flag_search']; ?>";

                if (flag_search !== "true") {
                    if ((localStorage.getItem('client_code') == '' || localStorage.getItem('client_code') == null) && localStorage.getItem('user_type_id') != 1) {
                        this.$toast.error('Seleccione un cliente para poder filtrar los files', {
                            // override the global option
                            position: 'top-right'
                        })
                        return false
                    }

                    if (this.dateRange == '') {
                        this.$toast.error('Seleccione un rango de fechas para poder filtrar los files', {
                            // override the global option
                            position: 'top-right'
                        })
                        return false
                    }
                }

                this.loading = true

                axios.post(
                        baseURL + 'board/searchBillings', {
                            lang: this.lang,
                            customer: localStorage.getItem('client_code'),
                            check: this.check,
                            type: this.type,
                            dateRange: this.dateRange,
                            nrofile: this.nrofile,
                            flag_search: flag_search,
                        }
                    )
                    .then((result) => {
                        this.loading = false
                        this.billings = result.data.billings
                        this.quantityBillings = result.data.quantityBillings
                        this.quantityBillingsFailed = result.data.quantityBillingsFailed
                        this.detailBillings = result.data.detail

                        eval("this.dataBillings = {'% Monto debe - $" + this.detailBillings.monto_total_stdebe + "'  : " + this.detailBillings.porcentaje_stdebe + ", '% del importe de pago - $" + this.detailBillings.monto_total_impago + "': " + this.detailBillings.porcentaje_impago + "}")
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            showBilling: function(id) {
                this.formPay = true
                this.billingSelected = id
            },
            cancelPay: function() {
                this.formPay = false
            },
            payBilling: function(index) {
                // Mostrar un loader..
                this.showLoader("{{ trans('board.label.loading') }}")
                // this.mount = this.billings[index - 1].saldo

                setTimeout(function() {
                    $('#formPay_' + index).submit()
                }, 10)
            },
            toggleInputPay: function(value) {
                this.inputPay = value
            },
        }
    })
</script>
@endsection