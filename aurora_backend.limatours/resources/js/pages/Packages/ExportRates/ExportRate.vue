<template>
    <div class="container-fluid">

        <div class="row col-12 no-margin">
            <div class="col-4">
                <input class="form-control" id="search_packages" type="search" v-model="query_packages"
                       value="" placeholder="Buscar por nombre o código">
            </div>
            <div class="col-1 back-green back-green-left">
                <select class="form-control" v-model="form.year" :disabled="enabledBtnExcel">
                    <option :value="year_current">{{ year_current }}</option>
                    <option :value="year_current+1">{{ year_current + 1 }}</option>
                    <option :value="year_current+2">{{ year_current + 2 }}</option>
                    <option :value="year_current+3">{{ year_current + 3 }}</option>
                </select>
            </div>
            <div class="col-4 back-green">
                <v-select :options="clients" :disabled="enabledBtnExcel"
                          label="name" :filterable="false" @search="onSearch"
                          placeholder="Filtro por nombre ó ID del cliente"
                          v-model="form.client" name="clients" id="clients" style="height: 35px;">
                    <template slot="option" slot-scope="option">
                        <div class="d-center">
                            <span>{{ option.label }}</span>
                        </div>
                    </template>
                    <template slot="selected-option" slot-scope="option">
                        <div class="selected d-center">
                            <span>{{ option.label }}</span>
                        </div>
                    </template>
                </v-select>
            </div>
            <div class="col-1 back-green back-green-right">
                <button @click="see_errors()" v-if="rate_errors.length>0"
                        class="btn btn-danger" type="button" :disabled="enabledBtnExcel"
                        style="float: right; margin-right: 5px;">
                    <font-awesome-icon :icon="['fas', 'times']"/>
                    ({{ rate_errors.length }})
                </button>
            </div>
            <div class="col-2 back-green back-green-right">
                <button @click="exportExcel()"
                        class="btn btn-success" type="button" :disabled="enabledBtnExcel"
                        style="float: right; margin-right: 5px;">
                    <font-awesome-icon :icon="['fas', 'file-excel']"/>
                    Exportar ({{ ratesChoosed.length }})
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="VueTables__table table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th class="vueTable_column_package">
                        <span title="" class="VueTables__heading">Paquete</span>
                    </th>
                    <th class="vueTable_column_name">
                        <span title="" class="VueTables__heading">Nombre</span>
                    </th>
                    <th class="vueTable_column_categories">
                        <span title="" class="VueTables__heading">Categorias</span>
                    </th>
                    <th class="vueTable_column_period">
                        <span title="" class="VueTables__heading">Periodo</span>
                    </th>
                    <th class="vueTable_column_status">
                        <span title="" class="VueTables__heading">Seleccionar</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="plan_rates.length>0"
                    :class="{'trPadding':true,'trExtension':(plan_rate.package.extension==1)}"
                    v-for="(plan_rate,index) in plan_rates" :key="plan_rate.id">
                    <td class="vueTable_column_package">
                        <div class="table-package" style="font-size: 0.9em;">
                            <span v-if="plan_rate.package.code">
                                {{ plan_rate.id }} - [{{ plan_rate.package.code }}] -
                            </span>
                            <span v-if="!(plan_rate.package.code)">
                                <span v-if="plan_rate.package.extension=='1'">[E{{ plan_rate.package.id }}] - </span>
                                <span v-if="plan_rate.package.extension=='0'">[P{{ plan_rate.package.id }}] - </span>
                            </span>
                            <span v-html="plan_rate.package.translations[0].name"></span>
                        </div>
                    </td>
                    <td class="vueTable_column_name">
                        <div class="table-category" style="font-size: 0.9em;">
                            [{{ plan_rate.service_type.abbreviation }}] - {{ plan_rate.name }}
                        </div>
                    </td>
                    <td class="vueTable_column_categories">
                        <div class="table-category" style="font-size: 0.9em;">
                            <div class="badge badge-primary bag-category mr-1"
                                 v-for="category in plan_rate.plan_rate_categories" :key="category.id">
                                {{ category.category.translations[0].value }}
                            </div>
                        </div>
                    </td>
                    <td class="vueTable_column_period">
                        <div class="table-category" style="font-size: 0.9em;">
                            {{ plan_rate.date_from | formatDate }} - {{ plan_rate.date_to | formatDate }}
                        </div>
                    </td>
                    <td class="vueTable_column_status">
                        <div class="table-status" style="font-size: 0.9em; cursor: pointer;">
                            <font-awesome-icon :icon="['fas', 'check-circle']" @click="changeChoosed(plan_rate)"
                                               :class="'fa-2x check_'+plan_rate.choosed"/>
                        </div>
                    </td>
                </tr>

                <tr v-if="plan_rates.length==0" class="trPadding">
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
                        <a href="javascript:void(0);" :disabled="(pageChosen==1 || loading)" class="page-link">&lt;</a>
                    </li>
                    <li v-for="(page,index_page) in plan_rate_pages" @click="setPage(page)" :key="index_page"
                        :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosen), 'disabled':loading }">
                        <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                    </li>
                    <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                        'disabled':(pageChosen==plan_rate_pages.length || loading)}" @click="setPage(pageChosen+1)">
                        <a href="javascript:void(0);" :disabled="(pageChosen==plan_rate_pages.length || loading)"
                           class="page-link">&gt;</a>
                    </li>
                </ul>
            </nav>
        </div>


        <b-modal title="Errores en Tarifas" centered ref="my-modal" size="lg">
            <div>
                <b-tabs v-model="tabIndex">
                    <b-tab :title="'('+rate.plan_rate_id+') ' + rate.plan_rate_name" v-for="(rate,index_error) in rate_errors" :key="index_error">
                        <b-tabs>
                            <b-tab :title="'('+category.id+') ' + category.name" v-for="(category,index_error_cate) in rate.errors" :key="index_error_cate">
                                <p v-for="service in category.services" :key="service.id">
                                    <span style="background-color: #68ffb5;"><b>{{ service.type.toUpperCase() }}</b> - {{
                                            service.name
                                        }} [{{ service.code }}] [{{ service.id }}]</span>: <b>{{ service.date_in }} -
                                    {{ service.date_out }}</b><br>
                                    <b>error:</b> {{ service.error }}
                                </p>
                            </b-tab>
                        </b-tabs>
                    </b-tab>
                </b-tabs>
            </div>

            <div slot="modal-footer">
                <button type="button" @click="hideModal()" class="btn btn-danger">Cerrar</button>
            </div>
        </b-modal>

        <block-page></block-page>
    </div>

