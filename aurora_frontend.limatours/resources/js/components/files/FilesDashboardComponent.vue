<template>
    <div class="container-fluid files-dashboard page-hoja-master" @mousemove="mouse_move">

        <div class="mouse_user" v-for="user in users_in_room"
             v-if="users_in_room !== undefined && users_in_room.length>1 && code !== user.id && user.pos_x && (user.active_cursor)"
             :style="'left:'+user.pos_x+'px; top:'+user.pos_y+'px; color:'+user.color">
                <i class="fa fa-hand-pointer"> {{user.name}}</i>
        </div>

        <!------- Cabecera ------->
        <div class="head-dashboard row">
            <div class="col-md-12">
                <div class="float-left mb-3">
                    <h3 class="content-title">
                        <span class="mr-4" v-on:click="showHeaderFile()"><span class="icon-edit cursor-pointer ml-3"></span></span>
                        <span class="mr-4" v-on:click="showHeaderFile()"><b class="subtitle">N° File:</b> {{ nrofile }}</span>
                        <span class="mr-4" v-on:click="showHeaderFile()"><b>Nombre</b>: {{ file.descri }}</span>
                        <span class="mr-4" v-on:click="showHeaderFile()"><b>Paxs</b>: {{ file.paxs }} </span>
                        <span class="mr-4" v-on:click="showHeaderFile()"><b>Fecha In</b>: {{ file.diain | formDate }} </span>
                        <span class="mr-4" v-on:click="showHeaderFile()"><b>Cliente</b>: {{ file.razoncli }} </span>
                        <span class="mr-4" v-on:click="showHeaderFile()">
                            <span v-if="!flag_header_file" class="icon-plus-circle"></span>
                            <span v-if="flag_header_file" class="icon-minus-circle"></span>
                        </span>
                    </h3>
                </div>
                <div class="float-left">
                    <div class="head-filters">
                        <a class="icon-users pr-2 btn-filter"
                           title="Pasajeros"
                           v-on:click="modalPassengers(nrofile, file.paxs, file.canadl, file.canchd, file.caninf)">
                        </a>
                        <a class="icon-list pr-2 btn-filter"
                           title="Acomodo de pasajeros - General"
                           v-on:click="passengerAccommodation()">
                        </a>
                        <span>
                            <v-select id="filter_city"
                                      @input="filterCity"
                                      :options="filter_cities"
                                      v-model="filter_city"
                                      class="p2 border"
                                      style="width:200px;display:inline-block;"
                                      autocomplete="true">
                            </v-select>
                            <!-- span class="icon-map-pin pr-2"></span>
                            {{ file.ciudad }}
                            -->
                        </span>
                        <span>Filtrar por:</span>
                        <a href="javascript:;"
                           title="Todos"
                           v-bind:class="['badge btn-filter', (filters.length == 0) ? 'filter-all' : '']"
                           v-on:click="toggleFilter('all')">
                            <span class="icon-globe"></span>
                        </a>
                        <a href="javascript:;"
                           title="Traslados"
                           v-bind:class="['badge btn-filter', (filters.indexOf('traslados') > -1 || filters.length == 0) ? 'filter-trans' : '']"
                           v-on:click="toggleFilter('traslados')">
                            <span class="icon-car"></span>
                        </a>
                        <a href="javascript:;"
                           title="Vuelos"
                           v-bind:class="['badge btn-filter', (filters.indexOf('vuelos') > -1 || filters.length == 0) ? 'filter-aec' : '']"
                           v-on:click="toggleFilter('vuelos')">
                            <span class="icon-flight"></span>
                        </a>
                        <a href="javascript:;"
                           title="Paquetes"
                           v-bind:class="['btn-filter', (filters.indexOf('paquetes') > -1 || filters.length == 0) ? 'filter-exc' : '']"
                           v-on:click="toggleFilter('paquetes')">
                            <span class="icon-briefcase"></span>
                        </a>
                        <a href="javascript:;"
                           title="Hoteles"
                           v-bind:class="['badge btn-filter', (filters.indexOf('Hoteles') > -1 || filters.length == 0) ? 'filter-htl' : '']"
                           v-on:click="toggleFilter('Hoteles')">
                            <span class="icon-building"></span>
                        </a>
                        <a href="javascript:;"
                           title="Trenes"
                           v-bind:class="['badge btn-filter', (filters.indexOf('trenes') > -1 || filters.length == 0) ? 'badge-danger filter-tren' : '']"
                           v-on:click="toggleFilter('trenes')">
                            <span class="icon-train"></span>
                        </a>
                        <a href="javascript:;"
                           title="Restaurantes"
                           v-bind:class="['badge btn-filter', (filters.indexOf('restaurantes') > -1 || filters.length == 0) ? 'filter-res' : '']"
                           v-on:click="toggleFilter('restaurantes')">
                            <span class="icon-am-food"></span>
                        </a>
                        <a href="javascript:;"
                           title="Guías"
                           v-bind:class="['badge', (filters.indexOf('guias') > -1) ? 'badge-dark' : '']"
                           class="btn-filter"
                           v-on:click="toggleFilter('guias')">
                            <span class="icon-map"></span>
                        </a>
                    </div>
                </div>

                <div class="float-right">

                    <div class="contenedor" v-if="users_in_room !== undefined && users_in_room.length>1">

                        <div class="conectados">
                            <div class="conectados-descripcion">{{ translations.label.connected }}</div>
                            <div class="conectados-avatares">
                                <div class="avatar-content" data-toggle="tooltip" data-placement="top"
                                     v-for="user in users_in_room" :title="user.name" @click="set_active_cursor(user.id)">
                                    <i class="fa fa-hand-pointer cursor-icon" :style="'color:'+user.color" v-if="user.active_cursor"></i>
                                    <img v-if="user.usertype==='registered'" :src="baseURL + 'images/users/' + user.photo"
                                         :style="'box-shadow: -1px 1px 3px 2px '+ user.color"
                                         onerror="this.src = baseURL + 'images/users/user_default.png'">
                                    <img :src="baseURL + 'images/anonimo.jpg'" v-else>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <template v-if="flag_header_file">
            <div class="card card-file mt-5">
                <div class="card-header">
                    Detalle del FILE
                    <div>

                    </div>
                </div>
                <div class="card-body">
                    <form>
                        <div>
                            <h3 class="title-section">En relación al cliente</h3>
                            <div class="row">
                                <div class="col-12 col-md-1">
                                    <div class="form-group">
                                        <label for="sector"><b>Sector</b></label>
                                        <input type="text" class="form-control form-control-sm" id="sector" v-model="file.codsec" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label for="ref"><b>Ref.</b></label>
                                        <input type="text" class="form-control form-control-sm" id="ref" v-model="file.nroref" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <div class="form-group">
                                        <label for="cliente"><b>Cliente</b></label>
                                        <v-select id="cliente" :options="all_clients"
                                                  :value="file.codcli"
                                                  v-model="file.codcli"
                                                  class="form-control form-control-sm"
                                                  autocomplete="true">
                                        </v-select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label for="fecha"><b>Fecha</b></label>
                                        <date-picker class="form-control form-control-sm" id="fecha" v-model="file.fecha_show" :config="options">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label for="tipo_venta"><b>Tip. Venta</b></label>
                                        <select class="form-control form-control-sm" id="tipo_venta" v-model="file.tipoventa">
                                            <option value="-">-------</option>
                                            <option value="1">Contado</option>
                                            <option value="2">Crédito</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="tarifa"><b>Tarifa</b></label>
                                        <input type="text" class="form-control form-control-sm" id="tarifa" v-model="file.tarifa" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label for="atendido_por"><b>Atendido por</b></label>
                                        <input type="text" class="form-control form-control-sm" id="atendido_por" v-model="file.codope" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="ref_ext"><b>Ref. Ext.</b></label>
                                        <input type="text" class="form-control form-control-sm" id="ref_ext" v-model="file.refext" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="title-section">En relación al pasajero</h3>
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label for="pax"><b>Pax</b></label>
                                        <input type="text" class="form-control form-control-sm" id="pax" v-model="file.descri" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-1">
                                    <div class="form-group">
                                        <label for="adultos"><b>Adultos</b></label>
                                        <input type="number" class="form-control form-control-sm" id="adultos" v-model="file.canadl" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-1">
                                    <div class="form-group">
                                        <label for="niños"><b>Niños</b></label>
                                        <input type="number" class="form-control form-control-sm" id="niños" v-model="file.canchd" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-1">
                                    <div class="form-group">
                                        <label for="infantes"><b>Infantes</b></label>
                                        <input type="number" class="form-control form-control-sm" id="infantes" v-model="file.caninf" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-1">
                                    <div class="form-group">
                                        <label for="idioma"><b>Idioma</b></label>
                                        <input type="text" class="form-control form-control-sm" id="idioma" v-model="file.idioma" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label for="diain"><b>Dia Inicio</b></label>
                                        <date-picker class="form-control  form-control-sm" id="diain" v-model="file.diain_show" :config="options">
                                        </date-picker>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label for="diaout"><b>Dia Fin</b></label>
                                        <date-picker class="form-control  form-control-sm" id="diaout" v-model="file.diaout_show" :config="options">
                                        </date-picker>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="solicito"><b>Solicitó</b></label>
                                    <input type="text" class="form-control form-control-sm" id="solicito" v-model="file.solici" />
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="promocion"><b>Promoción</b></label>
                                    <input type="text" class="form-control form-control-sm" id="promocion" v-model="file.promos" />
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="observaciones"><b>Observaciones</b></label>
                                    <input type="text" class="form-control form-control-sm" id="observaciones" v-model="file.observ" />
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="rest_pedido"><b>Rest. Pedido</b></label>
                                    <input type="text" class="form-control form-control-sm" id="rest_pedido" v-model="file.nroped" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="vendedor"><b>Vendedor</b></label>
                                    <input type="text" class="form-control form-control-sm" id="vendedor" v-model="file.codven" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="operador"><b>Operó</b></label>
                                    <input type="text" class="form-control form-control-sm" id="operador" v-model="file.operad" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="tkt"><b>TKT</b></label>
                                    <input type="text" class="form-control form-control-sm" id="tkt" v-model="file.ticket" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="factura"><b>Factura</b></label>
                                    <input type="text" class="form-control form-control-sm" id="factura" v-model="file.factur" />
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div id="modalAlerta">
                                    <div class="group-btn">
                                        <button type="button" v-bind:disabled="loading_button" @click="update_file()"
                                                class="btn btn-primary">Actualizar FILE
                                        </button>
                                        <button type="button" v-bind:disabled="loading_button" @click="cancelHeaderFile()"
                                                class="btn btn-secondary">Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <div v-if="!flag_header_file">
            <!------- Cabecera ------->
            <!-- div class="row filtros">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-8">
                            <ol class="box filter">
                                <li class="box-item">Cambiar vista:</li>
                                <li class="box-item">
                                    <a href="javascript:void(0);" v-on:click="view=0;changeView(view);">
                                        <i class="icon-trello" v-bind:class="[(view==0)?'font-weight-bold':'']"></i>
                                    </a>
                                </li>
                                <li class="box-item">
                                    <a href="javascript:void(0);" v-on:click="view=1;changeView(view);">
                                        <i class="icon-calendar" v-bind:class="[(view==1)?'font-weight-bold':'']"></i>
                                    </a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div -->
            <!------- calender ------->
