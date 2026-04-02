<template>
    <div>
        <div class="animated fadeIn">
            <template v-if="!loadingInit">
                <b-row>
                    <b-col lg="6">
                        <b-row>
                            <b-col lg="6">
                                <b-form-group>
                                    <label for="clients">Clientes:</label>
                                    <v-select
                                        :options="clients"
                                        label="code" code="code"
                                        multiple
                                        :reduce="client => client.code"
                                        :filterable="false"
                                        @search="onSearch"
                                        placeholder="Filtro por nombre ó ID del cliente"
                                        v-model="clientsSelected"
                                        style="height: 35px;"
                                    >
                                        <template
                                            slot="option"
                                            slot-scope="option"
                                        >
                                            <div class="d-center">
                                                <span>{{ option.label }}</span>
                                            </div>
                                        </template>
                                        <template
                                            slot="selected-option"
                                            slot-scope="option"
                                        >
                                            <div class="selected d-center">
                                                <span>{{ option.label }}</span>
                                            </div>
                                        </template>
                                    </v-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="6">
                                <b-form-group>
                                    <label for="nroref">Número de File:</label>
                                    <input type="number" v-model="nroref" class="form-control"
                                        id="nroref" />
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col lg="6">
                        <b-row>
                            <b-col lg="2">
                                <b-form-group>
                                    <label for="fecini">Fecha desde:</label>
                                    <date-picker
                                        :config="datePickerOptions"
                                        id="fecini"
                                        name="fecini"
                                        placeholder="DD/MM/YYYY"
                                        ref="fecini"
                                        v-model="fecini"
                                    >
                                    </date-picker>
                                </b-form-group>
                            </b-col>

                            <b-col lg="2">
                                <b-form-group>
                                    <label for="fecout">Fecha hasta:</label>
                                    <date-picker
                                        :config="datePickerOptions"
                                        id="fecout"
                                        name="fecout"
                                        placeholder="DD/MM/YYYY"
                                        ref="fecout"
                                        v-model="fecout"
                                    >
                                    </date-picker>
                                </b-form-group>
                            </b-col>

                            <b-col lg="3">
                                <b-form-group>
                                    <label for="types">Tipo de Reporte:</label>
                                    <b-form-select
                                        id="types"
                                        v-model="typeModel"
                                        :plain="true"
                                        :options="optionsTypeModel"
                                    >
                                    </b-form-select>
                                </b-form-group>
                            </b-col>

                            <b-col lg="3">
                                <b-form-group>
                                    <label for="filtrar">&nbsp;</label>
                                    <b-button
                                        class="btn-block"
                                        id="filtrar"
                                        :disabled="loadingSearch || loadingInit"
                                        v-on:click="getMasi()"
                                        variant="primary">
                                        Filtrar
                                    </b-button>
                                </b-form-group>
                            </b-col>

                            <b-col lg="2">
                                <b-form-group>
                                    <label for="excel">&nbsp;</label>
                                    <b-button
                                        class="btn-block"
                                        id="excel"
                                        :disabled="loadingSearch || loadingInit || loading_excel"
                                        v-on:click="downloadExcel()"
                                        variant="primary">
                                        Excel
                                    </b-button>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col lg="6">
                        <b-row>
                            <b-col lg="6">
                                <b-form-group>
                                    <label for="year1">Ciudad:</label>
                                    <v-select
                                        :options="optionsCityModel"
                                        label="value" code="value"
                                        :reduce="city => city.value"
                                        :filterable="true"
                                        placeholder="Filtrar ciudades"
                                        v-model="cityModel"
                                        style="height: 35px;"
                                        @input="onChange"
                                    >
                                        <template
                                            slot="option"
                                            slot-scope="option"
                                        >
                                            <div class="d-center">
                                                <span>{{ option.text }}</span>
                                            </div>
                                        </template>
                                        <template
                                            slot="selected-option"
                                            slot-scope="option"
                                        >
                                            <div class="selected d-center">
                                                <span>{{ option.text }}</span>
                                            </div>
                                        </template>
                                    </v-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="6">
                                <b-form-group>
                                    <label for="region">Región:</label>
                                    <v-select
                                        :options="regions"
                                        multiple
                                        :reduce="region => region.code"
                                        label="label" code="code"
                                        :filterable="true"
                                        placeholder="TODOS"
                                        v-model="region"
                                        style="height: 35px;"
                                    ></v-select>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col lg="6">
                        <b-row>
                            <template v-for="(_region, r) in data.statisticsByRegions">
                                <div class="col" v-bind:key="'region-' + r">
                                    <b-form-group>
                                        <b-button variant="primary" class="w-100">
                                            Región C{{ _region.REGIONS }}<br />
                                            <b-badge variant="light">{{ _region.CNT }} <span>encuestas</span></b-badge>
                                        </b-button>
                                    </b-form-group>
                                </div>
                            </template>
                        </b-row>
                    </b-col>
                </b-row>

                <div class="row p-0 m-0">
                    <template v-for="(city, c) in cities">
                        <div
                            class="btn btn-danger mb-3 mr-3"
                            v-bind:key="'city-' + c"
                            v-on:click="removeCity(c)"
                        >
                            Ciudad: <b>{{ city }}</b>
                        </div>
                    </template>
                </div>

                <template v-if="!loadingSearch">
                    <b-row>
                        <b-col lg="6">
                            <b-card no-body class="bg-primary">
                                <b-card-body class="pb-0">
                                    <h4 class="mb-1">
                                        <strong
                                            v-text="data.chart01.cntAll.CNT"
                                        ></strong>
                                        Encuestas completadas
                                            (<small v-if="quantity_people > data.chart01.cntAll.CNT">
                                                {{ (quantity_people > 0) ? parseFloat(data.chart01.cntAll.CNT * 100 / quantity_people).toFixed(2) : 0 }}%
                                            </small>
                                            <small v-else>
                                                100%
                                            </small>)
                                    </h4>
                                    <h4 class="mb-1">
                                        <strong
                                            v-text="quantity_people > data.chart01.cntAll.CNT ? quantity_people : data.chart01.cntAll.CNT"
                                        ></strong>
                                        Personas que recibieron la encuesta
                                    </h4>

                                    <p class="mb-1 p-0">{{ quantity_people }} Notificación MASI</p>
                                    <p class="mb-1 p-0">{{ (data.chart01.cntAll.CNT > quantity_people) ? data.chart01.cntAll.CNT - quantity_people : 0 }} Aplicación WEB</p>
                                </b-card-body>
                                <card-line1-chart-example
                                    chartId="card-chart-01"
                                    class="chart-wrapper px-3"
                                    style="height:70px;"
                                    :height="70"
                                    :historial="data.chart01.historial"
                                />
                            </b-card>
                        </b-col>

                        <b-col lg="6">
                            <b-card no-body class="bg-danger">
                                <b-card-body class="pb-0">
                                    <h3 class="mb-0">
                                        <strong
                                            v-text="
                                                this.cntRecomendariaFavorablemente(
                                                    data.chart02
                                                )
                                            "
                                        ></strong>
                                    </h3>
                                    <p>
                                        De los encuestados recomendaría
                                        favorablemente el destino (>6)
                                    </p>
                                </b-card-body>
                                <card-bar-chart-example
                                    chartId="card-chart-04"
                                    class="chart-wrapper px-3"
                                    style="height:70px;"
                                    height="70"
                                    :data="data.chart02"
                                />
                            </b-card>
                        </b-col>
                    </b-row>

                    <!-- EXCURSIONES -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <h3><strong>EXCURSIONES</strong></h3>
                        </b-col>
                    </b-row>

                    <b-row
                        v-if="
                            Object.entries(total_excursiones['avg']).length > 0
                        "
                    >
                        <b-col lg="6">
                            <div class="progress my-3">
                                <template
                                    v-for="(item, i) in total_excursiones[
                                        'avg'
                                    ]"
                                >
                                    <div
                                        v-bind:class="['progress-bar']"
                                        role="progressbar"
                                        v-bind:style="
                                            'border-radius:0px!important; width: ' +
                                                item +
                                                '%; background-color:' +
                                                total_excursiones['colors'][i]
                                        "
                                        v-bind:aria-valuenow="item"
                                        v-bind:title="i"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <b
                                            >{{
                                                parseFloat(item).toFixed(2)
                                            }}%</b
                                        >
                                    </div>
                                </template>
                            </div>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="12">
                            <b-card-group columns class="card-columns">
                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-1'"
                                            :data="data.q01"
                                            :caption="'Itinerario'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-2'"
                                            :data="data.q02"
                                            :caption="'Transporte'"
                                        />
                                    </div>
                                </b-card>
                            </b-card-group>
                        </b-col>
                    </b-row>

                    <!-- GUÍA LOCAL -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <h3><strong>GUÍA LOCAL</strong></h3>
                        </b-col>
                    </b-row>

                    <b-row
                        v-if="
                            Object.entries(total_guia_local['avg']).length > 0
                        "
                    >
                        <b-col lg="6">
                            <div class="progress my-3">
                                <template
                                    v-for="(item, i) in total_guia_local['avg']"
                                >
                                    <div
                                        v-bind:class="['progress-bar']"
                                        role="progressbar"
                                        v-bind:style="
                                            'border-radius:0px!important; width: ' +
                                                item +
                                                '%; background-color:' +
                                                total_guia_local['colors'][i]
                                        "
                                        v-bind:aria-valuenow="item"
                                        v-bind:title="i"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <b
                                            >{{
                                                parseFloat(item).toFixed(2)
                                            }}%</b
                                        >
                                    </div>
                                </template>
                            </div>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="12">
                            <b-card-group columns class="card-columns">
                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-3'"
                                            :data="data.q03"
                                            :caption="'Puntualidad'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-4'"
                                            :data="data.q04"
                                            :caption="'Disposición'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-5'"
                                            :data="data.q05"
                                            :caption="
                                                'Conocimiento del destino'
                                            "
                                        />
                                    </div>
                                </b-card>
                            </b-card-group>
                        </b-col>
                    </b-row>

                    <!-- HOTELES -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <h3><strong>HOTELES</strong></h3>
                        </b-col>
                    </b-row>

                    <b-row
                        v-if="Object.entries(total_hoteles['avg']).length > 0"
                    >
                        <b-col lg="6">
                            <div class="progress my-3">
                                <template
                                    v-for="(item, i) in total_hoteles['avg']"
                                >
                                    <div
                                        v-bind:class="['progress-bar']"
                                        role="progressbar"
                                        v-bind:style="
                                            'border-radius:0px!important; width: ' +
                                                item +
                                                '%; background-color:' +
                                                total_hoteles['colors'][i]
                                        "
                                        v-bind:aria-valuenow="item"
                                        v-bind:title="i"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <b
                                            >{{
                                                parseFloat(item).toFixed(2)
                                            }}%</b
                                        >
                                    </div>
                                </template>
                            </div>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="12">
                            <b-card-group columns class="card-columns">
                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-6'"
                                            :data="data.q06"
                                            :caption="'Atención y servicio'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-7'"
                                            :data="data.q07"
                                            :caption="'Orden y limpieza'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-8'"
                                            :data="data.q08"
                                            :caption="'Desayuno'"
                                        />
                                    </div>
                                </b-card>
                            </b-card-group>
                        </b-col>
                    </b-row>

                    <!-- RESTAURANTES -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <h3><strong>RESTAURANTES</strong></h3>
                        </b-col>
                    </b-row>

                    <b-row
                        v-if="
                            Object.entries(total_restaurantes['avg']).length > 0
                        "
                    >
                        <b-col lg="6">
                            <div class="progress my-3">
                                <template
                                    v-for="(item, i) in total_restaurantes[
                                        'avg'
                                    ]"
                                >
                                    <div
                                        v-bind:class="['progress-bar']"
                                        role="progressbar"
                                        v-bind:style="
                                            'border-radius:0px!important; width: ' +
                                                item +
                                                '%; background-color:' +
                                                total_restaurantes['colors'][i]
                                        "
                                        v-bind:aria-valuenow="item"
                                        v-bind:title="i"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <b
                                            >{{
                                                parseFloat(item).toFixed(2)
                                            }}%</b
                                        >
                                    </div>
                                </template>
                            </div>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="12">
                            <b-card-group columns class="card-columns">
                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-9'"
                                            :data="data.q09"
                                            :caption="'Calidad de comida'"
                                        />
                                    </div>
                                </b-card>

                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-10'"
                                            :data="data.q10"
                                            :caption="'Atención y servicio'"
                                        />
                                    </div>
                                </b-card>
                            </b-card-group>
                        </b-col>
                    </b-row>

                    <!-- GENERAL -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <h3><strong>GENERAL</strong></h3>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col lg="12">
                            <b-card-group columns class="card-columns">
                                <b-card>
                                    <div class="chart-wrapper">
                                        <pie-fusion-chart
                                            :id="'chart-11'"
                                            :data="data.q11"
                                            :caption="
                                                '¿El destino cumplió con tus expectativas?'
                                            "
                                        />
                                    </div>
                                </b-card>
                            </b-card-group>
                        </b-col>
                    </b-row>

                    <!-- ¿Qué podemos mejorar? -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <b-card header="¿CÓMO PODEMOS MEJORAR?">
                                <table-chart :data="data.q12" />
                            </b-card>
                        </b-col>
                    </b-row>

                    <!-- ¿Qué te ha gustado más? -->
                    <b-row class="mt-3">
                        <b-col lg="12">
                            <b-card header="¿QUÉ TE HA GUSTADO MÁS?">
                                <table-chart :data="data.q13" />
                            </b-card>
                        </b-col>
                    </b-row>
                </template>

                <template v-else>
                    <b-row>
                        <b-col lg="12" class="text-center">
                            <font-awesome-icon :icon="myIcon" spin size="2x" />
                        </b-col>
                    </b-row>
                </template>
            </template>
            <template v-else>
                <b-row>
                    <b-col lg="12" class="text-center">
                        <font-awesome-icon :icon="myIcon" spin size="2x" />
                    </b-col>
                </b-row>
            </template>
        </div>
    </div>
