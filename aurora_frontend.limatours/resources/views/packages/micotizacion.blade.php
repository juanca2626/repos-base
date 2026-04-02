 @extends('layouts.app')
@section('content')
    {{-- <style>
        .content_services_group {
            display: flex;
            flex-wrap: wrap;
        }

        .content_services_group .prod-fecha {
            background: #fafafa;
        }
        .content_services_group .prod-fecha::before {
            display: block;
            content: "";
            border-left: 3px solid #e9e9e9;
            height: 25px;
            position: absolute;
            width: 100%;
            top: -50px;
            left: 50%;
            height: 70%;
        }
    </style> --}}
    <section class="page-cotizacion">
        <div class="container">
            <div class="row">
                <div class="col-12 titulo">
                    <h2>Nueva Cotización</h2>
                </div>
                <div class="col-12 cotizacion-crear">
                    <div class="form">
                        <div class="form-row">
                            <div class="form-group cotizacion-crear--nombre"> <i class="icon icon-folder"></i>
                                <input type="text" class="form-control" v-model="quote_name" placeholder="Nombre de la Cotización">
                            </div>
                            <div class="form-group"> <i class="icon icon-calendar"></i>
                                <date-picker class="form-control" v-model="quote_date" :config="optionsR"></date-picker>
                            </div>
                            <div class="form-group cotizacion-crear--pasajeros"> <i class="icon icon-user"></i>
                                <button id="dropdownPax" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="form-control">
                                    <span class="text"><strong class="num">2</strong>adultos</span>
                                    <span class="text"><strong class="num">0</strong>niños</span>
                                </button>
                                <div aria-labelledby="dropdownPax" class="dropdown dropdown-menu" x-placement="bottom-start">
                                    <div class="container-dropdown">
                                        <div class="form-group">
                                            <label>Adultos</label>
                                            <select class="form-control">
                                                <option value="1">1 </option>
                                                <option value="2">2 </option>
                                                <option value="3">3 </option>
                                                <option value="4">4 </option>
                                                <option value="5">5 </option>
                                                <option value="6">6 </option>
                                                <option value="7">7 </option>
                                                <option value="8">8 </option>
                                                <option value="9">9 </option>
                                                <option value="10">10 </option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Niños</label>
                                            <select class="form-control">
                                                <option value="0">0 </option>
                                                <option value="1">1 </option>
                                                <option value="2">2 </option>
                                                <option value="3">3 </option>
                                                <option value="4">4 </option>
                                                <option value="5">5 </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group cotizacion-crear--boton">
                                <button class="btn btn-primary" @click="newQuote()" :disabled="loading">
                                    <span v-if="!(loading)">Crear</span>
                                    <span v-if="loading"><i class="fa fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 cotizacion-editar">
                    <div class="btn-group">
                        <div class="btn btn-link cotizacion-editar--lista">
                            <a href="#" class="link" data-toggle="modal" data-target="#modal-pax">
                                <i class="icon icon-user-check"></i><span class="text">Lista de Pasajeros</span><small>(4)</small>
                            </a>
                        </div>
                        <div class="btn btn-link cotizacion-editar--categorias">
                            <a href="#" id="dropdownCategoria" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="link">
                                <i class="icon icon-tag"></i><span class="text">Categorías</span><small>(@{{ categories.length }})</small>
                            </a>
                            <div aria-labelledby="dropdownCategoria" class="dropdown dropdown-menu" x-placement="bottom-start">
                                <div class="container-dropdown">
                                    <div class="form-group row">
                                        <label>Seleccionar Categorías</label>
                                        <label class="form-check col-12">
                                            <input style="margin-top: 14px;margin-right: 5px;" class="form-check-input" type="checkbox"
                                                   @change="toggleAllCategories()" v-model="checkedAllCategories">
                                                Todas las categorías
                                        </label>
                                        <label class="form-check col-6" v-for="cate in categories">
                                                <input style="margin-top: 14px; margin-right: 5px;" class="form-check-input" type="checkbox"
                                                      v-model="cate.check" @change="toggleCategory(cate)">
                                                 @{{ cate.translations[0].value  }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn btn-link cotizacion-editar--rangos">
                            <a href="#" id="dropdownRango" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="link">
                                <i class="icon icon-maximize-2"></i><span class="text">Rangos</span><small>(5)</small>
                            </a>
                            <div aria-labelledby="dropdownRango" class="dropdown dropdown-menu" x-placement="bottom-start">
                                <div class="container-dropdown">
                                    <div class="form-group">
                                        <label>Seleccionar Rangos</label>
                                        <table class="table justify-content-center">
                                            <thead>
                                                <tr>
                                                <th scope="col" class="col-ini">#</th>
                                                <th scope="col" class="col">Desde</th>
                                                <th scope="col" class="col">Hasta</th>
                                                <th scope="col" class="col">Simple</th>
                                                <th scope="col" class="col">Doble</th>
                                                <th scope="col" class="col">Triple</th>
                                                <th scope="col" class="col-fin">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                <th scope="row" class="th">1</th>
                                                <td nowrap class="td"><input type="text" class="form-control start" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control end" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                                </tr>
                                                <tr>
                                                <th scope="row" class="th">2</th>
                                                <td nowrap class="td"><input type="text" class="form-control start" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control end" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                                </tr>
                                                <tr>
                                                <th scope="row" class="th">3</th>
                                                <td nowrap class="td"><input type="text" class="form-control start" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control end" placeholder="1"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn btn-link cotizacion-editar--notas ml-5">
                            <a href="#" class="link" data-toggle="modal" data-target="#modal-notas">
                                <i class="icon icon-message-square"></i><span class="text">Notas</span><small>(0)</small>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 cotizacion-rangos" title="Categorías seleccionadas">
                    <span v-for="cate in categories" v-if="cate.check" class="rango back-red">@{{ cate.translations[0].value }}</span>
                </div>
                <div class="col-12 cotizacion-rangos" title="Rangos seleccionados">
                    <span class="rango back-gray">1</span>
                    <span class="rango back-gray">2</span>
                    <span class="rango back-gray">5</span>
                    <span class="rango back-gray">6-10</span>
                    <span class="rango back-gray">11-99</span>
                </div>
                <div class="col-12 cotizacion-categorias d-flex justify-content-between align-items-center" title="Categorías seleccionadas">
                    <div>
                        <span class="btn btn-tab categoria active">Turista</span>
                        <span class="btn btn-tab categoria">Turista Superior</span>
                        <span class="btn btn-tab categoria">Primera</span>
                        <span class="btn btn-tab categoria">Primera Superior</span>
                    </div>
                    <div class="icon-eliminar">
                        <i class="icon icon-trash"></i>
                    </div>
                </div>
                <div class="col-12 cotizacion-listado">
                    <div class="leyenda">
                        <span class="leyenda-fecha">Fecha</span>
                        <span class="leyenda-descripcion">Descripcion</span>
                        <span class="leyenda-tipo">Tipo</span>
                        <span class="leyenda-detalle">Detalle</span>
                        <span class="leyenda-precio">Precio x Per.</span>
                        <span class="leyenda-acomodacion">Acomod.</span>
                    </div>

                    <div class="row cotizaciones-listado">
                        <div class="col-12 draggable">
                            <!-- 1 ------------------------------------------ // -->
                            <draggable tag="ul" handle=".handle">
                                <transition-group>
                                </transition-group>
                                <li class="producto">
                                    <div class="prod-acciones d-flex align-items-center">
                                        <i class="icon icon-drag handle"></i>
                                        <input style="" class="form-check-input" type="checkbox" @change="">
                                        <a href="#"><i class="icon icon-plus-circle"></i></a>
                                    </div>
                                    <div class="prod-fecha">
                                        <date-picker class="date" v-model="add_hotel_date" :config="optionsR"></date-picker>
                                    </div>
                                    <div class="prod-estado">
                                        <span class="estado estado-default"></span>
                                    </div>
                                    <div class="prod-descripcion">
                                        <span class="id">[CUZ111]</span>
                                        <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                        <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                    </div>
                                    <div class="prod-tipo">
                                        <div class="btn btn-icon" title="Público (cambiar a privado)"><i class="icon icon-globe-switch"></i></div>
                                    </div>
                                    <div class="prod-detalle">
                                        <span class="adt">4<small>adultos</small></span>
                                        <span class="chd">2<small>niños</small></span>
                                    </div>
                                    <div class="prod-editar">
                                        <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                    </div>
                                    <div class="prod-precio">
                                        <span class="producto-precio--num"><small>s/.</small>41.5</span>
                                    </div>
                                    <div class="prod-acomodacion">
                                        <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                    </div>
                                </li>
                            </draggable>
                            <!-- 2 ------------------------------------------ // -->
                            <draggable tag="ul"   handle=".handle">
                                <transition-group>
                                </transition-group>
                                <li class="producto">
                                    <div class="prod-acciones d-flex align-items-center">
                                        <i class="icon icon-drag handle"></i>
                                        <input style="" class="form-check-input" type="checkbox" @change="">
                                        <a href="#"><i class="icon icon-plus-circle"></i></a>
                                    </div>
                                    <div class="prod-fecha">
                                        <a href="#" class="link">16/07/2019</a>
                                    </div>
                                    <div class="prod-estado">
                                        <span class="estado estado-default"></span>
                                    </div>
                                    <div class="prod-descripcion">
                                        <span class="id">[CUZ111]</span>
                                        <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                        <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                    </div>
                                    <div class="prod-tipo">
                                        <div class="btn btn-icon" title="Privado (cambiar a público)"><i class="icon icon-lock-switch"></i></div>
                                    </div>
                                    <div class="prod-detalle">
                                        <button id="dropdownPax" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="form-control">
                                            <span class="text"><strong class="num">4 </strong>adultos</span>
                                            <span class="text"><strong class="num">2 </strong>niños</span>
                                        </button>
                                        <div aria-labelledby="dropdownPax" class="dropdown dropdown-menu" x-placement="bottom-start">
                                            <div class="container-dropdown">
                                                <div class="form-group">
                                                    <label>Adultos</label>
                                                    <select class="form-control">
                                                        <option value="1">1 </option>
                                                        <option value="2">2 </option>
                                                        <option value="3">3 </option>
                                                        <option value="4">4 </option>
                                                        <option value="5">5 </option>
                                                        <option value="6">6 </option>
                                                        <option value="7">7 </option>
                                                        <option value="8">8 </option>
                                                        <option value="9">9 </option>
                                                        <option value="10">10 </option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Niños</label>
                                                    <select class="form-control">
                                                        <option value="0">0 </option>
                                                        <option value="1">1 </option>
                                                        <option value="2">2 </option>
                                                        <option value="3">3 </option>
                                                        <option value="4">4 </option>
                                                        <option value="5">5 </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prod-editar">
                                        <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                    </div>
                                    <div class="prod-precio">
                                        <span class="producto-precio--num"><small>s/.</small>3245.5</span>
                                    </div>
                                    <div class="prod-acomodacion">
                                        <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                    </div>
                                 </li>
                            </draggable>
                            <!-- 3 ------------------------------------------ // -->
                            <draggable tag="ul"   handle=".handle">
                                <li class="producto">
                                        <div class="prod-acciones d-flex align-items-center">
                                            <i class="icon icon-drag handle"></i>
                                            <input style="" class="form-check-input" type="checkbox" @change="">
                                            <a href="#"><i class="icon icon-plus-circle"></i></a>
                                        </div>
                                        <div class="prod-fecha">
                                            <a href="#" class="link">17/07/2019</a>
                                        </div>
                                        <div class="prod-estado">
                                            <span class="estado estado-ok"></span>
                                        </div>
                                        <div class="prod-descripcion">
                                            <span class="id">[CUZ111]</span>
                                            <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                            <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                        </div>
                                        <div class="prod-tipo">
                                            <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                        </div>
                                        <div class="prod-detalle">
                                            <span class="adt">4<small>adultos</small></span>
                                            <span class="chd">2<small>niños</small></span>
                                        </div>
                                        <div class="prod-editar">
                                            <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                        </div>
                                        <div class="prod-precio">
                                            <span class="producto-precio--num"><small>s/.</small>841.0</span>
                                        </div>
                                        <div class="prod-acomodacion">
                                            <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                        </div>
                                        <!-- agrupado -->
                                        <div class="content_services_group">
                                            <div class="prod-acciones d-flex align-items-center">
                                            </div>
                                            <div class="prod-fecha">
                                                <a href="#" class="link">17/07/2019</a>
                                            </div>
                                            <div class="prod-estado">
                                                <span class="estado estado-ok"></span>
                                            </div>
                                            <div class="prod-descripcion">
                                                <span class="id">[CUZ111]</span>
                                                <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                                <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                            </div>
                                            <div class="prod-tipo">
                                                <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                            </div>
                                            <div class="prod-detalle">
                                                <span class="adt">4<small>adultos</small></span>
                                                <span class="chd">2<small>niños</small></span>
                                            </div>
                                            <div class="prod-editar">
                                                <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                            </div>
                                            <div class="prod-precio">
                                                <span class="producto-precio--num"><small>s/.</small>841.0</span>
                                            </div>
                                            <div class="prod-acomodacion">
                                                <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                            </div>
                                        </div>
                                        <!-- agrupado -->
                                        <div class="content_services_group">
                                            <div class="prod-acciones d-flex align-items-center">
                                            </div>
                                            <div class="prod-fecha">
                                                <a href="#" class="link">17/07/2019</a>
                                            </div>
                                            <div class="prod-estado">
                                                <span class="estado estado-ok"></span>
                                            </div>
                                            <div class="prod-descripcion">
                                                <span class="id">[CUZ111]</span>
                                                <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                                <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                            </div>
                                            <div class="prod-tipo">
                                                <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                            </div>
                                            <div class="prod-detalle">
                                                <span class="adt">4<small>adultos</small></span>
                                                <span class="chd">2<small>niños</small></span>
                                            </div>
                                            <div class="prod-editar">
                                                <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                            </div>
                                            <div class="prod-precio">
                                                <span class="producto-precio--num"><small>s/.</small>841.0</span>
                                            </div>
                                            <div class="prod-acomodacion">
                                                <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                            </div>
                                        </div>
                                        <!-- agrupado -->
                                        <div class="content_services_group">
                                            <div class="prod-acciones d-flex align-items-center">
                                            </div>
                                            <div class="prod-fecha">
                                                <a href="#" class="link">17/07/2019</a>
                                            </div>
                                            <div class="prod-estado">
                                                <span class="estado estado-ok"></span>
                                            </div>
                                            <div class="prod-descripcion">
                                                <span class="id">[CUZ111]</span>
                                                <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                                <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                            </div>
                                            <div class="prod-tipo">
                                                <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                            </div>
                                            <div class="prod-detalle">
                                                <span class="adt">4<small>adultos</small></span>
                                                <span class="chd">2<small>niños</small></span>
                                            </div>
                                            <div class="prod-editar">
                                                <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                            </div>
                                            <div class="prod-precio">
                                                <span class="producto-precio--num"><small>s/.</small>841.0</span>
                                            </div>
                                            <div class="prod-acomodacion">
                                                <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                            </div>
                                        </div>

                                 </li>
                            </draggable>
                            <!-- 4 ------------------------------------------ // -->
                            <draggable tag="ul"   handle=".handle">
                                <li class="producto">
                                    <div class="prod-acciones d-flex align-items-center">
                                        <i class="icon icon-drag handle"></i>
                                        <input style="" class="form-check-input" type="checkbox" @change="">
                                        <a href="#"><i class="icon icon-plus-circle"></i></a>
                                    </div>
                                    <div class="prod-fecha">
                                        <a href="#" class="link">18/07/2019</a>
                                    </div>
                                    <div class="prod-estado">
                                        <span class="estado estado-rq"></span>
                                    </div>
                                    <div class="prod-descripcion">
                                        <span class="id">[CUZ111]</span>
                                        <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                        <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                    </div>
                                    <div class="prod-tipo">
                                        <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                    </div>
                                    <div class="prod-detalle">
                                        <span class="adt">4<small>adultos</small></span>
                                        <span class="chd">2<small>niños</small></span>
                                    </div>
                                    <div class="prod-editar">
                                        <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                    </div>
                                    <div class="prod-precio">
                                        <span class="producto-precio--num"><small>s/.</small>17245.2</span>
                                    </div>
                                    <div class="prod-acomodacion">
                                        <span class="producto-acomodacion--cambiar btn btn-icon" title="Asignar Lista"><i class="icon icon-user-switch"></i></span>
                                    </div>
                                 </li>
                            </draggable>
                            <!-- 5 ------------------------------------------ // -->
                            <draggable tag="ul"   handle=".handle">
                                <li class="producto">
                                    <div class="prod-acciones d-flex align-items-center">
                                        <i class="icon icon-drag handle"></i>
                                        <input style="" class="form-check-input" type="checkbox" @change="">
                                        <a href="#"><i class="icon icon-plus-circle"></i></a>
                                    </div>
                                    <div class="prod-fecha">
                                        <a href="#" class="link">19/07/2019</a>
                                    </div>
                                    <div class="prod-estado">
                                        <span class="estado estado-rq"></span>
                                    </div>
                                    <div class="prod-descripcion">
                                        <span class="id">[CUZ111]</span>
                                        <span class="texto">Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.</span>
                                        <span class="cargar"><a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a></span>
                                    </div>
                                    <div class="prod-tipo">
                                        <div class="btn btn-icon" title="Privado"><i class="icon icon-lock"></i></div>
                                    </div>
                                    <div class="prod-detalle">
                                        <span class="adt">4<small>adultos</small></span>
                                        <span class="chd">2<small>niños</small></span>
                                    </div>
                                    <div class="prod-editar">
                                        <span class="btn btn-icon producto-editar--boton" title="Editar"><i class="icon icon-edit"></i></span>
                                    </div>
                                    <div class="prod-precio">
                                        <span class="producto-precio--num"><small>s/.</small>25.5</span>
                                    </div>
                                    <div class="prod-acomodacion">
                                        <span class="producto-acomodacion--cambiar btn btn-icon" title="Lista Asignada"><i class="icon icon-user-check"></i></span>
                                    </div>
                                 </li>
                            </draggable>
                        </div>
                    </div>
                </div>
                <div class="col-12 cotizacion-incluir d-flex justify-content-end">
                    <div class="btn btn-secondary" data-toggle="modal" data-target="#modal-hotel">+ Hotel</div>
                    <div class="btn btn-secondary" data-toggle="modal" data-target="#modal-servicios">+ Servicio</div>
                    <div class="btn btn-secondary" data-toggle="modal" data-target="#">+ Extension</div>
                </div>
                <div class="col-12 cotizacion-cotizar">
                    <div class="btn btn-primary">Cotizar</div>
                </div>
            </div>
        </div>
    </section>
    <div class="modales">
        {{--          modal-pax           --}}
        <div class="modal fade modal-extensiones" id="modal-pax" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <h2><strong>Datos del pasajero</strong></h2>
                                </div>
                                <div class="d-flex justify-content-start mb-2">
                                    <p class="col-7">Ingresar la informacion solicitada: <strong> 10</strong> adultos  <strong> 1</strong> niño</p>
                                    <div class="col-7">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input radio" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="1" v-model="modePassenger">
                                            <label class="form-check-label" for="inlineRadio1">Modo básico</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input radio" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="2" v-model="modePassenger">
                                            <label class="form-check-label" for="inlineRadio2">Modo completo</label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <a href="javascript:;"><i class="icon-share"></i> Cargar lista</a>
                                    </div>
                                </div>
                            </div>
                            <!----------- Modo Basico ----------->
                            <div>
                                <div class="row mt-3">
                                    <div class="col-12 form-check form-check-inline pl-4">
                                        <input class="form-check-input" type="checkbox" name="inlineOption" id="inline1" value="1" v-model="repeatPassenger">
                                        <label class="form-check-label" for="inline1">Repetir datos del 1er pasajero</label>
                                    </div>
                                </div>

                                <div v-if="repeatPassenger == 1">
                                    <div class="row mt-5">
                                        <div class="d-flex justify-content-start pl-4">
                                            <form class="form-inline">
                                                <div class="form-group information">
                                                    <label class="pr-5"><strong>Pasajero</strong></label>
                                                    <input type="text" class="form-control name ml-5" placeholder="Nombres"/>
                                                    <input type="text" class="form-control last-name ml-5" placeholder="Apellidos"/>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <hr class="mt-5 mb-5">
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary save">Guardar datos</button>
                                    </div>
                                </div>
                            </div>
                            <!----------- End Modo Basico ----------->
                            <!----------- Modo Basico 10personas ----------->
                            <div v-if="modePassenger == 1 && repeatPassenger != 1">
                                <div class="row">
                                    <div class="mt-5 col-6 d-flex justify-content-start pl-4">
                                        <form class="form-inline">
                                            <div class="form-group information-basic">
                                                <label class="pr-3">
                                                    <strong>Pasajero 1</strong>
                                                </label>
                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="mt-5 col-6 d-flex justify-content-start pl-4">
                                        <form class="form-inline">
                                            <div class="form-group information-basic">
                                                <label class="pr-3">
                                                    <strong>Pasajero 2</strong>
                                                </label>
                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="mt-5 col-6 d-flex justify-content-start pl-4">
                                        <form class="form-inline">
                                            <div class="form-group information-basic">
                                                <label class="pr-3">
                                                    <strong>Pasajero 3</strong>
                                                </label>
                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="mt-5 col-6 d-flex justify-content-start pl-4">
                                        <form class="form-inline">
                                            <div class="form-group information-basic">
                                                <label class="pr-3">
                                                    <strong>Pasajero 3</strong>
                                                </label>
                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <hr class="mt-5 mb-5">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary save">Guardar datos</button>
                                </div>
                            </div>
                            <!----------- End Modo Basico 10personas ----------->
                            <!----------- Modo completo ----------->
                            <div v-if="modePassenger == 2 && repeatPassenger != 1">
                                <div class="mt-5">
                                    <div id="accordion" role="tablist">
                                        <div class="card mb-3">
                                            <button class="b-left" data-toggle="collapse" v-bind:href="'#collapse_' + 1" aria-expanded="true" v-bind:aria-controls="'collapse_' + 1">
                                                <div class="card-header d-flex" role="tab" v-bind:id="'heading_' + 1">
                                                    Pasajero 1
                                                </div>
                                            </button>
                                            <div v-bind:id="'collapse_' + 1" v-bind:class="['collapse', (1 == 1) ? 'show' : '']" role="tabpanel" v-bind:aria-labelledby="'heading_' + 1" data-parent="#accordion">
                                                <div class="card-body information-complete">
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                                                <select class="form-control ml-3">
                                                                    <option value="">Sexo</option>
                                                                    <option value="M">Masculino</option>
                                                                    <option value="F">Femenino</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control ml-3" placeholder="Fecha de nacimiento"/>
                                                                <select class="form-control ml-3">
                                                                    <option value="">Tipo de documento</option>
                                                                    <option value="PAS">PASAPORTE</option>
                                                                </select>
                                                                <input type="text" class="form-control ml-3" placeholder="Numero del documento">
                                                                <select class="form-control ml-3">
                                                                    <option value="">Pais del documento</option>
                                                                    <option value="IT">ITALIA</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group information">
                                                                <input type="text" class="form-control email ml-3" placeholder="Email">
                                                                <input type="text" class="form-control ml-3" placeholder="Telefono movil">
                                                                <!-- input type="text" class="form-control ml-3" placeholder="localizador" -->
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form action="form-inline">
                                                            <div class="form-group">
                                                                <textarea class="form-control txt-notas ml-3" rows="3" placeholder="Notas del pasajero"></textarea>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card mb-3">
                                            <button class="b-left" data-toggle="collapse" v-bind:href="'#collapse_' + 2" aria-expanded="true" v-bind:aria-controls="'collapse_' + 2">
                                                <div class="card-header d-flex" role="tab" v-bind:id="'heading_' + 2">
                                                    Pasajero 2
                                                </div>
                                            </button>
                                            <div v-bind:id="'collapse_' + 2" v-bind:class="['collapse', (2 == 1) ? 'show' : '']" role="tabpanel" v-bind:aria-labelledby="'heading_' + 2" data-parent="#accordion">
                                                <div class="card-body information-complete">
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control ml-3" placeholder="Nombres"/>
                                                                <input type="text" class="form-control ml-3" placeholder="Apellidos"/>
                                                                <select class="form-control ml-3">
                                                                    <option value="">Sexo</option>
                                                                    <option value="M">Masculino</option>
                                                                    <option value="F">Femenino</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control ml-3" placeholder="Fecha de nacimiento"/>
                                                                <select class="form-control ml-3">
                                                                    <option value="">Tipo de documento</option>
                                                                    <option value="PAS">PASAPORTE</option>
                                                                </select>
                                                                <input type="text" class="form-control ml-3" placeholder="Numero del documento">
                                                                <select class="form-control ml-3">
                                                                    <option value="">Pais del documento</option>
                                                                    <option value="IT">ITALIA</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form class="form-inline">
                                                            <div class="form-group information">
                                                                <input type="text" class="form-control email ml-3" placeholder="Email">
                                                                <input type="text" class="form-control ml-3" placeholder="Telefono movil">
                                                                <!-- input type="text" class="form-control ml-3" placeholder="localizador" -->
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                                        <form action="form-inline">
                                                            <div class="form-group">
                                                                <textarea class="form-control txt-notas ml-3" rows="3" placeholder="Notas del pasajero"></textarea>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-5">
                                        <button class="btn btn-primary save">Guardar datos</button>
                                    </div>
                                    <!----------- End Modo Completo----------->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--        End modal-pax         --}}

        {{--          modal-notas           --}}
        <div class="modal fade" id="modal-notas" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-block" style="margin:-20px;">
                            <div class="mb-5">
                                <h2> <i class="icon-file-text"></i> NOTAS</h2>
                            </div>
                            <div class="container box-content">
                                <div class="row mt-4">
                                    <div class="col-12 d-flex justify-content-start align-items-center">
                                        <div class="ml-4">
                                            <b-button v-on:click="" class="btn btn-danger btn-lg ml-5">Nueva nota</b-button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 ml-4">
                                    <form class=" mt-3">
                                        <div class="form-group">
                                            <label><strong>Nota nueva</strong></label>
                                            <textarea rows="4" class="form-control" placeholder="Escribe aquí tu mensaje ...."></textarea>
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-end">
                                            <p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p>
                                            <b-button class="btn btn-danger btn-lg m-2" v-on:click="">Guardar nota</b-button>
                                            <b-button v-on:click="" class="btn btn-inverse btn-lg m-2">Regresar</b-button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="content-comments">
                                <div class="comment-box p-4 mb-4">
                                    <!-- Avatar -->
                                    <div class="commet-avatar">
                                        <img src="http://a06.t26.net/taringa/avatares/A/6/F/9/A/0/OK/48x48_BCD.jpg" alt="" />
                                    </div>
                                    <!-- Contenedor del Comentario -->
                                    <div class="box">
                                        <div class="commet-head mb-1">
                                            <h4>Oliver Jones</h4>
                                            <div class="d-flex justify-content-start">
                                                <span class="text-muted mr-4">Julio 10,2019 - 16:22hrs  </span>
                                                <a class="text-muted" href="#">Editar</a>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, fuga nam pariatur repudiandae asperiores itaque error eligendi, illum sequi adipisci vel quod sapiente deserunt? Animi quasi possimus dolorum nihil reiciendis?
                                        </div>
                                    </div>
                                </div>
                                <div class="comment-box p-4 mb-4">
                                    <!-- Avatar -->
                                    <div class="commet-avatar">
                                        <img src="http://a06.t26.net/taringa/avatares/A/6/F/9/A/0/OK/48x48_BCD.jpg" alt="" />
                                    </div>
                                    <!-- Contenedor del Comentario -->
                                    <div class="box">
                                        <div class="commet-head mb-1">
                                            <h4>Oliver Jones</h4>
                                            <div class="d-flex justify-content-start">
                                                <span class="text-muted mr-4">Julio 10,2019 - 16:22hrs  </span>
                                                <a class="text-muted" href="#">Editar</a>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, fuga nam pariatur repudiandae asperiores itaque error eligendi, illum sequi adipisci vel quod sapiente deserunt? Animi quasi possimus dolorum nihil reiciendis?
                                        </div>
                                    </div>
                                    <!-- Respuesta al Comentario -->
                                    <hr class="ml-10">
                                    <div class="commet-response p-3">
                                        <div class="commet-icon">
                                            <i class="icon-corner-down-right"></i>
                                        </div>
                                        <!-- Avatar -->
                                        <div class="commet-avatar">
                                            <img src="http://a12.t26.net/taringa/avatares/4/C/2/7/3/2/-UnaZorrita/48x48_7E6.jpg" alt="" />
                                        </div>
                                        <!-- Contenedor del Comentario -->
                                        <div class="box">
                                            <div class="commet-head mb-1">
                                                <h4>Melania Blur</h4>
                                                <div class="d-flex justify-content-start">
                                                    <span class="text-muted mr-4">Julio 10,2019 - 16:22hrs  </span>
                                                    <a class="text-muted" href="#">Editar</a>
                                                </div>
                                            </div>
                                            <div class="comment-content">
                                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, fuga nam pariatur repudiandae asperiores itaque error eligendi, illum sequi adipisci vel quod sapiente deserunt? Animi quasi possimus dolorum nihil reiciendis?
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Respuesta al Comentario -->
                                    <hr class="ml-10">
                                    <div class="commet-response p-3">
                                        <div class="commet-icon">
                                            <i class="icon-corner-down-right"></i>
                                        </div>
                                        <!-- Avatar -->
                                        <div class="commet-avatar">
                                            <img src="http://a06.t26.net/taringa/avatares/A/6/F/9/A/0/OK/48x48_BCD.jpg" alt="" />
                                        </div>
                                        <!-- Contenedor del Comentario -->
                                        <div class="box">
                                            <div class="commet-head mb-1">
                                                <h4>Oliver Jones</h4>
                                                <div class="d-flex justify-content-start">
                                                    <span class="text-muted mr-4">Julio 10,2019 - 16:22hrs  </span>
                                                    <span class="text-muted mr-4">  (Editado)  </span>
                                                    <a class="text-muted" href="#">Editar</a>
                                                </div>
                                            </div>
                                            <div class="comment-content">
                                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, fuga nam pariatur repudiandae asperiores itaque error eligendi, illum sequi adipisci vel quod sapiente deserunt? Animi quasi possimus dolorum nihil reiciendis?
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="comment-box p-4 mb-4">
                                    <!-- Avatar -->
                                    <div class="commet-avatar">
                                        <img src="http://a12.t26.net/taringa/avatares/4/C/2/7/3/2/-UnaZorrita/48x48_7E6.jpg" alt="" />
                                    </div>
                                    <!-- Contenedor del Comentario -->
                                    <div class="box">
                                        <div class="commet-head mb-1">
                                            <h4>Melania Blur</h4>
                                            <div class="d-flex justify-content-start">
                                                <span class="text-muted mr-4">Julio 10,2019 - 16:22hrs  </span>
                                                <a class="text-muted" href="#">Editar</a>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, fuga nam pariatur repudiandae asperiores itaque error eligendi, illum sequi adipisci vel quod sapiente deserunt? Animi quasi possimus dolorum nihil reiciendis?
                                        </div>
                                    </div>
                                    <!-- Respuesta al Comentario -->
                                    <hr class="ml-10">
                                    <div class="commet-response p-3">
                                        <div class="response ml-10">
                                            <i class="icon-corner-down-right" id="ico"></i>
                                            <a href="#" @click="viewFormResponse()" id="res"> Responder</a>
                                        </div>
                                        <div class="ml-10">
                                            <form class=" mt-3" v-show="showFormResponse">
                                                <div class="form-group">
                                                    <textarea rows="4" class="form-control" placeholder="Escribe aquí tu mensaje ...."></textarea>
                                                </div>
                                                <div class="form-group d-flex align-items-center justify-content-end">
                                                    <p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p>
                                                    <b-button class="btn btn-danger btn-lg m-2" v-on:click="">Guardar nota</b-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--        End modal-notas         --}}

        {{--          modal-rango           --}}
        <div class="modal fade modal-extensiones" id="modal-rank" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <table class="table justify-content-center">
                            <thead>
                                <tr>
                                <th scope="col" class="col-ini">#</th>
                                <th scope="col" class="col">Desde</th>
                                <th scope="col" class="col">Hasta</th>
                                <th scope="col" class="col">Simple</th>
                                <th scope="col" class="col">Doble</th>
                                <th scope="col" class="col">Triple</th>
                                <th scope="col" class="col-fin">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <th scope="row" class="th">1</th>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                </tr>
                                <tr>
                                <th scope="row" class="th">2</th>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                </tr>
                                <tr>
                                <th scope="row" class="th">3</th>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="1"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="208.50"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td"><input type="text" class="form-control" placeholder="0.00"/></td>
                                <td nowrap class="td icon"><a class="" title=""><i class="icon-plus-square"></i></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{--        End modal-rango         --}}

        {{--          modal-hotel           --}}
        <div class="modal fade modal-extensiones" id="modal-hotel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <h3>Añadir hoteles</h3>
                        </div>

                        <div class="form-content">
                            <form class="form">
                                <div class="form-row d-flex justify-content-between align-items-center">
                                    <div class="form-group">
                                        <label for="">Fecha:</label>
                                        <date-picker class="date form-control" v-model="add_hotel_date" :config="optionsR"></date-picker>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Categorias:</label>
                                        <select class="form-control" id="Categorias">
                                            <option>Turista</option>
                                            <option>Turista superior</option>
                                            <option>Primera</option>
                                            <option>Primera superior</option>
                                            <option>Lujo</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Destinos:</label>
                                        <select class="form-control" id="Destino">
                                            <option>Lima</option>
                                            <option>Puno</option>
                                            <option>Arequipa</option>
                                            <option>Cusco</option>
                                            <option>Tacna</option>
                                            <option>Puno</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Noches:</label>
                                        <select class="form-control" id="Destino">
                                            <option>Lima</option>
                                            <option>Puno</option>
                                            <option>Arequipa</option>
                                            <option>Cusco</option>
                                            <option>Tacna</option>
                                            <option>Puno</option>
                                        </select>
                                    </div>
                                    <div class="form-group cotizacion-crear--boton">
                                        <button class="btn btn-primary"><i class="icon-search"></i></button>
                                    </div>

                                </div>
                                <div>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Filtrar por la palabra ..." aria-label="Recipient's username" aria-describedby="button-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="button-addon2"><i class="icon-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive table-results">
                                <table class="table">
                                    <thead>
                                        <tr>
                                        <th scope="col" class=""> </th>
                                        <th scope="col" class="">Codigo</th>
                                        <th scope="col" class="">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                        Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                            Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                            Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--        End modal-hotel         --}}

        {{--        modal-servicios         --}}
        <div class="modal fade modal-extensiones" id="modal-servicios" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <h3>Añadir Servicios</h3>
                        </div>

                        <div class="form-content">
                            <form class="form">
                                <div class="form-row d-flex justify-content-between align-items-end">
                                    <div class="form-group">
                                        <label for="">Fecha:</label>
                                        <date-picker class="date form-control" v-model="add_service_date" :config="optionsR"></date-picker>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Filtrar por la palabra:</label>
                                        <input type="text" class="form-control filter" id="palabra" placeholder="Filtrar por la palabra ...">
                                    </div>
                                    <div class="form-group cotizacion-crear--boton">
                                        <button class="btn btn-primary"><i class="icon-search"></i></button>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive table-results">
                                <table class="table">
                                    <thead>
                                        <tr>
                                        <th scope="col" class=""> </th>
                                        <th scope="col" class="">Codigo</th>
                                        <th scope="col" class="">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                        Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                            Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                        <tr>
                                        <th scope="row"><i class="fas fa-circle"></i></th>
                                        <td>CUZ111</td>
                                        <td>
                                            Tour privado de medio día a la ciudad de Cusco con visita a Koricancha, Catedral, Sacsayhuamán, Quenqo, Puca Pucara y Tambomachay.<a href="#" class="link" data-toggle="modal" data-target="#modalVerDetalle">Ver más</a>
                                        </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
        {{--      End modal-servicios       --}}

        {{--        modal-extension      --}}
        <div class="modal fade modal-extensiones" id="modal-servicios" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-5">
                            <h3>Añadir Servicios</h3>
                        </div>
                        <div>

