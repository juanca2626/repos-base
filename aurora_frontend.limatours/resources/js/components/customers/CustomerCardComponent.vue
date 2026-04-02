<template>
    <div class="container">
        <h4 class="ml-0 mr-0">FICHA DE CLIENTE</h4>
        <template v-if="client_code != ''">
            <div class="pt-3">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'info') ? 'active' : '']" v-on:click="toggleView('info')" href="#info">Información General</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'facturacion') ? 'active' : '']" v-on:click="toggleView('facturacion')" href="#facturacion">Facturación</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'emergencia') ? 'active' : '']" v-on:click="toggleView('emergencia')" href="#emergencia">Emergencia</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'ventas') ? 'active' : '']" v-on:click="toggleView('ventas')" href="#ventas">Ventas</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'hoteles') ? 'active' : '']" v-on:click="toggleView('hoteles')" href="#hoteles">Hoteles</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'guias') ? 'active' : '']" v-on:click="toggleView('guias')" href="#guias">Guías</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'tours_opcionales') ? 'active' : '']" v-on:click="toggleView('tours_opcionales')" href="#tours_opcionales">Tours Opcionales</a></li>
                    <li class="nav-item"><a v-bind:class="['nav-link', (option == 'recordatorios') ? 'active' : '']" v-on:click="toggleView('recordatorios')" href="#recordatorios">Recordatorios</a></li>
                </ul>
            </div>

            <div class="pt-3">
                <div v-if="option == 'info'">
                    <h4 class="ml-0 mr-0">Información General</h4>
                    <form class="form" id="form">
                        <div class="form-group">
                            <label for="tipo">Tipo de Cliente</label>
                            <select id="tipo" name="tipo" class="form-control">
                                <option value="">Seleccione</option>
                                <option v-for="(v, k) in clasificacion_clientes" v-bind:value="v.CODIGO" v-bind:selected="info_general.TIPCLI != undefined && info_general.TIPCLI.trim() == v.CODIGO">{{ v.DESCRI }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="relevancia">Relevancia del Cliente</label>
                            <select id="relevancia" name="relevancia" class="form-control">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="antiguedad">Cliente Lito desde</label>
                            <input id="antiguedad" name="antiguedad" class="form-control" v-bind:value="info_general.FECCRE" />
                        </div>
                        <div class="form-group form-check">
                            <input type="hidden" name="capacitado" value="0" />
                            <input type="checkbox" class="form-check-input" id="capacitado" name="capacitado" value="1" v-bind:checked="info_general.CLICAP == 1" />
                            <label class="form-check-label" for="capacitado">¿Cliente Capacitado en el uso de AURORA?</label>
                        </div>
                        <div class="form-group form-check">
                            <input type="radio" class="form-check-input" id="programacion_propia" name="programacion" value="1" v-bind:checked="info_general.PROPRO == 1" />
                            <label class="form-check-label" for="programacion_propia">¿Programación Propia?</label>
                        </div>
                        <div class="form-group form-check">
                            <input type="radio" class="form-check-input" id="programacion_lito" name="programacion" value="2" v-bind:checked="info_general.PROPRO == 2" />
                            <label class="form-check-label" for="programacion_lito">¿Programación LIMATOURS?</label>
                        </div>
                        <div class="form-group">
                            <label for="link_drive">Link del Drive</label>
                            <input id="link_drive" name="link_drive" class="form-control" v-bind:value="info_general.LINDRI" />
                        </div>
                        <div class="form-group form-check">
                            <input type="hidden" name="cargo_tc" value="0" />
                            <input type="checkbox" class="form-check-input" id="cargo_tc" name="cargo_tc" v-bind:checked="info_general.CATCRE == 1" />
                            <label class="form-check-label" for="cargo_tc">¿Cargo por pago con TC. 3.5%?</label>
                        </div>

                        <div v-if="regalos.length > 0">

                            <h4 class="mr-0 ml-0 mb-3">.En la cotización se debe incluir por defecto:</h4>
                            <div v-for="regalo in regalos">
                                <div class="form-group form-check">
                                    <input type="hidden" v-bind:name="regalo.TIPPRO" value="0" />
                                    <input type="checkbox" value="1" class="form-check-input" v-bind:id="regalo.TIPPRO" v-bind:name="regalo.TIPPRO" />
                                    <label class="form-check-label" v-bind:for="regalo.TIPPRO">{{ regalo.NOMBRE }}</label>
                                </div>
                                <div class="form-group" v-if="regalo.OBSERV == 1">
                                    <label v-bind:for="'observaciones_' + regalo.TIPPRO">Observaciones</label>
                                    <textarea class="form-control" v-bind:id="'observaciones_' + regalo.TIPPRO" v-bind:name="'observaciones_' + regalo.TIPPRO"></textarea>
                                </div>
                            </div>
                        </div>

                        <strong>Itinerario</strong>

                        <div class="form-group">
                            <label for="forma_entrega">Forma de Entrega</label>
                            <select id="forma_entrega" name="forma_entrega" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="1" v-bind:selected="info_general.FORENT == 1">Envío a Cliente</option>
                                <option value="2" v-bind:selected="info_general.FORENT == 2">Entrega de Documento impreso a Paxs</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="formato_documento">Formato del Documento</label>
                            <select id="formato_documento" name="formato_documento" class="form-control">
                                <option value="">Seleccione</option>
                                <option value="1" v-bind:selected="info_general.FORDOC == 1">LOGO Y FORMATO LITO</option>
                                <option value="2" v-bind:selected="info_general.FORDOC == 2">LOGO Y FORMATO CLIENTE</option>
                                <option value="3" v-bind:selected="info_general.FORDOC == 3">LOGO CLIENTE + FORMATO LITO</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="configuracion_transporte">Configuración de Transporte</label>
                            <select id="configuracion_transporte" name="configuracion_transporte" class="form-control" v-model="flag_configuracion_transporte">
                                <option value="">Seleccione</option>
                                <option value="1" v-bind:selected="info_general.TRANSP == 1">Antiguo</option>
                                <option value="2" v-bind:selected="info_general.TRANSP == 2">Nuevo</option>
                                <option value="3" v-bind:selected="info_general.TRANSP == 3">Otro</option>
                            </select>
                        </div>

                        <div class="form-group" v-if="flag_configuracion_transporte == 3">
                            <label for="otro_configuracion_transporte">Información adicional del Transporte</label>
                            <input type="text" class="form-control" id="otro_configuracion_transporte" name="otro_configuracion_transporte" v-bind:value="info_general.OBSTRA" />
                        </div>

                        <div class="form-group">
                            <label for="politicas_pago">Políticas de Pago</label>
                            <textarea class="form-control" id="politicas_pago" name="politicas_pago" v-model="policies"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="politicas_cancelacion">Políticas de Cancelación</label>
                            <textarea class="form-control" id="politicas_cancelacion" name="politicas_cancelacion"></textarea>
                        </div>

                        <div ng-show="acuerdos.length > 0">

                            <h4 class="mr-0 ml-0 mb-3">.Acuerdos Especiales:</h4>

                            <div v-for="acuerdo in acuerdos">
                                <div class="form-group form-check">
                                    <input type="hidden" v-bind:name="acuerdo.TIPPRO" value="0" />
                                    <input type="checkbox" value="1" class="form-check-input" v-bind:id="acuerdo.TIPPRO" v-bind:name="acuerdo.TIPPRO" />
                                    <label class="form-check-label" v-bind:for="acuerdo.TIPPRO">{{ acuerdo.NOMBRE }}</label>
                                </div>
                                <div class="form-group" v-if="acuerdo.OBSERV == 1">
                                    <label v-bind:for="'detalles_' + acuerdo.TIPPRO">Detalles {{ acuerdo.NOMBRE }}</label>
                                    <textarea class="form-control" v-bind:id="'detalles_' + acuerdo.TIPPRO" v-bind:name="'detalles_' + acuerdo.TIPPRO"></textarea>
                                </div>

                                <div class="form-group" v-if="acuerdo.FECVIG == 1">
                                    <label v-bind:for="'vigencia_' + acuerdo.TIPPRO">Fecha de Vigencia</label>
                                    <input type="text" class="datepicker form-control" v-bind:id="'vigencia_' + acuerdo.TIPPRO" v-bind:name="'vigencia_' + acuerdo.TIPPRO" />
                                </div>
                            </div>
                        </div>


                        <h4 class="mr-0 ml-0 mb-3">.Material operativo para la cuenta:</h4>

                        <div class="form-group">
                            <label for="letrero">Letrero</label>
                            <input type="text" class="form-control" id="letrero" name="letrero" v-bind:value="info_general.LETRER" />
                        </div>

                        <div class="form-group">
                            <label for="badge">Badge</label>
                            <input type="text" class="form-control" id="badge" name="badge" v-bind:value="info_general.BADGE" />
                        </div>

                        <div class="form-group">
                            <label for="uniforme">Uniforme</label>
                            <input type="text" class="form-control" id="uniforme" name="uniforme" v-bind:value="info_general.UNIFOR" />
                        </div>

                        <div class="form-group">
                            <label for="pax_comment">PAX Comment</label>
                            <input type="text" class="form-control" id="pax_comment" name="pax_comment" v-bind:value="info_general.PAXCOM" />
                        </div>



                        <!-- h4 class="mr-0 ml-0 mb-3">Logo en Formato para Descargar</h4>

                        FORMULARIO PARA CAMBIAR IMAGEN..

                        -->

                        <div class="form-group">
                            <label for="comentarios">Comentarios Adicionales</label>
                            <textarea class="form-control" id="comentarios" max-length="100" name="comentarios" v-model="comadi"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="politicas_grupos">Políticas para Grupos</label>
                            <textarea class="form-control" id="politicas_grupos" name="politicas_grupos"></textarea>
                        </div>

                        <button type="button" v-on:click="save()" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>
                </div>

                <div v-if="option == 'facturacion'">
                    <h4 class="ml-0 mr-0 mb-3">Facturación</h4>
                    <form class="form" id="form">
                        <div class="form-group form-check">
                            <input type="hidden" name="credito" value="0" />
                            <input type="checkbox" class="form-check-input" id="credito" name="credito" value="1" v-bind:checked="facturacion_customer.CREDIT == 1" />
                            <label class="form-check-label" for="credito">¿Crédito?</label>
                        </div>
                        <div class="form-group form-check">
                            <input type="hidden" name="factura_file" value="0" />
                            <input type="checkbox" class="form-check-input" id="factura_file" name="factura_file" v-bind:checked="facturacion_customer.FACFIL == 1" v-model="flag_factura_file" value="1" />
                            <label class="form-check-label" for="factura_file">¿Se envía factura x File?</label>
                        </div>
                        <div class="form-group" v-if="flag_factura_file == 1">
                            <label for="observaciones_factura_file">Observaciones</label>
                            <textarea id="observaciones_factura_file" name="observaciones_factura_file" class="form-control" v-model.trim="observaciones_factura_file"></textarea>
                        </div>

                        <h4 class="mr-0 ml-0 mb-3">.Contacto para envío de facturas*<br /><small>En caso sea una persona diferente a la del área de reservas.</small></h4>
                        <div class="form-group">
                            <label for="nombre_contacto_factura">Nombres</label>
                            <input type="text" id="nombre_contacto_factura" name="nombre_contacto_factura" class="form-control" v-bind:value.trim="facturacion_customer.NOMFAC" />
                        </div>
                        <div class="form-group">
                            <label for="correo_contacto_factura">Correo Electrónico</label>
                            <input type="text" id="correo_contacto_factura" name="correo_contacto_factura" class="form-control" v-bind:value.trim="facturacion_customer.EMAFAC" />
                        </div>
                        <div class="form-group">
                            <label for="telefono_contacto_factura">Teléfono</label>
                            <input type="text" id="telefono_contacto_factura" name="telefono_contacto_factura" class="form-control" v-bind:value.trim="facturacion_customer.TELFAC" />
                        </div>
                        <button type="button" v-on:click="save()" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>
                </div>

                <div v-if="option == 'emergencia'">
                    <h4 class="mr-0 ml-0 mb-3">Emergencia (Fuera del Horario QR)</h4>
                    <form class="form" id="form_emergencia">
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="nombre_contacto_emergencia">Nombres</label>
                                <input type="text" id="nombre_contacto_emergencia" v-model="nombre_contacto_emergencia" name="nombre_contacto_emergencia" class="form-control" />
                            </div>
                            <div class="form-group col-6">
                                <label for="correo_contacto_emergencia">Correo Electrónico</label>
                                <input type="text" id="correo_contacto_emergencia" v-model="correo_contacto_emergencia" name="correo_contacto_emergencia" class="form-control" />
                            </div>
                            <div class="form-group col-6">
                                <label for="telefono_contacto_emergencia">Teléfono</label>
                                <input type="text" id="telefono_contacto_emergencia" v-model="telefono_contacto_emergencia" name="telefono_contacto_emergencia" class="form-control" />
                            </div>
                            <div class="form-group col-6">
                                <label for="idiomas_contacto_emergencia">Idiomas</label>
                                <select class="form-control" id="idiomas_contacto_emergencia" v-model="idiomas_contacto_emergencia" name="idiomas_contacto_emergencia">
                                    <option>----</option>
                                    <option v-bind:value="idioma.DESCRI.trim()" v-for="idioma in idiomas">{{ idioma.DESCRI.trim() }}</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" v-on:click="addEmergencia()" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>

                    <h4 class="mr-0 ml-0 mb-3">.Contactos de emergencia actuales</h4>
                    <template v-if="contacts.length > 0">
                        <table class="display table table-bordered table-striped" >
                        <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Correo Electrónico</th>
                            <th>Teléfono</th>
                            <th>Idiomas</th>
                            <th class="center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(contact, key) in contacts">
                            <td>{{ contact.NOMEME.trim() }}</td>
                            <td>{{ contact.EMAEME.trim() }}</td>
                            <td>{{ contact.TELEME.trim() }}</td>
                            <td>{{ contact.IDIOMA.trim() }}</td>
                            <td class="center">
                                <div class="btn-group right">
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false"><i class="fa fa-cog"></i> <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-tracking">
                                        <li>
                                            <a href="javascript:;"
                                               v-on:click="editEmergencia(contact)"
                                               class="btn-effect" style="color:#332F2E,">
                                                <i class="fa fa-exchange" aria-hidden="true"></i>
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:;" v-on:click="deleteEmergencia(contact)" class="btn-effect" style="color:#332F2E," >
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </template>
                    <div class="alert alert-warning" v-else>No hay contactos de emergencia asignados.</div>
                </div>

                <div v-if="option == 'ventas'">
                </div>

                <div v-if="option == 'guias'">
                    <h4 class="mr-0 ml-0 mb-3">Guías Preferentes</h4>
                    <form class="form">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="destino">Destino</label>
                                <v-select :options="all_destinos"
                                          @search="filterDestinos"
                                          v-model="destino_p"
                                          @input="filterGuias('')"
                                          id="destino_p"
                                          label="DESCRI"
                                          class="form-control">
                                </v-select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="guia">Código de Guía</label>
                                <v-select :options="all_guias"
                                          @search="filterGuias"
                                          v-model="guia_p"
                                          id="guia_p"
                                          label="RAZON"
                                          class="form-control">
                                </v-select>
                            </div>
                        </div>
                        <button type="button" v-on:click="saveGuia('P')" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>

                    <h4 class="mr-0 ml-0 mb-3">.Guías preferentes actuales</h4>
                    <table class="display table table-bordered table-striped" v-if="guias_preferentes.length > 0">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Guía</th>
                            <th>Destino</th>
                            <th class="center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="guia in guias_preferentes">
                            <td>{{ guia.CODIGO.trim() }}</td>
                            <td>{{ guia.RAZON.trim() }}</td>
                            <td>{{ guia.CODCIU.trim() }}</td>
                            <td class="center">
                                <div class="btn-group right">
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false"><i class="fa fa-cog"></i> <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-tracking">
                                        <li>
                                            <a href="javascript:;" v-on:click="deleteGuia( guia, 'P' )" class="btn-effect" style="color:#332F2E," >
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="alert alert-warning" v-else>No hay guías preferentes asignados.</div>

                    <h4 class="mr-0 ml-0 mb-3">Guías Vetados</h4>
                    <form class="form">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="destino">Destino</label>
                                <v-select :options="all_destinos"
                                          @search="filterDestinosVetados"
                                          v-model="destino_v"
                                          @input="filterGuiasVetados('')"
                                          id="destino_v"
                                          label="DESCRI"
                                          class="form-control">
                                </v-select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="guia">Código de Guía</label>
                                <v-select :options="all_guias"
                                          @search="filterGuiasVetados"
                                          v-model="guia_v"
                                          id="guia_p"
                                          label="RAZON"
                                          class="form-control">
                                </v-select>
                            </div>
                        </div>
                        <button type="button" v-on:click="saveGuia('V')" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>

                    <h4 class="mr-0 ml-0 mb-3">.Guías vetados actuales</h4>
                    <table class="display table table-bordered table-striped" v-if="guias_vetados.length > 0">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Guía</th>
                            <th>Destino</th>
                            <th class="center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="guia in guias_vetados">
                            <td>{{ guia.CODIGO.trim() }}</td>
                            <td>{{ guia.RAZON.trim() }}</td>
                            <td>{{ guia.CODCIU.trim() }}</td>
                            <td class="center">
                                <div class="btn-group right">
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false"><i class="fa fa-cog"></i> <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-tracking">
                                        <li>
                                            <a href="javascript:;" v-on:click="deleteGuia( guia, 'V' )" class="btn-effect" style="color:#332F2E,">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="alert alert-warning" v-else>No hay guías vetados asignados.</div>
                </div>

                <div v-if="option == 'hoteles'">
                    <h4 class="mr-0 ml-0 mb-3">Hoteles Preferentes</h4>
                    <div class="form">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="destino">Destino</label>
                                <v-select :options="all_destinos_hoteles"
                                          @search="filterDestinosHoteles"
                                          @input="filterHoteles('')"
                                          v-model="destino_p"
                                          id="destino"
                                          label="DESCRI"
                                          class="form-control">
                                </v-select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="hotel">Código de Hotel</label>
                                <v-select :options="all_hoteles"
                                          @search="filterHoteles"
                                          v-model="hotel_p"
                                          id="hotel"
                                          label="RAZON"
                                          class="form-control">
                                </v-select>
                            </div>
                        </div>
                        <button type="button" v-on:click="saveHotel('P')" class="btn btn-primary" style="width:150px">Guardar</button>
                    </div>

                    <h4 class="mr-0 ml-0 mb-3">.Hoteles preferentes actuales</h4>
                    <table class="display table table-bordered table-striped" v-if="hoteles_preferentes.length > 0">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Hotel</th>
                            <th>Destino</th>
                            <th class="center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="hotel in hoteles_preferentes">
                            <td>{{ hotel.CODHOT.trim() }}</td>
                            <td>{{ hotel.RAZON.trim() }}</td>
                            <td>{{ hotel.DESTIN.trim() }}</td>
                            <td class="center">
                                <div class="btn-group right">
                                    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" aria-expanded="false"><i class="fa fa-cog"></i> <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-tracking">
                                        <li>
                                            <a href="javascript:;" v-on:click="deleteHotel( hotel, 'P' )" class="btn-effect" style="color:#332F2E">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="alert alert-warning" v-else>No hay hoteles preferentes asignados.</div>
                </div>

                <div v-if="option == 'tours_opcionales'">
                    <h4 class="mr-0 ml-0 mb-3">Tours Opcionales</h4>
                    <form class="form" id="form">
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="tours_opcionales" name="tours_opcionales" value="1" v-model="flag_tours_opcionales" v-on:checked="tours_customer.VETOOP.trim() == 1" />
                            <label class="form-check-label" for="tours_opcionales">¿Venta de tours opcionales?</label>
                        </div>

                        <div class="form-group form-check" v-if="flag_tours_opcionales || tours_customer.VETOOP.trim() == 1">
                            <input type="checkbox" class="form-check-input" id="comision_cliente" name="comision_cliente" v-model="flag_comision_cliente" v-on:checked="tours_customer.COMCLI.trim() == 1" value="1" />
                            <label class="form-check-label" for="comision_cliente">¿Se protege comisión del cliente?</label>
                        </div>

                        <div class="form-group" v-if="flag_comision_cliente || tours_customer.COMCLI.trim() == 1">
                            <label for="porcentaje_comision_cliente">%</label>
                            <input type="text" id="pocentaje_comision_cliente" name="porcentaje_comision_cliente" class="form-control" v-bind:value.trim="tours_customer.PORCLI" />
                        </div>

                        <button type="button" v-on:click="save()" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>
                </div>

                <div v-if="option == 'recordatorios'">
                    <h4 class="mr-0 ml-0 mb-3">Recordatorios</h4>
                    <form id="form">
                        <div class="alert alert-warning" v-if="loading">Cargando..</div>
                        <div class="form-group mb-3" v-for="(reminder, r) in reminders">
                            <label>{{ reminder.title }}</label>
                            <div class="d-flex">
                                <table class="table table-bordered table-striped mb-3 mr-3" style="width:auto!important;"
                                       v-for="(category, c) in reminder.categories">
                                    <thead>
                                    <tr>
                                        <td>{{ category.title }}</td>
                                        <td>Tipos de notificación</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(time, t) in category.times">
                                        <!-- td>
                                            <input type="checkbox"
                                                   v-bind:checked="(recordatorios_customer.REPAFP[reminder.id] != undefined && recordatorios_customer.REPAFP[reminder.id][category.id] != undefined && recordatorios_customer.REPAFP[reminder.id][category.id][time.id] != undefined)" />
                                        </td -->
                                        <td>
                                            {{ time.time }} {{ time.format }}
                                        </td>
                                        <td>
                                            <label v-bind:class="['ui', 'field', (loading) ? 'disabled' : '', 'mr-2']" v-for="(_type, t) in types_reminder">
                                                <input type="checkbox"
                                                       v-bind:name="'options[' + reminder.id + '][' + category.id + '][' + time.id + '][' + _type.id + ']'"
                                                       v-bind:checked="(_type.title == 'email' && (Object.entries(recordatorios_customer.REPAFP).length == 0)) || (recordatorios_customer.REPAFP[reminder.id] != undefined && recordatorios_customer.REPAFP[reminder.id][category.id] != undefined && recordatorios_customer.REPAFP[reminder.id][category.id][time.id] != undefined && recordatorios_customer.REPAFP[reminder.id][category.id][time.id][_type.id] == 1)"
                                                       value="1" /> {{ _type.title.toUpperCase() }}</label>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="correo_contacto_recordatorio">Correo Electrónico (separar con comas si hay más de 1 contacto)</label>
                            <input type="text" id="correo_contacto_recordatorio" name="correo_contacto_recordatorio" v-bind:value="recordatorios_customer.CORNOT" class="form-control" />
                        </div>
                        <button type="button" v-on:click="save()" class="btn btn-primary" style="width:150px">Guardar</button>
                    </form>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="alert alert-warning">Por favor, seleccione un cliente y actualice la página para visualizar la información de la ficha.</div>
        </template>
    </div>
</template>
<script>
    export default {
        props: ['translations'],
        data: () => {
            return {
                lang: '',
                loading: false,
                // customers: [],
                client_code: '',
                option: 'info',
                info_general: {

                },
                logoUpload: false,
                defaultImg: '',
                policies: '',
                comadi: '',
                categorias_hotel: [],
                clasificacion_clientes: [],
                flag_configuracion_transporte: false,
                regalos: [],
                acuerdos: [],
                idiomas: [],
                loadingData: true,
                flag_factura_file: 0,
                contacts: [],
                destino_p: '',
                destino_v: '',
                hotel_p: '',
                all_customers: [],
                allcustomers: {},
                categorias_hotel: [],
                clasificacion_clientes: [],
                facturacion_customer: [],
                policies: 'Valor Inicial',
                observaciones_factura_file: '',
                tours_customer: [],
                // --- guias..
                all_guias: [],
                all_destinos: [],
                all_destinos_hoteles: [],
                all_destinos_vetados: [],
                guia_v: '',
                guia_p: '',
                guias_preferentes: [],
                guias_vetados: [],
                all_hoteles: [],
                hoteles_preferentes: [],
                flag_tours_opcionales: false,
                flag_comision_cliente: false,
                id_emergencia: '',
                nombre_contacto_emergencia: '',
                correo_contacto_emergencia: '',
                telefono_contacto_emergencia: '',
                idiomas_contacto_emergencia: '',
                edit_emergencia: false,
                recordatorios_customer: {
                    'REPAFP': {

                    },
                    'CORNOT': ''
                },
                reminders: [],
                types_reminder: []
            }
        },
        created: function () {
            // this.searchCustomers()
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            if (localStorage.getItem('client_code'))
            {
                if (localStorage.getItem('client_code') != '' && localStorage.getItem('client_code') != null) {
                    this.client_code = localStorage.getItem('client_code')
                }
            }
        },
        computed: {

        },
        methods: {
            getReminders: function () {
                this.loading = true
                axios.post(
                    baseExternalURL + 'api/reminders'
                )
                    .then((result) => {
                        this.loading = false
                        this.reminders = result.data.reminders
                        this.types_reminder = result.data.types
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                    })
            },
            /*
            searchCustomers: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/search_all', {
                        lang: this.lang,
                        customer: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.customers = result.data.customers
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filter: function (value) {
                this.customer = value
                this.toggleView()
            },
             */
            toggleView: function (_option) {

                if (localStorage.getItem('client_code'))
                {
                    if (localStorage.getItem('client_code') != '' && localStorage.getItem('client_code') != null) {
                        this.client_code = localStorage.getItem('client_code')
                    }
                }

                if(_option != undefined)
                {
                    this.option = _option
                }

                let vm = this
                eval('vm.' + vm.option + '()')
            },
            save: function () {
                this.loading = true
                let params = $('#form').serialize()

                console.log(params)

                axios.post(
                    baseURL + 'customers/update', {
                        type: this.option,
                        params: params,
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        console.log(result)

                        if(result.data == 'success')
                        {
                            this.$toast.success('La información se actualizó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error(result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            info: function () {
                this.loading = true

                axios.post(
                    baseURL + 'customers/info_general', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        // this.customer = result.data.customer,
                        this.comadi = result.data.info_general.COMADI
                        this.info_general = result.data.info_general
                        this.categorias_hotel = result.data.categorias_hotel
                        this.clasificacion_clientes = result.data.clasificacion_clientes
                        this.regalos = result.data.regalos
                        this.acuerdos = result.data.acuerdos

                        setTimeout(function(){
                            $.each(result.data.regalos_cliente, function(i, item) {
                                console.log(item)
                                eval('$("#' + item['TIPPRO'] + '").prop("checked", true)')
                                eval('$("#observaciones_' + item['TIPPRO'] +  '").val("' + item['OBSERV'] + '")')
                            }),

                            $.each(result.data.acuerdos_cliente, function(i, item) {
                                console.log(item)
                                eval('$("#' + item['TIPPRO'] + '").prop("checked", true)')
                                eval('$("#observaciones_' + item['TIPPRO'] +  '").val("' + item['OBSERV'] + '")')
                                eval('$("#vigencia_' + item['TIPPRO'] +  '").val("' + item['FECVIG'] + '")')
                            })
                        }, 100)

                        if (this.info_general.LOGO) {
                            this.logoUpload = true
                            let folderCloudinary = ''
                            if (document.domain === 'aurora.limatours.com.pe')
                            {
                                folderCloudinary = 'https://res.cloudinary.com/litomarketing/image/upload/v1558128057/aurora/logos/'
                            }
                            else
                            {
                                folderCloudinary = 'https://res.cloudinary.com/litomarketing/image/upload/v1558128057/aurora/logos_dev/'
                            }
                            this.defaultImg = folderCloudinary + this.info_general.LOGO
                        } else {
                            this.defaultImg = ''
                        }

                        axios.post(
                            baseURL + 'customers/find_policies', {
                                lang: this.lang,
                                customer: this.client_code
                            }
                        )
                            .then((result) => {
                                this.loading = false
                                this.policies = result.data.policies
                            })
                            .catch((e) => {
                                console.log(e)
                            })

                        console.log(result.data)
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            facturacion: function () {
                this.loading = true

                axios.post(
                    baseURL + 'customers/facturacion', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        this.facturacion_customer = result.data.facturacion
                        this.flag_factura_file = this.facturacion_customer.FACFIL;
                        this.observaciones_factura_file = (result.data.facturacion.OBSERV != null) ? result.data.facturacion.OBSERV : ''
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            emergencia: function () {
                this.loading = true

                axios.post(
                    baseURL + 'customers/emergencia', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.edit_emergencia = false

                        this.id_emergencia = ''
                        this.correo_contacto_emergencia = ''
                        this.nombre_contacto_emergencia = ''
                        this.telefono_contacto_emergencia = ''
                        this.idiomas_contacto_emergencia = ''

                        this.contacts = result.data.contacts
                        this.idiomas = result.data.idiomas
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            addEmergencia: function () {
                this.loading = true
                let params = $('#form_emergencia').serialize()

                axios.post(
                    baseURL + ((this.edit_emergencia) ? 'customers/editar_emergencia' : 'customers/agregar_emergencia'), {
                        params: params,
                        lang: this.lang,
                        customer: this.client_code,
                        codref: this.id_emergencia,
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('La información se actualizó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error(result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        this.emergencia()
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            editEmergencia: function (emergencia) {
                this.edit_emergencia = true

                this.correo_contacto_emergencia = emergencia.EMAEME.trim()
                this.nombre_contacto_emergencia = emergencia.NOMEME.trim()
                this.telefono_contacto_emergencia = emergencia.TELEME.trim()
                this.idiomas_contacto_emergencia = emergencia.IDIOMA.trim()
                this.id_emergencia = emergencia.CODREF.trim()
            },
            deleteEmergencia: function (emergencia) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/eliminar_emergencia', {
                        codref: emergencia.CODREF,
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('La información se actualizó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error(result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        this.emergencia()
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamineto', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            ventas: function () {

            },
            hoteles: function () {
                this.loading = true
                this.filterDestinosHoteles()

                axios.post(
                    baseURL + 'customers/hoteles', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.hoteles_preferentes = result.data.hoteles_preferentes
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            guias: function () {
                this.loading = true
                this.filterDestinos()
                this.filterDestinosVetados()

                axios.post(
                    baseURL + 'customers/guias', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.guias_preferentes = result.data.guias_preferentes
                        this.guias_vetados = result.data.guias_vetados


                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            tours_opcionales: function () {
                this.loading = true

                axios.post(
                    baseURL + 'customers/tours_opcionales', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.tours_customer = result.data.tours_opcionales
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterDestinos: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_destinos', {
                        lang: this.lang,
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_destinos = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterGuias: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_guias', {
                        lang: this.lang,
                        destino: this.destino_p.CODIGO.trim(),
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_guias = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterDestinosVetados: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_destinos', {
                        lang: this.lang,
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_destinos_vetados = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterGuiasVetados: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_guias', {
                        lang: this.lang,
                        destino: this.destino_p.CODIGO.trim(),
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_destinos_vetados = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterDestinosHoteles: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_destinos', {
                        lang: this.lang,
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_destinos_hoteles = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            filterHoteles: function (search, loading) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/filter_hoteles', {
                        lang: this.lang,
                        destino: this.destino_p.CODIGO.trim(),
                        term: (search != '' && search != undefined) ? search : ''
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.all_hoteles = result.data
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            saveHotel: function (_type) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/agregar_hotel', {
                        lang: this.lang,
                        identi: _type,
                        destino: this.destino_p.CODIGO.trim(),
                        hotel: this.hotel_p.CODIGO.trim(),
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('El hotel se agregó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error('Error de procesamiento', {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        if(result.data == 'success')
                        {
                            this.hoteles()
                        }
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            deleteHotel: function (hotel, _type) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/eliminar_hotel', {
                        lang: this.lang,
                        identi: _type,
                        destino: hotel.DESTIN.trim(),
                        hotel: hotel.CODHOT.trim(),
                        customer: hotel.CODIGO.trim()
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('El hotel se eliminó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error('Error de procesamiento', {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        if(result.data == 'success')
                        {
                            this.hoteles()
                        }
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            saveGuia: function (_type) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/agregar_guia', {
                        lang: this.lang,
                        identi: _type,
                        destino: this.destino_p.CODIGO.trim(),
                        guia: this.guia_p.CODIGO.trim(),
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('El guía se agregó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error('Error de procesamiento', {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        if(result.data == 'success')
                        {
                            this.hoteles()
                        }
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            deleteGuia: function (guia, _type) {
                this.loading = true

                axios.post(
                    baseURL + 'customers/eliminar_guia', {
                        lang: this.lang,
                        identi: _type,
                        destino: guia.CODCIU.trim(),
                        guia: guia.CODIGO.trim(),
                        customer: guia.CODCLI.trim()
                    }
                )
                    .then((result) => {
                        this.loading = false

                        if(result.data == 'success')
                        {
                            this.$toast.success('El guía se eliminó correctamente.', {
                                // override the global option
                                position: 'top-right'
                            })
                        }
                        else
                        {
                            this.$toast.error('Error de procesamiento', {
                                // override the global option
                                position: 'top-right'
                            })
                        }

                        if(result.data == 'success')
                        {
                            this.guias()
                        }
                    })
                    .catch((e) => {
                        this.$toast.error('Error de procesamiento', {
                            // override the global option
                            position: 'top-right'
                        })

                        console.log(e)
                    })
            },
            recordatorios: function () {
                this.loading = true

                axios.post(
                    baseURL + 'customers/recordatorios', {
                        lang: this.lang,
                        customer: this.client_code
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.getReminders()

                        let __reminders = result.data.recordatorios.REPAFP

                        if(__reminders == '' || __reminders == null)
                        {
                            __reminders = '{}'
                        }

                        this.recordatorios_customer = {
                            'REPAFP': JSON.parse(__reminders),
                            'CORNOT': result.data.recordatorios.CORNOT
                        }

                        console.log(this.recordatorios_customer)
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
        }
    }
</script>
