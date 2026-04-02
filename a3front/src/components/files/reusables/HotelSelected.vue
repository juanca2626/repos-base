<template>
  <div class="bg-pink-stick p-5 mt-5">
    <a-row type="flex" style="gap: 8px" justify="space-between" align="middle" class="me-5 pe-5">
      <a-col>
        <div>
          <a-row type="flex" style="gap: 8px" justify="start" align="middle">
            <a-col>
              <font-awesome-icon :icon="['fa-solid', 'fa-hotel']" style="font-size: 18px" />
            </a-col>
            <a-col>
              <a-tag color="warning" class="m-0">
                <small class="text-uppercase text-500 text-dark-warning">
                  {{ filesStore.getFileItinerary.object_code }}
                </small>
              </a-tag>
            </a-col>
            <a-col>
              <b style="font-size: 18px">{{ filesStore.getFileItinerary.name }}</b>
            </a-col>
            <a-col v-if="filesStore.getFileItinerary?.category">
              <a-tag color="#c63838">
                <small class="text-uppercase text-400">
                  {{ filesStore.getFileItinerary.category }}
                </small>
              </a-tag>
            </a-col>
            <a-col v-if="filesStore.getFileItinerary?.city_in_name">
              <a-tag>
                <small class="text-uppercase text-600">
                  {{ filesStore.getFileItinerary.city_in_name }}
                </small>
              </a-tag>
            </a-col>
          </a-row>
        </div>
      </a-col>
      <a-col class="text-dark-gray">
        <a-row type="flex" style="gap: 8px" align="middle">
          <a-col>
            <font-awesome-icon :icon="['far', 'calendar']" />
          </a-col>
          <a-col>
            <b>{{ formatDate(filesStore.getFileItinerary.date_in, 'DD/MM/YYYY') }}</b>
          </a-col>
          <a-col>
            <big class="text-dark text-700">|</big>
          </a-col>
          <a-col>
            <b>{{ formatDate(filesStore.getFileItinerary.date_out, 'DD/MM/YYYY') }}</b>
          </a-col>
        </a-row>
      </a-col>
    </a-row>

    <template v-for="(room, r) in filesStore.getFileItinerary.rooms" :key="'room-' + r">
      <hr
        class="border-0"
        style="
          background-color: #c4c4c4;
          height: 1px !important;
          margin-top: 1rem;
          margin-bottom: 1rem;
          margin-right: 4rem;
          text-align: left;
        "
      />
      <a-row type="flex" align="middle" justify="space-between" class="my-2">
        <a-col flex="auto" class="d-flex" style="gap: 5px">
          <b class="text-capitalize">{{ t('global.label.room') }}:</b>
          <span class="text-uppercase">{{ room.room_name }}</span>
        </a-col>
        <a-col flex="auto" class="d-flex" style="gap: 5px">
          <i class="bi bi-moon-fill text-dark-gray"></i>
          <b>{{ t('global.label.nights') }}:</b>
          <b class="text-danger">{{
            textPad({ text: room.units[0].nights.length, start: 0, length: 2 })
          }}</b>
        </a-col>
        <a-col flex="auto" class="d-flex" style="gap: 5px">
          <font-awesome-icon :icon="['fas', 'bed']" />
          <b class="text-capitalize">{{ t('global.label.units') }}:</b>
          <b class="text-danger">{{ textPad({ text: room.total_rooms, start: 0, length: 2 }) }}</b>
        </a-col>
        <a-col flex="auto" class="me-5">
          <a-row type="flex" align="middle" justify="end" style="gap: 5px">
            <a-col class="d-flex">
              <font-awesome-icon :icon="['fas', 'circle-xmark']" size="lg" class="text-danger" />
            </a-col>
            <a-col>
              <b>{{ room.rate_plan_name }}</b>
            </a-col>
            <a-col>
              <b class="text-danger">$ {{ room.amount_sale }}</b>
            </a-col>
          </a-row>
        </a-col>
        <a-col>
          <a href="javascript:;" @click="handleSelected('room', room)">
            <template v-if="validateSelected(room.units) == 0">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 448 512"
                width="30"
                height="30"
                class="text-danger bi-square"
              >
                <path
                  d="M384 80c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H384zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"
                />
              </svg>
            </template>
            <template v-else>
              <template v-if="validateSelected(room.units) == room.units.length">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 448 512"
                  width="30"
                  height="30"
                  class="text-danger bi-checked"
                >
                  <path
                    d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM337 209L209 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L303 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"
                  />
                </svg>
              </template>
              <template v-else>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 448 512"
                  width="30"
                  height="30"
                  class="text-danger bi-minus"
                >
                  <path
                    d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm88 200H296c13.3 0 24 10.7 24 24s-10.7 24-24 24H152c-13.3 0-24-10.7-24-24s10.7-24 24-24z"
                  />
                </svg>
              </template>
            </template>
          </a>
        </a-col>
      </a-row>
      <hr
        class="border-0"
        style="
          background-color: #c4c4c4;
          height: 1px !important;
          margin-top: 1rem;
          margin-bottom: 1rem;
          margin-right: 4rem;
          text-align: left;
        "
      />
      <template v-if="room.units.length > 1">
        <a-row type="flex" class="mt-3 mb-4" justify="start" align="middle" style="gap: 5px">
          <a-col class="d-flex">
            <svg
              class="feather feather-git-pull-request"
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle cx="18" cy="18" r="3" />
              <circle cx="6" cy="6" r="3" />
              <path d="M13 6h3a2 2 0 0 1 2 2v7M6 9v12" />
            </svg>
          </a-col>
          <a-col>
            <b class="text-600" style="border-bottom: 1px solid #373737; padding-bottom: 1px">
              {{ t('global.label.units') }}
            </b>
          </a-col>
          <template v-if="filesStore.isConfirmationCodeRoom(room).flag_confirmation">
            <a-col>
              Código de confirmación:
              <b>{{ filesStore.isConfirmationCodeRoom(room).confirmation_code }}</b>
            </a-col>
          </template>
        </a-row>
        <template v-for="(unit, u) in room.units" :key="u">
          <a-row class="ms-5" type="flex" align="middle" justify="space-between" style="gap: 5px">
            <a-col>
              <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                <a-col>
                  <FontAwesomeIcon
                    :icon="['far', 'calendar']"
                    class="text-dark-gray"
                  ></FontAwesomeIcon>
                </a-col>
                <a-col>
                  <b>{{ formatDate(unit.nights[0].date, 'DD/MM') }}</b>
                </a-col>
                <a-col>
                  <FontAwesomeIcon icon="arrow-right-long" class="text-dark-gray"></FontAwesomeIcon>
                </a-col>
                <a-col>
                  <b>{{ formatDate(unit.nights[unit.nights.length - 1].date, 'DD/MM') }}</b>
                </a-col>
              </a-row>
            </a-col>
            <a-col flex="auto">
              <a-row type="flex" justify="end" align="middle" style="gap: 7px">
                <a-col>
                  <span class="text-400"> {{ room.room_name }} - {{ room.room_type }} </span>
                </a-col>
                <template
                  v-if="
                    !filesStore.isConfirmationCodeRoom(room).flag_confirmation &&
                    unit.confirmation_status
                  "
                >
                  <a-col>
                    <b>|</b>
                  </a-col>
                  <a-col>
                    Código de confirmación:
                    <b class="text-600">{{ unit.confirmation_code || '---' }}</b>
                  </a-col>
                </template>
              </a-row>
            </a-col>
            <a-col>
              <a-row type="flex" justify="start" align="middle" class="ms-4" style="gap: 10px">
                <template v-if="filesStore.calculatePenalityRoomUnitsCost([room], [unit.id]) > 0">
                  <a-col>
                    <FontAwesomeIcon
                      icon="triangle-exclamation"
                      class="text-warning"
                      style="width: 24px; height: 24px"
                    ></FontAwesomeIcon>
                  </a-col>
                  <a-col>
                    <b class="text-warning text-700" style="font-size: 18px">
                      $
                      {{
                        formatNumber({
                          number: filesStore.calculatePenalityRoomUnitsCost([room], [unit.id]),
                          digits: 2,
                        })
                      }}
                    </b>
                  </a-col>
                </template>
                <template v-else>
                  <a-col>
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      size="lg"
                      class="text-success"
                    />
                  </a-col>
                </template>
              </a-row>
            </a-col>
            <a-col class="ps-5 ms-5 me-4">
              <a-row type="flex" justify="end" align="top" style="gap: 7px">
                <a-col>
                  <a-row>
                    <a-col>
                      <b style="font-size: 16px" class="text-600">${{ unit.amount_cost }}</b>
                    </a-col>
                    <a-col>
                      <files-edit-field-static :inline="true" :hide-content="false">
                        <template #label>
                          <svg
                            v-if="room.room_amount.file_amount_type_flag_id === 1"
                            style="margin-top: 7px; color: #ffcc00; cursor: pointer"
                            class="feather feather-lock"
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            viewBox="0 0 24 24"
                          >
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                          </svg>
                          <svg
                            v-if="room.room_amount.file_amount_type_flag_id === 2"
                            style="margin: 7px 0; color: #3d3d3d; cursor: pointer"
                            class="feather feather-lock"
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            viewBox="0 0 24 24"
                          >
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                          </svg>
                          <svg
                            v-if="room.room_amount.file_amount_type_flag_id === 3"
                            style="margin: 7px 0; color: #c4c4c4; cursor: pointer"
                            class="feather feather-lock"
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            viewBox="0 0 24 24"
                          >
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                          </svg>
                        </template>
                        <template #popover-content>
                          {{ room.room_amount.file_amount_type_flag.description }}
                        </template>
                      </files-edit-field-static>
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
            </a-col>
            <a-col v-if="1 != 1">
              <a href="javascript:;" @click="handleSelected('unit', unit)">
                <template v-if="selected.indexOf(unit.id) > -1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 448 512"
                    width="24"
                    height="24"
                    class="svg-danger"
                  >
                    <path
                      d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM337 209L209 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L303 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"
                    />
                  </svg>
                </template>
                <template v-else>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 448 512"
                    width="24"
                    height="24"
                    class="svg-danger"
                  >
                    <path
                      d="M384 80c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H384zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"
                    />
                  </svg>
                </template>
              </a>
            </a-col>
          </a-row>
        </template>
      </template>
      <template v-else>
        <a-row type="flex" justify="space-between" align="middle" style="margin-right: 4rem">
          <a-col>
            <a-row type="flex" justify="start" align="middle">
              <a-col>
                Código de confirmación:
                <b class="text-600">{{ room.units[0].confirmation_code || '---' }}</b>
              </a-col>
            </a-row>
          </a-col>
          <a-col>
            <a-row type="flex" justify="start" align="middle" style="gap: 5px">
              <template
                v-if="
                  filesStore.calculatePenalityRoomsSale(filesStore.getFileItinerary.rooms, [
                    room.id,
                  ]) > 0
                "
              >
                <a-col>
                  <FontAwesomeIcon
                    icon="circle-info"
                    class="text-info"
                    style="width: 24px; height: 24px"
                  ></FontAwesomeIcon>
                </a-col>
                <a-col>
                  <FontAwesomeIcon
                    icon="triangle-exclamation"
                    class="text-warning"
                    style="width: 24px; height: 24px"
                  ></FontAwesomeIcon>
                </a-col>
                <a-col>
                  <b class="text-dark-warning text-500" style="font-size: 12px"
                    >Penalidad por cancelación</b
                  >
                </a-col>
                <a-col>
                  <b class="text-warning text-700" style="font-size: 18px">
                    $
                    {{
                      formatNumber({
                        number: filesStore.calculatePenalityRoomsSale(
                          filesStore.getFileItinerary.rooms,
                          [room.id]
                        ),
                        digits: 2,
                      })
                    }}
                  </b>
                </a-col>
              </template>
              <template v-else>
                <a-col>
                  <font-awesome-icon
                    :icon="['fas', 'circle-check']"
                    size="lg"
                    class="text-success"
                  />
                </a-col>
                <a-col>
                  <b class="text-dark-success text-500 text-uppercase" style="font-size: 11px">
                    {{ t('files.label.no_penalty') }}
                  </b>
                </a-col>
              </template>
            </a-row>
          </a-col>
        </a-row>
      </template>
    </template>
  </div>

  <div
    v-if="
      filesStore.calculatePenalityRoomUnitsSale(filesStore.getFileItinerary.rooms, selected) > 0
    "
    class="bg-gray p-5 my-5"
  >
    <a-row type="flex" justify="space-between" class="my-3">
      <a-col class="mx-3">
        <a-row type="flex" justify="space-between" align="middle" style="gap: 4px">
          <a-col class="text-info cursor-pointer" v-on:click="showPoliciesCancellation()">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 512 512"
              width="24"
              height="24"
              class="d-flex"
            >
              <path
                d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"
              />
            </svg>
          </a-col>
          <a-col flex="auto" class="text-info cursor-pointer">
            <span class="cursor-pointer" v-on:click="showPoliciesCancellation()">{{
              t('global.label.cancellation_policies')
            }}</span>
          </a-col>
          <a-col>
            <span>{{ t('global.label.total_cost_price_with_penalty') }}:</span>
          </a-col>
        </a-row>

        <div class="p-4 border bg-white my-4">
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

            <a-form-item label="Seleccione el file que asume la penalidad" v-if="asumed_by == 12">
              <a-select
                size="large"
                placeholder="Selecciona"
                :default-active-first-option="false"
                :not-found-content="null"
                :options="filesStore.getFiles"
                v-model:value="file_id"
                :field-names="{ label: 'description', value: 'id' }"
                showSearch
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
      <a-col flex="auto" class="mx-3">
        <a-row type="flex" justify="start" align="middle" style="gap: 4px">
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
          <a-col class="text-warning">
            <span class="text-700" style="font-size: 24px"
              >$
              {{
                formatNumber({
                  number: filesStore.calculatePenalityRoomUnitsSale(
                    filesStore.getFileItinerary.rooms,
                    selected
                  ),
                  digits: 2,
                })
              }}</span
            >
          </a-col>
          <a-col class="text-dark-warning">
            <span class="text-500" style="font-size: 12px"
              >Debe pagar la penalidad si cambia o anula el hotel</span
            >
          </a-col>
        </a-row>
        <div class="p-4 border bg-white my-4">
          <p>{{ t('files.message.price_by_room_type_and_pax') }}</p>

          <table style="width: 100%">
            <tr>
              <td>
                <small>Precio neto</small>
              </td>
              <td class="text-center">
                <small>{{ t('global.label.unit') }}</small>
              </td>
              <td class="text-center">
                <small>{{ t('global.label.pax_type') }}</small>
              </td>
              <td class="text-center">
                <small>Cantidad</small>
              </td>
              <td class="text-right">
                <small>Total</small>
              </td>
            </tr>
            <template v-for="(_room, r) in filesStore.getFileItinerary.rooms">
              <tr>
                <td>
                  <span class="text-600 text-warning" style="font-size: 12px">
                    $
                    {{
                      formatNumber({
                        number:
                          filesStore.calculatePenalityRoomsCost(filesStore.getFileItinerary.rooms, [
                            _room.id,
                          ]) /
                          (_room.total_adults * _room.units.length),
                        digits: 2,
                      })
                    }}
                  </span>
                  <font-awesome-icon icon="fa-solid fa-arrow-right-long" class="mx-2 text-gray" />
                  <span
                    class="d-inline-block"
                    style="
                      color: #c4c4c4;
                      font-size: 12px;
                      border: 1px solid #c4c4c4;
                      border-radius: 6px;
                      padding: 3px;
                    "
                  >
                    $
                    {{
                      formatNumber({
                        number:
                          filesStore.calculatePenalityRoomsSale(filesStore.getFileItinerary.rooms, [
                            _room.id,
                          ]) /
                          (_room.total_adults * _room.units.length),
                        digits: 2,
                      })
                    }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="text-600" style="font-size: 12px">
                    <i class="bi bi-building-fill"></i>
                    {{ showTypeRoom(_room.total_adults / _room.total_rooms) }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="text-600" style="font-size: 12px">ADL</span>
                  <i class="bi bi-person-fill"></i>
                </td>
                <td class="text-center">
                  <span class="text-600 text-warning" style="font-size: 12px">
                    {{ textPad({ text: _room.total_adults, start: 0, length: 2 }) }}
                  </span>
                </td>
                <td class="text-right">
                  <span class="text-600 text-warning" style="font-size: 12px"
                    >$
                    {{
                      formatNumber({
                        number: filesStore.calculatePenalityRoomsCost(
                          filesStore.getFileItinerary.rooms,
                          [_room.id]
                        ),
                        digits: 2,
                      })
                    }}</span
                  >
                </td>
              </tr>
              <tr v-if="_room.total_children > 0">
                <td>
                  <span class="text-600 text-warning" style="font-size: 12px">
                    $
                    {{
                      formatNumber({
                        number:
                          filesStore.calculatePenalityRoomsCost(filesStore.getFileItinerary.rooms, [
                            _room.id,
                          ]) /
                          (_room.total_children * _room.units.length),
                        digits: 2,
                      })
                    }}
                  </span>
                  <font-awesome-icon icon="fa-solid fa-arrow-right-long" class="mx-2 text-gray" />
                  <span
                    class="d-inline-block"
                    style="
                      color: #c4c4c4;
                      font-size: 12px;
                      border: 1px solid #c4c4c4;
                      border-radius: 6px;
                      padding: 3px;
                    "
                  >
                    $
                    {{
                      formatNumber({
                        number: _room.amount_sale / _room.total_children,
                        digits: 2,
                      })
                    }}
                  </span>
                </td>
                <td>
                  <span class="text-600" style="font-size: 12px">
                    <i class="bi bi-building-fill"></i>
                    {{ showTypeRoom(_room.total_children / _room.total_rooms) }}
                  </span>
                </td>
                <td>
                  <span class="text-600" style="font-size: 12px">CHD</span>
                  <i class="bi bi-person-fill"></i>
                </td>
                <td>
                  <span class="text-600 text-warning" style="font-size: 12px">
                    {{ textPad({ text: _room.total_children, start: 0, length: 2 }) }}
                  </span>
                </td>
                <td>
                  <span class="text-600 text-warning" style="font-size: 12px"
                    >$ {{ formatNumber({ number: _room.amount_cost, digits: 2 }) }}</span
                  >
                </td>
              </tr>
            </template>
            <tr v-if="filesStore.getFileItinerary.rooms.length > 1">
              <td colspan="5" class="text-right">
                <span
                  class="d-inline-block text-600"
                  style="
                    font-size: 12px;
                    border: 1px solid #ffcc00;
                    border-radius: 6px;
                    padding: 3px;
                    color: #3d3d3d;
                  "
                  >$
                  {{
                    formatNumber({
                      number: filesStore.calculatePenalityRoomsCost(
                        filesStore.getFileItinerary.rooms,
                        filesStore.getFileItinerary.rooms.map((room) => room.id)
                      ),
                      digits: 2,
                    })
                  }}</span
                >
              </td>
            </tr>
          </table>
        </div>
      </a-col>
    </a-row>
  </div>

  <a-modal v-model:visible="modalPoliciesCancellation" :width="500">
    <template #title>
      <div class="text-left">
        <b class="text-700" style="font-size: 12px">Política de cancelación</b>
      </div>
    </template>
    <div id="files-layout" style="margin: -20px; margin-top: 0">
      <div class="files-edit m-0 p-0">
        <div class="bg-pink-stick">
          <template v-for="(_room, r) in filesStore.getFileItinerary.rooms">
            <hr v-if="r > 0" />
            <p class="m-0" style="font-size: 12px">
              Room: <b>{{ _room.room_type }}</b>
            </p>
            <p class="m-0" style="font-size: 12px">
              {{ JSON.parse(_room.units[0].policies_cancellation)[0].message }}.
            </p>
          </template>
        </div>
      </div>
    </div>
    <template #footer></template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref, watch } from 'vue';
  import { debounce } from 'lodash-es';
  import { useFilesStore, useExecutivesStore } from '@store/files';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { formatDate, formatNumber, textPad } from '@/utils/files.js';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onChangeSelected', 'onChangeAsumed']);
  const filesStore = useFilesStore();
  const executivesStore = useExecutivesStore();
  const selected = ref([]);
  const modalPoliciesCancellation = ref(false);

  defineProps({
    type: {
      type: String,
      default: () => '',
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const showPoliciesCancellation = () => {
    modalPoliciesCancellation.value = true;
  };

  const showTypeRoom = (_type) => {
    _type = parseFloat(Math.round(_type)).toFixed(0);

    let types = ['', 'Simple', 'Doble', 'Triple'];
    return types[_type];
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

  onBeforeMount(async () => {
    filesStore.initedAsync();
    await executivesStore.fetchAll('');
    await filesStore.fetchAsumedBy();

    handleSelected('hotel', filesStore.getFileItinerary.rooms);

    filesStore.finished();
  });

  const handleSelected = (type, data) => {
    if (type === 'hotel') {
      selected.value = [];

      data.forEach((room) => {
        handleSelected('room', room);
      });
    } else if (type === 'room') {
      data.units.forEach((unit) => {
        let isSelected = selected.value.includes(unit.id);

        if (isSelected) {
          // Quita si ya está seleccionada o si hay que limpiar todas
          selected.value = selected.value.filter((id) => id !== unit.id);
        } else {
          // Agrega si no está seleccionada
          selected.value.push(unit.id);
        }
      });
    } else if (type === 'unit') {
      const isSelected = selected.value.includes(data.id);

      if (isSelected) {
        selected.value = selected.value.filter((id) => id !== data.id);
      } else {
        selected.value.push(data.id);
      }
    }

    validateHandleSelect();
  };

  const validateHandleSelect = () => {
    const { rooms } = filesStore.getFileItinerary;
    const roomPenalty = filesStore.calculatePenalityRoomUnitsSale(rooms, selected.value);
    flag_validate.value = roomPenalty > 0;
    filesStore.calculatePenality('unit', selected.value);
    emit('onChangeSelected', selected.value);
  };

  const flag_validate = ref(false);
  const executive_id = ref('');
  const file_id = ref('');
  const asumed_by = ref('');
  const motive = ref('');

  // Función para emitir cambios de estado
  const emitChange = () => {
    emit('onChangeAsumed', {
      executive_id: executive_id.value,
      file_id: file_id.value,
      flag_validate: flag_validate.value,
      asumed_by: asumed_by.value,
      motive: motive.value,
    });
  };

  // Observa cambios en las referencias
  watch([flag_validate, asumed_by, executive_id, file_id, motive], () => {
    emitChange();
  });

  const validateSelected = (units) => {
    return units.filter((unit) => selected.value.includes(unit.id)).length;
  };
</script>
