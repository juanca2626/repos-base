<template>
    <div class="container-fluid" style="position: relative;">
        <div
            class="row justify-content-end"
            style="padding-bottom: 0%;width: 30%;position:absolute;right:0;z-index:1000"
        >
            <div
                class="b-form-group form-group"
                style="width: 100%;margin-right: 21px;margin-bottom: -40px;"
            >
                <div class="form-row">
                    <!-- <label class="col-3 col-form-label" for="searchStatus">{{ $t('clients.state') }}</label> -->
                    <label class="col-3 col-form-label" for="selectMarket">{{ $t('clients.market') }}</label>
                    <div class="col-8">
                        <select
                            @change="filterByMarket"
                            ref="selectMarket"
                            class="form-control"
                            name="selectMarket"
                            id="selectMarket"
                            required
                            v-model="marketSelection"
                        >
                            <!-- <option value="-1" selected hidden>{{ $t('clients.select_market') }}</option> -->
                            <option value="-1" disabled>Seleccione un mercado</option>
                            <option value="0">TODOS</option>
                            <option :value="market.value" v-for="market in markets" v-bind:key="market.value">
                                <!-- {{$t(status.text)}}
                                -->
                                {{market.text}}
                            </option>
                        </select>
                    </div>
                    <div class="col-5"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- <span class="loading-indicator" v-if="loading"></span> -->
        <v-server-table
            :columns="table.columns"
            :options="tableOptions"
            v-on:loading="loadingData = !loadingData"
            v-on:loaded="loadingData = !loadingData"
            ref="table"
        >
            <template slot="beforeBody">
                <div class="loading-table-container" v-if="loadingData">
                    <img alt="loading" height="51px" src="/images/loading.svg" class="mx-auto d-block"/>
                </div>
            </template>
            <div class="table-state" slot="weekly" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.weekly)"
                    v-model="props.row.weekly"
                    :id="'checkbox_weekly_'+props.row.id"
                    :name="'checkbox_weekly_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.weekly, 'weekly')"
                    switch></b-form-checkbox>
            </div>
            <div class="table-state" slot="day_before" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.day_before)"
                    v-model="props.row.day_before"
                    :id="'checkbox_day_before_'+props.row.id"
                    :name="'checkbox_day_before_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.day_before, 'day_before')"
                    switch></b-form-checkbox>
            </div>
            <div class="table-state" slot="daily" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.daily)"
                    v-model="props.row.daily"
                    :id="'checkbox_daily_'+props.row.id"
                    :name="'checkbox_daily_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.daily, 'daily')"
                    switch></b-form-checkbox>
            </div>
            <div class="table-state" slot="survey" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.survey)"
                    v-model="props.row.survey"
                    :id="'checkbox_survey_'+props.row.id"
                    :name="'checkbox_survey_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.survey, 'survey')"
                    switch></b-form-checkbox>
            </div>
            <div class="table-state" slot="whatsapp" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.whatsapp)"
                    v-model="props.row.whatsapp"
                    :id="'checkbox_whatsapp_'+props.row.id"
                    :name="'checkbox_whatsapp_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.whatsapp, 'whatsapp')"
                    switch></b-form-checkbox>
            </div>
            <div class="table-state text-center" slot="logo" slot-scope="props">
                <button class="btn btn-primary my-2" style="width:80px;" @click="showModal(props.row)">
                    <font-awesome-icon :icon="['fas', 'image']" class="mr-1"/>
                    Editar
                </button>
            </div>
            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="!!(props.row.status)"
                    v-model="props.row.status"
                    :id="'checkbox_status_'+props.row.id"
                    :name="'checkbox_status_'+props.row.id"
                    @change="switchConfig(props.row.id,props.row.status, 'status')"
                    switch></b-form-checkbox>
            </div>
        </v-server-table>
        <b-modal
            :title="modalTitle"
            centered
            ref="logoEditModal"
            size="md"
            :no-close-on-backdrop="true"
            :no-close-on-esc="true"
            :hide-footer="false"
            :hide-header-close="true"
        >
            <div class="client-details row align-items-center" v-if="clientData">
                <div class="col">
                    <h6>
                        Mercado:
                        <br/>
                        {{clientData.market_name}}
                    </h6>
                </div>
                <div class="col">
                    <h6>
                        Código:
                        <br/>
                        {{clientData.code}}
                    </h6>
                </div>
                <div class="col">
                    <h6>
                        Cliente:
                        <br/>
                        {{clientData.name}}
                    </h6>
                </div>
            </div>
            <div class="image-container" v-if="clientData">
                <div class="loading-container" v-show="loadingLogo">
                    <img alt="loading" height="51px" src="/images/loading.svg" class="mx-auto d-block"/>
                </div>
                <img
                    class="client-img img-fluid mx-auto d-block"
                    :src="clientData.logo"
                    alt="logo"
                />
            </div>

            <div slot="modal-footer">
                <input
                    type="file"
                    class="btn btn-success"
                    @change="changeLogo()"
                    ref="logoSelection"
                    id="logoSelection"
                    accept="image/jpeg, image/png"
                    style="display: none"
                />
                <button @click="openExplorer()" class="btn btn-primary mr-1" :disabled="loadingLogo">Cambiar imagen
                </button>
                <!-- <button @click="restoreDefault()" class="btn btn-success mr-1">Restaurar</button> -->
                <button @click="hideModal()" class="btn btn-danger" :disabled="loadingLogo">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <!-- <b-modal
          centered
          ref="logoRestore"
          size="sm"
          id="modalDialog"
          :no-close-on-backdrop="false"
          :no-close-on-esc="false"
          :hide-footer="false"
        >
          <h6>¿Está seguro que desea cambiar la imagen a la predeterminada?</h6>

          <div slot="modal-footer">

          </div>
        </b-modal>-->
    </div>
