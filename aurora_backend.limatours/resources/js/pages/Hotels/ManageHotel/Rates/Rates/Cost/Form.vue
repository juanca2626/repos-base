<template>
  <div class="container">
    <div class="row">
      <h2>{{ $t('hotelsmanagehotelratesratescost.rate') }}: {{ form.name }}</h2>
    </div>
    <div class="row cost-table-container">
      <b-tabs style="width: 100%;">
        <b-tab>
          <template #title>
            <i class="fas fa-file-invoice"></i> Datos de Tarifa
          </template>
          <div class="container">
            <div class="vld-parent">
              <loading :active.sync="loading_rate" :can-cancel="false" color="#BD0D12"></loading>
              <table class="table mt-4">
                <thead>
                <tr>
                  <th class="small-title">
                    <span class="">Channel</span>
                  </th>
                  <th class="small-title" v-if="isHyperguestSelected">
                    <span class="">Tipo Channel</span>
                  </th>
                  <th class="small-title">
                    <span class="">Tipo de Tarifa</span>
                  </th>
                  <th class="small-title">
                    <span class="">Allotment</span>
                  </th>
                  <th class="small-title">
                    <span class="">Tarifario</span>
                  </th>
                  <th class="small-title">
                    <span class="">Impuestos</span>
                  </th>
                  <th class="small-title">
                    <span class="">Servicios</span>
                  </th>
                  <th class="small-title">
                    <span class="">Multipropiedad</span>
                  </th>
                  <th class="small-title">
                    <span class="">Promoción</span>
                  </th>
                  <th class="small-title">
                    <span class="">Margen de Protección (%)</span>
                  </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td class="py-2">
                    <b-form-select
                        :options="channels"
                        ref="channels"
                        v-model="form.channel_id"
                        v-validate="{ required: true }">
                    </b-form-select>
                  </td>
                  <td class="py-2" v-if="isHyperguestSelected">
                    <b-form-select
                        :options="hyperguestTypes"
                        v-model="form.type_channel"
                        v-validate="{ required: true }"
                        :required="isHyperguestSelected">
                    </b-form-select>
                  </td>
                  <td class="py-2">
                    <b-form-select
                        :options="rateTypes"
                        :state="validateState('ratesTypes')"
                        ref="ratesTypes"
                        v-model="form.type"
                        v-validate="{ required: true }">
                    </b-form-select>
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.allotment"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.rate"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.taxes"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.services"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.timeshares"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.promotions"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                  <td class="py-2">
                    <c-switch
                        :uncheckedValue="false"
                        :value="true"
                        class="mx-1"
                        color="primary"
                        v-model="form.flag_process_markup"
                        variant="pill"
                        :disabled="blocked_hyperguest"
                    />
                  </td>
                </tr>
                </tbody>
              </table>
              <!--div class="row table-options mt-4">
                  <div class="col-2 small-title">
                      <span class="">Tipo de Tarifa</span>
                  </div>
                  <div class="col-1 small-title">
                      <span class="">Allotment</span>
                  </div>
                  <div class="col-1 small-title">
                      <span class="">Tarifario</span>
                  </div>
                  <div class="col-2 small-title">
                      <span class="">Impuestos</span>
                  </div>
                  <div class="col-2 small-title">
                      <span class="">Servicios</span>
                  </div>
                  <div class="col-2 small-title">
                      <span class="">Multipropiedad</span>
                  </div>
                  <div class="col-2 small-title">
                      <span class="">Promoción</span>
                  </div>
                  <div class="col-2">
                      <b-form-select
                          :options="rateTypes"
                          :state="validateState('ratesTypes')"
                          ref="ratesTypes"
                          v-model="form.type"
                          v-validate="{ required: true }">
                      </b-form-select>
                  </div>
                  <div class="col-1" style="padding: 0.75rem;">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.allotment"
                          variant="pill"
                      />
                  </div>
                  <div class="col-1" style="padding: 0.75rem;">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.rate"
                          variant="pill"
                      />
                  </div>
                  <div class="col-2">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.taxes"
                          variant="pill"
                      />
                  </div>
                  <div class="col-2">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.services"
                          variant="pill"
                      />
                  </div>
                  <div class="col-2">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.timeshares"
                          variant="pill"
                      />
                  </div>
                  <div class="col-2">
                      <c-switch
                          :uncheckedValue="false"
                          :value="true"
                          class="mx-1"
                          color="primary"
                          v-model="form.promotions"
                          variant="pill"
                      />
                  </div>
              </div -->
              <div class="row">
                <div class="b-form-group form-group col-4">
                  <div class="form-row">
                    <label class="col-form-label" for="name">{{ $t('global.name') }}</label>
                    <input
                        :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                        data-vv-validate-on="none"
                        id="name"
                        name="name"
                        type="text"
                        v-model="form.name"
                        v-validate="'required'">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('name')"/>
                      <span v-show="errors.has('name')">Campo requerido</span>
                    </div>
                  </div>
                </div>
                <div class="b-form-group form-group col-2">
                  <div class="form-row">
                    <label class="col-form-label" for="code">
                      {{ $t('hotelsmanagehotelratesratescost.code') }}
                    </label>
                    <input
                        :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                        data-vv-validate-on="none"
                        id="code"
                        name="code"
                        type="text"
                        v-validate="'required'"
                        v-model="form.code">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('code')"/>
                      <span v-show="errors.has('name')">Campo requerido</span>
                    </div>
                  </div>
                </div>
                <div class="b-form-group form-group col-6">
                  <div class="form-row">
                    <label class="col-12 col-form-label" for="meals">
                      {{ $t('hotelsmanagehotelratesratescost.meal_name') }}
                    </label>
                    <v-select
                        class="col-12"
                        :options="meals"
                        :resetOnOptionsChange="true"
                        :selectOnTab="true"
                        autocomplete="true"
                        id="meals"
                        ref="mealTypeahead"
                        label="text"
                        v-model="form.meal">
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                         v-show="customErrors.meals">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;"/>
                      <span>{{ $t('hotelsmanagehotelratesratescost.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <b-tabs class="mb-5">
                <b-tab :key="language.id" :title="language.name" ref="tabLanguage"
                       @click="set_language(language.id)"
                       v-for="language in languages">
                  <div class="row">
                    <div class="col-sm-12 b-form-group form-group">
                      <div class="form-row">
                        <div class="col-sm-6">
                          <label for="commercial_name">
                            {{ $t('hotelsmanagehotelratesratescost.commercial_name') }}
                          </label>
                          <input
                              :class="{'form-control':true, 'is-valid':errors.has('commercial_name'), 'is-invalid':errors.has('commercial_name') }"
                              data-vv-validate-on="none"
                              id="commercial_name"
                              name="commercial_name"
                              type="text"
                              v-model="form.translations[currentLang].commercial_name"
                              v-validate="'required'">
                          <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                               style="margin-left: 5px;"
                                               v-show="errors.has('commercial_name')"/>
                            <span v-show="errors.has('commercial_name')">
                                                            Campo requerido
                                                        </span>
                          </div>
                        </div>
                        <div class="col-6">
                          <div class="form-group">
                            <label for="no_show">No Show</label>
                            <input type="text" id="no_show"
                                   v-model="form.translations_no_show[currentLang].no_show"
                                   class="form-control"/>
                          </div>
                        </div>
                        <div class="col-12">
                          <label for="day_use">Day Use</label>
                          <textarea name="" id="day_use" cols="15" rows="5"
                                    v-model="form.translations_day_use[currentLang].day_use"
                                    class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                          <div class="form-group">
                            <label for="notes">Notas</label>
                            <textarea name="" id="notes" cols="15" rows="5" @keyup="check_send_to_mkt_enabled = true"
                                      v-model="form.translations_notes[currentLang].notes"
                                      class="form-control"></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </b-tab>
              </b-tabs>
              <!-- <div class="row mt-4">
                {{ form }}
              </div> -->
              <div class="row mt-4" v-if="form.promotions">
                <div class="col-12">
                  <hr/>
                </div>
                <div class="col-12">
                  <h2>Promociones: (Booking Window)</h2>
                </div>
                <div class="col-12">
                  <div :key="index" class="row mt-2"
                       v-for="(promotion, index) in form.promotionsData">
                    <div class="col-1 my-auto">
                      Desde:
                    </div>
                    <div class="col-3">
                      <date-picker
                          :config="datePickerOptions"
                          :id="'promotion-'+index+'-from'"
                          :name="'promotion-'+index+'-from'"
                          placeholder="inicio: DD/MM/YYYY"
                          :ref="'promotion-'+index+'-from'"
                          v-model="form.promotionsData[index].from"
                      >
                      </date-picker>
                    </div>
                    <div class="col-1 my-auto">
                      Hasta:
                    </div>
                    <div class="col-3">
                      <date-picker
                          :config="datePickerOptions"
                          :id="'promotion-'+index+'-to'"
                          :name="'promotion-'+index+'-to'"
                          placeholder="inicio: DD/MM/YYYY"
                          :ref="'promotion-'+index+'-to'"
                          v-model="form.promotionsData[index].to"
                      >
                      </date-picker>
                    </div>
                    <div class="col-1">
                      <button @click="addPromotion" class="btn btn-primary">
                        +
                      </button>
                    </div>
                    <div class="col-1" v-if="index > 0">
                      <button @click="removePromotion(index)" class="btn btn-primary">
                        -
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-4" v-if="form.promotions">
                <div class="col-12">
                  <hr/>
                </div>
              </div>
              <div class="row text-right mt-3">
                <div class="col-12">

                    <label class="left" v-if="check_send_to_mkt_enabled">
                        <input class="" :disabled="!check_send_to_mkt_enabled" type="checkbox" v-model="check_send_to_mkt">
                        Notificar a Marketing que revise las notas generales del hotel
                    </label>

                  <img src="/images/loading.svg" v-if="loading" width="40px"/>
                  <button @click="submit(true)" class="btn btn-success" type="button" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                  </button>
                  <router-link to="../" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                      {{ $t('global.buttons.cancel') }}
                    </button>
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </b-tab>
        <b-tab v-if="this.$route.name != 'RatesRatesCostForm' && ratePlanID != undefined" :disabled="blocked_hyperguest">
          <template #title>
            <i class="fas fa-file-invoice"></i> Plan Tarifario
          </template>
          <div class="container">
            <!--                        <b-navbar type="dark" variant="light">-->
            <!--                            <b-navbar-nav>-->
            <!--                                <b-nav-item href="#">-->
            <!--                                    <button type="button" class="btn btn-success" @click="showFormNewRatePlan"-->
            <!--                                            :disabled="newOrEditRatePlan">-->
            <!--                                        Nuevo Plan Tarifario-->
            <!--                                    </button>-->
            <!--                                </b-nav-item>-->
            <!--                            </b-navbar-nav>-->
            <!--                        </b-navbar>-->
            <LayoutStep2
                :newOrEditRatePlan="newOrEditRatePlan"
                :formAction="formAction"
                :hotelID="hotelID"
                :options="optionsStepTwo"
                :ratePlanID="ratePlanID"
                v-show="ratePlanID"
            ></LayoutStep2>
          </div>
        </b-tab>
        <b-tab v-if="this.$route.name != 'RatesRatesCostForm' && ratePlanID != undefined"
               @click="getAssociationsRate">
          <template #title>
            <i class="fas fa-exchange-alt"></i> Asociar Tarifa (Opcional)
          </template>
          <div class="row">
            <div class="col-12">
              <div class="alert alert-warning">
                <i class="fas fa-bullhorn"></i> Tener en cuenta al realizar esta operación: Este proceso
                puede tomarse su tiempo en actualizar los registros, esto se debe por la gran cantidad
                de información, se le estará notificando a su correo cuando el proceso termine.
              </div>
            </div>
            <div class="col-12">
              <div class="vld-parent">
                <loading :active.sync="loading_associate_rate" :can-cancel="false"
                         color="#BD0D12"></loading>
                <h3>Asociar (Opcional): </h3>
                <div class="row">
                  <div class="b-form-group form-group col-12">
                    <label for="regions" class="font-weight-bold">
                      Regiones
                    </label>
                    <b-form-checkbox size="sm" v-model="selected_all_regions"
                                     @input="changeSelectedAllRegions"
                                     class="text-primary">
                      Agregar todos:
                    </b-form-checkbox>
                    <v-select multiple
                              placeholder="Regiones"
                              :options="regions"
                              id="regions"
                              autocomplete="true"
                              data-vv-as="region"
                              data-vv-name="region"
                              name="region"
                              label="text"
                              @input="changeRegion()"
                              v-model="form_association.regions">
                    </v-select>
                  </div>
                  <div class="col-md-12">
                    <p class="font-weight-bold">Seleccione el país ó el cliente</p>
                  </div>
                  <div class="b-form-group form-group col-12">
                    <label for="countries" class="font-weight-bold">Países</label>
                    <b-form-radio-group
                        v-model="form_association.except_country"
                        :options="options_country"
                        @input="changeExceptCountry"
                        class="mb-3"
                        value-field="item"
                        text-field="name"
                    ></b-form-radio-group>
                    <v-select multiple
                              placeholder="Países"
                              :options="countries"
                              id="countries"
                              autocomplete="true"
                              data-vv-as="country"
                              data-vv-name="country"
                              name="country"
                              label="name"
                              :disabled="disabled_assoc"
                              v-model="form_association.countries">
                    </v-select>
                  </div>
                  <div class="b-form-group form-group col-12">
                    <label for="clients" class="font-weight-bold">Clientes</label>
                    <b-form-radio-group
                        v-model="form_association.except_client"
                        :options="options_clients"
                        class="mb-3"
                        value-field="item"
                        text-field="name"
                    ></b-form-radio-group>
                    <v-select multiple
                              placeholder="Clientes"
                              :options="clients"
                              id="clients"
                              autocomplete="true"
                              data-vv-as="client"
                              data-vv-name="client"
                              @search="get_clients"
                              name="client"
                              label="label"
                              :disabled="disabled_assoc"
                              v-model="form_association.clients">
                    </v-select>
                  </div>
                  <div class="b-form-group form-group col-3">
                    <button class="btn btn-success" type="button" @click="associateRate"
                            :disabled="loading_associate_rate">
                      <i class="fa fa-save" v-if="!loading_associate_rate"></i>
                      <i class="fa fa-spin fa-spinner" v-else></i> Guardar
                    </button>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </b-tab>
      </b-tabs>
    </div>
    <div class="row mt-4">
      <div class="col-12">
        <hr/>
      </div>
      <div class="col-12 text-right">
        <button @click="returnHome()" class="btn btn-secondary" type="button" v-if="step === 2">
          {{ $t('hotelsmanagehotelratesratescost.buttons.goback') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { API } from './../../../../../../api'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
import datePicker from 'vue-bootstrap-datetimepicker'
import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
import Progress from 'bootstrap-vue/src/components/progress/progress'
import CSwitch from '@coreui/vue/src/components/Switch/Switch'
import TableClient from './../../../../../../components/TableClient'
import moment from 'moment'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import LayoutStep2 from './Step2/Layout'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import Multiselect from 'vue-multiselect'

export default {
  components: {
    'table-client': TableClient,
    VueBootstrapTypeahead,
    'b-form-group': BFormGroup,
    datePicker,
    BFormSelect,
    'b-progress': Progress,
    CSwitch,
    vSelect,
    Loading,
    LayoutStep2,
    Multiselect,
  },
  data: () => {
    return {
      countries: [],
      regions: [],
      clients: [],
      hotelID: '',
      ratePlanID: '',
      optionsStepTwo: {
        ratePlanName: '',
        promotions: false,
      },
      loading: false,
      selected_all_regions: false,
      languages: [],
      showError: false,
      currentLang: 1,
      invalidError: false,
      countError: 0,
      policies: [],
      policy: null,
      policySearch: null,
      policyDays: {
        all: false,
        1: false,
        2: false,
        3: false,
        4: false,
        5: false,
        6: false,
        7: false,
      },
      meals: [],
      meal: null,
      mealSearch: null,
      datePickerFromOptions: {
        format: 'DD/MM/YYYY',
        useCurrent: false,
        locale: localStorage.getItem('lang'),
      },
      datePickerToOptions: {
        format: 'DD/MM/YYYY',
        useCurrent: false,
        locale: localStorage.getItem('lang'),
      },
      rooms: [],
      rateTypes: [],
      channels: [],
      formAction: 'post',
      step: 1,
      currentRooms: [],
      defaultRooms: [],
      table: {
        columns: ['room', 'period', 'policy', 'adult', 'child', 'infant', 'extra'],
        options: {
          headings: {
            room: 'Habitacion',
            period: 'Periodo',
            policy: 'Política',
            adult: 'Adulto US$',
            kid: 'Niño US$',
            infant: 'Infante US$',
            extra: 'Extra US$',
          },
          sortable: [],
          filterable: false,
        },
      },
      customErrors: {
        meals: false,
        policy: false,
      },
      currentRoomIndex: null,
      save: {
        counter: 0,
        max: 100,
      },
      currentTime: 0,
      historyData: [],
      historySearch: null,
      historySet: null,
      datePickerOptions: {
        format: 'DD/MM/YYYY',
        useCurrent: false,
        locale: localStorage.getItem('lang'),
      },
      form_association: {
        regions: [],
        countries: [],
        clients: [],
        except_country: false,
        except_client: false,
      },
      form: {
        name: '',
        code: '',
        meal_id: '',
        type: '',
        translations: {
          '1': {
            'id': '',
            'commercial_name': '',
          },
          '2': {
            'id': '',
            'commercial_name': '',
          },
          '3': {
            'id': '',
            'commercial_name': '',
          },
        },
        translations_no_show: {
          '1': {
            'id': '',
            'no_show': '100% + impuestos de ley',
          },
          '2': {
            'id': '',
            'no_show': '100% fee + 18% Tax',
          },
          '3': {
            'id': '',
            'no_show': '100% + taxa de 18% de imposto',
          },
        },
        translations_notes: {
          '1': {
            'id': '',
            'notes': '',
          },
          '2': {
            'id': '',
            'notes': '',
          },
          '3': {
            'id': '',
            'notes': '',
          },
        },
        translations_day_use: {
          '1': {
            'id': '',
            'day_use': '',
          },
          '2': {
            'id': '',
            'day_use': '',
          },
          '3': {
            'id': '',
            'day_use': '',
          },
        },
        allotment: false,
        rate: false,
        taxes: false,
        services: false,
        timeshares: false,
        promotions: false,
        channel_id: 1,
        promotionsData: [
          {
            from: moment().format('DD/MM/YYYY'),
            to: moment().format('DD/MM/YYYY'),
          }],
        flag_process_markup: false,
        no_show: '',
        notas: '',
        day_use: '',
        price_dynamic: false,
        type_channel: null
      },
      formTwo: {
        data: [],
        policy_id: '',
      },
      language_choose: '',
      loading_associate_rate: false,
      loading_rate: false,
      disabled_assoc: false,
      year: '',
      options_country: [
        { item: false, name: 'Incluir los siguientes paises' },
        { item: true, name: 'No incluir los siguientes paises' },
      ],
      options_clients: [
        { item: false, name: 'Incluir los siguientes clientes' },
        { item: true, name: 'No incluir los siguientes clientes' },
      ],
      newOrEditRatePlan: true,
        check_send_to_mkt: false,
        check_send_to_mkt_enabled: false,
        blocked_hyperguest: false,
        hyperguestTypes: [
            { value: 1 , text: 'PUSH' },
            { value: 2 , text: 'PULL' },
        ]
    }
  },
  computed: {
    years () {
      let previousYear = moment().subtract(2, 'years').year()
      let currentYear = moment().add(5, 'years').year()
      let years = []

      do {
        years.push({ value: previousYear, text: previousYear })
        previousYear++
      } while (currentYear > previousYear)

      return years
    },
    isHyperguestSelected() {
        const selectedChannel = this.channels.find(channel =>
            channel.value === this.form.channel_id
        );
        return selectedChannel && selectedChannel.text === 'HYPERGUEST';
    }
  },
  created () {
    let currentDate = new Date();
    this.year = currentDate.getFullYear();
    this.hotelID = parseInt(this.$route.params.hotel_id);
    this.ratePlanID = (this.$route.params.rate_id !== undefined) ? parseInt(this.$route.params.rate_id) : undefined;
    this.fetchHotelChannels();
  },
  mounted: async function () {
    try {
        await this.loadAdditionalData();
        await this.loadInitialData();
        await this.get_regions();
        await this.getChannels();
    } catch (error) {
        this.handleError(error);
    }
  },
  methods: {
    async loadInitialData() {
        try {
        // Cargar lenguajes
        const languagesResult = await API.get('/languages/');
        this.languages = languagesResult.data.data;
        this.currentLang = this.languages[0].id;

        // Inicializar formulario
        this.initializeFormTranslations();

        // Cargar datos de tarifa si existe rate_id
        if (this.$route.params.rate_id !== undefined) {
            await this.loadRateData();
        }

        // this.form = { ...this.form, ...this.createFormStructure() };
        this.currentTime = moment().unix();

        } catch (error) {
        this.showNotificationError(
            this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
            this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        );
        throw error;
        }
    },

    async loadAdditionalData() {
        try {
        // Cargar comidas
        const mealsResult = await API.get('/meals/selectBox?lang=' + localStorage.getItem('lang'));
        this.meals = mealsResult.data.data;

        // Cargar tipos de tarifa
        const rateTypesResult = await API.get('/ratesplanstypes/selectBox?lang=' + localStorage.getItem('lang'));
        this.rateTypes = rateTypesResult.data.data;

        } catch (error) {
        this.showNotificationError(
            this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
            this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        );
        throw error;
        }
    },

    initializeFormTranslations() {
        this.languages.forEach((value) => {
        // Configuración de translations_no_show
        this.form.translations_no_show[value.id] = {
            id: '',
            no_show: this.getNoShowText(value.id),
        };

        // Configuración de translations_notes
        this.form.translations_notes[value.id] = {
            id: '',
            notes: '',
        };

        // Configuración de translations_day_use
        this.form.translations_day_use[value.id] = {
            id: '',
            day_use: this.getDayUseText(value.id),
        };

        // Configuración de translations
        this.form.translations[value.id] = {
            id: '',
            commercial_name: '',
        };
        });
    },

    getNoShowText(languageId) {
        const texts = {
            1: '100% + impuestos de ley',
            2: '100% fee + 18% Tax',
            3: '100% + taxa de 18% de imposto'
        };
        return texts[languageId] || '';
    },

    getDayUseText(languageId) {
        const texts = {
        1: 'Por favor contacte a su especialista para mas detalles',
        2: 'Please contact your specialist for more details',
        3: 'Entre em contato com seu especialista para obter mais detalhes'
        };
        return texts[languageId] || '';
    },

    createFormStructure() {
        let formStructure = {
            translations: {},
            translations_no_show: {},
            translations_notes: {},
            translations_day_use: {},
        };

        this.languages.forEach((value) => {
        formStructure.translations[value.id] = { id: '', commercial_name: '' };
        formStructure.translations_no_show[value.id] = {
            id: '',
            no_show: this.getNoShowText(value.id)
        };
        formStructure.translations_notes[value.id] = { id: '', notes: '' };
        formStructure.translations_day_use[value.id] = {
            id: '',
            day_use: this.getDayUseText(value.id)
        };
        });

        return formStructure;
    },

    async loadRateData() {
        try {
        this.ratePlanID = parseInt(this.$route.params.rate_id);
        const rateResult = await API.get(`rates/cost/${this.$route.params.hotel_id}/${this.$route.params.rate_id}/?lang=${localStorage.getItem('lang')}`);

        this.formAction = 'put';
        const data = rateResult.data.data;

        this.updateFormWithRateData(data);
        this.handlePromotionsData(data);
        this.step = 1;

        } catch (error) {
        this.showNotificationError(
            this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
            this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        );
        throw error;
        }
    },

    updateFormWithRateData(data) {

        const initializeTranslations = (translationsData, defaultField, defaultValue = '') => {
            const translations = {};
            this.languages.forEach(lang => {
                translations[lang.id] = {
                    id: translationsData[lang.id]?.id || '',
                    [defaultField]: translationsData[lang.id]?.[defaultField] || defaultValue
                };
            });
            return translations;
        };

        this.form = {
            name: data.name,
            code: data.code,
            meal_id: data.meal.id,
            meal: data.meal,
            type: data.type,
            translations: initializeTranslations(data.translations, 'commercial_name', ''),
            translations_no_show: initializeTranslations(data.translations_no_show, 'no_show', this.getNoShowText(data.language_id)),
            translations_notes: initializeTranslations(data.translations_notes, 'notes', ''),
            translations_day_use: initializeTranslations(data.translations_day_use, 'day_use', this.getDayUseText(data.language_id)),
            rate: data.rate,
            allotment: data.allotment,
            taxes: data.taxes,
            services: data.services,
            timeshares: data.timeshares,
            promotions: data.promotions,
            channel_id: data.channel_id,
            promotionsData: [],
            flag_process_markup: (data.flag_process_markup != null) ? data.flag_process_markup : false,
            price_dynamic: (data.price_dynamic == 1) ? true : false,
            type_channel: data.type_channel
        };

        // this.handleTranslationData(data, 'translations', 'commercial_name');
        // this.handleTranslationData(data, 'translations_notes', 'notes');
        // this.handleTranslationData(data, 'translations_no_show', 'no_show');
        // this.handleTranslationData(data, 'translations_day_use', 'day_use');
    },

    handleTranslationData(data, property, defaultField) {
        if (data[property].length === 0) {
            this.languages.forEach((value) => {
                this.form[property][value.id] = { id: '', [defaultField]: '' };
            });
        } else {
            this.languages.forEach((value) => {
                if (!this.form[property][value.id]) {
                    this.form[property][value.id] = {
                        id: this.form[property].id,
                        [defaultField]: this.form[property][defaultField]
                    };
                }
            });
        }
    },

    handlePromotionsData(data) {
        if (data.promotionsData.length === 0) {
        this.form.promotionsData = [{
            from: moment().format('DD/MM/YYYY'),
            to: moment().format('DD/MM/YYYY'),
        }];
        } else {
        data.promotionsData.forEach((promotion, index) => {
            const temp = {
            from: moment(promotion.promotion_from).format('DD/MM/YYYY'),
            to: moment(promotion.promotion_to).format('DD/MM/YYYY'),
            };
            this.$set(this.form.promotionsData, index, temp);
        });
        }
    },

    handleError(error) {
        console.error('Error en mounted:', error);
        this.showNotificationError(
        this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
        this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
        );
    },

    showNotificationError(title, text) {
        this.$notify({
        group: 'main',
        type: 'error',
        title: title,
        text: text,
        });
    },

    getChannels: async function () {
        try {
            const result = await API({
            method: 'GET',
            url: 'channels/selectHotelBox',
            });
            this.channels = result.data.data;
        } catch (e) {
            console.log(e);
        }
    },
    get_clients (search, loading) {
      loading(true)
      let countries = []
      if (this.form_association.countries.length > 0) {
        this.form_association.countries.forEach(function (item) {
          countries.push(item.id)
        })
      }
      API.get('clients/selectBox/by/name?query=' + search, {
        params: {
          'countries': countries,
          'except_country': this.form_association.except_country,
        },
      }).then((result) => {
        loading(false)
        let clients_ = []
        result.data.data.forEach((c) => {
          clients_.push({
            label: c.name,
            code: c.id,
          })
        })
        this.clients = clients_
      }).catch(() => {
        loading(false)
      })
    },
    get_regions: async function () {
        try {
            const result = await API({
            method: 'GET',
            url: 'markets/selectbox?lang=' + localStorage.getItem('lang'),
            });
            this.regions = result.data.data;
            this.loading = false;
        } catch (e) {
            console.log(e);
        }
    },
    get_countries () {
      API({
        method: 'GET',
        url: 'country/selectbox?lang=' + localStorage.getItem('lang'),
      }).then((result) => {
        result.data.data.forEach((c) => {
          c.name = '[' + c.iso + '] - ' + c.translations[0].value
        })
        this.countries = result.data.data
        this.loading = false
      }).catch((e) => {
        console.log(e)
      })
    },
    validateState (ref) {

    },
    submit (shallContinue) {
      this.$validator.validateAll().then(isValid => {
        if (this.isHyperguestSelected && !this.form.type_channel) {
            this.$notify({
                group: 'main',
                type: 'error',
                title: 'Validación',
                text: 'Debe seleccionar un tipo para Hyperguest',
            });
            this.loading_rate = false;
            return;
        }

        if (isValid) {
          this.loading_rate = true
            this.form.send_to_mkt =  (this.check_send_to_mkt) ? 1 : 0

            const formData = {
                ...this.form,
                type_channel: this.isHyperguestSelected ? this.form.type_channel : null
            };

            API({
                method: this.formAction,
                url: 'rates/cost/' + this.$route.params.hotel_id +
                    (this.$route.params.rate_id !== undefined ? '/' + this.$route.params.rate_id : ''),
                data: formData,
            }).then((result) => {
                this.loading_rate = false
                if (this.formAction == 'post') {
                this.ratePlanID = parseInt(result.data.rate_plan)

                if (shallContinue) {
                    this.formAction = 'put'
                    this.optionsStepTwo.promotions = this.form.promotions
                    this.$router.push({ path: 'edit/' + this.ratePlanID })

                }
                }

                if (this.formAction === 'put') {
                location.reload()
                this.optionsStepTwo = {
                    promotions: this.form.promotions,
                }
                }
            }).catch((e) => {
                this.loading_rate = false
                console.log(e)
            })
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$i18n.t('global.modules.ratescost'),
            text: this.$i18n.t('hotelsmanagehotelratesratescost.error.messages.information_complete'),
          })
          this.loading_rate = false
        }
      })
    },
    returnHome () {
      this.$router.push('/hotels/' + this.hotelID + '/manage_hotel/rates/rates/cost')
    },
    addPromotion () {
      this.form.promotionsData.push({
        from: moment().format('DD/MM/YYYY'),
        to: moment().format('DD/MM/YYYY'),
      })
    },
    removePromotion (index) {
      let promotionsData = this.form.promotionsData
      promotionsData.splice(index, 1)
      this.form.promotionsData = promotionsData
    },
    set_language (currentLang) {
      this.currentLang = currentLang
    },
    associateRate: function () {
      if (this.form_association.regions.length === 0 &&
          (this.form_association.countries.length > 0 || this.form_association.clients.length > 0)) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$i18n.t('global.modules.ratescost'),
          text: 'Debe seleccionar al menos una region',
        })
      } else {
        this.loading_associate_rate = true
        API({
          method: 'POST',
          url: 'rates/cost/' + this.$route.params.hotel_id + '/' + this.$route.params.rate_id + '/associate_rate',
          data: {
            'regions': this.form_association.regions,
            'countries': this.form_association.countries,
            'except_country': this.form_association.except_country,
            'clients': this.form_association.clients,
            'except_client': this.form_association.except_client,
          },
        }).then((result) => {
          if (result.data.success) {
            this.$notify({
              group: 'main',
              type: 'success',
              title: this.$i18n.t('global.modules.ratescost'),
              text: result.data.message,
            })
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$i18n.t('global.modules.ratescost'),
              text: result.data.message,
            })
          }
          this.loading_associate_rate = false
        }).catch((e) => {
          this.loading_associate_rate = false
          console.log(e)
        })
      }

      // } else {

      // }

    },
    getCountryByRegion: function () {
      let regions = []
      this.form_association.regions.forEach(function (item) {
        regions.push(item.value)
      })
      if (this.form_association.regions.length > 0) {
        this.loading_associate_rate = true
        API({
          method: 'POST',
          url: 'markets/countries',
          data: {
            regions: regions,
          },
        }).then((result) => {
          this.countries = []
          this.loading_associate_rate = false
          result.data.countries.forEach((c) => {
            c.name = '[' + c.iso + '] - ' + c.name
          })
          this.countries = result.data.countries
          this.removeCountryByRegion()

        }).catch((e) => {
          this.loading_associate_rate = false
          console.log(e)
        })
      } else {
        this.form_association.countries = []
      }

    },
    changeRegion: function () {
      if (this.form_association.regions.length === 0) {
        this.form_association.except_country = false
        this.form_association.except_client = false
        this.form_association.countries = []
        this.form_association.clients = []
        this.disabled_assoc = true
      } else {
        this.disabled_assoc = false
      }
      this.getCountryByRegion()
    },
    removeCountryByRegion: function () {
      let index_remove = []
      for (let c = 0; c < this.form_association.countries.length; c++) {
        let check_country = false
        for (let t = 0; t < this.form_association.length; t++) {
          if (this.form_association.countries[c].id == this.countries[t].id) {
            check_country = true
          }
        }
        if (check_country) {
          index_remove.push(c)
        }
      }
      this.form_association.countries = this.form_association.countries.filter((val, index) => {
        return index_remove.includes(index)
      })
    },
    changeExceptCountry: function () {
      this.form_association.except_country = !!this.form_association.except_country
      this.form_association.clients = []
    },
    changeSelectedAllRegions: function () {
      this.selected_all_regions = !!this.selected_all_regions
      if (this.selected_all_regions) {
        this.form_association.regions = []
        let _regions = []
        this.regions.forEach(function (item) {
          _regions.push(item)
        })
        this.form_association.regions = _regions
        this.getCountryByRegion()
        this.disabled_assoc = false
      } else {
        this.form_association.regions = []
        this.form_association.countries = []
        this.form_association.clients = []
        this.form_association.except_country = false
        this.form_association.except_client = false
        this.disabled_assoc = true
      }
    },
    getAssociationsRate: function () {
      this.loading_associate_rate = true
      this.countries = []
      API.get('rates/cost/' + this.$route.params.rate_id + '/associate_rate?lang=' + localStorage.getItem('lang')).
          then((result) => {
            let data = result.data.data
            this.form_association.regions = data.association_regions
            this.form_association.countries = data.association_countries
            this.form_association.clients = data.association_clients
            this.form_association.except_country = data.except_country
            this.form_association.except_client = data.except_client
            if (data.association_regions.length > 0) {
              let regions = []
              data.association_regions.forEach(function (item) {
                regions.push(item.value)
              })
              API({
                method: 'POST',
                url: 'markets/countries',
                data: {
                  regions: regions,
                },
              }).then((result) => {
                result.data.countries.forEach((c) => {
                  c.name = '[' + c.iso + '] - ' + c.name
                })
                this.countries = result.data.countries
              }).catch((e) => {
                console.log(e)
              })
            }
            this.loading_associate_rate = false
          }).
          catch(() => {
            this.loading_associate_rate = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
              text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error'),
            })
          })
    },
    showFormNewRatePlan: function () {
      this.newOrEditRatePlan = true
    },
    async fetchHotelChannels() {
        try {
            const hotel_id = this.$route.params.hotel_id;
            const response = await API.get(`hotels/${hotel_id}/channels?lang=${localStorage.getItem('lang')}`);
            if (response.data.data) {
                this.channels = response.data.data;
                const hyperguestChannel = this.channels.find(channel =>
                    channel.name === "HYPERGUEST" &&
                    channel.pivot.state === 1 &&
                    channel.pivot.type === "2"
                );

                // this.blocked_hyperguest = !!hyperguestChannel;
            }
        } catch (error) {
            console.error("Error fetching hotel channels:", error);
            this.blocked_hyperguest = false;
        }
    },
  },
}
</script>

<style lang="stylus">
.with-border
  border 1px solid #e4e7ea

.table-days
  margin-bottom 0

  th
    text-align center
    background-color #e4e7ea

  td
    text-align center
    padding 5px 0

    .success
      color #28a745

    .danger
      color #dc3545

.rooms-table-headers
  text-align center
  background-color #e4e7ea

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button
  -webkit-appearance none
  margin 0

input[type="number"]
  -moz-appearance textfield

.small-title
  background #2F353A
  text-align center
  color #FFFFFF
  font-weight 700
  font-size 14px
  padding 0.75rem

.table-options
  .col-2
    padding 0.75rem
    text-align center

.rooms-table
  input[type=number]
    padding-right 0 !important
    background none !important

    .disabled-tab{
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }

    .disabled-tab span {
        color: #6c757d !important;
    }
</style>

