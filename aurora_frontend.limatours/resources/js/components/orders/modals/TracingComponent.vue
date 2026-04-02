<template>
    <div class="modal modal--cotizacion" id="tracing-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal--cotizacion__document" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title">
                            <b v-if="view == 'list'">Seguimiento de Pedido: #{{ order.nroped }}</b>
                            <b v-if="view == 'create'">Agregar Seguimiento: #{{ order.nroped }}</b>
                            <div class="float-right">
                                <button v-if="view == 'list'" v-on:click="toggleView('create')" v-bind:disabled="loading_button" type="button" class="btn btn-primary" style="width:100px !important;">Agregar Seguimiento</button>
                                <button v-if="view == 'create'" v-on:click="toggleView('list')" v-bind:disabled="loading_button" type="button" class="btn btn-primary" style="width:100px !important;">Regresar</button>
                            </div>
                        </h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div id="accordion" v-if="view == 'list'">
                            <div class="alert alert-warning" v-if="loading">Cargando..</div>
                            <div class="alert alert-warning" v-if="quantity == 0 && !loading">No se encontró contenido para mostrar.</div>

                            <div class="card" v-for="(t, k) in tracings" v-if="quantity > 0 && !loading">
                                <div class="card-header" v-bind:id="'heading_' + k">
                                    <h5 class="mb-0">
                                        <button class="btn btn-md" data-toggle="collapse" v-bind:data-target="'#collapse_' + k" aria-expanded="true">
                                            Seguimiento # {{ (k + 1) }}
                                        </button>
                                    </h5>
                                </div>

                                <div v-bind:id="'collapse_' + k" v-bind:class="['collapse', (k == 0) ? 'show' : '']" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <p class="col-md-6">
                                                Medio: <strong>{{ t.DESCRI_MEDIO.trim() }}</strong><br />
                                                Cliente: <strong>{{ order.razon.trim() }} {{ order.codigo.trim() }} - ({{ order.remite.trim() }})</strong>
                                            </p>
                                            <p class="col-md-6">
                                                Fecha de Registro: <strong>{{ t.FECREG }} {{ t.HORREG }}</strong><br />
                                                Asunto: <strong>{{ order.asunto.trim() }}</strong>
                                            </p>
                                            <p class="col-md-12">Estado del Pedido hasta la fecha: <strong>{{ t.DESCRI_ESTADO.trim() }}</strong></p>
                                        </div>
                                        <hr />
                                        <div class="content __tracing-mail-content" v-html="t.ACCION"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="view == 'create'">
                            <form class="form" id="form">
                                <div class="mb-3 row">
                                    <div class="form-group col-md-6">
                                        <label for="ejecutivoLito">Ejecutivo Lito</label>
                                        <input type="text" class="form-control" id="ejecutivoLito" placeholder="Ejecutivo Lito" v-model="executive_tracing" readonly="readonly" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="cliente">Cliente</label>
                                        <input type="text" class="form-control" id="cliente" v-model="client_tracing" placeholder="Cliente" readonly="readonly" />
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <div class="form-group col-md-6">
                                        <label for="ejecutivoCliente">Ejecutivo Cliente</label>
                                        <input type="text" class="form-control" id="ejecutivoCliente" v-model="executive_client_tracing" placeholder="Ejecutivo Cliente" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="correoCliente">Correo Electrónico Cliente</label>
                                        <input type="text" class="form-control" id="correoCliente" placeholder="Correo Electrónico" v-model="email_tracing" />
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <div class="form-group col-md-4">
                                        <label for="fecha">Fecha para re-abrir el Seguimiento</label>
                                        <date-picker class="date mr-2" id="fecha"
                                                     v-model="date_tracing"
                                                     :config="options">
                                        </date-picker>
                                        <p class="help-block">Servirá para agendar un recordatorio.</p>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="medio">Medio</label>
                                        <select class="form-control" id="medio" v-model="medio_tracing" required="required">
                                            <option value="" disabled="disabled" selected="selected">Seleccione</option>
                                            <option v-for="(_me, m) in media" v-bind:value="_me.CODIGO">{{ _me.NOMBRE }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="estado">Estado</label>
                                        <select class="form-control" id="estado" v-model="state_tracing" required="required">
                                            <option value="" disabled="disabled" selected="selected">Seleccione</option>
                                            <option v-for="(state, s) in status" v-bind:value="state.CODIGO">{{ state.NOMBRE }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <div class="form-group col-md-12">
                                        <label for="acciones">Acciones</label>
                                        <textarea id="acciones" v-model="actions_tracing" class="form-control"></textarea>
                                    </div>
                                </div>

                                <button type="button" v-on:click="saveTracing()" class="btn btn-primary"> Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style type="text/css">
    .__tracing-mail-content img { position: relative !important; width: auto !important; }
    .aQH {
        padding-top: 16px;
        margin-left: -16px;
        display: block;
        clear: both;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .aZo {
        display: block;
        float: left;
        margin: 0 0 16px 16px;
        height: 120px;
        width: 180px;
        position: relative;
    }
    .aZn {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 0;
        border-top: 1px solid #e5e5e5;
    }
    .aSH {
        position: absolute;
        top: 1px;
        right: 1px;
        bottom: 1px;
        left: 1px;
        overflow: hidden;
        z-index: 1;
        background-color: #ccc;
    }
    .aSH > img {
        display:none !important;
    }
    .aQy {
        background-color: #fff;
        display: inline-block;
        height: 120px;
        width: 180px;
        overflow: hidden;
        position: relative;
        z-index: 0;
        text-decoration: none;
    }
    .aVY {
        position: absolute;
        overflow: visible;
        top: 0;
        left: 0;
        right: 0;
        height: 0;
        z-index: 2;
    }
    .aSG {
        background: #fff;
        margin: auto;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }
    .aSh {
        position: absolute;
        top: 1px;
        right: 1px;
        bottom: 1px;
        left: 1px;
        overflow: hidden;
        z-index: 1;
    }
    .aYy {
        height: 120px;
        position: absolute;
        top: 85px;
        right: 0;
        left: 0;
        background-color: #f5f5f5;
        border-top: 1px solid #e5e5e5;
    }
    .aZp .aYy {
        top: 0;
        border-top: none;
    }
    .aYA {
        float: left;
        height: 34px;
        width: 35px;
        text-align: center;
        vertical-align: middle;
        line-height: 40px;
    }
    .aYz {
        overflow: hidden;
    }
    .aQA {
        font-size: 12px;
        color: #777;
        font-weight: bold;
        line-height: 1.2em;
        margin-top: 9px;
        margin-right: 30px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        word-wrap: normal;
    }
    .aYp {
        font-size: 11px;
        color: #aaa;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        word-wrap: normal;
        display: none;
    }
    .aZp .aYp {
        display: block;
        margin-top: 4px;
    }
    .aSI {
        position: absolute;
        bottom: 0;
        right: 0;
        z-index: 3;
        height: 20px;
        width: 20px;
        border-right: solid 20px transparent;
    }
    .aQw {
        position: absolute;
        left: 35px;
        opacity: .01;
        bottom: 15px;
    }
    .aZp .aQw {
        opacity: 1;
    }
    .aZo .aQw .aQv {
        background: rgba(0,0,0,0.6);
        -moz-border-radius: 3px;
        border-radius: 3px;
        float: left;
        margin-right: 8px;
        min-width: 0;
        width: 30px;
        height: 24px;
        padding: 0;
        line-height: 23px;
        cursor: pointer;
    }
    .aZK {
        height: 0;
        overflow: hidden;
        clear: both;
    }
</style>

<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                order: [],
                tracings: [],
                quantity: 0,
                loading_button: false,
                loading: false,
                view: 'list',
                status: [],
                quantityStatus: 0,
                media: [],
                quantityMedia: 0,
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                },
                executive_tracing: '',
                client_tracing: '',
                email_tracing: '',
                executive_client_tracing: '',
                date_tracing: '',
                medio_tracing: '',
                state_tracing: '',
                actions_tracing: ''
            }
        },
        created: function () {

        },
        methods: {
            load: function () {
                this.order = this.data.order
                this.searchTracings()
                this.searchStatus()
                this.searchMedia()

                this.executive_tracing = (this.order.codusu != '' && this.order.codusu != null) ? this.order.codusu.trim() : ''
                this.client_tracing = (this.order.codigo != '' && this.order.codigo != null) ? this.order.codigo.trim() : ''
                this.email_tracing = (this.order.remite != '' && this.order.remite != null) ? this.order.remite.trim() : ''
            },
            toggleView: function (_view) {
                this.view = _view
            },
            searchStatus: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/search_status_tracing', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        this.status = result.data.status
                        this.quantityStatus = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchMedia: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/search_media_tracing', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        this.media = result.data.media
                        this.quantityMedia = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            saveTracing: function () {

                if(this.email_tracing == '')
                {
                    this.$toast.error('Ingrese un correo electrónico del cliente para guardar el seguimiento.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.medio_tracing == '')
                {
                    this.$toast.error('Seleccione un medio para guardar el seguimiento.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.state_tracing == '')
                {
                    this.$toast.error('Seleccione un estado para guardar el seguimiento.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.actions_tracing == '')
                {
                    this.$toast.error('Ingrese un comentario/acciones para guardar el seguimiento.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading_button = true

                axios.post(
                    baseURL + 'orders/save_tracing', {
                        lang: this.lang,
                        order: this.order,
                        nroped: this.order.nroped.trim(),
                        asunto: this.order.nompax.trim(),
                        correo_desde: this.order.codusu.trim(),
                        correo: this.email_tracing,
                        fecha: this.date_tracing,
                        medio: this.medio_tracing,
                        estado: this.state_tracing,
                        acciones: this.actions_tracing
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        if(result.data.response == 'success')
                        {
                            this.executive_tracing = ''
                            this.client_tracing = ''
                            this.email_tracing = ''
                            this.date_tracing = ''
                            this.medio_tracing = ''
                            this.state_tracing = ''

                            this.view = 'list'
                            this.searchTracings()
                        }
                        else
                        {
                            this.$toast.error('Ocurrió un error al guardar el seguimiento.', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            loadTracing: function(id, data) {
                data = JSON.parse(data)
                this.order = data.order
                this.searchTracings()
            },
            searchTracings: function () {
                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/search_tracings', {
                        lang: this.lang,
                        nroped: this.order.nroped
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.loading_button = false

                        this.tracings = result.data.tracings
                        this.quantity = result.data.quantity
                        let vm = this

                        $.each(vm.tracings, function(i, item) {
                            vm.loading = true
                            vm.loading_button = true

                            axios.post(baseURL + 'orders/find_file_tracing', {
                                    lang: vm.lang,
                                    nroped: item.NROPED,
                                    fecreg: item.FECREG,
                                    horreg: item.HORREG
                                }
                            )
                                .then((result) => {

                                    axios.post(
                                        baseURL + 'orders/find_content_tracing', {
                                            lang: vm.lang,
                                            nroped: item.NROPED,
                                            file: result.data.content
                                        }
                                    )
                                        .then( function( _result ) {
                                            vm.loading = false
                                            vm.loading_button = false

                                            let r = _result.data.content

                                            r = r.split("↵").join("")
                                            r = r.split("<br>").join("")

                                            vm.tracings[i].ACCION = r
                                        })
                                        .catch((e) => {
                                            console.log(e)
                                        })
                                })
                                .catch((e) => {
                                    console.log(e)
                                })
                        })
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
        }
    }
</script>