carrusel
                        </div>


                        <hr>
                    </div>
                </div>
            </div>
        </div>
        {{--      End modal-extension       --}}
    </div>


    <div class="cotizacion-modals">
        <div class="modal fade modal-extensiones" id="modalEditar" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <h3 id="myLargeModalLabel" class="modal-title"><strong>Editar Servicio</strong></h3>
                        <center>...</center>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade modal-extensiones" id="modalVerDetalle" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <h3 id="myLargeModalLabel" class="modal-title"><strong>Detalles del Servicio</strong></h3>
                        <center>...</center>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--    <div class="btn btn-icon" title="Cambiar a Privado"><i class="icon icon-globe-switch"></i></div>--}}
@endsection
@section('css')
<style>
.bootstrap-datetimepicker-widget table td.active, .bootstrap-datetimepicker-widget table td.active:hover {
    background-color: #A71B20;
    color: #ffffff;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}



</style>
@endsection

@section('js')
    <script>

        let id = 3;
        new Vue({
            el: '#app',
            data: {
                loading:false,
                quote_name:'Prueba',
                quote_date:'',
                add_service_date:'',
                add_hotel_date:'',
                package_selected: [],
                categories: [],
                modePassenger: 1,
                repeatPassenger: 0,
                checkedAllCategories:false,
                options: {
                    format: 'ddd Do MMM YYYY',
                    useCurrent: false,
                    locale: 'es',
                },
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                },
                showFormResponse: false,
            },
            created: function () {
                this.getPackagesSelected()
            },
            mounted() {
              this.searchCategories()
              let _today = this.getTodayFormat()
              this.quote_date = _today
              this.add_service_date = _today
              this.add_hotel_date = _today
            },
            computed: {
            },
            methods: {
                getTodayFormat : function() {
                    ahora = new Date();

                    dia = ahora.getDate();
                    anoActual = ahora.getFullYear();
                    mesActual = ahora.getMonth() + 1;
                    mesActual = (mesActual <= 9) ? '0' + mesActual : mesActual;
                    diaActual = (dia <= 9) ? '0' + dia : dia;
                    inicio = diaActual + '/' + mesActual + '/' + anoActual;
                    return inicio;
                },
                formatDate: function (_date, charFrom, charTo, orientation) {
                  console.log( _date )
                    _date = _date.split(charFrom)
                    _date =
                      ( orientation )
                        ? _date[2] + charTo + _date[1] + charTo + _date[0]
                        : _date[0] + charTo + _date[1] + charTo + _date[2]
                    return _date
                },
                newQuote(){

                    if( this.quote_name == ""){
                      this.$toast.warning( "Por favor ingrese un nombre a la cotización", {
                        position: 'top-right'
                      })
                      return
                    }

                    if( this.quote_date == ""){
                      this.$toast.warning( "Por favor ingrese una fecha de inicio", {
                        position: 'top-right'
                      })
                      return
                    }

                  category_ids = []
                    this.categories.forEach( _c =>{
                      if( _c.check ){
                        category_ids.push( _c.id )
                      }
                    } )

                    if( category_ids.length == 0){
                      this.$toast.warning( "Por favor seleccione al menos una categoría", {
                        position: 'top-right'
                      })
                      return
                    }

                    this.loading = true

                    let data = {
                        name : this.quote_name,
                        date : this.formatDate( this.quote_date, '/', '-', 1 ),
                        categories : category_ids,
                        service_type_id : 1
                    }
                    console.log( data )
                    axios.post('api/quotes', data).then(response => {

                        console.log(response)
                        this.loading = false
                    }).catch(error => {
                          this.$toast.error( "Ocurrió un error interno", {
                            position: 'top-right'
                          })
                        console.log(error)
                      this.loading = false
                    })
                },
                toggleAllCategories(){
                    this.categories.forEach( c => {
                      c.check = this.checkedAllCategories
                    } )
                },
                toggleCategory(cate){
                  cate.check = cate.check
                },
                searchCategories(){
                  axios.get('api/typeclass/selectbox?lang='+localStorage.getItem('lang')).then(response => {

                    let _categories = []

                    response.data.data.forEach( c => {
                      c.check = false
                      if( c.code != "X" && c.code != "x" ){
                        _categories.push(c)
                      }
                    } )

                    this.categories = _categories
                  }).catch(error => {
                    console.log(error)
                  })
                },
                getPackagesSelected:function(){
                    axios.get(baseExternalURL + 'api/packages/selected')
                        .then((result) => {
                            this.package_selected = result.data
                        })
                },
                viewFormResponse: function() {
                    document.getElementById("ico").className = "response-disable";
                    document.getElementById("res").className = "response-disable";
                    this.showFormResponse = true

                }
            }
        })
    </script>
    <script>

    </script>
@endsection