<!--            @event-delete="onEventDelete"-->
            <div class="container-fluid calender mt-5 p-0">
                <vue-cal id="calendar-files"
                         ref="vuecal"
                         :selected-date="dayNow"
                         :time-from="0 * 60"
                         :time-to="24 * 60"
                         :disable-views="['years', 'year', 'month']"
                         :locale="locale"
                         x-small
                         :editable-events="{ title: false, drag: true, resize: true, delete: false, create: true }"
                         cell-contextmenu
                         show-all-day-events="short"
                         :on-event-click="onEventClick"
                         @event-change="onEventChange"
                         :on-event-create="onEventCreate"
                         @cell-click="$refs.vuecal.createEvent(
                            $event,
                            60,
                            { title: '' }
                         )"
                         :events="services">
                </vue-cal>
            </div>
            <!-- calendar -->

            <div class="modal modal--cotizacion" id="modal" data-keyword="false" data-backdrop="static" tabindex="-1" role="dialog" style="overflow: scroll;">
                <div class="modal-dialog modal--cotizacion__document" role="document">
                    <div class="modal-content modal--cotizacion__content">
                        <div class="modal-header">
                            <template v-if="selectedEvent.nroite == undefined || selectedEvent.nroite == 0">
                                <button class="close" v-on:click="deleteEvent()" type="button" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                            </template>
                            <template v-else>
                                <button class="close" v-on:click="cancelEvent()" type="button" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                            </template>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning" v-if="loading">
                                Actualizando información en el calendario general.. Espere un momento.
                            </div>
                            <div class="btn-group mb-5" role="group" v-if="(selectedEvent.nroite == undefined || selectedEvent.nroite == 0) && !readonly">
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'TRAS') ? 'active' : '']"
                                        v-on:click="toggleType('TRAS')">
                                    TRASLADOS
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'PAQ') ? 'active' : '']"
                                        v-on:click="toggleType('PAQ')">
                                    PAQUETES
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'ENT') ? 'active' : '']"
                                        v-on:click="toggleType('ENT')">
                                    ENTRADAS
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'TOUR') ? 'active' : '']"
                                        v-on:click="toggleType('TOUR')">
                                    TOUR
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'RES') ? 'active' : '']"
                                        v-on:click="toggleType('RES')">
                                    RESTAURANTE
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'TREN') ? 'active' : '']"
                                        v-on:click="toggleType('TREN')">
                                    TREN
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'ASI') ? 'active' : '']"
                                        v-on:click="toggleType('ASI')">
                                    ASISTENCIA
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'MISC') ? 'active' : '']"
                                        v-on:click="toggleType('MISC')">
                                    MISCELÁNEOS
                                </button>
                                <button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'VUELO') ? 'active' : '']"
                                        v-on:click="toggleType('VUELO')">
                                    VUELOS
                                </button>
                                <button v-bind:class="['btn', 'btn-light', (type == 'HOT') ? 'active' : '']"
                                        v-on:click="toggleType('HOT')">
                                    HOTELES
                                </button>
                                <!-- button type="button"
                                        v-bind:class="['btn', 'btn-light', (type == 'traslado_aerodromo') ? 'active' : '']"
                                        v-on:click="toggleType('traslado_aerodromo')">
                                    TRASLADO AERÓDROMO
                                </button -->
                            </div>
                            <div class="col-xs-12" style="overflow:hidden" v-if="selectedEvent.nroite != undefined && view == 'detail'">
                                <div class="float-right" >
                                    <button v-bind:disabled="loading_button" type="button" v-if="!flag_edit && selectedEvent.clase!=='H'" v-on:click="editEvent()" class="btn btn-white mt-2 mb-2" style="font-size: 2.5rem;"><span class="icon-edit"></span></button>
                                    <!-- button type="button" v-if="!flag_edit" v-on:click="deleteEvent()" class="btn btn-white mt-2 mb-2"><i class="fa fa-trash fa-2x"></i></button -->
                                    <button v-bind:disabled="loading_button" type="button" v-if="flag_edit" v-on:click="cancelEvent()" class="btn btn-white mt-2 mb-2" style="font-size: 2.5rem;"><span class="icon-arrow-left"></span></button>
                                    <button v-bind:disabled="loading_button" type="button" v-if="flag_edit" v-on:click="saveEvent()" class="btn btn-white mt-2 mb-2" style="font-size: 2.5rem;"><span class="icon-save"></span></button>
                                </div>
                            </div>

                            <template v-if="type != 'VUELO' && type != 'HOT'">
                                <div class="alert alert-warning" v-if="!((selectedEvent.ciuin != undefined && selectedEvent.ciuin != '') || (selectedEvent.ciuout != undefined && selectedEvent.ciuout != ''))">Se mostrarán todos los servicios, a menos que se filtren por ciudades de origen o destino.</div>
                                <div class="alert alert-danger" v-if="selectedEvent.service != undefined && selectedEvent.service.length > 0 && all_bastars.length == 0"><span class="icon-help-circle" style="font-size: 2.5rem;"></span> No se encontró tarifa disponible para el servicio y la cantidad de pasajeros.</div>
                            </template>

                            <template v-if="transfers.length > 1 && flag_edit && transfer.nroite == undefined">
                                <div class="alert alert-warning mt-3">Traslados existentes para la fecha seleccionada.</div>

                                <table class="table table-xs table-striped table-hover" v-if="transfers.length > 1">
                                    <thead>
                                    <tr>
                                        <th>CODIGO</th>
                                        <th>REFERENCIA</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(transfer, t) in transfers" v-if="transfer.ciudes == selectedEvent.ciuin">
                                        <td>{{ transfer.codsvs }} ({{ transfer.fecin }} {{ transfer.horin | formHour }})</td>
                                        <td>{{ transfer.title }})</td>
                                        <td><button class="btn btn-info btn-xs" v-on:click="setTransfer(t)">Seleccionar traslado</button></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </template>

                            <template v-if="(((type == 'TREN' || (selectedEvent.catser == 'TREN' && selectedEvent.nroite != undefined)) && !flag_edit) || (type != 'TREN' || (selectedEvent.catser != 'TREN' && selectedEvent.nroite != undefined)))">
                                <div class="modal--cotizacion__header d-flex">
                                    <template v-if="!flag_edit">
                                        <h3 class="modal-title pb-3" style="border-bottom:2px dashed #eee;"><b><i v-bind:class="[selectedEvent.icon, 'pr-2']" v-if="selectedEvent.icon != ''"></i> {{ selectedEvent.codsvs }} | {{ selectedEvent.title }}</b></h3>
                                    </template>
                                    <template v-else>
                                        <template v-if="selectedEvent.nroite == undefined">
                                            <template v-if="!(type == 'VUELO' || selectedEvent.catser == 'VUELO' || type == 'HOT' || selectedEvent.catser == 'HOT')">
                                                <template v-if="(selectedEvent.service == undefined || selectedEvent.service == '')">
                                                    <input type="text" class="form-control" placeholder="Filtrar servicios.." v-model="query" v-on:keyup="searchServices(query)" />
                                                </template>
                                                <template v-else>
                                                    <input type="text" class="form-control" v-bind:value="selectedEvent.service.codigo + ' - ' + selectedEvent.service.descri" disabled="disabled" />
                                                </template>
                                                <button v-if="!readonly" type="button" v-on:click="reset('service')" class="btn link-clear">LIMPIAR FILTRO</button>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <input type="text" class="form-control" placeholder="Título del servicio" v-model="selectedEvent.title" />
                                        </template>
                                    </template>
                                </div>

                                <template v-if="loading_services">
                                    <div class="alert alert-warning">Cargando servicios.. Por favor, espere..</div>
                                </template>

                                <template v-if="all_services.length > 0 && !loading_services && selectedEvent.nroite == undefined && (selectedEvent.service == undefined || selectedEvent.service == '') && type != 'VUELO' && type != 'HOT'">
                                    <div class="d-block mt-3">
                                        <table class="table table-stripped table-hover">
                                            <thead>
                                            <tr>
                                                <td>CÓDIGO</td>
                                                <td>DESCRIPCIÓN</td>
                                                <td v-if="type == 'TREN'">HORIN</td>
                                                <td v-if="type == 'TREN'">HOROUT</td>
                                                <td><i class="fa fa-cogs"></i></td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(_all_service, _as) in all_services">
                                                <td>
                                                    {{ _all_service.codigo }}
                                                </td>
                                                <td>
                                                    {{ _all_service.descri }}
                                                </td>
                                                <td v-if="type == 'TREN'">
                                                    {{ _all_service.horin }}
                                                </td>
                                                <td v-if="type == 'TREN'">
                                                    {{ _all_service.horout }}
                                                </td>
                                                <td>
                                                    <button type="button mb-1" v-on:click="showDetailService(_all_service.codigo, true)" title="Ver remarks y restricciones" class="btn btn-info btn-sm"><i class="fa fa-clipboard" style="font-size:1.5em!important;"></i></button>
                                                    <button type="button" v-on:click="setService(_all_service)" title="Seleccionar" class="btn btn-info btn-sm"><i class="fa fa-hand-point-left" style="font-size:1.5em!important;"></i></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modal modal--cotizacion" id="modalAlertaRemarks" role="dialog" style="overflow: scroll;">
                                        <div class="modal-dialog modal-sm modal--cotizacion__document" role="document">
                                            <div class="modal-content modal--cotizacion__content">
                                                <div class="modal-header">
                                                    <button class="close" type="button" v-on:click="closeModal('modalAlertaRemarks')" aria-label="Close"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="modal--cotizacion__header">
                                                        <h3 class="modal-title"><b>Políticas y Restricciones</b></h3>
                                                    </div>
                                                    <div class="modal--cotizacion__body">
                                                        <div class="d-block">
                                                            <div class="alert alert-warning" v-if="loading_remarks || loading_restrictions">Cargando remarks y restricciones...</div>
                                                            <template v-if="!(loading_remarks || loading_restrictions)">
                                                                <template v-if="content_remarks != '' || content_restrictions != ''">
                                                                    <ul class="nav nav-tabs">
                                                                        <li class="nav-item" v-if="content_remarks != ''">
                                                                            <a v-bind:class="['nav-link', (flag_view == 'remarks') ? 'active' : '']" v-on:click="toggleFlag('remarks')" href="#">Remarks</a>
                                                                        </li>
                                                                        <li class="nav-item" v-if="content_restrictions != ''">
                                                                            <a v-bind:class="['nav-link', (flag_view == 'restrictions' || content_remarks == '') ? 'active' : '']" v-on:click="toggleFlag('restrictions')" href="#">Restricciones</a>
                                                                        </li>
                                                                    </ul>
                                                                    <div class="tab-content">
                                                                        <div v-bind:class="['tab-pane', 'show', 'active']">
                                                                            <template v-if="flag_view == 'remarks' && content_remarks != ''">
                                                                                <textarea v-model="content_remarks" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                            </template>
                                                                            <template v-if="(flag_view == 'restrictions' || content_remarks == '') && content_restrictions != ''">
                                                                                <textarea v-model="content_restrictions" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                                <template v-else>
                                                                    <div class="alert alert-info">No se encontró información para remarks o restricciones para el servicio seleccionado.</div>
                                                                </template>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div class="modal--cotizacion__header">
                                    <template v-if="flag_bastar != ''">
                                        <template v-if="all_bastars.length > 1">
                                            <template v-if="flag_edit">
                                                <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.bastar != '' && typeof selectedEvent.bastar == 'string'">BASE DE TARIFAS INICIAL: {{ selectedEvent.bastar }}</span -->

                                                <v-select class="form-control p-2 border"
                                                          style="width: 100%; margin-right: 2rem;"
                                                          :options="all_bastars"
                                                          label="codigo" :filterable="false"
                                                          placeholder="Tarifa"
                                                          v-model="selectedEvent.bastar">
                                                    <template slot="option" slot-scope="option">
                                                        <div class="d-center">
                                                            {{ option.bastar }} - {{ option.desbas }}
                                                        </div>
                                                    </template>
                                                    <template slot="selected-option" slot-scope="option">
                                                        <div class="selected d-center">
                                                            {{ option.bastar }} - {{ option.desbas }}
                                                        </div>
                                                    </template>
                                                </v-select>
                                                <button type="button" v-on:click="reset('bastar')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                            </template>
                                            <template v-else>
                                                <template v-if="!loading">
                                                    <input type="text" disabled="disabled" class="p-2 border col-lg-1" placeholder="Base de Tarifas" v-bind:value="(typeof selectedEvent.bastar == 'object') ? selectedEvent.bastar.bastar : selectedEvent.bastar" />
                                                    <input type="text" disabled="disabled" class="p-2 border col-lg-9" placeholder="Base de Tarifas" v-bind:value="selectedEvent.desbas" />
                                                </template>
                                                <template v-else>
                                                    Cargando información de tarifas...
                                                </template>
                                            </template>
                                        </template>
                                    </template>
                                </div>
                            </template>

                            <div class="modal--cotizacion__body">
                                <div class="d-flex align-items-start" v-if="!(selectedEvent.see_preview_communications)">
                                    <div v-bind:class="['col-lg-' + ((view == 'detail' && !flag_edit) ? 10 : 12)]">
                                        <div class="d-block" v-if="view == 'detail'">
                                            <table id="table-reset">
                                                <tr v-if="flag_edit && (selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') && (type == 'RES' || selectedEvent.tipsvs == 'RES')">
                                                    <td class="text-center">
                                                        <span class="icon-am-dinner"></span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="filter_service"
                                                                   v-bind:disabled="(typeof selectedEvent.service == 'object' && Object.entries(selectedEvent.service).length > 0)"
                                                                   v-model="filter_service" id="filter_lunch" value="8" />
                                                            <label class="form-check-label" for="filter_lunch">Almuerzo</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="filter_service"
                                                                   v-bind:disabled="(typeof selectedEvent.service == 'object' && Object.entries(selectedEvent.service).length > 0)"
                                                                   v-model="filter_service" id="filter_dinner" value="9" />
                                                            <label class="form-check-label" for="filter_dinner">Cena</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <template v-if="(selectedEvent.catser == 'VUELO' || type == 'VUELO')">
                                                    <tr v-if="!(selectedEvent.tipsvs == 'EXC' || type == 'TOUR' || type == 'RES' || selectedEvent.tipsvs == 'RES' || type == 'ENT' || selectedEvent.tipsvs == 'ENT')">
                                                        <td class="text-center">
                                                            <i class="icon-flight"></i>
                                                        </td>
                                                        <td>
                                                            <template v-if="!flag_edit">
                                                                <p v-if="selectedEvent.ciavue != '' && selectedEvent.nrovue != ''" class="pb-0 m-0">
                                                                    <strong>{{ ( selectedEvent.ciavue && typeof selectedEvent.ciavue == 'object' && typeof selectedEvent.ciavue.codigo != 'undefined') ? selectedEvent.ciavue.codigo : selectedEvent.ciavue }}</strong> - <strong>{{ selectedEvent.nrovue }}</strong></p>
                                                                <p v-else class="pb-0 m-0">Información de vuelo no disponible.</p>
                                                            </template>
                                                            <template v-else>
                                                                <div style="width:250px; display:inline-block;">
                                                                    <v-select v-bind:class="['p-2', 'border', (!readonly) ? '' : 'disabled']"
                                                                              :options="airlines"
                                                                              value="codigo"
                                                                              label="codigo" :filterable="false"
                                                                              @input="searchInfoFlight()"
                                                                              @search="searchAirlines"
                                                                              placeholder="Línea Aérea"
                                                                              v-model="selectedEvent.ciavue"
                                                                              style="padding-top: 0.2rem!important; width: 250px!important;">
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
                                                                </div>

                                                                <div style="width:250px; display:inline-block;">
                                                                    <input v-model="selectedEvent.nrovue" v-on:change="searchInfoFlight()" v-bind:disabled="readonly" class="p-2 m-1 border" placeholder="Número de vuelo" />
                                                                </div>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td scope="row" colspan="2">
                                                            <div class="alert alert-warning mt-3" v-if="flag_edit && (api_flights.length == 0 && flag_flight == false && selectedEvent.ciavue != '' && selectedEvent.nrovue != '')">No se encontró información de vuelo con los datos ingresados.</div>

                                                            <table class="table table-xs table-striped table-hover" v-if="api_flights.length > 1">
                                                                <thead>
                                                                <tr>
                                                                    <th>DESDE</th>
                                                                    <th>HASTA</th>
                                                                    <th>ACCIONES</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr v-for="(element, af) in api_flights">
                                                                    <td>{{ element.departure.airport }} ({{ element.departure.estimated | formHour }})</td>
                                                                    <td>{{ element.arrival.airport }} ({{ element.arrival.estimated | formHour }})</td>
                                                                    <td><button class="btn btn-info btn-xs" v-on:click="setApiFlight(af)">Seleccionar tramo</button></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="icon-calendar d-block" style="margin-top: 0;"></span>
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0">Fecha inicio: <strong>{{ selectedEvent.fecin | formDate }}</strong> <template v-if="selectedEvent.fecout != '' && selectedEvent.fecout != null"> - Fecha fin: <strong>{{ selectedEvent.fecout | formDate }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Fecha inicio: <date-picker @dp-change="searchInfoFlight()" class="datepicker p-2 m-1" v-bind:disabled="readonly" v-model="selectedEvent.fecin" :config="options"></date-picker> - Fecha fin: <date-picker class="datepicker p-2 m-1" v-model="selectedEvent.fecout" v-bind:disabled="readonly" :config="options"></date-picker></p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <i class="icon-map-pin"></i>
                                                        </td>
                                                        <td style="vertical-align:middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0"><strong>{{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}</strong> <template v-if="selectedEvent.ciuout != selectedEvent.ciuin"> <i class="fa fa-arrow-right"></i> <strong>{{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')">
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuin != '' && typeof selectedEvent.ciuin == 'string'">CIUDAD INICIAL: {{ selectedEvent.ciuin }}</span -->
                                                                    <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                        <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                  :options="destinations_from"
                                                                                  label="codciu" :filterable="false"
                                                                                  @search="searchDestinationsFrom"
                                                                                  @input="setDestinationsFrom"
                                                                                  placeholder="Ciudad de origen"
                                                                                  v-model="selectedEvent.ciuin"
                                                                                  name="ciuin"
                                                                                  autocomplete="off">
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
                                                                        <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') || readonly)">
                                                                            <button type="button" v-on:click="reset('ciuin')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                        </div>
                                                                    </template>
                                                                    <template v-else>
                                                                        {{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}
                                                                    </template>
                                                                </div>
                                                                <i class="fa fa-arrow-right"></i>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')">
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuout != '' && typeof selectedEvent.ciuout == 'string'">CIUDAD FINAL: {{ selectedEvent.ciuout }}</span -->
                                                                    <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                        <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                  :options="destinations_to"
                                                                                  label="codciu" :filterable="false"
                                                                                  @search="searchDestinationsTo"
                                                                                  @input="setDestinationsTo"
                                                                                  placeholder="Ciudad de destino"
                                                                                  v-model="selectedEvent.ciuout"
                                                                                  name="ciuout"
                                                                                  autocomplete="off"
                                                                                  style="width: 250px; display:inline-block;">
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
                                                                        <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '')  || readonly)">
                                                                            <button type="button" v-on:click="reset('ciuout')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template v-else>
                                                                {{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="icon-clock d-block" style="margin-top: 0px;"></span>
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0"><template v-if="selectedEvent.horin != '' && selectedEvent.horin != null">Hora inicio: <strong>{{ selectedEvent.horin }}</strong></template> <template v-if="selectedEvent.horout != '' && selectedEvent.horout != null"> - Hora fin: <strong>{{ selectedEvent.horout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Hora inicio: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TIN')" v-model="selectedEvent.horin" /> - Hora fin: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TOT')" v-model="selectedEvent.horout" /></p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="flag_edit && (selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') && (type == 'VUELO' || selectedEvent.catser == 'VUELO')">
                                                        <td class="text-center">
                                                            <span class="icon-navigation"></span>
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="type_service" v-model="type_service" id="origin_national" value="0" />
                                                                <label class="form-check-label" for="origin_national">Nacional</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="type_service" v-model="type_service" id="origin_international" value="1" />
                                                                <label class="form-check-label" for="origin_international">Internacional</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center" style="vertical-align: top;">
                                                            <span class="icon-align-justify"></span>
                                                        </td>
                                                        <td>
                                                            <textarea style="resize:none; width:520px;" class="form-control border" v-bind:readonly="!flag_edit" v-model="selectedEvent.contentFull" rows="2">{{ selectedEvent.contentFull }}</textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <i class="icon-users"></i>
                                                        </td>
                                                        <td>
                                                            <template v-if="!flag_edit">
                                                                <template v-if="!loading_passengers">
                                                                    <a href="javascript:;" v-on:click="showPassengersService()" class="pb-0 m-0">
                                                                        {{ (passengers_service.length > 0) ? passengers_service.length : file.paxs }} Pasajero(s)
                                                                    </a>
                                                                </template>
                                                                <template v-else>
                                                                    <p class="pb-0 m-0">Cargando información de pasajeros..</p>
                                                                </template>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Información de pasajeros no disponible hasta guardar el servicio.</p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <template v-else-if="((selectedEvent.catser == 'TREN' || type == 'TREN') && flag_edit)">
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="icon-calendar d-block" style="margin-top: 0;"></span>
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0">Fecha inicio: <strong>{{ selectedEvent.fecin | formDate }}</strong> <template v-if="selectedEvent.fecout != '' && selectedEvent.fecout != null"> - Fecha fin: <strong>{{ selectedEvent.fecout | formDate }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Fecha inicio: <date-picker class="datepicker p-2 m-1" v-bind:disabled="readonly" v-model="selectedEvent.fecin" :config="options"></date-picker> - Fecha fin: <date-picker class="datepicker p-2 m-1" v-model="selectedEvent.fecout" v-bind:disabled="readonly" :config="options"></date-picker></p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="icon-clock d-block" style="margin-top: 0px;"></span>
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0"><template v-if="selectedEvent.horin != '' && selectedEvent.horin != null">Hora inicio: <strong>{{ selectedEvent.horin }}</strong></template> <template v-if="selectedEvent.horout != '' && selectedEvent.horout != null"> - Hora fin: <strong>{{ selectedEvent.horout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Hora inicio: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TIN' || readonly_hours)" v-model="selectedEvent.horin" /> - Hora fin: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TOT' || readonly_hours)" v-model="selectedEvent.horout" /></p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <i class="icon-map-pin"></i>
                                                        </td>
                                                        <td style="vertical-align:middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0"><strong>{{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}</strong> <template v-if="selectedEvent.ciuout != selectedEvent.ciuin"> <i class="fa fa-arrow-right"></i> <strong>{{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')">
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuin != '' && typeof selectedEvent.ciuin == 'string'">CIUDAD INICIAL: {{ selectedEvent.ciuin }}</span -->
                                                                    <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                        <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                  :options="destinations_from"
                                                                                  label="codciu" :filterable="false"
                                                                                  @search="searchDestinationsFrom"
                                                                                  @input="setDestinationsFrom"
                                                                                  placeholder="Ciudad de origen"
                                                                                  v-model="selectedEvent.ciuin"
                                                                                  name="ciuin"
                                                                                  autocomplete="off">
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
                                                                        <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') || readonly)">
                                                                            <button type="button" v-on:click="reset('ciuin')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                        </div>
                                                                    </template>
                                                                    <template v-else>
                                                                        {{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}
                                                                    </template>
                                                                </div>
                                                                <i class="fa fa-arrow-right"></i>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')">
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuout != '' && typeof selectedEvent.ciuout == 'string'">CIUDAD FINAL: {{ selectedEvent.ciuout }}</span -->
                                                                    <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                        <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                  :options="destinations_to"
                                                                                  label="codciu" :filterable="false"
                                                                                  @search="searchDestinationsTo"
                                                                                  @input="setDestinationsTo"
                                                                                  placeholder="Ciudad de destino"
                                                                                  v-model="selectedEvent.ciuout"
                                                                                  name="ciuout"
                                                                                  autocomplete="off"
                                                                                  style="width: 250px; display:inline-block;">
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
                                                                        <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '')  || readonly)">
                                                                            <button type="button" v-on:click="reset('ciuout')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                        </div>
                                                                    </template>
                                                                    <template v-else>
                                                                        {{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center" colspan="2" scope="row">
                                                            <template v-if="type == 'TREN' || selectedEvent.catser == 'TREN'">
                                                                <div class="modal--cotizacion__header d-flex">
                                                                    <template v-if="!flag_edit">
                                                                        <h3 class="modal-title pb-3" style="border-bottom:2px dashed #eee;"><b><i v-bind:class="[selectedEvent.icon, 'pr-2']" v-if="selectedEvent.icon != ''"></i>  {{ selectedEvent.codsvs }} | {{ selectedEvent.title }}</b></h3>
                                                                    </template>
                                                                    <template v-else>
                                                                        <template v-if="selectedEvent.nroite == undefined">
                                                                            <template v-if="!(type == 'VUELO' || selectedEvent.catser == 'VUELO')">
                                                                                <!-- div class="card _options_service" v-if="hover_service">
                                                                                    <div class="card-header">
                                                                                        <h5>Remarks y Restricciones</h5>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <div class="alert alert-warning" v-if="loading_remarks || loading_restrictions">Cargando remarks y restricciones...</div>
                                                                                        <template v-if="!(loading_remarks || loading_restrictions)">
                                                                                            <template v-if="content_remarks != '' || content_restrictions != ''">
                                                                                                <ul class="nav nav-tabs">
                                                                                                    <li class="nav-item" v-if="content_remarks != ''">
                                                                                                        <a v-bind:class="['nav-link', (flag_view == 'remarks') ? 'active' : '']" v-on:click="toggleFlag('remarks')" href="#">Remarks</a>
                                                                                                    </li>
                                                                                                    <li class="nav-item" v-if="content_restrictions != ''">
                                                                                                        <a v-bind:class="['nav-link', (flag_view == 'restrictions' || content_remarks == '') ? 'active' : '']" v-on:click="toggleFlag('restrictions')" href="#">Restricciones</a>
                                                                                                    </li>
                                                                                                </ul>
                                                                                                <div class="tab-content">
                                                                                                    <div v-bind:class="['tab-pane', 'show', 'active']">
                                                                                                        <template v-if="flag_view == 'remarks' && content_remarks != ''">
                                                                                                            <textarea v-model="content_remarks" readonly="readonly" class="form-control" rows="15"></textarea>
                                                                                                        </template>
                                                                                                        <template v-if="(flag_view == 'restrictions' || content_remarks == '') && content_restrictions != ''">
                                                                                                            <textarea v-model="content_restrictions" readonly="readonly" class="form-control" rows="15"></textarea>
                                                                                                        </template>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </template>
                                                                                            <template v-else>
                                                                                                <div class="alert alert-info">No se encontró información para remarks o restricciones para el servicio seleccionado.</div>
                                                                                            </template>
                                                                                        </template>
                                                                                    </div>
                                                                                </div -->
                                                                                <template v-if="(selectedEvent.service == undefined || selectedEvent.service == '')">
                                                                                    <input type="text" class="form-control" placeholder="Filtrar servicios.." v-model="query" v-on:keyup="searchServices(query)" />
                                                                                </template>
                                                                                <template v-else>
                                                                                    <input type="text" class="form-control" v-bind:value="selectedEvent.service.codigo + ' - ' + selectedEvent.service.descri" disabled="disabled" />
                                                                                </template>
                                                                                <!-- v-select class="p2 border"
                                                                                          style="width: 100%; margin-right: 2rem; height: 35px"
                                                                                          :options="all_services"
                                                                                          label="codigo" :filterable="false"
                                                                                          @search="searchServices"
                                                                                          @input="setService"
                                                                                          placeholder="Servicio"
                                                                                          v-model="selectedEvent.service">
                                                                                    <template slot="option" slot-scope="option">
                                                                                        <div class="d-center" @mouseenter="showDetailService(option.codigo, true)">
                                                                                            {{ option.codigo }} - {{ option.descri }}
                                                                                        </div>
                                                                                    </template>
                                                                                    <template slot="selected-option" slot-scope="option">
                                                                                        <div class="selected d-center">
                                                                                            {{ option.codigo }} - {{ option.descri }}
                                                                                        </div>
                                                                                    </template>
                                                                                </v-select -->
                                                                                <button v-if="!readonly" type="button" v-on:click="reset('service')" class="btn link-clear">LIMPIAR FILTRO</button>
                                                                            </template>
                                                                        </template>
                                                                        <template v-else>
                                                                            <input type="text" class="form-control" placeholder="Título del servicio" v-model="selectedEvent.title" />
                                                                        </template>
                                                                    </template>
                                                                </div>

                                                                <template v-if="loading_services">
                                                                    <div class="alert alert-warning">Cargando servicios.. Por favor, espere..</div>
                                                                </template>

                                                                <template v-if="all_services.length > 0 && !loading_services && selectedEvent.nroite == undefined && (selectedEvent.service == undefined || selectedEvent.service == '') && type != 'VUELO'">
                                                                    <div class="d-block mt-3">
                                                                        <table class="table table-stripped table-hover">
                                                                            <thead>
                                                                            <tr>
                                                                                <td>CÓDIGO</td>
                                                                                <td>DESCRIPCIÓN</td>
                                                                                <td v-if="type == 'TREN'">HORIN</td>
                                                                                <td v-if="type == 'TREN'">HOROUT</td>
                                                                                <td><i class="fa fa-cogs"></i></td>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            <tr v-for="(_all_service, _as) in all_services">
                                                                                <td>
                                                                                    {{ _all_service.codigo }}
                                                                                </td>
                                                                                <td>
                                                                                    {{ _all_service.descri }}
                                                                                </td>
                                                                                <td v-if="type == 'TREN'">
                                                                                    {{ _all_service.horin }}
                                                                                </td>
                                                                                <td v-if="type == 'TREN'">
                                                                                    {{ _all_service.horout }}
                                                                                </td>
                                                                                <td>
                                                                                    <button type="button mb-1" v-on:click="showDetailService(_all_service.codigo, true)" title="Ver remarks y restricciones" class="btn btn-info btn-sm"><i class="fa fa-clipboard" style="font-size:1.5em!important;"></i></button>
                                                                                    <button type="button" v-on:click="setService(_all_service)" title="Seleccionar" class="btn btn-info btn-sm"><i class="fa fa-hand-point-left" style="font-size:1.5em!important;"></i></button>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>

                                                                    <div class="modal modal--cotizacion" id="modalAlertaRemarks" role="dialog" style="overflow: scroll;">
                                                                        <div class="modal-dialog modal-sm modal--cotizacion__document" role="document">
                                                                            <div class="modal-content modal--cotizacion__content">
                                                                                <div class="modal-header">
                                                                                    <button class="close" type="button" v-on:click="closeModal('modalAlertaRemarks')" aria-label="Close"><span>&times;</span></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="modal--cotizacion__header">
                                                                                        <h3 class="modal-title"><b>Políticas y Restricciones</b></h3>
                                                                                    </div>
                                                                                    <div class="modal--cotizacion__body">
                                                                                        <div class="d-block">
                                                                                            <div class="alert alert-warning" v-if="loading_remarks || loading_restrictions">Cargando remarks y restricciones...</div>
                                                                                            <template v-if="!(loading_remarks || loading_restrictions)">
                                                                                                <template v-if="content_remarks != '' || content_restrictions != ''">
                                                                                                    <ul class="nav nav-tabs">
                                                                                                        <li class="nav-item" v-if="content_remarks != ''">
                                                                                                            <a v-bind:class="['nav-link', (flag_view == 'remarks') ? 'active' : '']" v-on:click="toggleFlag('remarks')" href="#">Remarks</a>
                                                                                                        </li>
                                                                                                        <li class="nav-item" v-if="content_restrictions != ''">
                                                                                                            <a v-bind:class="['nav-link', (flag_view == 'restrictions' || content_remarks == '') ? 'active' : '']" v-on:click="toggleFlag('restrictions')" href="#">Restricciones</a>
                                                                                                        </li>
                                                                                                    </ul>
                                                                                                    <div class="tab-content">
                                                                                                        <div v-bind:class="['tab-pane', 'show', 'active']">
                                                                                                            <template v-if="flag_view == 'remarks' && content_remarks != ''">
                                                                                                                <textarea v-model="content_remarks" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                                                            </template>
                                                                                                            <template v-if="(flag_view == 'restrictions' || content_remarks == '') && content_restrictions != ''">
                                                                                                                <textarea v-model="content_restrictions" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                                                            </template>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </template>
                                                                                                <template v-else>
                                                                                                    <div class="alert alert-info">No se encontró información para remarks o restricciones para el servicio seleccionado.</div>
                                                                                                </template>
                                                                                            </template>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>

                                                                <div class="modal--cotizacion__header">
                                                                    <template v-if="flag_bastar != ''">
                                                                        <template v-if="all_bastars.length > 1">
                                                                            <template v-if="flag_edit">
                                                                                <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.bastar != '' && typeof selectedEvent.bastar == 'string'">BASE DE TARIFAS INICIAL: {{ selectedEvent.bastar }}</span -->

                                                                                <v-select class="form-control p-2 border"
                                                                                          style="width: 100%; margin-right: 2rem;"
                                                                                          :options="all_bastars"
                                                                                          label="codigo" :filterable="false"
                                                                                          placeholder="Tarifa"
                                                                                          v-model="selectedEvent.bastar">
                                                                                    <template slot="option" slot-scope="option">
                                                                                        <div class="d-center">
                                                                                            {{ option.bastar }} - {{ option.desbas }}
                                                                                        </div>
                                                                                    </template>
                                                                                    <template slot="selected-option" slot-scope="option">
                                                                                        <div class="selected d-center">
                                                                                            {{ option.bastar }} - {{ option.desbas }}
                                                                                        </div>
                                                                                    </template>
                                                                                </v-select>
                                                                                <!-- button type="button" v-on:click="reset('bastar')" class="btn border btn-light">LIMPIAR FILTRO</button -->
                                                                            </template>
                                                                            <template v-else>
                                                                                <template v-if="!loading">
                                                                                    <input type="text" disabled="disabled" class="p-2 border col-lg-1" placeholder="Base de Tarifas" v-bind:value="(typeof selectedEvent.bastar == 'object') ? selectedEvent.bastar.bastar : selectedEvent.bastar" />
                                                                                    <input type="text" disabled="disabled" class="p-2 border col-lg-9" placeholder="Base de Tarifas" v-bind:value="selectedEvent.desbas" />
                                                                                </template>
                                                                                <template v-else>
                                                                                    Cargando información de tarifas...
                                                                                </template>
                                                                            </template>
                                                                        </template>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center" style="vertical-align: top;">
                                                            <span class="icon-align-justify"></span>
                                                        </td>
                                                        <td>
                                                            <textarea style="resize:none; width:520px;" class="form-control border" v-bind:readonly="!flag_edit" v-model="selectedEvent.contentFull" rows="2">{{ selectedEvent.contentFull }}</textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <i class="icon-users"></i>
                                                        </td>
                                                        <td>
                                                            <template v-if="!flag_edit">
                                                                <template v-if="!loading_passengers">
                                                                    <a href="javascript:;" v-on:click="showPassengersService()" class="pb-0 m-0">
                                                                        {{ (passengers_service.length > 0) ? passengers_service.length : file.paxs }} Pasajero(s)
                                                                    </a>
                                                                </template>
                                                                <template v-else>
                                                                    <p class="pb-0 m-0">Cargando información de pasajeros..</p>
                                                                </template>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Información de pasajeros no disponible hasta guardar el servicio.</p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <template v-else>
                                                    <template v-if="(type == 'HOT' || selectedEvent.catser == 'HOT') && selectedEvent.nroite != undefined && !flag_edit">
                                                        <tr>
                                                            <td class="text-center" style="vertical-align: top;">
                                                                <span class="fa fa-tag"></span>
                                                            </td>
                                                            <td>
                                                                <p>{{ selectedEvent.categoria_hotel }}</p>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr>
                                                        <td class="text-center">
                                                            <i class="icon-map-pin"></i>
                                                        </td>
                                                        <td style="vertical-align:middle;">
                                                            <template v-if="!flag_edit">
                                                                <p class="pb-0 m-0"><strong>{{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}</strong> <template v-if="selectedEvent.ciuout != selectedEvent.ciuin"> <i class="fa fa-arrow-right"></i> <strong>{{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')">
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuin != '' && typeof selectedEvent.ciuin == 'string'">CIUDAD INICIAL: {{ selectedEvent.ciuin }}</span -->
                                                                    <template v-if="type == 'HOT'">
                                                                        <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                            <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                      :options="destinations_from"
                                                                                      label="code" :filterable="(type == 'HOT') ? true : false"
                                                                                      @search="searchDestinationsFrom"
                                                                                      @input="setDestinationsFrom"
                                                                                      placeholder="Ciudad de origen"
                                                                                      v-model="selectedEvent.ciuin"
                                                                                      name="ciuin"
                                                                                      autocomplete="off">
                                                                                <template slot="option" slot-scope="option">
                                                                                    <div class="d-center">
                                                                                        {{ option.label }}
                                                                                    </div>
                                                                                </template>
                                                                                <template slot="selected-option" slot-scope="option">
                                                                                    <div class="selected d-center">
                                                                                        {{ option.label }}
                                                                                    </div>
                                                                                </template>
                                                                            </v-select>
                                                                            <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') || readonly)">
                                                                                <button type="button" v-on:click="reset('ciuin')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                            </div>
                                                                        </template>
                                                                        <template v-else>
                                                                            {{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}
                                                                        </template>
                                                                    </template>
                                                                    <template v-else>
                                                                        <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                            <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                      :options="destinations_from"
                                                                                      label="codciu" :filterable="false"
                                                                                      @search="searchDestinationsFrom"
                                                                                      @input="setDestinationsFrom"
                                                                                      placeholder="Ciudad de origen"
                                                                                      v-model="selectedEvent.ciuin"
                                                                                      name="ciuin"
                                                                                      autocomplete="off">
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
                                                                            <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '') || readonly)">
                                                                                <button type="button" v-on:click="reset('ciuin')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                            </div>
                                                                        </template>
                                                                        <template v-else>
                                                                            {{ (typeof selectedEvent.ciuin == 'object' && selectedEvent.ciuin != null) ? selectedEvent.ciuin.codciu : selectedEvent.ciuin }}
                                                                        </template>
                                                                    </template>
                                                                </div>
                                                                <i class="fa fa-arrow-right" v-if="type != 'HOT'"></i>
                                                                <div v-bind:style="'display:inline-block;' + ((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT') ? 'width:250px;' : '')"
                                                                     v-if="type != 'HOT'"
                                                                >
                                                                    <!-- span class="badge badge-secondary" style="width:100%; font-size:11px;" v-if="selectedEvent.ciuout != '' && typeof selectedEvent.ciuout == 'string'">CIUDAD FINAL: {{ selectedEvent.ciuout }}</span -->
                                                                    <template v-if="selectedEvent.codsvs == undefined || selectedEvent.codsvs == '' || selectedEvent.codsvs == 'AEIFLT' || selectedEvent.codsvs == 'AECFLT'">
                                                                        <v-select v-bind:class="['p-2', 'border', ((selectedEvent.service == undefined || selectedEvent.service == '') && !readonly) ? '' : 'disabled']"
                                                                                  :options="destinations_to"
                                                                                  label="codciu" :filterable="false"
                                                                                  @search="searchDestinationsTo"
                                                                                  @input="setDestinationsTo"
                                                                                  placeholder="Ciudad de destino"
                                                                                  v-model="selectedEvent.ciuout"
                                                                                  name="ciuout"
                                                                                  autocomplete="off"
                                                                                  style="width: 250px; display:inline-block;">
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
                                                                        <div class="form p-2 m-0" style="border-radius:0!important;" v-if="!((selectedEvent.codsvs == undefined || selectedEvent.codsvs == '')  || readonly)">
                                                                            <button type="button" v-on:click="reset('ciuout')" class="btn border btn-light">LIMPIAR FILTRO</button>
                                                                        </div>
                                                                    </template>
                                                                    <template v-else>
                                                                        {{ (typeof selectedEvent.ciuout == 'object' && selectedEvent.ciuout != null) ? selectedEvent.ciuout.codciu : selectedEvent.ciuout }}
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            <span class="icon-calendar d-block" style="margin-top: 0;"></span>
                                                            <span class="icon-clock d-block" v-if="!(type == 'HOT' || selectedEvent.catser == 'HOT')" style="margin-top: 22px;"></span>
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            <template v-if="!flag_edit">
                                                                <p v-bind:class="[(type == 'HOT' || selectedEvent.catser == 'HOT') ? '' : 'pb-4', 'm-0']">Fecha inicio: <strong>{{ selectedEvent.fecin | formDate }}</strong> <template v-if="selectedEvent.fecout != '' && selectedEvent.fecout != null"> - Fecha fin: <strong>{{ selectedEvent.fecout | formDate }}</strong></template></p>
                                                                <p class="pb-0 m-0" v-if="!(type == 'HOT' || selectedEvent.catser == 'HOT')"><template v-if="selectedEvent.horin != '' && selectedEvent.horin != null">Hora inicio: <strong>{{ selectedEvent.horin }}</strong></template> <template v-if="selectedEvent.horout != '' && selectedEvent.horout != null"> - Hora fin: <strong>{{ selectedEvent.horout }}</strong></template></p>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Fecha inicio: <date-picker @dp-change="showUrlHotel()" class="datepicker p-2 m-1" v-bind:disabled="readonly" v-model="selectedEvent.fecin" :config="options"></date-picker> - Fecha fin: <date-picker class="datepicker p-2 m-1" @dp-change="showUrlHotel()" v-model="selectedEvent.fecout" v-bind:disabled="readonly" :config="options"></date-picker></p>
                                                                <p class="pb-0 m-0" v-if="!(type == 'HOT' || selectedEvent.catser == 'HOT')">Hora inicio: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TIN')" v-model="selectedEvent.horin" /> - Hora fin: <input type="time" class="p-2 m-1 border" v-bind:disabled="(selectedEvent.tipsvs == 'TOT')" v-model="selectedEvent.horout" /></p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="type == 'HOT' && selectedEvent.nroite == undefined">
                                                        <td class="text-center">
                                                            <i class="icon-filter"></i>
                                                        </td>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <select class="form-control" v-on:change="showUrlHotel()"
                                                                            v-model="selectedEvent.typeclass_id">
                                                                        <option value="all">Todas las categorías</option>
                                                                        <option :value="hotel.class_id"
                                                                                v-for="hotel in classes_hotel">
                                                                            {{ hotel.class_name }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <input type="text" class="form-control"
                                                                           v-on:change="showUrlHotel()"
                                                                           v-model="selectedEvent.hotels_search_code"
                                                                           placeholder="Filtrar por nombre, código, etc..">
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!(type == 'HOT' || selectedEvent.catser == 'HOT')">
                                                        <td class="text-center" style="vertical-align: top;">
                                                            <span class="icon-align-justify"></span>
                                                        </td>
                                                        <td>
                                                            <textarea style="resize:none; width:520px;" class="form-control border" v-bind:readonly="!flag_edit" v-model="selectedEvent.contentFull" rows="2">{{ selectedEvent.contentFull }}</textarea>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!(type == 'HOT' || selectedEvent.catser == 'HOT')">
                                                        <td class="text-center">
                                                            <i class="icon-users"></i>
                                                        </td>
                                                        <td>
                                                            <template v-if="!flag_edit">
                                                                <template v-if="!loading_passengers">
                                                                    <a href="javascript:;" v-on:click="showPassengersService()" class="pb-0 m-0">
                                                                        {{ (passengers_service.length > 0) ? passengers_service.length : file.paxs }} Pasajero(s)
                                                                    </a>
                                                                </template>
                                                                <template v-else>
                                                                    <p class="pb-0 m-0">Cargando información de pasajeros..</p>
                                                                </template>
                                                            </template>
                                                            <template v-else>
                                                                <p class="pb-0 m-0">Información de pasajeros no disponible hasta guardar el servicio.</p>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                    <template v-if="!(selectedEvent.catser == 'VUELO' || type == 'VUELO' || selectedEvent.catser == 'HOT' || type == 'HOT')">
                                                        <tr v-if="!(selectedEvent.tipsvs == 'EXC' || type == 'TOUR' || type == 'RES' || selectedEvent.tipsvs == 'RES' || type == 'ENT' || selectedEvent.tipsvs == 'ENT')">
                                                            <td class="text-center">
                                                                <i class="icon-flight"></i>
                                                            </td>
                                                            <td>
                                                                <template v-if="!flag_edit">
                                                                    <p v-if="selectedEvent.ciavue != '' && selectedEvent.nrovue" class="pb-0 m-0"><strong>{{ (typeof selectedEvent.ciavue == 'object') ? selectedEvent.ciavue.codigo : selectedEvent.ciavue }}</strong> - <strong>{{ selectedEvent.nrovue }}</strong></p>
                                                                    <p v-else class="pb-0 m-0">Información de vuelo no disponible.</p>
                                                                </template>
                                                                <template v-else>
                                                                    <div style="width:250px; display:inline-block;">
                                                                        <v-select v-bind:class="['p-2', 'border', (!readonly) ? '' : 'disabled']"
                                                                                  :options="airlines"
                                                                                  value="codigo"
                                                                                  label="codigo" :filterable="false"
                                                                                  @input="searchInfoFlight()"
                                                                                  @search="searchAirlines"
                                                                                  placeholder="Línea Aérea"
                                                                                  v-model="selectedEvent.ciavue"
                                                                                  style="padding-top: 0.2rem!important; width: 250px!important;">
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
                                                                    </div>

                                                                    <div style="width:250px; display:inline-block;">
                                                                        <input v-model="selectedEvent.nrovue" v-on:change="searchInfoFlight()" v-bind:disabled="readonly" class="p-2 m-1 border" placeholder="Número de vuelo" />
                                                                    </div>
                                                                </template>

                                                                <table class="table table-xs table-striped table-hover" v-if="api_flights.length > 1">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>DESDE</th>
                                                                        <th>HASTA</th>
                                                                        <th>ACCIONES</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr v-for="(element, af) in api_flights">
                                                                        <td>{{ element.departure.airport }} ({{ element.departure.estimated | formHour }})</td>
                                                                        <td>{{ element.arrival.airport }} ({{ element.arrival.estimated | formHour }})</td>
                                                                        <td><button class="btn btn-info btn-xs" v-on:click="setApiFlight(af)">Seleccionar tramo</button></td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </template>
                                            </table>

                                            <div class="d-block" v-if="!flag_edit && selectedEvent.nroite != undefined && (type == 'HOT' || selectedEvent.catser == 'HOT') && selectedEvent.variations.length > 0">
                                                <h4 class="mr-0 ml-0 mb-2 mt-3">Variaciones</h4>

                                                <table class="table table-xs table-striped table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th style="text-align:center;">
                                                            <input style="width:auto!important;"
                                                                   v-model="all_variations"
                                                                   v-on:change="allVariations()"
                                                                   type="checkbox" />
                                                        </th>
                                                        <th>TIPO</th>
                                                        <th>CAN. HAB.</th>
                                                        <th>CAN. PAXS</th>
                                                        <th>STATUS</th>
                                                        <th>ANULAR</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(variation, v) in selectedEvent.variations">
                                                        <td style="text-align:center;">
                                                            <input style="width:auto!important;" type="checkbox" v-model="variations[variation.nroite]" />
                                                        </td>
                                                        <td>{{ variation.desbas_inicial }}</td>
                                                        <td>{{ variation.cantid }}</td>
                                                        <td>{{ variation.canpax }}</td>
                                                        <td>{{ (variation.anulado == 0) ? ((variation.estado_hotel.trim() == 'OK') ? 'CONFIRMED' : variation.estado_hotel) : 'CXL CON PENALIDAD' }}</td>
                                                        <td>
                                                            <button v-bind:disabled="loading_button"
                                                                    type="button"
                                                                    v-if="selectedEvent.anulado == 0"
                                                                    v-on:click="preCancelAllRooms(variation)"
                                                                    class="col-lg-12 btn btn-white mt-2 mb-2">
                                                                <i class="fa fa-times-circle fa-2x col-lg-12"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>

                                                <div id="modalAlerta" v-if="Object.entries(variations).length > 0">
                                                    <div class="group-btn">
                                                        <button type="button" @click="detailVariations()"
                                                                class="btn btn-primary">
                                                            Visualizar detalle de habitaciones
                                                        </button>
                                                        <button type="button" @click="assignPaxs()"
                                                                class="btn btn-primary">
                                                            Asignar Pasajeros
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="modalAlerta" v-if="selectedEvent.nroite == undefined">
                                                <div class="group-btn">
                                                    <template v-if="type == 'HOT' || selectedEvent.catser == 'HOT'">
                                                        <a v-bind:href="url_hotel" v-bind:disabled="url_hotel == ''" target="_blank"
                                                           v-bind:class="['btn', 'btn-primary', (url_hotel == '') ? 'disabled' : '']">
                                                            Filtrar Hoteles
                                                        </a>
                                                    </template>
                                                    <template v-else>
                                                        <button type="button" @click="saveEvent()"
                                                                class="btn btn-primary">
                                                            <template v-if="selectedEvent.nroite > 0">Guardar</template>
                                                            <template v-else>Agregar</template>
                                                            <template v-if="readonly && type == 'TRAS'">Traslado</template>
                                                            <template v-else>Servicio</template>
                                                        </button>
                                                    </template>
                                                    <button type="button" @click="deleteEvent()"
                                                            class="btn btn-secondary">Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block" v-if="view == 'viewRooms'">
                                            <div v-for="(variation, v) in selectedEvent.variations"
                                                 v-if="variations[variation.nroite]">
                                                <h4 style="margin:0; padding:0; margin-top:2rem;">{{ variation.desbas_inicial }}</h4>

                                                <table class="table table-xs table-striped table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>TIPO</th>
                                                        <th>CAN. HAB.</th>
                                                        <th>CAN. PAXS</th>
                                                        <th>STATUS</th>
                                                        <th>COD. RSV.</th>
                                                        <th>PAXS</th>
                                                        <th>CANCELAR</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(room, r) in variation.rooms">
                                                        <td>{{ room.nomhab }}</td>
                                                        <td>{{ room.cantid }}</td>
                                                        <td>{{ room.canpax }}</td>
                                                        <td>{{ (room.tipfil.trim() == 'GASCAN') ? 'CXL CON PENALIDAD' : ((room.estrsv.trim() == 'OK') ? 'CONFIRMED' : room.estrsv) }}</td>
                                                        <td>{{ room.codrsv }}</td>
                                                        <td>
                                                            <button v-bind:disabled="loading_button"
                                                                    type="button"
                                                                    v-if="!flag_edit && (selectedEvent.catser == 'HOT' && !(room.tipfil.trim() == 'GASCAN'))"
                                                                    v-on:click="showRoom(variation.nroite, (r + 1))"
                                                                    class="col-lg-12 btn btn-white mt-2 mb-2">
                                                                <i class="fa fa-users fa-2x col-lg-12"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <button v-bind:disabled="loading_button"
                                                                    type="button"
                                                                    v-if="!flag_edit && selectedEvent.anulado == 0 && (selectedEvent.catser == 'HOT' && !(room.tipfil.trim() == 'GASCAN')) && (room.tipcha == 2 || room.tipcha == 3)"
                                                                    v-on:click="preCancelRoom(room)"
                                                                    class="col-lg-12 btn btn-white mt-2 mb-2">
                                                                <i class="fa fa-times-circle fa-2x col-lg-12"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div id="modalAlerta">
                                                <div class="group-btn">
                                                    <button type="button" v-on:click="hideRooms()"
                                                            class="btn btn-secondary">Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block" v-if="view == 'assignPaxs'">
                                            <!-- h4 class="mr-0 ml-0 mb-2 mt-0">Lista de Pasajeros asignados a las habitaciones</h4 -->

                                            <div class="row" style="align-items: flex-start;">
                                                <div class="col-lg-3">
                                                    <h4 class="mr-0 ml-0 mb-3 mt-0" style="text-align:center;">PASAJEROS</h4>
                                                    <draggable
                                                        class="list-group"
                                                        style="min-height:30px;background-color:#f6f6f6;margin-bottom:2rem;margin-top:1rem;"
                                                        :list="passengers_event[selectedEvent.nroite]"
                                                        group="people"
                                                        ghost-class="ghost"
                                                    >
                                                        <div class="list-group-item"
                                                             v-for="(passenger, p) in passengers_event[selectedEvent.nroite]">
                                                            {{ passenger.nombre }} <b>({{ passenger.tipo }})</b>
                                                        </div>
                                                    </draggable>
                                                </div>
                                                <div class="col-lg-9">
                                                    <h4 class="mr-0 ml-0 mb-3 mt-0" style="text-align:center;">HABITACIONES</h4>
                                                    <div class="row" style="align-items: flex-start;">
                                                        <div class="col-lg-4" v-if="i > 0" v-for="i in 3">
                                                            <div class="col-lg-12"
                                                                 v-for="(variation, v) in selectedEvent.variations"
                                                                 v-if="type_room[variation.codsvs][variation.nroite] == i && variations[variation.nroite]">
                                                                <h5><b>{{ variation.desbas_inicial }}</b></h5>

                                                                <div v-for="(room, r) in variation.rooms"
                                                                     v-if="(check_rooms.length == 0 || check_rooms[(r + 1)]) && !(room.tipfil.trim() == 'GASCAN')">
                                                                    <h6>CANPAX: <b>{{ room.canpax }}</b> / CODRSV: <b>{{ room.codrsv }}</b></h6>
                                                                    <draggable
                                                                        class="list-group"
                                                                        style="min-height:30px;background-color:#f6f6f6;margin-bottom:2rem;margin-top:1rem;"
                                                                        :list="rooms[room.nroite][(r + 1)]"
                                                                        group="people"
                                                                        @change="change($event)"
                                                                        ghost-class="ghost">
                                                                        <div class="list-group-item"
                                                                             v-for="(element, index) in rooms[room.nroite][(r + 1)]">
                                                                            {{ element.nombre }}
                                                                        </div>
                                                                    </draggable>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="modalAlerta">
                                                <div class="group-btn">
                                                    <button type="button" v-bind:disabled="loading_button" v-on:click="saveRooms()"
                                                            class="btn btn-primary">Guardar acomodo
                                                    </button>
                                                    <button type="button" v-bind:disabled="loading_button" v-if="selectedEvent.flag_acomodo == 1" v-on:click="showModal('modalAlertaPaxsSave')"
                                                            class="btn btn-secondary">Limpiar acomodo
                                                    </button>
                                                    <button type="button" v-bind:disabled="loading_button" v-on:click="hideRooms()"
                                                            class="btn btn-secondary">Regresar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block" v-if="view == 'passengers'">
                                            <h4 class="mr-0 ml-0 mb-2 mt-0">Lista de Pasajeros asignados al servicio</h4>

                                            <template v-if="!loading_passengers">
                                                <table class="table table-xs table-striped table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th><input type="checkbox" v-bind:disabled="readonly" v-model="all" v-on:change="togglePassengers()" /></th>
                                                        <th>NOMBRE DEL PAX</th>
                                                        <th>TIPO</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr v-for="(passenger, p) in all_passengers">
                                                        <td><input type="checkbox" v-bind:value="1" v-bind:disabled="readonly" v-on:change="updatePassengersList()"
                                                                   v-model="check_passengers_service[p]" /></td>
                                                        <td>{{ passenger.nombre }}</td>
                                                        <td>{{ passenger.tipo }}</td>
                                                    </tr>
                                                    </tbody>
                                                </table>

                                                <div id="modalAlerta" v-if="!readonly">
                                                    <div class="group-btn">
                                                        <button type="button" @click="savePassengersService()"
                                                                class="btn btn-primary">Guardar
                                                        </button>
                                                        <button type="button" v-on:click="view = 'detail'"
                                                                class="btn btn-secondary">Regresar
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="alert alert-warning mt-2">Cargando información de pasajeros..</div>
                                            </template>
                                        </div>

                                        <div class="d-block" v-if="view == 'components'">
                                            <h4 class="mr-0 ml-0 mb-2 mt-0">Componentes</h4>

                                            <template v-if="!loading_button">
                                                <template v-if="(component_editable.nroite != undefined && component_editable.nroite >= 0)">
                                                    <div class="form">
                                                        <div class="row pb-0">
                                                            <div class="col-lg-12">
                                                                <label>SERVICIO</label>
                                                                <template v-if="component_editable.nroite == 0">
                                                                    <v-select class="form-control p-2 border"
                                                                              :options="all_services"
                                                                              label="codigo" :filterable="false"
                                                                              @input="searchRatesByService()"
                                                                              @search="searchServicesComponent"
                                                                              placeholder="Servicio"
                                                                              v-model="component_editable.service">
                                                                        <template slot="option" slot-scope="option">
                                                                            <div class="d-center">
                                                                                {{ option.codigo }} - {{ option.descri }}
                                                                            </div>
                                                                        </template>
                                                                        <template slot="selected-option" slot-scope="option">
                                                                            <div class="selected d-center">
                                                                                {{ option.codigo }} - {{ option.descri }}
                                                                            </div>
                                                                        </template>
                                                                    </v-select>
                                                                </template>
                                                                <template v-else>
                                                                    <span class="form-control p-2 border">{{ component_editable.codsvs }} - {{ component_editable.descri }}</span>
                                                                </template>
                                                            </div>
                                                        </div>

                                                        <div class="row pb-4">
                                                            <div class="col-lg-12">
                                                                <label>TARIFA</label> <span class="badge badge-secondary" style="font-size:11px;" v-if="component_editable.bastar != '' && typeof component_editable.bastar == 'string'">ACTUAL: {{ component_editable.bastar }}</span>
                                                                <v-select class="form-control p-2 border"
                                                                          :options="all_bastars"
                                                                          label="codigo" :filterable="false"
                                                                          @search="searchRatesByService"
                                                                          placeholder="Tarifa"
                                                                          v-model="component_editable.tarifa">
                                                                    <template slot="option" slot-scope="option">
                                                                        <div class="d-center">
                                                                            {{ option.bastar }} - {{ option.descri }}
                                                                        </div>
                                                                    </template>
                                                                    <template slot="selected-option" slot-scope="option">
                                                                        <div class="selected d-center">
                                                                            {{ option.bastar }} - {{ option.descri }}
                                                                        </div>
                                                                    </template>
                                                                </v-select>
                                                                <!-- input type="text" class="form-control p-2 border" v-model="component_editable.bastar" / -->
                                                            </div>
                                                        </div>

                                                        <div class="row pb-4">
                                                            <div class="col-lg-4">
                                                                <label>FECIN</label>
                                                                <date-picker class="datepicker form-control p-3 border" v-model="component_editable.fecin" :config="options"></date-picker>
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label>HORIN</label>
                                                                <input type="time" class="form-control border" v-model="component_editable.horin" />
                                                            </div>

                                                            <div class="col-lg-4">
                                                                <label>HOROUT</label>
                                                                <input type="time" class="form-control border" v-model="component_editable.horout" />
                                                            </div>
                                                        </div>

                                                        <div class="row pb-4">
                                                            <div class="col-lg-3">
                                                                <label>QTY</label>
                                                                <input type="number" class="form-control border" v-model="component_editable.cansvs" />
                                                            </div>

                                                            <div class="col-lg-3">
                                                                <label>PAXS</label>
                                                                <input type="number" class="form-control border" v-model="component_editable.canpax" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <table class="table table-xs table-striped table-hover">
                                                        <thead>
                                                        <tr>
                                                            <!-- th><input type="checkbox" v-model="all" v-on:change="togglePassengers()" /></th -->
                                                            <th colspan="2" scope="row">SERVICIO</th>
                                                            <th>TARIFA</th>
                                                            <th>FECIN</th>
                                                            <th>HORIN</th>
                                                            <th>HOROUT</th>
                                                            <th>QTY</th>
                                                            <th>PAXS</th>
                                                            <th>TIPO</th>
                                                            <th>GRUPO</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr v-for="(component, c) in components">
                                                            <!-- td><input type="checkbox" v-bind:value="1" v-on:change="updatePassengersList()" v-model="check_passengers_service[p]" /></td -->
                                                            <td><button href="javascript:;"
                                                                        v-on:click="editComponent(component)"
                                                            ><i class="fa fa-edit"></i></button> <button href="javascript:;"
                                                                                                         v-on:click="deleteComponent(component)"
                                                            ><i class="fa fa-trash"></i></button></td>
                                                            <td>{{ component.codsvs }} - <b>{{ component.descri }}</b></td>
                                                            <td>{{ component.bastar }} - <b>{{ component.desbas }}</b></td>
                                                            <td>{{ component.fecin | formDate }}</td>
                                                            <td>{{ component.horin }}</td>
                                                            <td>{{ component.horout }}</td>
                                                            <td>{{ component.cansvs }}</td>
                                                            <td>{{ component.canpax }}</td>
                                                            <td>{{ component.tippax }}</td>
                                                            <td>{{ component.grupo }}</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </template>

                                                <template v-if="!(Object.values(component_editable).length > 0 && component_editable.nroite != undefined)">
                                                    <button type="button" class="btn btn-primary" v-on:click="addComponent()">Agregar nuevo componente</button>

                                                    <div id="modalAlerta">
                                                        <div class="group-btn">
                                                            <!-- button type="button" @click="savePassengersService()"
                                                                    class="btn btn-primary">Guardar
                                                            </button -->
                                                            <button type="button" v-on:click="cancelComponent(); view = 'detail';"
                                                                    class="btn btn-secondary">Regresar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div id="modalAlerta">
                                                        <div class="group-btn">
                                                            <button type="button" @click="saveComponent()"
                                                                    class="btn btn-primary">Guardar
                                                            </button>
                                                            <button type="button" v-on:click="cancelComponent(true)"
                                                                    class="btn btn-secondary">Cancelar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <div class="alert alert-warning mt-2">Cargando componentes..</div>
                                            </template>
                                        </div>

                                        <div class="d-block" v-if="view == 'show_information'">
                                            <h4 class="mr-0 ml-0 mb-2 mt-0">Políticas y Restricciones</h4>

                                            <div class="mt-2">
                                                <div class="alert alert-warning" v-if="loading_remarks || loading_restrictions">Cargando remarks y restricciones...</div>
                                                <template v-if="!(loading_remarks || loading_restrictions)">
                                                    <template v-if="content_remarks != '' || content_restrictions != ''">
                                                        <ul class="nav nav-tabs">
                                                            <li class="nav-item" v-if="content_remarks != ''">
                                                                <a v-bind:class="['nav-link', (flag_view == 'remarks') ? 'active' : '']" v-on:click="toggleFlag('remarks')" href="#">Remarks</a>
                                                            </li>
                                                            <li class="nav-item" v-if="content_restrictions != ''">
                                                                <a v-bind:class="['nav-link', (flag_view == 'restrictions' || content_remarks == '') ? 'active' : '']" v-on:click="toggleFlag('restrictions')" href="#">Restricciones</a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content">
                                                            <div v-bind:class="['tab-pane', 'show', 'active']">
                                                                <template v-if="flag_view == 'remarks' && content_remarks != ''">
                                                                    <textarea v-model="content_remarks" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                </template>
                                                                <template v-if="(flag_view == 'restrictions' || content_remarks == '') && content_restrictions != ''">
                                                                    <textarea v-model="content_restrictions" readonly="readonly" class="form-control" style="font-size:12px;resize:none;" rows="15"></textarea>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        <div class="alert alert-info">No se encontró información para remarks o restricciones para el servicio seleccionado.</div>
                                                    </template>
                                                </template>
                                            </div>

                                            <div id="modalAlerta">
                                                <div class="group-btn">
                                                    <button type="button" v-on:click="view = 'detail'"
                                                            class="btn btn-secondary">Regresar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2"
                                         v-if="selectedEvent.nroite != undefined && selectedEvent.nroite > 0 && view == 'detail'">
                                        <div class="d-block">
                                            <div class="float-right">
                                                <button v-bind:disabled="loading_button"
                                                        type="button"
                                                        v-if="!flag_edit && view != 'components' && (selectedEvent.catser !== 'HOT')"
                                                        v-on:click="showComponents()"
                                                        class="col-lg-12 btn btn-white mt-2 mb-2">
                                                    <i class="fa fa-puzzle-piece fa-2x col-lg-12"></i> Componentes
                                                </button>

                                                <button v-bind:disabled="loading_button"
                                                        type="button"
                                                        v-if="!flag_edit && view != 'components'"
                                                        v-on:click="showInformation()"
                                                        class="col-lg-12 btn btn-white mt-2 mb-2">
                                                    <i class="fa fa-info fa-2x col-lg-12"></i> Políticas y Remarks
                                                </button>

                                                <button v-bind:disabled="loading_button"
                                                        type="button"
                                                        v-if="selectedEvent.catser == 'VUELO' && !flag_edit && view != 'assign_transfer' && (selectedEvent.ciavue != '' && selectedEvent.ciavue != null && selectedEvent.nrovue != '' && selectedEvent.nrovue != null)"
                                                        v-on:click="assignTransfer(selectedEvent.fecin, selectedEvent.ciavue, selectedEvent.nrovue)"
                                                        class="col-lg-12 btn btn-white mt-2 mb-2">
                                                    <i class="icon-car fa-2x col-lg-12"></i> Agregar Traslado
                                                </button>

                                                <button v-bind:disabled="loading_button"
                                                        type="button"
                                                        v-if="!flag_edit && selectedEvent.anulado == 0 && (selectedEvent.catser == 'HOT')"
                                                        v-on:click="preCancelHotel()"
                                                        class="col-lg-12 btn btn-white mt-2 mb-2">
                                                    <i class="fa fa-times-circle fa-2x col-lg-12"></i> Cancelar Hotel
                                                </button>

                                                <button v-bind:disabled="loading_button"
                                                        type="button"
                                                        v-if="!flag_edit && selectedEvent.anulado == 0 && (selectedEvent.catser == 'HOT')"
                                                        @click="will_send_confirmation()"
                                                        class="col-lg-12 btn btn-white mt-2 mb-2">
                                                    <i class="fa fa-envelope fa-2x col-lg-12"></i> {{ translations.label.confirmation_codes }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="(selectedEvent.see_preview_communications)">
                                    <div class="d-block">
                                        <h4 class="mr-0 ml-0 mb-2 mt-3">Previsualización de Variaciones</h4>

                                        <div class="d-flex mb-5">
                                            <input readonly="readonly" class="form-control" type="text" v-bind:value="returnURL()" />
                                            <button type="button" class="btn ml-2 btn-info" v-clipboard:copy="returnURL()" @click="copied()">
                                                <i class="fa fa-copy"></i> Copiar
                                            </button>
                                        </div>
                                        <div class="d-flex mb-5">
                                            <label for="">
                                                Para:
                                                <input type="text" class="form-control" v-model="email_for_send">
                                            </label>
                                            <label class="ml-4" for="">
                                                CC:
                                                <input type="text" class="form-control" v-model="email_cc_for_send" :disabled="true">
                                            </label>

                                            <button type="button" class="btn ml-4 btn-info" :disabled="loading_button" @click="send_confirmation()">
                                                <i class="fa fa-envelope"></i> Enviar
                                            </button>
                                        </div>

                                        <hr />

                                        <table class="table table-xs table-striped table-hover">
                                            <thead>
                                            <tr>
                                                <th>TIPO</th>
                                                <th>CAN. HAB.</th>
                                                <th>CAN. PAXS</th>
                                                <th>STATUS</th>
                                                <th>
                                                    CÓDIGO DE CONFIRMACIÓN
                                                </th>
                                            </tr>
                                            </thead>

                                            <tbody v-for="(service, s) in services_for_send">
                                                <h3 style="color: #cd0000 !important; margin: 12px 0px;">
                                                    <i class="fa fa-calendar"></i>
                                                    <b v-for="date_ in service.dates_array"> - {{ date_ | format_date }}</b>
                                                </h3>
                                                <tr v-for="(variation, v) in service.variations">
                                                    <td>{{ variation.desbas_inicial }}</td>
                                                    <td>{{ variation.cantid }}</td>
                                                    <td>{{ variation.canpax }}</td>
<!--                                                    <td>{{ (variation.anulado == 0) ? ((variation.estado.trim() == 'OK') ? 'CONFIRMED' : variation.estado) : 'CXL CON PENALIDAD' }}</td>-->
                                                    <td>{{ (variation.anulado == 0) ? ((variation.estado_hotel.trim() === 'OK') ? 'CONFIRMED' : variation.estado_hotel ) : 'CXL CON PENALIDAD' }}</td>
                                                    <td style="text-align: center">
                                                        <input style="min-width: 200px;" type="text" class="form-control" placeholder="Código de Proveedor" v-model="variation.codcfm" v-on:keyup="duplicate_codcfm_sames(s, v)">
                                                    </td>
                                                </tr>
                                            </tbody>

                                        </table>

                                        <div class="group-btn">

                                            <button type="button" class="btn btn-primary right" :disabled="loading_button" @click="save_confirmation_codes()">
                                                Guardar
                                            </button>

                                            <button type="button" @click="selectedEvent.see_preview_communications=0"
                                                    class="btn btn-primary">
                                                < Volver
                                            </button>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalAlertaPaxs" v-if="flag_edit && !(selectedEvent.nroite != undefined && selectedEvent.nroite > 0)" tabindex="1" role="dialog" class="modal modal--cotizacion">
                <div role="document" class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h4 class="text-center">
                                <div class="icon">
                                    <i class="icon-alert-circle" v-if="!loading"></i>
                                    <i class="spinner-grow" v-if="loading"></i>
                                </div>
                                <strong v-if="!loading">¿Está seguro de cambiar la info?</strong>
                                <strong v-if="loading">{{ translations.label.loading }}</strong>
                            </h4>
                            <p class="text-center" v-if="!loading"><strong>Tiene información pendiente de guardar, por favor, revísela antes de continuar..</strong></p>
                            <div class="group-btn" v-if="!loading">
                                <button type="button" @click="clearService()" data-dismiss="modal"
                                        class="btn btn-secondary">Cambiar
                                </button>
                                <button type="button" @click="closeModal('modalAlertaPaxs')" data-dismiss="modal"
                                        class="btn btn-primary">Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalAlertaPaxs" v-if="!flag_edit && selectedEvent.catser == 'HOT' && modal" tabindex="1" role="dialog" class="modal modal--cotizacion">
                <div role="document" class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h4 class="text-center">
                                <div class="icon">
                                    <i class="icon-alert-circle" v-if="!loading"></i>
                                    <i class="spinner-grow" v-if="loading"></i>
                                </div>
                                <template v-if="!loading">
                                    <strong>¿Está seguro de cancelar <template v-if="!flag_room && !flag_variation">el hotel</template><template v-else-if="!flag_room">el grupo de habitaciones</template><template v-else>la habitación</template>?</strong>
                                    <div>
                                        <!-- p v-if="variation.room_mysql != '' && variation.room_mysql != null" v-for="(variation, v) in selectedEvent.variations">
                                            {{ Object.entries(variation.room_mysql.policies_cancellation)[1] }}
                                        </p -->
                                    </div>
                                </template>
                                <strong v-if="loading">{{ translations.label.loading }}</strong>
                            </h4>
                            <div class="text-center" v-if="!loading">
                                <div v-for="(variation, v) in selectedEvent.variations" v-if="(!flag_variation || flag_variation.nroite == variation.nroite) && variation.check_cancel == 'OK'">
                                    <p v-for="(room, r) in variation.rooms" v-if="(!flag_room || flag_room.codrsv == room.codrsv) && room.estado == 'OK'">
                                        <strong>HABITACIÓN - # RESERVA: {{ room.codrsv }}</strong>
                                        Se puede cancelar hasta el {{ room.policies_cancellation.apply_date }} sin gastos. Después de eso se cobrará: $. {{ room.policies_cancellation.penalty_price }}.<br />
                                    </p>
                                    <hr v-if="!flag_variation && selectedEvent.variations.length > 1" />
                                </div>
                            </div>
                            <div class="group-btn" v-if="!loading">
                                <template>
                                    <button type="button" v-if="!flag_room && !flag_variation" @click="cancelHotel()" data-dismiss="modal"
                                            class="btn btn-secondary">Cancelar Hotel
                                    </button>
                                    <button type="button" v-else-if="!flag_room" @click="cancelAllRooms(flag_variation)" data-dismiss="modal"
                                            class="btn btn-secondary">Cancelar Grupo
                                    </button>
                                    <button type="button" v-else @click="cancelRoom(flag_room)" data-dismiss="modal"
                                            class="btn btn-secondary">Cancelar Habitación
                                    </button>
                                </template>
                                <button type="button" @click="closeModal('modalAlerta')" data-dismiss="modal"
                                        class="btn btn-primary">Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalAlertaPaxsSave" v-if="view == 'assignPaxs' && modal == ''" tabindex="1" role="dialog" class="modal modal--cotizacion">
                <div role="document" class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h4 class="text-center">
                                <div class="icon">
                                    <i class="icon-alert-circle" v-if="!loading"></i>
                                    <i class="spinner-grow" v-if="loading"></i>
                                </div>
                                <strong v-if="!loading">¿Está seguro de borrar la info?</strong>
                                <strong v-if="loading">{{ translations.label.loading }}</strong>
                            </h4>
                            <div class="group-btn" v-if="!loading">
                                <button type="button" @click="resetRooms()" data-dismiss="modal"
                                        class="btn btn-secondary">Limpiar acomodo
                                </button>
                                <button type="button" @click="closeModal('modalAlertaPaxs')" data-dismiss="modal"
                                        class="btn btn-primary">Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalAlertaPaxsSave" v-if="view == 'passengers' && modal == ''" tabindex="1" role="dialog" class="modal modal--cotizacion">
                <div role="document" class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h4 class="text-center">
                                <div class="icon">
                                    <i class="icon-alert-circle" v-if="!loading"></i>
                                    <i class="spinner-grow" v-if="loading"></i>
                                </div>
                                <strong v-if="!loading">¿Desea actualizar la asignación de los pasajeros?</strong>
                                <strong v-if="loading">{{ translations.label.loading }}</strong>
                            </h4>
                            <p class="text-center" v-if="!loading"><strong>En caso no se actualice la asignación, se asignará toda la lista actual de pasajeros.</strong></p>
                            <div class="group-btn" v-if="!loading">
                                <button type="button" @click="showPassengersService()" data-dismiss="modal"
                                        class="btn btn-secondary">Actualizar
                                </button>
                                <button type="button" @click="searchPassengersByService(selectedEvent.nroite, true);" data-dismiss="modal"
                                        class="btn btn-primary">Asignación por defecto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <modal-passengers ref="modal_passengers"></modal-passengers>
        <accommodation-passengers ref="accommodation_passengers"></accommodation-passengers>

        <div v-bind:class="['_options', (show_options) ? '' : 'hide']">
            <button type="button" v-on:click="toggleOptions()" class="btn btn-light btn-lg btn-config"><i class="fas fa-cogs fa-2x"></i></button>
            <div class="card" style="width:100%;">
                <h3 class="card-header">Opciones</h3>
                <div class="card-body">
                    <div>
                        <div class="form-group">
                            <div class="form-check">
                                <label for="update_events" class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="update_events" v-model="update_events" /> <small>Actualizar eventos posteriores cuando se modifique un evento.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-cal/dist/drag-and-drop.es.js'
    import draggable from 'vuedraggable'

    $.extend(true, $.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });

    import accomodationPassengers from '../AccommodationPassengers'
    // Node socket.io
    import store from "../../store"
    // Node socket.io
    export default {
        props: ['data'],
        store,
        components: {
            draggable,
            'accommodation-passengers' : accomodationPassengers
        },
        data: () => {
            return {
                baseURL: window.baseURL,
                api_flights: [],
                flag_flight: false,
                readonly: false,
                show_options: false,
                loading_services: false,
                loading_remarks: false,
                loading_restrictions: false,
                content_remarks: '',
                content_restrictions: '',
                flag_bastar: '',
                all_bastars: [],
                update_events: false,
                flag_view: 'remarks',
                descri_service: '',
                flag_edit: false,
                flag_room: false,
                flag_variation: false,
                selectedEvent: {},
                showDialog: false,
                locale: 'es',
                lang: '',
                nrofile: '',
                loading: false,
                loading_button: false,
                // file: {},
                dayNow: '',
                services_ws: [],
                services: [],
                services_for_send: [],
                email_for_send: '',
                email_cc_for_send: localStorage.getItem('user_email'),
                all_passengers: [],
                passengers_event: {},
                passengers_show: [],
                passengers_service: [],
                view: 'detail',
                all: false,
                check_passengers_service: [],
                loading_passengers: false,
                options: {
                    format: 'DD/MM/YYYY',
                },
                filters: [],
                all_services: [],
                filter_service: 8, // 8 - Almuerzo, 9 - Cena..
                type_service: 0,
                destinations_from: [],
                destinations_to: [],
                type: 'TRAS',
                flag_header_file: false,
                all_clients: [],
                searchItemsSource: false,
                CancelToken: false,
                hover_service: false,
                timeout: '',
                timeout_query: '',
                cantidad_total_paxs: 0,
                airlines: [],
                components: [],
                component_editable: {

                },
                filter_cities: [],
                filter_city: '',
                translations: {
                    label: {},
                    btn: {}
                },
                transfers: [],
                transfer: {

                },
                query: '',
                readonly_hours: false,
                url_hotel: '',
                classes_hotel: [],
                variations: {

                },
                rooms: {},
                check_rooms: [],
                all_variations: false,
                accommodation_simple: [],
                accommodation_doble: [],
                accommodation_triple: [],
                hotels: [],
                hotels_array: [],
                types_room: [
                    'SGL',
                    'DBL',
                    'TPL'
                ],
                type_room: {},
                modal: '',
                total_sockets_in_room: 0,
                position_x: 0,
                position_y: 0,
                active_cursor: false,
            }
        },
        computed: {
            file(){
                console.log(this.$store.state.rooms.room.data_file)
                if(this.$store.state.rooms.room.data_file === undefined){
                    return {
                        id: "",
                        nroemp: 5,
                        identi: "R",
                        nroref: 0,
                        nrores: "",
                        nropre: "",
                        fecha: "",
                        codsec: "",
                        grupo: "",
                        concta: "",
                        tarifa: "",
                        moncot: "",
                        succli: "",
                        codcli: "",
                        codven: "",
                        codope: "",
                        solici: "",
                        refext: "",
                        descri: "",
                        idioma: "",
                        diain: "",
                        diaout: "",
                        canadl: "",
                        canchd: "",
                        caninf: "",
                        razon: "",
                        cuit: "",
                        coniva: "",
                        direcc: "",
                        codciu: "",
                        ciudad: "",
                        postal: "",
                        provin: null,
                        pais: "",
                        telefo: "",
                        cliopc: null,
                        razopc: null,
                        cuiopc: null,
                        ivaopc: null,
                        diropc: null,
                        codopc: null,
                        ciuopc: null,
                        posopc: null,
                        proopc: null,
                        paiopc: null,
                        telopc: null,
                        observ: "",
                        nropax: "",
                        codgru: "",
                        operad: "",
                        cotiza: "",
                        vouche: "",
                        ticket: "",
                        factur: "",
                        status: "",
                        nrotot: "",
                        promos: "",
                        flag_hotel: "",
                        piaced:"",
                        nroped:"",
                        tipoventa:"",
                        razoncli:""
                    }
                } else {
                    return this.$store.state.rooms.room.data_file
                }
            },
            users_in_room(){
                return this.$store.state.rooms.room.users
            },
            messages_alert(){
                return this.$store.state.notification
            },
            notifications(){
                return this.$store.state.rooms.room.notifications
            },
        },
        watch:{
            notifications(notifications){
                if( notifications !== undefined ){
                    let notification = notifications[notifications.length-1]
                    this.$toast.success( notification.user + ': ' + notification.message, {
                        position: 'top-right'
                    })
                }
            },
            messages_alert(message_obj){
                if( message_obj !== undefined ){
                    console.log(message_obj)
                    this.$toast.success(message_obj.message, {
                        position: 'top-right'
                    })
                }
            },
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.user_id = parseInt( localStorage.getItem('user_id') )
            this.user_invited = ( localStorage.getItem('code') === 'guest' )
            this.code = (this.user_invited) ? localStorage.getItem('code_guest') : localStorage.getItem('code')

            this.dayNow = new Date()
            this.nrofile = this.data.nrofile

            // Node socket.io
            axios.post(
                baseSocketURL + 'api/login',
                {
                    code : this.code,
                    photo : localStorage.getItem('photo'),
                    name : localStorage.getItem('name'),
                    usertype : (this.user_invited) ? 'invited' : 'registered',
                    color : this.get_random_color()
                }
            )
                .then((result) => {
                    this.$store.state.auth.token = result.data.token
                    if(!Vue.prototype.$socket){
                        require("../../plugins/socket-io")
                    }
                    this.$socket.connect()
                    this.$store.commit("rooms/set_room", {id:"file_"+this.nrofile}, {root:true})
                    this.$socket.emit('rooms/join', "file_"+this.nrofile)

                    // Solo para ver:
                    this.total_sockets_in_room = this.$store.state.rooms.countUsersForLive["file_"+this.nrofile]
                    console.log(this.$store.state.rooms.room)
                })
                .catch((e) => {
                    console.log(e)
                })
            // Node socket.io

            this.init()
            this.setTranslations()
        },
        methods: {
            set_active_cursor(user_code){
                if( user_code === this.code ){
                    this.active_cursor = !(this.active_cursor)
                    this.$socket.emit('rooms/set_active_cursor', "file_"+this.nrofile, this.active_cursor)
                }
            },
            get_random_color() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            },
            // mouseEnter(event) {
            //     console.log('mouseneter');
            //     this.popup = true;
            //     this.$el.addEventListener('mousemove', this.mouseMove, false);
            // },
            // mouseLeave(event) {
            //     this.popup = false;
            //     // this.$el.removeEventListener('mousemove', this.mouseMove());
            // },
            mouse_move(event) {
                if( this.users_in_room !== undefined && this.users_in_room.length>1 && this.active_cursor){
                    console.log(event.clientX, event.clientY)
                    this.$socket.emit('rooms/set_user_positions', "file_"+this.nrofile, event.clientX, event.clientY)
                }
            },
            get_info_hotel(codsvs){
                if( this.hotels_array[codsvs] === undefined ){
                    axios.get(baseExternalURL + 'api/suppliers?search=' + codsvs)
                        .then(response => {
                            this.hotels_array[codsvs] = response.data.data

                            if( this.hotels_array[codsvs].length > 0 ){
                                this.email_for_send = this.hotels_array[codsvs][0].email
                            } else {
                                this.email_for_send = ""
                            }
                        })
                        .catch(e => {
                            console.log(e)
                        })
                } else {
                    if( this.hotels_array[codsvs].length > 0 ){
                        this.email_for_send = this.hotels_array[codsvs][0].email
                    } else {
                        this.email_for_send = ""
                    }
                }
            },
            send_confirmation() {

                if( this.email_for_send === '' ){
                    this.$toast.warning("Por favor ingrese el email destino", {
                        position: 'top-right'
                    })
                    return
                }

                let provider_name = ''
                let provider_language_id = ''
                if( this.hotels_array[this.selectedEvent.codsvs].length > 0 ){
                    provider_name = this.hotels_array[this.selectedEvent.codsvs][0].name
                    provider_language_id = this.hotels_array[this.selectedEvent.codsvs][0].language_id
                }

                this.loading_button = true

                console.log(this.selectedEvent)

                let data = {
                    lang: localStorage.getItem('lang'),
                    to: this.email_for_send,
                    cc: this.email_cc_for_send,
                    link: baseURL + 'register_codcfm/' + this.nrofile + '?codsvs=' + this.selectedEvent.codsvs,
                    provider_name: (provider_name==='') ? this.selectedEvent.title : provider_name,
                    provider_language_id: (provider_language_id==='') ? 1 : provider_language_id,
                    hotel_code: this.selectedEvent.codsvs,
                    file_number: this.nrofile,
                    executive_name: localStorage.getItem('name'),
                    executive_email: localStorage.getItem('user_email')
                }
                axios.post(baseExternalURL + 'api/files/services/confirmation_codes/notification', data)
                    .then(response => {

                        if (response.data.success) {
                            this.$toast.success("Enviado correctamente", {
                                position: 'top-right'
                            })
                        }

                        this.loading_button = false
                    })
                    .catch(e => {
                        console.log(e)
                        this.loading_button = false
                    })
            },
            save_confirmation_codes() {

                let services_ = []

                this.services_for_send.forEach((s) => {
                    s.variations.forEach((v) => {
                        if (v.codcfm != 0 && v.codcfm !== null && v.codcfm.trim() !== '') {
                            services_.push({
                                id: v.id,
                                item_number: v.nroite,
                                confirmation_code: v.codcfm,
                                preview_status: v.status_hotel
                            })
                        }
                    })
                })

                if( services_.length === 0 ){
                    this.$toast.warning("Ningún codigo ingresado para guardar", {
                        position: 'top-right'
                    })
                    return
                }
                this.loading_button = true

                let data = {
                    services: services_
                }
                axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/services/confirmation_codes', data)
                    .then((response) => {
                        if (response.data.success) {
                            this.$toast.success(this.translations.message.success, {
                                position: 'top-right'
                            })

                            services_.forEach((s) => {
                                // Nodo de envio
                                this.services_for_send.forEach((s_for_send) => {
                                    s_for_send.variations.forEach((v) => {
                                        if (s.id === v.id) {
                                            v.estado_hotel = "OK"
                                        }
                                    })
                                })
                                // Nodo que llena el nodo de envio
                                this.services.forEach((service)=>{
                                    if(s.id===service.id){
                                        service.estado_hotel = "OK"
                                    }
                                })
                                // Nodo que pinta en el modal elegido del calendario
                                this.selectedEvent.variations.forEach((variation) => {
                                    if( variation.id === s.id ){
                                        variation.estado_hotel = "OK"
                                    }
                                })
                            })

                        } else {
                            this.$toast.error(this.translations.message.error, {
                                position: 'top-right'
                            })
                        }
                        this.loading_button = false
                    })
            },
            copied(){
                this.$toast.success('Copiado!', {
                    position: 'top-right'
                })
            },
            returnURL: function () {
                return baseURL + 'register_codcfm/' + this.nrofile + '?lang=' + this.lang +'&codsvs=' + this.selectedEvent.codsvs
            },
            duplicate_codcfm_sames(index_service, index_variation){
                if( index_service === 0 && this.services_for_send.length > 1 ){
                    this.services_for_send.forEach((service, key_s)=>{
                        if( key_s > 0 ){
                            console.log(service)
                            service.variations.forEach((v)=>{
                                if( v.bastar === this.services_for_send[0].variations[index_variation].bastar &&
                                    v.cantid === this.services_for_send[0].variations[index_variation].cantid
                                ){
                                    v.codcfm = this.services_for_send[0].variations[index_variation].codcfm
                                }
                            })
                        }
                    })
                }
            },
            will_send_confirmation(){

                this.services_for_send = []
                let array_services_flag = []
                this.services.forEach((s)=>{
                    s.dates_array = []
                    if(s.catser === 'HOT' && s.codsvs === this.selectedEvent.codsvs){

                        if(!(array_services_flag[s.nroite])){
                            this.services_for_send.push(s)
                            s.variations.forEach((v)=>{
                                // indice del arreglo construido
                                array_services_flag[v.nroite] = { key: this.services_for_send.length - 1 }
                            })
                        }

                        this.services_for_send[array_services_flag[s.nroite].key].dates_array.push(s.fecin)

                    }
                })

                this.get_info_hotel(this.selectedEvent.codsvs)

                this.selectedEvent.see_preview_communications = 1

            },
            resetRooms: function () {
                let vm = this
                // selectedEvent.nroite -> es como el hotel activo elegido
                vm.passengers_event[vm.selectedEvent.nroite] = JSON.parse(localStorage.getItem('all_passengers'))

                vm.selectedEvent.variations.forEach((variation, v) => {

                    if(vm.variations[variation.nroite])
                    {
                        let _rooms = variation.rooms

                        _rooms.forEach((room, r) => {
                            if((vm.check_rooms.length == 0 || vm.check_rooms[(r + 1)]) && !(room.tipfil.trim() == 'GASCAN'))
                            {
                                vm.$set(vm.rooms[room.nroite], (r + 1), [])
                            }
                        })
                    }
                })

                setTimeout(function () {
                    vm.saveRooms()
                }, 100)
            },
            allVariations: function () {
                let vm = this
                let _variations = vm.selectedEvent.variations
                vm.variations = {}

                if(vm.all_variations)
                {
                    _variations.forEach((variation, v) => {
                        vm.$set(vm.variations, variation.nroite, vm.all_variations)
                    })
                }
            },
            saveRooms: function() {
                this.loading = true
                this.loading_button = true

                // axios.post(baseExpressURL + 'api/v1/files/' + this.nrofile + '/rooms_pax', {
                let rooms_ = {}

                this.selectedEvent.variations.forEach( (v)=>{
                    Vue.set(rooms_, v.nroite, this.rooms[ v.nroite ])
                })

                axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/accommodations', {
                    rooms: rooms_,
                    variations: this.selectedEvent.variations,
                    codsvs: this.selectedEvent.codsvs
                })
                    .then(response => {

                        if( response.data.success ){
                            this.init(true, true, false)

                            this.$toast.success('Pasajeros acomodados correctamente', {
                                position: 'top-right'
                            })

                            this.orderServices()
                        } else {
                            this.$toast.error(response.data.message, {
                                position: 'top-right'
                            })
                        }

                        this.loading = false
                        this.loading_button = false
                        this.all_variations = false

                    })
                    .catch(e => {
                        this.loading = false
                        this.loading_button = false
                        console.log(e)
                    })
            },
            change: function (e) {
                console.log(e)
            },
            hideRooms: function () {
                this.view = 'detail'
                // this.rooms = {}
                this.all_variations = false
                this.variations = {}
                this.check_rooms = []
            },
            detailVariations: function () {
                this.view = 'viewRooms'
            },
            assignPaxs: function () {
                this.view = 'assignPaxs'
            },
            showRoom: function (v, r) {
                this.view = 'assignPaxs'
                this.variations = {}
                this.variations[v] = true
                this.check_rooms[r] = true
            },
            preCancelHotel: function () {
                console.log("Cancelando hotel completo..")
                console.log(this.selectedEvent.variations)

                this.flag_room = false
                this.flag_variation = false
                this.modal = true
                this.showModal('modalAlertaPaxs')
            },
            preCancelAllRooms: function (_variation) {
                console.log("Cancelando variación completa..")
                console.log(_variation)

                this.flag_variation = _variation
                this.flag_room = false

                this.modal = true
                this.showModal('modalAlertaPaxs')
            },
            preCancelRoom: function (_room) {
                console.log("Cancelando habitación..")
                console.log(_room)

                this.flag_variation = false
                this.flag_room = _room

                this.modal = true
                this.showModal('modalAlertaPaxs')
            },
            cancelHotel: function () {
                axios.post(baseExternalURL + 'api/cancel_hotel_files', {
                    nroref: this.nrofile,
                    event: this.selectedEvent
                })
                    .then((result) => {

                        if (result.data.success === true) {
                            this.services_ws = []
                            this.closeModal('modal')

                            this.loadServices()
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            cancelAllRooms: function () {
                axios.post(baseExternalURL + 'api/cancel_hotel_files', {
                    nroref: this.nrofile,
                    event: this.selectedEvent,
                    variation: this.flag_variation
                })
                    .then((result) => {

                        if (result.data.success === true) {
                            this.services_ws = []
                            this.closeModal('modal')

                            this.loadServices()
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            cancelRoom: function () {
                axios.post(baseExternalURL + 'api/cancel_hotel_files', {
                    nroref: this.nrofile,
                    event: this.selectedEvent,
                    room: this.flag_room
                })
                    .then((result) => {

                        if (result.data.success === true) {
                            this.services_ws = []
                            this.closeModal('modal')

                            this.loadServices()
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            getClientsByExecutive: function (_code) {
                axios.get('api/clients/selectBox/by/executive')
                    .then((result) => {
                        if (result.data.success === true) {

                            let clients = result.data.data

                            clients.forEach((element) => {
                                if (_code == element.client_code) {
                                    localStorage.setItem('client_id', element.code)
                                }
                            })
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            showUrlHotel: function () {

                if(this.type == 'HOT' || this.selectedEvent.catser == 'HOT')
                {
                    this.url_hotel = ''

                    if(this.selectedEvent.ciuin == undefined)
                    {
                        this.url_hotel = ''
                    }
                    else
                    {
                        axios.post(baseExternalURL + 'api/search_passengers', {
                            file: this.nrofile,
                            type: 'file'
                        })
                            .then(response => {

                                let _passengers = response.data.passengers

                                axios.post(baseExternalURL + 'api/toggle_view_hotels', {
                                    file: this.nrofile,
                                    adults: this.file.canadl,
                                    child: this.file.canchd,
                                    fecini: moment(this.selectedEvent.fecin, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                                    fecfin: moment(this.selectedEvent.fecout, 'DD/MM/YYYY').format('YYYY-MM-DD'),
                                    ciuin: this.selectedEvent.ciuin,
                                    passengers: _passengers,
                                    typeclass_id: this.selectedEvent.typeclass_id,
                                    hotels_search_code: this.selectedEvent.hotels_search_code
                                })
                                    .then(response => {

                                        console.log(response.data)

                                        axios.post(baseURL + 'toggle_view_hotels', {
                                            items: response.data.items
                                        })
                                            .then(response => {

                                                this.url_hotel = '/hotels?search=1';
                                            })
                                            .catch(error => {
                                                console.log(error)
                                            })
                                    })
                                    .catch(error => {
                                        console.log(error)
                                    })
                            })
                            .catch(e => {
                                console.log(e)
                            })

                    }
                }
            },
            setTranslations() {
                axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/board').then((data) => {
                    this.translations = data.data
                })
            },
            prepareCalendar: function () {
                setTimeout(function() {
                    $('.vuecal').css({
                        height: (($(window).height() + $('nav.navbar').height()) - 50) + 'px'
                    });
                }, 100)
            },
            toggleOptions: function () {
                this.show_options = !this.show_options
            },
            toggleFlag: function (_flag_view) {
                this.flag_view = _flag_view
            },
            showInformation: function () {
                let vm = this
                vm.view = 'show_information'
                vm.loading_remarks = true
                vm.loading_restrictions = true

                axios.post(baseExternalURL + 'api/services/' + vm.selectedEvent.codsvs + '/remarks', {
                    service: vm.selectedEvent
                })
                    .then(response => {
                        vm.content_remarks = response.data.content
                        vm.loading_remarks = false
                    })
                    .catch(error => {
                        console.log(error)
                    })


                axios.post(baseExternalURL + 'api/services/' + vm.selectedEvent.codsvs + '/restrictions', {
                    service: vm.selectedEvent
                })
                    .then(response => {
                        vm.content_restrictions = response.data.content
                        vm.loading_restrictions = false
                    })
                    .catch(error => {
                        console.log(error)
                    })
            },
            searchDetail: function (_service) {
                let vm = this
                let prev_service = localStorage.getItem('_service')
                localStorage.setItem('_service', '')

                this.showModal('modalAlertaRemarks')

                axios.post(baseExternalURL + 'api/services/' + _service + '/remarks', {
                    service: vm.selectedEvent
                })
                    .then(response => {
                        if(prev_service == _service)
                        {
                            vm.content_remarks = response.data.content
                            vm.loading_remarks = false
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    })


                axios.post(baseExternalURL + 'api/services/' + _service + '/restrictions', {
                    service: vm.selectedEvent
                })
                    .then(response => {
                        if(prev_service == _service)
                        {
                            vm.content_restrictions = response.data.content
                            vm.loading_restrictions = false
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    })
            },
            showDetailService: function (_service, _hover) {
                let vm = this
                localStorage.setItem('_service', '')
                let prev_service = localStorage.getItem('_service')

                vm.hover_service = true
                vm.content_restrictions = ''
                vm.content_remarks = ''
                vm.loading_remarks = true
                vm.loading_restrictions = true

                if(prev_service != _service)
                {
                    if(_hover == true)
                    {
                        localStorage.setItem('_service', _service)

                        if(vm.timeout != '')
                        {
                            clearTimeout(vm.timeout)
                        }

                        vm.timeout = setTimeout(function () {
                            vm.searchDetail(_service)
                        }, 1000);
                    }
                }
            },
            cancelHeaderFile: function () {
                this.flag_header_file = false
                this.prepareCalendar()
            },
            update_file: function () {
                this.loading = true
                this.showLoader('Cargando...')

                this.file.paxs = parseInt(parseInt(this.file.canadl) + parseInt(this.file.canchd) + parseInt(this.file.caninf))

                axios.post(baseExternalURL + 'api/files/' + this.nrofile, {
                    file: this.file,
                    update_events: this.update_events
                })
                    .then(response => {
                        let vm = this
                        vm.loading = false
                        vm.hideLoader()

                        vm.$toast.success('FILE actualizado correctamente.', {
                            position: 'top-right'
                        })
                        console.log(vm.file)

                        vm.services_ws = vm.translate_data_services( response.data.file_, response.data.services, 0 )
                        vm.orderServices()

                        if(vm.file.paxs_prime != vm.file.paxs)
                        {
                            vm.modalPassengers(vm.nrofile, vm.file.paxs, vm.file.canadl, vm.file.canchd, vm.file.caninf)
                        }

                        setTimeout(function () {
                            // vm.init(true)
                            vm.init(true, false, true)
                            vm.flag_header_file = false
                        })
                    })
                    .catch(error => {
                        this.$toast.error('Ocurrió un error al guardar la información del FILE. Por favor, intente nuevamente.', {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.hideLoader()
                        this.loading = false
                    })
            },
            showHeaderFile: function () {

                this.file.diain_show = moment(this.file.diain).format('DD/MM/YYYY')
                this.file.diaout_show = moment(this.file.diaout).format('DD/MM/YYYY')
                this.file.fecha_show = moment(this.file.fecha).format('DD/MM/YYYY')

                this.flag_header_file = !this.flag_header_file
            },
            setService: function (_service) {
                this.showDetailService(_service.codigo, true)
                this.selectedEvent.service = _service

                let _event = localStorage.getItem('prev_event')

                if(_event != '' && _event != undefined)
                {
                    this.selectedEvent.ciuin = _service.ciudes.trim()
                    this.selectedEvent.ciuout = _service.ciuhas.trim()

                    localStorage.setItem('set_from', 1)
                    localStorage.setItem('set_to', 1)
                    this.searchDestinationsFrom(_service.ciudes)
                    this.searchDestinationsTo(_service.ciuhas)
                }
                else
                {
                    localStorage.setItem('set_from', 1)
                    localStorage.setItem('set_to', 1)
                    this.searchDestinationsFrom(_service.ciudes)
                    this.searchDestinationsTo(_service.ciuhas)
                }

                let _flag = 1;

                if(this.type == 'TREN')
                {
                    if(_service.horin != '' && _service.horin != null)
                    {
                        this.selectedEvent.horin = _service.horin.replace('.', ':')
                    }
                    else
                    {
                        _flag = 0;
                    }

                    if(_service.horout != '' && _service.horout != null)
                    {
                        this.selectedEvent.horout = _service.horout.replace('.', ':')
                    }
                    else
                    {
                        _flag = 0;
                    }

                    if(_flag == 1)
                    {
                        this.readonly_hours = true
                    }
                }

                console.log(_service.ciudes)
                console.log(_service.ciuhas)

                axios.post(baseExternalURL + 'api/services/' + _service.codigo + '/bastar', {
                    service: this.selectedEvent,
                    tipser: this.type
                })
                    .then(response => {
                        this.flag_bastar = response.data.bastar
                        this.all_bastars = response.data.bastars
                    })
                    .catch(error => {
                        console.log(error)
                    })
            },
            setDestinationsTo: function (_destination) {
                let vm = this
                vm.searchServices('')
            },
            setDestinationsFrom: function (_destination) {
                let vm = this
                vm.searchServices('')

                if(vm.type == 'HOT' || vm.selectedEvent.catser == 'HOT')
                {
                    vm.showUrlHotel()
                }
            },
            clearService: function () {
                let _type = localStorage.getItem('service_type')

                if(_type != '')
                {
                    this.type_service = 0 // Destino nacional..
                    this.type = _type
                    this.readonly = false
                    this.selectedEvent.service = undefined
                    this.selectedEvent.ciuin = undefined
                    this.selectedEvent.ciuout = undefined
                    this.selectedEvent.ciavue = undefined
                    this.selectedEvent.nrovue = ''
                    this.selectedEvent.contentFull = ''

                    this.query = ''
                    this.all_services = []

                    this.searchServices('')
                    this.searchDestinationsFrom('')
                    this.searchDestinationsTo('')
                    this.searchAirlines('')

                    localStorage.setItem('service_type', '')
                }
            },
            toggleType: function (_type) {

                localStorage.setItem('service_type', _type);

                if(
                    (typeof this.selectedEvent.service == 'object' && Object.entries(this.selectedEvent.service).length > 0) ||
                    (typeof this.selectedEvent.ciuin == 'object' && Object.entries(this.selectedEvent.ciuin).length > 0) ||
                    (typeof this.selectedEvent.ciuout == 'object' && Object.entries(this.selectedEvent.ciuout).length > 0)
                )
                {
                    this.showModal('modalAlertaPaxs');
                }
                else
                {
                    this.clearService();
                }
            },
            toggleFilter: function (_filter) {

                if(_filter == 'all')
                {
                    if(this.filters.length > 0)
                    {
                        this.filters = []
                        this.filter_city = ''
                    }
                    else
                    {
                        this.filters.push(_filter)
                    }
                }
                else
                {
                    let key = this.filters.indexOf(_filter)

                    if(key > -1)
                    {
                        this.filters.splice(key, 1)
                    }
                    else
                    {
                        this.filters.push(_filter)
                    }
                }

                this.orderServices(this.services_ws)
            },
            filterCity: function () {
                this.showLoader('Cargando...')
                this.services = []
                // apunta al front, consultas al back reservations_hotels y reformato de campos en mismo front
                axios.post(baseURL + 'order_services_file', {
                    lang: this.lang,
                    city: this.filter_city,
                    services: this.services_ws,
                    filters: this.filters
                }).then((response) => {
                    this.hideLoader()
                    let vm = this
                    vm.$set(vm, 'services', response.data.services)
                    vm.$set(vm, 'hotels', response.data.hotels)
                    vm.$set(vm, 'type_room', response.data.type_room)

                    // vm.passengers_event = {}
                    vm.validateHotel(vm.services)
                }).catch((response) => {
                    console.log(response.data)
                    this.services = []
                })
            },
            showLoader(texto) {
                this.loading = true
                var $backdrop = $(".backdrop-banners"), timeLoading = 250

                $backdrop.css({ display: 'block' }).animate({ opacity: 1 }, timeLoading, function() {
                    $backdrop.prepend('<div id="spinner-loader"><div class="spinner"><span class="logo"></span></div>' +
                        '<div class="spinner-text">' + texto + '<small>Por favor espere.</small></div></div>')
                })
            },
            hideLoader() {
                this.loading = false
                var $backdrop = $(".backdrop-banners"), timeLoading = 250
                $backdrop.css({ display: 'none' }).animate({ opacity: 0 }, timeLoading, function(){
                    $backdrop.html('');
                });
            },
            init: function (_loading, update, socket) {

                if(update == undefined || !(update))
                {
                    localStorage.setItem('prev_event', '')

                    this.prepareCalendar()
                    this.getClassHotelByClientId()
                    this.searchClients()
                    if( _loading !== undefined ){
                        this.show_file(_loading, update, socket)
                        console.log('update con socket')
                    }
                }

                if(_loading == undefined)
                {
                    this.loading = true
                    this.showLoader('Cargando...')
                    this.searchPassengers(_loading, update)
                }

            },
            show_file(_loading, update, socket){
                axios.get(baseExternalURL + 'api/files/' + this.nrofile + '?lang='+localStorage.getItem('lang'))
                // axios.get(baseExpressURL + 'api/v1/files/' + this.nrofile)
                    .then(response => {

                        if(response.data.success){
                            let file = this.translate_data_file(response.data.data)
                            this.dayNow = file.diain

                            file.canadl = (file.canadl > 0 && file.canadl != null)  ? file.canadl : 0
                            file.canchd = (file.canchd > 0 && file.canchd != null)  ? file.canchd : 0
                            file.caninf = (file.caninf > 0 && file.caninf != null)  ? file.caninf : 0

                            Vue.set(file, 'paxs_prime', parseInt(parseInt(file.canadl) + parseInt(file.canchd) + parseInt(file.caninf)))
                            Vue.set(file, 'paxs', parseInt(parseInt(file.canadl) + parseInt(file.canchd) + parseInt(file.caninf)))

                            file.tipoventa_show = (file.tipoventa == 1) ? 'CONTADO' : ((file.tipoventa == 2) ? 'CREDITO' : '')

                            if(_loading == undefined)
                            {
                                this.loading = false
                                this.hideLoader()
                            }

                            if(update == undefined)
                            {
                                this.getClientsByExecutive(file.codcli)
                                this.loadServices()
                            }

                            localStorage.setItem('client_id', response.data.data.client_id)

                            //*R. TIME
                            this.$store.commit("rooms/set_data", ['data_file', file], {root:true})
                            if(socket){
                                this.$socket.emit('rooms/refresh', "file_"+this.nrofile, 'data_file', file)
                            }
                            //*R. TIME
                        } else {

                            this.loading = false
                            this.hideLoader()
                            this.$toast.warning(response.data.message, {
                                position: 'top-right'
                            })
                        }

                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)

                        if(_loading == undefined)
                        {
                            this.hideLoader()
                            this.loading = false
                        }
                    })
            },
            translate_data_file(data){
               let data_ = {
                   id: data.id,
                   nroemp: 5,
                   identi: "R",
                   nroref: data.file_number,
                   nrores: data.reservation_number,
                   nropre: data.budget_number,
                   fecha: data.created_at,
                   codsec: data.sector_code,
                   grupo: data.group,
                   concta: data.sale_type,
                   tarifa: data.tariff,
                   moncot: data.currency,
                   succli: data.revision_stages,
                   codcli: data.client.code,
                   codven: data.executive_code,
                   codope: data.executive_code_sale,
                   solici: data.applicant,
                   refext: data.file_code_agency,
                   descri: data.description,
                   idioma: data.lang,
                   diain: data.date_in,
                   diaout: data.date_out,
                   canadl: data.adults,
                   canchd: data.children,
                   caninf: data.infants,
                   razon: data.client.name,
                   cuit: data.client.ruc,
                   coniva: data.use_invoice,
                   direcc: data.client.address,
                   codciu: data.client.city_code,
                   ciudad: data.client.city_name,
                   postal: data.client.postal_code,
                   provin: null,
                   pais: data.client.countries.translations[0].value,
                   telefo: data.client.phone,
                   cliopc: null,
                   razopc: null,
                   cuiopc: null,
                   ivaopc: null,
                   diropc: null,
                   codopc: null,
                   ciuopc: null,
                   posopc: null,
                   proopc: null,
                   paiopc: null,
                   telopc: null,
                   observ: data.observation,
                   nropax: data.total_paxs,
                   codgru: data.client.countries.iso_ifx,
                   operad: data.executive_code_process,
                   cotiza: data.have_quote,
                   vouche: data.have_voucher,
                   ticket: data.have_ticket,
                   factur: data.have_invoice,
                   status: data.status,
                   nrotot: data.total_paxs,
                   promos: data.promotion,
                   flag_hotel: data.total_accommodation,
                   piaced:"-"+data.client.general_markup+".00",
                   nroped:data.order_number,
                   tipoventa:data.sale_type,
                   razoncli:data.client.name
               }
               return data_
            },
            editEvent: function () {
                this.flag_edit = true
                this.flag_flight = false
                this.readonly = false

                let _fecin = moment(this.selectedEvent.fecin).format('DD/MM/YYYY')
                let _fecout = moment(this.selectedEvent.fecout).format('DD/MM/YYYY')

                this.selectedEvent.fecin = _fecin
                this.selectedEvent.fecout = _fecout

                if(this.selectedEvent.codsvs == 'AEIFLT')
                {
                    this.type_service = 1
                }

                if(this.selectedEvent.codsvs == 'AECFLT')
                {
                    this.type_service = 0
                }

                localStorage.setItem('set_from', 1)
                localStorage.setItem('set_to', 1)
                localStorage.setItem('set_airline', 1)
                this.searchDestinationsFrom(this.selectedEvent.ciuin)
                this.searchDestinationsTo(this.selectedEvent.ciuout)
                this.searchAirlines(this.selectedEvent.ciavue)

                axios.post(baseExternalURL + 'api/services/' + this.selectedEvent.codsvs + '/bastar', {
                    service: this.selectedEvent,
                    tipser: this.selectedEvent.catser
                })
                    .then(response => {
                        this.flag_bastar = response.data.bastar
                        this.all_bastars = response.data.bastars

                        this.all_bastars.forEach((element, e, array) => {
                            if (element.bastar == this.selectedEvent.bastar) {
                                Vue.set(this.selectedEvent, 'bastar', element)
                            }
                        })
                    })
                    .catch(error => {
                        console.log(error)
                        this.loading = false
                    })

                if(this.type == 'TRAS' || this.selectedEvent.catser == 'TRAS')
                {
                    this.searchTransfers(this.selectedEvent.fecin)
                }

                localStorage.setItem('save_service', 0)
            },
            searchAirlines: function (search, loading) {

                console.log(search)

                if(typeof search != 'object')
                {
                    let _search = (search != '' && search != undefined) ? search.toUpperCase() : ''

                    axios.get(baseExpressURL + 'api/v1/flights/airlines?term=' + _search)
                        .then(response => {
                            this.airlines = response.data.data

                            if(localStorage.getItem('set_airline') != '' && localStorage.getItem('set_airline') != undefined)
                            {
                                if(_search != '')
                                {
                                    Vue.set(this.selectedEvent, 'ciavue', this.airlines[0])
                                    // this.selectedEvent.ciuin = this.destinations_from[0]
                                    localStorage.setItem('set_airline', '')
                                }
                            }
                        })
                        .catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                            this.loading = false
                        })
                }
            },
            searchTransfers: function (_date) {
                this.transfer = {}

                if((this.selectedEvent.ciavue != '' && this.selectedEvent.ciavue != undefined && this.selectedEvent.ciavue != null) && (this.selectedEvent.nrovue != '' && this.selectedEvent.nrovue != undefined && this.selectedEvent.ciavue != null))
                {
                    axios.post(baseExpressURL + 'api/v1/files/' + this.nrofile + '/' + this.selectedEvent.nroite + '/transfer', {
                        date: _date,
                        ciavue: this.selectedEvent.ciavue,
                        nrovue: this.selectedEvent.nrovue
                    })
                        .then(response => {
                            let _response = response.data.data

                            _response.forEach((element, e, array) => {

                                if(!this.readonly)
                                {
                                    if(element.nroite != this.selectedEvent.nroite && (element.tipsvs.trim() == 'TIN' || element.tipsvs.trim() == 'TOT'))
                                    {
                                        this.transfers.push(element)

                                        if(element.ciavue == this.selectedEvent.ciavue && element.nrovue == this.selectedEvent.nrovue)
                                        {
                                            this.setTransfer(e)
                                        }
                                    }
                                }
                            })
                        })
                        .catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                }
            },
            setTransfer: function (element, _readonly) {

                if(_readonly == undefined)
                {
                    element = this.transfers[element]
                }
                else
                {
                    element.ciudes = element.ciuin
                    element.ciuhas = element.ciuout

                    if(element.horin_flight != undefined)
                    {
                        element.horin = element.horin_flight
                    }

                    if(element.horout_flight != undefined)
                    {
                        element.horout = element.horout_flight
                    }
                }

                console.log(element)

                if(this.selectedEvent.catser == 'TRAS')
                {
                    if(element.ciuhas == 'LIM')
                    {
                        this.selectedEvent.tipsvs = 'TOT'
                        this.selectedEvent.horin = moment(element.horin, 'HH:mm').format('HH:mm')

                        if(element.tipsvs_flight != undefined)
                        {
                            axios.post(baseExpressURL + 'api/v1/files/time_transfer', {
                                ciuin: (this.selectedEvent.ciuin.codciu != undefined) ? this.selectedEvent.ciuin.codciu : this.selectedEvent.ciuin,
                            })
                                .then(response => {
                                    let time = (response.data.data[0] != undefined) ? response.data.data[0].descri : ''

                                    console.log(time)

                                    if(time != '')
                                    {
                                        time = time.split(' ')
                                        let time_aec = time[0].split(':')
                                        let time_aei = time[1].split(':')

                                        let hour = 0
                                        let minute = 0

                                        if(element.tipsvs_flight == 'AEC')
                                        {
                                            hour = parseInt(time_aec[0])
                                            minute = parseInt(time_aec[1])
                                        }

                                        if(element.tipsvs_flight == 'AEI')
                                        {
                                            hour = parseInt(time_aei[0])
                                            minute = parseInt(time_aei[1])
                                        }

                                        this.selectedEvent.horin = moment(this.selectedEvent.fecin + ' ' + this.selectedEvent.horin, 'DD/MM/YYYY HH:mm').subtract(hour, 'hours').subtract(minute, 'minutes').format('HH:mm')
                                    }
                                })
                                .catch(error => {
                                    this.$toast.error(this.translations.messages.internal_error, {
                                        position: 'top-right'
                                    })
                                    console.log(error)
                                })
                        }

                        this.selectedEvent.horout = moment(element.horin, 'HH:mm').format('HH:mm')

                        localStorage.setItem('set_from', 1)
                        localStorage.setItem('set_to', 1)
                        this.searchDestinationsFrom(element.ciudes)
                        this.searchDestinationsTo(element.ciudes)
                    }

                    if(element.ciudes == 'LIM')
                    {
                        this.selectedEvent.tipsvs = 'TIN'
                        this.selectedEvent.horin = moment(element.horout, 'HH:mm').format('HH:mm')
                        this.selectedEvent.horout = moment(element.horout, 'HH:mm').add(1, 'hour').format('HH:mm')

                        localStorage.setItem('set_from', 1)
                        localStorage.setItem('set_to', 1)
                        this.searchDestinationsFrom(element.ciuhas)
                        this.searchDestinationsTo(element.ciuhas)
                    }
                }

                /*
                localStorage.setItem('set_airline', 1)
                this.searchAirlines(this.selectedEvent.ciavue)
                */

                this.readonly = true
                this.transfer = element
            },
            assignTransfer: function (_date, _ciavue, _nrovue, _save_passengers) {
                localStorage.setItem('prev_event', JSON.stringify(this.selectedEvent))
                let _event = JSON.parse(localStorage.getItem('prev_event'))
                let vm = this

                this.view = 'detail'
                this.transfer = {}
                this.flag_bastar = ''
                this.all_bastars = []
                this.readonly = true
                this.flag_edit = true
                this.type = 'TRAS'

                this.selectedEvent = {
                    // icon: 'icon-car',
                    title: '',
                    _nroite: _event.nroite,
                    fecin: moment(_event.fecout).format('DD/MM/YYYY'),
                    fecout: moment(_event.fecout).format('DD/MM/YYYY'),
                    fecin_prime: moment(_event.fecout).format('YYYY-MM-DD'),
                    horin_flight: moment(_event.horin, 'HH:mm').format('HH:mm'),
                    horout_flight: moment(_event.horout, 'HH:mm').format('HH:mm'),
                    tipsvs_flight: _event.tipsvs,
                    horin: '',
                    horout: '',
                    horin_prime: moment(_event.horout, 'HH:mm').format('HH:mm'),
                    ciuin: _event.ciuin,
                    ciuout: _event.ciuout,
                    nrovue: _event.nrovue,
                    ciavue: _event.ciavue,
                    canpax: _event.canpax,
                    relation: 1,
                    catser: 'TRAS',
                }

                this.setTransfer(this.selectedEvent, true)

                /*
                if(_event.ciudes == 'LIM')
                {
                    Vue.set(this.selectedEvent, 'tipsvs', 'TOT')
                    Vue.set(this.selectedEvent, 'ciuin', _event.ciuin)
                    Vue.set(this.selectedEvent, 'ciuout', _event.ciuin)
                    Vue.set(this.selectedEvent, 'horin', moment(_event.horin, 'HH:mm').format('HH:mm'))
                    Vue.set(this.selectedEvent, 'horout', moment(_event.horin, 'HH:mm').format('HH:mm'))
                }

                if(_event.ciuhas == 'LIM')
                {
                    Vue.set(this.selectedEvent, 'tipsvs', 'TIN')
                    Vue.set(this.selectedEvent, 'ciuin', _event.ciuout)
                    Vue.set(this.selectedEvent, 'ciuout', _event.ciuout)
                    Vue.set(this.selectedEvent, 'horin', moment(_event.horout, 'HH:mm').format('HH:mm'))
                    Vue.set(this.selectedEvent, 'horout', moment(_event.horout, 'HH:mm').add(1, 'hour').format('HH:mm'))
                }
                */

                axios.post(baseExpressURL + 'api/v1/files/' + this.nrofile + '/' + _event.nroite + '/transfer', {
                    date: moment(_date).format('DD/MM/YYYY'),
                    ciavue: _ciavue,
                    nrovue: _nrovue
                })
                    .then(response => {
                        let _response = response.data.data

                        if(_response.length > 1)
                        {
                            _response.forEach((element, e, array) => {

                                if(element.nroite != _event.nroite && (element.tipsvs.trim() == 'TIN' || element.tipsvs.trim() == 'TOT'))
                                {
                                    Vue.set(this.selectedEvent, 'catser', this.type)
                                    Vue.set(this.selectedEvent, 'title', element.title)
                                    Vue.set(this.selectedEvent, 'codsvs', element.codsvs)
                                    Vue.set(this.selectedEvent, 'nroite', element.nroite)

                                    console.log(this.selectedEvent)
                                    console.log(element)

                                    this.transfers.push(element)

                                    if(element.ciavue == this.selectedEvent.ciavue && element.nrovue == this.selectedEvent.nrovue)
                                    {
                                        _event.tipsvs = element.tipsvs
                                        _event.horin = element.horin
                                        _event.horout = element.horout

                                        this.setTransfer(_event, true)
                                    }

                                    if(_save_passengers == true)
                                    {
                                        vm.saveEvent()
                                    }
                                }
                            })
                        }
                        else
                        {
                            /*
                            vm.services.forEach((element, e, array) => {
                                if(element.nroite == _event.nroite)
                                {
                                    setTimeout(function () {
                                        vm.cancelEvent(e)
                                    }, 100)
                                }
                            })
                            */
                        }
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
            },
            saveEvent: function () {
                let vm = this
                vm.loading_button = true
                let _event = vm.selectedEvent

                if(_event.nroite > 0)
                {
                    if(_event.title == '' || _event.title == undefined)
                    {
                        this.$toast.error('Ingrese un título de referencia para el servicio', {
                            position: 'top-right'
                        })

                        vm.loading_button = false

                        return false
                    }
                }
                else
                {
                    if(this.type != 'VUELO')
                    {
                        if(_event.service == '' || _event.service == undefined)
                        {
                            this.$toast.error('Seleccione un servicio para agregar al file.', {
                                position: 'top-right'
                            })

                            vm.loading_button = false

                            return false
                        }
                    }
                    else
                    {

                    }
                }

                if(_event.ciuin == '' || _event.ciuin == undefined || _event.ciuin == null)
                {
                    this.$toast.error('Seleccione una ciudad de origen para guardar el servicio', {
                        position: 'top-right'
                    })

                    vm.loading_button = false

                    return false
                }

                if(_event.ciuout == '' || _event.ciuout == undefined || _event.ciuout == null)
                {
                    this.$toast.error('Seleccione una ciudad de destino para guardar el servicio', {
                        position: 'top-right'
                    })

                    vm.loading_button = false

                    return false
                }

                if(_event.fecin == '')
                {
                    this.$toast.error('Ingrese una fecha de inicio para el servicio.', {
                        position: 'top-right'
                    })

                    vm.loading_button = false

                    return false
                }

                if(_event.catser == 'VUELO')
                {
                    if(typeof _event.ciuin == 'object' && typeof _event.ciuout == 'object')
                    {
                        if(_event.ciuin.codpais == 'PE' && _event.ciuout.codpais == 'PE')
                        {
                            vm.type_service = 0
                        }
                    }
                }

                if(_event.nroite != undefined)
                {
                    axios.post(baseExternalURL + 'api/services/' + _event.codsvs + '/bastar', {
                        service: _event,
                        tipser: _event.catser
                    })
                        .then(response => {
                            console.log(response.data)
                            this.loading_button = false

                            if( typeof this.selectedEvent.bastar ==  'undefined' ||
                                this.selectedEvent.bastar == null || this.selectedEvent.bastar == '')
                            {
                                _event.bastar = this.all_bastars[0].bastar
                            }

                            this.flag_bastar = response.data.bastar
                            this.all_bastars = response.data.bastars

                            // Vue.set(_event, 'bastar', response.data.bastars[0])
                            console.log(_event)

                            if(_event.catser !== 'VUELO')
                            {
                                if(vm.all_bastars.length === 0 || this.flag_bastar == '' || this.all_bastars.length == 0)
                                {
                                    this.$toast.error('No se encontró base de tarifas para el servicio y la cantidad de pasajeros.', {
                                        position: 'top-right'
                                    })
                                    vm.loading_button = false
                                    return false
                                }
                            }

                            _event.tipsvs = (_event.catser !== 'VUELO') ? vm.all_bastars[0].tipsvs : ((vm.type_service == 0) ? 'AEC' : 'AEI')
                            _event.codsvs = (_event.catser !== 'VUELO') ? vm.all_bastars[0].codsvs : ((vm.type_service == 0) ? 'AECFLT' : 'AEIFLT')

                            localStorage.setItem('save_service', 1)

                            if(this.flag_edit)
                            {
                                let _fecin = moment(_event.fecin, "DD/MM/YYYY").toDate().format("DD/MM/YYYY");
                                let _fecout = moment(_event.fecout, "DD/MM/YYYY").toDate().format("DD/MM/YYYY");

                                _event.fecin = _fecin
                                _event.fecout = _fecout
                            }

                            vm.services.forEach((element, e, array) => {

                                if(element.nroite == _event.nroite)
                                {
                                    setTimeout(function () {
                                        vm.cancelEvent(e)
                                    }, 100)
                                }
                            })

                            vm.loading = true

                            axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/service/' + _event.nroite, {
                                event: _event,
                                file: vm.file,
                                lang: vm.lang,
                                flag_edit: vm.flag_edit,
                                bastar: _event.bastar,
                                update_events: vm.update_events,
                                transfer: (_event.catser == 'VUELO' && this.transfer.nroite != undefined) ? this.transfer.nroite : ''
                            })
                                .then(response => {
                                    vm.loading = false
                                    vm.loading_button = false

                                    if(response.data.type == 'success')
                                    {
                                        this.$toast.success('Actualizado correctamente', {
                                            position: 'top-right'
                                        })

                                        vm.selectedEvent.title = response.data.event.title
                                        let __event = localStorage.getItem('prev_event')

                                        if(__event != '' && __event != undefined && __event != null)
                                        {
                                            __event = JSON.parse(__event)

                                            if(__event.nroite != _event.nroite)
                                            {
                                                if(_event.catser != 'TRAS')
                                                {
                                                    vm.$set(vm.selectedEvent, 'tipsvs', response.data.bastars[0].tipsvs)
                                                }
                                            }

                                            vm.searchPassengersByService(__event.nroite, true)
                                        }
                                        else
                                        {
                                            vm.loadServices()

                                            vm.selectedEvent.bastar = response.data.event.bastar
                                            vm.selectedEvent.desbas = response.data.event.desbas
                                            vm.selectedEvent.ciavue = response.data.event.ciavue
                                            vm.selectedEvent.nrovue = response.data.event.nrovue
                                            vm.selectedEvent.fecin = moment(response.data.event.fecin, "DD/MM/YYYY").toDate().format("YYYY-MM-DD")
                                            vm.selectedEvent.fecout = moment(response.data.event.fecout, "DD/MM/YYYY").toDate().format("YYYY-MM-DD")
                                        }
                                    }
                                    else
                                    {
                                        this.$toast.error('Ocurrió un error al guardar el servicio. Por favor, intente nuevamente.', {
                                            position: 'top-right'
                                        })
                                    }
                                    console.log(response)
                                })
                                .catch(error => {
                                    this.loading = false
                                    vm.loading_button = false
                                    console.log(error)
                                    this.$toast.error(this.translations.messages.internal_error, {
                                        position: 'top-right'
                                    })
                                })
                        })
                        .catch(error => {
                            console.log(error)
                        })
                }
                else
                {
                    if(this.type != 'VUELO')
                    {
                        if(this.flag_bastar == '' || this.all_bastars.length == 0)
                        {
                            this.$toast.error('No se encontró base de tarifas para el servicio y la cantidad de pasajeros.', {
                                position: 'top-right'
                            })

                            vm.loading_button = false

                            return false
                        }
                    }

                    localStorage.setItem('save_service', 1)

                    let _fecin = moment(_event.fecin, "DD/MM/YYYY").toDate().format("DD/MM/YYYY");
                    let _fecout = moment(_event.fecout, "DD/MM/YYYY").toDate().format("DD/MM/YYYY");

                    if(typeof this.selectedEvent.bastar ==  'undefined' || this.selectedEvent.bastar == null || this.selectedEvent.bastar == '')
                    {
                        Vue.set(_event, 'bastar', vm.all_bastars[0])
                    }

                    _event.fecin = _fecin
                    _event.fecout = _fecout
                    _event.catser = vm.type
                    let bastar__ = (typeof vm.selectedEvent.bastar == 'object') ? vm.selectedEvent.bastar.bastar : vm.selectedEvent.bastar
                    _event.bastar = (vm.type != 'VUELO') ? bastar__ : 2000
                    _event.tipsvs = (vm.type != 'VUELO') ? vm.all_bastars[0].tipsvs : ((vm.type_service == 0) ? 'AEC' : 'AEI')
                    _event.codsvs = (vm.type != 'VUELO') ? vm.all_bastars[0].codsvs : ((vm.type_service == 0) ? 'AECFLT' : 'AEIFLT')

                    vm.loading = true

                    axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/service', {
                        event: _event,
                        file: vm.file,
                        lang: vm.lang,
                        flag_edit: vm.flag_edit,
                        bastar: _event.bastar
                    })
                        .then(response => {
                            vm.loading_button = false
                            vm.loading = false

                            if(response.data.type === 'success')
                            {
                                vm.loading_button = false
                                vm.loading = false
                                vm.flag_edit = false
                                // vm.loadServices()

                                vm.$toast.success('Servicio guardado correctamente..', {
                                    position: 'top-right'
                                })

                                vm.$set(vm, 'selectedEvent', response.data.event)
                                vm.$set(vm.selectedEvent, 'catser', vm.type)
                                vm.$set(vm.selectedEvent, 'fecin_prime', moment(response.data.event.fecin, "DD/MM/YYYY").toDate().format("YYYY-MM-DD"))
                                vm.$set(vm.selectedEvent, 'fecin', moment(response.data.event.fecin, "DD/MM/YYYY").toDate().format("YYYY-MM-DD"))
                                vm.$set(vm.selectedEvent, 'fecout', moment(response.data.event.fecout, "DD/MM/YYYY").toDate().format("YYYY-MM-DD"))

                                setTimeout(function () {
                                    _event = localStorage.getItem('prev_event')

                                    if(_event == undefined || _event == '' || _event == null)
                                    {
                                        localStorage.setItem('prev_event', JSON.stringify(vm.selectedEvent))

                                        vm.showModal('modalAlertaPaxsSave')
                                        localStorage.setItem('flag_save_passengers', 1)
                                    }
                                    else
                                    {
                                        if(typeof _event == 'string')
                                        {
                                            _event = JSON.parse(_event)
                                        }

                                        vm.searchPassengersByService(_event.nroite, true)
                                    }
                                }, 10)

                                /*
                                axios.post(baseExpressURL + 'api/v1/files/' + vm.nrofile + '/passengers/' + response.data.event.nroite, {
                                    nrosec_passengers: vm.check_passengers_service,
                                    passengers: vm.all_passengers,
                                    tipsvs: response.data.event.tipsvs,
                                    codsvs: response.data.event.codsvs,
                                    bastar: _event.bastar,
                                })
                                    .then(response => {
                                        vm.loading_button = false
                                        vm.flag_edit = false

                                        vm.$toast.success('Pasajeros asignados correctamente..', {
                                            position: 'top-right'
                                        })
                                    })
                                    .catch(response => {
                                        console.log(error)
                                        vm.loading_button = false
                                        vm.$toast.error(this.translations.messages.internal_error, {
                                            position: 'top-right'
                                        })
                                    })
                                 */
                            }
                            else
                            {
                                vm.$toast.error('Ocurrió un error al guardar el servicio. Por favor, intente nuevamente.', {
                                    position: 'top-right'
                                })
                            }
                            console.log(response)
                        })
                        .catch(error => {
                            vm.loading = false
                            vm.loading_button = false
                            console.log(error)
                            vm.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        })
                }
            },
            showComponents: function () {
                this.loading_button = true
                this.components = []

                axios.get(baseExternalURL+ 'api/files/' + this.nrofile + '/services/' + this.selectedEvent.nroite + '/components')
                // axios.post(baseExpressURL + 'api/v1/services/' + this.nrofile + '/components/' + this.selectedEvent.nroite)
                    .then(response => {
                        this.loading_button = false

                        if(response.data.data.length > 0)
                        {
                            this.components = this.translate_data_services( response.data.file, response.data.data, 1 )
                        }
                        this.view = 'components'
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                    })
            },
            searchServicesComponent: function (search, loading) {
                this.all_services = []
                // t03
                axios.post(baseExternalURL + 'api/search_services_component', {
                    query: search.toUpperCase(),
                    ciuin: this.selectedEvent.ciuin,
                    ciuout: this.selectedEvent.ciuout
                })
                    .then(response => {
                        this.all_services = response.data
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            searchRatesByService: function (search, loading) {
                this.all_bastars = []

                console.log(this.component_editable.codsvs)
                console.log(this.component_editable.service)

                if(typeof this.component_editable.service == 'undefined' && typeof this.component_editable.codsvs == 'undefined')
                {
                    this.$toast.error('Seleccione un servicio para filtrar las tarifas.', {
                        position: 'top-right'
                    })

                    return false;
                }
                // t03 t04
                axios.post(baseExternalURL + 'api/search_rates_services', {
                    service: (typeof this.component_editable.codsvs == 'undefined') ? this.component_editable.service.codigo : this.component_editable.codsvs,
                    query: (search != undefined) ? search.toUpperCase() : '',
                    fecin: this.selectedEvent.fecin,
                    fecout: this.selectedEvent.fecout
                })
                    .then(response => {
                        this.all_bastars = response.data
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            cancelComponent: function (_view) {
                this.component_editable = {}

                if(_view != undefined)
                {
                    this.showComponents()
                }
            },
            addComponent: function () {
                this.flag_bastar = ''
                this.all_bastars = []
                this.all_services = []

                this.components.push({
                    nroite: 0,
                    fecin: moment(this.selectedEvent.fecin).format("DD/MM/YYYY")
                })
                this.component_editable = this.components[this.components.length - 1]
            },
            editComponent: function (_component) {
                this.component_editable = _component
                this.component_editable.fecin = moment(_component.fecin).format("DD/MM/YYYY");
            },
            deleteComponent: function (_component) {
                axios.post(baseExternalURL + 'api/delete_component', {
                    file: this.nrofile,
                    component: _component.nroite,
                    file_service_id: _component.id
                })
                    .then(response => {

                        if(response.data.type == 'success')
                        {
                            this.component_editable = {}
                            this.$toast.success('El componente se eliminó correctamente.', {
                                position: 'top-right'
                            })

                            this.showComponents()
                        }
                        else
                        {
                            this.$toast.error(response.data.message, {
                                position: 'top-right'
                            })
                        }
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            saveComponent: function () {
                axios.post(baseExternalURL + 'api/save_component', {
                    file: this.file,
                    event: this.selectedEvent,
                    component: this.component_editable
                })
                    .then(response => {

                        if(response.data.type === 'success')
                        {
                            this.component_editable = {}
                            this.$toast.success('El componente se guardó correctamente.', {
                                position: 'top-right'
                            })

                            this.showComponents()
                        }
                        else
                        {
                            this.$toast.error(response.data.message, {
                                position: 'top-right'
                            })
                        }
                        // this.all_services = response.data
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                        console.log(error)
                        this.loading = false
                    })
            },
            getClassHotelByClientId: function () {
                let _client_id = localStorage.getItem('client_id')

                axios.get('api/client_hotels/classes?client_id=' + _client_id + '&lang=' + this.lang)
                    .then((result) => {
                        if (result.data.success) {
                            this.classes_hotel = result.data.data
                        }
                    }).catch((e) => {
                    if( e.response.status === 401 ){
                        // window.location = "/"
                        window.location = window.location.href
                        // window.location = '/error?type=expired&lang='+this.lang+'&redirect=' + window.location.href
                    }
                    console.log(e.response.status);
                    console.log(e);
                })
            },
            searchDestinationsFrom: function (search, loading) {
                console.log(search)
                console.log(this.type)
                console.log(this.destinations_from)

                let _client_id = localStorage.getItem('client_id')

                if(this.type == 'HOT')
                {
                    axios.get('services/hotels/destinations?client_id=' + _client_id)
                        .then((result) => {
                            this.destinations_from = result.data
                            // this.destinies_select = result.data
                        }).catch((e) => {
                        console.log(e)
                    })
                }
                else
                {
                    if (typeof search != 'object') {
                        let _search = (search != '' && search != undefined) ? search.toUpperCase() : ''
                        // t01
                        axios.get(baseExpressURL + 'api/v1/flights/origins?type=' + this.type_service + '&term=' + _search)
                            .then(response => {
                                this.destinations_from = response.data.data

                                if (localStorage.getItem('set_from') != '' && localStorage.getItem('set_from') != undefined) {
                                    if (_search != '') {
                                        console.log(this.destinations_from)
                                        Vue.set(this.selectedEvent, 'ciuin', this.destinations_from[0])
                                        // this.selectedEvent.ciuin = this.destinations_from[0]
                                        localStorage.setItem('set_from', '')
                                    }
                                }
                            })
                            .catch(error => {
                                this.$toast.error(this.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                                console.log(error)
                            })
                    }
                }
            },
            searchDestinationsTo: function (search, loading) {
                console.log(search)

                if(typeof search != 'object')
                {
                    let _search = (search != '' && search != undefined) ? search.toUpperCase() : ''
                    // t01
                    axios.get(baseExpressURL + 'api/v1/flights/origins?type=' + this.type_service + '&term=' + _search)
                        .then(response => {
                            this.destinations_to = response.data.data

                            if(localStorage.getItem('set_to') != '' && localStorage.getItem('set_to') != undefined)
                            {
                                if(_search != '')
                                {
                                    console.log(this.destinations_to)
                                    Vue.set(this.selectedEvent, 'ciuout', this.destinations_to[0])
                                    localStorage.setItem('set_to', '')
                                }
                            }
                        })
                        .catch(error => {
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                            console.log(error)
                        })
                }
            },
            searchServices: function (search, loading) {
                this.all_services = []
                let vm = this

                if(search == '')
                {
                    search = this.query
                }

                if(search != '' && search != undefined)
                {
                    this.hover_service = false
                    this.loading_services = true

                    if(vm.timeout_query != '')
                    {
                        clearTimeout(vm.timeout_query)
                    }

                    vm.timeout_query = setTimeout(function () {
                        // t03
                        axios.post(baseExternalURL + 'api/search_services_by_types', {
                            type: vm.type,
                            filter_service: vm.filter_service,
                            query: search.toUpperCase(),
                            ciuin: vm.selectedEvent.ciuin,
                            ciuout: vm.selectedEvent.ciuout,
                            fecin: vm.selectedEvent.fecin,
                            fecout: vm.selectedEvent.fecout
                        })
                            .then(response => {
                                vm.loading_services = false
                                vm.all_services = response.data
                            })
                            .catch(error => {
                                vm.$toast.error(vm.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                                console.log(error)
                                vm.loading = false
                            })
                    }, 1000);
                }
            },
            cancelEvent: function (e) {
                let vm = this

                if(vm.flag_edit)
                {
                    if(localStorage.getItem('save_service') == 1)
                    {
                        let _event = vm.selectedEvent

                        let _fecin = moment(_event.fecin, "DD/MM/YYYY").toDate().format("YYYY-MM-DD");
                        let _fecout = moment(_event.fecout, "DD/MM/YYYY").toDate().format("YYYY-MM-DD");

                        _event.fecin = _fecin
                        _event.fecout = _fecout

                        if(e != undefined)
                        {
                            _event.start = _fecin + ' ' + _event.horin
                            _event.end = _fecout + ' ' + _event.horout

                            vm.$set(vm.services, e, _event)
                        }

                        vm.$set(vm, 'selectedEvent', _event)
                    }
                    else
                    {
                        if(vm.selectedEvent.nroite != undefined)
                        {
                            let _event = localStorage.getItem('service_prev')

                            if(_event != '' && _event != null)
                            {
                                _event = JSON.parse(_event)
                                vm.$set(vm, 'selectedEvent', _event)

                                vm.services.forEach((element, e, array) => {
                                    if(element.nroite == _event.nroite)
                                    {
                                        _event.start = element.start
                                        _event.end = element.end

                                        setTimeout(function () {
                                            vm.$set(vm.services, e, _event)
                                        }, 100)
                                    }
                                })
                            }
                        }
                    }

                    localStorage.setItem('save_service', 0)
                    vm.flag_edit = false
                }
            },
            reset: function (_field) {
                let vm = this
                vm.$set(vm.selectedEvent, _field, '')
                vm.$set(vm, 'readonly', false)
                vm.$set(vm, 'query', '')
                vm.$set(vm, 'all_services', [])
                vm.$set(vm, 'readonly_hours', false)
                // vm.selectedEvent[_field] = undefined

                if(_field == 'ciuin' || _field == 'ciuout')
                {
                    vm.$set(vm.selectedEvent, 'service', '')

                    if(_field == 'ciuin')
                    {
                        vm.searchDestinationsFrom('')
                    }

                    if(_field == 'ciuout')
                    {
                        vm.searchDestinationsTo('')
                    }
                }

                if(_field == 'service')
                {
                    vm.$set(vm.selectedEvent, 'ciuin', '')
                    vm.$set(vm.selectedEvent, 'ciuout', '')
                    vm.$set(vm.selectedEvent, 'bastar', '')
                    vm.$set(vm, 'all_bastars', [])

                    vm.searchDestinationsFrom('')
                    vm.searchDestinationsTo('')
                }
            },
            deleteEvent: function () {


                console.log(this.selectedEvent)
                console.log('olass')

                if(this.selectedEvent._nroite != undefined)
                {
                    let _event = JSON.parse(localStorage.getItem('prev_event'))
                    this.selectedEvent = _event
                    this.flag_edit = false
                    this.readonly = false

                    localStorage.setItem('prev_event', '')
                }
                else
                {
                    this.onEventDelete({ event: this.selectedEvent })
                }
            },
            modalPassengers: function (file, paxs, canadl, canchd, caninf) {
                this.view = ''
                localStorage.setItem('search_passengers', 1)
                this.$refs.modal_passengers.modalPassengers('file', file, parseFloat(paxs), parseFloat(canadl), parseFloat(canchd), parseFloat(caninf))
            },
            passengerAccommodation: function () {
                this.view = ''
                localStorage.setItem('search_passengers', 1)
                this.$refs.accommodation_passengers.accommodationPassengers('file', this.file, this.hotels)
            },
            validateHotel: function (services) {
                let vm = this

                let nroites_ = []
                let nroites_array = []
                services.forEach((item, i) => {
                    if(item.catser === 'HOT')
                    {
                        item.variations.forEach((variation, v) => {{
                            if( nroites_array[variation.nroite] === undefined ){
                                nroites_array[variation.nroite] = []
                                nroites_.push(variation.nroite)
                            }
                        }})
                    }
                })

                console.log(nroites_array)
                console.log(nroites_)
                // t13g
                axios.post(baseExpressURL + 'api/v1/files/' + vm.nrofile + '/rooms', { nroites: nroites_ })
                    .then((response_) => {
                        console.log(response_.data.data)
                        response_.data.data.forEach((room)=>{
                            nroites_array[ room.nroite ].push(room)
                        })

                        console.log(nroites_array)

                        services.forEach((item, i) => {
                            if(item.catser === 'HOT')
                            {
                                let ignore = [];
                                let nroite = item.nroite;

                                vm.services[i].flag_acomodo = 1
                                vm.$set(vm.passengers_event, nroite, [])
                                vm.$set(vm.services[i], 'check_cancel', 'OK')

                                let rooms_mysql = item.all_rooms_mysql

                                item.variations.forEach((variation, v) => {
                                    vm.$set(vm.services[i].variations[v], 'check_cancel', 'OK')
                                    // // t13g
                                    // axios.get(baseExpressURL + 'api/v1/files/' + vm.nrofile + '/' + variation.nroite + '/room')
                                    //     .then((response) => {

                                    let _rooms = nroites_array[ variation.nroite ]

                                    vm.$set(vm.services[i].variations[v], 'rooms', _rooms)

                                    if(_rooms.length == 0)
                                    {
                                        vm.$set(vm.passengers_event, nroite, vm.all_passengers)
                                    }

                                    _rooms.forEach((room, r) => {

                                        if(room.estado == 'RQ')
                                        {
                                            vm.$set(vm.services[i].variations[v], 'check_cancel', 'RQ')
                                            vm.$set(vm.services[i], 'check_cancel', 'RQ')
                                            // vm.$set(vm.services[i].variations[v], 'estado', 'RQ')
                                        }

                                        if(room.tipfil.trim() == 'GASCAN')
                                        {
                                            vm.$set(vm.services[i].variations[v], 'anulado', 1)
                                            vm.$set(vm.services[i], 'anulado', 1)
                                        }

                                        rooms_mysql.forEach((room_mysql, rm) => {
                                            if(room_mysql.id == room.codrsv)
                                            {
                                                vm.$set(vm.services[i].variations[v].rooms[r], 'room_mysql', room_mysql)
                                                let policies_cancellation = JSON.parse(room_mysql.policies_cancellation)
                                                vm.$set(vm.services[i].variations[v].rooms[r], 'policies_cancellation', policies_cancellation[0])
                                            }
                                        })

                                        vm.$set(vm.rooms, room.nroite, {})

                                        // axios.get(baseExpressURL + 'api/v1/files/' + vm.nrofile + '/' + variation.codsvs + '/' + room.nroite + '/room/' + (r + 1))
                                        axios.get(baseExternalURL + 'api/files/services/' + variation.id + '/accommodations/room/'+(r + 1))
                                            .then((response) => {
                                                let _passengers = vm.translate_data_accommodations(vm.nrofile, variation.codsvs, room.nroite, variation.clase, response.data.data)
                                                vm.$set(vm.rooms[room.nroite], (r + 1), _passengers)

                                                if(_passengers.length > 0)
                                                {
                                                    vm.services[i].flag_acomodo = 0
                                                }

                                                _passengers.forEach((passenger, p) => {
                                                    ignore.push(passenger.nrosec)
                                                })

                                                if(_rooms.length == (r + 1))
                                                {
                                                    setTimeout(function () {
                                                        let indice = 0;
                                                        vm.all_passengers.forEach((passenger, p) => {
                                                            if(!ignore.includes(passenger.nrosec))
                                                            {
                                                                vm.$set(vm.passengers_event[nroite], indice, passenger)
                                                                indice++
                                                            }
                                                        })
                                                    }, 1000)
                                                }
                                            })
                                            .catch((response) => {
                                                console.log(response)
                                            })
                                    })
                                })



                            }
                        })

                    })

            },
            orderServices: function () {
                this.showLoader('Cargando...')
                this.services = []
                this.loading_button = true
                this.loading = true

                // apunta al front, consultas al back reservations_hotels y reformato de campos en mismo front
                axios.post(baseURL + 'order_services_file', {
                    lang: this.lang,
                    services: this.services_ws,
                    city: this.filter_city,
                    filters: this.filters
                }).then((response) => {
                    this.hideLoader()
                    this.loading = false
                    this.loading_button = false
                    let vm = this

                    setTimeout(function () {
                        vm.loadServices()
                    }, 5000)

                    vm.$set(vm, 'filter_cities', response.data.cities)
                    vm.$set(vm, 'services', response.data.services)
                    vm.$set(vm, 'hotels', response.data.hotels)
                    vm.$set(vm, 'type_room', response.data.type_room)

                    vm.validateHotel(vm.services)
                }).catch((response) => {
                    this.loading_button = false
                    this.hideLoader()
                    console.log(response.data)
                    this.services = []
                })
            },
            loadServices: function () {
                let vm = this

                setTimeout(function () {
                    if(vm.services_ws.length == 0)
                    {
                        vm.services_ws = []
                        vm.showLoader('Cargando...')
                        vm.loading_button = true
                    }

                    axios.get(baseExternalURL + 'api/files/' + vm.nrofile + '/services')
                    // axios.get(baseExpressURL + 'api/v1/files/' + vm.nrofile + '/services')
                        .then(response => {

                            if(vm.services_ws.length == 0)
                            {
                                vm.hideLoader()
                                vm.loading_button = false
                            }
                            //** GENERA BUCLE
                            // setTimeout(function () {
                            //     vm.services_ws = vm.translate_data_services( response.data.file, response.data.data, 0 )
                            //     vm.orderServices()
                            // }, 10)
                            // **

                            if(vm.services_ws.length != response.data.data.length)
                            {
                                setTimeout(function () {
                                    vm.services_ws = vm.translate_data_services( response.data.file, response.data.data, 0 )
                                    console.log(vm.services_ws)
                                    vm.orderServices()
                                }, 10)
                            }
                            else
                            {
                                setTimeout(function () {
                                    // vm.loadServices() // BUCLE
                                }, 3000)
                            }

                        })
                        .catch(error => {
                            console.log(error)

                            if(vm.services_ws.length == 0)
                            {
                                vm.hideLoader()
                                vm.loading_button = false
                            }

                            setTimeout(function () {
                                // vm.loadServices() // BUCLE
                            }, 5000)

                            vm.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        })
                }, 100)
            },
            searchPassengers: function (_loading, update) {
                this.loading_passengers = true
                this.all_passengers = []
                // axios.get(baseExpressURL + 'api/v1/passenger/conpax?nroref=' + this.nrofile)
                axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/passengers')
                    .then(response => {

                        this.loading_passengers = false

                        if(response.data.data.length > 0)
                        {
                            this.all_passengers = this.translate_data_passengers(response.data.data)
                            localStorage.setItem('all_passengers', JSON.stringify(this.all_passengers))
                        }

                        this.show_file(_loading, update, false)
                    })
                    .catch(response => {
                        console.log(error)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },
            translate_data_services(file_, services, full){

                let services_ = []

                services.forEach( (data)=>{
                    let data_ = {
                        id: data.id,
                        clase: data.classification_iso,
                        codsvs: data.code,
                        clasif: data.classification,
                        nroite: data.item_number,
                        itepaq: data.item_number_parent,
                        flag_acomodo: data.flag_accommodation,
                        nroref: file_.file_number,
                        cantid: data.total_rooms,
                        preped: data.code_request_book,
                        prefac: data.code_request_invoice,
                        prevou: data.code_request_voucher,
                        estado: data.status_ifx,
                        estado_hotel: data.status_hotel,
                        codcfm: data.confirmation_code,
                        desbas_inicial: data.base_name_initial,
                        infoad: data.additional_information,
                        canpax: data.total_paxs,
                        categoria_hotel: data.category_hotel_name,
                        anulado: data.number_annulments,
                        desbas: data.base_name_original,
                        relation: data.relation_nights,
                        razon: data.airline_name,
                        ciavue: data.airline_code,
                        nrovue: data.airline_number,
                        canadl: file_.adults,
                        canchd: file_.children,
                        caninf: file_.infants,
                        catser: data.category_code_ifx,
                        tipsvs: data.type_code_ifx,
                        bastar: data.base_code,
                        descri: data.description,
                        horin_prime: data.start_time,
                        descri_es: data.description_ES,
                        flag_es: data.description_ES_code,
                        descri_en: data.description_EN,
                        flag_en: data.description_EN_code,
                        descri_pt: data.description_PT,
                        flag_pt: data.description_PT_code,
                        descri_it: data.description_IT,
                        flag_it: data.description_IT_code,
                        ciuin: data.city_in_iso,
                        descri_ciudad: data.city_name,
                        descri_pais: data.country_name,
                        fecin: data.date_in.slice(0, 10),
                        horin: data.start_time,
                        ciuout: data.city_out_iso,
                        fecout: data.date_out.slice(0, 10),
                        horout: data.departure_time
                    }
                    if(full){
                        data_.nroope = data.operation_number;
                        data_.fecope = data.operation_date;
                        data_.tipope = data.operation_type;
                        data_.tippax = data.passenger_type;
                        data_.secpax = data.passenger_sequence_number;
                        data_.paiin = data.starting_country_iso_ifx;
                        data_.zonin = data.start_zone_ifx;
                        data_.gruin = data.starting_country_grouping;
                        data_.paiout = data.out_country_iso_ifx;
                        data_.zonout = data.out_zone_ifx;
                        data_.gruout = data.out_country_grouping;
                        data_.moneda = data.currency;
                        data_.monvta  = data.currency_sale;
                        data_.moncos = data.currency_cost;
                        data_.vtaloc = data.amount_sale;
                        data_.cosloc = data.amount_cost;
                        data_.vtauni = data.amount_sale_unit;
                        data_.cosuni = data.amount_cost_unit;
                        data_.grvuni = data.taxed_unit_sale;
                        data_.grcuni = data.taxed_unit_cost;
                        data_.ivvuni = data.unit_sale_taxes;
                        data_.ivcuni = data.unit_cost_taxes;
                        data_.diario = data.mode_calculation_days;
                        data_.paxuni = data.mode_calculation_paxs;
                        data_.cansvs = data.total_services;
                        data_.tarifa = data.total_amount;
                        data_.netpag = data.total_amount_provider;
                        data_.piaced = data.markup_created;
                        data_.iatced = data.total_amount_created;
                        data_.netfac = data.total_amount_invoice;
                        data_.netgra = data.total_amount_taxed;
                        data_.netexe = data.total_amount_exempt;
                        data_.tax3 = data.taxes;
                        data_.cotiza = data.use_quote;
                        data_.vouche = data.use_voucher;
                        data_.itiner = data.use_itinerary;
                        data_.vouemi = data.voucher_sent;
                        data_.nrovou = data.voucher_number;
                        data_.ticket = data.use_ticket;
                        data_.tktemi = data.ticket_sent;
                        data_.docum = data.use_accounting_document;
                        data_.docemi = data.accounting_document_sent;
                        data_.nrosuc = data.branch_number;
                        data_.serie = data.serie;
                        data_.tipdoc = data.document_type;
                        data_.nrodoc = data.document_number;
                        data_.docupr = data.document_skeleton;
                        data_.doprem = data.document_purchase_order;
                        data_.nropro = data.lending_accountant;
                        data_.viacom = data.reservation_for_send;
                        data_.comemi = data.reservation_sent;
                        data_.asigna = data.provider_for_assign;
                        data_.asiemi = data.provider_assigned;
                    }
                    services_.push(data_)
                } )

                return services_
            },
            translate_data_passengers(passengers){

                let passengers_ = []

                passengers.forEach( (data)=>{
                    let data_ = {
                        id: data.id,
                        nroemp: 5,
                        identi: "R",
                        nroref: data.reservation.file_code,
                        nrosec: data.sequence_number,
                        nroord: data.order_number,
                        nropax: data.frequent,
                        nombre: data.name,
                        tipo: data.type,
                        sexo: data.genre,
                        fecnac: data.date_birth,
                        ciunac: data.city_iso,
                        nacion: data.country_iso,
                        tipdoc: data.doctype_iso,
                        nrodoc: data.document_number,
                        tiphab: data.suggested_room_type,
                        status: "OK",
                        correo: data.email,
                        celula: data.phone,
                        resmed: data.medical_restrictions,
                        resali: data.dietary_restrictions,
                        observ: data.notes
                    }
                    passengers_.push(data_)
                } )

                return passengers_
            },
            translate_data_accommodations(nrofile, codsvs, nroite, clase, data){

                let response = []

                data.forEach( (data)=>{
                    let data_ = {
                        id: data.id,
                        nroemp: 5,
                        ideref: "R",
                        nroref: nrofile,
                        nroite: nroite,
                        tipsvs: clase,
                        codsvs: codsvs,
                        nrosec: data.passenger.sequence_number,
                        nombre: data.passenger.name + ' ' + data.passenger.surnames,
                        nrohab: data.room_key
                    }
                    response.push(data_)
                } )

                return response
            },
            searchPassengersByService: function (_nroite, _save) {
                this.passengers_service = []
                this.check_passengers_service = []
                this.all = false

                // axios.get(baseExpressURL + 'api/v1/files/' + this.nrofile + '/passengers/' + _nroite)
                axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/accommodations/service?item_number=' + _nroite)
                    .then(response => {

                        if(response.data.data.length > 0)
                        {
                            this.passengers_service = response.data.data
                        }

                        this.validatePassengerInService(_save)
                    })
                    .catch(response => {
                        console.log(error)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },
            showPassengersService: function () {
                let vm = this

                let _event = localStorage.getItem('prev_event')

                if(_event != '' && _event != null)
                {
                    _event = JSON.parse(_event);
                    vm.searchPassengersByService(_event.nroite, true)
                }
                else
                {
                    vm.searchPassengersByService(_event.nroite)
                    vm.loading = false
                    vm.$set(vm, 'view', 'passengers')
                    // Mostrar alerta para la asignación de pasajeros..
                }
            },
            savePassengersService: function () {

                let vm = this
                this.loading_button = true
                let _passengers = this.cantidad_total_paxs

                if(_passengers > 0)
                {
                    this.selectedEvent.canpax = (_passengers > 0) ? _passengers : this.file.paxs

                    axios.post(baseExternalURL + 'api/services/' + this.selectedEvent.codsvs + '/bastar', {
                        service: this.selectedEvent,
                        tipser: this.selectedEvent.catser
                    })
                        .then(response => {
                            this.flag_bastar = response.data.bastar
                            this.all_bastars = response.data.bastars

                            if( typeof this.selectedEvent.bastar ==  'undefined' || this.selectedEvent.bastar == null ||
                                this.selectedEvent.bastar == '')
                            {
                                Vue.set(this.selectedEvent, 'bastar', this.flag_bastar)
                            }

                            if(this.all_bastars.length > 0)
                            {
                                console.log("Bastar al momento de guardar un servicio..")
                                console.log(this.selectedEvent.bastar)
                                console.log(this.selectedEvent)

                                // axios.post(baseExpressURL + 'api/v1/files/' + this.nrofile + '/passengers/' + this.selectedEvent.nroite, {
                                axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/passengers/service/' + this.selectedEvent.nroite, {
                                    nrosec_passengers: this.check_passengers_service,
                                    passengers: this.all_passengers,
                                    tipsvs: this.selectedEvent.tipsvs,
                                    codsvs: this.selectedEvent.codsvs,
                                    bastar: (typeof this.selectedEvent.bastar == 'object') ? this.selectedEvent.bastar.bastar : this.selectedEvent.bastar
                                    // transfer: (this.transfer.nroite != undefined) ? this.transfer.nroite : ''
                                })
                                    .then(response => {
                                        this.loading_button = false
                                        this.flag_edit = false

                                        let _event = localStorage.getItem('prev_event')

                                        if(_event != undefined && _event != '' && _event != null)
                                        {
                                            this.view = 'detail'

                                            console.log(typeof _event)

                                            if(typeof _event == 'string')
                                            {
                                                _event = JSON.parse(_event)
                                            }

                                            Vue.set(this, 'selectedEvent', _event)

                                            localStorage.setItem('prev_event', '')
                                            this.loadServices()
                                        }
                                        else
                                        {
                                            if(this.selectedEvent.catser == 'VUELO')
                                            {
                                                this.searchPassengersByService(this.selectedEvent.nroite, false) // Actualizando lista de pasajeros..
                                                // this.saveEvent()

                                                if((this.selectedEvent.ciavue != '' && this.selectedEvent.ciavue != undefined && this.selectedEvent.ciavue != null) && (this.selectedEvent.nrovue != '' && this.selectedEvent.nrovue != undefined && this.selectedEvent.nrovue != null))
                                                {
                                                    this.assignTransfer(this.selectedEvent.fecin, this.selectedEvent.ciavue, this.selectedEvent.nrovue, true)
                                                }
                                            }
                                            else
                                            {
                                                this.view = 'detail'
                                            }
                                        }

                                        this.$toast.success('Pasajeros asignados correctamente..', {
                                            position: 'top-right'
                                        })

                                        if(localStorage.getItem('flag_save_passengers') == 1)
                                        {
                                            localStorage.setItem('flag_save_passengers', '')
                                            // this.loadServices()
                                            // this.closeModal('modal')
                                        }
                                    })
                                    .catch(error => {
                                        console.log(error)
                                        this.loading_button = false
                                        this.$toast.error(this.translations.messages.internal_error, {
                                            position: 'top-right'
                                        })
                                    })
                            }
                            else
                            {
                                this.loading_button = false

                                this.$toast.error('No se encontró base de tarifas para el servicio y la cantidad de pasajeros.', {
                                    position: 'top-right'
                                })

                                return false
                            }
                        })
                        .catch(error => {
                            console.log(error)
                        })
                }
                else
                {
                    this.$toast.error('Debe seleccionar al menos un pasajero para realizar la asignación', {
                        position: 'top-right'
                    })
                }
            },
            validatePassengerInService: function (_save) {
                let vm = this

                setTimeout(function () {
                    vm.cantidad_total_paxs = 0

                    vm.all_passengers.forEach((element, e, array) => {
                        if(vm.passengers_service.length > 0)
                        {
                            let flag = true
                            vm.passengers_service.forEach((el, i, arr) => {

                                if(flag == true)
                                {
                                    if(element.nrosec == el.nrosec)
                                    {
                                        flag = false
                                        vm.cantidad_total_paxs += 1
                                        vm.$set(vm.check_passengers_service, e, 1)
                                    }
                                    else
                                    {
                                        vm.$set(vm.check_passengers_service, e, 0)
                                    }
                                }
                            })
                        }
                        else
                        {
                            vm.$set(vm, 'all', true)
                            vm.cantidad_total_paxs += 1
                            vm.$set(vm.check_passengers_service, e, 1)
                        }
                    })

                    if(_save == true)
                    {
                        vm.closeModal('modalAlertaPaxsSave')

                        setTimeout(function () {
                            vm.savePassengersService()
                        }, 1000)
                    }

                }, 10)
            },
            togglePassengers: function () {
                let vm = this

                vm.all_passengers.forEach((element, e, array) => {
                    vm.$set(vm.check_passengers_service, e, ((vm.all) ? 1 : 0))
                })
            },
            updatePassengersList: function () {
                this.cantidad_total_paxs = 0

                this.check_passengers_service.forEach((element, e, array) => {
                    if(element == 1)
                    {
                        this.cantidad_total_paxs += 1
                    }
                })
            },
            showPassengersService: function () {
                if(this.all_passengers.length > 0)
                {
                    this.view = 'passengers'
                }
                else
                {
                    let vm = this
                    vm.closeModal('modal')
                    vm.modalPassengers(this.nrofile, this.file.paxs, this.file.canadl, this.file.canchd, this.file.caninf)

                    setTimeout(function () {
                        vm.selectedEvent = {}
                    }, 10)
                }
            },

            onEventClick: function (event, e) {
                localStorage.setItem('prev_event', '')

                event.see_preview_communications = 0

                console.log(event)
                this.selectedEvent = event
                this.selectedEvent.type_destination = 0
                this.view = 'detail'
                this.flag_flight = false
                this.flag_edit = false
                this.readonly = false
                this.flag_bastar = ''
                this.all_bastars = []
                this.all_services = []
                this.variations = {}
                this.all_variations = false
                // this.rooms = {}
                this.showModal('modal')
                this.searchPassengersByService(this.selectedEvent.nroite)

                if(this.selectedEvent.nroite != undefined)
                {
                    this.type = this.selectedEvent.catser

                    if(this.selectedEvent.catser == 'TRAS')
                    {
                        let _date = moment(this.selectedEvent.fecin).format('DD/MM/YYYY')
                        this.searchTransfers(_date)
                    }

                    axios.post(baseExternalURL + 'api/services/' + this.selectedEvent.codsvs + '/bastar', {
                        service: this.selectedEvent,
                        tipser: this.selectedEvent.catser
                    })
                        .then(response => {
                            this.flag_bastar = response.data.bastar
                            this.all_bastars = response.data.bastars

                            this.all_bastars.forEach((element, e, array) => {
                                if(element.bastar == this.selectedEvent.bastar)
                                {
                                    Vue.set(this.selectedEvent, 'bastar', element)
                                }
                            })
                        })
                        .catch(error => {
                            console.log(error)
                        })

                    localStorage.setItem('service_prev', JSON.stringify(this.selectedEvent))
                }
                e.stopPropagation()
            },
            onEventCreate: function (event, deleteEventFunction) {
                localStorage.setItem('_service', '')
                localStorage.setItem('prev_event', '')
                this.readonly = false
                this.flag_flight = false
                this.flag_edit = true // Opcion para editar el evento..
                this.hover_service = false
                this.content_restrictions = ''
                this.content_remarks = ''
                this.loading_remarks = false
                this.loading_restrictions = false
                this.all_bastars = []
                this.flag_bastar = ''
                this.airlines = []
                this.view = 'detail'
                this.query = ''
                this.all_services = []
                this.url_hotel = ''

                let _event = event
                let _start = moment(_event.start).format('HH:mm')
                let _end = moment(_event.end).format('HH:mm')
                let _fecin = moment(_event.start).format('DD/MM/YYYY')
                let _fecout = moment(_event.end).format('DD/MM/YYYY')

                _event.horin = _start
                _event.horout = _end
                _event.fecin = _fecin
                _event.fecout = _fecout
                _event.canpax = this.file.paxs

                this.selectedEvent = _event
                this.selectedEvent.type_destination = 0
                this.showModal('modal')
                this.deleteEventFunction = deleteEventFunction

                this.passengers_service = []
                this.validatePassengerInService()

                this.searchDestinationsFrom('')
                this.searchDestinationsTo('')
            },
            onEventChange: function (data) {

                let vm = this
                vm.flag_edit = false

                if(data.event.nroite != undefined)
                {
                    let _start = moment(data.event.start).format('HH:mm')
                    let _end = moment(data.event.end).format('HH:mm')
                    let _fecin = moment(data.event.start).format('YYYY-MM-DD')
                    let _fecout = moment(data.event.end).format('YYYY-MM-DD')
                    let _event = data.event

                    vm.services.forEach((element, e, array) => {

                        if(element.nroite == _event.nroite)
                        {
                            _event.horin = _start
                            _event.horout = _end
                            _event.fecin = _fecin
                            _event.fecout = _fecout

                            vm.$set(vm.services, e, _event)
                        }
                    })

                    axios.post(baseExternalURL + 'api/services/' + _event.codsvs + '/bastar', {
                        service: _event,
                        tipser: _event.catser
                    })
                        .then(response => {
                            vm.flag_bastar = response.data.bastar
                            vm.all_bastars = response.data.bastars

                            console.log(vm.all_bastars)

                            if(typeof vm.all_bastars != 'undefined' && vm.all_bastars.length > 0)
                            {
                                axios.post(baseExternalURL + 'api/files/' + vm.nrofile + '/service/' + _event.nroite, {
                                    event: _event,
                                    file: vm.file,
                                    lang: vm.lang,
                                    bastar: vm.all_bastars[0].bastar,
                                    flag_edit: vm.flag_edit,
                                    update_events: vm.update_events
                                })
                                    .then(response => {
                                        console.log(response)
                                        this.loadServices()
                                        this.selectedEvent.bastar = response.data.bastar;
                                    })
                                    .catch(error => {
                                        console.log(error)
                                        this.$toast.error(this.translations.messages.internal_error, {
                                            position: 'top-right'
                                        })
                                    })
                            }
                            else
                            {
                                this.$toast.error('No se encuentra la base de tarifa para actualizar el servicio.', {
                                    position: 'top-right'
                                })
                            }
                        })
                        .catch(error => {
                            console.log(error)
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        })
                }
            },
            /**
             * Eliminar un servicio / evento
             * @param data
             */
            onEventDelete: function (data) {

                console.log(data)

                let vm = this

                if(data.event != undefined && data.event.nroite != undefined)
                {
                    vm.services.forEach((element, e, array) => {
                        if(element.nroite == data.event.nroite)
                        {
                            vm.services.splice(e, 1)
                            vm.closeModal('modal')
                        }
                    })

                    axios.delete(baseExternalURL + 'api/files/' + this.nrofile + '/service/' + data.event.nroite)
                        .then(response => {
                            console.log(response)
                        })
                        .catch(response => {
                            console.log(error)
                            this.$toast.error(this.translations.messages.internal_error, {
                                position: 'top-right'
                            })
                        })
                }
                else
                {
                    vm.closeModal('modal')
                    vm.selectedEvent = {}
                    vm.deleteEventFunction()
                }
            },
            searchClients: function () {
                axios.get('api/clients/selectBox/by/executive')
                    .then((result) => {

                        if (result.data.success === true) {
                            this.all_clients = result.data.data
                        }
                    }).catch((e) => {
                    console.log(e)
                })
            },
            searchInfoFlight: function () {

                if(this.type == 'VUELO' || this.selectedEvent.catser == 'VUELO')
                {
                    if(typeof this.selectedEvent.nrovue != 'undefined' && this.selectedEvent.nrovue != '' && typeof this.selectedEvent.ciavue != 'undefined' && this.selectedEvent.ciavue != '')
                    {
                        this.api_flights = []
                        this.flag_flight = false

                        Vue.set(this.selectedEvent, 'ciuout', '')
                        Vue.set(this.selectedEvent, 'ciuin', '')

                        axios.post(baseExternalURL + 'api/flight_info', {
                            access_key: 'ba6a46b6f37bc8014b2f0ae226ff73c7',
                            flight_number: this.selectedEvent.nrovue,
                            airline_iata: this.selectedEvent.ciavue.codigo,
                        })
                            .then((response) => {
                                let _flights = response.data.data
                                let _date = moment(this.selectedEvent.fecin, 'DD/MM/YYYY').format('YYYY-MM-DD')

                                _flights.forEach((element, index, array) => {

                                    if(element.flight_date == _date)
                                    {
                                        this.api_flights.push(element)
                                    }

                                    if(index == (_flights.length - 1))
                                    {
                                        if(this.api_flights.length == 1)
                                        {
                                            this.setApiFlight(0)
                                        }

                                        console.log(this.api_flights)
                                    }
                                })
                            })
                            .catch((e) => {
                                console.log(e);
                            });
                    }
                }
            },
            setApiFlight: function (_index) {
                let _date = moment(this.selectedEvent.fecin, "DD/MM/YYYY").toDate().format('YYYY-MM-DD')
                let element = this.api_flights[_index]
                this.flag_flight = true

                if(element.flight_date == _date)
                {
                    localStorage.setItem('set_airline', 1)
                    localStorage.setItem('set_from', 1)
                    localStorage.setItem('set_to', 1)

                    this.type_service = 1

                    this.searchDestinationsTo(element.arrival.iata)
                    this.searchDestinationsFrom(element.departure.iata)
                    this.searchAirlines(element.airline.iata)

                    Vue.set(this.selectedEvent, 'fecout', moment(element.arrival.estimated).format('DD/MM/YYYY'))
                    Vue.set(this.selectedEvent, 'horin', moment(element.departure.estimated).format('HH:mm'))
                    Vue.set(this.selectedEvent, 'horout', moment(element.arrival.estimated).format('HH:mm'))

                    this.api_flights = []
                }
            },
            showModal: function  (_modal) {
                setTimeout(function () {
                    $('#' + _modal).modal('show')
                }, 10)
            },
            closeModal: function (_modal) {
                if(_modal == 'modalAlertaRemarks')
                {
                    this.hover_service = false
                }

                if(_modal == 'modal')
                {
                    if(this.selectedEvent.nroite != undefined && this.selectedEvent.nroite > 0)
                    {
                        this.loadServices()
                    }
                }

                this.modal = ''
                setTimeout(function () {
                    $('#' + _modal).modal('hide')
                }, 10)
            },
        },

        filters: {
            format_date : function (_date) {
                if( _date == undefined ){
                    // console.log('fecha no parseada: ' + _date)
                    return;
                }
                let secondPartDate = ''

                if( _date.length > 10 ){
                    secondPartDate = _date.substr(10, _date.length )
                    _date = _date.substr(0,10)
                }

                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date + secondPartDate
            },
        }
    };
</script>

<style>
    .cursor-icon{
        position: absolute;
        margin-top: -7px;
        margin-left: 21px;
    }
    .mouse_user{
        position: absolute;
        z-index: 99999;
    }
    .icon-green{
        color: #02b864 !important;
    }
    .right{
        float: right;
    }
    .cursor-pointer{
        cursor: pointer;
    }
    .cursor-pointer:hover{
        opacity: 0.7;
    }
</style>
