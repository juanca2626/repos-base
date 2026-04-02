<template>
    <div class="modal modal--cotizacion" id="update-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
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
                                    <label>Nompax</label>
                                    <input type="text" class="form-control" id="nompax" v-model.trim="nompax" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label>Producto</label>
                                    <b-form-select v-model.trim="product"
                                        :reduce="products => products.value" :options="products"
                                        class="form-control ml-1">
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <v-select :options="clients"
                                        v-model="client"
                                        :reduce="clients => clients.client_code"
                                        @filterable="false"
                                        @search="searchClients"
                                        autocomplete="true"
                                        class="form-control"
                                    >
                                    </v-select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-group">
                                    <label>Referencia</label>
                                    <input type="text" class="form-control" id="refext" v-model.trim="refext" />
                                </div>
                            </div>

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
                products: [],
                clients: [],
                product: '',
                client: '',
                refext: '',
                nompax: '',
                observ: ''
            }
        },
        created: function () {
            console.log(this.data)

            this.order = this.data.order
            this.translations = this.data.translations

            const allowedValues = [1, 2, 5];
            this.products = this.data.products.filter(
                (product) => allowedValues.includes(parseInt(product.value))
            );

            this.product = this.order.codsec.trim()
            this.client = this.order.codigo.trim()
            this.refext = this.order.refext.trim()
            this.nompax = this.order.nompax.trim()
            this.observ = this.order.observ.trim()

            let vm = this
            setTimeout(function () {
                vm.load()
            }, 10)
        },
        methods: {
            load: function() {
                this.searchClients(this.client)
            },
            searchClients: function (search, loading) {
                if(search != '' && search != null  || (search == '' && this.clients.length == 0))
                {
                    axios.get('api/clients/selectBox/by/executive?queryCustom=' + search)
                        .then((result) => {

                            if (result.data.success === true)
                            {
                                this.clients = result.data.data

                                if (localStorage.getItem('client_id') != '' && localStorage.getItem('client_id') != null)
                                {
                                    this.clients.forEach((element) => {

                                        if (localStorage.getItem('client_id') == element.code) {
                                            this.client = element.client_code
                                        }
                                    })
                                }

                            }
                        }).catch((e) => {
                        console.log(e)
                    })
                }
            },
            save: function () {
                this.loading = true

                axios.post(
                    baseURL + 'orders/update', {
                        lang: this.lang,
                        nroPed: this.order.nroped,
                        codcli: this.client,
                        codsec: this.product,
                        refext: this.refext,
                        namePax: this.nompax,
                        paxPlan: this.observ
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

                            this.order.codsec = this.product
                            this.order.codigo = this.client
                            this.order.refext = this.refext
                            this.order.nompax = this.nompax
                            this.order.observ = this.observ

                            this.$parent._updateOrder(this.order)
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
