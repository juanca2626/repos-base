<template>
  <div class="d-block px-4 mt-3" v-if="props.title && props.type != 'new'">
    Estás a un paso de <b>eliminar</b> la reserva de hotel
    <template v-if="props.type == 'modification'"> y generar una <b>nueva reserva</b></template
    >.
  </div>
  <div class="box-merged">
    <a-row type="flex" align="start" justify="start" class="mb-2 mt-3">
      <a-col :span="props.type == 'cancellation' ? 24 : 11" v-if="props.type != 'new'">
        <div
          v-bind:class="[
            'box-left',
            props.flag_preview ? '' : 'box-bordered p-4',
            props.flag_simulation ? 'my-0' : '',
          ]"
        >
          <template v-if="props.show_communication && type_from != 'modification'">
            <a-row
              align="middle"
              justify="end"
              class="mb-3"
              style="gap: 5px"
              v-if="!props.flag_preview"
            >
              <a-col>
                <span
                  class="mx-2 bordered"
                  v-if="props.show_communication && emailsFrom.length > 0"
                >
                  {{ emailsFrom[0] }}
                </span>
              </a-col>
              <a-col>
                <span
                  class="cursor-pointer text-danger"
                  v-on:click="showModalEmails('from', emailsFrom)"
                >
                  <font-awesome-icon :icon="['far', 'square-plus']" size="lg" />
                </span>
              </a-col>
            </a-row>
          </template>
          <div class="box-title" v-if="!props.flag_preview">
            <a-row type="flex" align="middle" justify="space-between">
              <a-col class="d-flex">
                <a-row type="flex" align="middle" justify="start" style="gap: 7px">
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'hotel']" style="font-size: 1.3rem" />
                  </a-col>
                  <a-col>
                    <span class="ellipsis d-block">
                      <b>{{ from.name }}</b>
                    </span>
                  </a-col>
                  <a-col v-if="from?.category">
                    <a-tag color="#c63838">
                      {{ from?.category }}
                    </a-tag>
                  </a-col>
                </a-row>
              </a-col>
              <a-col
                class="d-flex box-mutted"
                v-if="
                  filesStore.validateConfirmationCode(from) != '' &&
                  filesStore.validateConfirmationCode(from) != null
                "
              >
                Código de confirmación: <b>{{ filesStore.validateConfirmationCode(from) }}</b>
              </a-col>
            </a-row>
          </div>

          <div class="items">
            <template v-for="(room, r) in from.rooms" :key="r">
              <template v-for="(unit, u) in room.units" :key="u">
                <template v-if="selected.length == 0 || selected.indexOf(unit.id) > -1">
                  <div
                    v-bind:class="[
                      'bg-pink-stick',
                      props.flag_preview ? 'p-5 mb-4' : 'p-3 mt-3 mb-4',
                    ]"
                  >
                    <a-row
                      type="flex"
                      align="middle"
                      justify="space-between"
                      class="mb-3"
                      v-if="props.flag_preview"
                    >
                      <a-col class="d-flex ant-row-middle">
                        <big>
                          <font-awesome-icon :icon="['fas', 'hotel']" />
                        </big>
                        <a-tag color="warning" class="mx-2 text-dark-warning text-500">
                          {{ from.object_code }}
                        </a-tag>
                        <big class="mx-2 ellipsis d-block">
                          <b>{{ from.name }}</b>
                        </big>
                        <a-tag color="#c63838" class="mx-2" v-if="from?.category">
                          {{ from?.category }}
                        </a-tag>
                      </a-col>
                      <a-col>
                        <big class="me-1">
                          <i class="bi bi-calendar4"></i>
                        </big>
                        <big
                          ><b>{{ formatDate(from.date_in, 'DD/MM/YYYY') }}</b></big
                        >
                        <big><b class="mx-1" style="font-size: 16px">|</b></big>
                        <big
                          ><b>{{ formatDate(from.date_out, 'DD/MM/YYYY') }}</b></big
                        >
                      </a-col>
                      <a-col
                        class="d-flex box-mutted"
                        v-if="
                          filesStore.validateConfirmationCode(from) != '' &&
                          filesStore.validateConfirmationCode(from) != null
                        "
                      >
                        Código de confirmación:
                        <b>{{ filesStore.validateConfirmationCode(from) }}</b>
                      </a-col>
                    </a-row>
                    <a-row
                      type="flex"
                      align="middle"
                      :class="[props.flag_preview ? 'px-3 pt-2' : '']"
                      :justify="props.type == 'cancellation' ? 'space-between' : 'space-between'"
                      style="gap: 10px"
                    >
                      <a-col class="d-flex" v-if="!props.flag_preview">
                        <span class="me-1">
                          <i class="bi bi-calendar4"></i>
                        </span>
                        <b>{{ formatDate(from.date_in, 'DD/MM/YYYY') }}</b>
                        <b class="mx-1" style="font-size: 16px">|</b>
                        <b>{{ formatDate(from.date_out, 'DD/MM/YYYY') }}</b>
                      </a-col>
                      <template v-if="props.type == 'cancellation'">
                        <a-col class="d-flex" style="gap: 7px">
                          <b>{{ t('global.label.room') }}:</b>
                          <span class="text-uppercase">{{ room.room_type }}</span>
                        </a-col>
                        <template v-if="props.flag_preview">
                          <a-col class="d-flex" style="gap: 7px">
                            <b>{{ t('global.label.nights') }}:</b>
                            <b class="text-danger">{{ room.units[0].nights.length }}</b>
                          </a-col>
                          <a-col class="d-flex" style="gap: 7px">
                            <b class="text-capitalize">{{ t('global.label.units') }}:</b>
                            <b class="text-danger">{{ room.units.length }}</b>
                          </a-col>
                          <a-col class="d-flex" style="gap: 7px">
                            <div class="d-flex align-items-center">
                              <font-awesome-icon
                                :icon="['fas', 'circle-xmark']"
                                size="lg"
                                class="text-danger"
                              />
                              <b class="mx-2">{{ room.rate_plan_name }}</b>
                              <b class="text-danger">$ {{ room.amount_sale }}</b>
                            </div>
                          </a-col>
                        </template>
                        <a-col class="d-flex" style="gap: 7px" v-if="!props.flag_preview">
                          <small class="d-flex ant-row-middle" style="gap: 7px">
                            <b class="text-danger">
                              {{ room.total_adults / room.units.length }}
                              {{ t('global.label.adults') }}
                              <template v-if="room.total_children > 0">
                                {{ room.total_children / room.units.length }}
                                {{ t('global.label.children') }}
                              </template>
                            </b>
                          </small>
                        </a-col>
                      </template>
                    </a-row>
                    <a-row
                      type="flex"
                      align="middle"
                      :class="[props.flag_preview ? 'px-3 pt-2' : '']"
                      :justify="props.type == 'cancellation' ? 'space-between' : 'space-between'"
                      style="gap: 10px"
                    >
                      <a-col class="d-flex my-2" style="gap: 7px">
                        <a-row type="flex" align="middle" style="gap: 7px">
                          <a-col>
                            <span class="d-flex" v-if="unit.penality.penalty_sale > 0">
                              <font-awesome-icon
                                :icon="['fas', 'triangle-exclamation']"
                                size="xl"
                                class="text-warning"
                              />
                            </span>
                            <span class="d-flex" v-else>
                              <font-awesome-icon
                                :icon="['fas', 'circle-check']"
                                size="xl"
                                class="text-success"
                              />
                            </span>
                          </a-col>
                          <a-col>
                            <span
                              v-bind:class="[
                                unit.penality.penalty_sale > 0
                                  ? 'text-dark-warning'
                                  : 'text-success',
                                'text-700',
                              ]"
                            >
                              <template v-if="unit.penality.penalty_sale > 0">
                                {{ t('files.label.cancellation_with_penalty') }}
                              </template>
                              <template v-else>
                                {{ t('files.label.cancellation_without_penalty') }}
                              </template>
                            </span>
                          </a-col>
                          <a-col v-if="unit.penality.penalty_sale > 0">
                            <span
                              v-bind:class="[
                                unit.penality.penalty_sale > 0 ? 'text-warning' : 'text-success',
                                'text-700',
                                'h5',
                                'mb-0',
                              ]"
                            >
                              $
                              {{ formatNumber({ number: unit.penality.penalty_sale, digits: 2 }) }}
                            </span>
                          </a-col>
                        </a-row>
                      </a-col>
                      <a-col
                        class="d-flex"
                        v-if="unit.confirmation_code != '' && unit.confirmation_code != null"
                      >
                        <span
                          v-if="filesStore.validateConfirmationCode(from) == ''"
                          class="box-mutted"
                        >
                          {{ t('files.label.confirmation_code') }}:
                          <b>{{ unit.confirmation_code }}</b>
                        </span>
                      </a-col>
                    </a-row>
                    <div class="mt-2 mx-1" v-if="props.type != 'cancellation'">
                      <a-row type="flex" align="middle" justify="space-between" style="gap: 7px">
                        <a-col style="gap: 7px">
                          <b>{{ t('global.label.room') }}: </b>
                          <small class="text-uppercase text-500">{{ room.room_type }}</small>
                        </a-col>
                        <a-col class="d-flex" style="gap: 7px">
                          <b class="text-danger"
                            >{{ room.total_adults / room.units.length }} adulto(s)
                            {{ room.total_children / room.units.length }} niño(s)</b
                          >
                        </a-col>
                      </a-row>
                    </div>
                  </div>
                </template>
              </template>
            </template>
          </div>

          <template v-if="props.show_communication && type_from != 'modification'">
            <a-row
              align="middle"
              type="flex"
              justify="start"
              style="gap: 5px"
              @click="showNotesFrom = !showNotesFrom"
            >
              <a-col>
                <div
                  v-bind:class="[
                    'cursor-pointer',
                    !showNotesFrom ? 'text-dark-gray' : 'text-danger',
                  ]"
                >
                  <template v-if="showNotesFrom">
                    <i
                      class="bi bi-check-square-fill text-danger d-flex"
                      style="font-size: 1.5rem"
                    ></i>
                  </template>
                  <template v-else>
                    <font-awesome-icon :icon="['far', 'square']" size="xl" />
                  </template>
                </div>
              </a-col>
              <a-col>
                <span class="cursor-pointer">{{ t('global.label.add_note_to_provider') }}</span>
              </a-col>
            </a-row>
            <div class="mb-3" v-if="showNotesFrom">
              <template v-if="lockedNotesFrom">
                <a-card style="width: 100%" class="mt-3" :headStyle="{ background: black }">
                  <template #title> {{ t('global.label.note_in_reserve_to_provider') }}: </template>
                  <template #extra>
                    <a href="javascript:;" @click="lockedNotesFrom = false" class="text-danger">
                      <font-awesome-icon :icon="['fas', 'pencil']" />
                    </a>
                  </template>
                  <p class="mb-2">
                    <b>{{ notesFrom }}</b>
                  </p>
                  <template v-for="(file, f) in filesFrom" :key="f">
                    <a-row align="middle" class="mb-2">
                      <i class="bi bi-paperclip"></i>
                      <a :href="file" target="_blank" class="text-dark mx-1">
                        {{ showName(file) }}
                      </a>
                    </a-row>
                  </template>
                </a-card>
              </template>

              <template v-if="!lockedNotesFrom">
                <p class="text-danger mt-3 mb-2">{{ t('global.label.note_to_provider') }}:</p>
                <a-row align="top" style="gap: 5px" justify="space-between">
                  <a-col flex="auto">
                    <a-textarea
                      v-model:value="notesFrom"
                      :maxlength="100"
                      show-count
                      :placeholder="t('global.label.placeholder_note_to_provider')"
                      :auto-size="{ minRows: 2 }"
                    />
                  </a-col>
                  <a-col class="mx-2">
                    <file-upload
                      v-bind:folder="'communications'"
                      @onResponseFiles="responseFilesFrom"
                    />
                  </a-col>
                  <a-col>
                    <a-button
                      danger
                      type="default"
                      size="large"
                      :disabled="!(notesFrom != '' || filesFrom.length > 0)"
                      class="d-flex ant-row-middle text-600"
                      @click="lockedNotesFrom = true"
                      :loading="communicationsStore.isLoading || communicationsStore.isLoadingAsync"
                    >
                      <font-awesome-icon
                        :icon="['far', 'floppy-disk']"
                        v-if="
                          !(communicationsStore.isLoading || communicationsStore.isLoadingAsync)
                        "
                      />
                    </a-button>
                  </a-col>
                </a-row>
              </template>
            </div>
            <template v-if="lockedNotesFrom || !showNotesFrom">
              <a-row align="middle" type="flex" justify="end" class="mx-2">
                <a-col class="ant-row-end">
                  <a-button
                    danger
                    type="default"
                    size="large"
                    class="d-flex ant-row-middle text-600"
                    @click="showCommunicationFrom()"
                    :loading="communicationsStore.isLoading || communicationsStore.isLoadingAsync"
                  >
                    <i
                      class="bi bi-search"
                      v-if="!(communicationsStore.isLoading || communicationsStore.isLoadingAsync)"
                    ></i>
                    <span class="mx-2">{{ t('global.label.show_communication') }}</span>
                  </a-button>
                </a-col>
              </a-row>
            </template>
          </template>
        </div>
      </a-col>
      <a-col
        :span="2"
        class="text-center merge-icon"
        v-if="props.type != 'new' && props.type != 'cancellation'"
      >
        <i class="bi bi-arrow-right-circle d-block" style="font-size: 4rem; padding-top: 6rem"></i>
      </a-col>
      <a-col :span="props.type == 'new' ? 24 : 11">
        <div
          v-bind:class="['box-left box-bordered p-4', props.flag_simulation ? 'my-0' : '']"
          v-for="(hotel, h) in merged_to"
          :key="h"
        >
          <a-row align="middle" justify="end" class="mb-3" style="gap: 4px">
            <!-- a-col>
              <span>Correo asociado a reservas:</span>
            </a-col -->
            <a-col>
              <span class="mx-2 bordered" v-if="hotel.emails && hotel.emails.length > 0">{{
                hotel.emails[0]
              }}</span>
            </a-col>
            <a-col>
              <span
                class="cursor-pointer text-danger"
                v-on:click="showModalEmails('to', hotel.emails, h)"
              >
                <font-awesome-icon :icon="['far', 'square-plus']" size="lg" />
              </span>
            </a-col>
          </a-row>
          <div class="box-title" v-if="!props.flag_preview">
            <a-row type="flex" align="middle" justify="start" style="gap: 7px">
              <a-col>
                <span>
                  <font-awesome-icon :icon="['fa-solid', 'fa-hotel']" style="font-size: 18px" />
                </span>
              </a-col>
              <a-col>
                <span class="ellipsis d-block">
                  <b class="text-uppercase">{{ hotel.name }}</b>
                </span>
              </a-col>
              <a-col>
                <a-tag :color="hotel.color_class">
                  {{ hotel.class }}
                </a-tag>
              </a-col>
            </a-row>
          </div>

          <div class="items" v-for="(room, r) in hotel.rooms" :key="r">
            <!-- template v-for="index in parseInt(hotel.quantity)" -->
            <div
              v-bind:class="[
                'bg-purple-stick',
                props.flag_preview ? 'p-5' : 'p-3 mt-3 mb-4',
                `hotel-${index}`,
              ]"
              v-for="(rate, r) in room.rates"
              :key="r"
            >
              <a-row type="flex" align="middle" justify="space-between" v-if="props.flag_preview">
                <a-col class="d-flex">
                  <span>
                    <i class="bi bi-building-fill"></i>
                  </span>
                  <span class="mx-2 ellipsis d-block">
                    <b>{{ hotel.name }}</b>
                  </span>
                  <a-tag :color="hotel.color_class">
                    {{ hotel.class }}
                  </a-tag>
                </a-col>
                <a-col>
                  <span class="me-1">
                    <i class="bi bi-calendar4"></i>
                  </span>
                  <b>{{ formatDate(rate.rate[0].amount_days[0].date, 'DD/MM/YYYY') }}</b>
                  <template v-if="rate.rate[0].amount_days.length > 1">
                    <b class="mx-1" style="font-size: 16px">|</b>
                    <b>{{
                      formatDateNight(
                        rate.rate[0].amount_days[rate.rate[0].amount_days.length - 1].date,
                        'DD/MM/YYYY'
                      )
                    }}</b>
                  </template>
                </a-col>
              </a-row>
              <a-row
                type="flex"
                align="middle"
                :justify="props.type == 'cancellation' ? 'start' : 'space-between'"
                style="gap: 10px"
              >
                <a-col class="d-flex" v-if="!props.flag_preview">
                  <span class="me-1">
                    <i class="bi bi-calendar4"></i>
                  </span>
                  <b>{{ formatDate(rate.rate[0].amount_days[0].date, 'DD/MM/YYYY') }}</b>
                  <template v-if="rate.rate[0].amount_days.length > 0">
                    <b class="mx-1" style="font-size: 16px">|</b>
                    <b>{{
                      formatDateNight(
                        rate.rate[0].amount_days[rate.rate[0].amount_days.length - 1].date,
                        'DD/MM/YYYY'
                      )
                    }}</b>
                  </template>
                </a-col>
                <template v-if="props.type == 'new'">
                  <a-col class="d-flex" style="gap: 7px">
                    <small class="d-flex ant-row-middle" style="gap: 7px">
                      <b>Room:</b>
                      <span class="text-uppercase">
                        {{ rate.quantity_room }} {{ room.description }}
                      </span>
                    </small>
                  </a-col>
                  <template v-if="props.flag_preview">
                    <a-col type="flex">
                      <b>{{ t('global.label.nights') }}:</b>
                      <b class="text-danger">{{ room.units[0].nights.length }}</b>
                    </a-col>
                    <a-col type="flex">
                      <b>{{ t('global.label.units') }}:</b>
                      <b class="text-danger">{{ room.units.length }}</b>
                    </a-col>
                    <a-col type="flex">
                      <div class="d-flex align-items-center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 512 512"
                          width="24"
                          height="24"
                          class="svg-danger"
                        >
                          <path
                            d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"
                          />
                        </svg>
                        <b class="mx-2">{{ room.rate_plan_name }}</b>
                        <b class="text-danger">$ {{ room.amount_sale }}</b>
                      </div>
                    </a-col>
                  </template>
                  <a-col class="d-flex" style="gap: 7px">
                    <small class="d-flex ant-row-middle" style="gap: 7px">
                      <b class="text-danger">
                        <template v-for="person in filter.quantity_persons_rooms">
                          <template
                            v-if="
                              person.adults <= room.occupation &&
                              person.adults + person.child === room.occupation
                            "
                          >
                            {{ person.adults * rate.quantity_room }} adulto(s)
                            {{ person.child * rate.quantity_room }} niño(s)
                          </template>
                        </template>
                      </b>
                    </small>
                  </a-col>
                </template>
                <a-col class="d-flex" style="gap: 7px">
                  <template v-if="rate.onRequest === 1">
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      size="lg"
                      class="text-success"
                    />
                  </template>
                  <template v-else>
                    <font-awesome-icon
                      :icon="['fas', 'triangle-exclamation']"
                      size="lg"
                      class="text-warning"
                    />
                  </template>
                  <span class="text-700 text-success" v-if="rate.onRequest == 1"> Confirmada </span>
                  <span class="text-700 text-success">$ {{ rate.total }}</span>
                </a-col>
              </a-row>
              <div class="mt-2 mx-1" v-if="props.type != 'new'">
                <a-row type="flex" align="middle" justify="space-between">
                  <a-col>
                    <b>{{ t('global.label.room') }}: </b>
                    <small class="text-uppercase text-500">
                      {{
                        textPad({
                          text: rate.quantity_room,
                          start: '0',
                          length: 2,
                        })
                      }}
                      {{ room.room_type }}
                    </small>
                  </a-col>
                  <a-col class="d-flex" style="gap: 7px">
                    <b class="text-danger"
                      >{{ room.occupation * rate.quantity_room }} adulto(s) 0 niño(s)</b
                    >
                  </a-col>
                </a-row>
              </div>
            </div>
            <!-- /template -->
          </div>

          <template v-if="props.show_communication">
            <a-row align="middle" type="flex" justify="start" style="gap: 5px">
              <a-col>
                <div
                  v-bind:class="[
                    'cursor-pointer',
                    !hotel.showNotesTo ? 'text-dark-gray' : 'text-danger',
                  ]"
                  @click="hotel.showNotesTo = !hotel.showNotesTo"
                >
                  <template v-if="hotel.showNotesTo">
                    <i
                      class="bi bi-check-square-fill text-danger d-flex"
                      style="font-size: 1.5rem"
                    ></i>
                  </template>
                  <template v-else>
                    <font-awesome-icon :icon="['far', 'square']" size="xl" />
                  </template>
                </div>
              </a-col>
              <a-col>
                <span class="cursor-pointer" @click="hotel.showNotesTo = !hotel.showNotesTo">{{
                  t('global.label.add_note_to_provider')
                }}</span>
              </a-col>
            </a-row>

            <div class="mb-3" v-if="hotel.showNotesTo">
              <template v-if="hotel.lockedNotesTo">
                <a-card style="width: 100%" class="mt-3" :headStyle="{ background: black }">
                  <template #title> {{ t('global.label.note_in_reserve_to_provider') }}: </template>
                  <template #extra>
                    <a href="javascript:;" @click="hotel.lockedNotesTo = false" class="text-danger">
                      <font-awesome-icon :icon="['fas', 'pencil']" />
                    </a>
                  </template>
                  <p class="mb-2">
                    <b>{{ hotel.notesTo }}</b>
                  </p>
                  <template v-for="(file, f) in hotel.filesTo" :key="f">
                    <a-row align="middle" class="mb-2">
                      <i class="bi bi-paperclip"></i>
                      <a :href="file" target="_blank" class="text-dark mx-1">
                        {{ showName(file) }}
                      </a>
                    </a-row>
                  </template>
                </a-card>
              </template>

              <template v-if="!hotel.lockedNotesTo">
                <p class="text-danger mt-3 mb-2">{{ t('global.label.note_to_provider') }}:</p>
                <a-row align="top" style="gap: 5px" justify="space-between">
                  <a-col flex="auto">
                    <a-textarea
                      v-model:value="hotel.notesTo"
                      :maxlength="100"
                      show-count
                      :placeholder="t('global.label.placeholder_note_to_provider')"
                      :auto-size="{ minRows: 2 }"
                    />
                  </a-col>
                  <a-col class="mx-2">
                    <file-upload
                      v-bind:folder="'communications'"
                      @onResponseFiles="responseFilesTo($event, hotel)"
                    />
                  </a-col>
                  <a-col>
                    <a-button
                      danger
                      type="default"
                      size="large"
                      :disabled="!(hotel.notesTo != '' || hotel?.filesTo?.length > 0)"
                      class="d-flex ant-row-middle text-600"
                      @click="hotel.lockedNotesTo = true"
                      :loading="communicationsStore.isLoading || communicationsStore.isLoadingAsync"
                    >
                      <font-awesome-icon
                        :icon="['far', 'floppy-disk']"
                        v-if="
                          !(communicationsStore.isLoading || communicationsStore.isLoadingAsync)
                        "
                      />
                    </a-button>
                  </a-col>
                </a-row>
              </template>
            </div>

            <template v-if="hotel.lockedNotesTo || !hotel.showNotesTo">
              <a-row align="middle" type="flex" justify="end" class="mx-2">
                <a-col class="ant-row-end">
                  <a-button
                    danger
                    type="default"
                    size="large"
                    class="d-flex ant-row-middle text-600"
                    @click="showCommunicationTo(h)"
                    :loading="communicationsStore.isLoading || communicationsStore.isLoadingAsync"
                  >
                    <i
                      class="bi bi-search"
                      v-if="!(communicationsStore.isLoading || communicationsStore.isLoadingAsync)"
                    ></i>
                    <span class="mx-2">{{ t('global.label.show_communication') }}</span>
                  </a-button>
                </a-col>
              </a-row>
            </template>
          </template>
        </div>
      </a-col>
    </a-row>
  </div>

  <div class="box-buttons" v-if="props.buttons">
    <a-row type="flex" justify="end" align="middle">
      <a-col>
        <a-button
          type="default"
          class="mx-2 px-4 text-600"
          v-on:click="prevStep()"
          default
          :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.back') }}
        </a-button>
        <a-button
          type="primary"
          class="mx-2 px-4 text-600"
          v-if="selected.length > 0 || type == 'new'"
          v-on:click="processReservation(false)"
          default
          :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          <template v-if="type == 'cancellation'">{{ t('global.button.continue') }}</template>
          <template v-else>{{ t('global.button.reserve') }}</template>
        </a-button>
      </a-col>
    </a-row>
  </div>

  <a-modal v-model:open="modal.open" :width="800" :closable="true" :maskClosable="false">
    <template #title></template>
    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            @click="closeModal"
            >Cerrar</a-button
          >
        </a-col>
      </a-row>
    </template>
    <div>
      <IframeHTML :html="modal.html" />
    </div>
  </a-modal>

  <a-modal v-model:open="flagModalEmails" :width="720" :closable="false" :maskClosable="false">
    <template #title>
      <span class="text-center">Correos adicionales para solicitud de reserva:</span>
    </template>
    <a-form layout="vertical">
      <a-form-item label="Agregar correos adicionales">
        <a-select
          :not-found-content="null"
          :options="[]"
          v-model:value="emails"
          mode="tags"
          style="width: 100%"
          placeholder="Agregar correos adicionales"
        ></a-select>
      </a-form-item>
    </a-form>
    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button
            key="button"
            type="default"
            default
            size="large"
            class="text-600"
            @click="closeModalEmails"
            >{{ t('global.button.cancel') }}</a-button
          >
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            @click="handleChangeEmails"
            >{{ t('global.button.save') }}</a-button
          >
        </a-col>
      </a-row>
    </template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import IframeHTML from '@/components/files/reusables/IframeHTML.vue';
  import { useFilesStore } from '@store/files';
  import { useCommunicationsStore } from '@/stores/global';
  import { formatDate, formatDateNight, formatNumber, textPad } from '@/utils/files.js';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  import { notification } from 'ant-design-vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const merged_to = ref([]);

  const emit = defineEmits(['onPrevStep', 'onNextStep', 'onLoadReservation']);

  const props = defineProps({
    buttons: {
      type: Boolean,
      default: () => true,
    },
    show_communication: {
      type: Boolean,
      default: () => true,
    },
    title: {
      type: Boolean,
      default: () => true,
    },
    from: {
      type: Object,
      default: () => null,
    },
    to: {
      type: Object,
      default: () => null,
    },
    selected: {
      type: Array,
      default: () => [],
    },
    date_from: {
      type: String,
      default: () => '',
    },
    date_to: {
      type: String,
      default: () => '',
    },
    type: {
      type: String,
      default: () => '',
    },
    filter: {
      type: Object,
      default: () => {},
    },
    flag_simulation: {
      type: Boolean,
      default: () => false,
    },
    flag_preview: {
      type: Boolean,
      default: () => false,
    },
  });

  onBeforeMount(async () => {
    console.log('PROPS: ', props);

    if (props.to) {
      let merged = [];

      for (const item of props.to) {
        const existingItinerary = merged.find((m) => m.id === item.id);

        if (!existingItinerary) {
          // Clonar item y sus habitaciones
          const clonedRooms = item.rooms.map((room) => ({
            ...room,
            rates: [...room.rates],
          }));

          merged.push({
            ...item,
            rooms: clonedRooms,
          });
        } else {
          for (const room of item.rooms) {
            const existingRoom = existingItinerary.rooms.find((r) => r.room_id === room.room_id);

            if (!existingRoom) {
              // Nueva habitación → agregar clonada
              existingItinerary.rooms.push({
                ...room,
                rates: [...room.rates],
              });
            } else {
              // Fusionar rates únicos
              const combinedRates = [...existingRoom.rates, ...room.rates];
              existingRoom.rates = Array.from(new Set(combinedRates));
            }
          }
        }
      }

      console.log(merged);
      merged_to.value = merged;
    }

    if (props.type == 'new') {
      type_from.value = '';
      type_to.value = 'new';
    }

    if (props.type == 'modification') {
      if (props.from.object_code != merged_to.value[0].code) {
        // Es son hoteles diferentes.. update
        type_from.value = 'cancellation';
        type_to.value = 'new';
      } else {
        type_from.value = 'modification';
        type_to.value = '';
      }
    }

    if (props.type == 'cancellation') {
      type_from.value = 'cancellation';
      type_to.value = '';
    }

    filter.value = props.filter ? props.filter : filesStore.getSearchParametersHotels;
    client_id.value = localStorage.getItem('client_id');

    if (props.from != undefined && props.from.object_code != undefined) {
      await searchProvidersFrom(props.from.object_code);
    }

    if (merged_to.value != undefined) {
      merged_to.value.forEach(async (hotel) => {
        hotel.emails = [];
        await searchProvidersTo(hotel.code, hotel);
      });
    }
  });

  const filesStore = useFilesStore();
  const communicationsStore = useCommunicationsStore();

  const filter = ref({});
  const type_from = ref('');
  const type_to = ref('');

  const flagModalEmails = ref(false);
  const emailsFrom = ref([]);

  const showNotesFrom = ref(false);
  const lockedNotesFrom = ref(false);

  const notesFrom = ref('');
  const filesFrom = ref([]);

  const client_id = ref('');

  const emails = ref([]);
  const emailsType = ref('');
  const emailsHotel = ref(0);

  const modal = ref({
    open: false,
    html: '',
    subject: '',
  });

  const showCommunicationFrom = async () => {
    const filteredRooms = props.from.rooms
      .map((room) => {
        const filteredUnits = room.units
          .filter((unit) => props.selected.length === 0 || props.selected.includes(unit.id))
          .map((unit) => unit.id);

        return filteredUnits.length > 0 ? { id: room.id, units: filteredUnits } : null;
      })
      .filter(Boolean); // Eliminar entradas `null`;

    if (filteredRooms.length == 0) {
      return;
    }

    let params = {
      rooms: filteredRooms,
      notas: notesFrom.value || '',
      attachments: filesFrom.value || [],
    };

    await communicationsStore.previewCommunication(
      'itineraries/' + props.from.id,
      params,
      'hotel',
      type_from.value
    );

    modal.value.html = communicationsStore.getHotelHtml;
    modal.value.subject = communicationsStore.getSubject;
    modal.value.open = true;
  };

  const showCommunicationTo = async (_index) => {
    let rates = [];
    let hotel = merged_to.value[_index];

    const filteredRooms = (props.from.rooms ?? [])
      .map((room) => {
        const filteredUnits = room.units
          .filter((unit) => props.selected.length === 0 || props.selected.includes(unit.id))
          .map((unit) => unit.id);

        return filteredUnits.length > 0 ? { id: room.id, units: filteredUnits } : null;
      })
      .filter(Boolean);

    hotel.rooms.forEach((room) => {
      const _passengers = hotel.passengers;
      let ignore = [];

      room.rates.forEach((rate) => {
        const quantity = parseInt(rate.quantity_room) || 0;
        let passengers = [];

        for (let i = 1; i <= quantity; i++) {
          for (const passenger of _passengers) {
            if (!ignore.includes(passenger.id) && passengers.length < quantity * room.occupation) {
              passengers.push(passenger);
              ignore.push(passenger.id);
            }
          }

          rates.push({
            token_search: hotel.token_search,
            hotel_id: hotel.id,
            best_option: hotel.best_option_taken,
            rate_plan_room_id: rate.rateId,
            guest_note: null,
            date_from: filter.value.date_from,
            date_to: filter.value.date_to,
            quantity_adults: room.occupation,
            quantity_child: 0,
            child_ages: filter.value.quantity_persons_rooms[0].ages_child,
            passengers: passengers,
          });
        }
      });
    });

    let params = {
      reservation_add: {
        client_id: client_id.value,
        file_code: filesStore.getFile.fileNumber,
        reference: '',
        send_mail: 0,
        guests: hotel.passengers,
        reservations: rates,
        entity: 'Cart',
        object_id: null,
      },
      reservation_delete: filteredRooms ?? [],
      notas: hotel.notesTo || '',
      attachments: hotel.filesTo || [],
    };

    const url =
      type_from.value == 'modification' ? `itineraries/${props.from.id}` : filesStore.getFile.id;
    const type_communication = type_from.value == 'modification' ? 'modification' : type_to.value;

    await communicationsStore.previewCommunication(url, params, 'hotel', type_communication);

    if (communicationsStore.getError === '') {
      modal.value.html = communicationsStore.getHotelHtml;
      modal.value.subject = communicationsStore.getSubject;
      modal.value.open = true;
    } else {
      notification.error({
        message: 'Error de Consulta',
        description: communicationsStore.getError,
      });
    }
  };

  const searchProvidersFrom = async (_object_code) => {
    await filesStore.fetchProviders(_object_code);
    const emails = filesStore.getProvider.contacts.map((contact) => contact.email);
    emailsFrom.value = emails || [];
  };

  const searchProvidersTo = async (_object_code, hotel) => {
    await filesStore.fetchProviders(_object_code);
    const emails = filesStore.getProvider.contacts.map((contact) => contact.email);
    hotel.emails = emails || [];
  };

  const closeModal = () => {
    modal.value.open = false;
  };

  const responseFilesFrom = (files) => {
    filesFrom.value = files.map((item) => item.link);
  };

  const responseFilesTo = (files, hotel) => {
    hotel.filesTo = files.map((item) => item.link);
  };

  const showName = (file) => {
    let parts = file.split('/').splice(-1);
    return parts[0];
  };

  const showModalEmails = (type, _emails, hotel) => {
    emails.value = _emails;
    emailsType.value = type;
    emailsHotel.value = hotel;
    flagModalEmails.value = true;
  };

  const closeModalEmails = () => {
    flagModalEmails.value = false;
  };

  const processReservation = async (flag_return) => {
    let cancellation = {};
    let reservation = {};
    let response = {};

    if (props.type != 'new') {
      let rooms = [];

      props.from.rooms.forEach((room) => {
        let units = [];

        room.units.forEach((unit) => {
          if (props.selected.indexOf(unit.id) > -1 || props.selected.length === 0) {
            units.push(unit.id);
          }
        });

        if (units.length > 0) {
          rooms.push({
            id: room.id,
            units: units,
          });
        }
      });

      cancellation = {
        file_number: filesStore.getFile.fileNumber,
        itinerary_id: props.from.id,
        confirmation: props.from.confirmation_status,
        rooms: rooms,
        notas: notesFrom.value,
        attachments: filesFrom.value,
        emails: emailsFrom.value,
        type_from: type_from.value,
      };

      if (props.type === 'cancellation') {
        cancellation = {
          ...cancellation,
          type: 'hotel',
          flag_email: 'cancellation',
          file_id: filesStore.getFile.id,
        };

        response = {
          reservation: props,
          params: cancellation,
          reservation_merged: merged_to.value,
        };

        if (typeof flag_return == 'undefined' || !flag_return) {
          emit('onLoadReservation', response);
        }
      }
    }

    if (props.type != 'cancellation') {
      let index = 0;
      let rates = [];
      let ignore = [];

      for (const hotel of merged_to.value) {
        hotel.rooms.map((room) => {
          const _passengers = hotel.passengers;

          for (const rate of room.rates) {
            const quantity = parseInt(rate.quantity_room) || 0;
            let passengers = [];

            for (let i = 1; i <= quantity; i++) {
              for (const passenger of _passengers) {
                if (
                  !ignore.includes(passenger.id) &&
                  passengers.length < quantity * room.occupation
                ) {
                  passengers.push(passenger);
                  ignore.push(passenger.id);
                }
              }

              rates.push({
                token_search: hotel.token_search,
                hotel_id: hotel.id,
                best_option: hotel.best_option_taken,
                rate_plan_room_id: rate.rateId,
                guest_note: hotel.notesTo || '',
                date_from: filter.value.date_from,
                date_to: filter.value.date_to,
                quantity_adults: room.occupation,
                quantity_child: 0,
                passengers: passengers,
                child_ages: filter.value.quantity_persons_rooms[0].ages_child,
                emails: hotel.emails || [],
                notas: hotel.notesTo || '',
                attachments: hotel.filesTo || [],
                code: hotel.code,
              });
            }
          }
        });
      }

      const originalPassengers = filesStore.getFilePassengers;
      const passengers = originalPassengers.map((p) => ({
        ...p,
        states: null,
        date_birth:
          p.date_birth && p.date_birth !== '0000-00-00'
            ? dayjs(p.date_birth, 'DD/MM/YYYY').format('YYYY-MM-DD')
            : '',
      }));

      reservation = {
        client_id: client_id.value,
        file_code: filesStore.getFile.fileNumber,
        reference: null,
        send_mail: 0,
        guests: passengers,
        reservations: rates,
      };

      let params = {
        type: 'hotel',
        lang: localStorage.getItem('lang'),
        flag_email:
          index > 0 && props.type == 'modification'
            ? 'new'
            : type_from.value === 'modification'
              ? props.type
              : 'new',
        reservation_add: reservation,
        file_id: filesStore.getFile.id,
        file_itinerary_id: props.from?.id ?? null,
        cancellation: cancellation,
        file_number: filesStore.getFile.fileNumber,
      };

      response = {
        reservation: props,
        params: params,
        reservation_merged: merged_to.value,
      };

      if (typeof flag_return == 'undefined' || !flag_return) {
        emit('onLoadReservation', response);
      }
    }

    if (flag_return) {
      return response;
    }
  };

  const prevStep = () => {
    emit('onPrevStep');
  };

  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleChangeEmails = async () => {
    for (const email of emails.value) {
      if (!isValidEmail(email)) {
        notification.error({
          message: 'E-mail incorrecto',
          description: `El correo electrónico ingresado "${email}" no es válido.`,
        });

        return; // Detenemos la ejecución completa de la función
      }
    }

    if (emailsType.value == 'from') {
      emailsFrom.value = emails.value;
    }

    if (emailsType.value == 'to') {
      merged_to.value[emailsHotel.value].emails = emails.value;
    }

    flagModalEmails.value = false;
  };

  defineExpose({
    processReservation,
  });
</script>
