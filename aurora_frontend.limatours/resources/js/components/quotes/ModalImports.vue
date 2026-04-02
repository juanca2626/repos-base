<template>
    <div class="modal modal--cotizacion modal--envios" id="modalImports" tabindex="-1" role="dialog">
        <div class="modal-dialog modal--cotizacion__document" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>Mis Cotizacione Antiguas</b></h3>
                        <div class="link-volver">
                            <div class="col-12 mt-4">
                                <div class="row">
                                    <div class="col-sm-auto"><span class="modal-paragraph"><a href="#"
                                              data-toggle="modal"
                                              data-target="#modalImports"><b>Volver a Mis Cotizaciones<i
                                            class="icon-arrow-left"></i></b></a></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal--cotizacion__body">
                        <form class="form">
                            <div class="row no-gutters">
                                <div class="form-group input-group col-sm-4 pl-1 mr-auto">
                                    <span class="input-group-text"><i class="icon-search"></i></span>
                                    <input :disabled="loading" class="form-control" id="query_import_quotes" v-model="query_import_quotes"
                                           type="text" placeholder="Filtrar cotizaciones antiguas...">
                                </div>
                            </div>
                        </form>
                        <div class="tbl--cotizacion" v-if="!loading">
                            <div class="tbl--cotizacion__header">
                                <div class="row no-gutters align-items-center">
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__codigo">
                                            <h4 class="tbl--cotizacion__title">codigo</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__detalles">
                                            <h4 class="tbl--cotizacion__title">nombre</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__fecha">
                                            <h4 class="tbl--cotizacion__title">fecha inicio</h4>
                                        </div>
                                    </div>
                                    <div class="col px-6">
                                        <div class="tbl--cotizacion__ciudad">
                                            <h4 class="tbl--cotizacion__title">ciudades</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__duracion">
                                            <h4 class="tbl--cotizacion__title">duracion</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto px-3">
                                        <div class="tbl--cotizacion__tipo">
                                            <h4 class="tbl--cotizacion__title">tipo</h4>
                                        </div>
                                    </div>
                                    <div class="col px-3">
                                        <div class="tbl--cotizacion__acciones">
                                            <h4 class="tbl--cotizacion__title">opción</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tbl--cotizacion tbl--cotizacion__detalle">
                                <div class="tbl--cotizacion__content" v-for="header in headersImport" v-if="headersImport.length>0">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__codigo">
                                                <span>#{{ header.NROPLA }}</span>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__detalles">
                                                <span>
                                                    {{ header.DESCRI }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__fecha"><span>
                                                <b>{{ header.FECPED | reformatDate }}</b></span></div>
                                        </div>
                                        <div class="col px-6">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__ciudad">
                                                <span class="tag" v-for="city in header.cities">
                                                    {{ city }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__duracion text-center">
                                                <span>{{ header.NOCHES }}</span><small>noches</small></div>
                                        </div>
                                        <div class="col-sm-auto px-3">
                                            <div class="tbl--cotizacion__item tbl--cotizacion__tipo text-center">
                                                <span v-if="header.PRIVAD == 'COM'">SIM</span>
                                                <span v-if="header.PRIVAD == 'PRI'">PC</span>
                                            </div>
                                        </div>
                                        <div class="col px-3">
                                            <div class="tbl--cotizacion__acciones">
                                                <a href="#" class="acciones__item">
                                                <span class="icon--acciones">
                                                    <i v-if="!header.loading" @click="importHeader(header)" class="icon-download" title="Importar"></i>
                                                    <i v-if="header.loading" class="fa fa-spin fa-spinner" title="Esperando..."></i>
                                                </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <nav aria-label="page navigation" v-if="headersImport.length>0">
                                    <ul class="pagination">
                                        <li :class="{'page-item':true,'disabled':(pageChosen==1)}" @click="setPage(pageChosen-1)">
                                            <a class="page-link" href="#">Anterior</a>
                                        </li>

                                        <li v-for="page in quote_pages" @click="setPage(page)"
                                                 -                                            :class="{'page-item':true,'active':(page==pageChosen) }">
                                            <a class="page-link" href="javascript:;">{{ page }}</a>
                                        </li>

                                        <li :class="{'page-item':true,'disabled':(pageChosen==quote_pages.length)}" @click="setPage(pageChosen+1)">
                                            <a class="page-link" href="#">Siguiente</a>
                                        </li>
                                    </ul>
                                </nav>
                                <div class="tbl--cotizacion__content" v-if="headersImport.length==0">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col px-12 text-center">
                                            Ninguna cotización por mostrar
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
                quotes : [],
                headersImport:[],
                query_import_quotes : '',
                pageChosen : 1,
                limit : 5,
                quote_pages : [],
            }
        },
        created() {
        },
        mounted() {
            this.filterQuotesInformix()
            let search_quotes = document.getElementById('query_import_quotes')
            let timeout_quotes
            search_quotes.addEventListener('keydown', () => {
                clearTimeout(timeout_quotes)
                timeout_quotes = setTimeout(() => {
                    this.pageChosen = 1
                    this.filterQuotesInformix()
                    clearTimeout(timeout_quotes)
                }, 1000)
            })

        },
        methods: {
            importHeader(h){

                h.loading = true

                let data = {
                    code : h.NROPLA,
                    name : h.DESCRI,
                    date_in : h.FECPED,
                    nights : h.NOCHES,
                    type_class_code : h.PRIVAD
                }
                axios.post('api/quotes/import/header', data)
                    .then(response => {
                        if( response.data.success ){
                            this.$toast.success('Importado correctamente', {
                                position: 'top-right'
                            })
                            h.loading = false
                            this.pageChosen = 1
                            this.filterQuotesInformix()
                            this.$root.$emit('reloadQuotes')
                        } else {
                            this.$toast.error('Ocurrió un error interno', {
                                position: 'top-right'
                            })
                        }
                    }).catch(error => {
                    h.loading = false
                    this.$toast.error(error, {
                        position: 'top-right'
                    })
                    console.log(error)
                })
            },
            setPage(page){
                if( page < 1 || page > this.quote_pages.length ){
                    return;
                }
                this.pageChosen = page
                this.filterQuotesInformix()
            },
            filterQuotesInformix(){
                this.loading = true
                axios.get('api/quotes/import/headers?query=' + this.query_import_quotes+
                    '&page='+this.pageChosen+'&limit='+this.limit)
                    .then(response => {
                        response.data.data.forEach( h=>{
                            h.loading = false
                        } )
                        this.headersImport = response.data.data
                        this.quote_pages = []
                        for( let i=0; i<(response.data.count/this.limit); i++){
                            this.quote_pages.push(i+1)
                        }
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
