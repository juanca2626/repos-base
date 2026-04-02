<template>
    <div>
        <div class="container">
            <div class="container mb-3" v-if="show_form">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-paper-plane"></i> Envio de mensaje de Prueba
                    </div>
                    <div class="card-body">
                        <b-overlay :show="loading_button" rounded="sm">
                            <div v-on:click="toggleForm()"
                                style="right: 10px; position: absolute; top: 10px; cursor: pointer;">
                                <i class="fa fa-times"></i></div>
                            <form>
                                <div class="form-group">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <button type="button"
                                                v-bind:class="['btn', 'btn-lg', (format == 1) ? 'btn-success' : 'btn-secondary']"
                                                v-on:click="changeFormat(1)">
                                            <i class="fas fa-envelope"></i> E-mails <i class="fas fa-check-circle"
                                                                                    v-if="format == 1"></i>
                                        </button>
                                        <button type="button"
                                                v-bind:class="['btn', 'btn-lg', (format == 2) ? 'btn-success' : 'btn-secondary']"
                                                v-on:click="changeFormat(2)">
                                            <i class="fab fa-whatsapp"></i> WhatsApp <i class="fas fa-check-circle"
                                                                                        v-if="format == 2"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Tipo de mensaje</label>
                                            <select v-model="form.type" class="form-control border"
                                                    @change="changeTypeMessage()">
                                                <option value="" selected="selected" disabled="disabled">Seleccione..
                                                </option>
                                                <option value="1" v-if="format == 1 || format == 2">Una semana antes
                                                </option>
                                                <option value="2" v-if="format == 1 || format == 2">24 horas (seleccione el
                                                    primer día)
                                                </option>
                                                <option value="3" v-if="format == 1 || format == 2">Día a día</option>
                                                <option value="5" v-if="format == 1 || format == 2">Mensaje despedida
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>Nº de File</label>
                                            <input type="text" class="form-control border" v-model="form.file"
                                                v-on:change="searchPassengers()" placeholder="Nº File"
                                                :disabled="form.type == ''"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>Fecha de File</label>
                                            <select name="" id="" class="form-control border" v-model="form.date"
                                                    :disabled="form.type == ''">
                                                <option :value="date.date" v-for="date in dates_services">
                                                    {{date.date}}
                                                </option>
                                            </select>
                                            <!--                                    <date-picker class="datepicker form-control border" v-model="form.date"-->
                                            <!--                                                 :config="options"></date-picker>-->
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Pasajero</label>
                                            <v-select :options="all_passengers"
                                                    label="nrodoc"
                                                    v-model="form.passenger"
                                                    class="form-control border"
                                                    autocomplete="true"
                                                    :disabled="form.type == ''">
                                                <template slot="option" slot-scope="option">
                                                    <div class="d-center">
                                                        {{ option.nombre }}
                                                        <br><small>{{ option.nrodoc }}</small>
                                                    </div>
                                                </template>
                                                <template slot="selected-option" slot-scope="option">
                                                    <div class="selected d-center">
                                                        {{ option.nombre }}<br><small>{{ option.nrodoc }}</small>
                                                    </div>
                                                </template>
                                            </v-select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" v-bind:disabled="loading_button"
                                                class="btn btn-primary" v-on:click="submitForm()">
                                            <template v-if="loading_button">
                                                Cargando...
                                            </template>
                                            <template v-if="!loading_button">
                                                <i class="fa fa-paper-plane"></i> Enviar
                                            </template>
                                        </button>
                                    </div>
                                </div>
                                <table class="table table-hover" id="_senders" style="width:50%;">
                                    <thead>
                                    <tr>
                                        <th colspan="2" scope="row">
                                            <template v-if="format == 1">Correo electrónico</template>
                                            <template v-if="format == 2">Celular</template>
                                        </th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(sender, s) in senders">
                                        <td>
                                            <p class="text-sm-left text-danger" v-if="format == 2">
                                                cod. pais + numero de celular <i class="fas fa-arrow-right"></i>
                                                Ejm.:(<b>51</b>954114787)
                                            </p>
                                            <input v-bind:type="(format == 1) ? 'email' : 'tel'" class="form-control"
                                                v-model="senders[s]">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-lg"
                                                    v-if="s == 0"
                                                    v-on:click="addSender()">
                                                <i class="fa fa-plus"></i> Agregar
                                            </button>
                                            <button type="button" class="btn btn-danger btn-lg"
                                                    v-if="s > 0"
                                                    v-on:click="removeSender(s)">
                                                <i class="fa fa-trash"></i> Quitar
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                        </b-overlay>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                    <p class="mb-0">Cargando..</p>
                </div>
                <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                    <p class="mb-0">
                        No se encontró información para mostrar.
                        Por favor, intente con nuevos filtros.
                    </p>
                </div>
            </div>
            <div class="container-fluid" v-if="!show_form || (quantity > 0 && !loading)">
                <button v-if="!show_form" type="button"
                    class="btn btn-primary mt-3 mb-3" v-on:click="toggleForm()">
                    <i class="fa fa-paper-plane"></i> Envío de Mensaje
                </button>

                <div class=" mt-5">
                    <input type="number" class="form-control"
                        v-model="file" placeholder="Filtrar por número de FILE.."
                        @change="searchLogs(0)" />
                    <template v-if="quantity > 0 && !loading">
                        <table class="table table-hover mt-3">
                            <thead>
                                <tr>
                                    <th class="text-center">File</th>
                                    <th class="text-center">Fecha Inicio</th>
                                    <th class="text-center">Fecha Fin</th>
                                    <th>Cliente</th>
                                    <th class="text-center">Fecha / Hora de Envío</th>
                                    <th class="text-center"><i class="fa fa-cog"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(log, l) in logs">
                                    <tr>
                                        <td class="text-center">{{ log.file }}</td>
                                        <td class="text-center">{{ log.fecini | formDate }}</td>
                                        <td class="text-center">{{ log.fecout | formDate }}</td>
                                        <td>
                                            <strong class="text-danger">{{ log.codcli }} </strong>
                                            - {{ log.razon }}
                                        </td>
                                        <td class="text-center">
                                            {{ log.feclog | formDate }} {{ log.horlog }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-danger"
                                                    type="button" v-on:click="showMessagesByFile(log)"
                                                    title="Ver mensajes enviados">
                                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <nav aria-label="page navigation">
                            <div class="text-center">
                                <ul class="pagination">
                                    <li :class="{'page-item':true,'disabled':(page==0)}"
                                        @click="setPage(0)">
                                        <a class="page-link" href="#">&laquo;</a>
                                    </li>

                                    <li :class="{'page-item':true,'disabled':(page==0)}"
                                        @click="setPage(page)">
                                        <a class="page-link" href="#"><</a>
                                    </li>

                                    <template v-for="(_page, p) in pages">
                                        <li v-bind:key="'page-' + p" @click="setPage(p)" v-if="show_pages[p]"
                                            :class="{'page-item':true,'active':(p == page) }">
                                            <a class="page-link" href="javascript:;">{{ _page }}</a>
                                        </li>
                                    </template>

                                    <li :class="{'page-item':true,'disabled':(page == pages - 1)}"
                                        @click="setPage(page + 1)">
                                        <a class="page-link" href="#">></a>
                                    </li>

                                    <li :class="{'page-item':true,'disabled':(page == pages - 1)}"
                                        @click="setPage(pages - 1)">
                                        <a class="page-link" href="#">&raquo;</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </template>
                </div>
            </div>
        </div>

        <div class="modal modal--cotizacion" v-if="detail.file != undefined" id="modal" tabindex="-1" role="dialog"
             style="overflow: scroll;">
            <div class="modal-dialog modal--cotizacion__document" role="document">
                <div class="modal-content modal--cotizacion__content">
                    <div class="modal-header">
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="modal--cotizacion__header">
                            <h3 class="modal-title"><b>FILE: {{ detail.file }}</b></h3>
                        </div>
                        <div class="modal--cotizacion__body">
                            <div class="d-block">
                                <div class="container-fluid p-0">
                                    <template v-if="loading_modal">
                                        <div class="alert alert-warning">Cargando..</div>
                                    </template>
                                    <template v-if="!loading_modal">
                                        <div class="d-flex">
                                            <div class="mt-3 container-fluid p-0">
                                                <table class="table table-hover" id="_details"
                                                       v-show="details.length > 0 && !loading_modal">
                                                    <thead>
                                                    <tr>
                                                        <th>Fecha / Hora</th>
                                                        <th>Nombre</th>
                                                        <th>Email / Celular</th>
                                                        <th>Tipo Mensaje</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(detail, d) in details">
                                                        <td class="text-center">{{ detail.feclog | formDate }} {{
                                                            detail.horlog }}
                                                        </td>
                                                        <td class="text-center">{{ detail.nombre }}</td>
                                                        <td class="text-center">{{ detail.email }}</td>
                                                        <td class="text-center">
                                                            <div>
                                                                <span class="label label-warning"
                                                                      v-if="detail.tipomensaje == 1">
                                                                    <i class="fa fa-envelope" aria-hidden="true"></i> 1 semana antes</span>
                                                                <span class="label label-default"
                                                                      v-if="detail.tipomensaje == 2">
                                                                    <i class="fa fa-envelope" aria-hidden="true"></i> 24 horas antes</span>
                                                                <span class="label label-primary"
                                                                      v-if="detail.tipomensaje == 3">
                                                                    <i class="fa fa-envelope" aria-hidden="true"></i> Día a día</span>
                                                                <span class="label label-info"
                                                                      v-if="detail.tipomensaje == 4">
                                                                    <i class="fa fa-envelope" aria-hidden="true"></i> Ultimo dia</span>
                                                                <span class="label label-danger"
                                                                      v-if="detail.tipomensaje == 5">
                                                                    <i class="fa fa-envelope" aria-hidden="true"></i> Despedida</span>
                                                                <span class="label label-success"
                                                                      v-if="detail.tipomensaje == 6">
                                                                    <i class="fa fa-whatsapp" aria-hidden="true"></i> Mensaje WhatsApp
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ detail.estado }}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        props: ['translations'],
        data: () => {
            return {
                quantity: 0,
                logs: [],
                take: 10,
                pages: 0,
                page: 0,
                loading: true,
                loading_button: false,
                loading_modal: true,
                query: '',
                detail: {},
                details: [],
                format: 1,
                senders: [''],
                show_form: false,
                form: {
                    date: '',
                    type: '',
                    file: '',
                },
                all_passengers: [],
                dates_services: [],
                options: {
                    format: 'DD/MM/YYYY',
                },
                show_pages: [],
                view_pages: 10,
                file: '',
            }
        },
        created: function () {

        },
        mounted: function () {
            this.lang = localStorage.getItem('lang')
            this.flag = localStorage.getItem('bossFlag')

            this.searchLogs(0)
        },
        computed: {},
        methods: {
            validatePagination: function () {
                this.view_pages = 10

                for(let p=0;p<this.pages;p++)
                {
                    this.show_pages[p] = false

                    if(this.page < this.view_pages)
                    {
                        if(this.view_pages > 0)
                        {
                            this.view_pages -= 1
                            this.show_pages[p] = true
                        }
                    }
                    else
                    {
                        if(this.page >= (this.pages - (this.view_pages) / 2))
                        {
                            if(p >= (pages - this.view_pages))
                            {
                                this.show_pages[p] = true
                            }
                        }
                        else
                        {
                            if(p >= parseFloat(this.page - parseFloat(this.view_pages / 2)) && p <= parseFloat(this.page + parseFloat(this.view_pages / 2)))
                            {
                                this.show_pages[p] = true
                            }
                        }
                    }
                }

            },
            toggleForm: function () {
                this.show_form = !this.show_form
                this.form = {
                    date: '',
                    type: '',
                    file: '',
                }
            },
            searchPassengers: function () {
                if (this.form.file != undefined && this.form.file != '') {
                    this.all_passengers = []
                    this.loading_button = true
                    axios.post(
                        baseExternalURL + 'api/masi/search_paxs_by_file', {
                            nrofile: this.form.file
                        }
                    )
                        .then((result) => {
                            this.loading_button = false
                            this.all_passengers = result.data.data.passengers
                            let dates = []
                            result.data.data.dates.forEach(function (item) {
                                dates.push({
                                    'date': moment(item.date, 'YYYY-MM-DD').format('DD/MM/YYYY'),
                                })
                            })

                            if (dates.length > 0) {
                                if (this.form.type == 1) {
                                    this.form.date = dates[0].date
                                }

                                if (this.form.type == 5) {
                                    this.form.date = dates[dates.length - 1].date
                                }
                            }
                            this.dates_services = dates
                        })
                        .catch((e) => {
                            this.loading_button = false
                        })
                } else {
                    this.all_passengers = []
                    this.dates_services = []
                }
            },
            processForm: function () {
                this.loading_button = true

                axios.post(
                    baseMasiExternalURL + 'api/mailing', {
                        format: this.format,
                        form: this.form,
                        senders: this.senders
                    }
                )
                    .then((result) => {
                        this.loading_button = false

                        if (result.data.status == true) {
                            this.$toast.success('Correo de prueba enviado correctamente..', {
                                // override the global option
                                position: 'top-right'
                            })
                        } else {
                            this.$toast.error('Ocurrió un error. ' + result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        this.loading_button = false
                    })
            },
            validPhone: function (str) {
                let phoneformat = /^[\+]?[0-9]{2,5}[0-9]{9}$/
                if (str.match(phoneformat)) {
                    return true
                } else {
                    this.$toast.error('Ingrese un número de teléfono válido para continuar..', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }
            },
            validEmail: function (str) {
                let mailformat = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/
                if (str.match(mailformat)) {
                    return true
                } else {
                    this.$toast.error('Ingrese un correo electrónico válido para continuar..', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }
            },
            validSender: function (s) {
                return (this.format == 1) ? this.validEmail(this.senders[s]) : this.validPhone(this.senders[s])
            },
            validateSenders: function () {
                let flag = true

                this.senders.forEach((sender, s) => {

                    if (flag) {
                        if (sender == '') {
                            flag = false
                            this.$toast.error('Ingrese un destinatario para continuar..', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                        if (this.form.type != undefined && this.form.type == '') {
                            flag = false
                            this.$toast.error('Seleccione un tipo de envío para continuar..', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                        if (this.form.file != undefined && this.form.file == '') {
                            flag = false
                            this.$toast.error('Ingrese un file para continuar..', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                        if (this.form.date != undefined && this.form.date == '') {
                            flag = false
                            this.$toast.error('Ingrese una fecha para continuar..', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                        if (this.form.passenger == undefined && this.form.passenger == '') {
                            flag = false
                            this.$toast.error('Seleccione un pasajero para continuar..', {
                                // override the global option
                                position: 'top-right'
                            })
                            return false
                        }

                        if (this.validSender(s)) {
                            if ((this.senders.length - 1) == s) {
                                this.processForm()
                            }
                        } else {
                            flag = false
                        }
                    }
                })
            },
            submitForm: function () {
                this.validateSenders()
            },
            changeFormat: function (_format) {
                this.format = _format
                this.form.type = ''

                this.senders = ['']
            },
            addSender: function () {
                this.senders.push('')
            },
            removeSender: function (s) {
                this.senders.splice(s, 1)
            },
            // file, codcli, searchAllLog
            setPage: function (_page) {
                this.page = _page
                this.searchLogs()
            },
            searchLogs: function () {
                this.loading = true
                this.logs = [];

                axios.post(
                    baseExternalURL + 'api/masi/search_logs', {
                        identi: 'E',
                        usuario: '',
                        total: '',
                        file: this.file,
                        first: this.take,
                        skip: this.page,
                    }
                )
                    .then((result) => {
                        this.loading = false

                        console.log(result);

                        if(this.file != '')
                        {
                            if(result.data[this.file] != undefined)
                            {
                                this.logs = [
                                    result.data[this.file]
                                ];
                            }
                        }
                        else
                        {
                            this.logs = result.data.data;
                        }

                        this.quantity = this.logs.length
                        this.pages = result.data.pages ?? 0;

                        this.validatePagination()
                    })
                    .catch((e) => {
                        this.loading = false
                        this.logs = []
                        this.quantity = 0

                        console.log(e)
                    })
            },
            showMessagesByFile: function (log) {
                $('#_details').DataTable().destroy()
                this.detail = log
                this.loading_modal = true

                setTimeout(function () {
                    $('#modal').modal('show')
                }, 10)

                axios.post(
                    baseExternalURL + 'api/masi/search_all_logs', {
                        file: log.file,
                        codcli: log.codcli.trim()
                    }
                )
                    .then((result) => {
                        this.loading_modal = false
                        this.details = result.data.data

                        if (this.details.length > 0) {
                            setTimeout(function () {
                                $('#_details').DataTable({
                                    searching: true,
                                    paging: true,
                                    ordering: true,
                                    language: {
                                        'sProcessing': 'Procesando...',
                                        'sLengthMenu': 'Mostrar _MENU_ registros',
                                        'sZeroRecords': 'No se encontraron resultados',
                                        'sEmptyTable': 'Ningún dato disponible en esta tabla =(',
                                        'sInfo': 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
                                        'sInfoEmpty': 'Mostrando registros del 0 al 0 de un total de 0 registros',
                                        'sInfoFiltered': '(filtrado de un total de _MAX_ registros)',
                                        'sInfoPostFix': '',
                                        'sSearch': 'Buscar:',
                                        'sUrl': '',
                                        'sInfoThousands': ',',
                                        'sLoadingRecords': 'Cargando...',
                                        'oPaginate': {
                                            'sFirst': 'Primero',
                                            'sLast': 'Último',
                                            'sNext': 'Siguiente',
                                            'sPrevious': 'Anterior'
                                        },
                                        'oAria': {
                                            'sSortAscending': ': Activar para ordenar la columna de manera ascendente',
                                            'sSortDescending': ': Activar para ordenar la columna de manera descendente'
                                        },
                                        'buttons': {
                                            'copy': 'Copiar',
                                            'colvis': 'Visibilidad'
                                        }
                                    }
                                })
                            }, 10)
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        this.logs = []
                        this.quantity = 0

                        console.log(e)
                    })
            },
            changeTypeMessage: function () {
                if (this.dates_services.length > 0) {
                    if (this.form.type != 5) {
                        this.form.date = this.dates_services[0].date
                    }

                    if (this.form.type == 5) {
                        this.form.date = this.dates_services[this.dates_services.length - 1].date
                    }
                }

                console.log(this.form.type)
            }
        }
    }
</script>
