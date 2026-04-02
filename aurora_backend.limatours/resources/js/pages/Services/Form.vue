<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row">
            <div class="col-md-12">
                <form>
                    <p class="divider-text">
                        <strong>{{ $t('services.service_origin') }}</strong>
                    </p>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>{{ $t('services.country') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="countries"
                                          :value="form.origin.country_id"
                                          @input="countryOriginChange"
                                          autocomplete="true"
                                          data-vv-as="country"
                                          data-vv-name="country_origin"
                                          name="country_origin"
                                          v-model="countryOriginSelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('country_origin')">
                                <span>{{ errors.first('country_origin') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.state') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="states"
                                          :value="form.origin.state_id"
                                          @input="stateOriginChange"
                                          autocomplete="true"
                                          data-vv-as="state"
                                          data-vv-name="state_origin"
                                          v-model="stateOriginSelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('state_origin')">
                                <span>{{ errors.first('state_origin') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.city') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="cities"
                                          :value="form.origin.city_id"
                                          @input="cityOriginChange"
                                          autocomplete="true"
                                          data-vv-as="city"
                                          data-vv-name="city_origin"
                                          v-model="cityOriginSelected">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.zone') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="zones"
                                          :value="form.origin.zone_id"
                                          @input="zoneOriginChange"
                                          autocomplete="true"
                                          data-vv-as="zone"
                                          data-vv-name="zone_origin"
                                          v-model="zoneOriginSelected">
                                </v-select>
                            </div>
                        </div>
                    </div>
                    <p class="divider-text">
                        <strong>{{ $t('services.service_destination') }}</strong>
                    </p>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>{{ $t('services.country') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="countries"
                                          :value="form.destiny.country_id"
                                          @input="countryDestinyChange"
                                          autocomplete="true"
                                          data-vv-as="country"
                                          data-vv-name="country_destiny"
                                          v-model="countryDestinySelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('country_destiny')">
                                <span>{{ errors.first('country_destiny') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.state') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="states"
                                          :value="form.destiny.state_id"
                                          @input="stateDestinyChange"
                                          autocomplete="true"
                                          data-vv-as="state"
                                          data-vv-name="state_destiny"
                                          v-model="stateDestinySelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select" v-show="errors.has('state_destiny')">
                                <span>{{ errors.first('state_destiny') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.city') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="cities"
                                          :value="form.destiny.city_id"
                                          @input="cityDestinyChange"
                                          autocomplete="true"
                                          data-vv-as="city"
                                          data-vv-name="city_destiny"
                                          v-model="cityDestinySelected">
                                </v-select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>{{ $t('services.zone') }}</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="zones"
                                          :value="form.destiny.zone_id"
                                          @input="zoneDestinyChange"
                                          autocomplete="true"
                                          data-vv-as="zone"
                                          data-vv-name="zone_destiny"
                                          v-model="zoneDestinySelected">
                                </v-select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row mt-4">
                        <div class="col-sm-3" v-if="!hasClient">
                            <div class="form-row">
                                <label>{{ $t('services.currency') }}</label>
                                <div class="col-sm-12 p-0">
                                    <v-select :options="currencies"
                                              :value="form.currency_id"
                                              @input="currencyChange"
                                              autocomplete="true"
                                              data-vv-as="currency"
                                              data-vv-name="currency"
                                              v-model="currencySelected"
                                              v-validate="'required'">
                                    </v-select>
                                    <span class="invalid-feedback-select" v-show="errors.has('currency')">
                                    <span>{{ errors.first('currency') }}</span>
                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2" v-if="!hasClient">
                            <label>{{ $t('services.reservation_from') }}</label>
                            <div class="col-sm-12 p-0">
                                <input class="form-control"
                                       data-vv-as="reserve"
                                       data-vv-name="reserve"
                                       type="number"
                                       v-model.number="form.qty_reserve"
                                       v-validate="'required'">
                                <span class="invalid-feedback" v-show="errors.has('reserve')">
                                <span>{{ errors.first('reserve') }}</span>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-2" v-if="!hasClient">
                            <label>.</label>
                            <div class="col-sm-12 p-0">
                                <v-select :options="unitDurationsReserve"
                                          :value="form.unitDurationReserve_id"
                                          @input="unitDurationReserveChange"
                                          autocomplete="true"
                                          data-vv-as="unit duration"
                                          data-vv-name="unitDurationReserve"
                                          v-model="unitDurationsReserveSelected"
                                          v-validate="'required'">
                                </v-select>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('unitDurationReserve')">
                                            <span>{{ errors.first('unitDurationReserve') }}</span>
                                        </span>
                            </div>
                        </div>
                        <div class="col-sm-2" v-if="!hasClient">
                            <label>{{ $t('services.service_code_aurora') }}</label>
                            <div class="col-sm-12 p-0">
                                <input class="form-control"
                                       data-vv-as="codigo aurora"
                                       data-vv-name="aurora_code"
                                       type="text"
                                       :disabled="true"
                                       v-model="form.aurora_code"
                                       v-validate="'required'">
                                <span class="invalid-feedback" v-show="errors.has('aurora_code')">
                                    <span>{{ errors.first('aurora_code') }}</span></span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label>{{ $t('services.service_status') }}</label>
                            <div class="col-sm-12 p-0">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="form.status"
                                          variant="pill">
                                </c-switch>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row mt-4" v-if="!hasClient">
                        <div class="col-sm-2">
                            <label>{{ $t('services.reservation_from_client') }}</label>
                            <div class="col-sm-12 p-0">
                                <input class="form-control"
                                       type="number"
                                       v-model.number="form.qty_reserve_client">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label>Tag</label>
                            <div class="col-sm-12 p-0">
                                <select name="" id="" v-model="form.tag_service_id" class="form-control">
                                    <option value="">Seleccione Tag</option>
                                    <option :value="tag_service.id" v-for="tag_service in tag_services">{{
                                            tag_service.translations[0].value
                                        }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2" v-if="!hasClient">
                            <label>Tipo</label>
                            <div class="col-sm-12 p-0">
                                <select name="type" id="type" v-model="form.type" class="form-control"
                                        v-bind:class="[form.type === 'service' ? 'service' : 'supplement']">
                                    <option value="service" class="service">Servicio</option>
                                    <option value="supplement" class="supplement">Suplemento</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2" v-if="!hasClient">
                            <label>Exclusivo</label>
                            <div class="col-sm-12 p-0">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="form.exclusive"
                                          variant="pill">
                                </c-switch>
                            </div>
                        </div>

                        <div class="col-sm-4" v-if="(form.exclusive) && !hasClient">
                            <label>Cliente Exclusivo</label>
                            <v-select :options="exclusive_clients"
                                      :value="form.exclusive_client_id"
                                      @input="client_exclusive_change"
                                      @search="search_clients"
                                      autocomplete="true"
                                      v-model="exclusive_client_selected">
                            </v-select>
                        </div>

                        <div class="col-sm-4" v-if="this.$route.params.id != null">
                            <label>Idiomas de guia</label>
                            <div class="col-sm-12 p-0">
                                <div class="form-check form-check-inline" v-for="(language,index) in languagesGuide">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           @change="validateOperationLanguageGuide(language)"
                                           :id="'check_lang'+index"
                                           v-model="language.checked">
                                    <label class="form-check-label" :for="'check_lang'+index"> {{
                                            language.language_name
                                        }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <label>Compensación</label>
                            <div class="col-sm-12 p-0">
                                <c-switch :value="true" class="mx-1" color="success"
                                          v-model="form.compensation"
                                          variant="pill">
                                </c-switch>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 p-0">
                        <b-tabs>
                            <b-tab :title="this.$i18n.t('services.tab_detail')">
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="name_service">{{ $t('services.name_service') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="name"
                                                   data-vv-name="name"
                                                   id="name_service"
                                                   name="name_service"
                                                   type="text"
                                                   v-model="form.name"
                                                   v-validate="'required'">
                                            <span class="invalid-feedback" v-show="errors.has('name')">
                                            <span>{{ errors.first('name') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="name_service">{{ $t('services.latitude_name') }}</label>
                                        <input class="form-control"
                                               data-vv-as="latitude" data-vv-name="latitude"
                                               id="latitude"
                                               max="90"
                                               min="-90"
                                               name="latitude"
                                               type="number"
                                               v-model.number="form.latitude"
                                               v-validate="'required'">
                                        <span class="invalid-feedback" v-show="errors.has('latitude')">
                                        <span>{{ errors.first('latitude') }}</span>
                                    </span>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="name_service">{{ $t('services.longitude_name') }}</label>
                                        <input class="form-control"
                                               data-vv-as="longitude"
                                               data-vv-name="longitude"
                                               id="longitude"
                                               max="180"
                                               min="-180"
                                               name="longitude"
                                               type="number"
                                               v-model.number="form.longitude"
                                               v-validate="'required'">
                                        <span class="invalid-feedback" v-show="errors.has('longitude')">
                                            <span>{{ errors.first('longitude') }}</span>
                                        </span>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group row">
                                            <div class="col-sm-12" v-if="!hasClient">
                                                <label>
                                                    {{ $t('services.include_accommodation') }}
                                                    <i class="fa fa-info-circle icon-ex" aria-hidden="true"
                                                       v-b-tooltip.hover
                                                       title="Solo valido para los servicios de tipo Paquete y que duren más de 1 día."></i>
                                                </label>
                                                <div class="col-sm-12 p-0">
                                                    <b-form-checkbox v-model="form.include_accommodation"
                                                                     name="include_accommodation" size="lg"
                                                                     :disabled="form.unitDuration_id != 2 || form.category_id != 2 || form.duration <= 1"
                                                                     switch>
                                                    </b-form-checkbox>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <b-tabs>
                                    <b-tab :key="language.id" :title="language.name" ref="tabLanguage"
                                           @click="set_language(language)"
                                           v-for="language in languages">
                                        <div class="form-row container_channel_code" v-show="!hasClient">
                                            <label for="names">Nombre</label>
                                            <div class="col-sm-12 p-0">
                                                <input class="form-control input-internal"
                                                       id="names"
                                                       name="names"
                                                       type="text"
                                                       v-model="form.names[language.id].name">
                                            </div>
                                        </div>
                                        <div class="form-row container_channel_code" v-show="!hasClient">
                                            <label for="descripction">{{ $t('services.description') }}</label>
                                            <div class="col-sm-12 p-0">
                                            <textarea class="form-control input-internal"
                                                      id="descripction" name="descripction"
                                                      rows="3"
                                                      v-model="form.description[language.id].name"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-row container_channel_code" v-show="!hasClient">
                                            <label for="summary">Summary
                                                <i class="fa fa-exclamation-circle icon-ex" @click="show_composition()"
                                                   v-if="composition.length>0"
                                                   title="Ver alternativas de textos en el compuesto"></i>
                                            </label>
                                            <div class="col-sm-12 p-0">
                                                <vue-editor
                                                    v-model="form.summary[language.id].name"
                                                    :editorToolbar="customToolbar"
                                                    v-bind:id="'summary-' + language.iso"
                                                    v-bind:name="'summary-' + language.iso">
                                                </vue-editor>
                                            </div>
                                        </div>
                                        <div class="form-row container_channel_code" v-show="!hasClient">
                                            <label for="itinerary">{{ $t('services.itinerary') }}
                                                <i class="fa fa-exclamation-circle icon-ex" @click="show_composition()"
                                                   v-if="composition.length>0"
                                                   title="Ver alternativas de textos en el compuesto"></i>
                                            </label>

                                            <div class="col-sm-12 p-0">
                                                <vue-editor
                                                    v-model="form.itinerary[language.id].name"
                                                    :editorToolbar="customToolbar"
                                                    v-bind:id="'itinerary-' + language.iso"
                                                    v-bind:name="'itinerary-' + language.iso">
                                                </vue-editor>
                                            </div>
                                        </div>
                                        <div class="form-row container_channel_code">
                                            <label for="link_trip_advisor">Link TripAdvisor</label>
                                            <div class="col-sm-12 p-0">
                                            <textarea class="form-control input-commerce"
                                                      :id="'link_trip_advisor'+language.id"
                                                      :name="'link_trip_advisor'+language.id"
                                                      rows="5"
                                                      v-model="form.link_trip_advisor[language.id].name"></textarea>
                                            </div>
                                        </div>
                                        <div class="row" v-if="form.include_accommodation">
                                            <div class="col-md-12">
                                                <hr style="height: 2px; margin-top:20px !important; margin-bottom:20px !important;"/>
                                            </div>
                                        </div>
                                        <div class="form-row container_channel_code"
                                             v-if="form.include_accommodation && (form.unitDuration_id == 2 && form.category_id == 2 && form.duration > 1)">
                                            <label for="accommodation">Agregar acomodación:</label>
                                            <b-input-group
                                                :prepend="form.duration + ' ' + ' Días / ' + (form.duration - 1 ) + ' noches'"
                                                class="mb-2 mr-sm-2 mb-sm-0">
                                                <b-form-input :id="'accommodation'+language.id"
                                                              class="input-accommodation"
                                                              :disabled="form.duration <= 1 || form.unitDuration_id != 2 || form.category_id != 2"
                                                              :name="'accommodation'+language.id"
                                                              v-model="form.accommodation[language.id].accommodation"
                                                ></b-form-input>
                                            </b-input-group>
                                        </div>

                                    </b-tab>
                                </b-tabs>
                                <div class="form-group row p-1">
                                    <div class="col-sm-6">
                                        <label>{{ $t('services.restriction') }}</label>
                                        <multiselect :clear-on-select="false"
                                                     :close-on-select="false"
                                                     :hide-selected="true"
                                                     :multiple="true"
                                                     :options="restrictions"
                                                     :placeholder="this.$i18n.t('services.restriction')"
                                                     :preserve-search="true"
                                                     :tag-placeholder="this.$i18n.t('services.restriction')"
                                                     :taggable="true"
                                                     @tag="addRestrictions"
                                                     label="name"
                                                     ref="multiselect"
                                                     track-by="code"
                                                     v-model="restrictionsService">
                                        </multiselect>
                                    </div>
                                    <div class="col-sm-6 p-0">
                                        <label>{{ $t('services.experiences') }}</label>
                                        <multiselect :clear-on-select="false"
                                                     :close-on-select="false"
                                                     :hide-selected="true"
                                                     :multiple="true"
                                                     :options="experiences"
                                                     :placeholder="this.$i18n.t('services.experiences')"
                                                     :preserve-search="true"
                                                     :tag-placeholder="this.$i18n.t('services.experiences')"
                                                     :taggable="true"
                                                     @tag="addExperiences"
                                                     label="name"
                                                     ref="multiselect"
                                                     track-by="code"
                                                     v-model="experiencesService">
                                        </multiselect>
                                    </div>
                                    <div class="col-sm-12">
                                        <label>Remarks</label>
                                        <vue-editor
                                            v-model="form.notes"
                                            :editorToolbar="customToolbar"
                                            id="notes"
                                            name="notes">
                                        </vue-editor>
                                    </div>
                                </div>
                            </b-tab>
                            <b-tab :title="this.$i18n.t('services.tab_config')">
                                <div class="form-group row">
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.typeService') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="categories"
                                                      :value="form.category_id"
                                                      @input="categoryChange"
                                                      autocomplete="true"
                                                      data-vv-as="type service"
                                                      data-vv-name="type_service"
                                                      name="type_service"
                                                      v-model="categorySelected"
                                                      v-validate="'required'">
                                            </v-select>
                                            <span class="invalid-feedback-select"
                                                  v-show="errors.has('type_service')">
                                            <span>{{ errors.first('type_service') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label>{{ $t('services.subtypeService') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="subCategories"
                                                      :value="form.subCategory_id"
                                                      @input="subCategoryChange"
                                                      autocomplete="true"
                                                      data-vv-as="sub type"
                                                      data-vv-name="sub_type"
                                                      v-model="subCategorySelected"
                                                      v-validate="'required'">
                                            </v-select>
                                            <span class="invalid-feedback-select" v-show="errors.has('sub_type')">
                                            <span>{{ errors.first('sub_type') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label>{{ $t('services.category') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="serviceTypes"
                                                      :value="form.serviceType_id"
                                                      @input="serviceTypesChange"
                                                      autocomplete="true"
                                                      data-vv-as="category"
                                                      data-vv-name="category"
                                                      v-model="serviceTypeSelected"
                                                      v-validate="'required'">
                                            </v-select>
                                            <span class="invalid-feedback-select" v-show="errors.has('category')">
                                            <span>{{ errors.first('category') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-row">
                                            <label>{{ $t('services.classification') }}</label>
                                            <div class="col-sm-12 p-0">
                                                <v-select :options="classifications"
                                                          :value="form.classification_id"
                                                          @input="classificationChange"
                                                          autocomplete="true"
                                                          data-vv-as="classification"
                                                          data-vv-name="classification"
                                                          v-model="classificationSelected"
                                                          v-validate="'required'">
                                                </v-select>
                                                <span class="invalid-feedback-select"
                                                      v-show="errors.has('classification')">
                                                <span>{{ errors.first('classification') }}</span>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2" v-if="!hasClient">
                                        <label>{{ $t('services.equivalence_aurora') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="equivalence aurora"
                                                   data-vv-name="equivalence_aurora"
                                                   id="equivalence_aurora"
                                                   name="equivalence_aurora"
                                                   :disabled="true"
                                                   type="text"
                                                   v-model.number="form.equivalence_aurora">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.unit') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="units"
                                                      :value="form.unit_id"
                                                      @input="unitChange"
                                                      autocomplete="true"
                                                      data-vv-as="unit"
                                                      data-vv-name="unit"
                                                      v-model="unitSelected"
                                                      v-validate="'required'">
                                            </v-select>
                                            <span class="invalid-feedback-select" v-show="errors.has('unit')">
                                            <span>{{ errors.first('unit') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.unitDuration') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <v-select :options="unitDurations"
                                                      @input="unitDurationChange"
                                                      autocomplete="true"
                                                      data-vv-as="unit duration"
                                                      data-vv-name="unitDuration"
                                                      v-model="unitDurationsSelected"
                                                      v-validate="'required'">
                                            </v-select>
                                            <span class="invalid-feedback-select"
                                                  v-show="errors.has('unitDuration')">
                                            <span>{{ errors.first('unitDuration') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <label>{{ $t('services.duration') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="duration" data-vv-name="duration"
                                                   id="duration"
                                                   name="duration"
                                                   type="text"
                                                   v-model.number="form.duration"
                                                   v-validate="'required|numeric|min_value:1'">
                                            <span class="invalid-feedback" v-show="errors.has('duration')">
                                            <span>{{ errors.first('duration') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.capacity_min') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="capacity min" data-vv-name="capacity_min"
                                                   id="capacity_min"
                                                   name="capacity_min"
                                                   type="text"
                                                   v-model="form.capacity_min"
                                                   v-validate="'required|numeric|min_value:1'">
                                            <span class="invalid-feedback" v-show="errors.has('capacity_min')">
                                            <span>{{ errors.first('capacity_min') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.capacity_max') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="capacity max"
                                                   data-vv-name="capacity_max"
                                                   type="text"
                                                   v-model="form.capacity_max"
                                                   v-validate="'required|numeric|min_value:1'">
                                            <span class="invalid-feedback" v-show="errors.has('capacity_max')">
                                            <span>{{ errors.first('capacity_max') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <label>{{ $t('services.min_ege') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <input class="form-control"
                                                   data-vv-as="ege" data-vv-name="min_ege"
                                                   id="min_ege"
                                                   name="min_ege"
                                                   type="text"
                                                   v-model="form.min_ege"
                                                   v-validate="'required|numeric|min_value:0'">
                                            <span class="invalid-feedback" v-show="errors.has('min_ege')">
                                            <span>{{ errors.first('min_ege') }}</span>
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row" v-if="!hasClient">
                                    <div class="col-sm-2" v-if="!hasClient">
                                        <label>{{ $t('services.service_affection_igv') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form.affected_igv"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-sm-2" v-if="!hasClient">
                                        <label>{{ $t('services.requires_itinerary') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form.req_itinerary"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-sm-3" v-if="!hasClient">
                                        <label>{{ $t('services.requires_image_itinerary') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form.req_image_itinerary"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-sm-2" v-if="!hasClient">
                                        <label>{{ $t('services.service_affection_markup') }}</label>
                                        <div class="col-sm-12 p-0">
                                            <c-switch :value="true" class="mx-1" color="success"
                                                      v-model="form.affected_markup"
                                                      variant="pill">
                                            </c-switch>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2" v-if="!hasClient">
                                        <div class="col-sm-3 p-0">
                                            <label>{{ $t('package.package_physical_intensity') }}</label>
                                            <v-select :options="physicalIntensities"
                                                      :value="form.physical_intensity_id"
                                                      @input="physicalIntensityChange"
                                                      autocomplete="true"
                                                      data-vv-as="physical_intensity"
                                                      data-vv-name="physical_intensity"
                                                      v-model="physicalIntensitySelected">
                                            </v-select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2" v-if="!hasClient">
                                        <div class="col-sm-12 p-0">
                                            <label>{{ $t('services.pre_requirements') }}</label>
                                            <multiselect :clear-on-select="false"
                                                         :close-on-select="false"
                                                         :hide-selected="true"
                                                         :multiple="true"
                                                         :options="requirements"
                                                         :placeholder="this.$i18n.t('services.pre_requirements')"
                                                         :preserve-search="true"
                                                         :tag-placeholder="this.$i18n.t('services.pre_requirements')"
                                                         :taggable="true"
                                                         @tag="addRequirements"
                                                         label="name"
                                                         ref="multiselect"
                                                         track-by="code"
                                                         v-model="requirementsService">
                                            </multiselect>
                                        </div>
                                    </div>
                                </div>
                            </b-tab>

                            <b-tab title="Composición" v-if="client_id == ''">
                                <div class="col-12">
                                    <h3 class="text-center title-equivalence">
                                        Equivalencia Aurora. Código: {{ form.aurora_code }} | N°:
                                        {{ form.equivalence_aurora }}
                                    </h3>
                                </div>

                                <div class="col-12 text-center my-2">
                                    <div class="row mx-0 px-0">
                                        <div class="px-1">
                                            <label class="btn btn-outline-success col-6 left"
                                                   @click="choose_parent(service_parent)"
                                                   v-for="service_parent in services_parents"
                                                   :for="'parent_'+service_parent.id">
                                                <input type="radio" v-model="service_parent_id"
                                                       :value="service_parent.id" class="btn-check" name="radio_parent"
                                                       :id="'parent_'+service_parent.id">
                                                {{ service_parent.master_service.code }} -
                                                {{ service_parent.master_service.description }}
                                                <label class="right alert-warning"
                                                       v-if="service_parent.components.length===0">Directo</label>
                                                <label class="right alert-info"
                                                       v-if="service_parent.components.length>0">Padre
                                                    ({{ service_parent.components.length }})</label>
                                            </label>
                                            <label class="btn btn-outline-success col-12"
                                                   v-if="services_parents.length===0">
                                                <label class="right alert-info">No tiene ningún servicio
                                                    agregado.</label>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12" v-if="services_parents.length>0">
                                    <h4 class="title-equivalence"
                                        v-if="services_components.length===1 && services_components[0].type_service==='direct'">
                                        Servicio Directo: {{ service_parent_code }}
                                    </h4>
                                    <h4 class="title-equivalence" v-else>
                                        Componentes de Servicio: {{ service_parent_code }}
                                    </h4>
                                </div>

                                <div class="col-12 text-center my-2">
                                    <div class="row mx-0 px-0">
                                        <div class="col-6 px-1" v-for="service_component in services_components"
                                             v-if="service_component.type_service==='component'">
                                            <label class="btn left" style="width: 100%;"
                                                   :class="{'btn-outline-info':service_component.type_service==='component', 'btn-outline-warning':service_component.type_service==='direct'}"
                                                   :for="'component_'+service_component.codsvs">
                                                {{ service_component.codsvs }} - {{ service_component.descri }}
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </div>
                </form>
            </div>
            <div class="col-sm-6 mt-2">
                <div>
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="validateBeforeSubmitClient" class="btn btn-success" type="submit"
                            v-if="!loading && formAction === 'put' && hasClient">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <button @click="validateBeforeSubmit" class="btn btn-success" type="submit"
                            v-if="!loading && formAction === 'put' && !hasClient">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <button @click="validateBeforeSubmitNew" class="btn btn-success" type="submit"
                            v-if="!loading && formAction === 'post'">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{ $t('global.buttons.submit') }}
                    </button>
                    <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </div>
            </div>
        </div>
        <b-modal title="Notificaciones" centered ref="my-modal-notify" size="md">
            <div class="row" v-show="!optionSelect">
                <div class="col-md-12">
                    <p class="text-center">¿Desea enviar una notificación de los cambios realizados?</p>
                </div>
            </div>
            <div class="form-group row" v-show="optionSelect">
                <div class="col-sm-12">
                    <label for="name_service">Emails</label>
                    <multiselect :clear-on-select="false"
                                 :close-on-select="false"
                                 :hide-selected="true"
                                 :multiple="true"
                                 :options="emails"
                                 placeholder="Emails"
                                 :preserve-search="true"
                                 tag-placeholder="Emails"
                                 :taggable="true"
                                 @tag="addEmail"
                                 label="name"
                                 ref="multiselect"
                                 track-by="email"
                                 v-model="emailsService">
                    </multiselect>
                </div>
                <div class="col-md-12">
                    <label for="name_service">Mensaje</label>
                    <textarea class="form-control"
                              id="message" name="descripction"
                              rows="3"
                              v-model="form_notify.message"></textarea>
                </div>
            </div>
            <template slot="modal-footer">
                <div class="row" v-show="!optionSelect">
                    <div class="col-md-12">
                        <button @click="optionSelection(1)" class="btn btn-success" type="reset" v-if="!loading">
                            Si, enviar y guardar
                        </button>
                        <button @click="optionSelection(2)" class="btn btn-secondary" type="reset" v-if="!loading">
                            No, solo guardar
                        </button>
                    </div>
                </div>
                <div class="row" v-show="optionSelect">
                    <div class="col-md-12">
                        <button @click="optionSelection(3)" class="btn btn-success" type="reset" v-if="!loading">
                            Enviar y guardar
                        </button>
                        <button @click="hideModal()" class="btn btn-danger" type="reset" v-if="!loading">
                            {{ $t('global.buttons.cancel') }}
                        </button>
                    </div>
                </div>
            </template>
        </b-modal>

        <b-modal title="Composición del Servicio" centered ref="my-modal-composition" size="md">
            <div class="row col-12">

                <div class="alert alert-info">Lo siguiente son los servicios que componen la equivalencia con sus
                    textos, usted podrá utilizar para copiar el contenido al editor de textos de la equivalencia.
                </div>

                <div class="col-12">
                    <button v-for="composition_ in composition" :class="{'check-true':composition_.checked}"
                            class="btn" type="button" @click="set_composition(composition_)"
                            style="float: left; margin-right: 5px; margin-bottom: 5px">
                        {{ composition_.master_service.code }}
                    </button>
                </div>

                <div class="col-12">
                    <button v-for="composition_language in composition_languages"
                            :class="{'check-true':composition_language.checked}"
                            class="btn" type="button" @click="set_composition_language(composition_language)"
                            style="float: left; margin-right: 5px; margin-bottom: 5px">
                        {{ composition_language.name }}
                    </button>
                </div>

                <div class="col-12 card" style="padding-top: 5px"
                     v-if="composition_language_choosed.skeleton!==undefined || composition_language_choosed.itinerary!==undefined">

                    <label for="" class="icon-ex">Skeleton:</label>
                    <p class="canSelectText">
                        {{ composition_language_choosed.skeleton }}
                    </p>

                    <label for="" class="icon-ex">Itinerario:</label>
                    <p class="canSelectText">
                        {{ composition_language_choosed.itinerary }}
                    </p>

                    <label class="icon-ex">Copiar sólo textos en {{ composition_language_choosed.name }}
                        <input type="checkbox" v-model="copy_this_texts" :disabled="copy_all_texts">
                    </label>
                    <label class="icon-ex">Copiar textos en los {{ composition_languages.length }} idiomas
                        <input type="checkbox" v-model="copy_all_texts">
                    </label>

                </div>
                <div class="col-12 card" style="padding-top: 5px" v-else>

                    <div class="alert-warning">Ningún texto para mostrar</div>

                </div>

            </div>
            <template slot="modal-footer">
                <div class="col-md-12">
                    <button @click="do_copy_texts()" :disabled="!(copy_this_texts) && !(copy_all_texts)"
                            class="btn btn-success right" type="reset" v-if="!loading">
                        Confirmar
                    </button>
                    <button @click="hideModal()" class="btn btn-danger" type="reset" v-if="!loading">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </div>
            </template>
        </b-modal>

    </div>

</template>

<script>
import {API} from './../../api'
import {Switch as cSwitch} from '@coreui/vue'
import BTab from 'bootstrap-vue/es/components/tabs/tab'
import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import Multiselect from 'vue-multiselect'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import BModal from 'bootstrap-vue/es/components/modal/modal'
import {VueEditor} from 'vue2-editor'

export default {
    name: 'Services-Add',
    components: {
        BTabs,
        BTab,
        cSwitch,
        VueBootstrapTypeahead,
        vSelect,
        Multiselect,
        BFormCheckbox,
        BFormCheckboxGroup,
        BInputNumber,
        BModal,
        Loading,
        VueEditor,
    },
    data: () => {
        return {
            customToolbar: [[{header: [false, 1, 2, 3, 4, 5, 6]}], ['bold', 'italic', 'underline'], [{list: 'ordered'}, {list: 'bullet'}], [{align: ''}, {align: 'center'}, {align: 'right'}, {align: 'justify'}], [{color: []}, {background: []}], ['clean']],
            loading: false,
            optionSelect: false,
            requirements: [],
            restrictions: [],
            experiences: [],
            classifications: [],
            states: [],
            cities: [],
            districts: [],
            zones: [],
            physicalIntensities: [],
            countries: [],
            currencies: [],
            languages: [],
            categories: [],
            unitDurations: [],
            unitDurationsReserve: [
                {'code': 1, 'label': 'Horas'},
                {'code': 2, 'label': 'Días'}
            ],
            subCategories: [],
            countryOriginSelected: [],
            stateOriginSelected: [],
            physicalIntensitySelected: [],
            cityOriginSelected: [],
            zoneOriginSelected: [],
            countryDestinySelected: [],
            stateDestinySelected: [],
            cityDestinySelected: [],
            zoneDestinySelected: [],
            serviceTypes: [],
            units: [],
            serviceTypeSelected: [],
            currencySelected: [],
            classificationSelected: [],
            categorySelected: [],
            unitSelected: [],
            unitDurationsSelected: [],
            unitDurationsReserveSelected: [],
            subCategorySelected: [],
            restrictionsService: [],
            requirementsService: [],
            experiencesService: [],
            emailsService: [],
            formAction: 'post',
            languagesGuide: [],
            emails: [],
            form_notify: {
                emails: [],
                message: ''
            },
            tag_services: [],
            form: {
                origin: {
                    country_id: '',
                    state_id: '',
                    city_id: '',
                    zone_id: '',
                },
                destiny: {
                    country_id: '',
                    state_id: '',
                    city_id: '',
                    zone_id: '',
                },
                names: {},
                commercial: {},
                description: {},
                description_commercial: {},
                itinerary_commercial: {},
                itinerary: {},
                summary: {},
                summary_commercial: {},
                link_trip_advisor: {},
                accommodation: {},
                equivalence_aurora: '',
                name: '',
                notes: '',
                latitude: 0,
                longitude: 0,
                qty_reserve: 1,
                qty_reserve_client: 1,
                capacity_min: 1,
                duration: 1,
                hasNotify: false,
                emails: '',
                message: '',
                status: true,
                compensation: false,
                tag_service_id: '',
                physical_intensity_id: '',
                type: 'service',
                exclusive: false,
                exclusive_client: {},
                include_accommodation: false,
            },
            exclusive_client_selected: "",
            exclusive_clients: [],
            composition: [],
            composition_languages: [],
            composition_language_choosed: {
                skeleton: '',
                itinerary: '',
                name: ''
            },
            copy_all_texts: false,
            copy_this_texts: false,
            language_choose: "",
            services_parents: [],
            service_parent_id: '',
            service_parent_code: '',
            services_components: [],
            service_component_code: '',
            client_id: '',
            hasClient: false,
        }
    },
    created() {
        this.client_id = window.localStorage.getItem('client_id')
        this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
    },
    mounted: function () {
        this.loading = true
        this.loadTagServices()
        API.get('users/notification/service').then((result) => {
            let emails = result.data.data
            emails.forEach((email) => {
                this.emails.push({
                    name: email.name + ' <' + email.email + '>',
                    email: email.email
                })
            })
        })
        API.get('/service/form?lang=' + localStorage.getItem('lang')).then((result) => {
            this.loading = false
            this.languages = result.data.data.languages
            this.language_choose = (result.data.data.languages.lenght > 0) ? result.data.data.languages[0] : ""
            let form = {}
            let commerce = result.data.data.languages
            commerce.forEach((value) => {
                this.form.names[value.id] = {
                    name: ''
                }
                this.form.commercial[value.id] = {
                    name: ''
                }
                this.form.description[value.id] = {
                    name: ''
                }
                this.form.description_commercial[value.id] = {
                    name: ''
                }
                this.form.summary[value.id] = {
                    name: ''
                }
                this.form.summary_commercial[value.id] = {
                    name: ''
                }
                this.form.itinerary[value.id] = {
                    name: ''
                }
                this.form.itinerary_commercial[value.id] = {
                    name: ''
                }
                this.form.link_trip_advisor[value.id] = {
                    name: ''
                }
                this.form.accommodation[value.id] = {
                    accommodation: ''
                }
            })
            //countries
            let paises = result.data.data.countries
            paises.forEach((country) => {
                this.countries.push({
                    label: country.translations[0].value,
                    code: country.translations[0].object_id
                })
            })

            //currency
            let monedas = result.data.data.currencies
            monedas.forEach((currency) => {
                this.currencies.push({
                    label: currency.translations[0].value,
                    code: currency.translations[0].object_id
                })
            })

            //restrictions
            let arraytranslRestriction = result.data.data.restrictions
            let a = 0
            arraytranslRestriction.forEach((restriction) => {
                this.restrictions[a] = {
                    code: restriction.translations[0].object_id,
                    name: restriction.translations[0].value
                }
                a++
            })

            //categories
            let categorias = result.data.data.categories
            categorias.forEach((category) => {
                this.categories.push({
                    label: category.translations[0].value,
                    code: category.translations[0].object_id
                })
            })

            //service_types
            let serviceTypes = result.data.data.service_types
            serviceTypes.forEach((serviceTypes) => {
                this.serviceTypes.push({
                    label: serviceTypes.code + ' - ' + serviceTypes.translations[0].value,
                    code: serviceTypes.translations[0].object_id
                })
            })

            //units
            let unitsList = result.data.data.units
            unitsList.forEach((units) => {
                this.units.push({
                    label: units.translations[0].value,
                    code: units.translations[0].object_id
                })
            })

            //Unit durations
            let unitDurations = result.data.data.unit_durations
            unitDurations.forEach((unitDurations) => {
                this.unitDurations.push({
                    label: unitDurations.translations[0].value,
                    code: unitDurations.translations[0].object_id
                })
            })

            //countries
            let physicalintensity = result.data.data.physical_intensity
            physicalintensity.forEach((physical_intensity) => {
                this.physicalIntensities.push({
                    label: physical_intensity.translations[0].value,
                    code: physical_intensity.translations[0].object_id
                })
            })

            this.loadLanguagesGuide()
            //Modificar
            if (this.$route.params.id !== undefined) {
                API.get('/services/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
                        form.service = result.data.data
                        this.formAction = 'put'
                        this.checkedLanguageGuide(form.service[0].languages_guide)
                        //-----Origen
                        //Pais
                        if (form.service[0].service_origin[0] && form.service[0].service_origin[0].country_id != null) {
                            this.form.origin.country_id = form.service[0].service_origin[0].country_id
                            this.countryOriginSelected.push({
                                code: form.service[0].service_origin[0].country_id,
                                label: form.service[0].service_origin[0].country.translations[0].value,
                            })
                        }
                        //estado
                        if (form.service[0].service_origin[0] && form.service[0].service_origin[0].state_id != null) {
                            this.form.origin.state_id = form.service[0].service_origin[0].state_id
                            this.stateOriginSelected.push({
                                code: form.service[0].service_origin[0].state_id,
                                label: form.service[0].service_origin[0].state.translations[0].value,
                            })
                        }
                        //ciudad
                        if (form.service[0].service_origin[0] && form.service[0].service_origin[0].city_id != null) {
                            this.form.origin.city_id = form.service[0].service_origin[0].city_id
                            this.cityOriginSelected.push({
                                code: form.service[0].service_origin[0].city_id,
                                label: form.service[0].service_origin[0].city.translations[0].value,
                            })
                        }
                        //zona
                        if (form.service[0].service_origin[0] && form.service[0].service_origin[0].zone_id != null) {
                            this.form.origin.zone_id = form.service[0].service_origin[0].zone_id
                            this.zoneOriginSelected.push({
                                code: form.service[0].service_origin[0].zone_id,
                                label: form.service[0].service_origin[0].zone.translations[0].value
                            })
                        }

                        //intensidad fisica
                        if (form.service[0].physical_intensity_id) {
                            this.form.physical_intensity_id = form.service[0].physical_intensity_id
                            this.physicalIntensitySelected.push({
                                code: form.service[0].physical_intensity_id,
                                label: form.service[0].physical_intensity.translations[0].value
                            })
                        }

                        //-----Destino
                        //Pais
                        if (form.service[0].service_destination[0] && form.service[0].service_destination[0].country_id != null) {
                            this.form.destiny.country_id = form.service[0].service_destination[0].country_id
                            this.loadState(form.service[0].service_destination[0].country_id)
                            this.countryDestinySelected.push({
                                code: form.service[0].service_destination[0].country_id,
                                label: form.service[0].service_destination[0].country.translations[0].value,
                            })
                        }
                        //estado
                        if (form.service[0].service_destination[0] && form.service[0].service_destination[0].state_id != null) {
                            this.form.destiny.state_id = form.service[0].service_destination[0].state_id
                            this.loadCity(form.service[0].service_destination[0].state_id)
                            this.stateDestinySelected.push({
                                code: form.service[0].service_destination[0].state_id,
                                label: form.service[0].service_destination[0].state.translations[0].value,
                            })
                        }
                        //ciudad
                        if (form.service[0].service_destination[0] && form.service[0].service_destination[0].city_id != null) {
                            this.form.destiny.city_id = form.service[0].service_destination[0].city_id
                            this.loadZone(form.service[0].service_destination[0].city_id)
                            this.cityDestinySelected.push({
                                code: form.service[0].service_destination[0].city_id,
                                label: form.service[0].service_destination[0].city.translations[0].value,
                            })
                        }
                        //zona
                        if (form.service[0].service_destination[0] && form.service[0].service_destination[0].zone_id != null) {
                            this.form.destiny.zone_id = form.service[0].service_destination[0].zone_id
                            this.zoneDestinySelected.push({
                                code: form.service[0].service_destination[0].zone_id,
                                label: form.service[0].service_destination[0].zone.translations[0].value,
                            })
                        }
                        //moneda
                        this.form.currency_id = form.service[0].currency_id
                        this.currencySelected.push({
                            code: form.service[0].currency_id,
                            label: form.service[0].currency.translations[0].value,
                        })

                        this.form.aurora_code = form.service[0].aurora_code
                        this.form.name = form.service[0].name
                        this.form.latitude = form.service[0].latitude
                        this.form.longitude = form.service[0].longitude
                        this.form.equivalence_aurora = form.service[0].equivalence_aurora
                        this.form.duration = form.service[0].duration
                        this.form.qty_reserve = form.service[0].qty_reserve
                        this.form.qty_reserve_client = form.service[0].qty_reserve_client
                        this.form.capacity_min = form.service[0].pax_min
                        this.form.capacity_max = form.service[0].pax_max
                        this.form.capacity_max = form.service[0].pax_max
                        this.form.min_ege = form.service[0].min_age
                        this.form.affected_igv = !!(form.service[0].affected_igv)
                        this.form.affected_markup = !!(form.service[0].affected_markup)
                        this.form.status = !!(form.service[0].status)
                        this.form.compensation = !!(form.service[0].compensation)
                        this.form.exclusive = !!(form.service[0].exclusive)
                        this.form.exclusive_client_id = form.service[0].exclusive_client_id

                        if (form.service[0].exclusive_client_id !== '' && form.service[0].exclusive_client_id !== null) {
                            API.get('clients/selectBox/by/name?query=' + form.service[0].exclusive_client_id)
                                .then((result) => {
                                    if (result.data.data.length > 0) {
                                        this.exclusive_client_selected = {
                                            label: result.data.data[0].name,
                                            code: result.data.data[0].id
                                        }
                                    }
                                })
                        }

                        this.form.tag_service_id = form.service[0].tag_service_id
                        this.form.req_itinerary = !!(form.service[0].require_itinerary)
                        this.form.req_image_itinerary = !!(form.service[0].require_image_itinerary)
                        this.form.include_accommodation = !!(form.service[0].include_accommodation)
                        this.form.notes = form.service[0].notes
                        this.form.type = form.service[0].type

                        //tipo de servicio
                        this.form.category_id = form.service[0].service_sub_category.service_category_id
                        this.loadSubCategory(form.service[0].service_sub_category.service_category_id)
                        this.categorySelected.push({
                            code: form.service[0].service_sub_category.service_category_id,
                            label: form.service[0].service_sub_category.service_categories.translations[0].value,
                        })

                        //subtipo de servicio
                        this.form.subCategory_id = form.service[0].service_sub_category_id
                        this.subCategorySelected.push({
                            code: form.service[0].service_sub_category_id,
                            label: form.service[0].service_sub_category.translations[0].value,
                        })
                        //categoria
                        this.form.serviceType_id = form.service[0].service_type_id
                        this.serviceTypeSelected.push({
                            code: form.service[0].service_type_id,
                            label: form.service[0].service_type.code + ' - ' + form.service[0].service_type.translations[0].value,
                        })

                        //classificacion
                        this.form.classification_id = form.service[0].classification_id
                        this.classificationSelected.push({
                            code: form.service[0].classification_id,
                            label: form.service[0].classification.translations[0].value,
                        })
                        //unidad
                        this.form.unit_id = form.service[0].unit_id
                        this.unitSelected.push({
                            code: form.service[0].unit_id,
                            label: form.service[0].units.translations[0].value,
                        })

                        //unidad duracion
                        this.form.unitDuration_id = form.service[0].unit_duration_id
                        this.unitDurationsSelected.push({
                            code: form.service[0].unit_duration_id,
                            label: form.service[0].unit_durations.translations[0].value,
                        })
                        //unidad duracion de la reserva
                        this.form.unitDurationReserve_id = form.service[0].unit_duration_reserve
                        if (form.service[0].unit_duration_reserve === 1) {
                            this.unitDurationsReserveSelected.push({
                                code: form.service[0].unit_duration_reserve,
                                label: 'Horas',
                            })
                        } else if (form.service[0].unit_duration_reserve === 2) {
                            this.unitDurationsReserveSelected.push({
                                code: form.service[0].unit_duration_reserve,
                                label: 'Días',
                            })
                        }
                        //Restricciones
                        let arrayAuxRestriction = form.service[0].restriction
                        let r = 0
                        let argDataR = []
                        arrayAuxRestriction.forEach((restriction) => {
                            argDataR[r] = {
                                code: restriction.translations[0].object_id,
                                name: restriction.translations[0].value
                            }
                            r++
                        })
                        this.restrictionsService = argDataR
                        this.form.restrictionsService = argDataR

                        //Experiencias
                        let arrayAuxExperience = form.service[0].experience
                        let e = 0
                        let argDataE = []
                        arrayAuxExperience.forEach((experience) => {
                            argDataE[e] = {
                                code: experience.translations[0].object_id,
                                name: experience.translations[0].value
                            }
                            e++
                        })
                        this.experiencesService = argDataE
                        this.form.experiencesService = argDataE

                        //Pre-Requisitos
                        let arrayAuxRequirement = form.service[0].requirement
                        let j = 0
                        let argData = []
                        arrayAuxRequirement.forEach((requirement) => {
                            argData[j] = {
                                code: requirement.translations[0].object_id,
                                name: requirement.translations[0].value
                            }
                            j++
                        })
                        this.requirementsService = argData
                        this.form.requirementsService = argData

                        //Textos
                        let arrayTranslations = form.service[0].service_translations
                        arrayTranslations.forEach((translation) => {
                            if (this.form.commercial[translation.language_id] !== undefined) {
                                this.form.commercial[translation.language_id].id = translation.id
                                this.form.commercial[translation.language_id].name = translation.name_commercial
                                this.form.names[translation.language_id].name = translation.name
                                this.form.description[translation.language_id].name = translation.description
                                this.form.description_commercial[translation.language_id].name = translation.description_commercial
                                this.form.summary[translation.language_id].name = translation.summary
                                this.form.summary_commercial[translation.language_id].name = translation.summary_commercial
                                this.form.itinerary[translation.language_id].name = translation.itinerary
                                this.form.itinerary_commercial[translation.language_id].name = translation.itinerary_commercial
                                this.form.link_trip_advisor[translation.language_id].name = translation.link_trip_advisor
                                this.form.accommodation[translation.language_id].accommodation = translation.accommodation
                            }
                        })

                        this.composition = form.service[0].composition

                        if (this.composition.length > 0) {
                            this.composition.forEach((c) => {
                                c.checked = 0
                                c.languages_ = []
                                this.languages.forEach((l) => {
                                    let data_translation = {
                                        id: l.id,
                                        iso: l.iso,
                                        name: l.name,
                                        checked: 0
                                    }

                                    c.master_service.translations.forEach((t) => {
                                        if (t.slug === 'skeleton' && t.language_id === l.id) {
                                            data_translation.skeleton = t.value
                                        }
                                        if (t.slug === 'itinerary' && t.language_id === l.id) {
                                            data_translation.itinerary = t.value
                                        }
                                    })
                                    c.languages_.push(data_translation)
                                })
                            })

                            this.composition[0].checked = 1

                            this.composition_languages = this.composition[0].languages_
                            if (this.language_choose !== '') {
                                this.composition_languages.forEach((c_l) => {
                                    if (c_l.id === this.language_choose.id) {
                                        c_l.checked = 1
                                    }
                                })
                            } else {
                                this.composition_languages[0].checked = 1
                            }

                            this.composition_language_choosed.skeleton = this.composition[0].languages_[0].skeleton
                            this.composition_language_choosed.itinerary = this.composition[0].languages_[0].itinerary
                            this.composition_language_choosed.name = this.composition[0].languages_[0].name
                            this.composition_language_choosed.id = this.composition[0].languages_[0].id
                        }

                        this.services_parents = []
                        if (form.service[0].composition_.length > 0) {
                            this.services_parents = form.service[0].composition_
                            this.service_parent_id = this.services_parents[0].id
                            this.choose_parent(this.services_parents[0])
                        }

                        this.loading = false
                    })
            }

            //requirements
            let arraytranslRequirement = result.data.data.requirements
            let b = 0
            arraytranslRequirement.forEach((requirement) => {
                this.requirements[b] = {
                    code: requirement.translations[0].object_id,
                    name: requirement.translations[0].value
                }
                b++
            })

            //classifications
            let classification = result.data.data.classifications
            classification.forEach((classification) => {
                this.classifications.push({
                    label: classification.translations[0].value,
                    code: classification.translations[0].object_id
                })
            })

            //experiences
            let arraytranslExperiences = result.data.data.experiences
            let i = 0
            arraytranslExperiences.forEach((experience) => {
                this.experiences[i] = {
                    code: experience.translations[0].object_id,
                    name: experience.translations[0].value
                }
                i++
            })

        })

    },
    computed: {
        buildSchedule: function () {
            return this.form.schedule = []
        },
    },
    methods: {
        choose_parent(service_parent) {
            this.service_parent_code = service_parent.master_service.code
            this.services_components = []
            if (service_parent.components.length > 0) {
                this.services_components = service_parent.components
                this.services_components.forEach((s_c) => {
                    s_c.type_service = 'component'
                })
            } else {
                this.services_components.push({
                    type_service: 'direct',
                    codsvs: service_parent.master_service.code,
                    nroite: null,
                    clasvs: service_parent.master_service.classification,
                    diain: null,
                    diaout: null,
                    horin: null,
                    horout: null,
                    ciudes: service_parent.master_service.city_in_iso,
                    ciuhas: service_parent.master_service.city_out_iso,
                    descri: service_parent.master_service.description,
                })
            }
            this.service_component_code = this.services_components[0].codsvs

        },
        set_language(l) {
            this.language_choose = l
        },
        do_copy_texts() {

            if (this.copy_all_texts) {
                this.composition_languages.forEach((c_l) => {
                    this.form.summary[c_l.id].name = c_l.skeleton
                    this.form.summary_commercial[c_l.id].name = c_l.skeleton
                    this.form.itinerary[c_l.id].name = c_l.itinerary
                    this.form.itinerary_commercial[c_l.id].name = c_l.itinerary
                    this.form.link_trip_advisor[c_l.id].name = c_l.link_trip_advisor

                    document.getElementById("summary" + c_l.id).textContent = c_l.skeleton;
                    document.getElementById("summary_commercial" + c_l.id).textContent = c_l.skeleton;
                    document.getElementById("itinerary" + c_l.id).textContent = c_l.itinerary;
                    document.getElementById("itinerary_commercial" + c_l.id).textContent = c_l.itinerary;
                    document.getElementById("link_trip_advisor" + c_l.id).textContent = c_l.link_trip_advisor;
                })
            } else {
                this.form.summary[this.composition_language_choosed.id].name = this.composition_language_choosed.skeleton
                this.form.summary_commercial[this.composition_language_choosed.id].name = this.composition_language_choosed.skeleton
                this.form.itinerary[this.composition_language_choosed.id].name = this.composition_language_choosed.itinerary
                this.form.itinerary_commercial[this.composition_language_choosed.id].name = this.composition_language_choosed.itinerary
                this.form.link_trip_advisor[this.composition_language_choosed.id].name = this.composition_language_choosed.link_trip_advisor

                document.getElementById("summary" + this.composition_language_choosed.id).textContent = this.composition_language_choosed.skeleton;
                document.getElementById("summary_commercial" + this.composition_language_choosed.id).textContent = this.composition_language_choosed.skeleton;
                document.getElementById("itinerary" + this.composition_language_choosed.id).textContent = this.composition_language_choosed.itinerary;
                document.getElementById("itinerary_commercial" + this.composition_language_choosed.id).textContent = this.composition_language_choosed.itinerary;
                document.getElementById("link_trip_advisor" + this.composition_language_choosed.id).textContent = this.composition_language_choosed.link_trip_advisor;
            }

            this.hideModal()

        },
        set_composition_language(composition_language_) {
            console.log('si')
            console.log(composition_language_)
            console.log(composition_language_.skeleton)
            this.composition_languages.forEach((l) => {
                l.checked = 0
            })

            composition_language_.checked = 1

            this.composition_language_choosed.skeleton = composition_language_.skeleton
            this.composition_language_choosed.itinerary = composition_language_.itinerary
            this.composition_language_choosed.name = composition_language_.name
            this.composition_language_choosed.id = composition_language_.id
        },
        set_composition(composition_) {
            this.composition.forEach((c) => {
                c.checked = 0
            })
            composition_.checked = 1
            this.composition_languages = composition_.languages_

            this.composition_languages.forEach((l) => {
                l.checked = 0
            })

            this.composition_languages[0].checked = 1

            this.composition_language_choosed.skeleton = composition_.languages_[0].skeleton
            this.composition_language_choosed.itinerary = composition_.languages_[0].itinerary
            this.composition_language_choosed.name = composition_.languages_[0].name
            this.composition_language_choosed.id = composition_.languages_[0].id
        },
        show_composition() {
            this.$refs['my-modal-composition'].show()

            if (this.language_choose !== '') {
                this.composition_languages.forEach((c_l) => {
                    if (c_l.id === this.language_choose.id) {
                        c_l.checked = 1
                        this.composition_language_choosed.skeleton = c_l.skeleton
                        this.composition_language_choosed.itinerary = c_l.itinerary
                        this.composition_language_choosed.name = c_l.name
                        this.composition_language_choosed.id = c_l.id
                    } else {
                        c_l.checked = 0
                    }
                })
            } else {
                this.composition_languages[0].checked = 1
            }

        },
        client_exclusive_change: function (value) {
            this.exclusive_client_selected = value
            if (this.exclusive_client_selected != null) {
                this.form.exclusive_client_id = this.exclusive_client_selected.code
            } else {
                this.form.exclusive_client_id = ''
            }

        },
        search_clients(search, loading) {
            loading(true)
            API.get('clients/selectBox/by/name?query=' + search)
                .then((result) => {
                    loading(false)
                    let clients_ = []
                    result.data.data.forEach((c) => {
                        clients_.push({
                            label: c.name,
                            code: c.id
                        })
                    })
                    this.exclusive_clients = clients_
                }).catch(() => {
                loading(false)
            })
        },
        loadTagServices: function () {
            API.get('tagservices/?lang=' + localStorage.getItem('lang')).then((result) => {
                this.loading = false
                if (result.data.success === true) {
                    this.tag_services = result.data.data
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Fetch Error',
                        text: result.data.message
                    })
                }
            })
                .catch((e) => {
                })
        },
        getServiceText: function () {
            let form = {}
            API.get('/services/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    form.service = result.data.data
                    this.formAction = 'put'
                    //Textos
                    this.form.description_commercial = {}
                    this.form.description = {}
                    this.form.names = {}
                    this.form.commercial = {}
                    this.form.itinerary = {}
                    this.form.itinerary_commercial = {}
                    this.form.summary = {}
                    this.form.summary_commercial = {}
                    this.form.link_trip_advisor = {}
                    this.form.accommodation = {}
                    let arrayTranslations = form.service[0].service_translations
                    arrayTranslations.forEach((translation) => {
                        this.form.commercial[translation.language_id].id = translation.id
                        this.form.commercial[translation.language_id].name = translation.name_commercial
                        this.form.names[translation.language_id].name = translation.name
                        this.form.description[translation.language_id].name = translation.description
                        this.form.description_commercial[translation.language_id].name = translation.description_commercial
                        this.form.summary[translation.language_id].name = translation.summary
                        this.form.summary_commercial[translation.language_id].name = translation.summary_commercial
                        this.form.itinerary[translation.language_id].name = translation.itinerary
                        this.form.itinerary_commercial[translation.language_id].name = translation.itinerary_commercial
                        this.form.link_trip_advisor[translation.language_id].name = translation.link_trip_advisor
                        this.form.accommodation[translation.language_id].accommodation = translation.accommodation
                    })
                    this.loading = false
                })
        },
        countryOriginChange: function (value) {
            this.country = value
            if (this.country != null) {
                if (this.form.origin.country_id !== this.country.code) {
                    this.form.origin.zone_id = ''
                    this.form.origin.state_id = ''
                    this.form.origin.state_id = ''
                    this.zoneOriginSelected = []
                    this.cityOriginSelected = []
                    this.stateOriginSelected = []
                }
                this.form.origin.country_id = this.country.code
                this.loadState(this.country.code)
            } else {
                this.form.origin.zone_id = ''
                this.form.origin.state_id = ''
                this.form.origin.state_id = ''
                this.zoneOriginSelected = []
                this.cityOriginSelected = []
                this.stateOriginSelected = []
            }
        },
        stateOriginChange: function (value) {
            this.state = value
            if (this.state != null) {
                if (this.form.origin.state_id !== this.state.code) {
                    this.form.origin.city_id = ''
                    this.form.origin.zone_id = ''
                    this.zoneOriginSelected = []
                    this.cityOriginSelected = []
                }
                this.form.origin.state_id = this.state.code
                this.loadCity(this.state.code)
            } else {
                this.form.origin.city_id = ''
                this.form.origin.zone_id = ''
                this.zoneOriginSelected = []
                this.cityOriginSelected = []
            }
        },
        cityOriginChange: function (value) {
            this.city = value
            if (this.city != null) {
                if (this.form.origin.city_id !== this.city.code) {
                    this.form.origin.zone_id = ''
                    this.zoneOriginSelected = []
                }
                this.form.origin.city_id = this.city.code
                if (this.city.code > 0) {
                    this.loadZone(this.city.code)
                }
            } else {
                this.form.origin.city_id = ''
                this.zoneOriginSelected = []
            }
        },
        zoneOriginChange: function (value) {
            this.zone = value
            if (this.zone !== null) {
                this.form.origin.zone_id = this.zone.code
            } else {
                this.form.origin.zone_id = ''
                this.zoneOriginSelected = []
            }
        },
        physicalIntensityChange: function (value) {
            this.physical_intensity = value
            if (this.physical_intensity !== null) {
                this.form.physical_intensity_id = this.physical_intensity.code
            } else {
                this.form.physical_intensity_id = ''
                this.physicalIntensitySelected = []
            }
        },
        //destiny
        countryDestinyChange: function (value) {
            this.country = value
            if (this.country != null) {
                if (this.form.destiny.country_id !== this.country.code) {
                    this.form.destiny.zone_id = ''
                    this.form.destiny.state_id = ''
                    this.form.destiny.state_id = ''
                    this.zoneDestinySelected = []
                    this.cityDestinySelected = []
                    this.stateDestinySelected = []
                }
                this.form.destiny.country_id = this.country.code
                this.loadState(this.country.code)
            } else {
                this.form.destiny.zone_id = ''
                this.form.destiny.state_id = ''
                this.form.destiny.state_id = ''
                this.zoneDestinySelected = []
                this.cityDestinySelected = []
                this.stateDestinySelected = []
            }
        },
        stateDestinyChange: function (value) {
            this.state = value
            if (this.state != null) {
                if (this.form.destiny.state_id !== this.state.code) {
                    this.form.destiny.city_id = ''
                    this.form.destiny.zone_id = ''
                    this.zoneDestinySelected = []
                    this.cityDestinySelected = []
                }
                this.form.destiny.state_id = this.state.code
                this.loadCity(this.state.code)
            } else {
                this.form.destiny.city_id = ''
                this.form.destiny.zone_id = ''
                this.zoneDestinySelected = []
                this.cityDestinySelected = []
            }
        },
        cityDestinyChange: function (value) {
            this.city = value
            if (this.city != null) {
                if (this.form.destiny.city_id !== this.city.code) {
                    this.form.destiny.zone_id = ''
                    this.zoneDestinySelected = []
                }
                this.form.destiny.city_id = this.city.code
                if (this.city.code > 0) {
                    this.loadZone(this.city.code)
                }
            } else {
                this.form.destiny.city_id = ''
                this.zoneDestinySelected = []
            }
        },
        zoneDestinyChange: function (value) {
            this.zone = value
            if (this.zone !== null) {
                this.form.destiny.zone_id = this.zone.code
            } else {
                this.form.destiny.zone_id = ''
                this.zoneDestinySelected = []
            }
        },
        currencyChange: function (value) {
            this.currency = value
            if (this.currency != null) {
                this.form.currency_id = this.currency.code
            } else {
                this.form.currency_id = ''
            }
        },
        loadState(codecountry) {
            if (codecountry > 0) {
                this.states = []
                this.cities = []
                this.zones = []
                this.districts = []

                API.get('/state/getstates/' + codecountry + '/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let departamentos = result.data.data
                        departamentos.forEach((state) => {
                            this.states.push({
                                label: state.translations[0].value,
                                code: state.translations[0].object_id
                            })
                        })
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            }
        },
        loadCity(codestate) {
            if (codestate > 0) {

                this.cities = []
                this.zones = []
                this.districts = []
                API.get('/city/getcities/' + codestate + '/' + localStorage.getItem('lang'))
                    .then((result) => {
                        let cuidades = result.data.data
                        cuidades.forEach((city) => {
                            this.cities.push({
                                label: city.translations[0].value,
                                code: city.translations[0].object_id
                            })
                        })
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            }
        },

        loadZone(codecity) {
            this.zones = []
            //this.form.zone_id=''
            API.get('/zone/getzones/' + codecity + '/' + localStorage.getItem('lang'))
                .then((result) => {
                    let zonas = result.data.data
                    zonas.forEach((zone) => {
                        this.zones.push({
                            label: zone.translations[0].value,
                            code: zone.translations[0].object_id
                        })
                    })

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        loadSubCategory(code) {
            this.subCategories = []
            API.get('/service_categories/' + code + '/' + localStorage.getItem('lang') + '/subcategory')
                .then((result) => {
                    let subCategories = result.data.data
                    subCategories.forEach((subCategories) => {
                        this.subCategories.push({
                            label: subCategories.translations[0].value,
                            code: subCategories.translations[0].object_id
                        })
                    })

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('global.error.messages.connection_error')
                })
            })
        },
        addRestrictions(newTag) {
            const tag = {
                name: newTag,
                code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.restrictionsService.push(tag)
        },
        addRequirements(newTag) {
            const tag = {
                name: newTag,
                code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.requirementsService.push(tag)
        },
        addExperiences(newTag) {
            const tag = {
                name: newTag,
                code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.experiencesService.push(tag)
        },
        categoryChange: function (value) {
            this.category = value
            console.log('category', this.category)
            if (this.category != null) {
                if (this.form.category_id !== this.category.code) {
                    this.form.subCategory_id = ''
                    this.subCategorySelected = []
                }
                if (this.category.code != 2) {
                    this.form.include_accommodation = false
                }
                this.form.category_id = this.category.code
                this.loadSubCategory(this.category.code)
            } else {
                this.form.subCategory_id = ''
                this.subCategorySelected = []
            }
        },
        subCategoryChange: function (value) {
            this.subcategory = value
            if (this.subcategory != null) {
                this.form.subCategory_id = this.subcategory.code
            } else {
                this.form.subCategory_id = ''
                this.subCategorySelected = []
            }
        },
        serviceTypesChange: function (value) {
            this.serviceType = value
            if (this.serviceType != null) {
                this.form.serviceType_id = this.serviceType.code
            } else {
                this.form.serviceType_id = ''
                this.serviceTypeSelected = []
            }
        },
        unitChange: function (value) {
            this.unit = valu
            if (this.unit != null) {
                this.form.unit_id = this.unit.code
            } else {
                this.form.unit_id = ''
                this.unitSelected = []
            }
        },
        unitDurationChange: function (value) {
            if (value != null) {
                this.form.unitDuration_id = value.code;
                if (value.code != 2) {
                    this.form.include_accommodation = false;
                }
            } else {
                this.form.unitDuration_id = '';
            }
        },
        unitDurationReserveChange: function (value) {
            this.unitDurationReserve = value
            if (this.unitDurationReserve != null) {
                this.form.unitDurationReserve_id = this.unitDurationReserve.code
            } else {
                this.form.unitDurationReserve_id = ''
                this.unitDurationsReserveSelected = []
            }
        },
        classificationChange: function (value) {
            this.classification = value
            if (this.classification != null) {
                this.form.classification_id = this.classification.code
            } else {
                this.form.classification_id = ''
                this.classificationSelected = []
            }
        },
        validateBeforeSubmit: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.optionSelect = false
                    this.$refs['my-modal-notify'].show()
                    // this.submit()
                    // this.$validator.reset() //solution
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.information_complete')
                    })
                    this.loading = false
                }

            })
        },
        validateBeforeSubmitClient: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                    // this.$validator.reset() //solution
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.information_complete')
                    })
                    this.loading = false
                }

            })
        },
        validateBeforeSubmitNew: function () {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                    this.$validator.reset() //solution
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.information_complete')
                    })
                    this.loading = false
                }

            })
        },

        submit: function () {
            //restrictions
            let varRestrictions = this.restrictionsService
            let restrictions = []
            varRestrictions.forEach((restriction) => {
                restrictions.push(restriction.code)
            })

            //experiences
            let varExperiences = this.experiencesService
            let experiences = []
            varExperiences.forEach((experience) => {
                experiences.push(experience.code)
            })

            //requirements
            let varRequirements = this.requirementsService
            let requirements = []
            varRequirements.forEach((requirement) => {
                requirements.push(requirement.code)
            })

            this.form.translRestrictions = restrictions
            this.form.translExperiences = experiences
            this.form.translRequirements = requirements

            if (this.formAction !== 'put') {
                this.form.status = (this.form.status === false ? 0 : 1)
                this.form.compensation = (this.form.compensation === false ? 0 : 1)
                this.form.exclusive = (this.form.exclusive === false ? 0 : 1)
                this.form.include_accommodation = (this.form.include_accommodation === false ? 0 : 1)
                this.form.affected_igv = (this.form.affected_igv === false ? 0 : 1)
                this.form.affected_markup = (this.form.affected_markup === false ? 0 : 1)
                this.form.req_itinerary = (this.form.req_itinerary === false ? 0 : 1)
                this.form.req_image_itinerary = (this.form.req_image_itinerary === false ? 0 : 1)
            }

            this.form.hasNotify = this.optionSelect
            this.form.emails = this.emailsService
            this.form.message = this.form_notify.message

            this.loading = true
            API({
                method: this.formAction,
                url: 'services' + (this.$route.params.id !== undefined ? '/' + this.$route.params.id : ''),
                data: this.form
            }).then((result) => {
                this.loading = false
                if (result.data.success === false) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.error.save')
                    })

                } else {
                    let service_id
                    if (this.formAction !== 'put') {
                        service_id = result.data.data.id
                    } else {
                        service_id = this.$route.params.id
                    }
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: this.$t('global.modules.services'),
                        text: this.$t('global.success.save')
                    })
                    this.optionSelect = false
                    this.$router.push({path: '/services_new/edit/' + service_id})
                    location.reload()
                }
            })

        },

        optionSelection: function (option) {
            if (option === 1) {
                this.optionSelect = true
            } else if (option === 2) {
                this.$refs['my-modal-notify'].hide()
                this.submit()
                this.$validator.reset() //solution
            } else {
                if (this.formAction == 'put' && this.optionSelect) {
                    if (this.emailsService.length == 0) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: 'Debe seleccionar al menos un email'
                        })
                    } else {
                        this.$refs['my-modal-notify'].hide()
                        this.submit()
                    }

                }

            }
        },
        hideModal() {
            this.optionSelect = false
            this.$refs['my-modal-notify'].hide()
            this.$refs['my-modal-composition'].hide()
        },
        CancelForm() {
            this.$router.push({path: '/services_new'})
        },
        addEmail(newTag) {
            const tag = {
                name: newTag,
                email: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
            }
            this.emailsService.push(tag)
        },
        removeEmail: function (index) {
            this.form_notify.emails.splice(index, 1)
        },
        loadLanguagesGuide: function () {
            for (let i = 0; i < this.languages.length; i++) {
                this.languagesGuide.push({
                    language_id: this.languages[i].id,
                    language_name: this.languages[i].name,
                    checked: false
                })
            }
        },
        addLanguageGuide: function (language_id) {
            API.post('add/language/guide', {language_id: language_id, service_id: this.$route.params.id})
        },
        deleteLanguageGuide: function (language_id) {
            API.post('delete/language/guide', {language_id: language_id, service_id: this.$route.params.id})
        },
        validateOperationLanguageGuide: function (language) {
            if (language.checked) {
                this.addLanguageGuide(language.language_id)
            } else {
                this.deleteLanguageGuide(language.language_id)
            }
        },
        checkedLanguageGuide: function (languages_guide) {
            for (let i = 0; i < languages_guide.length; i++) {
                for (let j = 0; j < this.languagesGuide.length; j++) {
                    if (languages_guide[i].language_id == this.languagesGuide[j].language_id) {
                        this.languagesGuide[j].checked = true
                    }
                }
            }
        },
    }
}
</script>

<style lang="stylus">
.icon-ex {
    cursor: pointer;
    color: #3a56e0;
}

.invalid-feedback-select {
    width: 100%;
    margin-top: 0.25rem;
    font-size: 80%;
    color: #f86c6b;
}

.input-commerce {
    background-color: #f1f2ff !important;
}

.input-accommodation {
    background-color: #fff7f1 !important;
}

.input-internal {
    background-color: #f0f3f5 !important;
}

.service {
    background: #4dbd743b !important;
}

.supplement {
    background: #ffc10759 !important;
}

.canSelectText {
    user-select: text;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

