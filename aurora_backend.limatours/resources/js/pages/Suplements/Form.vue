<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row" id="container_suplement">
                        <label class="col-sm-1 col-form-label" for="suplement_name">{{ $t('suplements.suplement_name')
                            }}</label>
                        <div class="col-sm-4">
                            <input :class="{'form-control':true }"
                                   id="suplement_name" name="suplement_name"
                                   type="text"
                                   v-model="form.translations[currentLang].suplement_name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('suplement_name')"/>
                                <span v-show="errors.has('suplement_name')">{{ errors.first('suplement_name') }}</span>
                            </div>
                        </div>
                        <select class="col-sm-1 form-control" id="lang" required size="0" v-model="currentLang">
                            <option v-bind:value="language.id" v-for="language in languages">
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="per_person">{{ $t('suplements.per_person')
                            }}</label>
                        <div class="col-sm-2" id="per_person">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.per_person"
                                      variant="pill" @change="changeSwitchPerPerson">
                            </c-switch>
                        </div>
                        <label class="col-sm-2 col-form-label" for="per_room">{{ $t('suplements.per_room') }}</label>
                        <div class="col-sm-2" id="per_room">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.per_room"
                                      variant="pill"  @change="changeSwitchPerRoom">
                            </c-switch>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="state">{{ $t('suplements.state') }}</label>
                        <div class="col-sm-2" id="state">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.state"
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
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'SuplementsList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import {API} from './../../api'
  import {Switch as cSwitch} from '@coreui/vue'

  export default {
    components: {
      cSwitch,
    },
    data: () => {
      return {
        languages: [],
        suplement: null,
        showError: false,
        currentLang: '1',
        invalidError: false,
        countError: 0,
        loading: false,
        formAction: 'post',
        form: {
          per_person: false,
          per_room: true,
          state: true,
          translations: {
            '1': {
              'id': '',
              'suplement_name': ''
            }
          }
        }
      }
    },
    computed: {},
    mounted: function () {
      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id

          let form = this.form

          let languages = this.languages

          languages.forEach((value) => {
            form.translations[value.id] = {
              id: '',
              suplement_name: ''
            }
          })
          if (this.$route.params.id !== undefined) {

            API.get('/suplements/' + this.$route.params.id)
              .then((result) => {
                this.suplement = result.data.data
                this.formAction = 'put'
                this.form.per_person = (this.suplement.per_person ==1) ? true:false
                this.form.per_room = (this.suplement.per_room ==1) ?true:false
                this.form.state = (this.suplement.state ==1) ?true:false
                let arrayTranslations = this.suplement.translations

                arrayTranslations.forEach((translation) => {
                  form.translations[translation.language_id] = {
                    id: translation.id,
                    suplement_name: translation.value
                  }
                })
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('suplements.error.messages.name'),
                text: this.$t('suplements.error.messages.connection_error')
              })
            })
          }

          this.form = form
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('suplements.error.messages.name'),
          text: this.$t('suplements.error.messages.connection_error')
        })
      })
    },
    methods: {
      changeSwitchPerPerson:function(){
        if (this.form.per_person)
        {
          this.form.per_room = false
        }else{
          this.form.per_room = true
        }
      },
      changeSwitchPerRoom:function(){
        if (this.form.per_room)
        {
          this.form.per_person = false
        }else{
          this.form.per_person = true
        }
      },
      validateBeforeSubmit() {
        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.suplements'),
              text: this.$t('suplements.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit() {

        this.loading = true

        API({
          method: this.formAction,
          url: 'suplements/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              if(this.formAction==='put'){
                this.$forceUpdate()
              }
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.suplements'),
                text: this.$t('suplements.error.messages.suplement_incorrect')
              })
              this.loading = false
            } else {
              this.$router.push('/suplements/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      },
      remove() {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'suplements/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/suplements/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.suplements'),
                text: this.$t('suplements.error.messages.suplement_delete')
              })

              this.loading = false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    #container_suplement
        margin-bottom 15 px
</style>
