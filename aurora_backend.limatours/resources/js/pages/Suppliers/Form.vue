<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="code">Code</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="code" maxlength="6" name="code"
                                   placeholder="Código del Usuario"
                                   type="text"
                                   v-model="form.code" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('code')" />
                                <span v-show="errors.has('code')">{{ errors.first('code') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="name" name="name" placeholder="Nombre del Usuario"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')" />
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="address">{{ $t('users.mail') }}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="address" name="email" placeholder="Email del Usuario"
                                   type="text"
                                   v-model="form.email" v-validate="'required|email'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('email')" />
                                <span v-show="errors.has('email')">{{ errors.first('email') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="address">{{ $t('users.password') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }" id="password1" name="password1"
                                   placeholder="Contraseña del Usuario"
                                   type="password" ref="password1"
                                   v-model="form.password" v-validate="'min:6|max:35'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('password')" />
                                <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="address">{{ $t('users.repeate_password') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }" id="password2" name="password2"
                                   placeholder="Repetir Contraseña"
                                   type="password" autocomplete="off" data-vv-as="password1"
                                   v-model="form.password2" v-validate="'confirmed:password1'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('password2')" />
                                <span v-show="errors.has('password2')">{{ errors.first('password2') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="rol">{{ $t('users.markets') }}</label>
                        <div class="col-sm-5">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="markets"
                                         :preserve-search="true"
                                         :taggable="true"
                                         label="name"
                                         ref="multiselect"
                                         track-by="code"
                                         v-model="marketUser">
                            </multiselect>
                            <!-- @tag="addTag" -->
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="rol">{{ $t('users.rol') }}</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="rol" required size="0" v-model="form.role">
                                <option value=""></option>
                                <option :value="rol.value" v-for="rol in roles">
                                    {{ rol.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <!--
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="type">{{ $t('users.user_type') }}</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="type" required size="0" v-model="form.userType">
                                <option value=""></option>
                                <option :value="userType.value" v-for="userType in userTypes">
                                    {{ userType.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                -->
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value=true class="mx-1" color="success"
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
                <img src="/images/loading.svg" v-if="loading" width="40px" />
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'SuppliersList' }" v-if="!loading">
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
  import Multiselect from 'vue-multiselect'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      vSelect,
      Multiselect,
      cSwitch,
    },
    data: () => {
      return {
        loading: false,
        formAction: 'post',
        rolelenght: 0,
        roles: [],
        userTypes: [],
        markets: [],
        marketUser: [],
        codeMarkets: [],
        form: {
          id: '',
          code: '',
          name: '',
          email: '',
          password: '',
          password2: '',
          role: '',
          user_type_id: 2,
          status: true
        }
      }
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/suppliers/' + this.$route.params.id)
          .then((result) => {
            this.form.id = result.data.data.id
            this.form.code = result.data.data.code
            this.form.name = result.data.data.name
            this.form.email = result.data.data.email
            this.form.status = !!result.data.data.status
            this.rolelenght = result.data.data.roles.length
            if (this.rolelenght > 0) {
              this.form.role = result.data.data.roles[0].id
            }
            this.form.user_type_id = result.data.data.user_type_id

            //markets
            let arrayMarkets = result.data.data.markets
            let j = 0
            let argData = []
            arrayMarkets.forEach((markets) => {
              argData[j] = {
                code: markets.id,
                name: markets.name
              }
              j++
            })

            this.marketUser = argData
            this.form.marketUser = argData

            this.formAction = 'put'
          })
      }
      API.get('/roles/selectBox')
        .then((result) => {
          this.roles = result.data.data
        })
      API.get('/usertypes/selectBox')
        .then((result) => {
          this.userTypes = result.data.data
        })
      //markets
      API.get('/markets/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {

          let mark = result.data.data
          mark.forEach((market) => {
            this.markets.push({
              name: market.text,
              code: market.value
            })
          })
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('users.error.messages.name'),
          text: this.$t('users.error.messages.connection_error')
        })
      })
    },
    methods: {
      validateBeforeSubmit () {
        if (this.form.password == '' && this.formAction == 'post') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suppliers.title'),
            text: this.$t('users.error.messages.validate_pass_post')
          })
          return
        }

        if (this.form.password !== '') {
          if (this.form.password !== this.form.password2) {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('suppliers.title'),
              text: this.$t('users.error.messages.validate_pass')
            })
            return
          }
        }

        if (this.form.role == '') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suppliers.title'),
            text: this.$t('users.error.messages.select_rol')
          })
          return
        }
        /*
          if (this.form.userType == '') {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('suppliers.title'),
                text: this.$t('users.error.messages.select_type')
              })
              return
          }
  */
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.users'),
              text: this.$t('users.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {

        //markets
        let varable = this.marketUser
        let argData = []
        varable.forEach((maarket) => {
          argData.push(maarket.code)
        })
        this.form.status = (this.form.status == false ? 0 : 1)
        this.form.codeMarkets = argData
        this.loading = true

        API({
          method: this.formAction,
          url: 'suppliers/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            console.log('valor')
            console.log(result)
            if (result.data.success === true) {
              this.$router.push('/suppliers/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('suppliers.title'),
                text: this.$t('users.error.messages.information_complete')
              })

              this.loading = false
            }
          })
          .catch((e) => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('suppliers.title'),
              text: e.data.message
            })
          })
      }
    }
  }
</script>

<style lang="stylus">
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

