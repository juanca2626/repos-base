<div class="d-block" style="margin:-20px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2> {{ trans('board.label.passenger_data') }}</h2>
            </div>
            <div class="d-flex justify-content-start">
                <p class="col-7">{{ trans('board.label.enter_the_requested_information') }}: <strong> @{{ detailPassengers.canadl }}</strong> {{ trans('board.label.adults') }} <span v-if="detailPassengers.canchd > 0"><strong> @{{ detailPassengers.canchd }}</strong> {{ trans('board.label.kids') }}</span> <span v-if="detailPassengers.canchd > 0"><strong> @{{ detailPassengers.caninf }}</strong> {{ trans('board.label.infants') }}</span></p>
                <div class="col-7">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input radio" type="radio" name="inlineRadioOptions1" id="inlineRadio1" value="1" v-model="modePassenger">
                        <label class="form-check-label" for="inlineRadio1">{{ trans('board.label.basic_mode') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input radio" type="radio" name="inlineRadioOptions2" id="inlineRadio2" value="2" v-model="modePassenger">
                        <label class="form-check-label" for="inlineRadio2">{{ trans('board.label.full_mode') }}</label>
                    </div>
                </div>
                <div class="col-2" v-if="detailPassengers.estado == 0">
                    <form>
                    <input type="file" ref="file" name="file" id="file" v-on:change="changeUpload()" style="display:none;" />
                        <label for="file" v-if="excel == ''"><i class="icon-share"></i> {{ trans('board.label.load_list') }}</label>
                        <button class="btn btn-primary" v-if="excel != ''" type="button" v-on:click="importPassengers()">Procesar Excel</button>
                    </form>
                </div>
                <div class="col-2" v-if="detailPassengers.estado == 0">
                    <a href="{{ url('/samples/IMPORTADOR.xlsx') }}" target="_blank">Descargar formato</a>
                </div>
            </div>
        </div>
        <!----------- Modo Basico ----------->
        <div>
            <div class="row mt-3">
                <div class="col-12 form-check form-check-inline pl-4">
                    <input class="form-check-input" type="checkbox" name="inlineOption" id="inline1" :disabled="detailPassengers.estado > 0" value="1" v-model="repeatPassenger">
                    <label class="form-check-label" for="inline1">{{ trans('board.label.repeat_1st_passenger_data') }}</label>
                </div>
            </div>

            <div v-if="repeatPassenger == 1">
                <div class="row mt-5">
                    <div class="d-flex justify-content-start pl-4">
                        <form class="form-inline">
                            <div class="form-group information">
                                <label class="pr-5"><strong>{{ trans('board.label.passenger') }}</strong></label>
                                <input type="text" class="form-control name ml-5" placeholder="{{ trans('board.label.name_passenger') }}" v-model="passengers[0].nombres" :disabled="detailPassengers.estado > 0"/>
                                <input type="text" class="form-control last-name ml-5" placeholder="{{ trans('board.label.last_name_passenger') }}" v-model="passengers[0].apellidos" :disabled="detailPassengers.estado > 0"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!----------- End Modo Basico ----------->
        <!----------- Modo Basico 10personas ----------->
        <div v-if="modePassenger == 1 && repeatPassenger != 1">
            <div class="row">
                <div class="mt-5 col-6 d-flex justify-content-start pl-4" v-for="index in totalPassengers">
                    <form class="form-inline">
                        <div class="form-group information-basic">
                            <label class="pr-3">
                                <strong>{{ trans('board.label.passenger') }} @{{ index }} (@{{ passengers[index - 1].tipo }})</strong>
                            </label>
                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.name_passenger') }}" v-model="passengers[index - 1].nombres" :disabled="detailPassengers.estado > 0"/>
                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.last_name_passenger') }}" v-model="passengers[index - 1].apellidos" :disabled="detailPassengers.estado > 0"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!----------- End Modo Basico 10personas ----------->
        <!----------- Modo completo ----------->
        <div v-if="modePassenger == 2 && repeatPassenger != 1">
            <div class="mt-5">
                <div id="accordion" role="tablist">
                    <div class="card mb-3" v-for="index in totalPassengers">
                        <button class="b-left" data-toggle="collapse" v-bind:href="'#collapse_' + index" aria-expanded="true" v-bind:aria-controls="'collapse_' + index">
                            <div class="card-header d-flex" role="tab" v-bind:id="'heading_' + index">
                                {{ trans('board.label.passenger') }} @{{ index }} (@{{ passengers[index - 1].tipo }})
                            </div>
                        </button>
                        <div v-bind:id="'collapse_' + index" v-bind:class="['collapse', (index == 1) ? 'show' : '']" role="tabpanel" v-bind:aria-labelledby="'heading_' + index" data-parent="#accordion">
                            <div class="card-body information-complete">
                                <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                    <form class="form-inline">
                                        <div class="form-group">
                                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.name_passenger') }}" v-model="passengers[index - 1].nombres" :disabled="detailPassengers.estado > 0"/>
                                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.last_name_passenger') }}" v-model="passengers[index - 1].apellidos" :disabled="detailPassengers.estado > 0"/>
                                            <select class="form-control ml-3" v-model="passengers[index - 1].sexo" :disabled="detailPassengers.estado > 0">
                                                <option value="">{{ trans('board.label.gender_passenger') }}</option>
                                                <option value="M">{{ trans('board.label.male') }}</option>
                                                <option value="F">{{ trans('board.label.female') }}</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                    <form class="form-inline">
                                        <div class="form-group">
                                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.date_of_birth') }}" v-model="passengers[index - 1].fecnac" :disabled="detailPassengers.estado > 0"/>
                                            <select class="form-control ml-3" v-model="passengers[index - 1].tipdoc" :disabled="detailPassengers.estado > 0">
                                                <option value="">{{ trans('board.label.document_type_passenger') }}</option>
                                                <option v-bind:value="doctype.iso" v-for="doctype in doctypes">@{{ doctype.translations[0].value }}</option>
                                            </select>
                                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.document_number_passenger') }}" v-model="passengers[index - 1].nrodoc" :disabled="detailPassengers.estado > 0">
                                            <select class="form-control ml-3" v-model="passengers[index - 1].nacion" :disabled="detailPassengers.estado > 0">
                                                <option value="">{{ trans('board.label.country_of_document_passenger') }}</option>
                                                <option v-bind:value="country.iso" v-for="country in countries">@{{ country.translations[0].value }}</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                    <form class="form-inline">
                                        <div class="form-group information">
                                            <input type="text" class="form-control email ml-3" placeholder="{{ trans('board.label.email_passenger') }}" v-model="passengers[index - 1].correo" :disabled="detailPassengers.estado > 0">
                                            <input type="text" class="form-control ml-3" placeholder="{{ trans('board.label.phone_passenger') }}" v-model="passengers[index - 1].celula" :disabled="detailPassengers.estado > 0">
                                            <!-- input type="text" class="form-control ml-3" placeholder="localizador" -->
                                        </div>
                                    </form>
                                </div>
                                <div class="col-12 d-flex justify-content-start pl-4 pt-4">
                                    <form action="form-inline">
                                        <div class="form-group">
                                            <textarea class="form-control txt-notas ml-3" rows="3" placeholder="{{ trans('board.label.passenger_notes') }}" v-model="passengers[index - 1].observ" :disabled="detailPassengers.estado > 0"></textarea>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!----------- End Modo Completo----------->
        <div v-if="show_passenger_save">
            <hr class="mt-5 mb-5">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary save" v-on:click="savePassengers()" :disabled="loadingModal || detailPassengers.estado > 0">{{ trans('board.btn.save') }}</button>
            </div>

            <div class="alert alert-warning mt-3 mb-3" v-if="loadingModal">
                <p class="mb-0">{{ trans('board.label.loading') }}</p>
            </div>
        </div>
    </div>
</div>
