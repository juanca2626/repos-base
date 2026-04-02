<template>

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <label v-if="!loading">Agregar a:</label>
            </div>
            <div class="col-md-12">
                <button v-for="(category,category_index) in categories_plan" @click="checkCategory(category_index)"
                        class="btn btn-danger" type="submit" v-if="!loading"
                        style="float: left; margin-right: 5px;">
                    <font-awesome-icon v-if="category.check" :icon="['fas', 'check-square']"/>
                    <font-awesome-icon v-if="!category.check" :icon="['fas', 'square']"/>
                    {{category.name}}
                </button>
            </div>
        </div>

        <!-- div class="col-12" style="padding-left: 0; margin-bottom: 0">

            <button @click="back()" class="btn btn-success" type="button">
                <font-awesome-icon :icon="['fas', 'angle-left']"
                                   style="margin-left: 5px;" />
                Atrás
            </button>
        </div -->

        <div class="b-form-group form-group bottom0">
            <div class="form-row">

                <div>
                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="nacional" v-on:change="searchDestinations()" v-model="flight.type" value="0" />
                            <label class="form-check-label" for="nacional">Vuelo Doméstico</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="internacional" v-on:change="searchDestinations()" v-model="flight.type" value="1" />
                            <label class="form-check-label" for="internacional">Vuelo Internacional</label>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                        <div class="form-row">
                            <div class="form-group ml-3">
                                <label>Origen:</label>
                                <template>
                                    <v-select class="destino"
                                              :options="destinations_flights"
                                              :value="codciu"
                                              label="codciu" :filterable="false" @search="searchDestinations"
                                              placeholder="Origen"
                                              v-model="flight.origin" style="width: 250px">
                                        <template slot="option" slot-scope="option">
                                            <div class="d-center">
                                                {{ option.ciudad }} - {{ option.pais }}
                                            </div>
                                        </template>
                                        <template slot="selected-option" slot-scope="option">
                                            <div class="selected d-center">
                                                {{ option.ciudad }} - {{ option.pais }}
                                            </div>
                                        </template>
                                    </v-select>
                                </template>
                            </div>
                            <div class="form-group ml-3">
                                <label>Destino:</label>
                                <template>
                                    <v-select class="destino"
                                              :options="destinations_flights"
                                              :value="codciu"
                                              label="codciu" :filterable="false"
                                              @search="searchDestinations"
                                              placeholder="Destino"
                                              v-model="flight.destiny" style="width: 250px">
                                        <template slot="option" slot-scope="option">
                                            <div class="d-center">
                                                {{ option.ciudad }} - {{ option.pais }}
                                            </div>
                                        </template>
                                        <template slot="selected-option" slot-scope="option">
                                            <div class="selected d-center">
                                                {{ option.ciudad }} - {{ option.pais }}
                                            </div>
                                        </template>
                                    </v-select>
                                </template>
                            </div>
                            <div class="form-group ml-3">
                                <label>Fecha:</label>
                                <date-picker
                                    placeholder="Fecha"
                                    v-model="flight.date"
                                    :config="{ format: 'DD/MM/YYYY' }"
                                    autocomplete="off"
                                    class="form-control"
                                ></date-picker>
                            </div>

                            <div class="form-group ml-3">
                                <img src="/images/loading.svg" v-if="loading" width="40px"
                                     style="margin-top: 28px;" />
                                <button v-on:click="chooseFlight()"
                                        class="btn btn-success" type="submit" v-if="!loading"
                                        style="margin-top: 28px;">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                    Agregar vuelo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!--
                <div class="col-sm-4">
                    <label class="col-form-label">Destino</label>
                    <v-select :options="ubigeos"
                              @input="ubigeoChange"
                              :value="this.ubigeo_id"
                              v-model="ubigeoSelected"
                              :placeholder="this.$t('hotels.search.messages.hotel_ubigeo_search')"
                              autocomplete="true"></v-select>
                </div>

                <div class="col-2">
                    <div class="col-sm-2"><label class="col-form-label">Inicio</label></div>
                    <div class="input-group col-12">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            id="date_from"
                            autocomplete="off"
                            name="date_from" ref="datePickerFrom"
                            v-model="date_from" v-validate="'required'"
                        >
                        </date-picker>

                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"
                                           v-show="errors.has('date_from')" />
                        <span v-show="errors.has('date_from')">{{ errors.first('date_from') }}</span>
                    </div>
                </div>

                <div class="col-sm-4">
                    <img src="/images/loading.svg" class="right" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;" />
                    <button @click="search" class="btn btn-success right" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']" />
                        Buscar
                    </button>
                </div>
                -->

            </div>

        </div>

        <div class="b-form-group form-group">
            <div class="form-row">
                <b-tabs style="width: 100%;">
                    <b-tab :key="cat.id" :title="cat.category.translations[0].value"
                           ref="tabcategory" @click="searchByParts(catKey, cat.type_class_id)"
                           v-for="(cat, catKey) in categories">

                        <div class="col-12 sectionShare" v-show="!(cat.shareMode) && flightForShare.id>0">
                            <div class="col-12">
                                {{ flightForShare.date_in | formatDate }} |
                                <strong>
                                    <span>Vuelo [{{ flightForShare.code_flight }}] </span>
                                </strong>
                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']" />
                                    Cancelar
                                </button>

                                <button @click="shareHere(cat.id)" style="margin-right: 10px"
                                        class="btn btn-sm btn-info right" type="submit">
                                    <font-awesome-icon :icon="['fas', 'share-alt']" />
                                    Compartir aquí
                                </button>
                            </div>
                        </div>

                        <div class="col-12 sectionShare marginB20" v-show="cat.shareMode">
                            <div class="form-row">
                                <h5>
                                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                                    Diríjase a la pestaña de la(s) categoría(s) que desea compartir
                                </h5>
                            </div>

                            <div class="col-12">
                                {{ flightForShare.date_in | formatDate }} |
                                <strong>
                                    <span>Vuelo [{{ flightForShare.code_flight }}] | <template v-if="flightForShare.origin != '' && flightForShare.origin != null">Origen: {{ flight.origin }}</template>
                                        <template v-if="flightForShare.destiny != '' && flightForShare.destiny != null">
                                            <template v-if="flightForShare.origin != '' && flightForShare.origin != null"> - </template>
                                            Destino: {{ flightForShare.destiny }}</template></span>
                                </strong>

                                <button @click="cancelShare()" style="margin-right: 10px"
                                        class="btn btn-sm btn-danger right"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']" />
                                    Cancelar
                                </button>
                            </div>

                        </div>

                        <div class="col-12 sectionResult marginB20" v-show="!(cat.shareMode)">
                            <div class="form-row">
                                <h5>
                                    <font-awesome-icon :icon="['fas', 'hotel']" />
                                    Vuelos asignados:
                                </h5>
                            </div>
                            <div class="form-row" v-if="cat.currentFlights.length == 0">
                                <label>Ningún vuelo agregado en esta categoría</label>
                            </div>

                            <div :class="'col-12 row2_' + (fkey%2)" v-for="(flight, fkey) in cat.currentFlights"
                                 v-if="cat.currentFlights.length > 0">
                                <button @click="showModalDelete(flight.id)" :disabled="loading"
                                        class="btn btn-sm btn-danger"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'trash']" />
                                </button>
                                {{ flight.date_in | formatDate }} |
                                <strong>
                                    <span>Vuelo [{{ flight.code_flight }}] |
                                        <template v-if="flight.origin != '' && flight.origin != null">Origen: {{ flight.origin }}</template>
                                        <template v-if="flight.destiny != '' && flight.destiny != null">
                                            <template v-if="flight.origin != '' && flight.origin != null"> - </template>
                                            Destino: {{ flight.destiny }}</template>
                                    </span>
                                </strong>

                                <button @click="willShare(flight)" style="margin-right: 10px"
                                        class="btn btn-sm btn-info right" :disabled="loading"
                                        type="button">
                                    <font-awesome-icon :icon="['fas', 'share-alt']" />
                                </button>

                            </div>
                        </div>

                    </b-tab>
                </b-tabs>
            </div>
        </div>

        <div class="col-12" style="padding-left: 0;">
            <button @click="back()" class="btn btn-success" type="button">
                <font-awesome-icon :icon="['fas', 'angle-left']"
                                   style="margin-left: 5px;" />
                Atrás
            </button>
        </div>

        <b-modal :title="modalDeleteName" centered ref="my-modal-delete" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="removeFlight()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>

