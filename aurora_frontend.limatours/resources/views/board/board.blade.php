@extends('layouts.app')
@section('content')
<section class="page-board">
    <div v-if="loading">
        <div id="loading-state" class="text-center py-5 my-5">
            <div class="spinner-border mb-3" role="status" style="color: #8B1D1D; width: 3.5rem; height: 3.5rem; border-width: 0.25em;">
                <span class="d-none visually-hidden">Cargando...</span>
            </div>
            <h4 class="fw-bold mt-2" style="color: #8B1D1D;">Cargando información...</h4>
            <p class="text-muted">Preparando su panel personalizado, {{ auth()->user()->name }}.</p>
        </div>
    </div>
    <div class="container-fluid" v-if="!loading">
        <div class="container" v-if="Object.keys(executives).length > 0 || (executive && bossFlag == 0)">
            <div class="tabs-board tabs">
                <ul class="nav nav-primary text-center">
                    <li class="nav-item">
                        <a v-bind:class="[(tab == 'pendings') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('pendings')"><i class="icon-chevron-down"></i>{{ trans('board.label.my_pendings') }}</a>
                    </li>
                    <li class="nav-item" v-if="bossFlag == 0">
                        <a v-bind:class="[(tab == 'order_report') ? 'active' : '', 'nav-link' ]" href="javascript:;" @click="toggleView('order_report')"><i class="icon-chevron-down"></i>{{ trans('board.label.order_report') }}</a>
                    </li>
                </ul>
                <hr>
            </div>
        </div>
        <!---------- Mis pendientes ---------->
        <div class="pendientes container" v-if="tab == 'pendings'">
            <!------- Cabecera ------->
            <div class="filtros m-5" v-if="bossFlag == 0">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-5">
                            <ol class="box">
                                <li class="box-item">{{ trans('board.label.summary') }}: </li>
                                <li class="box-item"><a href="#">@{{ dashboard.files_totales }} files</a></li>
                                <li class="breadcrumb-item box-item"><a href="#">@{{ dashboard.vips  }} vip</a></li>
                                <li class="breadcrumb-item "><a href="#">@{{ dashboard.tareas_totales }} tareas</a></li>
                            </ol>
                        </div>
                        <div class="col-4">
                            <ol class="box filter colors-filters">
                                <li class="box-item">{{ trans('board.label.filters') }}: </li>
                                <li class="box-item">
                                    <a href="#" role="button">
                                        <i class="icon-filter-fire min-7"></i>
                                    </a>
                                </li>
                                <li class="box-item">
                                    <a href="#" role="button">
                                        <i class="icon-filter-flame min-15"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-filter-leaf min-30"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-filter-plant max-30"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-star val-0"></i>
                                    </a>
                                </li>
                            </ol>
                        </div>

                        <div class="col-3">
                            <ol class="box">
                                <li class="box-item">{{ trans('board.label.specialist') }}: </li>
                                <li class="box-item">
                                    <b-form-select v-model="executive" @change="filterExecutive()"
                                        :options="executives" class="ml-1"
                                        style="margin-top:-5px !important; max-width:110px !important;">
                                    </b-form-select>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!------- End cabecera ------->

            <!------- Cabecera Jefes ------->
            <div class="filtros m-5" v-if="bossFlag == 1 && Object.keys(executives).length > 0">
                <div class="col p-0">
                    <div class="row">
                        <div class="col" v-if="Object.keys(modulesFilter).length > 0">
                            <ol class="box">
                                <li class="box-item">{{ trans('board.label.modules') }}: </li>
                                <li class="box-item">
                                    <b-form-select v-model="moduleFilter" @change="filterModule()"
                                        :options="modulesFilter" class="ml-1"
                                        style="margin-top:-5px !important; max-width:135px !important;">
                                    </b-form-select>
                                </li>
                            </ol>
                        </div>
                        <div class="col-auto">
                            <ol class="box filter colors-filters">
                                <li class="box-item">{{ trans('board.label.filters') }}: </li>
                                <li class="box-item">
                                    <a href="#" role="button">
                                        <i class="icon-filter-fire min-7"></i>
                                    </a>
                                </li>
                                <li class="box-item">
                                    <a href="#" role="button">
                                        <i class="icon-filter-flame min-15"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-filter-leaf min-30"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-filter-plant max-30"></i>
                                    </a>
                                </li>
                                <li class="box-item font">
                                    <a href="#" role="button">
                                        <i class="icon-star val-0"></i>
                                    </a>
                                </li>
                            </ol>
                        </div>

                        <div class="col-auto">
                            <ol class="box">
                                <li class="box-item">{{ trans('board.label.boss') }}: </li>
                                <li class="box-item">{{ auth()->user()->code }} - {{ auth()->user()->name }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!------- End cabecera Jefes ------->
            <!------- Tareas por hacer ------->
            <div :class="(bossFlag != bossFlagSearch) ? 'row' : ''" v-if="Object.keys(executives).length > 0 || (executive && bossFlag == 0)">
                <div v-bind:class="[(bossFlag == bossFlagSearch && bossFlag == 1) ? 'col-lg-12' : 'col-lg-7', 'col']" style="padding-top: 3rem;">
                    <h4 class="d-flex m-0 justify-content-between align-items-center" v-if="bossFlag != bossFlagSearch">
                        {{ trans('board.label.specialist') }}: @{{ executive }}
                        <button class="btn btn-primary" v-on:click="regresar()">
                            {{ trans('board.btn.back') }}
                        </button>
                    </h4>
                    <div class="table-responsive-sm">
                        <table class="mt-5" v-bind:class="[(bossFlag == bossFlagSearch && bossFlag == 1) ? 'table-jefes' : '', 'table' ]">
                            <thead>
                                <tr style="border-top: none;">
                                    <th scope="col" class="d-flex justify-content-end"></th>
                                    <th scope="col" class="d-flex justify-content-end">@{{ (bossFlag == 0) ? task_to_do : executive }}</th>
                                    <th scope="col" class="text-center">
                                        <div>@{{ dashboard.response_7.tareas }}</div>
                                        <div class="text-muted">{{ trans('board.label.less_than_7_days') }}</div>
                                    </th>
                                    <th scope="col" class="text-center">
                                        <div>@{{ dashboard.response_15.tareas }}</div>
                                        <div class="text-muted">{{ trans('board.label.less_than_15_days') }}</div>
                                    </th>
                                    <th scope="col" class="text-center">
                                        <div>@{{ dashboard.response_30.tareas }}</div>
                                        <div class="text-muted">{{ trans('board.label.less_than_30_days') }}</div>
                                    </th>
                                    <th scope="col" class="text-center">
                                        <div>@{{ dashboard.response_mes.tareas }}</div>
                                        <div class="text-muted">{{ trans('board.label.more_than_30_days') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-center colors-filters">
                                <!-- Tablero para Especialistas -->
                                <tr v-for="(module, m) in modules" v-if="bossFlag != bossFlagSearch || bossFlag == 0">
                                    <th scope="row">
                                        <small class="text-uppercase" style="font-size: 11px;">@{{ module.value }}</small>
                                    </th>
                                    <td v-bind:class="[ (dashboard.response_7[m] != undefined && dashboard.response_7[m].length > 0) ? 'min-7' : 'val-0' ]">
                                        <span v-on:click="showFiles('response_7', m)">
                                            @{{ (dashboard.response_7[m] != undefined) ? dashboard.response_7[m].length : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_15[m] != undefined && dashboard.response_15[m].length > 0) ? 'min-15' : 'val-0' ]">
                                        <span v-on:click="showFiles('response_15', m)">
                                            @{{ (dashboard.response_15[m] != undefined) ? dashboard.response_15[m].length : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_30[m] != undefined && dashboard.response_30[m].length > 0) ? 'min-30' : 'val-0' ]">
                                        <span v-on:click="showFiles('response_30', m)">
                                            @{{ (dashboard.response_30[m] != undefined) ? dashboard.response_30[m].length : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_mes[m] != undefined && dashboard.response_mes[m].length > 0) ? 'max-30' : 'val-0' ]">
                                        <span v-on:click="showFiles('response_mes', m)">
                                            @{{ (dashboard.response_mes[m] != undefined) ? dashboard.response_mes[m].length : 0 }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Tablero para Jefes -->
                                <tr v-for="(executive, e) in executives" v-if="bossFlagSearch == bossFlag && bossFlag == 1">
                                    <th scope="row" class="d-flex justify-content-end">
                                        <small class="text-uppercase" style="font-size:11px;">@{{ executive }}</small>
                                    </th>
                                    <td v-bind:class="[ (dashboard.response_7[e] != undefined && dashboard.response_7.tareas > 0) ? 'min-7' : 'val-0' ]">
                                        <span v-on:click="filterExecutive(e)">
                                            @{{ (dashboard.response_7[e] != undefined && dashboard.response_7[e].tareas > 0) ? dashboard.response_7[e].quantity : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_15[e] != undefined && dashboard.response_15.tareas > 0) ? 'min-15' : 'val-0' ]">
                                        <span v-on:click="filterExecutive(e)">
                                            @{{ (dashboard.response_15[e] != undefined && dashboard.response_15[e].tareas > 0) ? dashboard.response_15[e].quantity : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_30[e] != undefined && dashboard.response_30.tareas > 0) ? 'min-30' : 'val-0' ]">
                                        <span v-on:click="filterExecutive(e)">
                                            @{{ (dashboard.response_30[e] != undefined && dashboard.response_30[e].tareas > 0) ? dashboard.response_30[e].quantity : 0 }}
                                        </span>
                                    </td>
                                    <td v-bind:class="[ (dashboard.response_mes[e] != undefined && dashboard.response_mes.tareas > 0) ? 'max-30' : 'val-0' ]">
                                        <span v-on:click="filterExecutive(e)">
                                            @{{ (dashboard.response_mes[e] != undefined && dashboard.response_mes[e].tareas > 0) ? dashboard.response_mes[e].quantity : 0 }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="vl" v-if="bossFlag == 0"></div>
                </div>
                <div class="col col-lg-5" v-if="bossFlag != bossFlagSearch || bossFlag == 0">
                    <div class="FilesxCerrar">
                        <h4 class="mb-0">
                            {{ trans('board.label.files_to_close') }}
                            <span class="text-muted ml-3"> / {{ trans('board.label.nearest_date') }}</span>
                        </h4>
                        <hr align="center" width="90%" class="">
                        <table class="table table-borderless">
                            <tbody class="p-0 colors-filters">
                                <tr>
                                    <th>
                                    <td>@{{ dashboard.response_7.tareas }}</td>
                                    <td class="">
                                        <i class="fas fa-circle min-7"></i>
                                    </td>
                                    <td>@{{ dashboard.fecha_7 }}</td>
                                    <td>x@{{ dashboard.response_7.quantity }}</td>
                                    <td><a href="#" v-on:click="showCalendar('7')">{{ trans('board.label.calendar') }}</a></td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td>@{{ dashboard.response_15.tareas }}</td>
                                    <td>
                                        <i class="fas fa-circle min-15"></i>
                                    </td>
                                    <td>@{{ dashboard.fecha_15 }}</td>
                                    <td>x@{{ dashboard.response_15.quantity }}</td>
                                    <td><a href="#" v-on:click="showCalendar('15')">{{ trans('board.label.calendar') }}</a></td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td>@{{ dashboard.response_30.tareas }}</td>
                                    <td>
                                        <i class="fas fa-circle min-30"></i>
                                    </td>
                                    <td>@{{ dashboard.fecha_30 }}</td>
                                    <td>x@{{ dashboard.response_30.quantity }}</td>
                                    <td><a href="#" v-on:click="showCalendar('30')">{{ trans('board.label.calendar') }}</a></td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td>@{{ dashboard.response_mes.tareas }}</td>
                                    <td>
                                        <i class="fas fa-circle max-30"></i>
                                    </td>
                                    <td>{{ trans('board.label.after') }} @{{ dashboard.fecha_30 }}</td>
                                    <td>x@{{ dashboard.response_mes.quantity }}</td>
                                    <td><a href="#" v-on:click="showCalendar('mes')">{{ trans('board.label.calendar') }}</a></td>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="PrioridaddeAtencion">
                        <h4 class="mb-0">
                            {{ trans('board.label.attention_priority') }}
                        </h4>
                        <hr align="center" width="90%" class="">
                        <table class="table table-borderless">
                            <tbody class="text-center colors-filters">
                                <tr>
                                    <th>
                                    <td v-bind:class="[ (dashboard.response_7 != undefined && dashboard.response_7.quantity > 0) ? 'min-7' : '' ]">
                                        <div class="circle">@{{ porcentaje_7 }}%</div>
                                    </td>
                                    <td>
                                        <div class="box-progress">
                                            <div id="progress" class='progress-bar progress-min7' v-bind:style="{ width: porcentaje_7 + '%' }"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_7.quantity }}</b> {{ trans('board.label.files') }}
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_7.tareas }}</b> {{ trans('board.label.works') }}
                                    </td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td v-bind:class="[ (dashboard.response_15 != undefined && dashboard.response_15.quantity > 0) ? 'min-15' : '' ]">
                                        <div class="circle">@{{ porcentaje_15 }}%</div>
                                    </td>
                                    <td>
                                        <div class="box-progress">
                                            <div id="progress" class='progress-bar progress-min15' v-bind:style="{ width: porcentaje_15 + '%' }"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_15.quantity }}</b> {{ trans('board.label.files') }}
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_15.tareas }}</b> {{ trans('board.label.works') }}
                                    </td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td v-bind:class="[ (dashboard.response_30 != undefined && dashboard.response_30.quantity > 0) ? 'min-30' : '' ]">
                                        <div class="circle">@{{ porcentaje_30 }}%</div>
                                    </td>
                                    <td>
                                        <div class="box-progress">
                                            <div id="progress" class='progress-bar progress-min30' v-bind:style="{ width: porcentaje_30 + '%' }"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_30.quantity }}</b> {{ trans('board.label.files') }}
                                    </td>
                                    <td>
                                        <b class="d-block">@{{ dashboard.response_30.tareas }}</b> {{ trans('board.label.works') }}
                                    </td>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                    <td v-bind:class="[ (dashboard.response_mes != undefined && dashboard.response_mes.quantity > 0) ? 'max-30' : '' ]">
                                        <div class="circle">@{{ porcentaje_mes }}%</div>
                                    </td>
                                    <td>
                                        <div class="box-progress">
                                            <div id="progress" class='progress-bar progress-max30' v-bind:style="{ width: porcentaje_mes + '%' }"></div>
                                        </div>
                                    </td>
                                    <td><b class="d-block">@{{ dashboard.response_mes.quantity }}</b> {{ trans('board.label.files') }}</td>
                                    <td><b class="d-block">@{{ dashboard.response_mes.tareas }}</b> {{ trans('board.label.works') }}</td>
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="card border-0 shadow-sm py-5 px-4 mb-4 bg-light" style="border-radius: 15px;">
                    <div class="card-body text-center">
                        <div class="mb-4 text-center d-flex justify-content-center">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                                <span style="color: #8B1D1D;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="4rem" height="4rem" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <h2 class="fw-bold mb-2 text-dark">¡Bienvenid@, {{ auth()->user()->name }}!</h2>
                        <p class="text-muted mb-4 fs-5">
                            Panel de gestión administrativa activo.
                        </p>

                        <div class="d-grid gap-2 d-md-block">
                            <a href="{{ url('account') }}" class="btn btn-lg px-5 shadow-sm"
                                style="background-color: #8B1D1D; color: white; border-radius: 30px;">
                                <i class="bi bi-person-vcard me-2"></i> Ir a mi perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!------- End Tareas por hacer ------->
            <!------- Files proximos a caducar ------->
            <div class="row my-5" id="results" v-if="(bossFlag != bossFlagSearch || bossFlag == 0) && quantity > 0 && !loading">
                <h4 class="colors-filters">
                    <i v-bind:class="[ icon, _class, 'mr-3' ]"></i>
                    @{{ title }}
                    <!-- span class="text-muted ml-3"> Los siguientes files requieren tu atención</span -->
                </h4>
                <div class="col-12">
                    <div class="table-responsive-sm">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-muted">{{ trans('board.th.vip') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.file') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.name') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.customer') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.entry') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.mount') }}</th>
                                    <th scope="col" class="text-muted">#PAX</th>
                                    <th v-for="(module, m) in modules" scope="col" class="text-muted">
                                        <span v-bind:title="module.value" v-b-tooltip.hover>@{{ module.key }}</span>
                                    </th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.notes') }}</th>
                                    <th scope="col" class="text-muted">{{ trans('board.th.itinerary') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr v-for="(file, f) in files">
                                    <th scope="row" class="align-middle" style="color: #BBBDBF;"><i v-bind:class="[ (file.detalle[6] == 'VIP') ? 'icon-star' : '' ]"></i></th>
                                    <td><a v-bind:href='getUrl(f)' target="_blank">@{{ f }}</a></td>
                                    <td>@{{ file.detalle[1]  }}</td>
                                    <td><strong>@{{ file.detalle[2] }}</strong></td>
                                    <td>@{{ file.detalle[3] }}</td>
                                    <td>@{{ file.detalle[5] }}</td>
                                    <td>@{{ file.detalle[4] }}</td>
                                    <td v-for="(module, m) in modules">
                                        <a v-if="m != 'E' && file[m] == 1" v-bind:href="getUrlDetail(file[m], module.url, f)" v-bind:target="getTarget(module.url)" class="a-icon">
                                            <i v-bind:class="[ file[m] == 1 ? 'icon-x' : 'icon-check' ]" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                                        </a>
                                        <a v-if="m == 'E'" href="javascript:;" v-on:click="modalPassengers(f, file.detalle[4])" class="a-icon">
                                            <i v-bind:class="[ file[m] == 1 ? 'icon-x' : 'icon-check' ]" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                                        </a>
                                        <i v-bind:class="[ file[m] == 1 ? 'icon-x' : 'icon-check' ]" v-if="file[m] != 1 && m != 'E'" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                                    </td>
                                    <td><a href="#" v-on:click="loadNotes(f)" class="line-none"><i class="icon-file-text" v-bind:title="[ file.notas ]" v-b-tooltip.hover></i></a></td>
                                    <td><a v-bind:href='getUrlItinerary(f)'>detalles</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row my-5" v-if="(bossFlag != bossFlagSearch || bossFlag == 0) && quantity == 0">
                <div class="d-flex align-items-center justify-content-center p-4"
                    style="width:100%; gap: 20px; background-color: #FFF3CD; border-radius: 8px; border: 1px solid #FFE69C; color: #856404;">

                    <span class="me-3" style="font-size: 2rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder-x" viewBox="0 0 16 16">
                            <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181L15.546 8H14.54l.265-2.91A1 1 0 0 0 13.81 4H2.19a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91H9v1H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zm6.339-1.577A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z" />
                            <path d="M11.854 10.146a.5.5 0 0 0-.707.708L12.293 12l-1.146 1.146a.5.5 0 0 0 .707.708L13 12.707l1.146 1.147a.5.5 0 0 0 .708-.708L13.707 12l1.147-1.146a.5.5 0 0 0-.707-.708L13 11.293z" />
                        </svg>
                    </span>

                    <span class="fw-bold fs-5">{{ trans('board.label.no_data') }}</span>

                    <span class="ms-3" style="font-size: 1.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </span>
                </div>
            </div>
            <!------- End Files proximos a caducar ------->
        </div>
        <!-------- End Mis pendientes -------->

        <div class="reporte-pedidos container align-content-center" v-if="bossFlag == 0 && tab == 'order_report'">
            <div class="box-report m-3">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        {{ trans('board.label.region_summary') }}: @{{ regions }}
                    </div>
                    <div class="form-group mx-4" style="width: 250px;">
                        <date-range-picker
                            :locale-data="locale_data"
                            :time-picker24-hour="timePicker24Hour"
                            :show-week-numbers="showWeekNumbers"
                            :auto-apply="true"
                            :ranges="false"
                            :auto-apply="false"
                            v-model="dateRangeResume">
                        </date-range-picker>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" v-on:click="showDetailOrders()" v-bind:disabled="loadingDetail">
                            {{ trans('board.btn.search') }}
                        </button>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-3" v-if="loadingDetail">
                    <p class="mb-0">{{ trans('board.label.loading') }}</p>
                </div>
                <div class="alert alert-warning mt-3 mb-3" v-if="!loadingDetail && Object.values(sections).length == 0">
                    <p class="mb-0">{{ trans('board.label.no_data') }}</p>
                </div>
                <div v-if="!loadingDetail && quantityOrders > 0">
                    <hr class="hr-result mt-4" />
                    <div class="report-result">
                        <div clas="d-flex justify-content-between mb-3">
                            <div class="d-flex justify-content-around text-center">
                                <div class="box-result d-flex justify-content-between align-items-center">
                                    <span>{{ trans('board.label.worked_quotes') }} </span>
                                    <div>@{{ all_stats.all_quotes }}</div>
                                </div>
                                <div class="box-result d-flex justify-content-between align-items-center">
                                    <span>{{ trans('board.label.quotes_answered_on_time') }} </span>
                                    <div>@{{ all_stats.quotes_ok }}</div>
                                </div>
                                <div class="box-result d-flex justify-content-between align-items-center">
                                    <span>{{ trans('board.label.work_rate') }} </span>
                                    <div>@{{ all_stats.work_rate }}</div>
                                </div>
                            </div>

                            <div class="graphic">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"
                                        v-bind:style="{
                                            'width': '100%',
                                            'background':('linear-gradient(to right' +
                                            ((all_stats.time_response > 0) ? (', #36A2EB 0% ' + all_stats.time_response + '%') : '') +
                                            ((all_stats.time_response > 0 && all_stats.time_response < 90) ? (', #FF6384 ' + all_stats.time_response + '% 90%') : '') +
                                            ', #CCC 0% 100%' +
                                            ')'),
                                            'color': '#FFF'
                                         }"
                                        aria-valuenow="0"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                        <div class="porcentaje">@{{ all_stats.time_response }}%</div>
                                        <div class="descripcion">{{ trans('board.label.percentage_of_response_time') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box-report m-3" v-if="bossFlag == 0 && tab == 'order_report'">
            <hr class="hr-result">
            <div class="row">
                <div v-bind:class="'col' + ((Object.values(sections).length % 2 == 0) ? '-6' : '-4')" v-for="(item, i) in sections">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            @{{ i }} {{-- trans('board.label.orders') --}}
                        </div>
                    </div>
                    <div class="report-result">
                        <div class="d-flex justify-content-around text-center p-5">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">{{ trans('board.th.quantity') }}</th>
                                        <th scope="col">{{ trans('board.th.value') }}(USD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">{{ trans('board.th.orders_received') }}</th>
                                        <td>@{{ item.stats.all_orders }}</td>
                                        <td>@{{ item.stats.mount_all_orders }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ trans('board.th.orders_placed') }}</th>
                                        <td>@{{ item.stats.orders_placed }}</td>
                                        <td>@{{ item.stats.mount_orders_placed }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="graphic">
                            <vc-donut
                                :sections="graph_section[i]" :total="100"
                                :start-angle="0" :auto-adjust-text-size="true"
                                has-legend legend-placement="bottom">
                                @{{ item.stats.all_orders }} {{ trans('board.th.orders_received') }}
                            </vc-donut>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div v-if="(Object.keys(executives).length > 0 || (executive && bossFlag == 0)) && !loading">
    <!------- Modal Calendario ------->
    <b-modal id="calendar" size="md" hide-footer v-model="calendar" class="calender-modal">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><strong>{{ trans('board.label.earrings_calendar') }}</strong></h2>
            </div>
            <div class="box-content">
                <div class="">
                    <div class="d-flex justify-content-start align-items-center">
                        <ol class="list-filter">
                            <li class="box-item">
                                <strong>{{ trans('board.label.files_to_close') }}</strong>
                            </li>
                            <li class="box-item">
                                <span class="mr-3 text-muted">(@{{ dashboard.files_totales }})</span>
                            </li>
                            <li class="box-item">
                                <a href="#" v-on:click="showCalendar('7')" role="button">
                                    <i class="icon-filter-fire min-7"></i>
                                </a>
                                <span class="ml-1 text-muted">(@{{ dashboard.response_7.quantity }})</span>
                            </li>
                            <li class="box-item">
                                <a href="#" v-on:click="showCalendar('15')" role="button">
                                    <i class="icon-filter-flame min-15"></i>
                                </a>
                                <span class="ml-1 text-muted">(@{{ dashboard.response_15.quantity }})</span>
                            </li>
                            <li class="box-item font">
                                <a href="#" v-on:click="showCalendar('30')" role="button">
                                    <i class="icon-filter-leaf min-30"></i>
                                </a>
                                <span class="ml-1 text-muted">(@{{ dashboard.response_30.quantity }})</span>
                            </li>
                            <li class="box-item font">
                                <a href="#" v-on:click="showCalendar('mes')" role="button">
                                    <i class="icon-filter-plant max-30"></i>
                                </a>
                                <span class="ml-1 text-muted">(@{{ dashboard.response_mes.quantity }})</span>
                            </li>
                            <li class="box-item font">
                                <a href="#" role="button">
                                    <i class="icon-star val-0"></i>
                                </a>
                                <span class="ml-1 text-muted">(@{{ dashboard.vips }})</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{ trans('board.label.loading') }}</p>
            </div>

            <div class="" v-if="!loadingModal">
                <table class="table">
                    <tbody class="p-0">
                        <tr v-for="file in filesCalendar">
                            <td class="" nowrap>
                                <i v-bind:class="['fas', 'fa-circle', (file.type != undefined && file.type != 'mes') ? ('min-' + file.type) : 'max-30']"></i>
                                @{{ (file.detail != undefined) ? file.detail[3] : '' }}
                            </td>
                            <td><i v-bind:class="[ (file.detail != undefined && file.detail[4] == 'VIP') ? 'icon-star' : '' ]"></i></td>
                            <td nowrap>{{ trans('board.th.file') }} @{{ (file.detail != undefined) ?file.detail[0] : '' }}</td>
                            <td class="text-muted"> @{{ (file.detail != undefined) ? file.detail[1] : '' }}</td>
                            <td class="">@{{ (file.detail != undefined) ? file.detail[2] : '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-modal>
    <!------- End modal Calendario ------->
    <!------- Modal NOTAS ------->
    <b-modal id="my-modal" v-model="my_modal" size="lg" hide-footer>
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2> <i class="icon-file-text"></i> {{ trans('board.th.notes') }}</h2>
            </div>
            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{ trans('board.label.loading') }}</p>
            </div>
            <div class="container box-content" v-if="!loadingModal">
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-start align-items-center">
                        <div class="ml-4"><strong>{{ trans('board.th.file') }} #@{{ nroFile }}</strong></div>
                        <div v-for="(module, m) in modules" scope="col" class="text-muted" v-if="fileModal[m] != undefined">
                            <span class="ml-4" v-bind:title="[ module.value ]" v-b-tooltip.hover>@{{ module.key }}</span>
                            <span class="ml-1">
                                <i v-bind:class="[ fileModal[m] == 1 ? 'icon-x' : 'icon-check' ]" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                            </span>
                        </div>
                        <div class="ml-4">
                            <b-button v-on:click="addNote()" :disabled="loadingModal" v-if="showTableNotes" class="btn btn-danger btn-lg ml-5">{{ trans('board.label.new_note') }}</b-button>
                        </div>
                    </div>
                </div>
                <div class="mt-5 ml-4">
                    <form v-if="!showTableNotes" class=" mt-3">
                        <div class="form-group">
                            <label><strong>{{ trans('board.label.new_note') }}</strong></label>
                            <textarea v-model="content" rows="4" class="form-control" placeholder="Escribe aquí tu mensaje ...."></textarea>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-end">
                            <!-- p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p -->
                            <b-button class="btn btn-danger btn-lg m-2" :disabled="loadingModal" v-on:click="saveNote('')">{{ strtoupper(trans('board.btn.save_note')) }}</b-button>
                            <b-button v-on:click="closeForm()" :disabled="loadingModal" v-if="!showTableNotes" class="btn btn-inverse btn-lg m-2">{{ strtoupper(trans('board.btn.cancel')) }}</b-button>
                        </div>
                    </form>
                </div>
            </div>
            <div v-if="showTableNotes && !loadingModal">
                <div class="mb-5">
                    {{ trans('board.label.file_notes') }} (@{{ notesQuantity }})
                </div>
                <div class="content-comments">
                    <div v-bind:class="['comment-box', 'p-4', 'mb-4', (note.status != 1) ? 'deleted' : '']" v-for="note in notes">
                        <!-- Avatar -->
                        <div class="commet-avatar" v-if="note.user.photo != '' && note.user.photo != null">
                            <img v-bind:src="'/images/users/' + note.user.photo" alt="" />
                        </div>
                        <!-- Contenedor del Comentario -->
                        <div class="box">
                            <div class="commet-head mb-1">
                                <h4>@{{ note.user.name  }}</h4>
                                <div class="d-flex justify-content-start">
                                    <span class="text-muted mr-4 p-1">@{{ note.created_at }}</span>
                                    <span v-if="note.status == 1 && note.user.code == user.code">
                                        <a class="text-muted" v-on:click="editNote(note)" href="javascript:;">{{ strtoupper(trans('board.btn.edit')) }}</a>&#32;&#32;&#32; &middot; &#32;&#32;&#32;<a class="text-muted" v-on:click="deleteNote(note)" href="javascript:;">{{ strtoupper(trans('board.btn.delete')) }}</a>
                                    </span>
                                </div>
                            </div>
                            <div class="comment-content" v-if="!(idNote == note.id)">
                                @{{ note.comment }}
                            </div>
                            <div class="comment-content" v-if="idNote == note.id">
                                <textarea class="form-control" v-model="content"></textarea>
                                <div class="box">
                                    <b-button v-on:click="saveNote('')" class="btn btn-danger btn-sm">{{ strtoupper(trans('board.btn.update')) }}</b-button> <b-button class="btn btn btn-inverse btn-sm m-2 btn-secondary" v-on:click="closeForm()">{{ strtoupper(trans('board.btn.cancel')) }}</b-button>
                                </div>
                            </div>
                        </div>

                        <!-- Respuesta al Comentario -->
                        <hr class="ml-10" v-if="note.status == 1">
                        <div class="commet-response p-3" v-if="note.status == 1">
                            <div class="response ml-10">
                                <i class="icon-corner-down-right"></i>
                                <a href="javascript:;" v-on:click="viewFormResponse(note.id)" :disabled="loadingModal" id="res"> {{ strtoupper(trans('board.btn.reply')) }}</a>
                            </div>
                            <div class="ml-10" v-show="idParent == note.id">
                                <form class="mt-3">
                                    <div class="form-group">
                                        <textarea v-model="content" rows="4" class="form-control" placeholder="Escribe aquí tu mensaje ...."></textarea>
                                    </div>
                                    <div class="form-group d-flex align-items-center justify-content-end">
                                        <!-- p class="text-muted m-2">Julio 10,2019 - 16:22hrs</p -->
                                        <b-button class="btn btn-danger btn-lg m-2" :disabled="loadingModal" v-on:click="saveNote(note.id)">{{ strtoupper(trans('board.btn.save_note')) }}</b-button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Respuesta al Comentario -->
                        <div v-for="n in note.notes">
                            <hr class="ml-10">
                            <div v-bind:class="['commet-response', 'p-3', (n.status != 1) ? 'deleted' : '']">
                                <div class="commet-icon">
                                    <i class="icon-corner-down-right"></i>
                                </div>
                                <!-- Avatar -->
                                <div class="commet-avatar" v-if="n.user.photo != '' && n.user.photo != null">
                                    <img v-bind:src="'/images/users/' + n.user.photo" alt="" />
                                </div>
                                <!-- Contenedor del Comentario -->
                                <div class="box">
                                    <div class="commet-head mb-1">
                                        <h4>@{{ n.user.name }}</h4>
                                        <div class="d-flex justify-content-start">
                                            <span class="text-muted mr-4 p-1">@{{ n.created_at }}</span>
                                            <span v-if="n.status == 1 && n.user.code == user.code">
                                                <a class="text-muted" v-on:click="editNote(n)" href="javascript:;">{{ strtoupper(trans('board.btn.edit')) }}</a>&#32;&#32;&#32; &middot; &#32;&#32;&#32;<a class="text-muted" v-on:click="deleteNote(n)" href="javascript:;">{{ strtoupper(trans('board.btn.delete')) }}</a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="comment-content" v-if="!(idNote == n.id)">
                                        @{{ n.comment }}
                                    </div>
                                    <div class="comment-content" v-if="idNote == n.id">
                                        <textarea class="form-control" v-model="content"></textarea>
                                        <div class="box">
                                            <b-button v-on:click="saveNote('')" class="btn btn-danger btn-sm">{{ strtoupper(trans('board.btn.update')) }}</b-button> <b-button class="btn btn btn-inverse btn-sm m-2 btn-secondary" v-on:click="closeForm()">{{ strtoupper(trans('board.btn.cancel')) }}</b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-modal>
    <!------- Modal Datos pasajeros ------->
    <modal-passengers ref="modal_passengers"></modal-passengers>
    <!------- End Modal PAX PASAJEROS ------->
</div>
@endsection
@section('css')
<style>
    .calender-modal {
        padding-top: 100px;
    }

    label {
        color: #4F4B4B;
    }

    a {
        text-decoration-line: none;
        font-size: 15px;
        font-weight: bolder;
    }

    .list-filter {
        display: flex;
        display: -ms-flexbox;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        list-style: none;
        margin: 1rem;
        padding-left: 0px !important;
    }

    .list-filter>li {
        margin-left: 8px;
    }

    li>a:hover {
        text-decoration: none;
    }

    .val-0 {
        color: #BBBDBF;
        cursor: pointer;
    }

    .min-7 {
        color: #CE3B4D;
        font-weight: bold;
        cursor: pointer;
    }

    .min-15 {
        color: #EA932D;
        font-weight: bold;
        cursor: pointer;
    }

    .min-30 {
        color: #04B5AA;
        font-weight: bold;
        cursor: pointer;
    }

    .max-30 {
        color: #4BC910;
        font-weight: bold;
        cursor: pointer;
    }


    .red {
        color: red;
    }
</style>
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js"
    integrity="sha512-wT7uPE7tOP6w4o28u1DN775jYjHQApdBnib5Pho4RB0Pgd9y7eSkAV1BTqQydupYDB9GBhTcQQzyNMPMV3cAew=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            _module: 'board',
            update_menu: 1,
            quote_date: '',
            icon: 'icon-filter-fire',
            _class: '',
            title: '',
            loading: true,
            loadingDetail: false,
            quantity: 0,
            user: [],
            modules: [],
            timePicker24Hour: false,
            showWeekNumbers: false,
            singleDatePicker: true,
            startDate: moment().add('days', 2).format('Y-MM-DD'),
            minDate: moment().add('days', 2).format('Y-MM-DD'),
            locale_data: {
                direction: 'ltr',
                format: moment.localeData().postformat('ddd D MMM'),
                separator: ' - ',
                applyLabel: 'Guardar',
                cancelLabel: 'Cancelar',
                weekLabel: 'W',
                customRangeLabel: 'Rango de Fechas',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            },
            options: {
                format: 'DD/MM/YYYY',
                useCurrent: true,
            },
            dashboard: {
                response_7: {
                    tareas: 0,
                    items: {},
                    quantity: 0
                },
                response_15: {
                    tareas: 0,
                    items: {},
                    quantity: 0
                },
                response_30: {
                    tareas: 0,
                    items: {},
                    quantity: 0
                },
                response_mes: {
                    tareas: 0,
                    items: {},
                    quantity: 0
                }
            },
            dashboardTemp: [],
            files: [],
            porcentaje_7: 0,
            porcentaje_15: 0,
            porcentaje_30: 0,
            porcentaje_mes: 0,
            executive: '',
            executives: [],
            _executives: [],
            bossFlag: '{{ $bossFlag }}',
            bossFlagSearch: 0,
            /* Detalle de Notas */
            notes: [],
            notesQuantity: 0,
            showTableNotes: true,
            showFormResponse: false,
            content: '',
            loadingModal: false,
            loading: false,
            nroFile: 0,
            total_paxs: 0,
            idNote: 0,
            idParent: 0,
            fileModal: [],
            /* -- Detalle de Notas */
            my_modal: false,
            pending_passengers: false,
            calendar: false,
            filesCalendar: [],
            notifications: [],
            backdoor: '{{ $return }}',
            modePassenger: 1,
            repeatPassenger: 0,
            totalPassengers: [],
            show_passenger_save: true,
            passengers: [],
            modulesFilter: [],
            moduleFilter: '',
            lang: 'en',
            quantityOrders: 0,
            all_stats: [],
            sections: [],
            graph_section: [],
            dataQuotes: [],
            dataURL: '{!! $dataURL !!}',
            tab: 'pendings',
            dateRangeResume: {
                'startDate': "{!! $date['startDate'] !!}",
                'endDate': "{!! $date['endDate'] !!}"
            },
            dateRangeOrders: '',
            dateRangeQuotes: '',
            orders: {
                'all_quotes': 0
            },
            regions: '{{ $regions }}',
            mount: 0,
            formPay: false,
            billingSelected: 0,
            // Productividad..
            executiveP: '',
            executivesP: [],
            quantityExecutivesP: 0,
            excel: '',
            translations: {
                label: {},
                btn: {}
            },
        },
        created: function() {
            localStorage.setItem('boss', false)
            this.setTranslations()
            Cookies.set(window.parametersPackagesDetails, "", window.domain)
        },
        mounted: async function() {
            this.loading = true;
            this.lang = localStorage.getItem('lang');
            localStorage.setItem('boss', this.bossFlag);
            this.bossFlagSearch = this.bossFlag;

            await this.getDashboard();
            this.dataURL = JSON.parse(this.dataURL);

            if (this.dataURL.action == 'modalPassengers') {
                eval("this.$refs.modal_passengers.modalPassengers('file', '" + this.dataURL.file + "', '" + this.dataURL.paxs + "')");
            }

            await this.searchExecutivesP();
            this.loading = false;
        },
        computed: {},
        methods: {
            setTranslations() {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/board').then((data) => {
                    this.translations = data.data
                })
            },
            toggleView: function(t) {
                this.tab = t

                if (t == 'order_report') {
                    this.showDetailOrders()
                }
            },
            showModal: function(element) {
                if (eval('this.' + element + ' == false')) {
                    eval('this.' + element + ' = true')
                }
            },
            closeModal: function(element) {
                if (eval('this.' + element + ' == true')) {
                    eval('this.' + element + ' = false')
                }
            },
            resetDashboard: function() {
                this.quantity = 0
                this.user = []
                this.modules = []
                this.dashboard = {
                    response_7: {
                        tareas: 0,
                        items: {},
                        quantity: 0
                    },
                    response_15: {
                        tareas: 0,
                        items: {},
                        quantity: 0
                    },
                    response_30: {
                        tareas: 0,
                        items: {},
                        quantity: 0
                    },
                    response_mes: {
                        tareas: 0,
                        items: {},
                        quantity: 0
                    }
                }
                this.files = []
                this.porcentaje_7 = 0
                this.porcentaje_15 = 0
                this.porcentaje_30 = 0
                this.porcentaje_mes = 0
            },
            getDashboard: async function() {
                if (this.bossFlag == 1 && this.bossFlagSearch != this.bossFlag) {
                    this.dashboardTemp = this.dashboard
                }

                if (!this.loading) {
                    this.showLoader("{{ trans('board.label.loading') }}")
                }

                this.resetDashboard()

                await axios.post(
                        baseURL + 'board/dashboard', {
                            lang: this.lang,
                            executive: this.executive,
                            bossFlag: this.bossFlagSearch
                        }
                    )
                    .then(async (result) => {

                        this.user = result.data.user

                        if (this.bossFlagSearch == this.bossFlag) {
                            this.executives = result.data.executives
                            this.executive = result.data.executive
                            this.modulesFilter = result.data.modulesFilter
                        }

                        if (this.bossFlagSearch == 0) {
                            this.modules = result.data.modules
                            this.dashboard = result.data.dashboard
                            this.porcentaje_7 = result.data.dashboard.porcentaje_7
                            this.porcentaje_15 = result.data.dashboard.porcentaje_15
                            this.porcentaje_30 = result.data.dashboard.porcentaje_30
                            this.porcentaje_mes = result.data.dashboard.porcentaje_mes

                            // this.hideLoader()

                            // Volviendo a mostrar la info..
                            let prevSearch = localStorage.getItem('prevSearch')
                            if (this.backdoor == 1 && prevSearch != '') {
                                eval('this.' + prevSearch)
                            }
                        } else {
                            await this.showExecutives()
                        }
                    })
                    .catch((e) => {
                        console.log(e);
                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })

                if (!this.loading) {
                    this.hideLoader()
                }
            },
            showExecutive: async function(_index) {
                let vm = this,
                    _total = Object.keys(this.executives).length

                if (_total == 0) {
                    return false
                }

                let key = this._executives[_index];

                await axios.post(
                        baseURL + 'board/executives', {
                            executive: key,
                            module: vm.moduleFilter
                        }
                    )
                    .then((result) => {

                        console.log(key)
                        console.log(result.data.dashboard)

                        vm.$set(vm.dashboard.response_7, key, result.data.dashboard.response_7[key])
                        vm.$set(vm.dashboard.response_15, key, result.data.dashboard.response_15[key])
                        vm.$set(vm.dashboard.response_30, key, result.data.dashboard.response_30[key])
                        vm.$set(vm.dashboard.response_mes, key, result.data.dashboard.response_mes[key])

                        // contar tareas..
                        vm.dashboard.response_7.tareas += result.data.dashboard.response_7[key].tareas
                        vm.dashboard.response_15.tareas += result.data.dashboard.response_15[key].tareas
                        vm.dashboard.response_30.tareas += result.data.dashboard.response_30[key].tareas
                        vm.dashboard.response_mes.tareas += result.data.dashboard.response_mes[key].tareas

                        if (_index + 1 == _total) {
                            vm.hideLoader()
                        }

                        if (_index + 1 < _total) {
                            vm.showExecutive(_index + 1);
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                        vm.hideLoader()

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            showExecutives: async function() {
                let vm = this
                vm.resetDashboard()

                await axios.post(
                        baseURL + 'board/executives', {
                            executives: vm.executives,
                            module: vm.moduleFilter
                        }
                    )
                    .then((result) => {
                        let executives = result.data

                        Object.entries(executives).forEach(([key, value]) => {
                            vm.$set(vm.dashboard.response_7, key, value.dashboard.response_7[key])
                            vm.$set(vm.dashboard.response_15, key, value.dashboard.response_15[key])
                            vm.$set(vm.dashboard.response_30, key, value.dashboard.response_30[key])
                            vm.$set(vm.dashboard.response_mes, key, value.dashboard.response_mes[key])

                            vm.dashboard.response_7.tareas += value.dashboard.response_7[key].tareas
                            vm.dashboard.response_15.tareas += value.dashboard.response_15[key].tareas
                            vm.dashboard.response_30.tareas += value.dashboard.response_30[key].tareas
                            vm.dashboard.response_mes.tareas += value.dashboard.response_mes[key].tareas
                        })
                    })
                    .catch((e) => {
                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            showFiles: async function(type, category) {
                this.files = []
                this.showLoader("{{ trans('board.label.loading') }}")

                await axios.post(
                        baseURL + 'board/files', {
                            type: type,
                            category: category
                        }
                    )
                    .then((result) => {
                        localStorage.setItem('prevSearch', 'showFiles("' + type + '", "' + category + '")') // Función para actualizar al regresar

                        this.files = result.data.files
                        this.icon = result.data.icon
                        this.title = result.data.title
                        this._class = result.data.class
                        this.quantity = result.data.quantity

                        this.hideLoader()

                        setTimeout(function() {
                            //Funcionalidad de scroll lento para el enlace ancla en 3 segundos
                            $("#app").animate({
                                scrollTop: $('#results').offset().top
                            }, 1000);
                        }, 100)
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            getUrl: function(nrofile) {
                return 'http://genero.limatours.com.pe:8200/wa/r/litt0160?Arg=' + this.executive.toLowerCase() + '&Arg=5&Arg=aurora&Arg=seleccion&Arg=' + nrofile
            },
            getUrlDetail: function(value, url, nrofile) {
                if (url != 'getURL') {
                    if (url.indexOf('genero.') < 0) {
                        if (url.indexOf('//') < 0) {
                            if (url.indexOf('modal') < 0) {
                                return this.getURLExterno(url, nrofile)
                            } else {
                                return url + "(" + nrofile + ")"
                            }

                        } else {
                            return url
                        }
                    } else {
                        return (url != '') ? (url + 'Arg=' + this.executive.toLowerCase() + '&Arg=5&Arg=' + nrofile) : '#'
                    }
                } else {
                    return this.getUrl(nrofile)
                }
            },
            getURLExterno: function(url, nrofile) {
                return url + ((url.indexOf('?') < 0) ? '?' : '&') + 'nrofile=' + nrofile
            },
            getTarget: function(url) {
                return (url != '') ? '_blank' : '_self'
            },
            getUrlItinerary: function(nrofile) {
                return '/board/details/' + nrofile
            },
            showLoader: function(texto) {
                let $backdrop = $(".backdrop-banners"),
                    timeLoading = 250

                $backdrop.css({
                    'display': 'block',
                }).animate({
                    opacity: 1
                }, timeLoading, function() {
                    $backdrop.prepend('<div id="spinner-loader"><div class="spinner"><span class="logo"></span></div>' +
                        '<div class="spinner-text">' + texto + '<small>Por favor espere.</small></div></div>')
                })
            },
            hideLoader: function() {
                let $backdrop = $(".backdrop-banners"),
                    timeLoading = 250

                $backdrop.css({
                    display: 'none'
                }).animate({
                    opacity: 0
                }, timeLoading, function() {
                    $backdrop.html('');
                });
            },
            filterExecutive: function(event) {
                if (event != '' && event != undefined) {
                    this.executive = event
                }
                this.bossFlagSearch = 0
                // this.showLoader("{{ trans('board.label.loading') }}")
                this.getDashboard()
            },
            regresar: async function() {

                if (this.bossFlagSearch != this.bossFlag) {
                    this.bossFlagSearch = this.bossFlag
                    this.dashboard = this.dashboardTemp
                    this.dashboardTemp = {}
                }
            },
            resetForm: function() {
                this.content = ''
            },
            closeForm: function() {
                this.showTableNotes = true
                this.idNote = 0
                this.resetForm()
            },
            loadNotes: function(file) {
                this.showModal('my_modal')
                // this.$refs.searchNotifications() // Cargando notifications..

                this.fileModal = (this.files[file] != undefined) ? this.files[file] : []
                this.loadingModal = true
                this.showTableNotes = true
                this.nroFile = file
                this.idParent = 0
                this.idNote = 0
                // Reseteando las notas..
                this.notes = []
                this.notesQuantity = 0

                axios.post(
                        baseURL + 'board/notes', {
                            file: this.nroFile
                        }
                    )
                    .then((result) => {
                        this.notes = result.data.notes
                        this.notesQuantity = Object.keys(result.data.notes).length
                        this.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loadingModal = false

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            addNote: function() {
                this.showTableNotes = false
                this.idNote = 0
                this.content = ''
            },
            editNote: function(note) {
                // this.showTableNotes = false
                this.idNote = note.id
                this.content = note.comment
            },
            saveNote: function(parent) {
                this.loadingModal = true

                axios.post(
                        baseURL + 'board/save_note', {
                            id: this.idNote,
                            parent: parent,
                            file: this.nroFile,
                            content: this.content,
                            user: this.executive
                        }
                    )
                    .then((result) => {
                        this.loadNotes(this.nroFile)
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loadingModal = false

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            deleteNote: function(note) {
                axios.post(
                        baseURL + 'board/delete_note', {
                            id: note.id
                        }
                    )
                    .then((result) => {
                        this.loadNotes(this.nroFile)
                    })
                    .catch((e) => {
                        console.log(e)
                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },
            viewFormResponse: function(parent) {
                if (this.idParent == 0) {
                    this.idParent = parent
                } else {
                    this.idParent = 0
                }
            },
            sortedArray: function(element, field) {
                this.loadingModal = true
                eval("this." + element + " = Object.values(this." + element + ")")

                function compare(a, b) {
                    if (eval("a." + field + " < b." + field))
                        return -1
                    if (eval("a." + field + " > b." + field))
                        return 1
                    return 0;
                }

                this.loadingModal = false
                return eval("this." + element + ".sort(compare)")
            },
            showCalendar: function(type) {
                eval("this.filesCalendar = this.dashboard.response_" + type + ".items")
                let vm = this,
                    _total = Object.keys(this.filesCalendar).length,
                    _cantidad = 0

                setTimeout(function() {

                    if (_total > 0) {
                        vm.showModal('calendar')
                        vm.loadingModal = true
                    } else {
                        vm.closeModal('calendar')
                        return false
                    }

                    $.each(vm.filesCalendar, function(i, item) {

                        if (!(Array.isArray(item) || typeof item == 'object')) {
                            axios.post(
                                    baseURL + 'board/file', {
                                        file: item
                                    }
                                )
                                .then((result) => {
                                    // console.log(result)
                                    vm.filesCalendar[i] = {
                                        file: item,
                                        type: type,
                                        detail: result.data.nroref,
                                        date: result.data.nroref.date
                                    }

                                    if (_cantidad == (_total - 1)) {
                                        this.filesCalendar = vm.sortedArray('filesCalendar', 'date', 'showCalendar("' + type + '")')
                                    }

                                    _cantidad++
                                })
                                .catch((e) => {
                                    console.log(e)
                                    vm.loadingModal = false

                                    if (e.message == 'Unauthenticated.') {
                                        window.location.reload()
                                    }
                                })
                        } else {
                            this.filesCalendar = vm.sortedArray('filesCalendar', 'date', 'showCalendar("' + type + '")')
                            vm.loadingModal = false
                        }
                    })

                }, 100)
            },
            showNotification: function(id, data) {
                data = JSON.parse(data)
                this.loadNotes(data.file)
            },
            showPassengers: function(id, data) {
                data = JSON.parse(data)
                this.modalPassengers('file', data.file, data.paxs)
            },
            modalPassengers: function(file, paxs) {
                this.$refs.modal_passengers.modalPassengers('file', file, paxs)
            },
            filterModule: async function() {
                this.showLoader("{{ trans('board.label.loading') }}")
                await this.showExecutives()
            },
            showDetailOrders: function(page) {

                if (this.dateRangeResume == '') {
                    this.$toast.error('Seleccione un rango de fechas para poder filtrar los reportes', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if (page == undefined) {
                    this.page = 1
                }

                this.loadingDetail = true
                this.quantityOrders = 0
                this.all_stats = []
                this.sections = []

                axios.post(
                        baseURL + 'search_orders', {
                            lang: this.lang,
                            type: 'E',
                            user: '',
                            status: 'ALL',
                            dateRange: this.dateRangeResume,
                            team: '',
                            product: '',
                            limit: this.page
                        }
                    )
                    .then((result) => {
                        let _quantity = result.data.quantity

                        if (this.page == 'show') {
                            this.loadingDetail = false
                            this.graph_section = []
                            this.sections = []

                            axios.post(
                                    baseURL + 'board/orders', {
                                        lang: this.lang
                                    }
                                )
                                .then((result) => {
                                    let vm = this
                                    this.loadingDetail = false
                                    this.quantityOrders = result.data.quantity
                                    this.all_stats = result.data.all_stats
                                    this.sections = result.data.sections

                                    Object.entries(vm.sections).forEach(([key, value]) => {

                                        let porcentaje_meta = (value.limit - value.stats.percent_placed > 0) ? value.limit - value.stats.percent_placed : 0
                                        let cantidad_pendiente = (value.__orders > value.stats.orders_placed) ? value.__orders - value.stats.orders_placed : 0

                                        Vue.set(vm.graph_section, key, eval(
                                            "[" +
                                            "{ label: '" + value.stats.percent_placed + "% META REALIZADA (" + value.stats.orders_placed + ")', value: " + value.stats.percent_placed + ", color: '#36A2EB' }," +
                                            "{ label: '" + (porcentaje_meta) + "% META PENDIENTE (" + (cantidad_pendiente) + ")', value: " + (porcentaje_meta) + ", color: '#FF6384' }" +
                                            "]"
                                        ));
                                        // eval("vm.graph_sections.push({'" + key + "':  });");
                                        // eval("this.dataQuotes = [['Porcentaje en STELA', " + this.orders.percent_stela_quotes + "], ['Porcentaje de AURORA', " + this.orders.percent_aurora_quotes + "]]")
                                    });
                                })
                                .catch((e) => {
                                    console.log(e)
                                    this.loadingDetail = false

                                    if (e.message == 'Unauthenticated.') {
                                        window.location.reload()
                                    }
                                })
                        } else {
                            if (_quantity == 0) {
                                this.page = 'show'
                            } else {
                                this.page = this.page + 1
                            }

                            this.showDetailOrders(this.page)
                        }
                    })
                    .catch((e) => {
                        this.loadingDetail = false
                        console.log(e)
                    })
            },
            searchExecutivesP: async function(search, loading) {
                await axios.post(
                        baseURL + 'board/executives_user', {
                            lang: this.lang
                        }
                    )
                    .then((result) => {
                        this.executivesP = result.data.executives
                        this.quantityExecutivesP = result.data.quantity
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    });
</script>
@endsection