</template>

<script>
import { API } from "./../../api";
import axios from "axios";
import CardLine1ChartExample from "../Masi/dashboard/CardLine1ChartExample";
import CardBarChartExample from "../Masi/dashboard/CardBarChartExample";
import TableChart from "../Masi/dashboard/TableChart";
import datePicker from "vue-bootstrap-datetimepicker";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faSpinner } from "@fortawesome/free-solid-svg-icons";
import PieFusionChart from "./dashboard/PieFusionChart";

import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";

export default {
    name: "dashboard",
    components: {
        CardLine1ChartExample,
        CardBarChartExample,
        // MainChartExample,
        // SocialBoxChartExample,
        // CalloutChartExample,
        // DoughnutChart,
        datePicker,
        TableChart,
        FontAwesomeIcon,
        PieFusionChart,
        vSelect
    },
    data() {
        let fecha = new Date();

        return {
            loading_excel: false,
            loadingInit: true,
            loadingSearch: false,
            myIcon: faSpinner,
            URLNodeCommercialP: "",
            cityModel: null,
            clients: [],
            clientsSelected: [],
            client_id: null,
            region: [],
            cities: [],
            nroref: '',
            fecini: moment().add(-1, 'month').format('DD/MM/YYYY'),
            fecout: moment().format('DD/MM/YYYY'),
            optionsCityModel: [],
            datePickerOptions: {
                format: "DD/MM/YYYY",
                useCurrent: false,
                locale: localStorage.getItem("lang")
            },
            optionsTypeModel: [
                {
                    value: 0,
                    text: "Reporte Global"
                },
                {
                    value: 1,
                    text: "Reporte Desglosado"
                },
                {
                    value: 3,
                    text: "Encuesta PAXS"
                }
            ],
            yearsModel: [
                {
                    value: 0,
                    text: "Todos los años."
                },
                {
                    value: fecha.getFullYear() - 1,
                    text: fecha.getFullYear() - 1
                },
                {
                    value: fecha.getFullYear(),
                    text: fecha.getFullYear()
                }
            ],
            optionsMonthModel: [
                {
                    value: 0,
                    text: "Todos las meses"
                },
                {
                    value: 1,
                    text: "ENERO"
                },
                {
                    value: 2,
                    text: "FEBRERO"
                },
                {
                    value: 3,
                    text: "MARZO"
                },
                {
                    value: 4,
                    text: "ABRIL"
                },
                {
                    value: 5,
                    text: "MAYO"
                },
                {
                    value: 6,
                    text: "JUNIO"
                },
                {
                    value: 7,
                    text: "JULIO"
                },
                {
                    value: 8,
                    text: "AGOSTO"
                },
                {
                    value: 9,
                    text: "SETIEMBRE"
                },
                {
                    value: 10,
                    text: "OCTUBRE"
                },
                {
                    value: 11,
                    text: "NOVIEMBRE"
                },
                {
                    value: 12,
                    text: "DICIEMBRE"
                }
            ],
            yearModel: 0, //fecha.getFullYear(),
            monthModel: 0, //3,
            typeModel: 0,
            data: {},
            selected: "Month",
            tableItems: [],
            items: [
                {
                    id: 1,
                    first_name: "Fred",
                    last_name: "Flintstone"
                },
                {
                    id: 2,
                    first_name: "Wilma",
                    last_name: "Flintstone"
                },
                {
                    id: 3,
                    first_name: "Barney",
                    last_name: "Rubble"
                },
                {
                    id: 4,
                    first_name: "Betty",
                    last_name: "Rubble"
                },
                {
                    id: 5,
                    first_name: "Pebbles",
                    last_name: "Flintstone"
                },
                {
                    id: 6,
                    first_name: "Bamm Bamm",
                    last_name: "Rubble"
                },
                {
                    id: 7,
                    first_name: "The Great",
                    last_name: "Gazzoo"
                },
                {
                    id: 8,
                    first_name: "Rockhead",
                    last_name: "Slate"
                },
                {
                    id: 9,
                    first_name: "Pearl",
                    last_name: "Slaghoople"
                }
            ],
            tableFields: {
                /*
                    avatar: {
                      label: 'Icon People',// '<i class="icon-people"></i>',
                      class: 'text-center'
                    },
                    */
                pax: {
                    label: "Usuario"
                },
                nroref: {
                    label: "File",
                    class: "text-center"
                }
            },

            type: "pie2d",
            renderAt: "chart-container",
            width: "100%",
            height: "100%",
            dataFormat: "json",
            datasource: {
                chart: {
                    caption: "Number of visitors in the last 3 days",
                    subCaption: "Bakersfield Central vs Los Angeles Topanga",
                    theme: "fusion"
                },
                data: []
            },
            total_excursiones: [],
            total_guia_local: [],
            total_hoteles: [],
            total_restaurantes: [],
            quantity_people: 0,
            regions: [
                { code: '1', label: 'C1' },
                { code: '2', label: 'C2' },
                { code: '3', label: 'C3' },
            ]
        };
    },
    computed: {},
    methods: {
        onSearch(search, loading) {

            if(search != '' || (search == '' && this.clients.length == 0))
            {
                loading(true);
                API.get(
                    "/client/search?lang=" +
                        localStorage.getItem("lang") +
                        "&queryCustom=" +
                        search
                )
                    .then(result => {
                        loading(false);
                        let clients = result.data.data;
                        let _clients = [];
                        clients.forEach(clients => {
                            _clients.push({
                                code: clients.code,
                                label: "(" + clients.code + ") " + clients.name,
                                id: clients.id
                            });
                        });
                        this.clients = _clients;
                        this.form_clients = _clients;
                    })
                    .catch(() => {
                        loading(false);
                        this.$notify({
                            group: "main",
                            type: "error",
                            title: "Clientes",
                            text: this.$t("global.error.messages.information_error")
                        });
                    });
            }
        },
        onChange: function($event) {
            if(this.cityModel != null && this.cityModel != '')
            {
                this.cities.push(this.cityModel);
            }
        },
        removeCity: function(_city) {
            this.cities.splice(_city, 1);
        },
        calculateProgressBar: function(_graphs) {
            "use strict";

            let promedios = {};
            let colors = {};
            let items = 0;

            _graphs.forEach((item, i) => {
                if (_graphs[i].length > 0) {
                    items += 1;
                }
            });

            _graphs.forEach((item, index) => {
                for (let i = 0; i < item.length; i++) {
                    promedios[item[i]["LABEL"]] =
                        promedios[item[i]["LABEL"]] == undefined
                            ? 0
                            : parseFloat(promedios[item[i]["LABEL"]]);
                    promedios[item[i]["LABEL"]] += parseFloat(
                        parseFloat(item[i]["VALUE"]) / items
                    );

                    colors[item[i]["LABEL"]] = item[i]["COLOR"];
                }
            });

            return { avg: promedios, colors: colors };
        },
        getMasi: function () {

            if(!this.loadingSearch)
            {
                this.loadingSearch = true
                this.data = {}

                let filter = {
                    client: this.clientsSelected,
                    ciudad: this.cities,
                    fecini: this.fecini,
                    fecout: this.fecout,
                    region: this.region,
                    nroref: this.nroref,
                }

                // let endpoint = "http://extranet.litoapps.com/";
                // let url = `${endpoint}masi/api.php?ctrl=poll&fx=getResults`;
                // let url = `${this.URLNodeCommercialP}masi/api.php?ctrl=poll&fx=getResults`;
                let url = `${this.URLNodeCommercialP}files/polls/results`;
                axios
                    .post(url, filter)
                    .then(response => {

                        this.data = response.data.data;

                        this.total_excursiones = this.calculateProgressBar([
                            this.data.q01,
                            this.data.q02
                        ]);

                        this.total_guia_local = this.calculateProgressBar([
                            this.data.q03,
                            this.data.q04,
                            this.data.q05
                        ]);

                        this.total_hoteles = this.calculateProgressBar([
                            this.data.q06,
                            this.data.q07,
                            this.data.q08
                        ]);

                        this.total_restaurantes = this.calculateProgressBar([
                            this.data.q09,
                            this.data.q10
                        ]);

                        this.tableItems = response.data.last10Poll;

                        if (this.optionsCityModel.length === 0) {
                            this.optionsCityModel = this.getCities(
                                this.data.allCities
                            );
                        }

                        this.searchQuantityPeople()

                        this.loadingInit = false;
                        this.loadingSearch = false;
                    })
                    .catch(error => {
                        this.loadingSearch = false
                        console.log(error);
                    })
                    .finally(() => {
                        this.loadingSearch = false
                        console.log("getMasi finalizado");
                    });
            }
        },
        searchQuantityPeople: function () {
            let filter = {
                client: this.clientsSelected,
                from: this.fecini,
                to: this.fecout,
                region: this.region,
                nroref: this.nroref,
            }

            API({
                method: 'POST',
                url: 'masi/quantity_people',
                data: filter
            })
                .then((result) => {
                    if(result.data.type == 'success')
                    {
                        this.quantity_people = result.data.quantity_people
                    }
                    console.log(result.data)
                })
                .catch(error => {
                    console.log(error);
                })
        },
        variant(value) {
            let $variant;
            if (value <= 25) {
                $variant = "info";
            } else if (value > 25 && value <= 50) {
                $variant = "success";
            } else if (value > 50 && value <= 75) {
                $variant = "warning";
            } else if (value > 75 && value <= 100) {
                $variant = "danger";
            }
            return $variant;
        },
        flag(value) {
            return "flag-icon flag-icon-" + value;
        },
        cntRecomendariaFavorablemente(values) {
            let sumAll = 0;
            let sumOnlyRecommend = 0;
            values.forEach(v => {
                sumAll += parseInt(v.CNT);
                if (v.PESPRE > 5) {
                    sumOnlyRecommend += parseInt(v.CNT);
                }
            });
            return ((sumOnlyRecommend / sumAll) * 100).toFixed(2) + " %";
        },
        getCities(allCitiesDB) {
            let x = [
                {
                    value: null,
                    text: "Todos las ciudades."
                }
            ];
            allCitiesDB.forEach(v => {
                x.push({
                    value: v.CODIGO,
                    text: v.DESCRI
                });
            });
            return x;
        },
        downloadExcel: function() {
            let type = this.typeModel === null ? 1 : this.typeModel;

            this.loading_excel = true;
            API.post(
                "/masi/downloadExcel",
                {
                    client: this.clientsSelected,
                    ciudad: this.cities,
                    fecini: this.fecini,
                    fecout: this.fecout,
                    region: this.region,
                    nroref: this.nroref,
                    type: type,
                },
                {
                    responseType: "blob"
                }
            )
                .then(response => {
                    const url = URL.createObjectURL(
                        new Blob([response.data], {
                            type: "application/vnd.ms-excel"
                        })
                    );
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", "Descargar excel");
                    document.body.appendChild(link);
                    link.click();

                    this.loading_excel = false;
                })
                .catch(error => {
                    this.loading_excel = false;
                    console.log(error);
                });
        }
    },
    created() {

        API.get(
            "/routes/commercial-p"
        )
            .then(result => {
                console.log(result.data.data)
                this.URLNodeCommercialP = result.data;
                this.getMasi()
            })
            .catch(() => {
                loading(false);
                this.$notify({
                    group: "main",
                    type: "error",
                    title: "Route Node Commercial P",
                    text: this.$t("global.error.messages.information_error")
                });
            });
    },
    watch: {}
};
</script>

<style>
/* IE fix */
#card-chart-01,
#card-chart-02 {
    width: 100% !important;
}
</style>
