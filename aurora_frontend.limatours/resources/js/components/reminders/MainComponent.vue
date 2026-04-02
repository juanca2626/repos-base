<template>
    <div>
        <div class="container">
            <!-- div class="form" v-if="create">
                <div class="form-row justify-content-between">
                    <div class="form-group mx-4 fecha">
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
                    <div class="form-group mx-4">
                        <label>
                            <strong>Producto</strong>
                        </label>
                        <b-form-select v-model="product" :reduce="products => products.value" :options="products" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group mx-4 fecha" v-bind:disabled="check != 'E'">
                        <label>
                            <strong>Especialista</strong>
                        </label>
                        <v-select label="text" :disabled="check != 'E'" :reduce="executives => executives.value" :options="executives" v-model="executive" class="form-control"></v-select>
                    </div>
                    <div class="form-group mx-4">
                        <label>
                            <strong>Buscar por pedido</strong>
                        </label>
                        <input type="number" class="form-control" v-model="query" />
                    </div>
                </div>
                <div class="form-row justify-content-between">
                    <div class="form-group mx-4" v-if="quantityTeams > 0">
                        <label>
                            <strong>Equipo</strong>
                        </label>
                        <b-form-select v-model="team" :options="teams" v-on:change="searchExecutives()" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group mx-4">
                        <label>
                            <strong>Estado</strong>
                        </label>
                        <b-form-select v-model="status" :options="all_status" class="form-control ml-1">
                        </b-form-select>
                    </div>
                    <div class="form-group mx-4">
                        <label>
                            <strong>Filtrar por</strong>
                        </label>
                        <div class="text-muted mt-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="customer" value="C" />
                                <label class="form-check-label" for="customer">Cliente</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="check" v-model="check" id="executive" value="E">
                                <label class="form-check-label" for="executive">Especialista</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mx-4 reporte-boton" style="margin-top:9px;">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="search()">
                            Buscar
                        </button>
                    </div>
                    <div class="form-group mx-4 reporte-boton" style="margin-top:9px;">
                        <button class="btn btn-primary" v-bind:disabled="loading || quantity == 0" v-on:click="downloadExcel()">
                            Exportar Excel
                        </button>
                    </div>
                </div>
            </div-->
        </div>

        <div class="mt-5" v-if="!create">
            <div class="container">
                <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                    <p class="mb-0">{{ translations.label.loading }}</p>
                </div>
                <div class="alert alert-warning" v-if="quantity == 0 && !loading">
                    <p class="mb-0">{{ translations.label.no_data }}</p>
                </div>
            </div>

            <div class="container-fluid">
                <table class="table table-hover" id="_reminders" v-show="quantity > 0 && !loading">
                    <thead>
                    <tr>
                        <th>Recordatorio</th>
                        <th>Categoría</th>
                        <th>Tiempos / Acciones</th>
                        <th><i class="fa fa-cog"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(reminder, r) in reminders">
                    </tr>
                    </tbody>
                </table>
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
                reminders: [],
                loading: false,
                create: false,
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
        },
        computed: {

        },
        methods: {
            search: function (page) {
                this.loading = true

                axios.post(
                    baseExternalURL + 'api/reminders'
                )
                    .then((result) => {
                        this.reminders = result.data.reminders
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            addReminder: function () {
                this.create = true
            },
            saveReminder: function () {
                this.loading = true

                axios.post(
                    baseExternalURL + 'api/save_reminder', {

                    }
                )
                    .then((result) => {
                        this.reminders = result.data.reminders
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            deleteReminder: function () {
                this.loading = true

                axios.post(
                    baseExternalURL + 'api/delete_reminder', {

                    }
                )
                    .then((result) => {
                        this.reminders = result.data.reminders
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            }
        }
    };
</script>
