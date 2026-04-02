<template>
    <div class="container-fluid">

        <table-server :columns="table.columns" :options="tableOptions" :url="urlPhotos" class="text-center"
                      ref="table">
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/photos/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'photos')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row)" class="m-0 p-0"
                                            v-if="$can('delete', 'photos')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
            <div class="table-service_ubigeo" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{props.row.translations[0].value}}
            </div>

            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>


        </table-server>
        <b-modal :title="destinyName" centered ref="my-modal" size="sm">
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
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import ProgressBar from 'bootstrap-vue/src/components/progress/progress-bar'
    import Tooltip from 'bootstrap-vue/src/components/tooltip/tooltip'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import FileUpload from 'vue-simple-upload/dist/FileUpload'

    export default {
        components: {
            BFormCheckbox,
            'table-server': TableServer,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal,
            'b-progress': Progress,
            'b-progress-bar': ProgressBar,
            'b-tooltip': Tooltip,
            vSelect,
            'fileupload': FileUpload
        },
        data: () => {
            return {
                table: {
                    columns: ['actions', 'id', 'folder', 'name', 'status'],
                },
                urlPhotos: '',
                destinyName: '',
                id: '',
                loadFile: false,
            }
        },
        computed: {
            menuOptions: function () {
                return [
                    {
                        type: 'edit',
                        link: 'services_new/edit/',
                        icon: 'dot-circle',
                    },
                    {
                        type: 'delete',
                        link: 'services_new/edit/',
                        icon: 'times',
                    },
                ]
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        folder: this.$i18n.t('photos.folder_cloudinary'),
                        name: this.$i18n.t('photos.name'),
                        status: this.$i18n.t('photos.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    responseAdapter ({ data }) {
                        return {
                            data: data.data,
                            count: data.count
                        }
                    },
                    // params: {
                    //     'status': this.status_id,
                    //     'service_type': this.type_service_id,
                    //     'service_category': this.category_service_id,
                    //     'service_name': this.service_name,
                    // },
                    requestFunction: function (data) {
                        let url = '/multimedia?token=' + window.localStorage.getItem('access_token') + '&lang='
                            + localStorage.getItem('lang')
                        return API.get(url, {
                            params: data
                        }).catch(function (e) {
                            this.dispatch('error', e)
                        }.bind(this))
                    }
                }
            }
        },
        created () {
            this.csrf = window.Laravel.csrfToken
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            checkboxChecked: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            showModal (service_id, service_name) {
                this.service_id = service_id
                this.serviceName = service_name
                this.$refs['my-modal'].show()
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            changeState: function (id, status) {
                API({
                    method: 'put',
                    url: 'multimedia/' + id + '/status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.photos'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            },
            remove () {
                API({
                    method: 'DELETE',
                    url: 'multimedia/' + this.service_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdate()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.photos'),
                                text: this.$t('global.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.photos'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            onUpdate () {
                this.urlPhotos = '/api/photos?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang')
                this.$refs.table.$refs.tableserver.refresh()
            },
            ubigeoChange: function (value) {
                this.ubigeo = value
                if (this.ubigeo != null) {
                    if (this.ubigeo_id != this.ubigeo.code) {
                    }
                    this.ubigeo_id = this.ubigeo.code
                } else {
                    this.ubigeo_id = ''
                }
            },
            statusChange: function (value) {
                this.status = value
                if (this.status != null) {
                    this.status_id = this.status.code
                } else {
                    this.status_id = ''
                }
            },
        }
    }
</script>

<style lang="stylus">


    /* style 2 */
    .inputfile {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }

    .inputfile + label {
        max-width: 80%;
        font-size: 0.875rem;
        font-weight: 400;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        background-color: #bd0d12;
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        line-height: 1.5;
        border: 1px solid transparent;
    }

    .inputfile + label {
        color: #fff;
    }

    .inputfile:focus + label,
    .inputfile.has-focus + label,
    .inputfile + label:hover {
        color: #722040;
    }

</style>
