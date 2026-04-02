<template>
  <div>
    <template v-if="type == 'package'">
      <div v-if="loadingModal" class="alert alert-warning">{{ translations.board.label.loading }}</div>
      <div v-if="msg_error" class="alert alert-danger">{{ msg_error }}</div>
      <div class="wrapper center-block" v-if="!loadingModal && !msg_error">
        <div class="panel-group" id="accordion_pax" role="tablist" aria-multiselectable="true">
          <div class="panel panel-default" v-for="index in totalPassengers">
            <div class="my-5">
              <div v-bind:class="['panel-heading', index == 1 ? 'active' : '']" role="tab" v-bind:id="'heading_' + index">
                <h4 class="panel-title">
                  <a role="button" data-toggle="collapse" v-bind:href="'#collapse_pax_' + index" aria-expanded="true" v-bind:aria-controls="'collapse_pax_' + index" data-parent="#accordion_pax">
                    {{ translations.board.label.passenger }} {{ index }} ({{ passengers[index - 1].tipo }})
                  </a>
                </h4>
              </div>
              <div v-bind:id="'collapse_pax_' + index" v-bind:class="['collapse', index == 1 ? 'show' : '']" role="tabpanel" v-bind:aria-labelledby="'heading_' + index" data-parent="#accordion_pax">
                <div class="panel-body">
                  <ValidationObserver ref="form" v-slot="{ invalid }">
                    <form class="mt-4 content-pax">
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.name_passenger }} <sup class="color-primary font-weight-bold">*</sup></label>
                          <ValidationProvider v-bind:name="translations.board.label.name_passenger + ' ' + index" immediate :rules="getRules(index)" v-slot="{ errors }">
                            <div style="width: 340px">
                              <input
                                type="text"
                                class="form-control"
                                v-bind:placeholder="translations.board.label.name_passenger"
                                v-model="passengers[index - 1].nombres"
                                :disabled="
                                  (passengers[index - 1].flag_nombres != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                  detailPassengers.estado > 0 ||
                                  passengers[index - 1].prohib == 1
                                "
                              />
                            </div>
                            <template v-if="errors">
                              <p class="ml-3 mt-3 text-danger">{{ errors[0] }}</p>
                            </template>
                          </ValidationProvider>
                        </div>
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.last_name_passenger }} <sup class="color-primary font-weight-bold">*</sup></label>
                          <ValidationProvider v-bind:name="translations.board.label.last_name_passenger + ' ' + index" immediate :rules="getRules(index)" v-slot="{ errors }">
                            <div style="width: 340px">
                              <input
                                type="text"
                                class="form-control"
                                v-bind:placeholder="translations.board.label.last_name_passenger"
                                v-model="passengers[index - 1].apellidos"
                                :disabled="
                                  (passengers[index - 1].flag_apellidos != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                  detailPassengers.estado > 0 ||
                                  passengers[index - 1].prohib == 1
                                "
                              />
                            </div>
                            <template v-if="errors">
                              <p class="ml-3 mt-3 text-danger">{{ errors[0] }}</p>
                            </template>
                          </ValidationProvider>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.date_of_birth }} - (<b>DD/MM/YYYY</b>)</label>
                          <div style="width: 340px">
                            <date-picker
                              v-bind:placeholder="translations.board.label.date_of_birth"
                              v-model="passengers[index - 1].fecnac"
                              :disabled="passengers[index - 1].flag_fecnac != undefined || detailPassengers.estado > 0"
                              :config="{ format: 'DD/MM/YYYY' , maxDate: maxDate }"
                              autocomplete="off"
                              class="form-control"
                            ></date-picker>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.gender_passenger }}</label>
                          <div style="width: 340px">
                            <select
                              class="form-control"
                              v-model="passengers[index - 1].sexo"
                              :disabled="
                                (passengers[index - 1].flag_sexo != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            >
                              <option value="">{{ translations.board.label.gender_passenger }}</option>
                              <option value="M">{{ translations.board.label.male }}</option>
                              <option value="F">{{ translations.board.label.female }}</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.email_passenger }}</label>
                          <div style="width: 340px">
                            <input
                              type="text"
                              class="form-control email"
                              v-bind:placeholder="translations.board.label.email_passenger"
                              v-model="passengers[index - 1].correo"
                              :disabled="
                                (passengers[index - 1].flag_correo != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            />
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.phone_passenger }} - (<b>51</b>954114787)</label>
                          <div style="width: 340px">
                            <input
                              type="number"
                              class="form-control"
                              v-bind:placeholder="translations.board.label.phone_passenger"
                              v-model="passengers[index - 1].celula"
                              :disabled="
                                (passengers[index - 1].flag_celula != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            />
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.document_type_passenger }}</label>
                          <div style="width: 340px">
                            <select
                              class="form-control"
                              v-model="passengers[index - 1].tipdoc"
                              :disabled="
                                (passengers[index - 1].flag_tipdoc != null && (user_login == null || user_login.user_type_id != 3)) || detailPassengers.estado > 0 || passengers[index - 1].prohib == 1
                              "
                            >
                              <option value="-1">{{ translations.board.label.document_type_passenger }}</option>
                              <option v-bind:value="doctype.iso" v-for="doctype in doctypes">{{ doctype.translations.length > 0 ? doctype.translations[0].value : "" }}</option>
                            </select>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <label for="" style="width: 200px">{{ translations.board.label.document_number_passenger }}</label>
                          <div style="width: 340px">
                            <input
                              type="text"
                              class="form-control"
                              v-bind:placeholder="translations.board.label.document_number_passenger"
                              v-model="passengers[index - 1].nrodoc"
                              :disabled="
                                (passengers[index - 1].flag_nrodoc != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            />
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-start">
                          <label for="" style="width: 200px">{{ translations.board.label.medical_restrictions }}</label>
                          <div style="width: 340px">
                            <textarea
                              class="form-control txt-notas medical_restrictions"
                              rows="3"
                              v-bind:placeholder="translations.board.label.medical_restrictions"
                              v-model="passengers[index - 1].resmed"
                              :disabled="
                                (passengers[index - 1].flag_resmed != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            ></textarea>
                          </div>
                        </div>
                        <div class="d-flex align-items-start">
                          <label for="" style="width: 200px">{{ translations.board.label.dietary_restrictions }}</label>
                          <div style="width: 340px">
                            <textarea
                              class="form-control txt-notas dietary_restrictions"
                              rows="3"
                              v-bind:placeholder="translations.board.label.dietary_restrictions"
                              v-model="passengers[index - 1].resali"
                              :disabled="
                                (passengers[index - 1].flag_resali != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            ></textarea>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center mt-2 mb-5">
                        <div class="d-flex align-items-start">
                          <label for="" style="width: 200px">{{ translations.board.label.passenger_notes }}</label>
                          <div style="width: 340px">
                            <textarea
                              class="form-control txt-notas"
                              rows="3"
                              v-bind:placeholder="translations.board.label.passenger_notes"
                              v-model="passengers[index - 1].observ"
                              :disabled="
                                (passengers[index - 1].flag_observ != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                detailPassengers.estado > 0 ||
                                passengers[index - 1].prohib == 1
                              "
                            ></textarea>
                          </div>
                        </div>
                      </div>
                    </form>
                  </ValidationObserver>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <div v-bind:class="[open_modal ? 'modal' : 'd-block pb-3', 'modal--cotizacion']" id="modal-pax" tabindex="-1" role="dialog" v-bind:style="open_modal ? 'overflow: scroll;' : ''">
        <div class="modal-dialog modal--cotizacion__document" v-bind:style="open_modal ? '' : 'pointer-events:all!important;'" role="document">
          <div v-bind:class="[open_modal ? 'modal-content' : 'p-0', 'modal--cotizacion__content']">
            <div class="modal-header" v-if="open_modal">
              <button class="close" type="button" v-on:click="closeModal('modal-pax', true)" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body" v-bind:style="open_modal ? '' : 'padding:0!important;'">
              <div class="modal--cotizacion__header">
                <h3 class="modal-title">
                  <b>{{ translations.board.label.passenger_data }}</b>
                </h3>
              </div>
              <div class="modal--cotizacion__body">
                <div class="d-block">
                  <ValidationObserver ref="form" v-slot="{ invalid }">
                    <b-form>
                      <div class="container-fluid p-0">
                        <template v-if="loadingModal && !msg_error">
                          <div class="alert alert-warning">{{ translations.board.label.loading }}</div>
                        </template>

                        <template v-if="msg_error">
                          <div class="alert alert-danger">{{ msg_error }}</div>
                        </template>

                        <template v-if="!loadingModal && msg_error == ''">
                          <template v-if="type == 'file'">
                            <div class="d-flex mb-5" v-if="nroFile != undefined && nroFile > 0">
                              <input readonly="readonly" class="form-control" type="text" v-bind:value="returnURL()" />
                              <button type="button" class="btn btn-danger" v-clipboard:copy="returnURL()">{{ translations.board.btn.copy }}</button>
                            </div>
                            <hr />
                          </template>
                          <div class="d-flex mt-2">
                            <p class="col p-0">
                              {{ translations.board.label.enter_the_requested_information }}: <strong> {{ detailPassengers.canadl }}</strong> {{ translations.board.label.adults }}
                              <span v-if="detailPassengers.canchd > 0"
                                ><strong> {{ detailPassengers.canchd }}</strong> {{ translations.board.label.kids }}</span
                              >
                              <span v-if="detailPassengers.caninf > 0"
                                ><strong> {{ detailPassengers.caninf }}</strong> {{ translations.board.label.infants }}</span
                              >
                            </p>
                            <template v-if="lock_list == 0">
                              <div class="col-auto" v-if="detailPassengers.estado == 0 && prohib == 0">
                                <form>
                                  <input type="file" ref="file" name="file" id="file" v-on:change="changeUpload()" style="display: none" />
                                  <label for="file" v-if="excel == ''"><i class="fas fa-upload"></i> {{ translations.board.label.load_list }}</label>
                                  <button class="btn btn-primary" v-if="excel != ''" type="button" v-on:click="importPassengers()">
                                    <i class="icon-upload"></i>{{ translations.board.label.process_list }}
                                  </button>
                                </form>
                              </div>
                              <div class="col-auto" v-if="detailPassengers.estado == 0">
                                <a href="/export_excel?type=passengers" v-if="prohib == 0" target="_blank">
                                  <i class="fas fa-download"></i>
                                  {{ translations.board.label.download_list }}</a
                                >
                                <a href="javascript:;" v-if="prohib == 1" v-on:click="showAlert('No se puede hacer carga masiva, 1 ó mas pasajeros ya con boletaje')">
                                  <i class="icon-download"></i>{{ translations.board.label.download_list }}</a
                                >
                              </div>
                            </template>
                          </div>

                          <!----------- Modo Basico ----------->
                          <div v-if="modePassenger == 1 && passengers.length > 1">
                            <div class="row">
                              <div class="mt-4 col-12 form-check form-check-inline pl-4">
                                <input class="form-check-input" type="checkbox" name="inlineOption" id="inline1" :disabled="detailPassengers.estado > 0" value="1" v-model="repeatPassenger" />
                                <label class="form-check-label" for="inline1">{{ translations.board.label.repeat_1st_passenger_data }}</label>
                              </div>
                            </div>

                            <div v-if="repeatPassenger == 1 && modePassenger == 1">
                              <div class="row">
                                <div class="mt-4 d-flex justify-content-start pl-4">
                                  <form class="form-inline">
                                    <div class="form-group information">
                                      <label class="pr-5"
                                        ><strong>{{ translations.board.label.passenger }}</strong></label
                                      >
                                      <input
                                        type="text"
                                        class="form-control name ml-5"
                                        v-bind:placeholder="translations.board.label.name_passenger"
                                        v-model="passengers[0].nombres"
                                        :disabled="detailPassengers.estado > 0 || passengers[0].prohib == 1"
                                      />
                                      <input
                                        type="text"
                                        class="form-control last-name ml-5"
                                        v-bind:placeholder="translations.board.label.last_name_passenger"
                                        v-model="passengers[0].apellidos"
                                        :disabled="detailPassengers.estado > 0 || passengers[0].prohib == 1"
                                      />
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
                              <div class="mt-4 col-6 d-flex justify-content-start pl-4" v-for="index in totalPassengers">
                                <div class="form-inline">
                                  <div class="form-group information-basic">
                                    <label class="pr-3">
                                      <strong>{{ translations.board.label.passenger }} {{ index }} ({{ passengers[index - 1].tipo }})</strong>
                                    </label>
                                    <input
                                      type="text"
                                      class="form-control"
                                      v-bind:placeholder="translations.board.label.name_passenger"
                                      v-model="passengers[index - 1].nombres"
                                      :disabled="
                                        (passengersO[index - 1].nombres != '' && (user_login == null || user_login.user_type_id != 3)) ||
                                        detailPassengers.estado > 0 ||
                                        passengers[index - 1].prohib == 1
                                      "
                                    />
                                    <input
                                      type="text"
                                      class="form-control"
                                      v-bind:placeholder="translations.board.label.last_name_passenger"
                                      v-model="passengers[index - 1].apellidos"
                                      :disabled="
                                        (passengersO[index - 1].apellidos != '' && (user_login == null || user_login.user_type_id != 3)) ||
                                        detailPassengers.estado > 0 ||
                                        passengers[index - 1].prohib == 1
                                      "
                                    />
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!----------- End Modo Basico 10personas ----------->

                          <!----------- Modo completo ----------->
                          <div v-if="modePassenger == 2">
                            <div class="mt-4">
                              <div id="accordion_pax" role="tablist">
                                <div class="card mb-3" v-for="index in totalPassengers">
                                  <button
                                    type="button"
                                    class="b-left"
                                    data-toggle="collapse"
                                    v-bind:href="'#collapse_pax_' + index"
                                    aria-expanded="true"
                                    v-bind:aria-controls="'collapse_pax_' + index"
                                  >
                                    <div class="card-header d-flex" role="tab" v-bind:id="'heading_' + index">
                                      {{ translations.board.label.passenger }} {{ index }} ({{ passengers[index - 1].tipo }}): {{ passengers[index - 1].nombres }}
                                      {{ passengers[index - 1].apellidos }} - {{ showTypeRoom(passengers[index - 1].tiphab) }}
                                    </div>
                                  </button>
                                  <div
                                    v-bind:id="'collapse_pax_' + index"
                                    v-bind:class="['collapse', index == 1 ? 'show' : '']"
                                    role="tabpanel"
                                    v-bind:aria-labelledby="'heading_' + index"
                                    data-parent="#accordion_pax"
                                  >
                                    <div class="card-body information-complete">
                                      <div class="col-12 pl-4 pt-4" v-if="index === 1 && user_type_id == 3 && type == 'quote' && client_allow_direct_passenger_create == 1">
                                        <b-form-checkbox v-model="passengers[index - 1].is_direct_client" name="check-button" switch>
                                          {{ translations.board.label.add_direct_customer }}
                                        </b-form-checkbox>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.name_passenger }}
                                            </label>
                                            <ValidationProvider :name="(translations.board.label.name_passenger ? translations.board.label.name_passenger.toLowerCase() : '') + ' ' + index" immediate :rules="getRules(index)" v-slot="{ errors }">
                                              <input
                                                type="text"
                                                class="form-control"
                                                v-bind:placeholder="translations.board.label.name_passenger"
                                                v-model="passengers[index - 1].nombres"
                                                :disabled="
                                                  (passengers[index - 1].flag_nombres != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                  detailPassengers.estado > 0 ||
                                                  passengers[index - 1].prohib == 1
                                                "
                                              />
                                              <template v-if="errors">
                                                <div class="pt-2 pb-5">
                                                  <span class="text-danger position-absolute">{{ errors[0] }}</span>
                                                </div>
                                              </template>
                                            </ValidationProvider>
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.last_name_passenger }}
                                            </label>
                                            <ValidationProvider :name="(translations.board.label.last_name_passenger ? translations.board.label.last_name_passenger.toLowerCase() : '') + ' ' + index" immediate :rules="getRules(index)" v-slot="{ errors }">
                                              <input
                                                type="text"
                                                class="form-control"
                                                v-bind:placeholder="translations.board.label.last_name_passenger"
                                                v-model="passengers[index - 1].apellidos"
                                                :disabled="
                                                  (passengers[index - 1].flag_apellidos != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                  detailPassengers.estado > 0 ||
                                                  passengers[index - 1].prohib == 1
                                                "
                                              />
                                              <template v-if="errors">
                                                <div class="pt-2 pb-5">
                                                  <span class="text-danger position-absolute">{{ errors[0] }}</span>
                                                </div>
                                              </template>
                                            </ValidationProvider>
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.gender_passenger }}
                                            </label>
                                            <select
                                              class="form-control"
                                              v-model="passengers[index - 1].sexo"
                                              :disabled="
                                                (passengers[index - 1].flag_sexo != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            >
                                              <option value="">{{ translations.board.label.gender_passenger }}</option>
                                              <option value="M">{{ translations.board.label.male }}</option>
                                              <option value="F">{{ translations.board.label.female }}</option>
                                            </select>
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.type_of_room }}
                                            </label>
                                            <select
                                              class="form-control"
                                              v-model="passengers[index - 1].tiphab"
                                              :disabled="
                                                (passengers[index - 1].flag_tiphab != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            >
                                              <option value="-1"></option>
                                              <option v-for="(type, t) in types_room" v-if="t > 0" v-bind:value="t">{{ type }}</option>
                                            </select>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.date_of_birth }}
                                            </label>
                                            <date-picker
                                              v-bind:placeholder="translations.board.label.date_of_birth"
                                              v-model="passengers[index - 1].fecnac"
                                              :disabled="passengers[index - 1].flag_fecnac != undefined || detailPassengers.estado > 0"
                                              :config="{ format: 'DD/MM/YYYY', maxDate: maxDate }"
                                              autocomplete="off"
                                              data-toggle="tooltip"
                                              title="DD/MM/YYYY"
                                              class="form-control"
                                            ></date-picker>
                                            <small class="badge badge-info mt-1">Format: <b>DD/MM/YYYY</b></small>
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;"
                                            >
                                              {{ translations.board.label.document_type_passenger }}
                                            </label>
                                             <select
                                              class="form-control"
                                              v-model="passengers[index - 1].tipdoc"
                                              :disabled="
                                                (passengers[index - 1].flag_tipdoc != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            >
                                              <option value="-1"></option>
                                              <option v-bind:value="doctype.iso" v-for="doctype in doctypes">
                                                {{ doctype.translations.length > 0 ? doctype.translations[0].value : "" }}
                                              </option>
                                            </select>
                                          </div>
                                          <div class="col-auto">
                                            <label class="position-absolute small" style="top: -10px; left: 10px; background: white; padding: 0px 5px; color: rgb(108, 117, 125); z-index: 10;">
                                            {{ translations.board.label.document_number_passenger }}
                                          </label>
                                            <input
                                              type="text"
                                              class="form-control"
                                              v-bind:placeholder="translations.board.label.document_number_passenger"
                                              v-model="passengers[index - 1].nrodoc"
                                              :disabled="
                                                (passengers[index - 1].flag_nrodoc != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            />
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;z-index: 10;"
                                            >
                                              {{ translations.board.label.country_of_document_passenger }}
                                            </label>
                                            <ValidationProvider :name="(translations.board.label.country_of_document_passenger ? translations.board.label.country_of_document_passenger.toLowerCase() : '') + ' ' + index" immediate :rules="getRulesCountry(index)" v-slot="{ errors }">
                                              <v-select
                                                label="label_country"
                                                code="iso"
                                                :reduce="(country) => country.iso"
                                                :options="countries[index - 1]"
                                                @input="searchCitiesByCountry(passengers[index - 1].nacion, index - 1)"
                                                v-model="passengers[index - 1].nacion"
                                                class="form-control"
                                                :filterable="true"
                                                v-bind:disabled="
                                                  (passengers[index - 1].flag_nacion != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                  detailPassengers.estado > 0 ||
                                                  passengers[index - 1].prohib == 1
                                                "
                                              >
                                                <template slot="option" slot-scope="option">
                                                  <div class="d-center">
                                                    <template v-if="option.translations != undefined">
                                                      {{ option.translations.length > 0 ? option.translations[0].value : "" }}
                                                    </template>
                                                  </div>
                                                </template>
                                                <template slot="selected-option" slot-scope="option">
                                                  <div class="selected d-center">
                                                    <template v-if="option.translations != undefined">
                                                      {{ option.translations.length > 0 ? option.translations[0].value : "" }}
                                                    </template>
                                                  </div>
                                                </template>
                                              </v-select>
                                              <template v-if="errors">
                                                <div class="pt-2 pb-5">
                                                  <span class="text-danger position-absolute">{{ errors[0] }}</span>
                                                </div>
                                              </template>
                                            </ValidationProvider>
                                          </div>
                                        </div>
                                      </div>

                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col-auto" v-if="passengers[index - 1].is_direct_client">
                                            <v-select
                                              label="name"
                                              code="iso"
                                              :reduce="(city) => city.iso"
                                              :options="cities[index - 1]"
                                              v-model="passengers[index - 1].city_ifx_iso"
                                              v-bind:placeholder="translations.board.label.city"
                                              class="form-control"
                                              :filterable="true"
                                              v-bind:disabled="
                                                (passengers[index - 1].flag_city_ifx_iso != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            >
                                              <template slot="option" slot-scope="option">
                                                <div class="d-center">
                                                  {{ option.name }}
                                                </div>
                                              </template>
                                              <template slot="selected-option" slot-scope="option">
                                                <div class="selected d-center">
                                                  {{ option.name }}
                                                </div>
                                              </template>
                                            </v-select>
                                          </div>
                                          <div class="col-auto">
                                            <label class="position-absolute small" style="top: -10px; left: 10px; background: white; padding: 0px 5px; color: rgb(108, 117, 125); z-index: 10;">
                                              {{ translations.board.label.email_passenger }}
                                          </label>
                                            <ValidationProvider v-bind:name="'correo ' + index" rules="email2" v-slot="{ errors }">
                                              <div>
                                                <input
                                                  type="text"
                                                  class="form-control"
                                                  v-bind:placeholder="translations.board.label.email_passenger"
                                                  v-model="passengers[index - 1].correo"
                                                  :disabled="
                                                    (passengers[index - 1].flag_correo != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                    detailPassengers.estado > 0 ||
                                                    passengers[index - 1].prohib == 1
                                                  "
                                                />
                                                <p class="ml-3 mt-3">{{ errors[0] }}</p>
                                              </div>
                                            </ValidationProvider>
                                          </div>
                                          <div class="col-auto">
                                            <label
                                              class="position-absolute small"
                                              style="top: -10px; left: 10px; background: white; padding: 0 5px; color: #6c757d;z-index: 10;"
                                            >
                                              {{ translations.board.label.phone_code_passenger }}
                                            </label>
                                            <v-select
                                              code="code"
                                              :options="phones[index - 1]"
                                              :reduce="(phone) => phone.code"
                                              label="label"
                                              v-model="passengers[index - 1].phone_code"
                                              class="form-control"
                                              :disabled="
                                                (passengers[index - 1].flag_celula != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                              autocomplete="true"
                                            >
                                            </v-select>
                                          </div>
                                          <div class="col-auto">
                                            <div>
                                              <label class="position-absolute small" style="top: -10px; left: 10px; background: white; padding: 0px 5px; color: rgb(108, 117, 125); z-index: 10;">
                                            {{ translations.board.label.phone_passenger }}
                                          </label>
                                              <input
                                                type="number"
                                                class="form-control"
                                                v-bind:placeholder="translations.board.label.phone_passenger"
                                                v-model="passengers[index - 1].celula"
                                                :disabled="
                                                  (passengers[index - 1].flag_celula != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                  detailPassengers.estado > 0 ||
                                                  passengers[index - 1].prohib == 1
                                                "
                                              />
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block" v-if="index === 1 && user_type_id == 3 && type == 'quote' && client_allow_direct_passenger_create == 1">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col">
                                            <textarea
                                              class="form-control w-100 medical_restrictions"
                                              rows="3"
                                              v-bind:placeholder="translations.board.label.address"
                                              :disabled="
                                                (passengers[index - 1].flag_address != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                              v-model="passengers[index - 1].address"
                                            ></textarea>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col">
                                            <file-single-upload
                                              v-bind:passenger_index="index - 1"
                                              v-bind:folder_aws="'passengers'"
                                              v-bind:document="passengers[index - 1].document_url"
                                              @onResponseFiles="(value) => responseFilesFrom(value, index - 1)"
                                            />
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col">
                                            <textarea
                                              class="form-control w-100 medical_restrictions"
                                              rows="3"
                                              v-bind:placeholder="translations.board.label.medical_restrictions"
                                              v-model="passengers[index - 1].resmed"
                                              :disabled="
                                                (passengers[index - 1].flag_resmed != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            ></textarea>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col">
                                            <textarea
                                              class="form-control w-100 dietary_restrictions"
                                              rows="3"
                                              v-bind:placeholder="translations.board.label.dietary_restrictions"
                                              v-model="passengers[index - 1].resali"
                                              :disabled="
                                                (passengers[index - 1].flag_resali != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            ></textarea>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="d-block">
                                        <div class="d-flex justify-content-start pt-4">
                                          <div class="col">
                                            <textarea
                                              class="form-control w-100"
                                              rows="3"
                                              v-bind:placeholder="translations.board.label.passenger_notes"
                                              v-model="passengers[index - 1].observ"
                                              :disabled="
                                                (passengers[index - 1].flag_observ != undefined && (user_login == null || user_login.user_type_id != 3)) ||
                                                detailPassengers.estado > 0 ||
                                                passengers[index - 1].prohib == 1
                                              "
                                            ></textarea>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!----------- End Modo Completo----------->

                          <div class="mt-4" v-if="show_passenger_save">
                            <div class="d-flex justify-content-end">
                              <b-button type="button" class="btn btn-primary" @click.self="showModal('modalAlertaPaxs')" :disabled="invalid || loadingModal || detailPassengers.estado > 0">
                                {{ translations.board.btn.save }}
                              </b-button>
                            </div>
                          </div>
                        </template>
                      </div>
                    </b-form>
                  </ValidationObserver>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- MODAL -->
    <div id="modalAlertaPaxs" v-if="modal" tabindex="1" role="dialog" class="modal">
      <div role="document" class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body">
            <h4 class="text-center">
              <div class="icon">
                <i class="icon-alert-circle" v-if="!loadingModal"></i>
                <i class="spinner-grow" v-if="loadingModal"></i>
              </div>
              <strong v-if="!loadingModal">{{ translations.board.label.sure_save }}</strong>
              <strong v-if="loadingModal">{{ translations.board.label.loading }}</strong>
            </h4>
            <p class="text-center" v-if="!loadingModal">
              <strong>{{ translations.board.label.confirm_save }}</strong>
            </p>
            <div class="group-btn" v-if="!loadingModal">
              <button type="button" @click="savePassengers()" data-dismiss="modal" class="btn btn-secondary">{{ translations.board.btn.save }}</button>
              <button type="button" @click="closeModal('modalAlertaPaxs')" data-dismiss="modal" class="btn btn-primary">{{ translations.board.btn.cancel }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- -->
  </div>
</template>

<script>
import { required, max } from "vee-validate/dist/rules";
import { extend, ValidationObserver, ValidationProvider } from "vee-validate";
import moment from "moment";

export default {
  components: { ValidationObserver, ValidationProvider },
  props: {
    'multi_region' : {
        type: Boolean,
        default: false
    }
  },
  data: () => {
    return {
      validarPrimeraIteracion: true,
      nroFile: 0,
      open_modal: true,
      lang: localStorage.getItem("lang"),
      translations: {
        board: {
          label: {},
          btn: {}
        },
        quote: {
          label: {},
          btn: {}
        },
        reservations: {
          global: {
            rule_required: null,
            rule_max32: null,
            rule_regex01: null
          }
        }
      },
      modePassenger: 2,
      repeatPassenger: 0,
      totalPassengers: [],
      show_passenger_save: true,
      passengers: [],
      passengersO: [],
      countries: [],
      phones: [],
      cities: [],
      doctypes: [],
      pending_passengers: false,
      detailPassengers: [],
      total_paxs: 0,
      canADL: 0,
      canCHD: 0,
      canINF: 0,
      loadingModal: false,
      msg_error: "",
      excel: "",
      prohib: 0,
      message_error: "",
      lock_list: 0,
      user_login: null,
      type: "file",
      types_room: ["", "SGL", "DBL", "TPL"],
      modal: true,
      user_type_id: localStorage.getItem("user_type_id"),
      user_id: localStorage.getItem("user_id"),
      client_allow_direct_passenger_create: false,
      maxDate: moment(new Date())
    };
  },
  computed: {
    messageRequired() {
      const rule = this.translations?.reservations?.global?.rule_required;
      return rule ? rule : "Campo requerido";
    },
    messageMax32() {
      const rule = this.translations?.reservations?.global?.rule_max32;
      return rule ? rule : "Máximo 32 caracteres";
    },
    messageRegex01() {
      const rule = this.translations?.reservations?.global?.rule_regex01;
      return rule ? rule : "Formato inválido";
    },
    isCountrySelected() {
      return true;
    }
  },
  async created() {
    await this.setTranslations();
    await this.searchDoctypes();

    // Reglas cuando es formulario en pagina blade
    extend("required", {
      ...required,
      message: this.messageRequired
    });
    extend("max", {
      ...max,
      message: this.messageMax32
    });
    extend("regex", {
      validate: (value) => /^[a-zA-Z0-9-_ \s]*$/.test(value),
      message: this.messageRegex01
    });
    extend('email2', {
      validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
      message: 'The email must be valid'
    });
    extend('required_country', {
      validate: (value) => {
        return value !== null && value !== undefined && value !== '' && value !== '-1';
      },
      message: this.messageRequired
    });
  },
  mounted() {
    this.client_allow_direct_passenger_create = localStorage.getItem("client_allow_direct_passenger_create");
    extend("required", {
      ...required,
      message: this.messageRequired
    });
    extend("max", {
      ...max,
      message: this.messageMax32
    });
    extend("regex", {
      validate: (value) => /^[a-zA-Z0-9\s]*$/.test(value),
      message: this.messageRegex01
    });
    extend('email2', {
      validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
      message: 'The email must be valid'
    });
    extend('required_country', {
      validate: (value) => {
        return value !== null && value !== undefined && value !== '' && value !== '-1';
      },
      message: this.messageRequired
    });


  },
  watch: {
  passengers(newVal) {
    const allowedClientIds = [17396, 17003, 17008];
    const clientId = parseInt(localStorage.getItem("client_id"), 10);

    if (allowedClientIds.includes(clientId) && newVal.length >= 1) {
      this.passengers[0].is_direct_client = true;
      }
  }
},
  methods: {
    getRules(index) {
      if (index === 1 && this.validarPrimeraIteracion) {
        return "required";
      } else {
        return "";
      }
    },
    getRulesCountry(index) {
      return ""
    },
    responseFilesFrom: function (value, index) {
      let vm = this;
      vm.$set(vm.passengers[index], "document_url", value);

      console.log(vm.passengers[index]);
    },
    returnURL: function () {
      return (
        baseURL +
        "register_paxs/" +
        this.nroFile +
        "?lang=" +
        this.lang +
        "&paxs=" +
        this.total_paxs +
        "&canadl=" +
        this.detailPassengers.canadl +
        "&canchd=" +
        this.detailPassengers.canchd +
        "&caninf=" +
        this.detailPassengers.caninf
      );
    },
    showTypeRoom: function (_tiphab) {
      return _tiphab != null && _tiphab != "" ? this.types_room[_tiphab] : "";
    },
    doCopy: function () {
      let _message = this.returnURL();
      this.$copyText(_message).then(
        function (e) {
          alert("Copiado!");
          console.log(e);
        },
        function (e) {
          alert("No se pudo copiar..");
          console.log(e);
        }
      );
    },
    showModal: function (_modal) {
      this.client_allow_direct_passenger_create = localStorage.getItem("client_allow_direct_passenger_create");
      this.modal = true;

      setTimeout(function () {
        $("#" + _modal).modal("show");
      }, 10);
    },
    closeModal: function (_modal, _close) {
      let vm = this;
      $("#" + _modal).modal("hide");

      if (_close == true) {
        setTimeout(function () {
          vm.modal = false;
        }, 1000);
      }
    },
    async setTranslations() {
      try {
        await axios.get(baseURL + "translation/" + this.lang + "/slug/reservations").then((data) => {
          if (data && data.data) {
            this.translations.reservations = data.data;
          }
        });
      } catch (e) {
        console.warn("Could not load translations for reservations", e);
      }
      try {
        await axios.get(baseURL + "translation/" + this.lang + "/slug/board").then((data) => {
          if (data && data.data) {
            this.translations.board = data.data;
          }
        });
      } catch (e) {
        console.warn("Could not load translations for board", e);
      }
      try {
        axios.get(baseURL + "translation/" + this.lang + "/slug/quote").then((data) => {
          if (data && data.data) {
            this.translations.quote = data.data;
          }
        });
      } catch (e) {
        console.warn("Could not load translations for quote", e);
      }
    },
    showAlert: function (_message) {
      this.$toast.error(_message, {
        // override the global option
        position: "top-right"
      });
      return false;
    },
    async searchCountries() {
      let vm = this;

      await axios
        .post(baseURL + "board/all_countries_iso", {
          lang: vm.lang
        })
        .then((result) => {
          let countries = result.data.countries;

          let phones = countries.map((item) => {
            return {
              label: item.translations[0].value + " (+" + item.phone_code + ")",
              code: item.phone_code
            };
          });

          vm.passengers.forEach((item, i) => {
            vm.$set(vm.countries, i, result.data.countries);
            vm.$set(vm.phones, i, phones);
          });
          // vm.$set(vm.countries, index, result.data.countries)
        })
        .catch((result) => {});
    },
    async searchDoctypes() {
      await axios
        .post(baseURL + "board/all_doctypes", {
          lang: this.lang
        })
        .then((result) => {
          this.doctypes = result.data.doctypes;
        })
        .catch((result) => {});
    },
    validateChildQuote: function () {
      let response = true;
      this.passengers.forEach((pax, p) => {
        if (pax.tipo === "CHD" && pax.fecnac === "") {
          response = false;
        }
      });

      return response;
    },
    async validateChilds() {
      let response = true;
      this.passengers.forEach((pax, p) => {
        console.log(pax);
        if (pax.tipo == "CHD" && pax.fecnac == "") {
          response = false;
        }
      });

      return response;
    },
    validatePassports: function () {
      let response = true;
      this.passengers.forEach((pax, p) => {
        if (response) {
          this.passengers.forEach((item, i) => {
            //console.log("Cadena de consulta:", pax.nrodoc);
            //console.log("Cadena de validación: ", item.pax);

            if (response) {
              if (pax.nrodoc === item.nrodoc && p != i && pax.nrodoc != "" && pax.nrodoc != "-1" && pax.nrodoc != null && pax.nrodoc != undefined) {
                response = false;
              }
            }
          });
        }
      });

      return response;
    },
    async validatePassengers() {
        let vm = this;
        const form = (vm.$refs.form && vm.$refs.form[0]) ? vm.$refs.form[0] : null;

        if(form)
        {
            const result = await form.validate();

            if (result) {
                if (this.canCHD > 0) {
                    const result_childs = await vm.validateChilds();

                    if (!result_childs) {
                        vm.$toast.error("Se necesita la fecha de nacimiento de los niños como información requerida. Por favor, intente nuevamente", {
                            // override the global option
                            position: "top-right"
                        });
                    }

                    return result_childs;
                }
            }
            else
            {
                this.$toast.error(this.translations.quote.messages.empty_passenger, {
                    position: 'top-right'
                });
            }

            return result;
        }
        else
        {
            this.$toast.error(this.translations.quote.messages.empty_passenger, {
                position: 'top-right'
            });

            return false;
        }
    },
    async validatePassengersQuote() {
      let vm = this;
      const result = await vm.$refs.form.validate();
      console.log(result);
      if (result == true) {
        console.log(this.detailPassengers.canchd);
        if (this.detailPassengers.canchd > 0) {
          const result_childs = await vm.validateChilds();
          console.log(result_childs);
          if (result_childs == true) {
            vm.$parent.reserveQuote();
          } else {
            vm.$toast.error("Se necesita la fecha de nacimiento de los niños como información requerida. Por favor, intente nuevamente", {
              // override the global option
              position: "top-right"
            });
          }
        } else {
          vm.$parent.reserveQuote();
        }
      } else {
        console.log("Error validacion de pasajeros: " + result);
      }
    },
    getError: function () {
      return this.msg_error;
    },
    getPassengers: function () {
      let vm = this;
      return vm.passengers;
    },
    async modalPassengers(_type, nrofile, paxs, adl, chd, inf, modal) {
        //console.log("modalPassengers", _type, nrofile, paxs, adl, chd, inf, modal);
      if (localStorage.getItem("modal_aurora_paxs") === "false") {
        localStorage.removeItem("modal_aurora_paxs");
        this.open_modal = false;
      }

      adl = adl > 0 ? adl : 0;
      chd = chd > 0 ? chd : 0;
      inf = inf > 0 ? inf : 0;
      // -- RESET
      this.type = _type;
      this.total_paxs = paxs;
      this.passengers = [];
      this.repeatPassenger = 0;
      this.totalPassengers = [];
      this.cities = [];
      this.modePassenger = 2;
      // -- RESET
      this.pending_passengers = true;
      this.nroFile = nrofile;
      this.canADL = 0;
      this.canCHD = 0;
      this.canINF = 0;
      this.loadingModal = true;
      this.msg_error = "";
      this.message_error = "";
      for (let i = 1; i <= this.total_paxs; i++) {
        this.cities[i - 1] = [];
      }
      if (_type != "package") {
        if (modal == undefined || modal == true) {
          if (this.open_modal) {
            this.showModal("modal-pax");
          }
        }
      } else {
        this.show_passenger_save = false;
      }

      try {
        let result = null;
        const payload = {
          file: this.nroFile !== null && this.nroFile !== "" ? this.nroFile : 0,
          type: this.type,
          quote: localStorage.getItem("save_quote_file")
        };

        const tryPost = async (url) => {
          if (!url) return null;
          try {
            return await axios.post(url, payload);
          } catch (e) {
            console.warn("search_passengers failed:", url, e);
            return null;
          }
        };

        // baseExternalURL + api/search_passengers
        if (this.type == "file" || this.type == "package" || this.type == "quote") {
          const externalBase = (typeof baseExternalURL !== "undefined") ? baseExternalURL : "";
          const internalBase = (typeof baseURL !== "undefined") ? baseURL : "";

          result = await tryPost(externalBase ? (externalBase + "api/search_passengers") : "");
          if (!result || !result.data || result.data.type !== "success") {
            result = await tryPost(internalBase ? (internalBase + "search_passengers") : "search_passengers");
          }
        } else {
          const internalBase = (typeof baseURL !== "undefined") ? baseURL : "";
          result = await tryPost(internalBase ? (internalBase + "search_passengers") : "search_passengers");
        }

        if (result && result.data && result.data.type === "success") {
            localStorage.removeItem("save_quote_file");
            if (
              result.data.detail &&
              result.data.detail.success &&
              !result.data.detail.process &&
              result.data.detail.message == "File data not found"
            ) {
              const hasFile = this.nroFile !== null && this.nroFile !== undefined && this.nroFile !== "";
              if (this.type === "file" && hasFile) {
                this.$toast.error(
                  (this.translations.quote && this.translations.quote.label && this.translations.quote.label.file_not_exist)
                    ? this.translations.quote.label.file_not_exist
                    : "El file no existe",
                  { position: "top-right" }
                );
              }
            }
            let totalPax = [];
            let _passengers = result.data.passengers || [];
            this.user_login = result.data.user || null;
            this.lock_list = result.data.lock_list || 0;
            this.prohib = result.data.prohib || 0;
            this.message_error = result.data.mensaje || "";
            this.repeatPassenger = result.data.repeat_passenger || 0;

            if (this.type == "file" || this.type == 'package') {
              this.detailPassengers = result.data.detail || {
                  canadl: adl,
                  canchd: chd,
                  caninf: inf,
                  estado: 0
              };

              let _adl_api = (this.detailPassengers && this.detailPassengers.canadl > 0) ? parseFloat(this.detailPassengers.canadl) : 0;
              let _chd_api = (this.detailPassengers && this.detailPassengers.canchd > 0) ? parseFloat(this.detailPassengers.canchd) : 0;
              let _inf_api = (this.detailPassengers && this.detailPassengers.caninf > 0) ? parseFloat(this.detailPassengers.caninf) : 0;

              if (adl > _adl_api) {
                this.detailPassengers.canadl = adl;
              }

              if (chd > _chd_api) {
                this.detailPassengers.canchd = chd;
              }

              if (inf > _inf_api) {
                this.detailPassengers.caninf = inf;
              }

              if (modal == false) {
                let _passenger = _passengers.length > 0 ? _passengers[0] : {};

                if ((_passenger.nombres == undefined || _passenger.nombres == "") && (_passenger.apellidos == undefined || _passenger.apellidos == "")) {
                  if (this.$parent.closeBloqued != undefined && this.$parent.closeBloqued() != undefined && typeof this.$parent.closeBloqued == "function") {
                    this.$parent.closeBloqued();
                  }
                }
              }
            } else {
              this.detailPassengers = {
                canadl: parseFloat(adl),
                canchd: parseFloat(chd),
                caninf: parseFloat(inf),
                estado: 0
              };
            }

            let vm = this;

            let _adl = (vm.detailPassengers && vm.detailPassengers.canadl > 0) ? parseFloat(vm.detailPassengers.canadl) : 0;
            let _chd = (vm.detailPassengers && vm.detailPassengers.canchd > 0) ? parseFloat(vm.detailPassengers.canchd) : 0;
            let _inf = (vm.detailPassengers && vm.detailPassengers.caninf > 0) ? parseFloat(vm.detailPassengers.caninf) : 0;

            let total_final = parseFloat(parseFloat(_adl) + parseFloat(_chd) + parseFloat(_inf));
            vm.total_paxs = isNaN(total_final) ? paxs : total_final;

            if (vm.total_paxs == 0 && paxs > 0) {
                vm.total_paxs = paxs;
            }

            for (let i = 1; i <= vm.total_paxs; i++) {
              totalPax.push(i);
              if (_passengers[i - 1] == undefined) {
                _passengers.push(this.fillPassenger(i - 1));
              }
            }
            this.passengersO = _passengers;
            this.passengers = _passengers;

            try {
              const loadResult = await axios.post(baseURL + "load_passengers", {
                file: this.nroFile,
                passengers: _passengers
              });

              this.loadingModal = false;
              if (vm.passengers.length > 0) {
                await this.searchCountries();
              }

              for (let e = 0; e < vm.passengers.length; e++) {
                  let element = vm.passengers[e];
                  vm.$set(vm.cities, e, []);
                  let phone_code = element.phone_code;
                  if (element.nacion) {
                    await vm.searchCitiesByCountry(element.nacion, e, element.city_ifx_iso);
                  }

                  if (phone_code !== null && phone_code !== undefined && phone_code !== "") {
                      vm.$set(vm.passengers[e], "phone_code", phone_code.toString());
                  }

                  Object.entries(element).forEach(function (el) {
                    let value = el[1];
                    let field = el[0];

                    if (field == "sexo" || field == "tipdoc" || field == "tiphab") {
                      if (value == "" || value == null || value == "-1") {
                        vm.$set(vm.passengers[e], field, "");
                        vm.$set(vm.passengersO[e], field, "");
                      }
                    }
                    if (field == "city_ifx_iso" || field == "nacion" || field == "nrodoc") {
                      if (value == "" || value == null) {
                        vm.$set(vm.passengers[e], field, "");
                        vm.$set(vm.passengersO[e], field, "");
                      }
                    }

                    if (value != null && value != "" && value != "-1") {
                      value = typeof value == "string" ? value.trim() : value;
                    }

                    if (vm.type == "file") {
                      vm.$set(vm.passengers[e], "flag_" + field, undefined);
                    } else {
                      vm.$set(vm.passengers[e], "flag_" + field, undefined);
                    }
                  });

                  if (!vm.passengers[e].tipo) {
                    let _tipo = "INF";
                    if (e < vm.detailPassengers.canchd + vm.detailPassengers.canadl) { _tipo = "CHD"; }
                    if (e < vm.detailPassengers.canadl) { _tipo = "ADL"; }
                    vm.$set(vm.passengers[e], "tipo", _tipo);
                  }
              }

              if (!this.detailPassengers.canadl) { this.detailPassengers.canadl = adl || 0; }
              if (!this.detailPassengers.canchd) { this.detailPassengers.canchd = chd || 0; }
              if (!this.detailPassengers.caninf) { this.detailPassengers.caninf = inf || 0; }

              setTimeout(() => {
                vm.totalPassengers = [];
                for (let i = 1; i <= vm.total_paxs; i++) {
                  vm.totalPassengers.push(i);
                }
              }, 10);
            } catch (loadError) {
              console.log(loadError);
              this.loadingModal = false;
            }
          } else {
            this.loadingModal = false;
            console.log("[Debug] Respuesta API Passengers:", result ? result.data : "Sin respuesta");

            let serverMessage = (result && result.data && (result.data.mensaje || result.data.message)) ? result.data.mensaje || result.data.message : null;

            if (this.type == "file") {
              this.msg_error = serverMessage || "El file no es válido. Por favor, intente nuevamente.";
            } else {
              this.msg_error = serverMessage || "Ocurrió un problema al obtener los datos del servidor. Por favor, intente nuevamente.";
            }
          }
        } catch (e) {
          this.msg_error = "Ocurrió un problema al listar los pasajeros. Por favor, actualice la página y vuelva a intentarlo.";
          console.log(e);
          this.loadingModal = false;
        }
    },
    fillPassenger: function (passenger) {
      let _ignore = 0;
      let _tipo = "";
      let vm = this;

      if (this.detailPassengers.canadl > this.canADL && _ignore == 0) {
        _tipo = "ADL";
        this.canADL += 1;
        _ignore = 1;
      }

      if (this.detailPassengers.canchd > this.canCHD && _ignore == 0) {
        _tipo = "CHD";
        this.canCHD += 1;
        _ignore = 1;
      }

      if (this.detailPassengers.caninf > this.canINF && _ignore == 0) {
        _tipo = "INF";
        this.canINF += 1;
      }

      return {
        tipo: _tipo,
        nrosec: 0,
        nroref: vm.nroFile,
        nombres: "",
        apellidos: "",
        sexo: "",
        tiphab: -1,
        fecnac: "",
        tipdoc: -1,
        nrodoc: "",
        nacion: "",
        city_ifx_iso: "",
        correo: "",
        phone_code: "",
        celula: "",
        resmed: "",
        resali: "",
        observ: "",
        document_url: ""
      };

      // eval('this.passengers[' + passenger + '].tipo = \'' + _tipo + '\'')
      // eval('this.passengers[' + passenger + '].nrosec = 0')
      // eval('this.passengers[' + passenger + '].nroref = \'' + vm.nroFile + '\'')
      // eval('this.passengers[' + passenger + '].nombres = \'\'')
      // eval('this.passengers[' + passenger + '].apellidos = \'\'')
      // eval('this.passengers[' + passenger + '].sexo = -1')
      // eval('this.passengers[' + passenger + '].tiphab = -1')
      // eval('this.passengers[' + passenger + '].fecnac = \'\'')
      // eval('this.passengers[' + passenger + '].tipdoc = -1')
      // eval('this.passengers[' + passenger + '].nrodoc = \'\'')
      // eval('this.passengers[' + passenger + '].nacion = \'\'')
      // eval('this.passengers[' + passenger + '].city_ifx_iso = \'\'')
      // eval('this.passengers[' + passenger + '].correo = \'\'')
      // eval('this.passengers[' + passenger + '].phone_code = \'\'')
      // eval('this.passengers[' + passenger + '].celula = \'\'')
      // eval('this.passengers[' + passenger + '].resmed = \'\'')
      // eval('this.passengers[' + passenger + '].resali = \'\'')
      // eval('this.passengers[' + passenger + '].observ = \'\'')

      // eval('this.passengersO[' + passenger + '].tipo = \'' + _tipo + '\'')
      // eval('this.passengersO[' + passenger + '].nrosec = 0')
      // eval('this.passengersO[' + passenger + '].nroref = \'' + vm.nroFile + '\'')
      // eval('this.passengersO[' + passenger + '].nombres = \'\'')
      // eval('this.passengersO[' + passenger + '].apellidos = \'\'')
      // eval('this.passengersO[' + passenger + '].sexo = -1')
      // eval('this.passengersO[' + passenger + '].tiphab = -1')
      // eval('this.passengersO[' + passenger + '].fecnac = \'\'')
      // eval('this.passengersO[' + passenger + '].tipdoc = -1')
      // eval('this.passengersO[' + passenger + '].nrodoc = \'\'')
      // eval('this.passengersO[' + passenger + '].nacion = \'\'')
      // eval('this.passengersO[' + passenger + '].city_ifx_iso = \'\'')
      // eval('this.passengersO[' + passenger + '].correo = \'\'')
      // eval('this.passengersO[' + passenger + '].phone_code = \'\'')
      // eval('this.passengersO[' + passenger + '].celula = \'\'')
      // eval('this.passengersO[' + passenger + '].resmed = \'\'')
      // eval('this.passengersO[' + passenger + '].resali = \'\'')
      // eval('this.passengersO[' + passenger + '].observ = \'\'')
    },
    setNroFile: function (nrofile) {
      this.nroFile = nrofile;
      this.type = "file";
    },
    savePassengers: async function (modal) {
      if (this.type == "package") {
        localStorage.setItem("passengers_temp", this.passengers);
      } else {
        let vm = this;
        //Todo Validamos en caso tenga activado la opcion de Agregar Cliente directo
        let validate_direct_client = this.validateDirectClient();
        if (!validate_direct_client && this.type !== "file") {
          vm.$toast.error("Para poder agregar un cliente directo es necesario ingresar los campos: Nombre, apellidos, tipo de documento, numero de documento, dirección, país y ciudad", {
            // override the global option
            position: "top-right"
          });
          vm.closeModal("modalAlertaPaxs");
          return;
        }
        //Todo: Validamos si hay niños, deben ingresar la fecha de nacimiento
        let result_child = this.validateChildQuote();
        if (!result_child && this.type !== "file") {
          let vm = this;
          vm.$toast.error("Se necesita la fecha de nacimiento de los niños como información requerida. Por favor, intente nuevamente", {
            // override the global option
            position: "top-right"
          });
          vm.closeModal("modalAlertaPaxs");
          return;
        }

        //Todo: Validamos si hay pasaportes, deben ser diferentes
        let result_passports = this.validatePassports();
        if (!result_passports) {
          let vm = this;
          vm.$toast.error("Se debe considerar números de pasaportes diferentes para los pasajeros. O en su defecto dejarlos vacíos.", {
            // override the global option
            position: "top-right"
          });
          vm.closeModal("modalAlertaPaxs");
          return;
        }

        if ((this.nroFile != undefined && this.nroFile != "") || this.type == "session") {
            this.loadingModal = true;
            let vm = this;
            let _url = this.type == "session" ? baseURL + "save_passengers" : baseExternalURL + "api/save_passenger";

            vm.passengers = this.cleanInputSelected(vm.passengers);

            if (vm.type == "file") {
                axios
                .post(baseExternalURL + "api/modal/paxs/update", {
                    nrofile: vm.nroFile,
                    flag_notify: localStorage.getItem("flag_notify_paxs"),
                    user_id: localStorage.getItem("user_id"),
                    client_id: localStorage.getItem("client_id"),
                    data: JSON.stringify(vm.passengers)
                })
                .then((response) => {
                    localStorage.removeItem("flag_notify_paxs");
                    console.log(response.data);
                })
                .catch((e) => {
                    console.log(e);
                });
            }

            axios.post(_url, {
                passengers: vm.passengers,
                repeat: vm.repeatPassenger,
                modePassenger: vm.modePassenger,
                file: vm.nroFile,
                type: vm.type,
                paxs: vm.total_paxs
            })
            .then((result) => {
                if (modal == false) {
                    this.$parent.destroyCartAndRedirect();
                } else {
                    if (result.data.process == true && result.data.success == true) {

                        if (this.type == "quote") {
                            this.$parent.saveQuote();
                        }

                        vm.$toast.success(vm.translations.board !== undefined && vm.translations.board.message !== undefined ? vm.translations.board.message.success : "Success", {
                            // override the global option
                            position: "top-right"
                        });

                        if (this.type == "file") {
                            axios
                            .post(baseExternalURL + "api/files/" + vm.nroFile + "/passengers", {
                                passengers: vm.passengers,
                                paxs: vm.total_paxs
                            })
                            .then((response) => {
                                let vm = this;
                                vm.loading_button = false;
                                vm.flag_edit = false;

                                vm.loadingModal = false;
                                vm.closeModal("modalAlertaPaxs");

                                if (localStorage.getItem("search_passengers") == 1) {
                                console.log("Mostrando el acomodo para la lista de pasajeros..");

                                setTimeout(function () {
                                    // vm.$parent.passengerAccommodation()
                                    vm.closeModal("modal-pax", true);
                                }, 100);
                                localStorage.removeItem("search_passengers");
                                }

                                /*
                                vm.$toast.success("Pasajeros asignados correctamente.", {
                                position: "top-right"
                                });
                                */
                            })
                            .catch((response) => {
                                console.log(response);
                                vm.loading_button = false;
                                vm.$toast.error(vm.translations.board.message.error, {
                                position: "top-right"
                                });
                            });
                        } else {
                            vm.loadingModal = false;
                            vm.closeModal("modalAlertaPaxs");
                            vm.closeModal("modal-pax", this.type !== "quote");
                        }
                    } else {
                        vm.loadingModal = false;
                        vm.closeModal("modalAlertaPaxs");

                        vm.$toast.error(vm.translations.board.message.error, {
                            // override the global option
                            position: "top-right"
                        });
                    }
                }
            })
            .catch((e) => {
                vm.loadingModal = false;
                console.log(e);
            });
        } else {
          vm.closeModal("modal-pax", true);
        }
      }
    },
    /*
        deletePassenger: function(passenger) {
            axios.post(baseURL + 'api/delete_passenger', {
                params: this.passengers[passenger]
            })
                .then((result) => {
                    console.log(result)
                })
                .catch((e) => {
                    console.log(e)
                })
        },
        */
    changeUpload: function () {
      this.excel = this.$refs.file.files[0];
      console.log(this.excel);
    },
    importPassengers: function () {
      this.loading = true;

      if (this.excel != "" && this.excel != null) {
        let formData = new FormData();
        formData.append("excel", this.excel);
        formData.append("file", this.nroFile);
        formData.append("paxs", this.total_paxs);

        axios
          .post(baseExternalURL + "api/import_passengers", formData, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          })
          .then((result) => {
            this.loading = false;

            if (result.data.type == "success") {
              this.excel = "";
              this.modalPassengers(this.type, this.nroFile, this.total_paxs);

              this.$toast.success(vm.translations.board.message.success, {
                // override the global option
                position: "top-right"
              });
            } else {
              this.$toast.error(vm.translations.board.message.error, {
                // override the global option
                position: "top-right"
              });
            }
          })
          .catch((e) => {
            this.loading = false;
            console.log(e);
          });
      }
    },
    cleanInputSelected: function (passengers) {
      let vm = this;
      passengers.forEach(function (item, i) {
        if (item.doctype_iso == "-1") {
          vm.$set(passengers[i], "doctype_iso", null);
        }

        if (item.nacion == "-1") {
          vm.$set(passengers[i], "nacion", null);
        }

        if (item.sexo == "") {
          vm.$set(passengers[i], "sexo", null);
        }

        if (item.tipdoc == "-1") {
          vm.$set(passengers[i], "tipdoc", null);
        }

        if (item.city_ifx_iso == "-1" || !item.city_ifx_iso) {
          vm.$set(passengers[i], "city_ifx_iso", null);
        }

        if(item.nombres && item.apellidos)
        {
          vm.$set(passengers[i], "nombre", `${item.apellidos ?? ''}, ${item.nombres ?? ''}`)
        }
      });

      return passengers;
    },
    validateDirectClient: function () {
      let errors = 0;
      this.passengers.forEach((pax, p) => {
        if (pax.is_direct_client) {
          if (pax.nombres === "") {
            errors++;
          }

          if (pax.apellidos === "") {
            errors++;
          }

          if (pax.address === "") {
            errors++;
          }

          if (pax.tipdoc === "-1" || pax.tipdoc === "") {
            errors++;
          }

          if (pax.nrodoc === "-1" || pax.nrodoc === "") {
            errors++;
          }

          if (pax.nacion === "-1" || pax.nacion === "") {
            errors++;
          }

          if (pax.city_ifx_iso === "-1" || pax.city_ifx_iso === "") {
            errors++;
          }
        }
      });

      return errors > 0 ? false : true;
    },
    async searchCitiesByCountry(iso, index) {
      let vm = this;
      console.log(vm.passengersO[index].city_ifx_iso);
      vm.$set(vm.cities, index, []);

      let country = vm.countries[index].find((item) => {
        return item.iso == vm.passengersO[index].nacion;
      });

      // if(country && !vm.passengers[index].celula){
      vm.$set(vm.passengers[index], "phone_code", country.phone_code);
      // }

      setTimeout(function () {
        axios
          .get(baseExternalURL + "api/country/" + iso + "/cities_ifx")
          .then((result) => {
            vm.$set(vm.cities, index, result.data.data);
            vm.$set(vm.passengers[index], "city_ifx_iso", vm.passengersO[index].city_ifx_iso);
          })
          .catch((result) => {
            // console.log(result)
          });
      }, 100);
    }
  }
};
</script>