</template>


<script>
    import { API } from './../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            BFormCheckbox,
            BModal
        },
        data: () => {
            return {
                modalTitle: 'EDICIÓN DE LOGO',
                loadingData: false,
                loadingLogo: false,
                markets: [],
                clientData: null,

                target: '',
                marketSelection: '0',
                searchStatus: '1',
                clients_id: null,
                clientName: '',
                clients: {},
                table: {
                    columns: [
                        'market_name',
                        'code',
                        'name',
                        'weekly',
                        'day_before',
                        'daily',
                        'survey',
                        'whatsapp',
                        'logo',
                        'status'
                    ]
                }
            }
        },
        mounted () {
        },
        created () {
            API.get(
                '/markets/selectbox?token=' + window.localStorage.getItem('access_token')
            )
                .then(result => {
                    // console.log();
                    this.markets = result.data.data
                })
                .catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.clients'),
                        text: this.$t('clients.error.messages.information_error')
                    })
                }) // SE OBTIENE LOS MERCADOS DISPONIBLES
            this.$parent.$parent.$on('langChange', payload => {
                this.tableUpdate()
            })
            localStorage.setItem('status', '1') // SOLO CLIENTES DE ESTADO ACTIVADO
            localStorage.setItem('market', '0') //TODOS LOS MERCADOS
        },
        computed: {
            tableOptions: function () {
                return {
                    headings: {
                        market_name: 'Mercado',
                        code: 'Código',
                        name: 'Nombre',
                        weekly: 'Mail 7d',
                        day_before: 'Mail 24h',
                        daily: 'Mail diario',
                        survey: 'Encuesta',
                        whatsapp: 'Whatsapp',
                        logo: 'Logo',
                        status: 'Estado'
                    },
                    sortable: ['name'],
                    filterable: ['name'],
                    perPageValues: [],
                    requestFunction: function (data) {
                        //   console.log($.loadingData);
                        return API.get(
                            '/masi_mailing?token=' +
                            window.localStorage.getItem('access_token') +
                            '&lang=' +
                            localStorage.getItem('lang') +
                            '&status=' +
                            localStorage.getItem('status') +
                            '&market=' +
                            localStorage.getItem('market'),
                            {
                                params: data
                            }
                        )
                            .then(result => {
                                //   console.log(result);
                                return result.data
                            })
                            .catch(() => {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.clients'),
                                    text: this.$t('clients.error.messages.information_error')
                                })
                            })
                            .finally(() => {
                                this.loadingData = false
                            })
                    },
                    texts: {
                        count: this.$t('global.table.count'),
                        first: this.$t('global.table.first'),
                        last: this.$t('global.table.last'),
                        filter: this.$t('global.table.filter'),
                        filterPlaceholder: this.$t('global.table.filterPlaceholder'),
                        limit: this.$t('global.table.limit'),
                        page: this.$t('global.table.page'),
                        noResults: this.$t('global.table.noResults'),
                        filterBy: this.$t('global.table.filterBy'),
                        loading: '',
                        defaultOption: this.$t('global.table.defaultOption'),
                        columns: this.$t('global.table.columns')
                    }
                }
            }
        },
        methods: {
            restoreDefault () {
                this.$refs['logoRestore'].show()
                //   this.clientData.logo = "MASI.png";
            },
            openExplorer () {
                this.$refs['logoSelection'].click()
            },
            changeLogo () {
                let file = this.$refs['logoSelection'].files[0]

                if (file.size > 6 * 1000 * 1000) {
                    //máximo 6MB
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Cambio de imagen - Fallido',
                        text: 'El tamaño del archivo supera el máximo permitido (1MB).'
                    })
                } else {
                    this.loadingLogo = true
                    var formData = new FormData()

                    formData.append('imagefile', file)
                    formData.set('client', this.clientData.code)
                    formData.set('imagename', this.clientData.code)
                    formData.set('imagefolder', 'aurora/logos')
                    API.post('/upload/clientlogo', formData)
                        .then(response => {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Cambio de imagen - Correcto',
                                text: 'Se ha actualizado correctamente el logo del cliente.'
                            })
                            this.clientData.logo = response.data.logo
                            this.tableUpdate()
                        })
                        .catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Cambio de imagen - Fallido',
                                text: 'Ocurrieron erroes al intentar cargar el archivo.'
                            })
                        })
                        .finally(() => {
                            this.loadingLogo = false
                        })
                }
            },
            hideModal () {
                this.$refs['logoEditModal'].hide()
            },
            showModal (option) {
                this.clientData = option

                this.$refs['logoEditModal'].show()
            },
            tableUpdate () {
                this.$refs.table.refresh()
            },
            filterByMarket: function () {
                localStorage.setItem('market', this.marketSelection)
                this.tableUpdate()
            },
            switchConfig: function (clients_id, status, config) {
                let status_config = 1
                if (status === true) {
                    status_config = 0
                }
                console.log(status_config)
                API.get(
                    '/masi_mailing/update/' +
                    clients_id +
                    '?token=' +
                    window.localStorage.getItem('access_token') +
                    '&status=' +
                    status_config +
                    '&config=' +
                    config
                ).then(result => {
                    if (result.data.success === true) {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Configuración de cliente',
                            text: 'Se ha realizado correctamente la actualización'
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.clients'),
                            text: this.$t('clients.error.messages.information_error')
                        })
                    }
                })
            }
        }
    }
