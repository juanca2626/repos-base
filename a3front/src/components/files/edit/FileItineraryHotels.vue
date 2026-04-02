<template>
  <template v-for="room in itinerary.rooms" :key="room.id">
    <a-row
      :class="[
        'itineraries mb-0 position-relative',
        room.flag_show ? 'active' : '',
        room.status === 0 ? 'active p-2' : '',
      ]"
      justify="space-between"
      align="middle"
      v-if="room.status !== 0 || room.amount > 0"
    >
      <div class="locked" v-if="room.status === 0"></div>
      <a-row justify="space-between" style="gap: 10px" align="middle" class="w-100">
        <a-col flex="auto" class="d-flex" style="gap: 7px">
          <span class="text-uppercase text-400">
            {{ room.room_name }}
          </span>
          <b class="text-uppercase">
            <small>{{ room.room_type }}</small>
          </b>
        </a-col>
        <a-col class="d-flex ant-row-middle ant-row-start" style="gap: 7px">
          <a-tag :bordered="true" color="purple"
            >N°
            <span class="text-lowercase"
              >{{ t('global.label._of') }} {{ t('global.label.units') }}</span
            >: <b>{{ room.total_rooms }}</b></a-tag
          >
          <div class="d-flex ant-row-middle" style="gap: 7px" v-if="room.status !== 0">
            <a href="javascript:;" class="text-danger mx-3" @click="toggleShowItinerary(room)">
              <b
                ><font-awesome-icon :icon="['fas', room.flag_show ? 'chevron-up' : 'chevron-down']"
              /></b>
            </a>
            <files-toggler-service-passengers
              @onRefreshItineraryCache="handleRefreshItineraryCache"
              :data="{
                type: 'room',
                passengers: passengers,
                object_id: room.id,
                itinerary: itinerary,
                max: room.total_adults,
              }"
            />
            <file-button-delete-item
              @onHandleGoToReservations="goToReservations"
              v-if="!itinerary.hyperguest"
              :data="{
                type: 'room',
                itinerary: itinerary,
                params: { room_id: room.id, hotel_id: itinerary.id },
              }"
            />
            <div style="display: flex; align-items: center; gap: 5px; font-size: 11px">
              <b>{{ room.total_adults }}</b>
              <span>ADL</span>
              <template v-if="room.total_children > 0">
                <b>{{ room.total_children }}</b>
                <span>CHD</span>
              </template>
            </div>
          </div>
        </a-col>
        <a-col class="d-flex ant-row-middle" style="gap: 7px">
          <template v-if="room.status === 0">
            <small class="text-danger text-600 z-100">
              {{ t('files.label.cancelled_room') }}
              <span class="text-lowercase">
                <template v-if="room.amount_sale > 0"> {{ t('global.label.with') }} </template>
                <template v-else> {{ t('global.label.without') }} </template>
                {{ t('files.label.penalty') }}
              </span>
            </small>
          </template>
          <template v-else>
            <template v-if="room.confirmation_status">
              <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" class="text-success" />
            </template>
            <template v-else>
              <a-tooltip>
                <template #title>
                  <small>{{ t('files.message.no_confirmation_room') }}</small>
                </template>
                <font-awesome-icon
                  :icon="['fas', 'triangle-exclamation']"
                  class="text-warning"
                  fade
                />
              </a-tooltip>
            </template>
          </template>
          <div class="d-flex" style="justify-content: flex-end; width: 120px">
            <files-toggler-service-amount
              v-if="room.status === 1"
              @onRefreshItineraryCache="handleRefreshItineraryCache"
              :data="{
                type: 'room',
                item: room,
                itinerary_id: itinerary.id,
              }"
            />
            <span class="text-danger" v-else>
              ${{ formatNumber({ number: room.amount_sale, digits: 2 }) }}
            </span>
          </div>
        </a-col>
      </a-row>
      <a-row
        justify="space-between"
        align="middle"
        class="w-100 d-block mb-3"
        v-if="room.flag_show"
      >
        <hr class="mb-3 w-100 border-0 height-1" style="background-color: #c4c4c4" />
        <a-row justify="space-between" align="middle" class="w-100" style="gap: 7px">
          <div class="d-flex">
            <svg
              class="feather feather-git-pull-request"
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
            <small class="text-600 mx-2 text-underline text-uppercase">
              {{ t('files.label.break_down_units') }}
            </small>
          </div>
        </a-row>

        <a-row
          justify="space-between"
          align="middle"
          class="w-100"
          v-for="(unit, u) in room.units"
          :key="u"
        >
          <a-row
            justify="space-between"
            align="middle"
            class="w-100"
            :class="['itineraries itineraries-gray', unit.flag_show ? 'open' : '']"
            v-if="room.flag_show"
          >
            <a-row
              justify="space-between"
              align="middle"
              class="w-100 my-1"
              style="padding: 0 15px; gap: 20px"
            >
              <a-col flex="auto">
                <span
                  style="
                    font-weight: 400;
                    font-size: 12px;
                    line-height: 19px;
                    text-transform: uppercase;
                  "
                >
                  {{ room.room_name }}
                </span>
              </a-col>
              <a-col>
                <a-col><span class="text-danger">|</span></a-col>
              </a-col>
              <a-col>
                <b style="font-size: 11px; line-height: 19px" class="text-uppercase">{{
                  room.room_type
                }}</b>
              </a-col>
              <a-col>
                <a-col><span class="text-danger">|</span></a-col>
              </a-col>
              <a-col>
                <a-row>
                  <template v-if="unit.nights.length > 0">
                    <a-col>
                      <span
                        class="text-uppercase"
                        style="font-weight: 400; font-size: 11px; line-height: 19px"
                      >
                        {{ textPad({ text: unit.nights.length, start: '0', length: 2 }) }}
                        {{ t('global.label.night')
                        }}<template v-if="unit.nights.length > 1">s</template>
                      </span>
                    </a-col>
                    <a-col>
                      <a href="javascript:;" class="text-danger mx-3" @click="toggleShowUnit(unit)">
                        <b
                          ><font-awesome-icon
                            :icon="['fas', unit.flag_show ? 'chevron-up' : 'chevron-down']"
                        /></b>
                      </a>
                    </a-col>
                  </template>
                </a-row>
              </a-col>
              <a-col class="d-flex">
                <files-toggler-service-passengers
                  @onRefreshItineraryCache="handleRefreshItineraryCache"
                  :data="{
                    type: 'unit',
                    passengers: data.passengers,
                    object_id: unit.id,
                    itinerary: itinerary,
                    max: unit.adult_num + unit.child_num,
                  }"
                />
                <span
                  style="
                    display: flex;
                    align-items: center;
                    margin-left: 20px;
                    font-weight: 400;
                    font-size: 12px;
                    line-height: 19px;
                    flex-grow: 1;
                  "
                >
                  <b>{{ unit.adult_num }}</b> &nbsp; ADL
                  <template v-if="unit.child_num > 0">
                    &nbsp; <b>{{ unit.child_num }}</b> &nbsp; CHD
                  </template>
                </span>
              </a-col>
              <a-col>
                <span>
                  <template v-if="room.confirmation_status">
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      size="lg"
                      class="text-success"
                    />
                  </template>
                  <template v-else>
                    <a-tooltip>
                      <template #title>
                        <small>{{ t('files.message.no_confirmation_room') }}</small>
                      </template>
                      <font-awesome-icon
                        :icon="['fas', 'triangle-exclamation']"
                        class="text-warning"
                        fade
                      />
                    </a-tooltip>
                  </template>
                </span>
              </a-col>
              <a-col>
                <div class="d-flex ant-row-middle" style="justify-content: end">
                  <div class="d-flex" style="justify-content: flex-end">
                    <span
                      style="font-weight: 600; font-size: 14px; line-height: 23px; color: #4f4b4b"
                    >
                      ${{ unit.amount_cost }}
                    </span>
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
                      <template #popover-content>{{
                        room.room_amount.file_amount_type_flag.description
                      }}</template>
                    </files-edit-field-static>
                  </div>
                </div>
              </a-col>
            </a-row>

            <a-row justify="space-between" align="middle" class="w-100" v-if="unit.flag_show">
              <hr class="m-1 w-100 border-0 height-1" style="background-color: #c4c4c4" />
              <a-row
                justify="space-between"
                align="middle"
                class="w-100 mx-4 mb-2"
                style="gap: 7px"
              >
                <div class="d-flex" style="gap: 5px">
                  <svg
                    class="feather feather-git-pull-request"
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
                    {{ t('files.label.break_down_nights') }}
                  </span>
                </div>
              </a-row>
              <a-row
                justify="space-between"
                align="middle"
                class="w-100 mx-5 mb-2"
                style="gap: 7px"
                v-for="(night, n) in unit.nights"
                :key="n"
              >
                <a-col>
                  <a-row style="gap: 7px">
                    <!-- a-col>
                      <span
                        style="
                          font-weight: 400;
                          font-size: 12px;
                          line-height: 19px;
                          text-transform: uppercase;
                        "
                      >
                        {{ room.room_name }}
                      </span>
                    </a-col>
                    <a-col>
                      <span class="text-danger">|</span>
                    </a-col>
                    <a-col>
                      <b style="font-size: 12px; line-height: 19px">{{ room.room_type }}</b>
                    </a-col>
                    <a-col>
                      <span class="text-danger">|</span>
                    </a-col -->
                    <!-- a-col>
                      <span style="font-weight: 400; font-size: 12px; line-height: 19px">01 noche</span>
                    </a-col -->
                    <a-col>
                      <span
                        style="font-weight: 400; font-size: 12px; line-height: 19px"
                        class="text-uppercase"
                      >
                        <font-awesome-icon :icon="['fas', 'moon']" />
                      </span>
                    </a-col>
                    <a-col>
                      <span style="font-size: 12px; line-height: 19px">
                        {{ formatDate(night.date, 'DD/MM/YYYY') }}
                      </span>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col class="d-flex">
                  <a-row justify="end" align="middle">
                    <a-col>
                      <span
                        style="font-weight: 600; font-size: 14px; line-height: 23px; color: #4f4b4b"
                      >
                        ${{ formatNumber({ number: night.total_amount_cost, digits: 2 }) }}
                      </span>
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
                        <template #popover-content>{{
                          room.room_amount.file_amount_type_flag.description
                        }}</template>
                      </files-edit-field-static>
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
            </a-row>
          </a-row>
        </a-row>
      </a-row>
    </a-row>
  </template>
</template>

<script setup>
  import { toRefs } from 'vue';
  import { formatDate, formatNumber, textPad } from '@/utils/files.js';
  import FilesTogglerServicePassengers from '@/components/files/edit/FilesTogglerServicePassengers.vue';
  import FilesTogglerServiceAmount from '@/components/files/edit/FilesTogglerServiceAmount.vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import FileButtonDeleteItem from '@/components/files/edit/FileButtonDeleteItem.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onRefreshItineraryCache', 'onHandleGoToReservations']);

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const { itinerary, passengers } = toRefs(props.data);

  const toggleShowItinerary = (_room) => {
    _room.flag_show = !(_room.flag_show ?? false);
    localStorage.setItem(`itinerary_room_${_room.id}_flag_show`, _room.flag_show);
  };

  const toggleShowUnit = (_unit) => {
    _unit.flag_show = !(_unit.flag_show ?? false);
    localStorage.setItem(`itinerary_room_unit_${_unit.id}_flag_show`, _unit.flag_show);
  };

  const goToReservations = () => {
    emit('onHandleGoToReservations');
  };

  const handleRefreshItineraryCache = () => {
    emit('onRefreshItineraryCache');
  };
</script>
