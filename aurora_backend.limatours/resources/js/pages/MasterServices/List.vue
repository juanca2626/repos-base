<template>
    <div class="container-fluid">

        <div v-show="show_list">

            <div class="row col-12 no-margin">
                <div class="col-3 card " style="background: #dfffff;">
                    <button @click="filter_by_group(g_all)"
                            class="btn" type="submit"
                            style="float: right; margin-right: 5px;">
                        <font-awesome-icon v-if="g_all.check" :icon="['fas', 'check-square']" />
                        <font-awesome-icon v-if="!g_all.check" :icon="['fas', 'square']" />
                        {{g_all.name}}
                    </button>
                </div>
                <div class="col-3 card" :class="{'check-true':g_parents.check}">
                    <button @click="filter_by_group(g_parents)"
                            class="btn" type="submit"
                            style="float: right; margin-right: 5px;">
                        <font-awesome-icon v-if="g_parents.check" :icon="['fas', 'check-square']" />
                        <font-awesome-icon v-if="!g_parents.check" :icon="['fas', 'square']" />
                        {{g_parents.name}}
                    </button>
                </div>
                <div class="col-3 card" :class="{'check-true':g_directs.check}">
                    <button @click="filter_by_group(g_directs)"
                            class="btn" type="submit"
                            style="float: right; margin-right: 5px;">
                        <font-awesome-icon v-if="g_directs.check" :icon="['fas', 'check-square']" />
                        <font-awesome-icon v-if="!g_directs.check" :icon="['fas', 'square']" />
                        {{g_directs.name}}
                    </button>
                </div>
                <div class="col-3 card" :class="{'check-true':g_components.check}">
                    <button @click="filter_by_group(g_components)"
                            class="btn" type="submit"
                            style="float: right; margin-right: 5px;">
                        <font-awesome-icon v-if="g_components.check" :icon="['fas', 'check-square']" />
                        <font-awesome-icon v-if="!g_components.check" :icon="['fas', 'square']" />
                        {{g_components.name}}
                    </button>
                </div>
            </div>

            <div class="row col-12 no-margin">
                <input class="form-control" id="search_master_services" type="search" v-model="query_master_services"
                       value="" placeholder="Buscar por texto">
            </div>

            <div class="row col-12 no-margin">
                <div class="table-responsive">
                    <table class="VueTables__table table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="vueTable_column_n">
                                <span title="" class="VueTables__heading">#</span>
                            </th>
                            <th class="vueTable_column_id">
                                <span title="" class="VueTables__heading">ID</span>
                            </th>
                            <th class="vueTable_column_code">
                                <span title="" class="VueTables__heading">Código</span>
                            </th>
                            <th class="vueTable_column_classification">
                                <span title="" class="VueTables__heading">Clasificación</span>
                            </th>
                            <th class="vueTable_column_cities">
                                <span title="" class="VueTables__heading">Ciudades</span>
                            </th>
                            <th class="vueTable_column_description">
                                <span title="" class="VueTables__heading">Descripción</span>
                            </th>
                            <th class="vueTable_column_status">
                                <span title="" class="VueTables__heading">Estado</span>
                            </th>
                            <th class="vueTable_column_options">
                                <span title="" class="VueTables__heading">Opc.</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="master_services.length>0" v-for="(master_service, i) in master_services">
                            <td class="vueTable_column_n">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ i + 1 + ((pageChosen-1)*limit) }}
                                </div>
                            </td>
                            <td class="vueTable_column_id">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ master_service.id }}
                                </div>
                            </td>
                            <td class="vueTable_column_code">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ master_service.code }}
                                </div>
                            </td>
                            <td class="vueTable_column_classification">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ master_service.classification }}
                                </div>
                            </td>
                            <td class="vueTable_column_cities">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ master_service.city_in_iso }} > {{ master_service.city_out_iso }}
                                </div>
                            </td>
                            <td class="vueTable_column_description">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    <span v-html="master_service.description"></span>
                                </div>
                            </td>
                            <td class="vueTable_column_status">
                                <div class="table-master-services" style="font-size: 0.9em;">
                                    {{ master_service.status_ifx }}
                                </div>
                            </td>
                            <td class="vueTable_column_status">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    <b-dropdown-item-button @click="view_more_info(master_service)" class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'search']" class="m-0"/>
                                        Más Información
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button @click="will_free(master_service)" class="m-0 p-0">
                                        <i class="fa fa-wind m-0" style="color: black;"></i>
                                        Liberar
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'cubes']" class="m-0"/>
                                        Componentes
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </td>
                        </tr>

                        <tr v-if="master_services.length==0" class="trPadding">
                            <td colspan="8">
                                <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
                                <center><span v-if="!loading">Ninguno por mostrar</span></center>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>

            <div class="VuePagination row col-md-12 justify-content-center content-scroll-y">
                <nav class="text-center">
                    <ul class="pagination_ VuePagination__pagination" style="">
                        <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                            'disabled':(pageChosen==1 || loading)}" @click="setPage(pageChosen-1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==1 || loading)" class="page-link">&lt;</a>
                        </li>
                        <li v-for="page in master_service_pages" @click="setPage(page)"
                            :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosen), 'disabled':loading }">
                            <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                        </li>
                        <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                            'disabled':(pageChosen==master_service_pages.length || loading)}" @click="setPage(pageChosen+1)">
                            <a href="javascript:void(0);" :disabled="(pageChosen==master_service_pages.length || loading)" class="page-link">&gt;</a>
                        </li>
                    </ul>
                </nav>


                <b-modal title="Información del Servicio" ref="modal-info" hide-footer size="lg">

                    <h4>Información detallada del servicio</h4>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>[codigo] Código:</strong><br><span>{{ master_service_selected.code }}</span></p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[clasvs] Clasificación:</strong><br><span>{{ master_service_selected.classification }}</span></p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[tipo] Tipo:</strong><br><span>{{ master_service_selected.type_iso }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>[descri] Descripción:</strong><br><span>{{ master_service_selected.description }}</span></p>
                            </div>
                            <div class="col-sm-8">
                                <p><strong>[lintlx] Descripción Larga:</strong><br><span>{{ master_service_selected.description_large }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>[codgru] País:</strong><br>
                                <span>{{ master_service_selected.country_iso }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[ciudes] Ciudad In:</strong><br>
                                <span>{{ master_service_selected.city_in_iso }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[ciuout] Ciudad Out:</strong><br>
                                <span>{{ master_service_selected.city_out_iso }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>[preped] Prestador al q se le pide el svs:</strong><br>
                                <span>{{ master_service_selected.provider_code_request }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[prefac] Prestador al q se factura:</strong><br>
                                <span>{{ master_service_selected.provider_code_bill }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>[prevou] Prestador al q se le envía el voucher:</strong><br>
                                <span>{{ master_service_selected.provider_code_voucher }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-3">
                                <p><strong>[unidad] Unidad de venta:</strong><br>
                                <span>{{ master_service_selected.unit }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[diario] Forma de tarifación segun tiempo en q se da el svs:</strong><br>
                                <span>{{ master_service_selected.pricing_code_time }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[paxuni] Forma de tarifación respecto a la venta svs:</strong><br>
                                <span>{{ master_service_selected.pricing_code_sale }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[via] Si envia comunicación al prestador:</strong><br>
                                <span>{{ master_service_selected.allow_provider_email }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-3">
                                <p><strong>[vouche] Si envia voucher:</strong><br>
                                <span>{{ master_service_selected.allow_voucher }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[itiner] Si esta dentro de la generacion del itinerario:</strong><br>
                                <span>{{ master_service_selected.allow_itinerary }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[asigna] Si el servicio es asignable:</strong><br>
                                <span>{{ master_service_selected.assignable }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[cantnt] Cantidad de noches:</strong><br>
                                <span>{{ master_service_selected.nights }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-3">
                                <p><strong>[basein] Si permite margen/markup:</strong><br>
                                <span>{{ master_service_selected.allow_markup }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[ctavta] Cuenta contable venta:</strong><br>
                                <span>{{ master_service_selected.accounting_account_sale }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[ctacos] Cuenta contable costo:</strong><br>
                                <span>{{ master_service_selected.accounting_account_cost }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[interm] Intermediacion, dato para facturacion:</strong><br>
                                <span>{{ master_service_selected.intermediation }}</span>
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-3">
                                <p><strong>[operad] Estado:</strong><br>
                                <span>{{ master_service_selected.status_ifx }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[codaux] Codaux:</strong><br>
                                <span>{{ master_service_selected.codaux }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                                <p><strong>[codfac] Si el servicio es de exportación o no:</strong><br>
                                <span>{{ master_service_selected.allow_export }}</span>
                                </p>
                            </div>
                            <div class="col-sm-3">
                            </div>
                        </div>
                    </div>

                </b-modal>

            </div>

        </div>

        <div v-show="show_free">

            <div class="row mb-3">
                <button class="btn btn-danger" @click="show_free=0; show_list=1">< Volver</button>
            </div>

            <div class="row col-12 no-margin mb-5">
                <div class="col-10">
                    <h3 v-if="free_message===''">
                        <i class="fa fa-wind"></i> Liberar servicio [{{ master_service_selected.code }}] - {{ master_service_selected.description }}
                    </h3>
                    <h3 v-else>
                        Servicio [{{ master_service_selected.code }}] - {{ master_service_selected.description }}<br><br>
                        <span class="aler alert-warning"> <i class="fa fa-info-circle"></i> {{ free_message }}</span>
                    </h3>
                </div>
            </div>

            <div class="row col-12 no-margin" v-if="free_message===''">

                <div class="col-12">
                    <b-card-text>
                        <div class="text-right">
                            <button class="btn btn-success mb-4" type="button" @click="createForm" v-if="!viewForm"> Agregar
                                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                            </button>
                        </div>

                        <form v-show="viewForm">
                            <div class="row">
                                <div class="col-3">
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
                                            autocomplete="off"
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
                                <div class="col-3">
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
                                            autocomplete="off"
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
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-3">
                                    <label for="every_">1 Liberado por cada:</label>
                                    <input type="number" id="every_" name="every_" v-model="every"
                                           class="form-control"
                                           data-vv-scope="formReleased"
                                           :v-validate="'required|min_value:0|max_value:40'">
                                    <span class="invalid-feedback-select" v-show="errors.has('formReleased.every_')">
                                        <span>{{ errors.first('formReleased.every_') }}</span>
                                    </span>
                                </div>
                                <div class="col-2">
                                    <label for="to">.
                                    </label>
                                    <div class="input-group mb-3">Paxs pagados</div>
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
                                    <button class="btn btn-danger" type="button" @click="viewForm = false"> Cancelar</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-sm-2 mb-2">
                                <label for="period">Periodo</label>
                                <select @change="searchPeriod" ref="period" class="form-control" id="period"
                                        required
                                        size="0" v-model="selectPeriod">
                                    <option :value="year.value" v-for="year in years">
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

                        <table-client :columns="table_service_released.columns" :data="released"
                                      :options="tableOptionsService" id="dataTable"
                                      theme="bootstrap4">
                            <div slot="id" slot-scope="props" style="padding: 15px;">
                                <strong class="font-weight-bold">{{props.row.id}}</strong>
                            </div>
                            <div class="table-translations" slot="date_from" slot-scope="props" style="padding: 12px;">
                                {{props.row.date_from | formatDateRange}}
                            </div>
                            <div class="table-translations" slot="date_to" slot-scope="props" style="padding: 12px;">
                                {{props.row.date_to | formatDateRange}}
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
                                        class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                        Editar
                                    </b-dropdown-item-button>
                                    <b-dropdown-item-button
                                        @click="showModal(props.row.id)"
                                        class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                                        {{$t('global.buttons.delete')}}
                                    </b-dropdown-item-button>
                                </b-dropdown>
                            </div>

                        </table-client>
                    </b-card-text>
                </div>


                <b-modal :title="'Editar'" centered ref="modal-edit-params" size="lg">
                    <div class="row">
                        <div class="col-3">
                            <label for="rates">Fecha desde</label>
                            <div class="input-group mb-3">
                                <date-picker
                                    :config="datePickerFromOptions"
                                    @dp-change="setDateFrom_param"
                                    ref="datePickerFrom_param"
                                    id="dates_from_block_param"
                                    name="dates_from_block_param"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_from_block"
                                    data-vv-scope="formReleasedRangeParam"
                                    v-model="date_from_param" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('formReleased.dates_from_block')">
                                    <span>{{ errors.first('formReleased.dates_from_block') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label for="rates">Fecha hasta</label>
                            <div class="input-group mb-3">
                                <date-picker
                                    :config="datePickerToOptions"
                                    ref="datePickerTo"
                                    id="dates_to_block_"
                                    name="dates_to_block_"
                                    data-vv-as="fecha"
                                    data-vv-name="dates_to_block"
                                    data-vv-scope="formReleasedRangeParam"
                                    v-model="date_to_param" v-validate="'required'">
                                </date-picker>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback-select"
                                      v-show="errors.has('formReleased.dates_to_block')">
                                    <span>{{ errors.first('formReleased.dates_to_block') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-2">
                            <label for="every_param_">1 Liberado por cada:</label>
                            <input type="number" id="every_param_" name="every_param_" v-model="every_param" class="form-control"
                                   data-vv-scope="formReleasedEditRangeParam"
                                   :v-validate="'required|min_value:0|max_value:40'">
                        </div>
                        <div class="col-2 mt-2">
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
                        <div class="col-12 mb-2 mt-2">
                            <label>Seleccione el periodo a duplicar</label>
                            <select ref="period_to_copy" class="form-control" id="period_to_copy"
                                    size="0" v-model="period_to_copy">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-2 mt-2">
                            <label>Seleccione el periodo</label>
                            <select ref="period_to_duplicate" class="form-control" id="period_to_duplicate"
                                    size="0" v-model="period_to_duplicate">
                                <option :value="year.value" v-for="year in years">
                                    {{ year.text}}
                                </option>
                            </select>
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

                <b-modal :title="title_modal" centered ref="modal-delete-release" size="sm">
                    <p class="text-center">{{$t('global.message_delete')}}</p>
                    <div slot="modal-footer">
                        <button type="button" @click="remove_release()" class="btn btn-success">{{$t('global.buttons.accept')}}
                        </button>
                        <button type="button" @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}
                        </button>
                    </div>
                </b-modal>

            </div>
        </div>

        <block-page></block-page>
    </div>

</template>

<script>
import { API } from './../../api'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BlockPage from '../../components/BlockPage'
import BModal from 'bootstrap-vue/es/components/modal/modal'
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import BDropDown from "bootstrap-vue/es/components/dropdown/dropdown";
import BDropDownItemButton from "bootstrap-vue/es/components/dropdown/dropdown-item-button";
import Multiselect from 'vue-multiselect'
import datePicker from 'vue-bootstrap-datetimepicker'
import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css'
import moment from "moment";
import TableClient from '../../components/TableClient'

export default {
    components: {
        TableClient,
        Multiselect,
        BFormCheckbox,
        BModal,
        datePicker,
        BlockPage, vSelect,
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
    },
    data: () => {
        return {
            loading: false,
            loadingFrm: false,
            ratesChoosed:[],
            packages: [],
            master_services: [],
            query_master_services: '',
            pageChosen : 1,
            limit : 20,
            master_service_pages : [],
            g_parents: {
                name: 'Padres',
                check: true
            },
            g_directs: {
                name: 'Directos',
                check: true
            },
            g_components: {
                name: 'Componentes',
                check: true
            },
            g_all: {
                name: 'Todos',
                check: true
            },
            master_service_selected: {},
            show_list: true,
            show_free: false,
            viewForm: false,
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
            date_from: '',
            date_to: '',
            master_service_selected_type: '',
            every: 15,
            type_discount: 'percentage',
            value: 100,
            free_message: '',
            selectPeriod: '',
            released: [],
            table_service_released: {
                columns: ['id', 'date_from', 'date_to', 'every', 'type_discount', 'value', 'options']
            },
            date_from_param: '',
            date_to_param: '',
            every_param: 15,
            type_discount_param: 'percentage',
            value_param: 100,
            title_modal: '',
            master_service_released_id: '',
            period_to_copy: '',
            period_to_duplicate: '',
        }
    },
    mounted () {

        this.$i18n.locale = localStorage.getItem('lang')
        this.$root.$emit('update_title_master_services')

        let search_master_services = document.getElementById('search_master_services')
        let timeout_
        search_master_services.addEventListener('keydown', () => {
            clearTimeout(timeout_)
            timeout_ = setTimeout(() => {
                this.pageChosen = 1
                this.onUpdate()
                clearTimeout(timeout_)
            }, 1000)
        })

        this.onUpdate()

        this.$root.$on('update_master_services', (payload) => {
            this.onUpdate()
        })

        let currentDate = new Date()
        this.selectPeriod = currentDate.getFullYear()
        this.period_to_copy = currentDate.getFullYear()
    },
    computed: {
        tableOptionsService: function () {
            return {
                headings: {
                    id: 'ID',
                    date_from: 'Desde',
                    date_to: 'Hasta',
                    every: 'Cada',
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
    methods: {
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
            let data = {
                master_service_id: this.master_service_selected.id,
                year_copy: this.period_to_copy,
                year_apply: this.period_to_duplicate
            }
            API({
                method: 'POST',
                url: 'master_service_released/duplicate/period',
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
                    }else{
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
        cancelModalDuplicate: function () {
            this.$refs['my-modal-duplicate'].hide()
        },
        editRangeFrom: function () {
            let data = {
                date_from: moment(this.date_from_param, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                date_to: moment(this.date_to_param, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                type_discount: this.type_discount_param,
                every: this.every_param,
                value: this.value_param,
            }
            API({
                method: 'PUT',
                url: 'master_service_released/'+this.master_service_released_id,
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
        cancelModalAddParams: function () {
            this.$refs['modal-edit-params'].hide()
        },
        setDateFrom_param(e) {
            this.date_to_param= ''
            this.$refs.datePickerTo.dp.minDate(e.date)
        },
        showModalEditParams: function (row) {
            this.master_service_released_id = row.id
            this.date_from_param = moment(row.date_from, 'YYYY-MM-DD').format('DD/MM/YYYY')
            this.date_to_param = moment(row.date_to, 'YYYY-MM-DD').format('DD/MM/YYYY')
            this.type_param = row.type
            this.type_discount_param = row.type_discount
            this.value_param = row.value
            this.$refs['modal-edit-params'].show()
        },
        remove_release: function () {
            API({
                method: 'DELETE',
                url: 'master_service_released/' + this.delete_id,
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
        showModal (id) {
            this.delete_id = id
            this.title_modal = "Registro #" + id
            this.$refs['modal-delete-release'].show()
        },
        showModalDuplicate: function () {
            this.$refs['my-modal-duplicate'].show()
        },
        searchPeriod: function () {
            this.getReleased()
        },
        getReleased: function () {
            this.loading = true
            API.get('/master_service_released?master_service_id=' + this.master_service_selected.id + '&year=' + this.selectPeriod)
                .then((result) => {
                    this.loading = false
                    this.released = result.data.data
                }).catch(() => {
                this.loading = false
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Liberados',
                    text: "Error Interno"
                })
            })
        },
        save: function () {
            this.loading = true
            let data = {
                master_service_id: this.master_service_selected.id,
                date_from: moment(this.date_from, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                date_to: moment(this.date_to, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                every: this.every,
                type_discount: this.type_discount,
                value: this.value,
            }
            API({
                method: 'POST',
                url: 'master_service_released',
                data: data
            })
                .then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.getReleased()
                        this.viewForm = false
                    } else {
                        if (result.data.error === 'RANGE_EXIST') {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Liberados',
                                text: 'Ya tienes registrado el rango de fechas para las tarifas seleccionadas'
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
        setDateFrom (e) {
            this.date_to = ''
            this.$refs.datePickerTo.dp.minDate(e.date)
        },
        createForm: function () {
            this.viewForm = true
        },
        will_free(service){

            this.loading = true
            API.get('/master_services/' + service.id )
                .then((result) => {
                    this.master_service_selected = service
                    this.master_service_selected_type = result.data.type
                    if( this.master_service_selected_type!=='direct' ){
                        this.free_message = "La asignación de Liberados sólo está permitida para servicios directos"
                    } else {
                        this.free_message = ''
                        this.getReleased()
                    }
                    this.show_free = true
                    this.show_list = false

                    this.loading = false
                }).catch(() => {
                this.loading = false
            })

        },
        view_more_info(service){
            this.master_service_selected = service
            this.$refs['modal-info'].show();
        },
        hideModal() {
            this.$refs['modal-delete-release'].hide()
            this.$refs['modal-info'].hide()
        },
        filter_by_group (me) {
            this.g_parents.check = false
            this.g_directs.check = false
            this.g_components.check = false
            this.g_all.check = false

            me.check = true

            if( me.name === 'Todos' ){
                this.g_parents.check = true
                this.g_directs.check = true
                this.g_components.check = true
                this.g_all.check = true
            }

            this.onUpdate()
        },
        setPage(page){
            if( page < 1 || page > this.master_service_pages.length ){
                return;
            }
            this.pageChosen = page
            this.onUpdate()
        },
        onUpdate () {

            this.loading = true
            this.master_services = []

            let g_parents_check = (this.g_parents.check) ? 1 : 0
            let g_directs_check = (this.g_directs.check) ? 1 : 0
            let g_components_check = (this.g_components.check) ? 1 : 0

            API({
                method: 'GET',
                url: 'master_services?token=' + window.localStorage.getItem('access_token') +
                    '&lang=' + localStorage.getItem('lang') + '&queryCustom='+this.query_master_services +
                    '&page=' + this.pageChosen + '&limit=' + this.limit +
                    '&parents=' + g_parents_check + '&directs=' + g_directs_check + '&components=' + g_components_check
            })
                .then((result) => {

                    this.master_service_pages = []
                    for( let i=0; i<(result.data.count/this.limit); i++){
                        this.master_service_pages.push(i+1)
                    }

                    this.master_services = result.data.data
                    this.loading = false

                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Error Modulo Servicios Maestros',
                    text: this.$t('packages.error.messages.connection_error')
                })
            })
        },
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
        }
    }
}
</script>

<style lang="stylus">
.back-green{
    background: #bfffd0;
    padding: 5px;
}
.back-green-right{
    border-radius: 0 4px 4px 0px;
}
.back-green-left{
    border-radius: 4px 0px 0px 4px;
}
.table-actions {
    display: flex;
}
.trExtension, .trExtension > th, .trExtension > td {
    background-color: #e9eaff;
}
.trExtension:hover, .trExtension:hover > th, .trExtension:hover > td {
    background-color: #e2e3ff;
}
.VueTables__limit {
    display: none;
}
.no-margin{
    padding-left: 0;
    padding-bottom: 5px !important;
    padding-right: 0px;
}
.trPadding, .trPadding > th, .trPadding > td{
    padding: 10px !important;
}
.check-true{
    background: #63c2de;
}
.pagination_{
    list-style: none;
    border-radius: 0.25rem;
}
.VuePagination__pagination li{
    float: left;
    margin-bottom: 12px;
}
.content-scroll-y{
    justify-content: center !important;
    max-height: 88px;
    overflow-y: scroll;
}
</style>
