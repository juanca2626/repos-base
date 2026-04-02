<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="name" name="name"
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
                        <label class="col-sm-2 col-form-label" for="code">{{ $t('code') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="code" name="code"
                                   type="text"
                                   v-model="form.code" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('code')"/>
                                <span v-show="errors.has('code')">{{ errors.first('code') }}</span>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="email">{{ $t('email') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   data-vv-as="email" id="email"
                                   name="email"
                                   type="text" v-model="form.email" v-validate="'required|email'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('email')"/>
                                <span v-show="errors.has('email')">{{ errors.first('email') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="position">{{ $t('position') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="position" name="position"
                                   type="text"
                                   v-model="form.position" v-validate="''">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('position')"/>
                                <span v-show="errors.has('position')">{{ errors.first('position') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('status') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value="form.status" class="mx-1" color="primary"
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
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>
        <div class="col-sm-6 text-right" v-if="form.action==='put'">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="remove" class="btn btn-danger" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'times']"/>
                    {{ $t('global.buttons.delete') }}
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
    props: ['form'],
    components: {
      VueBootstrapTypeahead,
      cSwitch,
    },
    data: () => {
      return {
        languages: [],
        user: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,

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

    },
    methods: {
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {
        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.users'),
              text: this.$t('error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true
        console.log(this.form)
        API({
          method: this.form.action,
          url: 'users/' + (this.form.id !== null ? this.form.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.users'),
                text: this.$t('error.messages.user_incorrect')
              })

              this.loading = false
            } else {
              this.close()
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'users/' + (this.form.id !== null ? this.form.id : '')
        })
          .then((result) => {
            console.log(result)
            if (result.data.success === true) {
              this.$router.push('/informations/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.users'),
                text: this.$t('error.messages.contact_delete')
              })

              this.loading = false
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
