<template>
    <div class="modal modal--cotizacion" id="add-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>AGREGAR USUARIO</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">
                        <div class="alert alert-warning" v-if="loading">Cargando..</div>

                        <div class="form">

                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input type="text" class="form-control" id="usuario" v-model.trim="executive" />
                                </div>
                            </div>

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
                quantity: 0,
                loading: false,
                translations: [],
                products: [],
                regions: [],
                product: '',
                region: '',
                regional: '',
                jefe: '',
                executive: ''
            }
        },
        created: function () {

        },
        methods: {
            load: function() {
                this.searchProducts()
                this.searchRegions()
                this.translations = this.data.translations
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

                if(this.executive == '')
                {
                    this.$toast.error('Ingrese un usuario', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.product == '')
                {
                    this.$toast.error('Seleccione un producto', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.region == '')
                {
                    this.$toast.error('Seleccione una región', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.regional == '')
                {
                    this.$toast.error('Ingrese un jefe regional', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.jefe == '')
                {
                    this.$toast.error('Ingrese un jefe', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true

                axios.post(
                    baseURL + 'users/addTOM', {
                        lang: this.lang,
                        executive: this.executive.trim().toUpperCase(),
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
