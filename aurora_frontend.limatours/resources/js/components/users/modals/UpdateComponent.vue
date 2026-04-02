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
                        <h3 class="modal-title"><b>ACTUALIZAR USUARIO: {{ executive.NOMESP }}</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div class="alert alert-warning" v-if="loading">Cargando..</div>

                        <div class="form">

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Producto</label>
                                    <b-form-select v-model.trim="product" :reduce="products => products.value" :options="products" class="form-control ml-1">
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Región</label>
                                    <b-form-select v-model.trim="region" :reduce="regions => regions.value" :options="regions" class="form-control ml-1">
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Regional</label>
                                    <input type="text" class="form-control" id="regional" v-model.trim="regional" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Jefe</label>
                                    <input type="text" class="form-control" id="jefe" v-model.trim="jefe" />
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
                products: [],
                regions: [],
                product: '',
                region: '',
                regional: '',
                jefe: ''
            }
        },
        created: function () {

        },
        methods: {
            load: function() {
                this.searchProducts()
                this.searchRegions()
                this.executive = this.data.executive
                this.translations = this.data.translations

                this.product = (this.executive.CODIGO != null) ? this.executive.CODIGO.trim() : ''
                this.region = (this.executive.REGION != null) ? this.executive.REGION.trim() : ''
                this.regional = (this.executive.JEFE_REGIONAL != null) ? this.executive.JEFE_REGIONAL : ''
                this.jefe = (this.executive.JEFE != null) ? this.executive.JEFE : ''
            },
            searchProducts: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/products', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.products = result.data.products
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchRegions: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/all_regions', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.regions = result.data.regions
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {

                if(this.product == '')
                {
                    this.$toast.error('Seleccione un producto para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.region == '')
                {
                    this.$toast.error('Seleccione una región para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.regional == '')
                {
                    this.$toast.error('Seleccione un jefe regional para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.jefe == '')
                {
                    this.$toast.error('Seleccione un jefe para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true

                axios.post(
                    baseURL + 'users/updateTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim().toUpperCase(),
                        sector: this.product.trim().toUpperCase(),
                        region: this.region.trim().toUpperCase(),
                        regional: this.regional.trim().toUpperCase(),
                        jefe: this.jefe.trim().toUpperCase()
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
            }
        }
    }
</script>