</template>

<script>
import {API} from './../../../api'
import TableServer from '../../../components/TableServer'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BlockPage from '../../../components/BlockPage'
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
    components: {
        BFormCheckbox,
        'table-server': TableServer,
        BlockPage, vSelect
    },
    data: () => {
        return {
            loading: false,
            enabledBtnExcel: true,
            ratesChoosed: [],
            packages: [],
            plan_rates: [],
            type_packages: [],
            package_id: null,
            query_packages: '',
            physical_intensity_color: 'FFFFFF',
            packageName: '',
            pageChosen: 1,
            limit: 10,
            plan_rate_pages: [],
            i_rates: 0,
            form: {
                year: new Date().getFullYear(),
                client: ""
            },
            year_current: new Date().getFullYear(),
            clients: [],
            rate_errors: [],
            tabIndex: 0,
        }
    },
    mounted() {

        this.$i18n.locale = localStorage.getItem('lang')
        this.$root.$emit('updateTitlePackage', {tab: 1})
        this.type_packages.push({
            name: this.$i18n.t('packages.packages'),
            check: true,
            _class: 'trPadding'
        }, {
            name: this.$i18n.t('packages.extensions'),
            check: true,
            _class: 'trPadding trExtension'
        })

        let search_packages = document.getElementById('search_packages')
        let timeout_extensions
        search_packages.addEventListener('keydown', () => {
            clearTimeout(timeout_extensions)
            timeout_extensions = setTimeout(() => {
                this.pageChosen = 1
                this.onUpdate()
                clearTimeout(timeout_extensions)
            }, 1000)
        })

        this.onUpdate()

    },
    created() {
        localStorage.setItem("packagenamemanage", "")
    },
    computed: {},
    methods: {
        hideModal() {
            this.$refs['my-modal'].hide()
        },
        see_errors() {
            this.$refs['my-modal'].show()
        },
        onSearch(search, loading) {
            loading(true);
            this.clients = [];
            console.log(search);
            API.get('/client/search?lang=' + localStorage.getItem('lang') + '&queryCustom=' + search).then((result) => {
                loading(false);
                result.data.data.forEach((clients) => {
                    this.clients.push({label: '(' + clients.code + ') ' + clients.name, id: clients.id});
                });
            }).catch(() => {
                loading(false);
            });
        },
        setPage(page) {
            if (page < 1 || page > this.plan_rate_pages.length) {
                return;
            }
            this.pageChosen = page
            this.onUpdate()
        },
        changeChoosed(me) {
            me.choosed = !(me.choosed)
            let count_r = 0
            this.ratesChoosed.forEach((rate, k) => {
                if (me.id == rate.id) {
                    count_r++
                    this.ratesChoosed.splice(k, 1)
                }
            })
            if (!count_r) {
                this.ratesChoosed.push(me)
            }

            if (this.ratesChoosed.length > 0) {
                this.enabledBtnExcel = false
            } else {
                this.enabledBtnExcel = true
            }
        },
        exportExcel() {
            this.rate_errors = []
            this.i_rates = 0
            this.doExportExcel(this.ratesChoosed[this.i_rates])
        },
        doExportExcel(rate) {

            this.enabledBtnExcel = true
            let title = rate.service_type.abbreviation + ' - ' + rate.name

            let year_ = this.form.year
            let client_id_ = (this.form.client !== null && this.form.client.id !== undefined) ? this.form.client.id : ""

            API({
                method: 'POST',
                url: 'package/plan_rates/' + rate.id + '/excel/' + rate.service_type_id + '?lang=' +
                    localStorage.getItem('lang') + '&title=' + title + '&client_id=' +
                    client_id_ + '&year=' + year_,
                responseType: 'blob',
            })
                .then((response) => {
                    API({
                        method: 'POST',
                        url: 'package/plan_rates/errors'
                    })
                        .then((response2) => {
                            if (response2.data.data[rate.id]) {
                                if (response2.data.data[rate.id].id === 0) {
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('global.modules.services'),
                                        text: title + "El cliente elegido no tiene markup asociado al paquete."
                                    })
                                } else {
                                    this.rate_errors.push({
                                        plan_rate_id: rate.id,
                                        plan_rate_name: title,
                                        errors: response2.data.data[rate.id]
                                    })
                                    console.log(this.rate_errors)
                                    this.$notify({
                                        group: 'main',
                                        type: 'error',
                                        title: this.$t('global.modules.services'),
                                        text: title + ". No tiene tarifas en algunos servicios/hoteles"
                                    })
                                }
                            } else {
                                var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                                var fileLink = document.createElement('a')
                                fileLink.href = fileURL
                                fileLink.setAttribute('download', 'TARIFAS ' + this.form.year + ' - ' + title + '.xlsx')
                                document.body.appendChild(fileLink)
                                fileLink.click()
                            }

                            this.enabledBtnExcel = false

                            this.i_rates++
                            console.log(rate.id, ' Comparando y esperando')
                            if (this.i_rates < this.ratesChoosed.length) {
                                setTimeout(() => {
                                    console.log(rate.id, ' Enviado nuevamente')
                                    this.doExportExcel(this.ratesChoosed[this.i_rates])
                                }, 1500)
                            } else {
                                console.log(this.rate_errors)
                            }

                        }).catch(() => {

                        this.enabledBtnExcel = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('services.error.messages.information_error')
                        })
                    })

                }).catch(() => {

                this.enabledBtnExcel = false
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('services.error.messages.information_error')
                })
            })
        },
        onUpdate() {

            this.loading = true
            this.plan_rates = []
            API({
                method: 'GET',
                url: 'plan_rates/export/list?token=' + window.localStorage.getItem('access_token') +
                    '&lang=' + localStorage.getItem('lang') + '&queryCustom=' + this.query_packages +
                    '&page=' + this.pageChosen + '&limit=' + this.limit
            })
                .then((result) => {
                    result.data.data.forEach(_data => {
                        _data.choosed = false
                        this.ratesChoosed.forEach(rate => {
                            if (_data.id == rate.id) {
                                _data.choosed = true
                            }
                        })
                    })

                    this.plan_rate_pages = []
                    for (let i = 0; i < (result.data.count / this.limit); i++) {
                        this.plan_rate_pages.push(i + 1)
                    }

                    this.plan_rates = result.data.data
                    this.loading = false

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Error Modulo Exportación Tarifas',
                    text: this.$t('packages.error.messages.connection_error')
                })
            })
        },
        getNamePackage(code, name, extension) {
            var nameComplete = ''
            if (name !== null) {
                nameComplete = code + ' - ' + name;
            } else {
                nameComplete = code
            }
            localStorage.setItem('packagenamemanage', nameComplete)
            localStorage.setItem('package_extension', extension)
            this.$root.$emit('updateTitlePackage', {tab: 1})
        }
    },
    filters: {
        formatDate: function (_date) {
            if (_date == undefined) {
                // console.log('fecha no parseada: ' + _date)
                return
            }
            _date = _date.split('-')
            _date = _date[2] + '/' + _date[1] + '/' + _date[0]
            return _date
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
