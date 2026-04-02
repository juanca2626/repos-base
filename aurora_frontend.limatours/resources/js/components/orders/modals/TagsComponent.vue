<template>
    <div class="modal modal--cotizacion" id="tags-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title">
                            <b>ETIQUETAS ACTUALES</b>
                            <div class="float-right">
                                <a class="btn" v-if="flag == 1" v-show="view == 'list'" v-bind:disabled="loading_button" href="javascript:;" v-on:click="toggleView( 'create' )">
                                    <i class="fa fa-plus-circle fa-2x"></i>
                                </a>
                                <a class="btn" v-show="view == 'create'" v-bind:disabled="loading_button" href="javascript:;" v-on:click="save()">
                                    <i class="fa fa-save fa-2x"></i>
                                </a>
                                <a class="btn" v-show="view == 'create'" v-bind:disabled="loading_button" href="javascript:;" v-on:click="toggleView( 'list' )">
                                    <i class="fa fa-arrow-circle-left fa-2x"></i>
                                </a>
                            </div>
                        </h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div v-if="view == 'list'">
                            <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                                <p class="mb-0">No se encontró información para mostrar.</p>
                            </div>

                            <div class="alert alert-warning" v-if="loading">
                                <p class="mb-0">Cargando..</p>
                            </div>

                            <div class="mb-3" v-if="quantity > 0 && !loading" v-for="(label, l) in labels">
                                <a href="javascript:;" v-if="flag == 1 && order.length == 0" class="btn edit iconLeft" v-on:click="_remove( label.ID )">
                                    <i class="cursor-pointer fa fa-minus-circle"></i>
                                </a>

                                <template v-if="(flag == 1 && order.length > 0) || flag == 0">
                                    <span class="p-2"
                                        v-bind:style="{ cursor: 'pointer !important', background: '#' + label.COLBAC + '!important', color: '#' + label.COLTEX + '!important' }"
                                        href="javascript:;" v-on:click="active( label.ID )">
                                        <b>{{ label.NOMBRE }}</b>
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="p-2" v-bind:style="{ cursor: 'default !important', background: '#' + label.COLBAC + '!important', color: '#' + label.COLTEX + '!important' }" href="javascript:;">
                                        <b>{{ label.NOMBRE }}</b>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div v-if="view == 'create'">
                            <div class="form">
                                <input type="hidden" name="identi" id="identi" value="Q" />
                                <div class="mb-3">
                                    <span class="form_label">Título: </span>
                                    <input v-model="title" type="text" placeholder="Titulo de etiqueta (máx 15 car.)" maxlength="15"
                                           class="form-control">
                                </div>
                                <div class="row">
                                    <div class="radios col-md-12">
                                        <div class="float-left">
                                            <span class="form_label">Fondo: </span>
                                            <input v-model="colbac" type="color">
                                        </div>
                                        <div class="float-left" style="margin-left: 50px;">
                                            <span class="form_label">Letra: </span>
                                            <div class="label_radio">
                                                <label><input v-model="coltex" type="radio" value="0"> <i>Blanco</i></label>
                                            </div>
                                            <div class="label_radio">
                                                <label><input v-model="coltex" type="radio" value="1"> <i>Negro</i></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                data: [],
                identi: 'O',
                order: [],
                translations: [],
                flag: 0,
                lang: '',
                loading: false,
                loading_button: false,
                view: 'list',
                labels: [],
                title: '',
                colbac: '',
                coltex: 0,
                quantity: 0
            }
        },
        created: function () {

        },
        mounted: function () {

        },
        computed: {

        },
        methods: {
            load: function () {
                this.lang = localStorage.getItem('lang')
                this.flag = localStorage.getItem('boss')

                this.order = this.data.order
                this.translations = this.data.translations

                this.searchLabels()
            },
            toggleView: function (_view) {
                this.view = _view
            },
            searchLabels: function () {
                this.loading = true
                this.loading_button = true
                this.labels = []

                axios.post(
                    baseURL + 'orders/search_labels', {
                        lang: this.lang,
                        identi: this.identi
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.loading_button = false

                        this.labels = result.data.labels
                        this.quantity = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {

                if(this.title == '')
                {
                    this.$toast.error('Ingrese un título', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.colbac == '')
                {
                    this.$toast.error('Ingrese un color de fondo', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading_button = true

                axios.post(
                    baseURL + 'orders/save_label', {
                        lang: this.lang,
                        titulo : this.title,
                        fondo : this.colbac,
                        letra : this.coltex,
                        identi : this.identi
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        this.searchLabels()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            active: function (id) {
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/active_label', {
                        lang: this.lang,
                        identi: this.identi,
                        code: id,
                        codped: this.order.nroped
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            _remove: function (id) {
                this.loading_button = true

                axios.post(
                    baseURL + 'orders/remove_label', {
                        lang: this.lang,
                        identi: this.identi,
                        codigo: id
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        this.searchLabels()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    }
</script>
