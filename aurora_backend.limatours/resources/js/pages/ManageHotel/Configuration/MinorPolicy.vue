<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit>
                <form class="row">
                    <div class="col-6">
                        <div class="col-12 mb-4 mt-2">
                            <b-form-checkbox id="checkbox-1"
                                             name="checkbox-1" unchecked-value="null"
                                             v-model="form.allows_child"
                                             value="ok">
                                {{$t('allows_child')}}
                            </b-form-checkbox>
                        </div>
                        <div class="col-12">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-3 col-form-label" for="mini_child">{{$t('min_child')}}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control input" id="mini_child" name="mini_child"
                                               placeholder="" type="text" v-model="form.min_age_child">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-3 col-form-label"
                                           for="max_child">{{$t('max_teenagers')}}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control input" id="max_child" name="max_child" placeholder=""
                                               type="text"
                                               v-model="form.max_age_child">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="col-12 mb-4 mt-2">
                            <b-form-checkbox id="checkbox-2"
                                             name="checkbox-2"
                                             unchecked-value="null" v-model="form.allows_teenagers"
                                             value="ok">
                                {{$t('allows_teenagers')}}
                            </b-form-checkbox>
                        </div>
                        <div class="col-12">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-3 col-form-label"
                                           for="mini_teenagers">{{$t('min_teenagers')}}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control input" id="mini_teenagers" name="mini_teenagers"
                                               placeholder=""
                                               type="text"
                                               v-model="form.min_age_teenagers">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="b-form-group form-group">
                                <div class="form-row">
                                    <label class="col-sm-3 col-form-label"
                                           for="max_teenagers">{{$t('max_teenagers')}}</label>
                                    <div class="col-sm-9">
                                        <input class="form-control input" id="max_teenagers" name="max_teenagers"
                                               placeholder=""
                                               type="text"
                                               v-model="form.max_age_teenagers">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../api'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    // props: ['hotel_id'],
    components: {
      VueBootstrapTypeahead,
      cSwitch,
    },
    data: () => {
      return {
        languages: [],
        minor: [],
        hotel_id: 1,
        loading: false,
        form: {
          allows_child: null,
          min_age_child: null,
          max_age_child: null,
          allows_teenagers: null,
          max_age_teenagers: null,
          min_age_teenagers: null,
        }
      }
    },
    computed: {},
    mounted: function () {
      this.fetchData(this.$i18n.locale)
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {

        if (this.form == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotel'),
            text: this.$t('error.messages.hotel_incorrect')
          })
          return false
        } else {
          this.submit()
        }
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('minor_policies/?lang=' + lang + '&id=' + this.hotel_id).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.minor = result.data.data
            if (this.minor[0].allows_child == 1) {
              this.form.allows_child = 'ok'
            }
            if (this.minor[0].allows_teenagers == 1) {
              this.form.allows_teenagers = 'ok'
            }
            this.form.min_age_child = this.minor[0].min_age_child
            this.form.max_age_child = this.minor[0].max_age_child
            this.form.max_age_teenagers = this.minor[0].max_age_teenagers
            this.form.min_age_teenagers = this.minor[0].min_age_teenagers
            console.log(this.minor)
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
      },
      submit () {

        API({
          method: 'put',
          url: 'minor_policies/' + (this.hotel_id),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.minor_policy'),
                text: this.$t('error_insert')
              })

              this.loading = false
            } else {
              this.$notify({
                group: 'main',
                type: 'success',
                title: this.$t('global.modules.minor_policy'),
                text: this.$t('success_insert')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
