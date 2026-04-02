<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">
            <div class="form-row">
                <div class="col-sm-5">
                    <label class="col-form-label">Clientes</label>
                    <v-select :options="clients"
                              :value="form.client_id"
                              @input="clientChange"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Filtro por nombre ó ID del cliente"
                              v-model="clientSelected" name="clients" id="clients" style="height: 35px;">
                        <template slot="option" slot-scope="option">
                            <div class="d-center">
                                <span>{{option.label}}</span>
                            </div>
                        </template>
                        <template slot="selected-option" slot-scope="option">
                            <div class="selected d-center">
                                <span>{{option.label}}</span>
                            </div>
                        </template>
                    </v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Evento</label>
                    <v-select :options="eventos"
                              @input="eventoChange"
                              :value="this.form.evento"
                              v-model="eventoSelected"
                              :placeholder="'Seleccione un evento'"
                              autocomplete="true"></v-select>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">Fechas</label>
                    <div class="input-group ">
                        <date-picker
                            :config="datePickerFromOptions"
                            @dp-change="setDateFrom"
                            id="date_from"
                            autocomplete="off"
                            name="date_from" ref="datePickerFrom"
                            v-model="form.date_from"
                        >
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <label class="col-form-label">.</label>
                    <div class="input-group ">
                        <date-picker
                            :config="datePickerToOptions"
                            @dp-change="setDateTo"
                            autocomplete="off"
                            id="date_to"
                            name="date_to" ref="datePickerTo"
                            v-model="form.date_to">
                        </date-picker>
                        <div class="input-group-append">
                            <button class="btn btn-secondary datepickerbutton" title="Toggle"
                                    type="button">
                                <i class="far fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-sm-1">
                    <img src="/images/loading.svg" v-if="loading" width="40px"
                         style="float: right; margin-top: 35px;"/>
                    <button @click="search" class="btn btn-success" type="submit" v-if="!loading"
                            style="float: right; margin-top: 35px;">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                        Buscar
                    </button>
                </div>
            </div>
            <table-server :columns="table.columns" :options="tableOptions" :url="urlAudit"
                          class="text-center"
                          ref="table">
                <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                    <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm" v-if="props.row.event != 'restored'">
                        <template slot="button-content">
                            <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                        </template>
                        <li @click="restore(props.row)" class="nav-link m-0 p-0">
                            <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'auditclients')">
                                <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                                Restaurar
                            </b-dropdown-item-button>
                        </li>
                    </b-dropdown>
                </div>
                <div class="table-service-user" slot="user" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.user.code}} - {{props.row.user.name}} <br>
                    <small class="badge badge-secondary"> {{props.row.user.roles[0].name}} </small>
                </div>
                <div class="table-service-event" slot="event" slot-scope="props" style="font-size: 16px">
                    <div v-bind:class="{'badge-danger':props.row.event == 'deleted',
                                        'badge-success':props.row.event == 'created',
                                        'badge-info':props.row.event == 'updated',
                                        'badge-primary':props.row.event == 'restored'}"
                         class="badge">
                        <span v-if="props.row.event == 'deleted'">ELIMINADO</span>
                        <span v-else-if="props.row.event == 'created'">CREADO</span>
                        <span v-else-if="props.row.event == 'updated'">ACTUALIZADO</span>
                        <span v-else>RESTAURADO</span>
                    </div>
                </div>
                <div class="table-service-package" slot="package" slot-scope="props" style="font-size: 0.9em">
                    <span
                        v-if="props.row.client.length > 0">{{props.row.client[0].id}} - {{props.row.client[0].name}}</span>
                </div>
                <div class="table-service-module" slot="module" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.module}}
                </div>
                <div class="table-service-sobrowser" slot="sobrowser" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.user_agent_name}}
                </div>
                <div slot="child_row" slot-scope="props" class="m-2">
                    <div v-if="props.row.auditable_type == 'App\\Client'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>

                            <div class="col-2"
                                 v-if="typeof props.row.new_values.code !== 'undefined' || typeof props.row.old_values.code !== 'undefined'">
                                <strong>Codigo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.code}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.code}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.name !== 'undefined' || typeof props.row.old_values.name !== 'undefined'">
                                <strong>Nombre:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.country_id !== 'undefined' || typeof props.row.old_values.country_id !== 'undefined'">
                                <strong>Pais:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.country_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.country_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.market_id !== 'undefined' || typeof props.row.old_values.market_id !== 'undefined'">
                                <strong>Mercado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.market_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.market_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.have_credit !== 'undefined' || typeof props.row.old_values.have_credit !== 'undefined'">
                                <strong>Tiene credito:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.have_credit === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.have_credit === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.have_credit === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.have_credit === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.credit_line !== 'undefined' || typeof props.row.old_values.credit_line !== 'undefined'">
                                <strong>Linea de credito:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.credit_line}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.credit_line}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.language_id !== 'undefined' || typeof props.row.old_values.language_id !== 'undefined'">
                                <strong>Idioma:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.language_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.language_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status === 1">Activo</span>
                                    <span v-else-if="props.row.old_values.status === 0">Inactivo</span>
                                    <span v-else>Eliminado</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status === 1">Activo</span>
                                    <span v-else-if="props.row.new_values.status === 0">Inactivo</span>
                                    <span v-else>Eliminado</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\Galery'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2 text-left text-justify"
                                 v-if="typeof props.row.new_values.position !== 'undefined' || typeof props.row.old_values.position !== 'undefined'">
                                <strong>Posición:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.position}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.position}}
                            </span>
                            </div>
                        </div>
                        <div class="row m-3">
                            <div class="col-12 text-left text-justify"
                                 v-if="typeof props.row.new_values.url !== 'undefined' || typeof props.row.old_values.url !== 'undefined'">
                                <strong>Url:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <img :src="props.row.old_values.url" alt="" width="120px" height="120px">
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                <img :src="props.row.new_values.url" alt="" width="120px" height="120px">
                            </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\Markup'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service !== 'undefined' || typeof props.row.old_values.service !== 'undefined'">
                                <strong>Servicio %:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.hotel !== 'undefined' || typeof props.row.old_values.hotel !== 'undefined'">
                                <strong>Hotel %:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.hotel}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.hotel}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ClientSeller'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.user_id !== 'undefined' || typeof props.row.old_values.user_id !== 'undefined'">
                                <strong>Usuario:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.user_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.user_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\MarkupService'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.markup !== 'undefined' || typeof props.row.old_values.markup !== 'undefined'">
                                <strong>Markup:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.markup}} %
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.markup}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_id !== 'undefined' || typeof props.row.old_values.service_id !== 'undefined'">
                                <strong>Servicio:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\MarkupHotel'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.markup !== 'undefined' || typeof props.row.old_values.markup !== 'undefined'">
                                <strong>Markup:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.markup}} %
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.markup}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.hotel_id !== 'undefined' || typeof props.row.old_values.hotel_id !== 'undefined'">
                                <strong>Hotel:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.hotel_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.hotel_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceMarkupRatePlan'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.markup !== 'undefined' || typeof props.row.old_values.markup !== 'undefined'">
                                <strong>Markup:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.markup}} %
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.markup}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id !== 'undefined' || typeof props.row.old_values.service_rate_id !== 'undefined'">
                                <strong>Servicio - Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_rate_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_rate_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\MarkupRatePlan'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.markup !== 'undefined' || typeof props.row.old_values.markup !== 'undefined'">
                                <strong>Markup:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.markup}} %
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.markup}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_plan_id !== 'undefined' || typeof props.row.old_values.rate_plan_id !== 'undefined'">
                                <strong>Hotel - Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_plan_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.rate_plan_id_name}}
                                </span>
                            </div>
                        </div>

                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceClient'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_id !== 'undefined' || typeof props.row.old_values.service_id !== 'undefined'">
                                <strong>Servicio:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\HotelClient'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.hotel_id !== 'undefined' || typeof props.row.old_values.hotel_id !== 'undefined'">
                                <strong>Hotel:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.hotel_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.hotel_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceClientRatePlan'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id !== 'undefined' || typeof props.row.old_values.service_rate_id !== 'undefined'">
                                <strong>Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_rate_id}} - {{props.row.old_values.service_rate_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_rate_id}} - {{props.row.new_values.service_rate_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ClientRatePlan'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.period !== 'undefined' || typeof props.row.old_values.period !== 'undefined'">
                                <strong>Periodo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.period}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.period}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_plan_id !== 'undefined' || typeof props.row.old_values.rate_plan_id !== 'undefined'">
                                <strong>Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_plan_id}} - {{props.row.old_values.rate_plan_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.rate_plan_id}} - {{props.row.new_values.rate_plan_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ClientServiceOffer'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.client[0].id">
                                <strong>Cliente ID:</strong><br>
                                {{props.row.client[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id !== 'undefined' || typeof props.row.old_values.service_rate_id !== 'undefined'">
                                <strong>Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_rate_id_name}} - {{props.row.old_values.rate_plan_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_rate_id_name}} - {{props.row.new_values.rate_plan_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_from !== 'undefined' || typeof props.row.old_values.date_from !== 'undefined'">
                                <strong>Desde:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date_from}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.date_from}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_to !== 'undefined' || typeof props.row.old_values.date_to !== 'undefined'">
                                <strong>Hasta:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date_to}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.date_to}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.value !== 'undefined' || typeof props.row.old_values.value !== 'undefined'">
                                <strong>Oferta %:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.value}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.value}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.is_offer !== 'undefined' || typeof props.row.old_values.is_offer !== 'undefined'">
                                <strong>Es una oferta?:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.is_offer === 1">Si</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.is_offer === 1">No</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status === 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-service-date_audit" slot="date_audit" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.created_at | formatDate}}
                </div>
            </table-server>
        </div>
    </div>
