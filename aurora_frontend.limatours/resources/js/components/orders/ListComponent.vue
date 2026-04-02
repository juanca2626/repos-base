<template>
    <div>
        <div class="container">
            <div class="form p-5">
                <div class="d-flex justify-content-between align-items-center py-4">
                    <div class="col">
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
                    <div class="col">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <v-select class="form-control p-3" v-model="product" :options="products"
                                  :reduce="product => product.value" label="text">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.text }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.text }}
                                </div>
                            </template>
                        </v-select>
                        <!-- b-form-select v-model="product" :reduce="products => products.value"
                                       :options="products" class="form-control ml-1">
                        </b-form-select -->
                    </div>
                    <div class="col" v-bind:disabled="check != 'E'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value"
                                  :options="executives" v-model="executive" class="form-control p-3">
                            <template slot="option" slot-scope="option">
                                <div class="d-center">
                                    {{ option.text }}
                                </div>
                            </template>
                            <template slot="selected-option" slot-scope="option">
                                <div class="selected d-center">
                                    {{ option.text }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                    <div class="col">
                        <label>
                            <strong>Buscar por pedido</strong>
                        </label>
                        <input type="number" class="form-control" v-model="query" />
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center py-4">
                    <div class="col">
                        <label>
                            <strong>Equipo</strong>
                        </label>
                        <b-form-select v-model="team" @change="searchExecutives()"
                            :options="teams" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="col">
                        <label>
                            <strong>Estado</strong>
                        </label>
                        <b-form-select v-model="status" :options="all_status" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="col-auto">
                        <label>
                            <strong>Filtrar por</strong>
                        </label>
                        <div class="text-muted mt-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check"
                                       v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check"
                                       v-model="check" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" v-bind:disabled="loading || quantity == 0" v-on:click="downloadExcel()">
                            Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="container">
                <template v-if="loading && message_loading">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                            role="progressbar" :aria-valuenow="getPercentPages" aria-valuemin="0" aria-valuemax="100"
                            :style="`width: ${getPercentPages}%`"></div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-3">
                        <p class="mb-0">{{ this.message_loading }}</p>
                    </div>
                </template>
                <div class="alert alert-warning" v-if="quantity == 0 && !loading && message_loading !== ''">
                    <p class="mb-0">No se encontró información para mostrar. Por favor, intente con nuevos filtros.</p>
                </div>
            </div>

            <div class="container-fluid" v-if="quantity > 0 && !loading">
                <table class="table table-hover" id="_orders">
                    <thead>
                    <tr>
                        <th><i class="fa fa-clock-o fa-2x" aria-hidden="true"></i></th>
                        <th>Número Pedido</th>
                        <th>Fecha Pedido</th>
                        <th>Fecha de Respuesta</th>
                        <th>Número de Cotización</th>
                        <th>Programación Referente</th>
                        <th>Monto Estimado</th>
                        <th>Número File</th>
                        <th>Monto Concretado</th>
                        <th>Fecha IN - cotización</th>
                        <th>Fecha de viaje estimado</th>
                        <th>Especialista</th>
                        <th>Producto</th>
                        <th>Cliente</th>
                        <th><i class="fa fa-comment"></i></th>
                        <th><i class="fa fa-cog"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(order, o) in orders">
                        <td><div v-b-tooltip v-bind:title="order.horas + ' hora(s)'">
                            <i v-bind:class="['fa', 'fa-circle', 'fa-2x', 'text-' + order.class ]" aria-hidden="true"></i>
                        </div></td>
                        <td style="width:85px; text-align:center;">{{order.nroped}} <small style="display:none;">{{ order.nroord }}</small>
                            <div v-bind:class="'etiqueta_' + order.nroped">
                                <span v-for="label in order.etiquetas" v-show="label.id > 0" class="badge" v-bind:style="'cursor:default !important;background: #' + label.colbac + '; color: #' + label.coltex" href="javascript:;"><b>{{ label.etiqueta }}</b></span>
                            </div>
                        </td>
                        <td>{{ order.fecrec | formDate }} {{order.horrec}}</td>
                        <td>{{ order.fecres | formDate }} {{order.horres}}</td>
                        <td>{{ (order.nroref != '' && order.nroref != null) ? order.nroref : '' }}
                            <template v-if="order.chkpro > 0 && order.chkpro != null">
                                <br /><small>{{ order.chkpro_desc }}</small>
                            </template>
                        </td>
                        <td>{{ order.nompaq }}</td>
                        <td><a target="_blank" v-bind:href="updatePriceEstimated(o)" style="color:inherit!important;">{{order.price_estimated}}</a></td>
                        <td class="center">
                            {{ order.nrofile }}
                        </td>
                        <td>{{ order.price_end }}</td>
                        <td>{{ order.fectravel | formDate }}</td>
                        <td>{{ ((order.fectravel_tca != '' && order.fectravel_tca != null) ? (order.fectravel_tca) : (order.fectravel)) | formDate }}</td>
                        <td class="center"><b>{{ order.codusu }}</b></td>
                        <td class="center">{{ order.producto }}</td>
                        <td class="center"><b>{{ order.codigo }}</b></td>
                        <td nowrap style="width:100px !important;">
                            <b-popover v-bind:target="'popover-target-' + o" triggers="hover" placement="top">
                                <b>Nombre de Paxs / Grupo</b><br />
                                {{ order.nompax }}<br />
                                <div v-if="(order.observ != undefined && order.observ != null)">
                                    <div v-if="order.observ.trim() != ''">
                                        <br />{{ order.observ }}
                                    </div>
                                </div>
                            </b-popover>
                            <span v-bind:id="'popover-target-' + o" v-if="order.nompax != undefined && order.nompax != null">
                                {{ ( order.nompax.trim() ).substr( 0, 20 ) }}...
                            </span>
                        </td>
                        <td rowspan style="width:120px !important;">
                            <!-- button type="button" data-toggle="modal" class="btn btn-secondary" data-target="#email-modal"
                                    style="font-size:1.6rem!important;"
                                    v-on:click="toggleModal( o, 'email', { order: order, translations: translations } )">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </button -->
                            <div class="btn-group dropleft" role="group" aria-label="Button group with nested dropdown">
                                <button id="cogs" type="button" class="btn btn-secondary dropdown-toggle border-0"
                                        style="font-size:1.6rem!important;"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="cogs">
                                    <a v-on:click="toggleModal( o, 'email', { order: order, translations: translations } )"
                                        data-toggle="modal" href="javascript:;" style="font-size:14px;"
                                        data-target="#email-modal" class="dropdown-item text-dark">
                                        <i class="fas fa-envelope" aria-hidden="true"></i>
                                        Correos asociados
                                    </a>
                                    <template v-if="1 == 2">
                                        <a href="javascript:;" style="font-size:14px;"
                                            data-toggle="modal" data-target="#tags-modal"
                                            v-on:click="toggleModal( o, 'tags', { identi: 'O', order: order, translations: translations } )"
                                            class="dropdown-item text-dark">
                                            <i class="fas fa-tag" aria-hidden="true"></i>
                                            Etiquetar
                                        </a>
                                    </template>
                                    <template v-if="flag == 1">
                                        <a href="javascript:;" style="font-size:14px;"
                                            data-toggle="modal" data-target="#reassign-modal"
                                            v-on:click="toggleModal( o, 'reassign', { order: order, translations: translations } )"
                                            class="dropdown-item text-dark">
                                            <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                                            Reasignar pedido
                                        </a>
                                    </template>

                                    <!-- (order.ESTADO == 'OK' || order.NROREF != '' ||
                                                    order.NROFILE != '' || order.ESTADO == 'XL' || )-->
                                    <a href="javascript:;" style="font-size:14px;"
                                        v-on:click="toggleModal( o, 'update', { order: order, products: products, translations: translations } )"
                                        data-toggle="modal" data-target="#update-modal"
                                        class="dropdown-item text-dark">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                        Editar pedido
                                    </a>

                                    <template v-if="flag == 1">
                                        <a href="javascript:;" style="font-size:14px;"
                                            v-on:click="toggleModal( o, 'update-response', { order: order, translations: translations } )"
                                            data-toggle="modal" data-target="#update-response-modal"
                                            class="dropdown-item text-dark">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                            Editar Respuesta
                                        </a>
                                    </template>

                                    <!-- flag == 1 && order.ESTADO == 'OK' -->
                                    <a href="javascript:;" style="font-size:14px;"
                                        v-on:click="toggleModal( o, 'update-obs', { order: order, translations: translations } )"
                                        data-toggle="modal" data-target="#update-obs-modal"
                                        class="dropdown-item text-dark">
                                        <!-- i class="fas fa-pen" aria-hidden="true"></i -->
                                        <i class="fas fa-file" aria-hidden="true"></i> Observaciones
                                    </a>

                                    <!-- order.ESTADO != 'XL' && flag == 1 -->
                                    <template v-if="order.estado != 'XL' && flag == 1">
                                        <a href="javascript:;" style="font-size:14px;"
                                            v-on:click="_remove(order.nroped, order.nroord)"
                                            class="dropdown-item text-dark">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                            Anular pedido
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <template v-if="!loading">
            <component ref="template" v-if="modal != '' && modal != null" v-bind:is="modal" v-bind:data="dataModal"></component>
        </template>
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
        props: ['translations'],
        data: () => {
            return {
                data: [],
                flag: 0,
                lang: '',
                loading: true,
                timePicker24Hour: false,
                showWeekNumbers: false,
                singleDatePicker: true,
                startDate: '',
                minDate: '',
                locale_data: {
                    direction: 'ltr',
                    format: 'DD-MM-YYYY',
                    separator: ' - ',
                    applyLabel: 'Guardar',
                    cancelLabel: 'Cancelar',
                    weekLabel: 'W',
                    customRangeLabel: 'Rango de Fechas',
                    daysOfWeek: moment.weekdaysMin(),
                    monthNames: moment.monthsShort(),
                    firstDay: moment.localeData().firstDayOfWeek()
                },
                teams: [],
                team: 'TODOS',
                executives: [],
                quantityExecutives: 0,
                executive: '',
                check: 'E',
                all_status: {
                    'PE': 'PENDIENTES',
                    'OK' : 'ATENDIDOS',
                    'XL': 'ANULADOS',
                    'ALL': 'TODOS'
                },
                status: 'ALL',
                query: '',
                quantity: 0,
                index: 0,
                orders: [],
                quantityTeams: 0,
                products: [],
                product: '',
                quantityProducts: 0,
                dateRange: '',
                modal: '',
                dataModal: {},
                page: 0,
                pages: 1,
                message_loading: '',
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.flag = localStorage.getItem('bossFlag')
            this.searchTeams()
            this.searchExecutives()
            this.searchProducts()
            this.customer = localStorage.getItem('client_code')
        },
        computed: {
            getPercentPages() {
                if (this.pages > 0) {
                    return Math.floor((this.page / this.pages) * 100);
                }
                return 0;
            }
        },
        methods: {
            toggleModal: function(index, _modal, _data) {
                this.index = index
                this.dataModal = _data
                this.modal = 'order-' + _modal + '-modal'
                let vm = this

                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            _closeModal: function () {
                $('.modal').modal('hide')

                let vm = this
                setTimeout(function() {
                    vm.modal = ''
                }, 10)
            },
            downloadExcel: function () {
                window.location = baseURL + 'export_excel?type=orders&table=';
            },
            _updateOrder: function () {
                this.search()
            },
            _remove: function (nroped, nroord) {
                this.loading = true

                axios.post(
                    baseURL + 'orders/remove', {
                        lang: this.lang,
                        nroped: nroped,
                        nroord: nroord
                    }
                )
                    .then((result) => {
                        console.log("Eliminado: ", result.data);
                        this.loading = false
                        this.search()
                    })
                    .catch((e) => {
                        this.loading = false;
                        console.log(e)
                    })
            },
            updatePriceEstimated: function (_order) {
                let order = this.orders[_order]

                if(order.nroref_identi == 'B' && order.nroref != '' && order.nroref != null)
                {
                    return baseExternalURL + 'update_amounts_quote?quote_id=' + order.nroref + '&client_id=' + order.codigo
                }
                else
                {
                    return false
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

                        if(this.quantityTeams > 0)
                        {
                            localStorage.setItem('bossFlag', 1)
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchExecutives: function () {
                this.executive = ''
                this.quantityExecutives = 0

                axios.post(
                    baseURL + 'board/executives_user', {
                        lang: this.lang,
                        team: this.team
                    }
                )
                    .then((result) => {
                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchProducts: function () {
                this.loading = true
                axios.post(
                    baseURL + 'board/products', {
                        lang: this.lang,
                        ignore: '3'
                    }
                )
                    .then((result) => {
                        this.loading = false

                        const allowedValues = [1, 2, 5, ''];
                        this.products = result.data.products.filter(
                            (product) => (allowedValues.includes(product.value) || allowedValues.includes(parseInt(product.value)))
                        );

                        console.log("Productos: ", this.products);

                        this.quantityProducts = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            refreshFile: function (_order) {

            },
            delay: async function (ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },
            makeRequestWithRetry: async function (url, data, maxRetries = 3) {
                let lastError;

                for (let attempt = 1; attempt <= maxRetries; attempt++) {
                    try {
                        const response = await axios.post(url, data);

                        if(response.data.type === 'success')
                        {
                            this.page++;
                            return response;
                        }
                        else
                        {
                            lastError = response.data.message ?? '';

                            if (attempt < maxRetries) {
                                await this.delay(attempt * 1000);
                                continue;
                            }
                        }
                    } catch (error) {
                        lastError = error;

                        // Solo reintentar para errores 503 o de conexión
                        if (error.response?.status === 503 || error.code === 'ECONNABORTED') {
                            console.log(`Intento ${attempt} fallido. Reintentando en ${attempt * 1000}ms...`);

                            if (attempt < maxRetries) {
                                await this.delay(attempt * 1000);
                                continue;
                            }
                        }

                        // Si no es un error 503, no reintentar
                        throw error;
                    }
                }

                throw lastError;
            },
            // Función para procesar grupos de páginas
            processInBatches: async function (pages, batchSize = 10) {
                this.customer = localStorage.getItem('client_code') ?? '';

                if(this.check == 'C' && this.customer == '')
                {
                    this.customer = 'TODOS'
                }

                const baseParams = {
                    lang: this.lang,
                    type: this.check,
                    user: this.check === 'C' ? this.customer : this.executive,
                    status: this.status,
                    dateRange: this.dateRange,
                    team: this.team,
                    product: this.product,
                    nroped: this.query
                };

                let stopProcessing = false;

                for (let i = 0; i < pages.length; i += batchSize) {
                    if (stopProcessing) break;

                    const batch = pages.slice(i, i + batchSize);
                    this.message_loading = `Procesando página ${Math.floor(i/batchSize) + 1} de ${Math.ceil(pages.length/batchSize)}.. Espere un momento..`;

                    const batchPromises = batch.map(page =>
                        this.makeRequestWithRetry(`${baseURL}search_orders`, {
                            ...baseParams,
                            limit: page
                        }, 3)
                    );

                    const batchResponses = await Promise.all(batchPromises);

                    for (const response of batchResponses) {
                        const orders = response.data?.orders;

                        if (Array.isArray(orders)) {
                            if (orders.length === 0) {
                                stopProcessing = true;
                            }
                            this.orders.push(...orders);
                        }
                    }

                    if (!stopProcessing) {
                        await this.delay(1000);
                    } else {
                        throw new Error('STOP_BATCH_PROCESS');
                    }
                }
            },
            search: async function () {
                this.message_loading = '';
                this.page = 0;

                if(this.dateRange == '' && this.query == '')
                {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true
                this.orders = [];
                this.quantity = 0;

                const dataTableConfig = {
                    searching: true,
                    language: {
                        "sProcessing": "Procesando...",
                        "sLengthMenu": "Mostrar _MENU_ registros",
                        "sZeroRecords": "No se encontraron resultados",
                        "sEmptyTable": "Ningún dato disponible en esta tabla =(",
                        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sSearch": "Buscar:",
                        "sUrl": "",
                        "sInfoThousands": ",",
                        "sLoadingRecords": "Cargando...",
                        "oPaginate": {
                            "sFirst": "Primero",
                            "sLast": "Último",
                            "sNext": "Siguiente",
                            "sPrevious": "Anterior"
                        },
                        "oAria": {
                            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        },
                        "buttons": {
                            "copy": "Copiar",
                            "colvis": "Visibilidad"
                        }
                    }
                };

                let totalPages = 1;

                if(this.dateRange && !this.query)
                {
                    const response = await axios.post(`${baseURL}total_orders`, {
                        dateRange: this.dateRange,
                    });

                    totalPages = response.data.pages ?? 0;
                    this.pages = totalPages;
                }

                this.message_loading = 'Cargando.. Espere un momento..';

                try {
                    const allPages = Array.from({ length: totalPages }, (_, i) => i + 1);
                    await this.processInBatches(allPages, 7);

                } catch (error) {
                    if (error.message === 'STOP_BATCH_PROCESS') {
                        console.warn('Proceso detenido: Se encontró una página con un array de pedidos vacío.');
                    } else {
                        console.error('Error grave al cargar:', error);
                    }
                } finally {
                    this.quantity = this.orders.length;
                    this.loading = false;
                    this.$nextTick(() => {
                        $('#_orders').DataTable(dataTableConfig);
                    });
                }
            }
        }
    };
</script>
