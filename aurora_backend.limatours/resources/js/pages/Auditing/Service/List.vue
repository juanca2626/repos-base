<template>
    <div class="vld-parent">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="container-fluid">
            <div class="form-row">
                <div class="col-sm-5">
                    <label class="col-form-label">Servicios</label>
                    <v-select :options="services"
                              :value="form.service_id"
                              @input="packageChange"
                              label="name" :filterable="false" @search="onSearch"
                              placeholder="Filtro por nombre ó codigo del servicio"
                              v-model="packageSelected" name="services" id="services" style="height: 35px;">
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
                <div class="table-service-package" slot="service" slot-scope="props" style="font-size: 0.9em">
                    <span
                        v-if="props.row.service.length > 0">{{props.row.service[0].id}} - {{props.row.service[0].name}}</span>
                </div>
                <div class="table-service-module" slot="module" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.module}}
                </div>
                <div class="table-service-sobrowser" slot="sobrowser" slot-scope="props" style="font-size: 0.9em">
                    {{props.row.user_agent_name}}
                </div>
                <div slot="child_row" slot-scope="props" class="m-2">
                    <div v-if="props.row.auditable_type == 'App\\Service'">
                        <div class="row m-3">
                            <div class="col-2" v-if="props.row.service[0].id">
                                <strong>Service ID:</strong><br>
                                {{props.row.service[0].id}}
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
                                 v-if="typeof props.row.new_values.aurora_code !== 'undefined' || typeof props.row.old_values.aurora_code !== 'undefined'">
                                <strong>Codigo Aurora:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.aurora_code}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.aurora_code}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.equivalence_aurora !== 'undefined' || typeof props.row.old_values.equivalence_aurora !== 'undefined'">
                                <strong>Equiv. Aurora:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.equivalence_aurora}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.equivalence_aurora}}
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
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.min_age !== 'undefined' || typeof props.row.old_values.min_age !== 'undefined'">
                                <strong>Edad Minima:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.min_age}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.min_age}}</span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_min !== 'undefined' || typeof props.row.old_values.pax_min !== 'undefined'">
                                <strong>Pax Minimo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_min}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.pax_min}}</span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_max !== 'undefined' || typeof props.row.old_values.pax_max !== 'undefined'">
                                <strong>Pax Maximo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_max}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.pax_max}}</span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.unit_id !== 'undefined' || typeof props.row.old_values.unit_id !== 'undefined'">
                                <strong>Unidad de medida:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.unit_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.unit_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.unit_duration_id !== 'undefined' || typeof props.row.old_values.unit_duration_id !== 'undefined'">
                                <strong>Unidad de duración:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.unit_duration_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.unit_duration_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.duration !== 'undefined' || typeof props.row.old_values.duration !== 'undefined'">
                                <strong>Duración:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.duration}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.duration}}</span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.latitude !== 'undefined' || typeof props.row.old_values.latitude !== 'undefined'">
                                <strong>Mapa latitud:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.latitude}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.latitude}}</span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.longitude !== 'undefined' || typeof props.row.old_values.longitude !== 'undefined'">
                                <strong>Mapa longitud:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.longitude}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success">{{props.row.new_values.longitude}}</span>
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
                                    <span v-if="props.row.old_values.infant_min_age === null">0</span>
                                    <span v-else>{{props.row.old_values.infant_min_age}}</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.infant_min_age}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.infant_max_age !== 'undefined' || typeof props.row.old_values.infant_max_age !== 'undefined'">
                                <strong>Edad max. infante:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.infant_max_age === null">0</span>
                                    <span v-else>{{props.row.old_values.infant_max_age}}</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.infant_max_age}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tag_service_id !== 'undefined' || typeof props.row.old_values.tag_service_id !== 'undefined'">
                                <strong>Tag:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.tag_service_id === null">-</span>
                                    <span v-else>{{props.row.old_values.tag_service_id_name}}</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                    <span v-if="props.row.new_values.tag_service_id === null">-</span>
                                    <span v-else>{{props.row.new_values.tag_service_id_name}}</span>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.limit_confirm_hours !== 'undefined' || typeof props.row.old_values.limit_confirm_hours !== 'undefined'">
                                <strong>Limite de horas de confirmación:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <span v-if="props.row.old_values.limit_confirm_hours === null">0</span>
                                    <span v-else>{{props.row.old_values.limit_confirm_hours}}</span>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                      {{props.row.new_values.limit_confirm_hours}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.affected_markup !== 'undefined' || typeof props.row.old_values.affected_markup !== 'undefined'">
                                <strong>Afécto a markup:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.affected_markup === true">Activado</div>
                                    <div v-else-if="props.row.old_values.affected_markup === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.affected_markup === true">Activado</div>
                                    <div v-else-if="props.row.new_values.affected_markup === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_type_id !== 'undefined' || typeof props.row.old_values.service_type_id !== 'undefined'">
                                <strong>Categoria:</strong><br>
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
                                 v-if="typeof props.row.new_values.classification_id !== 'undefined' || typeof props.row.old_values.classification_id !== 'undefined'">
                                <strong>Clasificación:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.classification_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.classification_id_name}}
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.require_itinerary !== 'undefined' || typeof props.row.old_values.require_itinerary !== 'undefined'">
                                <strong>Requiere foto en itinerario:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.require_itinerary === true">Activado</div>
                                    <div v-else-if="props.row.old_values.require_itinerary === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.require_itinerary === true">Activado</div>
                                    <div v-else-if="props.row.new_values.require_itinerary === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.include_accommodation !== 'undefined' || typeof props.row.old_values.include_accommodation !== 'undefined'">
                                <strong>Incluye acomodación:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.include_accommodation === true">Activado</div>
                                    <div
                                        v-else-if="props.row.old_values.include_accommodation === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.include_accommodation === true">Activado</div>
                                    <div
                                        v-else-if="props.row.new_values.include_accommodation === false">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.unit_duration_reserve !== 'undefined' || typeof props.row.old_values.unit_duration_reserve !== 'undefined'">
                                <strong>Unidad de medida reserva:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.unit_duration_reserve === 1">Horas</div>
                                    <div v-else> Días</div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.include_accommodation === 1">Horas</div>
                                    <div v-else> Días</div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_sub_category_id !== 'undefined' || typeof props.row.old_values.service_sub_category_id !== 'undefined'">
                                <strong>Sub Tipo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_sub_category_id_name}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_sub_category_id_name}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceOrigin'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>

                            <div class="col-2"
                                 v-if="typeof props.row.new_values.country_id !== 'undefined' || typeof props.row.old_values.country_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>País:</strong><br>
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.state_id !== 'undefined' || typeof props.row.old_values.state_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Estado:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.state_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.state_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.city_id !== 'undefined' || typeof props.row.old_values.city_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Ciudad:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.city_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.city_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.zone_id !== 'undefined' || typeof props.row.old_values.zone_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Zona:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.zone_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.zone_id_name}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceDestination'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.country_id !== 'undefined' || typeof props.row.old_values.country_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>País:</strong><br>
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.state_id !== 'undefined' || typeof props.row.old_values.state_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Estado:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.state_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.state_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.city_id !== 'undefined' || typeof props.row.old_values.city_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Ciudad:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.city_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.city_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.zone_id !== 'undefined' || typeof props.row.old_values.zone_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Zona:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.zone_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.zone_id_name}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceTranslation'">
                        <div class="row m-3">
                            <div class="col-12 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicios ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.language_id !== 'undefined' || typeof props.row.old_values.language_id !== 'undefined'">
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
                             v-if="typeof props.row.new_values.name_commercial !== 'undefined' || typeof props.row.old_values.name_commercial !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Nombre comercial:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.name_commercial}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.name_commercial}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.description !== 'undefined' || typeof props.row.old_values.description !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Descripción:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.description}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.description}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.itinerary !== 'undefined' || typeof props.row.old_values.itinerary !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Itinerario:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.itinerary}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.itinerary}}
                                </span>
                            </div>
                        </div>
                        <div class="row m-3"
                             v-if="typeof props.row.new_values.summary !== 'undefined' || typeof props.row.old_values.summary !== 'undefined'">
                            <div class="col-12 text-left text-justify">
                                <strong>Summary:</strong><br>
                                <span class="badge badge-secondary text-package" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.summary}}
                                </span>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                   class="text-danger fas fa-chevron-down"></i>
                                <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                <span class="badge badge-success text-package" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.summary}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\RequirementService'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.requirement_id !== 'undefined' || typeof props.row.old_values.requirement_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Pre-requisito:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.requirement_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.requirement_id_name}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\RestrictionService'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.restriction_id !== 'undefined' || typeof props.row.old_values.restriction_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Restricción:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.restriction_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.restriction_id_name}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ExperienceService'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.experience_id !== 'undefined' || typeof props.row.old_values.experience_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Experiencia:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.experience_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.experience_id_name}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceScheduleDetail'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.monday !== 'undefined' || typeof props.row.old_values.monday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Lunes:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.monday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.monday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tuesday !== 'undefined' || typeof props.row.old_values.tuesday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Martes:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.tuesday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.tuesday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.wednesday !== 'undefined' || typeof props.row.old_values.wednesday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Miercoles:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.wednesday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.wednesday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.thursday !== 'undefined' || typeof props.row.old_values.thursday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Jueves:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.thursday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.thursday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.friday !== 'undefined' || typeof props.row.old_values.friday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Viernes:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.friday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.friday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.saturday !== 'undefined' || typeof props.row.old_values.saturday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Sabado:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.saturday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.saturday}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.sunday !== 'undefined' || typeof props.row.old_values.sunday !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Domingo:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.sunday}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.sunday}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceOperation'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.day !== 'undefined' || typeof props.row.old_values.day !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Día:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.day}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.day}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.start_time !== 'undefined' || typeof props.row.old_values.start_time !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Hora de inicio:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.start_time}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.start_time}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.shifts_available !== 'undefined' || typeof props.row.old_values.shifts_available !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Turno disponible:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.shifts_available}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.shifts_available}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.sshh_available !== 'undefined' || typeof props.row.old_values.sshh_available !== 'undefined'">
                                <strong>Disponibilidad de sshh en ruta:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.sshh_available === 1">Si</div>
                                    <div v-else-if="props.row.old_values.sshh_available === 0">No</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.sshh_available === 1">Si</div>
                                    <div v-else-if="props.row.new_values.sshh_available === 0">No</div>
                                    <div v-else> - </div>
                                </span>
                            </div>

                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceOperationActivity'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-10"
                                 v-if="typeof props.row.new_values.service_type_activity_id !== 'undefined' || typeof props.row.old_values.service_type_activity_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Actividad:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_type_activity_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_type_activity_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.minutes !== 'undefined' || typeof props.row.old_values.minutes !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Minutos:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.minutes}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.minutes}}
                                </span>
                                </div>
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
                    <div v-if="props.row.auditable_type == 'App\\ServiceTax'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.amount !== 'undefined' || typeof props.row.old_values.amount !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Monto:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.amount}} %
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.amount}} %
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceChild'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.min_age !== 'undefined' || typeof props.row.old_values.min_age !== 'undefined'">
                                <div class="col-12 text-left text-justify">
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.max_age !== 'undefined' || typeof props.row.old_values.max_age !== 'undefined'">
                                <div class="col-12 text-left text-justify">
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>

                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceInclusion'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.day !== 'undefined' || typeof props.row.old_values.day !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Día:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.day}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.day}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.inclusion_id !== 'undefined' || typeof props.row.old_values.inclusion_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Inclusion:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.inclusion_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.inclusion_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.include !== 'undefined' || typeof props.row.old_values.include !== 'undefined'">
                                <strong>Incluye Si/No:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.include === 1">Si</div>
                                    <div v-else-if="props.row.old_values.include === 0">No</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.include === 1">Si</div>
                                    <div v-else-if="props.row.new_values.include === 0">No</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.see_client !== 'undefined' || typeof props.row.old_values.see_client !== 'undefined'">
                                <strong>Ver cliente Si/No:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.see_client === 1">Si</div>
                                    <div v-else-if="props.row.old_values.see_client === 0">No</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.see_client === 1">Si</div>
                                    <div v-else-if="props.row.new_values.see_client === 0">No</div>
                                    <div v-else> - </div>
                                </span>
                            </div>

                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceRate'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
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
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.allotment !== 'undefined' || typeof props.row.old_values.allotment !== 'undefined'">
                                <strong>Allotment:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.allotment === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.allotment === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.allotment === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.allotment === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.rate !== 'undefined' || typeof props.row.old_values.rate !== 'undefined'">
                                <strong>Tarifa:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.rate === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.rate === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.rate === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.rate === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.taxes !== 'undefined' || typeof props.row.old_values.taxes !== 'undefined'">
                                <strong>Impuestos:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.taxes === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.taxes === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.taxes === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.taxes === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.services !== 'undefined' || typeof props.row.old_values.services !== 'undefined'">
                                <strong>Servicios:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.services === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.services === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.services === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.services === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.advance_sales !== 'undefined' || typeof props.row.old_values.advance_sales !== 'undefined'">
                                <strong>Venta anticipada:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.advance_sales === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.advance_sales === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.advance_sales === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.advance_sales === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.promotions !== 'undefined' || typeof props.row.old_values.promotions !== 'undefined'">
                                <strong>Promoción:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.promotions === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.promotions === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.promotions === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.promotions === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceRatePlan'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id_name !== 'undefined' || typeof props.row.old_values.service_rate_id_name !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Tarifa:</strong><br>
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
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_cancellation_policy_id !== 'undefined' || typeof props.row.old_values.service_cancellation_policy_id !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Politica de cancelación:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.service_cancellation_policy_id_name}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.service_cancellation_policy_id_name}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_from !== 'undefined' || typeof props.row.old_values.date_from !== 'undefined'">
                                <div class="col-12 text-left text-justify">
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date_to !== 'undefined' || typeof props.row.old_values.date_to !== 'undefined'">
                                <div class="col-12 text-left text-justify">
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.monday !== 'undefined' || typeof props.row.old_values.monday !== 'undefined'">
                                <strong>Lunes:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.monday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.monday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.monday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.monday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.tuesday !== 'undefined' || typeof props.row.old_values.tuesday !== 'undefined'">
                                <strong>Martes:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.tuesday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.tuesday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.tuesday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.tuesday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.wednesday !== 'undefined' || typeof props.row.old_values.wednesday !== 'undefined'">
                                <strong>Miercoles:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.wednesday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.wednesday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.wednesday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.wednesday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.thursday !== 'undefined' || typeof props.row.old_values.thursday !== 'undefined'">
                                <strong>Jueves:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.thursday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.thursday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.thursday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.thursday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.friday !== 'undefined' || typeof props.row.old_values.friday !== 'undefined'">
                                <strong>Viernes:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.friday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.friday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.friday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.friday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.saturday !== 'undefined' || typeof props.row.old_values.saturday !== 'undefined'">
                                <strong>Sabado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.saturday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.saturday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.saturday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.saturday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.sunday !== 'undefined' || typeof props.row.old_values.sunday !== 'undefined'">
                                <strong>Domingo:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.sunday === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.sunday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.sunday === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.sunday === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_from !== 'undefined' || typeof props.row.old_values.pax_from !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Pax Desde:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_from}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.pax_from}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.pax_to !== 'undefined' || typeof props.row.old_values.pax_to !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Pax Hasta:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.pax_to}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.pax_to}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.price_adult !== 'undefined' || typeof props.row.old_values.price_adult !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Precio Adulto:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.price_adult}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.price_adult}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.price_child !== 'undefined' || typeof props.row.old_values.price_child !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Precio niño:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.price_child}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.price_child}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.price_infant !== 'undefined' || typeof props.row.old_values.price_infant !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Precio infante:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.price_infant}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.price_infant}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.price_guide !== 'undefined' || typeof props.row.old_values.price_guide !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Precio guia:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.price_guide}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.price_guide}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.status !== 'undefined' || typeof props.row.old_values.status !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.old_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.status === 1">Activado</div>
                                    <div v-else-if="props.row.new_values.status === 0">Desactivado</div>
                                    <div v-else> - </div>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="props.row.auditable_type == 'App\\ServiceInventory'">
                        <div class="row m-3">
                            <div class="col-2 text-left text-justify" v-if="props.row.service[0].id">
                                <strong>Servicio ID:</strong><br>
                                {{props.row.service[0].id}}
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.service_rate_id_name !== 'undefined' || typeof props.row.old_values.service_rate_id_name !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Tarifa:</strong><br>
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
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.day !== 'undefined' || typeof props.row.old_values.day !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Día:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.day}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.day}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.date !== 'undefined' || typeof props.row.old_values.date !== 'undefined'">
                                <div class="col-12 text-left text-justify">
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
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.inventory_num !== 'undefined' || typeof props.row.old_values.inventory_num !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Inventario:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.inventory_num}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.inventory_num}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.total_booking !== 'undefined' || typeof props.row.old_values.total_booking !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Total Reservas:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.total_booking}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.total_booking}}
                                </span>
                                </div>
                            </div>
                            <div class="col-2"
                                 v-if="typeof props.row.new_values.total_canceled !== 'undefined' || typeof props.row.old_values.total_canceled !== 'undefined'">
                                <div class="col-12 text-left text-justify">
                                    <strong>Total canceladas:</strong><br>
                                    <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    {{props.row.old_values.total_canceled}}
                                </span>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <i v-if="props.row.event != 'created' && props.row.event != 'deleted'"
                                       class="text-danger fas fa-chevron-down"></i>
                                    <br v-if="props.row.event != 'created' && props.row.event != 'deleted'">
                                    <span class="badge badge-success" v-if="props.row.event != 'deleted'">
                                    {{props.row.new_values.total_canceled}}
                                </span>
                                </div>
                            </div>

                            <div class="col-2"
                                 v-if="typeof props.row.new_values.locked !== 'undefined' || typeof props.row.old_values.locked !== 'undefined'">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-secondary" v-if="props.row.event != 'created'">
                                    <div v-if="props.row.old_values.locked === 1">Si</div>
                                    <div v-else-if="props.row.old_values.locked === 0">No</div>
                                    <div v-else> - </div>
                                </span><br v-if="props.row.event != 'created'">
                                <i v-if="props.row.event != 'created'" class="text-danger fas fa-chevron-down"></i><br
                                v-if="props.row.event != 'created'">
                                <span class="badge badge-success">
                                     <div v-if="props.row.new_values.locked === 1">Si</div>
                                    <div v-else-if="props.row.new_values.locked === 0">No</div>
                                    <div v-else> - </div>
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
                services: [],
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
                        'service',
                        'module',
                        'ip_address',
                        'sobrowser',
                        'date_audit',
                        'actions'],
                },
                urlAudit: '',
                form: {
                    service_id: null,
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
                        service: 'Servicio',
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
                        'service_id': (this.form.service_id === null) ? '' : this.form.service_id,
                        'date_from': (this.form.date_from === '') ? '' : moment(this.form.date_from, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'date_to': (this.form.date_to === '') ? '' : moment(this.form.date_to, 'DD/MM/YYYY').
                            format('YYYY-MM-DD'),
                        'event': this.form.evento,
                    },
                    requestFunction: function(data) {
                        let url = '/audit/service?token=' + window.localStorage.getItem('access_token') + '&lang='
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
            this.loadService();
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
            loadService() {
                API.get('/service/search?lang=' + localStorage.getItem('lang')).then((result) => {
                    let packages_data = result.data.data;
                    packages_data.forEach((services) => {
                        this.services.push({label: services.aurora_code + ' - ' + services.name, id: services.id});
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
                this.services = [];
                console.log(search);
                API.get('/service/search?lang=' + localStorage.getItem('lang') + '&query=' + search).
                    then((result) => {
                        loading(false);
                        let packages_data = result.data.data;
                        packages_data.forEach((services) => {
                            this.services.push({label: services.aurora_code + ' - ' + services.name, id: services.id});
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
                    this.form.service_id = package_select.id;
                } else {
                    this.form.service_id = '';
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

