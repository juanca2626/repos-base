<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{$t('taxes.service_name')}}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="name" name="name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('name')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="value">{{$t('taxes.service_value')}}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="value" name="value"
                                   placeholder="Introduce el Impuesto"
                                   type="text"
                                   v-model="form.value" v-validate="'required|decimal|min:1'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                 v-show="errors.has('value')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"/>
                                <span>{{ errors.first('value') }}</span>
                            </div>
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
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'ServicesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../api'

  export default {
    components: {},
    data: () => {
      return {
        tax: null,
        loading: false,
        formAction: 'post',
        form: {
          name: '',
          country_id: null,
          type: 's',
          value: 0
        }
      }
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/taxes/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.tax = result.data.data

            this.form.name = this.tax[0].name
            this.form.country_id = this.tax[0].country_id
            this.form.value = this.tax[0].value

            this.formAction = 'put'
          })
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
              title: this.$t('global.modules.taxes'),
              text: this.$t('taxes.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        this.form.country_id = this.$route.params.country_id

        this.loading = true

        API({
          method: this.formAction,
          url: 'taxes/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/countries/taxes/' + this.$route.params.country_id + '/services/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.taxes'),
                text: this.$t('taxes.error.messages.information_error')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('taxes.error.messages.name'),
            text: this.$t('taxes.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>


