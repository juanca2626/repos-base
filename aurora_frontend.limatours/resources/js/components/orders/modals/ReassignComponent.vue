<template>
    <div class="modal modal--cotizacion" id="reassign-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
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
                                <div class="text-muted mt-3">
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="check_reassign" v-model="check_reassign" id="producto_reassign" value="P" class="form-check-input">
                                        <label for="producto_reassign" class="form-check-label">Producto</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="check_reassign" v-model="check_reassign" id="region_reassign" value="R" class="form-check-input">
                                        <label for="region_reassign" class="form-check-label">Region</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="check_reassign" v-model="check_reassign" id="especialista_reassign" value="E" class="form-check-input">
                                        <label for="especialista_reassign" class="form-check-label">Especialista</label>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="mb-4" v-if="check_reassign == 'P'">
                                <div class="form-group">
                                    <label>
                                        <strong>Producto</strong>
                                    </label>
                                    <v-select label="text"
                                        :reduce="sectors => sectors.value"
                                        :options="sectors"
                                        v-model="sector"
                                        class="form-control">
                                    </v-select>
                                </div>
                            </div>

                            <div class="mb-4" v-if="check_reassign == 'R'">
                                <div class="form-group">
                                    <label>
                                        <strong>Region</strong>
                                    </label>
                                    <v-select label="text"
                                              :reduce="regions => regions.value"
                                              :options="regions"
                                              v-model="region"
                                              class="form-control">
                                    </v-select>
                                </div>
                            </div>

                            <div class="mb-4" v-if="check_reassign == 'E'">
                                <div class="form-group">
                                    <label>
                                        <strong>Especialista</strong>
                                    </label>
                                    <v-select label="text"
                                              :reduce="executives => executives.value"
                                              :options="executives"
                                              v-model="executive"
                                              class="form-control">
                                    </v-select>
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
                executives: [],
                quantityExecutives: 0,
                sectors: [],
                quantitySectors: 0,
                regions: [],
                quantityRegions: 0,
                order: [],
                quantity: 0,
                loading: false,
                check_reassign: 'P',
                sector: '',
                region: '',
                executive: '',
                translations: []
            }
        },
        created: function () {
            this.order = this.data.order
            this.sector = this.order.codsec.trim()
            this.region = this.order.region.trim()
            this.translations = this.data.translations
        },
        methods: {
            load: function() {
                this.allSectors()
                this.allRegions()
                this.allExecutives()
            },
            allSectors: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/all_sectors', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false

                        this.sectors = result.data.sectors
                        this.quantitySectors = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            allRegions: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/all_regions', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false

                        this.regions = result.data.regions
                        this.quantityRegions = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            allExecutives: function () {
                this.loading = true

                axios.post(
                    baseURL + 'board/all_executives', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false

                        this.executives = result.data.executives
                        this.quantityExecutives = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {
                this.loading = true

                axios.post(
                    baseURL + 'orders/reassign', {
                        lang: this.lang,
                        id: this.order.nroped,
                        nroord: this.order.nroord,
                        codigo: this.order.codigo,
                        sector: this.sector,
                        region: this.region,
                        prev_executive: this.order.codusu,
                        executive_selected: this.executive
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data.response.response == 'success')
                        {
                            this.$toast.success('El pedido se reasignó correctamente al usuario: ' + result.data.response.razon.trim() + ' (' + result.data.response.codusu.trim() + ').' , {
                                // override the global option
                                position: 'top-right'
                            })

                            this.$parent.search()
                            this.$parent._closeModal()
                        }
                        else
                        {
                            this.$toast.error('Ocurrió un error al reasignar el pedido.', {
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
