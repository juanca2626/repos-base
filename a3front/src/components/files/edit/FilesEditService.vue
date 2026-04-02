<template>
  <div
    style="position: relative"
    :data-id="`${itinerary.id}`"
    v-if="
      itinerary.status ||
      itinerary.total_amount > 0 ||
      socketsStore.getNotifications.some(
        (item) => item?.itinerary_id === itinerary.id && item.action === 'delete'
      )
    "
    class="spin-white"
  >
    <a-badge-ribbon
      :color="itinerary.isNew || itinerary.isUpdated ? '#EB5757' : '#737373'"
      class="cursor-pointer"
      style="position: absolute; z-index: 999; top: -7px; padding: 3px 5px"
      v-if="
        itinerary.isNew ||
        itinerary.isUpdated ||
        (itinerary.isError &&
          socketsStore.getNotifications.filter((item) => item.itinerary_id === itinerary.id)
            .length > 0)
      "
      @click="toggleViewStatus()"
    >
      <template #text>
        <span>
          <a-popover
            placement="topRight"
            v-if="
              socketsStore.getNotifications.filter(
                (item) => item.itinerary_id === itinerary.id && item.flag_show
              ).length > 0
            "
          >
            <template #title>
              <small class="text-uppercase"
                ><font-awesome-icon :icon="['fas', 'arrows-rotate']" spin />
                {{ t('global.message.itinerary_updated') }}
                <a-tag color="red" class="ms-1">{{ itinerary.object_code }}</a-tag></small
              >
            </template>
            <template #content>
              <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                <template
                  v-for="item in socketsStore.getNotifications.filter(
                    (item) => item.itinerary_id === itinerary.id && item.flag_show
                  )"
                >
                  <p class="mb-0">
                    <font-awesome-icon
                      :icon="['far', item.success ? 'thumbs-up' : 'thumbs-down']"
                      :class="item.success ? 'text-success' : 'text-danger'"
                    />
                    {{ truncateString(t(item.description), 90) }}
                    <small v-if="!item.success" class="text-600 text-uppercase"
                      >({{ t(item.message) }})</small
                    >
                  </p>
                  <p class="mb-0 text-dark-gray" style="font-size: 0.9rem">
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <a-col>
                        <small v-if="item.user_code">
                          <font-awesome-icon :icon="['far', 'circle-user']" class="me-1" />
                          <b>{{ item.user_code }}</b>
                        </small>
                        <small v-else>
                          <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                          <b>Aurora BOT</b>
                        </small>
                      </a-col>
                      <a-col>
                        <small v-if="item?.date && item?.time">
                          <font-awesome-icon :icon="['far', 'clock']" class="me-1" />
                          <b>{{ formatDate(item.date) }} {{ item.time }}</b>
                        </small>
                      </a-col>
                    </a-row>
                  </p>
                </template>
              </div>
            </template>
            <font-awesome-icon :icon="['fas', 'circle-question']" fade />
          </a-popover>
          <template v-else>
            <span v-if="itinerary.isNew">
              <font-awesome-icon :icon="['fas', 'bolt']" fade />
            </span>
            <span v-else>
              <a-popover placement="topRight">
                <template #title>
                  <small class="text-uppercase"
                    ><font-awesome-icon :icon="['fas', 'arrows-rotate']" spin />
                    {{ t('global.message.itinerary_updated') }}
                    <a-tag color="red" class="ms-1">{{ itinerary.object_code }}</a-tag></small
                  >
                </template>
                <template #content>
                  <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                    <p class="mb-0">
                      <font-awesome-icon :icon="['far', 'thumbs-up']" :class="'text-success'" />
                      {{ t('files.notification.itinerary_update_remote') }}.
                    </p>
                    <p class="mb-0 text-dark-gray" style="font-size: 0.9rem">
                      <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                        <a-col>
                          <small>
                            <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                            <b>Aurora BOT</b>
                          </small>
                        </a-col>
                        <a-col> </a-col>
                      </a-row>
                    </p>
                  </div>
                </template>
                <font-awesome-icon :icon="['fas', 'bullseye']" fade />
              </a-popover>
            </span>
          </template>
        </span>
      </template>
    </a-badge-ribbon>

    <a-spin
      :spinning="loading || itinerary.isLoading"
      :size="!(toggleServices && !itinerary.flag_show) ? 'large' : ''"
      style="margin-top: -0.4rem"
    >
      <template #indicator>
        <a-tooltip>
          <template #title>
            <small class="text-uppercase">{{ t('files.message.loading') }}...</small>
          </template>
          <LoadingMaca size="small" v-if="loading || itinerary.isLoading" />
        </a-tooltip>
      </template>
      <a-row type="flex" justify="space-between" align="top">
        <a-col flex="auto">
          <div
            v-bind:class="[
              'files-edit__service',
              itinerary.status ||
              socketsStore.getNotifications.some(
                (item) => item?.itinerary_id === itinerary.id && item.action === 'delete'
              )
                ? ''
                : 'files-edit__service--cancel',
            ]"
            :style="`${!isLockedADMIN(itinerary.entity) ? 'min-height:auto!important;' : ''} ${itinerary.isNew || itinerary.isUpdated ? 'border-color: #EB5757;' : ''} ${itinerary.isError ? 'border-color: #737373;' : ''}`"
            :class="{
              'files-edit__service--compact': toggleServices && !itinerary.flag_show,
            }"
          >
            <div
              class="locked"
              v-if="
                !itinerary.status ||
                filesStore.getFile.status === 'ce' ||
                filesStore.getFile.status === 'bl' ||
                filesStore.getFile.status === 'xl'
              "
            ></div>
            <template v-if="true">
              <div
                class="files-edit__service-row-1"
                :class="{ 'files-edit__service-row-1-in-ope': itinerary.is_in_ope }"
                v-show="!toggleServices"
              >
                <div class="files-edit__service-header">
                  <files-edit-field-static :inline="true" :highlighted="true">
                    <template #label>
                      <font-awesome-icon icon="fa-regular fa-calendar" size="xl" />
                      {{ t('global.label.start') }}:
                    </template>
                    <template #content>
                      <span style="text-transform: capitalize">
                        {{ formatDate(itinerary.date_in, 'MMM DD, YYYY', locale) }}
                      </span>
                    </template>
                    <template #popover-content>&nbsp;</template>
                  </files-edit-field-static>

                  <files-edit-field-static
                    :inline="true"
                    :highlighted="true"
                    v-if="itinerary.entity == 'hotel'"
                  >
                    <template #label>
                      <font-awesome-icon icon="fa-regular fa-calendar" size="xl" />
                      {{ t('global.label.end') }}:
                    </template>
                    <template #content>
                      <span style="text-transform: capitalize">
                        {{ formatDate(itinerary.date_out, 'MMM DD, YYYY', locale) }}
                      </span>
                    </template>
                    <template #popover-content>&nbsp;</template>
                  </files-edit-field-static>

                  <files-edit-field-static :inline="true">
                    <template #label>
                      <span class="text-uppercase">{{ t('global.label.day') }}</span>
                    </template>
                    <template #content
                      >{{ checkDates(filesStore.getFile.dateIn, itinerary.date_in) }}
                    </template>
                    <template #popover-content>&nbsp;</template>
                  </files-edit-field-static>

                  <files-edit-field-divider style="margin-top: 2px" />

                  <files-edit-field-static :inline="true">
                    <template #content>
                      <span style="text-transform: uppercase">
                        {{ getWeekDay(itinerary.date_in, locale) }}
                      </span>
                    </template>
                  </files-edit-field-static>

                  <files-edit-field-divider style="margin-top: 2px" />

                  <files-edit-field-static :inline="true">
                    <template #label>
                      <span class="text-uppercase">
                        {{ itinerary.city_in_name }}
                      </span>
                    </template>
                    <template #popover-content>&nbsp;</template>
                  </files-edit-field-static>

                  <template v-if="itinerary.hyperguest">
                    <hyperguest-icon :text="itinerary.rooms[0].rate_plan_name ?? ''" />
                  </template>

                  <template v-if="itinerary.entity === 'flight'">
                    <svg
                      v-if="itinerary.flights_completed"
                      style="color: #1ed790; margin-top: 2px"
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      class="feather feather-check-circle"
                      viewBox="0 0 24 24"
                    >
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                      <path d="M22 4 12 14.01l-3-3" />
                    </svg>
                    <template v-else>
                      <font-awesome-icon
                        :icon="['fas', 'triangle-exclamation']"
                        class="text-warning"
                        size="xl"
                      />
                    </template>
                  </template>
                </div>

                <div style="display: flex; gap: 20px">
                  <files-toggler-service-passengers
                    v-if="!loading"
                    @onRefreshItineraryCache="handleRefreshItineraryCache"
                    :data="{
                      adl: itinerary.adults,
                      chd: itinerary.children,
                      passengers: passengers,
                      format: 'general',
                      type: 'itinerary',
                      object_id: itinerary.id,
                      itinerary: itinerary,
                    }"
                  />
                  <template v-if="itinerary.status && itinerary?.category">
                    <a-tag
                      color="#c63838"
                      v-if="
                        itinerary.entity == 'service' ||
                        itinerary.entity == 'hotel' ||
                        itinerary.entity == 'service-temporary' ||
                        itinerary.entity == 'service-mask'
                      "
                    >
                      <small class="text-uppercase">{{ itinerary.category }}</small>
                    </a-tag>
                  </template>
                  <span class="text-danger text-600 z-100" v-if="!itinerary.status"
                    >{{ t('files.label.cancelled_service') }}
                    <span class="text-lowercase">
                      <template v-if="itinerary.total_amount > 0">{{
                        t('global.label.with')
                      }}</template
                      ><template v-else>{{ t('global.label.without') }}</template>
                      {{ t('files.label.penalty') }}
                    </span>
                  </span>
                  <template v-if="itinerary.status">
                    <files-toggler-service-log
                      :data="{ itinerary: itinerary }"
                      v-if="
                        itinerary.entity == 'hotel' ||
                        itinerary.entity == 'service' ||
                        itinerary.entity == 'flight' ||
                        itinerary.entity == 'service-temporary' ||
                        itinerary.entity == 'service-mask'
                      "
                    />
                  </template>
                </div>
              </div>

              <div
                class=""
                :class="{
                  'files-edit__service-row-2': itinerary?.entity !== 'service-temporary',
                  'files-edit__service-row-2-service-temporary':
                    itinerary?.entity === 'service-temporary',
                  'files-edit__service-row-2-service-mask': itinerary?.entity === 'service-mask',
                  'files-edit__service-row-2--opened': itinerary.flag_show,
                  'files-edit__service-row-2-in-ope': itinerary.is_in_ope,
                  'pb-2':
                    itinerary.flag_show &&
                    (itinerary?.show_master_services === false || itinerary?.entity === 'flight'),
                }"
              >
                <template v-if="itinerary.isCompleted">
                  <a-row justify="space-between" align="middle">
                    <a-col>
                      <a-row justify="space-between" align="middle" style="gap: 5px">
                        <a-col>
                          <span
                            class="me-2 cursor-pointer text-dark-gray"
                            v-on:click="
                              itinerary.entity != 'flight'
                                ? filesStore.updateItineraryTrash(itinerary)
                                : undefined
                            "
                          >
                            <template
                              v-if="
                                filesStore.getItinerariesTrash
                                  .map((itinerary) => itinerary.id)
                                  .indexOf(itinerary.id) > -1
                              "
                            >
                              <font-awesome-icon
                                :icon="['fas', 'square-check']"
                                size="lg"
                                class="text-danger"
                              />
                            </template>
                            <template v-else>
                              <font-awesome-icon
                                :icon="[
                                  'far',
                                  itinerary.entity === 'flight' ? 'square-minus' : 'square',
                                ]"
                                size="lg"
                              />
                            </template>
                          </span>
                        </a-col>
                        <template v-if="itinerary.protected_rate">
                          <a-col>
                            <a-tooltip>
                              <template #title>
                                <small class="text-uppercase">{{
                                  t('files.label.protected_rate')
                                }}</small>
                              </template>
                              <font-awesome-icon
                                :icon="['fas', 'shield-halved']"
                                class="text-dark-warning me-1 cursor-pointer"
                                size="lg"
                                beat
                              />
                            </a-tooltip>
                          </a-col>
                        </template>
                        <a-col style="display: flex; align-items: center">
                          <template v-if="parseInt(itinerary.sent_to_ope) === 2">
                            <template v-if="!itinerary.is_in_ope">
                              <a-tooltip>
                                <template #title>
                                  <small class="text-uppercase">{{
                                    t('global.label.sent_to_ope')
                                  }}</small>
                                </template>
                                <span class="text-dark-gray cursor-pointer me-2">
                                  <font-awesome-icon :icon="['far', 'circle-dot']" size="lg" />
                                </span>
                              </a-tooltip>
                            </template>
                            <template v-else>
                              <a-tooltip>
                                <template #title>
                                  <small class="text-uppercase">{{
                                    t('global.label.programmed')
                                  }}</small>
                                </template>
                                <span class="text-dark-gray cursor-pointer me-2">
                                  <font-awesome-icon :icon="['fas', 'check-double']" size="lg" />
                                </span>
                              </a-tooltip>
                            </template>
                          </template>
                          <template v-else>
                            <template v-if="itinerary.is_programmable">
                              <a-tooltip placement="left">
                                <template #title>
                                  <small class="text-uppercase">{{
                                    t('global.label.programmable')
                                  }}</small>
                                </template>
                                <span class="text-dark-gray cursor-pointer me-2">
                                  <requirements-ope :itinerary="itinerary" />
                                  <!-- font-awesome-icon :icon="['fas', 'list-check']" size="lg" shake / -->
                                </span>
                              </a-tooltip>
                            </template>
                          </template>
                          <files-edit-field-static
                            :inline="true"
                            :highlighted="true"
                            v-show="toggleServices"
                          >
                            <template #label>
                              <span>
                                <font-awesome-icon :icon="['far', 'calendar']" size="lg" />
                              </span>
                            </template>
                            <template #content>
                              <template
                                v-if="
                                  itinerary.entity === 'flight' &&
                                  !itinerary.flights_completed &&
                                  isEditable
                                "
                              >
                                <span
                                  class="me-2 text-danger cursor-pointer"
                                  v-if="!activeSelectDate"
                                  @click="clickActiveSelectDate"
                                >
                                  <a-tooltip>
                                    <template #title>
                                      <small class="text-uppercase">{{
                                        t('files.label.modify_date')
                                      }}</small>
                                    </template>
                                    {{ formatDate(itinerary.date_in, 'DD/MM/YYYY', locale) }}
                                  </a-tooltip>
                                </span>
                                <template v-else>
                                  <a-date-picker
                                    class="me-2"
                                    v-model:value="itinerary.new_date_in"
                                    format="DD/MM/YYYY"
                                    :allowClear="false"
                                    :disabledDate="disabledDate"
                                    @blur="handleSelectDateBlur(false)"
                                    @change="handleSelectDateBlur(true)"
                                  />
                                </template>
                              </template>
                              <template v-else>
                                <span class="me-2">{{
                                  formatDate(itinerary.date_in, 'DD/MM/YYYY', locale)
                                }}</span>
                              </template>
                            </template>
                            <template #popover-content>&nbsp;</template>
                          </files-edit-field-static>
                        </a-col>
                        <a-col style="display: flex; align-items: center">
                          <font-awesome-icon
                            :icon="['fas', 'hotel']"
                            size="lg"
                            class="text-dark"
                            v-if="itinerary.entity == 'hotel'"
                          />
                          <template
                            v-if="
                              itinerary.entity == 'service' ||
                              itinerary.entity == 'service-temporary' ||
                              itinerary.entity == 'service-mask'
                            "
                          >
                            <template v-if="itinerary.entity != 'service-mask'">
                              <font-awesome-icon
                                :icon="
                                  filesStore.showServiceIcon(
                                    itinerary.service_category_id,
                                    itinerary.service_sub_category_id,
                                    itinerary.service_type_id
                                  )
                                "
                                class="text-dark"
                                size="lg"
                              />
                            </template>
                            <template v-else>
                              <font-awesome-icon
                                :icon="['fas', 'cube']"
                                size="lg"
                                class="text-dark"
                              />
                            </template>
                            <font-awesome-icon
                              :icon="['fas', 'gift']"
                              class="mx-2 xy-2 text-dark"
                              size="lg"
                              v-if="itinerary.entity === 'service-mask'"
                            />
                          </template>
                          <font-awesome-icon
                            :icon="['fas', 'plane']"
                            class="text-dark"
                            size="lg"
                            v-if="itinerary.entity === 'flight'"
                          />
                        </a-col>
                        <template v-if="itinerary.entity == 'flight'">
                          <a-col>
                            <files-edit-field-static :inline="true" :hide-content="true">
                              <template #label>
                                <span
                                  class="mx-2 text-capitalize"
                                  style="
                                    font-weight: 400;
                                    font-size: 15px;
                                    letter-spacing: -0.015em;
                                    cursor: pointer;
                                  "
                                >
                                  <template v-if="itinerary.object_code.indexOf('AEI') > -1">
                                    {{ t('files.button.flight') }}
                                    {{ t('global.label.international') }}
                                    <span style="margin-left: 16px">|</span>
                                  </template>
                                  <template v-if="itinerary.object_code.indexOf('AEC') > -1">
                                    {{ t('files.button.flight') }} {{ t('global.label.domestic') }}
                                    <span style="margin-left: 16px">|</span>
                                  </template>
                                </span>
                              </template>
                              <template #popover-content>&nbsp;</template>
                            </files-edit-field-static>
                          </a-col>
                          <a-col>
                            <files-edit-field-static :inline="true" :highlighted="true">
                              <template #label>
                                <div v-if="itinerary.flights_completed">
                                  <span class="mx-2 color-black">
                                    {{ itinerary.city_in_name }}
                                  </span>
                                  <i class="bi bi-chevron-right"></i>
                                  <span class="mx-2 color-black">
                                    {{ itinerary.city_out_name }}
                                  </span>
                                </div>
                                <div v-else>
                                  <span
                                    class="mx-2 color-danger cursor-pointer"
                                    v-if="!activeSourceSearchOrigin"
                                    @click="clickActiveSourceSearchOrigin"
                                    >{{
                                      itinerary.city_in_name ? itinerary.city_in_name : '-'
                                    }}</span
                                  >
                                  <file-combo-flights
                                    v-else
                                    @blur="handleSelectOriginBlur"
                                    @change="changeSelectOriginBlur"
                                    :file-id="file_id"
                                    :itinerary-id="itinerary.id"
                                    :type-flight="itinerary.name"
                                    type="in"
                                    :city-iso="itinerary.city_out_iso"
                                    :valor-initial="itinerary.city_in_iso"
                                    @focus-requested="isFocusRequested = true"
                                  />
                                  <font-awesome-icon
                                    :icon="['fas', 'angle-right']"
                                    class="text-dark-gray"
                                  />
                                  <span
                                    class="mx-2 color-danger cursor-pointer"
                                    v-if="!activeSourceSearchDestination"
                                    @click="clickActiveSourceSearchDestination"
                                    >{{
                                      itinerary.city_out_name ? itinerary.city_out_name : '-'
                                    }}</span
                                  >
                                  <file-combo-flights
                                    v-else
                                    @blur="handleSelectDestinationBlur"
                                    @change="changeSelectDestinationBlur"
                                    :file-id="file_id"
                                    :itinerary-id="itinerary.id"
                                    :type-flight="itinerary.name"
                                    type="out"
                                    :city-iso="itinerary.city_in_iso"
                                    :valor-initial="itinerary.city_out_iso"
                                    @focus-requested="isFocusRequested = true"
                                  />
                                </div>
                              </template>
                              <template #popover-content>&nbsp;</template>
                            </files-edit-field-static>
                          </a-col>
                          <a-col v-if="toggleServices">
                            <template v-if="itinerary.entity == 'flight'">
                              <template v-if="itinerary.flights_completed">
                                <font-awesome-icon
                                  :icon="['fas', 'circle-check']"
                                  size="lg"
                                  class="text-success"
                                />
                              </template>
                              <template v-else>
                                <font-awesome-icon
                                  :icon="['fas', 'triangle-exclamation']"
                                  class="text-warning"
                                  size="lg"
                                  fade
                                />
                              </template>
                            </template>
                          </a-col>
                        </template>
                        <template
                          v-if="
                            itinerary.entity == 'service' ||
                            itinerary.entity == 'service-temporary' ||
                            itinerary.entity == 'service-mask'
                          "
                        >
                          <a-col>
                            <files-edit-field-static :inline="true" :hide-content="false">
                              <template #label>
                                <span
                                  style="
                                    font-weight: 400;
                                    font-size: 15px;
                                    letter-spacing: -0.015em;
                                    cursor: pointer;
                                  "
                                >
                                  {{ truncateString(itinerary.name, 55) }}
                                </span>
                              </template>
                              <template #popover-content>
                                <span class="text-700" style="margin-right: 2px"
                                  >{{ itinerary.object_code }}:</span
                                >
                                <span class="text-500">{{ itinerary.name }}</span>
                              </template>
                            </files-edit-field-static>
                          </a-col>
                        </template>
                        <template v-if="itinerary.entity == 'hotel'">
                          <a-col>
                            <files-edit-field-static :inline="true" :hide-content="false">
                              <template #label>
                                <span
                                  style="
                                    font-weight: 400;
                                    font-size: 15px;
                                    letter-spacing: -0.015em;
                                    cursor: pointer;
                                  "
                                >
                                  {{ truncateString(itinerary.name, 25) }}
                                </span>
                                <span class="text-danger">|</span>
                                <span
                                  class="text-uppercase"
                                  style="
                                    font-weight: 500;
                                    font-size: 13px;
                                    letter-spacing: -0.015em;
                                    cursor: pointer;
                                  "
                                >
                                  {{ itinerary.rooms[0].units[0].nights.length }}
                                  {{ t('global.label.night')
                                  }}<template v-if="itinerary.rooms[0].units[0].nights.length > 1"
                                    >s</template
                                  >
                                </span>
                                <span class="text-danger">|</span>
                                <span
                                  class="text-uppercase"
                                  style="
                                    font-weight: 500;
                                    font-size: 13px;
                                    letter-spacing: -0.015em;
                                    cursor: pointer;
                                  "
                                >
                                  {{ calculateRooms(itinerary.rooms) }}
                                  <template v-if="calculateRooms(itinerary.rooms) > 1">
                                    {{ t('global.label.units') }}
                                  </template>
                                  <template v-else> {{ t('global.label.unit') }} </template>
                                </span>
                              </template>
                              <template #popover-content>
                                <span class="text-700" style="margin-right: 2px">
                                  {{ itinerary.object_code }}:
                                </span>
                                <span class="text-500">{{ itinerary.name }}</span>
                              </template>
                            </files-edit-field-static>
                          </a-col>
                          <a-col v-if="itinerary.hyperguest && toggleServices">
                            <hyperguest-icon :text="itinerary.rooms[0].rate_plan_name ?? ''" />
                          </a-col>
                          <a-col class="mx-2">
                            <a-tooltip
                              v-if="itinerary.rooms.some((room) => !room.confirmation_status)"
                            >
                              <template #title>
                                <small>{{ t('files.message.no_confirmation_hotel') }}</small>
                              </template>
                              <font-awesome-icon
                                :icon="['fas', 'triangle-exclamation']"
                                class="text-warning"
                                fade
                                size="lg"
                              />
                            </a-tooltip>
                            <template v-else>
                              <font-awesome-icon
                                :icon="['fas', 'circle-check']"
                                size="lg"
                                class="text-success"
                              />
                            </template>
                          </a-col>
                        </template>
                      </a-row>
                    </a-col>
                    <a-col class="d-flex">
                      <a-row justify="end" align="middle" style="gap: 10px">
                        <a-col v-if="!flag_frequence">
                          <div
                            v-if="
                              itinerary.entity == 'hotel' ||
                              itinerary.entity == 'service' ||
                              itinerary.entity == 'service-temporary' ||
                              itinerary.entity == 'service-mask'
                            "
                          >
                            <template v-if="!isGroupActive(itinerary.object_code).length > 0">
                              <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                                <template
                                  v-if="
                                    parseInt(itinerary.service_type_id) === 1 &&
                                    filesStore.getServiceSchedules(
                                      itinerary,
                                      getWeekDay(itinerary.date_in, 'en').toLowerCase()
                                    )
                                  "
                                >
                                  <a-col>
                                    <!-- span>
                                    {{  filesStore.getServiceSchedules(
                                      itinerary,
                                      getWeekDay(itinerary.date_in, 'en').toLowerCase()
                                    ) }}
                                  </span -->
                                    <a-select
                                      style="min-width: 90px"
                                      :size="toggleServices ? 'small' : ''"
                                      v-model:value="itinerary.start_time"
                                      :field-names="{ label: 'start_time', value: 'start_time' }"
                                      :options="
                                        filesStore.getServiceSchedules(
                                          itinerary,
                                          getWeekDay(itinerary.date_in, 'en').toLowerCase()
                                        )
                                      "
                                      @select="updateTimeServiceSelect($event, itinerary)"
                                      :disabled="itineraryStore.isLoading"
                                    >
                                    </a-select>
                                  </a-col>
                                </template>
                                <template v-else>
                                  <a-col>
                                    <base-input-time
                                      :allowClear="false"
                                      value-format="HH:mm"
                                      placeholder=""
                                      v-model="itinerary.start_time"
                                      :disabled="itineraryStore.isLoading"
                                      format="HH:mm"
                                      :size="toggleServices ? 'small' : ''"
                                      @input="
                                        updateTimeServiceSingle($event, itinerary, 'start_time')
                                      "
                                    />
                                  </a-col>
                                  <a-col>
                                    <font-awesome-icon
                                      :icon="['fas', 'arrow-right']"
                                      class="text-dark-gray"
                                    />
                                  </a-col>
                                  <a-col>
                                    <base-input-time
                                      :allowClear="false"
                                      value-format="HH:mm"
                                      v-model="itinerary.departure_time"
                                      :disabled="itineraryStore.isLoading"
                                      format="HH:mm"
                                      :size="toggleServices ? 'small' : ''"
                                      @input="
                                        updateTimeServiceSingle($event, itinerary, 'departure_time')
                                      "
                                    />
                                  </a-col>
                                </template>
                              </a-row>
                            </template>
                          </div>
                        </a-col>
                        <a-col
                          v-if="
                            isLockedADMIN(itinerary.entity) && itinerary.entity !== 'service-mask'
                          "
                        >
                          <font-awesome-icon
                            :icon="`fa-solid fa-chevron-${itinerary.flag_show ? 'up' : 'down'}`"
                            class="toggle-compuestos"
                            :class="{ 'is-custom-disabled': checkedCompuestos }"
                            size="lg"
                            @click="toggleCompuestos"
                          />
                        </a-col>
                        <a-col
                          :style="`${props.activeKeyItineraries === '3' ? '' : 'width: 215px'}`"
                        >
                          <a-row type="flex" justify="end" align="middle" style="gap: 7px">
                            <a-col
                              v-if="
                                itinerary.entity == 'service' ||
                                itinerary.entity == 'service-temporary' ||
                                itinerary.entity == 'service-mask'
                              "
                            >
                              <file-button-replace-item
                                @onHandleGoToReservations="goToReservations"
                                :disabled="flagItineraryDetail"
                                :data="{
                                  type: 'service',
                                  params: { service_id: itinerary.id },
                                  itinerary: itinerary,
                                }"
                              />
                            </a-col>
                            <a-col v-if="itinerary.entity == 'hotel' && !itinerary.hyperguest">
                              <file-button-replace-item
                                :disabled="flagItineraryDetail"
                                @onHandleGoToReservations="goToReservations"
                                :data="{
                                  type: 'hotel',
                                  params: { hotel_id: itinerary.id },
                                  itinerary: itinerary,
                                }"
                              />
                            </a-col>
                            <a-col v-if="itinerary.entity === 'flight'">
                              <files-edit-field-static
                                :inline="true"
                                :hide-content="false"
                                :highlighted="false"
                                :link="true"
                              >
                                <template #label>
                                  <span
                                    class="cursor-pointer"
                                    @click="willRemoveFlight(itinerary.id)"
                                  >
                                    <font-awesome-icon :icon="['far', 'trash-can']" />
                                  </span>
                                </template>
                                <template #popover-content>
                                  {{ t('global.label.destroy') }}
                                  {{ t('files.button.flight') }}</template
                                >
                              </files-edit-field-static>
                            </a-col>
                            <template
                              v-if="
                                itinerary.entity === 'service' ||
                                itinerary.entity === 'service-temporary' ||
                                itinerary.entity === 'service-mask'
                              "
                            >
                              <a-col>
                                <file-button-delete-item
                                  @onHandleGoToReservations="goToReservations"
                                  :data="{
                                    type: 'service',
                                    params: { service_id: itinerary.id },
                                    itinerary: itinerary,
                                  }"
                                />
                              </a-col>
                            </template>
                            <template v-if="itinerary.entity == 'hotel'">
                              <a-col>
                                <file-button-delete-item
                                  @onHandleGoToReservations="goToReservations"
                                  :data="{
                                    type: 'hotel',
                                    params: { hotel_id: itinerary.id },
                                    itinerary: itinerary,
                                  }"
                                />
                              </a-col>
                            </template>
                            <a-col>
                              <popover-hover-and-click
                                placement-click="bottom"
                                :data="props.data"
                                :buttons="buttonsPoppupNotes"
                                :content-button-save="
                                  serviceLogIdNote == ''
                                    ? t('global.button.save')
                                    : t('global.label.edit')
                                "
                                :hide-save="false"
                                @onPopoverClick="handleClickPoppup"
                                @onCancel="cancelNoteClick"
                                @onSave="handleServiceLogSave"
                              >
                                <svg
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="feather feather-mail files-edit-field-static-icon"
                                  width="16"
                                  height="16"
                                  fill="none"
                                  stroke="currentColor"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  viewBox="0 0 24 24"
                                  style="overflow: visible"
                                >
                                  <path
                                    d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
                                  />
                                  <path d="m22 6-10 7L2 6" />
                                  <circle
                                    cx="22"
                                    cy="4"
                                    r="5"
                                    fill="#eb5757"
                                    stroke="none"
                                    v-if="itinerary.isNewNote || itinerary.isUpdatedNote"
                                  >
                                    <animate
                                      attributeName="opacity"
                                      values="1;0.5;1"
                                      dur="1.5s"
                                      repeatCount="indefinite"
                                    />
                                  </circle>
                                </svg>
                                <template #content-hover>{{
                                  t('global.label.service_notes')
                                }}</template>
                                <template #content-click style="position: relative">
                                  <div v-if="loadingNote" class="loading">
                                    <a-spin
                                      style="
                                        position: absolute;
                                        top: 50%;
                                        left: 50%;
                                        transform: translate(-50%, -50%);
                                      "
                                    />
                                  </div>
                                  <div class="files-toggler-service-log">
                                    <a-tabs
                                      v-model:activeKey="activeKeyTabsServiceNotes"
                                      style="width: 400px"
                                      @change="handleTabChange"
                                      hide-add
                                      :destroyInactiveTabPane="true"
                                    >
                                      <a-tab-pane
                                        key="1"
                                        :tab="t('global.label.write')"
                                        force-render
                                      >
                                        <div
                                          style="
                                            display: flex;
                                            flex-direction: column;
                                            align-items: center;
                                            font-size: 14px;
                                            color: #000;
                                          "
                                        >
                                          <a-form direction="vertical" style="width: 95%">
                                            <a-row>
                                              <a-col>
                                                <a-form-item
                                                  :label="t('global.label.service_notes')"
                                                  style="font-size: 16px"
                                                >
                                                  <a-radio-group v-model:value="serviceLogTypeNote">
                                                    <a-radio
                                                      value="INFORMATIVE"
                                                      style="font-size: 11px"
                                                      >{{ t('global.label.informative') }}</a-radio
                                                    >
                                                    <a-radio
                                                      value="REQUIREMENT"
                                                      style="font-size: 11px"
                                                      >{{ t('global.label.requirement') }}</a-radio
                                                    >
                                                  </a-radio-group>
                                                </a-form-item>
                                              </a-col>
                                            </a-row>
                                            <a-form-item :label="t('global.label.classification')">
                                              <a-select
                                                :placeholder="t('global.label.select_option')"
                                                v-model:value="serviceLogClassificationCode"
                                              >
                                                <a-select-option value="" selected hidden>{{
                                                  t('global.label.select_option')
                                                }}</a-select-option>
                                                <a-select-option
                                                  v-for="item in serviceNotesStore?.classifications"
                                                  :key="item.code.trim()"
                                                  :value="item.code.trim()"
                                                >
                                                  {{ item.description }}
                                                </a-select-option>
                                              </a-select>
                                            </a-form-item>
                                            <a-form-item>
                                              <a-textarea
                                                style="width: 100%"
                                                :placeholder="
                                                  t('global.message.writer_notificacion_ope')
                                                "
                                                :rows="3"
                                                show-count
                                                :maxlength="500"
                                                v-model:value="serviceLogDescription"
                                              />
                                            </a-form-item>
                                            <template #content-buttons>
                                              <!-- <div :span="10" style="text-align: right; margin-bottom: 0">
                                                <a-button style="margin-right: 10px" @click="cancelNoteClick"
                                                  >Cancelar</a-button
                                                >
                                                <a-button type="primary" @click="handleServiceLogSave">{{
                                                  serviceLogIdNote == '' ? 'Guardar' : 'Editar'
                                                }}</a-button>
                                              </div> -->
                                            </template>
                                          </a-form>
                                        </div>
                                      </a-tab-pane>
                                      <a-tab-pane
                                        key="2"
                                        :tab="t('files.label.notes') + ' (' + countNotes + ')'"
                                      >
                                        <div style="margin-bottom: 10px">
                                          <div
                                            :style="{
                                              display: 'flex',
                                              justifyContent: selectedNote
                                                ? 'space-between'
                                                : 'flex-end',
                                              gap: '10px',
                                              margin: '10px 0',
                                            }"
                                          >
                                            <files-edit-field-static
                                              :inline="true"
                                              :hide-content="false"
                                              :highlighted="false"
                                              :link="false"
                                              v-if="selectedNote"
                                            >
                                              <template #popover-content>{{
                                                t('global.button.back')
                                              }}</template>
                                              <template #label>
                                                <svg
                                                  @click="selectedNote = null"
                                                  class="svg-icon"
                                                  width="16"
                                                  height="16"
                                                  viewBox="0 0 24 24"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <path
                                                    d="M19 12H5"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M12 19L5 12L12 5"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </svg>
                                              </template>
                                            </files-edit-field-static>

                                            <files-edit-field-static
                                              :inline="true"
                                              :hide-content="false"
                                              :highlighted="false"
                                              :link="false"
                                            >
                                              <template #popover-content>{{
                                                t('global.label.create_note')
                                              }}</template>
                                              <template #label>
                                                <svg
                                                  class="feather feather-plus-square svg-icon"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                  width="16"
                                                  height="16"
                                                  fill="none"
                                                  stroke="currentColor"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  viewBox="0 0 24 24"
                                                  @click="handleSvgClick"
                                                >
                                                  <rect
                                                    width="18"
                                                    height="18"
                                                    x="3"
                                                    y="3"
                                                    rx="2"
                                                    ry="2"
                                                  />
                                                  <path d="M12 8v8M8 12h8" />
                                                </svg>
                                              </template>
                                            </files-edit-field-static>
                                          </div>

                                          <a-empty
                                            v-if="
                                              (loadingNote && selectedNote == null) ||
                                              !serviceNotesStore?.getServiceNotes?.length
                                            "
                                          />
                                          <div
                                            v-else
                                            v-if="!selectedNote"
                                            style="
                                              display: flex;
                                              justify-content: space-between;
                                              background-color: #fafafa;
                                              border-radius: 6px;
                                              border: 1px solid #e9e9e9;
                                              padding: 5px 20px;
                                              margin-bottom: 10px;
                                            "
                                            v-for="(
                                              note, index
                                            ) in serviceNotesStore.getServiceNotes"
                                            :key="note.id"
                                            @click="
                                              note.status === 1 || note.status === 5
                                                ? selectNote(note)
                                                : null
                                            "
                                          >
                                            <div style="cursor: pointer">
                                              <div
                                                v-if="!(note.status === 1 || note.status === 5)"
                                                style="display: flex; gap: 8px; opacity: 0.4"
                                              >
                                                <div class="icon">
                                                  <font-awesome-icon :icon="['fas', 'spinner']" />
                                                </div>
                                                <div>
                                                  {{ t('global.message.requirement_in_process') }}
                                                  <div
                                                    style="
                                                      font-size: 12px;
                                                      color: #4f4b4b;
                                                      font-weight: 500;
                                                    "
                                                  >
                                                    {{ note.classification_name }}
                                                  </div>
                                                  <div
                                                    style="
                                                      font-size: 12px;
                                                      color: #979797;
                                                      font-weight: 400;
                                                    "
                                                  >
                                                    {{ note?.created_by_name || '' }}
                                                  </div>
                                                </div>
                                              </div>
                                              <div v-else>
                                                <div
                                                  style="
                                                    font-size: 12px;
                                                    color: #4f4b4b;
                                                    font-weight: 500;
                                                  "
                                                >
                                                  {{ note.classification_name }}
                                                </div>
                                                <div
                                                  style="
                                                    font-size: 12px;
                                                    color: #979797;
                                                    font-weight: 400;
                                                  "
                                                >
                                                  {{ note?.created_by_name || '' }}
                                                </div>
                                              </div>
                                            </div>
                                            <div
                                              style="
                                                display: flex;
                                                flex-direction: column;
                                                justify-content: space-between;
                                              "
                                            >
                                              <div>
                                                <div
                                                  style="
                                                    font-size: 12px;
                                                    color: #eb5757;
                                                    font-weight: 600;
                                                    text-align: right;
                                                  "
                                                >
                                                  {{ note.created_date }}
                                                </div>
                                                <div
                                                  style="
                                                    font-size: 10px;
                                                    color: #333;
                                                    font-weight: 400;
                                                    text-align: right;
                                                  "
                                                >
                                                  {{ note.created_hour }}
                                                </div>
                                              </div>
                                            </div>
                                          </div>

                                          <div
                                            v-if="selectedNote && !loadingNote"
                                            class="detail-note"
                                          >
                                            <a-row>
                                              <a-col
                                                :span="2"
                                                :order="1"
                                                style="
                                                  display: flex;
                                                  justify-content: flex-start;
                                                  align-items: center;
                                                "
                                              >
                                                <svg
                                                  class="svg-icon"
                                                  width="17"
                                                  height="17"
                                                  viewBox="0 0 17 17"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                  @click="editNote()"
                                                >
                                                  <g clip-path="url(#clip0_25750_6622)">
                                                    <path
                                                      d="M14.1667 10.3838V14.1663C14.1667 14.5421 14.0175 14.9024 13.7518 15.1681C13.4861 15.4338 13.1258 15.583 12.7501 15.583H2.83341C2.45769 15.583 2.09736 15.4338 1.83168 15.1681C1.566 14.9024 1.41675 14.5421 1.41675 14.1663V4.24967C1.41675 3.87395 1.566 3.51362 1.83168 3.24794C2.09736 2.98226 2.45769 2.83301 2.83341 2.83301H6.61591"
                                                      stroke="#3D3D3D"
                                                      stroke-width="2"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                    <path
                                                      d="M12.7501 1.41699L15.5834 4.25033L8.50008 11.3337H5.66675V8.50033L12.7501 1.41699Z"
                                                      stroke="#3D3D3D"
                                                      stroke-width="2"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                    />
                                                  </g>
                                                  <defs>
                                                    <clipPath id="clip0_25750_6622">
                                                      <rect width="17" height="17" fill="white" />
                                                    </clipPath>
                                                  </defs>
                                                </svg>
                                              </a-col>
                                              <a-col
                                                :span="2"
                                                :order="2"
                                                style="
                                                  display: flex;
                                                  justify-content: flex-start;
                                                  align-items: center;
                                                "
                                                @click="showModal"
                                              >
                                                <svg
                                                  class="svg-icon"
                                                  width="17"
                                                  height="17"
                                                  viewBox="0 0 17 17"
                                                  fill="none"
                                                  xmlns="http://www.w3.org/2000/svg"
                                                >
                                                  <path
                                                    d="M2.125 4.25H3.54167H14.875"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M5.66675 4.25033V2.83366C5.66675 2.45794 5.816 2.0976 6.08168 1.83192C6.34736 1.56625 6.70769 1.41699 7.08341 1.41699H9.91675C10.2925 1.41699 10.6528 1.56625 10.9185 1.83192C11.1842 2.0976 11.3334 2.45794 11.3334 2.83366V4.25033M13.4584 4.25033V14.167C13.4584 14.5427 13.3092 14.903 13.0435 15.1687C12.7778 15.4344 12.4175 15.5837 12.0417 15.5837H4.95841C4.58269 15.5837 4.22236 15.4344 3.95668 15.1687C3.691 14.903 3.54175 14.5427 3.54175 14.167V4.25033H13.4584Z"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M7.08325 7.79199V12.042"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                  <path
                                                    d="M9.91675 7.79199V12.042"
                                                    stroke="#3D3D3D"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                  />
                                                </svg>
                                              </a-col>
                                              <a-col
                                                :span="10"
                                                :order="3"
                                                style="
                                                  font-size: 12px;
                                                  color: #4f4b4b;
                                                  font-weight: 500;
                                                  display: flex;
                                                  align-items: center;
                                                "
                                                >{{ selectedNote.classification_name }}</a-col
                                              >
                                              <a-col :span="10" :order="4">
                                                <div>
                                                  <div
                                                    style="
                                                      font-size: 12px;
                                                      color: #eb5757;
                                                      font-weight: 600;
                                                      text-align: right;
                                                    "
                                                  >
                                                    {{ selectedNote.created_date }}
                                                  </div>
                                                  <div
                                                    style="
                                                      font-size: 9px;
                                                      color: #333;
                                                      font-weight: 400;
                                                      text-align: right;
                                                    "
                                                  >
                                                    {{ selectedNote.created_hour }}
                                                  </div>
                                                </div>
                                              </a-col>
                                            </a-row>
                                            <a-row>
                                              <a-col
                                                :span="24"
                                                style="
                                                  font-size: 12px;
                                                  color: #979797;
                                                  font-weight: 400;
                                                "
                                              >
                                                <p>{{ selectedNote.created_by_name }}</p>
                                              </a-col>
                                            </a-row>
                                            <a-row>
                                              <a-col
                                                :span="24"
                                                style="
                                                  font-size: 14px;
                                                  color: #737373;
                                                  font-weight: 400;
                                                "
                                              >
                                                <p>{{ selectedNote.description }}</p>
                                              </a-col>
                                            </a-row>
                                          </div>
                                        </div>
                                      </a-tab-pane>
                                    </a-tabs>
                                  </div>
                                </template>
                                <!-- <template #content-buttons>&nbsp;</template> -->
                              </popover-hover-and-click>
                            </a-col>
                            <template
                              v-if="
                                itinerary.entity == 'service' ||
                                itinerary.entity == 'service-temporary'
                              "
                            >
                              <files-voucher :itinerary="itinerary" />
                            </template>
                            <a-col v-if="itinerary.entity === 'service'">
                              <files-toggler-service-passengers
                                v-if="!loading"
                                @onRefreshItineraryCache="handleRefreshItineraryCache"
                                :data="{
                                  format: 'service',
                                  type: 'itinerary',
                                  passengers: passengers,
                                  object_id: itinerary.id,
                                  itinerary: itinerary,
                                }"
                              />
                            </a-col>
                            <a-col v-if="itinerary.entity !== 'flight'">
                              <files-edit-field-static :inline="true" :hide-content="false">
                                <template #label>
                                  <span
                                    class="d-flex cursor-pointer text-dark-gray"
                                    @click="toggleModalInformation(itinerary)"
                                  >
                                    <font-awesome-icon :icon="['far', 'circle-question']" />
                                  </span>
                                </template>
                                <template #popover-content>
                                  <span class="text-capitalize">
                                    {{ t('global.label.more') }} {{ t('global.label.information') }}
                                  </span>
                                </template>
                              </files-edit-field-static>
                            </a-col>
                            <a-col
                              type="flex"
                              :class="['d-flex', !itinerary.status ? 'z-100' : '']"
                              style="justify-content: flex-end; width: 90px"
                            >
                              <template v-if="itinerary.entity === 'flight'">
                                <a-row
                                  type="flex"
                                  style="gap: 10px"
                                  v-if="
                                    itinerary.city_in_iso &&
                                    itinerary.city_out_iso &&
                                    itinerary.city_in_iso !== '' &&
                                    itinerary.city_out_iso !== ''
                                  "
                                >
                                  <!-- a-col>
                                    <a-tooltip>
                                      <template #title>
                                        <small class="text-uppercase"
                                          >PAXS: <b>{{ itinerary.total_paxs }}</b>
                                          {{ t('files.label.assigned') }}(S) /
                                          <b>{{ itinerary.permitted_paxs }}</b>
                                          {{ t('files.label.permitted') }}(S)</small
                                        >
                                      </template>
                                      <template v-if="!itinerary.is_valid">
                                        <font-awesome-icon
                                          :icon="['far', 'face-frown']"
                                          class="text-dark-warning"
                                          size="lg"
                                        />
                                      </template>
                                      <template v-if="itinerary.is_valid">
                                        <font-awesome-icon
                                          :icon="['far', 'face-smile-beam']"
                                          class="text-dark-gray"
                                          size="lg"
                                        />
                                      </template>
                                    </a-tooltip>
                                  </a-col -->
                                  <a-col v-if="toggleServices">
                                    <files-toggler-service-passengers
                                      v-if="!loading"
                                      @onRefreshItineraryCache="handleRefreshItineraryCache"
                                      :data="{
                                        adl: itinerary.adults,
                                        chd: itinerary.children,
                                        passengers: passengers,
                                        format: 'general',
                                        type: 'itinerary',
                                        object_id: itinerary.id,
                                        itinerary: itinerary,
                                      }"
                                    />
                                  </a-col>
                                </a-row>
                              </template>
                              <template v-else>
                                <files-edit-field-static
                                  :inline="true"
                                  :highlighted="true"
                                  :hide-content="itinerary.status"
                                >
                                  <template #label style="height: auto">
                                    <div class="d-block text-center" style="font-size: 18px">
                                      <span
                                        class="d-block text-700"
                                        v-if="itinerary.total_amount > 0"
                                      >
                                        <font-awesome-icon
                                          :icon="['fas', 'ban']"
                                          class="mx-1"
                                          fade
                                          v-if="!itinerary.status"
                                        />
                                        ${{
                                          formatNumber({
                                            number: itinerary.total_amount / itinerary.adults,
                                            digits: 0,
                                          })
                                        }}
                                      </span>
                                      <!-- small class="text-danger" style="font-size: 10px">
                                        ADL
                                      </small -->
                                    </div>
                                  </template>
                                  <template #popover-content v-if="!itinerary.status">
                                    {{ t('files.label.cancelled_service') }}
                                    <span class="text-lowercase">
                                      <template v-if="itinerary.total_amount > 0">{{
                                        t('global.label.with')
                                      }}</template
                                      ><template v-else>{{ t('global.label.without') }}</template>
                                      {{ t('files.label.penalty') }}
                                    </span>
                                  </template>
                                </files-edit-field-static>
                              </template>
                            </a-col>
                          </a-row>
                        </a-col>
                      </a-row>
                    </a-col>
                  </a-row>
                  <template v-if="isLockedADMIN(itinerary.entity)">
                    <template
                      v-if="
                        itinerary.flag_show &&
                        (itinerary.entity == 'hotel' ||
                          itinerary.entity == 'service' ||
                          itinerary.entity == 'service-temporary')
                      "
                    >
                      <hr
                        style="
                          margin-top: 1rem;
                          margin-bottom: 1rem;
                          background: #c4c4c4;
                          border: 0;
                          height: 1px;
                        "
                      />
                      <a-tooltip v-if="itinerary?.show_master_services === false">
                        <template #title>{{ t('files.message.loading') }}..</template>
                        <a-skeleton class="mt-3" active :paragraph="{ rows: 2 }" :title="false" />
                      </a-tooltip>
                      <div v-else>
                        <div class="d-flex">
                          <svg
                            class="feather feather-git-pull-request me-1"
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
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
                          <span
                            style="font-weight: 600; font-size: 12px; line-height: 19px"
                            class="text-underline text-uppercase"
                          >
                            <template
                              v-if="
                                itinerary.entity == 'service' ||
                                itinerary.entity == 'service-temporary' ||
                                itinerary.entity == 'service-mask'
                              "
                              >{{ t('global.label.master_services') }}</template
                            >
                            <template v-if="itinerary.entity == 'hotel'">{{
                              t('global.label.units')
                            }}</template>
                          </span>
                        </div>

                        <template
                          v-if="
                            itinerary.entity !== 'hotel' &&
                            itinerary.entity !== 'flight' &&
                            itinerary.days &&
                            itinerary.days.length > 1 &&
                            parseInt(itinerary.service_category_id) !== 13
                          "
                        >
                          <div
                            class="files-edit__service mt-3 mb-0 pb-3"
                            style="border-width: 1px"
                            v-for="(day, d) in itinerary.days"
                          >
                            <div class="files-edit__service-row-1 py-3">
                              <div class="files-edit__service-header">
                                <files-edit-field-static :inline="true" :highlighted="true">
                                  <template #label>
                                    <font-awesome-icon icon="fa-regular fa-calendar" size="xl" />
                                    {{ t('global.label.start') }}:
                                  </template>
                                  <template #content>
                                    <span style="text-transform: capitalize">
                                      {{ formatDate(day.date_in, 'MMM DD, YYYY', locale) }}
                                    </span>
                                  </template>
                                  <template #popover-content>&nbsp;</template>
                                </files-edit-field-static>

                                <files-edit-field-static :inline="true">
                                  <template #label>
                                    <span class="text-uppercase">{{ t('global.label.day') }}</span>
                                  </template>
                                  <template #content
                                    >{{ checkDates(itinerary.date_in, day.date_in) }}
                                  </template>
                                  <template #popover-content>&nbsp;</template>
                                </files-edit-field-static>

                                <files-edit-field-divider style="margin-top: 2px" />

                                <files-edit-field-static :inline="true">
                                  <template #content>
                                    <span style="text-transform: uppercase">
                                      {{ getWeekDay(day.date_in, locale) }}
                                    </span>
                                  </template>
                                </files-edit-field-static>

                                <!--
                                  <files-edit-field-divider style="margin-top: 2px" />

                                  <files-edit-field-static :inline="true">
                                    <template #label>
                                      <span style="text-transform: uppercase">
                                        {{ day.services[0].city_in_iso }}
                                      </span>
                                    </template>
                                    <template #popover-content>&nbsp;</template>
                                  </files-edit-field-static>
                                -->
                              </div>
                            </div>

                            <div class="px-4">
                              <file-itinerary-services
                                :data="{
                                  frequence_code: frequence_code,
                                  flag_frequence: flag_frequence,
                                  fileNumber: filesStore.getFile.fileNumber,
                                  services: day.services,
                                  passengers: passengers,
                                  itinerary: itinerary,
                                }"
                                v-if="
                                  itinerary.entity == 'service' ||
                                  itinerary.entity == 'service-temporary' ||
                                  itinerary.entity == 'service-mask'
                                "
                                @onHandleGoToReservations="goToReservations"
                                @onRefreshItineraryCache="handleRefreshItineraryCache"
                              />
                            </div>
                          </div>
                        </template>
                        <template v-else>
                          <file-itinerary-services
                            :data="{
                              frequence_code: frequence_code,
                              flag_frequence: flag_frequence,
                              fileNumber: filesStore.getFile.fileNumber,
                              days: itinerary.days,
                              services: itinerary.services,
                              passengers: passengers,
                              itinerary: itinerary,
                            }"
                            v-if="
                              itinerary.entity == 'service' ||
                              itinerary.entity == 'service-temporary' ||
                              itinerary.entity == 'service-mask'
                            "
                            @onHandleGoToReservations="goToReservations"
                            @onRefreshItineraryCache="handleRefreshItineraryCache"
                          />
                        </template>
                        <file-itinerary-hotels
                          :data="{
                            passengers: passengers,
                            itinerary: itinerary,
                          }"
                          v-if="itinerary.entity == 'hotel'"
                          @onHandleGoToReservations="goToReservations"
                          @onRefreshItineraryCache="handleRefreshItineraryCache"
                        />
                      </div>
                    </template>
                  </template>
                  <template v-if="itinerary.flag_show && itinerary.entity == 'flight'">
                    <hr class="mb-3 w-100 border-0 height-1" style="background-color: #c4c4c4" />
                    <template
                      v-if="
                        !itinerary.city_in_iso ||
                        !itinerary.city_out_iso ||
                        itinerary.city_in_iso === '' ||
                        itinerary.city_out_iso === ''
                      "
                    >
                      <a-alert type="info">
                        <template #description>
                          <a-row type="flex" justify="start" align="top" style="gap: 10px">
                            <a-col>
                              <p class="text-700 mb-1">
                                {{ t('files.message.title_flight_incompleted') }}
                              </p>
                              {{ t('files.message.content_flight_incompleted') }}
                            </a-col>
                          </a-row>
                        </template>
                      </a-alert>
                    </template>
                    <div v-else>
                      <div class="d-flex">
                        <svg
                          class="feather feather-git-pull-request me-1"
                          xmlns="http://www.w3.org/2000/svg"
                          width="16"
                          height="16"
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
                        <small
                          style="font-weight: 600; font-size: 12px; line-height: 19px"
                          class="text-underline text-uppercase"
                        >
                          {{ t('global.label.information') }} {{ t('global.label.of') }}
                          {{ t('files.button.flight') }}
                        </small>
                      </div>
                      <file-itinerary-flights
                        :data="{
                          fileId: file_id,
                          flights: itinerary.flights,
                          itinerary: itinerary,
                        }"
                        @onRefreshItineraryCache="handleRefreshItineraryCache"
                      />
                    </div>
                  </template>
                </template>
                <template v-else>
                  <a-skeleton class="mt-3" active :paragraph="{ rows: 3 }" :title="false" />
                </template>
              </div>
            </template>
          </div>
        </a-col>
      </a-row>
    </a-spin>

    <a-modal
      :z-index="1031"
      v-model:visible="openModalDelete"
      :maskClosable="false"
      class="text-center"
      @mousedown.stop
      @click.stop
    >
      <div v-if="loadingModalDelete" class="loading">
        <a-spin style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)" />
      </div>
      <h3>{{ t('files.message.delete_note') }}</h3>
      <p>
        <a-alert
          :description="t('global.message.notification_ope')"
          type="error"
          show-icon
          class="custom-alert"
        >
          <template #icon>
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g clip-path="url(#clip0_25805_10854)">
                <path
                  d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                  stroke="#FF3B3B"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M12 8V12"
                  stroke="#FF3B3B"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M12 16H12.01"
                  stroke="#FF3B3B"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </g>
              <defs>
                <clipPath id="clip0_25805_10854">
                  <rect width="24" height="24" fill="white" />
                </clipPath>
              </defs>
            </svg>
          </template>
        </a-alert>
      </p>
      <h5>{{ t('files.message.question_delete_note') }}</h5>
      <template #footer>
        <div class="text-center">
          <a-button
            type="default"
            class="bnt-default"
            default
            @click="cancelModalNote"
            size="large"
          >
            {{ t('global.label.cancel') }}
          </a-button>
          <a-button type="primary" primary @click="removeModalNote" size="large">
            {{ t('global.button.delete') }}
          </a-button>
        </div>
      </template>
    </a-modal>

    <a-modal
      v-model:visible="showModalRemoveFlight"
      :maskClosable="false"
      class="text-center"
      style="position: relative"
    >
      <h3>{{ t('global.button.delete') }} {{ t('files.button.flight') }}</h3>
      <p>{{ t('files.message.remove_flight') }}</p>
      <h5>{{ t('global.label.confirm_process') }}</h5>
      <template #footer>
        <div class="text-center">
          <a-button
            type="default"
            class="text-500"
            default
            :disabled="flightStore.isLoading"
            @click="handleCancel"
            size="large"
          >
            {{ t('global.button.cancel') }}
          </a-button>
          <a-button
            type="primary"
            class="text-500"
            primary
            :loading="flightStore.isLoading"
            @click="removeFlight"
            size="large"
          >
            <span :class="flightStore.isLoading ? 'ms-2' : ''">
              {{ t('global.button.continue') }}
            </span>
          </a-button>
        </div>
      </template>
    </a-modal>

    <a-modal
      :width="1300"
      v-model:visible="showModalPromotionHotels"
      :maskClosable="false"
      class="text-center"
    >
      <template #title>
        <a-row type="flex" justify="start" align="middle" style="gap: 7px" class="px-4 mt-4">
          <a-col>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 512 512"
              fill="#000"
              width="1rem"
              height="1rem"
              class="d-flex"
            >
              <path
                d="M505 442.7l-99.7-99.7c-4.5-4.5-10.6-7-17-7h-16.3c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6 .1-34zm-297-90.7c-79.5 0-144-64.3-144-144 0-79.5 64.4-144 144-144 79.5 0 144 64.3 144 144 0 79.5-64.4 144-144 144zm27.1-152.5l-45-13.5c-5.2-1.6-8.8-6.8-8.8-12.7 0-7.3 5.3-13.2 11.8-13.2h28.1c4.6 0 9 1.3 12.8 3.7 3.2 2 7.4 1.9 10.1-.7l11.8-11.2c3.5-3.4 3.3-9.2-.6-12.1-9.1-6.8-20.1-10.8-31.4-11.4V112c0-4.4-3.6-8-8-8h-16c-4.4 0-8 3.6-8 8v16.1c-23.6 .6-42.7 20.6-42.7 45.1 0 20 13 37.8 31.6 43.4l45 13.5c5.2 1.6 8.8 6.8 8.8 12.7 0 7.3-5.3 13.2-11.8 13.2h-28.1c-4.6 0-9-1.3-12.8-3.7-3.2-2-7.4-1.9-10.1 .7l-11.8 11.2c-3.5 3.4-3.3 9.2 .6 12.1 9.1 6.8 20.1 10.8 31.4 11.4V304c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8v-16.1c23.6-.6 42.7-20.5 42.7-45.1 0-20-13-37.8-31.6-43.4z"
              />
            </svg>
          </a-col>
          <a-col>
            <h5 class="mb-0 text-700" style="font-size: 18px !important">
              {{ t('global.label.promotions') }}
            </h5>
          </a-col>
        </a-row>
      </template>
      <div id="files-layout">
        <div class="files-edit p-0">
          <hotel-search :showHeader="false" :showFilters="false" />
        </div>
      </div>
      <template #footer>
        <span class="d-block" style="margin-top: -24px"></span>
      </template>
    </a-modal>

    <div id="quotes-layout" style="position: relative; z-index: 999">
      <ModalComponent
        v-if="flagModalInformation"
        :modal-active="isModalActive"
        class="modal-itinerario-detail"
        :closable="true"
        @close="onClose"
      >
        <template #body>
          <ContentHotel :hotel="filesStore.getHotelInformation" :flag-remarks="true" />
        </template>
      </ModalComponent>

      <ModalComponent
        :modal-active="isModalActive"
        v-if="flagModalInformationService"
        class="modal-itinerario-detail"
        :closable="true"
        @close="onClose"
      >
        <template #body>
          <ContentTransfers
            v-if="service.service_category_id === '1' || service.service_category_id === '13'"
            :service="service"
            :service-date="service.date_in"
            :category-name="service.category"
            :flag-remarks="true"
          />
          <ContentLunch
            v-if="service.service_category_id === '10'"
            :service="service"
            :service-date="service.date_in"
            :category-name="service.category"
            :flag-remarks="true"
          />
          <ContentTours
            v-if="service.service_category_id === '9'"
            :service="service"
            :service-date="service.date_in"
            :category-name="service.category"
            :flag-remarks="true"
          />
          <ContentMiscellaneous
            v-if="!['1', '13', '10', '9'].includes(String(service.service_category_id))"
            :service="service"
            :service-date="service.date_in"
            :category-name="service.category"
            :flag-remarks="true"
          />
        </template>
      </ModalComponent>
    </div>

    <InformationServiceModalComponent
      :is-open="modalIsOpenInformation"
      :data="modalInformation"
      @update:is-open="modalIsOpenInformation = $event"
    />
  </div>
