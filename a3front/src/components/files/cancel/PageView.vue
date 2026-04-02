<template>
  <template v-if="filesStore.isLoading || itineraryStore.isLoadingAsync">
    <div class="files-edit">
      <a-skeleton rows="1" active />
    </div>
    <div class="files-edit files-edit__border">
      <a-skeleton rows="1" active />
    </div>
    <div class="files-edit files-edit__border">
      <a-skeleton rows="1" active />
    </div>
  </template>
  <div class="files-edit" v-else>
    <a-steps :current="step" size="large" class="p-5 mb-5">
      <a-step :title="showMessage(0)" description="Servicio y Hoteles con penalidad" />
      <a-step :title="showMessage(1)" description="Comunicaciones" />
      <a-step :title="showMessage(2)" description="Anulación completa" />
    </a-steps>

    <div v-if="step == 0">
      <div class="d-flex justify-content-between align-items-center mt-5">
        <h4 class="title"><font-awesome-icon :icon="['fas', 'ban']" /> Anular File</h4>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
              {{ t('global.button.return_to_program') }}
            </span>
          </a-button>
          <a-button
            type="primary"
            class="btn-danger ms-2 px-4 text-600"
            v-on:click="nextStep()"
            default
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            v-if="status_reason_id != ''"
            size="large"
          >
            <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
              {{ t('global.button.continue') }}
            </span>
          </a-button>
        </div>
      </div>

      <div class="bg-light p-4 mt-3">
        <a-alert type="warning" :showIcon="false" class="mb-4">
          <template #message>
            <span class="text-dark-warning text-600"
              >Debes seleccionar un motivo para la anulación del file.</span
            >
          </template>
        </a-alert>
        <a-form :model="formState" :label-col="labelCol" :wrapper-col="wrapperCol">
          <a-form-item>
            <template #label>
              <span class="text-600">Motivo para la anulación del file</span>
              <b class="text-danger px-2">*</b>
            </template>
            <a-select
              :allowClear="false"
              class="w-100"
              v-model:value="status_reason_id"
              :showSearch="true"
              :fieldNames="{ label: 'name', value: 'id' }"
              :options="status_reasons"
            >
            </a-select>
          </a-form-item>
        </a-form>

        <div class="bg-light" v-if="filesStore.getAllPenality > 0">
          <a-row type="flex" justify="space-between" style="gap: 20px">
            <a-col>
              <a-row type="flex" justify="space-between" align="middle" style="gap: 4px">
                <a-col flex="auto" class="text-info">
                  <span class="cursor-pointer" v-on:click="showPoliciesCancellation()">{{
                    t('global.label.cancellation_policies')
                  }}</span>
                </a-col>
                <a-col>
                  <span>Penalidad total por anular el FILE:</span>
                </a-col>
              </a-row>

              <div class="p-4 border bg-white mt-4">
                <a-form layout="vertical">
                  <a-form-item label="¿Quién asume la penalidad?">
                    <a-select
                      size="large"
                      placeholder="Selecciona"
                      :default-active-first-option="false"
                      :not-found-content="null"
                      :options="filesStore.getAsumedBy"
                      v-model:value="asumed_by"
                      showSearch
                      :filter-option="false"
                      :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                    >
                    </a-select>
                  </a-form-item>

                  <a-form-item
                    label="Seleccione la especialista que asume la penalidad"
                    v-if="asumed_by == 13"
                  >
                    <a-select
                      size="large"
                      placeholder="Selecciona"
                      :default-active-first-option="false"
                      :not-found-content="null"
                      :options="executivesStore.getExecutives"
                      v-model:value="executive_id"
                      :field-names="{ label: 'name', value: 'id' }"
                      showSearch
                      :filter-option="false"
                      @search="searchExecutives"
                    >
                    </a-select>
                  </a-form-item>

                  <a-form-item
                    label="Seleccione el file que asume la penalidad"
                    v-if="asumed_by == 12"
                  >
                    <a-select
                      size="large"
                      placeholder="Selecciona"
                      :default-active-first-option="false"
                      :not-found-content="null"
                      :options="filesStore.getFiles"
                      v-model:value="file_id"
                      showSearch
                      :field-names="{ label: 'description', value: 'id' }"
                      :filter-option="false"
                      @search="searchFiles"
                    >
                    </a-select>
                  </a-form-item>

                  <a-form-item label="Motivo">
                    <a-textarea :rows="4" placeholder="Ingrese un motivo" v-model:value="motive" />
                  </a-form-item>
                </a-form>
              </div>
            </a-col>
            <a-col flex="auto">
              <a-row type="flex" justify="start" align="middle">
                <a-col class="text-warning">
                  <svg
                    class="feather feather-check-circle"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <path d="M22 4 12 14.01l-3-3" />
                  </svg>
                </a-col>
                <a-col class="text-warning ms-2">
                  <h5 class="m-0">
                    $
                    {{
                      formatNumber({
                        number: filesStore.getAllPenality,
                        digits: 2,
                      })
                    }}
                  </h5>
                </a-col>
                <a-col class="text-dark-warning ms-2">
                  <span class="text-500">Debe pagar la penalidad si anula el FILE</span>
                </a-col>
              </a-row>
              <div class="p-4 border bg-white mt-4">
                <p>Precio neto por tipo de pasajero</p>

                <table style="width: 100%">
                  <thead>
                    <tr>
                      <th>
                        <small class="text-gray my-1"> Costo neto </small>
                      </th>
                      <th>
                        <small class="text-gray my-1"> Habitación </small>
                      </th>
                      <th>
                        <small class="text-gray my-1"> {{ t('global.label.pax_type') }} </small>
                      </th>
                      <th>
                        <small class="text-gray my-1"> Cantidad </small>
                      </th>
                      <th>
                        <small class="text-gray my-1"> Total </small>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <template
                      v-if="
                        filesStore.getPenalitySGL > 0 ||
                        filesStore.getPenalityDBL > 0 ||
                        filesStore.getPenalityTPL > 0
                      "
                    >
                      <tr v-if="filesStore.getPenalitySGL > 0">
                        <td>
                          <p class="mb-0 text-warning text-center">
                            <span class="text-800">
                              $
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getPenalityADLCost + filesStore.getPenalitySGLCost,
                                  digits: 2,
                                })
                              }}
                            </span>

                            <FontAwesomeIcon
                              icon="arrow-right-long"
                              class="text-dark-gray mx-3"
                            ></FontAwesomeIcon>

                            <a-tag>
                              $
                              {{
                                formatNumber({
                                  number: filesStore.getPenalityADL + filesStore.getPenalitySGL,
                                  digits: 2,
                                })
                              }}
                            </a-tag>
                          </p>
                        </td>
                        <td>
                          <p class="text-700 text-center mb-0">Simple</p>
                        </td>
                        <td>
                          <p class="text-center mb-0">
                            <span class="text-700">ADL </span>
                            <font-awesome-icon :icon="['far', 'user']" />
                          </p>
                        </td>
                        <td>
                          <p class="text-center mb-0">
                            <span class="text-800">{{
                              textPad({
                                text: Math.ceil(filesStore.getFile.adults / 1),
                                start: '0',
                                length: 2,
                              })
                            }}</span>
                          </p>
                        </td>
                        <td>
                          <p class="text-warning text-center mb-0">
                            <span class="text-800"
                              >$
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getAllPenalityCost + filesStore.getPenalitySGLCost,
                                  digits: 2,
                                })
                              }}</span
                            >
                          </p>
                        </td>
                      </tr>
                      <tr v-if="filesStore.getPenalityDBL > 0">
                        <td>
                          <p class="mb-0 text-warning text-center">
                            <span class="text-800">
                              $
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getPenalityADLCost + filesStore.getPenalityDBLCost,
                                  digits: 2,
                                })
                              }}
                            </span>

                            <FontAwesomeIcon
                              icon="arrow-right-long"
                              class="text-dark-gray mx-3"
                            ></FontAwesomeIcon>

                            <a-tag>
                              $
                              {{
                                formatNumber({
                                  number: filesStore.getPenalityADL + filesStore.getPenalityDBL,
                                  digits: 2,
                                })
                              }}
                            </a-tag>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center text-700">Doble</p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-700">ADL </span>
                            <font-awesome-icon :icon="['far', 'user']" />
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800">{{
                              textPad({
                                text: Math.ceil(filesStore.getFile.adults / 2),
                                start: '0',
                                length: 2,
                              })
                            }}</span>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800 text-warning"
                              >$
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getAllPenalityCost + filesStore.getPenalityDBLCost,
                                  digits: 2,
                                })
                              }}</span
                            >
                          </p>
                        </td>
                      </tr>
                      <tr v-if="filesStore.getPenalityTPL > 0">
                        <td>
                          <p class="mb-0 text-warning text-center">
                            <span class="text-800">
                              $
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getPenalityADLCost + filesStore.getPenalityTPLCost,
                                  digits: 2,
                                })
                              }}
                            </span>

                            <FontAwesomeIcon
                              icon="arrow-right-long"
                              class="text-dark-gray mx-3"
                            ></FontAwesomeIcon>

                            <a-tag>
                              $
                              {{
                                formatNumber({
                                  number: filesStore.getPenalityADL + filesStore.getPenalityTPL,
                                  digits: 2,
                                })
                              }}
                            </a-tag>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center text-700">Triple</p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-700">ADL </span>
                            <font-awesome-icon :icon="['far', 'user']" />
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800">{{
                              textPad({
                                text: Math.ceil(filesStore.getFile.adults / 3),
                                start: '0',
                                length: 2,
                              })
                            }}</span>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800 text-warning"
                              >$
                              {{
                                formatNumber({
                                  number:
                                    filesStore.getAllPenalityCost + filesStore.getPenalityTPLCost,
                                  digits: 2,
                                })
                              }}</span
                            >
                          </p>
                        </td>
                      </tr>
                    </template>
                    <template v-else>
                      <tr>
                        <td>
                          <p class="mb-0 text-warning">
                            <span class="text-800">
                              $
                              {{
                                formatNumber({
                                  number: filesStore.getPenalityADLCost,
                                  digits: 2,
                                })
                              }}
                            </span>

                            <FontAwesomeIcon
                              icon="arrow-right-long"
                              class="text-dark-gray mx-3"
                            ></FontAwesomeIcon>

                            <a-tag>
                              $
                              {{
                                formatNumber({
                                  number: filesStore.getPenalityADL,
                                  digits: 2,
                                })
                              }}
                            </a-tag>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-700">-</span>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-700">ADL</span>
                            <i class="bi bi-person-fill"></i>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800">{{
                              textPad({
                                text: filesStore.getFile.adults,
                                start: '0',
                                length: 2,
                              })
                            }}</span>
                          </p>
                        </td>
                        <td>
                          <p class="mb-0 text-center">
                            <span class="text-800"
                              >$
                              {{
                                formatNumber({
                                  number: filesStore.getAllPenalityCost,
                                  digits: 2,
                                })
                              }}</span
                            >
                          </p>
                        </td>
                      </tr>
                    </template>
                    <tr v-if="filesStore.getFile.children > 0">
                      <td>
                        <p class="mb-0 text-warning">
                          <span class="text-800">
                            $
                            {{
                              formatNumber({
                                number: filesStore.getPenalityCHDCost,
                                digits: 2,
                              })
                            }}
                          </span>

                          <FontAwesomeIcon
                            icon="arrow-right-long"
                            class="text-dark-gray mx-3"
                          ></FontAwesomeIcon>

                          <a-tag>
                            $
                            {{
                              formatNumber({
                                number: filesStore.getPenalityCHD,
                                digits: 2,
                              })
                            }}
                          </a-tag>
                        </p>
                      </td>
                      <td>
                        <p class="mb-0 text-center">
                          <span class="text-700">Doble</span>
                        </p>
                      </td>
                      <td>
                        <p class="mb-0 text-center">
                          <span class="text-700">CHD</span>
                          <i class="bi bi-person-arms-up"></i>
                        </p>
                      </td>
                      <td>
                        <p class="mb-0 text-center">
                          <span class="text-800">{{
                            textPad({
                              text: filesStore.getFile.children,
                              start: '0',
                              length: 2,
                            })
                          }}</span>
                        </p>
                      </td>
                      <td>
                        <p class="mb-0 text-center">
                          <span class="text-800"
                            >$
                            {{
                              formatNumber({
                                number: filesStore.getAllPenalityCost,
                                digits: 2,
                              })
                            }}</span
                          >
                        </p>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- a-row
                  type="flex"
                  justify="space-between"
                >
                  <a-col flex="auto" class="text-left">
                    <p class="mb-1 text-warning">
                      <span class="text-800">$ {{ filesStore.getFileItinerary.total_amount }}</span>
                    </p>
                  </a-col>
                </a-row -->
              </div>
            </a-col>
          </a-row>
          <!-- a-row type="flex" justify="center" align="middle" class="text-danger">
            <a-col>
              <span class="mx-3 h1">
                <font-awesome-icon :icon="['fa-solid', 'fa-circle-exclamation']" />
              </span>
            </a-col>
            <a-col>
              <span class="mx-3 text-700">Penalidad total por anular el File</span>
            </a-col>
            <a-col>
              <b class="mx-3 h1 text-800"
                >$ {{ formatNumber({ number: filesStore.getAllPenality }) }}</b
              >
            </a-col>
            <a-col>
              <div class="mx-3 text-500">
                <p class="pb-0 mb-0">Penalidad total por pasajero adulto</p>
                <p class="pb-0 mb-0" v-if="filesStore.getFile.children > 0">
                  Penalidad total por pasajero niño
                </p>
              </div>
            </a-col>
            <a-col>
              <div class="mx-3 text-800">
                <p class="pb-0 mb-0">
                  <b
                    >$
                    {{
                      formatNumber({
                        number: filesStore.getPenalityADL * filesStore.getFile.adults,
                      })
                    }}</b
                  >
                </p>
                <p class="pb-0 mb-0" v-if="filesStore.getFile.children > 0">
                  <b
                    >$
                    {{
                      formatNumber({
                        number: filesStore.getPenalityCHD * filesStore.getFile.children,
                      })
                    }}</b
                  >
                </p>
              </div>
            </a-col>
          </a-row -->
        </div>
      </div>

      <div class="bg-white bordered p-4 my-4">
        <FileHeader v-bind:data="filesStore.getFile" v-bind:editable="false" />
      </div>

      <div
        class="bg-white bordered p-4 py-4"
        v-if="filesStore.getPenalityHotels.length > 0 || filesStore.getPenalityServices.length > 0"
      >
        <template v-if="filesStore.getPenalityHotels.length > 0">
          <div class="subtitle my-3 mx-2">
            <a-row type="flex" align="middle">
              <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.5rem"></i>
              <div class="d-flex mx-2 text-dark-warning">Hoteles que generan penalidad</div>
            </a-row>
          </div>

          <template v-for="(_hotel, h) in filesStore.getPenalityHotels" :key="'penalty-hotel-' + h">
            <hotel-merge
              :show_communication="false"
              type="cancellation"
              :flag_simulation="true"
              :flag_preview="true"
              :title="false"
              :from="_hotel"
              :buttons="false"
            />
          </template>
        </template>

        <template v-if="filesStore.getPenalityServices.length > 0">
          <hr class="line-dashed size-2 mt-5 mb-5" v-if="filesStore.getPenalityHotels.length > 0" />
          <div class="subtitle my-3 mx-2">
            <a-row type="flex" align="middle">
              <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.5rem"></i>
              <div class="d-flex mx-2 text-dark-warning">Servicios que generan penalidad</div>
            </a-row>
          </div>

          <template
            v-for="(_service, s) in filesStore.getPenalityServices"
            :key="'penalty-service-' + s"
          >
            <service-merge
              :show_communication="false"
              type="cancellation"
              :flag_simulation="true"
              :flag_preview="true"
              :title="false"
              :from="_service"
              :buttons="false"
            />
          </template>
        </template>
      </div>

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="px-4 text-600"
              v-on:click="returnToProgram()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
                {{ t('global.button.cancel') }}
              </span>
            </a-button>
            <a-button
              type="primary"
              default
              class="ms-2 px-4 text-600"
              v-on:click="nextStep()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              v-if="status_reason_id != ''"
              size="large"
            >
              <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
                {{ t('global.button.continue') }}
              </span>
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 1">
      <div class="d-flex justify-content-between align-items-center mt-5 mb-5">
        <div class="title">
          <font-awesome-icon :icon="['far', 'comments']" />
          {{ t('files.label.communication_to_provider') }}
        </div>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :loading="filesStore.isLoading || filesStore.isLoadingAsync || loadingServices"
            size="large"
          >
            <span
              :class="
                filesStore.isLoading || filesStore.isLoadingAsync || loadingServices ? 'ms-2' : ''
              "
            >
              {{ t('global.button.return_to_program') }}
            </span>
          </a-button>
        </div>
      </div>

      <div class="bg-light p-5" v-if="status_reason_name != '' && status_reason_name != null">
        <a-row
          align="middle"
          type="flex"
          justify="start"
          style="gap: 8px"
          class="mb-3"
          @click="flagNotes = !flagNotes"
        >
          <a-col>
            <div v-bind:class="['cursor-pointer', !flagNotes ? 'text-dark-gray' : 'text-danger']">
              <template v-if="flagNotes">
                <font-awesome-icon :icon="['far', 'square-check']" style="font-size: 1.3rem" />
              </template>
              <template v-else>
                <font-awesome-icon :icon="['far', 'square']" style="font-size: 1.3rem" />
              </template>
            </div>
          </a-col>
          <a-col>
            <span class="cursor-pointer" style="font-size: 16px"
              >Agregar motivo para anulación del FILE <b>{{ filesStore.getFile.fileNumber }}</b> a
              las comunicaciones.</span
            >
          </a-col>
        </a-row>

        <div class="bg-white p-3" style="border-radius: 6px">
          <a-row type="flex" justify="start" align="middle" style="gap: 20px">
            <a-col><span style="font-size: 12px">Motivo para anulación del FILE:</span></a-col>
            <a-col
              ><b style="font-size: 12px">{{ status_reason_name }}</b></a-col
            >
          </a-row>
        </div>
      </div>

      <template v-if="filesStore.getFileItineraries.length > 0">
        <div
          class="subtitle my-5 mx-2 px-4"
          style="font-size: 18px"
          v-if="
            filesStore.getFileItineraries.filter(
              (itinerary) => itinerary.entity === 'hotel' && itinerary.status
            ).length > 0
          "
        >
          <a-row type="flex" align="middle" justify="start" style="gap: 10px">
            <a-col>
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-warning" />
            </a-col>
            <a-col>
              <span class="text-dark-warning text-uppercase" style="font-size: 14px"
                >Comunicaciones a hoteles</span
              >
            </a-col>
          </a-row>
        </div>

        <hotel-merge
          v-for="(itinerary, i) in filesStore.getFileItineraries.filter(
            (itinerary) => itinerary.entity == 'hotel' && itinerary.status
          )"
          :key="itinerary.id || i"
          ref="hotels"
          :show_communication="true"
          :title="false"
          type="cancellation"
          :flag_notes="flagNotes"
          :status_reason_name="status_reason_name"
          :flag_simulation="true"
          :flag_preview="false"
          :from="itinerary"
          :buttons="false"
        />

        <div
          class="subtitle my-5 mx-2 px-4"
          style="font-size: 18px"
          v-if="
            filesStore.getFileItineraries.filter(
              (itinerary) =>
                (itinerary.entity === 'service' || itinerary.entity == 'service-temporary') &&
                itinerary.status
            ).length > 0
          "
        >
          <a-row type="flex" align="middle" justify="start" style="gap: 10px">
            <a-col>
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="text-warning" />
            </a-col>
            <a-col>
              <span class="text-dark-warning text-uppercase" style="font-size: 14px"
                >Comunicaciones a servicios</span
              >
            </a-col>
          </a-row>
        </div>

        <template v-if="loadingServices">
          <a-skeleton active />
        </template>
        <div v-show="!loadingServices">
          <service-merge
            v-for="(itinerary, i) in filesStore.getFileItineraries.filter(
              (itinerary) =>
                (itinerary.entity === 'service' || itinerary.entity == 'service-temporary') &&
                itinerary.status
            )"
            :key="itinerary.id || i"
            ref="services"
            :show_communication="true"
            :title="true"
            type="cancellation"
            :flag_notes="flagNotes"
            :status_reason_name="status_reason_name"
            :flag_simulation="true"
            :flag_preview="false"
            :from="itinerary"
            :buttons="false"
          />
        </div>
      </template>
      <template v-else>
        <a-alert type="warning" class="mt-3">
          <template #message>
            <div class="text-dark-warning text-600">
              No hay comunicaciones disponibles para hoteles y/o servicios, se puede proceder con la
              cancelación.
            </div>
          </template>
        </a-alert>
      </template>

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              v-if="
                filesStore.getPenalityHotels.length > 0 || filesStore.getPenalityServices.length > 0
              "
              type="default"
              class="px-4 text-600"
              v-on:click="prevStep()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
                {{ t('global.button.cancel') }}
              </span>
            </a-button>
            <a-button
              type="primary"
              default
              class="ms-2 px-4 text-600"
              v-on:click="processReservation()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync || loadingServices"
              size="large"
            >
              <span
                :class="
                  filesStore.isLoading || filesStore.isLoadingAsync || loadingServices ? 'ms-2' : ''
                "
              >
                {{ t('global.button.continue') }}
              </span>
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 2">
      <div class="mt-5 pt-5">
        <div class="text-center">
          <h2 class="text-danger text-800">
            Anulación de file {{ filesStore.getFile.fileNumber }} exitosa
          </h2>
          <div class="my-5">
            <a-row type="flex" justify="center" align="middle">
              <a-col>
                <svg
                  style="color: #1ed790"
                  class="feather feather-check-circle"
                  xmlns="http://www.w3.org/2000/svg"
                  width="5rem"
                  height="5rem"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                  <path d="M22 4 12 14.01l-3-3" />
                </svg>
              </a-col>
            </a-row>
          </div>
        </div>

        <div class="box-completed bg-light py-3 px-5">
          <a-row type="flex" align="middle" justify="space-between" class="mx-5">
            <a-col class="me-4">
              <span class="text-danger">
                <b>Detalle del file anulado</b>
              </span>
            </a-col>
            <a-col flex="auto">
              <span class="bg-white px-3 py-2 bordered w-100 ant-row-middle">
                <span class="d-flex mx-1">
                  <i class="bi bi-pencil"></i>
                </span>
                <span class="text-danger text-600">Motivo de anulación:</span>
                <span class="mx-2">{{ status_reason_name }}</span>
              </span>
            </a-col>
          </a-row>
          <a-row type="flex" align="top" justify="space-between" class="my-3 mx-5">
            <a-col>
              <p class="d-flex" style="gap: 5px">
                <b>Nombre del Cliente:</b>
                <span>{{ filesStore.getFile.clientName }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Nombre del File:</b>
                <span>{{ filesStore.getFile.description }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Nacionalidad:</b> <span>{{ showLanguage() }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Fecha de Reserva:</b>
                <span>{{ filesStore.getFile.dateIn }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>E-mail del ejecutivo:</b>
                <span class="text-lowercase"
                  >{{ filesStore.getFile.executiveCode }}@limatours.com.pe</span
                >
              </p>
            </a-col>
            <a-col>
              <p>
                <b class="text-danger">Fechas:</b>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Inicio del file:</b>
                <span>{{ formatDate(filesStore.getFile.dateIn) }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Finalización del file:</b>
                <span>{{ formatDate(filesStore.getFile.dateOut) }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Cantidad de pax: </b>
                <font-awesome-icon icon="fa-solid fa-user" />
                <span>{{ filesStore.getFile.adults }}</span>
                <font-awesome-icon icon="fa-solid fa-child-reaching" />
                <span>{{ filesStore.getFile.children }}</span>
              </p>
            </a-col>
            <a-col>
              <p>
                <b class="text-danger">Total por anulación del file:</b>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Precio por pax: <font-awesome-icon icon="fa-solid fa-user" /> </b>
                <span
                  >USD {{ formatNumber({ number: filesStore.getPenalityADL, digits: 2 }) }}</span
                >
              </p>
              <p class="d-flex" style="gap: 5px" v-if="filesStore.getFile.children > 0">
                <b>Precio por pax: <font-awesome-icon icon="fa-solid fa-child-reaching" /> </b>
                <span
                  >USD
                  {{
                    formatNumber({
                      number: filesStore.getPenalityCHD,
                      digits: 2,
                    })
                  }}</span
                >
              </p>
              <p class="d-flex text-dark-warning" style="gap: 5px">
                <b>Penalidad total por anulación:</b>
                <b> USD {{ formatNumber({ number: filesStore.getAllPenality, digits: 2 }) }} </b>
              </p>
            </a-col>
          </a-row>
        </div>

        <div class="box-buttons mt-5">
          <a-row type="flex" justify="center" align="middle">
            <a-col>
              <a-button
                type="primary"
                class="px-4 text-600"
                v-on:click="returnToProgram()"
                default
                :loading="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
                  {{ t('global.button.close') }}
                </span>
              </a-button>
            </a-col>
          </a-row>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { onBeforeMount, ref, watch } from 'vue';
  import { debounce } from 'lodash-es';
  import { useRouter, useRoute } from 'vue-router';
  import { notification } from 'ant-design-vue';
  import {
    useFilesStore,
    useStatusesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
    useItineraryStore,
  } from '@store/files';
  import { useLanguagesStore } from '@store/global';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { formatNumber, formatDate, textPad, processLoadingItineraries } from '@/utils/files.js';
  import FileHeader from '@/components/files/reusables/FileHeader.vue';
  import HotelMerge from '@/components/files/reusables/HotelMerge.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const languagesStore = useLanguagesStore();

  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();

  const step = ref(0);
  const formState = ref({
    user: '',
    password: '',
  });
  const status_reason_id = ref('');
  const status_reason_name = ref('');
  const status_reasons = ref([]);
  const services = ref([]);
  const hotels = ref([]);

  const showMessage = (_step) => {
    let message = 'Finalizado';

    if (step.value < _step) {
      message = 'En espera';
    }

    if (step.value == _step) {
      message = 'En proceso';
    }

    return message;
  };

  const flagNotes = ref(false);

  const returnToProgram = () => {
    router.push({ name: 'files-edit', params: route.params });
  };

  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  onBeforeMount(async () => {
    const { id } = route.params;

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });

      if (filesStore.getFile?.id !== parseInt(id)) {
        router.push({ name: 'error_404' });
      }

      const codes = filesStore.getFileItineraries
        .filter((itinerary) => itinerary.entity === 'service')
        .map((itinerary) => {
          return itinerary?.services.map((service) => service.code_ifx);
        });

      const unique_codes = [...new Set(codes.flat())];

      await Promise.all([
        filesStore.searchServicesGroups({ codes: unique_codes }),
        filesStore.searchServicesFrequences({ codes: unique_codes }),
        // filesStore.searchServiceSchedules({ codes: unique_codes }),
      ]);

      itineraryStore.initedAsync();
      await filesStore.getPassengersById({ fileId: id });
      await processLoadingItineraries();
      itineraryStore.finished();
    }

    await filesStore.fetchStatusReasons();
    await filesStore.calculatePenality();
    await filesStore.fetchAsumedBy();

    if (filesStore.getAllPenality > 0) {
      flag_validate.value = true;
    }

    let flag_continue = true;

    filesStore.getFileItineraries.map((itinerary) => {
      if (!itinerary.confirmation_status && flag_continue) {
        notification.error({
          message: 'Error al anular',
          description: `El ${itinerary.name} del día ${itinerary.date_in} no se encuentra confirmado. Por lo que no podemos anular el File.`,
        });

        flag_continue = false;
      }
    });

    if (filesStore.getFile.status !== 'ok') {
      flag_continue = false;
    }

    if (!flag_continue) {
      let route = 'files-edit';
      let params = {
        id: filesStore.getFile.id,
      };

      router.push({ name: route, params: params });
    }

    await filesStore.getStatusReasons.forEach(async (reason) => {
      if (reason.status_iso === 'xl' || reason.status_iso === 'XL') {
        status_reasons.value.push(reason);
      }
    });

    filesStore.finished();
  });

  const loadingServices = ref(false);

  watch(
    () => services.value.length,
    async () => {
      if (step.value == 1) {
        loadingServices.value = true;
        filesStore.initedAsync();
        for (const refService of services.value) {
          await refService?.handleCancellationType?.(true);
          await sleep(500); // Aquí sí se espera correctamente
        }
        filesStore.finished();
        loadingServices.value = false;
      }
    }
  );

  watch(
    () => step.value,
    async (newValue) => {
      if (newValue == 0) {
        filesStore.inited();
        filesStore.clearPenality();
        await filesStore.calculatePenality();
        filesStore.finished();
      }
    },
    { immediate: true }
  );

  const nextStep = () => {
    if (step.value == 0) {
      if (flag_validate.value && asumed_by.value == '') {
        notification['warning']({
          message: `Error de penalidad`,
          description: 'Complete los datos de quién asume la penalidad',
          duration: 5,
        });

        return false;
      }
    }

    step.value++;
  };

  const prevStep = () => {
    step.value--;
  };

  const showLanguage = () => {
    return (
      languagesStore.getLanguages.find((item) => item.value === filesStore.getFile.lang).label ?? ''
    );
  };

  watch(
    () => status_reason_id.value,
    async (newValue) => {
      if (newValue != '' && newValue != null) {
        const status_reason = status_reasons.value.filter(
          (status_reason) => status_reason.id === newValue
        );

        status_reason_name.value = status_reason[0].name;
      }
    },
    { immediate: true }
  );

  const processReservation = async () => {
    let params = {
      file_id: filesStore.getFile.id,
      file_number: filesStore.getFile.fileNumber,
      notas: '',
      attachments: [],
      status_reason_id: status_reason_id.value,
    };

    await filesStore.cancel(params);

    if (filesStore.getError != '') {
      notification.error({
        message: 'Error',
        description:
          'El FILE no se pudo anular. Por favor, actualice la página e inténtelo de nuevo.',
      });
    } else {
      nextStep();
    }
  };

  const searchExecutives = debounce(async (value) => {
    if (value != '' || (value == '' && executivesStore.getExecutives.length == 0)) {
      await executivesStore.fetchAll(value);
    }
  }, 300);

  const searchFiles = debounce(async (value) => {
    if (value != '') {
      await filesStore.fetchAll({ filter: value });
    }
  }, 300);

  const flag_validate = ref(false);
  const executive_id = ref('');
  const file_id = ref('');
  const asumed_by = ref('');
  const motive = ref('');

  // Función para emitir cambios de estado
  /*
  const emitChange = () => {
    emit('onChangeAsumed', {
      executive_id: executive_id.value,
      file_id: file_id.value,
      flag_validate: flag_validate.value,
      asumed_by: asumed_by.value,
      motive: motive.value,
    });
  };
  */

  // Observa cambios en las referencias
  /*
  watch([flag_validate, asumed_by, executive_id, file_id, motive], () => {
    emitChange();
  });
  */
</script>
