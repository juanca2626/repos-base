@extends('layouts.app')
@section('content')
    <section class="packages__details">
        <div class="hero__primary">
        </div>
    </section>

    <b-overlay :show="loading_reservation" :opacity="0.42" rounded="lg">
        <section class="packages__details my-5 container pb-5 border-bottom">
            <h1 class="package-title color-primary mb-5">2. {{trans('package.label.complete_the_missing_information')}}</h1>

            <h2 class="mt-3" v-if="fileNumber">{{trans('package.label.file_number')}}:</h2>
            <div class="wrapper center-block" v-if="fileNumber">
                <div class="form-group">
                    <input type="text" class="form-control mt-3" v-model="fileNumber" disabled>
                </div>
            </div>

            <h2 class="mt-3">{{trans('package.label.passenger_information')}}:</h2>
            <div class="wrapper center-block">
                <modal-passengers ref="modal_passengers"></modal-passengers>
            </div>

            <h2 class="mt-3">{{trans('package.label.flight_information')}}:</h2>
            <modal-flights ref="modal_flights"></modal-flights>

            <template v-for="(service, s) in data_reservation.services_added_package">
                <h2 class="mt-3 mb-4">@{{ service.name }}</h2>
                <form class="mt-4 content-pax">
                    <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center">
                            <label class="">
                                Asignar pasajeros:
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-start align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center mr-4" v-for="index in paxs">
                            <label v-bind:class="['checkbox-ui', (flag_check[s]) ? '' : 'disabled']"
                                   v-bind:disabled="!flag_check[s]"
                                   v-on:click="toggleCheck(s, (index - 1))">
                                <i v-bind:class="['fa', (paxs_selected[s].paxs[(index - 1)] === true) ? 'fa-check-square' : 'fa-square']"></i>
                                {{ trans('board.label.passenger') }} @{{ index }}
                            </label>
                        </div>
                    </div>
                </form>
            </template>
        </section>
        <section class="packages__details my-5 container pb-5">
            <form class="mt-4 content-pax">
                <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                    <div class="d-block">
                        <button type="button" class="btn-primary" @click="reservationGo"
                                style="width: 220px;"
                                :disabled="!!loading_reservation">
                            <i class="fas fa-spinner fa-spin" v-show="loading_reservation"></i>
                            {{trans('package.label.book')}}
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </b-overlay>
    <b-modal class="modal-central modal-content" id="modal_reservation_errors" aria-hidden="true"
             ref="modal_reservation_errors" :no-close-on-backdrop="true" :no-close-on-esc="true"
             :hide-header-close="true">
        <h1 class="mb-3">
            <i class="fas fa-exclamation-triangle"></i> {{ trans('reservations.label.reservation') }}
        </h1>

        <p class="alert alert-danger text-justify p-3">
            {{trans('quote.label.sorry_your_operation_could_not_carried_out')}}
        </p>
        <div v-if="errors_reservations.services.length > 0">
            <h4 class="font-weight-bold mt-3" style="color:#000000 !important">
                {{trans('quote.label.services')}}:
            </h4>
            <div v-for="service in errors_reservations.services">
                <p style="font-weight: bold !important;color:#000000 !important">
                    <i class="fas fa-times-circle text-danger"></i> @{{ service.service_code }}
                </p>
                <ul>
                    <li class="text-danger" v-for="error in service.errors">@{{ error.error }}</li>
                </ul>
            </div>
        </div>
        <div v-if="errors_reservations.hotels.length > 0">
            <h4 class="font-weight-bold mt-3" style="color:#000000 !important">
                {{trans('quote.label.hotels')}}:
            </h4>
            <div v-for="hotel in errors_reservations.hotels">
                <p style="font-weight: bold !important;color:#000000 !important">
                    <i class="fas fa-times-circle text-danger"></i> @{{ hotel.hotel_code }}
                </p>
                <ul>
                    <li class="text-danger" v-for="error in hotel.errors">@{{ error.error }}</li>
                </ul>
            </div>
        </div>
        <hr>
        <button @click="closeModalReservationErrors()" style="height: 50px !important;"
                class="btn btn-cancelar">{{ trans('quote.label.close') }}
        </button>
    </b-modal>
    @include('layouts.partials.write_us')
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                fileNumber: '',
                loading_reservation: false,
                data_reservation: null,
                lang: 'en',
                paxs: 0,
                canadl: 0,
                canchd: 0,
                caninf: 0,
                flag_check: {},
                paxs_selected: {},
                file_code: '',
                client_name: '',
                client_code: '',
                reservation: {},
                errors_reservations: {
                    hotels: [],
                    services: []
                },
            },

            created: function () {
                this.client_name = localStorage.getItem('client_name')
                this.client_code = localStorage.getItem('client_code')
                this.client_id = localStorage.getItem('client_id')
                this.lang = localStorage.getItem('lang')

                if (localStorage.getItem('package_reservation')) {
                    this.data_reservation = JSON.parse(localStorage.getItem('package_reservation'))
                    this.fileNumber = this.data_reservation.file_number || '';

                    if (localStorage.getItem("user_type_id") == 4) {
                        dataLayer.push({
                            // "fileNumber": fileNumber,
                            "event": "begin_checkout",
                            "funnel_type": "package-funnel",
                            "currency": "USD",
                            "value": this.data_reservation.total_amount,
                            "package_id": this.data_reservation.id,
                            "package_code": this.data_reservation.code,
                            "package_name": this.data_reservation.package_name_gtm,
                            "items": this.data_reservation.services_gtm
                        });
                    }

                    } else {
                    window.location.href = '/packages'
                }
            },
            mounted () {
                this.canadl = (this.data_reservation.quantity_persons && this.data_reservation.quantity_persons.adults) ? parseFloat(this.data_reservation.quantity_persons.adults) : 0
                this.canchd = (this.data_reservation.quantity_persons && (this.data_reservation.quantity_persons.child_with_bed || this.data_reservation.quantity_persons.child_without_bed)) ? (parseFloat(this.data_reservation.quantity_persons.child_with_bed || 0) + parseFloat(this.data_reservation.quantity_persons.child_without_bed || 0)) : 0
                this.caninf = 0
                this.paxs = this.canadl + this.canchd + this.caninf

                this.data_reservation.services_added_package.forEach((service, s) => {
                    Vue.set(this.flag_check, s, true)
                    Vue.set(this.paxs_selected, s, {
                        limit: service.quantity_adults,
                        paxs: {}
                    })

                    for (let i = 0; i < this.paxs; i++) {
                        Vue.set(this.paxs_selected[s].paxs, i, false)
                    }
                })

                // pasar el el numero de file para que traiga los pasajeros
                this.$refs.modal_passengers.modalPassengers('package', this.fileNumber, parseFloat(this.paxs), parseFloat(this.canadl), parseFloat(this.canchd), parseFloat(this.caninf))
                this.$refs.modal_flights.modalFlight('', this.data_reservation.flights)
            },
            computed: {},
            methods: {
                chargeEventGtm: function(package) {
                    if (localStorage.getItem("user_type_id") == 4) {
                        dataLayer.push({
                            "event": "begin_checkout",
                            "funnel_type": "package-funnel",
                            "currency": "USD",
                            "value": package.amounts.total_amount,
                            "package_id": package.id,
                            "package_code": package.code,
                            "package_name": package.descriptions.name_gtm,
                            "items": package.services_gtm
                        });
                    }
                },
                goBiosafetyProtocols () {
                    window.location.href = '/biosafety-protocols'
                },
                toggleCheck: function (s, index) {
                    if (this.flag_check[s] === true || !this.paxs_selected[s].paxs[index] === false) {
                        this.flag_check[s] = true
                        let paxs_total = 0
                        this.paxs_selected[s].paxs[index] = !this.paxs_selected[s].paxs[index]

                        Object.entries(this.paxs_selected[s].paxs).forEach((pax, p) => {
                            if (pax[1] === true) {
                                paxs_total++

                                if (paxs_total === this.paxs_selected[s].limit) {
                                    this.flag_check[s] = false
                                }
                            }
                        })
                    }
                },
                processReservation: function () {
                    // Aquí se ejecuta la reserva..
                    if (this.data_reservation != null) {
                        this.data_reservation.passengers = this.$refs.modal_passengers.getPassengers()
                        this.data_reservation.flights = this.$refs.modal_flights.getFlights()
                        this.data_reservation.file_code = this.fileNumber

                        this.data_reservation.services_added_package.forEach((service, s) => {
                            Vue.set(this.data_reservation.services_added_package[s], 'paxs', this.paxs_selected[s].paxs)
                        })

                        this.loading_reservation = true
                        axios.post(baseExternalURL + 'services/client/reservation/package', this.data_reservation
                        ).then((result) => {
                            if (result.data.success) {
                                result.data.data.file_code = this.fileNumber;
                                axios.post(baseExternalURL + 'services/hotels/reservation/add', result.data.data
                                ).then((result) => {
                                    this.loading_reservation = false
                                    if (result.data.success) {
                                        this.reservation = result.data.data

                                        if(localStorage.getItem('parameters_ota_generic')!=null){

                                           let parameters_ota_generic = JSON.parse(localStorage.getItem('parameters_ota_generic'))

                                            let data_reservation = {
                                                generic_service_id:parameters_ota_generic.generic_service_id,
                                                file_code:result.data.data.file_code

                                            }
                                            axios.post(baseExternalURL + 'api/generic_otas/reservation/package', data_reservation
                                            ).then((result2) => {
                                                localStorage.removeItem('parameters_ota_generic')

                                                window.location = baseURL + 'package/booking/' + result.data.data.file_code
                                            })
                                        }else{
                                            window.location = baseURL + 'package/booking/' + result.data.data.file_code
                                        }

                                    }
                                }).catch((e) => {
                                    this.loading_reservation = false
                                })
                            } else {
                                this.errors_reservations = result.data.errors
                                this.openModalReservationErrors()
                                this.loading_reservation = false
                            }

                        }).catch((e) => {
                            this.loading_reservation = false
                        })
                    }
                },
                openModalReservationErrors: function () {
                    this.$refs['modal_reservation_errors'].show()
                },
                closeModalReservationErrors: function () {
                    this.$refs['modal_reservation_errors'].hide()
                },
                async reservationGo () {
                    let vm = this
                    const response = await vm.$refs.modal_passengers.validatePassengers()

                    if(response)
                    {
                        vm.processReservation();
                    }
                }

            }
        })
    </script>
@endsection
