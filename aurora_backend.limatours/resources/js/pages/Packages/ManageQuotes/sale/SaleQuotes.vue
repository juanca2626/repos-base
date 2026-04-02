<template>
    <div class="col-lg-12">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="row">
            <div class="col-sm-4">
                <!-- <b-form-checkbox
                    v-model="enable_fixed_prices"
                    :id="'checkbox_enable_fixed_prices'"
                    :name="'checkbox_enable_fixed_prices'"
                    @change="changeStateFixed(enable_fixed_prices)"
                    switch>
                    Habilitar precios fijos
                </b-form-checkbox> -->
            </div>
            <div class="col-sm-8">
                <div class="row col-lg-12" style="margin-bottom: 10px">
                    <div class="col-lg-2">
                        <label for="market_id">Mercado:</label>
                        <select class="form-control" id="market_id" v-model="market" @change="setClients">
                            <option value="">Seleccione Mercado</option>
                            <option :value="market" v-for="market in markets" :key="market.id"> {{ market.name }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="country_id">País:</label>
                        <select class="form-control" id="country_id" v-model="country" @change="setClientsByCountry">
                            <option value="">Seleccione el país</option>
                            <option :value="country.id" v-for="country in countries"> {{ country.name }}</option>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label for="market_id">Cliente:</label>
                        <multiselect :clear-on-select="false"
                                     :close-on-select="false"
                                     :hide-selected="true"
                                     :multiple="true"
                                     :options="clients"
                                     placeholder="Seleccione uno o varios clientes"
                                     :preserve-search="true"
                                     tag-placeholder="Clientes"
                                     :taggable="true"
                                     @tag="addRestrictions"
                                     label="name"
                                     ref="multiselect"
                                     track-by="id"
                                     v-model="clientsSelected">
                        </multiselect>
                    </div>
                    <div class="col-lg-1">
                        <label for="btn-primary">Markup</label>
                        <input type="text" v-model="markup" class="form-control">
                    </div>
                    <div class="col-lg-1">
                        <label for="btn-primary">.</label>
                        <button id="btn-primary" class="btn btn-primary" @click="addPackageRateSale">Agregar</button>
                    </div>

                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <table class="table table-bordered">
                    <thead>
                        <th style="vertical-align: middle;">Planes Tarifarios</th>
                        <th>Precio<br />fijo</th>
                    </thead>
                    <tbody>
                    <tr v-for="(rate,index_rate) in package_rates_cost"
                        :key="index_rate"
                        :class="{ 'selected_rate': rate.selected }"
                        >
                        <td style="cursor:pointer;" @click="selectRate(index_rate)">
                            <div class="left mr-1 p-2">
                                [{{ rate.service_type.code }}] {{ rate.name }}
                            </div>
                            <div v-if="rateSelected && !rate.enable_fixed_prices">
                                <div class="right">
                                    <button type="button" class="btn btn-danger btn-sm" @click="openModalConfirmation">
                                        Actualizar todo
                                    </button>
                                </div>
                                <div class="right" v-if="rate_errors.length>0">
                                    <button type="button" class="btn btn-danger" @click="openModalErrors">Ver Errores</button>
                                </div>
                            </div>
                        </td>
                        <td class="check_class">
                            <b-form-checkbox
                                v-model="rate.enable_fixed_prices" 
                                @input="changeStateFixed(rate.id,rate.enable_fixed_prices,index_rate)"
                                switch > 
                            </b-form-checkbox>
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-8">
                <b-tabs card>
                    <b-tab active>
                        <template #title>
                            Tarifas / Precios
                            <span v-if="!rateEnableFixedPrices">diferenciados</span>
                            <span v-else>fijos</span>
                        </template>
                        <b-card-text>
                            <h5 class="text-center" v-if="package_rates_sales.length === 0">
                                Debe seleccionar una tarifa
                            </h5>
                            <b-tabs v-model="tabIndexFixed" v-if="package_rates_sales.length > 0 && rateEnableFixedPrices">
                                <b-tab :title="category.category" v-for="(category,index) in categories_fixed" :key="index">
                                    <b-card-text>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light text-center">
                                                    <tr>
                                                        <th>Simple</th>
                                                        <th>Doble</th>
                                                        <th>Triple</th>
                                                        <th>Niño con cama</th>
                                                        <th>Niño sin cama</th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-if="categories_fixed.length === 0">
                                                        <th colspan="6" class="center">
                                                            <p class="text-center">Debe seleccionar una tarifa</p>
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="">
                                                                <div class="">
                                                                    <div class="col-md-12 input-group-sm">
                                                                        <input :class="{'form-control':true }"
                                                                               type="text"
                                                                               data-vv-as="simple_fixed"
                                                                               :name="`simple_fixed`"
                                                                               v-validate="'required|decimal:2|min_value:1'"
                                                                               :data-vv-scope="'formFixed_'+index"
                                                                               v-model="category.rates.simple">
                                                                        <span class="invalid-feedback-select"
                                                                              v-show="errors.has(`simple_fixed`)">
                                                            <span>{{ errors.first(`simple_fixed`) }}</span>
                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="">
                                                                <div class="col-md-12 input-group-sm">
                                                                    <input :class="{'form-control':true }"
                                                                           type="text"
                                                                           data-vv-as="double_fixed"
                                                                           :name="`double_fixed`"
                                                                           v-validate="'required|decimal:2|min_value:1'"
                                                                           :data-vv-scope="'formFixed_'+index"
                                                                           v-model="category.rates.double">
                                                                    <span class="invalid-feedback-select"
                                                                          v-show="errors.has(`double_fixed`)">
                                                            <span>{{ errors.first(`double_fixed`) }}</span>
                                                        </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="">
                                                                <div class="col-md-12 input-group-sm">
                                                                    <input :class="{'form-control':true }"
                                                                           type="text"
                                                                           data-vv-as="triple_fixed"
                                                                           :name="`triple_fixed`"
                                                                           v-validate="'required|decimal:2|min_value:1'"
                                                                           :data-vv-scope="'formFixed_'+index"
                                                                           v-model="category.rates.triple">
                                                                    <span class="invalid-feedback-select"
                                                                          v-show="errors.has(`triple_fixed`)">
                                                            <span>{{ errors.first(`triple_fixed`) }}</span>
                                                        </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="">
                                                                <div class="col-md-12 input-group-sm">
                                                                    <input :class="{'form-control':true }"
                                                                           type="text"
                                                                           data-vv-as="child_with_bed_fixed"
                                                                           :name="`child_with_bed_fixed`"
                                                                           v-validate="'required|decimal:2|min_value:1'"
                                                                           :data-vv-scope="'formFixed_'+index"
                                                                           v-model="category.rates.child_with_bed">
                                                                    <span class="invalid-feedback-select"
                                                                          v-show="errors.has(`child_with_bed_fixed`)">
                                                            <span>{{ errors.first(`child_with_bed_fixed`) }}</span>
                                                        </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="">
                                                                <div class="col-md-12 input-group-sm">
                                                                    <input :class="{'form-control':true }"
                                                                           type="text"
                                                                           data-vv-as="child_without_bed_fixed"
                                                                           :name="`child_without_bed_fixed`"
                                                                           v-validate="'required|decimal:2|min_value:1'"
                                                                           :data-vv-scope="'formFixed_'+index"
                                                                           v-model="category.rates.child_without_bed">
                                                                    <span class="invalid-feedback-select"
                                                                          v-show="errors.has(`child_with_bed_fixed`)">
                                                            <span>{{ errors.first(`child_with_bed_fixed`) }}</span>
                                                        </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-success"
                                                                    @click="storeRateFixed(category,index)">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <button class="btn btn-success" @click="storeRateFixedAll()">
                                            Guardar y copiar los precios<br>
                                            <small>(Copiara los precios de la primera categoria)</small>
                                        </button>
                                    </b-card-text>
                                </b-tab>
                            </b-tabs>
                            <table class="table table-bordered" v-if="package_rates_sales.length > 0">
                                <thead>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th v-if="!rateEnableFixedPrices">Venta</th>
                                <th width="100px">Markup</th>
                                <th></th>
                                <th>
                                    <i class="fas fa-trash-alt"></i>
                                </th>
                                </thead>
                                <tbody>
                                <tr v-for="sale in package_rates_sales" :key="sale.id" :class="sale.tr_class">
                                    <th class="p-2 bg-white">
                                <span class="badge badge-success" v-if="sale.seller_type == 'App\\Client'">
                                   <i class="fas fa-user-tie"></i> CLIENTE
                                </span>
                                        <span class="badge badge-warning" v-else>
                                    <i class="fas fa-store"></i> MERCADO
                                </span>
                                    </th>
                                    <th class="p-2 bg-white">
                                <span v-if="sale.seller != null">
                                    <span v-if="sale.seller_type == 'App\\Client'">
                                        {{ sale.seller.code }} - {{ sale.seller.name }}<br>
                                        <span class="badge badge-info">
                                            {{ sale.seller.countries.translations[0].value }}
                                        </span>
                                    </span>
                                    <span v-else>
                                        {{ sale.seller.name }}
                                    </span>
                                </span>
                                    </th>
                                    <th class="p-2 bg-white" v-if="!rateEnableFixedPrices">
                                        <!--                                showModal-->
                                        <button type="button" class="btn btn-primary" @click="showModal(sale.id)"
                                                v-if="sale.markup >0">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary" @click="showModal(sale.id)"
                                                v-else>
                                            <i class="fas fa-money-bill"></i>
                                        </button>
                                    </th>
                                    <th class="bg-white">
                                        <input type="text" v-model="sale.markup" class="form-control"
                                               style="width: 100px;"
                                               @keyup.enter="updateMarkupRateSale(sale)">
                                    </th>
                                    <th class="p-2 bg-white">
                                        <b-form-checkbox
                                            :checked="checkboxChecked(sale.status)"
                                            :id="'checkbox_'+sale.id"
                                            :name="'checkbox_'+sale.id"
                                            @change="changeStateRateSale(sale.id)"
                                            switch>
                                        </b-form-checkbox>
                                    </th>
                                    <th class="p-2 bg-white">
                                        <button type="button" class="btn btn-sm btn-danger" @click="showModalDelete(sale)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </b-card-text>
                    </b-tab>
                </b-tabs>
                <div class="col-lg-12" v-if="rateSelected">
                </div>
            </div>
        </div>
        <!-- moda precios       -->
        <b-modal :title="modalTitle" centered ref="my-modal" size="lg">
            <div>
                <b-tabs v-model="tabIndex">
                    <b-tab :title="category.category.translations[0].value" v-for="category in categories"
                           :key="category.id"
                           @click="getRateSale(category.id)">
                        <div class="col-md-12 p-0 mb-3">
                            <h4>Niños</h4>
                            <div>
                                <b-list-group horizontal>
                                    <b-list-group-item>
                                        <strong>Con cama</strong>
                                    </b-list-group-item>
                                    <b-list-group-item>
                                        {{price_child_with_bed}}
                                    </b-list-group-item>
                                    <b-list-group-item>
                                        <strong>Sin cama</strong>
                                    </b-list-group-item>
                                    <b-list-group-item>
                                        {{price_child_without_bed}}
                                    </b-list-group-item>
                                </b-list-group>
                            </div>
                        </div>
                        <b-tabs>
                            <b-tab title="Privado" active v-if="service_type =='PC'">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered">
                                            <thead class="thead-light text-center">
                                            <tr>
                                                <th>#</th>
                                                <th>{{ $t('packagesmanagepackagerates.from') }}</th>
                                                <th>{{ $t('packagesmanagepackagerates.to') }}</th>
                                                <th>Simple</th>
                                                <th>Doble</th>
                                                <th>Triple</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-if="form.ratesPrivate.length === 0">
                                                <th colspan="6" class="center">
                                                    <p class="text-center">No se encontraron tarifas</p>
                                                </th>
                                            </tr>
                                            <tr v-for="(rate,index) in form.ratesPrivate" :key="`contact-${index+1}`">
                                                <th class="text-center">
                                                    <div class="col-md-12 font-weight-bold">
                                                        {{index + 1}}
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   v-model="rate[0].pax_from"
                                                                   disabled>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="pax_to"
                                                                   :name="`pax_to${index}`"
                                                                   v-validate="'required'"
                                                                   data-vv-scope="formPrivate"
                                                                   v-model="rate[0].pax_to"
                                                                   :disabled="true">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`pax_to${index}`)">
                                                            <span>{{ errors.first(`pax_to${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="simple"
                                                                   :name="`simple_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formPrivate"
                                                                   v-model="rate[0].simple">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`simple_${index}`)">
                                                            <span>{{ errors.first(`simple_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="double"
                                                                   :name="`double_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formPrivate"
                                                                   v-model="rate[0].double">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`double_${index}`)">
                                                            <span>{{ errors.first(`double_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="triple"
                                                                   :name="`triple_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formPrivate"
                                                                   v-model="rate[0].triple">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`triple_${index}`)">
                                                            <span>{{ errors.first(`triple_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-12">
                                        <!--                                        <button @click="validateBeforeSave(1)"-->
                                        <!--                                                v-if="form.ratesShared.length > 0"-->
                                        <!--                                                class="btn btn-sm btn-success pull-right"-->
                                        <!--                                                type="button">-->
                                        <!--                                            <font-awesome-icon :icon="['fas', 'dot-circle']" />-->
                                        <!--                                            {{$t('global.buttons.submit')}}-->
                                        <!--                                        </button>-->
                                        <button class="btn btn-success" @click="updateTablePackageDynamicSaleRates(2)">
                                            Actualizar
                                        </button>
                                    </div>
                                </div>
                            </b-tab>
                            <b-tab title="Compartido" v-if="service_type =='SIM'">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered">
                                            <thead class="thead-light text-center">
                                            <tr>
                                                <th></th>
                                                <th>{{ $t('packagesmanagepackagerates.from') }}</th>
                                                <th>{{ $t('packagesmanagepackagerates.to') }}</th>
                                                <th>Simple</th>
                                                <th>Doble</th>
                                                <th>Triple</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-if="form.ratesShared.length === 0">
                                                <th colspan="6" class="center">
                                                    <p class="text-center">No se encontraron tarifas</p>
                                                </th>
                                            </tr>
                                            <tr v-for="(rate,index) in form.ratesShared" :key="`contact-${index+1}`">
                                                <th class="text-center">
                                                    <div class="col-md-12 font-weight-bold">
                                                        {{index + 1}}
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   v-model="rate[0].pax_from"
                                                                   disabled>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="col-md-12 input-group-sm">
                                                        <input :class="{'form-control':true }"
                                                               type="text"
                                                               data-vv-as="pax_to"
                                                               :name="`pax_to${index}`"
                                                               v-validate="'required'"
                                                               data-vv-scope="formShared"
                                                               v-model="rate[0].pax_to"
                                                               :disabled="true">
                                                        <span class="invalid-feedback-select"
                                                              v-show="errors.has(`pax_to${index}`)">
                                                            <span>{{ errors.first(`pax_to${index}`) }}</span>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="">
                                                            <div class="col-md-12 input-group-sm">
                                                                <input :class="{'form-control':true }"
                                                                       type="text"
                                                                       data-vv-as="simple"
                                                                       :name="`simple_${index}`"
                                                                       v-validate="'required|decimal:2'"
                                                                       data-vv-scope="formShared"
                                                                       v-model="rate[0].simple">
                                                                <span class="invalid-feedback-select"
                                                                      v-show="errors.has(`simple_${index}`)">
                                                            <span>{{ errors.first(`simple_${index}`) }}</span>
                                                        </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="double"
                                                                   :name="`double_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formShared"
                                                                   v-model="rate[0].double">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`double_${index}`)">
                                                            <span>{{ errors.first(`double_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="">
                                                        <div class="col-md-12 input-group-sm">
                                                            <input :class="{'form-control':true }"
                                                                   type="text"
                                                                   data-vv-as="triple"
                                                                   :name="`triple_${index}`"
                                                                   v-validate="'required|decimal:2'"
                                                                   data-vv-scope="formShared"
                                                                   v-model="rate[0].triple">
                                                            <span class="invalid-feedback-select"
                                                                  v-show="errors.has(`triple_${index}`)">
                                                            <span>{{ errors.first(`triple_${index}`) }}</span>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-12">
                                        <!--                                        <button @click="validateBeforeSave(2)"-->
                                        <!--                                                v-if="form.ratesPrivate.length > 0"-->
                                        <!--                                                class="btn btn-sm btn-success pull-right"-->
                                        <!--                                                type="button">-->
                                        <!--                                            <font-awesome-icon :icon="['fas', 'dot-circle']" />-->
                                        <!--                                            {{$t('global.buttons.submit')}}-->
                                        <!--                                        </button>-->
                                        <button class="btn btn-success" @click="updateTablePackageDynamicSaleRates(1)">
                                            Actualizar
                                        </button>
                                    </div>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </b-tab>
                </b-tabs>
            </div>
            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">Cerrar</button>
            </div>
        </b-modal>
        <b-modal :title="modalConfirmTitle" centered ref="my-modal-confirm" size="sm">
            <p class="text-center">¿Desea actualizar todas las tarifas?</p>
            <div slot="modal-footer">
                <button @click="updateMarkups()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal title="Revisión de Errores" centered ref="my-modal-errors" size="lg">
            <p v-for="(sale,index_sale) in rate_errors" :key="index_sale">
                <span style="background: #890005; color: white;padding: 2px 7px;border-radius: 4px;"
                      v-if="sale.seller_type == 'App\\Client'">
                    <span class="badge badge-info">{{ sale.seller.countries.translations[0].value }}</span> {{ sale.seller.name }}
                </span>
                <span style="background: #890005; color: white;padding: 2px 7px;border-radius: 4px;" v-else>
                    {{ sale.seller.name }}
                </span>

                <br>
                <span style="margin-left: 40px;" v-for="category in sale.categories">
                     - CATEGORÍA: "{{ category.name }}"<br>
                    <span v-for="hotel in category.hotel_errors">
                     <span style="margin-left: 60px; background: #fad0ff; border-radius: 4px;">- HOTEL: "{{ hotel.name }}: {{ hotel.date_in }} - {{ hotel.date_out }}"</span><br>
                     <span style="margin-left: 60px;" v-if="hotel.rooms.length===0">
                         <b>* No tiene habitaciones asignadas</b>
                     </span>
                      <span style="margin-left: 60px;" v-else>
                         <b v-for="room in hotel.rooms">* No se encuentra sus tarifas para el tipo de hab {{ room.occupation }}</b><br>
                      </span>
                        <br>
                </span>
                </span>
            </p>
            <div slot="modal-footer">
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
        <b-modal :title="saleName" centered ref="my-modal-deleted" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="deleteSaleRateMarkup()" class="btn btn-success" :disabled="load_delete">
                    <i class="fa fa-spin fa-spinner" v-if="load_delete"></i>
                    <font-awesome-icon :icon="['fas', 'dot-circle']" v-else/>
                    {{$t('global.buttons.accept')}}
                </button>
                <button @click="hideModalDeleted()" class="btn btn-danger" :disabled="load_delete">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </b-modal>
        <block-page></block-page>
    </div>
</template>

<script>
    import { API } from './../../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import vSelect from 'vue-select'
    import 'vue-select/dist/vue-select.css'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import TableClient from './.././../../../components/TableClient'
    import BlockPage from '../../../../components/BlockPage'
    import Multiselect from 'vue-multiselect'
    import { Switch as cSwitch } from '@coreui/vue'

    export default {
        name: 'FormCostAdd',
        components: {
            'table-client': TableClient,
            BFormCheckbox,
            cSwitch,
            vSelect,
            BModal,
            Loading,
            Multiselect,
            BlockPage,
        },
        data: () => {
            return {
                tabIndex: 0,
                tabIndexFixed: 0,
                enable_fixed_prices: false,
                loading: false,
                loading_rate_fixed: false,
                selectKey: 0,
                package_id: null,
                sale_id: null,
                market: '',
                country: '',
                rateSelected: false,
                rateEnableFixedPrices: '',
                package_plan_rate_id: null,
                load_delete: false,
                package_rates_cost: [],
                package_rates_sales: [],
                categories: [],
                categories_fixed: [],
                markets: [],
                clients: [],
                country_clients: [],
                countries: [],
                client_id: '',
                clientsSelected: [],
                service_type: '',
                service_type_id: 1,
                modalTitle: 'Tarifas venta',
                form: {
                    ratesPrivate: [],
                    ratesShared: [],
                    ratesFixed: [],
                },
                modalConfirmTitle: 'Confirmación',
                table: {
                    columns: ['pax_from', 'pax_to', 'simple', 'double', 'triple', 'actions'],
                },
                category_id: '',
                markup: 0,
                rate_errors: [],
                times_i: 0,
                price_child_with_bed: 0,
                price_child_without_bed: 0,
                saleName: '',
                package_rate_sale_markup_id: '',
            }
        },
        computed: {},
        created () {
            this.package_id = this.$route.params.package_id
        },
        mounted: function () {
            this.getPackageRatesCost()
            this.getMarkets()
            this.getConfigPackage()
            this.$root.$emit('updateTitlePackage')
        },
        methods: {
            checkboxChecked: function (status) {
                if (status === 1) {
                    return true
                } else {
                    return false
                }
            },
            setClientsByCountry: function () {
                this.country_clients = []
                if (this.country !== '') {
                    this.clients = []
                    this.market.clients.forEach(c => {
                        let country = ''
                        if (c.countries != null) {
                            country = (c.countries.translations !== null && c.countries.translations.length > 0)
                                ? ' (' + c.countries.translations[0].value + ')'
                                : c.country_id
                        }
                        if (c.country_id == this.country) {
                            this.clients.push({
                                id: c.id,
                                name: c.code + ' - ' + c.name + country,
                            })
                        }
                    })
                } else {
                    this.setClients()
                }
            },
            setClients: function () {
                this.clients = []
                this.countries = []
                this.country = ''
                this.market.clients.forEach(c => {
                    let country = ''
                    if (c.countries != null) {
                        country = (c.countries.translations !== null && c.countries.translations.length > 0) ? ' (' +
                            c.countries.translations[0].value + ')' : c.country_id
                    }
                    this.clients.push({
                        id: c.id,
                        name: c.code + ' - ' + c.name + country,

                    })
                })
                this.setCountries()
            },
            setCountries: function () {
                this.countries = []
                let _countries = []
                this.market.clients.forEach(c => {
                    if (c.countries != null) {
                        let country = (c.countries.translations !== null && c.countries.translations.length > 0)
                            ? c.countries.translations[0].value
                            : c.country_id
                        _countries.push({
                            id: c.countries.id,
                            name: country,
                        })
                    }
                })

                this.countries = this.removeDuplicates(_countries)

            },
            removeDuplicates: function (array) {
                return array.filter((thing, index, self) =>
                    index === self.findIndex((t) => (
                        t.id === thing.id
                    )),
                )
            },
            changeStateRateSale: function (sale_id) {
                API.put('/package/rates/sales/markups/update', {
                    sale_id: sale_id,
                }).then((result) => {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: 'Paquetes',
                        text: result.data.message,
                    })
                    this.getPackageRatesSalesMarkups()
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes',
                        text: error,
                    })
                })
            },
            selectRate: function (index_rate) { 

                this.setAttributeSelected()
                this.service_type = this.package_rates_cost[index_rate].service_type.code
                this.service_type_id = this.package_rates_cost[index_rate].service_type_id
                this.$set(this.package_rates_cost[index_rate], 'selected', true)
                this.rateSelected = true
                this.rateEnableFixedPrices = this.package_rates_cost[index_rate].enable_fixed_prices
                this.package_plan_rate_id = this.package_rates_cost[index_rate].id
                this.getPackageRatesSalesMarkups()
                if(this.rateEnableFixedPrices){
                    this.getCategoriesFixedRates()                
                }
             
            },
            getPackageRatesCost: function () {
                this.loading = true
                API.get('/package/rates/cost/?package_id=' + this.package_id).then((result) => {
                    let results = result.data;
                    results.forEach((rate, index) => {
                        results[index].enable_fixed_prices = (results[index].enable_fixed_prices === 1) ? true : false
                    });
                    this.package_rates_cost = results
                    this.setAttributeSelected()
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    console.log(error)
                })
            },
            getConfigPackage: function () {
                this.loading = true
                API.get('/packages/' + this.package_id + '/configurations').then((result) => {                    
                    if (result.data.success) {
                        this.enable_fixed_prices = (result.data.data.enable_fixed_prices === 1)
                    } else {
                        this.enable_fixed_prices = false
                    }
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    console.log(error)
                })
            },
            setAttributeSelected: function () {
                for (let i = 0; i < this.package_rates_cost.length; i++) {
                    this.$set(this.package_rates_cost[i], 'selected', false)
                }
            },
            getPackageRatesSalesMarkups: function () {
                this.loading = true
                API.get('/package/rates/sales/markups?package_plan_rate_id=' + this.package_plan_rate_id).then((result) => {
                    result.data.forEach(d => {
                        d.tr_class = ''
                    })
                    this.package_rates_sales = result.data
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    console.log(error)
                })
            },
            getMarkets: function () {
                this.loading = true
                API.get('/markets').then((result) => {
                    this.markets = result.data.data
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    console.log(error)
                })
            },
            addPackageRateSale: function () {
                this.loading = true
                API.post('/package/rates/sales/markups/add', {
                    package_plan_rate_id: this.package_plan_rate_id,
                    market_id: this.market.id,
                    markup: this.markup,
                    client_id: (this.country !== '' && this.clientsSelected.length === 0) ? this.clients : this.clientsSelected,
                }).then((result) => {
                    this.clientsSelected = []
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: 'Paquetes',
                        text: result.data.message,
                    })
                    this.getPackageRatesSalesMarkups()
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes',
                        text: error.response.data.message,
                    })
                })
            },
            updateMarkupRateSale: function (sale) {
                API.put('/package/rates/sales/markup/update', {
                    sale_id: sale.id,
                    markup: sale.markup,
                }).then((result) => {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: 'Paquetes',
                        text: result.data.message,
                    })
                    this.getPackageRatesSalesMarkups()
                    this.loading = false
                }).catch((error) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes',
                        text: error.response.data.message,
                    })
                })
            },
            showModal: function (sale_id) {
                this.$refs['my-modal'].show()
                this.sale_id = sale_id
                API({
                    method: 'get',
                    url: '/package/plan_rates/' + this.package_plan_rate_id + '?lang=' + localStorage.getItem('lang'),
                }).then((result) => {
                    if (result.data.success === true) {
                        this.categories = result.data.data.plan_rate_categories
                        this.category_id = this.categories[0].id
                        this.getRatesByType(sale_id)
                    } else {
                        this.categories = []
                    }
                }).catch((e) => {
                    console.log(e)
                })
            },
            getCategoriesFixedRates: function () {
                this.loading = true
                this.categories_fixed = []
                API({
                    method: 'get',
                    url: '/package/plan_rates/' + this.package_plan_rate_id + '/fixed?lang=' + localStorage.getItem('lang'),
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === true) {
                        this.categories_fixed = result.data.data
                    } else {
                        this.categories_fixed = []
                    }
                }).catch((e) => {
                    this.loading = false
                    console.log(e)
                })
            },
            hideModal () {
                this.$refs['my-modal'].hide()
                this.$refs['my-modal-confirm'].hide()
                this.$refs['my-modal-errors'].hide()
            },
            getRatesByType: function (sale_id) {
                this.loading = true
                this.form.ratesPrivate = []
                this.form.ratesShared = []
                this.price_child_with_bed = 0
                this.price_child_without_bed = 0
                API.get('/package/package_dynamic_sale_rates/' + this.category_id + '?sale_id=' + sale_id + '&service_type_id=' + this.service_type_id).then((result) => {
                    this.loading = false
                    let rates = result.data.data
                    if (rates.private && rates.private.length > 0) {
                        this.form.ratesPrivate = this.formatRatesShowByType(rates.private)
                        rates.private.forEach((rate, index) => {
                            if (rate.pax_to === 2 && rate.pax_from === 2) {
                                this.price_child_with_bed = rate.child_with_bed
                                this.price_child_without_bed = rate.child_without_bed
                            }
                        })
                    }
                    if (rates.shared && rates.shared.length > 0) {
                        this.form.ratesShared = this.formatRatesShowByType(rates.shared)
                        rates.shared.forEach((rate, index) => {
                            if (rate.pax_to === 2 && rate.pax_from === 2) {
                                this.price_child_with_bed = rate.child_with_bed
                                this.price_child_without_bed = rate.child_without_bed
                            }
                        })
                    }
                }).catch(() => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.package'),
                        text: this.$t('global.error.messages.connection_error'),
                    })
                })
            },
            formatRatesShowByType: function (rates) {
                let arrayNew = []
                rates.forEach((rate, index) => {
                    arrayNew.push(
                        [
                            {
                                id: rate.id,
                                pax_from: rate.pax_from,
                                pax_to: rate.pax_to,
                                simple: rate.simple,
                                double: rate.double,
                                triple: rate.triple,
                            },
                        ],
                    )
                })
                return arrayNew
            },
            validateBeforeSave: function (type) {
                let form = (type === 1) ? 'formShared' : 'formPrivate'
                this.$validator.validateAll(form).then((result) => {
                    if (result) {
                        this.submitByType(type)
                        this.$validator.reset()
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.information_complete'),
                        })
                        this.loading = false
                    }
                })
            },

            submitByType: function (type) {
                let dataRate = (type === 1) ? this.form.ratesShared : this.form.ratesPrivate
                this.loading = true
                API({
                    method: 'post',
                    url: 'package/package_dynamic_sale_rates/',
                    data: {
                        'package_plan_rate_category_id': this.category_id,
                        'service_type_id': type,// Compartido || privado
                        'rates': dataRate,
                    },
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.save'),
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.success.save'),
                        })
                        this.getRatesByType(this.sale_id)
                    }
                })
            },
            updateTablePackageDynamicSaleRates: function (type) {
                // console.log(this.form.ratesShared)
                // console.log(this.form.ratesPrivate)
                let dataRate = (type === 1) ? this.form.ratesShared : this.form.ratesPrivate

                console.log(dataRate)
                this.loading = true
                API({
                    method: 'put',
                    url: 'package/package_dynamic_sale_rates/',
                    data: {
                        'rates': dataRate,
                    },
                }).then((result) => {
                    this.loading = false
                    if (result.data.success === false) {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.save'),
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.success.save'),
                        })
                        this.getRatesByType(this.sale_id)
                    }
                }).catch((error) => {
                    this.loading = false
                })
            },
            getRateSale: function (category) {
                this.category_id = category
                this.getRatesByType(this.sale_id)
            },
            openModalConfirmation: function () {
                this.$refs['my-modal-confirm'].show()
            },
            openModalErrors: function () {
                this.$refs['my-modal-errors'].show()
            },
            updateMarkups: function () {
                if (this.package_rates_sales.length === 0) {
                    return
                }

                this.package_rates_sales.forEach(p_r_s => {
                    p_r_s.tr_class = ''
                })

                this.$root.$emit('blockPage')
                this.times_i = 0
                this.do_updateMarkups(this.package_rates_sales.length)
            },
            do_updateMarkups: function (total) {

                API.put('/package/rates/sales/markup/update/general', {
                    package_plan_rates: [this.package_rates_sales[this.times_i]],
                }).then((result) => {
                    this.rate_errors = []
                    if (result.data.success) {
                        this.package_rates_sales[this.times_i].tr_class = 'tr-success'
                        this.times_i++
                        this.$notify({
                            group: 'main',
                            type: 'success',
                            title: 'Paquetes',
                            text: '(' + this.times_i + '/' + total + ') Calculo Satisfactorio',
                        })
                        if (this.times_i === total) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Paquetes',
                                text: result.data.message,
                            })
                            this.$root.$emit('unlockPage')
                            this.$refs['my-modal-confirm'].hide()
                        } else {
                            this.do_updateMarkups(total)
                        }
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'danger',
                            title: 'Paquetes',
                            text: result.data.message,
                        })
                        this.rate_errors = result.data.errors
                        this.$root.$emit('unlockPage')
                        this.$refs['my-modal-confirm'].hide()
                    }
                }).catch((error) => {
                    this.$refs['my-modal-confirm'].hide()
                    this.$root.$emit('unlockPage')
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Paquetes',
                        text: error.response.data.message,
                    })
                })
            },
            changeStateFixed: function (rate_id, enable_fixed_prices,index_rate) {

                this.loading = true                            
                let e_fixed_prices = enable_fixed_prices ? 1 : 0;                    
                API({
                    method: 'put',
                    url: 'packages/' + rate_id + '/fixed_prices',
                    data: { enable_fixed_prices: e_fixed_prices}
                })
                .then((result) => {
                    this.loading = false
                    if (result.data.success === true) {                            
                        this.selectRate(index_rate);
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: this.$t('global.error.messages.information_error')
                        })
                    }
                })

 

               
            },
            changeStateFixedBK: function (enable_fixed_prices) {
                this.loading = true
                API({
                    method: 'put',
                    url: 'packages/' + this.package_id + '/fixed_prices',
                    data: { enable_fixed_prices: enable_fixed_prices }
                })
                    .then((result) => {
                        this.loading = false
                        if (result.data.success === true) {
                            if(this.categories_fixed.length === 0 && this.rateSelected){
                                this.getCategoriesFixedRates()
                            }
                            // this.enable_fixed_prices = !!(enable_fixed_prices)
                            // console.log(this.enable_fixed_prices)
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: this.$t('global.modules.services'),
                                text: this.$t('global.error.messages.information_error')
                            })
                        }
                    })
            },            

            addRestrictions (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000)),
                }
                this.clientsSelected.push(tag)
            },
            storeRateFixed: function (category,index) {
                this.$validator.validateAll('formFixed_'+index).then((result) => {
                    if (result) {
                        this.loading = true
                        API({
                            method: 'post',
                            url: 'package/plan_rates/fixed',
                            data: {
                                'child_with_bed': category.rates.child_with_bed,
                                'child_without_bed': category.rates.child_without_bed,
                                'id': category.rates.id,
                                'package_plan_rate_category_id': category.rates.package_plan_rate_category_id,
                                'simple': category.rates.simple,
                                'double': category.rates.double,
                                'triple': category.rates.triple,
                            },
                        }).then((result) => {
                            this.loading = false
                            if (result.data.success === false) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.error.save'),
                                })
                            } else {
                                this.getCategoriesFixedRates()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.success.save'),
                                })
                            }
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.information_complete'),
                        })
                    }
                })

            },
            storeRateFixedAll: function () {
                this.$validator.validateAll('formFixed_0').then((result) => {
                    if (result) {
                        this.loading = true
                        API({
                            method: 'post',
                            url: 'package/plan_rates/fixed_all',
                            data: {
                                'package_plan_rate_id': this.package_plan_rate_id,
                                'child_with_bed': this.categories_fixed[0].rates.child_with_bed,
                                'child_without_bed': this.categories_fixed[0].rates.child_without_bed,
                                'simple': this.categories_fixed[0].rates.simple,
                                'double': this.categories_fixed[0].rates.double,
                                'triple': this.categories_fixed[0].rates.triple,
                            },
                        }).then((result) => {
                            this.loading = false
                            if (result.data.success === false) {
                                this.$notify({
                                    group: 'main',
                                    type: 'error',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.error.save'),
                                })
                            } else {
                                this.getCategoriesFixedRates()
                                this.$notify({
                                    group: 'main',
                                    type: 'success',
                                    title: this.$t('global.modules.package'),
                                    text: this.$t('global.success.save'),
                                })
                            }
                        })
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.package'),
                            text: this.$t('global.error.information_complete'),
                        })
                    }
                })
            },
            showModalDelete :function(row){
                this.saleName = 'Eliminar: ' + row.seller.name
                this.package_rate_sale_markup_id = row.id
                this.$refs['my-modal-deleted'].show()
            },
            hideModalDeleted () {
                this.$refs['my-modal-deleted'].hide()
            },
            deleteSaleRateMarkup:function(){
                this.load_delete = true
                API({
                    method: 'DELETE',
                    url: 'package/rates/sales/markups/' + this.package_rate_sale_markup_id
                })
                    .then((result) => {
                        this.load_delete = false
                        if (result.data.success) {
                            this.getPackageRatesSalesMarkups()
                            this.hideModalDeleted()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Error',
                                text: this.$t('packages.error.messages.package_delete')
                            })
                        }
                    }).catch(() => {
                    this.load_delete = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('packages.error.messages.name'),
                        text: this.$t('packages.error.messages.connection_error')
                    })
                })
            }

        },
    }
</script>

<style>
    .selected_rate {
        background-color: seagreen;
        color: white;
    }

    .tr-success, .tr-success input {
        background: #d4ffb3;
    }
  
    .check_class .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #ff0c09 !important;
        background-color: #ff0c09 !important;
    }

</style>
