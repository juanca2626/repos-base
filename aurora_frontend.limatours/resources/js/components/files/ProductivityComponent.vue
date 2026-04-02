<template>
    <div class="container">
        <div class="form">
            <div class="form-row justify-content-between">
                <div class="form-group col-md-3">
                    <label>
                        <strong>Rango de Fechas</strong>
                    </label>
                    <date-range-picker
                        :locale-data="locale_data"
                        :time-picker24-hour="timePicker24Hour"
                        :show-week-numbers="showWeekNumbers"
                        :ranges="false"
                        :auto-apply="true"
                        v-model="dateRange">
                    </date-range-picker>
                </div>
                <div class="form-group col-md-3" v-if="quantityTeams > 0">
                    <label>
                        <strong>Equipo</strong>
                    </label>
                    <b-form-select v-model="team" :options="teams" v-on:change="searchExecutives()" class="form-control ml-1">
                    </b-form-select>
                </div>
                <div class="form-group col-md-3">
                    <label>
                        <strong>Visualizar por</strong>
                    </label>
                    <b-form-select v-model="view" :options="views" class="form-control ml-1">
                    </b-form-select>
                </div>
                <div class="form-group col-md-3">
                    <label>
                        <strong>Tipo</strong>
                    </label>
                    <b-form-select v-model="type" :options="types" class="form-control">
                    </b-form-select>
                </div>
            </div>
            <div class="form-row justify-content-between">
                <div class="form-group col-md-3 reporte-boton">
                    <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                        Buscar
                    </button>
                </div>

                <div class="form-group col-md-3 reporte-boton">
                    <button class="btn btn-primary" v-bind:disabled="loading || quantity == 0" v-on:click="downloadExcel()">
                        Exportar Excel
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                <p class="mb-0">{{ translations.label.loading }}</p>
            </div>
            <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                <p class="mb-0">{{ translations.label.no_data }}</p>
            </div>

            <div class="mx-5" v-if="quantity > 0 && !loading">
                <button type="button" class="btn btn-danger mb-3" @click="_toggle(!expand)">
                    {{ expand ? 'CONTRAER TODO' : 'EXPANDIR TODO' }}
                </button>

                <div class="table-responsive">
                    <table class="table table-hover" id="_files">
                        <thead class="thead-light">
                            <tr>
                                <th>CÓDIGO</th>
                                <th>DESCRIPCIÓN</th>
                                <th>N° DE FILE</th>
                                <th v-for="col in columns" :key="col.key"
                                    class="cursor-pointer"
                                    @click="_sort(col.key)">
                                    {{ col.label }}
                                    <i :class="getSortIcon(col.key)"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(_file, f) in files">
                                <tr :id="f" :class="{ 'table-active font-weight-bold': isExpanded(f) }" :key="`f_${f}`">
                                    <td><a href="javascript:;" @click="showDetail(f)">{{ f }}</a></td>
                                    <td>
                                        <a href="javascript:;" @click="showDetail(f)">
                                            {{ _file.view === 'C' ? _file.items[0].razon : _file.items[0].razon_qr }}
                                        </a>
                                    </td>
                                    <td>{{ _file.quantity }}</td>
                                    <td>{{ _file.venta | formatPrice }}</td>
                                    <td>{{ _file.beneficio | formatPrice }}</td>
                                    <td>{{ formatPercent(_file.porcentaje) }}</td>
                                </tr>

                                <template v-if="isExpanded(f)">
                                    <template v-for="(_detail, d) in _file.detail">
                                        <tr class="bg-light shadow-sm" :key="`f_${f}_c_${d}`">
                                            <td class="pl-4"><a href="javascript:;" @click="showFiles(d)">{{ d }}</a></td>
                                            <td>
                                                <a href="javascript:;" @click="showFiles(d)">
                                                    {{ _file.view === 'C' ? _detail.items[0].razon_qr : _detail.items[0].razon }}
                                                </a>
                                            </td>
                                            <td>{{ _detail.quantity }}</td>
                                            <td>{{ _detail.venta | formatPrice }}</td>
                                            <td>{{ _detail.beneficio | formatPrice }}</td>
                                            <td>{{ formatPercent(_detail.porcentaje) }}</td>
                                        </tr>

                                        <template v-if="view_files.includes(d) || expand">
                                            <tr v-for="(_item, i) in _detail.items" :key="`f_${f}_c_${d}_i_${i}`" class="table-sm border-bottom">
                                                <td class="pl-5 text-muted small">{{ _item.nroref }}</td>
                                                <td class="text-muted small">{{ _item.nombre_file }}</td>
                                                <td class="text-muted small">{{ _item.nroref }}</td>
                                                <td class="text-muted small">{{ _item.venta | formatPrice }}</td>
                                                <td class="text-muted small">{{ _item.beneficio | formatPrice }}</td>
                                                <td class="text-muted small">{{ formatPercent(_item.porcentaje) }}</td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
