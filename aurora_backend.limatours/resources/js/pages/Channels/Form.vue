<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="channel_name">{{ $t('channels.channel_name')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'input':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="name" name="name" placeholder=""
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="channel_code">{{ $t('channels.channel_code')
                            }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'input':true }"
                                   id="code" name="code" placeholder=""
                                   type="text"
                                   v-model="form.code">
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('channels.status.title') }}</label>
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
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'ChannelsList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import MenuEdit from './../../components/MenuEdit'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'menu-edit': MenuEdit,
      cSwitch
    },
    data: () => {
      return {
        showModal: false,
        languages: [],
        currentLang: '1',
        invalidError: false,
        invalidErrorCode: false,
        countError: 0,
        countErrorCode: 0,
        showError: false,
        loading: false,
        formAction: 'post',
        form: {
          name: '',
          code: '',
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
      },
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/channels/' + this.$route.params.id)
          .then((result) => {

            this.form = result.data.data
            this.formAction = 'put'
            this.form.status = (this.form.status == 1 ? true : false)
          })
      }
    },
    methods: {
      validateBeforeSubmit () {

        if (this.form.name == '' && this.formAction == 'post') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('channels.title'),
            text: this.$t('channels.error.messages.information_complete')
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
              title: this.$t('global.modules.channels'),
              text: this.$t('channels.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {


        if (this.form.name == '') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('channels.title'),
            text: this.$t('channels.error.messages.information_complete')
          })
          return false
        }

        this.loading = true
        if (this.formAction != 'put') {
          this.form.status = (this.form.status == false ? 0 : 1)
        }

        API({
          method: this.formAction,
          url: 'channels/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/channels/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.channels'),
                text: this.$t('channels.error.messages.channel_incorrect')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('channels.error.messages.name'),
            text: this.$t('channels.error.messages.connection_error')
          })
        })
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'channels/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/channels/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.channels'),
                text: this.$t('channels.error.messages.channel_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('channels.error.messages.name'),
            text: this.$t('channels.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>


