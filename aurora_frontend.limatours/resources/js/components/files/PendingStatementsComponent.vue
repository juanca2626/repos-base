<template>
    <div>
        <div class="container">
            <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                <p class="mb-0">Cargando..</p>
            </div>
        </div>

        <div class="container-fluid">
            <fusioncharts v-if="!loading"
                          id="mychartcontainer-statements"
                          chartid="vueChart-statements"
                          width="100%"
                          height="500"
                          type="stackedcolumn2dline"
                          :dataSource="dataSource">
            </fusioncharts>
        </div>

        <div class="container mt-4" v-if="detail.length > 0">
            <h2 class="mb-4">Detalle de la selección:</h2>
            <table class="table">
                <thead>
                <tr>
                    <th>FILE</th>
                    <th>REFERENCIA</th>
                    <th>CLIENTE</th>
                    <th>SECTOR</th>
                    <th>ESPECIALISTA</th>
                    <th>FECIN</th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="(_item, d) in detail">
                        <td>{{ _item.nroref }}</td>
                        <td>{{ _item.reference }}</td>
                        <td>{{ _item.codcli }}</td>
                        <td>{{ _item.codsec }}</td>
                        <td>{{ _item.codope }}</td>
                        <td>{{ _item.fecin }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    // Using font-awesome 5 icons
    $.extend(true, $.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    })

    export default {
        props: ['data'],
        data: () => {
            return {
                lang: '',
                loading: false,
                quantity: 0,
                date: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                },
                bossFlag: '0',
                translations: [],
                executives: [],
                dataSource: {
                },
                fits_7: 0,
                grupos_7: 0,
                otros_7: 0,
                excepciones_7: 0,
                fits_15: 0,
                grupos_15: 0,
                otros_15: 0,
                excepciones_15: 0,
                fits_30: 0,
                grupos_30: 0,
                otros_30: 0,
                excepciones_30: 0,
                fits_mes: 0,
                grupos_mes: 0,
                otros_mes: 0,
                excepciones_mes: 0,
                detail: [],
                items: [
                    {
                        0: [],
                        1: [],
                        2: [],
                        3: []
                    },
                    {
                        0: [],
                        1: [],
                        2: [],
                        3: []
                    },
                    {
                        0: [],
                        1: [],
                        2: [],
                        3: []
                    }
                ]
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.bossFlag = this.data.bossFlag
            this.translations = this.data.translations

            this.dataSource = {
                "chart": {
                    "caption": "FILES PENDIENTES DE PASE",
                    "subCaption": "",
                    "xAxisname": "PRODUCTO",
                    "yAxisName": "CANTIDAD DE FILES",
                    "numberPrefix": "",
                    "divlineColor": "#999999",
                    "divLineDashed": "1",
                    "theme": "fusion"
                },
                "categories": [
                    {
                        "category": [
                            {
                                "label": "FITS"
                            },
                            {
                                "label": "GRUPOS & SERIES"
                            },
                            {
                                "label": "OTROS"
                            }
                        ]
                    }
                ],
                "dataset": [
                    {
                        "seriesname": "menos de 7 días",
                        'color': '#9F9F9F',
                        'valueFontColor': '#FFFFFF',
                        "data": [
                            {
                                "value": this.fits_7,
                                'color': '#9F9F9F',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.grupos_7,
                                'color': '#9F9F9F',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.otros_7,
                                'color': '#9F9F9F',
                                'valueFontColor': '#FFFFFF'
                            }
                        ]
                    },
                    {
                        "seriesname": "menos de 15 días",
                        'color': '#FBC73B',
                        'valueFontColor': '#FFFFFF',
                        "data": [
                            {
                                "value": this.fits_15,
                                'color': '#FBC73B',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.grupos_15,
                                'color': '#FBC73B',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.otros_15,
                                'color': '#FBC73B',
                                'valueFontColor': '#FFFFFF'
                            }
                        ]
                    },
                    {
                        "seriesname": "menos de 30 días",
                        'color': '#FE812A',
                        'valueFontColor': '#FFFFFF',
                        "data": [
                            {
                                "value": this.fits_30,
                                'color': '#FE812A',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.grupos_30,
                                'color': '#FE812A',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.otros_30,
                                'color': '#FE812A',
                                'valueFontColor': '#FFFFFF'
                            }
                        ]
                    },
                    {
                        "seriesname": "más de 30 días",
                        'color': '#DD0100',
                        'valueFontColor': '#FFFFFF',
                        "data": [
                            {
                                "value": this.fits_mes,
                                'color': '#DD0100',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.grupos_mes,
                                'color': '#DD0100',
                                'valueFontColor': '#FFFFFF'
                            },
                            {
                                "value": this.otros_mes,
                                'color': '#DD0100',
                                'valueFontColor': '#FFFFFF'
                            }
                        ]
                    }
                ]
            }

            FusionCharts.addEventListener('dataPlotClick', this.handler_statements)

            this.searchExecutives()
        },
        computed: {

        },
        methods: {
            handler_statements: function(e) {
                let props = e.data
                console.log(props)
                console.log(this.items[props.dataIndex][props.datasetIndex])

                this.detail = this.items[props.dataIndex][props.datasetIndex]
            },
            searchExecutives: function () {
                this.loading = true

                axios.post(baseURL + 'board/executives_user', {
                    team: 'TODOS'
                })
                    .then((result) => {
                        let vm = this
                        vm.loading = false
                        vm.executives = result.data.executives

                        setTimeout(function () {
                            vm.search(0)
                        }, 10)
                    })
                    .catch((e) => {
                        this.loading = false
                    })
            },
            search: function (_index) {
                let vm = this

                if(this.bossFlag == 1)
                {
                    vm.loading = true
                    axios.post(
                        baseURL + 'reports/files/search', {
                            executives: vm.executives,
                            module: 'B'
                        }
                    )
                        .then((result) => {
                            vm.loading = false
                            let _response = result.data

                            Object.entries(_response).forEach(([i, item]) => {
                                console.log(item)
                                let _dashboard = item.dashboard

                                vm.fits_7 += _dashboard.response_7.files['FITS'].length
                                vm.fits_15 += _dashboard.response_15.files['FITS'].length
                                vm.fits_30 += _dashboard.response_30.files['FITS'].length
                                vm.fits_mes += _dashboard.response_mes.files['FITS'].length

                                vm.grupos_7 += _dashboard.response_7.files['GRUPOS & SERIES'].length
                                vm.grupos_15 += _dashboard.response_15.files['GRUPOS & SERIES'].length
                                vm.grupos_30 += _dashboard.response_30.files['GRUPOS & SERIES'].length
                                vm.grupos_mes += _dashboard.response_mes.files['GRUPOS & SERIES'].length

                                vm.otros_7 += _dashboard.response_7.files['OTROS'].length
                                vm.otros_15 += _dashboard.response_15.files['OTROS'].length
                                vm.otros_30 += _dashboard.response_30.files['OTROS'].length
                                vm.otros_mes += _dashboard.response_mes.files['OTROS'].length

                                setTimeout(function () {
                                    if(_dashboard.response_7.files['FITS'].length > 0)
                                    {
                                        vm.items[0][0] = vm.items[0][0].concat(_dashboard.response_7.files['FITS'])
                                    }

                                    if(_dashboard.response_15.files['FITS'].length > 0)
                                    {
                                        vm.items[0][1] = vm.items[0][1].concat(_dashboard.response_15.files['FITS'])
                                    }

                                    if(_dashboard.response_30.files['FITS'].length > 0)
                                    {
                                        vm.items[0][2] = vm.items[0][2].concat(_dashboard.response_30.files['FITS'])
                                    }

                                    if(_dashboard.response_mes.files['FITS'].length > 0)
                                    {
                                        vm.items[0][3] = vm.items[0][3].concat(_dashboard.response_mes.files['FITS'])
                                    }

                                    if(_dashboard.response_7.files['GRUPOS & SERIES'].length > 0)
                                    {
                                        vm.items[1][0] = vm.items[1][0].concat(_dashboard.response_7.files['GRUPOS & SERIES'])
                                    }

                                    if(_dashboard.response_15.files['GRUPOS & SERIES'].length > 0)
                                    {
                                        vm.items[1][1] = vm.items[1][1].concat(_dashboard.response_15.files['GRUPOS & SERIES'])
                                    }

                                    if(_dashboard.response_30.files['GRUPOS & SERIES'].length > 0)
                                    {
                                        vm.items[1][2] = vm.items[1][2].concat(_dashboard.response_30.files['GRUPOS & SERIES'])
                                    }

                                    if(_dashboard.response_mes.files['GRUPOS & SERIES'].length > 0)
                                    {
                                        vm.items[1][3] = vm.items[1][3].concat(_dashboard.response_mes.files['GRUPOS & SERIES'])
                                    }

                                    if(_dashboard.response_7.files['OTROS'].length > 0)
                                    {
                                        vm.items[2][0] = vm.items[2][0].concat(_dashboard.response_7.files['OTROS'])
                                    }

                                    if(_dashboard.response_15.files['OTROS'].length > 0)
                                    {
                                        vm.items[2][1] = vm.items[2][1].concat(_dashboard.response_15.files['OTROS'])
                                    }

                                    if(_dashboard.response_30.files['OTROS'].length > 0)
                                    {
                                        vm.items[2][2] = vm.items[2][2].concat(_dashboard.response_30.files['OTROS'])
                                    }

                                    if(_dashboard.response_mes.files['OTROS'].length > 0)
                                    {
                                        vm.items[2][3] = vm.items[2][3].concat(_dashboard.response_mes.files['OTROS'])
                                    }

                                    if(_dashboard.response_7.files['EXCEPCIONES'].length > 0)
                                    {
                                        vm.items[3][0] = vm.items[3][0].concat(_dashboard.response_7.files['EXCEPCIONES'])
                                    }

                                    if(_dashboard.response_15.files['EXCEPCIONES'].length > 0)
                                    {
                                        vm.items[3][1] = vm.items[3][1].concat(_dashboard.response_15.files['EXCEPCIONES'])
                                    }

                                    if(_dashboard.response_30.files['EXCEPCIONES'].length > 0)
                                    {
                                        vm.items[3][2] = vm.items[3][2].concat(_dashboard.response_30.files['EXCEPCIONES'])
                                    }

                                    if(_dashboard.response_mes.files['EXCEPCIONES'].length > 0)
                                    {
                                        vm.items[3][3] = vm.items[3][3].concat(_dashboard.response_mes.files['EXCEPCIONES'])
                                    }

                                    vm.dataSource.dataset[0].data = [
                                        { 'value': vm.fits_7, 'color': '#9F9F9F', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.grupos_7, 'color': '#9F9F9F', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.otros_7, 'color': '#9F9F9F', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.excepciones_7, 'color': '#9F9F9F', 'valueFontColor': '#FFFFFF' }
                                    ]

                                    vm.dataSource.dataset[1].data = [
                                        { 'value': vm.fits_15, 'color': '#FBC73B', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.grupos_15, 'color': '#FBC73B', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.otros_15, 'color': '#FBC73B', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.excepciones_15, 'color': '#FBC73B', 'valueFontColor': '#FFFFFF' }
                                    ]

                                    vm.dataSource.dataset[2].data = [
                                        { 'value': vm.fits_30, 'color': '#FE812A', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.grupos_30, 'color': '#FE812A', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.otros_30, 'color': '#FE812A', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.excepciones_30, 'color': '#FE812A', 'valueFontColor': '#FFFFFF' }
                                    ]

                                    vm.dataSource.dataset[3].data = [
                                        { 'value': vm.fits_mes, 'color': '#DD0100', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.grupos_mes, 'color': '#DD0100', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.otros_mes, 'color': '#DD0100', 'valueFontColor': '#FFFFFF' },
                                        { 'value': vm.excepciones_mes, 'color': '#DD0100', 'valueFontColor': '#FFFFFF' }
                                    ]
                                }, 10)
                            })


                        })
                        .catch((e) => {
                            console.log(e)
                        })

                    /*
                    if(_index == undefined)
                    {
                        _index = 0
                    }

                    let _cantidad = vm.executives.length

                    if(_index < _cantidad)
                    {
                        let value = vm.executives[_index]

                        console.log(value)

                        if ((value.NOMESP != undefined && value.NOMESP != '' && value.NOMESP != null) || value.value != undefined)
                        {
                            if(value.value != undefined && value.value != null && value.value != '')
                            {
                                value.NOMESP = value.value
                            }


                        }
                    }
                    */
                }
                else
                {
                    vm.loading = true

                    axios.post(
                        baseURL + 'reports/files/search', {
                            lang: this.lang,
                            bossFlag: this.bossFlag,
                            module: 'B'
                        }
                    )
                        .then((result) => {
                            vm.loading = false

                            vm.fits_7 += _dashboard.response_7.files['FITS'].length
                            vm.fits_15 += _dashboard.response_15.files['FITS'].length
                            vm.fits_30 += _dashboard.response_30.files['FITS'].length
                            vm.fits_mes += _dashboard.response_mes.files['FITS'].length

                            vm.grupos_7 += _dashboard.response_7.files['GRUPOS & SERIES'].length
                            vm.grupos_15 += _dashboard.response_15.files['GRUPOS & SERIES'].length
                            vm.grupos_30 += _dashboard.response_30.files['GRUPOS & SERIES'].length
                            vm.grupos_mes += _dashboard.response_mes.files['GRUPOS & SERIES'].length

                            vm.otros_7 += _dashboard.response_7.files['OTROS'].length
                            vm.otros_15 += _dashboard.response_15.files['OTROS'].length
                            vm.otros_30 += _dashboard.response_30.files['OTROS'].length
                            vm.otros_mes += _dashboard.response_mes.files['OTROS'].length

                            setTimeout(function () {
                                if(_dashboard.response_7.files['FITS'].length > 0)
                                {
                                    vm.items[0][0] = vm.items[0][0].concat(_dashboard.response_7.files['FITS'])
                                }

                                if(_dashboard.response_15.files['FITS'].length > 0)
                                {
                                    vm.items[0][1] = vm.items[0][1].concat(_dashboard.response_15.files['FITS'])
                                }

                                if(_dashboard.response_30.files['FITS'].length > 0)
                                {
                                    vm.items[0][2] = vm.items[0][2].concat(_dashboard.response_30.files['FITS'])
                                }

                                if(_dashboard.response_mes.files['FITS'].length > 0)
                                {
                                    vm.items[0][3] = vm.items[0][3].concat(_dashboard.response_mes.files['FITS'])
                                }

                                if(_dashboard.response_7.files['GRUPOS & SERIES'].length > 0)
                                {
                                    vm.items[1][0] = vm.items[1][0].concat(_dashboard.response_7.files['GRUPOS & SERIES'])
                                }

                                if(_dashboard.response_15.files['GRUPOS & SERIES'].length > 0)
                                {
                                    vm.items[1][1] = vm.items[1][1].concat(_dashboard.response_15.files['GRUPOS & SERIES'])
                                }

                                if(_dashboard.response_30.files['GRUPOS & SERIES'].length > 0)
                                {
                                    vm.items[1][2] = vm.items[1][2].concat(_dashboard.response_30.files['GRUPOS & SERIES'])
                                }

                                if(_dashboard.response_mes.files['GRUPOS & SERIES'].length > 0)
                                {
                                    vm.items[1][3] = vm.items[1][3].concat(_dashboard.response_mes.files['GRUPOS & SERIES'])
                                }

                                if(_dashboard.response_7.files['OTROS'].length > 0)
                                {
                                    vm.items[2][0] = vm.items[2][0].concat(_dashboard.response_7.files['OTROS'])
                                }

                                if(_dashboard.response_15.files['OTROS'].length > 0)
                                {
                                    vm.items[2][1] = vm.items[2][1].concat(_dashboard.response_15.files['OTROS'])
                                }

                                if(_dashboard.response_30.files['OTROS'].length > 0)
                                {
                                    vm.items[2][2] = vm.items[2][2].concat(_dashboard.response_30.files['OTROS'])
                                }

                                if(_dashboard.response_mes.files['OTROS'].length > 0)
                                {
                                    vm.items[2][3] = vm.items[2][3].concat(_dashboard.response_mes.files['OTROS'])
                                }

                                if(_dashboard.response_7.files['EXCEPCIONES'].length > 0)
                                {
                                    vm.items[3][0] = vm.items[3][0].concat(_dashboard.response_7.files['EXCEPCIONES'])
                                }

                                if(_dashboard.response_15.files['EXCEPCIONES'].length > 0)
                                {
                                    vm.items[3][1] = vm.items[3][1].concat(_dashboard.response_15.files['EXCEPCIONES'])
                                }

                                if(_dashboard.response_30.files['EXCEPCIONES'].length > 0)
                                {
                                    vm.items[3][2] = vm.items[3][2].concat(_dashboard.response_30.files['EXCEPCIONES'])
                                }

                                if(_dashboard.response_mes.files['EXCEPCIONES'].length > 0)
                                {
                                    vm.items[3][3] = vm.items[3][3].concat(_dashboard.response_mes.files['EXCEPCIONES'])
                                }

                                vm.dataSource.dataset[0].data = [
                                    { 'value': vm.fits_7 },
                                    { 'value': vm.grupos_7 },
                                    { 'value': vm.otros_7 },
                                    { 'value': vm.excepciones_7 }
                                ]

                                vm.dataSource.dataset[1].data = [
                                    { 'value': vm.fits_15 },
                                    { 'value': vm.grupos_15 },
                                    { 'value': vm.otros_15 },
                                    { 'value': vm.excepciones_15 }
                                ]

                                vm.dataSource.dataset[2].data = [
                                    { 'value': vm.fits_30 },
                                    { 'value': vm.grupos_30 },
                                    { 'value': vm.otros_30 },
                                    { 'value': vm.excepciones_30 }
                                ]

                                vm.dataSource.dataset[3].data = [
                                    { 'value': vm.fits_mes },
                                    { 'value': vm.grupos_mes },
                                    { 'value': vm.otros_mes },
                                    { 'value': vm.excepciones_mes }
                                ]
                            }, 10)
                        })
                        .catch((e) => {
                            console.log(e)
                        })
                }
            }
        }
    };
</script>
