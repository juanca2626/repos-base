<template>
    <div class="modal modal--cotizacion" id="customers-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>ASIGNAR CLIENTES AL ESPECIALISTA: {{ executive.NOMESP }}</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">

                        <div class="form">
                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <v-select label="text" :reduce="clients => clients.value" :options="clients" @search="searchClients" v-model="client" class="form-control"></v-select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-group">
                                    <button class="btn btn-primary" v-bind:disabled="loading_button" v-on:click="save()">
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div clas="mt-2">
                            <div class="alert alert-warning" v-if="loading">Cargando..</div>
                            <div class="alert alert-warning" v-if="!loading && quantity == 0">No se encontró información.</div>
                            <table class="table table-striped" id="_executives" v-if="quantity > 0 && !loading">
                                <thead>
                                <tr>
                                    <th>CODIGO</th>
                                    <th>RAZON SOCIAL</th>
                                    <th class="center">ACCIONES</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(customer, c) in customers">
                                    <td>{{ customer.NOMING }}</td>
                                    <td>{{ customer.RAZON }}</td>
                                    <td class="center">
                                        <a class="edit btn-effect btn-check btn btn-xs" title="Eliminar" v-on:click="_remove( customer.NOMING )">
                                            <i class="fa fa-times fa-2x"></i>
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
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
                loading_button: false,
                translations: [],
                identi: '',
                clients: [],
                client: '',
                customers: []
            }
        },
        created: function () {

        },
        methods: {
            load: function() {
                this.executive = this.data.executive
                this.identi = this.data.identi
                this.translations = this.data.translations
                this.searchClients()
                this.searchCustomers()
            },
            searchClients: function (search, loading) {
                this.loading_button = true

                axios.post(
                    baseURL + 'board/all_clients', {
                        lang: this.lang,
                        client: search
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.clients = result.data.clients
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchCustomers: function () {
                this.loading = true

                axios.post(
                    baseURL + 'users/customersTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        identi: this.identi
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.customers = result.data.customers
                        this.quantity = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {

                if(this.client == '')
                {
                    this.$toast.error('Seleccione un cliente para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'users/addCustomerTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        identi: this.identi,
                        customer: this.client
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.loading_button = false
                        this.searchCustomers()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            _remove: function (_customer) {
                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'users/removeCustomerTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        identi: this.identi,
                        customer: _customer
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.searchCustomers()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    }
</script>
