<template>
    <div class="modal fade modal-general modal-editar-dia" id="modal-import-master-sheet" >
        <div :class="'modal-dialog ' + modal_size" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>
                <div class="modal-body" v-show="view_content===1">
                    <div class="d-flex justify-content-between">
                        <div class="modal-title">{{ translations.label.import }} {{ translations.label.title }}</div>
                        <div class="input-filtrar-modal">
                            <input type="text" :placeholder="translations.label.filter" :disabled="loading" id="query_search_hms">
                            <i class="icon-serie-drag"></i>
                            <div class="icono icon-search"></div>
                        </div>
                    </div>
                    <hr>
                    <br>
                    <div class="container" style="overflow-y: scroll; max-height:85%;">
                        <div class="table-series">
                            <div class="t-header">
                                <div class="selector">SELEC.</div>
                                <div class="codigo">CODIGO <span class="icon-arrow-down"></span></div>
                                <div class="descripcion">DESCRIPCIÓN</div>
                                <div class="cliente">CLIENTE</div>
                                <div class="salida">SALIDA</div>
                                <div class="paxes">PAXES</div>
                                <div class="leaders">LEADERS</div>
                            </div>
                            <div class="t-body">
                                <div v-for="ms in master_sheets" class="celda" :class="{'c-active':radio_ms_selected==ms.id}" @click="set_radio_ms_selected(ms.id)">
                                    <div class="selector" >
                                        <input type="radio" name="radio_ms_selected" v-model="radio_ms_selected" :value="ms.id">
                                    </div>
                                    <div class="codigo">HM-{{ ms.id }}</div>
                                    <div class="descripcion">{{ ms.name }}</div>
                                    <div class="cliente">{{ ms.user.name }}</div>
                                    <div class="salida">{{ ms.date_out }}</div>
                                    <div class="paxes">{{ ms.paxes }}</div>
                                    <div class="leaders">{{ ms.leader }}</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-3" v-if="serie_categories.length>0">
                        <div class="alert alert-warning warning-modal" role="alert">
                            <p class="message-alert d-flex">Recuerda que el proceso de importar no elimina los servicios actuales. Si necesitas reemplazar el itinerario actual debes
                                <a class="link-remove ml-1" href="javascript:;" @click="toggleModal( 0, 'remover-content', { translations: translations, serie_id : serie_id } )"
                                   data-toggle="modal" data-target="#modal-borrar-contenido">eliminar primero todos los servicios.</a>
                            </p>
                        </div>
                    </div>
