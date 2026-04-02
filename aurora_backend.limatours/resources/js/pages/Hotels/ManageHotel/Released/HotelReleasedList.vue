<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="col-12">
            <b-card-text>
                <button class="btn btn-success mb-4" type="button" @click="createForm"
                        v-if="!viewForm && $can('create', 'hotelreleased')"> Agregar
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
                <form v-show="viewForm">
                    <div class="row">
                        <div class="col-3">
                            <label for="rates">Tarifas</label>
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="rates"
                                         placeholder="Elegir tarifas"
                                         :preserve-search="true"
                                         :taggable="true"
                                         group-values="rates"
                                         group-label="select_all"
                                         :group-select="true"
                                         v-model="rates_ids"
                                         label="name"
                                         ref="multiselect"
                                         track-by="id"
                                         id="rates"
                                         name="rates"
                                         data-vv-scope="formReleased"
                                         @input="rateChange"
                                         v-validate="'required'">
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.rates')">
                                <span>{{ errors.first('formReleased.rates') }}</span>
                        </span>
                        </div>
                        <div class="col-2">
                            <label for="rates">Fecha desde</label>
                            <div class="input-group mb-3">
                                <date-picker
                                    :config="datePickerFromOptions"
                                    @dp-change="setDateFrom"
                                    ref="datePickerFrom"
                                    id="dates_from_block"
                                    name="dates_from_block"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_from_block"
                                    data-vv-scope="formReleased"
                                    v-model="date_from" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon4">
                                <i class="far fa-calendar"></i>
                            </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('formReleased.dates_from_block')">
                                            <span>{{ errors.first('formReleased.dates_from_block') }}</span>
                        </span>
                            </div>
                        </div>
                        <div class="col-2">
                            <label for="rates">Fecha hasta</label>
                            <div class="input-group mb-3">
                                <date-picker
                                    :config="datePickerToOptions"
                                    ref="datePickerTo"
                                    id="dates_to_block"
                                    name="dates_to_block"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_to_block"
                                    data-vv-scope="formReleased"
                                    v-model="date_to" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon5">
                                <i class="far fa-calendar"></i>
                            </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('formReleased.dates_to_block')">
                            <span>{{ errors.first('formReleased.dates_to_block') }}</span>
                        </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-2 mt-2">
                            <hr>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-2">
                            <label for="rates">Tipo de liberado</label>
                            <select class="form-control" name="type" id="type" v-model="type"
                                    data-vv-scope="formReleased"
                                    v-validate="'required'">
                                <option value="room">Por Habitación</option>
                                <option value="passenger">Por pasajero</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label for="rooms">Habitación</label>
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="rooms"
                                         placeholder="Elegir habitaciones"
                                         :preserve-search="true"
                                         group-values="rooms"
                                         group-label="select_all"
                                         :group-select="true"
                                         :taggable="true"
                                         v-model="room_ids"
                                         label="name"
                                         ref="multiselect"
                                         track-by="room_id"
                                         name="rooms"
                                         id="rooms"
                                         data-vv-scope="formReleased"
                                         v-validate="'required'">
                            </multiselect>
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.rooms')">
                                <span>{{ errors.first('formReleased.rooms') }}</span>
                        </span>
                        </div>
                        <div class="col-1">
                            <label for="to">Hasta</label>
                            <input type="number" id="to" name="to" v-model="to" class="form-control"
                                   data-vv-scope="formReleased"
                                   v-validate="'required'">
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.to')">
                                <span>{{ errors.first('formReleased.to') }}</span>
                            </span>
                        </div>
                        <div class="col-2">
                            <label for="qty_released">Cant. Liberado</label>
                            <input type="number" id="qty_released" v-model="qty_released" class="form-control"
                                   name="qty_released"
                                   data-vv-scope="formReleased"
                                   v-validate="'required|min_value:1'">
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.qty_released')">
                                <span>{{ errors.first('formReleased.qty_released') }}</span>
                            </span>
                        </div>
                        <div class="col-1">
                            <label for="limit">Limite a liberar</label>
                            <input type="number" id="limit" name="limit" v-model="limit"
                                   class="form-control"
                                   data-vv-scope="formReleased"
                                   v-validate="'required|min_value:1'">
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.limit')">
                                <span>{{ errors.first('formReleased.limit') }}</span>
                            </span>
                        </div>
                        <div class="col-2">
                            <label for="type_discount">Tipo de descuento</label>
                            <select class="form-control" name="type_discount" id="type_discount"
                                    data-vv-scope="formReleased"
                                    v-model="type_discount">
                                <option value="percentage">Por %</option>
                                <option value="amount">Por Importe</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label for="value">Valor descuento</label>
                            <input type="number" id="value" v-model="value" class="form-control" name="value"
                                   data-vv-scope="formReleased"
                                   v-validate="'required|min_value:0'">
                            <span class="invalid-feedback-select" v-show="errors.has('formReleased.value')">
                                <span>{{ errors.first('formReleased.value') }}</span>
                            </span>
                        </div>
                        <div class="col-3">
                            <label>.</label><br>
                            <button class="btn btn-success" type="button" @click="validateBeforeSubmit()"> Guardar
                            </button>
                            <button class="btn btn-danger" type="button" @click="cancelForm()"> Cancelar</button>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-sm-2 mb-2">
                        <label for="period">Periodo</label>
                        <select @change="searchPeriod" ref="period" class="form-control" id="period"
                                required
                                size="0" v-model="selectPeriod">
                            <option :value="year.value" v-for="year in years" :key="year">
                                {{ year.text}}
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-8 mb-2"></div>
                    <div class="col-sm-2 mb-2">
                        <label>.</label><br>
                        <b-button-group class="float-right">
                            <b-dropdown right text="Opciones">
                                <b-dropdown-item @click="showModalDuplicate()">
                                    Duplicar periodo
                                </b-dropdown-item>
                            </b-dropdown>
                        </b-button-group>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <b-card-group deck>
                            <b-card header="Tarifas" no-body>
                                <b-list-group>
                                    <b-list-group-item :class="{ active: item.active }" href="#"
                                                       class="d-flex justify-content-between align-items-center"
                                                       @click="getRanges(item,index)"
                                                       v-for="(item,index) in released" :key="index">
                                        {{item.rate_plan.name}}
                                        <b-badge variant="danger" pill
                                                 @click="showModal(item.id,item.rate_plan.name)"
                                                 v-if="$can('delete', 'hotelreleased')">
                                            <i class="fas fa-trash"></i>
                                        </b-badge>
                                    </b-list-group-item>
                                    <b-list-group-item v-if="released.length === 0">
                                        No hay tarifas agregadas
                                    </b-list-group-item>
                                </b-list-group>
                            </b-card>
                        </b-card-group>
                    </div>
                    <div class="col-9">
                        <table-client :columns="table_hotel_released_range.columns"
                                      :data="released_ranges"
                                      :options="tableOptionsHotelRatePlanRange" id="dataTable"
                                      theme="bootstrap4">
                            <div class="table-translations" slot="id" slot-scope="props" style="padding: 12px;">
                                {{props.row.id }}
                            </div>
                            <div class="table-translations" slot="date_from" slot-scope="props" style="padding: 12px;">
                                {{props.row.date_from | formatDateRange}}
                            </div>
                            <div class="table-translations" slot="date_to" slot-scope="props" style="padding: 12px;">
                                {{props.row.date_to | formatDateRange}}
                            </div>
                            <div slot="options" slot-scope="props" style="padding: 15px;">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    <b-dropdown-item-button
                                        @click="showModalAddParams(props.row)"
                                        class="m-0 p-0"
                                        v-if="$can('create', 'hotelreleased')">
                                        <font-awesome-icon :icon="['fas', 'plus']" class="m-0"/>
                                        Agregar
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button
                                        @click="showModalDeleteRange(props.row.id)"
                                        class="m-0 p-0"
                                        v-if="$can('delete', 'hotelreleased')">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>
                            <div slot="child_row" slot-scope="props" class="m-2" style="padding: 5px;">
                                <table-client :columns="table_hotel_released_range_params.columns"
                                              :data="props.row.released_params"
                                              :options="tableOptionsHotelRatePlanRangeParams" id="dataTable"
                                              theme="bootstrap4">
                                    <div class="table-translations" slot="room_id" slot-scope="props"
                                         style="padding: 12px;">
                                        <strong class="font-weight-bold" v-if="props.row.room">{{props.row.room.translations[0].value
                                            }}</strong>
                                        <span class="font-weight-bold" v-else> - </span>
                                    </div>
                                    <div class="table-translations" slot="type" slot-scope="props"
                                         style="padding: 12px;">
                                        <span class="badge badge-success" style="font-size: 13px"
                                              v-if="props.row.type === 'room'">POR HABITACIÓN</span>
                                        <span class="badge badge-warning" v-if="props.row.type === 'passenger'"
                                              style="font-size: 13px">POR PASAJERO</span>
                                    </div>
                                    <div class="table-translations" slot="type_discount" slot-scope="props"
                                         style="padding: 12px;">
                                <span class="badge badge-success" v-if="props.row.type_discount === 'percentage'"
                                      style="font-size: 13px">POR PORCENTAJE</span>
                                        <span class="badge badge-success" v-if="props.row.type_discount === 'amount'"
                                              style="font-size: 13px">POR IMPORTE</span>
                                        <span class="badge badge-success" v-if="props.row.type_discount === 'passenger'"
                                              style="font-size: 13px">CANT. PASAJERO</span>
                                    </div>
                                    <div slot="options" slot-scope="props" style="padding: 15px;">
                                        <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                            <template slot="button-content">
                                                <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                            </template>
                                            <b-dropdown-item-button
                                                @click="showModalEditParams(props.row)"
                                                class="m-0 p-0"
                                                v-if="$can('create', 'hotelreleased')">
                                                <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                                Editar
                                            </b-dropdown-item-button>
                                            <b-dropdown-item-button
                                                @click="showModalRoom(props.row.id,((props.row.room && props.row.room.translations.length > 0) ? props.row.room.translations[0].value : 'Liberado por pasajero'))"
                                                class="m-0 p-0"
                                                v-if="$can('delete', 'hotelreleased')">
                                                <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                                {{$t('global.buttons.delete')}}
                                            </b-dropdown-item-button>
                                        </b-dropdown>
                                    </div>
                                </table-client>
                            </div>
                        </table-client>
                    </div>
                </div>
            </b-card-text>
        </div>
        <b-modal :title="rate_name" centered ref="modal-add-params" size="lg">
            <div class="row">
                <div class="col-3">
                    <label for="rates">Tipo de liberado</label>
                    <select class="form-control" name="type_param" id="type_param" v-model="type_param"
                            data-vv-scope="formReleasedRangeParam"
                            v-validate="'required'">
                        <option value="room">Por Habitación</option>
                        <option value="passenger">Por pasajero</option>
                    </select>
                </div>
                <div class="col-5">
                    <label for="rooms">Habitación</label>
                    <multiselect :clear-on-select="false"
                                 :close-on-select="false"
                                 :multiple="true"
                                 :options="rooms_param"
                                 placeholder="Elegir habitaciones"
                                 :preserve-search="true"
                                 group-values="rooms"
                                 group-label="select_all"
                                 :group-select="true"
                                 :taggable="true"
                                 v-model="room_ids"
                                 label="name"
                                 ref="multiselect"
                                 track-by="room_id"
                                 name="rooms_param"
                                 id="rooms_param"
                                 data-vv-scope="formReleasedRangeParam"
                                 v-validate="'required'">
                    </multiselect>
                    <span class="invalid-feedback-select" v-show="errors.has('formReleasedRangeParam.rooms_param')">
                                <span>{{ errors.first('formReleasedRangeParam.rooms_param') }}</span>
                        </span>
                </div>
                <div class="col-2">
                    <label for="to">Hasta</label>
                    <input type="number" id="to_param" name="to_param" v-model="to_param" class="form-control"
                           data-vv-scope="formReleasedRangeParam"
                           v-validate="'required|min_value:1'">
                    <span class="invalid-feedback-select" v-show="errors.has('formReleasedRangeParam.to_param')">
                                <span>{{ errors.first('formReleasedRangeParam.to_param') }}</span>
                            </span>
                </div>
                <div class="col-2 mt-2">
                    <label for="qty_released">Cant. Liberado</label>
                    <input type="number" id="qty_released_param" v-model="qty_released_param" class="form-control"
                           data-vv-scope="formReleasedRangeParam"
                           name="qty_released_param"
                           v-validate="'required|min_value:1'">
                    <span class="invalid-feedback-select"
                          v-show="errors.has('formReleasedRangeParam.qty_released_param')">
                                <span>{{ errors.first('formReleasedRangeParam.qty_released_param') }}</span>
                            </span>
                </div>
                <div class="col-2">
                    <label for="limit_param">Limite a liberar</label>
                    <input type="number" id="limit_param" name="limit_param" v-model="limit_param" class="form-control"
                           data-vv-scope="formReleasedRangeParam"
                           v-validate="'required|min_value:1'">
                </div>
                <div class="col-3 mt-2">
                    <label for="type_discount">Tipo de descuento</label>
                    <select class="form-control" name="type_discount_param" id="type_discount_param"
                            data-vv-scope="formReleasedRangeParam"
                            v-model="type_discount_param">
                        <option value="percentage">Por %</option>
                        <option value="amount">Por Importe</option>
                    </select>
                </div>
                <div class="col-2 mt-2">
                    <label for="value">Valor descuento</label>
                    <input type="number" id="value_param" v-model="value_param" class="form-control" name="value_param"
                           data-vv-scope="formReleasedRangeParam"
                           v-validate="'required|min_value:1'">
                    <span class="invalid-feedback-select" v-show="errors.has('formReleasedRangeParam.value_param')">
                                <span>{{ errors.first('formReleasedRangeParam.value_param') }}</span>
                            </span>
                </div>
            </div>
            <div slot="modal-footer">
                <button type="button" @click="validateBeforeSubmitRange()" class="btn btn-success"
                        :disabled="loadingFrm">
                    <i class="fa fa-spin fa-spinner" v-if="loadingFrm"></i>
                    <font-awesome-icon :icon="['fas', 'dot-circle']" v-else/>
                    {{$t('global.buttons.save')}}
                </button>
                <button type="button" @click="cancelModalAddParams()" class="btn btn-danger" :disabled="loadingFrm">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <b-modal :title="'Editar'" centered ref="modal-edit-params" size="lg">
            <div class="row">
                <div class="col-3">
                    <label>Tipo de liberado</label>
                    <label class="form-control">
                        <span v-if="type_param === 'room'">Por Habitación</span>
                        <span v-if="type_param === 'passenger'">Por pasajero</span>
                    </label>
                </div>
                <div class="col-5" v-if="type_param === 'room'">
                    <label for="rooms">Habitación</label>
                    <label for="rooms" class="form-control">{{room_name}}</label>
                </div>
                <div class="col-2">
                    <label for="to">Hasta</label>
                    <input type="number" id="to_param_" name="to_param_" v-model="to_param" class="form-control"
                           data-vv-scope="formReleasedEditRangeParam"
                           v-validate="'required'">
                    <span class="invalid-feedback-select" v-show="errors.has('formReleasedRangeParam.to_param')">
                                <span>{{ errors.first('formReleasedRangeParam.to_param') }}</span>
                            </span>
                </div>
                <div class="col-2 mt-2">
                    <label for="qty_released_param_">Cant. Liberado</label>
                    <input type="number" id="qty_released_param_" v-model="qty_released_param" class="form-control"
                           data-vv-scope="formReleasedEditRangeParam"
                           name="qty_released_param_"
                           v-validate="'required|min_value:1'">
                    <span class="invalid-feedback-select"
                          v-show="errors.has('formReleasedEditRangeParam.qty_released_param')">
                                <span>{{ errors.first('formReleasedEditRangeParam.qty_released_param') }}</span>
                            </span>
                </div>
                <div class="col-2">
                    <label for="limit_param_">Limite a liberar</label>
                    <input type="number" id="limit_param_" name="limit_param_" v-model="limit_param"
                           class="form-control"
                           data-vv-scope="formReleasedEditRangeParam"
                           v-validate="'required|min_value:1'">
                </div>
                <div class="col-3 mt-2">
                    <label for="type_discount_param_">Tipo de descuento</label>
                    <select class="form-control" name="type_discount_param_" id="type_discount_param_"
                            data-vv-scope="formReleasedEditRangeParam"
                            v-model="type_discount_param">
                        <option value="percentage">Por %</option>
                        <option value="amount">Por Importe</option>
                    </select>
                </div>
                <div class="col-2 mt-2">
                    <label for="value">Valor descuento</label>
                    <input type="number" id="value_param_" v-model="value_param" class="form-control" name="value"
                           data-vv-scope="formReleasedEditRangeParam"
                           v-validate="'required|min_value:1'">
                    <span class="invalid-feedback-select" v-show="errors.has('formReleasedEditRangeParam.value_param')">
                        <span>{{ errors.first('formReleasedEditRangeParam.value_param') }}</span>
                    </span>
                </div>
            </div>
            <div slot="modal-footer">
                <button type="button" @click="validateBeforeSubmitEditParam()" class="btn btn-success"
                        :disabled="loadingFrm">
                    <i class="fa fa-spin fa-spinner" v-if="loadingFrm"></i>
                    <font-awesome-icon :icon="['fas', 'dot-circle']" v-else/>
                    {{$t('global.buttons.save')}}
                </button>
                <button type="button" @click="cancelModalAddParams()" class="btn btn-danger" :disabled="loadingFrm">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <b-modal :title="'Duplicar periodo'" centered ref="my-modal-duplicate" size="md">
            <div class="row">
                <div class="col-12">
                    <label for="rates">Tarifas</label>
                    <multiselect :clear-on-select="false"
                                 :close-on-select="false"
                                 :multiple="true"
                                 :options="rates"
                                 placeholder="Elegir tarifas"
                                 :preserve-search="true"
                                 :taggable="true"
                                 group-values="rates"
                                 group-label="select_all"
                                 :group-select="true"
                                 v-model="rates_ids"
                                 label="name"
                                 ref="multiselect"
                                 track-by="id"
                                 id="rates_duplicate"
                                 name="rates_duplicate"
                                 data-vv-scope="formDuplicate"
                                 v-validate="'required'">
                    </multiselect>
                    <span class="invalid-feedback-select" v-show="errors.has('formDuplicate.rates')">
                                <span>{{ errors.first('formDuplicate.rates') }}</span>
                        </span>
                </div>
                <div class="col-12 mb-2 mt-2">
                    <label for="rates">Seleccione el periodo a duplicar</label>
                    <select ref="period_to_copy" class="form-control" id="period_to_copy"
                            size="0" v-model="period_to_copy">
                        <option :value="year.value" v-for="year in years" :key="year">
                            {{ year.text}}
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-2 mt-2">
                    <label for="rates">Seleccione el periodo</label>
                    <select ref="period_to_duplicate" class="form-control" id="period_to_duplicate"
                            size="0" v-model="period_to_duplicate">
                        <option :value="year.value" v-for="year in years" :key="year">
                            {{ year.text}}
                        </option>
                    </select>
                </div>
                <div class="col-12 mb-2 mt-2">
                    <p class="alert alert-info">
                        Nota: Si el periodo que va a duplicar ya existen registros, estos registros se sobreescribirán.
                    </p>
                </div>
            </div>
            <div slot="modal-footer">
                <button type="button" @click="validateBeforeSubmitDuplicate()" class="btn btn-success"
                        :disabled="loadingFrm">
                    <i class="fa fa-spin fa-spinner" v-if="loadingFrm"></i>
                    <font-awesome-icon :icon="['fas', 'dot-circle']" v-else/>
                    {{$t('global.buttons.save')}}
                </button>
                <button type="button" @click="cancelModalDuplicate()" class="btn btn-danger" :disabled="loadingFrm">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <b-modal :title="title_modal" centered ref="my-modal-rate" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button type="button" @click="removeRate()" class="btn btn-success">{{$t('global.buttons.accept')}}
                </button>
                <button type="button" @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <b-modal :title="title_modal" centered ref="my-modal-room" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button type="button" @click="removeRoom()" class="btn btn-success">{{$t('global.buttons.accept')}}
                </button>
                <button type="button" @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <b-modal :title="title_modal" centered ref="my-modal-range" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button type="button" @click="removeRange()" class="btn btn-success">{{$t('global.buttons.accept')}}
                </button>
                <button type="button" @click="hideModalRange()" class="btn btn-danger">{{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
    </div>
</template>
<script>
    import { API } from '../../../../api'
    import BCardText from 'bootstrap-vue/es/components/card/card-text'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import TableClient from '../../../../components/TableClient'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Multiselect from 'vue-multiselect'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import datePicker from 'vue-bootstrap-datetimepicker'
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
    import moment from 'moment'

    export default {
        components: {
            BCardText,
            BFormCheckbox,
            TableClient,
            Multiselect,
            Loading,
            BModal,
            datePicker,
            vSelect
        },
        data: () => {
            return {
                hidden: 'display:true',
                viewForm: false,
                loading: false,
                loadingFrm: false,
                released: [],
                released_ranges: [],
                rate_name: '',
                date_from: '',
                date_to: '',
                title_modal: '',
                room_name: '',
                selectPeriod: '',
                delete_id: '',
                room_ids: [],
                rates_ids: [],
                type: 'room',
                type_param: 'room',
                type_discount: 'percentage',
                type_discount_param: 'percentage',
                qty_released_param: 1,
                qty_released: 1,
                value: 0,
                value_param: 0,
                limit: 1,
                limit_param: 1,
                to: 0,
                to_param: 0,
                rooms: [
                    {
                        select_all: 'Seleccionar todo',
                        rooms: []
                    }
                ],
                rooms_param: [
                    {
                        select_all: 'Seleccionar todo',
                        rooms: []
                    }
                ],
                rates: [
                    {
                        select_all: 'Seleccionar todo',
                        rates: []
                    }
                ],
                rateSelected: [],
                roomSelected: [],
                table_hotel_released: {
                    columns: ['id', 'rate', 'options']
                },
                table_hotel_released_range: {
                    columns: ['id', 'date_from', 'date_to', 'options']
                },
                table_hotel_released_range_params: {
                    columns: ['id', 'room_id', 'type', 'to', 'qty_released', 'limit', 'type_discount', 'value', 'options']
                },
                datePickerFromOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                    widgetPositioning: { 'vertical': 'bottom' }
                },
                datePickerToOptions: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    locale: localStorage.getItem('lang'),
                    widgetPositioning: { 'vertical': 'bottom' }
                },
                hotel_rate_plan_released_range_id: '',
                hotel_rate_plan_released_range_param_id: '',
                period_to_copy: '',
                period_to_duplicate: '',
            }
        },
        computed: {
            tableOptionsHotel: function () {
                return {
                    headings: {
                        id: 'ID',
                        rate: 'Tarifa',
                        options: 'Opciones'
                    },
                    uniqueKey: 'id',
                    filterable: [],
                }
            },
            tableOptionsHotelRatePlanRange: function () {
                return {
                    headings: {
                        id: 'ID',
                        date_from: 'Desde',
                        date_to: 'Hasta',
                        options: 'Opciones'
                    },
                    uniqueKey: 'id',
                    filterable: [],
                }
            },
            tableOptionsHotelRatePlanRangeParams: function () {
                return {
                    headings: {
                        id: 'ID',
                        room_id: 'Habitación',
                        type: 'Tipo de liberado',
                        to: 'Hasta',
                        limit: 'Limite',
                        qty_released: 'Cant. de liberados',
                        type_discount: 'Tipo Descuento',
                        value: 'Valor',
                        options: 'Opciones'
                    },
                    uniqueKey: 'id',
                    filterable: [],
                }
            },
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
        created: function () {
            this.getReleased()
        },
        mounted () {
            let currentDate = new Date()
            this.selectPeriod = currentDate.getFullYear()
            this.period_to_copy = currentDate.getFullYear()
            setTimeout(() => {
                this.hidden = 'display:none'
            }, 500)
            this.getRatesHotel(this.$i18n.locale)

        },
        methods: {
            getReleased: function () {
                this.loading = true
                API.get('/hotel_released?hotel_id=' + this.$route.params.hotel_id + '&year=' + this.selectPeriod)
                    .then((result) => {
                        this.loading = false
                        let released = []
                        let released_ranges = []
                        result.data.data.forEach(function (item, index, array) {
                            item.active = false
                            if (index === 0) {
                                item.active = true
                                released_ranges = item.released_ranges
                            }
                            released.push(item)
                        })
                        this.released = released
                        this.released_ranges = released_ranges
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('hotelsmanagehotelgallery.error.messages.connection_error')
                    })
                })
            },
            createForm: function () {
                this.viewForm = true
            },
            cancelForm: function () {
                this.viewForm = false
            },
            rateChange: function (value) {
                let select = value
                if (select != null) {
                    this.getRooms()
                }
            },
            getRooms: function () {
                this.loading = true
                let rates_id = []
                this.rates_ids.forEach(c => {
                    rates_id.push(c.id)
                })
                let data = {
                    rates_id: rates_id
                }
                API.post('hotel_released/rates/rooms', data)
                    .then((result) => {
                        this.loading = false
                        console.log(result.data.success === true)

                        if (result.data.success === true) {
                            this.rooms[0].rooms = result.data.data
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
            },
            getRatesHotel: function (lang) {
                this.loading = true
                API.get('rates/cost/' + this.$route.params.hotel_id + '/?lang=' + lang + '&status=1')
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.rates[0].rates = result.data.data
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
            },
            validateBeforeSubmit: function () {
                this.$validator.validateAll('formReleased').then((result) => {
                    if (result) {
                        this.save()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Liberados',
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            validateBeforeSubmitRange: function () {
                this.$validator.validateAll('formReleasedRangeParam').then((result) => {
                    if (result) {
                        this.saveRangeFrom()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Liberados',
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            validateBeforeSubmitEditParam: function () {
                this.$validator.validateAll('formReleasedEditRangeParam').then((result) => {
                    if (result) {
                        this.editRangeFrom()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Liberados',
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            validateBeforeSubmitDuplicate: function () {
                this.$validator.validateAll('formDuplicate').then((result) => {
                    if (result) {
                        this.submitDuplicate()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Liberados',
                            text: this.$t('servicesmanageserviceincludes.error.messages.information_complete')
                        })
                        this.loading = false
                    }

                })
            },
            submitDuplicate: function () {
                this.loadingFrm = true
                let rates_id = []
                this.rates_ids.forEach(c => {
                    rates_id.push(c.id)
                })
                let data = {
                    rates_plans_id: rates_id,
                    year_copy: this.period_to_copy,
                    year_apply: this.period_to_duplicate
                }
                API({
                    method: 'POST',
                    url: 'hotel_released/duplicate/period',
                    data: data
                })
                    .then((result) => {
                        this.loadingFrm = false
                        if (result.data.success === true) {
                            this.getReleased()
                            this.cancelModalDuplicate()
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Liberados',
                                text: 'Se duplico correctamente'
                            })
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: 'Error al duplicar'
                            })
                        }
                    }).catch(() => {
                    this.loadingFrm = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            save: function () {
                this.loading = true
                let rates_id = []
                let rooms_id = []
                this.rates_ids.forEach(c => {
                    rates_id.push(c.id)
                })
                this.room_ids.forEach(c => {
                    rooms_id.push(c.room_id)
                })
                let data = {
                    rates_plans_id: rates_id,
                    rooms_id: rooms_id,
                    type: this.type,
                    date_from: moment(this.date_from, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                    date_to: moment(this.date_to, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                    limit: this.limit,
                    to: this.to,
                    type_discount: this.type_discount,
                    value: this.value,
                    qty_released: this.qty_released,
                }
                API({
                    method: 'POST',
                    url: 'hotel_released',
                    data: data
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            this.getReleased()
                            this.cancelForm()
                        } else {
                            if (result.data.error === 'RANGE_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el rango de fechas para las tarifas seleccionadas'
                                })
                            }

                            if (result.data.error === 'ROOM_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el tipo de liberado en las tarifas seleccionadas'
                                })
                            }

                        }
                    }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            showModal (id, inclusion) {
                this.delete_id = id
                this.title_modal = inclusion
                this.$refs['my-modal-rate'].show()
            },
            showModalRoom (id, inclusion) {
                this.delete_id = id
                this.title_modal = inclusion
                this.$refs['my-modal-room'].show()
            },
            hideModal () {
                this.$refs['my-modal-rate'].hide()
                this.$refs['my-modal-room'].hide()
            },
            removeRate: function () {
                API({
                    method: 'DELETE',
                    url: 'hotel_released/rate/' + this.delete_id,
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.getReleased()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            removeRoom: function () {
                API({
                    method: 'DELETE',
                    url: 'hotel_released/' + this.delete_id,
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.getReleased()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            removeRange: function () {
                API({
                    method: 'DELETE',
                    url: 'hotel_released/range/' + this.hotel_rate_plan_released_range_id,
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.hotel_rate_plan_released_range_id = ''
                            this.hideModalRange()
                            this.getReleased()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: this.$t('servicesmanageserviceincludes.error.messages.service_delete')
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            setDateFrom (e) {
                this.date_to = ''
                this.$refs.datePickerTo.dp.minDate(e.date)
            },
            searchPeriod: function () {
                this.getReleased()
            },
            editRangeFrom: function () {
                this.loadingFrm = true
                let data = {
                    hotel_rate_plan_released_range_param_id: this.hotel_rate_plan_released_range_param_id,
                    type_discount: this.type_discount_param,
                    value: this.value_param,
                    limit: this.limit_param,
                    to: this.to_param,
                    qty_released: this.qty_released_param,
                }
                API({
                    method: 'POST',
                    url: 'hotel_released/range_released_param',
                    data: data
                })
                    .then((result) => {
                        this.loadingFrm = false
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Liberados',
                                text: 'Se guardo correctamente'
                            })
                            this.getReleased()
                            this.cancelModalAddParams()
                        } else {
                            if (result.data.error === 'RANGE_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el rango de fechas para las tarifas seleccionadas'
                                })
                            }

                            if (result.data.error === 'ROOM_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el tipo de liberado en las tarifas seleccionadas'
                                })
                            }
                        }
                    }).catch(() => {
                    this.loadingFrm = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            saveRangeFrom: function () {
                this.loadingFrm = true
                let rooms_id = []
                this.room_ids.forEach(c => {
                    rooms_id.push(c.room_id)
                })
                let data = {
                    hotel_rate_plan_released_range_id: this.hotel_rate_plan_released_range_id,
                    rooms_id: rooms_id,
                    type: this.type_param,
                    limit: this.limit_param,
                    to: this.to_param,
                    type_discount: this.type_discount_param,
                    value: this.value_param,
                    qty_released: this.qty_released_param,
                }
                API({
                    method: 'POST',
                    url: 'hotel_released/range_released',
                    data: data
                })
                    .then((result) => {
                        this.loadingFrm = false
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Liberados',
                                text: 'Se guardo satisfactoriamente'
                            })
                            this.getReleased()
                            this.cancelModalAddParams()
                        } else {
                            if (result.data.error === 'RANGE_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el rango de fechas para las tarifas seleccionadas'
                                })
                            }

                            if (result.data.error === 'ROOM_EXIST') {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: 'Liberados',
                                    text: 'Ya tienes registrado el tipo de liberado en las tarifas seleccionadas'
                                })
                            }
                        }
                    }).catch(() => {
                    this.loadingFrm = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('servicesmanageserviceincludes.error.messages.connection_error')
                    })
                })
            },
            cancelModalAddParams: function () {
                this.rate_name = ''
                this.room_ids = []
                this.$refs['modal-add-params'].hide()
                this.$refs['modal-edit-params'].hide()
            },
            showModalEditParams: function (row) {
                this.hotel_rate_plan_released_range_param_id = row.id
                this.room_name = (row.room && row.room.translations.length > 0) ? row.room.translations[0].value : 'Liberado por pasajero'
                this.type_param = row.type
                this.limit_param = row.limit
                this.to_param = row.to
                this.type_discount_param = row.type_discount
                this.value_param = row.value
                this.$refs['modal-edit-params'].show()
            },
            showModalAddParams: function (row) {
                this.rate_name = ''
                this.hotel_rate_plan_released_range_id = row.id
                this.getRangeParams()
                this.$refs['modal-add-params'].show()
            },
            getRangeParams: function () {
                this.loadingFrm = true
                this.room_ids = []
                let data = {
                    hotel_rate_plan_released_id: this.hotel_rate_plan_released_range_id
                }
                API.post('hotel_released/rates/rooms/byRange', data)
                    .then((result) => {
                        this.loadingFrm = false
                        if (result.data.success === true) {
                            this.rate_name = result.data.data.rate_plan_name
                            this.rooms_param[0].rooms = result.data.data.rooms
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Fetch Error',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.loadingFrm = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Liberados',
                        text: this.$t('hotelsmanagehotelratesratescost.error.messages.connection_error')
                    })
                })
            },
            showModalDeleteRange: function (id) {
                this.hotel_rate_plan_released_range_id = id
                this.$refs['my-modal-range'].show()
            },
            showModalDuplicate: function (id) {
                this.$refs['my-modal-duplicate'].show()
            },
            hideModalRange: function () {
                this.hotel_rate_plan_released_range_id = ''
                this.$refs['my-modal-range'].hide()
            },
            cancelModalDuplicate: function () {
                this.rates_ids = []
                this.$refs['my-modal-duplicate'].hide()
            },
            getRanges: function (item,index) {
                this.released_ranges = item.released_ranges
                this.released.forEach(function(item){
                    item.active = false
                })
                item.active = true
            }
        }, filters: {
            formatDateRange: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>
<style scoped>
    .buttons {
        margin-top: 35px;
    }

    .btn-primary {
        background-color: #005ba5;
        color: white;
        border-color: #005ba5;
    }

    .btn-primary:hover {
        background-color: #0b4d75;
    }
</style>

