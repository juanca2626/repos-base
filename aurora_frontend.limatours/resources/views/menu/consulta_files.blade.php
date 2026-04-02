@extends('layouts.app')
@section('content')
<section class="page-consulting__files">
    <div class="container">
        <div class="motor-busqueda">
            <h2>{{trans('files.title.file_query')}}</h2>
            <div class="form justify-content-around">
                <div class="form-row d-flex align-items-end">
                    <div class="form-group mx-5 busqueda">
                        <label class="pr-5"><strong>{{trans('files.label.type_of_search')}}:</strong></label>
                        <select class="form-control" v-model="typeSearch">
                            <option value="dates">{{trans('files.label.date')}}</option>
                            <option value="file">{{trans('files.label.file')}}</option>
                            <option value="description">{{trans('files.label.file_description')}}</option>
                            <option value="order">{{trans('files.label.order')}}</option>
                            <option value="locator">{{trans('files.label.locator')}}</option>
                        </select>
                    </div>
                    <!-- DATES -->
                    <div class="form-group fecha mx-4" v-if="typeSearch=='dates'">
                        <label class="pr-5"><strong>{{trans('files.label.date_range')}}:</strong></label>
                        <date-range-picker
                            :locale-data="locale_data"
                            :time-picker24-hour="timePicker24Hour"
                            :show-week-numbers="showWeekNumbers"
                            :ranges="false"
                            :auto-apply="true"
                            v-model="dateRange">
                        </date-range-picker>
                    </div>
                    <!-- FILE -->
                    <div class="form-group file mx-4" v-if="typeSearch=='file'">
                        <label class="pr-5"><strong>{{trans('files.label.num_file')}}:</strong></label>
                        <input type="text" class="form-control" v-model="inputSearchFile" required>
                    </div>
                    <!-- ORDER -->
                    <div class="form-group orden mx-4" v-if="typeSearch=='order'">
                        <label class="pr-5"><strong>{{trans('files.label.num_order')}}:</strong></label>
                        <input type="text" class="form-control" v-model="inputSearchOrder" required>
                    </div>
                    <!-- DESCRIPTION -->
                    <div class="form-group file mx-4" v-if="typeSearch=='description'">
                        <label class="pr-5"><strong>{{trans('files.label.file_description')}}:</strong></label>
                        <input type="text" class="form-control" v-model="inputSearchDescription" required>
                    </div>
                    <!-- LOCALIZADOR -->
                    <div class="form-group localizador mx-4" v-if="typeSearch=='locator'">
                        <label class="pr-5"><strong>{{trans('files.label.num_locator')}}:</strong></label>
                        <input type="text" class="form-control" v-model="inputSearchLocator" required>
                    </div>
                    <!-- BUSCAR -->
                    <div class="form-group btn-buscar mx-5">
                        <button class="btn btn-primary" @click="search()"><i class="icon-search"></i> {{trans('files.label.search')}}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="table-responsive-sm">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th scope="col" class="text-muted">{{trans('files.label.file')}}</th>
                                <th scope="col" class="text-muted">EC</th>
                                <th scope="col" class="text-muted">{{trans('files.label.order')}}</th>
                                <th scope="col" class="text-muted">{{trans('files.label.file_description')}}</th>
                                <th scope="col" class="text-muted">{{trans('files.label.arrival_date')}}</th>
                                <th scope="col" class="text-muted">{{trans('files.label.departure_date')}}</th>
                                <th scope="col" class="text-muted">{{trans('files.label.quantity_pax')}}</th>
                                <th scope="col" class="text-muted">{{trans('files.label.options')}}</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <tr v-if="loadingSearch && files.length==0">
                                <td colspan="7" class="text-center">{{trans('files.label.loading')}}....</td>
                            </tr>
                            <tr v-if="!loadingSearch && files.length==0">
                                <td colspan="7" class="text-center">{{trans('files.label.no_files_found')}}.</td>
                            </tr>
                            <tr v-if="!loadingSearch && files.length>0" v-for="(file, f) in files">
                                <td>@{{ file.file_number }}</td>
                                <td>
                                    <span data-toggle="tooltip" :title="file.EC">@{{ file.executive_code_process }}</span>
                                </td>
                                <td>@{{ file.order_related }}</td>
                                <td>@{{ file.description }}</td>
                                <td>@{{ file.date_in }}</td>
                                <td>@{{ file.date_out }}</td>
                                <td>@{{ file.paxs }}</td>
                                <td style="padding-top: 2px;">
                                    <div>
                                        <b-dropdown size="sm" text="" class="m-md-2">
                                            <b-dropdown-item v-on:click="showServices(file)"><i class="mx-1 icon-link"></i> {{trans('files.label.programming_tracking')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <b-dropdown-item v-on:click="showSkeleton(file)"><i class="mx-1 icon-link"></i> {{trans('files.label.service_detail')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <b-dropdown-item v-on:click="showScheduled(file)"><i class="mx-1 icon-link"></i>{{trans('files.label.scheduled_services')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <!-- b-dropdown-item v-on:click="modalPassengers(file.file_number, file.CNTMAXPAXS)"><i class="mx-1 icon-link"></i>{{trans('files.label.passenger_information')}} - N°@{{ file.file_number }}</b-dropdown-item -->
                                            <b-dropdown-item v-on:click="showHotels(file)"><i class="mx-1 icon-link"></i>{{trans('files.label.list_of_hotels')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <b-dropdown-item v-on:click="showInvoice(file)"><i class="mx-1 icon-link"></i>{{trans('files.label.see_invoice')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <b-dropdown-item v-on:click="showItinerary(file)"><i class="mx-1 icon-link"></i>{{trans('files.label.see_itinerary')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            <b-dropdown-item v-on:click="showGuides(file)"><i class="mx-1 icon-link"></i>{{trans('files.label.list_of_guides')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                            {{-- <b-dropdown-item v-on:click="showDetailFile(file.file_number)"><i class="mx-1 icon-link"></i>{{trans('files.label.file_detail_status')}} - N°@{{ file.file_number }}</b-dropdown-item>--}}
                                            <!-- b-dropdown-item v-on:click="modalFlights(file.file_number)"><i class="mx-1 icon-link"></i>{{trans('files.label.add_flights')}} - N°@{{ file.file_number }}</b-dropdown-item -->
                                            <b-dropdown-item v-on:click="completeFile(file.file_number, file.CNTMAXPAXS)"><i class="mx-1 icon-link"></i>{{trans('files.label.passenger_information')}} / {{trans('files.label.add_flights')}} - N°@{{ file.file_number }}</b-dropdown-item>
                                        </b-dropdown>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div>
    <b-modal id="services" size="lg" hide-footer v-model="services" class="modal-servicios">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><strong>{{trans('files.label.programming_tracking')}} #@{{ fileSelected.file_number }}</strong></h2>
            </div>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <template v-if="!loadingModal && !fileSelected.services">
                {{trans('files.label.no_information_exists')}}.
            </template>
            <template v-if="!loadingModal && fileSelected.services" v-for="(service, city) in fileSelected.services">
                <table class="table-results">
                    <thead>
                        <tr>
                            <th colspan="9" class="text-center">@{{ city }}</th>
                        </tr>
                        <tr>
                            <th>fecSVS</th>
                            <th>horIN</th>
                            <th>codSVS</th>
                            <th>{{trans('files.label.service_description')}}</th>
                            <th>TRP</th>
                            <th>BTT</th>
                            <th>{{trans('files.label.guide')}}</th>
                            <th>{{trans('files.label.name')}}</th>
                            <th>{{trans('files.label.phone')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in service" v-if="city">
                            <td>@{{ v.FECIN | moment("D/M/YYYY") }}</td>
                            <td>@{{ v.HORIN }}</td>
                            <td>@{{ v.CODSVS }}</td>
                            <td>@{{ v.DESSVS }}</td>
                            <td>@{{ v.NOMTRP }}</td>
                            <td>@{{ v.TIPVEH }}</td>
                            <td>@{{ v.CODGUI }}</td>
                            <td>@{{ v.NOMGUI }}</td>
                            <td>
                                <span v-if="v.CELGUI">+51</span>@{{ v.CELGUI }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </div>
    </b-modal>

    <b-modal id="itinerary" size="sm" hide-footer v-model="itinerary" class="modal-itinerary">
        <div class="d-block" style="margin:-20px;">
            <div class="mb-4">
                <h2 class="d-inline"><strong>{{trans('files.label.programming_tracking')}} #@{{ fileSelected.file_number }}</strong></h2>
            </div>

            {{-- <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">--}}
            {{-- <p class="mb-0">{{trans('files.label.loading')}}...</p>--}}
            {{-- </div>--}}

            <form v-if="!showBtnDownload">
                <h2 class="mb-2">{{ trans('quote.label.itinerary') }} <i
                        class="icon-download"></i></h2>
                <hr>
                <h5 class="mb-3">{{ trans('quote.label.do_you_want_a_cover') }}:</h5>
                <div class="d-flex align-items-center justify-content-around mb-3">
                    <label class="mx-3" @click.stop="">
                        <input :disabled="loading"
                            name="with_logo_client_radio" type="radio"
                            :value="true" v-model="fileSelected.withHeader" @change="changeWithCover(fileSelected)"> Sí
                    </label>
                    <label class=" mx-3" @click.stop="">
                        <input :disabled="loading"
                            name="with_logo_client_radio" type="radio"
                            :value="false" v-model="fileSelected.withHeader" @change="changeWithCover(fileSelected)"> No
                    </label>
                </div>
                <h5>{{ trans('quote.label.do_you_want_a_client_logo') }}:</h5>
                <div class="d-flex align-items-center justify-content-around mb-3">
                    <label class=" mx-3">
                        <input :disabled="loading" @click.stop=""
                            name="with_header_radio1" type="radio"
                            @change="setWithHeader(fileSelected)"
                            :value="1" v-model="fileSelected.withClientLogo"> {{ trans('quote.label.yes') }}
                    </label>
                    <label class="mx-3">
                        <input :disabled="loading" @click.stop=""
                            name="with_header_radio2" type="radio"
                            @change="setWithHeader(fileSelected)"
                            :value="2" v-model="fileSelected.withClientLogo">
                        {{ trans('quote.label.no') }}
                    </label>
                    <label class="mx-3">
                        <input :disabled="loading" @click.stop=""
                            name="with_header_radio2" type="radio"
                            @change="setWithHeader(fileSelected)"
                            :value="3" v-model="fileSelected.withClientLogo">
                        {{ trans('quote.label.nothing') }}
                    </label>
                </div>
                <div class="text-align-center mb-3" v-if="fileSelected.withHeader">
                    {{-- <b-form-group id="input-group-3" label="{{ trans('files.label.portada') }}:" label-for="input-3"> --}}
                    <select @click.stop=""
                        class="showWithCover" style="border-color: #e2e8f0;width: 100%;"
                        @change="setComboPortada(fileSelected)"
                        v-model="fileSelected.portada"
                        required="required">
                        <option value="amazonas">AMAZONAS</option>
                        <option value="arequipa">AREQUIPA</option>
                        <option value="arequipa-catedral">AREQUIPA CATEGRAL</option>
                        <option value="argentina">ARGENTINA</option>
                        <option value="aventura">AVENTURA</option>
                        <option value="ballestas">BALLESTAS</option>
                        <option value="bolivia">BOLIVIA</option>
                        <option value="brasil">BRASIL</option>
                        <option value="camino-inca">CAMINO INCA</option>
                        <option value="chile">CHILE</option>
                        <option value="colca">COLCA</option>
                        <option value="comunidad-local">COMUNIDAD LOCAL</option>
                        <option value="cusco">CUSCO</option>
                        <option value="cusco-iglesia">CUSCO IGLESIA</option>
                        <option value="familia1">FAMILIA1</option>
                        <option value="familia2">FAMILIA2</option>
                        <option value="familia3">FAMILIA3</option>
                        <option value="familia4">FAMILIA4</option>
                        <option value="huaraz">HUARAZ</option>
                        <option value="kuelap">KUELAP</option>
                        <option value="lima1">LIMA1</option>
                        <option value="lima2">LIMA2</option>
                        <option value="lima3">LIMA3</option>
                        <option value="lujo">LUJO</option>
                        <option value="machupicchu">MACHUPICCHU</option>
                        <option value="mapi">MAPI</option>
                        <option value="maras">MARAS</option>
                        <option value="moray">MORAY</option>
                        <option value="mpi2">MPI2</option>
                        <option value="nasca">NASCA</option>
                        <option value="playas-del-norte">PLAYAS DEL NORTE</option>
                        <option value="portada">PORTADA</option>
                        <option value="puerto-maldonado">PUERTO MALDONADO</option>
                        <option value="puno">PUNO</option>
                        <option value="trujillo">TRUJILLO</option>
                        <option value="valle">VALLE</option>
                        <option value="vinicunca">VINICUNCA</option>
                    </select>
                    <!-- v-select
                                class="form-control"
                                id="input-3"
                                v-model="fileSelected.portada"
                                label="DESCRI" :reduce="portadas => portadas.CODGRU" :options="portadas"
                                required
                            ></v-select -->
                    {{-- </b-form-group> --}}
                </div>
                {{-- <div class="d-flex align-items-center" v-if="fileSelected.portada != '-' && fileSelected.portada != ''">
                        <img class="showWithCover" v-if="fileSelected.withClientLogo == 3"
                             {{-- :src="baseExternalURL + 'images/word_with_client_logo/' + fileSelected.portada + '.jpg'"
                             :src=" 'https://res.cloudinary.com/litomarketing/image/upload/aurora/portadas/' + fileSelected.portada + '.jpg' "
                             style="margin: 9px;width: 120px;height: auto;"/>


                    </div> --}}
                <div style="height: 170px; width: 170px" v-if="caja==true && loading==true">

                </div>
                <template v-if="imagePortada && fileSelected.withHeader !=''">
                    <div class="text-center" v-if="imagePortada">

                        <img class="showWithCover"
                            style="margin: 9px;width: 170px;height: auto;"
                            :src="imagePortada"
                            style="margin: 9px;width: 120px;height: auto;">
                    </div>
                </template>

                <div class="d-flex my-3">
                    <button class="btn btn-primary mx-1" type="button"
                        :disabled="loading"
                        @click.stop="downloadItinerary(fileSelected)">
                        <i class="fa fa-spin fa-spinner"
                            v-if="loading"></i> {{ trans('quote.label.save') }}
                    </button>
                </div>
            </form>

            <div class="col-md-12 p-0" v-if="showBtnDownload">
                <a download class="btn btn-primary mx-1" :href="urlDocument">
                    <i class="fas fa-download animated faa-bounce"></i> {{trans('global.label.download_itinerary')}}
                </a>
            </div>
        </div>
    </b-modal>

    <b-modal id="skeleton" size="lg" hide-footer v-model="skeleton" class="modal-skeleton">
        <div class="d-block" style="margin:-20px;">

            <div>
                <h2><i class="far fa-calendar-check mx-2"></i>{{trans('files.label.service_detail')}} - N° @{{ fileSelected.file_number }}</h2>
            </div>

            <hr>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <div class="row" v-if="!loadingModal">
                <div class="col-6">
                    <p><strong>FILE:</strong> N°@{{ fileSelected.file_number }}</p>
                    <p><strong>REF:</strong> @{{ fileSelected.description }}</p>
                </div>
                <div class="col-6 text-right">
                    <b-button size="lg" variant="danger" v-on:click="downloadPDF('skeleton')">
                        <i class="far fa-file-pdf"></i> PDF
                    </b-button>
                    <b-button size="lg" variant="danger" v-on:click="downloadDOCX(fileSelected)">
                        <i class="far fa-file-word"></i> WORD
                    </b-button>
                </div>
            </div>

            <div class="mx-6" v-if="!loadingModal && fileSelected?.services?.length > 0">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.services')}}</h3>
                <table ref="table" class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-fecha text-muted">{{trans('files.label.date')}}</th>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-descripcion text-muted">{{trans('files.label.description')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.services">
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.CIUDDESCRI2 }}</td>
                            <td class="text-left p-3">
                                <div v-html="v.TEXTO_HTML"></div>
                                <small class="d-block" v-if="v.DESCRI_ORIGINAL">
                                    <b>@{{ v.DESCRI_ORIGINAL }}</b>
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-6" v-if="!loadingModal && fileSelected?.hotels?.length > 0">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.hotels')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-hotel text-muted">{{trans('files.label.hotel')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-habitacion text-muted">{{trans('files.label.type_of_room')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.start')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.ends')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.hotels">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">@{{ v.DESCRI }}</td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.DESBAS }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.ESTADO }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-6" v-if="!loadingModal && fileSelected?.package?.length > 0">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.package')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-descripcion text-muted">{{trans('files.label.description')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.arrival')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.package">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">@{{ v.SERVICE }}</td>
                            <td>@{{ v.CODCFM }}</td>
                            <td class="text-left">@{{ v.DESCRI }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.ESTADO }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-6" v-if="!loadingModal && fileSelected?.trains?.length > 0">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.trains')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-pax text-muted">{{trans('files.label.quantity_pax')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.trains">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">
                                @{{ ((!v.PREPED)?'':v.PREPED) + ' ' + ((!v.NROVUE)?'':v.NROVUE) + v.DESCRI }}
                            </td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.CANPAX }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.HORIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.HOROUT }}</td>
                            <td>@{{ v.ESTADO }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-6" v-if="!loadingModal && fileSelected?.flights?.length > 0">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.flights')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-pax text-muted">{{trans('files.label.quantity_pax')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.arrival')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.flights">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">
                                @{{ ((!v.PREPED)?'':v.PREPED) + ' ' + ((!v.NROVUE)?'':v.NROVUE) + v.DESCRI }}
                            </td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.CANPAX }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.HORIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.HOROUT }}</td>
                            <td>@{{ v.ESTADO }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-modal>

    <b-modal id="scheduled" size="lg" hide-footer v-model="scheduled">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><i class="far fa-calendar-check mx-2"></i>{{trans('files.label.scheduled_services')}} - N°@{{ fileSelected.file_number }}</h2>
            </div>

            <hr>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <div class="row" v-if="!loadingModal">
                <div class="col-6">
                    <p><strong>FILE:</strong> N°@{{ fileSelected.file_number }}</p>
                    <p><strong>REF:</strong> @{{ fileSelected.description }}</p>
                </div>
                <div class="col-6 text-right">
                    <b-button size="lg" variant="danger" v-on:click="downloadPDF('scheduled')">
                        <i class="far fa-file-pdf"></i> PDF
                    </b-button>
                </div>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.services">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.services')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-num text-muted">N°</th>
                            <th class="w-fecha text-muted">{{trans('files.label.date')}}</th>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-torre text-muted">{{trans('files.label.day_hour')}}<br>{{trans('files.label.control_tower')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="(v,k) in fileSelected.services">
                            <td>@{{ k+1 }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">
                                <span class="d-block" v-html="v.SERVICE_HTML"></span>
                                <small class="d-block" v-if="v.DESCRI">
                                    <b v-html="v.DESCRI"></b>
                                </small>
                            </td>
                            <td>@{{ v.TORRE }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.hotels">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.hotels')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-num text-muted">N°</th>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-hotel text-muted">{{trans('files.label.hotel')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-habitacion text-muted">{{trans('files.label.type_of_room')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.start')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.ends')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="(v,k) in fileSelected.hotels">
                            <td>@{{ k+1 }}</td>
                            <td>@{{ v.CIU_IN }}</td>
                            <td>@{{ v.DESCRI }}</td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.DESBAS }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>
                                <template v-if="v.CODCFM">
                                    <div v-html="v.ESTADO"></div>
                                </template>
                                <template v-else>
                                    <div>RQ</div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.package">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.package')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-descripcion text-muted">{{trans('files.label.description')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.arrival')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.package">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">@{{ v.SERVICE }}</td>
                            <td>@{{ v.CODCFM }}</td>
                            <td class="text-left">@{{ v.DESCRI }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>
                                <div v-html="v.ESTADO"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.trains">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.trains')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-pax text-muted">{{trans('files.label.quantity_pax')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.arrival')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.trains">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">
                                @{{ ((!v.PREPED)?'':v.PREPED) + ' ' + ((!v.NROVUE)?'':v.NROVUE) + v.DESCRI }}
                            </td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.CANPAX }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.HORIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.HOROUT }}</td>
                            <td>
                                <template v-if="v.CODCFM">
                                    <div v-html="v.ESTADO"></div>
                                </template>
                                <template v-else>
                                    <div>RQ</div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.flights">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.flights')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th class="w-ciudad text-muted">{{trans('files.label.city')}}</th>
                            <th class="w-servicio text-muted">{{trans('files.label.services')}}</th>
                            <th class="w-confirmacion text-muted">{{trans('files.label.confirmation')}}</th>
                            <th class="w-pax text-muted">{{trans('files.label.quantity_pax')}}</th>
                            <th class="w-partida text-muted">{{trans('files.label.departure')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-llegada text-muted">{{trans('files.label.arrival')}}</th>
                            <th class="w-hora text-muted">{{trans('files.label.time')}}</th>
                            <th class="w-status text-muted">{{trans('files.label.state')}}</th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="v in fileSelected.flights">
                            <td>@{{ v.CIUIN }}</td>
                            <td class="text-left">
                                @{{ ((!v.PREPED)?'':v.PREPED) + ' ' + ((!v.NROVUE)?'':v.NROVUE) + v.DESCRI }}
                            </td>
                            <td>@{{ v.CODCFM }}</td>
                            <td>@{{ v.CANPAX }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.HORIN }}</td>
                            <td>@{{ v.FECOUT }}</td>
                            <td>@{{ v.HOROUT }}</td>
                            <td>
                                <div v-html="v.ESTADO"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-modal>

    <modal-passengers ref="modal_passengers"></modal-passengers>

    <modal-flights ref="modal_flights"></modal-flights>

    <b-modal id="hotels" size="lg" hide-footer v-model="hotels">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><i class="icon-grid mx-2"></i>{{trans('files.label.list_of_hotels')}}</h2>
                <hr>
                <div class="my-2 d-flex align-content-center justify-content-around">
                    <p><strong>FILE:</strong> N°@{{ fileSelected.file_number }}</p>
                    <p><strong>REF:</strong> @{{ fileSelected.description }}</p>
                    <p class="text-right">
                        <b-button size="lg" variant="danger" v-on:click="downloadExcel('hotels')">
                            <i class="far fa-file-pdf"></i> EXCEL
                        </b-button>
                        <b-button size="lg" variant="danger" v-on:click="downloadPDF('hotels')">
                            <i class="far fa-file-word"></i> PDF
                        </b-button>
                    </p>
                </div>
            </div>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.hotels">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.hotels')}}</h3>
                <table class="table">
                    <thead class="p-0">
                        <tr>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="p-0">
                        <tr v-for="(v, k) in fileSelected.hotels">
                            <td>@{{ v.FECIN + ' - ' + v.FECOUT}}</td>
                            <td class="text-left">
                                <span v-if="v.RAZON!=null" style="display:block;">@{{ v.RAZON }}</span>
                                <span v-if="v.DIRECC!=null" style="display:block;">@{{ v.DIRECC }}</span>
                                <span v-if="v.CIUDAD!=null" style="display:block;">@{{ v.CIUDAD + ' ' + v.PAIS}}</span>
                                <span v-if="v.TELF!=null" style="display:block;">TELF: @{{ v.TELF }}</span>
                                <span v-if="v.FAX!=null" style="display:block;">FAX: @{{ v.FAX }}</span>
                                <!-- <span ng-show="h.TIPOHAB!=null" style="display:block;">@{{ h.TIPOHAB }}</span> -->
                                <span v-if="v.DESBAS!=null" v-html="v.DESBAS"></span>
                                <span v-if="v.TEXT2!=null" v-html="v.TEXT2" style="display:block;"></span>
                                RECORD: @{{ v.CONFIRMA }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-modal>

    <b-modal id="invoice" size="lg" hide-footer v-model="invoice">
        <div class="d-block" style="margin:-20px;" v-if="fileSelected.file_number">
            <div>
                <h2>
                    <i class="icon-grid mx-2"></i>{{trans('files.label.see_invoice')}} N°@{{ fileSelected.file_number }}
                    <span style="float: right;font-weight: normal;font-size: 15px;">
                        <a :href="baseURLFront + 'consulta_files/getPdfInvoice/'+ fileSelected.client_code +'/'+ fileSelected.file_number +'/'" target="_blank"><i class="icon-download"></i>Download</a>
                    </span>
                </h2>
            </div>
            <hr>
            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <div v-if="!loadingModal">

                <div class="md-modal">
                    <div class="md-content">
                        <div id="form_invoice" class="screen screen1">

                            <div class="mainContent content-factura">

                                <div class="row">
                                    <div class="col-6 text-left">
                                        <div class="pull-left">{{ \Carbon\Carbon::now()->locale($lang)->translatedFormat('d F Y')  }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                        <div class="right">LIMA TOURS S.A.C.</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 text-left">
                                        <div class="pull-left">@{{ invoiceDataUser.RAZON }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                        <div class="pull-right">
                                            Av. Juan de Arona 755, Piso 3
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 text-left">
                                        <div class="pull-left">@{{ invoiceDataUser.DIRECC }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                        <div class="pull-right">LIMA-PERU</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 text-left">
                                        <div class="pull-left">@{{ invoiceDataUser.PAIS }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                        <div class="pull-right">RUC.:20536830376</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 text-left">
                                        <div class="pull-left">RUC : @{{ invoiceDataUser.CUIT }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <p class="text-center"><strong>{{trans('files.label.invoice')}} Nº :</strong> @{{ fileSelected.file_number }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <p class="text-center"><strong>{{trans('files.label.name_pax_group')}} :</strong> @{{ fileSelected.description }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mainContent" style="margin-top: 25px;">
                                <div class="row mx-2">
                                    <div class="col-6 text-left">
                                        <div class="pull-left"><strong>{{trans('files.label.arrival_date')}}:</strong> @{{ fileSelected.DIAIN2 }}</div>
                                    </div>
                                    <div class="col-6 text-right">
                                        <div class="pull-right"><strong>{{trans('files.label.departure_date')}}:</strong> @{{ fileSelected.DIAOUT2 }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mainContent">
                                <div>
                                    <div class="col-12 mt-5" style="padding: 0px">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="nro-invoice text-muted">NRO</th>
                                                    <th class="descri-invoice text-muted">{{trans('files.label.description')}}</th>
                                                    <th class="preuni-invoice text-muted">{{trans('files.label.price_unit')}}</th>
                                                    <th class="pretot-invoice text-muted">{{trans('files.label.total')}} US$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(v, k) in invoiceData.report[0]">
                                                    <td class="nro-invoice">@{{ v.NROITE }}</td>
                                                    <td class="descri-invoice">@{{ v.DESCRI }}</td>
                                                    <td class="preuni-invoice">@{{ v.IMPCOM }}</td>
                                                    <td class="pretot-invoice">@{{ v.DEBEFORMAT }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="preuni-invoice">{{trans('files.label.total')}}:</td>
                                                    <td class="pretot-invoice">
                                                        <div class=""><strong>USD$ @{{ invoiceTotal }}</strong></div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <br />

                                <!-- NOTA DE CRÉDITO -->
                                <div v-if="invoiceTotalNotaCred>0">
                                    <div class="col-12" style="padding: 0px">
                                        <p class="text-center"><strong>{{trans('files.label.credit_note')}} Nº :</strong> @{{ fileSelected.file_number }}</p>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="nro-invoice text-muted">NRO</th>
                                                    <th class="descri-invoice text-muted">{{trans('files.label.description')}}</th>
                                                    <th class="preuni-invoice text-muted">{{trans('files.label.price_unit')}}</th>
                                                    <th class="pretot-invoice text-muted">{{trans('files.label.total')}} US$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(v, k) in this.invoiceData.notes.notacredito">
                                                    <td class="nro-invoice">@{{ v.NROITE }}</td>
                                                    <td class="descri-invoice">@{{ v.DESCRI }}</td>
                                                    <td class="preuni-invoice">@{{ v.IMPCOM }}</td>
                                                    <td class="pretot-invoice">@{{ v.DEBEFORMAT }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="preuni-invoice">{{trans('files.label.total')}}:</td>
                                                    <td class="pretot-invoice">
                                                        <div class=""><strong>USD$ @{{ invoiceTotalNotaCred }}</strong></div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <br />

                                <!-- NOTA DE DÉBITO -->
                                <div v-if="invoiceTotalNotaDebi>0">
                                    <div class="col-12" style="padding: 0px">
                                        <p class="text-center"><strong>{{trans('files.label.debit_note')}} Nº:</strong> @{{ fileSelected.file_number }}</p>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="nro-invoice">NRO</th>
                                                    <th class="descri-invoice">{{trans('files.label.description')}}</th>
                                                    <th class="preuni-invoice">{{trans('files.label.price_unit')}}</th>
                                                    <th class="pretot-invoice">{{trans('files.label.total')}} US$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(v, k) in this.invoiceData.notes.notadebito">
                                                    <td class="nro-invoice">@{{ v.NROITE }}</td>
                                                    <td class="descri-invoice">@{{ v.DESCRI }}</td>
                                                    <td class="preuni-invoice">@{{ v.IMPCOM }}</td>
                                                    <td class="pretot-invoice">@{{ v.DEBEFORMAT }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="preuni-invoice">{{trans('files.label.total')}}:</td>
                                                    <td class="pretot-invoice">
                                                        <div class=""><strong>USD$ @{{ invoiceTotalNotaDebi }}</strong></div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </b-modal>

    <b-modal id="guides" size="lg" hide-footer v-model="guides">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><i class="icon-grid mx-2"></i>{{trans('files.label.list_of_guides')}} - N°@{{ fileSelected.file_number }}</h2>
            </div>
            <hr>
            <div class="alert alert-warning mt-3 mb-3 text-center" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <div class="mx-5" v-if="!loadingModal && fileSelected.guides">
                <h3 class="my-5"><i class="far fa-hand-point-right mx-2"></i>{{trans('files.label.guide')}}</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th class="w-fecha">{{trans('files.label.date')}}</th>
                            <th class="w-ciudad">{{trans('files.label.city')}}</th>
                            <th class="w-servicio">{{trans('files.label.services')}}</th>
                            <th>{{ trans('files.label.guide') }}</th>
                            <th class="w-torre">{{trans('files.label.date')}} / {{trans('files.label.time')}}<br>{{trans('files.label.control_tower')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(v, k) in fileSelected.guides">
                            <td>@{{ k+1 }}</td>
                            <td>@{{ v.FECIN }}</td>
                            <td>@{{ v.CITY }}</td>
                            <td class="text-left">
                                <span v-html="v.SERVICE_HTML"></span>
                            </td>
                            <td>@{{ v.GUI }} <span v-if="v.CELGUI">(@{{ v.CELGUI }})</span></td>
                            <td>
                                <div v-html="v.TOWER_HTML"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </b-modal>

    <b-modal id="detail" size="md" hide-footer v-model="modalDetail">
        <div class="d-block" style="margin:-20px;">
            <div>
                <h2><i class="icon-grid mx-2"></i>{{trans('files.label.file_detail_status')}} N°@{{ nroFile }}</h2>
            </div>
            <hr>
            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{trans('files.label.loading')}}...</p>
            </div>

            <table class="table mx-5" v-if="!loadingModal">
                <tbody>
                    <tr v-for="(module, m) in modules">
                        <td class="text-left" style="border:0px; width: 250px;">@{{ m }}</td>
                        <td style="border:0px;" class="text-center">
                            <span v-bind:class="[ module == 1 ? 'badge-danger' : 'badge-success', 'badge' ]">
                                <i v-bind:class="[ module == 1 ? 'icon-x' : 'icon-check' ]"></i>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </b-modal>

    <b-modal id="flights" size="lg" hide-footer v-model="flightsModal">
        <div class="d-block" style="margin:-20px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2><i class="far fa-calendar-check mx-2"></i>{{trans('files.label.add_flights')}}</h2>
                    </div>
                </div>
                <hr>
                <!--
                    <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                        <p class="mb-0">{{trans('files.label.loading')}}...</p>
                    </div>
                    -->
                <template v-if="!loadingModal">
                    <div class="d-flex mb-5">
                        <input readonly="readonly" class="form-control" type="text" v-bind:value="returnURL()" />
                        <button type="button" class="btn btn-info" v-clipboard:copy="returnURL()">{{trans('files.btn.copy')}}</button>
                    </div>
                    <hr />
                </template>
                <div v-for="(item, index) in flights">
                    <div class="mt-5">
                        <div class="form-row d-flex justify-content-end align-items-center">
                            <div class="col-2 ">
                                <div class="left label_radio d-flex align-items-center">
                                    <input class="mx-2" type="radio" v-model="form.flightType" checked="true" value="AEC" v-on:change="changeFlightType">
                                    <label> {{trans('files.label.national')}}</label>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="left label_radio d-flex align-items-center">
                                    <input class="mx-2" type="radio" v-model="form.flightType" value="AEI" v-on:change="changeFlightType">
                                    <label>{{trans('files.label.international')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-prod">
                        <div class="form-row mb-4">
                            <div class="form-group origen">
                                <label>{{trans('files.label.origin')}}</label>
                                <b-form-select class="form-control" v-model="flights[index].origen" :options="optionsCities" :disabled="index>0"></b-form-select>
                            </div>
                            <div class="form-group destino">
                                <label>{{trans('files.label.destination')}}</label>
                                <b-form-select class="form-control" v-model="flights[index].destino" :options="optionsCities"></b-form-select>
                            </div>
                            <div class="form-group fecha">
                                <label>{{trans('files.label.departure_date')}}</label>
                                <date-picker :name="'picker'"
                                    v-model="date"
                                    :config="options">
                                </date-picker>
                            </div>
                            <div class="form-group">
                                <label>{{trans('files.label.time')}} {{trans('files.label.exit')}}:</label>
                                <div class="form-control salida">
                                    <input class="time" type="time" v-model="flights[index].horaida">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{trans('files.label.time')}} {{trans('files.label.of_arrival')}}:</label>
                                <div class="form-control llegada">
                                    <input class="time" type="time" v-model="flights[index].horaisalida">
                                </div>
                            </div>
                        </div>
                        <div class="form-row mb-4">
                            <div class="form-group pnr">
                                <label>PNR</label>
                                <b-form-input class="form-control" v-model="flights[index].pnr" type="text"></b-form-input>
                            </div>
                            <div class="form-group aereolinea">
                                <label>{{trans('files.label.air_line')}}</label>
                                <b-form-select class="form-control" v-model="flights[index].company" :options="optionsAirlines">
                                </b-form-select>
                            </div>
                            <div class="form-group vuelo">
                                <label>{{trans('files.label.num_flight')}}</label>
                                <b-form-input v-model="flights[index].numfly" type="text"></b-form-input>
                            </div>
                            <div class="form-group pax" style="display: none;">
                                <label>&nbsp;<br></label>
                                <b-button size="lg" block variant="danger" v-on:click="flights.pop()">[X]</b-button>
                            </div>
                            <div class="form-group pax">
                                <label>N° PAX</label>
                                <b-form-input v-model="form.pax" type="number" min="1" :max="fileSelected.CNTMAXPAXS"></b-form-input>
                            </div>
                        </div>
                        <div class="form-row mb-4">
                            <div class="form-group observaciones">
                                <label>{{trans('files.label.observations')}}</label>
                                <b-form-textarea class="form-control" v-model="form.obs" rows="4"></b-form-textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="my-3">
                    <div class="d-flex justify-content-end">
                        <div class="form-group btn-buscar mx-3">
                            <button class="btn agregar btn-seleccionar" v-on:click="addFlight()" :disabled="loadingModal"><i class="icon-plus mx-2"></i>{{trans('files.label.add_flights')}}</button>
                        </div>
                        <div class="form-group btn-buscar">
                            <button class="btn guardar btn-primary" v-on:click="saveFlight(fileSelected)" :disabled="loadingModal"><i class="icon-save mx-2"></i>{{trans('files.label.save')}}</button>
                        </div>
                    </div>
                </div>

                <div class="row mt-5 mx-5">
                    <div class="col-12 mt-5">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{trans('files.label.description')}}</th>
                                    <th>{{trans('files.label.outbound_date')}}</th>
                                    <th>{{trans('files.label.return_date')}}</th>
                                    <th>{{trans('files.label.air_line')}}</th>
                                    <th>{{trans('files.label.num_flight')}}</th>
                                    <th>PAX</th>
                                    <th>PNR</th>
                                    <th>{{trans('files.label.observations')}}</th>
                                    <th>{{trans('files.label.options')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loadingModal">
                                    <td class="text-center" colspan="9">{{trans('files.label.loading')}}....</td>
                                </tr>
                                <tr v-if="!loadingModal && allFlights" v-for="(v, k) in allFlights">
                                    <td>@{{ v.DESCRI }}</td>
                                    <td>@{{ v.FECIN | moment("D/M/YYYY") }}</td>
                                    <td>@{{ v.FECOUT | moment("D/M/YYYY") }}</td>
                                    <td>@{{ v.CIAVUE }}</td>
                                    <td>@{{ v.NROVUE }}</td>
                                    <td>@{{ v.CANPAX }}</td>
                                    <td>@{{ v.PNR }}</td>
                                    <td>@{{ v.INFOAD }}</td>
                                    <td><a href="javascript:void(0);" v-on:click="removeFlight(v)">[X]</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </b-modal>

</div>

@endsection
@section('css')
<style>

</style>
@endsection
@section('js')
<script src="{{ asset('js/components/pizza.js')}}"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            code: localStorage.getItem('code'),
            client_id: localStorage.getItem('client_id'),

            typeSearch: 'dates',
            inputSearchFile: '',
            inputSearchOrder: '',
            inputSearchDescription: '',
            inputSearchLocator: '',
            loadingModal: false,
            services: false,
            itinerary: false,
            skeleton: false,
            scheduled: false,
            paxs: false,
            hotels: false,
            invoice: false,
            guides: false,
            flightsModal: false,

            portadas: [],

            excel: '',

            invoiceData: {
                report: [],
                user: [],
                notes: []
            },
            invoiceDataUser: [],
            invoiceTotal: 0,
            invoiceTotalNotaCred: 0,
            invoiceTotalNotaDebi: 0,
            files: [],
            fileSelected: [],
            modalDetail: false,
            modules: [],
            nroFile: '',

            form: {
                flightType: 'AEC'
            },
            flights: [{
                origen: 'ABC',
                destino: 'ABC',
                fechaida: '',
                horaida: '00:00',
                horaisalida: '00:00',
                company: '7L',
                pnr: '',
                numfly: ''
            }],
            allFlights: [],
            optionsCities: [],
            citiesPe: [],
            citiesIn: [],
            optionsAirlines: [],
            dateRange: '',
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
            date: moment().format('DD-MM-YYYY'),

            optionsT: {
                format: 'h:mm:ss',
                useCurrent: true,
                showClear: true,
                showClose: true,
            },
            hourReminder: '',
            firstSearch: true,
            loading: false,
            baseExternalURL: '',
            loadingSearch: false,
            showBtnDownload: false,
            urlDocument: false,
            baseURLFront: baseURL,

            //variables modal quote
            idCliente: '',
            imagePortada: '',
            urlPortada: '',
            portadaName: '',
            caja: true,
            iconoX: false,
            updateImage: false,
            textoCliente: '',
            language_for_download: 'es',
            name_portada: '',
            client_logo: '',
        },

        mounted() {
            this.client_id = localStorage.getItem('client_id')
            switch (localStorage.getItem('lang')) {
                case 'es':
                    this.lang = 0
                    break;
                case 'pt':
                    this.lang = 2
                    break;
                case 'it':
                    this.lang = 3
                    break;
                default:
                    this.lang = 1
                    break;
            }
            this.search()

            this.baseExternalURL = window.baseExternalURL
            // this.searchCountries()
            // this.searchDoctypes()
        },

        computed: {},

        methods: {
            returnURL: function() {
                return baseURL + 'register_flights/' + this.fileSelected.file_number + '?lang=' + localStorage.getItem('lang')
            },
            doCopy: function() {
                let _message = this.returnURL()
                this.$copyText(_message).then(function(e) {
                    alert('Copiado!')
                    console.log(e)
                }, function(e) {
                    alert('No se pudo copiar..')
                    console.log(e)
                })
            },
            setWithClientLogo: function(fileSelected) {
                fileSelected.withClientLogo = false
            },
            setWithHeader2: function(fileSelected) {
                fileSelected.withHeader = false
            },
            setWithHeader: function(fileSelected) {
                console.log(fileSelected)
                if (this.fileSelected.withClientLogo == 1) {
                    this.imagePortada = ''
                    this.loading = true
                    this.caja = true

                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: this.fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.name_portada = result.data.portada
                    });

                    this.fileSelected.withHeader = true


                } else if (this.fileSelected.withClientLogo == 2) {

                    this.fileSelected.withHeader = true
                    this.loading = true
                    this.caja = true
                    this.imagePortada = '';


                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: this.fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.name_portada = result.data.portada
                    });

                } else {
                    this.imagePortada = ''
                    this.select_itinerary_with_client_logo = ""
                    this.fileSelected.withHeader = true

                    this.loading = true
                    this.caja = true


                    axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: this.fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.name_portada = result.data.portada
                    });

                }

                //    if((localStorage.getItem('client_name')).toUpperCase()  !== (localStorage.getItem('client_name')).toUpperCase() ){

                //        this.updateImage = true
                //        this.textoCliente = localStorage.getItem('client_name')

                //    }
            },
            search: function() {

                let vm = this
                this.client_id = localStorage.getItem('client_id');

                if (this.client_id.length == 0) {
                    vm.$toast.warning('Seleccionar un cliente para continuar', {
                        position: 'top-right'
                    })
                    return
                }

                if (!this.firstSearch) {

                    let error = false
                    let msgError = ''

                    if (this.typeSearch == 'dates' && !this.dateRange) {
                        msgError = 'Ingresar rango de fechas para iniciar búsqueda.'
                        error = true
                    } else if (this.typeSearch == 'file' && !this.inputSearchFile) {
                        msgError = 'Ingresar file para iniciar búsqueda.'
                        error = true
                    } else if (this.typeSearch == 'order' && !this.inputSearchOrder) {
                        msgError = 'Ingresar número de pedido para iniciar búsqueda.'
                        error = true
                    } else if (this.typeSearch == 'description' && !this.inputSearchDescription) {
                        msgError = 'Ingresar una descripción para iniciar búsqueda.'
                        error = true
                    } else if (this.typeSearch == 'locator' && !this.inputSearchLocator) {
                        msgError = 'Ingresar número de localizador para iniciar búsqueda.'
                        error = true
                    } else {}

                    if (error) {
                        vm.$toast.warning(msgError, {
                            position: 'top-right'
                        })
                        return
                    }
                }

                this.files = []
                this.loadingSearch = true

                axios.get(`${baseURL}consulta_files/getClient/${this.client_id}`)
                    .then((resCli) => {
                        const codCli = resCli.data.code //'9CORTE' //resCli.data.code
                        this.client_logo = resCli.data.logo
                        let url = ''

                        if (this.typeSearch == 'dates') {

                            if (this.firstSearch) {
                                url = `${baseURL}consulta_files/searchByDates/${codCli}/0/0`
                                this.firstSearch = false
                            } else {
                                console.log("Fecha Seleccionada: ", this.dateRange);
                                let sDate = this.dateRange.startDate
                                const sDateDD = ((sDate.getDate() < 10) ? '0' : '') + sDate.getDate().toString()
                                const sDateMM = (((sDate.getMonth() + 1) < 10) ? '0' : '') + (sDate.getMonth() + 1).toString()
                                const sDateYY = sDate.getFullYear().toString()
                                sDate = `${sDateYY}-${sDateMM}-${sDateDD}`

                                let eDate = this.dateRange.endDate
                                const eDateDD = ((eDate.getDate() < 10) ? '0' : '') + eDate.getDate().toString()
                                const eDateMM = (((eDate.getMonth() + 1) < 10) ? '0' : '') + (eDate.getMonth() + 1).toString()
                                const eDateYY = eDate.getFullYear().toString()
                                eDate = `${eDateYY}-${eDateMM}-${eDateDD}`

                                url = `${baseURL}consulta_files/searchByDates/${codCli}/${sDate}/${eDate}`
                            }

                        } else if (this.typeSearch == 'file') {
                            url = `${baseURL}consulta_files/searchByFile/${codCli}/${this.inputSearchFile}`
                        } else if (this.typeSearch == 'description') {
                            url = `${baseURL}consulta_files/searchByDescription/${codCli}/${encodeURIComponent(this.inputSearchDescription)}`
                        } else if (this.typeSearch == 'order') {
                            url = `${baseURL}consulta_files/searchByOrder/${codCli}/${this.inputSearchOrder}`
                        } else if (this.typeSearch == 'locator') {
                            url = `${baseURL}consulta_files/searchByLocator/${codCli}/${this.inputSearchLocator}`
                        } else {
                            url = ``
                        }

                        axios.get(url)
                            .then((result) => {
                                this.files = result.data.files
                                this.loadingSearch = false
                                this.hideLoader()
                            })
                            .catch((e) => {
                                this.files = []
                                this.loadingSearch = false
                                this.hideLoader()
                            })

                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showServices: function(file) {
                let vm = this
                vm.showModal('services')
                vm.loadingModal = true
                const nroRef = file.file_number
                this.fileSelected = file
                this.fileSelected.services = false
                const url = `${baseURL}consulta_files/getServices/${nroRef}/${this.lang}`
                axios.get(url)
                    .then((result) => {
                        if (typeof result.data.services !== 'undefined') {
                            this.fileSelected.services = (result.data.services.length == 0) ? false : result.data.services
                        }
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showSkeleton: function(file) {
                let vm = this
                vm.showModal('skeleton')
                vm.loadingModal = true
                const codCli = file.client_code,
                    nroRef = file.file_number,
                    nroLoc = '00000000'
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getSkeleton/${codCli}/${nroRef}/${nroLoc}/${this.lang}`
                axios.get(url)
                    .then((result) => {
                        this.fileSelected.services = []
                        let servicios = result.data.servicios;
                        servicios.forEach((element, pos) => {
                            this.fileSelected.services.push({
                                'CIUDDESCRI2': (!element.CIUDDESCRI2) ? '' : element.CIUDDESCRI2,
                                'CIUDESCRI': (!element.CIUDESCRI) ? '' : element.CIUDESCRI,
                                'CODSVS': (!element.CODSVS) ? '' : element.CODSVS,
                                'DESCRIPTION': (!element.DESCRIPTION) ? '' : element.DESCRIPTION,
                                'FECIN': (!element.FECIN) ? '' : element.FECIN,
                                'NROITE': (!element.NROITE) ? '' : element.NROITE,
                                'TABLA': (!element.TABLA) ? '' : element.TABLA,
                                'TABLA': (!element.TABLA) ? '' : element.TABLA,
                                'TEXTOS': (!element.TEXTOS) ? '' : element.TEXTOS,
                                'TEXTO_HTML': (!element.TEXTO_HTML) ? '' : element.TEXTO_HTML,
                                'DESCRI_ORIGINAL': (!element.DESCRI_ORIGINAL) ? '' : element.DESCRI_ORIGINAL,
                            })
                        });
                        // this.fileSelected.services = (result.data.servicios.length==0)?false:result.data.servicios
                        this.fileSelected.hotels = (result.data.hoteles.length == 0) ? false : result.data.hoteles
                        this.fileSelected.trains = (result.data.trenes.length == 0) ? false : result.data.trenes
                        this.fileSelected.flights = (result.data.flights.length == 0) ? false : result.data.flights
                        this.fileSelected.package = (result.data.package.length == 0) ? false : result.data.package
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showScheduled: function(file) {
                let vm = this
                vm.showModal('scheduled')
                vm.loadingModal = true
                const codCli = file.client_code,
                    nroRef = file.file_number
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getScheduled/${codCli}/${nroRef}/${this.lang}`
                axios.get(url)
                    .then((result) => {
                        this.fileSelected.services = (result.data.services.length == 0) ? false : result.data.services
                        this.fileSelected.hotels = (result.data.hotels.length == 0) ? false : result.data.hotels
                        this.fileSelected.trains = (result.data.trains.length == 0) ? false : result.data.trains
                        this.fileSelected.flights = (result.data.flights.length == 0) ? false : result.data.flights
                        this.fileSelected.package = (result.data.package.length == 0) ? false : result.data.package
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showPaxs: function(file) {
                let vm = this
                vm.showModal('paxs')
                vm.loadingModal = true
                const nroRef = file.file_number
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getPaxs/${nroRef}`
                axios.get(url)
                    .then((result) => {
                        this.fileSelected.file = (result.data.file.length == 0) ? false : result.data.file
                        this.fileSelected.paxs = (result.data.pasajeros.length == 0) ? false : result.data.pasajeros

                        console.log('file selected')
                        console.log(this.fileSelected)

                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showHotels: function(file) {
                let vm = this
                vm.showModal('hotels')
                vm.loadingModal = true
                const codCli = file.client_code,
                    nroRef = file.file_number
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getHotels/${codCli}/${nroRef}`
                axios.get(url)
                    .then((result) => {
                        //console.log(result)
                        this.fileSelected.hotels = (result.data.hoteles.length == 0) ? false : result.data.hoteles
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showInvoice: function(file) {
                let vm = this
                vm.showModal('invoice')
                vm.loadingModal = true
                const codCli = file.client_code,
                    nroRef = file.file_number
                this.fileSelected = file
                axios.get(`${baseURL}consulta_files/getInvoice/${codCli}/${nroRef}`)
                    .then((result) => {

                        this.invoiceData.report = result.data.report
                        this.invoiceData.user = result.data.user
                        this.invoiceData.notes = result.data.notes
                        this.invoiceDataUser = result.data.user

                        let total = 0
                        let totalNotaCred = 0
                        let totalNotaDebi = 0

                        this.invoiceData.report[0].forEach(function(v) {
                            total += parseFloat(v.DEBE)
                        })

                        this.invoiceData.notes.notacredito.forEach(function(v) {
                            totalNotaCred += parseFloat(v.DEBE)
                        })

                        this.invoiceData.notes.notadebito.forEach(function(v) {
                            totalNotaDebi += parseFloat(v.DEBE)
                        })

                        this.invoiceTotal = total
                        this.invoiceTotalNotaCred = totalNotaCred
                        this.invoiceTotalNotaDebi = totalNotaDebi
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showItinerary: function(file) {
                let vm = this
                vm.$set(vm, 'itinerary', true)
                vm.$set(vm, 'fileSelected', file)
                vm.$set(vm.fileSelected, 'portada', 'amazonas')
                vm.$set(vm.fileSelected, 'withHeader', true)
                vm.$set(vm.fileSelected, 'withClientLogo', 1)

                this.showBtnDownload = false
                //this.itinerary = true
                //this.fileSelected = file
                //this.fileSelected.withHeader = true

                //this.searchPortadas()
                this.setComboPortada(vm.fileSelected);
            },

            searchPortadas: function() {
                let vm = this
                this.showLoader('Cargando..')

                axios.post(`${baseURL}consulta_files/getPortadas/${this.lang}`)
                    .then((result) => {
                        this.portadas = result.data
                        vm.fileSelected.portada = result.data[0].CODGRU

                        this.hideLoader()
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },
            async changeWithCover(fileSelected) {
                let _toggle = (this.fileSelected.withHeader) ? 'block' : 'none'
                $('.showWithCover').css('display', _toggle)
                if (!this.fileSelected.withHeader) {
                    this.fileSelected.withClientLogo = 4
                    this.imagePortada = "";
                    this.urlPortada = '';

                } else {

                    this.loading = true
                    this.caja = true;
                    this.imagePortada = '';

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: 3,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.caja = false;
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';

                    });

                    this.fileSelected.withClientLogo = 3
                }

                // if((localStorage.getItem('client_name')).toUpperCase()  !== (localStorage.getItem('client_name')).toUpperCase() ){

                //     this.updateImage = true
                //     this.textoCliente = localStorage.getItem('client_name')
                // }

                //this.$forceUpdate()
            },

            async setComboPortada(fileSelected) {

                if (fileSelected.withClientLogo == 3) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';


                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.caja = false;
                        this.name_portada = result.data.portada

                    });

                } else if (fileSelected.withClientLogo == 1) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';

                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.caja = false;
                        this.name_portada = result.data.portada

                    });
                } else if (fileSelected.withClientLogo == 2) {
                    this.caja = true;
                    this.loading = true
                    this.imagePortada = '';

                    this.select_itinerary_with_client_logo = this.select_itinerary_with_cover;

                    await axios.get(window.a3BaseQuoteServerURL + 'api/quote/imageCreate', {
                        params: {
                            file: fileSelected.file_number,
                            clienteId: this.client_id,
                            portada: fileSelected.portada,
                            portadaName: fileSelected.description,
                            estado: fileSelected.withClientLogo,
                            refPax: localStorage.getItem('client_name'),
                            lang: localStorage.getItem('lang'),
                            itinerary: true,
                        }
                    }).then((result) => {

                        this.imagePortada = window.a3BaseQuoteServerURL + result.data.image + '.jpg'
                        this.loading = false
                        this.urlPortada = result.data.image + '.jpg';
                        this.name_portada = result.data.portada
                        this.caja = false;

                    });
                }
            },

            // downloadItinerary: function(file) {
            //     let vm = this
            //     let withClientLogoNot = file.withClientLogo == 4 ? 0 : file.withClientLogo
            //     let withHeaderNot = file.withHeader == false ? 0 : 1;
            //     //file.withHeader = (file.withHeader && !withClientLogoNot) ? 1 : 0
            //     file.portada = (withClientLogoNot) == 0 ? '0000' : file.portada;
            //     let imgPortada = (withClientLogoNot) == 0 ? 0 : this.name_portada;
            //     if ((file.withHeader == 1 && file.portada == '-') || (withClientLogoNot == 1 && file.portada == '-')) {
            //         vm.$toast.warning('Seleccione una portada para continuar.', {
            //             position: 'top-right'
            //         })
            //         return false
            //     }
            //
            //     // this.showLoader('Cargando..')
            //     this.loading = true
            //     this.showBtnDownload = false
            //     axios.get(`${baseURL}consulta_files/getItinerary/${file.file_number}/${file.client_code}/${withHeaderNot}/${withClientLogoNot}/${file.portada}/${this.lang}/${imgPortada}`)
            //         .then((result) => {
            //             if (result.data.estado == 1) {
            //                 // this.itinerary = false
            //                 this.loading = false
            //                 this.showBtnDownload = true
            //                 // let link = document.createElement("a")
            //                 // link.download = result.data.message
            //                 this.urlDocument = 'https://extranet.litoapps.com/pdf/' + result.data.message
            //                 // link.click();
            //             }
            //         })
            //         .catch((e) => {
            //             this.loading = false
            //
            //             console.log(e)
            //         })
            // },

            downloadItinerary: function(file) {
                let vm = this
                let withClientLogoNot = file.withClientLogo == 4 ? 0 : file.withClientLogo
                let withHeaderNot = file.withHeader == false ? 0 : 1;
                file.portada = (withClientLogoNot) == 0 ? '0000' : file.portada;
                let imgPortada = (withClientLogoNot) == 0 ? 0 : this.name_portada;

                if ((file.withHeader == 1 && file.portada == '-') || (withClientLogoNot == 1 && file.portada == '-')) {
                    vm.$toast.warning('Seleccione una portada para continuar.', {
                        position: 'top-right'
                    })
                    return false
                }

                this.loading = true
                this.showBtnDownload = false

                axios.get(`${baseURL}consulta_files/getItinerary/${file.file_number}/${file.client_code}/${withHeaderNot}/${withClientLogoNot}/${file.portada}/${this.lang}/${imgPortada}`)
                    .then((result) => {
                        if (result.data.estado == 1) {
                            this.loading = false
                            this.showBtnDownload = true
                            this.urlDocument = result.data.message;
                        } else {
                            // Manejo de error si estado != 1
                            vm.$toast.error('Error al generar el itinerario', { position: 'top-right' });
                            this.loading = false;
                        }
                    })
                    .catch((e) => {
                        this.loading = false
                        console.log(e)
                        vm.$toast.error('Ocurrió un error de conexión', { position: 'top-right' });
                    })
            },

            modalPassengers: function(nrofile, paxs) {
                this.$refs.modal_passengers.modalPassengers('file', nrofile, paxs)
            },


            modalFlights: function(nrofile) {
                this.$refs.modal_flights.modalFlight(nrofile, undefined, true)
            },

            completeFile: function(nrofile, paxs) {
                let lang = localStorage.getItem('lang')
                window.open(`${baseURL}register_file/${nrofile}?lang=${lang}&paxs=${paxs}&canadl=0&canchd=0&caninf=0`);
            },

            showFlights: function(file) {
                let vm = this
                vm.showModal('flightsModal')
                vm.loadingModal = true
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getFlights/${file.file_number}`
                axios.get(url)
                    .then((result) => {
                        console.log(result)
                        //this.fileSelected.flights = (result.data.length==0)?false:result.data
                        this.allFlights = (result.data.length == 0) ? false : result.data

                        const urlCities = `${baseURL}consulta_files/getFlightsData`
                        axios.get(urlCities)
                            .then((response) => {
                                // console.log(response)
                                this.citiesPe = this.getCities(response.data.citiesPe)
                                this.citiesIn = this.getCities(response.data.citiesIn)
                                this.optionsCities = this.citiesPe
                                this.optionsAirlines = this.getAirLines(response.data.airlines)
                            });

                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            saveFlight: function(file) {
                let vm = this
                vm.loadingModal = true

                const newFile = [{
                    nrofile: file.file_number,
                    pax: this.form.pax,
                    paxtotal: file.CNTMAXPAXS,
                    obs: this.form.obs,
                    type: this.form.flightType,
                    typefly: 'IV'
                }];

                axios.post(`${baseURL}consulta_files/saveFlight`, {
                        params: {
                            flights: this.flights,
                            file: newFile
                        }
                    })
                    .then((result) => {

                        vm.$toast.success('Se añadió correctamente la información...', {
                            position: 'top-right'
                        })

                        const url = `${baseURL}consulta_files/getFlights/${file.file_number}`
                        axios.get(url)
                            .then((result) => {
                                this.allFlights = (result.data.length == 0) ? false : result.data
                                vm.loadingModal = false
                            })

                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },

            addFlight: function() {
                let vm = this
                if (!this.flights[(this.flights.length - 1)].destino) {
                    vm.$toast.warning('Seleccione destino para continuar.', {
                        position: 'top-right'
                    })
                    return false
                }
                this.flights.push({})
                this.flights[(this.flights.length - 1)].origen = this.flights[(this.flights.length - 2)].destino
            },

            removeFlight: function(flight) {

                let vm = this
                vm.loadingModal = true

                var newFlight = {
                    nroref: flight.file_number,
                    nroite: flight.NROITE,
                    codven: flight.CODVEN,
                    origin: flight.DESCRI,
                    fecin: flight.FECIN,
                    fecout: flight.FECOUT
                };

                axios.post(`${baseURL}consulta_files/removeFlight`, {
                        params: {
                            flight: newFlight
                        }
                    })
                    .then((result) => {

                        if (result.data.response == 'success') {
                            vm.$toast.success('Se eliminó correctamente la información...', {
                                position: 'top-right'
                            })

                            const url = `${baseURL}consulta_files/getFlights/${flight.file_number}`
                            axios.get(url)
                                .then((result) => {
                                    this.allFlights = (result.data.length == 0) ? false : result.data
                                    vm.loadingModal = false
                                })
                        } else {
                            vm.$toast.warning(result.data.text, {
                                position: 'top-right'
                            })
                            vm.loadingModal = false
                        }

                    })
                    .catch((e) => {
                        console.log(e)
                    })

            },

            changeFlightType: function() {
                this.flights = [{}];
                if (this.form.flightType == 'AEC') this.optionsCities = this.citiesPe
                else this.optionsCities = this.citiesIn
            },

            showButtonClose: function(index) {
                if ((parseInt(index) > 0) && ((this.flights.length - 1) == parseInt(index))) return true
                else return false
            },

            showGuides: function(file) {
                let vm = this
                vm.showModal('guides')
                vm.loadingModal = true
                const nroRef = file.file_number
                this.fileSelected = file
                const url = `${baseURL}consulta_files/getGuides/${nroRef}/${this.lang}`
                axios.get(url)
                    .then((result) => {
                        this.fileSelected.guides = (result.data.length == 0) ? false : result.data
                        vm.loadingModal = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.hideLoader()
                    })
            },

            showModal: function(element) {
                if (eval('this.' + element + ' == false')) {
                    eval('this.' + element + ' = true')
                }
            },

            showLoader: function(texto) {
                this.loading = true
                let $backdrop = $(".backdrop-banners"),
                    timeLoading = 250

                $backdrop.css({
                    display: 'block'
                }).animate({
                    opacity: 1
                }, timeLoading, function() {
                    $backdrop.prepend('<div id="spinner-loader"><div class="spinner"><span class="logo"></span></div>' +
                        '<div class="spinner-text">' + texto + '<small>Por favor espere.</small></div></div>')
                })
            },

            hideLoader: function() {
                this.loading = false
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

            showDetailFile: function(nroref) {
                this.modalDetail = true
                this.loadingModal = true
                this.nroFile = nroref

                axios.post(
                        baseURL + 'board/file', {
                            file: this.nroFile
                        }
                    )
                    .then((result) => {
                        this.loadingModal = false
                        this.modules = result.data.modulos
                    })
                    .catch((e) => {
                        this.loadingModal = false

                        if (e.message == 'Unauthenticated.') {
                            window.location.reload()
                        }
                    })
            },

            getCities(allCities) {
                let cities = []
                allCities.forEach((v) => {
                    cities.push({
                        value: v.CODCIU,
                        text: v.CIUDAD + ' (' + v.CODCIU + ') - ' + v.PAIS
                    })
                })
                return cities
            },

            getAirLines(allAirLines) {
                let airlines = []
                allAirLines.forEach((v) => {
                    airlines.push({
                        value: v.CODIGO,
                        text: v.RAZON
                    })
                })
                return airlines
            },

            downloadExcel: function(_type) {
                window.location = baseURL + 'export_excel?type=' + _type + '&table=';
            },

            downloadPDF: function(_type) {
                window.location = baseURL + 'export_pdf?type=' + _type;
            },

            downloadDOCX: function(file) {
                const codCli = file.client_code,
                    nroRef = file.file_number,
                    nroLoc = '00000000'
                // window.location = `https://extranet.litoapps.com/skeleton-word.php?from=A2&lang=${this.lang}&codcli=${codCli}&f=${nroRef}&logo=${this.client_logo}`
                window.location = baseFilesOnedbURL + `tracking/handleSkeleton?from=A2&lang=${this.lang}&codcli=${codCli}&f=${nroRef}&logo=${this.client_logo}`
            }

        }
    })
</script>
@endsection
