<template>
    <div class="modal modal--cotizacion" id="email-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal--cotizacion__document" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title text-center">
                            <b v-if="view == 'list'">PEDIDO: #{{ order.nroped }}</b>
                        </h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div id="accordion" v-if="view == 'list'">
                            <div class="alert alert-warning" v-if="loading">Cargando..</div>
                            <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                                No se encontró contenido para mostrar.
                            </div>

                            <div class="card" v-for="(t, k) in tracings" v-bind:key="k">
                                <template v-if="quantity > 0 && !loading && (t.ACCION != '' && t.ACCION != null)">
                                    <div class="card-header" v-bind:id="'heading_' + k">
                                        <h5 class="mb-0">
                                            <button class="btn btn-md" data-toggle="collapse"
                                                    v-bind:data-target="'#collapse_' + k" aria-expanded="true">
                                                CORREO # {{ (k + 1) }}
                                            </button>
                                        </h5>
                                    </div>

                                    <div v-bind:id="'collapse_' + k"
                                         v-bind:class="['collapse', (k == 0) ? 'show' : '']"
                                         data-parent="#accordion">
                                        <div class="card-body" style="overflow-y:auto;">
                                            <div class="content __tracing-mail-content" v-html="clearContent(t.ACCION)"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
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

    .__tracing-mail-content table,
    .__tracing-mail-content thead,
    .__tracing-mail-content tbody,
    .__tracing-mail-content tr,
    .__tracing-mail-content td,
    .__tracing-mail-content th {
        border: 0!important;
    }

    .__tracing-mail-content p {
        color: #000!important;
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
            clearContent: function (htmlString) {
                // Crear un contenedor temporal para manipular el HTML
                let tempContainer = document.createElement('div');
                tempContainer.innerHTML = htmlString;

                // Recorrer todos los divs y eliminar atributos de estilo
                tempContainer.querySelectorAll('*').forEach(div => {
                    div.removeAttribute('style'); // Eliminar estilos en línea
                    div.removeAttribute('class'); // Eliminar clases
                    div.removeAttribute('dir'); // Eliminar direcciones
                    div.removeAttribute('data-ogsc'); // Eliminar atributos personalizados
                });

                tempContainer.querySelectorAll('div').forEach(div => {
                    div.style.display = 'block';
                });

                /*
                tempContainer = tempContainer.replace(
                    /<img(.*?)src="(.*?)"(.*?)data-url="(.*?)"(.*?)>/g,
                    `<a href="$2" target="_blank" class="d-block btn btn-info my-3">Ver Imagen</a>`
                );
                */

                // Retornar el HTML limpio
                return tempContainer.innerHTML;
            },
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
            loadTracing: function(id, data) {
                data = JSON.parse(data)
                this.order = data.order
                this.searchTracings()
            },
            searchTracings: function () {
                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/search_emails', {
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

                            console.log(item)

                            axios.post(
                                baseURL + 'orders/find_content_tracing', {
                                    lang: vm.lang,
                                    nroped: item.NROEQU,
                                    file: item.ARCHIV
                                }
                            )
                                .then( function( _result ) {
                                    vm.loading = false
                                    vm.loading_button = false

                                    let r = _result.data.content

                                    if(r !== '' && r !== null)
                                    {
                                        r = r.split("↵").join("");
                                        r = r.split("<br>").join("");
                                        vm.tracings[i].ACCION = r;
                                    }
                                    else
                                    {
                                        vm.tracings[i].ACCION = '';
                                    }
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
