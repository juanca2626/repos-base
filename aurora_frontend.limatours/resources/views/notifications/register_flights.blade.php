@extends('layouts.app')

@section('content')
    <modal-flights v-if="!loading" ref="modal_flights"></modal-flights>
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                loading: true,
                nrofile: '{{ $nrofile }}'
            },
            created: function () {
                localStorage.setItem('flag_notify_flights', 1)
                localStorage.setItem('lang', '{{ $lang }}')
                localStorage.setItem('modal_aurora_flights', 'false')
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
                    eval("this.$refs.modal_flights.modalFlight('" + this.nrofile + "')")
                }
            }
        })
    </script>
@endsection
