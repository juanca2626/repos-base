<template>
  <div class="vld-parent">
    <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
    <div class="row">
      <div class="col-sm-12">
        <form @submit.prevent="validateBeforeSubmit">
          <b-tabs>
            <b-tab active>
              <template #title>
                <i class="fas fa-user"></i> Datos de cliente
              </template>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="code">{{ $t('clients.code') }}</label>
                  <div class="col-sm-5">
                    <input
                        :class="{'form-control':true, 'is-invalid': flag_code, 'is-valid' : (flag_code===false) }"
                        id="code" name="code" maxlength="6"
                        type="text" :disabled="block_code"
                        v-model="form.code" v-on:keyup="validate_code()">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="flag_code_message!==''"/>
                      <span>{{ flag_code_message }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
                  <div class="col-sm-5">
                    <input :class="{'form-control':true }"
                           id="name" name="name"
                           type="text"
                           v-model="form.name" v-validate="'required|max:255'">
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
                  <label class="col-sm-2 col-form-label" for="name">Razón Social</label>
                  <div class="col-sm-5">
                    <input :class="{'form-control':true }"
                           id="business_name" name="business_name"
                           type="text"
                           v-model="form.business_name" v-validate="'required|max:255'">
                  </div>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="email">{{ $t('users.mail') }}</label>
                  <div class="col-sm-5">
                    <input class="form-control input" id="email" name="email"
                           placeholder="Email del Usuario"
                           type="text" @change="removeAccents()"
                           v-model="form.email" v-validate="'required|email'">
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
                  <label class="col-sm-2 col-form-label">{{
                      $t('clients.logo')
                    }}</label>
                  <div class="col-sm-5">
                    <img class="logo-preview" style="width: auto;height: 70px;"
                         :src="form.logo"
                         alt="logo-image" @click="showLogoModal(form.logo)">
                  </div>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="classifications">Clasificación</label>
                  <div class="col-sm-5">
                    <v-select :options="classifications"
                              :value="form.classification"
                              autocomplete="true"
                              v-model="classificationSelected"
                    >
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                         v-show="errorClassifications">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                      <span>{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label">{{ $t('clients.market') }}</label>
                  <div class="col-sm-5">
                    <v-select :options="markets"
                              :value="form.market_id"
                              @input="marketChange"
                              autocomplete="true"
                              v-model="marketSelected"
                    >
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorMarket">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                      <span>{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="executive_code">KAM</label>
                  <div class="col-sm-5">
                    <input class="form-control input" id="executive_code" name="executive_code"
                           placeholder="Código del ejecutivo principal. Ejem: CLO"
                           type="text"
                           v-model="form.executive_code" v-validate="'required'">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('executive_code')"/>
                      <span
                          v-show="errors.has('executive_code')">{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- INICIO CAMPO BDM -->
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" title="Business Development Manager" for="bdm">BDM</label>
                  <div class="col-sm-5">

                    <v-select :options="bdms"
                              :value="form.bdm"
                              @input="bdmChange"
                              autocomplete="true"
                              v-model="bdmSelected"
                    >
                    </v-select>

                  </div>
                </div>
              </div>
              <!-- FIN CAMPO BDM -->
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="ruc">RUC</label>
                  <div class="col-sm-5">
                    <input class="form-control input" id="ruc" name="ruc" placeholder="Ruc"
                           type="text"
                           v-model="form.ruc" v-validate="'required|min:6|max:15|alpha_num'">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('ruc')"/>
                      <span v-show="errors.has('ruc')">{{ errors.first('ruc') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="address">Dirección</label>
                  <div class="col-sm-5">
                    <input class="form-control input" id="address" name="address" placeholder="Dirección"
                           type="text"
                           v-model="form.address" v-validate="'required'">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('address')"/>
                      <span v-show="errors.has('address')">{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label">{{ $t('clients.country') }}</label>
                  <div class="col-sm-5">
                    <v-select :options="countries"
                              :value="form.country_id"
                              @input="countryChange"
                              autocomplete="true"
                              v-model="countrySelected"
                    >
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                         v-show="errorCountry">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                      <span>{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label">Ciudad</label>
                  <div class="col-sm-5">
                    <v-select :options="ifx_cities"
                              :value="form.city"
                              @search="search_ifx_cities"
                              autocomplete="true"
                              v-model="ifx_city_selected"
                    >
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCity">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                      <span>{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label">{{ $t('clients.language') }}</label>
                  <div class="col-sm-5">
                    <v-select :options="languagesList"
                              :value="form.language_id"
                              @input="languageChange"
                              autocomplete="true"
                              v-model="languageSelected"
                    >
                    </v-select>
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                         v-show="errorLanguage">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                      <span>{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="postal_code">Código Postal</label>
                  <div class="col-sm-2">
                    <input class="form-control input" id="postal_code" name="postal_code"
                           placeholder="Código Postal"
                           type="text"
                           v-model="form.postal_code">
                  </div>
                  <label class="col-sm-1 col-form-label" for="phone">Teléfono</label>
                  <div class="col-sm-2">
                    <input class="form-control input" id="phone" name="phone" placeholder="Teléfono"
                           type="text"
                           v-model="form.phone">
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label" for="web">Web</label>
                  <div class="col-sm-2">
                    <input class="form-control input" id="web" name="web" placeholder="Web"
                           type="text"
                           v-model="form.web">
                  </div>
                  <label class="col-sm-1 col-form-label" for="anniversary">Aniversario</label>
                  <div class="col-sm-2">
                    <date-picker
                        :config="datePickerToOptions"
                        id="anniversary"
                        name="anniversary"
                        placeholder="DD/MM/YYYY"
                        ref="anniversary"
                        v-model="form.anniversary"
                    >
                    </date-picker>
                  </div>
                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row">

                  <label class="col-sm-2 col-form-label" for="general_markup">Markup General (%)</label>
                  <div class="col-sm-2">
                    <input class="form-control input" id="general_markup" name="general_markup"
                           type="number" step="1" min="0" max="100"
                           v-model="form.general_markup" v-validate="'required'">
                    <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                      <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                         style="margin-left: 5px;" v-show="errors.has('general_markup')"/>
                      <span
                          v-show="errors.has('general_markup')">{{ $t('clients.error.required') }}</span>
                    </div>
                  </div>

                  <label class="col-sm-2 col-form-label">{{ $t('clients.have_credit') }}</label>
                  <div class="col-sm-1" ref="have_credit">
                    <c-switch :value="true" class="mx-1" color="success"
                              v-model="form.have_credit"
                              variant="pill">
                    </c-switch>
                  </div>
                  <template v-if="form.have_credit === true">
                    <label class="col-sm-2 col-form-label" for="credit_line">{{
                        $t('clients.credit_line')
                      }}</label>
                    <div class="col-sm-3">
                      <input :class="{'form-control':true }"
                             id="credit_line" name="credit_line"
                             type="text"
                             v-model="form.credit_line"
                             v-validate.immediate="'decimal:2|required_if:have_credit,true'">
                      <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                           style="margin-left: 5px;"
                                           v-show="errors.has('credit_line')"/>
                        <span
                            v-show="errors.has('credit_line')">{{ errors.first('credit_line') }}</span>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
              <div class="b-form-group form-group">
                <div class="form-row">
                  <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
                  <div class="col-sm-2">
                    <c-switch :value="true" class="mx-1" color="success"
                              v-model="form.status"
                              variant="pill">
                    </c-switch>
                  </div>


                </div>
              </div>

              <div class="b-form-group form-group">
                <div class="form-row mt-3">
                    <!-- Switch commission_status -->
                    <label class="col-sm-2 col-form-label">Comision Status</label>
                    <div class="col-sm-1" ref="commission_status">
                    <c-switch :value="true" class="mx-1" color="primary"
                                v-model="form.commission_status"
                                variant="pill">
                    </c-switch>
                    </div>

                    <!-- Campo condicional commission -->
                    <template v-if="form.commission_status === true">
                    <label class="col-sm-2 col-form-label" for="commission">Comision (%)</label>
                    <div class="col-sm-2">
                        <input class="form-control input"
                            id="commission" name="commission"
                            type="number" step="0.01" min="0"
                            v-model="form.commission"
                            v-validate.immediate="'decimal:2|required_if:commission_status,true'">
                        <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                        <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                            style="margin-left: 5px;"
                                            v-show="errors.has('commission')"/>
                        <span v-show="errors.has('commission')">{{ errors.first('commission') }}</span>
                        </div>
                    </div>
                    </template>
                </div>
              </div>

            </b-tab>
            <b-tab>
              <template #title>
                <i class="fas fa-user-cog"></i> Configuración
              </template>
              <div class="row">
                <div class="col-md-12">
                  <h4>Países</h4>
                  <hr>
                </div>
                <div class="form-group col-sm-3" v-for="region in businessRegions" :key="region.id">
                    <label class="col-form-label">
                        {{ region.description }}
                        <i class="fas fa-info-circle clickable" :title="getCountriesTooltip(region)"></i>
                    </label>
                    <div>
                        <c-switch
                            v-model="selectedRegions[`R${region.id}`]"
                            class="mx-1"
                            color="success"
                            variant="pill"
                        >
                        </c-switch>
                    </div>
                </div>
                <div class="col-md-12">
                  <h4>Reservas</h4>
                  <hr>
                </div>
                <div class="form-group col-sm-3">
                  <label class="col-form-label" for="allow_direct_passenger">
                    Permitir creación de pasajero directo
                    <i class="fas fa-info-circle" data-toggle="tooltip"
                        title="Permite crear un pasajero directo para la facturacion, esta opcion se habilita en cotizaciones."></i>
                  </label>
                  <div>
                    <c-switch :value=true class="mx-1" color="success"
                        id="allow_direct_passenger"
                        v-model="form.allow_direct_passenger_creation"
                        variant="pill">
                    </c-switch>
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <label class="col-form-label" id="hotel_on_request">
                    Permitir hoteles en On Request
                    <i class="fas fa-info-circle" data-toggle="tooltip"
                        title="Permite buscar/reservar hoteles en On Request"></i>
                  </label>
                  <div>
                    <c-switch :value=true class="mx-1" color="success"
                        id="hotel_on_request"
                        v-model="form.hotel_allowed_on_request"
                        variant="pill">
                    </c-switch>
                  </div>
                </div>
                <div class="col-md-12" v-if="user_id == 1">
                  <h4>E-commerce</h4>
                  <hr>
                </div>
                <div class="form-group col-sm-2" v-if="user_id == 1">
                  <label class="col-form-label" id="flag_ecommerce">
                    E-commerce
                    <i class="fas fa-info-circle" data-toggle="tooltip"
                        title="Esta opcion es para que el cliente pueda tener acceso al modulo de e-coomerce ejm: Vista"></i>
                  </label>
                  <div>
                    <c-switch :value=true class="mx-1" color="success"
                        id="flag_ecommerce"
                        v-model="form.ecommerce"
                        variant="pill">
                    </c-switch>
                  </div>
                </div>

                <div class="col-md-12">
                  <h4>Notificaciones</h4>
                  <hr>
                </div>
                <div class="form-group col-sm-2">
                  <label class="col-form-label" id="flag_use_email">
                    Activar envío de correos
                    <i class="fas fa-info-circle" data-toggle="tooltip"
                        title="Activar envío de correos"></i>
                  </label>
                  <div>
                    <c-switch :value=true class="mx-1" color="success"
                        id="flag_use_email"
                        v-model="form.use_email"
                        variant="pill">
                    </c-switch>
                  </div>
                </div>
              </div>
            </b-tab>
          </b-tabs>

        </form>
      </div>
      <div class="col-sm-6 mt-3">
        <div slot="footer">
          <img src="/images/loading.svg" v-if="loading" width="40px"/>
          <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
            {{ $t('global.buttons.submit') }}
          </button>
          <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
            {{ $t('global.buttons.cancel') }}
          </button>
        </div>
      </div>
      <b-modal :title="modalTitle" centered ref="logoEditModal" size="md" :no-close-on-backdrop="true"
               :hide-header-close="true" :no-close-on-esc="true" :hide-footer="false">
        <div class="image-container" v-if="form.logo">
          <div class="loading-container" v-show="loadingLogo">
            <img alt="loading" height="51px" src="/images/loading.svg" class="mx-auto d-block"/>
          </div>
          <img class="client-img img-fluid mx-auto d-block"
               :src="form.logo"
               alt="logo"/>
        </div>

        <div slot="modal-footer">
          <input type="file" class="btn btn-success" @change="changeLogo()" ref="logoSelection"
                 id="logoSelection"
                 accept="image/jpeg, image/png" style="display: none"/>
          <button @click="openExplorer()" class="btn btn-primary mr-1" :disabled="loadingLogo">Cambiar imagen
          </button>
          <button @click="hideLogoModal()" class="btn btn-danger" :disabled="loadingLogo">Cancelar</button>
        </div>
      </b-modal>
    </div>
  </div>
</template>

<script>
import { API } from './../../api'
import { Switch as cSwitch } from '@coreui/vue'
import BTab from 'bootstrap-vue/es/components/tabs/tab'
import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import Multiselect from 'vue-multiselect'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import BModal from 'bootstrap-vue/es/components/modal/modal'
import datePicker from 'vue-bootstrap-datetimepicker'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import { BTooltip } from 'bootstrap-vue'

export default {
  components: {
    BTabs,
    BTab,
    cSwitch,
    VueBootstrapTypeahead,
    vSelect,
    Multiselect,
    BFormCheckbox,
    BFormCheckboxGroup,
    datePicker,
    Loading,
    BModal,
    BTooltip
  },
  name: 'tabs',
  data: () => {
    return {
      modalTitle: 'EDICIÓN DE LOGO',
      loadingLogo: false,
      images: [],
      languages: [],
      markets: [],
      marketSelected: [],
      market: null,
      marketSearch: '',
      bdms: [],
      bdmSelected: [],
      bdm: null,
      classifications: [
        {
          code: 'PQA',
          label: 'Pure Quest Adv.',
        },
        {
          code: 'RIN',
          label: 'Incentivos',
        },
        {
          code: 'RVI',
          label: 'Virtuoso',
        },
        {
          code: 'RGL',
          label: 'Gay & Lesbian',
        },
        {
          code: 'RST',
          label: 'Signature Travel',
        },
        {
          code: 'RCR',
          label: 'Cruceros',
        },
        {
          code: 'RMY',
          label: 'Mayorista',
        },
        {
          code: 'RMI',
          label: 'Minorista',
        },
        {
          code: 'ATTA',
          label: 'ATTA',
        },
        {
          code: 'SPR',
          label: 'Solo presupuesto',
        },
        {
          code: 'TVM',
          label: 'Traveller Made',
        },
        {
          code: 'TLE',
          label: 'Travel Leaders',
        },
        {
          code: 'PAXD',
          label: 'Pax Directo',
        },
        // {
        //     code : "01",
        //     label : "Persona"
        // },
        // {
        //     code : "02",
        //     label : "Empresa"
        // },
      ],
      classificationSelected: '',

      ifx_cities: [],
      ifx_city_selected: '',

      countries: [],
      countrySelected: '',
      country: null,
      countrySearch: '',

      languagesList: [],
      languageSelected: [],
      language: null,
      languageSearch: '',

      showError: false,
      currentLang: '1',
      invalidErrorClassification: false,
      invalidErrorMarket: false,
      invalidErrorCountry: false,
      invalidErrorCity: false,
      invalidErrorLanguage: false,
      countError: 0,
      loading: false,
      flagCredit: false,
      formAction: 'post',
      market_id: '',
      name: '',
      status: false,
      charged: false,
      zip_code: '',
      credit_line: null,
      id_image: '',
      url_image: '',
      form: {
        logo: 'https://res.cloudinary.com/litodti/image/upload/aurora/logos/masi.png',
        status: false,
        ecommerce: false,
        use_email: false,
        allow_direct_passenger_creation: false,
        hotel_allowed_on_request: true,
        email: '',
        market: null,
        bdm: null,
        channels: {},
        translDesc: {
          '1': {
            'id': '',
            'hotel_description': '',
          },
        },
        translAddr: {
          '1': {
            'id': '',
            'hotel_address': '',
          },
        },
      },
      block_code: false,
      user_id: null,
      datePickerToOptions: {
        format: 'DD/MM/YYYY',
        useCurrent: false,
        locale: localStorage.getItem('lang'),
      },
      flag_code: '',
      flag_code_message: '',
      businessRegions: [],
      selectedRegions: [],
      loadingRegions: false
    }
  },
  computed: {
    errorClassifications: function () {
      if (this.form.classification == '') {
        if (this.invalidErrorClassification == false) {
          this.invalidErrorClassification = true
          return false
        } else {
          return true
        }
      }
    },
    errorMarket: function () {
      if (this.form.market_id == '') {
        if (this.invalidErrormarket == false) {
          this.invalidErrorMarket = true
          return false
        } else {
          return true
        }
      }
    },
    errorCountry: function () {
      if (this.form.country_id == '') {
        if (this.invalidErrorCountry == false) {
          this.invalidErrorCountry = true
          return false
        } else {
          return true
        }
      }
    },
    errorCity: function () {
      if (this.form.country_id == '') {
        if (this.invalidErrorCity == false) {
          this.invalidErrorCity = true
          return false
        } else {
          return true
        }
      }
    },
    errorLanguage: function () {
      if (this.form.language_id == '') {
        if (this.invalidErrorLanguage == false) {
          this.invalidErrorLanguage = true
          return false
        } else {
          return true
        }
      }
    },

  },
  mounted: function () {
    // this.cleanFields()
    API.get('/me').then((response) => {
      this.user_id = response.data.id
    })
    API.get('/languages').then((result) => {
      this.languages = result.data.data
      this.currentLang = result.data.data[0].id

      let form = {}

      this.languages.forEach((language) => {
        this.languagesList.push({
          label: language.name,
          code: language.id,
        })
      })

      //markets
      API.get('/markets/selectbox?lang=' + localStorage.getItem('lang')).then((result) => {

        let mark = result.data.data
        mark.forEach((market) => {
          this.markets.push({
            label: market.text,
            code: market.value,
          })
        })

      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.error.messages.name'),
          text: this.$t('clients.error.messages.connection_error'),
        })
      })

      //bdms
      API.get('/users?search=&typeUser=&user_actives=&role=bdm').then((result) => {

        let bdms_ = result.data.data
        bdms_.forEach((bdm_) => {
          this.bdms.push({
            label: '[' + bdm_.code + '] - ' + bdm_.name,
            code: bdm_.id,
          })
        })

      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: '[!] Clientes',
          text: 'BDMs no encontrados',
        })
      })

      //countries
      API.get('/country/selectbox?lang=' + localStorage.getItem('lang')).then((result) => {

        let paises = result.data.data
        paises.forEach((country) => {
          this.countries.push({
            label: country.translations[0].value,
            code: country.translations[0].object_id,
            iso: country.iso,
            iso_ifx: country.iso_ifx,
          })
        })

      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.error.messages.name'),
          text: this.$t('clients.error.messages.connection_error'),
        })
      })

      if (this.$route.params.id !== undefined) {

        this.block_code = true
        this.loading = true

        API.get('/clients/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
        .then((result) => {
          this.loading = false

          form.client = result.data.data
          this.formAction = 'put'

          this.form.market_id = form.client.markets.id
          this.marketSelected.push({
            code: form.client.markets.id,
            label: form.client.markets.name,
          })

          if (form.client.bdm && form.client.bdm != null) {
            this.form.bdm = form.client.bdm.id
            this.bdmSelected.push({
              code: form.client.bdm.id,
              label: '[' + form.client.bdm.code + '] - ' + form.client.bdm.name,
            })
          }

          if (form.client.country_id != null) {
            this.form.country_id = form.client.country_id
            this.countrySelected = []
            this.countrySelected = {
              code: form.client.country_id,
              label: form.client.countries.translations[0].value,
              iso: form.client.countries.iso,
              iso_ifx: form.client.countries.iso_ifx,
            }
          }

          if (form.client.language_id != null) {
            this.form.language_id = form.client.language_id
            this.languageSelected.push({
              code: form.client.languages.id,
              label: form.client.languages.name,
            })
          }

          if (form.client.classification_code != null) {
            this.form.classification_code = form.client.classification_code
            this.form.classification_name = form.client.classification_name
            this.classificationSelected = {
              code: form.client.classification_code,
              label: form.client.classification_name,
            }
          }

          if (form.client.city_code != null) {
            this.form.city_code = form.client.city_code
            this.form.city_name = form.client.city_name
            this.ifx_city_selected = {
              code: form.client.city_code,
              label: form.client.city_name,
            }
          }

          this.form.code = form.client.code
          this.form.name = form.client.name
          this.form.business_name = form.client.business_name
          this.form.address = form.client.address
          this.form.ruc = form.client.ruc
          this.form.postal_code = form.client.postal_code
          this.form.web = form.client.web
          this.form.anniversary = this.formatDate(form.client.anniversary)
          this.form.phone = form.client.phone
          this.form.email = form.client.email
          this.form.use_email = (form.client.use_email === 'SI')
          this.form.hotel = form.client.hotel
          this.form.service = form.client.service
          this.form.status = !!form.client.status
          this.form.ecommerce = !!form.client.ecommerce
          this.form.allow_direct_passenger_creation = !!form.client.allow_direct_passenger_creation
          this.form.hotel_allowed_on_request = true
          if (form.client.configuration != null) {
            this.form.hotel_allowed_on_request = !!form.client.configuration.hotel_allowed_on_request
          }
          this.form.have_credit = !!form.client.have_credit
          this.form.credit_line = form.client.credit_line
          this.form.executive_code = form.client.executive_code
          this.form.general_markup = form.client.general_markup
          this.form.commission_status = !!form.client.commission_status
          this.form.commission = form.client.commission
          //image logo
          this.form.logo = form.client.logo

          form.client.business_regions.forEach((e) => {
            this.selectedRegions[("R" + e.id)] = true;
          });
        })
      } else {
        this.form.general_markup = 17
      }

    }).catch(() => {
      this.loading = false
      this.$notify({
        group: 'main',
        type: 'error',
        title: this.$t('clients.error.messages.name'),
        text: this.$t('clients.error.messages.connection_error'),
      })
    })

    this.loadBusinessRegions()
  },
  methods: {
    removeAccents () {
      this.form.email = this.form.email.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    },
    formatDate: function (_date) {
      if (_date == undefined) {
        // console.log('fecha no parseada: ' + _date)
        return
      }
      _date = _date.split('-')
      _date = _date[2] + '/' + _date[1] + '/' + _date[0]
      return _date
    },
    validate_code () {
      this.form.code = this.form.code.toUpperCase()
      if (this.form.code === '') {
        this.flag_code = true
        this.flag_code_message = 'El código es requerido'
      } else {
        if (this.form.code.length >= 3) {
          // Post
          API.get('clients/exist?code=' + this.form.code).then((result) => {
            if (!result.data.success) {
              let _errors = result.data.errors
              // Encuentra el último arreglo de errores no vacío
              var lastErrorArray = Object.values(_errors).find(arr => Array.isArray(arr) && arr.length > 0)
              // Obtén el último mensaje de error si se encontró un arreglo no vacío
              this.flag_code_message = lastErrorArray ? lastErrorArray[lastErrorArray.length - 1] : ''

            } else {
              this.flag_code = false
              this.flag_code_message = ''
            }
          }).catch(() => {
            console.log('error')
          })
        } else {
          this.flag_code = true
          this.flag_code_message = 'El código debe tener al menos 3 caracteres'
        }
      }
    },
    search_ifx_cities (search, loading) {
      loading(true)
      console.log(this.countrySelected)
      let country_code = (this.countrySelected !== '') ? this.countrySelected.iso_ifx : ''
      API.get('cities/informix?filter=' + search + '&country_code=' + country_code).then((result) => {
        loading(false)
        let cities_ = []
        result.data.data.forEach((c) => {
          cities_.push({
            label: c.descri,
            code: c.codigo,
          })
        })
        this.ifx_cities = cities_
      }).catch(() => {
        loading(false)
      })
    },
    changeLogo () {
      let file = this.$refs['logoSelection'].files[0]

      if (file.size > 6 * 1000 * 1000) {
        //máximo 6MB
        this.$notify.error('El tamaño del archivo supera el máximo permitido (1MB).', {
          position: 'top-right',
        })
      } else {
        this.loadingLogo = true
        var formData = new FormData()

        formData.append('imagefile', file)
        formData.set('client', this.form.code)
        formData.set('imagename', this.form.code)
        formData.set('imagefolder', 'aurora/logos')
        API.post('/upload/clientlogo', formData).then(response => {
          this.form.logo = response.data.logo
          this.$forceUpdate()
          this.$notify.success('Se ha actualizado correctamente el logo del cliente.', {
            position: 'top-right',
          })

        }).catch(() => {
          this.$notify.error('Ocurrieron erroes al intentar cargar el archivo.', {
            position: 'top-right',
          })
        }).finally(() => {
          this.loadingLogo = false
        })
      }
    },
    openExplorer () {
      this.$refs['logoSelection'].click()
    },
    hideLogoModal () {
      this.$refs['logoEditModal'].hide()
    },
    showLogoModal (logo) {

      this.$refs['logoEditModal'].show()
    },
    marketChange: function (value) {
      this.market = value
      if (this.market != null) {
        this.form.market_id = this.market.code
      } else {
        this.form.market_id = ''
      }

    },
    bdmChange: function (value) {
      this.bdm = value
      if (this.bdm != null) {
        this.form.bdm = this.bdm.code
      } else {
        this.form.bdm = ''
      }

    },
    countryChange: function (value) {
      this.country = value
      if (this.country != null) {
        this.form.country_id = this.country.code
      } else {
        this.form.country_id = ''
      }

    },
    languageChange: function (value) {
      this.language = value
      if (this.language != null) {
        this.form.language_id = this.language.code
      } else {
        this.form.language_id = ''
      }

    },
    CancelForm () {
      this.id_image = ''
      this.$router.push({ path: '/clients/list' })
    },
    validateBeforeSubmit () {
      if ((this.flag_code && this.formAction == 'post')) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: 'Código Incorrecto',
        })
        return false
      }
      if ((this.form.market_id == null && this.formAction == 'post')) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: this.$t('clients.error.messages.client_market_complete'),
        })
        return false
      }

      if ((this.form.country_id == null && this.formAction == 'post')) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: this.$t('clients.error.messages.client_country_complete'),
        })
        return false
      }

      if ((this.ifx_city_selected === '' || this.ifx_city_selected === null) && this.formAction == 'post') {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: 'Por favor elegir un ciudad',
        })
        return false
      }

      if ((this.form.language_id == null && this.formAction == 'post')) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: this.$t('clients.error.messages.client_language_complete'),
        })
        return false
      }

      if ((this.form.have_credit !== false && (this.form.credit_line == undefined || this.form.credit_line == ''))) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: this.$t('clients.error.messages.credit_line'),
        })
        return false
      }
      if ((this.classificationSelected === '')) {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.title'),
          text: 'Indicar la calsificación del Cliente',
        })
        return false
      }

      const ids = Object.entries(this.selectedRegions).filter(([key, value]) => value == true).map(([key]) => parseInt(key.replace(/^R/, '')));
      console.log(ids);
      if(ids.length === 0){
        this.$notify({
          group: 'main',
          type: 'error',
          title: "Region",
          text: 'Seleccionar al menos una region',
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
            title: this.$t('clients.hotel_name'),
            text: this.$t('clients.error.messages.information_complete'),
          })
          this.loading = false
        }
      })
    },
    submit () {
      //market
      if ((this.market == null) && this.formAction == 'put' && this.form.market_id != '') {

      } else {
        this.form.market_id = this.market.code
      }

      //status
      if (this.formAction == 'put') {
        this.form.status = (this.form.status == false ? 0 : 1)
      }
      this.form.have_client = (this.form.have_client == false ? 0 : 1)
      this.form.use_email = (this.form.use_email == false ? 'NO' : 'SI')

      this.form.classification_code = (this.classificationSelected !== '') ? this.classificationSelected.code : ''
      this.form.classification_name = (this.classificationSelected !== '') ? this.classificationSelected.label : ''

      this.form.city_code = (this.ifx_city_selected !== '') ? this.ifx_city_selected.code : ''
      this.form.city_name = (this.ifx_city_selected !== '') ? this.ifx_city_selected.label : ''

    //   this.form.selected_regions = this.selectedRegions;

      this.loading = true

      API({
        method: this.formAction,
        url: 'clients' + (this.$route.params.id !== undefined ? '/' + this.$route.params.id : ''),
        data: this.form,
      }).then(async (result) => {

        console.log(result);

        if (result.data.success === false) {
          let errorText = 'Ocurrió un error al guardar'
          if (result.data.response) {
            errorText = (typeof result.data.response === 'string') ? result.data.response : JSON.stringify(result.data.response)
          } else if (result.data.errors) {
            errorText = result.data.errors.join(' | ')
          }

          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clients.hotel_name'),
            text: errorText,
          })

          this.loading = false
        } else {


          if (this.charged == true) {

            this.images.forEach((image, key) => {
              this.position = 0
              let formImagen = {
                image: image,
                type: 'client',
                object_id: result.data.object_id,
                url: '',
                slug: 'client_logo',
                position: this.position,
                state: true,
              }

              API({
                method: 'put',
                url: 'client/gallery/logo/',
                data: formImagen,
              }).then((result) => {
                if (result.data.success === false) {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.name'),
                    text: this.$t('clients.error.messages.gallery_incorrect'),
                  })
                  this.loading = false
                }
              })
            })
          } else {
            this.charged = false
            this.id_image = ''
          }

          const ids = Object.entries(this.selectedRegions).filter(([key, value]) => value == true).map(([key]) => parseInt(key.replace(/^R/, '')));

          await this.saveClientRegions(result.data.object_id);

          console.log('/clients/' + result.data.object_id + '/manage_client/regions/'+ (ids[0] || 1));

          this.$router.push({ path: '/clients/' + result.data.object_id + '/manage_client/regions/'+ (ids[0] || 1) })
        }
      }).catch((e) => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.hotel_name'),
          text: 'Error de conexión o fallo inesperado',
        })
        this.loading = false
      })
    },
    loadBusinessRegions() {
        this.loadingRegions = true;
        API.get('/business_region')
        .then(async (response) => {
            this.businessRegions = response.data.data;
            if (this.$route.params.id) {
                this.businessRegions.forEach((e) => {
                    const findId = this.selectedRegions.findIndex(item => item.id === e.id);
                    if(findId === -1){
                        this.selectedRegions[`R${e.id}`] = false;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading regions:', error);
            this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las regiones'
            });
        })
        .finally(() => {
            this.loadingRegions = false;
        });
    },
    // loadClientRegions() {
    //     // this.loading = true;
    //     API.get(`/clients/${this.$route.params.id}/business_region`)
    //     .then(response => {
    //         const ids = response.data.data.map(e => e.id);
    //         this.businessRegions.forEach((e) => {
    //             this.selectedRegions[e.id] = ids.includes(e.id);
    //         });

    //         console.log(this.selectedRegions);
    //     })
    //     .catch(error => {
    //         console.error('Error loading client regions:', error);
    //     });
    // },
    saveClientRegions(clientId) {
        const ids = Object.entries(this.selectedRegions).filter(([key, value]) => value == true).map(([key]) => parseInt(key.replace(/^R/, '')));
        if (ids.length === 0) return;

        API.post(`/clients/${clientId}/business_region`, {
            regions: ids
        }).then(() => {
            this.$notify({
                group: 'main',
                type: 'success',
                title: 'Éxito',
                text: 'Regiones guardadas correctamente'
            });
        }).catch(error => {
            console.error('Error saving regions:', error);
            this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error',
                text: 'No se pudieron guardar las regiones'
            });
        });
    },
    getCountriesTooltip(region) {
        if (!region.countries || region.countries.length === 0) {
            return 'Esta región no tiene países asociados';
        }

        return region.countries.map(country => {
            return this.getCountryName(country) + ` (${country.iso})`;
        }).join(', ');
    },
    getCountryName(country) {
        if (country.translations && country.translations.length > 0) {
        const lang = 1;
        const translation = country.translations.find(t => t.language_id == lang);
            return translation ? translation.value : country.iso;
        }
        return country.iso;
    }
  },
}
</script>
<style lang="stylus">
</style>
<!-- <style src="vue-multiselect/dist/vue-multiselect.min.css"></style> -->

