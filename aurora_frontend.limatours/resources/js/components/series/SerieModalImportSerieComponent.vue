<template>
    <div class="modal fade modal-general modal-editar-dia" id="modal-import-serie">
        <div :class="'modal-dialog ' + modal_size" role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" @click="close_modal()">{{ translations.label.close }} X</button>

                <div class="modal-body" v-show="view_content===1">
                    <div class="d-flex justify-content-between">
                        <div class="modal-title">{{ translations.label.import }} Grupos & Series</div>
                        <div class="input-filtrar-modal">
                            <input type="text" :placeholder="translations.label.filter" :disabled="loading" id="query_search_s"
                                   v-model="query_search">
                            <i class="icon-serie-drag"></i>
                            <div class="icono icon-search"></div>
                        </div>
                    </div>
                    <hr>
                    <br>
                    <div class="container" style="overflow-y: scroll; max-height:85%;">
                        <div class="table-series">
                            <div class="t-header text-center">
                                <div class="selector"> SELEC.</div>
                                <div class="codigo"> CODIGO <span class="icon-arrow-down"></span></div>
                                <div class="descripcion">DESCRIPCIÓN</div>
                                <div class="cliente">CLIENTE</div>
                                <div class="salida">INICIA</div>
                                <div class="icon"><span class="icon-calendar-confirm"></span></div>
                                <div class="icon"><span class="icon-tag"></span></div>
                                <div class="icon"><span class="icon-maximize-2"></span></div>
                                <div class="icon"><span class="icon-user-plus"></span></div>
                                <div class="icon"><span class="icon-shield1"></span></div>
                                <div class="icon"><span class="icon-layers1"></span></div>
                            </div>
                            <div class="t-body">
                                <div class="celda" v-for="s in series" :class="{'c-active':radio_s_selected==s.id}"
                                     @click="set_radio_s_selected(s.id)" v-if="s.id !== serie_id">
                                    <div class="selector">
                                        <input type="radio" name="radio_s_selected" v-model="radio_s_selected" :value="s.id">
                                    </div>
                                    <div class="codigo">GS-{{ s.id }}</div>
                                    <div class="descripcion">{{ s.name }}</div>
                                    <div class="cliente">{{ s.user.name }}</div>
                                    <div class="salida">{{ s.date_start }}</div>
                                    <div class="icon">0</div>
                                    <div class="icon">0</div>
                                    <div class="icon">0</div>
                                    <div class="icon">P/P</div>
                                    <div class="icon">0 - 0 - 0</div>
                                    <div class="icon">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3" v-if="serie_categories.length>0">
                        <div class="alert alert-warning warning-modal" role="alert">
                            <p class="message-alert d-flex">Recuerda que el proceso de importar no elimina los servicios actuales. Si necesitas reemplazar el itinerario actual debes
                                <a class="link-remove ml-1"  href="javascript:;" @click="toggleModal( 0, 'remover-content', { translations: translations, serie_id : serie_id } )"
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
                    <button class="button-cancelar button-actualizar" type="button" @click="set_serie()"
                            :disabled="loading || radio_s_selected=='' || serie_categories.length>0">
                        {{ translations.label.import }}
                    </button>
                </div>

                <div class="modal-body" v-show="view_content===2" >
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
                                            <input class="form-check__input form-check-input" type="checkbox"  v-model="form.include_notes">
                                            <span class="form-check__text">Notas</span>
                                        </label>
                                    </div>
                                    <div class="select-check ml-5">
                                        <label class="form-check">
                                            <input class="form-check__input form-check-input" type="checkbox"  v-model="form.include_messages">
                                            <span class="form-check__text">Mensajes</span>
                                        </label>
                                    </div>
                                    <div class="select-check ml-5">
                                        <label class="form-check">
                                            <input class="form-check__input form-check-input" type="checkbox"  v-model="form.include_reminders">
                                            <span class="form-check__text">Recordatorios</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="options-select content-top__form form-group mt-3">
                                    <div class="select-txt categorias">Categorias:</div>

                                    <div class="container" v-if="type_classes.length===0" style="margin-top: 11px">
                                        <div class="alert alert-warning warning-modal" role="alert">
                                            <h5 class="title-warning">La Serie elegida no tiene ninguna categoría para copiar</h5>
                                        </div>
                                    </div>

                                    <div class="container">
                                        <div class="select-check row ml-10">
                                            <label class="form-check col-12" v-for="type_class in type_classes">
                                                <input class="form-check__input form-check-input" type="checkbox" v-model="type_class.check">
                                                <span class="form-check__text">{{ type_class.type_class.translations[0].value }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="mt-3">
                        <div class="alert alert-warning warning-modal" role="alert">
                            <h5 class="title-warning">Recuerda que al importar de otra cotización no se incluirá la siguiente info:  </h5>
                            <ul>
                                <li>Files asociados.</li>
                                <li>Pedidos, confirmaciones o respuestas de proveedores.</li>
                                <li>Cotización final (cálculo de precios y resumen).</li>
                                <li>Historial de cambios.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" v-show="view_content===2">
                    <button class="button-cancelar button-cancelar-info" type="button" @click="toggle_content(1)" :disabled="loading">
                        <span style="margin-left: 10px;">{{ translations.label.cancel }}</span>
                    </button>
                    <button class="button-cancelar button-actualizar" type="button" @click="import_serie()" :disabled="loading">
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
                modal: '',
                dataModal: {},
                modal_size: '',
                query_search: '',
                page_chosen: 1,
                limit: 50,
                radio_s_selected: '',
                s_selected: '',
                action_after: '',
                series: [],
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

            let query_search = document.getElementById('query_search_s')
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
            set_serie(){
                this.series.forEach( s=>{
                    if( s.id === this.radio_s_selected ){
                        this.s_selected = s
                    }
                } )

                this.form.name = this.s_selected.name
                let m = moment()
                this.form.comment = "Importado de Cotización GS-" + this.s_selected.id + " el " +
                    moment().format('DD-MM-YYYY') + " - " + m.hours() + ':' + m.minutes() + ' hrs.'

                this.search_type_classes()
                this.toggle_content(2)

            },
            search_type_classes() {

                this.type_classes = []
                axios.get(
                    baseExternalURL + 'api/series/'+this.s_selected.id+'/categories'
                )
                    .then((result) => {
                        if( result.data.success ){
                            result.data.data.forEach(_c => {
                                _c.check = false;
                                this.type_classes.push(_c);
                            })
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            set_radio_s_selected(id){
                this.radio_s_selected = id
            },
            search () {
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/series?query='+this.query_search
                    +'&page='+this.page_chosen+'&limit='+this.limit
                )
                    .then((result) => {
                        this.loading = false
                        this.series = result.data.data
                        let s_n = 0
                        this.series.forEach( s=>{
                            if( this.radio_s_selected === s.id ){
                                s_n++
                            }
                        })
                        if( s_n === 0 ){
                            this.radio_s_selected = ''
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            import_serie(){

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
                    include_reminders : ( this.form.include_reminders ) ? 1 : 0,
                    include_notes : ( this.form.include_notes ) ? 1 : 0,
                    categories : _categories
                }

                let _url
                if( this.serie_id === null ){
                    _url = baseExternalURL + 'api/series/clone/'+this.s_selected.id
                } else {
                    _url = baseExternalURL + 'api/series/'+this.serie_id+'/clone/'+this.s_selected.id
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
