<template>
    <div class="modal modal--cotizacion" id="countries-modal" tabindex="-1" role="dialog" style="overflow: scroll;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal--cotizacion__content">
                <div class="modal-header">
                    <button class="close" type="button" data-dismiss="modal"
                            aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal--cotizacion__header">
                        <h3 class="modal-title"><b>ASIGNAR PAISES AL ESPECIALISTA: {{ executive.NOMESP }}</b></h3>
                    </div>
                    <div class=" modal--cotizacion__body">

                        <div class="form">
                            <div class="mb-3">
                                <div class="form-group">
                                    <label>País</label>
                                    <select class="form-control ml-3" v-model="country">
                                        <option value="">PAIS</option>
                                        <option v-bind:value="_country.CODIGO" v-for="_country in countries">{{ _country.CODIGO }}{{ _country.DESCRI }}</option>
                                    </select>
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
                                    <th>PAIS</th>
                                    <th class="center">ACCIONES</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(_country, c) in all_countries">
                                    <td>{{ (_country.NOMING != null && _country.NOMING != '') ? _country.NOMING.trim() : '' }}</td>
                                    <td>{{ (_country.DESCRI != null && _country.DESCRI != '') ? _country.DESCRI.trim() : '' }}</td>
                                    <td class="center">
                                        <a class="edit btn-effect btn-check btn btn-xs" title="Eliminar" v-on:click="_remove( _country.NOMING )">
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
                lang: '',
                executive: [],
                quantity: 0,
                loading: false,
                loading_button: false,
                translations: [],
                countries: [],
                country: '',
                all_countries: []
            }
        },
        created: function () {

        },
        methods: {
            load: function() {
                this.lang = localStorage.getItem('lang')
                this.executive = this.data.executive
                this.translations = this.data.translations
                this.searchCountries()
                this.searchAllCountries()
            },
            searchCountries: function () {
                this.loading_button = true

                axios.post(
                    baseURL + 'board/all_countries', {
                        lang: this.lang,
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.countries = result.data.countries
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchAllCountries: function () {
                this.loading = true

                axios.post(
                    baseURL + 'users/countriesTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim()
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_countries = result.data.countries
                        this.quantity = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            save: function () {

                if(this.country == '')
                {
                    this.$toast.error('Seleccione un país para asignar al especialista', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'users/addCountryTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        country: this.country
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.loading_button = false
                        this.searchAllCountries()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            _remove: function (_country) {
                this.loading = true
                this.loading_button = true

                axios.post(
                    baseURL + 'users/removeCountryTOM', {
                        lang: this.lang,
                        executive: this.executive.NOMESP.trim(),
                        country: _country.trim()
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.searchAllCountries()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    }
</script>
