@extends ('layouts.app')
@section('content')
    <masi-statistics-component />
@endsection
@section('css')
    <style>
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0;
        }

        .border-1 {
            border-bottom: 1px solid black !important;
        }

        th {
            text-transform: uppercase;
        }

        .toast {
            flex-basis: unset !important;
        }

        .btn-download {
            font-size: 1.3rem;
            max-width: 100%;
            width: 180px;
            padding: 0;
            margin: 0;
            line-height: 1;
            height: 35px;
        }

        .btn-primary.disabled,
        .btn-primary:disabled {
            background-color: #890005 !important;
            border-color: #890005 !important;
            cursor: not-allowed;
        }

        @keyframes bar-loader-1 {
            0% {
                top: 18.18px;
                height: 64.64px
            }

            50% {
                top: 30.3px;
                height: 40.4px
            }

            100% {
                top: 30.3px;
                height: 40.4px
            }
        }

        @keyframes bar-loader-2 {
            0% {
                top: 21.209999999999997px;
                height: 58.580000000000005px
            }

            50% {
                top: 30.3px;
                height: 40.4px
            }

            100% {
                top: 30.3px;
                height: 40.4px
            }
        }

        @keyframes bar-loader-3 {
            0% {
                top: 24.240000000000002px;
                height: 52.52px
            }

            50% {
                top: 30.3px;
                height: 40.4px
            }

            100% {
                top: 30.3px;
                height: 40.4px
            }
        }

        .bar-loader div {
            position: absolute;
            width: 16.16px
        }

        .bar-loader div:nth-child(1) {
            left: 17.17px;
            background: #890005;
            animation: bar-loader-1 1.5873015873015872s cubic-bezier(0, 0.5, 0.5, 1) infinite;
            animation-delay: -0.3174603174603175s
        }

        .bar-loader div:nth-child(2) {
            left: 42.42px;
            background: #890005;
            animation: bar-loader-2 1.5873015873015872s cubic-bezier(0, 0.5, 0.5, 1) infinite;
            animation-delay: -0.15873015873015875s
        }

        .bar-loader div:nth-child(3) {
            left: 67.67px;
            background: #890005;
            animation: bar-loader-3 1.5873015873015872s cubic-bezier(0, 0.5, 0.5, 1) infinite;
            animation-delay: undefined
        }

        .loading-spinner {
            width: 101px;
            height: 101px;
            display: inline-block;
            overflow: hidden;
            background: none;
        }

        .bar-loader {
            width: 100%;
            height: 100%;
            position: relative;
            transform: translateZ(0) scale(1);
            backface-visibility: hidden;
            transform-origin: 0 0;
            /* see note above */
        }

        .bar-loader div {
            box-sizing: content-box;
        }

        .loader-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: absolute;
            top: 150px;
            width: 100%;
        }

        .loader-container .loading-message {
            width: 100%;
        }

        .loader-container .loading-message p {
            max-width: 70%;
            margin: 0 auto;
            display: block;
            text-align: center;
            font-size: 16px;
        }

        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 3s;
        }

        .container {
            padding: 4rem 1rem !important;
        }

        .column-totals {
            background: lightgray;
        }

        .statistics {
            font-size: 14px;
            min-height: 352px;
            padding: 3rem !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6 {
            line-height: 1.2;
        }

        input#fileSearch {
            padding-right: 50px;
        }

        .btn-clear-file {
            width: 50px;
            position: absolute;
            right: 60px;
            height: 40px;
            font-style: normal;
            font-weight: bold;
            font-size: 1.8rem;
            outline: none;
        }

        .btn-clear-file:focus {
            border: none;
            box-shadow: none;
        }

        .file-search {
            display: flex;
        }

        .btn-search {
            max-width: 50px;
        }

        .b-none {
            border: none !important
        }

        .tabs .nav-item {
            width: 25% !important;
            min-width: 200px !important;
        }

        .tabs-container {
            position: relative;
        }

        .tabs-container .tabs {
            padding: 0 !important;
        }

        .tabs-container .tabs .nav-tabs {
            border: none !important;
        }

        .tabs-container .tabs .tab-content {
            margin: 0 !important;
            border-color: #e9ecef !important;
            border: 1px solid #890005 !important;
        }

        .tabs-container .tabs .nav-link {
            padding: 20px !important;
            font-size: 14px;
            text-align: center !important;
            border-radius: 5px 5px 0px 0px !important;
            outline: none !important;
        }

        .tabs-container .tabs .nav-link.active {
            background: #ffffff !important;
            color: #890005 !important;
            border-color: #890005 #890005 #fff #890005;
        }

        .tabs-container .tabs .nav-link:not(.active):hover {
            border-bottom-color: #890005;
            color: #890005 !important;
        }

        div.loader {
            display: inline-block;
        }

        h2.statistics-title {
            line-height: 1.5;
            text-align: center;
        }

        .btn-clear,
        .btn-update {
            display: inline-block;
            font-size: 1.5rem;
            padding: 1px;
        }

        .btn-update {
            max-width: 200px;
            width: 50%;
        }

        .btn-clear {
            width: 40%;
            max-width: 100px;
        }

        .divider {
            border-bottom: 1px solid grey;
            width: 100%;
            height: 10px;
            margin: 10px 0;
        }

        .select_lang,
        .cliente-menu {
            display: none;

        }
    </style>
