@extends('layouts.app')
@section('content')
    <section class="page-central">
        <div class="container">
            <div id="_overlay"></div>

            <nav-central-bookings></nav-central-bookings>

            <!-- Tabla principal -->
            <b-table
                :items="services"
                :fields="fields"
                hover
                bordered
                small
                responsive
                :busy="loading"
                tbody-tr-td-class="align-middle"
            >
                <!-- Loader -->
                <template #table-busy>
                    <div class="text-center text-muted my-3">
                        <b-spinner small class="mr-2"></b-spinner>
                        Cargando servicios...
                    </div>
                </template>
                <template #cell(status)="row">
                    <b-badge
                        :variant="getStatusVariant(row.item.status)"
                        pill
                    >
                        @{{ getStatusLabel(row.item.status) }}
                    </b-badge>
                </template>
                <template #cell(actions)="row">
                    <button class="btn btn-detail-pentagrama btn-sm" @click="openDetail(row.item)">
                         Ver detalle
                    </button>
                    <button class="btn btn-process-pentagrama btn-sm ml-2" :disabled="isProcessDisabled(row.item)" @click="processService(row.item)">
                        Procesar
                    </button>
                </template>
            </b-table>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                <b-form-select
                    class="w-25"
                    v-model="limit"
                    :options="limits"
                    @change="onLimitChange"
                ></b-form-select>

                <b-pagination
                    v-if="!loading && pagination.total > limit"
                    :key="pagination.total + '-' + limit"
                    v-model="pageChosen"
                    :total-rows="pagination.total"
                    :per-page="limit"
                    align="right"
                    @change="onPageChange"
                ></b-pagination>
            </div>

            <!-- Modal de detalle (LEGACY) -->
            <div id="detail-modal"
                 class="modal fade"
                 tabindex="-1"
                 role="dialog"
                 aria-hidden="true"
                 ref="detailModal">

                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <!-- HEADER -->
                        <div class="modal-header">
                            <button type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- BODY -->
                        <div class="modal-body">

                            <h5 class="mb-3">
                                Servicio: @{{ selectedService.passenger }}
                            </h5>

                            <b-table :items="detailItems" :fields="detailFields" bordered small>
                                <template #cell(index)="row">
                                    @{{ row.index + 1 }}
                                </template>
                            </b-table>

                        </div>

                    </div>
                </div>
            </div>
            <!-- /Modal de detalle -->

            <!-- Modal de ERRORES (LEGACY) -->
            <div id="errors-modal"
                 class="modal fade"
                 tabindex="-1"
                 role="dialog"
                 aria-hidden="true"
                 ref="errorsModal">

                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <!-- HEADER -->
                        <div class="modal-header">
                            <button type="button"
                                    class="close text-white"
                                    data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- BODY -->
                        <div class="modal-body" style="max-height:70vh; overflow-y:auto;">

                            <!-- Error general -->
                            <div v-if="apiErrorMessage" class="alert alert-danger">
                                @{{ apiErrorMessage }}
                            </div>

                            <div v-if="!apiErrors || apiErrors.length === 0"
                                 class="text-muted">
                                No hay errores para mostrar.
                            </div>

                            <!-- Agrupado por entidad -->
                            <div v-for="(list, entity) in groupedErrors"
                                 :key="entity"
                                 class="card border-danger mb-2">

                                <div class="card-body py-2">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                <span class="badge badge-danger mr-2">
                                    @{{ entity.toUpperCase() }}
                                </span>
                                            <span class="text-muted">
                                    (@{{ list.length }})
                                </span>
                                        </h6>
                                    </div>

                                    <ul class="list-group list-group-flush">
                                        <li v-for="(e, idx) in list"
                                            :key="entity + '-' + idx"
                                            class="list-group-item py-2">

                                            <div class="d-flex align-items-start">

                                            <span class="badge badge-danger mr-2">
                                                @{{ e.code }}
                                            </span>

                                                <div class="flex-grow-1">
                                                    <div class="font-weight-bold">
                                                        @{{ e.message }}
                                                    </div>
                                                    <small v-if="e.description"
                                                           class="text-muted d-block">
                                                        Descripción: @{{ e.description }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        Código: @{{ e.external_service_id || '-' }}
                                                    </small>
                                                </div>

                                            </div>

                                        </li>
                                    </ul>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            <!-- /Modal de ERRORES -->
        </div>
    </section>
@endsection
@section('js')
    <script>
        new Vue({
            el: "#app",
            data: {
                apiErrors: [],
                apiErrorMessage: '',

                services: [],
                loading: false,

                // paginación
                pageChosen: 1,
                limit: 5,
                limits: [5, 10, 20],

                pagination: {
                    total: null,
                    last_page: null
                },

                // filtros existentes
                query_custom: "",
                _filter: "",
                _order: "",
                checkViewClosed: 0,
                checkViewPassed: 0,

                selectedService: {},
                detailItems: [],

                // Campos de la tabla principal
                fields: [
                    {
                        key: "id",
                        label: "ID",
                        tdClass: "align-middle"
                    },
                    {
                        key: "passenger",
                        label: "Pasajero",
                        tdClass: "align-middle"
                    },
                    {
                        key: "quote_number",
                        label: "N° Cotización",
                        // formatter: value => value.quote_number || '-',
                        tdClass: "align-middle"
                    },
                    {
                        key: "file_number",
                        label: "File",
                        // formatter: value => value.file_number || '-',
                        tdClass: "align-middle"
                    },
                    {
                        key: "status",
                        label: "Estado",
                        tdClass: "align-middle"
                    },
                    {
                        key: "created_at",
                        label: "Fecha Creación",
                        tdClass: "align-middle"
                    },
                    {
                        key: "actions",
                        label: "Acciones"
                    }
                ],

                // Campos del detalle
                detailFields: [
                    {
                        key: "index",
                        label: "#",
                        tdClass: "align-middle"
                    },
                    {
                        key: "external_service_id",
                        label: "Codigo Servicio",
                        tdClass: "align-middle"
                    },
                    {
                        key: "type_service",
                        label: "Tipo Servicio",
                        tdClass: "align-middle"
                    },
                    {
                        key: "external_service_description",
                        label: "Descripción",
                        tdClass: "align-middle"
                    },
                    {
                        key: "single_date",
                        label: "Fecha",
                        tdClass: "align-middle",
                        formatter: value => value ? new Date(value).toLocaleDateString() : '-'
                    },
                    {
                        key: "single_hour",
                        label: "Hora",
                        tdClass: "align-middle"
                    },
                    // {
                    //     key: "actions",
                    //     label: "Acciones"
                    // }
                ],

                loadingModal: false,
                showFilters: true,
                showOptions: true,
                showCustomers: false,
                showComponents: false,
                files: [],
                fileSelected: [],
                nrofile: null,
                modalTitle: "",
                contentModal: "",
                bookingId: "",
                urlbookings: "",
                date_type_id: "",
                status_id: "",
                final_check_id: "",
                checkboxs: [],
                componentsChoosed: [],
                componentsChoosedCount: 0,
                form: {
                    service_id: null,
                    id: null
                },
                viewBooking: {
                    customers: {
                        customer: ""
                    },
                    components: {
                        component: []
                    }
                },
                bookings: [],
                booking_pages: 0,
                // pageChosen: 1,
                // limits: [5, 10, 15, 20, 25, 30],
                // limit: 15,
                filter_code: "",
                filter_date_start: "",
                filter_paxs: "",
                filter_passenger: "",
                filter_passenger_email: "",
                filter_booking_state: "",
                filter_booking_type: "",
                filter_aurora_code: "",
                // _filter: "",
                // _order: "",
                view_pages: 15,
                show_pages: {},
                pages: 0,
                page: 0,
                booking_status: [{
                    code: "N",
                    name: "Nuevo"
                },
                    {
                        code: "C",
                        name: "Cancelado"
                    }
                ],
                flag_booking_status: {},
                flag_booking_codes: {},
                flag_status: ""
            },
            mounted() {
                this.loadServices();
            },
            computed: {
                groupedErrors() {
                    const grouped = {};
                    (this.apiErrors || []).forEach(e => {
                        const key = (e.entity || 'general').toString();
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(e);
                    });
                    return grouped;
                }
            },
            methods: {
                // Carga los servicios desde la API
                async loadServices() {
                    try {
                        this.loading = true;
                        const url = `${baseExternalURL}api/channel/pentagrama/list`;
                        const res = await axios.get(url,
                            {
                                params: {
                                    page: this.pageChosen,
                                    limit: this.limit
                                }
                            }
                        );

                        this.services = res.data.data || [];
                        this.pagination.total = Number(res.data.meta.total);
                        this.pagination.last_page = Number(res.data.meta.last_page);

                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                // Cambio de página
                onPageChange(page) {
                    this.pageChosen = page;
                    this.loadServices();
                },

                // Cambio de límite
                onLimitChange() {
                    this.limit = Number(this.limit);
                    this.pageChosen = 1;
                    this.loadServices();
                },

                // Abre el modal y carga el detalle
                openDetail(service) {
                    this.selectedService = service;
                    this.detailItems = service.details;
                    // $(this.$refs.detailModal).modal('show');
                    $('#detail-modal').modal('show');
                },

                getStatusLabel(status) {
                    switch (status) {
                        case "pending":
                            return "Pendiente";
                        case "processed":
                            return "Procesado";
                        default:
                            return status;
                    }
                },

                getStatusVariant(status) {
                    switch (status) {
                        case "pending":
                            return "warning";   // amarillo
                        case "canceled":
                            return "danger";   // amarillo
                        case "processed":
                            return "success";   // verde
                        default:
                            return "secondary";
                    }
                },

                // Verifica si el proceso está deshabilitado
                isProcessDisabled(item) {
                    return item.status === "processed" || item.quote_number != null;
                },

                // Abre el modal de errores
                openErrorsModal(errors, message) {
                    this.apiErrors = Array.isArray(errors) ? errors : [];
                    this.apiErrorMessage = message || 'Se encontraron errores.';
                    $('#errors-modal').modal('show');
                },

                // Generar numero de cotización
                async processService(service) {
                    try {
                        this.loading = true;

                        const client_id = localStorage.getItem("client_id");
                        const extension_pentagrama_service_id = service.id;

                        const url = window.a3BaseQuoteServerURL + "api/quote/pentagrama/generate";
                        const data = {
                            client_id,
                            extension_pentagrama_service_id,
                        };
                        const response = await axios.post(url, data);
                        const quote_number = response.data.quote_id;
                        await this.updateService(service, quote_number);
                    } catch (err) {
                        console.error(err);

                        const r = err && err.response ? err.response : null;
                        const data = r && r.data ? r.data : null;

                        // Captura errores de validación/back (422)
                        if (r && r.status === 422 && data && data.errors) {
                            this.openErrorsModal(data.errors, data.message);
                            return;
                        }

                        // fallback genérico
                        this.openErrorsModal(
                            [{ code: 'UNKNOWN_ERROR', entity: 'general', index: '-', message: err || 'Error desconocido' }],
                            'Ocurrio un error al procesar el servicio.'
                        );
                    } finally {
                        this.loading = false;
                    }
                },

                // Actualizar servicio
                async updateService(service, quote_number) {
                    try {
                        this.loading = true;

                        const url = `${baseExternalURL}api/channel/pentagrama/update/${service.id}/service`;
                        const data = {
                            quote_number: quote_number
                        };
                        const response = await axios.post(url, data);
                        service.quote_number = response.data.quote_number;
                        service.status = response.data.status;
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        });
    </script>
@endsection
@section('css')
    <style>
        table tbody td,
        table tbody th {
            vertical-align: middle;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            border-color: #3ea662;
            background-color: #3a9d5d;
        }

        .v-select input {
            height: 25px;
        }
    </style>
@endsection
