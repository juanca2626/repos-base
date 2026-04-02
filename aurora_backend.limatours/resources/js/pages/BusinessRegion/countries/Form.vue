<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="country_id">País</label>
                        <div class="col-sm-5">
                            <v-select
                                v-model="form.country_id"
                                :options="filteredCountries"
                                label="translated_name"
                                :reduce="country => country.id"
                                :class="{
                                'is-valid': !errors.has('country_id'),
                                'is-invalid': errors.has('country_id')
                                }"
                                name="country_id"
                                data-vv-as="país"
                                v-validate="'required'"
                                @search="onSearch"
                            >
                                <template #option="option">
                                {{ option.translated_name }} ({{ option.iso }})
                                </template>
                                <template #selected-option="option">
                                {{ option.translated_name }} ({{ option.iso }})
                                </template>
                            </v-select>

                            <span v-show="errors.has('country_id')" class="invalid-feedback">
                                {{  errors.first('country_id') }}
                            </span>

                            <span v-if="apiError" class="invalid-feedback d-block">
                                {{  apiError }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'BusinessRegionCountriesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>
<script>
import { API } from './../../../api'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

export default {
    components: {
        vSelect
    },
    data() {
        return {
            searchQuery: '',
            loading: false,
            countries: [],
            searchQuery: '',
            region_id: '',
            form: {
                country_id: ''
            },
            apiError: null
        }
    },
    computed: {
        filteredCountries() {
            if (!this.searchQuery) {
                return this.countries.map(country => ({
                    ...country,
                    translated_name: country.translations[0]?.value || country.iso
                }));
            }

            const query = this.searchQuery.toLowerCase();
            return this.countries
            .filter(country => {
                const name = (country.translations[0]?.value || '').toLowerCase();
                const iso = country.iso.toLowerCase();
                return name.includes(query) || iso.includes(query);
            })
            .map(country => ({
                ...country,
                translated_name: country.translations[0]?.value || country.iso
            }));
        }
    },
    async mounted() {
        this.$i18n.locale = localStorage.getItem('lang');
        await this.loadCountries();
    },
    methods: {
        onSearch(query) {
            this.searchQuery = query;
        },
        async loadCountries() {
            const response = await API.get('countries', {
                params: { lang: localStorage.getItem('lang') }
            });
            this.countries = response.data.data || [];
        },
        validateBeforeSubmit() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submitForm();
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Error",
                        text: "Todos los campos son requeridos"
                    });
                }
            });
        },
        async submitForm() {
            this.loading = true;
            this.apiError = null;
            try{
                this.region_id = this.$route.params.region_id;

                const response = await API.post(`business_region/${this.region_id}/countries`, this.form)

                if(response.data.success) {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: 'Éxito',
                        text: 'País asociado correctamente'
                    });

                    this.form.country_id = '';
                    this.$router.push(`list`);
                }else{
                    this.apiError = response.data.messsage;
                }
            }
            catch(error) {
                if(error.response && error.response.status === 409){
                    this.apiError = error.response.data.message;
                }else{
                    this.apiError = 'Ocurrió un error al procesar la solicitud';
                    console.log('Error:', error);
                }
            }
            finally {
                this.loading = false;
            }
        }
    }
}
</script>
<style scoped>
.v-select {
    width: 100%;
}
.vs__dropdown-toggle {
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
}
.vs__search::placeholder {
    color: #6c757d;
}
.is-invalid .vs__dropdown-toggle {
    border-color: #dc3545;
}
.is-valid .vs__dropdown-toggle {
    border-color: #28a745;
}
.invalid-feedback {
  color: #dc3545;
  font-size: 80%;
  margin-top: 0.25rem;
}
.d-block {
  display: block;
}
</style>