@endsection
@section('js')
    <script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {

            },
            created: function () {

            },
            mounted: function() {

            },
            computed: {

            },
            methods: {

            }
        });
    </script>
@endsection
@section('content-2')
    <section>
        <div class="container p-2 p-md-5" style="position: relative;">
            <div class="alert alert-warning text-justify">
                <strong>Importante:</strong>
                Las estadísticas solo estarán habilitadas a partir del 24 de agosto del 2021, por problemas con nuestro
                proveedor de correo no se podrá visualizar fechas anteriores. Te pedimos las disculpas del caso y te
                informamos que estamos trabajando para resolver este incidente lo antes posible
            </div>
            <h2 class="py-5 d-inline-block">Masi: Reportes Estadísticos</h2>
            <div class="loader ml-4" v-show="loadingFilter">
                <span class="spinner-border" role="status" aria-hidden="true"></span>
            </div>
            <div class="filters pb-5">
                @if (Auth::user()->rol->role_id==1 || Auth::user()->rol->role_id==30 || Auth::user()->rol->role_id==35 || Auth::user()->rol->role_id==3 || Auth::user()->rol->role_id==6 || Auth::user()->rol->role_id==11)
                    <div class="form-row mb-0">
                        <div class="form-group col-md-2 py-2">
                            <label for="selectFileType">Tipo de file</label>
                            <select ref="selectFileType" class="form-control" name="selectFileType" id="selectFileType"
                                    required
                                    v-model="filetypeFilter">
                                <option value="0">TODOS</option>
                                <option value="fits">FITS</option>
                                <option value="grupo">GRUPO</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 py-2">
                            <label for="selectRegion">Región</label>
                            <select ref="selectRegion" class="form-control" name="selectRegion" id="selectRegion"
                                    required
                                    v-model="regionFilter">
                                <option value="0">TODOS</option>
                                <option :value="region.id" v-for="region in regions" v-bind:key="region.id">
                                    @{{region.title}}
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 py-2">
                            <label for="selectCountry">País</label>
                            <select ref="selectCountry" class="form-control" name="selectCountry" id="selectCountry"
                                    required
                                    v-model="countryFilter">
                                <option value="0">TODOS</option>
                                <option :value="country.pais" v-for="country in countries" v-bind:key="country.pais">
                                    @{{country.pais}}
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 py-2">
                            <label for="selectClient">Cliente</label>
                            <select ref="selectClient" class="form-control" name="selectClient" id="selectClient"
                                    required
                                    v-model="clientFilter">
                                <option value="0">TODOS</option>
                                <option :value="client.codigo" v-for="client in clients" v-bind:key="client.codigo">
                                    @{{client.razon}}
                                </option>
                            </select>
                        </div>
                    </div>
                @elseif(Auth::user()->rol->role_id !=1 && Auth::user()->rol->role_id!=30 && Auth::user()->rol->role_id!=35)
                    <input type="hidden" name="selectClient" id="selectClient" v-model="clientFilter">
                @endif
                <div class="form-row mb-0">
                    <div class="form-group col-md-4 py-2">
                        <label for="fromDate">Desde</label>
                        <input type="date" class="form-control" name="fromDate" id="fromDate" v-model="fromDate"
                               :max="getEndDate" min="2019-01-01">
                    </div>
                    <div class="form-group col-md-4 py-2">
                        <label for="toDate">Hasta</label>
                        <input type="date" class="form-control" name="toDate" id="toDate" v-model="toDate"
                               :max="getEndDate"
                               :min="fromDate">
                    </div>
                    <div class="form-group col-md-4 py-2 text-right">
                        <label for="updateData" class="w-100">&nbsp;</label>
                        @if (Auth::user()->rol->role_id==1 || Auth::user()->rol->role_id==30 || Auth::user()->rol->role_id==35 || Auth::user()->rol->role_id==3 || Auth::user()->rol->role_id==6 || Auth::user()->rol->role_id==11)
                            <button type="button" id="updateData" v-on:click="clearFilters"
                                    class="btn btn-primary btn-clear"
                                    :disabled="loadingData">LIMPIAR
                            </button>
                        @endif
                        <button type="button" id="updateData" v-on:click="filterStatistics()"
                                class="btn btn-primary btn-update" :disabled="loadingData">ACTUALIZAR
                        </button>
                    </div>
                    <div class="col-md-12 pb-0 pt-3 px-3 mb-0">
                        <small>El rango de fechas seleccionado hace referencia a la llegada del pasajero.</small>
                        <small v-show="currentTab==2"><br>Para consultas estadísticas de
                            notificaciones, se sugiere realizar consultas con 1 mes de diferencia en el rango de
                            fechas</small></div>
                </div>
                <div class="divider"></div>
                <div class="form-row mb-0">
                    <div class="form-group col-md-4 py-2">
                        <label for="fileSearch" class="mb-3">Filtrar por número de file</label>
                        <div class="file-search">
                            <input :disabled="currentTab==2 || loadingData" type="number" class="form-control"
                                   name="fileSearch" id="fileSearch" v-model="fileSearch">
                            <button :disabled="currentTab==2 || loadingData" type="button" class="btn btn-clear-file"
                                    v-on:click="fileSearch = null" v-show="fileSearch && fileSearch.length>0"><i
                                    class="icon-trash"></i></button>
                            <button :disabled="currentTab==2 || loadingData" type="button"
                                    :disabled="fileSearch==null || fileSearch.length==0 || loadingData"
                                    v-on:click="filterStatistics(true)" id="updateData"
                                    class="btn btn-primary btn-search"><i
                                    class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tabs-container">
                <div class="loader-container" v-show="loadingData">
                    <div class="loading-spinner">
                        <div class="bar-loader">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div v-show="currentTab==2" class="loading-message">
                        <transition name="fade">
                            <p v-if="currentTab==2">Consideraciones: Consultas de mayor de rango de fechas resultan en
                                mayor
                                tiempo de espera de
                                respuesta.</p>
                        </transition>
                    </div>
                </div>
                <b-tabs v-model="currentTab">
                    <b-tab title="GENERAL" active>
                        <div class="statistics py-5 px-3">
                            <div class="statistics-container py-5" v-show="!loadingData">
                                <div class="graph-container row no-gutters" v-if="filesStatistics">
                                    <div class="col-md-12 px-0">
                                        <h2 class="statistics-title mb-1 mt-3 mt-md-0">Files con o sin datos para uso de
                                            MASI</h2>
                                        <h4 class="mb-5 text-center">Tipo:
                                            @{{(fileTypeResult==0)?'TODOS':fileTypeResult.toUpperCase()}}</h4>

                                        <div class="table-responsive">
                                            <table class="datatable table table-bordered b-none text-center">
                                                <thead>
                                                <th>ESTADO</th>
                                                <th>CANTIDAD DE FILES</th>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>SIN DATOS</td>
                                                    <td>@{{filesStatistics.without_data}}
                                                        (@{{getPercentage(filesStatistics.without_data,filesStatistics.total)}})
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>CON DATOS</td>
                                                    <td>@{{filesStatistics.with_data}}
                                                        (@{{getPercentage(filesStatistics.with_data,filesStatistics.total)}})
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0"></td>
                                                    <td class="column-totals">TOTAL: @{{filesStatistics.total}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <p>
                                                - Al seleccionar el tipo de file "TODOS": Se incluyendo todos los tipos
                                                de
                                                files fits, grupo, entre otros.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-right">
                                        <button type="button" class="btn btn-primary btn-download"
                                                v-on:click="downloadFileStatistics()" :disabled="loadingData">Descargar
                                            relación
                                        </button>
                                    </div>
                                </div>
                                <div class="no-data-message" v-else>
                                    <h2 class="statistics-title mb-5 mt-3 mt-md-0">Files con o sin datos para uso de
                                        MASI
                                    </h2>
                                    <h3 class="w-100 text-center">No se encontraron datos en los filtros
                                        seleccionados</h3>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    @if (Auth::user()->rol->role_id==1 || Auth::user()->rol->role_id==30 || Auth::user()->rol->role_id==35 || Auth::user()->rol->role_id==3 || Auth::user()->rol->role_id==6 || Auth::user()->rol->role_id==11)
                        <b-tab title="CHATBOT">
                            <div class="statistics">
                                <div class="statistics-container py-5" v-show="!loadingData">
                                    <div class="graph-container row no-gutters" v-if="channelStatistics.total>0">
                                        <h3 class="text-center mb-5 col-12" v-if="!byFileResults">Total de files con
                                            datos para
                                            uso de MASI:
                                            <br>
                                            <span class="font-weight-bold">Celular:
                                        @{{chatbotStatistics.totalFiles.phonenumber}}</span><br>
                                            <span class="font-weight-bold">Correo:
                                        @{{chatbotStatistics.totalFiles.email}}</span>
                                        </h3>
                                        <div class="col-md-4 px-0">
                                            <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso por
                                                canal</h2>
                                            <pie-chart :data="channelStatistics.data" suffix="%" legend="bottom"
                                                       :colors="['rgb(255, 99, 132)', 'rgb(54, 162, 235)' , 'rgb(255, 205, 86)']"
                                                       :library="{
                                            layout:{
                                                padding:{
                                                }
                                            },
                                            legend:{ display: true},
                                            responsive: true,
                                            tooltips: {
                                                callbacks: {
                                                    label: function(tooltipItem, data) {
                                                        const total = data.datasets[0].data.reduce((a, b) => a + b)
                                                        const label = data.labels[tooltipItem.index];
                                                        const value = data.datasets[0].data[tooltipItem.index];
                                                        let per = value/total * 100;
                                                        per = Math.round(per * 100) / 100
                                                        return `${label}: ${per} %`;
                                                    }
                                                }
                                            }
                                        }"></pie-chart>
                                        </div>
                                        <div class="col-md-8 px-0">
                                            <h2 class="statistics-title mb-5 mt-3 mt-md-0">&nbsp;</h2>
                                            <div class="table-responsive">
                                                <table class="datatable table table-bordered b-none text-center">
                                                    <thead>
                                                    <th>CANAL</th>
                                                    <th v-if="!byFileResults">NÚMERO DE FILES</th>
                                                    <th v-if="byFileResults">USO</th>
                                                    <th v-if="!byFileResults">Porcentaje uso del total de FILES con
                                                        datos
                                                    </th>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="channelData in chatbotStatistics.channels"
                                                        v-bind:key="channelData.CHANNELID">
                                                        <td>@{{channelData.CHANNEL}}</td>
                                                        <td>@{{channelData.NROFILES}}</td>
                                                        <td v-if="!byFileResults">
                                                            @{{getPercentage(channelData.NROFILES,
                                                            chatbotStatistics.totalFiles.totals)}}
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!byFileResults">
                                                        <td>SIN USO</td>
                                                        <td>@{{chatbotStatistics.withoutUse}}
                                                        </td>
                                                        <td>@{{getPercentage(chatbotStatistics.withoutUse,
                                                            chatbotStatistics.totalFiles.totals)}}
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!byFileResults">
                                                        <td class="column-totals">TOTAL</td>
                                                        <td class="column-totals">
                                                            @{{chatbotStatistics.totalFiles.totals}}
                                                        </td>
                                                        <td class="column-totals">100%
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <p class="data-legend" v-if="!byFileResults">
                                                - NÚMERO DE FILES: Representa la cantidad de files únicos que usaron el
                                                canal
                                                del
                                                chatbot.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="no-data-message5" v-else>
                                        <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso por canal</h2>
                                        <h3 class="w-100 text-center">No se encontraron registros de uso en los filtros
                                            seleccionados</h3>
                                    </div>
                                </div>
                                <div class="statistics-container py-5" v-show="!loadingData">
                                    <div class="graph-container row no-gutters" v-if="functionStatistics.total>0">
                                        <div class="col-md-4 px-0">
                                            <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso por
                                                función</h2>
                                            <pie-chart :data="functionStatistics.data" suffix="%" legend="bottom"
                                                       height="330px"
                                                       :colors="['rgb(255, 99, 132)', 'rgb(54, 162, 235)' , 'rgb(255, 205, 86)']"
                                                       :library="{
                                        layout:{
                                            padding:{
                                            }
                                        },
                                        legend:{ display: true},
                                        responsive: true,
                                            tooltips: {
                                                callbacks: {
                                                    label: function(tooltipItem, data) {
                                                        const total = data.datasets[0].data.reduce((a, b) => a + b)
                                                        const label = data.labels[tooltipItem.index];
                                                        const value = data.datasets[0].data[tooltipItem.index];
                                                        let per = value/total * 100;
                                                        per = Math.round(per * 100) / 100
                                                        return `${label}: ${per} %`;
                                                    }
                                                }
                                            }
                                    }"></pie-chart>
                                        </div>
                                        <div class="col-md-8 px-0">
                                            <h2 class="statistics-title mb-5 mt-3 mt-md-0">&nbsp;</h2>
                                            <div class="table-responsive">
                                                <table class="datatable table table-bordered b-none text-center">
                                                    <thead>
                                                    <th>FUNCIÓN</th>
                                                    <th v-if="!byFileResults">NÚMERO DE FILES</th>
                                                    <th v-if="byFileResults">USO</th>
                                                    <th v-if="!byFileResults">CONSULTAS ACUMULADAS</th>
                                                    <th v-if="!byFileResults">Porcentaje de uso de la función</th>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="functionData in chatbotStatistics.functions"
                                                        v-bind:key="functionData.FUNCTIONID">
                                                        <td>@{{functionData.FUNCTION}}</td>
                                                        <td>@{{functionData.NROFILES}}</td>
                                                        <td v-if="!byFileResults">@{{functionData.USES}}</td>
                                                        <td v-if="!byFileResults">
                                                            @{{getPercentage(functionData.USES,
                                                            functionStatistics.total)}}
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!byFileResults">
                                                        <td class="column-totals">TOTAL</td>
                                                        <td class="column-totals">@{{functionStatistics.nrofiles}}</td>
                                                        <td class="column-totals">@{{functionStatistics.total}}</td>
                                                        <td class="column-totals">100%</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <p class="data-legend" v-if="!byFileResults">
                                                - NÚMERO DE FILES: Representa la cantidad de files únicos que usaron la
                                                función
                                                del
                                                chatbot.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="no-data-message" v-else>
                                        <h2 class="statistics-title mb-5 mt-3 mt-md-0">Porcentaje de uso por
                                            función</h2>
                                        <h3 class="w-100 text-center">No se encontraron registros de uso en los filtros
                                            seleccionados</h3>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab title="NOTIFICACIONES">
                            <div class="statistics py-5 px-3">
                                <div class="statistics-container py-5" v-show="!loadingData">
                                    <div class="graph-container row no-gutters" v-if="notificationStatistics">
                                        <h3 class="text-center mb-5 col-12" v-if="!byFileResults">Total de files con
                                            datos para
                                            uso de MASI:
                                            <br>
                                            <span class="font-weight-bold">Celular:
                                        @{{notificationStatistics.totalFiles.phonenumber}}</span><br>
                                            <span class="font-weight-bold">Correo:
                                        @{{notificationStatistics.totalFiles.email}}</span>
                                        </h3>
                                        <div class="col-md-12 px-0">
                                            <h2 class="statistics-title mb-5 mt-3 mt-md-0">Estadísticas de
                                                notificaciones
                                                enviadas por correo y Whatsapp</h2>
                                            <div class="table-responsive">
                                                <table class="datatable table table-bordered b-none text-center">
                                                    <thead>
                                                    <th colspan="2"></th>
                                                    <th>1 SEMANA ANTES</th>
                                                    <th>1 DÍA ANTES</th>
                                                    <th>PRIMER DÍA</th>
                                                    <th>MENSAJE DE DESPEDIDA<br>(ENCUESTA)</th>
                                                    {{-- <th>TOTAL</th> --}}
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td class="border-1">WHATSAPP</td>
                                                        <td class="border-1">ENVIADOS</td>
                                                        <td class="border-1">-</td>
                                                        <td class="border-1">-</td>
                                                        <td class="border-1">@{{notificationStatistics.whatsapp['3']}}
                                                        </td>
                                                        <td class="border-1">-</td>
                                                        {{-- <td class="column-totals">
                                                            @{{notificationStatistics.whatsapp.total}}</td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td rowspan="6" class="align-middle">MAILING</td>
                                                        <td>ENVIADOS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].requests}}</td>
                                                        <td>@{{notificationStatistics.mailing['2'].requests}}</td>
                                                        <td>@{{notificationStatistics.mailing['3'].requests}}</td>
                                                        <td>@{{notificationStatistics.mailing['5'].requests}}</td>
                                                        {{-- <td class="column-totals">
                                                            @{{notificationStatistics.mailing['total'].requests}}</td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td>RECIBIDOS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].delivered}}
                                                            (@{{getPercentage(notificationStatistics.mailing['1'].delivered,
                                                            notificationStatistics.mailing['1'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['2'].delivered}}
                                                            (@{{getPercentage(notificationStatistics.mailing['2'].delivered,
                                                            notificationStatistics.mailing['2'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['3'].delivered}}
                                                            (@{{getPercentage(notificationStatistics.mailing['3'].delivered,
                                                            notificationStatistics.mailing['3'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['5'].delivered}}
                                                            (@{{getPercentage(notificationStatistics.mailing['5'].delivered,
                                                            notificationStatistics.mailing['5'].requests)}})
                                                        </td>
                                                        {{-- <td>@{{notificationStatistics.mailing['total'].delivered}}
                                                        (@{{getPercentage(notificationStatistics.mailing['total'].delivered, notificationStatistics.mailing['total'].requests)}})
                                                        </td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td>ABIERTOS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].opened}}
                                                            (@{{getPercentage(notificationStatistics.mailing['1'].opened,
                                                            notificationStatistics.mailing['1'].delivered)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['2'].opened}}
                                                            (@{{getPercentage(notificationStatistics.mailing['2'].opened,
                                                            notificationStatistics.mailing['2'].delivered)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['3'].opened}}
                                                            (@{{getPercentage(notificationStatistics.mailing['3'].opened,
                                                            notificationStatistics.mailing['3'].delivered)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['5'].opened}}
                                                            (@{{getPercentage(notificationStatistics.mailing['5'].opened,
                                                            notificationStatistics.mailing['5'].delivered)}})
                                                        </td>
                                                        {{-- <td>@{{notificationStatistics.mailing['total'].opened}}
                                                        (@{{getPercentage(notificationStatistics.mailing['total'].opened, notificationStatistics.mailing['total'].delivered)}})
                                                        </td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td>CLICK REALIZADOS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].clicks}}
                                                            (@{{getPercentage(notificationStatistics.mailing['1'].clicks,
                                                            notificationStatistics.mailing['1'].opened)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['2'].clicks}}
                                                            (@{{getPercentage(notificationStatistics.mailing['2'].clicks,
                                                            notificationStatistics.mailing['2'].opened)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['3'].clicks}}
                                                            (@{{getPercentage(notificationStatistics.mailing['3'].clicks,
                                                            notificationStatistics.mailing['3'].opened)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['5'].clicks}}
                                                            (@{{getPercentage(notificationStatistics.mailing['5'].clicks,
                                                            notificationStatistics.mailing['5'].opened)}})
                                                        </td>
                                                        {{-- <td>@{{notificationStatistics.mailing['total'].clicks}}
                                                        (@{{getPercentage(notificationStatistics.mailing['total'].clicks, notificationStatistics.mailing['total'].opened)}})
                                                        </td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td>REBOTADOS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].bounces}}
                                                            (@{{getPercentage(notificationStatistics.mailing['1'].bounces,
                                                            notificationStatistics.mailing['1'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['2'].bounces}}
                                                            (@{{getPercentage(notificationStatistics.mailing['2'].bounces,
                                                            notificationStatistics.mailing['2'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['3'].bounces}}
                                                            (@{{getPercentage(notificationStatistics.mailing['3'].bounces,
                                                            notificationStatistics.mailing['3'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['5'].bounces}}
                                                            (@{{getPercentage(notificationStatistics.mailing['5'].bounces,
                                                            notificationStatistics.mailing['5'].requests)}})
                                                        </td>
                                                        {{-- <td>@{{notificationStatistics.mailing['total'].bounces}}
                                                        (@{{getPercentage(notificationStatistics.mailing['total'].bounces, notificationStatistics.mailing['total'].requests)}})
                                                        </td> --}}
                                                    </tr>
                                                    <tr>
                                                        <td>OTROS</td>
                                                        <td>@{{notificationStatistics.mailing['1'].other}}
                                                            (@{{getPercentage(notificationStatistics.mailing['1'].other,
                                                            notificationStatistics.mailing['1'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['2'].other}}
                                                            (@{{getPercentage(notificationStatistics.mailing['2'].other,
                                                            notificationStatistics.mailing['2'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['3'].other}}
                                                            (@{{getPercentage(notificationStatistics.mailing['3'].other,
                                                            notificationStatistics.mailing['3'].requests)}})
                                                        </td>
                                                        <td>@{{notificationStatistics.mailing['5'].other}}
                                                            (@{{getPercentage(notificationStatistics.mailing['5'].other,
                                                            notificationStatistics.mailing['5'].requests)}})
                                                        </td>
                                                        {{-- <td>@{{notificationStatistics.mailing['total'].other}}
                                                        (@{{getPercentage(notificationStatistics.mailing['total'].other, notificationStatistics.mailing['total'].requests)}})
                                                        </td> --}}
                                                    </tr>
                                                    {{-- <tr>
                                                        <td class="border-0"></td>
                                                        <td class="border-0"></td>
                                                        <td class="border-0"></td>
                                                        <td class="border-0"></td>
                                                        <td class="border-0"></td>
                                                        <td class="column-totals">TOTAL NOTIFICACIONES ENVIADAS:</td>
                                                        <td class="column-totals">@{{notificationStatistics.totalSent}}
                                                    </td>
                                                    </tr> --}}
                                                    </tbody>
                                                </table>
                                                <p>
                                                    - % RECIBIDOS, REBOTADOS Y OTROS: El porcentaje se calculó sobre la
                                                    cantidad
                                                    de correos de
                                                    estado "ENVIADOS".<br>
                                                    - % ABIERTOS: El porcentaje se calculó sobre la cantidad de correos
                                                    de
                                                    estado "RECIBIDOS".<br>
                                                    - % CLICKS: El porcentaje se calculó sobre la cantidad de correos de
                                                    estado
                                                    "ABIERTOS".<br>
                                                    - ESTADO OTROS: Refiere a aquellos que no se ha enviado
                                                    correctamente el
                                                    correo, por error tipográfico o <br>
                                                    respuesta fallida de la plataforma.<br>
                                                    - CLICKS y ABIERTOS: En el caso de que la cantidad de "ABIERTOS" sea
                                                    menor o
                                                    nula en comparación al a cantidad de<br>
                                                    "CLICKS", puede deberse a que el usuario abrió un adjunto del correo
                                                    desde
                                                    la vista previa en el listado de correos.<br>
                                                    - Porcentaje ND: Refiere a "No determinado", debido a que el cálculo
                                                    porcentaje se ha intentado sobre una cantidad nula.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="no-data-message" v-else>
                                        <h2 class="statistics-title mb-5 mt-3 mt-md-0">Estadísticas de notificaciones
                                            enviadas
                                            por correo y Whatsapp</h2>
                                        <h3 class="w-100 text-center">No se encontraron datos en los filtros
                                            seleccionados</h3>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                    @endif
                </b-tabs>
            </div>

        </div>
    </section>
@endsection
@section('js-2')
    <script>
        new Vue({
            el: '#app',
            data: {
                DAYS_AGO: 21, // Constante para el filtro inicial de consulta
                filetypeFilter: 0,
                fileTypeResult: null,
                regionFilter: 0,
                clientFilter: '{{(Auth::user()->rol->role_id!=1 && Auth::user()->rol->role_id!=30 && Auth::user()->rol->role_id !=35)?Auth::user()->code:0}}',
                countryFilter: 0,
                loadingData: false,
                loadingFilter: false,
                regions: [],
                clients: [],
                fileSearch: null,
                countries: [],
                fromDate: null,
                toDate: null,
                chatbotStatistics: {},
                channelStatistics: {},
                functionStatistics: {},
                filesStatistics: null,
                notificationStatistics: null,
                currentTab: 0,
                byFileResults: false
            },
            mounted () {
            },
            watch: {
                currentTab: function (value) {
                    this.filetypeFilter = 0
                    this.fileSearch = ''
                    this.filterStatistics()
                },
                regionFilter: function (value) {
                    this.loadingFilter = true
                    this.countryFilter = 0
                    this.clientFilter = 0
                    if (value == 0) {
                        this.countries = []
                        this.clients = []
                        this.loadingFilter = false
                    } else {
                        axios.get(
                            baseExternalURL + `api/masi_statistics/regions/${value}/countries`).then((result) => {
                            this.countries = result.data.data
                            console.log(this.countries)
                        })
                            .catch((error) => {
                                console.log('ERROR COUNTRY SELECTBOX', error)
                                this.$toast.error('Ocurrieron errores al conseguir la información de los países', {
                                    position: 'top-right'
                                })
                            }).finally(() => {this.loadingFilter = false})
                    }
                },
                countryFilter: function (value) {
                    this.loadingFilter = true
                    this.clientFilter = 0
                    if (value == 0) {
                        this.clients = []
                        this.loadingFilter = false
                    } else {
                        axios.get(
                            baseExternalURL + `api/masi_statistics/regions/${this.regionFilter}/countries/${value}/clients`)
                            .then((result) => {
                                this.clients = result.data.data
                            })
                            .catch((error) => {
                                console.log('ERROR COUNTRY SELECTBOX', error)
                                this.$toast.error('Ocurrieron errores al conseguir la información de los clientes', {
                                    position: 'top-right'
                                })
                            }).finally(() => {this.loadingFilter = false})
                    }

                }
            },
            created () {
                let self = this
                let loadingRegions = false
                let loadingStatistics = false
                self.fromDate = moment().subtract(self.DAYS_AGO, 'days').format('YYYY-MM-DD')
                self.toDate = moment().format('YYYY-MM-DD')
                self.loadingData = true

                this.getStatistics(false, 'files')

                axios.get(baseExternalURL + 'api/masi_statistics/regions', {
                    lang: self.lang
                }).then((result) => {
                    self.regions = result.data.regions
                }).catch((error) => {
                    console.log('ERROR REGIONS SELECTBOX', error)
                    self.$toast.error('Ocurrieron errores al conseguir la información de las regiones', {
                        position: 'top-right'
                    })
                }).finally(() => {
                    self.loadingData = false
                })
            },
            computed: {
                console: () => console,
                window: () => window,
                getEndDate: () => {
                    return moment().format('YYYY-MM-DD')
                }
            },
            methods: {
                downloadFileStatistics () {
                    self = this
                    self.loadingData = true
                    let params = {
                        'fileType': (self.filetypeFilter == 0) ? '' : self.filetypeFilter,
                        'fromDate': self.formatDate(self.fromDate),
                        'toDate': self.formatDate(self.toDate),
                        'regionCode': (self.regionFilter == 0) ? '' : self.regionFilter,
                        'countryCode': (self.countryFilter == 0) ? '' : self.countryFilter,
                        'clientCode': (self.clientFilter == 0) ? '' : self.clientFilter
                    }
                    axios.get(baseExternalURL + 'api/masi_statistics/files/export', {
                        params: params,
                        responseType: 'blob',
                    }).then(function (response) {
                        var fileURL = window.URL.createObjectURL(new Blob([response.data]))
                        var fileLink = document.createElement('a')
                        fileLink.href = fileURL
                        fileLink.setAttribute('download', 'Relación_de_files_contacto.xlsx')
                        document.body.appendChild(fileLink)
                        fileLink.click()
                    })
                        .catch(function (error) {
                            console.log(error)
                            self.$toast.error('Ocurrieron errores al tratar de exportar los datos estadísticos', {
                                position: 'top-right'
                            })
                        }).finally(() => {
                        self.loadingData = false
                    })
                },
                clearFilters () {
                    this.fromDate = ''
                    this.toDate = ''
                    this.regionFilter = 0
                    this.countryFilter = 0
                    this.clientFilter = 0
                },
                formatResponse (res, resultType) {
                    var self = this
                    if (resultType == 'chatbot') {
                        let resultData = {
                            'totalFiles': {
                                'whatsapp': 0,
                                'email': 0
                            },
                            'channels': [
                                {
                                    'CHANNELID': 'X02',
                                    'CHANNEL': 'WEB',
                                    'NROFILES': 0
                                },
                                {
                                    'CHANNELID': 'X01',
                                    'CHANNEL': 'FACEBOOK',
                                    'NROFILES': 0
                                }
                            ], 'functions': [
                                {
                                    'FUNCTIONID': 'C01',
                                    'FUNCTION': 'ITINERARIO',
                                    'NROFILES': 0,
                                    'USES': 0
                                },
                                {
                                    'FUNCTIONID': 'C02',
                                    'FUNCTION': 'PROBLEMAS DE VUELO',
                                    'NROFILES': 0,
                                    'USES': 0
                                },
                                {
                                    'FUNCTIONID': 'C03',
                                    'FUNCTION': 'INCIDENTES',
                                    'NROFILES': 0,
                                    'USES': 0
                                }
                            ]
                        }
                        let channelData = {
                            'data': {
                                'FACEBOOK': 0,
                                'WEB': 0
                            },
                            'total': 0
                        }
                        let functionData = {
                            'data': {
                                'INCIDENTES': 0,
                                'ITINERARIO': 0,
                                'PROBLEMAS DE VUELO': 0
                            }, 'total': 0, 'nrofiles': 0
                        }
                        resultData.totalFiles = res.total
                        $.each(res.functions, function () {
                            let index = resultData.functions.findIndex(obj => obj.FUNCTIONID == this.functionid)
                            if (index != -1) {
                                resultData.functions[index].NROFILES = parseInt(this.nrofiles)
                                resultData.functions[index].USES = parseInt(this.uses)
                                functionData.total += parseInt(this.uses)
                                functionData.nrofiles += parseInt(this.nrofiles)
                                functionData.data[this.function.trim()] = parseInt(this.uses)
                            }
                        })
                        let totalChannelFiles = 0
                        let totalChannelUsability = 0
                        $.each(res.channels, function () {
                            let index = resultData.channels.findIndex(obj => obj.CHANNELID == this.channelid)
                            if (index != -1) {
                                resultData.channels[index].NROFILES = parseInt(this.nrofiles)
                                channelData.total += parseInt(this.nrofiles)
                                channelData.data[this.channel.trim()] = parseInt(this.nrofiles)
                                totalChannelUsability += parseInt(this.nrofiles)
                            }
                        })
                        self.chatbotStatistics = resultData
                        self.chatbotStatistics.withoutUse = self.chatbotStatistics.totalFiles.totals - totalChannelUsability
                        self.channelStatistics = channelData
                        self.functionStatistics = functionData
                    } else if (resultType == 'mailing') {
                        self.notificationStatistics = res
                    } else if (resultType == 'files') {
                        self.filesStatistics = res
                    }

                },
                getMailing: function () {

                },
                getStatistics (byfile = false, context) {
                    let endpoint = ''
                    if (context == 'mailing') {
                        endpoint = baseExternalURL + 'api/masi_statistics/mailing'
                    } else if (context == 'chatbot') {
                        endpoint = baseExternalURL + 'api/masi_statistics/chatbot'
                    } else if (context == 'files') {
                        endpoint = baseExternalURL + 'api/masi_statistics/files'
                    }
                    var self = this
                    self.loadingData = true
                    let params = {}
                    if (byfile) {
                        params = {
                            'filter': 'byFile',
                            'file': self.fileSearch
                        }
                    } else {
                        params = {
                            'fileType': (self.filetypeFilter == 0) ? '' : self.filetypeFilter,
                            'fromDate': self.formatDate(self.fromDate),
                            'toDate': self.formatDate(self.toDate),
                            'regionCode': (self.regionFilter == 0) ? '' : self.regionFilter,
                            'countryCode': (self.countryFilter == 0) ? '' : self.countryFilter,
                            'clientCode': (self.clientFilter == 0) ? '' : self.clientFilter
                        }
                    }
                    axios.get(endpoint, {
                        params: params
                    }).then(function (response) {
                        let res = response.data.data
                        if (byfile && context == 'files') {
                            if (res[0]) {
                                let email = (res[0].email) ? res[0].email.trim() : 'No hay registro'
                                let phone_number = (res[0].phone_number) ? res[0].phone_number.trim() : 'No hay registro'
                                alert(`Datos de contacto del file ${self.fileSearch}\nCorreo: ${email}\nTeléfono: ${phone_number}`)
                            } else {
                                alert('No se ha encontrado datos del file ingresado')
                            }

                        } else {
                            self.formatResponse(res, context)
                            self.fileTypeResult = self.filetypeFilter
                        }

                    })
                        .catch(function (error) {
                            console.log(error)
                            self.$toast.error('Ocurrieron errores al tratar de obtener los datos estadísticos', {
                                position: 'top-right'
                            })
                        }).finally(() => {
                        self.loadingData = false
                    })

                },
                filterStatistics (byfile = false) {
                    this.byFileResults = false
                    if (byfile) {
                        this.loadingData = true
                        this.byFileResults = true
                        if (this.currentTab == 0) { //Tab de Estadísticas de FILES
                            this.getStatistics(true, 'files')
                        } else if (this.currentTab == 1) { //Tab de Estadísticas de Chatbot
                            this.getStatistics(true, 'chatbot')
                        } else if (this.currentTab == 2) { //Tab de Estadísticas de Mailing
                            this.getStatistics(true, 'mailing')
                        }
                    } else {
                        if ((this.fromDate && !this.toDate) || (!this.fromDate && this.toDate)) {
                            this.$toast.error('Para el filtrado por rango de fecha, se deben seleccionar un valor para ambas')
                        } else if (!this.fromDate && !this.toDate) {
                            this.$toast.error('Para la consultar las estadísticas, debe seleccionar un rango de fechas')
                        } else if (this.fromDate >= this.toDate) {
                            this.$toast.error('La fecha seleccionada como "Desde" no puede ser mayor o igual a la fecha "Hasta"')
                        } else {
                            this.loadingData = true
                            if (this.currentTab == 0) { //Tab de Estadísticas de FILES
                                this.getStatistics(false, 'files')
                            } else if (this.currentTab == 1) { //Tab de Estadísticas de Chatbot
                                this.getStatistics(false, 'chatbot')
                            } else if (this.currentTab == 2) { //Tab de Estadísticas de Mailing
                                this.getStatistics(false, 'mailing')
                            }
                        }
                    }

                },
                getPercentage (amount, total, decimalPlaces = 2) {
                    if ((amount == 0 && total == 0) || (amount > 0 && total == 0)) return 'ND'
                    num = Math.round(amount / total * 100 + 'e' + decimalPlaces)
                    return `${Number(num + 'e' + -decimalPlaces)}%`
                },
                formatDate (date) {
                    if (date) {
                        return moment(date, 'YYYY-MM-DD').format('DD/MM/YYYY')
                    }
                    return null
                }
            }
        })
    </script>
@endsection
