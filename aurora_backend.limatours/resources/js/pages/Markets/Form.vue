<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="market_name">{{ $t('markets.market_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="market_name"
                                   name="market_name"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('market_name')"/>
                                <span v-show="errors.has('market_name')">{{ errors.first('market_name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('markets.status.title') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.status"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button
                        @click="validateBeforeSubmit"
                        class="btn btn-success"
                        type="submit"
                        v-if="!loading && $can('update', 'markets') && $can('create', 'markets')">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'MarketsList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      cSwitch
    },
    data: () => {
      return {
        market: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          name: '',
          status: false
        }
      }
    },
    computed: {
      validError: function () {
        if (this.errors.has('name') == false && this.form.name != '') {
          this.invalidError = false
          this.countError += 1
          return true

        } else if (this.countError > 0) {
          this.invalidError = true
        }
        return false
      }
    },
    mounted: function () {
      if (this.$route.params.id !== undefined) {
        API.get('/markets/' + this.$route.params.id)
          .then((result) => {
            this.market = result.data.data
            this.formAction = 'put'
            this.form.name = this.market.name
            this.form.status = (this.market.status == 1 ? true : false)
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
              title: this.$t('global.modules.markets'),
              text: this.$t('markets.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {
        this.loading = true
        if (this.formAction != 'put') {
          this.form.status = (this.form.status == false ? 0 : 1)
        }

        API({
          method: this.formAction,
          url: 'markets/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Cadena',
                text: result.data.message
              })

              this.loading = false
            } else {
              this.$router.push('/markets/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('markets.error.messages.name'),
            text: this.$t('markets.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>