<!--                    <div class="modal-card-editar-content">-->
<!--                    </div>-->
                </div>
                <div class="modal-footer" v-show="view_content===1">
                    <button class="button-cancelar button-cancelar-info" :disabled="loading" @click="close_modal()" type="button">
                        <span style="margin-left: 10px;">{{ translations.label.cancel }}</span>
                    </button>
                    <button class="button-cancelar button-actualizar" type="button" @click="set_master_sheet()"
                            :disabled="loading || radio_ms_selected=='' || serie_categories.length>0">
                        {{ translations.label.import }}
                    </button>
                </div>

                <div class="modal-body" v-show="view_content===2">
                    <div class="modal-title d-flex align-items-center">Opciones de Importación: <div style="font-weight: 300;">{{ translations.label.title }}</div></div>
                    <p>Selecciona las opciones que deseas incluir y las categorías que trabajarás en esta nueva cotización</p>
                    <br>
                    <div class="modal-card-editar-content">
                        <div class="container">
                            <div class="row">
                                <div class="input-icono">
                                    <input type="text" :placeholder="translations.label.file_name+'...'" v-model="form.name">
                                    <div class="icono icon-clipboard"></div>
                                </div>
                                <br>
                            </div>
                            <div class="row">
                                <div class="mensaje-card-textarea">
                                    <textarea class="text-modal" rows="5" style="font-size: 14px; font-style: italic; color: #bebebe;"
                                              disabled>{{ form.comment }}</textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="options-select content-top__form form-group d-flex justify-content-start mt-3">
                                    <div class="select-txt incluir mr-5">Incluir:</div>
                                    <div class="select-check">
                                        <label class="form-check">
                                            <input class="form-check__input form-check-input" v-model="form.include_messages" type="checkbox">
                                            <span class="form-check__text">Mensajes</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="options-select content-top__form form-group mt-3">
                                    <div class="select-txt categorias">Categorias:</div>
                                    <div class="container">
                                        <div class="select-check row ml-10">
                                            <label class="form-check col-6" v-for="type_class in type_classes">
                                                <input class="form-check__input form-check-input" type="checkbox" v-model="type_class.check">
                                                <span class="form-check__text">{{ type_class.translations[0].value }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer" v-show="view_content===2">
                    <button class="button-cancelar button-cancelar-info" type="button" @click="toggle_content(1)" :disabled="loading">
                        <span style="margin-left: 10px;">{{ translations.label.cancel }}</span>
                    </button>
                    <button class="button-cancelar button-actualizar" type="button" @click="import_master_sheet()" :disabled="loading">
                        <i class="fa fa-spinner fa-spin" v-if="loading"></i> {{ translations.label.import }}
                    </button>
                </div>

            </div>
        </div>

        <component ref="template" v-bind:is="modal" v-bind:data="dataModal"></component>

    </div>
</template>
<script>
    export default {
        props: ['data'],
        data: () => {
            return {
                lang: '',
                loading: false,
                modal_size: '',
                modal: '',
                dataModal: {},
                query_search: '',
                page_chosen: 1,
                limit: 50,
                radio_ms_selected: '',
                ms_selected: '',
                action_after: '',
                master_sheets: [],
                type_classes: [],
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                baseURL: window.baseURL,
                baseExternalURL: window.baseExternalURL,
                form : {},
                view_content : 1,
                serie_categories : [],
                serie_id : null,
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.translations = this.data.translations

            let query_search = document.getElementById('query_search_hms')
            let timeout_
            query_search.addEventListener('keydown', () => {
                clearTimeout(timeout_)
                timeout_ = setTimeout(() => {
                    this.page_chosen = 1
                    this.search()
                    clearTimeout(timeout_)
                }, 1000);
            })
        },
        computed: {

        },
        methods: {
            load(){
                this.search()
                this.search_type_classes()
                if( this.data.serie_id === undefined ) {
                    this.action_after = this.data.action_after
                } else {
                    this.action_after = ''
                }

                if( this.$parent.serie_categories !== undefined ){
                    this.serie_categories = this.$parent.serie_categories
                }
                if( this.data.serie_id !== undefined ){
                    this.serie_id = this.data.serie_id
                }
            },
            close_modal(){
                this.$parent._closeModal()
            },
            toggleModal(index, _modal, _data) {

                this.index = index
                this.dataModal = _data
                this.modal = 'serie-modal-' + _modal

                let vm = this
                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            set_master_sheet(){
                this.master_sheets.forEach( ms=>{
                    if( ms.id === this.radio_ms_selected ){
                        this.ms_selected = ms
                    }
                } )

                this.form.name = this.ms_selected.name
                let m = moment()
                this.form.comment = "Importado de Hoja Master HM-" + this.ms_selected.id + " el " +
                    moment().format('DD-MM-YYYY') + " - " + m.hours() + ':' + m.minutes() + ' hrs.'

                this.toggle_content(2)

            },
            search_type_classes: function() {
                axios.get(baseExternalURL + 'api/typeclass/selectbox?lang=' + localStorage.getItem('lang')).
                then((result) => {
                    result.data.data.forEach(_c => {
                        if (_c.code != 'X' && _c.code != 'x') {
                            _c.check = false;
                            this.type_classes.push(_c);
                        }
                    });
                }).
                catch((e) => {
                    console.log(e);
                });
            },
            set_radio_ms_selected(id){
                this.radio_ms_selected = id
            },
            search () {
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/master_sheet?query='+this.query_search
                    +'&page='+this.page_chosen+'&limit='+this.limit
                )
                    .then((result) => {
                        this.loading = false
                        this.master_sheets = result.data.data
                        let ms_n = 0
                        this.master_sheets.forEach( ms=>{
                            if( this.radio_ms_selected === ms.id ){
                                ms_n++
                            }
                        })
                        if( ms_n === 0 ){
                            this.radio_ms_selected = ''
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            import_master_sheet(){

                let _categories = []

                this.type_classes.forEach( t_c =>{
                    if( t_c.check ){
                        _categories.push(t_c.id)
                    }
                } )

                if( _categories.length === 0 ){
                    this.$toast.warning("Por favor elija categorías", {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                let data = {
                    name : this.form.name,
                    include_messages : ( this.form.include_messages ) ? 1 : 0,
                    categories : _categories
                }

                let _url
                if( this.serie_id === null ){
                    _url = baseExternalURL + 'api/series/master_sheet/'+this.ms_selected.id
                } else {
                    _url = baseExternalURL + 'api/series/'+this.serie_id+'/master_sheet/'+this.ms_selected.id
                }

                axios.post(_url, data)
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.imported_successfully, {
                                position: 'top-right'
                            })
                            if( this.action_after === 'go_edit' ){
                                window.location = '/serie/' + btoa( result.data.serie_id )
                            } else {
                                this.$parent._closeModal()
                                this.$parent.get_serie_categories()
                            }
                        } else {
                            this.$toast.error(this.translations.messages.internal_error_occurred, {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                }).
                catch((e) => {
                    console.log(e);
                });
            },
            toggle_content(number){
                this.view_content = number
                if( number === 2 ){
                    this.modal_size = 'modal-size-small'
                } else {
                    this.modal_size = ''
                }
            }
        },
        filters:{
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            }
        }
    }
</script>
<style>
    .modal-size-small{
        width: 640px !important;
    }
</style>
