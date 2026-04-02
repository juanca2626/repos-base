<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-6 left">
                <a href="/translations/inclusions/export" target="_blank" class="btn btn-success">Exportar Incluyentes</a>
            </div>
            <div class="col-6" v-if="query_text!=''">
                <div class="col-12 text-right">
                    <span class="mr-3">Convertir a:</span>
                    <label for="" class="mr-3">
                        <input type="radio" name="convert_texts" value="first_upper" v-model="type_convert" v-on:change="refresh_query_text()"> Primera mayúsc.
                    </label>
                    <label for="" class="mr-3">
                        <input type="radio" name="convert_texts" value="min" v-model="type_convert" v-on:change="refresh_query_text()"> minúsculas
                    </label>
                    <br>
                    <b style="color:#4fa2b3" class="mr-5">
                        {{ query_text }} > {{ query_text_to }}
                    </b>
                    <button type="button" class="btn btn-danger" @click="convert_text" :disabled="loading">Efectuar</button>
                </div>
            </div>
        </div>
        <table-server :columns="table.columns" :options="tableOptions" :url="urlInclusions" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                <span v-if="props.row.translations.length > 0">
                    <span v-for="trans in props.row.translations">
                        <label class="label label-primary">{{ trans.language.iso.toUpperCase() }}</label> <span v-html="trans.value"></span>
                        <br>
                    </span>
                </span>
            </div>
            <div class="table-actions" slot="operability" slot-scope="props" style="padding: 5px;display: block;">
                <div>
                    <b-button-group size="sm">
                        <b-button @click="changeOpe(props.row,'monday',props.row.monday)" :disabled="loading"
                                  :class="{'bg-success': props.row.monday,'bg-danger':!props.row.monday}">L
                        </b-button>
                        <b-button @click="changeOpe(props.row,'tuesday',props.row.tuesday)" :disabled="loading"
                                  :class="{'bg-success': props.row.tuesday,'bg-danger':!props.row.tuesday}">M
                        </b-button>
                        <b-button @click="changeOpe(props.row,'wednesday',props.row.wednesday)" :disabled="loading"
                                  :class="{'bg-success': props.row.wednesday,'bg-danger':!props.row.wednesday}">M
                        </b-button>
                        <b-button @click="changeOpe(props.row,'thursday',props.row.thursday)" :disabled="loading"
                                  :class="{'bg-success': props.row.thursday,'bg-danger':!props.row.thursday}">J
                        </b-button>
                        <b-button @click="changeOpe(props.row,'friday',props.row.friday)" :disabled="loading"
                                  :class="{'bg-success': props.row.friday,'bg-danger':!props.row.friday}">V
                        </b-button>
                        <b-button @click="changeOpe(props.row,'saturday',props.row.saturday)" :disabled="loading"
                                  :class="{'bg-success': props.row.saturday,'bg-danger':!props.row.saturday}">S
                        </b-button>
                        <b-button @click="changeOpe(props.row,'sunday',props.row.sunday)" :disabled="loading"
                                  :class="{'bg-success': props.row.sunday,'bg-danger':!props.row.sunday}">D
                        </b-button>
                    </b-button-group>
                </div>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/inclusions/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'inclusions')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.translations)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'inclusions')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="inclusionName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import { API } from './../../api'
    import TableServer from '../../components/TableServer'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
    import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            BFormCheckbox,
            'table-server': TableServer,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal
        },
        data: () => {
            return {
                loading: false,
                inclusionName: '',
                inclusions: [],
                urlInclusions: '/api/inclusions?token=' + window.localStorage.getItem('access_token'),
                table: {
                    columns: ['id', 'name', 'operability', 'actions'],
                },
                type_convert: "first_upper",
                query_text: "",
                query_text_to: "",
                translations_for_save_opt_1: [],
                translations_for_save_opt_2: [],
            }
        },
        computed: {
            menuOptions: function () {
                return [
                    {
                        type: 'edit',
                        link: 'inclusions/edit/',
                        icon: 'dot-circle',
                    }
                ]
            },
            tableOptions: function () {
                let vm = this
                return {
                    headings: {
                        id: 'ID',
                        name: this.$i18n.t('global.name'),
                        operability: 'Operatividad',
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: ['id'],
                    filterable: ['id'],
                    requestFunction: function (data) {
                        return API.get('/inclusions?token=' + window.localStorage.getItem('access_token'), {
                            params: data
                        })
                            .then((result) => {
                                vm.$set(vm, 'query_text', data.query)

                                if( isLowerCase(data.query) ){
                                    vm.type_convert = "first_upper"
                                } else {
                                    vm.type_convert = "min"
                                }

                                vm.refresh_query_text()

                                vm.translations_for_save_opt_1 = []
                                vm.translations_for_save_opt_2 = []

                                if(data.query!=""){
                                    result.data.data.forEach((data_)=>{
                                        data_.translations.forEach((trans_)=>{

                                            let find_data_query = trans_.value.split(data.query)
                                            if(find_data_query.length>1){

                                                let query_length = data.query.length
                                                let find_data_query_2 = trans_.value.split(find_data_query[0])
                                                let word_in_text = find_data_query_2[1].slice(0, query_length)

                                                vm.translations_for_save_opt_1.push(
                                                    {
                                                        id: trans_.id,
                                                        value: trans_.value.replaceAll(
                                                            word_in_text,
                                                 vm.query_text.charAt(0).toUpperCase() + vm.query_text.slice(1)
                                                        )
                                                    }
                                                )
                                                vm.translations_for_save_opt_2.push(
                                                    {
                                                        id: trans_.id,
                                                        value: trans_.value.replaceAll(
                                                            word_in_text,
                                                            vm.query_text.toLowerCase()
                                                        )
                                                    }
                                                )

                                                trans_.value = trans_.value.replaceAll(
                                                    word_in_text,
                                                    '<span class="text-focus">'+ word_in_text + '</span>'
                                                )

                                            }

                                        })
                                    })

                                    console.log(vm.translations_for_save_opt_1)
                                    console.log(vm.translations_for_save_opt_2)

                                }

                                return result.data
                            }).catch(() => {
                                vm.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: "Error",
                                    text: vm.$t('hotels.error.messages.connection_error')
                                })
                            })
                    },
                }
            }
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            convert_text(){
                let translations_for_save_opt = []
                if(this.type_convert == 'first_upper'){
                    translations_for_save_opt = this.translations_for_save_opt_1
                } else {
                    translations_for_save_opt = this.translations_for_save_opt_2
                }
                if(translations_for_save_opt.length>0){
                    this.loading = true
                    API.post('/inclusions/translations', {
                        data_update: translations_for_save_opt,
                    })
                        .then((result) => {
                            this.loading = false

                            if(result.data.success)
                            {
                                this.$notify({
                                    group: 'main',
                                    type: "success",
                                    title: "Efectuado correctamente"
                                })
                                this.onUpdate()
                            } else {

                                this.$notify({
                                    group: 'main',
                                    type: "error",
                                    title: "Error"
                                })
                            }
                        })
                }
            },
            refresh_query_text(){
                console.log(this.type_convert)
                if(this.type_convert === 'first_upper'){
                    this.query_text_to = this.query_text.charAt(0).toUpperCase() + this.query_text.slice(1)
                } else {
                    this.query_text_to =this.query_text.toLowerCase()
                }
            },
            showModal (inclusion_id, inclusion_name) {
                this.inclusion_id = inclusion_id
                this.inclusionName = (inclusion_name.length > 0) ? inclusion_name[0].value : inclusion_id.toString()
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'inclusions/' + this.inclusion_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.hideModal()
                            if (result.data.used === true) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.inclusions'),
                                    text: this.$t('inclusions.error.messages.used')
                                })
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.inclusions'),
                                    text: this.$t('inclusions.error.messages.inclusion_delete')
                                })
                            }
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('inclusions.error.messages.name'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlInclusions = '/api/inclusions?token=' + window.localStorage.getItem('access_token')
                this.$refs.table.$refs.tableserver.refresh()
            },
            changeOpe (row,day,status) {
                this.loading = true
                console.log(day,status)
                API({
                    method: 'PUT',
                    url: 'inclusions/' + row.id + '/operability',
                    data: {
                        'day': day,
                        'status' : (status == true) ? false : true
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.inclusions'),
                                text: this.$t('inclusions.error.messages.information_error')
                            })
                        } else {
                            this.onUpdate()
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('inclusions.error.messages.name'),
                        text: this.$t('inclusions.error.messages.connection_error')
                    })
                })
            }
        }

    }
</script>

<style lang="stylus">
    .label-primary{
        background: #dbd2ff;
        padding: 1px 4px;
        border-radius: 4px;
    }
    .text-focus{
        color: #0b4d75;
        font-weight: 500;
        background: #faffa3;
        text-decoration: underline;
    }
</style>
