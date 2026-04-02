<template>
    <div class="page-central">
        <div class="">
            <h2>Central Reservas</h2>
        </div>

        <div class="col-12 cotizacion-categorias d-flex justify-content-between align-items-center">
            <div>
                <button :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='tourcms') }" type="button"
                        @click="redirectPage('tourcms')">
                    Tourcms
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='expedia') }" type="button"
                        @click="redirectPage('expedia')">
                    Expedia
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='despegar') }" type="button"
                        @click="redirectPage('despegar')">
                    Despegar
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='pentagrama') }" type="button"
                         @click="redirectPage('pentagrama')">
                    Pentagrama
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='get-your-guide') }" type="button"
                         @click="redirectPage('get-your-guide')">
                    Get Your Guide
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='otas_generic') }" type="button"
                         @click="redirectPage('otas_generic')">
                    Otras OTAS
                </button>
                <button  :class="{ 'btn btn-tab categoria' : true, 'active' : (page=='report_otas') }" type="button"
                         @click="redirectPage('report_otas')">
                    Indicador OTS
                </button>
<!--                <button :class="{ 'btn btn-tab categoria tab-all' : true, 'active' : (page=='all') }" type="button"-->
<!--                        @click="redirectPage('all')">-->
<!--                    Todo-->
<!--                </button>-->
            </div>
        </div>
    </div>
</template>
<style>
    .tab-all{
        background: #466697 !important;
    }
</style>
<script>
    export default {
        data: () => {
            return {
                page:'',
                baseURL:window.baseURL
            }
        },
        created() {
        },
        mounted() {
            let _page = localStorage.getItem('central_bookings_page')
            if( _page == '' ){
                this.page = 'tourcms'
            } else {
                this.page = _page
            }

            let _path = window.location.pathname.split('/')
            if( _path[2] != this.page ){
                this.page = _path[2]
                this.redirectPage( this.page )
            }

        },
        methods: {
            redirectPage( page ){
                this.page = page
                localStorage.setItem('central_bookings_page',page)
                window.location.href = baseURL+"central_bookings/"+page;
            }
        },
        filters : {
            formatDate: function (_date) {
                if( _date == undefined ){
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
            }
        }
    }
</script>
