<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="room_type_id">{{
                            $t('hotelsmanagehotelrooms.room_type_id') }}</label>
                        <div class="col-sm-2">
                            <b-form-select id="room_type_id" v-model="form.room_type_id">
                                <option :value="room_type.id" v-for="room_type in room_types">
                                    {{ room_type.translations[0].value }}
                                </option>
                            </b-form-select>
                            <div class="bg-danger container_errors" v-show="errors.has('room_type_id')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('room_type_id') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="max_capacity">{{
                            $t('hotelsmanagehotelrooms.max_capacity') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="max_capacity" name="max_capacity"
                                   type="text"
                                   v-model="form.max_capacity" v-validate="'required|numeric|min_value:1'">
                            <div class="bg-danger container_errors" v-show="errors.has('max_capacity')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('max_capacity') }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="min_adults">{{
                            $t('hotelsmanagehotelrooms.min_adults') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="min_adults" name="min_adults"
                                   type="text"
                                   v-model="form.min_adults"
                                   v-validate="'required|numeric|min_value:0|max_value:'+ form.max_capacity">
                            <div class="bg-danger container_errors" v-show="errors.has('min_adults')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('min_adults') }}</span>
                            </div>
                        </div>
                    </div> -->
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="max_adults">{{
                            $t('hotelsmanagehotelrooms.max_adults') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="max_adults" name="max_adults"
                                   type="text"
                                   v-model="form.max_adults"
                                   v-validate="'required|numeric|min_value:'+form.min_adults+'|max_value:'+ form.max_capacity">
                            <div class="bg-danger container_errors" v-show="errors.has('max_adults')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('max_adults') }}</span>
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="max_child">{{ $t('hotelsmanagehotelrooms.max_child') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="max_child" name="max_child"
                                   type="text"
                                   v-model="form.max_child" v-validate="'required|numeric'">
                            <div class="bg-danger container_errors" v-show="errors.has('max_child')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('max_child') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="ignore_rate_child">Omitir pago extra</label>
                        <div class="col-sm-10" id="ignore_rate_child">
                            <b-form-group
                                :description="`Omitir pago extra, si la cantidad de pasajeros es: 1 adulto + 1 niño extra, para que la tarifa sea como una doble`"
                                label-for="ignore_rate_child">
                                <c-switch :value="true" class="mx-1" color="success" name="ignore_rate_child"
                                          v-model="form.ignore_rate_child" variant="pill"></c-switch>
                            </b-form-group>
                        </div>
                    </div>
                        <!--
                        <label class="col-sm-2 col-form-label" for="max_infants">{{
                            $t('hotelsmanagehotelrooms.max_infants') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="max_infants" name="max_infants"
                                   type="text"
                                   v-model="form.max_infants" v-validate="'required|numeric'">
                            <div class="bg-danger container_errors" v-show="errors.has('max_infants')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('max_infants') }}</span>
                            </div>
                        </div>
                        -->
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="min_inventory">{{
                            $t('hotelsmanagehotelrooms.min_inventory') }}</label>
                        <div class="col-sm-2">
                            <input :class="{'form-control':true}"
                                   id="min_inventory" name="min_inventory" placeholder=""
                                   type="text"
                                   v-model="form.min_inventory" v-validate="'required|numeric'">
                            <div class="bg-danger container_errors" v-show="errors.has('min_inventory')">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="margin_icon_error"/>
                                <span>{{ errors.first('min_inventory') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="state">{{ $t('hotelsmanagehotelrooms.state')
                            }}</label>
                        <div class="col-sm-2" id="state">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.state"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                </div>
                <b-card no-body>
                    <b-tabs card>
                        <b-tab :title="$t('hotelsmanagehotelrooms.room_details')">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="room_name">{{
                                    $t('hotelsmanagehotelrooms.room_name') }}</label>
                                <div class="col-sm-5">
                                    <input :class="{'form-control':true }"
                                           id="room_name" name="room_name"
                                           type="text"
                                           v-model="form.translations[currentLangName][0].room_name"
                                           v-validate="'required'">
                                    <div class="bg-danger container_errors" v-show="errors.has('room_name')">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           class="margin_icon_error"/>
                                        <span>{{ errors.first('room_name') }}</span>
                                    </div>
                                </div>
                                <select class="col-sm-1 form-control" required
                                        v-model="currentLangName">
                                    <option v-bind:value="language.id" v-for="language in languages">
                                        {{ language.iso }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="room_description">{{
                                    $t('hotelsmanagehotelrooms.room_description')
                                    }}</label>
                                <div class="col-sm-5">
                                        <textarea :class="{'form-control':true }"
                                                  id="room_description" name="room_description"
                                                  type="text"
                                                  v-model="form.translations[currentLangDescription][1].room_description"
                                                  v-validate="'required'">
                                        </textarea>
                                    <div class="bg-danger container_errors" v-show="errors.has('room_description')">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           class="margin_icon_error"/>
                                        <span>{{ errors.first('room_description') }}</span>
                                    </div>
                                </div>
                                <select class="col-sm-1 form-control" required
                                        v-model="currentLangDescription">
                                    <option v-bind:value="language.id" v-for="language in languages">
                                        {{ language.iso }}
                                    </option>
                                </select>
                            </div>
                        </b-tab>
                        <b-tab :title="$t('hotelsmanagehotelrooms.room_galery')">
                            <router-view ref="GaleryAdd"></router-view>
                        </b-tab>
                    </b-tabs>
                </b-card>
                <b-card no-body v-if="usersValidate">
                    <b-tabs card>
                        <b-tab :key="channel.id" :title="channel.name" v-for="channel in form.channels">
                            <div class="form-row container_channel_code">
                                <label class="col-sm-2 col-form-label">
                                    {{ $t('hotelsmanagehotelrooms.channel_code') }}
                                </label>
                                <div class="col-sm-5">

                                    <input :class="{'form-control':true }"
                                           name="channel_code"
                                           type="text"
                                           v-model="form.channels[channel.id].code"
                                    >
                                    <div class="bg-danger container_errors" v-show="errors.has('channel_code')"
                                         v-if="channel.id===1">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                           class="margin_icon_error"/>
                                        <span>{{ errors.first('channel_code') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="state">{{
                                    $t('hotelsmanagehotelrooms.channel_state') }}</label>
                                <div class="col-sm-5">
                                    <b-form-checkbox
                                            :id="'checkbox_'+channel.id"
                                            :name="'checkbox_'+channel.id"
                                            switch
                                            v-model="form.channels[channel.id].state"
                                            >
                                    </b-form-checkbox>
                                </div>
                            </div>
                            <div class="form-row" v-if="channel.name.includes('HYPERGUEST')">
                                <label class="col-sm-2 col-form-label" for="channel_state">Tipo:</label>
                                <div class="col-sm-5">
                                    <div class="form-inline">
                                        <label class="mr-2">PUSH</label>
                                        <b-form-radio
                                            class="mr-3"
                                            :id="'radio_push_'+channel.id"
                                            :name="'radio_type_'+channel.id"
                                            value="1"
                                            v-model="form.channels[channel.id].type"
                                            >
                                        </b-form-radio>

                                        <label class="mr-1">PULL</label>
                                        <b-form-radio
                                            :id="'radio_pull_'+channel.id"
                                            :name="'radio_type_'+channel.id"
                                            value="2"
                                            v-model="form.channels[channel.id].type"
                                            >
                                        </b-form-radio>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                    </b-tabs>
                </b-card>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="getRouteRoomsList()" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../../api'
  import { Switch as cSwitch } from '@coreui/vue'
  import BFormSelect from 'bootstrap-vue/es/components/form-select/form-select'
  import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
  import BTab from 'bootstrap-vue/es/components/tabs/tab'
  import BCard from 'bootstrap-vue/es/components/card/card'
  import BCardText from 'bootstrap-vue/es/components/card/card-text'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'

  export default {
    components: {
      cSwitch,
      BTab,
      BTabs,
      BCard,
      BCardText,
      BFormCheckbox,
      BFormCheckboxGroup,
      BFormSelect
    },
    data: () => {
      return {
        languages: [],
        room_types: [],
        loading: false,
        usersValidate:false,
        currentLangName: '1',
        currentLangDescription: '1',
        formAction: 'post',
        form: {
            room_type_id: null,
            max_capacity: 1,
            min_adults: 0,
            max_adults: 1,
            max_child: 0,
            max_infants: 0,
            min_inventory: 0,
            state: true,
            ignore_rate_child: false,// Ignorar pago tarifa extra de niño o infantes >= a 8 años
            hotel_id: null,
            channels: {},
            translations: {
                '1': [
                    {
                        'id': '',
                        'room_name': ''
                    },
                    {
                        'id': '',
                        'room_description': ''
                    }
                ]
            }
        },
      }
    },
    computed: function(){

    },
    created: function () {
      this.filter();
      this.form.hotel_id = this.$route.params.hotel_id
    },
    mounted () {
      API.get('/languages/').then((result) => {

        this.languages = result.data.data
        this.currentLangName = result.data.data[0].id
        this.currentLangDescription = result.data.data[0].id

        this.languages.forEach((value) => {
          this.form.translations[value.id] = [
            {
              id: '',
              room_name: ''
            },
            {
              id: '',
              room_description: ''
            }
          ]
        })

        API.get('/channels/selectHotelBox').then((result) => {
          let channels = result.data.data

          channels.forEach((channel) => {
            this.form.channels[channel.value] = {
              id: channel.value,
              name: channel.text,
              code: '',
              state: channel.value === 1,
              type: '',
            }
          })
        }).catch((e) => {
            console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
            text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
          })
        })

        API.get('/room_types/selectBox?lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.room_types = result.data.data
          }).catch((e) => {
            console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
            text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
          })
        })

        if (this.$route.params.room_id !== undefined) {
          API.get('/rooms/' + this.$route.params.room_id + '?lang=' + localStorage.getItem('lang'))
            .then((result) => {

              let room = result.data.data

              this.form.room_type_id = room.room_type_id
              this.form.max_capacity = room.max_capacity
              this.form.min_adults = room.min_adults
              this.form.max_adults = room.max_adults
              this.form.max_infants = room.max_infants
              this.form.max_child = room.max_child
              this.form.min_inventory = room.min_inventory
              this.form.state = !!room.state
              this.form.ignore_rate_child = !!room.ignore_rate_child

              this.formAction = 'put'

              let arrayTranslations = room.translations

              arrayTranslations.forEach((translation) => {
                if (translation.slug === 'room_name') {
                    if ( typeof this.form.translations[translation.language_id]  !== 'undefined')
                    {
                        this.form.translations[translation.language_id][0].id = translation.id
                        this.form.translations[translation.language_id][0].room_name = translation.value
                    }

                } else {
                    if ( typeof this.form.translations[translation.language_id]  !== 'undefined')
                    {
                        this.form.translations[translation.language_id][1].id = translation.id
                        this.form.translations[translation.language_id][1].room_description = translation.value
                    }
                }
              })

              room.channels.forEach((channel) => {
                this.form.channels[channel.id].code = channel.pivot.code
                this.form.channels[channel.id].state = !!channel.pivot.state,
                this.form.channels[channel.id].type = channel.pivot.type
              })
            }).catch((e) => {
              console.log(e)
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
              text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
            })
          })
        }
      })
    },
    methods: {
      filter(){
        API.get('hotels/filter')
            .then((result) => {
                this.usersValidate = !!result.data.access_bloqued;
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotels.error.messages.name'),
                text: this.$t('hotels.error.messages.connection_error')
            })
        })
      },
      getRouteRoomsList: function () {
        return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms'
      },
      validateBeforeSubmit: function () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.form.hotel_id = this.$route.params.hotel_id
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.channels'),
              text: this.$t('hotelsmanagehotelrooms.error.messages.information_complete')
            })

            this.loading = false
          }
        })
      },
      submit: function () {
        API({
          method: this.formAction,
          url: 'rooms' + (this.$route.params.room_id !== undefined ? '/' + this.$route.params.room_id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$refs.GaleryAdd.submit(result.data.object_id)
              this.$router.push('/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('hotelsmanagehotelrooms.error.messages.information_error')
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>

<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }

    .form-row {
        margin-bottom: 5px;
    }

    .container_errors {
        margin-top: 3px;
        border-radius: 2px;
    }

    .margin_icon_error {
        margin-left: 5px;
    }

    .container_channel_code {
        margin-bottom: 10px;
    }
</style>