</template>

<script>
  import { API } from '../../../../api'
  import { Switch as cSwitch } from '@coreui/vue'
  import BTab from 'bootstrap-vue/es/components/tabs/tab'
  import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
  import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import datePicker from 'vue-bootstrap-datetimepicker'
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
  import Loading from 'vue-loading-overlay'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    components: {
      BModal,
      Loading,
      BTabs,
      BTab,
      cSwitch,
      VueBootstrapTypeahead,
      BFormCheckbox,
      BFormCheckboxGroup,
      BInputNumber,
      vSelect,
      datePicker
    },
    data: () => {
      return {
        loading: false,
        hotels: [],
        reEntryServices: [],
        reEntryServicesDates: [],
        checkboxs: [],
        categories: [],
        tmpubigeos: [],
        flightForShare: [],
        ubigeos: [],
        ubigeo: null,
        ubigeoSelected: [],
        plan_rate_id: '',
        ubigeo_id: '',
        date_from: '',
        date_to: '',
        paxSgl: '',
        paxDbl: '',
        paxTpl: '',
        reEntry: true,
        viewHiddenscheck: false,
        showReEntrys: true,
        doSearchReEntrys: true,
        package_service_id: null,
        modalDeleteName: '',
        datePickerFromOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang')
        },
        datePickerToOptions: {
          format: 'DD/MM/YYYY',
          useCurrent: false,
          locale: localStorage.getItem('lang')
        },
        tabCategoryActiveId:"",
        key_cat: 0,
        loading: false,
        flight: {
          type: 0,
          origin: '',
          destiny: '',
          date: '',
        },
        categories_plan: [],
        airlines: [],
        destinations_flights: [],
        codigo: '',
        codciu: '',
        flights: []
      }
    },
    mounted: function () {

      this.$i18n.locale = localStorage.getItem('lang')

      API.get('/package/plan_rates/' + this.plan_rate_id + '?lang=' + localStorage.getItem('lang'))
        .then((result) => {
        result.data.data.plan_rate_categories.forEach(oCategs => {
          if (oCategs.category.code != 'X') {
            this.categories.push(oCategs)
          }
        })
        this.categories.forEach(c => {
          c.flights = []
          c.currentFlights = []
          c.shareMode = false
        })
        this.setCurrentFlights()
      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('packagesmanagepackagetexts.error.messages.name'),
          text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
        })
      })

    },
    created () {
      this.$root.$on('plan_rates_categories', (payload) => {
        console.log(payload)
        this.categories_plan.push(payload)
      })

      this.plan_rate_id = this.$route.params.package_plan_rate_id
      this.category_id = this.$route.params.category_id
      this.getCategories()
      this.searchDestinations()
    },
    methods: {
      getCategories: function () {
        API({
          method: 'get',
          url: '/package/plan_rates/' + this.$route.params.package_plan_rate_id + '?lang=' + localStorage.getItem('lang')
        })
          .then((result) => {
            if (result.data.success === true) {
              let categories = result.data.data.plan_rate_categories
              let arrayCatego = []
              for (let i = 0; i < categories.length; i++) {
                arrayCatego.push({
                  'id': categories[i].id,
                  'check': true,
                  'name': categories[i].category.translations[0].value,
                })
              }
              this.categories_plan = arrayCatego
            }
          }).catch((e) => {
        })
      },
      searchDestinations: function (search, loading) {
        this.loading = true

        let _search = (search != undefined && search != '') ? search.toUpperCase() : ''

        API.get('flights/destinations/' + this.flight.type + '?query=' + _search)
          .then(response => {
            this.destinations_flights = response.data.data
            this.loading = false
          })
          .catch(error => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.package'),
              text: this.$t('global.error.messages.connection_error')
            })
            this.loading = false
          })
      },
      searchAirlines: function (search, loading) {
        this.loading = true

        let _search = (search != undefined && search != '') ? search.toUpperCase() : ''

        API.get('flights/airlines?query=' + _search)
          .then(response => {
            this.airlines = response.data
            this.loading = false
          })
          .catch(error => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('global.modules.package'),
              text: this.$t('global.error.messages.connection_error')
            })
            this.loading = false
          })
      },

      searchByParts(_cat, type_class_id){
        this.key_cat = _cat
        this.tabCategoryActiveId = type_class_id

        if (!(this.ubigeo)) {
          return
        }

        if (this.date_from == '') {
          return
        }
        this.search()
      },
      showContentHotel(hotel){
        this.loading = true

        this.categories.forEach( c => {
          c.hotels.forEach( h => {
            if( hotel.id == h.id ){
              h.viewContent = !(h.viewContent)
            } else {
              h.viewContent = false
            }
          })
        })

        this.loading = false
      },
      viewHiddens () {
        this.loading = true
        this.viewHiddenscheck = !(this.viewHiddenscheck)
        this.loading = false
      },
      hideModal () {
        this.$refs['my-modal-delete'].hide()
      },
      showModalDelete (package_service_id) {
        this.package_service_id = package_service_id
        this.modalDeleteName = 'Vuelo n°: ' + this.package_service_id
        this.$refs['my-modal-delete'].show()
      },
      removeFlight () {
        API.delete('/package/service/' + this.package_service_id).then((result) => {
          if (result.data.success === true) {
            this.setCurrentFlights()
            this.hideModal()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Error al eliminar ',
              text: 'Por favor inténtelo más tarde'
            })
          }
        }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.package'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      willShare (h) {
        this.flightForShare = h
        this.categories.forEach(c => {
          if (c.id == h.package_plan_rate_category_id) {
            c.shareMode = true
          } else {
            c.shareMode = false
          }
        })
      },
      cancelShare () {
        this.flightForShare = []
        this.categories.forEach(c => {
          c.shareMode = false
        })
      },
      shareHere (category_id) {
        this.loading = true
        let data = {
          category_id: category_id,
          package_service_id: this.flightForShare.id
        }
        API.post('/package/package_plan_rate_category/flight/share', data).then((result) => {
          console.log(result)
          if (!(result.data.success)) {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('packagesmanagepackagetexts.error.messages.name'),
              text: result.data.text
            })
            this.loading = false
          } else {
            this.setcurrentFlights()
          }

        }).catch(() => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('packagesmanagepackagetexts.error.messages.name'),
            text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
          })
        })
      },
      filterSimilar (h) {
        this.date_from = this.formatDate(h.date_in)
        this.date_to = this.formatDate(h.date_out)
        let _ubigeo = {
          code: h.hotel.country.iso + ',' + h.hotel.state.iso + ',' + h.hotel.city_id,
          label: h.hotel.country.translations[0].value + ',' + h.hotel.state.translations[0].value + ',' +
            h.hotel.city.translations[0].value
        }
        console.log(h.hotel)
        this.ubigeo = _ubigeo
        this.ubigeoSelected = _ubigeo
        // this.ubigeoChange()
        this.search()
      },
      setCurrentFlights () {
        this.loading = true
        let package_plan_rate_categories_ids = []
        this.categories.forEach(pCategs => {
          package_plan_rate_categories_ids.push(pCategs.id)
        })
        let data = {
          plan_rate_categories: package_plan_rate_categories_ids
        }
        API.post('/package/package_plan_rate_category/flight/searchByCategories', data).then((result) => {

          this.categories.forEach(c => {
            c.currentFlights = []
            result.data.data.forEach(h => {
              if (h.package_plan_rate_category_id == c.id) {
                c.currentFlights.push(h)
              }
            })
          })
          this.loading = false
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('packagesmanagepackagetexts.error.messages.name'),
            text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
          })
        })
      },
      toggleViewRates (rate) {
        this.loading = true
        rate.showAllRates = !(rate.showAllRates)
        this.loading = false
      },
      checkboxChecked: function (status) {
        return !!status
      },
      setDateFrom (e) {
        this.$refs.datePickerTo.dp.minDate(e.date)
      },
      getUnique (arr, comp) {
        //store the comparison  values in array
        const unique = arr.map(e => e[comp]).// store the keys of the unique objects
        map((e, i, final) => final.indexOf(e) === i && i)
        // eliminate the dead keys & return unique objects
          .filter((e) => arr[e]).map(e => arr[e])
        return unique
      },

      convertDate: function (_date, charFrom, charTo) {
        _date = _date.split(charFrom)
        _date = _date[2] + charTo + _date[1] + charTo + _date[0]
        return _date
      },

      search () {
        this.loading = true

        let type_classes = []
        if( this.tabCategoryActiveId == '' ){
          type_classes.push(this.categories[0].type_class_id)
        } else {
          type_classes.push(this.tabCategoryActiveId)
        }

        if (!(this.ubigeo)) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Paquetes - Cotizador',
            text: 'Debe seleccionar una ciudad de destino'
          })
          this.loading = false
          return
        }

        if (this.date_from == '') {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Paquetes - Cotizador',
            text: 'Debe ingresar una fecha de inicio'
          })
          this.loading = false
          return
        }

        if (this.date_to == '') {
          this.date_to = this.date_from
        }

        let data = {
          'type_classes': type_classes,
          'destiny': this.ubigeo,
          'date_from': this.convertDate(this.date_from, '/', '-'),
          'date_to': this.convertDate(this.date_to, '/', '-')
        }
        console.log(data)
        API({
          method: 'POST',
          url: window.origin + '/services/hotels/services',
          data: data
        })
          .then((result) => {
            this.hotels = result.data.data
            this.hotelsInTabs()
            this.editServiceRooms()
          }).catch((e) => {
          console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Paquetes - Cotizador',
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },

      hotelsInTabs: function () {
        this.categories.forEach(c => {
          c.hotels = []
          this.hotels.forEach(h => {
            h.viewContent = false
            if (h.typeclass_id == c.type_class_id) {

              h.countRates = 0
              // for show rates
              for (let r = 0; r < h.rooms.length; r++) {
                h.rooms[r].countCalendars = 0
                for (let r_p_r = 0; r_p_r < h.rooms[r].rates_plan_room.length; r_p_r++) {
                  if (typeof (h.rooms[r].rates_plan_room[r_p_r].showAllRates) === 'undefined') {
                    h.rooms[r].rates_plan_room[r_p_r].showAllRates = 0
                  }
                  h.countRates++
                  for (let r_p_r_c = 0; r_p_r_c < h.rooms[r].rates_plan_room[r_p_r].calendarys.length; r_p_r_c++) {
                    h.rooms[r].countCalendars++
                  }
                }
              }
              // for show rates

              c.hotels.push(h)
            }
          })
        })
      },

      useDay: function (reEntryS, rKey) {

        if (reEntryS.countUse) {
          this.showReEntrys = false

          this.reEntryServices.forEach((rEnServ, rEnKey) => {
            if (rEnKey != rKey) {
              this.reEntryServices[rEnKey].status = false
            }
          })
          this.showReEntrys = true

          let _date_in = this.convertDate(this.date_from, '/', '-')
          let _date_to = this.convertDate(this.date_to, '/', '-')
          let _city = this.ubigeo.code
          _city = _city.split(',')
          let _country_id = _city[0]
          let _state_id = _city[1]
          let data = {
            action: this.reEntryServices[rKey].status,
            plan_rate_categories: [],
            date_in: _date_in,
            date_out: _date_to,
            state_id: _state_id,
            country_id: _country_id,
            single: this.paxSgl,
            double: this.paxDbl,
            triple: this.paxTpl,
            rooms: reEntryS
          }
          this.categories.forEach(c => {
            data.plan_rate_categories.push(c.id)
          })

          API({
            method: 'POST',
            url: '/package/package_plan_rate_category/updateRates',
            data: data
          })
            .then((result) => {
              console.log(result)
              this.doSearchReEntrys = false
              this.search()
            }).catch((e) => {
            console.log(e)
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Paquetes - Cotizador',
              text: this.$t('global.error.messages.connection_error')
            })
          })

        } else {
          reEntryS.countUse = 0
        }
        reEntryS.countUse++
      },

      editServiceRooms: function () {

        this.checkboxs = []
        let date_in = this.convertDate(this.date_from, '/', '-')
        let _city = this.ubigeo.code
        _city = _city.split(',')
        let _country_id = _city[0]
        let _state_id = _city[1]
        let data = {
          plan_rate_categories: []
        }
        this.categories.forEach(c => {
          data.plan_rate_categories.push(c.id)
        })

        API({
          method: 'POST',
          url: '/package/package_plan_rate_category/hotel/searchByCategories',
          data: data
        })
          .then((result) => {

            // Despues separar los que sean fechas menores a al actual date_in, misma ciudad y agrupar por fechas en botones
            // Este boton que acciona, debe mandar un arreglo de los rooms involucrados y identificar el package_service nuevo id (o existente a guardar)
            let tmpServicesReEntry = []
            result.data.data.forEach(service => {
              if (service.date_in == date_in) {
                service.service_rooms.forEach(r => {
                  this.checkboxs[service.hotel.id + '_' + r.rate_plan_room_id] = true
                })
              }

              if (Date.parse(service.date_in) < Date.parse(date_in) &&
                service.hotel.country_id == _country_id &&
                service.hotel.state_id == _state_id) {
                tmpServicesReEntry.push(service)
              }
            })
            console.log(this.checkboxs)

            if (this.doSearchReEntrys) {

              this.reEntryServices = []
              this.reEntryServicesDates = []
              let tmpDates = []
              tmpServicesReEntry.forEach(tmpS => {
                if (!(this.reEntryServicesDates[tmpS.date_in])) {
                  tmpDates.push(tmpS.date_in)
                  this.reEntryServicesDates[tmpS.date_in] = 1
                }
              })

              let n = 0
              tmpDates.forEach(tmpDate => {
                this.reEntryServices[n] = {
                  date: tmpDate,
                  status: false,
                  rooms: []
                }
                tmpServicesReEntry.forEach(tmpS => {
                  if (tmpS.date_in == tmpDate) {
                    tmpS.service_rooms.forEach(room => {
                      this.reEntryServices[n].rooms.push(room)
                    })
                  }
                })
                n++
              })
              if (this.reEntryServices.length > 0) {
                this.showReEntrys = true
              }
            } else {
              this.doSearchReEntrys = true
            }

            this.loading = false

          }).catch((e) => {
          console.log(e)
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Paquetes - Cotizador',
            text: this.$t('global.error.messages.connection_error')
          })
        })

      },

      chooseFlight: function () {
        let categoriesInsert = []
        let check = false
        for (let i = 0; i < this.categories_plan.length; i++) {
          if (this.categories_plan[i].check) {
            check = true
            categoriesInsert.push({ 'id': this.categories_plan[i].id })
          }
        }
        if (check) {
          let data = {
            'package_plan_rate_id': this.plan_rate_id,
            'date': this.flight.date,
            'flight': this.flight,
            'categories': categoriesInsert
          }

          API({
            method: 'POST',
            url: 'package/package_plan_rate_category/flight/rate',
            data: data
          })
            .then((result) => {
              if (result.data.success === true) {
                // this.updateRates()
                // this.updateDestinations()
                this.flight.date = ''
                this.setCurrentFlights()
              } else {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: 'Paquetes - Cotizador',
                  text: this.$t('global.error.messages.information_error')
                })
              }
            })
        }
      },


      updateRates () {
        API.get(window.origin + '/prices?category_id=' + this.category_id).then((result) => {
          console.log('Tarifas actualizadas')
        }).catch((e) => {
          console.log(e)
        })
      },
      updateDestinations () {
        API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
          console.log('Destinos actualizados')
        }).catch((e) => {
          console.log(e)
        })
      },
      back: function () {
        this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
          this.plan_rate_id + '/category/' + this.category_id)
      },
      checkCategory: function (index) {
        this.categories_plan[index].check = !this.categories_plan[index].check
      },
      formatDate: function (_date) {
        if (_date == undefined) {
          // console.log('fecha no parseada: ' + _date)
          return
        }
        _date = _date.split('-')
        _date = _date[2] + '/' + _date[1] + '/' + _date[0]
        return _date
      }

    },
    filters: {
      formatDate: function (_date) {
        if (_date == undefined) {
          // console.log('fecha no parseada: ' + _date)
          return
        }
        _date = _date.split('-')
        _date = _date[2] + '/' + _date[1] + '/' + _date[0]
        return _date
      },

      formatPrice: function (price) {
        return parseFloat(price).toFixed(2)
      }
    }
  }
</script>