</template>

<script setup>
  import { onBeforeMount, ref, watch, provide, toRef, computed } from 'vue';
  import { debounce } from 'lodash-es';
  import {
    formatDate,
    getWeekDay,
    checkDates,
    truncateString,
    formatNumber,
    formatTime,
  } from '@/utils/files.js';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import FilesEditFieldDivider from '@/components/files/edit/FilesEditFieldDivider.vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import FilesTogglerServicePassengers from '@/components/files/edit/FilesTogglerServicePassengers.vue';
  import FilesTogglerServiceLog from '@/components/files/edit/FilesTogglerServiceLog.vue';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';
  import FileItineraryServices from '@/components/files/edit/FileItineraryServices.vue';
  import FileItineraryHotels from '@/components/files/edit/FileItineraryHotels.vue';
  import FileItineraryFlights from '@/components/files/edit/FileItineraryFlights.vue';
  import FileButtonReplaceItem from '@/components/files/edit/FileButtonReplaceItem.vue';
  import FileButtonDeleteItem from '@/components/files/edit/FileButtonDeleteItem.vue';
  import HotelSearch from '@/components/files/reusables/HotelSearch.vue';
  import FilesVoucher from '@/components/files/edit/FilesVoucher.vue';
  import FileComboFlights from '@/components/files/edit/FileComboFlights.vue';
  import BaseInputTime from '@/components/files/reusables/BaseInputTime.vue';
  import RequirementsOpe from '@/components/files/reusables/RequirementsOPE.vue';
  import HyperguestIcon from '@/components/global/HyperguestIcon.vue';

  // import { notification } from 'ant-design-vue';
  import dayjs from 'dayjs';

  import {
    useServiceNotesStore,
    useItineraryStore,
    useFilesStore,
    useFlightStore,
  } from '@/stores/files';
  import { useSocketsStore } from '@/stores/global';
  import InformationServiceModalComponent from '@/components/files/service/components/informationServiceModalComponent.vue';
  import ContentHotel from '@/quotes/components/modals/ContentHotel.vue';
  import ContentTransfers from '@/quotes/components/modals/ContentTransfers.vue';
  import ContentLunch from '@/quotes/components/modals/ContentLunch.vue';
  import ContentTours from '@/quotes/components/modals/ContentTours.vue';
  import ContentMiscellaneous from '@/quotes/components/modals/ContentMiscellaneous.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';

  import { useI18n } from 'vue-i18n';
  import moment from 'moment';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';
  const { t, locale } = useI18n({
    useScope: 'global',
  });

  const socketsStore = useSocketsStore();

  const itineraryIdTemp = ref(null);
  const activeKeyTabsServiceNotes = ref('1');
  const serviceNotesStore = useServiceNotesStore();
  const itineraryStore = useItineraryStore();
  const filesStore = useFilesStore();
  const flightStore = useFlightStore();
  const file_id = filesStore.getFile.id;

  const flag_frequence = ref(false);
  const activeSourceSearchOrigin = ref(false);
  const activeSourceSearchDestination = ref(false);
  const activeSelectDate = ref(false);

  const props = defineProps({
    activeKeyItineraries: {
      type: String,
      default: '',
    },
    toggleServices: {
      type: Boolean,
      default: false,
    },
    itinerary: {
      type: Object,
      default: () => ({}),
    },
    checkedCompuestos: {
      type: Boolean,
      default: false,
    },
  });

  const toggleViewStatus = () => {
    itinerary.value.isNew = false;
    itinerary.value.isUpdated = false;
    itinerary.value.isError = false;
    itinerary.value.isNewNote = false;
    itinerary.value.isUpdatedNote = false;

    socketsStore.readNotifications(itinerary.value.id);
  };

  const frequence_code = ref('');
  const isModalActive = ref(true);

  const onClose = () => {
    // isModalActive.value = false;

    setTimeout(() => {
      flagModalInformation.value = false;
      flagModalInformationService.value = false;
    }, 10);
  };

  onBeforeMount(async () => {
    if (itinerary.value.entity !== 'hotel' && itinerary.value.entity !== 'flight') {
      const frequences = filesStore.getServiceFrequences;
      const quantity = itinerary.value.services.filter(
        (service) => frequences[service.code_ifx] && frequences[service.code_ifx].length > 0
      ).length;

      if (quantity > 0) {
        flag_frequence.value = true;
      }

      frequence_code.value = itinerary.value.frequence_code;
    }
  });

  const isLockedADMIN = () => {
    return true;
    // return entity !== 'service' || (entity === 'service' && getUserCode() === 'ADMIN');
  };

  const loading = ref(false);
  const loadingNote = ref(false);
  const loadingModalDelete = ref(false);
  const serviceLogIdNote = ref('');
  const serviceLogDescription = ref('');
  const serviceLogClassificationCode = ref('');
  const serviceLogTypeNote = ref('INFORMATIVE');
  const isToggleCompuesto = ref(props.checkedCompuestos);
  const passengers = ref(filesStore.getFilePassengers);
  const showModalPromotionHotels = ref(false);
  const modalIsOpenInformation = ref(false);
  const modalInformation = ref(null);

  const flagModalInformation = ref(false);
  const flagModalInformationService = ref(false);
  const service = ref({});

  const itinerary = toRef(props, 'itinerary');

  const showInformationHotel = async (itinerary) => {
    await filesStore.findHotelInformation(itinerary.object_id);

    if (filesStore.getHotelInformation) {
      setTimeout(() => {
        flagModalInformation.value = true;
      }, 100);
    }
  };

  const showInformationService = async (_service, object_id) => {
    service.value = JSON.parse(JSON.stringify(_service));
    service.value.unit_durations ??= {};
    service.value.unit_durations.translations ??= [{ value: '' }];
    service.value.service_translations = [
      {
        name: service.value.name,
        summary: service.value.service_summary,
      },
    ];

    service.value.id = Number(object_id) || 0;

    flagModalInformationService.value = true;
  };

  const showModalRemoveFlight = ref(false);
  const selectedNote = ref(null);

  const openModalDelete = ref(false);

  const countNotes = ref(0);

  const showModal = () => {
    openModalDelete.value = true;
  };

  const removeModalNote = () => {
    // AQUI EL CODIGO DE ELIMINAR
    const fileServiceId = file_id;
    const fileNoteId = selectedNote.value.id;
    loadingModalDelete.value = true;
    serviceNotesStore
      .remove({
        note_id: fileNoteId,
        file_id: fileServiceId,
        data: {
          created_by: getUserId(),
          created_by_code: getUserCode(),
          created_by_name: getUserName(),
        },
      })
      .then((res) => {
        resetServiceLogFields();
        openModalDelete.value = false;
        loadingModalDelete.value = false;
        console.log('respuesta', res);
        socketsStore.send({
          success: true,
          type: 'update_note_itinerary',
          file_number: filesStore.getFile.fileNumber,
          itinerary_id: serviceId,
          user_code: getUserCode(),
          user_id: getUserId(),
          message: 'Eliminación de nota',
          description: 'Se elimino una nota',
          entity: 'note',
        });
      })
      .catch((err) => {
        console.log(err);
        openModalDelete.value = false;
        loadingModalDelete.value = false;
      });
  };

  const cancelModalNote = () => {
    openModalDelete.value = false;
  };

  const willRemoveFlight = (itinerary_id) => {
    showModalRemoveFlight.value = true;
    itineraryIdTemp.value = itinerary_id;
  };

  const removeFlight = async () => {
    loading.value = true;
    await flightStore.remove({ fileId: file_id, fileItineraryId: itineraryIdTemp.value });
    handleCancel();
    emit('onHandleRefreshCache');
  };

  const handleCancel = () => {
    itineraryIdTemp.value = null;
    showModalRemoveFlight.value = false;
  };

  const selectNote = (note) => {
    selectedNote.value = note;
  };

  const editNote = () => {
    activeKeyTabsServiceNotes.value = '1';
    serviceLogIdNote.value = selectedNote.value.id;
    serviceLogTypeNote.value = selectedNote.value.type_note;
    serviceLogDescription.value = selectedNote.value.description;
    serviceLogClassificationCode.value = selectedNote.value.classification_code.trim();
    buttonsPoppupNotes.value = true;
    console.log(itineraryStore.value);
  };

  const resetServiceLogFields = () => {
    serviceLogIdNote.value = '';
    activeKeyTabsServiceNotes.value = '1';
    serviceLogTypeNote.value = 'INFORMATIVE';
    serviceLogClassificationCode.value = '';
    serviceLogDescription.value = '';
    selectedNote.value = null;
    loadingNote.value = false;
    buttonsPoppupNotes.value = true;
  };
  const buttonsPoppupNotes = ref(true);

  const handleClickPoppup = () => {
    countNotes.value = 0;
    resetServiceLogFields();
    loadingNote.value = true;
    const fileServiceId = file_id;
    const serviceId = itinerary.value.id;
    serviceNotesStore
      .fetchAll({ id: serviceId, file_id: fileServiceId })
      .then((response) => {
        console.log(response);
        loadingNote.value = false;
        countNotes.value = serviceNotesStore?.getServiceNotes?.length || 0;
      })
      .catch((err) => {
        console.log(err);
        loadingNote.value = false;
      });

    toggleViewStatus();
  };

  const handleSvgClick = () => {
    resetServiceLogFields();
  };

  const cancelNoteClick = () => {
    resetServiceLogFields();
  };

  const handleTabChange = (activeKey) => {
    const isSelectedTabNote = activeKey === '2';
    if (isSelectedTabNote) {
      selectedNote.value = null;
      buttonsPoppupNotes.value = false;
    } else {
      buttonsPoppupNotes.value = true;
    }
  };

  const emit = defineEmits(['onHandleGoToReservations', 'onHandleRefreshCache']);

  const goToReservations = () => {
    emit('onHandleGoToReservations');
  };

  /*
  const timeRange = (start, end) => {
    return [start, end];
  };
  */

  const isFocusRequested = ref(false);

  provide('focusState', isFocusRequested);

  const clickActiveSourceSearchOrigin = () => {
    activeSourceSearchOrigin.value = true;
    isFocusRequested.value = false;
    setTimeout(() => {
      isFocusRequested.value = true;
    }, 0);
  };

  const clickActiveSourceSearchDestination = () => {
    activeSourceSearchDestination.value = true;
    isFocusRequested.value = false;
    setTimeout(() => {
      isFocusRequested.value = true;
    }, 0);
  };

  const handleSelectOriginBlur = () => {
    activeSourceSearchOrigin.value = false;
  };

  const handleSelectDestinationBlur = () => {
    activeSourceSearchDestination.value = false;
  };

  const changeSelectOriginBlur = ({ value, option }) => {
    console.log('Nueva selección:', value, option); // Aqui trae el valor
    console.log('Valor a enviar', value.value);

    handleSelectOriginBlur();
    handleRefreshItineraryCache();
  };

  const changeSelectDestinationBlur = ({ value, option }) => {
    console.log('Nueva selección:', value, option); // Aqui trae el valor
    console.log('Valor a enviar', value.value);

    handleSelectDestinationBlur();
    handleRefreshItineraryCache();
  };

  const clickActiveSelectDate = () => {
    console.log(itinerary.value);
    const new_date_in = JSON.parse(JSON.stringify(itinerary.value.date_in));
    itinerary.value.new_date_in = dayjs(new_date_in);

    setTimeout(() => {
      activeSelectDate.value = true;
    }, 10);
  };

  const handleSelectDateBlur = async (flagUpdate) => {
    if (flagUpdate) {
      const newDate = dayjs(JSON.parse(JSON.stringify(itinerary.value.new_date_in))).format(
        'YYYY-MM-DD'
      );

      itinerary.value.isLoading = true;
      await itineraryStore.putUpdateDate({
        type: 'flight',
        fileId: filesStore.getFile.id,
        itineraryId: itinerary.value.id,
        date: newDate,
      });
      itinerary.value.date_in = newDate;
      // localStorage.setItem(`ignore_itinerary_${itinerary.value.id}`, true);
    }

    setTimeout(() => {
      activeSelectDate.value = false;

      if (flagUpdate) {
        handleRefreshItineraryCache();
      }
    }, 10);
  };

  const isValidTime = (value) => {
    const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
    return regex.test(value);
  };

  const updateTimeServiceSelect = async ($event, service) => {
    service.start_time = formatTime($event);
    let departure_time = moment(service.start_time, 'HH:mm');
    const master_services = filesStore.getServiceSchedules(
      service,
      getWeekDay(service.date_in, 'en').toLowerCase()
    );
    const items = [];

    for (const master_service of master_services) {
      const compositions = master_service.services_operation_activities;

      for (const composition of compositions) {
        composition.start_time = departure_time.format('HH:mm');
        departure_time = departure_time.clone().add(composition.minutes, 'minutes');
        composition.departure_time = departure_time.format('HH:mm');
      }

      service.departure_time = formatTime(departure_time.format('HH:mm'));

      items.push({
        master_service: {
          start_time: master_service.start_time,
        },
        compositions: compositions.map((composition) => {
          return {
            start_time: composition.start_time,
            departure_time: composition.departure_time,
          };
        }),
      });
    }

    const serviceId = itinerary.value.id;
    const data = {
      id: serviceId,
      type: 'itineraries',
      new_start_time: service.start_time,
      new_departure_time: service.departure_time,
      file_number: filesStore.getFile.fileNumber,
      itinerary_id: serviceId,
      frequency_code: '',
      flag_ignore_duration: 1,
      schedule: items,
    };

    await itineraryStore.putUpdateSchedule(data);
    handleRefreshItineraryCache();
  };

  const updateTimeServiceSingle = debounce(async ($event, service, type) => {
    if (service[type].length === 5 && isValidTime(service[type])) {
      const serviceId = itinerary.value.id;
      const data = {
        id: serviceId,
        type: 'itineraries',
        new_start_time: service.start_time ? service.start_time.slice(0, 5) : '',
        new_departure_time: service.departure_time ? service.departure_time.slice(0, 5) : '',
        file_number: filesStore.getFile.fileNumber,
        itinerary_id: serviceId,
        frequency_code: '',
        flag_ignore_duration: 0,
      };

      await itineraryStore.putUpdateSchedule(data);
      handleRefreshItineraryCache();
    }
  }, 350);

  /*
  const updateTimeService = async (newTime) => {
    let start_time = typeof newTime[0] != 'undefined' ? newTime[0] : '';
    let departure_time = typeof newTime[1] != 'undefined' ? newTime[1] : '';

    const serviceId = itinerary.value.id;
    const data = {
      id: serviceId,
      type: 'itineraries',
      new_start_time: start_time,
      new_departure_time: departure_time,
      file_number: filesStore.getFile.fileNumber,
      itinerary_id: serviceId,
      frequency_code: '',
      flag_ignore_duration: 0,
    };

    await itineraryStore.putUpdateSchedule(data);
    handleRefreshItineraryCache();
  };
  */

  const handleServiceLogSave = () => {
    loadingNote.value = true;
    const note_id = serviceLogIdNote.value;
    const fileServiceId = file_id;
    const type_note = serviceLogTypeNote.value;
    const description = serviceLogDescription.value;
    const classification_code = serviceLogClassificationCode.value.trim();
    const serviceId = itinerary.value.id;
    if (description && classification_code) {
      const searchClassification = serviceNotesStore.classifications.find((e) => {
        return e.code.trim() == classification_code;
      });
      const classification_name = searchClassification.description;
      const dateItinerary = itinerary.value.date_in;
      const data = {
        type_note: type_note,
        record_type: 'FOR_DATE',
        assignment_mode: 'FOR_SERVICE',
        dates: JSON.stringify([dateItinerary]),
        description: description,
        classification_code: classification_code,
        classification_name: classification_name,
        created_by: 0,
        created_by: getUserId(),
        created_by_code: getUserCode(),
        created_by_name: getUserName(),
      };
      if (note_id == '') {
        serviceNotesStore
          .create({ id: serviceId, file_id: fileServiceId, data: data })
          .then((res) => {
            serviceLogIdNote.value = '';
            serviceLogTypeNote.value = 'INFORMATIVE';
            serviceLogDescription.value = '';
            serviceLogClassificationCode.value = '';
            loadingNote.value = false;
            // notification['success']({
            //   message: `Creación de Nota`,
            //   description: 'Creado correctamente',
            //   duration: 5,
            // });
            countNotes.value = countNotes.value + 1;

            socketsStore.send({
              success: true,
              type: 'update_note_itinerary',
              file_number: filesStore.getFile.fileNumber,
              file_id: filesStore.getFile.id,
              itinerary_id: serviceId,
              id: res.data.id,
              user_code: getUserCode(),
              user_id: getUserId(),
              message: 'Creación de nota',
              description: 'Se agrega una nota',
              entity: 'note',
            });
          })
          .catch((err) => {
            console.log(err);
            loadingNote.value = false;
          });
      } else {
        loadingNote.value = true;
        serviceNotesStore
          .update({ note_id: note_id, itinerary_id: serviceId, file_id: fileServiceId, data: data })
          .then((res) => {
            serviceLogIdNote.value = '';
            serviceLogTypeNote.value = 'INFORMATIVE';
            serviceLogDescription.value = '';
            serviceLogClassificationCode.value = '';
            loadingNote.value = false;
            // notification['success']({
            //   message: `Actualización de Nota`,
            //   description: 'Actualizado correctamente',
            //   duration: 5,
            // });
            console.log('respuesta', res);

            socketsStore.send({
              success: true,
              type: 'update_note_itinerary',
              file_number: filesStore.getFile.fileNumber,
              file_id: filesStore.getFile.id,
              itinerary_id: serviceId,
              id: res.data.id,
              user_code: getUserCode(),
              user_id: getUserId(),
              message: 'Actualización de nota',
              description: 'Se actualizo una nota',
              entity: 'note',
            });
          })
          .catch((err) => {
            console.log(err);
            loadingNote.value = false;
          });
      }
    } else {
      console.log(classification_code);
      loadingNote.value = false;
    }
  };

  const isGroupActive = (object_code) => {
    const groups = filesStore.getServiceGroups;
    return (groups[object_code] || []).map((group) => {
      return {
        label: `${group.grupo} - ${group.desgru}`,
        value: `${group.grupo}`,
      };
    });
  };

  watch(
    () => props.checkedCompuestos,
    (checkedCompuestos) => {
      isToggleCompuesto.value = checkedCompuestos;
      itinerary.value.flag_show = checkedCompuestos;
    }
  );

  const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
  };

  const isEditable = computed(() => {
    return dayjs(itinerary.value.dateIn) <= dayjs(new Date());
  });

  const toggleCompuestos = () => {
    isToggleCompuesto.value = !isToggleCompuesto.value;
    toggleShowItinerary();
  };

  const toggleShowItinerary = () => {
    const key = `itinerary_${itinerary.value.id}_flag_show`;
    itinerary.value.flag_show = !itinerary.value.flag_show;
    localStorage.setItem(key, itinerary.value.flag_show);
  };

  const calculateRooms = (_rooms) => {
    let total_rooms = 0;
    _rooms
      .filter((room) => room.status === 1)
      .forEach((_room) => {
        total_rooms += _room.total_rooms;
      });

    return total_rooms;
  };

  /*
  const searchPromotionHotels = async (_itinerary) => {
    if (!filesStore.isLoadingAsync) {
      showModalPromotionHotels.value = true;

      let client_id = localStorage.getItem('client_id');
      let lang = localStorage.getItem('lang');

      if (client_id == '' || client_id == null) {
        client_id = 15766;
      }

      let params = {
        client_id: client_id,
        date_from: _itinerary.date_in,
        date_to: _itinerary.date_out,
        quantity_persons_rooms: [],
        quantity_rooms: _itinerary.rooms.length,
        set_markup: 0,
        zero_rates: true,
        hotels_search_code: '',
        allWords: false,
        type_classes: [1],
        typeclass_id: 'all',
        lang: lang,
        price_range: {
          min: 0,
          max: 950,
        },
        destiny: {
          code: `PE,${_itinerary.city_in_iso}`,
          label: '',
        },
      };

      await filesStore.fetchHotels(params, true);
    } else {
      notification['warning']({
        message: `Búsqueda de hoteles con promoción`,
        description: 'Se está realizando la búsqueda, espere un momento..',
        duration: 5,
      });
    }
  };
  */

  const handleRefreshItineraryCache = () => {
    itinerary.value.isLoading = true;
  };

  const toggleModalInformation = async (itinerary) => {
    if (itinerary.entity == 'service') {
      showInformationService(itinerary, itinerary.object_id);
    }
    if (itinerary.entity == 'hotel') {
      showInformationHotel(itinerary);
    }
    if (itinerary.entity === 'service-temporary') {
      modalIsOpenInformation.value = true;
      modalInformation.value = itinerary;
    }
  };
