<template>
    <div class="col-12">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">
            <form class="container">
                <div class="row col-12" v-if="step === 1">
                    <div class="b-form-group form-group col-6">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
                            <div class="col-sm-10">
                                <input
                                        :class="{'form-control':true, 'is-valid':errors.has('name'), 'is-invalid':errors.has('name') }"
                                        data-vv-validate-on="none"
                                        id="name"
                                        name="name"
                                        type="text"
                                        :disabled="step === 2"
                                        v-model="form.name"
                                        v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('name')"/>
                                    <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
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
                                        :disabled="step === 2"
                                        v-validate="'required'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;"
                                                       v-show="errors.has('commercial_name')"/>
                                    <span
                                            v-show="errors.has('commercial_name')">{{ errors.first('commercial_name') }}</span>
                                </div>
                            </div>
                            <select class="col-sm-2 form-control" id="lang" required size="0" v-model="currentLang">
                                <option v-bind:value="language.id" v-for="language in languages">
                                    {{ language.iso }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row text-right mt-3" v-if="step === 1">
                    <div class="col-12">
                        <img src="/images/loading.svg" v-if="loading" width="40px"/>
                        <button @click="submit(true)" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>
                        <button @click="submit(false)" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            Guardar y Salir
                        </button>
                        <router-link to="../" v-if="!loading">
                            <button class="btn btn-danger" type="reset">
                                {{$t('global.buttons.cancel')}}
                            </button>
                        </router-link>
                    </div>
                </div>

                <div class="row buttonTabs" v-if="step === 2">
                    <button type="button" :class="{'btn btn-primary':true, 'type_off':typeTrain=='OW'}" @click="changeTypeTrain('RT')"
                        v-show="!showConfirmTrainTypes">
                        RT
                    </button>
                    <button type="button" :class="{'btn btn-primary':true, 'type_off':typeTrain=='RT'}"  @click="changeTypeTrain('OW')"
                        v-show="!showConfirmTrainTypes" style="margin-left: 5px;">
                        OW
                    </button>
                    <div class="alert alert-warning" v-show="showConfirmTrainTypes">
                        <strong>Aviso Importante.</strong> Se detectó que la información fué <b>importada</b>
                        y es necesario que por favor confime el <b>tipo de tren</b> y verifique los códigos de <b>equivalencia aurora</b> y <b>frecuencia</b> del proveedor en la opción <i class="fa fa-bars"></i> "editar" en sus tarifas.<br><br>
                        <center>
                            <button type="button" class="btn btn-primary" @click="updateTypeTrain('RT')">
                                Definir como Round Trip (RT)
                            </button>
                            <button type="button" class="btn btn-primary" @click="updateTypeTrain('OW')">
                                Definir como One Way (OW)
                            </button>
                        </center>
                    </div>
                </div>
                <div class="row" v-if="step === 2">
                    <div class="b-form-group form-group col-2">
                        <label class="col-form-label">{{ $t('servicesmanageservicerates.date_range')}}</label>
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
                                        autocomplete="off"
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
                                        autocomplete="off"
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
                                    <span v-show="errors.has('dates_to')">{{ errors.first('dates_to') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-form-group form-group col-2">
                        <label class="col-form-label">ID Operador</label>
                    </div>
                    <div class="b-form-group form-group col-4">
                        <input type="text" class="form-control" v-model="formTwo.frequency_code">
                    </div>
                </div>
                <div class="row" v-if="step === 2">
                    <div class="b-form-group form-group col-6">
                        <div class="form-row" style="margin-bottom: 5px;">
                            <label class="col-sm-2 col-form-label" for="policy">{{
                                $t('servicesmanageservicerates.policy_name') }}</label>
                            <div class="col-sm-10">
                                <v-select :options="policies"
                                          label="name"
                                          :value="formTwo.policy_id"
                                          :filterable="false"
                                          @input="policyChange"
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
                                        <div class="selected d-center">
                                            {{ option.name }}
                                        </div>
                                    </template>
                                </v-select>
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon
                                            :icon="['fas', 'exclamation-circle']"
                                            style="margin-left: 5px;"
                                            v-show="errors.has('policy')"/>
                                    <span v-show="errors.has('policy')">{{ errors.first('policy') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="b-form-group form-group col-2">
                        <label class="col-form-label">Código de Frecuencia de Tren</label>
                    </div>
                    <div class="b-form-group form-group col-4">
                        <input type="text" class="form-control" placeholder="Ej: 32" v-model="formTwo.equivalence_code">
                    </div>
                </div>
                <div class="row" v-if="step === 2">
                    <div class="col-12">
                        <table class="table table-days">
                            <thead>
                            <tr>
                                <th>{{$t('global.days.everyone')}}</th>
                                <th>{{$t('global.days.monday')}}</th>
                                <th>{{$t('global.days.tuesday')}}</th>
                                <th>{{$t('global.days.wednesday')}}</th>
                                <th>{{$t('global.days.thursday')}}</th>
                                <th>{{$t('global.days.friday')}}</th>
                                <th>{{$t('global.days.saturday')}}</th>
                                <th>{{$t('global.days.sunday')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="text-center">
                                    <b-form-checkbox
                                            v-model="schedules.all"
                                            :id="'checkbox_all'"
                                            :name="'checkbox_all'"
                                            @change="changeSchedulesAll()"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.monday)"
                                            v-model="schedules.monday"
                                            :id="'checkbox_monday'"
                                            :name="'checkbox_monday'"
                                            @change="changeSchedule(schedules.monday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.tuesday)"
                                            v-model="schedules.tuesday"
                                            :id="'checkbox_tuesday'"
                                            :name="'checkbox_tuesday'"
                                            @change="changeSchedule(schedules.tuesday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.wednesday)"
                                            v-model="schedules.wednesday"
                                            :id="'checkbox_wednesday'"
                                            :name="'checkbox_wednesday'"
                                            @change="changeSchedule(schedules.wednesday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.thursday)"
                                            v-model="schedules.thursday"
                                            :id="'checkbox_thursday'"
                                            :name="'checkbox_thursday'"
                                            @change="changeSchedule(schedules.thursday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.friday)"
                                            v-model="schedules.friday"
                                            :id="'checkbox_friday'"
                                            :name="'checkbox_friday'"
                                            @change="changeSchedule(schedules.friday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.saturday)"
                                            v-model="schedules.saturday"
                                            :id="'checkbox_saturday'"
                                            :name="'checkbox_saturday'"
                                            @change="changeSchedule(schedules.saturday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                                <td class="text-center">
                                    <b-form-checkbox
                                            :checked="checkboxChecked(schedules.sunday)"
                                            v-model="schedules.sunday"
                                            :id="'checkbox_sunday'"
                                            :name="'checkbox_sunday'"
                                            @change="changeSchedule(schedules.sunday)"
                                            switch>
                                    </b-form-checkbox>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-3 add" v-if="step === 2 && paxs">
                    <div class="col-12">
                        <div class="rooms-table row rooms-table-headers">
                            <div class="col-7 my-auto">
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
                            <div class="col-7 my-auto">
                                <div class="row">
                                    <div class="col-5">
                                        <input :id="'pax-'+pax.id+'-pax_from'"
                                               :name="'pax-'+pax.id+'-pax_from'"
                                               class="form-control"
                                               step="1"
                                               min="1"
                                               v-model="currentPaxs[index].pax_from"
                                               type="number"/>
                                    </div>
                                    <div class="col-2">
                                        {{$t('servicesmanageservicerates.to')}}
                                    </div>
                                    <div class="col-5">
                                        <input :id="'pax-'+pax.id+'-pax_to'"
                                               :name="'pax-'+pax.id+'-pax_to'"
                                               class="form-control"
                                               step="1"
                                               min="1"
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
                            <div class="col-7 my-auto">
                                <div class="row">
                                    <div class="col-5">
                                        <input disabled
                                               class="form-control"
                                               step="1"
                                               min="1"
                                               type="number" style="background: #e1e1e1 !important; "/>
                                    </div>
                                    <div class="col-2">
                                        {{$t('servicesmanageservicerates.to')}}
                                    </div>
                                    <div class="col-5">
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
                <div class="row text-right my-3" v-if="step === 2">
                    <div class="col-12">

                        <button @click="checkTwo" class="btn btn-success" type="button"
                                v-if="tmpRatesIds.length === 0">
                            <font-awesome-icon :icon="['fas', 'plus']"/>
                            {{$t('global.buttons.add')}}
                        </button>

                        <button @click="cancelEdition" class="btn btn-danger" type="button"
                                v-if="tmpRatesIds.length > 0">
                            <font-awesome-icon :icon="['fas', 'times-circle']"/>
                            {{$t('global.buttons.cancel')}}
                        </button>
                        <button @click="checkTwo" class="btn btn-success" type="button"
                                v-if="tmpRatesIds.length > 0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            {{$t('global.buttons.submit')}}
                        </button>
                    </div>
                </div>
                <div class="row" v-if="step === 2">
                    <div class="col-12">
                        <div class="form-row">
                            <label class="col-sm-1 col-form-label" for="period">{{
                                $t('clientsmanageclienthotel.period') }}</label>
                            <div class="col-sm-1.5">
                                <select @change="searchPeriod" ref="period" class="form-control" id="period"
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

                            <div class="table-range text-center" slot="range"
                                 :class="{ 'trActive' : tmpClassTrs[props.row.id] }"
                                 slot-scope="props">
                                {{ props.row.pax_from }} - {{ props.row.pax_to }}
                            </div>
                            <div @click="editRate(props.row)" class="table-policy" slot="policy"
                                 slot-scope="props">
                                {{ props.row.policy.name }}
                            </div>
                            <div @click="editRate(props.row)" class="table-schedule" slot="schedule"
                                 slot-scope="props">
                                <span class="span-success" v-if="props.row.monday">{{ $t('global.days.monday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.tuesday">{{ $t('global.days.tuesday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.wednesday">{{ $t('global.days.wednesday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.thursday">{{ $t('global.days.thursday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.friday">{{ $t('global.days.friday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.saturday">{{ $t('global.days.saturday') | first2Char }}</span>
                                <span class="span-success" v-if="props.row.sunday">{{ $t('global.days.sunday') | first2Char }}</span>
                            </div>
                            <div @click="editRate(props.row)" class="table-period" slot="period"
                                 slot-scope="props">
                                {{ props.row.date_from | formatDate }} - {{ props.row.date_to | formatDate }}
                            </div>
                            <div @click="editRate(props.row)" class="table-price_adult" slot="price_adult"
                                 slot-scope="props">
                                {{ props.row.price_adult }}
                            </div>
                            <div @click="editRate(props.row)" class="table-price_child" slot="price_child"
                                 slot-scope="props">
                                {{ props.row.price_child }}
                            </div>
                            <div @click="editRate(props.row)" class="table-price_infant" slot="price_infant"
                                 slot-scope="props">
                                {{ props.row.price_infant }}
                            </div>
                            <div @click="editRate(props.row)" class="table-price_guide" slot="price_guide"
                                 slot-scope="props">
                                {{ props.row.price_guide }}
                            </div>
                            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    <li @click="editRate(props.row)" class="nav-link m-0 p-0">
                                        <b-dropdown-item-button class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                            {{$t('global.buttons.edit')}}
                                        </b-dropdown-item-button>
                                    </li>
                                    <b-dropdown-item-button @click="deleteRatePlan(props.row)" class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>
                            <!--                                <div @click="editRate(props.row)" class="table-period text-center" slot="edit"-->
                            <!--                                     slot-scope="props">-->
                            <!--                                    <font-awesome-icon :icon="['fas', 'edit']"-->
                            <!--                                                       style="cursor: pointer; margin: 5px"-->
                            <!--                                                       class="icon-success fa-2x" />-->
                            <!--                                </div>-->
                            <!--                                <div @click="deleteRatePlan(props.row)" class="table-period text-center" slot="delete"-->
                            <!--                                     slot-scope="props">-->
                            <!--                                    <font-awesome-icon :icon="['fas', 'trash']"-->
                            <!--                                                       style="cursor: pointer; margin: 5px"-->
                            <!--                                                       class="icon-danger fa-2x" />-->
                            <!--                                </div>-->
                        </table-client>
                    </div>
                </div>
                <div class="row" v-if="step === 2">
                    <router-link to="../" v-if="!loading">
                        <button class="btn btn-danger" type="button">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </router-link>
                </div>
                <div class="row" v-if="step === 2" v-show="0">
                    <div :class="{'col-8': !loading, 'col-12': loading}">
                        <b-progress
                                :max="save.max"
                                :value="save.counter"
                                animated
                                show-progress
                                v-if="loading"
                                variant="success"></b-progress>
                        <button @click="submitAll" class="btn btn-success" type="button" v-if="!loading">
                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                            Guardar
                        </button>
                        <router-link to="../" v-if="!loading">
                            <button class="btn btn-danger" type="button">
                                {{$t('global.buttons.cancel')}}
                            </button>
                        </router-link>
                    </div>
                    <div class="col-4 text-right">
                        <button @click="emptyForm" class="btn btn-secondary" type="button" v-if="!loading">
                            {{$t('global.buttons.cleanData')}}
                        </button>
                    </div>
                </div>
            </form>
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
                currentLang: 1,
                invalidError: false,
                countError: 0,
                policies: [],
                rates_plans: [],
                tmpRatesIds: [],
                tmpRatesEdit: [],
                tmpClassTrs: [],
                policy: null,
                policySelected: null,
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
                typeTrain:'RT',
                formAction: 'post',
                step: 1,
                currentPaxs: [],
                table: {
                    columns: ['range', 'period', 'schedule', 'policy', 'price_adult', 'price_child', 'price_infant', 'price_guide', 'actions'],
                    options: {
                        headings: {
                            range: 'Rango',
                            period: 'Periodo',
                            schedule: 'Horario',
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
                    policy: false
                },
                save: {
                    counter: 0,
                    max: 100
                },
                currentTime: 0,
                form: {
                    name: '',
                    translations: {
                        '1': {
                            'id': '',
                            'commercial_name': ''
                        }
                    }
                },
                formTwo: {
                    policy_id: '',
                    frequency_code: '',
                    equivalence_code: '',
                    dates_from: '',
                    dates_to: '',
                    updateIds: []
                },
                rate_plan_id: '',
                selectPeriod: '',
                showConfirmTrainTypes : false
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
            }
        },
        mounted: function () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()

            this.rate_plan_id = this.$route.params.rate_id
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
                        API.get('train_template/rate/' + this.$route.params.rate_id)
                            .then((result) => {
                                this.loading = false
                                this.formAction = 'put'
                                let data = result.data.data
                                this.fetchTableRates()
                                this.form = {
                                    name: data.name,
                                    translations: {}
                                }

                                let translations = {}

                                data.translations.forEach((translation) => {
                                    translations[translation.language_id] = {
                                        'id': translation.language_id,
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

            this.setInputs()
            // this.fetchTableRates()
        },
        methods: {
            updateTypeTrain(_type){

                API.put('/train_template/rate/' + this.rate_plan_id + '/plans/train_type_id', { train_type_abbreviation : _type })
                    .then((result) => {
                        if( result.data.success ){
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: this.$t('global.modules.services'),
                                text: "Actualizado Correctamente"
                            })
                            this.showConfirmTrainTypes = false
                            this.changeTypeTrain(_type)
                        } else {
                            console.log(result.data)
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
            changeTypeTrain(_type){
                this.typeTrain = _type
                this.fetchTableRates()
            },
            searchPeriod: function () {
                this.fetchTableRates()
            },
            fetchDataPolicies: function () {

                API.get('/train_cancellation_policies/selectBox')
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
                console.log(me)
                this.tmpRatesIds = []
                this.tmpClassTrs = []

                this.currentPaxs = []

                this.rates_plans.forEach((value) => {
                    if (value.date_from == me.date_from && value.date_to == me.date_to &&
                        value.train_cancellation_policy_id == me.train_cancellation_policy_id &&
                        value.frequency_code == me.frequency_code &&
                        value.equivalence_code == me.equivalence_code &&
                        value.monday == me.monday && value.tuesday == me.tuesday && value.wednesday == me.wednesday &&
                        value.thursday == me.thursday && value.friday == me.friday && value.saturday == me.saturday &&
                        value.sunday == me.sunday) {
                        this.tmpClassTrs[value.id] = true
                        this.tmpRatesIds.push(value.id)
                        this.currentPaxs.push({
                            pax_from: value.pax_from,
                            pax_to: value.pax_to,
                            adult: value.price_adult,
                            child: value.price_child,
                            infant: value.price_infant,
                            guide: value.price_guide
                        })

                    }
                })
                this.policySelected = {
                    id: me.policy.id,
                    name: me.policy.name
                }
                // this.fetchDataPolicies()
                this.formTwo.dates_from = this.convertDate(me.date_from)
                this.formTwo.dates_to = this.convertDate(me.date_to)
                this.formTwo.frequency_code = me.frequency_code
                this.formTwo.equivalence_code = me.equivalence_code
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
                this.formTwo.equivalence_code = ''
                this.formTwo.frequency_code = ''
                this.policySelected = null
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
                    url: '/train_template/rate/plan/' + s.id
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
                API.get('/train_template/rate/' + this.rate_plan_id + '/plans/' + this.selectPeriod +
                    '?train_type=' + this.typeTrain)
                        .then((result) => {
                            let _c = 0
                            result.data.data.forEach( r_p => {
                                if( r_p.train_type_id_undefined ){
                                    _c++
                                }
                            } )
                            if( _c > 0 ){
                                this.showConfirmTrainTypes = true
                            }
                            this.rates_plans = result.data.data

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
            setDateFrom (e) {
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            emptyForm () {
                this.currentPaxs = []
            },
            submit (shallContinue) {
                this.$validator.validateAll().then(isValid => {
                    if (isValid) {
                        this.loading = true
                        if( this.formAction == 'post' ){
                            API.post( 'train_template/'+ this.$route.params.train_id +'/rate', this.form )
                                .then((result) => {
                                    this.formTwo.rates_plan = result.data.rate_plan
                                    this.rate_plan_id = result.data.rate_plan
                                    if (shallContinue) {
                                        this.step = 2
                                    } else {
                                        this.$router.push('/trains/' + this.$route.params.train_id + '/manage_train/rates/cost')
                                    }
                                    this.loading = false
                                })
                        } else {
                            API.put('train_template/rate/' + this.rate_plan_id, this.form).then((result) => {
                                this.formTwo.rates_plan = result.data.rate_plan
                                this.rate_plan_id = result.data.rate_plan
                                if (shallContinue) {
                                    this.step = 2
                                } else {
                                    this.$router.push('/trains/' + this.$route.params.train_id + '/manage_train/rates/cost')
                                }
                                this.loading = false
                            })
                        }
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
                    this.formTwo.data = this.currentPaxs
                    this.formTwo.policy_id = this.policySelected.id
                    this.formTwo.monday = this.schedules.monday
                    this.formTwo.tuesday = this.schedules.tuesday
                    this.formTwo.wednesday = this.schedules.wednesday
                    this.formTwo.thursday = this.schedules.thursday
                    this.formTwo.friday = this.schedules.friday
                    this.formTwo.saturday = this.schedules.saturday
                    this.formTwo.sunday = this.schedules.sunday
                    this.formTwo.updateIds = this.tmpRatesIds
                    this.formTwo.typeTrain = this.typeTrain
                    this.loading = true

                    API({
                        method: 'POST',
                        url: 'train_template/rate/plans',
                        data: this.formTwo
                    })
                        .then((result) => {
                            if (result.data.success === true) {
                                this.loading = false
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
                            'frequency_code': item.frequency_code,
                            'equivalence_code': item.equivalence_code,
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
                    this.$router.push('/trains/' + this.$route.params.train_id + '/manage_train/rates/cost')
                })
            },
            checkTwo () {
                this.$validator.validateAll().then(isValid => {

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
                        url: 'rates/cost/' + this.$route.params.train_id + '/' + this.formTwo.rates_plan + '/paxs',
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
            policyChange: function (value) {
                this.policy_select = value
                if (this.policy_select != null) {
                    if (this.formTwo.policy_id !== this.policy_select.id) {
                        // this.formTwo.policy_id = ''
                        // this.policySelected = []
                        // this.fetchDataPolicies()
                    }
                } else {
                    // this.formTwo.policy_id = ''
                    // this.policySelected = []
                }
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

    .rooms-table-headers
        text-align center
        background-color #e4e7ea

    input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button
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

    .table-options .col-2
        padding 0.75rem
        text-align center

    .rooms-table input[type=number]
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

    .trActive
        background #ffecc9

    .type_off
        opacity 0.5
    .buttonTabs
        margin-bottom 10px
        border-bottom solid 1px #a5c6c6
        padding-bottom 13px
</style>

