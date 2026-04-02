<div class="box-acciones d-flex align-items-center justify-content-between">
    <div class="d-flex">
        <div class="mx-4">
            <a class="acciones__item" @click="openModalClose()">
                <span><i class="far fa-window-close"></i> {{ trans('quote.label.close') }}</span>
            </a>
        </div>
        <div class="mx-4">
            <a class="acciones__item" v-if="quote_id != null" @click="saveQuotePrev">
                <span><i class="far fa-save"></i> {{ trans('quote.label.save') }}</span>
            </a>
        </div>
        <div class="mx-4" v-if="quote_open.id_original != '' && !isQuoteBlocked">
            <a class="acciones__item" @click="openModalSaveAs">
                <span><i class="far fa-save"></i> {{ trans('quote.label.save_as') }}</span>
            </a>
        </div>
        <div class="mx-4" v-if="permissions.converttopackage && !isQuoteBlocked">
            <a class="acciones__item" @click="$refs.confirmConvertModal.show()">
                <span>
                    <i class="icon-repeat"></i> {{ trans('quote.label.convert_to_package') }}
                </span>
            </a>
        </div>
        <div class="mx-4 dropdown-group" >
            <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
               class="acciones__item" @click="downloadDropdown()">
                <span ><i class="icon-download"  ></i>{{ trans('quote.label.download') }}</span>
            </a>
            <div v-show="new_order_related" class="dropdown-menu dropdown-menu__opciones dropdown-menu-right" style="z-index: 100" @click.stop="clickDropDown($event)">
                <div class="dropdown-menu_body">
                    <div class="miniMenu">
                        <a href="javascript:;" @click.stop="willDownloadSkeleton()">
                            Skeleton <i class="icon-download"></i> @{{ quote_open.showDownloadSkeleton }}
                        </a>
                        <br>
                        <a @click.stop="goExport()" href="javascript:;"
                           v-if="quote_open.operation != '' && quote_open.operation != null">
                           {{ trans('quote.label.excel') }} <i class="icon-download"></i>
                        </a>
                        <br>
                        <a href="javascript:;" @click.stop="willDownloadItinerary(quote_open)">
                            {{ trans('quote.label.itinerary') }} <i class="icon-download"></i>
                        </a>
                    </div>
                    <div  class="showDownloadSkeleton">
                        <form>
                            <h5>Skeleton <i class="icon-download"></i></h5>
                            <hr>
                            <div class="ml-2">
                                <input type="text" click.stop="" v-model="refPax"
                                       placeholder="{{ trans('quote.label.reference_pax') }}"
                                       style="border: solid 1px #d1d1d1; padding: 0 5px;">
                            </div>
                            <h5>{{ trans('quote.label.category') }}:</h5>
                            <div class="ml-2">
                                <label class="d-flex align-items-center" v-for="c in quote_open.categories"
                                       @click.stop="quote_open.radioCategories=c.id">
                                    <input :disabled="loading" name="category_radio" type="radio" :value="c.id"
                                           v-model="quote_open.radioCategories">
                                    (@{{ c.type_class ? c.type_class.code : '' }}) @{{ c.type_class ? c.type_class.translations[0].value : '' }}
                                </label>
                            </div>
                            <h5>{{ trans('quote.label.language') }}:</h5>
                            <div class="ml-2">
                                <select @click.stop="" v-model="language_for_download">
                                    <option value="es" selected>{{ trans('quote.label.spanish') }}</option>
                                    <option value="en">{{ trans('quote.label.english') }}</option>
                                    <option value="pt">{{ trans('quote.label.portuguese') }}</option>
                                    <option value="it">{{ trans('quote.label.italian') }}</option>
                                </select>
                            </div>
                            <h5>{{ trans('quote.label.header') }}:</h5>
                            <div class="d-flex align-items-center">
                                <label class=" mx-3" @click.stop="">
                                    <input :disabled="loading" name="with_header_radio" type="radio" :value="true"
                                           v-model="quote_open.withHeader"> Sí
                                </label>
                                <label class=" mx-3" @click.stop="">
                                    <input :disabled="loading" name="with_header_radio" type="radio" :value="false"
                                           v-model="quote_open.withHeader"> No
                                </label>
                                <br>
                            </div>
                            <div class="d-flex my-3">
                                <button :disabled="loading" class="btn btn-secondary mx-1" type="button"
                                        @click.stop="backMiniMenu()">
                                    {{ trans('quote.label.cancel') }}
                                </button>
                                <button :disabled="loading" class="btn btn-primary mx-1" type="button"
                                        @click.stop="downloadSkeleton(quote_open)">
                                    <i class="fa fa-spin fa-spinner"
                                       v-if="loading"></i> {{ trans('quote.label.download') }}
                                </button>
                            </div>
                        </form>

                    </div>
                    <div  class="showDownloadItinerary" @click.stop="clickDropDown($event)" >
                        <form>
                            <h5>{{ trans('quote.label.itinerary') }} <i class="icon-download"></i></h5>
                            <hr>
                            <div class="ml-2">
                                <input type="text" click.stop="" v-model="refPax"
                                       placeholder="{{ trans('quote.label.reference_pax') }}"
                                       style="border: solid 1px #d1d1d1; padding: 0 5px; width: 190px;">
                            </div>
                            <h5>{{ trans('quote.label.category') }}:</h5>
                            <div class="ml-2" v-if="quote_open.categories">
                                <label class="d-flex align-items-center" style="gap: 5px;">
                                    <input :disabled="loading" name="category_radio" type="radio" value="0" v-model="quote_open.radioCategories">
                                    Todos
                                </label>
                                <label class="d-flex align-items-center" style="gap: 5px;" v-for="c in quote_open.categories"
                                       @click.stop="quote_open.radioCategories=c.id">
                                    <input :disabled="loading" name="category_radio" type="radio" :value="c.id"
                                           v-model="quote_open.radioCategories">
                                    @{{ c.type_class ? c.type_class.translations[0].value : '' }}
                                </label>
                            </div>
                            <h5>{{ trans('quote.label.language') }}:</h5>
                            <div class="ml-2">
                                <select @click.stop="" v-model="language_for_download" style="width: 190px;">
                                    <option value="es" selected>{{ trans('quote.label.spanish') }}</option>
                                    <option value="en">{{ trans('quote.label.english') }}</option>
                                    <option value="pt">{{ trans('quote.label.portuguese') }}</option>
                                    {{-- <option value="it">{{ trans('quote.label.italian') }}</option> --}}
                                </select>
                            </div>
                            <h5>{{ trans('quote.label.do_you_want_a_cover') }}:</h5>
                            <div class="d-flex align-items-center">
                                <label class=" mx-3" @click.stop="">
                                    <input :disabled="loading" @change="changeWithCover(quote_open)"
                                            name="with_header_radio_header1" type="radio" :value="true"
                                            v-model="quote_open.withHeader"> Sí
                                </label>
                                <label class=" mx-3" @click.stop="">
                                    <input :disabled="loading" @change="changeWithCover(quote_open)"
                                           name="with_header_radio_header1" type="radio" :value="false"
                                           v-model="quote_open.withHeader"> No
                                </label>
                            </div>
                            <div class="d-flex align-items-center" @click.stop="">
                                <select @click.stop="" v-model="select_itinerary_with_cover"   style="width: 190px;"
                                        @change="setComboPortada(quote_open)"
                                        class="showWithCover"
                                        v-if="imagePortada && quote_open.withHeader !=''">
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
                            </div>
                            <div style="height: 170px; width: 170px" v-if="caja==true && loading==true">

                            </div>
                            <template v-if="imagePortada && quote_open.withHeader !=''">



                                <div class="d-flex align-items-center" v-if="imagePortada" id="zoomImage">
                                    {{-- se cambia carpeta word por la de portada --}}
                                    <img class="showWithCover"
                                         :src="imagePortada"
                                         style="margin: 9px;width: 170px;height: auto;transition: transform .2s;cursor: pointer"
                                         @click.stop="zoomImage()" >
                                    <div v-if="iconoX==true" id="closeImage" class="estiloX" @click.stop="reducirImage()"><span style="position: relative;margin: auto;top: -3px;">X</span></div>
                                </div>


                                <h5>{{ trans('quote.label.do_you_want_a_client_logo') }}:</h5>
                                <div class="d-flex align-items-center">
                                    <label class=" mx-3" @click.stop="">
                                        <input :disabled="loading"
                                               name="with_header_radio1" type="radio"
                                               :value="1" v-model="quote_open.withClientLogo"
                                               @change="setWithHeader(quote_open)"
                                               > {{ trans('quote.label.yes') }}
                                    </label>
                                    <label class=" mx-3" @click.stop="">
                                        <input :disabled="loading"
                                               name="with_header_radio2" type="radio"
                                               @change="setWithHeader(quote_open)"
                                               :value="2" v-model="quote_open.withClientLogo"> {{ trans('quote.label.no') }}
                                    </label>

                                    <label class=" mx-3" @click.stop="">
                                        <input :disabled="loading"
                                               name="with_header_radio3" type="radio"
                                               @change="setWithHeader(quote_open)"
                                               :value="3" v-model="quote_open.withClientLogo"> {{ trans('quote.label.nothing') }}
                                    </label>
                                </div>


                            </template>

                            {{-- ------- Combo portada cliente ----- --}}

                            {{-- <div class="d-flex align-items-center" @click.stop="">
                                <select @click.stop=""
                                        v-model="select_itinerary_with_client_logo"
                                        v-if="quote_open.withClientLogo">
                                    <option value="0000">PARACAS - RESERVA</option>
                                    <option value="0001">CHOQUEQUIRAO</option>
                                    <option value="0002">CRUCEROS - FRONT</option>
                                    <option value="0003">CRUCEROS - LADO</option>
                                    <option value="0004">CAÑÓN DEL COLCA</option>
                                    <option value="0005">FAMILIA EN CUSCO</option>
                                    <option value="0006">PARACAS - CANDELABRO</option>
                                    <option value="0020">MACHU PICCHU 1</option>
                                    <option value="0010">MACHU PICCHU 2</option>
                                    <option value="0011">MACHU PICCHU 3</option>
                                    <option value="0012">MACHU PICCHU 4</option>
                                    <option value="0013">ICA - TUBULARES</option>
                                    <option value="0015">CATARATAS DE GOCTA</option>
                                    <option value="0017">PUERTO MALDONADO 1</option>
                                    <option value="0033">PUERTO MALDONADO 2</option>
                                    <option value="0018">SALKANTAY</option>
                                    <option value="0019">VALLE SAGRADO 1</option>
                                    <option value="0022">VALLE SAGRADO 2</option>
                                    <option value="0021">HUILLOC</option>
                                    <option value="0023">CEVICHE 1</option>
                                    <option value="0028">CEVICHE 2</option>
                                    <option value="0024">MAÍZ MORADO</option>
                                    <option value="0025">HUAYNA PICCHU</option>
                                    <option value="0026">VINICUNCA</option>
                                    <option value="0027">LIMA</option>
                                    <option value="0029">OLLANTAYTAMBO 1</option>
                                    <option value="0030">OLLANTAYTAMBO 2</option>
                                    <option value="0031">NORTE</option>
                                    <option value="0032">PUNO - CHULLPAS DE SILLUSTANI</option>
                                    <option value="0034">TARAPOTO</option>
                                    <option value="0035">TRUJILLO</option>
                                    <option value="0037">TREE HOUSE LODGE</option>
                                </select>
                            </div> --}}


                           <!--  <div class="d-flex align-items-center"  v-if="quote_open.withHeader">
                            Se cambia carpeta word_with_client_logo por la de portadas-cliente  cambiamos select_itinerary_with_client_logo por select_itinerary_with_cover
                                <img v-if="quote_open.withClientLogo"
                                     :src="baseExternalURL + 'images/portadas-cliente/' + 'cliente-' + select_itinerary_with_cover + '.jpg'"
                                     style="margin: 9px;width: 120px;height: auto;">
                            </div>-->
                            <div class="d-flex my-3" >
                                <button :disabled="loading" class="btn btn-secondary mx-1" type="button"
                                        @click.stop="backMiniMenu()">
                                    {{ trans('quote.label.cancel') }}
                                </button>
                                <button :disabled="loading" class="btn btn-primary mx-1" type="button"
                                        @click.stop="downloadItinerary(quote_open)">
                                    <i class="fa fa-spin fa-spinner"
                                       v-if="loading"></i> {{ trans('quote.label.download') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <span id="button-quote-me" class="d-inline-block" tabindex="0" v-if="quote_open != null && quote_open != '' && !isMultiRegionQuote">
        <b-button class="btn btn-primary" @click="quoteMePrev()" :disabled="loading_reserve ||  quote_open.id_original === '' || quote_open.id_original === null">
            <i class="far fa-list-alt"></i> {{ trans('quote.label.quote_me') }}
        </b-button>
    </span>
    <b-tooltip target="button-quote-me" variant="warning" v-if="quote_open.id_original === '' || quote_open.id_original === null" custom-class="my-tooltip">
        {{trans('quote.label.please_first_save_your_quote')}}
    </b-tooltip>
    <span id="button-booking" class="d-inline-block" tabindex="0">
        <b-button class="btn btn-primary ml-4" @click="goto_file" v-if="quote_open.file_id">
            {{ trans('quote.label.goto_file') }} <i class="fa fa-spin fa-spinner" v-show="loading_reserve"></i>
        </b-button>
        <b-button class="btn btn-primary" @click="confirmReservePrev" v-else
                  :disabled="isDisabledReservation || loading_reserve || quote_open.operation === 'ranges' || quote_open.id_original === '' || quote_open.id_original === null">
        {{ trans('quote.label.reserve') }} <i class="fa fa-spin fa-spinner" v-show="loading_reserve"></i>
        </b-button>
    </span>
    <template v-if="isDisabledReservation">
        <b-tooltip target="button-booking" variant="warning" custom-class="my-tooltip">
            {{trans('reservations.label.disabled')}}
        </b-tooltip>
    </template>
    <template v-else>
        <b-tooltip target="button-booking" variant="warning" v-if="quote_open.operation === 'ranges'" custom-class="my-tooltip">
            {{trans('quote.label.you_cannot_reserve_by_ranges')}}
        </b-tooltip>
        <b-tooltip target="button-booking" variant="warning" v-if="quote_open.id_original === '' || quote_open.id_original === null" custom-class="my-tooltip">
            {{trans('quote.label.please_first_save_your_quote')}}
        </b-tooltip>
    </template>
</div>

