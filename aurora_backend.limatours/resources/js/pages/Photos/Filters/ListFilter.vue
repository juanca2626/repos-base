<template>
    <div>
        <div class="container-fluid" v-show="isForm">
            <div class="row">
                <div class="col-md-8">
                    <h4>Filtro - Intereses:</h4>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success pull-right" type="submit" v-if="!loading"
                            @click="addFilter('interests')">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.add') }}
                    </button>
                </div>
            </div>
            <table-client :columns="table.columns" :data="interests" :loading="loading"
                          :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :name="'Filtro'" :options="menuOptions" @edit="edit(props.row)"
                               @remove="remove(props.row)"/>
                </div>
                <div class="table-service_ubigeo" slot="name" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.translations.length > 0">{{props.row.translations[0].value}}</span>
                    <span v-else> - </span>
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
            </table-client>
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4>Filtro - Composicíon:</h4>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success pull-right" type="submit" v-if="!loading"
                            @click="addFilter('composition')">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.add') }}
                    </button>
                </div>
            </div>
            <hr>
            <table-client :columns="table.columns" :data="composition" :loading="loading"
                          :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :name="'Markup'" :options="menuOptions" @edit="edit(props.row)"
                               @remove="remove(props.row)"/>
                </div>
                <div class="table-service_ubigeo" slot="name" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.translations.length > 0">{{props.row.translations[0].value}}</span>
                    <span v-else> - </span>
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
            </table-client>
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4>Filtro - Tipo de servicio:</h4>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success pull-right" type="submit" v-if="!loading"
                            @click="addFilter('type_of_service')">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.add') }}
                    </button>
                </div>
            </div>
            <table-client :columns="table.columns" :data="type_of_service" :loading="loading"
                          :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :name="'Markup'" :options="menuOptions" @edit="edit(props.row)"
                               @remove="remove(props.row)"/>
                </div>
                <div class="table-service_ubigeo" slot="name" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.translations.length > 0">{{props.row.translations[0].value}}</span>
                    <span v-else> - </span>
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
            </table-client>
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4>Filtro - Tipo de medio:</h4>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success pull-right" type="submit" v-if="!loading"
                            @click="addFilter('media_type')">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.add') }}
                    </button>
                </div>
            </div>
            <table-client :columns="table.columns" :data="media_type" :loading="loading"
                          :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :name="'Markup'" :options="menuOptions" @edit="edit(props.row)"
                               @remove="remove(props.row)"/>
                </div>
                <div class="table-service_ubigeo" slot="name" slot-scope="props" style="font-size: 0.9em">
                    <span v-if="props.row.translations.length > 0">{{props.row.translations[0].value}}</span>
                    <span v-else> - </span>
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
            </table-client>
        </div>
        <div class="container-fluid" v-show="!isForm">
            <div class="row">
                <div class="col-sm-12">
                    <form @submit.prevent="validateBeforeSubmit">
                        <div class="b-form-group form-group">
                            <div class="form-row" id="container_country">
                                <label class="col-sm-2 col-form-label" for="tag_name">Nombre del filtro</label>
                                <div class="col-sm-3">
                                    <input :class="{'form-control':true }"
                                           id="tag_name" name="tag_name"
                                           type="text"
                                           v-model="form.translations[currentLang].tag_name" v-validate="'required'">
                                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           style="margin-left: 5px;" v-show="errors.has('tag_name')"/>
                                        <span v-show="errors.has('tag_name')">{{ errors.first('tag_name') }}</span>
                                    </div>
                                </div>
                                <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                                    <option v-bind:value="language.id" v-for="language in languages">
                                        {{ language.iso }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="folder">Etiqueta Cloudinary</label>
                                <div class="col-sm-3">
                                    <!-- select class="form-control" id="folder" required size="0" v-model="form.tag"
                                            name="folder" v-validate="'required'">
                                        <option v-bind:value="tag.path" v-for="tag in tags">
                                            {{ tag.name }}
                                        </option>
                                    </select -->
                                    <input type="text" v-model="form.tag" id="folder" class="form-control" required />
                                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           style="margin-left: 5px;" v-show="errors.has('folder')"/>
                                        <span v-show="errors.has('folder')">{{ errors.first('folder') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-6">
                    <div slot="footer">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{ $t('global.buttons.submit') }}
                        </button>
                        <button class="btn btn-danger" type="button" @click="cancel">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from '../../../api'
    import TableClient from './../../../components/TableClient'
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
    import MenuEdit from '../../../components/MenuEdit'

    export default {
        components: {
            BFormCheckbox,
            'table-client': TableClient,
            'b-dropdown': BDropDown,
            'b-dropdown-item-button': BDropDownItemButton,
            BModal,
            'b-progress': Progress,
            'b-progress-bar': ProgressBar,
            'b-tooltip': Tooltip,
            vSelect,
            'menu-edit': MenuEdit,
            'fileupload': FileUpload
        },
        data: () => {
            return {
                table: {
                    columns: ['actions', 'id', 'tag', 'name', 'status'],
                },
                tags: [],
                languages: [],
                interests: [],
                composition: [],
                type_of_service: [],
                media_type: [],
                urlPhotos: '',
                tagName: '',
                currentLang: '1',
                id: '',
                type: '',
                loading: false,
                isForm: true,
                tag_id: '',
                form: {
                    tag: '',
                    type_tag: '',
                    translations: {
                        '1': {
                            'id': '',
                            'tag_name': ''
                        }
                    }
                }
            }
        },
        computed: {
            menuOptions: function () {

                let options = []

                if (this.$can('update', 'markups')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: '',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'editButton'
                    })
                }
                if (this.$can('update', 'markups')) {
                    options.push({
                        type: 'delete',
                        text: '',
                        link: '',
                        icon: 'trash',
                        type_action: 'button',
                        callback_delete: 'remove'
                    })
                }
                return options
            },
            tableOptions: function () {
                return {
                    headings: {
                        id: 'ID',
                        tag: this.$i18n.t('photos.tag_cloudinary'),
                        name: this.$i18n.t('photos.name'),
                        status: this.$i18n.t('photos.status'),
                        actions: this.$i18n.t('global.table.actions')
                    },
                    sortable: [],
                    filterable: [],
                }
            }
        },
        created () {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.fetchData()
            })
        },
        mounted () {
            this.$i18n.locale = localStorage.getItem('lang')
            this.fetchData()
            API.get('/tags/multimedia').then((result) => {
                let folders = result.data.tags
                folders.forEach((tag) => {
                    this.tags.push({
                        name: tag,
                        path: tag
                    })
                })
            })
            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    let form = this.form
                    let languages = this.languages
                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            tag_name: ''
                        }
                    })

                    this.form = form
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('inclusions.error.messages.name'),
                    text: this.$t('inclusions.error.messages.connection_error')
                })
            })
        },
        methods: {
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.photos'),
                            text: this.$t('inclusions.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            submit () {
                this.loading = true
                let url = 'multimedia/filter'
                if(this.formAction == 'PUT'){
                    url = 'multimedia/' + this.tag_id +'/filter'
                }
                API({
                    method: this.formAction,
                    url: url,
                    data: this.form
                })
                    .then((result) => {
                        if (result.data.success === false) {
                            if (result.data.error.length > 0 && result.data.error[0] === 'validation.unique') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.photos'),
                                    text: 'El tag de cloudinary ya se encuentra registrado.'
                                })
                            }else{
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.photos'),
                                    text: this.$t('inclusions.error.messages.information_error')
                                })
                            }

                            this.loading = false
                        } else {
                            this.isForm = true
                            this.fetchData()
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
            fetchData: function () {
                this.loading = true
                API.get('multimedia/filters?lang=' + localStorage.getItem('lang')).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        let data = result.data.data
                        this.interests = data[0].photo_filters
                        this.composition = data[1].photo_filters
                        this.type_of_service = data[2].photo_filters
                        this.media_type = data[3].photo_filters
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: result.data.message
                        })
                    }
                })
                    .catch((e) => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Fetch Error',
                            text: 'Cannot load data'
                        })
                    })

            },
            checkboxChecked: function (service_status) {
                if (service_status) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeState: function (id, status) {
                API({
                    method: 'put',
                    url: 'multimedia/' + id + '/filter_status',
                    data: { status: status }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData()
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
            remove (tag) {
                API({
                    method: 'DELETE',
                    url: 'multimedia/' + tag.id + '/filter'
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchData()
                            this.tag_id = ''
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
            addFilter: function (type) {
                this.isForm = false
                this.formAction = 'POST'
                this.currentLang = this.languages[0].id
                this.form = {
                    tag: '',
                    type_tag: type,
                    translations: {
                        '1': {
                            'id': '',
                            'tag_name': ''
                        }
                    }
                }
                let languages = this.languages
                languages.forEach((value) => {
                    this.form.translations[value.id] = {
                        id: '',
                        tag_name: ''
                    }
                })
            },
            cancel: function () {
                this.isForm = true
                this.form.type_tag = ''
                this.formAction = 'POST'
            },
            statusChange: function (value) {
                this.status = value
                if (this.status != null) {
                    this.status_id = this.status.code
                } else {
                    this.status_id = ''
                }
            },
            edit: function (tag, type) {
                this.tag_id = tag.id
                API.get('/multimedia/' + this.tag_id + '/filter')
                    .then((result) => {
                        let tag = result.data.data
                        this.formAction = 'PUT'
                        let arrayTranslations = tag[0].translations
                        this.form.tag = tag[0].tag
                        this.form.type_tag = type
                        arrayTranslations.forEach((translation) => {
                            this.form.translations[translation.language_id] = {
                                id: translation.id,
                                tag_name: translation.value
                            }
                        })
                        this.isForm = false
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
