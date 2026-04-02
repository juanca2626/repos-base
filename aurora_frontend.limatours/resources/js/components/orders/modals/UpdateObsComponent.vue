<template>
    <div class="modal modal--cotizacion" id="update-obs-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
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
                                    <label>Observaciones</label>
                                    <textarea class="form-control" id="observ" v-model.trim="observ" rows="3"></textarea>
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
                observ: ''
            }
        },
        created: function () {
            this.order = this.data.order
            this.translations = this.data.translations

            localStorage.setItem('order', JSON.stringify(this.order))
            let order = JSON.parse(localStorage.getItem('order'))

            this.observ = order.observ
        },
        mounted: function () {

        },
        methods: {
            load: function() {

            },
            save: function () {
                this.loading = true

                axios.post(
                    baseURL + 'orders/update_obs', {
                        lang: this.lang,
                        nroord: this.order.nroord,
                        nroped: this.order.nroped,
                        observ: this.observ
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

                            this.order.observ = this.observ
                            this.$parent._updateOrder()
                            this.$parent._closeModal()
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
