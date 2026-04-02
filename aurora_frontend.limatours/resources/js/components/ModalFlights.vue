<template>
    <div>
        <template v-if="type == 'modal'">
            <div v-bind:class="[(open_modal) ? 'modal' : 'd-block pb-3', 'modal--cotizacion']" id="modal-flight"
                tabindex="-1" role="dialog" v-bind:style="(open_modal) ? 'overflow: scroll;' : ''">
            <div class="modal-dialog modal--cotizacion__document"
                v-bind:style="(open_modal) ? '' : 'pointer-events:all!important;'" role="document">
                <div v-bind:class="[(open_modal) ? 'modal-content' : 'p-0', 'modal--cotizacion__content']">
                    <div class="modal-header" v-if="open_modal">
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-bind:style="(open_modal) ? '' : 'padding:0!important;'">
                        <div class="modal--cotizacion__header">
                            <h3 class="modal-title"><b>{{ translations.label.flight_data }}</b></h3>
                        </div>
                        <div class="modal--cotizacion__body">
                            <div class="d-block">
                                <ValidationObserver ref="form" v-slot="{ invalid }">
                                    <form>
                                        <div class="container-fluid p-0">
                                            <template v-if="(loadingModal || loadingFlights) && !msg_error">
                                                <div class="alert alert-warning">
                                                    {{ translations.label.loading }}
                                                </div>
                                            </template>

                                            <template v-if="msg_error">
                                                <div class="alert alert-danger">{{ msg_error }}</div>
                                            </template>

                                            <template v-if="msg_success">
                                                <div class="alert alert-success">{{ msg_success }}</div>
                                            </template>

                                            <template v-if="!loadingModal && msg_error == '' && msg_success == ''">
                                                <div class="d-flex mb-4">
                                                    <input readonly="readonly" class="form-control" type="text"
                                                        v-bind:value="returnURL()" />
                                                    <button type="button" class="btn btn-danger"
                                                        v-clipboard:copy="returnURL()">
                                                        {{ translations.btn.copy }}
                                                    </button>
                                                </div>
                                                <hr />
                                                <div class="mt-4">
                                                    <div class="d-flex justify-content-between">
                                                        <p>
                                                            {{ translations.label.enter_the_requested_information }}:
                                                            <strong> FILE {{ nroFile }}</strong>
                                                        </p>
                                                        <button type="button" v-on:click="editFlights()"
                                                            v-if="!update"
                                                            v-bind:disabled="loadingModal || loadingFlights"
                                                            class="btn btn-info border btn-lg">
                                                            DESBLOQUEAR
                                                        </button>
                                                    </div>
                                                </div>

                                                <!----------- Modo completo ----------->
                                                <div v-if="flights.length > 0 && !loadingFlights">
                                                    <div class="mt-4">
                                                        <div id="accordion_flight" role="tablist">
                                                            <div class="card mb-3" v-for="(_flight, f) in flights"
                                                                v-bind:key="'flight-' + f">
                                                                <button type="button" class="b-left"
                                                                    v-on:click="toggleIndex(f)" data-toggle="collapse"
                                                                    v-bind:href="'#collapse_flight_' + f" aria-expanded="true"
                                                                    v-bind:aria-controls="'collapse_flight_' + f">
                                                                    <div class="card-header d-flex" role="tab" v-bind:id="'heading_' + f">
                                                                        {{ translations.label.flight_data }}:&nbsp;
                                                                        <b v-if="flightsO[f].date != undefined && flightsO[f].date != null && flightsO[f].date != ''">
                                                                            {{ flightsO[f].date }}
                                                                        </b>
                                                                    </div>
                                                                </button>
                                                                <div v-bind:id="'collapse_flight_' + f" v-on:click="toggleIndex(f)"
                                                                    v-bind:class="['collapse', (f == 0) ? 'show' : '']" role="tabpanel"
                                                                    v-bind:aria-labelledby="'heading_' + f" data-parent="#accordion_flight">
                                                                    <div class="card-body information-complete">
                                                                        <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input" type="radio" id="nacional"
                                                                                    disabled="disabled" v-bind:checked="flightsO[f].type == 0" value="0" />
                                                                                <label class="form-check-label" for="nacional">
                                                                                    {{ translations.label.national }}
                                                                                </label>
                                                                            </div>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input" type="radio" id="internacional"
                                                                                    disabled="disabled" v-bind:checked="flightsO[f].type == 1" value="1" />
                                                                                <label class="form-check-label" for="internacional">
                                                                                    {{ translations.label.international }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                                            <div class="form-row">
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.origin }}</label>
                                                                                    <template v-if="flightsO[f].flag_origin == 0">
                                                                                        <v-select class="form-control destino"
                                                                                                :options="origins_flights[f]" :filterable="false" @search="searchOrigins"
                                                                                                v-bind:placeholder="translations.label.origin"
                                                                                                v-model="flightsO[f].origin" style="padding-top: 5px; width: 250px">
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
                                                                                    <template v-else>
                                                                                        <input type="text" class="form-control"
                                                                                            v-model="flightsO[f].origin" readonly="" />
                                                                                    </template>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.destiny }}</label>
                                                                                    <template v-if="flightsO[f].flag_destiny == 0">
                                                                                        <v-select class="form-control destino"
                                                                                                :options="destinations_flights[f]" :filterable="false"
                                                                                                @search="searchDestinations"
                                                                                                v-bind:placeholder="translations.label.destiny"
                                                                                                v-model="flightsO[f].destiny" style="padding-top: 5px; width: 250px">
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
                                                                                    <template v-else>
                                                                                        <input type="text" class="form-control"
                                                                                            v-model="flightsO[f].destiny" readonly="" />
                                                                                    </template>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.date }}</label>
                                                                                    <date-picker
                                                                                        v-bind:placeholder="translations.label.date"
                                                                                        v-model="flightsO[f].date"
                                                                                        :config="{ format: 'DD/MM/YYYY' }"
                                                                                        autocomplete="off"
                                                                                        class="form-control"
                                                                                        v-bind:readonly="flightsO[f].flag_date == 1"
                                                                                    ></date-picker>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.departure }}</label>
                                                                                    <input type="time" class="form-control"
                                                                                        v-bind:placeholder="translations.label.departure"
                                                                                        v-bind:readonly="flightsO[f].flag_departure == 1"
                                                                                        v-model="flightsO[f].departure" />
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.arrival }}</label>
                                                                                    <input type="time" class="form-control"
                                                                                        v-bind:placeholder="translations.label.arrival"
                                                                                        v-bind:readonly="flightsO[f].flag_arrival == 1"
                                                                                        v-model="flightsO[f].arrival"  />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                                            <div class="form-row">
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.pnr }}</label>
                                                                                    <input type="text" class="form-control"
                                                                                        v-bind:placeholder="translations.label.pnr"
                                                                                        v-bind:readonly="flightsO[f].flag_pnr == 1"
                                                                                        v-model="flightsO[f].pnr" />
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.airline }}</label>
                                                                                    <template v-if="flightsO[f].flag_airline == 0">
                                                                                        <v-select class="form-control destino"
                                                                                                :options="airlines[f]" :filterable="false"
                                                                                                @input="flight_info(f)"
                                                                                                @search="searchAirlines"
                                                                                                v-bind:placeholder="translations.label.airline"
                                                                                                v-model="flightsO[f].airline" style="padding-top: 5px; width: 250px">
                                                                                            <template slot="option" slot-scope="option">
                                                                                                <div class="d-center">
                                                                                                    {{ option.codigo }} - {{ option.razon }}
                                                                                                </div>
                                                                                            </template>
                                                                                            <template slot="selected-option" slot-scope="option">
                                                                                                <div class="selected d-center">
                                                                                                    {{ option.codigo }} - {{ option.razon }}
                                                                                                </div>
                                                                                            </template>
                                                                                        </v-select>
                                                                                    </template>
                                                                                    <template v-else>
                                                                                        <input type="text" class="form-control"
                                                                                            v-model="flightsO[f].airline" readonly="" />
                                                                                    </template>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.number_flight }}</label>
                                                                                    <input type="text" class="form-control"
                                                                                        v-on:change="flight_info(f)"
                                                                                        v-bind:placeholder="translations.label.number_flight"
                                                                                        v-bind:readonly="flightsO[f].flag_number_flight == 1"
                                                                                        v-model="flightsO[f].number_flight" />
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label>{{ translations.label.paxs }}</label>
                                                                                    <input type="text" class="form-control"
                                                                                        v-bind:placeholder="translations.label.paxs"
                                                                                        v-bind:readonly="flightsO[f].flag_paxs == 1"
                                                                                        v-model="flightsO[f].paxs" />
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!----------- End Modo Completo----------->
                                                <div class="mt-4" v-if="flights.length > 0">
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-primary"
                                                            v-on:click="showModal('modalAlertaFlight')"
                                                            :disabled="invalid || loadingModal">
                                                            {{ translations.btn.save }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </form>
                                </ValidationObserver>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </template>

        <template v-if="type == 'list'">
            <div class="panel-group" id="accordion_flight" role="tablist" aria-multiselectable="true" v-if="flights.length > 0">
                <ValidationObserver ref="form" v-slot="{ invalid }">
                    <template v-for="(_flight, f) in flights">
                        <div class="panel panel-default" v-bind:key="'flight-' + f">
                            <div class="my-5">
                                <div v-bind:class="['panel-heading', (f == 1) ? 'active' : '']" role="tab" v-bind:id="'heading_flights_' + f">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse"
                                        v-bind:href="'#collapse_flight_' + f" aria-expanded="true"
                                        v-bind:aria-controls="'collapse_flight_' + f"
                                        data-parent="#accordion_flight">
                                            {{ translations.label.flight_data }}:&nbsp;
                                            <b v-if="flightsO[f].date != undefined && flightsO[f].date != null && flightsO[f].date != ''">
                                                {{ flightsO[f].date }}
                                            </b>
                                        </a>
                                    </h4>
                                </div>
                                <div v-bind:id="'collapse_flight_' + f" v-bind:class="['collapse', (f == 0) ? 'show' : '']"
                                    v-on:click="toggleIndex(f)" role="tabpanel" v-bind:aria-labelledby="'heading_flights_' + f"
                                    data-parent="#accordion_flight">
                                    <template>
                                        <div class="d-flex justify-content-start pb-4 pt-4">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="nacional"
                                                    disabled="disabled" v-bind:checked="flightsO[f].type == 0" value="0" />
                                                <label class="form-check-label" for="nacional">
                                                    {{ translations.label.national }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="internacional"
                                                    disabled="disabled" v-bind:checked="flightsO[f].type == 1" value="1" />
                                                <label class="form-check-label" for="internacional">
                                                    {{ translations.label.international }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.origin }}</label>
                                                <div style="width:340px;">
                                                    <template v-if="flightsO[f].flag_origin == 0">
                                                        <v-select class="form-control destino"
                                                                :options="origins_flights[f]"
                                                                value="codciu"
                                                                label="codciu" :filterable="false" @search="searchOrigins"
                                                                v-bind:placeholder="translations.label.origin"
                                                                v-model="flightsO[f].origin" style="padding-top: 5px;">
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
                                                    <template v-else>
                                                        <input type="text" class="form-control"
                                                            v-model="flightsO[f].origin" readonly="" />
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.destiny }}</label>
                                                <div style="width:340px;">
                                                    <template v-if="flightsO[f].flag_destiny == 0">
                                                        <v-select class="form-control destino"
                                                                :options="destinations_flights[f]"
                                                                value="codciu"
                                                                label="codciu" :filterable="false" @search="searchDestinations"
                                                                v-bind:placeholder="translations.label.destiny"
                                                                v-model="flightsO[f].destiny" style="padding-top: 5px;">
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
                                                    <template v-else>
                                                        <input type="text" class="form-control"
                                                            v-model="flightsO[f].destiny" readonly="" />
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.date }}</label>
                                                <div style="width:340px;">
                                                    <date-picker
                                                        v-bind:placeholder="translations.label.date"
                                                        v-model="flightsO[f].date"
                                                        :config="{ format: 'DD/MM/YYYY' }"
                                                        autocomplete="off"
                                                        class="form-control"
                                                        v-bind:readonly="flightsO[f].flag_date == 1"
                                                    ></date-picker>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.number_flight }}</label>
                                                <div style="width: 340px;">
                                                    <input type="text" class="form-control"
                                                        v-on:change="flight_info(f)"
                                                        v-bind:placeholder="translations.label.number_flight"
                                                        v-bind:readonly="flightsO[f].flag_number_flight == 1"
                                                        v-model="flightsO[f].number_flight" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.departure }}</label>
                                                <div style="width:340px;">
                                                    <input type="time" class="form-control"
                                                        v-bind:placeholder="translations.label.departure"
                                                        v-bind:readonly="flightsO[f].flag_departure == 1"
                                                        v-model="flightsO[f].departure" />
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.arrival }}</label>
                                                <div style="width:340px;">
                                                    <input type="time" class="form-control"
                                                        v-bind:placeholder="translations.label.arrival"
                                                        v-bind:readonly="flightsO[f].flag_arrival == 1"
                                                        v-model="flightsO[f].arrival"  />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">{{ translations.label.pnr }}</label>
                                                <div style="width:340px;">
                                                    <input type="text" class="form-control"
                                                        v-bind:placeholder="translations.label.pnr"
                                                        v-bind:readonly="flightsO[f].flag_pnr == 1"
                                                        v-model="flightsO[f].pnr" />
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <label for="" style="width: 200px;">Compañia aérea</label>
                                                <div style="width:340px;">
                                                    <template v-if="flightsO[f].flag_airline == 0">
                                                        <v-select class="form-control destino"
                                                                :options="airlines[f]"
                                                                value="codigo"
                                                                label="codigo" :filterable="false"
                                                                @input="flight_info(f)"
                                                                @search="searchAirlines"
                                                                v-bind:placeholder="translations.label.airline"
                                                                v-model="flightsO[f].airline" style="padding-top: 5px;">
                                                            <template slot="option" slot-scope="option">
                                                                <div class="d-center">
                                                                    {{ option.codigo }} - {{ option.razon }}
                                                                </div>
                                                            </template>
                                                            <template slot="selected-option" slot-scope="option">
                                                                <div class="selected d-center">
                                                                    {{ option.codigo }} - {{ option.razon }}
                                                                </div>
                                                            </template>
                                                        </v-select>
                                                    </template>
                                                    <template v-else>
                                                        <input type="text" class="form-control"
                                                            v-model="flightsO[f].airline" readonly="" />
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                                            <!-- div class="d-flex align-items-start">
                                                <label for="" style="width: 200px;">Número de PAX</label>
                                                <div class="input-actions" style="width: 340px;">
                                                    <vue-numeric-input :value="2"
                                                                    :precision="2">

                                                    </vue-numeric-input>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-start">
                                                <label for="" style="width: 200px;">Observaciones</label>
                                                <textarea name="message" type="text" placeholder="Escribe algo" rows="4" class="form-control"
                                                        style="width: 340px;"></textarea>
                                            </div -->
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </ValidationObserver>
            </div>
        </template>
        <div id="modalAlertaFlight" v-if="modal" tabindex="1" role="dialog" class="modal">
            <div role="document" class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center">
                            <div class="icon">
                                <i class="icon-alert-circle" v-if="!loadingModal"></i>
                                <i class="spinner-grow" v-if="loadingModal"></i>
                            </div>
                            <strong v-if="!loadingModal">{{ translations.label.confirm_action }}</strong>
                            <strong v-if="loadingModal">{{ translations.label.loading }}</strong>
                        </h4>
                        <p class="text-center" v-if="!loadingModal"><strong>{{ translations.label.text_confirm_action }}</strong></p>
                        <div class="group-btn" v-if="!loadingModal">
                            <button type="button" @click="saveFlight()" data-dismiss="modal"
                                    class="btn btn-secondary">{{ translations.btn.save }}
                            </button>
                            <button type="button" @click="closeModal('modalAlertaFlight')" data-dismiss="modal"
                                    class="btn btn-primary">{{ translations.btn.cancel }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalAddFlight" v-if="add_modal" class="modal">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="d-block">
                            <ValidationObserver ref="form" v-slot="{ invalid }">
                                <form>
                                    <div class="container-fluid p-0">
                                        <template v-if="loadingModal && !msg_error">
                                            <div class="alert alert-warning">
                                                {{ translations.label.loading }}
                                            </div>
                                        </template>

                                        <template v-if="msg_error">
                                            <div class="alert alert-danger">{{ msg_error }}</div>
                                        </template>

                                        <template v-if="msg_success">
                                            <div class="alert alert-success">{{ msg_success }}</div>
                                        </template>

                                        <template v-if="!loadingModal && msg_error == '' && msg_success == ''">
                                            <!----------- Modo completo ----------->
                                            <div class="mt-4">
                                                <div class="card mb-3" v-bind:key="'flight-add'">
                                                    <div class="card-body information-complete">
                                                        <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    id="nacional-add" v-model="newFlight.type"
                                                                    value="0" name="type" />
                                                                <label class="form-check-label"
                                                                    for="nacional-add">
                                                                    {{ translations.label.national }}
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio"
                                                                    id="internacional-add" v-model="newFlight.type"
                                                                    value="1" name="type" />
                                                                <label class="form-check-label"
                                                                    for="internacional-add">
                                                                    {{ translations.label.international }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                            <div class="form-row">
                                                                <div class="form-group">
                                                                    <label>{{ translations.label.origin }}</label>
                                                                    <v-select class="form-control destino"
                                                                            :options="new_origins_flights" :filterable="false" @search="searchNewOrigins"
                                                                            v-bind:placeholder="translations.label.origin"
                                                                            v-model="newFlight.origin" style="padding-top: 5px; width: 250px">
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
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ translations.label.destiny }}</label>
                                                                    <v-select class="form-control destino"
                                                                            :options="new_destinations_flights" :filterable="false"
                                                                            @search="searchNewDestinations"
                                                                            v-bind:placeholder="translations.label.destiny"
                                                                            v-model="newFlight.destiny" style="padding-top: 5px; width: 250px">
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
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ translations.label.date }}</label>
                                                                    <date-picker
                                                                        v-bind:placeholder="translations.label.date"
                                                                        v-model="newFlight.date"
                                                                        :config="{ format: 'DD/MM/YYYY' }"
                                                                        autocomplete="off"
                                                                        class="form-control"
                                                                    ></date-picker>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!----------- End Modo Completo----------->
                                            <div class="mt-4">
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary"
                                                        v-on:click="saveNewFlight()"
                                                        :disabled="invalid || loadingModal">
                                                        {{ translations.btn.save }}
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </form>
                            </ValidationObserver>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        data: () => {
            return {
                nroFile: 0,
                loadingModal: false,
                open_modal: true,
                lang: localStorage.getItem('lang'),
                user_id: localStorage.getItem('user_id'),
                translations: {
                    label: {},
                    btn: {}
                },
                type: 0,
                origin: '',
                destiny: '',
                date: '',
                departure: '',
                arrival: '',
                pnr: '',
                airline: '',
                number_flight: '',
                paxs: '',
                airlines: [],
                origins_flights: [],
                destinations_flights: [],
                new_origins_flights: [],
                new_destinations_flights: [],
                CODIGO: '',
                CODCIU: '',
                flights: [],
                flightsO: [],
                index: -1,
                update: false,
                modal: false,
                add_modal: false,
                msg_error: '',
                msg_success: '',
                newFlight: {
                    type: 0,
                    origin: '',
                    destiny: '',
                    date: ''
                },
                loadingFlights: false,
                api_flights: []
            }
        },
        created: function () {
            this.setTranslations()
        },
        mounted: function() {

        },
        watch: {
            newFlight: {
                handler(newValue, oldValue) {
                    let vm = this

                    if(newValue.type != undefined && newValue.type != oldValue.type)
                    {
                        vm.searchNewDestinations(newValue.destiny.label)
                        vm.searchNewOrigins(newValue.origin.label)
                    }
                },
                deep: true
            },
        },
        computed: {

        },
        methods: {
            returnURL: function () {
                return baseURL + 'register_flights/' + this.nroFile + '?lang=' + this.lang
            },
            showTypeRoom: function (_tiphab) {
                return ((_tiphab != null && _tiphab != '') ? this.types_room[_tiphab] : '')
            },
            doCopy: function () {
                let _message = this.returnURL()
                this.$copyText(_message).then(function (e) {
                    alert('Copiado!')
                    console.log(e)
                }, function (e) {
                    alert('No se pudo copiar..')
                    console.log(e)
                })
            },
            flight_info: function (index) {
                let airline = (typeof this.flightsO[index].airline == 'object') ? this.flightsO[index].airline.codigo : ''
                let nrovue = this.flightsO[index].number_flight

                axios.post(baseExternalURL + 'api/flight_info', {
                    access_key: 'ba6a46b6f37bc8014b2f0ae226ff73c7',
                    flight_number: nrovue,
                    airline_iata: airline,
                })
                    .then((response) => {
                        let _flights = response.data.data
                        let _date = moment(this.flightsO[index].date, 'DD/MM/YYYY').format('YYYY-MM-DD')

                        _flights.forEach((element, i, array) => {

                            if(element.flight_date == _date)
                            {
                                this.api_flights.push(element)
                            }

                            if(i == (_flights.length - 1))
                            {
                                if(this.api_flights.length == 1)
                                {
                                    this.setApiFlight(0)
                                }
                            }
                        })
                    })
                    .catch((e) => {
                        console.log(e);
                    });
            },
            setApiFlight: function (_index) {
                let _date = moment(this.flights[this.index].fecin, "DD/MM/YYYY").toDate().format('YYYY-MM-DD')
                let element = this.api_flights[_index]

                if(element.flight_date == _date)
                {
                    localStorage.setItem('set_airline', 1)
                    localStorage.setItem('set_from', 1)
                    localStorage.setItem('set_to', 1)

                    this.type_service = 1

                    this.searchOrigins(element.arrival.iata, '')
                    this.searchDestinations(element.departure.iata, '')
                    this.searchAirlines(element.airline.iata, '')

                    Vue.set(this.flights[this.index], 'fecout', moment(element.arrival.estimated).format('DD/MM/YYYY'))
                    Vue.set(this.flights[this.index], 'horin', moment(element.departure.estimated).format('HH:mm'))
                    Vue.set(this.flights[this.index], 'horout', moment(element.arrival.estimated).format('HH:mm'))

                    this.api_flights = []
                }
            },
            toggleIndex: function (_index) {
                let vm = this

                if(vm.index != _index)
                {
                    vm.index = _index

                    setTimeout(() => {
                        if(vm.origins_flights[vm.index] == undefined)
                        {
                            vm.$set(vm.origins_flights, vm.index, []) // origins
                        }

                        if(vm.destinations_flights[vm.index] == undefined)
                        {
                            vm.$set(vm.destinations_flights, vm.index, []) // destinations
                        }

                        if(vm.airlines[vm.index] == undefined)
                        {
                            vm.$set(vm.airlines, vm.index, []) // airlines
                        }

                        let origin = (vm.flightsO[vm.index].origin != undefined) ? vm.flightsO[vm.index].origin : ''
                        let destiny = (vm.flightsO[vm.index].destiny != undefined) ? vm.flightsO[vm.index].destiny : ''
                        let airline = (vm.flightsO[vm.index].airline != undefined) ? vm.flightsO[vm.index].airline : ''

                        if(typeof origin == 'object')
                        {
                            origin = origin.codciu
                        }

                        if(typeof destiny == 'object')
                        {
                            destiny = destiny.codciu
                        }

                        if(typeof airline == 'object')
                        {
                            airline = airline.codigo
                        }

                        vm.searchOrigins(origin, '')
                        vm.searchDestinations(destiny, '')
                        vm.searchAirlines(airline, '')

                    }, 10)
                }
            },
            showModal: function  (_modal) {
                this.modal = true

                setTimeout(function () {
                    $('#' + _modal).modal('show')
                }, 10)
            },
            closeModal: function (_modal, _close) {
                let vm = this
                $('#' + _modal).modal('hide')

                if (_close == true) {
                    setTimeout(function () {
                        vm.modal = false
                    }, 1000)
                }
            },
            setTranslations() {
                axios.get(baseURL+'translation/'+this.lang+'/slug/flights').then((data) => {
                    this.translations = data.data
                })
            },
            showAlert: function (_message) {
                this.$toast.error(_message, {
                    // override the global option
                    position: 'top-right'
                })
                return false
            },
            searchNewOrigins: function (search, loading) {
                this.loading = true

                axios.get(baseExternalURL + 'api/flights/origins/' + this.newFlight.type + '?query=' + search.toUpperCase())
                    .then(response => {
                        this.new_origins_flights = response.data.data

                        if(this.new_origins_flights.length == 1)
                        {
                            this.newFlight[0].origin = this.new_origins_flights[0]
                        }

                        this.loading = false
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.message.error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            searchNewDestinations: function (search, loading) {
                this.loading = true

                axios.get(baseExternalURL + 'api/flights/origins/' + this.newFlight.type + '?query=' + search.toUpperCase())
                    .then(response => {
                        this.new_destinations_flights = response.data.data

                        if(this.new_destinations_flights.length == 1)
                        {
                            this.newFlight.destiny = this.new_destinations_flights[0]
                        }

                        this.loading = false
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.message.error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            searchOrigins: function (search, loading) {
                let vm = this

                if((search == '' && vm.origins_flights[vm.index].length == 0) || search != '')
                {
                    vm.loading = true
                    search = (search != '') ? search.toUpperCase() : search

                    if(loading != '' && loading != undefined)
                    {
                        loading(true)
                    }

                    axios.get(baseExternalURL + 'api/flights/origins/' + vm.flightsO[vm.index].type + '?query=' + search)
                        .then(response => {
                            vm.$set(vm.origins_flights, vm.index, response.data.data)

                            if(vm.update)
                            {
                                vm.origins_flights[vm.index].forEach((item) => {
                                    if(item['codciu'] == vm.flights[vm.index].origin)
                                    {
                                        vm.$set(vm.flightsO[vm.index], 'origin', item)
                                    }
                                })
                            }

                            vm.loading = false
                        })
                        .catch(error => {
                            vm.$toast.error(vm.translations.message.error, {
                                position: 'top-right'
                            })
                            vm.loading = false
                        })
                        .finally
                        {
                            if(loading != '' && loading != undefined)
                            {
                                loading(false)
                            }
                        }
                }
            },
            searchDestinations: function (search, loading) {
                let vm = this

                if((search == '' && vm.destinations_flights[vm.index].length == 0) || search != '')
                {
                    vm.loading = true
                    search = (search != '') ? search.toUpperCase() : search

                    if(loading != '' && loading != undefined)
                    {
                        loading(true)
                    }

                    axios.get(baseExternalURL + 'api/flights/origins/' + vm.flightsO[vm.index].type + '?query=' + search)
                        .then(response => {
                            vm.$set(vm.destinations_flights, vm.index, response.data.data)

                            if(vm.update)
                            {
                                vm.destinations_flights[vm.index].forEach((item) => {
                                    if(item['codciu'] == vm.flights[vm.index].destiny)
                                    {
                                        vm.$set(vm.flightsO[vm.index], 'destiny', item)
                                    }
                                })
                            }

                            vm.loading = false
                        })
                        .catch(error => {
                            vm.$toast.error(vm.translations.message.error, {
                                position: 'top-right'
                            })
                            vm.loading = false
                        })
                        .finally
                        {
                            if(loading != '' && loading != undefined)
                            {
                                loading(false)
                            }
                        }
                }
            },
            searchAirlines: function (search, loading) {
                let vm = this

                if((search == '' && vm.airlines[vm.index].length == 0) || search != '')
                {
                    vm.loading = true
                    search = (search != '') ? search.toUpperCase() : search

                    if(loading != '' && loading != undefined)
                    {
                        loading(true)
                    }

                    axios.get(baseExternalURL + 'api/flights/airlines?query=' + search)
                        .then(response => {
                            vm.$set(vm.airlines, vm.index, response.data.data)

                            if(vm.update)
                            {
                                vm.airlines[vm.index].forEach((item) => {
                                    if(item['codciu'] == vm.flights[vm.index].airline)
                                    {
                                        vm.$set(vm.flightsO[vm.index], 'airline', item)
                                    }
                                })
                            }

                            vm.loading = false
                        })
                        .catch(error => {
                            this.$toast.error(this.translations.message.error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                        .finally
                        {
                            if(loading != '' && loading != undefined)
                            {
                                loading(false)
                            }
                        }
                }
            },
            validateFlights: async function (_validate = 1) {
                let vm = this

                vm.flights.forEach((flight, f) => {
                    let tipsvs = (flight.tipsvs != undefined) ? flight.tipsvs.trim() : flight.code_flight.trim()

                    if(flight.airline == undefined)
                    {
                        Vue.set(vm.flights[f], 'airline', '')
                    }

                    if(tipsvs == 'AEC' || tipsvs == 'AEI' || tipsvs == 'AEIFLT' || tipsvs == 'AECFLT')
                    {
                        Object.entries(flight).forEach(([index, value]) => {

                            if((index == 'tipsvs' || index == 'code_flight') && (value.indexOf('AEI') > -1 || value.indexOf('AEC') > -1))
                            {
                                index = 'type'
                                value = (value.indexOf('AEI') > -1) ? _validate : 0
                            }

                            if(index == 'ciuin' || index == 'origin')
                            {
                                index = 'origin'
                            }

                            if(index == 'ciuout' || index == 'destiny')
                            {
                                index = 'destiny'
                            }

                            if(index == 'date_in')
                            {
                                index = 'date'
                                value = moment(value, 'YYYY-MM-DD').format('DD/MM/YYYY')
                            }

                            if(index == 'horin' || index == 'start_time')
                            {
                                index = 'departure'
                            }

                            if(index == 'horout' || index == 'end_time')
                            {
                                index = 'arrival'
                            }

                            if(index == 'canpax')
                            {
                                index = 'paxs'
                            }

                            if(index == 'nro_flight')
                            {
                                index = 'number_flight'
                            }

                            if(index == 'number_flight' || index == 'pnr' || index == 'airline' || index == 'origin' || index == 'destiny' || index == 'departure' || index == 'arrival')
                            {
                                value = (value != undefined && value != null && value != '') ? value.toString().trim() : ''
                            }

                            vm.$set(vm.flightsO[f], index, value)
                            index = (index.indexOf('flag') > -1) ? index.replace('flag_', '') : index

                            if(vm.update)
                            {
                                vm.$set(vm.flightsO[f], 'flag_' + index, 0)
                            }
                            else
                            {
                                vm.$set(vm.flightsO[f], 'flag_' + index, (value != '' && value != null) ? _validate : 0)
                            }
                        })
                    }
                });

                setTimeout(function () {
                    vm.toggleIndex(0)
                }, 100)
            },
            editFlights: async function () {
                let vm = this
                vm.loadingFlights = true
                vm.update = true
                vm.index = -1

                await setTimeout(async () => {
                    await vm.validateFlights(0)

                    await setTimeout(() => {
                        vm.loadingFlights = false
                    }, 10)
                }, 10)
            },
            searchFlights: function () {
                this.loading = true
                this.newFlight = {}

                axios.post(baseURL + 'search_flights', {
                    nrofile: this.nroFile
                })
                    .then(response => {
                        let vm = this
                        vm.loadingModal = false

                        if(response.data.data.length > 0)
                        {
                            vm.flights = response.data.data
                            vm.flightsO = response.data.data

                            vm.validateFlights()
                        }
                    })
                    .catch(error => {
                        this.msg_error = this.translations.message.error
                        this.$toast.error(this.translations.message.error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            getFlights: function () {
                let vm = this
                return vm.flights
            },
            modalFlight: function (nrofile, flights, update_) {
                this.loadingModal = true
                this.msg_error = ''

                if(localStorage.getItem('modal_aurora_flights') == 'false')
                {
                    localStorage.removeItem('modal_aurora_flights')
                    this.open_modal = false
                }

                let vm = this
                if(update_ != undefined)
                {
                    vm.update = update_
                }

                if(flights == undefined)
                {
                    vm.type = 'modal'
                    vm.nroFile = nrofile
                    vm.searchFlights()

                    if(this.open_modal)
                    {
                        setTimeout(function () {
                            $('#modal-flight').modal('show')
                        }, 10)
                    }
                }
                else
                {
                    vm.type = 'list'
                    vm.flights = flights
                    vm.flightsO = flights

                    vm.validateFlights()
                }
            },
            saveFlight: async function () {
                let vm = this

                vm.loading = false
                $.each(vm.flightsO, function (i, item) {

                    if(vm.flightsO[i].origin == '' && vm.flightsO[i].flag_origin == 0)
                    {
                        vm.$toast.error(vm.translations.message.origin_information + ' (' + vm.translations.label.flight + ' #' + (i + 1) + '). ' + vm.translations.message.complete_to_continue, {
                            // override the global option
                            position: 'top-right'
                        })

                        return false
                    }

                    if(vm.flightsO[i].destiny == '' && vm.flightsO[i].flag_destiny == 0)
                    {
                        vm.$toast.error(vm.translations.message.destiny_information + ' (' + vm.translations.label.flight + ' #' + (i + 1) + '). ' + vm.translations.message.complete_to_continue, {
                            // override the global option
                            position: 'top-right'
                        })

                        return false
                    }

                    if(vm.flightsO[i].airline == '' && vm.flightsO[i].flag_airline == 0)
                    {
                        vm.$toast.error(vm.translations.message.airline_information + ' (' + vm.translations.label.flight + ' #' + (i + 1) + '). ' + vm.translations.message.complete_to_continue, {
                            // override the global option
                            position: 'top-right'
                        })

                        return false
                    }

                    vm.loading = true

                    let origin = (vm.flightsO[i].flag_origin == 1) ? vm.flightsO[i].origin : vm.flightsO[i].origin.codciu;
                    let destiny = (vm.flightsO[i].flag_destiny == 1) ? vm.flightsO[i].destiny : vm.flightsO[i].destiny.codciu;

                    axios.post(baseExternalURL + 'api/save_flight/' + vm.nroFile + '/' + vm.flightsO[i].nroite, {
                        type: (vm.flightsO[i].type == 0) ? 'AEC' : 'AEI',
                        codsvs: (vm.flightsO[i].type == 0) ? 'AEC' : ((origin == 'LIM') ? 'DEPLIM' : 'ARRLIM'),
                        origin: origin,
                        destiny: destiny,
                        date: vm.flightsO[i].date,
                        departure: vm.flightsO[i].departure,
                        arrival: vm.flightsO[i].arrival,
                        pnr: vm.flightsO[i].pnr,
                        airline: (vm.flightsO[i].flag_airline == 1) ? vm.flightsO[i].airline : vm.flightsO[i].airline.codigo,
                        number_flight: vm.flightsO[i].number_flight,
                        paxs: vm.flightsO[i].paxs
                    })
                        .then(response => {
                            vm.$toast.success(vm.translations.message.success, {
                                // override the global option
                                position: 'top-right'
                            })

                            if(i == (vm.flightsO.length - 1))
                            {
                                // Actualización de modales..
                                axios.post(baseExternalURL + 'api/modal/flights/update', {
                                    nrofile: vm.nroFile,
                                    flag_notify: localStorage.getItem('flag_notify_flights'),
                                    user_id: localStorage.getItem('user_id'),
                                    client_id: localStorage.getItem('client_id'),
                                    data: JSON.stringify(vm.flightsO),
                                })
                                    .then(response => {
                                        localStorage.removeItem('flag_notify_flights')
                                        console.log(response.data)
                                    })
                                    .catch(error => {
                                        console.log(error)
                                    })

                                vm.searchFlights()

                                /*
                                setTimeout(function () {
                                    vm.$toast.success('Lista de vuelos actualizada correctamente.', {
                                        // override the global option
                                        position: 'top-right'
                                    })
                                }, 1000)
                                */
                            }
                        })
                        .catch(error => {
                            vm.$toast.error(vm.translations.message.error, {
                                position: 'top-right'
                            })
                            vm.loading = false
                        })
                })
            },
            saveNewFlight: function() {
                let vm = this
                vm.loadingModal = true

                let origin = (vm.newFlight.origin != undefined) ? vm.newFlight.origin.codciu : ''
                let destiny = (vm.newFlight.destiny != undefined) ? vm.newFlight.destiny.codciu : ''

                axios.post(baseExternalURL + 'api/save_flight/' + vm.nroFile, {
                    type: (vm.newFlight.type == 0) ? 'AEC' : 'AEI',
                    codsvs: (vm.newFlight.type == 0) ? 'AEC' : ((origin == 'LIM') ? 'DEPLIM' : 'ARRLIM'),
                    origin: origin,
                    destiny: destiny,
                    date: vm.newFlight.date,
                })
                    .then(response => {
                        vm.$toast.success(vm.translations.message.success, {
                            position: 'top-right'
                        })
                        vm.add_modal = false
                        vm.searchFlights()
                    })
                    .catch(error => {
                        vm.$toast.error(vm.translations.message.error, {
                            position: 'top-right'
                        })
                        vm.loadingModal = false
                    })
            }
        }
    }
</script>
