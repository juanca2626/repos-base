<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">

                <div class="b-form-group form-group" style="position: absolute; top: -70px; right: 0; width: 50px; padding-top:5px;" v-if="this.$route.name !== 'HotelsAdd'">
                    <c-switch class="mx-1" color="success"
                              title="Hotel nuevo"
                              v-model="form.flag_new"
                              variant="pill">
                    </c-switch>
                </div>

                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="chain_id">{{ $t('hotels.chain_name') }}</label>
                        <div class="col-sm-5">
                            <v-select :options="chains"
                                      id="chain_id"
                                      @input="chainChange"
                                      :value="form.chanel_id"
                                      autocomplete="true"
                                      v-model="chainSelected">
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorChain">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotels.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="country_id">{{ $t('hotels.country_name') }}</label>
                        <div class="col-sm-4">
                            <v-select :options="countries"
                                      id="country_id"
                                      :value="form.country_id"
                                      @input="countryChange"
                                      autocomplete="true"
                                      v-model="countrySelected">

                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCountry">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotels.error.required') }}</span>
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="state_id">{{ $t('hotels.state_name') }}</label>
                        <div class="col-sm-4">
                            <v-select :options="states"
                                      id="state_id"
                                      :value="form.state_id"
                                      @input="stateChange"
                                      autocomplete="true"
                                      v-model="stateSelected">

                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorState">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotels.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="city_id">{{ $t('hotels.city_name') }}</label>
                        <div class="col-sm-4">
                            <v-select :options="cities"
                                      id="city_id"
                                      :value="form.city_id"
                                      @input="cityChange"
                                      autocomplete="true"
                                      v-model="citySelected">
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCity">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotels.error.required') }}</span>
                            </div>
                        </div>
                        <label class="col-sm-2 col-form-label" for="district_id">{{ $t('hotels.district_name')
                            }}</label>
                        <div class="col-sm-4">
                            <v-select :options="districts"
                                      id="district_id"
                                      :value="form.district_id"
                                      @input="districtChange"
                                      autocomplete="true"
                                      v-model="districtSelected">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="zone_id">{{ $t('hotels.zone_name') }}</label>
                        <div class="col-sm-5">
                            <v-select :options="zones"
                                      id="zone_id"
                                      :value="form.zone_id"
                                      @input="zoneChange"
                                      autocomplete="true"
                                      v-model="zoneSelected">

                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="currency_id">{{ $t('hotels.currency_name')
                            }}</label>
                        <div class="col-sm-5">
                            <v-select :options="currencies"
                                      id="currency_id"
                                      :value="form.currency_id"
                                      @input="currencyChange"
                                      autocomplete="true"
                                      v-model="currencySelected">
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;" v-show="errorCurrency">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']" style="margin-left: 5px;"/>
                                <span>{{ $t('hotels.error.required') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('hotels.hotel_name') }}</label>
                        <div class="col-sm-5">
                            <input :class="{'form-control':true }"
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
                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('hotels.status_name') }}</label>
                        <div class="col-sm-5">
                            <c-switch :value="true" class="mx-1" color="success"
                                      v-model="form.status"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>
                </div>

                <!-- <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="form-row">
                         <label class="col-sm-2 col-form-label" for="preferential">{{$t('hotels.preferential_name')}}</label>
                          <div class="col-sm-2">
                              <input :class="{'form-control':true }"
                                id="preferential" max="9999" min="0" name="preferential" type="number" v-model="form.preferential">
                        </div>
                    </div>
                </div> -->

                <div class="b-form-group form-group" v-if="usersValidate">
                    <div class="animated fadeIn">
                        <b-tabs card>
                            <!--tabchannel dinamica-->
                            <b-tab :key="channel.id" :title="channel.name" v-for="channel in form.channels">
                                <div class="form-row container_channel_code">
                                    <label class="col-sm-2 col-form-label">
                                        {{ $t('hotels.channel_code') }}
                                    </label>
                                    <div class="col-sm-5">
                                        <input :class="{'form-control':true, 'is-invalid': form.channels[channel.id].state && (!form.channels[channel.id].code || form.channels[channel.id].code.trim() === '') && channelErrors[channel.id] }"
                                               :name="'channel_code_' + channel.id"
                                               type="text"
                                               v-model="form.channels[channel.id].code"
                                               @input="clearChannelError(channel.id)"
                                        >
                                        <div class="bg-danger container_errors" v-show="form.channels[channel.id].state && (!form.channels[channel.id].code || form.channels[channel.id].code.trim() === '') && channelErrors[channel.id]">
                                            <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                               class="margin_icon_error"/>
                                            <span>{{ $t('hotels.error.required') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <label class="col-sm-2 col-form-label" for="channel_state">{{
                                        $t('hotels.channel_state') }}</label>
                                    <div class="col-sm-5">
                                        <b-form-checkbox
                                            :id="'checkbox_'+channel.id"
                                            :name="'checkbox_'+channel.id"
                                            switch
                                            v-model="form.channels[channel.id].state"
                                            @change="clearChannelError(channel.id)"
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
                            <!--tabchannel dinamicos-->
                        </b-tabs>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="animated fadeIn">
                        <b-tabs>
                            <b-tab :title="this.$i18n.t('hotels.detail_tab')" active>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label">{{
                                            $t('hotels.category_name') }}</label>
                                        <div class="col-sm-2">
                                            <v-select :options="hotelcategories"
                                                      :value="form.hotelcategory_id"
                                                      @input="hotelcategoryChange"
                                                      autocomplete="true"
                                                      v-model="hotelcategorySelected">
                                            </v-select>
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                                 v-show="errorHotelCategory">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"/>
                                                <span>{{ $t('hotels.error.required') }}</span>
                                            </div>
                                        </div>
                                        <label class="col-sm-2 col-form-label">{{
                                            $t('hotels.hoteltype_name') }}</label>
                                        <div class="col-sm-2">
                                            <v-select :options="hoteltypes"
                                                      :value="form.hotel_type_id"
                                                      @input="hoteltypeChange"
                                                      autocomplete="true"
                                                      v-model="hoteltypeSelected">
                                            </v-select>

                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                                 v-show="errorHotelType">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"/>
                                                <span>{{ $t('hotels.error.required') }}</span>
                                            </div>
                                        </div>

                                        <label class="col-sm-2 col-form-label" for="stars">{{ $t('hotels.stars_name')
                                            }}</label>
                                        <div class="col-sm-2">
                                            <select class="form-control" id="stars" size="0" v-model="form.stars"
                                                    v-validate="'required'">
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>

                                        <!-- <label class="col-sm-2 col-form-label">{{
                                            $t('hotels.typeclass_name') }}</label>
                                        <div class="col-sm-2">
                                            <v-select :options="typesclass"
                                                      :value="form.typeclass_id"
                                                      @input="typeclassChange"
                                                      autocomplete="true"
                                                      v-model="typeclassSelected">
                                            </v-select>
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                                 v-show="errorTypeClass">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"/>
                                                <span>{{ $t('hotels.error.required') }}</span>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <!-- <label class="col-sm-2 col-form-label" for="stars">{{ $t('hotels.stars_name')
                                            }}</label>
                                        <div class="col-sm-2">
                                            <select class="form-control" id="stars" size="0" v-model="form.stars"
                                                    v-validate="'required'">
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div> -->
                                        <label class="col-sm-2 col-form-label" for="check_in_time">{{
                                            $t('hotels.checkintime_name') }}</label>
                                        <div class="col-sm-2">
                                            <input :class="{'form-control':true }"
                                                   id="check_in_time" name="check_in_time"
                                                   type="time"
                                                   v-model="form.check_in_time" v-validate="'required'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('check_in_time')"/>
                                                <span v-show="errors.has('check_in_time')">{{ errors.first('check_in_time') }}</span>
                                            </div>
                                        </div>
                                        <label class="col-sm-2 col-form-label" for="check_out_time">{{
                                            $t('hotels.checkouttime_name') }}</label>
                                        <div class="col-sm-2">
                                            <input :class="{'form-control':true }"
                                                   id="check_out_time" name="check_out_time"
                                                   type="time"
                                                   v-model="form.check_out_time" v-validate="'required'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('check_out_time')"/>
                                                <span v-show="errors.has('check_out_time')">{{ errors.first('check_out_time') }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="latitude">{{
                                            $t('hotels.latitude_name')
                                            }}</label>
                                        <div class="col-sm-2">
                                            <input :class="{'form-control':true }"
                                                   id="latitude" max="360"
                                                   min="-360" name="latitude" type="number"
                                                   v-model="form.latitude" v-validate="'required'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('latitude')"/>
                                                <span v-show="errors.has('latitude')">{{ errors.first('latitude') }}</span>
                                            </div>
                                        </div>
                                        <label class="col-sm-2 col-form-label" for="longitude">{{
                                            $t('hotels.longitude_name')
                                            }}</label>
                                        <div class="col-sm-2">
                                            <input :class="{'form-control':true }"
                                                   id="longitude" max="360"
                                                   min="-360" name="longitude" type="number"
                                                   v-model="form.longitude" v-validate="'required'">
                                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                   style="margin-left: 5px;"
                                                                   v-show="errors.has('longitude')"/>
                                                <span v-show="errors.has('longitude')">{{ errors.first('longitude') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="web_site">{{
                                            $t('hotels.website_name')
                                            }}</label>
                                        <div class="col-sm-8">
                                            <input :class="{'form-control':true }"
                                                   id="web_site" name="web_site"
                                                   pattern="https?://.+" placeholder="http://www.123.com"
                                                   type="url"
                                                   v-model="form.web_site">
                                        </div>
                                    </div>
                                </div>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="uploadFile">{{
                                            $t('hotels.hotel_image')
                                            }}</label>
                                        <div class="col-sm-5">
                                            <vue-dropzone :options="dropzoneOptions"
                                                          @vdropzone-removed-file="dropzoneRemoveFile"
                                                          @vdropzone-success="dropzoneSuccess"
                                                          id="uploadFile"
                                                          ref="uploadFile"
                                            ></vue-dropzone>
                                        </div>
                                    </div>
                                </div>

                                <b-tabs card >
                                  <b-tab :title="String(year)"  v-for="year in alerta_years" :key="year">
                                    <div class="b-form-group form-group" v-if="form.alerts[year]">
                                        <div class="form-row">
                                            <label class="col-sm-2 col-form-label" for="notes">Remarks</label>
                                            <div class="col-sm-8">
                                                <textarea :class="{'form-control':true }"
                                                          id="notes" name="notes"
                                                          cols="100"
                                                          rows="10"
                                                          v-model="form.alerts[year][currentLangAlerts].remarks"></textarea>
                                            </div>
                                            <div class="col-sm-2">
                                                <select class="form-control" required size="0"
                                                        v-model="currentLangAlerts">
                                                    <option v-bind:value="language.id" v-for="language in languages">
                                                        {{ language.iso }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="b-form-group form-group" v-if="form.alerts[year]">
                                        <div class="form-row">
                                            <label class="col-sm-2 col-form-label" for="summary">{{ $t('hotels.notes') }}</label>
                                            <div class="col-sm-8">
                                              <textarea :class="{'form-control':true }"
                                                    cols="100" id="summary"
                                                    name="summary"
                                                    rows="10"
                                                    @keyup="show_check_send_to_marketing=true"
                                                    v-model="form.alerts[year][currentLangAlerts].notes"> </textarea>
                                            </div>
                                            <div class="col-sm-2">

                                            </div>
                                        </div>
                                    </div>


                                  </b-tab>

                                </b-tabs>


                            </b-tab>
                            <b-tab :title="this.$i18n.t('hotels.description_address_tab')">
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="hotel_description">{{
                                            $t('hotels.hotel_description') }}</label>
                                        <div class="col-sm-8">
                                      <textarea :class="{'form-control':true }"
                                                cols="100" id="hotel_description"
                                                name="hotel_description"
                                                rows="10" type="text"
                                                v-model="form.translDesc[currentLang].hotel_description"> </textarea>
                                        </div>
                                        <div class="col-sm-2">
                                            <select class="form-control" required size="0"
                                                    v-model="currentLang">
                                                <option v-bind:value="language.id" v-for="language in languages">
                                                    {{ language.iso }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="b-form-group form-group">
                                    <div class="form-row">
                                        <label class="col-sm-2 col-form-label" for="hotel_address">{{
                                            $t('hotels.hotel_address') }}</label>
                                        <div class="col-sm-8">
                                      <textarea :class="{'form-control':true }"
                                                cols="100" id="hotel_address"
                                                name="hotel_address"
                                                rows="10" type="text"
                                                v-model="form.translAddr[currentLang].hotel_address"> </textarea>

                                        </div>
                                        <div class="col-sm-2">
                                            <select class="form-control" id="lang" required size="0"
                                                    v-model="currentLang">
                                                <option v-bind:value="language.id" v-for="language in languages">
                                                    {{ language.iso }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </b-tab>
                            <b-tab :title="this.$i18n.t('hotels.amenity_tab')">
                                <div>
                                    <label class="typo__label"></label>
                                    <multiselect :clear-on-select="false"
                                                 :close-on-select="false"
                                                 :multiple="true"
                                                 :options="amenitieslist"
                                                 :placeholder="this.$i18n.t('hotels.hotel_amenity')"
                                                 :preserve-search="true"
                                                 :tag-placeholder="this.$i18n.t('hotels.hotel_tag_amenity')"
                                                 :taggable="true"
                                                 @tag="addTag"
                                                 label="name"
                                                 ref="multiselect"
                                                 track-by="code"
                                                 v-model="amenitieshotel">
                                    </multiselect>
                                </div>
                            </b-tab>

                            <!-- Agergando TAB Clasificacition -->
                            <b-tab :title="this.$i18n.t('hotels.classification')">
                              <div class="pl-0">

                                <div class="col-12" style="text-align: end;">
                                  <input type="button" class="btn btn-success" :value="$t('global.buttons.addItem')"  @click="openClassification()"/>
                                </div>
                                <div class="col-12 pt-2">
                                  <table class="table table-striped">
                                    <thead>
                                      <tr>
                                        <th style="width: 10%;">{{ $t('global.table.actions') }}</th>
                                        <th style="width: 15%;">{{ $t('hotelsmanagehotelratesratescost.year') }}</th>
                                        <th style="width: 15%;">{{$t('hotels.preferential_name')}}</th>
                                        <th style="width: 15%;">Back-up</th>
                                        <th style="width: 45%;">{{ $t('hotels.typeclass_name') }}</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr v-for="(clase,i) of listClasses" >
                                        <td>
                                          <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                              <template slot="button-content">
                                                  <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                              </template>

                                              <b-dropdown-item-button class="m-0 p-0" @click="selectedTable(clase)">
                                                  <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                                  {{$t('global.buttons.edit')}}
                                              </b-dropdown-item-button>

                                              <b-dropdown-item-button @click.stop="clone(clase,i)" class="m-0 p-0">
                                                  <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                                  Clonar
                                              </b-dropdown-item-button>

                                              <b-dropdown-item-button @click.stop="eliminarItemClass(i)" class="m-0 p-0">
                                                  <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                  {{$t('global.buttons.delete')}}
                                              </b-dropdown-item-button>
                                          </b-dropdown>
                                        </td>

                                        <td class="py-2">{{ clase.year }}</td>
                                        <td class="py-2">{{ clase.preference == "1" ? "Si" : "No" }}</td>
                                        <td class="py-2">{{ clase.backup == "1" ? "Si" : "No" }}</td>
                                        <td class="py-2">
                                          <span v-for=" e in clase.dataClass">
                                            {{ clase.dataClass.length > 1 ? '('+e.label+')' : '('+e.label+')'  }}
                                          </span>
                                        </td>


                                      </tr>
                                    </tbody>
                                  </table>
                                </div>

                              </div>

                            </b-tab>
                            <!-- Fin Tab-->
                        </b-tabs>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">

                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="button" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="CancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>

        <b-modal title="Formulario Clasificación" centered ref="my-modal" size="md">
          <div class="col-12 pl-0">
            <div class="p-3 mb-2 mr-3 ml-3 bg-danger text-white" v-if="errorClase!=''">
              <span>{{ errorClase }}</span>
            </div>

            <label class="col-sm-12 col-form-label">{{ $t('hotelsmanagehotelratesratescost.year') }}</label>
            <div class="col-sm-12">
                <v-select
                  :options="years"
                  :reduce="label => label.code"
                  label="label"
                  v-model="formTypeClass.year"
                  :clearable="false"
                  autocomplete="true"

                  >
                </v-select>

            </div>


            <label class="col-sm-12 col-form-label" for="preference">{{$t('hotels.preferential_name')}}</label>
            <div class="col-sm-12">

                <c-switch :value="true" class="mx-1" color="success"
                        v-model="formTypeClass.preference"
                        variant="pill">
                </c-switch>

            </div>

            <label class="col-sm-12 col-form-label" for="backup">Back-up</label>
            <div class="col-sm-12">

                <c-switch :value="true" class="mx-1" color="success"
                        v-model="formTypeClass.backup"
                        variant="pill">
                </c-switch>

            </div>

            <label class="col-sm-12 col-form-label">{{ $t('hotels.typeclass_name') }}</label>
            <div class="col-sm-12 mb-3">
              <multiselect
                :clear-on-select="false"
                :close-on-select="false"
                :multiple="true"
                :options="typesClassList"
                placeholder=""
                :preserve-search="true"
                :tag-placeholder="this.$i18n.t('hotels.hotel_tag_clase')"
                :taggable="true"
                @tag="addTag2"
                label="label"
                track-by="code"
                v-model="formTypeClass.dataClass">
              </multiselect>

            </div>

          </div>
          <div slot="modal-footer">
              <input type="button" class="btn btn-success pl-4 pr-4" @click="agregarItemClass()" :value=" editarClase==false ? $t('global.buttons.addItem') : $t('global.buttons.edit')" />
              <input type="button" class="btn btn-warning" @click="CancelarItemClass()" :value="$t('global.buttons.cancel')" />
            </div>
        </b-modal>

        <div class="col-6" v-if="show_check_send_to_marketing">
            <label for="">
                <input type="checkbox" v-model="check_send_to_marketing"> Notificar a Marketing
            </label>
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
  import vue2Dropzone from 'vue2-dropzone'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

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
      vueDropzone: vue2Dropzone
    },
    name: 'tabs',
    data: () => {
      return {
          updateTab:1,
        images: [],
        languages: [],
        countries: [],
        states: [],
        cities: [],
        districts: [],
        zones: [],
        chains: [],
        currencies: [],
        hotelcategories: [],
        hoteltypes: [],
        typesclass: [],
        amenitieshotel: [],
        clasehotel:[],
        typesClassList:[],
        amenitieslist: [],
        translAmenity: [],
        channels: [],
        typeclassSelected: [],
        hotelcategorySelected: [],
        hoteltypeSelected: [],
        currencySelected: [],
        zoneSelected: [],
        districtSelected: [],
        citySelected: [],
        stateSelected: [],
        countrySelected: [],
        chainSelected: [],
        usersValidate:false,
        country: null,
        state: null,
        city: null,
        district: null,
        zone: null,
        chain: null,
        currency: null,
        hotelcategory: null,
        hoteltype: null,
        typeclass: null,
        hotel: null,
        countrySearch: '',
        stateSearch: '',
        citySearch: '',
        districtSearch: '',
        zoneSearch: '',
        currencySearch: '',
        hotelcategorySearch: '',
        hoteltypeSearch: '',
        typeclassSearch: '',
        showError: false,
        currentLang: '1',
        currentLangNotes: '1',
        currentLangAlerts: '1',
        invalidErrorChain: false,
        invalidErrorCountry: false,
        invalidErrorState: false,
        invalidErrorCity: false,
        invalidErrorDistrict: false,
        invalidErrorCurrency: false,
        invalidErrorHotelType: false,
        invalidErrorHotelCategory: false,
        invalidErrorTypeClass: false,
        countError: 0,
        loading: false,
        dropzoneOptions: {
          url: window.origin + '/api/galeries/image',
          cursor: 'move',
          acceptedFiles: 'image/*',
          maxFiles: 1,
          thumbnailWidth: 200,
          maxFilesize: 1,
          addRemoveLinks: true,
          dictDefaultMessage: '<i class=\'fas fa-cloud-upload\'></i> Upload File',
          headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token') }
        },
        formAction: 'post',
        chain_id: '',
        country_id: '',
        state_id: '',
        city_id: '',
        district_id: '',
        zone_id: '',
        name: '',
        currency_id: '',
        status: true,
        preferential : '',
        charged: false,
        hotel_type_id: '',
        hotelcategory_id: '',
        starts: '',
        check_in_time: '',
        check_out_time: '',
        typeclass_id: '',
        latitude: '',
        longitude: '',
        web_site: '',
        zip_code: '',
        id_image: '',
        url_image: '',
        form: {
          name:'',
          status: true,
          flag_new: true,
          chain: null,
          channels:{},
          translSummary: {
            '1': {
              'id': '',
              'summary': ''
            }
          },
          translNotes: {
            '1': {
              'id': '',
              'notes': ''
            }
          },
          translDesc: {
            '1': {
              'id': '',
              'hotel_description': ''
            }
          },
          translAddr: {
            '1': {
              'id': '',
              'hotel_address': ''
            }
          },
          alerts: '',
        },
        years:[],
        //year:(new Date()).getFullYear(),
        formTypeClass:{
          id:'',
          year:'',
          preference:'',
          backup:'',
          dataClass:[],

        },
        arrayClasses:[],
        editarClase:false,
        errorClase:'',
        arraySelected:[],
        titleClassifications:'',
        show_check_send_to_marketing: false,
        check_send_to_marketing: false,
        channelErrors: {},
      }
    },
    computed: {
       alerta_years() {
          let previousYear = moment().subtract(0, 'years').year()
          let currentYear = moment().add(2, 'years').year()

          let years = []

          do {
              years.push(previousYear)
              previousYear++
          } while (currentYear > previousYear)

          return years
      },
      errorChain: function () {
        if(this.usersValidate == true){ // no es un usuario hotel
          if (this.form.chain_id === '') {
            if (this.invalidErrorChain === false) {
              this.invalidErrorChain = true
              return false
            } else {
              return true
            }
          }
        }
      },
      errorCountry: function () {
        if (this.form.country_id === '') {
          if (this.invalidErrorCountry === false) {
            this.invalidErrorCountry = true
            return false
          } else {
            return true
          }
        }
      },
      errorState: function () {
        if (this.form.state_id === '') {
          if (this.invalidErrorState === false) {
            this.invalidErrorState = true
            return false
          } else {
            return true
          }
        }
      },
      errorCity: function () {
        if (this.form.city_id === '') {
          if (this.invalidErrorCity === false) {
            this.invalidErrorCity = true
            return false
          } else {
            return true
          }
        }
      },
      /*errorDistrict: function () {
        if (this.form.district_id == '') {
          if (this.invalidErrorDistrict == false) {
            this.invalidErrorDistrict = true
            return false
          } else {
            if (this.invalidErrorCity == true) {
              this.invalidErrorCity = true
              return false
            } else {
              return true
            }
          }
        }
      },*/
      errorCurrency: function () {
        if (this.form.currency_id === '') {
          if (this.invalidErrorCurrency === false) {
            this.invalidErrorCurrency = true
            return false
          } else {
            return true
          }
        }
      },
      errorHotelType: function () {
        if (this.form.hotel_type_id === '') {
          if (this.invalidErrorHotelType === false) {
            this.invalidErrorHotelType = true
            return false
          } else {
            return true
          }
        }
      },
      errorHotelCategory: function () {
        if (this.form.hotelcategory_id === '') {
          if (this.invalidErrorHotelCategory === false) {
            this.invalidErrorHotelCategory = true
            return false
          } else {
            return true
          }
        }
      },
      errorTypeClass: function () {
        if (this.form.typeclass_id === '') {
          if (this.invalidErrorTypeClass === false) {
            this.invalidErrorTypeClass = true
            return false
          } else {
            return true
          }
        }
      },
      listClasses: function (){
        return  _.sortBy(this.arrayClasses, 'year');
      }

    },
    created:function(){

        this.filter();
        //channel
        API.get('/channels/selectBox').then((result) => {
            let channels = result.data.data

            channels.forEach((channel) => {
                this.form.channels[channel.value] = {
                    id: channel.value,
                    name: channel.text,
                    code: '',
                    state: channel.value === 1,
                    type: ""
                }
            })
        }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelrooms.error.messages.name'),
                text: this.$t('hotelsmanagehotelrooms.error.messages.connection_error')
            })
        })
    },
    mounted: function () {

      // this.cleanFields()

      API.get('/languages/')
        .then((result) => {
          this.languages = result.data.data
          this.currentLang = result.data.data[0].id
          this.currentNotes = result.data.data[0].id
          let form = {}

          let langHotelDesc = this.languages
          let langHotelAddr = this.languages

          langHotelDesc.forEach((value) => {
            this.form.translSummary[value.id] = {
              id: '',
              summary: ''
            }
            this.form.translNotes[value.id] = {
              id: '',
              notes: ''
            }
            this.form.translDesc[value.id] = {
              id: '',
              hotel_description: ''
            }
          })

          langHotelAddr.forEach((value) => {
            this.form.translAddr[value.id] = {
              id: '',
              hotel_address: ''
            }
          })
          //countries
          API.get('/country/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let paises = result.data.data
              paises.forEach((country) => {
                this.countries.push({
                  label: country.translations[0].value,
                  code: country.translations[0].object_id
                })
              })

            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
          //chain
          API.get('chain/selectbox')
            .then((result) => {
              this.chains = result.data.data
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
          //currency
          API.get('/currency/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {

              let monedas = result.data.data
              monedas.forEach((currency) => {
                this.currencies.push({
                  label: currency.translations[0].value,
                  code: currency.translations[0].object_id
                })
              })
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
          //hotelcategory
          API.get('/hotelcategory/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let hotelcategoria = result.data.data
              hotelcategoria.forEach((hotelcat) => {
                this.hotelcategories.push({
                  label: hotelcat.translations[0].value,
                  code: hotelcat.translations[0].object_id
                })
              })

            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
          //hoteltype
          API.get('/hotel_type/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let hoteltipo = result.data.data
              hoteltipo.forEach((hoteltype) => {
                this.hoteltypes.push({
                  label: hoteltype.translations[0].value,
                  code: hoteltype.translations[0].object_id
                })
              })

            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
          //typeclass
          API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let tipos = result.data.data
              tipos.forEach((types) => {
                this.typesclass.push({
                  label: types.translations[0].value,
                  code: types.translations[0].object_id
                })
              })

            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })

          if (this.$route.params.id !== undefined) {
            API.get('/hotels/' + this.$route.params.id + '?lang=' + localStorage.getItem('lang'))
              .then((result) => {

                form.hotel = result.data.data
                this.formAction = 'put'

                this.form.chain_id = form.hotel.chains.id
                this.chainSelected.push({
                  code: form.hotel.chains.id,
                  label: form.hotel.chains.name
                })

                this.form.country_id = form.hotel.country_id
                this.countrySelected.push({
                  code: form.hotel.country_id,
                  label: form.hotel.country.translations[0].value
                })

                this.form.state_id = form.hotel.state_id
                this.stateSelected.push({
                  code: form.hotel.state_id,
                  label: form.hotel.state.translations[0].value
                })

                this.form.city_id = form.hotel.city_id
                this.citySelected.push({
                  code: form.hotel.city_id,
                  label: form.hotel.city.translations[0].value
                })

                this.form.district_id = form.hotel.district_id
                if (this.form.district_id != null) {
                  this.districtSelected.push({
                    code: form.hotel.district_id,
                    label: form.hotel.district.translations[0].value
                  })
                }

                this.form.zone_id = form.hotel.zone_id
                if (this.form.zone_id != null) {
                  this.zoneSelected.push({
                    code: form.hotel.zone_id,
                    label: form.hotel.zone.translations[0].value
                  })
                }

                this.form.name = form.hotel.name
                this.form.currency_id = form.hotel.currency_id
                this.currencySelected.push({
                  code: form.hotel.currency_id,
                  label: form.hotel.currency.translations[0].value
                })

                this.form.status = (form.hotel.status === 1)
                this.form.flag_new = (form.hotel.flag_new === 1)
                this.form.preferential = form.hotel.preferential;

                this.form.hotel_type_id = form.hotel.hotel_type_id
                this.hoteltypeSelected.push({
                  code: form.hotel.hotel_type_id,
                  label: form.hotel.hoteltype.translations[0].value
                })

                this.form.hotelcategory_id = form.hotel.hotelcategory_id
                this.hotelcategorySelected.push({
                  code: form.hotel.hotelcategory_id,
                  label: form.hotel.hotelcategory && form.hotel.hotelcategory.translations
                  ? form.hotel.hotelcategory.translations[0].value
                  : null
                })

                // this.form.typeclass_id = form.hotel.typeclass_id
                // this.typeclassSelected.push({
                //   code: form.hotel.typeclass_id,
                //   label: form.hotel.typeclass.translations[0].value
                // })

                this.form.stars = form.hotel.stars || '0'
                this.form.check_in_time = form.hotel.check_in_time.substring(0, 5)
                this.form.check_out_time = form.hotel.check_out_time.substring(0, 5)

                this.form.latitude = form.hotel.latitude
                this.form.longitude = form.hotel.longitude
                this.form.web_site = form.hotel.web_site
                this.form.zip_code = form.hotel.zip_code
                //this.form.notes = form.hotel.notes


                //amenity
                let arrayAuxAmenity = form.hotel.amenity
                let j = 0
                let argData = []
                arrayAuxAmenity.forEach((amenities) => {
                  argData[j] = {
                    code: amenities.translations[0].object_id,
                    name: amenities.translations[0].value
                  }
                  j++
                })

                this.amenitieshotel = argData
                this.form.amenitieshotel = argData

                //Clase

                let showPreferentials = form.hotel.hotelpreferentials
                let showBackups = form.hotel.hotelbackups
                let hoteltypeclass = form.hotel.hoteltypeclass
                let acum = 0;
                let dataClass = []


                showPreferentials.forEach((e, i) => {
                  hoteltypeclass.forEach(x=>{
                    if(e.year == x.year){
                      dataClass.push({code:x.typeclass_id, label:x.translations[0].value})
                    }
                  });

                  let backupItem = showBackups.find(b => b.year == e.year)

                  this.arrayClasses.push({dataClass});
                  this.arrayClasses[i].year=e.year
                  this.arrayClasses[i].preference = e.value == "1" ? true : false
                  this.arrayClasses[i].backup = backupItem ? backupItem.value == "1" : false,
                  dataClass = []
                });




                // this.clasehotel = argData2
                // this.formTypeClass.dataClass = argData2
                //this.arrayClasses = showPreferentials.hoteltypeclass

                //address and description
                let arraytranslDesc = form.hotel.translations
                let arraytranslSummary = form.hotel.translations
                let arraytranslAddr = form.hotel.translations
                let arraytranslNotes = form.hotel.translations

                arraytranslDesc.forEach((translation) => {
                  if (translation.slug === 'hotel_description') {
                    this.form.translDesc[translation.language_id] = {
                      id: translation.id,
                      hotel_description: translation.value
                    }
                  }
                })

                arraytranslSummary.forEach((translation) => {
                  if (translation.slug === 'summary') {
                    this.form.translSummary[translation.language_id] = {
                      id: translation.id,
                      summary: translation.value
                    }
                  }
                })

                arraytranslNotes.forEach((translation) => {
                  if (translation.slug === 'notes') {
                    this.form.translNotes[translation.language_id] = {
                      id: translation.id,
                      notes: translation.value
                    }
                  }
                })

                // form.hotel.alerts.forEach((row) => {
                //     this.form.alerts[row.year][row.language_id] = {
                //       id: row.id,
                //       year: row.year,
                //       remarks: row.remarks,
                //       notes: row.notes,
                //       language_id: row.language_id
                //     }
                // })
                // console.log(this.form.alerts);
                console.log(result.data.form_alerts);
                this.form.alerts = result.data.form_alerts

                arraytranslAddr.forEach((translation) => {
                  if (translation.slug === 'hotel_address') {
                    this.form.translAddr[translation.language_id] = {
                      id: translation.id,
                      hotel_address: translation.value
                    }
                  }
                })
                //channels
                let arrayChannels = form.hotel.channels
                //if (this.$refs.tabChannel !== undefined) {

                    arrayChannels.forEach((channel) => {
                      this.form.channels[channel.id].code = channel.pivot.code
                      this.form.channels[channel.id].state = !!channel.pivot.state
                      this.form.channels[channel.id].type = channel.pivot.type
                    })

               // }

                //image logo
                if (form.hotel.galeries.length > 0) {
                  this.form.images = form.hotel.galeries[0]
                  this.url_image = form.hotel.galeries[0].url
                  this.id_image = form.hotel.galeries[0].id

                  let imageObj = {
                    name: 'galeries/' + this.url_image + '?' + Date.now(),
                    size: 1176,
                    type: 'image/png'
                  }
                  if (this.$refs.uploadFile !== undefined) {
                    this.$refs.uploadFile.manuallyAddFile(imageObj,
                      '/images/galeries/' + this.url_image + '?' + Date.now())
                  }
                }
                //load ubigeo

                this.loadDistrict(this.form.city_id)
                this.loadZone(this.form.city_id)
                this.loadCity(this.form.state_id)
                this.loadState(this.form.country_id)



              })
          }else{
            this.form.alerts = this.alertextructure()
          }

          //amenities
          API.get('/amenities/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let arraytranslAmenity = result.data.data
              let i = 0

              arraytranslAmenity.forEach((amenities) => {
                this.amenitieslist[i] = {
                  code: amenities.translations[0].object_id,
                  name: amenities.translations[0].value
                }
                i++
              })
              console.log(this.amenitieslist,'1');
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })

          //typesClassList
          API.get('/typeclass/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              let tipos = result.data.data
              let tipoData = Object.values(tipos)
              let i = 0

              tipoData.forEach((types, e) => {
                types.translations.forEach((trans, y) => {

                  this.typesClassList.push({
                    label: trans.value,
                    code: trans.object_id
                  })
                  i++
                });

              })
              this.dateClasses();

            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
        //FIN DE Classifications
        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('hotels.error.messages.name'),
          text: this.$t('hotels.error.messages.connection_error')
        })

      })


    },



    methods: {
      clone(clase, i){

          const max = this.arrayClasses.reduce(function(prev, current) {
              return (prev.year > current.year) ? prev : current
          })

          let row = {
              year: parseInt(max.year) + 1,
              preference: clase.preference,
              dataClass: clase.dataClass,
              selected:true
          }
          this.arrayClasses.push(row);
      },
      alertextructure(){

        let data = {};

        this.alerta_years.forEach((year) => {

            let trans = {}
            this.languages.forEach((language) => {
              trans[language.id] = {
                'id': '',
                'remarks': '',
                'notes': ''
              }
            })
            data[year] = trans
        })

        return data;
      },
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
      dropzoneSuccess: function (file, response) {
        this.charged = true
        this.images = []
        this.images.push(response.timestamp)
      },
      chainChange: function (value) {
        this.chain = value
        if (this.chain != null) {
          this.form.chain_id = this.chain.code
        } else {
          this.form.chain_id = ''
        }

      },
      countryChange: function (value) {
        this.country = value
        if (this.country != null) {
          if (this.form.country_id !== this.country.code) {
            this.form.state_id = ''
            this.form.city_id = ''
            this.form.zone_id = ''
            this.form.district_id = ''
            this.zoneSelected = []
            this.districtSelected = []
            this.citySelected = []
            this.stateSelected = []
          }
          this.form.country_id = this.country.code
          this.loadState(this.country.code)
        } else {
          this.form.zone_id = ''
          this.form.district_id = ''
          this.zoneSelected = []
          this.districtSelected = []
          this.citySelected = []
          this.stateSelected = []
        }
      },
      stateChange: function (value) {
        this.state = value
        if (this.state != null) {
          if (this.form.state_id !== this.state.code) {
            this.form.city_id = ''
            this.form.zone_id = ''
            this.form.district_id = ''
            this.zoneSelected = []
            this.districtSelected = []
            this.citySelected = []
          }
          this.form.state_id = this.state.code
          this.loadCity(this.state.code)
        } else {
          this.form.zone_id = ''
          this.form.district_id = ''
          this.zoneSelected = []
          this.districtSelected = []
          this.citySelected = []
        }
      },
      cityChange: function (value) {

        this.city = value
        if (this.city != null) {
          if (this.form.city_id !== this.city.code) {
            this.form.zone_id = ''
            this.form.district_id = ''
            this.zoneSelected = []
            this.districtSelected = []
          }
          this.form.city_id = this.city.code
          if (this.city.code > 0) {
            this.loadDistrict(this.city.code)
            this.loadZone(this.city.code)
          }
        } else {
          this.form.zone_id = ''
          this.form.district_id = ''
          this.zoneSelected = []
          this.districtSelected = []
        }
      },
      districtChange: function (value) {
        this.district = value
        if (this.district != null) {
          this.form.district_id = this.district.code
        } else {
          this.form.district_id = ''
        }
      },
      zoneChange: function (value) {
        this.zone = value
        if (this.zone != null) {
          this.form.zone_id = this.zone.code
        } else {
          this.form.zone_id = ''
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
      hotelcategoryChange: function (value) {
        this.hotelcategory = value
        if (this.hotelcategory != null) {
          this.form.hotelcategory_id = this.hotelcategory.code
        } else {
          this.form.hotelcategory_id = ''
        }
      },
      hoteltypeChange: function (value) {
        this.hoteltype = value
        if (this.hoteltype != null) {
          this.form.hotel_type_id = this.hoteltype.code
        } else {
          this.form.hotel_type_id = ''
        }
      },
      typeclassChange: function (value) {
        this.typeclass = value
        if (this.typeclass != null) {
          this.form.typeclass_id = this.typeclass.code
        } else {
          this.form.typeclass_id = ''
        }
      },
      //LOADUBIGEO
      loadDistrict (codecity) {
        this.districts = []

        API.get('/district/getdistricts/' + codecity + '/' + localStorage.getItem('lang'))
          .then((result) => {
            let distritos = result.data.data
            distritos.forEach((district) => {
              this.districts.push({
                label: district.translations[0].value,
                code: district.translations[0].object_id
              })
            })

          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.error.messages.name'),
            text: this.$t('hotels.error.messages.connection_error')
          })
        })
      },
      loadZone (codecity) {
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
            title: this.$t('hotels.error.messages.name'),
            text: this.$t('hotels.error.messages.connection_error')
          })
        })
      },
      loadCity (codestate) {
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
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
        }
      },
      loadState (codecountry) {
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
              title: this.$t('hotels.error.messages.name'),
              text: this.$t('hotels.error.messages.connection_error')
            })
          })
        }
      },
      //delete image
      dropzoneRemoveFile () {
        if (this.id_image !== '') {
          API.delete('/hotel/logo/image/' + this.id_image)
            .then((result) => {
              if (result.data.success === false) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotels.hotel_name'),
                  text: this.$t('hotels.error.messages.connection_error')
                })
              }
            })
          this.id_image = ''
        }
      },
      addTag (newTag) {
        const tag = {
          name: newTag,
          code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
        }
        this.amenitieslista.push(tag)
        this.amenitieshotel.push(tag)
        console.log(this.amenitieshotel,'33')
      },
      addTag2 (newTag2) {
        const tag2 = {
          name: newTag2,
          code: newTag2.substring(0, 2) + Math.floor((Math.random() * 10000000))
        }
        this.claselista.push(tag2)
        this.formTypeClass.dataClass.push(tag2)

      },
      CancelForm () {
        this.id_image = ''
        this.$router.push({ path: '/hotels/list' })
      },
      validateBeforeSubmit () {
        if(this.usersValidate == true){ // no es un usuario hotel
          if ((this.form.chain_id == null && this.formAction === 'post')) {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.hotel_name'),
              text: this.$t('hotels.error.messages.hotel_chain_complete')
            })
            return false
          }
        }

        if ((this.country == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_country_complete')
          })
          return false
        }

        if ((this.state == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_state_complete')
          })
          return false
        }

        if ((this.city == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_city_complete')
          })
          return false
        }

        if ((this.currency == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_currency_complete')
          })
          return false
        }

        if ((this.hoteltype == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_hoteltype_complete')
          })
          return false
        }

        if ((this.hotelcategory == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_category_complete')
          })
          return false
        }
        /*
        if ((this.typeclass == null && this.formAction === 'post')) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_typeclass_complete')
          })
          return false
        }
        */
        if(this.arrayClasses.length == 0 && this.formAction === 'post'){
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.hotel_typeclass_complete')
          })
          return false
        }

        // Validar channels activos
        this.channelErrors = {}
        let channelError = false
        for (let channelId in this.form.channels) {
          if (this.form.channels[channelId].state && (!this.form.channels[channelId].code || this.form.channels[channelId].code.trim() === '')) {
            this.channelErrors[channelId] = true
            channelError = true
          } else {
            this.channelErrors[channelId] = false
          }
        }

        if (channelError) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotels.hotel_name'),
            text: this.$t('hotels.error.messages.information_complete')
          })
          this.loading = false
          return false
        }

        this.$validator.validateAll().then((result) => {
          if (result) {

            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('hotels.hotel_name'),
              text: this.$t('hotels.error.messages.information_complete')
            })
            this.loading = false
          }
        })
      },
      submit () {
        if(this.usersValidate == true){ // no es un usuario hotel
          if ((this.chain == null) && this.formAction === 'put' && this.form.chain_id !== '') {

          } else {
            this.form.chain_id = this.chain.code
          }
        }
        //country
        if ((this.country == null) && this.formAction === 'put' && this.form.country_id !== '') {

        } else {
          this.form.country_id = this.country.code
        }
        //state
        if ((this.state == null) && this.formAction === 'put' && this.form.state_id !== '') {

        } else {
          this.form.state_id = this.state.code
        }
        //city
        if ((this.city == null) && this.formAction === 'put' && this.form.city_id !== '') {

        } else {
          this.form.city_id = this.city.code
        }
        //district
        if ((this.district == null) && this.formAction == 'put' && this.form.district_id != '') {

        } else {
          //this.form.district_id = this.district.code ? this.district.code : null
            if (this.district == null) {
                this.form.district_id = null
            } else {
                this.form.district_id = this.district.code
            }
        }

          //zone
          if ((this.zone == null) && this.formAction == 'put' && this.form.zone_id != '') {

          } else {
              //this.form.zone_id = this.zone.code ? this.zone.code : null

              if (this.zone == null) {
                  this.form.zone_id = null
              } else {
                  this.form.zone_id = this.zone.code
              }
          }

        //currency
        if ((this.currency == null) && this.formAction === 'put' && this.form.currency_id !== '') {

        } else {
          this.form.currency_id = this.currency.code
        }
        //hoteltype
        if ((this.hoteltype == null) && this.formAction === 'put' && this.form.hotel_type_id !== '') {

        } else {
          this.form.hotel_type_id = this.hoteltype.code
        }
        //hotelcategory
        if ((this.hotelcategory == null) && this.formAction === 'put' && this.form.hotelcategory_id !== '') {

        } else {
          this.form.hotelcategory_id = this.hotelcategory.code
        }
        //typeclass
        // if ((this.typeclass == null) && this.formAction === 'put' && this.form.typeclass_id !== '') {

        // } else {
        //   this.form.typeclass_id = this.typeclass.code
        // }

        this.form.classification_preference = this.arrayClasses;

        //status
        if (this.formAction !== 'put') {
          this.form.status = (this.form.status === false ? 0 : 1)
          this.form.flag_new = (this.form.flag_new === false ? 0 : 1)
        }
        //amenities
        let varable = this.amenitieshotel
        let argData = []
        varable.forEach((amenity) => {
          argData.push(amenity.code)
        })
        this.form.translAmenity = argData

        this.form.send_to_mkt = (this.check_send_to_marketing) ? 1 : 0

        this.loading = true

        let url = 'hotels'
        if(this.$route.params.id !== undefined){
          url = url + "/" + this.$route.params.id
        }

        API({
          method: this.formAction,
          url: url ,
          data: this.form
        })
          .then(async (result) => {

            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotels.hotel_name'),
                text: this.$t('hotels.error.messages.hotel_incorrect')
              })

              this.loading = false
            } else {

              if (this.charged === true) {
                this.images.forEach(async (image, key) => {
                  this.position = 0
                  let formImagen = {
                    image: image,
                    type: 'hotel',
                    object_id: result.data.object_id,
                    url: '',
                    slug: 'hotel_logo',
                    position: this.position,
                    state: true
                  }

                  await API({
                    method: 'put',
                    url: 'hotel/gallery/logo/',
                    data: formImagen
                  }).then((result) => {
                    if (result.data.success === false) {
                      this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotels.hotel_name'),
                        text: this.$t('hotels.error.messages.gallery_incorrect')
                      })

                    }
                  })
                })
              } else {
                this.charged = false
                this.id_image = ''
              }


              if(this.$route.params.id === undefined){
                if (this.charged === true) {
                  setTimeout(() => {
                    this.$router.push({ path: '/hotels/edit/' + result.data.object_id })
                    location.reload()
                  }, 1000)

                }else{
                  this.$router.push({ path: '/hotels/edit/' + result.data.object_id })
                  location.reload()
                }
                this.loading = false

              }else{
                this.$notify({
                  group: 'main',
                  type: 'success',
                  title: this.$t('hotels.hotel_name'),
                  text: 'Updated successfully'
                })
                this.loading = false
              }



            }
          })


      },

      dateClasses(){

        let n = (new Date()).getFullYear();
        this.years=[]

        for(let x = n-2; x < n+3 ; ++x) {
          this.years.push({code:x, label:x},)
        }

      },

      agregarItemClass(){
        let acum = 0;
        let value2 = false;
        let value = false;
        if(this.formTypeClass.year ==''  || this.formTypeClass.dataClass.length ==0){

          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.hotels'),
            text: 'Debe llenar todos los campos!!'
          })

        }else{

          if(this.editarClase == true){
            this.arrayClasses.forEach((e, i) => {
              if(this.formTypeClass.year == e.year && this.formTypeClass.id != i){
                acum = acum + 1
                i=0;
              }
            });

            if(acum==1){

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.hotels'),
                text: 'Año ya registrado'
              })
              acum = 0;

            }else{
              this.arrayClasses.forEach((e,i) => {
                if(this.formTypeClass.id == i ){
                  e.id='';
                  e.year = '';
                  e.preference = '';
                  e.backup = '';
                  e.dataClass=[];

                  e.year = this.formTypeClass.year;
                  e.preference = this.formTypeClass.preference;
                  e.backup = this.formTypeClass.backup;
                  e.dataClass=this.formTypeClass.dataClass;
                  this.$refs['my-modal'].hide()
                  this.limpiarTable();
                  acum = 0;
                  i=0;
                }
              });
            }

          }else{

            value = this.arrayClasses.some(clase => clase.year == this.formTypeClass.year);

            if(value){

              value2 = false;
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.hotels'),
                text: 'Año ya registrado'
              })

            }else{
              this.arrayClasses.push({

                year:this.formTypeClass.year,
                preference:this.formTypeClass.preference,
                backup:this.formTypeClass.backup,
                dataClass:this.formTypeClass.dataClass,
              });
              this.$refs['my-modal'].hide()
              this.limpiarTable();
            }

            value = false;
            }
        }

      },

      selectedTable(tbl){

        this.editarClase = true
        this.$refs['my-modal'].show()
        this.arrayClasses.forEach((data,i) => {
          data.selected = false
          if(tbl.year == data.year){
              data.selected = true

              this.formTypeClass.id = i;
              this.formTypeClass.year = data.year;
              this.formTypeClass.preference= data.preference;
              this.formTypeClass.backup= data.backup;
              this.formTypeClass.dataClass = data.dataClass;
          }
        });
      },

      limpiarTable(){
        this.formTypeClass.id= '';
        this.formTypeClass.year = '';
        this.formTypeClass.preference='';
        this.formTypeClass.backup='';
        this.formTypeClass.dataClass=[];
        this.editarClase=false;
      },

      CancelarItemClass(){
        this.$refs['my-modal'].hide()
        this.arrayClasses.forEach(e => {
          if(e.selected == true){
            e.selected = false;
          }
        })

        this.limpiarTable();
      },

      eliminarItemClass(row){
        const data = this.arrayClasses.filter((clase, i) => row != i);
        this.arrayClasses = data;
        this.limpiarTable();

      },

      openClassification(){
        this.limpiarTable()
        this.$refs['my-modal'].show()

      },

      clearChannelError(channelId) {
        if (this.channelErrors[channelId]) {
          this.$set(this.channelErrors, channelId, false)
        }
      },

    }
  }
</script>
<style scoped>
.active_selected {
  color: white;
  background-color: #BD0D12 !important;

}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css">

</style>

