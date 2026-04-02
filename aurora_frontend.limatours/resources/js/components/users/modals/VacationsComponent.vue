<template>
    <div class="modal modal--cotizacion" id="vacations-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>VACACIONES: {{ executive.NOMESP }}</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div class="alert alert-warning" v-if="loading">Cargando..</div>

                        <div class="form">

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Fecha de Inicio</label>
                                    <date-picker class="date mr-2"
                                                 v-model="fecini"
                                                 :config="options">
                                    </date-picker>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Fecha de Fin</label>
                                    <date-picker class="date mr-2"
                                                 v-model="fecfin"
                                                 :config="options">
                                    </date-picker>
                                </div>
                            </div>


                            <div class="mb-3">
                                <div class="form-group">
                                    <button class="btn btn-primary" v-bind:disabled="loading" v-on:click="save()">
                                        Guardar
                                    </button>
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
    export default {
        props: ['data'],
        data: () => {
            return {
                executive: [],
                quantity: 0,
                loading: false,
                translations: [],
                vacations: [],
                fecini: '',
                fecfin: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                }
            }
        },
        created: function () {

        },
        methods: {
            load: function() {
                this.executive = this.data.executive
                this.translations = this.data.translations
                this.searchVacations()
            },
            searchVacations: function () {
                this.loading = true

                this.fecini = ''
                this.fecfin = ''

                axios.post(
                    baseURL + 'users/vacationsTOM', {
                        lang: this.lang,
                        id: this.executive.NOMESP.trim()
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data != false)
                        {
                            this.fecini = moment(String(result.data.FECINI)).format('DD/MM/YYYY')
                            this.fecfin = moment(String(result.data.FECFIN)).format('DD/MM/YYYY')
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {

                if(this.fecini == '')
                {
                    this.$toast.error('Seleccione una fecha de inicio para asignar vacaciones al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.fecini == '')
                {
                    this.$toast.error('Seleccione una fecha de fin para asignar vacaciones al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true

                axios.post(
                    baseURL + 'users/updateStateTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        state: 0,
                        fecini: this.fecini,
                        fecfin: this.fecfin
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.$parent.searchUsers()
                        this.$parent._closeModal()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
                //
            }
        }
    }
</script>
