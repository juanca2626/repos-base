<template>
    <div class="row">
        <div class="col-sm-8">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <div class="col-3">
                            <label class="col-sm-12 col-form-label">Seleccione uno o varios highlights</label>
                        </div>
                        <div class="col-9">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :hide-selected="true"
                                         :searchable="true"
                                         :multiple="true"
                                         :options="highlights"
                                         placeholder="Seleccione..."
                                         :preserve-search="false"
                                         tag-placeholder="Seleccione..."
                                         :taggable="false"
                                         @tag="addExecutive"
                                         label="name"
                                         ref="multiselect"
                                         track-by="code"
                                         data-vv-as="highlights"
                                         data-vv-name="highlights"
                                         name="highlights"
                                         v-model="highlightsChoosed"
                                         v-validate="'required'">
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('highlights')">
                                <span>Campo requerido</span>
                            </span>
                        </div>
                    </div>

                </div>
            </form>
        </div>
        <div class="col-md-4">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading"
                        :disabled="enabledBtnExcel">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}} ({{ packagesChoosed.length}})
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>


        <div class="col-md-12">
            <div class="col-4 p-0 mb-2">
                <input class="form-control" id="search_packages" type="search" v-model="query_packages"
                       value="" placeholder="Buscar por nombre o código">
            </div>
            <div class="table-responsive">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="vueTable_column_package">
                            <span title="" class="VueTables__heading">Paquete</span>
                        </th>
                        <th class="vueTable_column_status">
                            <span title="" class="VueTables__heading">Seleccionar</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="packages.length>0" v-for="pack in packages">
                        <td class="vueTable_column_package">
                            <div class="table-package" style="font-size: 0.9em;">
                                <div class="mb-1">
                                    {{ pack.id }} - <span v-html="pack.translations[0].name"></span>
                                </div>
                                <small class="badge service">{{pack.destinations}}</small>

                            </div>
                        </td>
                        <td class="vueTable_column_status">
                            <div class="table-status" style="font-size: 0.9em; cursor: pointer;">
                                <font-awesome-icon :icon="['fas', 'check-circle']" @click="changeChoosed(pack)"
                                                   :class="'fa-2x check_'+pack.choosed"/>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="packages.length==0" class="trPadding">
                        <td colspan="5">
                            <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
                            <center><span v-if="!loading">Ninguno por mostrar</span></center>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="VuePagination row col-md-12 justify-content-center">
                <nav class="text-center">
                    <ul class="pagination_ VuePagination__pagination" style="">
                        <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                        'disabled':(pageChosen==1 || loading)}" @click="setPage(pageChosen-1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==1 || loading)"
                               class="page-link">&lt;</a>
                        </li>
                        <li v-for="page in package_pages" @click="setPage(page)"
                            :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosen), 'disabled':loading }">
                            <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                        </li>
                        <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                        'disabled':(pageChosen==package_pages.length || loading)}" @click="setPage(pageChosen+1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==package_pages.length || loading)"
                               class="page-link">&gt;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

    </div>
</template>

