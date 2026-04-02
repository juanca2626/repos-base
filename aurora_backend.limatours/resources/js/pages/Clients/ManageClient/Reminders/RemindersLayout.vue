<template>
    <div class="vld-parent">

        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="m-0 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bell mr-2" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                </svg>Recordatorios
            </h4>
        </div>

        <form id="form">
            <div class="alert alert-info d-flex align-items-center shadow-sm" v-if="loading">
                <div class="spinner-border spinner-border-sm mr-3" role="status"></div>
                <strong>Cargando...</strong>
            </div>

            <div class="card shadow-sm mb-4" v-for="(reminder, r) in reminders" :key="`rem-${r}`">
                <div class="card-header bg-white pb-0 border-bottom-0 pt-4">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ reminder.title }}</h5>
                    <hr>
                </div>
                <div class="card-body pt-2">
                    <div class="row">
                        <div class="col-md-12 col-lg-6 mb-4" v-for="(category, c) in reminder.categories" :key="`cat-${c}`">
                            <h6 class="font-weight-bold mb-3 mt-2">
                                {{ category.title }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0 text-center">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="align-middle text-center" style="width: 30%">
                                                TIEMPO
                                            </th>
                                            <th class="align-middle text-center" v-for="(_type, typeIdx) in types_reminder" :key="`th-type-${typeIdx}`">
                                                {{ _type.title.toUpperCase() }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(time, t) in category.times" :key="`time-${t}`">
                                            <td class="align-middle font-weight-bold text-dark text-center">
                                                {{ time.time }} {{ time.format.replace(/days/i, 'Días').replace(/hours/i, 'Horas') }}
                                            </td>
                                            <td class="align-middle text-center" v-for="(_type, typeIdx) in types_reminder" :key="`td-type-${typeIdx}`">
                                                <div class="custom-control custom-checkbox d-inline-block">
                                                    <input type="checkbox"
                                                           class="custom-control-input"
                                                           :id="`chk-${reminder.id}-${category.id}-${time.id}-${_type.id}`"
                                                           :name="`options[${reminder.id}][${category.id}][${time.id}][${_type.id}]`"
                                                           :checked="(_type.title == 'email' &&
                                                           (Object.entries(recordatorios_customer.REPAFP).length == 0)) ||
                                                           (recordatorios_customer.REPAFP[reminder.id] != undefined &&
                                                           recordatorios_customer.REPAFP[reminder.id][category.id] != undefined &&
                                                           recordatorios_customer.REPAFP[reminder.id][category.id][time.id] != undefined &&
                                                           recordatorios_customer.REPAFP[reminder.id][category.id][time.id][_type.id] == 1)"
                                                           value="1"
                                                           :disabled="loading" />
                                                    <label class="custom-control-label"
                                                           :for="`chk-${reminder.id}-${category.id}-${time.id}-${_type.id}`"
                                                           style="cursor: pointer; padding-top: 2px;">
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label for="correo_contacto_recordatorio" class="font-weight-bold text-dark mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope mr-2" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                            </svg>Correos Electrónicos Destinatarios
                            <small class="text-muted d-block mt-1">Escriba el correo y presione <b>Enter</b> para añadirlo como etiqueta.</small>
                        </label>
                        <div class="custom-v-select">
                            <v-select taggable multiple no-drop v-model="emailsList" :options="[]" placeholder="Ingrese los correos y presione Enter..." :disabled="loading">
                                <template #no-options="{ search, searching, loading }">
                                    Presione Enter para añadir {{ search }}
                                </template>
                            </v-select>
                        </div>
                        <input type="hidden" id="correo_contacto_recordatorio" name="correo_contacto_recordatorio" :value="emailsList.join(',')" />
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="button" v-on:click="save()" class="btn btn-primary btn-lg shadow-sm px-5" :disabled="loading" style="min-width: 200px">
                    <span v-if="!loading">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-floppy mr-2" viewBox="0 0 16 16">
                            <path d="M11 2H9v3h2z"/>
                            <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0ZM1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5Zm3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                        </svg> Guardar Cambios
                    </span>
                    <span v-else><span class="spinner-border spinner-border-sm mr-2" role="status"></span> Guardando...</span>
                </button>
            </div>
        </form>

    </div>

</template>

<script>
    import {API} from './../../../../api'
    import 'vue-select/dist/vue-select.css'
    import vSelect from 'vue-select'

    export default {
        components: {
            'v-select': vSelect
        },
        data: () => {
            return {
                client_id: '',
                loading: false,
                reminders: [],
                types_reminder: [],
                emailsList: [],
                recordatorios_customer: {
                    'REPAFP': {},
                    'CORNOT': ''
                },
            }
        },
        mounted() {
            this.search_ifx()
        },
        created() {
            this.client_id = this.$route.params.client_id
        },
        computed: {},
        methods: {
            save: function () {
                this.loading = true
                let params = $('#form').serialize()

                console.log(params)

                API.put(
                    'reminders/ifx', {
                        type: this.option,
                        params: params,
                        client_id: this.client_id
                    }
                )
                    .then((result) => {
                        this.loading = false

                        console.log(result)

                        if (result.data === 'success') {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: "Reminders",
                                text: "La información se actualizó correctamente"
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'warning',
                                title: "Reminders",
                                text: result.data.message
                            })
                        }
                    })
                    .catch((e) => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: "Reminders",
                            text: "Error de procesamiento"
                        })

                        console.log(e)
                    })
            },
            search_ifx() {
                this.loading = true

                API.get('reminders/ifx?lang=es&client_id=' + this.client_id)
                    .then((result) => {
                        this.loading = false
                        this.search()
                        let __reminders = result.data.response.recordatorios.REPAFP

                        if(__reminders == '' || __reminders == null)
                        {
                            __reminders = '{}'
                        }

                        this.recordatorios_customer = {
                            'REPAFP': JSON.parse(__reminders),
                            'CORNOT': result.data.response.recordatorios.CORNOT
                        }

                        let correos = result.data.response.recordatorios.CORNOT;
                        if(correos) {
                            this.emailsList = correos.split(',').map(e => e.trim()).filter(e => e);
                        } else {
                            this.emailsList = [];
                        }

                        console.log(this.recordatorios_customer)
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
            search() {
                this.loading = true
                API.post('reminders')
                    .then((result) => {
                        this.loading = false
                        this.reminders = result.data.reminders
                        this.types_reminder = result.data.types

                    }).catch((err) => {
                    this.loading = false
                    console.log(err)
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Reminders",
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            }
        }
    }
</script>

<style lang="stylus">
</style>


