<template>
    <div>
        <div class="container">
            <div class="form p-5">
                <div class="d-flex justify-content-between align-items-center py-4">
                    <div class="col">
                        <label>
                            <strong>Fecha</strong>
                        </label>
                        <date-picker class="date mr-2"
                            v-model="date"
                            :config="options"></date-picker>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="downloadExcel()">
                            Descargar Excel
                        </button>
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
                loading: false,
                date: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                },
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.flag = localStorage.getItem('bossFlag')
        },
        computed: {

        },
        methods: {
            downloadExcel: function () {
                this.loading = true
                
                axios.post(baseExternalURL + 'api/user_access_report', {
                    date: this.date
                }, {
                    responseType: 'blob',
                }).then((result) => {
                    this.loading = false

                    var fileURL = window.URL.createObjectURL(new Blob([result.data]))
                    var fileLink = document.createElement('a')
                    fileLink.href = fileURL
                    fileLink.setAttribute('download', 'Reporte-Usuarios.xls')
                    document.body.appendChild(fileLink)
                    fileLink.click()
                })
                    .catch((error) => {
                        this.loading = false
                    })
            },
        }
    };
</script>
