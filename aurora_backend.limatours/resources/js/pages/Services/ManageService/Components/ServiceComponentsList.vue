<template>
    <div class="container-fluid">

        <div class="form-row" v-show="view_active==='substitutes'">
            <div class="col-2">
                <button @click="back()" class="btn btn-success" type="button">
                    < Atrás
                </button>
            </div>
            <div class="col-10">
                <b>REEMPLAZOS para: </b>
                {{ component_selected.id }} - {{component_selected.service.aurora_code}} -
                {{component_selected.service.equivalence_aurora}}
                <span style="background-color: #FFF0A2;color: #5F2902;">[{{ component_selected.service.service_type.translations[0].value }}]</span>
                - {{component_selected.service.name}}
            </div>
        </div>

        <div class="form-row" v-if="!view_accommodate_days">
            <label class="col-sm-2 col-form-label">Buscar Servicio:
            </label>
            <div class="col-8">
                <form @submit.prevent="validateBeforeSubmit">
                    <div id="input-group-1" role="group" class="form-group">
                        <div class="col-sm-12 p-0">
                            <v-select :options="components"
                                      :value="form.component_id"
                                      label="name" :filterable="false" @search="onSearch"
                                      :placeholder="$t('servicesmanageservicecomponents.filter')"
                                      v-validate="'required'"
                                      v-model="componentSelected" name="component" id="component" style="height: 35px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>
                                        {{ option.name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>
                                        {{ option.name }}
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('component')"/>
                                <span v-show="errors.has('component')">{{ errors.first('component') }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-2" style="text-align: right;">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'plus']"/>
                    {{ $t('services.add') }}
                </button>
            </div>

        </div>

        <div class="form-row"  v-show="view_active==='components'" v-if="max_nights>1">
            <button @click="view_accommodate_days=!view_accommodate_days" class="btn btn-success right btn-accomodate" type="button" v-if="!loading">
                <i :class="{'fa':true, 'fa-calendar-alt' : !view_accommodate_days, 'fa-times':view_accommodate_days}"></i>
                Acomodar días
            </button>
        </div>

        <div v-show="view_active==='components'">
            <table-server :columns="table.columns" :options="tableOptions" :url="urlcomponents" class="text-center"
                          ref="table">
                <div class="table-equivalence_aurora" slot="equivalence_aurora" slot-scope="props"
                     style="font-size: 0.9em">
                    {{props.row.service.aurora_code}} - {{props.row.service.equivalence_aurora}}
                </div>
                <div class="table-equivalence_stela" slot="equivalence_stela" slot-scope="props"
                     style="font-size: 0.9em">
                    {{props.row.service.equivalence_stela}}
                </div>

                <div class="table-component_name" slot="component_name" slot-scope="props" style="font-size: 0.9em">

                    <span v-if="!view_accommodate_days">
                        <span style="background-color: #FFF0A2;color: #5F2902;">[{{ props.row.service.service_type.translations[0].value }}]</span>
                    - {{props.row.service.name}}

                        <span class="blocks-mini" v-if="max_nights>1" @click="view_accommodate_days=true">
                            <span :class="'block-day-mini day-choose-' + d"
                                  v-for="(d, i) in props.row.days_active">
                                <span class="label-day">{{ i + 1 }}</span>
                            </span>
                        </span>

                    </span>


                    <span v-if="view_accommodate_days">
                        <span :class="'block-day day-choose-' + d"
                              v-for="(d, i) in props.row.days_active" @click="update_after_days(props.row, i)">
                            <span class="label-day">Día {{ i + 1 }}</span>
                        </span>
                    </span>

                </div>

                <div class="table-actions" slot="lock" slot-scope="props" style="margin: 10px">
                    <i class="fa cursor-pointer"
                       :class="{'fa-lock-open':!(props.row.lock), 'fa-lock locked':props.row.lock}"
                       title="Mientras esté abierto el cliente podrá removerlo en caso no requiera este servicio"
                       @click="lock(props.row)"></i>
                </div>

                <div class="table-actions" slot="substitutes" slot-scope="props" style="margin: 10px">
                    <button
                            @click="view_substitutes(props.row)"
                            class="btn btn-info"
                            type="button">
                        <font-awesome-icon :icon="['fas', 'plus']"/>
                        {{ props.row.substitutes_count }}
                    </button>
                </div>

                <div class="table-actions" slot="actions" slot-scope="props" style="margin: 10px">
                    <button
                            @click="copy(props.row)"
                            class="btn btn-info mr-2"
                            type="button"
                            v-if="$can('update', 'servicecomponents') && max_nights>1">
                        <font-awesome-icon :icon="['fas', 'copy']"/>
                    </button>
                    <button
                            @click="showModal(props.row.id, $t('servicesmanageservicecomponents.component') + ' ID: ' + props.row.id)"
                            class="btn btn-danger"
                            type="button"
                            v-if="$can('delete', 'servicecomponents')">
                        <font-awesome-icon :icon="['fas', 'trash']"/>
                    </button>
                </div>

            </table-server>
        </div>

        <div v-show="view_active==='substitutes'">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Código - Equiv.</th>
                    <th>Reemplazo</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="substitute in substitutes">
                    <td>{{ substitute.id }}</td>
                    <td>{{ substitute.service.aurora_code }} - {{ substitute.service.equivalence_aurora }}</td>
                    <td>{{ substitute.service.name }}</td>
                    <td>
                        <button
                                @click="showModal(substitute.id, 'Servicio Reemplazo ID: ' + substitute.id)"
                                class="btn btn-danger"
                                type="button"
                                v-if="$can('delete', 'servicecomponents')">
                            <font-awesome-icon :icon="['fas', 'trash']"/>
                        </button>
                    </td>
                </tr>
                <tr v-if="substitutes.length===0 && !loading">
                    <td colspan="4" class="text-center">Ningún reemplazo agregado</td>
                </tr>
                </tbody>
            </table>
        </div>

        <b-modal :title="componentName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>
</template>
<script>
    import TableServer from '../../../../components/TableServer'
    import {API} from '../../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'

    export default {
        components: {
            'table-server': TableServer,
            BFormCheckbox,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal,
            vSelect
        },
        data: () => {
            return {
                loading: false,
                componentName: '',
                component_id: '',
                urlcomponents: '',
                components: [],
                componentSelected: [],
                table: {
                    columns: ['id', 'equivalence_aurora', 'component_name', 'lock', 'substitutes', 'actions']
                },
                form: {
                    service_id: null,
                    component_id: null
                },
                view_active: "components", // substitutes
                substitutes: [],
                component_selected: {
                    service: {
                        name: "",
                        aurora_code: "",
                        equivalence_aurora: "",
                        service_type: {
                            translations: [
                                {
                                    value: ""
                                }
                            ]
                        }
                    }
                },
                max_nights: 0,
                max_nights_array: [],
                view_accommodate_days: false,
            }
        },
        mounted() {
            this.$i18n.locale = localStorage.getItem('lang')
            this.get_max_nights()
        },
        created() {
            this.form.service_id = this.$route.params.service_id
            this.urlcomponents = '/api/service/' + this.$route.params.service_id + '/components?token=' +
                window.localStorage.getItem('access_token') + '&page=1'
            this.$parent.$parent.$on('langChange', (payload) => {
                this.onUpdate()
            })
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        equivalence_aurora: "Código - Equiv.",
                        component_name: "Servicio",
                        lock: "Bloquear",
                        substitutes: "Reemplazos",
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id'],
                    responseAdapter(data) {
                        console.log(data)
                        data.data.forEach( (c)=>{
                            c.days_active = []
                            let total_duration_days = Math.ceil( c.service.duration / 24 )
                            let total_actives = 0
                            for( let k=0; k<data.max_nights; k++){
                                let active = 0
                                if( k>=c.after_days && total_actives<total_duration_days ){
                                    active = 1
                                    total_actives++
                                }
                                c.days_active.push(active)
                            }

                            console.log(c)
                        } )

                        return data;
                    }
                }
            }
        },
        methods: {
            copy(component){
                this.loading = true
                API.post('/service/'+this.$route.params.service_id+'/component/' + component.id + '/copy')
                    .then((result) => {
                        if( result.data.success ){
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'warning',
                                title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                                text: "El mismo servicio llegó a su máximo de ingresos permitidos: " + this.max_nights
                            })
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.system')
                    })
                })
            },
            update_after_days(component, i){
                this.loading = true

                component.after_days = i

                this.update_component( component, 1 )
            },
            get_max_nights(){
                API.get('/service/'+this.$route.params.service_id+'/components/max_nights')
                    .then((result) => {
                        this.max_nights = result.data.data
                        console.log(this.max_nights)
                        this.max_nights_array = []
                        for( let i=0; i<this.max_nights; i++ ){
                            this.max_nights_array.push(i+1)
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.system')
                    })
                })
            },
            lock(component) {

                this.loading = true

                component.lock = !component.lock

                this.update_component( component, 0 )
            },
            update_component(component, refresh){
                API.put('/service/component/' + component.id, { data : component })
                    .then((result) => {
                        this.loading = false
                        if( refresh ){
                            this.onUpdate()
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.system')
                    })
                })
            },
            back() {
                this.view_active = 'components'
                this.onUpdate()
            },
            store_substitute() {
                this.loading = true

                let data = {
                    service_id: this.componentSelected.id
                }

                API.post('/services/components/' + this.component_selected.id + '/substitutes', data)
                    .then((result) => {
                        this.loading = false
                        if (result.data.success) {
                            this.search_substitutes(this.component_selected.id)
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                                text: this.$t('servicesmanageservicecomponents.error.messages.already_added')
                            })
                        }
                    }).catch(() => {
                        this.loading = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                            text: this.$t('servicesmanageservicecomponents.error.messages.system')
                        })
                })
            },
            search_substitutes(component_id) {
                this.loading = true

                API.get('/services/components/' + component_id + '/substitutes')
                    .then((result) => {
                        this.loading = false
                        this.substitutes = result.data.data
                        this.view_active = "substitutes"
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.system')
                    })
                })
            },
            view_substitutes(component) {
                console.log(component)
                this.component_selected = component
                if (component.substitutes_count > 0) {
                    this.search_substitutes(component.id)
                } else {
                    this.substitutes = []
                    this.view_active = "substitutes"
                }

            },
            onSearch(search, loading) {
                loading(true)
                API.get('/services/selectBox?query=' + search)
                    .then((result) => {
                        loading(false)
                        this.components = result.data.data
                    }).catch(() => {
                    loading(false)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.system')
                    })
                })
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll().then((result) => {
                    if (result) {

                        if (this.view_active === 'components') {
                            this.form.service_id = this.$route.params.service_id
                            this.submit()
                        } else {
                            this.store_substitute()
                        }

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                            text: this.$t('servicesmanageservicecomponents.error.messages.system')
                        })

                        this.loading = false
                    }
                })
            },
            showModal(component_id, component_name) {
                this.component_id = component_id
                this.componentName = component_name
                this.$refs['my-modal'].show()
            },
            hideModal() {
                this.$refs['my-modal'].hide()
            },
            checkboxChecked: function (component_state) {
                if (component_state) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeState: function (component_id, status) {
                API({
                    method: 'put',
                    url: 'service/' + this.form.service_id + '/components/' + component_id + '/status',
                    data: {status: status}
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                                text: this.$t('servicesmanageservicecomponents.error.messages.system')
                            })
                        }
                    })
            },
            onUpdate() {
                this.urlcomponents = '/api/service/' + this.$route.params.service_id + '/components?token=' +
                    window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
            remove: function () {

                let route_
                if (this.view_active === 'components') {
                    route_ = '/service/' + this.$route.params.service_id + '/components/' + this.component_id
                } else { // substitutes
                    route_ = '/services/components/' + this.component_selected.id + '/substitutes/' + this.component_id
                }

                API({
                    method: 'DELETE',
                    url: route_
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            if (this.view_active === 'substitutes') {
                                this.search_substitutes(this.component_selected.id)
                            }
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                                text: this.$t('servicesmanageservicecomponents.error.messages.component_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                        text: this.$t('servicesmanageservicecomponents.error.messages.connection_error')
                    })
                })
            },
            submit: function () {
                this.form.component_id = this.componentSelected.id
                this.loading = true
                console.log(this.errors)
                API({
                    method: 'post',
                    url: 'service/' + this.form.service_id + '/components',
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.componentSelected = []
                            this.errors.items = []
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error: ' + this.$t('servicesmanageservicecomponents.components'),
                                text: this.$t('servicesmanageservicecomponents.error.messages.already_added')
                            })
                        }
                        this.loading = false
                    }).catch(() => {
                    this.loading = false
                })
            }
        }
    }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }

    .v-select input {
        height: 25px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .locked {
        color: #c8c430;
    }
    .btn-accomodate{
        right: 9%;
        position: absolute;
        z-index: 1;
    }
    .block-day{
        float: left;
        width: 40px;
        height: 38px;
        background: #ddffff;
        display: block;
        position: relative;
        padding: 2px;
        border: solid 1px #6c82c2;
        cursor: pointer;
    }
    .block-day:hover{
        opacity: 0.6;
    }
    .block-day .label-day{
        font-size: 8px;
        font-weight: 800;
    }
    .blocks-mini{
        position: relative;
        margin-top: 3px;
        float: right;
        margin-right: 30px;
        cursor: pointer;
    }
    .block-day-mini{
        float: left;
        width: 10px;
        height: 8px;
        background: #ddffff;
        display: block;
        position: relative;
        padding: 1px;
        border: solid 1px #6c82c2;
    }
    .block-day-mini .label-day{
        font-size: 7px;
    }
    .day-choose-1{
        background: radial-gradient(#71ff76, #eaff3994);
    }
</style>