<script>
    import { API } from './../../api'
    import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
    import BFormRadio from 'bootstrap-vue/es/components/form-radio/form-radio'
    import BFormRadioGroup from 'bootstrap-vue/es/components/form-radio/form-radio-group'
    import Multiselect from 'vue-multiselect'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import { Switch as cSwitch } from '@coreui/vue'

    export default {
        components: {
            BFormGroup,
            BFormRadio,
            BFormRadioGroup,
            vSelect,
            cSwitch,
            Multiselect
        },
        data: () => {
            return {
                loading: false,
                enabledBtnExcel: true,
                highlightsChoosed: [],
                packagesChoosed: [],
                formAction: 'post',
                highlights: [],
                packages: [],
                query_packages: '',
                target: '',
                package_pages: [],
                pageChosen: 1,
                limit: 10,
                form: {
                    executivesSelected: [],
                    packagesSelected: []
                }
            }
        },
        mounted () {
            let search_packages = document.getElementById('search_packages')
            let timeout_extensions
            search_packages.addEventListener('keydown', () => {
                clearTimeout(timeout_extensions)
                timeout_extensions = setTimeout(() => {
                    this.pageChosen = 1
                    this.fetchDataPackage()
                    clearTimeout(timeout_extensions)
                }, 1000)
            })
            this.fetchDataHighlights()
            this.fetchDataPackage()
        },
        methods: {
            fetchDataHighlights: function () {
                API.get('/image_highlights?lang=' + localStorage.getItem('lang') + '&token=' + window.localStorage.getItem('access_token'))
                    .then((result) => {
                        if (result.data.success === true) {
                            let highlights_data = result.data.data
                            let highlights = []
                            highlights_data.forEach(function (item) {
                                highlights.push({
                                    'code': item.id,
                                    'name': item.translations[0].value,
                                })
                            })
                            this.highlights = highlights
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    })
            },
            fetchDataPackage: function () {
                this.packages = []
                this.loading = true
                API.get('package/search?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang') + '&queryCustom=' + this.query_packages + '&page=' + this.pageChosen + '&limit=' + this.limit)
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            result.data.data.forEach(_data => {
                                _data.choosed = false
                                this.packagesChoosed.forEach(pack => {
                                    if (_data.id === pack.id) {
                                        _data.choosed = true
                                    }
                                })
                            })

                            this.package_pages = []
                            for (let i = 0; i < (result.data.count / this.limit); i++) {
                                this.package_pages.push(i + 1)
                            }
                            this.packages = result.data.data

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    })
            },
            addExecutive (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.form.executivesSelected.push(tag)
            },
            CancelForm () {
                this.$router.push({ name: 'PackageHighLightsList' })
            },
            validateBeforeSubmit () {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.submit()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.packages'),
                            text: this.$t('packages.error.messages.information_complete')
                        })
                        this.loading = false
                    }
                })
            },
            getIsdHighLights(){
                let ids = []
                this.highlightsChoosed.forEach(function(item){
                    ids.push(item.code)
                })
                return ids
            },
            getIsdPackagesChoosed(){
                let ids = []
                this.packagesChoosed.forEach(function(item){
                    ids.push(item.id)
                })
                return ids
            },
            submit () {

                this.loading = true

                API({
                    method: 'post',
                    url: 'packages/highlights',
                    data: {
                        'highlights': this.getIsdHighLights(),
                        'packages': this.getIsdPackagesChoosed()
                    }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.packagesChoosed = []
                            this.highlightsChoosed = []
                            this.enabledBtnExcel = true
                            this.packages.forEach(pack => {
                                pack.choosed = false
                            })
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Highlights',
                                text: this.$t('global.success.save')
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Highlights',
                                text: result.data.message
                            })

                        }
                    })
            },
            setPage (page) {
                if (page < 1 || page > this.package_pages.length) {
                    return
                }
                this.pageChosen = page
                this.fetchDataPackage()
            },
            changeChoosed (me) {
                me.choosed = !(me.choosed)
                let count_r = 0
                this.packagesChoosed.forEach((rate, k) => {
                    if (me.id == rate.id) {
                        count_r++
                        this.packagesChoosed.splice(k, 1)
                    }
                })
                if (!count_r) {
                    this.packagesChoosed.push(me)
                }

                if (this.packagesChoosed.length > 0) {
                    this.enabledBtnExcel = false
                } else {
                    this.enabledBtnExcel = true
                }
            },
        },
        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString().toLowerCase()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    }
</script>


<style lang="stylus">
    .back-green {
        background: #bfffd0;
        padding: 5px;
    }

    .back-green-right {
        border-radius: 0 4px 4px 0px;
    }

    .back-green-left {
        border-radius: 4px 0px 0px 4px;
    }

    .table-actions {
        display: flex;
    }

    .trExtension, .trExtension > th, .trExtension > td {
        background-color: #e9eaff;
    }

    .trExtension:hover, .trExtension:hover > th, .trExtension:hover > td {
        background-color: #e2e3ff;
    }

    .VueTables__limit {
        display: none;
    }

    .no-margin {
        padding-left: 0;
        padding-bottom: 5px !important;
        padding-right: 0px;
    }

    .trPadding, .trPadding > th, .trPadding > td {
        padding: 10px !important;
    }

    .check_true {
        color: #04bd12;
    }

    .pagination_ {
        list-style: none;
        border-radius: 0.25rem;
    }

    .VuePagination__pagination li {
        float: left;
        margin-bottom: 12px;
    }
</style>

