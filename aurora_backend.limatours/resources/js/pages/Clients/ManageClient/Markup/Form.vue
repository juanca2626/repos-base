<template>
    <div class="row mt-3">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="period">{{ $t('clientsmanageclientmarkup.period')
                            }}</label>
                        <div class="col-sm-3">
                            <select  class="form-control" id="selectPeriod" required size="0" v-model="form.period">
                                <option :value="period.value" v-for="period in periods">
                                    {{ period.value }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="markup_hotel">{{
                            $t('clientsmanageclientmarkup.markup_hotel') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="hotel" name="hotel"
                                   type="text"
                                   v-model="form.hotel" v-validate="'decimal:2|required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('hotel')"/>
                                <span v-show="errors.has('hotel')">{{ errors.first('hotel') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="service">{{
                            $t('clientsmanageclientmarkup.markup_service') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
                                   id="service" name="service"
                                   type="text"
                                   v-model="form.service" v-validate="'decimal:2|required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('service')"/>
                                <span v-show="errors.has('service')">{{ errors.first('service') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-4 col-form-label">{{ $t('global.status') }}</label>
                        <div class="col-sm-2 mt-1">
                            <c-switch class="mx-1" color="primary"
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
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.save')}}
                </button>
                <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    props: ['form'],
    components: {
      VueBootstrapTypeahead,
      cSwitch
    },
    data: () => {
      return {
        languages: [],
        clients: [],
        hotel: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        periods: [],
      }
    },
    computed: {
          },
    mounted() {
        //periods
        API.get('/markups/selectPeriod?lang=' + localStorage.getItem('lang'))
        .then((result) => {
            this.periods = result.data
            console.log("result")
            console.log(result.data)
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clientsmanageclientmarkup.error.messages.name'),
            text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
          })
        })
    },
    methods: {
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {
        if (this.form.period === null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clientsmanageclientmarkup.markup'),
            text: this.$t('clientsmanageclientmarkup.error.messages.select_period')
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
              title: this.$t('global.modules.contacts'),
              text: this.$t('clientsmanageclientmarkup.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true;
        this.form.region_id = this.$route.params.region_id;

        API({
          method: this.form.action,
          url: 'markups' + (this.form.id !== null ? '/'+this.form.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false && result.data.message == 'existing') {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.markups'),
                text: this.$t('clientsmanageclientmarkup.error.messages.existing')
              })

              this.loading = false
            } else if (result.data.success === false){
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.markups'),
                text: this.$t('clientsmanageclientmarkup.error.messages.markup_incorrect')
              })
              this.loading = false
            } else {
              this.close()
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clientsmanageclientmarkup.error.messages.name'),
            text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
