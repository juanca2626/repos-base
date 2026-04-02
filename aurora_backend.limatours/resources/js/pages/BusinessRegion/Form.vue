<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <!-- <label class="col-sm-2 col-form-label" for="zone_name">{{ $t('zones.zone_name') }}</label> -->
                         <label class="col-sm-2 col-form-label" for="description">Descripción</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="description" name="description"
                                   type="text"
                                   v-model="form.description"
                                   v-validate="'required'"
                                   >
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('description')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ errors.first('description') }}</span>
                            </div>
                            <span v-if="apiError" class="invalid-feedback d-block">
                                {{  apiError }}
                            </span>
                        </div>
                        <!-- <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select> -->
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
                <router-link :to="{ name: 'BusinessRegionList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>
<script>
  import { data } from 'jquery'
  import { API } from './../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'

  export default {
    components: {
      VueBootstrapTypeahead
    },
    data: () => {
      return {
        loading: false,
        formAction: 'post',
        cities: [],
        city: null,
        citySearch: '',
        languages: [],
        currentLang: '1',
        invalidError: false,
        countError: 0,
        apiError: null,
        form: {
            id: '',
            description: ''
        //   city_id: null,
        //   translations: {
        //     '1': {
        //       'id': '',
        //       'description': ''
        //     }
        //   }
        }
      }
    },
    mounted () {
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let form = {
            translations: {}
          }

          let languages = this.languages

          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              description: ''
            }
          })

          if (this.$route.params.id !== undefined) {
            API.get('/business_region/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
              .then((result) => {
                console.log(result);
                this.form = result.data.data;
                this.formAction = 'put'
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('zones.error.messages.name'),
                text: this.$t('zones.error.messages.connection_error')
              })
            })
          }
          this.form = form
        })
        .catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('zones.error.messages.name'),
                text: this.$t('zones.error.messages.connection_error')
            })
        })
    },
    computed: {
      validError: function () {
        if (this.errors.has('description') === false) {
          this.invalidError = false
          this.countError += 1
          return true
        } else {
          if (this.countError > 0) {
            this.invalidError = true
          }
          return false
        }
      }
    },
    methods: {
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.cities'),
              text: this.$t('zones.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      async submit () {
        if (this.formAction == 'put' && this.form.id != '') {
            this.form.id = this.$route.params.id
        }
        this.loading = true
        // console.log(this.form);
        try{
            const response = await API({
                method: this.formAction,
                url: 'business_region' + (this.$route.params.id !== undefined ? `/${this.$route.params.id}` : ''),
                data: this.form
            })
            if(response.data.success){
                this.$router.push('/business_region/list')
            }else{
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Ocurrió un error al hacer la petición",
                    text: data.errors
                })
            }
        }
        catch(error){
            if(error.response && error.response.status === 422){
                const errors = error.response.data.errors;
                this.errors.remove('description');
                if (errors.description) {
                    errors.description.forEach(errorMsg => {
                        this.errors.add({
                            field: 'description',
                            msg: errorMsg
                        });
                    });
                }
            }else{
                this.apiError = 'Ocurrió un error al procesar la solicitud';
                console.log('Error:', error);
            }
        }
        finally {
            this.loading = false
        }
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'business_region/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/business_region/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: "Error al eliminar",
                text: "Ocurrió un error al eliminar una región"
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>

<style lang="stylus">

</style>
