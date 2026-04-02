<template>
    <div class="modal modal--cotizacion modal--envios" id="modalOrders" tabindex="-1" role="dialog">
        <div class="modal-dialog modal--cotizacion__document" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>Relacionar pedido a la cotización: "{{ quoteChoosen.name }}, n°: {{ quoteChoosen.id }}"</b></h3>
                        <div class="link-volver">
                            <div class="col-12 mt-4">
                                <div class="row">
                                    <div class="col-sm-auto">
                                        <span class="modal-paragraph">
                                            <a href="#" data-toggle="modal" data-target="#modalOrders">

                                                <b>Volver a Mis Cotizaciones</b>
                                                <i class="icon-arrow-left"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal--cotizacion__body">
                        <form class="form">
                            <div class="row no-gutters">
                                <div class="form-group input-group col-sm-4 pl-1 mr-auto">
                                    <span class="input-group-text"><i class="icon-search"></i></span>
                                    <input :disabled="loading" class="form-control" id="query_orders" v-model="query_orders"
                                           type="text" placeholder="Filtrar pedidos...">
                                </div>
                            </div>
                        </form>
                        <div class="tbl--cotizacion" v-if="!loading">
                            <div class="tbl--cotizacion__header">
                                <div class="row no-gutters align-items-center">
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__codigo">
                                            <h4 class="tbl--cotizacion__title">N°</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__detalles">
                                            <h4 class="tbl--cotizacion__title">Nombre</h4>
                                        </div>
                                    </div>
                                    <div class="col px-6">
                                        <div class="tbl--cotizacion__fecha">
                                            <h4 class="tbl--cotizacion__title">Detalles</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__ciudad">
                                            <h4 class="tbl--cotizacion__title">N° Anterior</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tbl--cotizacion tbl--cotizacion__detalle">
                                <div class="tbl--cotizacion__content" v-for="o in filterOrders" v-if="orders.length>0" style="background: #edf3ff;">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__codigo">
                                                <span>

                                                    <label>
                                                        <input class="edit" type="checkbox" v-model="o.CHECKUSE" @change="relaciOrder( o )">
                                                        <b> {{ o.NROPED }} - {{ o.NROORD }}</b>
                                                    </label>

                                                </span>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                                <span>
                                                    <span v-html="o.NOMPAX"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col px-6">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                                <p style="font-size:12px; line-height: 8px;">Para: <b :title="o.RAZON">{{ o.CODIGO }}</b></p>
                                                <p style="font-size:12px; line-height: 8px;">De: <b :title="o.EJECU">{{ o.CODUSU }}</b></p>
                                                <p style="font-size:12px; line-height: 8px;">[{{ o.FECREC | formatDate }}, {{ o.HORREC }} ]</p>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__ciudad">
                                                <span>
                                                    <span v-if="o.USECOTI != ''">(V1: {{ o.USECOTI }})</span>
                                                    <span v-if="o.USECOTI_V2 != ''">(V2: {{ o.USECOTI_V2 }})</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tbl--cotizacion__content" v-if="orders.length==0">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col px-12 text-center">
                                            Ningún pedido por mostrar
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tbl--cotizacion" v-if="loading">
                            <center><i class="spinner-grow"></i></center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .navbar .aurora-main .dropdown-menu .nav-primary > .nav-item {
        margin-bottom: 15px;
    }
</style>
<script>
    export default {
        data: () => {
            return {
                loading : false,
                query_orders : '',
                quoteChoosen : '',
                orders:[],
                baseExternalURL:window.baseExternalURL
            }
        },
        created() {
        },
        computed:{
            filterOrders() {
                return this.orders.filter(order => {
                    return order.NOMPAX.toLowerCase().includes(this.query_orders.toLowerCase()) ||
                        order.NROPED.includes( parseInt( this.query_orders) )
                })
            }
        },
        mounted() {

            this.$root.$on('updateQuoteChoosen', (param) => {
                this.quoteChoosen = param
                this.filterOrdersInformix()
            })

        },
        methods: {
            relaciOrder(me){

                console.log( this.quoteChoosen )

                let lastNroped = ''
                let lastNroord = ''

                if( me.CHECKUSE ){
                    for( let i=0; i<this.orders.length; i++){
                        if( ( ( me.NROPED != this.orders[i].NROPED ) ||
                            ( me.NROPED == this.orders[i].NROPED && me.NROORD != this.orders[i].NROORD ) )
                            && this.orders[i].CHECKUSE ){
                            lastNroped = this.orders[i].NROPED;
                            lastNroord = this.orders[i].NROORD;
                            this.orders[i].CHECKUSE = false
                            this.orders[i].USECOTI_V2 = ''
                        }
                    }
                }

                let data = {
                    nropla_v2 : this.quoteChoosen.id,
                    lastNroped : lastNroped,
                    lastNroord : lastNroord,
                    nroped : me.NROPED,
                    nroord : me.NROORD,
                    mode : ( me.CHECKUSE ) ? 1 : 0
                }

                axios.post(window.a3BaseQuoteServerURL + 'api/quote/order/relate', data)
                    .then((returns) => {
                    if (returns.data.success) {
                        if( me.CHECKUSE ){
                            me.USECOTI = this.quoteChoosen.id;
                        } else {
                            me.USECOTI = '';
                        }
                        this.$root.$emit('reloadQuotes')
                    } else {
                        console.log( returns )
                        if( me.CHECKUSE ){
                            me.CHECKUSE = false;
                        } else {
                            me.CHECKUSE = true;
                        }
                    }
                } )
            },
            filterOrdersInformix(){
                this.loading = true
                axios.get('api/quotes/orders/byExecutive')
                    .then(response => {
                        response.data.data.forEach( h=>{
                            h.loading = false
                            h.CHECKUSE = false
                            if( this.quoteChoosen.id == h.USECOTI_V2 ){
                                h.CHECKUSE = true
                            }
                        } )
                        this.orders = response.data.data
                        this.loading = false
                    }).catch(error => {
                    this.loading = false
                        console.log(error)
                })
            }
        },
        filters: {
            formatDate : function (_date) {
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
            reformatDate: function (_date) {
                if( _date == undefined ){
                    return;
                }
                _date = moment(_date).format('ddd D MMM YYYY')
                return _date
            }
        }
    }
</script>
