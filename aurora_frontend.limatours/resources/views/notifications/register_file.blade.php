@extends('layouts.app')

@section('content')
    <template v-if="!loading">
        <modal-flights ref="modal_flights"></modal-flights>
        <hr />
        <modal-passengers ref="modal_passengers"></modal-passengers>
        <accommodation-passengers ref="accommodation_passengers"></accommodation-passengers>
    </template>
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                loading: true,
                nrofile: '{{ $nrofile }}',
                hotels: [],
                services: [],
                services_ws: [],
                filters: [],
                filter_city: '',
                type_room: {},
                loading_button: false,
                file: {},
                paxs: '{{ $paxs }}',
                adl: '{{ $canadl }}',
                chd: '{{ $canchd }}',
                inf: '{{ $caninf }}'
            },
            created: function () {
                let vm = this
                vm.initialize()

                setTimeout(function () {
                    vm.showModalFlights()
                    vm.showModalPaxs()
                }, 1000)
            },
            mounted() {
                
            },
            computed: {

            },
            methods: {
                initialize: function () {
                    let vm = this
                    localStorage.setItem('flag_notify_flights', 1)
                    localStorage.setItem('flag_notify_paxs', 1)
                    localStorage.setItem('lang', '{{ $lang }}')
                    localStorage.setItem('modal_aurora_flights', 'false')
                    localStorage.setItem('modal_aurora_paxs', 'false')

                    vm.loading = false
                },
                showModalFlights: function () {
                    eval("this.$refs.modal_flights.modalFlight('" + this.nrofile + "')")
                },
                showModalPaxs: function () {
                    localStorage.setItem('search_passengers', 1)
                    eval("this.$refs.modal_passengers.modalPassengers('file', '" +
                        this.nrofile + "', '" +
                        this.paxs + "', '" +
                        this.adl + "', '" +
                        this.chd + "', '" +
                        this.inf + "')")
                },
                passengerAccommodation: function () {
                    localStorage.setItem('search_passengers', 1)
                    this.$refs.accommodation_passengers.accommodationPassengers('file', this.nrofile, [])
                }
            }
        })
    </script>
@endsection
