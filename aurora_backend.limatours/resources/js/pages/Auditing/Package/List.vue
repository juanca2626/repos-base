<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">
            <div class="form-row">
                <div class="col-sm-5">
                    <label class="col-form-label">Paquetes</label>
                    <v-select :options="packages"
                              :value="form.package_id"
                              @input="packageChange"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Filtro por nombre ó ID del paquete"
                              v-model="packageSelected" name="packages" id="packages" style="height: 35px;">
                        <template slot="option" slot-scope="option">
                            <div class="d-center">
                                <span>{{ option.id }} - {{option.label}}</span>
                            </div>
                        </template>
                        <template slot="selected-option" slot-scope="option">
                            <div class="selected d-center">
                                <span>{{ option.id }} - {{option.label}}</span>
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
                            <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'auditpackages')">
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
                        v-if="props.row.package.length > 0">{{props.row.package[0].id}} - {{props.row.package[0].name}}</span>
                </div>
                <div class="table-service-module" slot="module" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.module}}
                </div>
                <div class="table-service-sobrowser" slot="sobrowser" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.user_agent_name}}
                </div>
                <div slot="child_row" slot-scope="props" class="m-2">
                    <div v-if="props.row.auditable_type == 'App\\Package'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.nights !== 'undefined' || typeof props.row.old_values.nights !== 'undefined'">
                                <strong>Noches:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.nights}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">{{props.row.new_values.nights}}</span>
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
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tag_id !== 'undefined' || typeof props.row.old_values.tag_id !== 'undefined'">
                                <strong>Categoria</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tag_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.tag_id_name}}</span>
                            </div>
                            <div class="col-4"
                                 v-if="typeof props.row.new_values.map_link !== 'undefined' || typeof props.row.old_values.map_link !== 'undefined'">
                                <strong>Mapa link:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.map_link}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created'  && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.map_link}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.extension !== 'undefined' || typeof props.row.old_values.extension !== 'undefined'">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.extension == 0">Paquete</div>
                                    <div v-else-if="props.row.old_values.extension == 1">Extension</div>
                                    <div v-else>Exclusivo</div>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <div v-if="props.row.new_values.extension == 0">Paquete</div>
                                    <div v-else-if="props.row.new_values.extension == 1">Extension</div>
                                    <div v-else>Exclusivo</div>
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
                                 v-if="typeof props.row.new_values.recommended !== 'undefined' || typeof props.row.old_values.recommended !== 'undefined'">
                                <strong>Recomendado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.recommended}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.recommended}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.allow_modify !== 'undefined' || typeof props.row.old_values.allow_modify !== 'undefined'">
                                <strong>Permitir modificar:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.allow_modify">Activado</span>
                                    <span v-else>Desactivado</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.allow_modify">Activado</span>
                                    <span v-else>Desactivado</span>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_dynamic !== 'undefined' || typeof props.row.old_values.rate_dynamic !== 'undefined'">
                                <strong>Presio desde:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_dynamic}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.rate_dynamic}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.physical_intensity_id_name !== 'undefined' || typeof props.row.old_values.physical_intensity_id_name !== 'undefined'">
                                <strong>Intensidad Fisica:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.physical_intensity_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.physical_intensity_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.allow_child !== 'undefined' || typeof props.row.old_values.allow_child !== 'undefined'">
                                <strong>Permitir niños:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.allow_child == 1">Activado</div>
                                    <div v-else-if="props.row.old_values.allow_child == 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                    <div v-if="props.row.new_values.allow_child == 1">Activado</div>
                                    <div v-else-if="props.row.new_values.allow_child == 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.allow_guide !== 'undefined' || typeof props.row.old_values.allow_guide !== 'undefined'">
                                <strong>Permitir Guias:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.allow_guide === true">Activado</div>
                                    <div v-else-if="props.row.old_values.allow_guide === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.allow_guide === true">Activado</div>
                                    <div v-else-if="props.row.new_values.allow_guide === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.allow_infant !== 'undefined' || typeof props.row.old_values.allow_infant !== 'undefined'">
                                <strong>Permitir Infantes:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.allow_infant === true">Activado</div>
                                    <div v-else-if="props.row.old_values.allow_infant === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.allow_infant === true">Activado</div>
                                    <div v-else-if="props.row.new_values.allow_infant === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.infant_min_age !== 'undefined' || typeof props.row.old_values.infant_min_age !== 'undefined'">
                                <strong>Edad min. infante:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.infant_min_age === null">0 meses</span>
                                    <span v-else>{{props.row.old_values.infant_min_age}} meses</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.infant_min_age}} meses
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.infant_max_age !== 'undefined' || typeof props.row.old_values.infant_max_age !== 'undefined'">
                                <strong>Edad max. infante:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.infant_max_age === null">0 meses</span>
                                    <span v-else>{{props.row.old_values.infant_max_age}} meses</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.infant_max_age}} meses
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.limit_confirmation_hours !== 'undefined' || typeof props.row.old_values.limit_confirmation_hours !== 'undefined'">
                                <strong>Limite de horas de confirmación:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.limit_confirmation_hours === null">0</span>
                                    <span v-else>{{props.row.old_values.limit_confirmation_hours}}</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.limit_confirmation_hours}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageTranslation'">
                        <div class="row m-3">
                            <div class="col-12 text-left text-justify" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.language_id_name !== 'undefined' || typeof props.row.old_values.language_id_name !== 'undefined'">
                            <div class="col-12 text-left text-justify">
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
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.name !== 'undefined' || typeof props.row.old_values.name !== 'undefined'">
                            <div class="col-12 text-left text-justify">
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
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.tradename !== 'undefined' || typeof props.row.old_values.tradename !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Nombre comercial:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tradename}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.tradename}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.label !== 'undefined' || typeof props.row.old_values.label !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Etiqueta:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.label}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.label}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.itinerary_label !== 'undefined' || typeof props.row.old_values.itinerary_label !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Itinerario etiqueta:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.itinerary_label}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.itinerary_label}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.description !== 'undefined' || typeof props.row.old_values.description !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Descripción:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.description}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.description}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.itinerary_description !== 'undefined' || typeof props.row.old_values.itinerary_description !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Itinerario:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.itinerary_description}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.itinerary_description}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.itinerary_link !== 'undefined' || typeof props.row.old_values.itinerary_link !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Itinerario link:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.itinerary_link}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package" style="width: 900px;"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.itinerary_link}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.policies !== 'undefined' || typeof props.row.old_values.policies !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Politicas:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.policies}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.policies}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.inclusion !== 'undefined' || typeof props.row.old_values.inclusion !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Inclusiones:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.inclusion}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.inclusion}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.restriction !== 'undefined' || typeof props.row.old_values.restriction !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Restricciones:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.restriction}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package"
                                      v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.restriction}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageChild'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.min_age !== 'undefined' || typeof props.row.old_values.min_age !== 'undefined'">
                                <strong>Edad minima:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.min_age}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.min_age}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.max_age !== 'undefined' || typeof props.row.old_values.max_age !== 'undefined'">
                                <strong>Edad maxima:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.max_age}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.max_age}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.status == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.status == 0">Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageExtension'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.extension_id !== 'undefined' || typeof props.row.old_values.extension_id !== 'undefined'">
                                <strong>Extension:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.extension_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.extension_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageTax'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tax_id !== 'undefined' || typeof props.row.old_values.tax_id !== 'undefined'">
                                <strong>Nombre:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tax_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.tax_id_name}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tax_id !== 'undefined' || typeof props.row.old_values.tax_id !== 'undefined'">
                                <strong>Valor:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tax_id_value}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.tax_id_value}}
                            </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\Galery'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
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
                    <div v-if="props.row.auditable_type == 'App\\FixedOutput'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date !== 'undefined' || typeof props.row.old_values.date !== 'undefined'">
                                <strong>Fecha:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.date}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.room !== 'undefined' || typeof props.row.old_values.room !== 'undefined'">
                                <strong>Cupos:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.room}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.room}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.state !== 'undefined' || typeof props.row.old_values.state !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.state == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.state == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.state == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.state == 0">Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageSchedule'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_from !== 'undefined' || typeof props.row.old_values.date_from !== 'undefined'">
                                <strong>Fecha desde:</strong><br>
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
                                <strong>Fecha desde:</strong><br>
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
                                 v-if="typeof props.row.new_values.room !== 'undefined' || typeof props.row.old_values.room !== 'undefined'">
                                <strong>Cupos:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.room}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.room}}
                            </span>
                            </div>
                        </div>
                        <div class="row m-3">
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.monday !== 'undefined' || typeof props.row.old_values.monday !== 'undefined'">
                                <strong>LU</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.monday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.monday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                     <span v-if="props.row.new_values.monday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.monday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.tuesday !== 'undefined' || typeof props.row.old_values.tuesday !== 'undefined'">
                                <strong>MA</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.tuesday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.tuesday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.tuesday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.tuesday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.wednesday !== 'undefined' || typeof props.row.old_values.wednesday !== 'undefined'">
                                <strong>MI</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.wednesday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.wednesday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.wednesday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.wednesday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.tuesday !== 'undefined' || typeof props.row.old_values.tuesday !== 'undefined'">
                                <strong>JU</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.tuesday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.tuesday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.tuesday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.tuesday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.friday !== 'undefined' || typeof props.row.old_values.friday !== 'undefined'">
                                <strong>VI</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.friday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.friday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.friday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.friday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.saturday !== 'undefined' || typeof props.row.old_values.saturday !== 'undefined'">
                                <strong>SA</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.saturday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.saturday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.saturday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.saturday == 0">Inactivo</span>
                                </span>
                            </div>
                            <div class="col-1"
                                 v-if="typeof props.row.new_values.sunday !== 'undefined' || typeof props.row.old_values.sunday !== 'undefined'">
                                <strong>DO</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.sunday == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.sunday == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.sunday == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.sunday == 0">Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackagePlanRate'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
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
                                 v-if="typeof props.row.new_values.date_from !== 'undefined' || typeof props.row.old_values.date_from !== 'undefined'">
                                <strong>Fecha desde:</strong><br>
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
                                <strong>Fecha desde:</strong><br>
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
                                 v-if="typeof props.row.new_values.service_type_id !== 'undefined' || typeof props.row.old_values.service_type_id !== 'undefined'">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_type_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.service_type_id_name}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.status == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.status == 0">Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackagePlanRateCategory'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.package_plan_rate_id !== 'undefined' || typeof props.row.old_values.package_plan_rate_id !== 'undefined'">
                                <strong>Nombre de tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.package_plan_rate_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.package_plan_rate_id_name}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.type_class_id !== 'undefined' || typeof props.row.old_values.type_class_id !== 'undefined'">
                                <strong>Categoria:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.type_class_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.type_class_id_name}}
                            </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageService'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-4"
                                 v-if="(typeof props.row.new_values.object_id !== 'undefined' || typeof props.row.old_values.object_id !== 'undefined') || typeof props.row.new_values.object_id_name !== 'undefined' || typeof props.row.old_values.object_id_name !== 'undefined'">
                                <strong
                                    v-if="props.row.new_values.type == 'service' || props.row.old_values.type == 'service'">Servico:</strong>
                                <strong
                                    v-if="props.row.new_values.type == 'hotel' || props.row.old_values.type == 'hotel'">Hotel:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.object_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.object_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_in !== 'undefined' || typeof props.row.old_values.date_in !== 'undefined'">
                                <strong>Fecha desde:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date_in}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.date_in}}
                                </span>
                            </div>
                            <div class="col-3"
                                 v-if="typeof props.row.new_values.calculation_included !== 'undefined' || typeof props.row.old_values.calculation_included !== 'undefined'">
                                <strong>Incluir markup en el calculo tarifario:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.calculation_included == 1">Activo</span>
                                    <span v-else-if="props.row.old_values.calculation_included == 0">Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                     <span v-if="props.row.new_values.calculation_included == 1">Activo</span>
                                    <span v-else-if="props.row.new_values.calculation_included == 0">Inactivo</span>
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="props.row.new_values.type == 'hotel' || props.row.old_values.type == 'hotel'">
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_out !== 'undefined' || typeof props.row.old_values.date_out !== 'undefined'">
                                <strong>Fecha hasta:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date_out}}
                                    </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.date_out}}
                                    </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.adult !== 'undefined' || typeof props.row.old_values.adult !== 'undefined'">
                                <strong>Adulto:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.adult}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.adult}}
                            </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.child !== 'undefined' || typeof props.row.old_values.child !== 'undefined'">
                                <strong>Niños:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.child}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.child}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.infant !== 'undefined' || typeof props.row.old_values.infant !== 'undefined'">
                                <strong>Infante:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.infant}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.infant}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.single !== 'undefined' || typeof props.row.old_values.single !== 'undefined'">
                                <strong>Simple:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.single}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.single}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.double !== 'undefined' || typeof props.row.old_values.double !== 'undefined'">
                                <strong>Doble:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.double}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.double}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.triple !== 'undefined' || typeof props.row.old_values.triple !== 'undefined'">
                                <strong>Triple:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.triple}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.triple}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.re_entry !== 'undefined' || typeof props.row.old_values.re_entry !== 'undefined'">
                                <strong>Re-ingreso:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.re_entry == 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.re_entry == 1">Activo</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageServiceRate'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-4"
                                 v-if="typeof props.row.new_values.package_service_id !== 'undefined' || typeof props.row.old_values.package_service_id !== 'undefined'">
                                <strong>Servicio:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.package_service_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.package_service_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id !== 'undefined' || typeof props.row.old_values.service_rate_id !== 'undefined'">
                                <strong>Servicio Tarifa:</strong><br>
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
                    <div v-if="props.row.auditable_type == 'App\\PackageServiceRoom'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_plan_room_id !== 'undefined' || typeof props.row.old_values.rate_plan_room_id !== 'undefined'">
                                <strong>Hotel:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_plan_room_id_hotel}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.rate_plan_room_id_hotel}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_plan_room_id !== 'undefined' || typeof props.row.old_values.rate_plan_room_id !== 'undefined'">
                                <strong>Tarifa:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_plan_room_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.rate_plan_room_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate_plan_room_id !== 'undefined' || typeof props.row.old_values.rate_plan_room_id !== 'undefined'">
                                <strong>Habitación:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.rate_plan_room_id_room}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.rate_plan_room_id_room}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageDynamicRate'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_type_id !== 'undefined' || typeof props.row.old_values.service_type_id !== 'undefined'">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_type_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.service_type_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_from !== 'undefined' || typeof props.row.old_values.pax_from !== 'undefined'">
                                <strong>Pax desde:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_from}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.pax_from}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_to !== 'undefined' || typeof props.row.old_values.pax_to !== 'undefined'">
                                <strong>Pax hasta:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_to}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.pax_to}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.simple !== 'undefined' || typeof props.row.old_values.simple !== 'undefined'">
                                <strong>Simple:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.simple}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.simple}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.double !== 'undefined' || typeof props.row.old_values.double !== 'undefined'">
                                <strong>Doble:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.double}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.double}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.triple !== 'undefined' || typeof props.row.old_values.triple !== 'undefined'">
                                <strong>Triple:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.triple}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.triple}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageRateSaleMarkup'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.seller_type !== 'undefined' || typeof props.row.old_values.seller_type !== 'undefined'">
                                <strong
                                    v-if="props.row.new_values.seller_type == 'App\\Market' || props.row.old_values.seller_type == 'App\\Market'">Mercado:</strong>
                                <strong
                                    v-if="props.row.new_values.seller_type == 'App\\Client' || props.row.old_values.seller_type == 'App\\Client'">Cliente:</strong>
                                <br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.seller_type_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.seller_type_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.markup !== 'undefined' || typeof props.row.old_values.markup !== 'undefined'">
                                <strong>Markup:</strong>
                                <br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.markup}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.markup}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.status == 1">Activado</span>
                                    <span v-else>Inactivo</span>
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                    <span v-if="props.row.new_values.status == 1">Activado</span>
                                    <span v-else>Inactivo</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageDynamicSaleRate'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_type_id !== 'undefined' || typeof props.row.old_values.service_type_id !== 'undefined'">
                                <strong>Tipo:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_type_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.service_type_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.seller_type !== 'undefined' || typeof props.row.old_values.seller_type !== 'undefined'">
                                <strong
                                    v-if="props.row.new_values.seller_type == 'App\\Market' || props.row.old_values.seller_type == 'App\\Market'">Mercado:</strong>
                                <strong
                                    v-if="props.row.new_values.seller_type == 'App\\Client' || props.row.old_values.seller_type == 'App\\Client'">Cliente:</strong>
                                <br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.package_rate_sale_markup_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.package_rate_sale_markup_id_name}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3">
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_from !== 'undefined' || typeof props.row.old_values.pax_from !== 'undefined'">
                                <strong>Pax desde:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_from}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.pax_from}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_to !== 'undefined' || typeof props.row.old_values.pax_to !== 'undefined'">
                                <strong>Pax hasta:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_to}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.pax_to}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.simple !== 'undefined' || typeof props.row.old_values.simple !== 'undefined'">
                                <strong>Simple:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.simple}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.simple}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.double !== 'undefined' || typeof props.row.old_values.double !== 'undefined'">
                                <strong>Doble:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.double}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.double}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.triple !== 'undefined' || typeof props.row.old_values.triple !== 'undefined'">
                                <strong>Triple:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.triple}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.triple}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackageInventory'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date !== 'undefined' || typeof props.row.old_values.date !== 'undefined'">
                                <strong>Fecha:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.date}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.date}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.inventory_num !== 'undefined' || typeof props.row.old_values.inventory_num !== 'undefined'">
                                <strong>Disponibilidad:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.inventory_num}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.inventory_num}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.locked !== 'undefined' || typeof props.row.old_values.locked !== 'undefined'">
                                <strong>Bloqueado:</strong><br>
                                <span class="badge badge-secondary text-wrap" v-if="props.row.event != 'created'">
                                <span v-if="props.row.old_values.locked">SI</span>
                                <span v-else>NO</span>
                            </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-wrap" v-if="props.row.event != 'deleted'">
                                <span v-if="props.row.new_values.locked">SI</span>
                                <span v-else>NO</span>
                            </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\PackagePermission'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.package[0].id">
                                <strong>Paquete ID:</strong><br>
                                {{props.row.package[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.user_id_name !== 'undefined' || typeof props.row.old_values.user_id_name !== 'undefined'">
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
                                 v-if="typeof props.row.new_values.tax_id !== 'undefined' || typeof props.row.old_values.tax_id !== 'undefined'">
                                <strong>Valor:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tax_id_value}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                {{props.row.new_values.tax_id_value}}
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
                packages: [],
                eventos: [
                    {code: 'created', label: 'Creado'},
                    {code: 'updated', label: 'Actualizado'},
                    {code: 'deleted', label: 'Eliminado'},
                    {code: 'restored', label: 'Restaurado'},
                ],
                packageSelected: [],
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
                    package_id: null,
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
                return [
                    {
                        type: 'edit',
                        link: 'services_new/edit/',
                        icon: 'dot-circle',
                    },
                    {
                        type: 'delete',
                        link: 'services_new/edit/',
                        icon: 'times',
                    },
                ];
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
                        'package_id': (this.form.package_id === null) ? '' : this.form.package_id,
                        'date_from': (this.form.date_from === '') ? '' : moment(this.form.date_from, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'date_to': (this.form.date_to === '') ? '' : moment(this.form.date_to, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'event': this.form.evento,
                    },
                    requestFunction: function(data) {
                        let url = '/audit/package?token=' + window.localStorage.getItem('access_token') + '&lang='
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
            this.loadPackage();
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
            loadPackage() {
                API.get('/package/search?lang=' + localStorage.getItem('lang')).then((result) => {
                    let packages_data = result.data.data;
                    packages_data.forEach((packages) => {
                        this.packages.push({label: packages.translations[0].name, id: packages.id});
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
                this.packages = [];
                console.log(search);
                API.get('/package/search?lang=' + localStorage.getItem('lang') + '&queryCustom=' + search).
                    then((result) => {
                        loading(false);
                        let packages_data = result.data.data;
                        packages_data.forEach((packages) => {
                            this.packages.push({label: packages.translations[0].name, id: packages.id});
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
            packageChange: function(value) {
                let package_select = value;
                if (package_select != null) {
                    this.form.package_id = package_select.id;
                } else {
                    this.form.package_id = '';
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

