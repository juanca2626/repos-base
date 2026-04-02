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
                        <label class="col-sm-2 col-form-label" for="surname">{{ $t('surname') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="surname" name="surname"
                                   type="text"
                                   v-model="form.surname" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('surname')"/>
                                <span v-show="errors.has('surname')">{{ errors.first('surname') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="lastname">{{ $t('lastname') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true, 'is-valid':validError, 'is-invalid':invalidError }"
                                   id="lastname" name="lastname"
                                   type="text"
                                   v-model="form.lastname" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('lastname')"/>
                                <span v-show="errors.has('lastname')">{{ errors.first('lastname') }}</span>
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
                                   v-model="form.position" v-validate="'required'">

                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('position')"/>
                                <span v-show="errors.has('position')">{{ errors.first('position') }}</span>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom: 5px;">
                    <label class="col-sm-2 col-form-label" for="position">{{ $t('hotel') }}</label>
                    <div class="col-sm-5">
                        <select class="form-control" id="hotel_id" required size="0" v-model="form.hotel_id">
                            <option :value="hotel.value" v-for="hotel in hotels">
                                {{ hotel.text }}
                            </option>
                        </select>
                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorHotel">
                            <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                            <span>{{ $t('error.required') }}</span>
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
        contact: null,
        hotels: [],
        hotel: null,
        hotelSearch: '',
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
      }
    },
    computed: {
      errorHotel: function () {
        if (this.form.hotel_id == null) {
          return false
        }

      },
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

      //hotels  
      API.get('/hotel/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.hotels = result.data.data
          console.log(this.hotels)
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('error.messages.name'),
          text: this.$t('error.messages.connection_error')
        })
      })
    },
    methods: {
      close () {
        this.$emit('changeStatus', false)
      },
      validateBeforeSubmit () {
        console.log(this.form)
        if (this.form.hotel_id == null) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotel'),
            text: this.$t('error.messages.hotel_incorrect')
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
              text: this.$t('error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.form.action,
          url: 'contacts/' + (this.form.id !== null ? this.form.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.contacts'),
                text: this.$t('error.messages.contact_incorrect')
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
          url: 'contacts/' + (this.form.id !== undefined ? this.form.id : '')
        })
          .then((result) => {
            console.log(result)
            if (result.data.success === true) {
              this.$router.push('/informations/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.contacts'),
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
