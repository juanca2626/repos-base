@extends ('layouts.app')
@section('content')
    <section>
        <div class="container mt-5">
            <div class="row mb-5">
                <div class="col-12">
                    <h2>MASI: CONFIGURACIÓN</h2>
                </div>
            </div>
            <b-tabs content-class="mt-3">
                <b-tab title="NOTIFICACIÓN" active>
                    <!-- div class="row mt-5 mb-5">
                        <div class="col-12">
                            <h2>NOTIFICACIÓN</h2>
                        </div>
                    </div -->

                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="b-form-group form-group">
                                <div class="form-group">
                                    <label for="schedule">Hora</label>
                                    <input type="time" v-model="time" v-bind:disabled="loadingUpdate"
                                        class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" v-if="!flag_schedule">
                            <div class="b-form-group form-group">
                                <div class="form-group">
                                    <label for="datee_schedule">Fecha máxima</label>
                                    <input type="date" v-model="date_schedule" v-bind:disabled="loadingUpdate"
                                        class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="button" v-on:click="updateTimeSchedule()"
                                class="btn btn-primary">Actualizar</button>
                        </div>
                    </div>
                    
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1"
                            v-model="flag_schedule">
                        <label class="form-check-label" for="inlineCheckbox1">Mantener la configuración por tiempo indefinido</label>
                    </div>
                </b-tab>
                <b-tab title="CORREOS">
                    <!-- div class="row mt-5 mb-5">
                        <div class="col-12">
                            <h2>CORREOS</h2>
                        </div>
                    </div -->

                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="b-form-group form-group">
                                <div class="form-group">
                                    <label for="selectMarket">Mercado</label>
                                    <select @change="filterByMarket" ref="selectMarket" class="form-control" name="selectMarket"
                                            id="selectMarket" v-model="marketSelection">
                                        <option value="-1" disabled>Seleccione un mercado</option>
                                        <option value="0">TODOS</option>
                                        <option :value="market.value" v-for="market in markets" v-bind:key="market.value">
                                            @{{market.text}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <b-overlay :show="loadingData || loadingUpdate" :opacity="0.42" rounded="lg">
                                <div>

                                    <v-server-table :columns="table.columns" :options="tableOptions"
                                                    v-on:loading="loadingData = !loadingData"
                                                    v-on:loaded="loadingData = !loadingData" ref="table">
                                        <div class="table-state" slot="weekly" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.weekly" :id="'checkbox_weekly_'+props.row.id"
                                                            :name="'checkbox_weekly_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.weekly, 'weekly')" switch
                                                            size="lg">
                                            </b-form-checkbox>
                                        </div>
                                        <div class="table-state" slot="day_before" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.day_before" :id="'checkbox_day_before_'+props.row.id"
                                                            :name="'checkbox_day_before_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.day_before, 'day_before')"
                                                            switch>
                                            </b-form-checkbox>
                                        </div>
                                        <div class="table-state" slot="daily" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.daily" :id="'checkbox_daily_'+props.row.id"
                                                            :name="'checkbox_daily_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.daily, 'daily')" switch>
                                            </b-form-checkbox>
                                        </div>
                                        <div class="table-state" slot="survey" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.survey" :id="'checkbox_survey_'+props.row.id"
                                                            :name="'checkbox_survey_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.survey, 'survey')" switch>
                                            </b-form-checkbox>
                                        </div>
                                        <div class="table-state" slot="whatsapp" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.whatsapp" :id="'checkbox_whatsapp_'+props.row.id"
                                                            :name="'checkbox_whatsapp_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.whatsapp, 'whatsapp')" switch>
                                            </b-form-checkbox>
                                        </div>
                                        <div class="table-state text-center logo-td" slot="logo" slot-scope="props">
                                            <img class="logo-preview"
                                                :src="props.row.logo"
                                                alt="logo-image" @click="showLogoModal(props.row)">

                                        </div>
                                        <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                                            <b-form-checkbox v-model="props.row.status" :id="'checkbox_status_'+props.row.id"
                                                            :name="'checkbox_status_'+props.row.id"
                                                            @change="switchConfig(props.row.id,props.row.status, 'status')" switch>
                                            </b-form-checkbox>
                                        </div>
                                    </v-server-table>
                                </div>
                            </b-overlay>
                        </div>
                    </div>
                </b-tab>
                <b-tab title="FILES">
                    <!-- div class="row mt-5 mb-5">
                        <div class="col-12">
                            <h2>CORREOS</h2>
                        </div>
                    </div -->

                    <div class="alert alert-warning">
                        <b>Los files que se encuentran aquí NO serán notificados por MASI (E-mail, Whatsapp)</b>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="b-form-group form-group">
                                <div class="form-group">
                                    <label for="file">File</label>
                                    <input type="text" v-on:keypress.enter="saveFile()" 
                                        v-bind:readonly="loadingUpdate"
                                        placeholder="Presionar enter para agregar el file" 
                                        class="form-control" v-model="file" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <b-overlay :show="loadingData || loadingUpdate" :opacity="0.42" rounded="lg">
                                <div>
                                    <v-server-table :columns="table.columns_files" :options="tableOptions_files"
                                        v-on:loading="loadingData = !loadingData"
                                        v-on:loaded="loadingData = !loadingData" ref="table_files">
                                        <div class="table-nroref text-center" slot="nroref" slot-scope="props">
                                            <div class="d-flex">
                                                <div class="col">
                                                    <b>@{{ props.row.nroref }}</b>
                                                </div>
                                                <div class="col-auto">
                                                    <button type="button" v-on:click="deleteFile(props.row.id)" class="btn btn-danger">
                                                        <i class="icon-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </v-server-table>
                                </div>
                            </b-overlay>
                        </div>
                    </div>
                </b-tab>
            </b-tabs>
        </div>
    </section>
    <b-modal :title="modalTitle" centered ref="logoEditModal" size="md" :no-close-on-backdrop="true"
             :no-close-on-esc="true" :hide-footer="false">
        <div class="client-details row align-items-center" v-if="clientData">
            <div class="col">
                <h5>
                    Mercado:
                    <br/>
                    @{{clientData.market_name}}
                </h5>
            </div>
            <div class="col">
                <h5>
                    Código:
                    <br/>
                    @{{clientData.code}}
                </h5>
            </div>
            <div class="col">
                <h5>
                    Cliente:
                    <br/>
                    @{{clientData.name}}
                </h5>
            </div>
        </div>
        <div class="image-container" v-if="clientData">
            <div class="loading-container" v-show="loadingLogo">
                <img alt="loading" height="51px" src="/images/loading.svg" class="mx-auto d-block"/>
            </div>
            <img class="client-img img-fluid mx-auto d-block"
                 :src="clientData.logo"
                 alt="logo"/>
        </div>

        <div slot="modal-footer">
            <input type="file" class="btn btn-success" @change="changeLogo()" ref="logoSelection" id="logoSelection"
                   accept="image/jpeg, image/png" style="display: none"/>
            <button @click="openExplorer()" class="btn btn-primary mr-1" :disabled="loadingLogo">Cambiar imagen</button>
            <!-- <button @click="restoreDefault()" class="btn btn-success mr-1">Restaurar</button> -->
            <button @click="hideLogoModal()" class="btn btn-danger" :disabled="loadingLogo">Cancelar</button>
        </div>
    </b-modal>
@endsection
@section('css')
    <style>
        .logo-td {
            min-width: 90px;
        }

        .pagination {
            display: -webkit-box !important;
            display: flex !important;
        }

        .pagination .page-item {
            float: none;
            margin-bottom: 10px;
        }

        .table thead tr th {
            background: #890005 !important;
            color: #fff;
            border-color: #890005 !important;
        }

        .table thead tr {
            height: 50px;
        }

        .table thead tr th {
            text-align: center;
        }

        .VueTables .row .col-md-12 {
            padding-left: 0 !important;
        }

        .VueTables > .row {
            margin: 0!important;
        }

        .select_lang,
        .cliente-menu {
            display: none;

        }

        .custom-switch .custom-control-label::before {
            left: -2.25em;
            width: 1.75em;
            pointer-events: all;
            border-radius: 0.5em;
        }

        .custom-control-label::before {
            position: absolute;
            top: 0.25em;
            left: -1.5em;
            display: block;
            width: 1em;
            height: 1em;
            pointer-events: none;
            content: "";
            background-color: #fff;
            border: #adb5bd solid 1px;
        }

        .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
            background-color: #fff;
            -webkit-transform: translateX(0.75em);
            transform: translateX(0.75em);
        }

        .custom-switch .custom-control-label::after {
            top: calc(0.25em + 2px);
            left: calc(-2.25em + 2px);
            width: calc(1em - 4px);
            height: calc(1em - 4px);
            background-color: #adb5bd;
            border-radius: 0.5em;
            -webkit-transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-transform 0.15s ease-in-out;
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-transform 0.15s ease-in-out;
            transition: transform 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            transition: transform 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-transform 0.15s ease-in-out;
        }

        .custom-control-label::after {
            position: absolute;
            top: 0.25em;
            left: -1.5em;
            display: block;
            width: 1em;
            height: 1em;
            content: "";
            background: no-repeat 50%/50% 50%;
        }

        #selectMarket {
            font-size: 14px;
            height: 35px;
        }

        .VueTables__search-field {
            display: contents;
        }

        .form-inline label {
            margin-right: 1rem;

        }

        .form-inline label,
        .form-inline input {
            font-size: 14px;
            height: 35px;
        }

        .table-responsive {
            position: relative;
        }

        .table {
            padding: 0;
            margin: 0;
        }

        .logo-preview {
            cursor: pointer;
            width: 100%;
            max-width: 70px;
        }

        .modal-title {
            font-size: 1.5rem;
        }

        .modal-footer div {
            width: 100%;
            text-align: right;
            margin: 0;
            padding: 1rem;
        }

        .table td {
            padding-left: 0.8em;
            vertical-align: middle;
            font-size: 14px;
            /* max-width: 50px; */
        }

        .custom-control.custom-switch {
            text-align: center;
        }


        .btn {
            border-radius: 0px !important;
            font-size: 14px !important;
            border: none;
            line-height: 0px;
            width: auto;
            height: 40px;
        }

        .modal-header {
            background-color: #990b0f;
            color: #fff !important;
            border-top-left-radius: 5px !important;
            border-top-right-radius: 5px !important;
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
            align-items: center;
            vertical-align: middle;
            display: flex;
            min-height: 51px;
            position: absolute;
            right: 0;
            /* top: 35px; */
            text-align: center;
            /* left: 0; */
            /* background-color: rgba(240, 243, 245, 0.651); */
            /* z-index: 999; */
        }

        .loading-table-container h5 {
            width: 100%;
            font-size: 14px;
        }

        .image-container .loading-container {
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

        .dropdown.btn-group {
            margin: 4px auto !important;
        }

        .modal .close {
            line-height: 16px !important;
            margin-bottom: 0px !important;
            font-size: 20px;
        }

        #modalDialog .modal-header {
            display: none !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .modal-dialog .modal-body {
            border-radius: 5px 5px 5px 5px !important;
            padding: 1rem !important;
        }
    </style>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                code: localStorage.getItem('code'),
                modalTitle: 'EDICIÓN DE LOGO',
                loadingData: false,
                loadingUpdate: false,
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
                    ],
                    columns_files: [
                        'nroref',
                    ]
                },
                tableOptions: {
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
                    sortIcon: {
                        base: 'fas cursor-pointer',
                        is: 'fa-sort',
                        up: 'fa-sort-up',
                        down: 'fa-sort-down'
                    },
                    sortable: ['market_name', 'code', 'name'],
                    filterable: ['name'],
                    perPageValues: [],
                    requestFunction: function (data) {
                        return axios.get(baseExternalURL + 'api/masi_mailing' +
                            '?lang=' +
                            localStorage.getItem('lang') +
                            '&status=' +
                            localStorage.getItem('status') +
                            '&market=' +
                            localStorage.getItem('market'), {
                            params: data
                        })
                            .then((response) => {

                                return response
                            })
                            .catch(() => {
                                this.$toast.error('Ocurrieron errores al conseguir la información de los clientes', {
                                    position: 'top-right'
                                })
                            })
                    },
                    texts: {
                        actions: 'Acciones',
                        edit: 'Editar',
                        count: 'Mostrando {from} de {to} de {count} registros|{count} registros|Un registro',
                        first: 'Primero',
                        last: 'Último',
                        filter: 'Filtro:',
                        filterPlaceholder: 'Criterio de búsqueda',
                        limit: 'Registros:',
                        page: 'Paginas:',
                        noResults: 'No hay coincidencias',
                        filterBy: 'Filtrado por {column}',
                        loading: '',
                        defaultOption: 'Seleccionar {column}',
                        columns: 'Columnas',
                    }
                },
                tableOptions_files: {
                    pagination:{
                        virtual: false
                    },
                    filterable: false, // Desactiva el filtro global
                    sortable: [], // Desactiva el ordenamiento
                    headings: {
                        nroref: 'File',
                        actions: 'Acciones'
                    },
                    requestFunction: function (data) {
                        return axios.get(baseExternalURL + 'api/masi_mailing_files')
                            .then((response) => {
                                return response;
                            })
                            .catch(() => {
                                this.$toast.error('Ocurrieron errores al conseguir la información de los files', {
                                    position: 'top-right'
                                })
                            })
                    },
                    texts: {
                        actions: 'Acciones',
                        edit: 'Editar',
                        count: 'Mostrando {from} de {to} de {count} registros|{count} registros|Un registro',
                        first: 'Primero',
                        last: 'Último',
                        filter: 'Filtro:',
                        filterPlaceholder: 'Criterio de búsqueda',
                        limit: 'Registros:',
                        page: 'Paginas:',
                        noResults: 'No hay coincidencias',
                        filterBy: 'Filtrado por {column}',
                        loading: '',
                        defaultOption: 'Seleccionar {column}',
                        columns: 'Columnas',
                    }
                },
                time: '',
                flag_schedule: false,
                date_schedule: '',
                file: '',
            },
            mounted () {
            },
            created () {

                this.code = localStorage.getItem('code')
                axios.get(baseExternalURL + 'api/markets/selectbox')
                    .then(result => {

                        this.markets = result.data.data
                    })
                    .catch(() => {
                        this.$toast.error('Ocurrieron errores al conseguir la información de los mercados', {
                            position: 'top-right'
                        })
                    }) // SE OBTIENE LOS MERCADOS DISPONIBLES
                localStorage.setItem('status', '1') // SOLO CLIENTES DE ESTADO ACTIVADO
                localStorage.setItem('market', '0') //TODOS LOS MERCADOS

                this.searchTimeSchedule()
            },
            computed: {},
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
                        this.$toast.error('El tamaño del archivo supera el máximo permitido (1MB).', {
                            position: 'top-right'
                        })
                    } else {
                        this.loadingLogo = true
                        var formData = new FormData()

                        formData.append('imagefile', file)
                        formData.set('client', this.clientData.code)
                        formData.set('imagename', this.clientData.code)
                        formData.set('imagefolder', 'aurora/logos')
                        axios.post(baseExternalURL + 'api/upload/clientlogo', formData)
                            .then(response => {
                                this.$toast.success('Se ha actualizado correctamente el logo del cliente.', {
                                    position: 'top-right'
                                })

                                this.clientData.logo = response.data.logo
                                this.tableUpdate()
                                // this.hideLogoModal();

                            })
                            .catch(() => {
                                this.$toast.error('Ocurrieron erroes al intentar cargar el archivo.', {
                                    position: 'top-right'
                                })
                            })
                            .finally(() => {
                                this.loadingLogo = false
                            })
                    }
                    //   this.$refs["logoFile"].click();

                    //   this.clientData.logo = "5MART.jpg";
                },
                hideLogoModal () {
                    this.$refs['logoEditModal'].hide()
                },
                showLogoModal (option) {
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
                    this.loadingUpdate = true
                    if (status == false || status == 'false') {
                        status = '0'
                    } else if (status == true || status == 'true') {
                        status = '1'
                    }
                    axios.get(baseExternalURL + 'api/masi_mailing/update/' +
                        clients_id +
                        '?status=' +
                        status +
                        '&config=' +
                        config
                    ).then(result => {
                        if (result.data.success === true) {
                            this.$toast.success('Se ha realizado correctamente la actualización', {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error('Ocurrieron erroes al intentar actualizar la información del cliente.', {
                                position: 'top-right'
                            })
                        }
                    }).finally(() => {
                        this.loadingUpdate = false
                    })
                },
                updateTimeSchedule: function () {
                    this.loadingUpdate = true
                    axios.post(baseExternalURL + 'api/masi/update_time_notification', {
                        time: this.time,
                        date: this.date_schedule,
                        flag_schedule: this.flag_schedule
                    }).then(result => {
                        this.loadingUpdate = false
                        if (result.data.success === true) {
                            this.$toast.success('Se ha realizado correctamente la actualización', {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error('Ocurrieron erroes al intentar actualizar la información del cliente.', {
                                position: 'top-right'
                            })
                        }
                    }).finally(() => {
                        this.loadingUpdate = false
                    })
                },
                searchTimeSchedule: function () {
                    axios.post(baseExternalURL + 'api/masi/search_time_notification', {
                        time: this.time
                    }).then(result => {
                        if(result.data.success)
                        {
                            this.time = result.data.time
                            this.date_schedule = result.data.date
                            this.flag_schedule = (result.data.flag_schedule == 1) ? true : false
                        }
                    })
                    .catch((error) => {
                        console.log(error)
                    })
                },
                deleteFile: function (file_id) {
                    this.loadingUpdate = true
                    axios.delete(baseExternalURL + 'api/masi_mailing_files/' + file_id).then(result => {
                        if (result.data.success === true) {
                            this.$refs.table_files.refresh()
                            this.file = ''
                            this.$toast.success('Se ha eliminado correctamente.', {
                                position: 'top-right'
                            })


                        } else {
                            this.$toast.error('Ocurrió un error.', {
                                position: 'top-right'
                            })
                        }
                    }).finally(() => {
                        this.loadingUpdate = false
                    })
                },
                saveFile: function () {
                    this.loadingUpdate = true
                    axios.post(baseExternalURL + 'api/masi_mailing_files', {
                        nroref: this.file,
                    }).then(result => {
                        this.loadingUpdate = false
                        if (result.data.success === true) {
                            this.$refs.table_files.refresh()
                            this.file = ''
                            this.$toast.success('Se ha agregado correctamente.', {
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error('Ocurrió un error.', {
                                position: 'top-right'
                            })
                        }
                    }).finally(() => {
                        this.loadingUpdate = false
                    })
                }
            }
        })
    </script>
@endsection
