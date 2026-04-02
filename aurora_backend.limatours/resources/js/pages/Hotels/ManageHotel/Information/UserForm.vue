<template>
  <div class="row">
    <div class="col-sm-12">
      <form @submit.prevent="validateBeforeSubmit">
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
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
            <label class="col-sm-2 col-form-label" for="code">{{
                $t('hotelsmanagehotelinformation.code')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                     id="code" name="code"
                     type="text"
                     v-model="form.code" v-validate="'required|max:6'">

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
            <label class="col-sm-2 col-form-label" for="email">{{
                $t('hotelsmanagehotelinformation.email')
              }}</label>
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
            <label class="col-sm-2 col-form-label" for="password">{{
                $t('hotelsmanagehotelinformation.password')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                     autocomplete="off" id="password" name="password"
                     placeholder="Password" ref="password" type="password" v-model="form.password"
                     v-validate="'min:6|max:35'">
              <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                   style="margin-left: 5px;" v-show="errors.has('password')"/>
                <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label" for="confirm_password">{{
                $t('hotelsmanagehotelinformation.confirm_password')
              }}</label>
            <div class="col-sm-5">
              <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                     autocomplete="off" data-vv-as="password"
                     id="confirm_password" name="confirm_password" placeholder="Confirm password"
                     type="password" v-model="form.password"
                     v-validate="''">
            </div>
          </div>
        </div>
        <!--
        <div class="b-form-group form-group">
            <div class="form-row">
                <label class="col-sm-4 col-form-label">{{ $t('hotelsmanagehotelinformation.access_hotel')
                    }}</label>
                <div class="col-sm-5">
                    <c-switch class="mx-1" color="primary"
                              v-model="form.range"
                              variant="pill">
                    </c-switch>
                </div>
            </div>
        </div>
        -->
        <div class="b-form-group form-group">
          <div class="form-row">
            <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
            <div class="col-sm-5">
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
          {{ $t('global.buttons.save') }}
        </button>
        <button @click="close" class="btn btn-danger" type="reset" v-if="!loading">
          {{ $t('global.buttons.cancel') }}
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
      if (this.errors.has('name') === false && this.form.name !== '') {
        this.invalidError = false
        this.countError += 1
        return true

      } else if (this.countError > 0) {
        this.invalidError = true
      }
      return false
    },
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
            text: this.$t('hotelsmanagehotelinformation.error.messages.information_complete'),
          })

          this.loading = false
        }
      })
    },
    submit () {

      this.loading = true
      API({
        method: this.form.action,
        url: 'hotel_users/' + (this.form.id !== null ? this.form.id : ''),
        data: this.form,
      }).then((result) => {
        if (result.data.success === false) {

          let _errors = result.data.errors
          // Encuentra el último arreglo de errores no vacío
          var lastErrorArray = Object.values(_errors).find(arr => Array.isArray(arr) && arr.length > 0);
          // Obtén el último mensaje de error si se encontró un arreglo no vacío
          var lastErrorMessage = lastErrorArray ? lastErrorArray[lastErrorArray.length - 1] : "No hay mensajes de error";

          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.users'),
            text: lastErrorMessage,
          })

          this.loading = false
        } else {
          this.close()
        }
      }).catch((e) => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotelsmanagehotelinformation.error.messages.name'),
          text: this.$t('hotelsmanagehotelinformation.error.messages.connection_error: ' + e),
        })
      })
    },
  },
}
</script>

<style lang="stylus">

</style>