#_files td, #_files td a {
    color:#373737;
    font-size:12px;
}
#_files .body td.child_last {
    font-size:12px!important;
}
</style>

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
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                startDate: moment().add('days', 2).format('Y-MM-DD'),
                minDate: moment().add('days', 2).format('Y-MM-DD'),
                dateRange: '',
                locale_data: {
                    direction: 'ltr',
                    format: moment.localeData().postformat('ddd D MMM'),
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                },
                bossFlag: '0',
                translations: [],
                views: {
                    'C': 'CLIENTE',
                    'E': 'ESPECIALISTA'
                },
                types: {
                    'QRV': 'QR VENTAS',
                    'QRR': 'QR RESERVA'
                },
                view: 'C',
                type: 'QRV',
                teams: [],
                team: 'TODOS',
                quantityTeams: 0,
                files: [],
                view_detail: [],
                view_files: [],
                x_type_sort: '',
                x_value_sort: '',
                expand: false,
                executives: [],
                quantityExecutives: 0,
                columns: [
                    { key: 'venta', label: 'VENTA' },
                    { key: 'beneficio', label: 'BENEFICIO' },
                    { key: 'porcentaje', label: '%' }
                ]
            }
        },
        created: function () {
            this.bossFlag = this.data.bossFlag
            this.translations = this.data.translations
            this.dateRange = this.data.dateRange

            this.searchExecutives()
            this.searchTeams()
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
        },
        computed: {

        },
        methods: {
            getSortIcon(key) {
                if (this.x_type_sort !== key) return 'fas fa-sort text-muted ml-1';
                return this.x_value_sort === 'asc' ? 'fas fa-chevron-up ml-1' : 'fas fa-chevron-down ml-1';
            },
            formatPercent(val) {
                return parseFloat(val).toFixed(0) + '%';
            },
            isExpanded(f) {
                return this.view_detail.includes(f) || this.expand;
            },
            _toggle: function (targetState) {
                this.expand = targetState;

                this.view_detail = [];
                this.view_files = [];

                if (this.expand) {
                    // Usamos un Set temporal para asegurar valores únicos (opcional pero recomendado)
                    const details = [];
                    const files = [];

                    Object.entries(this.files).forEach(([fileId, fileData]) => {
                        details.push(fileId);

                        if (fileData.detail) {
                            Object.keys(fileData.detail).forEach(itemId => {
                                files.push(itemId);
                            });
                        }
                    });

                    this.view_detail = details;
                    this.view_files = files;
                }
                // Si es false, los arrays se quedan vacíos [], lo que contrae todo automáticamente
            },
            _sort: function (_type) {
                let vm = this

                if(_type == vm.x_type_sort)
                {
                    vm.x_value_sort = 'asc'
                }
                else
                {
                    vm.x_value_sort = 'desc'
                }

                vm.x_type_sort = _type
                vm.search()
            },
            showDetail: function (_f) {
                let vm = this

                if(vm.view_detail.includes(_f))
                {
                    let index = vm.view_detail.indexOf(_f)
                    if (index > -1)
                    {
                        vm.view_detail.splice(index, 1)
                    }
                }
                else
                {
                    vm.view_detail.push(_f)
                }
            },
            showFiles: function (_i) {
                let vm = this

                if(vm.view_files.includes(_i))
                {
                    let index = vm.view_files.indexOf(_i)
                    if (index > -1)
                    {
                        vm.view_files.splice(index, 1)
                    }
                }
                else
                {
                    vm.view_files.push(_i)
                }
            },
            searchTeams: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/teams', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.teams = result.data.teams
                        this.quantityTeams = result.data.quantity

                        if(this.quantityTeams == 1)
                        {
                            this.team = result.data.team
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchExecutives: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/executives_user', {
                        lang: this.lang,
                        team: this.team
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            search: function () {
                // $('#_files').DataTable().destroy()
                this.view_detail = []
                this.loading = true
                this.files = []

                axios.post(
                    baseURL + 'search_productivity', {
                    dateRange: this.dateRange,
                    executives: this.executives,
                    team: this.team,
                    view: this.view,
                    type: this.type,
                    teams: this.teams,
                    type_sort: this.x_type_sort,
                    value_sort: this.x_value_sort
                })
                    .then((result) => {
                        this.loading = false
                        this.files = result.data.files
                        this.quantity = result.data.quantity

                        if(this.quantity > 0)
                        {
                            /*
                            setTimeout(function() {
                                $('#_files').DataTable({
                                    searching: false,
                                    language: {
                                        "sProcessing":     "Procesando...",
                                        "sLengthMenu":     "Mostrar _MENU_ registros",
                                        "sZeroRecords":    "No se encontraron resultados",
                                        "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
                                        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                                        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                                        "sInfoPostFix":    "",
                                        "sSearch":         "Buscar:",
                                        "sUrl":            "",
                                        "sInfoThousands":  ",",
                                        "sLoadingRecords": "Cargando...",
                                        "oPaginate": {
                                            "sFirst":    "Primero",
                                            "sLast":     "Último",
                                            "sNext":     "Siguiente",
                                            "sPrevious": "Anterior"
                                        },
                                        "oAria": {
                                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                                        },
                                        "buttons": {
                                            "copy": "Copiar",
                                            "colvis": "Visibilidad"
                                        }
                                    }
                                })
                            }, 10)
                             */
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            downloadExcel: function () {
                window.location = baseURL + 'export_excel?type=productivity&table='+this.view_detail.join(',')+','+this.view_files.join(',')
            }
        }
    };
</script>
