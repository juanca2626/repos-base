<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div>
            <div class="container-fluid">
                <div class="row cost-table-container">
                    <b-tabs style="width: 100%;">
                        <b-tab>
                            <template #title>
                                <i class="fas fa-file-invoice"></i> Datos de Tarifa
                            </template>
                            <div class="container">
                                <form class="container">
                                    <div class="row">
                                        <div class="b-form-group form-group col-6">
                                            <div class="form-row">
                                                <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name')
                                                    }}</label>
                                                <div class="col-sm-10">
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
                                                                           style="margin-left: 5px;"
                                                                           v-show="errors.has('name')"/>
                                                        <span
                                                            v-show="errors.has('name')">{{ errors.first('name') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-form-group form-group col-6">
                                            <div class="form-row">
                                                <label class="col-sm-4 col-form-label" for="commercial_name">
                                                    {{ $t('services.name_commercial')}}
                                                </label>
                                                <div class="col-sm-6">
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
                                                        <span
                                                            v-show="errors.has('commercial_name')">{{ errors.first('commercial_name') }}</span>
                                                    </div>
                                                </div>
                                                <select class="col-sm-2 form-control" id="lang" required size="0"
                                                        v-model="currentLang">
                                                    <option v-bind:value="language.id" v-for="language in languages">
                                                        {{ language.iso }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <table class="table">
                                        <thead>
                                        <tr>
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
                                                <span class="">Venta anticipada</span>
                                            </th>
                                            <th class="small-title">
                                                <span class="">Promoción</span>
                                            </th>
                                            <th class="small-title">
                                                <span class="">Margen de Protección (%)</span>
                                            </th>
                                            <th class="small-title">
                                                <span class="">Tarifa Dinamica</span>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td class="py-2">
                                                <b-form-select
                                                    :disabled="step === 2"
                                                    :options="rateTypes"
                                                    :state="validateState('ratesTypes')"
                                                    ref="ratesTypes"
                                                    v-model="form.type"
                                                    v-validate="{ required: true }">
                                                </b-form-select>
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.allotment"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.rate"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.taxes"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.services"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.advance_sales"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.promotions"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.flag_process_markup"
                                                    variant="pill"
                                                />
                                            </td>
                                            <td class="py-2">
                                                <c-switch
                                                    :disabled="step === 2"
                                                    :uncheckedValue="false"
                                                    :value="true"
                                                    class="mx-1"
                                                    color="success"
                                                    v-model="form.price_dynamic"
                                                    variant="pill"
                                                />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!-- div class="row table-options">
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
                                            <span class="">Venta Anticipada</span>
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
                                        <div class="col-1 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.allotment"
                                                variant="pill"
                                            />
                                        </div>
                                        <div class="col-1 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.rate"
                                                variant="pill"
                                            />
                                        </div>
                                        <div class="col-2 pt-0 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.taxes"
                                                variant="pill"
                                            />
                                        </div>
                                        <div class="col-2 pt-0 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.services"
                                                variant="pill"
                                            />
                                        </div>
                                        <div class="col-2 pt-0 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.advance_sales"
                                                variant="pill"
                                            />
                                        </div>
                                        <div class="col-2 pt-0 mt-3">
                                            <c-switch
                                                :uncheckedValue="false"
                                                :value="true"
                                                class="mx-1"
                                                color="success"
                                                v-model="form.promotions"
                                                variant="pill"
                                            />
                                        </div>

                                    </div -->
                                    <div class="row">
                                        <div class="col-12">
                                            <hr/>
                                        </div>
                                    </div>
                                    <div class="row text-right mt-3">
                                        <div class="col-12">
                                            <img src="/images/loading.svg" v-if="loading" width="40px"/>
                                            <button @click="submit(true)" class="btn btn-success" type="button"
                                                    v-if="!loading">
                                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                                {{$t('global.buttons.submit')}}
                                            </button>
                                            <router-link to="../" v-if="!loading">
                                                <button class="btn btn-danger" type="reset">
                                                    {{$t('global.buttons.cancel')}}
                                                </button>
                                            </router-link>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </b-tab>
                        <b-tab v-if="this.$route.name != 'RatesRatesCostForm' && rate_plan_id != undefined">
                            <template #title>
                                <i class="fas fa-file-invoice"></i> Plan Tarifario
                            </template>
                            <div class="container">
                                <b-navbar type="dark" variant="light">
                                    <b-navbar-nav>
                                        <b-nav-item href="#">
                                            <button type="button" class="btn btn-success" @click="showFormNewRatePlan"
                                                    :disabled="newOrEditRatePlan">
                                                Nuevo Plan Tarifario
                                            </button>
                                        </b-nav-item>
                                        <b-nav-item href="#" v-if="!hasClient">
                                            <button type="button" class="btn btn-success" @click="showFormEditProvider"
                                                    :disabled="editProvider">
                                                Editar Proveedor / Políticas
                                            </button>
                                        </b-nav-item>
                                    </b-navbar-nav>
                                </b-navbar>
                                <!-- NUEVAS TARIFAS-->
                                <div class="row" v-if="historyData.length > 0">
                                    <div class="col-2 text-right offset-5 my-auto">
                                        Tarifas Anteriores
                                    </div>
                                    <div class="col-5">
                                        <v-select
                                            :options="historyData"
                                            @input="setHistory"
                                            autocomplete="true"
                                            id="history"
                                            v-model="historySearch">
                                        </v-select>
                                    </div>
                                    <div class="col-12">
                                        <hr/>
                                    </div>
                                </div>
                                <div class="row mt-4" v-if="newOrEditRatePlan">
                                    <div class="b-form-group form-group col-6" v-if="!hasClient">
                                        <div class="form-row" style="margin-bottom: 5px;">
                                            <label class="col-sm-2 col-form-label" for="provider">
                                                {{ $t('servicesmanageservicerates.provider') }}
                                            </label>
                                            <div class="col-sm-10">
                                                <v-select :options="providers"
                                                          :value="formTwo.user_id"
                                                          label="name"
                                                          :filterable="false"
                                                          @search="onSearchProvider"
                                                          @input="providerChange"
                                                          id="provider"
                                                          name="provider"
                                                          v-model="providerSelected"
                                                          v-validate="'required'">
                                                    <template slot="option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                    <template slot="selected-option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                </v-select>
                                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;"
                                                     v-show="customErrors.provider">
                                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                                       style="margin-left: 5px;"/>
                                                    <span>{{ $t('servicesmanageservicerates.error.required') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="b-form-group form-group col-2">
                                        <label class="col-form-label">{{
                                            $t('servicesmanageservicerates.date_range')}}</label>
                                    </div>
                                    <div class="b-form-group form-group col-4">
                                        <div class="row">
                                            <div class="input-group col-6">
                                                <date-picker
                                                    id="dates_from"
                                                    name="dates_from"
                                                    :config="datePickerFromOptions"
                                                    @dp-change="setDateFrom"
                                                    placeholder="inicio: DD/MM/YYYY"
                                                    ref="datePickerFrom"
                                                    v-model="formTwo.dates_from"
                                                    v-validate="{ required: true }"
                                                >
                                                </date-picker>
                                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                    <font-awesome-icon
                                                        :icon="['fas', 'exclamation-circle']"
                                                        style="margin-left: 5px;"
                                                        v-show="errors.has('dates_from')"/>
                                                    <span v-show="errors.has('dates_from')">{{ errors.first('dates_from') }}</span>
                                                </div>
                                            </div>
                                            <div class="input-group col-6">
                                                <date-picker
                                                    id="dates_to"
                                                    name="dates_to"
                                                    :config="datePickerToOptions"
                                                    placeholder="fin: DD/MM/YYYY"
                                                    ref="datePickerTo"
                                                    v-model="formTwo.dates_to"
                                                    v-validate="{ required: true }"
                                                >
                                                </date-picker>
                                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                    <font-awesome-icon
                                                        :icon="['fas', 'exclamation-circle']"
                                                        style="margin-left: 5px;"
                                                        v-show="errors.has('dates_to')"/>
                                                    <span
                                                        v-show="errors.has('dates_to')">{{ errors.first('dates_to') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="newOrEditRatePlan">
                                    <div class="b-form-group form-group col-6" v-if="!hasClient">
                                        <div class="form-row" style="margin-bottom: 5px;">
                                            <label class="col-sm-2 col-form-label" for="policy">{{
                                                $t('servicesmanageservicerates.policy_name') }}</label>
                                            <div class="col-sm-10">
                                                <v-select :options="policies"
                                                          label="name"
                                                          :value="formTwo.policy_id"
                                                          :filterable="true"
                                                          id="policy"
                                                          name="policy"
                                                          v-model="policySelected"
                                                          v-validate="'required'">
                                                    <template slot="option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                    <template slot="selected-option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                </v-select>
                                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                                    <font-awesome-icon
                                                        :icon="['fas', 'exclamation-circle']"
                                                        style="margin-left: 5px;"
                                                        v-show="errors.has('policy')"/>
                                                    <span
                                                        v-show="errors.has('policy')">{{ errors.first('policy') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3 add" v-if="paxs && newOrEditRatePlan">
                                    <div class="col-12">
                                        <div class="rooms-table row rooms-table-headers">
                                            <div class="col-3 my-auto" v-if="!hasClient">
                                                Política de Cancelación
                                            </div>
                                            <div class="col-4 my-auto">
                                                {{$t('servicesmanageservicerates.range')}}
                                            </div>
                                            <div class="col-1">
                                                {{$t('servicesmanageservicerates.adult')}} US$
                                            </div>
                                            <div class="col-1">
                                                {{$t('servicesmanageservicerates.child')}} US$
                                            </div>
                                            <div class="col-1">
                                                {{$t('servicesmanageservicerates.infant')}} US$
                                            </div>
                                            <div class="col-1">
                                                {{$t('servicesmanageservicerates.guide')}} US$
                                            </div>
                                            <div class="col-1">
                                            </div>
                                        </div>
                                        <div :key="pax.id" class="rooms-table row" v-for="(pax, index) in currentPaxs">
                                            <div class="col-3 my-auto" v-if="!hasClient">
                                                <v-select :options="policies"
                                                          label="name"
                                                          :value="pax.service_cancellation_policy_id"
                                                          :filterable="true"
                                                          v-model="pax.service_cancellation_policy_selected">
                                                    <template slot="option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                    <template slot="selected-option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.name }}
                                                        </div>
                                                    </template>
                                                </v-select>
                                            </div>
                                            <div class="col-4 my-auto">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input :id="'pax-'+pax.id+'-pax_from'"
                                                               :name="'pax-'+pax.id+'-pax_from'"
                                                               class="form-control"
                                                               step="1"
                                                               min="1"
                                                               :disabled="hasClient"
                                                               v-model="currentPaxs[index].pax_from"
                                                               type="number"/>
                                                    </div>
                                                    <div class="col-4">
                                                        {{$t('servicesmanageservicerates.to')}}
                                                    </div>
                                                    <div class="col-4">
                                                        <input :id="'pax-'+pax.id+'-pax_to'"
                                                               :name="'pax-'+pax.id+'-pax_to'"
                                                               class="form-control"
                                                               step="1"
                                                               min="1"
                                                               :disabled="hasClient"
                                                               v-model="currentPaxs[index].pax_to"
                                                               type="number"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <input :id="'pax-'+pax.id+'-adult'"
                                                       :name="'pax-'+pax.id+'-adult'"
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number"
                                                       v-model="currentPaxs[index].adult"/>
                                            </div>
                                            <div class="col-1">
                                                <input :id="'pax-'+pax.id+'-child'"
                                                       :name="'pax-'+pax.id+'-child'"
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number"
                                                       v-model="currentPaxs[index].child"/>
                                            </div>
                                            <div class="col-1">
                                                <input :id="'pax-'+pax.id+'-infant'"
                                                       :name="'pax-'+pax.id+'-infant'"
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number"
                                                       v-model="currentPaxs[index].infant"/>
                                            </div>
                                            <div class="col-1">
                                                <input :id="'pax-'+pax.id+'-extra'"
                                                       :name="'pax-'+pax.id+'-extra'"
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number"
                                                       v-model="currentPaxs[index].guide"/>
                                            </div>
                                            <div class="col-1 text-center">
                                                <font-awesome-icon :icon="['fas', 'times-circle']"
                                                                   @click="removePax(index)"
                                                                   style="cursor: pointer;"
                                                                   class="icon-danger fa-2x"/>
                                            </div>
                                        </div>

                                        <div class="rooms-table row">
                                            <div class="col-3 my-auto" v-if="!hasClient">
                                                <input disabled
                                                       class="form-control"
                                                       type="text" style="background: #e1e1e1 !important; "/>
                                            </div>
                                            <div class="col-4 my-auto">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <input disabled
                                                               class="form-control"
                                                               step="1"
                                                               min="1"
                                                               type="number" style="background: #e1e1e1 !important; "/>
                                                    </div>
                                                    <div class="col-4">
                                                        {{$t('servicesmanageservicerates.to')}}
                                                    </div>
                                                    <div class="col-4">
                                                        <input disabled
                                                               class="form-control"
                                                               step="1"
                                                               min="1"
                                                               type="number" style="background: #e1e1e1 !important; "/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <input disabled
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number" style="background: #e1e1e1 !important; "/>
                                            </div>
                                            <div class="col-1">
                                                <input disabled
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number" style="background: #e1e1e1 !important; "/>
                                            </div>
                                            <div class="col-1">
                                                <input disabled
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number" style="background: #e1e1e1 !important; "/>
                                            </div>
                                            <div class="col-1">
                                                <input disabled
                                                       class="form-control"
                                                       step="0.01"
                                                       type="number" style="background: #e1e1e1 !important; "/>
                                            </div>
                                            <div class="col-1 text-center">
                                                <font-awesome-icon
                                                    :icon="['fas', 'plus']"
                                                    @click="addPax()"
                                                    style="cursor: pointer;"
                                                    class="icon-success fa-2x"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row text-right my-3" v-if="newOrEditRatePlan">
                                    <div class="col-12">

                                        <button @click="checkTwo" class="btn btn-success" type="button"
                                                v-if="tmpRatesIds.length === 0">
                                            <font-awesome-icon :icon="['fas', 'plus']"/>
                                            {{$t('global.buttons.add')}}
                                        </button>
                                        <button @click="checkTwo" class="btn btn-success" type="button"
                                                v-if="tmpRatesIds.length > 0">
                                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                            {{$t('global.buttons.submit')}}
                                        </button>
                                        <button @click="cancelEdition" class="btn btn-danger" type="button">
                                            <font-awesome-icon :icon="['fas', 'times-circle']"/>
                                            {{$t('global.buttons.cancel')}}
                                        </button>
                                    </div>
                                </div>


                                <div class="row" v-if="editProvider">
                                    <div class="col">
                                        <h3>Editar Proveedor / Políticas de cancelación</h3>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12"
                                         style="margin-bottom: 50px;background-color: #f0f3f5;padding-bottom: 22px;padding-top: 15px;"
                                         v-show="editProvider">
                                        <div class="col-6 left">
                                            <label class="col-form-label" for="period">
                                                Editar proveedor:
                                            </label>
                                            <v-select :options="providers"
                                                      :value="provider_update_selected"
                                                      label="name"
                                                      :filterable="false"
                                                      @search="onSearchProvider"
                                                      @input="providerChangeUpdate"
                                                      id="provider_update"
                                                      name="provider_update"
                                                      v-model="provider_update_selected">
                                                <template slot="option" slot-scope="option">
                                                    <div class="d-center">
                                                        {{ option.name }}
                                                    </div>
                                                </template>
                                                <template slot="selected-option" slot-scope="option">
                                                    <div class="d-center">
                                                        {{ option.name }}
                                                    </div>
                                                </template>
                                            </v-select>
                                        </div>
                                        <div class="col-6 left">
                                            <label class="col-form-label" for="period">
                                                Editar política:
                                            </label>
                                            <v-select :options="policies"
                                                      label="name"
                                                      :filterable="true"
                                                      v-model="policy_update_selected">
                                                <template slot="option" slot-scope="option">
                                                    <div class="d-center">
                                                        {{ option.name }}
                                                    </div>
                                                </template>
                                                <template slot="selected-option" slot-scope="option">
                                                    <div class="d-center">
                                                        {{ option.name }}
                                                    </div>
                                                </template>
                                            </v-select>
                                        </div>
                                        <div class="col-2 left">
                                            <label class="col-form-label" for="period">
                                                Rango desde:
                                            </label>
                                            <select v-model="range_update_from" class="form-control">
                                                <option :value="range_from" v-for="range_from in ranges_from">
                                                    {{range_from}}
                                                </option>
                                            </select>

                                        </div>
                                        <div class="col-2 left">
                                            <label class="col-form-label" for="period">
                                                Rango hasta:
                                            </label>
                                            <select v-model="range_update_to" class="form-control">
                                                <option :value="range_to" v-for="range_to in ranges_to">{{range_to}}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-4 left">
                                            <button type="button" style="margin-top: 34px"
                                                    @click="update_range_policies()"
                                                    :disabled="(provider_update_selected === null && policy_update_selected === null) || range_update_from === 0 || range_update_to === 0 || loading_update"
                                                    class="btn btn-success">
                                                <i class="fa fa-spin fa-spinner" v-if="loading_update"></i> Guardar
                                            </button>
                                            <button type="button" style="margin-top: 34px"
                                                    @click="cancelFormEditProvider()"
                                                    :disabled="loading_update"
                                                    class="btn btn-danger">
                                                <i class="fa fa-spin fa-spinner" v-if="loading_update"></i> Cancelar
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-row">
                                            <label class="col-sm-2 col-form-label" for="period">{{
                                                $t('clientsmanageclienthotel.period') }}</label>
                                            <div class="col-sm-3">
                                                <select @change="searchPeriod" ref="period" class="form-control"
                                                        id="period"
                                                        required
                                                        size="0" v-model="selectPeriod">
                                                    <option :value="year.value" v-for="year in years">
                                                        {{ year.text}}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div :key="currentTime" class="col-12" slot="range">
                                        <table-client :columns="table.columns"
                                                      :data="rates_plans"
                                                      :loading="false"
                                                      :options="table.options"
                                                      id="rooms-table"
                                                      ref="roomsTable"
                                                      theme="bootstrap4">
                                            <div class="table-option" slot="option" slot-scope="props"
                                                 style="padding: 5px;width: 90px">
                                                <button type="button" class="btn btn-success btn-sm"
                                                        @click="editRate(props.row)">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>

                                            </div>
                                            <div class="table-range text-center" slot="range"
                                                 :class="{ 'trActive' : tmpClassTrs[props.row.id] }"
                                                 slot-scope="props">
                                                <i v-if="props.row.flag_migrate === 0" class="fa fa-check-circle"></i>
                                                {{ props.row.pax_from }} - {{ props.row.pax_to }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-provider" slot="provider"
                                                 slot-scope="props">
                                                {{ props.row.user ? props.row.user.name : '' }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-policy" slot="policy"
                                                 slot-scope="props">
                                                {{ props.row.policy ? props.row.policy.name : '' }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-period" slot="period"
                                                 slot-scope="props">
                                                {{ props.row.date_from | formatDate }} - {{ props.row.date_to |
                                                formatDate }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-price_adult"
                                                 slot="price_adult"
                                                 slot-scope="props">
                                                {{ props.row.price_adult }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-price_child"
                                                 slot="price_child"
                                                 slot-scope="props">
                                                {{ props.row.price_child }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-price_infant"
                                                 slot="price_infant"
                                                 slot-scope="props">
                                                {{ props.row.price_infant }}
                                            </div>
                                            <div @click="editRate(props.row)" class="table-price_guide"
                                                 slot="price_guide"
                                                 slot-scope="props">
                                                {{ props.row.price_guide }}
                                            </div>
                                            <div class="table-actions" slot="actions" slot-scope="props"
                                                 style="padding: 5px;">
                                                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                                    <template slot="button-content">
                                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                                    </template>
                                                    <b-dropdown-item-button @click="deleteRatePlan(props.row)"
                                                                            class="m-0 p-0">
                                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                        {{$t('global.buttons.delete')}}
                                                    </b-dropdown-item-button>
                                                </b-dropdown>
                                            </div>
                                        </table-client>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab v-if="this.$route.name != 'RatesRatesCostForm' && rate_plan_id != undefined && !hasClient"
                               @click="getAssociationsRate">
                            <template #title>
                                <i class="fas fa-exchange-alt"></i> Asociar Tarifa (Opcional)
                            </template>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-bullhorn"></i> Tener en cuenta al realizar esta operación: Este
                                        proceso
                                        puede tomarse su tiempo en actualizar los registros, esto se debe por la gran
                                        cantidad
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
            </div>
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../api'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
    import BFormGroup from 'bootstrap-vue/es/components/form-group/form-group'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import BFormSelect from 'bootstrap-vue/src/components/form-select/form-select'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import Progress from 'bootstrap-vue/src/components/progress/progress'
    import CSwitch from '@coreui/vue/src/components/Switch/Switch'
    import TableClient from './../../../../../components/TableClient'
    import moment from 'moment'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'

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
            BFormCheckbox
        },
        data: () => {
            return {
                loading: false,
                languages: [],
                showError: false,
                disabled_assoc: false,
                currentLang: 1,
                invalidError: false,
                countError: 0,
                policies: [],
                providers: [],
                rates_plans: [],
                tmpRatesIds: [],
                tmpRatesEdit: [],
                tmpClassTrs: [],
                providerSelected: {},
                policy: null,
                policySelected: null,
                policy_update_selected: null,
                provider_update_selected: null,
                range_update_from: 0,
                range_update_to: 0,
                schedules: {
                    monday: true,
                    tuesday: true,
                    wednesday: true,
                    thursday: true,
                    friday: true,
                    saturday: true,
                    sunday: true,
                    all: true
                },
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
                paxs: [],
                rateTypes: [],
                formAction: 'post',
                step: 1,
                currentPaxs: [],
                table: {
                    columns: ['option', 'range', 'period', 'provider', 'policy', 'price_adult', 'price_child', 'price_infant', 'price_guide', 'actions'],
                    options: {
                        headings: {
                            option: 'Opción',
                            range: 'Rango',
                            period: 'Periodo',
                            schedule: 'Horario',
                            provider: 'Proveedor',
                            policy: 'Política',
                            price_adult: 'Adulto US$',
                            price_child: 'Niño US$',
                            price_infant: 'Infante US$',
                            price_guide: 'Guía US$',
                            actions: 'Acciones',
                        },
                        sortable: [],
                        filterable: false
                    }
                },
                customErrors: {
                    policy: false,
                    provider: false,
                },
                save: {
                    counter: 0,
                    max: 100
                },
                currentTime: 0,
                historyData: [],
                historySearch: null,
                historySet: null,
                form: {
                    name: '',
                    type: '',
                    translations: {
                        '1': {
                            'id': '',
                            'commercial_name': ''
                        }
                    },
                    allotment: false,
                    rate: false,
                    taxes: false,
                    services: false,
                    advance_sales: false,
                    promotions: false,
                    flag_process_markup: false,
                    price_dynamic: false,
                },
                formTwo: {
                    user_id: '',
                    policy_id: '',
                    dates_from: '',
                    dates_to: '',
                    updateIds: []
                },
                rate_plan_id: '',
                selectPeriod: '',
                ranges_from: [],
                ranges_to: [],
                loading_update: false,
                editProvider: false,
                newOrEditRatePlan: false,
                loading_associate_rate: false,
                selected_all_regions: false,
                regions: [],
                countries: [],
                clients: [],
                form_association: {
                    regions: [],
                    countries: [],
                    clients: [],
                    except_country: false,
                    except_client: false,
                },
                options_country: [
                    { item: false, name: 'Incluir los siguientes paises' },
                    { item: true, name: 'No incluir los siguientes paises' },
                ],
                options_clients: [
                    { item: false, name: 'Incluir los siguientes clientes' },
                    { item: true, name: 'No incluir los siguientes clientes' },
                ],
                hasClient: false
            }
        },
        created () {
            this.rate_plan_id = this.$route.params.rate_id
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
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
            }
        },
        mounted: function () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()

            API.get('/languages/')
                .then((result) => {
                    this.languages = result.data.data
                    this.currentLang = result.data.data[0].id
                    this.loading = true
                    let form = {
                        translations: {}
                    }

                    let languages = this.languages

                    languages.forEach((value) => {
                        form.translations[value.id] = {
                            id: '',
                            commercial_name: ''
                        }
                    })
                    if (this.$route.params.rate_id !== undefined) {
                        API.get('service/rates/cost/' + this.$route.params.service_id + '/' + this.$route.params.rate_id)
                            .then((result) => {
                                this.loading = false
                                this.formAction = 'put'
                                let data = result.data.data
                                this.fetchTableRates()
                                this.form = {
                                    name: data.name,
                                    type: data.service_type_rate_id,
                                    translations: {},
                                    allotment: (data.allotment) ? true : false,
                                    rate: (data.rate) ? true : false,
                                    taxes: (data.taxes) ? true : false,
                                    services: (data.services) ? true : false,
                                    advance_sales: (data.advance_sales) ? true : false,
                                    promotions: (data.promotions) ? true : false,
                                    flag_process_markup: (data.flag_process_markup == null || data.flag_process_markup == 1) ? true : false,
                                    price_dynamic: (data.price_dynamic == 1) ? true : false,
                                }

                                let translations = {}

                                data.translations.forEach((translation) => {
                                    translations[translation.language_id] = {
                                        'id': translation.id,
                                        'commercial_name': translation.value
                                    }
                                })

                                this.form.translations = translations

                                this.step = 1

                            }).catch(() => {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                            })
                        })
                    } else {
                        this.loading = false
                    }
                    this.fetchDataPolicies()
                    this.form = { ...this.form, ...form }

                    this.currentTime = moment().unix()
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                })
            })

            API.get('/service/rates/types/selectBox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    this.rateTypes = result.data.data
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('global.modules.services'),
                    text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                })
            })
            this.setInputs()
            this.get_regions()

        },
        methods: {
            set_ranges () {
                this.ranges_from = []
                let ranges_from = []
                this.ranges_to = []
                let ranges_to = []
                this.range_update_from = 0
                this.range_update_to = 0
                this.rates_plans.forEach((rate, k) => {
                    if (k === 0) {
                        this.range_update_from = rate.pax_from
                        this.range_update_to = rate.pax_to
                    }
                    ranges_from.push(rate.pax_from)
                    ranges_to.push(rate.pax_to)
                })
                this.ranges_from = [...new Set(ranges_from)]
                this.ranges_to = [...new Set(ranges_to)]
            },
            update_range_policies () {

                if (this.range_update_to < this.range_update_from) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: 'Rango "hasta" no puede ser menor'
                    })
                    return
                }

                if ((this.policy_update_selected !== null || this.provider_update_selected !== null) && this.range_update_from >= 1 && this.range_update_to >= 1) {
                    this.loading_update = true
                    let data = {
                        provider_id: (this.provider_update_selected !== null) ? this.provider_update_selected.id : null,
                        policy_id: (this.policy_update_selected !== null) ? this.policy_update_selected.id : null,
                        range_from: this.range_update_from,
                        range_to: this.range_update_to,
                        year: this.selectPeriod,
                    }

                    API.put('/services/cancellations_policies/' + this.rate_plan_id + '/ranges', data)
                        .then((result) => {
                            this.loading_update = false
                            if (result.data.success === true) {
                                this.provider_update_selected = null
                                this.policy_update_selected = null
                                this.fetchTableRates()
                                this.cancelFormEditProvider()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.services'),
                                    text: 'Políticas de rangos actualizados'
                                })
                            }
                        }).catch(() => {
                        this.loading_update = false
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                        })
                    })
                }
            },
            searchPeriod: function () {
                this.fetchTableRates()
            },
            fetchDataPolicies: function () {
                // API.get('/service/cancellations_policies/' + this.providerSelected.id + '/selectBox')
                API.get('/service/cancellations_policies/selectBox')
                    .then((result) => {
                        let policies = []
                        result.data.data.forEach((item) => {
                            policies.push({
                                id: item.value,
                                name: item.text
                            })
                        })
                        this.policies = policies

                    }).catch(() => {
                    console.log('policies no working')
                })
            },
            editRate: function (me) {
                this.tmpRatesIds = []
                this.tmpClassTrs = []
                this.currentPaxs = []
                this.rates_plans.forEach((value) => {
                    if (value.date_from === me.date_from && value.date_to === me.date_to &&
                        value.monday === me.monday && value.tuesday === me.tuesday && value.wednesday === me.wednesday &&
                        value.thursday === me.thursday && value.friday === me.friday && value.saturday === me.saturday &&
                        value.sunday === me.sunday) {
                        this.tmpClassTrs[value.id] = true
                        this.tmpRatesIds.push(value.id)
                        this.currentPaxs.push({
                            pax_from: value.pax_from,
                            pax_to: value.pax_to,
                            adult: value.price_adult,
                            child: value.price_child,
                            infant: value.price_infant,
                            guide: value.price_guide,
                            service_cancellation_policy_id: value.policy.id,
                            service_cancellation_policy_selected: value.policy
                        })

                        if (value.monday === 0) {
                            this.schedules.all = false
                            this.schedules.monday = false
                        }

                        if (value.tuesday === 0) {
                            this.schedules.all = false
                            this.schedules.tuesday = false
                        }

                        if (value.wednesday === 0) {
                            this.schedules.all = false
                            this.schedules.wednesday = false
                        }

                        if (value.thursday === 0) {
                            this.schedules.all = false
                            this.schedules.thursday = false
                        }

                        if (value.friday === 0) {
                            this.schedules.all = false
                            this.schedules.friday = false
                        }

                        if (value.saturday === 0) {
                            this.schedules.all = false
                            this.schedules.saturday = false
                        }

                        if (value.sunday === 0) {
                            this.schedules.all = false
                            this.schedules.sunday = false
                        }

                    }
                })
                if (me.user) {
                    this.providerSelected = {
                        id: me.user.id,
                        name: me.user.name
                    }
                } else {
                    this.providerSelected = {}
                }
                if (me.policy) {
                    this.policySelected = {
                        id: me.policy.id,
                        name: me.policy.name
                    }
                } else {
                    this.policySelected = null
                }
                this.formTwo.dates_from = this.convertDate(me.date_from)
                this.formTwo.dates_to = this.convertDate(me.date_to)
                this.newOrEditRatePlan = true
                this.cancelFormEditProvider()
            },
            convertDate: function (_date) {
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            },
            cancelEdition: function () {
                this.tmpRatesIds = []
                this.tmpClassTrs = []
                this.formTwo.dates_from = ''
                this.formTwo.dates_to = ''
                this.providerSelected = {}
                this.policySelected = null
                this.currentPaxs = []
                this.newOrEditRatePlan = false
            },
            changeSchedulesAll: function () {
                this.schedules.monday = !(this.schedules.all)
                this.schedules.tuesday = !(this.schedules.all)
                this.schedules.wednesday = !(this.schedules.all)
                this.schedules.thursday = !(this.schedules.all)
                this.schedules.friday = !(this.schedules.all)
                this.schedules.saturday = !(this.schedules.all)
                this.schedules.sunday = !(this.schedules.all)
            },
            checkboxChecked: function (check) {

                if (!(isNaN(parseInt(check)))) {
                    check = parseInt(check)
                }

                if (check) {
                    return 'true'
                } else {
                    return 'false'
                }
            },
            changeSchedule: function () {
                var self = this
                setTimeout(function () {
                    let n = 0
                    if (self.schedules.monday == false) { n++ }
                    if (self.schedules.tuesday == false) { n++ }
                    if (self.schedules.wednesday == false) { n++ }
                    if (self.schedules.thursday == false) { n++ }
                    if (self.schedules.friday == false) { n++ }
                    if (self.schedules.saturday == false) { n++ }
                    if (self.schedules.sunday == false) { n++ }
                    self.schedules.all = (n == 0) ? true : false
                }, 300)
            },
            deleteRatePlan: function (s) {
                API({
                    method: 'DELETE',
                    url: '/service/rates/plans/' + s.id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.fetchTableRates()
                            this.cancelEdition()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                    })
                })
            },

            fetchTableRates: function () {
                API.get('/service/rates/' + this.rate_plan_id + '/plans/' + this.selectPeriod)
                    .then((result) => {
                        this.rates_plans = result.data.data
                        this.set_ranges()
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.services'),
                        text: this.$t('servicesmanageservicerates.error.messages.connection_error')
                    })
                })

            },
            setInputs: function () {
                let nInputs
                // nInputs = (localStorage.serviceChoosed_type_code == 'PC') ? 1 : 1
                nInputs = 1
                for (var n = 0; n < nInputs; n++) {
                    this.currentPaxs.push({
                        pax_from: n + 1,
                        pax_to: n + 1,
                        adult: 0,
                        child: 0,
                        infant: 0,
                        guide: 0
                    })
                }
            },
            addPax: function () {
                let prevPaxTo = 0
                let prevPaxFrom = 0
                let prevAdult = 0
                let prevChild = 0
                let prevInfant = 0
                let prevGuide = 0
                if (this.currentPaxs.length > 0) {
                    let prevPax = this.currentPaxs[this.currentPaxs.length - 1]
                    prevPaxTo = parseInt(prevPax.pax_to)
                    prevPaxFrom = parseInt(prevPax.pax_from)
                    prevAdult = parseFloat(prevPax.adult)
                    prevChild = parseFloat(prevPax.child)
                    prevInfant = parseFloat(prevPax.infant)
                    prevGuide = parseFloat(prevPax.guide)
                }

                this.currentPaxs.push({
                    pax_from: prevPaxTo + 1,
                    pax_to: (prevPaxTo + 1) + ((prevPaxTo) - prevPaxFrom),
                    adult: 0,
                    child: 0,
                    infant: 0,
                    guide: 0
                })
            },
            removePax: function (i) {
                let tempPaxs = []
                this.currentPaxs.forEach((value, index) => {
                    if (index != i) {
                        tempPaxs.push(value)
                    }
                })
                this.currentPaxs = tempPaxs
            },
            onSearchProvider (search, loading) {
                if (search.length > 2) {
                    loading(true)
                    API.get('/providers/selectBox?query=' + search)
                        .then((result) => {
                            loading(false)
                            this.providers = result.data.data
                        }).catch(() => {
                        loading(false)
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('servicesmanageservicerates.error.messages.system')
                        })
                    })
                }
            },

            setHistory (event) {
                this.historySet = event

                // this.formTwo.data = event.data

            },
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            validateState (ref) {
                // if (this.fields[ref] && (this.fields[ref].dirty || this.fields[ref].validated)) {
                //   return !this.errors.has(ref)
                // }
                // return null
            },
            emptyForm () {
                this.currentPaxs = []
            },
            submit (shallContinue) {
                this.$validator.validateAll().then(isValid => {
                    if (isValid) {
                        this.loading = true
                        API({
                            method: this.formAction,
                            url: 'service/rates/cost/' + this.$route.params.service_id +
                                (this.rate_plan_id !== undefined ? '/' + this.rate_plan_id : ''),
                            data: this.form
                        }).then((result) => {
                            this.formTwo.rates_plan = result.data.rate_plan
                            if (this.formAction == 'post') {
                                this.formAction = 'put'
                                this.rate_plan_id = result.data.rate_plan
                                this.$router.push({ path: 'edit/' + this.rate_plan_id })
                            }
                            this.loading = false
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$i18n.t('servicesmanageservicerates.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
            },
            addRoom () {
                if (this.currentPaxs.length === 0) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('global.modules.ratescost'),
                        text: this.$i18n.t('servicesmanageservicerates.error.messages.missing_rates')
                    })
                } else {

                    this.currentPaxs.forEach((data) => {
                        if(!this.hasClient){
                            data.service_cancellation_policy_id = this.policySelected.id
                            if (data.service_cancellation_policy_selected) {
                                data.service_cancellation_policy_id = data.service_cancellation_policy_selected.id
                            }
                        }
                    })

                    this.formTwo.data = this.currentPaxs
                    if(!this.hasClient){
                        this.formTwo.user_id = this.providerSelected.id
                        this.formTwo.policy_id = this.policySelected.id // Eliminar
                    }
                    this.formTwo.monday = this.schedules.monday
                    this.formTwo.tuesday = this.schedules.tuesday
                    this.formTwo.wednesday = this.schedules.wednesday
                    this.formTwo.thursday = this.schedules.thursday
                    this.formTwo.friday = this.schedules.friday
                    this.formTwo.saturday = this.schedules.saturday
                    this.formTwo.sunday = this.schedules.sunday
                    this.formTwo.updateIds = this.tmpRatesIds
                    this.formTwo.rates_plan = this.rate_plan_id
                    this.formTwo.has_client = this.hasClient

                    this.loading = true

                    API({
                        method: 'POST',
                        url: 'service/rates/plans',
                        data: this.formTwo
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                this.loading = false
                                this.currentPaxs = []
                                this.addPax()
                                this.fetchTableRates()
                                if (this.tmpRatesIds.length > 0) {
                                    this.cancelEdition()
                                }
                            } else {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.services'),
                                    text: (result.data.message) ? 'El rango ingresado ya existe en las fechas indicadas' : this.$i18n.t('global.error.messages.information_complete')
                                })

                                this.loading = false
                            }
                        })
                }
            },
            submitAll () { // submitAll
                this.loading = true
                let tmppaxs = []
                let newDays = 0
                let currentDays = 0
                let tmp = []

                this.formTwo.data.forEach((item, index) => {
                    item.paxs.forEach((room) => {
                        newDays = Math.abs(
                            moment(item.dates_from, 'DD/MM/YYYY').diff(moment(item.dates_to, 'DD/MM/YYYY'), 'days'))

                        if (tmp.length > 0 && currentDays + newDays > 500) {
                            tmppaxs.push(tmp)
                            tmp = []
                            currentDays = 0
                        }
                        tmp.push({
                            'id': room.id,
                            'adult': room.adult,
                            'child': room.child,
                            'extra': room.extra,
                            'infant': room.infant,
                            'dates_from': item.dates_from,
                            'dates_to': item.dates_to,
                            'policy_id': item.policy_id,
                            'rates_plan': this.formTwo.rates_plan,
                            'group': index
                        })
                        currentDays = currentDays + newDays
                    })
                })

                tmppaxs.push(tmp)

                this.save.max = tmppaxs.length

                this.makeRequestsFromArray(tmppaxs).then(() => {
                    this.loading = false
                    this.$router.push('/services_new/' + this.$route.params.service_id + '/manage_service/rates/cost')
                })
            },
            checkTwo () {
                this.$validator.validateAll().then(isValid => {

                    if (this.providerSelected == null) {
                        this.customErrors.provider = true
                        return false
                    } else {
                        this.customErrors.provider = true
                    }
                    this.customErrors.provider = false

                    if (isValid) {
                        this.addRoom()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$i18n.t('servicesmanageservicerates.error.messages.information_complete')
                        })

                        this.loading = false
                    }

                })
            },
            addProgress (index) {
                this.save.counter = index
            },
            makeRequestsFromArray (arr) {
                let index = 0
                let self = this

                let request = () => {
                    let data = {
                        paxs: arr[index],
                        continue: index > 0
                    }

                    if (this.formAction === 'put') {
                        data.del = index === 0
                    }

                    return API({
                        method: self.formAction,
                        url: 'rates/cost/' + this.$route.params.service_id + '/' + this.formTwo.rates_plan + '/paxs',
                        data: data
                    }).then(() => {
                        index++
                        this.addProgress(index)
                        if (index >= arr.length) {
                            return 'done'
                        }
                        return request()
                    })
                }

                return request()
            },
            providerChange: function (value) {
                this.provider = value
                if (this.provider != null) {
                    if (this.formTwo.user_id !== this.provider.id) {
                    }
                }
            },
            providerChangeUpdate: function (value) {
                this.provider_update_selected = value
            },
            showFormEditProvider: function () {
                this.editProvider = true
                this.cancelEdition()
            },
            showFormNewRatePlan: function () {
                this.newOrEditRatePlan = true
                this.cancelFormEditProvider()
            },
            cancelFormEditProvider: function () {
                this.editProvider = false
                this.provider_update_selected = null
                this.policy_update_selected = null
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
            changeExceptCountry: function () {
                this.form_association.except_country = !!this.form_association.except_country
                this.form_association.clients = []
            },
            associateRate: function () {
                if (this.form_association.regions.length === 0 && (this.form_association.countries.length > 0 || this.form_association.clients.length > 0)) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('global.modules.ratescost'),
                        text: 'Debe seleccionar al menos una region'
                    })
                } else {
                    this.loading_associate_rate = true
                    API({
                        method: 'POST',
                        url: 'service/rate/cost/' + this.$route.params.rate_id + '/associate_rate',
                        data: {
                            'regions': this.form_association.regions,
                            'countries': this.form_association.countries,
                            'except_country': this.form_association.except_country,
                            'clients': this.form_association.clients,
                            'except_client': this.form_association.except_client,
                        }
                    }).then((result) => {
                        if (result.data.success) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$i18n.t('global.modules.ratescost'),
                                text: result.data.message
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$i18n.t('global.modules.ratescost'),
                                text: result.data.message
                            })
                        }
                        this.loading_associate_rate = false
                    }).catch((e) => {
                        this.loading_associate_rate = false
                        console.log(e)
                    })
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
                    }
                }).then((result) => {
                    loading(false)
                    let clients_ = []
                    result.data.data.forEach((c) => {
                        clients_.push({
                            label: c.name,
                            code: c.id
                        })
                    })
                    this.clients = clients_
                }).catch(() => {
                    loading(false)
                })
            },
            get_regions () {
                API({
                    method: 'GET',
                    url: 'markets/selectbox?lang=' + localStorage.getItem('lang')
                }).then((result) => {
                    this.regions = result.data.data
                    this.loading = false
                })
                    .catch((e) => {
                        console.log(e)
                    })
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
                            regions: regions
                        }
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
            getAssociationsRate: function () {
                this.loading_associate_rate = true
                this.countries = []
                API.get('service/rate/cost/' + this.$route.params.rate_id + '/associate_rate?lang=' + localStorage.getItem('lang'))
                    .then((result) => {
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
                                    regions: regions
                                }
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
                    }).catch(() => {
                    this.loading_associate_rate = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('hotelsmanagehotelratesratescost.error.messages.name'),
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
            }

        },
        filters: {
            first2Char: function (day) {
                return day.substr(0, 2)
            },
            formatDate: function (_date) {

                let tmpDate = _date.split(':')
                let secondPartDate = ''
                if (tmpDate.length > 1) {
                    secondPartDate = _date.substr(10, _date.length)
                    _date = _date.substr(0, 10)
                }

                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date + secondPartDate
            }
        }
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

    .rooos-table-headers
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

    .icon-success
        color #28a745

    .icon-danger
        color #dc3545

    .span-success
        color #fff
        background-color #4dbd74
        border-color #4dbd74
        padding 1px 4px
        border-radius 4px
        font-size 10px

    .span-danger
        color #fff
        background-color #BD0D12
        border-color #BD0D12
        padding 1px 4px
        border-radius 4px
        font-size 10px


    .trActive
        background #ffecc9

</style>

