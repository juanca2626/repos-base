<template>
    <div class="modal modal--cotizacion" id="update-response-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title text-center"><b>PEDIDO: #{{ order.nroped }}</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div class="alert alert-warning" v-if="loading">Cargando..</div>

                        <div class="form">

                            <div class="mb-4">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <date-picker class="date mr-2"
                                         v-model="date"
                                         :config="options">
                                    </date-picker>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label>Hora</label>
                                    <input type="time" class="form-control" id="hora" step="1" v-model="time" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <button class="btn btn-primary w-100" v-bind:disabled="loading" v-on:click="save()">
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
                order: [],
                quantity: 0,
                loading: false,
                translations: [],
                date: '',
                time: '',
                options: {
                    format: 'DD/MM/YYYY',
                    useCurrent: true,
                }
            }
        },
        created: function () {
            this.order = this.data.order
            this.translations = this.data.translations
        },
        methods: {
            load: function() {

            },
            save: function () {
                this.loading = true

                axios.post(
                    baseURL + 'orders/update_response', {
                        lang: this.lang,
                        nroped: this.order.nroped,
                        nroord: this.order.nroord,
                        codven: this.order.codusu,
                        fecha: this.date,
                        hora: this.time
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data.response == 'success')
                        {
                            this.$toast.success('El pedido se actualizó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })

                            this.$parent._closeModal()
                            this.$parent.search()
                        }
                        else
                        {
                            this.$toast.error('Ocurrió un error al actualizar el pedido.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    }
</script>
