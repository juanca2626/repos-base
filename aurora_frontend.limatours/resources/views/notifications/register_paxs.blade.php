@extends('layouts.app')

@section('content')
<template>
    <modal-passengers v-if="!loading" ref="modal_passengers"></modal-passengers>
    <accommodation-passengers v-if="!loading" ref="accommodation_passengers"></accommodation-passengers>
</template>
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                hotels: [],
                services: [],
                services_ws: [],
                filters: [],
                filter_city: '',
                type_room: {},
                loading: true,
                loading_button: false,
                file: {},
                nrofile: '{{ $nrofile }}',
                paxs: '{{ $paxs }}',
                adl: '{{ $canadl }}',
                chd: '{{ $canchd }}',
                inf: '{{ $caninf }}'
            },
            created: function () {
                localStorage.setItem('flag_notify_paxs', 1)
                localStorage.setItem('lang', '{{ $lang }}')
                localStorage.setItem('modal_aurora_paxs', 'false')
                let vm = this

                setTimeout(function () {
                    vm.loading = false

                    setTimeout(function () {
                        vm.showModal()
                    }, 10)
                }, 10)
            },
            mounted() {
            },
            computed: {

            },
            methods: {
                showModal: function () {
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