</script>

<style scoped>
  /* Asegúrate de que las clases superiores no afecten la opacidad del badge */
  .files-edit__service-row-2-in-ope .ant-ribbon-wrapper {
    float: right;
    margin-right: -22px;
    margin-top: -5px;
  }

  .custom-alert :deep(.ant-alert-description) {
    color: #ff3b3b; /* Mismo rojo que tu icono */
    font-weight: 400;
  }

  .disabled-button {
    pointer-events: none !important;
    opacity: 0.5 !important;
    cursor: not-allowed !important;
  }

  .loading {
    position: absolute;
    top: 0;
    left: 0;
    text-align: center;
    background: hsla(0, 0%, 100%, 0.7);
    border-radius: 4px;
    z-index: 30;
    height: 100%;
    width: 100%;
  }

  .detail-note {
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
    border-radius: 6px;
    border: 1px solid #e9e9e9;
    padding: 5px 20px;
    margin-bottom: 10px;
    transition: all 0.3s ease-out;
  }

  .close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
  }

  /* Animaciones */
  .slide-fade-enter-active,
  .slide-fade-leave-active {
    transition: all 0.3s ease-out;
  }

  .slide-fade-enter-from,
  .slide-fade-leave-to {
    opacity: 0;
    transform: translate(-50%, -40%);
  }

  .svg-icon:hover {
    cursor: pointer;
    transition: fill 0.3s ease; /* Efecto suave */
  }
  .color-black {
    color: #3d3d3d;
    font-weight: 700;
  }
  .color-danger {
    color: #eb5757;
    font-weight: 700;
  }
</style>