</script>

<style>
    .modal-header {
        background-color: #990b0f;
        color: #fff !important;
    }

    .modal-header .close {
        color: #fff !important;
    }

    .client-details {
        padding: 10px;
    }

    .image-container {
        background-color: #f2f2f2;
        padding: 10px;
        position: relative;
        width: 100%;
        height: 200px;
        display: flex;
        align-items: center;
        border-radius: 0.3rem;
    }

    .loading-table-container {
        width: 100%;
        height: 100%;
        position: absolute;
        max-height: calc(100% - 45px);
        background-color: rgba(240, 243, 245, 0.651);
        z-index: 999;
    }

    .image-container .loading-container {
        margin-left: -10px;
        position: absolute;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.3);
        height: 100%;
        display: flex;
        align-items: center;
        text-align: center;
        z-index: 9998;
    }

    .image-container .loading-container img {
        z-index: 9999;
    }

    .image-container .client-img {
        max-height: 100%;
    }

    .progress-bar {
        color: white;
        -webkit-border-radius: 0.25rem;
        -moz-border-radius: 0.25rem;
        border-radius: 0.25rem;
    }

    .table td {
        padding-left: 0.8em;
    }

    .custom-control.custom-switch {
        text-align: center;
    }

    .dropdown.btn-group {
        margin: 4px auto !important;
    }

    #modalDialog .modal-header {
        display: none !important;
    }
</style>