</template>

<script>
    import {API} from '../../../api';
    import TableServer from '../../../components/TableServer';
    import BModal from 'bootstrap-vue/es/components/modal/modal';
    import vSelect from 'vue-select';
    import 'vue-select/dist/vue-select.css';
    import Loading from 'vue-loading-overlay';
    import datePicker from 'vue-bootstrap-datetimepicker';
    import moment from 'moment';

    export default {
        components: {
            datePicker,
            'table-server': TableServer,
            vSelect,
            Loading,
            BModal,
        },
        data: () => {
            return {
                loading: false,
                clients: [],
                eventos: [
                    {code: 'created', label: 'Creado'},
                    {code: 'updated', label: 'Actualizado'},
                    {code: 'deleted', label: 'Eliminado'},
                    {code: 'restored', label: 'Restaurado'},
                ],
                clientSelected: [],
                eventoSelected: [],
                table: {
                    columns: [
                        'id',
                        'user',
                        'event',
                        'package',
                        'module',
                        'ip_address',
                        'sobrowser',
                        'date_audit',
                        'actions'],
                },
                urlAudit: '',
                form: {
                    client_id: null,
                    date_from: '',
                    date_to: '',
                    evento: '',
                },
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
                filter: '',
            };
        },
        computed: {
            menuOptions: function() {
            },
            tableOptions: function() {
                return {
                    headings: {
                        id: 'ID',
                        user: 'Usuario',
                        event: 'Evento',
                        package: 'Paquete',
                        module: 'Modulo',
                        ip_address: 'IP',
                        sobrowser: 'SO/Browser',
                        date_audit: 'Fecha auditoría',
                        actions: this.$i18n.t('global.table.actions'),
                    },
                    sortable: [],
                    filterable: [],
                    perPageValues: [],
                    responseAdapter({data}) {
                        return {
                            data: data.data,
                            count: data.count,
                        };
                    },
                    params: {
                        'client_id': (this.form.client_id === null) ? '' : this.form.client_id,
                        'date_from': (this.form.date_from === '') ? '' : moment(this.form.date_from, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'date_to': (this.form.date_to === '') ? '' : moment(this.form.date_to, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'event': this.form.evento,
                    },
                    requestFunction: function(data) {
                        let url = '/audit/client?token=' + window.localStorage.getItem('access_token') + '&lang='
                            + localStorage.getItem('lang');
                        return API.get(url, {
                            params: data,
                        }).catch(function(e) {
                            this.dispatch('error', e);
                        }.bind(this));

                    },
                };
            },
        },
        created() {
        },
        mounted() {
            this.loadClients();
        },
        methods: {
            setDateFrom(e) {
                if (e.date == false) {
                    this.form.date_from = '';
                } else {
                    this.$refs.datePickerTo.dp.minDate(e.date);
                }
            },
            setDateTo(e) {
                if (e.date == false) {
                    this.form.date_to = '';
                }
            },
            loadClients() {
                API.get('/client/search?lang=' + localStorage.getItem('lang')).then((result) => {
                    let _data = result.data.data;
                    _data.forEach((clients) => {
                        this.clients.push({label: '(' + clients.code + ') ' + clients.name, id: clients.id});
                    });
                }).catch((e) => {
                    console.log(e);
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Auditoría',
                        text: this.$t('global.error.messages.connection_error'),
                    });
                });
            },
            onSearch(search, loading) {
                loading(true);
                this.clients = [];
                console.log(search);
                API.get('/client/search?lang=' + localStorage.getItem('lang') + '&queryCustom=' + search).
                    then((result) => {
                        loading(false);
                        let packages_data = result.data.data;
                        packages_data.forEach((clients) => {
                            this.clients.push({label: '(' + clients.code + ') ' + clients.name, id: clients.id});
                        });
                    }).
                    catch(() => {
                        loading(false);
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: 'Auditoría',
                            text: this.$t('global.error.messages.information_error'),
                        });
                    });
            },
            clientChange: function(value) {
                let select = value;
                if (select != null) {
                    this.form.client_id = select.id;
                } else {
                    this.form.client_id = '';
                }
            },
            restore: function(row) {
                this.loading = true;
                API({
                    method: 'post',
                    url: 'audit/restore',
                    data: {
                        'id': row.id,
                    },
                }).then((result) => {
                    this.$notify({
                        group: 'main',
                        type: 'success',
                        title: 'Auditoría',
                        text: result.data.message,
                    });
                    this.$refs.table.$refs.tableserver.getData();
                    this.loading = false;
                    console.log(result);
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Auditoría',
                        text: this.$t('global.error.messages.connection_error'),
                    });
                    this.loading = false;
                });
            },
            search: function() {
                this.loading = true;
                this.$refs.table.$refs.tableserver.getData();
                this.loading = false;
            },
            eventoChange: function(value) {
                let evento_select = value;
                if (evento_select != null) {
                    this.form.evento = evento_select.code;
                } else {
                    this.form.evento = '';
                }
            },
        },

    };
</script>
<style scoped>
    .text-package {
        white-space: normal !important;
        text-align: justify;
        line-height: 17px !important;
        font-size: smaller;
    }
</style>

