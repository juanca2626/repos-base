<template>
    <div class="row">
        <div class="col-sm-12">
            <loading :active="loadingPackage || isProcessingPlanRates" :can-cancel="false" color="#BD0D12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-5x m-0 mb-2" style="color: var(--primary)"></i>
                </div>
                <div slot="after" class="text-center" v-show="!loadingPackage">
                    <p class="mb-2">Procesando tarifas. Recargue después de algunos segundos</p>
                    <button class="btn btn-primary" @click="reloadPage">
                        {{ $t('global.buttons.reload') }}
                    </button>
                </div>
            </loading>
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <div class="col-2">
                            <label for="code">{{ $t('packages.code') }}</label>
                        </div>
                        <div class="col-4">
                            <div class="col-sm-12 p-0">
                                <input class="form-control" id="code" max="20" min="1" type="text"
                                  v-model="form.code">
                                <span class="invalid-feedback-select" v-show="errors.has('code')">
                                  <span>{{ errors.first('code') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">

                        <div class="col-2">
                            <label for="country_origin">{{ $t('services.country') }}</label>
                        </div>
                        <div class="col-4">
                            <div class="col-sm-12 p-0">
                                <v-select :options="countries"
                                  :value="form.country_id"
                                  autocomplete="true"
                                  @input="countryChange"
                                  data-vv-as="country"
                                  data-vv-name="country_origin"
                                  id="country_origin"
                                  name="country_origin"
                                  v-model="countrySelected"
                                  v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('country_origin')">
                                  <span>{{ errors.first('country_origin') }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="col-2">
                            <label for="duration" class="col-sm-12 col-form-label">
                              {{ $t('packages.duration') }} ({{ $t('packages.nights') }})
                            </label>
                        </div>
                        <div class="col-4">
                            <input class="form-control" id="duration" max="20" min="1" type="number"
                              v-model.number="form.duration">
                        </div>

                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-2 col-form-label" for="physical_intensity">
                          {{ $t('package.package_physical_intensity') }}
                        </label>
                        <div class="col-4">
                            <v-select :options="physical_intensities"
                              :value="form.physical_intensity_id"
                              autocomplete="true"
                              data-vv-as="physical_intensity"
                              data-vv-name="physical_intensity"
                              id="physical_intensity"
                              name="physical_intensity"
                              @input="physicalIntensityChange"
                              v-model="physicalIntensitySelected"
                              v-validate="'required'">
                            </v-select>
                            <span class="invalid-feedback-select" v-show="errors.has('physical_intensity')">
                                <span>{{ errors.first('physical_intensity') }}</span>
                            </span>
                        </div>

                        <div class="col-2">
                            <label for="tag" class="col-sm-12 col-form-label">
                              {{ $t('package.category') }}
                            </label>
                        </div>

                        <div class="col-4">
                            <multiselect v-model="tagSelected" track-by="name" name="tag" id="tag"
                              v-validate="'required'"
                              @input="tagChange"
                              label="name" placeholder="Select one" :options="tags" :searchable="false"
                              :allow-empty="false">
                              <template slot="singleLabel" slot-scope="{ option }">
                                <strong>{{ option.name }}</strong> pertenece al grupo<strong> {{ option.group }}</strong>
                              </template>
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('tag')">
                                <span>{{ errors.first('tag') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">

                        <label for="type_package" class="col-2 col-form-label">{{ $t('packages.type') }}</label>

                        <div class="col-4">
                            <b-form-group>
                                <b-form-radio name="type_package" v-model="form.type_package" value="0">
                                  {{ $t('packages.package') }}
                                </b-form-radio>
                                <b-form-radio name="type_package" v-model="form.type_package" value="1">
                                  {{ $t('packages.extension') }}
                                </b-form-radio>
                                <b-form-radio name="type_package" v-model="form.type_package" value="2">
                                  Exclusivos
                                </b-form-radio>
                            </b-form-group>
                        </div>

                        <div class="col-2">
                            <label for="rate_dynamic" class="col-sm-12 col-form-label">
                              {{ $t('packages.price_from') }}
                            </label>
                        </div>

                        <div class="col-2">
                            <input class="form-control" id="rate_dynamic" max="1000" min="0" step="0.01" type="number"
                              v-model.number="form.rate_dynamic">
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label for="package_recommended" class="col-auto col-form-label">
                          {{ $t('package.package_recommended') }}
                        </label>
                        <div class="col">
                            <div class="col-sm-12 p-0">
                                <c-switch :value="true" class="mx-1" color="success"
                                  v-model="form.recommended" id="package_recommended"
                                  variant="pill">
                                </c-switch>
                            </div>
                        </div>

                        <label for="allow_modify" class="col-auto col-form-label">
                          {{ $t('packages.allow_modify') }}
                        </label>
                        <div class="col">
                          <div class="col-sm-12 p-0">
                              <c-switch :value="true" class="mx-1" color="success"
                                v-model="form.allow_modify" id="allow_modify"
                                variant="pill">
                              </c-switch>
                          </div>
                        </div>

                        <label for="free_sale" class="col-auto col-form-label">
                          Free Sale
                        </label>
                        <div class="col">
                          <div class="col-sm-12 p-0">
                              <c-switch :value="true" class="mx-1" color="success"
                                v-model="form.free_sale" id="free_sale"
                                variant="pill">
                              </c-switch>
                          </div>
                        </div>
                    </div>
                </div>
                
                <div class="b-form-group form-group">
                    <div class="mb-4">
                      <label for="map_itinerary_link">Portada (URL)</label>
                      <div class="col-sm-12 p-0">
                          <input class="form-control" type="text" v-model="form.portada_link"
                            data-vv-as="portada_link" id="portada_link"
                            data-vv-name="portada_link" name="portada_link"
                            v-validate="'url'" readonly="readonly">
                          <span class="invalid-feedback-select" v-show="errors.has('portada_link')">
                            <span>{{ errors.first('portada_link') }}</span>
                          </span>
                      </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="mb-4">
                      <label for="map_link">Mapa (URL)</label>
                      <div class="col-sm-12 p-0">
                          <input class="form-control" type="text" v-model="form.map_link"
                            data-vv-as="map_link" id="map_link"
                            data-vv-name="map_link" name="map_link"
                            v-validate="'url'">
                          <span class="invalid-feedback-select" v-show="errors.has('map_link')">
                            <span>{{ errors.first('map_link') }}</span>
                          </span>
                      </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="mb-4">
                      <label for="map_itinerary_link">Mapa Itinerario (URL)</label>
                      <div class="col-sm-12 p-0">
                          <input class="form-control" type="text" v-model="form.map_itinerary_link"
                            data-vv-as="map_itinerary_link" id="map_itinerary_link"
                            data-vv-name="map_itinerary_link" name="map_itinerary_link"
                            v-validate="'url'" readonly="readonly">
                          <span class="invalid-feedback-select" v-show="errors.has('map_itinerary_link')">
                            <span>{{ errors.first('map_itinerary_link') }}</span>
                          </span>
                      </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px" />
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import { API, APICLOUDINARY } from './../../api'
  import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
  import BFormRadio from 'bootstrap-vue/es/components/form-radio/form-radio'
  import BFormRadioGroup from 'bootstrap-vue/es/components/form-radio/form-radio-group'
  import Multiselect from 'vue-multiselect'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import { Switch as cSwitch } from '@coreui/vue'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

  export default {
    components: {
      BFormGroup,
      BFormRadio,
      BFormRadioGroup,
      vSelect,
      cSwitch,
      Multiselect,
      Loading
    },
    data: () => {
      return {
        loading: false,
        loadingPackage: false,
        formAction: 'post',
        physical_intensities: [],
        tags: [],
        tagSelected: [],
        countries: [],
        countrySelected: [],
        physicalIntensitySelected: [],
        form: {
          code: '',
          physical_intensity_id: '',
          tag_id: '',
          portada_link: '',
          map_link: '',
          map_itinerary_link: '',
          duration: 1,
          type_package: 0,
          rate_dynamic: 0,
          country_id: '',
          recommended: 0,
          free_sale: true,
          allow_modify: true,
        },
        isProcessingPlanRates: false
      }
    },
    mounted () {
      window.origin_cloudinary = `https://res.cloudinary.com/litodti/image/upload`

      this.$root.$emit('updateTitlePackage')
      //countries
      API.get('/country/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          let c = result.data.data
          c.forEach((country) => {
            this.countries.push({
              label: country.translations[0].value,
              code: country.translations[0].object_id
            })
          })

        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('services.error.messages.name'),
          text: this.$t('services.error.messages.connection_error')
        })
      })

      if (this.$route.params.id !== undefined) {
        this.loadingPackage = true;
        API.get('/packages/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.isProcessingPlanRates = result.data.data.is_processing_plan_rates || false;

            APICLOUDINARY({
              method: 'get',
              baseURL: window.origin_cloudinary,
              url: `/packages/${this.$route.params.id}/frontpage.jpg`,
            })
            .then((result) => {
              this.form.portada_link = `${window.origin_cloudinary}/packages/${this.$route.params.id}/frontpage.jpg`;
            })

            APICLOUDINARY({
              method: 'get',
              baseURL: window.origin_cloudinary,
              url: `/packages/${this.$route.params.id}/map.jpg`,
            })
            .then((result) => {
              this.form.map_itinerary_link = `${window.origin_cloudinary}/packages/${this.$route.params.id}/map.jpg`;
            })

            this.form.code = result.data.data.code
            this.form.duration = result.data.data.nights
            this.form.type_package = result.data.data.extension
            this.form.rate_dynamic = result.data.data.rate_dynamic
            this.form.map_link = (result.data.data.map_link != null) ? result.data.data.map_link.replace('//', '') : ''
            this.form.allow_modify = (result.data.data.allow_modify == 0) ? false : true
            this.form.recommended = (result.data.data.recommended == 0) ? false : true
            this.form.free_sale = (result.data.data.free_sale == 0) ? false : true
            this.formAction = 'put'

            //Pais
            this.form.country_id = result.data.data.country_id
            this.countrySelected.push({
              code: result.data.data.country_id,
              label: result.data.data.country.translations[0].value.toUpperCase(),
            })

            //Physical_intensity
            this.form.physical_intensity_id = result.data.data.physical_intensity_id
            this.physicalIntensitySelected.push({
              code: result.data.data.physical_intensity_id,
              label: result.data.data.physical_intensity.translations[0].value.toUpperCase(),
            })

            //Tag
            this.form.tag_id = result.data.data.tag_id
            this.tagSelected.push({
              code: result.data.data.tag_id,
              name: result.data.data.tag.translations[0].value.toUpperCase(),
              group: result.data.data.tag.tag_group.translations[0].value.toUpperCase(),
            })
            this.loadingPackage = false;
          })
      }
      API.get('/physical_intensities/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          let _physical_intensities = result.data.data
          _physical_intensities.forEach((p_i) => {
            this.physical_intensities.push({
              label: p_i.translations[0].value.toUpperCase(),
              code: p_i.translations[0].object_id
            })
          })
        })
      API.get('/tags/selectBox?lang=' + localStorage.getItem('lang'))
        .then((result) => {

          let _tags = result.data.data

          let _groups = []
          let _nameGroups = []
          _tags.forEach((tag) => {
            if (!(_groups[tag.tag_group_id])) {
              _groups[tag.tag_group_id] = []
            }
            _groups[tag.tag_group_id].push(tag)
            _nameGroups[tag.tag_group_id] = tag.tag_group.translations[0].value
          })

          _groups.forEach((group, g) => {
            this.tags.push({
              name: _nameGroups[g] + ':',
              group: _nameGroups[g],
              $isDisabled: true
            })
            group.forEach((g) => {
              this.tags.push({
                name: g.translations[0].value,
                group: g.tag_group.translations[0].value,
                code: g.translations[0].object_id
              })
            })
          })

        })
    },
    methods: {
        reloadPage() {
            window.location.reload();
        },
      physicalIntensityChange () {
        this.form.physical_intensity_id = this.physicalIntensitySelected.code
      },
      tagChange () {
        this.form.tag_id = this.tagSelected.code
      },
      countryChange () {
        this.form.country_id = this.countrySelected.code
      },
      CancelForm () {
        this.id_image = ''
        this.$router.push({ path: '/packages/list' })
      },
      validateBeforeSubmit () {
        if ((this.form.physical_intensity == '' && this.formAction == 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('packages.package'),
            text: this.$t('packages.error.messages.package_physical_intensity_complete')
          })
          return false
        }

        if (((this.form.duration == '' || this.form.duration <= 0 || this.form.duration > 20) && this.formAction == 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('packages.package'),
            text: this.$t('packages.error.messages.package_duration_complete')
          })
          return false
        }

        this.$validator.validateAll().then((result) => {
          if (result) {
            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.packages'),
              text: this.$t('packages.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'packages/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              if (this.formAction == 'post') {
                this.$router.push('/packages/' + result.data.object_id + '/manage_package')
              } else {
                this.$router.push('/packages/list')
              }
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Paquetes',
                text: result.data.message
              })

              this.loading = false
            }
          })
      }
    },
    filters: {
      capitalize: function (value) {
        if (!value) return ''
        value = value.toString().toLowerCase()
        return value.charAt(0).toUpperCase() + value.slice(1)
      }
    }
  }
</script>

<style lang="stylus">

</style>
