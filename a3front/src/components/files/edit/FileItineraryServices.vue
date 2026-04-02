<template>
  <template v-for="(service, item) in data.services" :key="item">
    <a-row
      :class="['itineraries', service.flag_show ? 'active p-2' : 'mb-0']"
      justify="space-between"
      align="middle"
    >
      <a-col>
        <a-row justify="space-between" style="gap: 7px" align="middle">
          <a-col v-if="isScheduleFrequences(service.code_ifx).length > 0 && 1 != 1">
            <span
              :class="['text-700', 'text-danger cursor-pointer']"
              :style="`border-bottom: 1px dashed #ccc`"
              v-if="!service.isEditable"
              @click="clickActiveSelectDate(service)"
            >
              <a-tooltip>
                <template #title>
                  <small class="text-uppercase">{{ t('files.label.modify_date') }}</small>
                </template>
                {{ formatDate(service.date_in, 'DD/MM/YYYY', locale) }}
              </a-tooltip>
            </span>
            <template v-else>
              <a-date-picker
                v-model:value="service.new_date_in"
                format="DD/MM/YYYY"
                :allowClear="false"
                :disabledDate="disabledDate"
                @blur="handleSelectDateBlur(service, false)"
                @change="handleSelectDateBlur(service, true)"
              />
            </template>
          </a-col>
          <a-col>
            <font-awesome-icon :icon="['far', 'clock']" style="color: c4c4c4" />
          </a-col>
          <a-col
            v-if="!service.isEditable"
            :style="`min-width: ${isScheduleFrequences(service.code_ifx).length > 0 || isGroupActive(service.code_ifx).length > 0 ? '360' : ''}px`"
          >
            <span v-if="!filesStore.isLoadingAsync">
              <template
                v-if="
                  isScheduleFrequences(service.code_ifx).length > 0 ||
                  isGroupActive(service.code_ifx).length > 0
                "
              >
                <template v-if="isScheduleFrequences(service.code_ifx).length > 0">
                  <a-select
                    size="small"
                    :allowClear="false"
                    class="w-100"
                    :value="`${service.start_time.substring(0, 5)} - ${service.departure_time.substring(0, 5)}`"
                    :showSearch="true"
                    @change="updateTimeServiceSchedule($event, 'services', service, 'frequency')"
                    :options="isScheduleFrequences(service.code_ifx)"
                  >
                  </a-select>
                </template>
                <template v-if="isGroupActive(service.code_ifx).length > 0">
                  <a-row type="flex" align="middle" justify="start" style="gap: 7px">
                    <a-col>
                      <a-select
                        size="small"
                        :allowClear="false"
                        v-model:value="service.frecuency_code"
                        :showSearch="true"
                        :fieldNames="{ label: 'label', value: 'value' }"
                        @change="setGroup(service.code_ifx, service.frecuency_code)"
                        placeholder="Seleccione"
                        :options="isGroupActive(service.code_ifx)"
                      >
                      </a-select>
                    </a-col>
                    <a-col v-if="isScheduleActive(service.code_ifx).length > 0">
                      <a-select
                        v-bind:disabled="isScheduleActive(service.code_ifx).length === 0"
                        size="small"
                        :allowClear="false"
                        class="w-100"
                        :value="`${service.start_time.substring(0, 5)} - ${service.departure_time.substring(0, 5)}`"
                        :showSearch="true"
                        @select="updateTimeServiceSchedule($event, 'services', service, 'group')"
                        :options="isScheduleActive(service.code_ifx)"
                      >
                      </a-select>
                    </a-col>
                  </a-row>
                </template>
              </template>
              <template v-else>
                <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                  <a-col>
                    <template v-if="itinerary.flag_schedule_group">
                      {{ formatTime(service.start_time) }}
                    </template>
                    <template v-else>
                      <base-input-time
                        :allowClear="false"
                        value-format="HH:mm"
                        placeholder=""
                        v-model="service.start_time"
                        :disabled="itineraryStore.isLoading"
                        format="HH:mm"
                        size="small"
                        @change="updateTimeServiceSingle($event, 'services', service, 'start_time')"
                      />
                    </template>
                  </a-col>
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-dark-gray" />
                  </a-col>
                  <a-col>
                    <template v-if="itinerary.flag_schedule_group">
                      {{ formatTime(service.departure_time) }}
                    </template>
                    <template v-else>
                      <base-input-time
                        :allowClear="false"
                        value-format="HH:mm"
                        v-model="service.departure_time"
                        :disabled="itineraryStore.isLoading"
                        placeholder=""
                        format="HH:mm"
                        size="small"
                        @change="
                          updateTimeServiceSingle($event, 'services', service, 'departure_time')
                        "
                      />
                    </template>
                  </a-col>
                </a-row>

                <!-- a-time-range-picker
                  value-format="HH:mm"
                  :value="timeRange(service.start_time, service.departure_time)"
                  placeholder=""
                  :disabled="itineraryStore.isLoading"
                  format="HH:mm"
                  size="small"
                  @change="updateTimeService($event, 'services', service)"
                / -->
              </template>
            </span>
          </a-col>
          <a-col>
            <span style="font-weight: 500">
              <a-tag color="red">
                <small class="text-600">{{ service.code_ifx }}</small>
              </a-tag>
              <a-tooltip>
                <template #title v-if="service.name.length > 30">
                  <small class="text-uppercase">{{ service.name }}</small>
                </template>
                <small class="text-uppercase">{{ truncateString(service.name, 30) }}</small>
              </a-tooltip>
            </span>
          </a-col>
        </a-row>
      </a-col>
      <a-col>
        <a-row justify="end" align="middle" style="gap: 7px">
          <template v-if="isAdmin()">
            <a-col>
              <a href="javascript:;" class="text-danger mx-3" @click="toggleShowItinerary(service)">
                <b
                  ><font-awesome-icon
                    :icon="['fas', service.flag_show ? 'chevron-up' : 'chevron-down']"
                /></b>
              </a>
            </a-col>
            <a-col>
              <!--<svg
                v-if="service.confirmation_status"
                style="color: #1ed790"
                class="feather feather-check-circle"
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
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <path d="M22 4 12 14.01l-3-3" />
              </svg>
              <svg
                v-else
                style="color: #ffcc00"
                class="feather feather-alert-triangle"
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
                <path
                  d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"
                />
              </svg>-->
              <file-button-replace-item
                @onHandleGoToReservations="goToReservations"
                :data="{
                  type: 'master_service',
                  itinerary: data.itinerary,
                  params: { master_service_id: service.id, service_id: data.itinerary.id },
                }"
              />
            </a-col>
            <a-col>
              <file-button-delete-item
                @onHandleGoToReservations="goToReservations"
                :data="{
                  type: 'master_service',
                  itinerary: data.itinerary,
                  params: { master_service_id: service.id, service_id: data.itinerary.id },
                }"
              />
              <!--<font-awesome-icon icon="fa-solid fa-ellipsis-vertical" />-->
            </a-col>
          </template>
          <a-col class="d-flex ant-row-end" style="width: 120px">
            <files-toggler-service-amount
              @onRefreshItineraryCache="handleRefreshItineraryCache"
              :data="{
                type: 'service',
                item: service,
                itinerary_id: data.itinerary.id,
              }"
            />
          </a-col>
        </a-row>
      </a-col>
    </a-row>

    <div class="mx-2 pt-1">
      <div
        v-if="service.flag_show"
        class="itineraries d-block p-2 active bg-white"
        style="margin-top: -15px"
      >
        <a-row justify="space-between" align="middle" class="mx-3 my-2" style="gap: 7px">
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
            <small class="text-600 mx-2 text-underline text-uppercase">
              {{ t('files.label.break_down_compositions') }}
            </small>
          </div>
        </a-row>

        <template
          v-for="composition in service.compositions"
          :key="`composition-${composition.id}`"
        >
          <div class="mx-3 my-1">
            <a-row justify="space-between" align="middle" class="itineraries m-0">
              <a-col>
                <a-row justify="space-between" style="gap: 7px" align="middle">
                  <a-col>
                    <font-awesome-icon :icon="['far', 'clock']" style="color: c4c4c4" />
                  </a-col>
                  <a-col>
                    <span>
                      <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                        <a-col>
                          <template v-if="itinerary.flag_schedule_group">
                            {{ formatTime(composition.start_time) }}
                          </template>
                          <template v-else>
                            <base-input-time
                              :allowClear="false"
                              value-format="HH:mm"
                              placeholder=""
                              v-model="composition.start_time"
                              :disabled="itineraryStore.isLoading"
                              format="HH:mm"
                              size="small"
                              @change="
                                updateTimeServiceSingle(
                                  $event,
                                  'services/compositions',
                                  composition,
                                  'start_time'
                                )
                              "
                            />
                          </template>
                        </a-col>
                        <a-col>
                          <font-awesome-icon
                            :icon="['fas', 'arrow-right']"
                            class="text-dark-gray"
                          />
                        </a-col>
                        <a-col>
                          <template v-if="itinerary.flag_schedule_group">
                            {{ formatTime(composition.departure_time) }}
                          </template>
                          <template v-else>
                            <base-input-time
                              :allowClear="false"
                              value-format="HH:mm"
                              v-model="composition.departure_time"
                              :disabled="itineraryStore.isLoading"
                              placeholder=""
                              format="HH:mm"
                              size="small"
                              @change="
                                updateTimeServiceSingle(
                                  $event,
                                  'services/compositions',
                                  composition,
                                  'departure_time'
                                )
                              "
                            />
                          </template>
                        </a-col>
                      </a-row>

                      <!-- a-time-range-picker
                        value-format="HH:mm"
                        :value="timeRange(composition.start_time, composition.departure_time)"
                        placeholder="H:m"
                        :disabled="itineraryStore.isLoading"
                        format="HH:mm"
                        size="small"
                        @change="updateTimeService($event, 'services/compositions', composition)"
                      / -->
                    </span>
                  </a-col>
                  <a-col>
                    <span style="font-weight: 500; font-size: 11px; line-height: 19px">
                      <a-tag color="red">{{ composition.code }}</a-tag> {{ composition.name }}</span
                    >
                  </a-col>
                </a-row>
              </a-col>
              <a-col style="gap: 7px">
                <a-row justify="end" align="middle" style="gap: 7px">
                  <template v-if="isAdmin()">
                    <a-col>
                      <!--<svg
                        v-if="service.confirmation_status"
                        style="color: #1ed790"
                        class="feather feather-check-circle"
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
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <path d="M22 4 12 14.01l-3-3" />
                      </svg>
                      <svg
                        v-else
                        style="color: #ffcc00"
                        class="feather feather-alert-triangle"
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
                        <path
                          d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"
                        />
                      </svg>-->
                      <file-button-replace-item
                        @onHandleGoToReservations="goToReservations"
                        :data="{
                          type: 'composition',
                          itinerary: data.itinerary,
                          params: {
                            composition_id: composition.id,
                            master_service_id: service.id,
                            service_id: data.itinerary.id,
                          },
                        }"
                      />
                    </a-col>
                    <a-col>
                      <file-button-delete-item
                        @onHandleGoToReservations="goToReservations"
                        :data="{
                          type: 'composition',
                          itinerary: data.itinerary,
                          params: {
                            composition_id: composition.id,
                            master_service_id: service.id,
                            service_id: data.itinerary.id,
                          },
                        }"
                      />
                      <!-- <font-awesome-icon icon="fa-solid fa-ellipsis-vertical" />-->
                    </a-col>
                  </template>
                  <a-col class="d-flex ant-row-end" style="width: 120px">
                    <span class="text-600">${{ composition.amount_cost }}</span>
                    <files-edit-field-static :inline="true" :hide-content="false">
                      <template #label>
                        <svg
                          v-if="service.service_amount.file_amount_type_flag_id === 1"
                          style="margin: 7px 0; color: #ffcc00; cursor: pointer"
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
                          v-if="service.service_amount.file_amount_type_flag_id === 2"
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
                          v-if="service.service_amount.file_amount_type_flag_id === 3"
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
                        {{ service.service_amount.file_amount_type_flag.description }}
                      </template>
                    </files-edit-field-static>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </div>
        </template>
      </div>
    </div>
  </template>
</template>

<script setup>
  import { toRefs, onBeforeMount } from 'vue';
  import { debounce } from 'lodash-es';
  import dayjs from 'dayjs';
  import FilesTogglerServiceAmount from '@/components/files/edit/FilesTogglerServiceAmount.vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import FileButtonReplaceItem from '@/components/files/edit/FileButtonReplaceItem.vue';
  import FileButtonDeleteItem from '@/components/files/edit/FileButtonDeleteItem.vue';
  import BaseInputTime from '@/components/files/reusables/BaseInputTime.vue';
  import { useItineraryStore, useFilesStore } from '@/stores/files';
  import { isAdmin } from '@/utils/auth';
  import { truncateString, formatDate, formatTime } from '@/utils/files.js';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const itineraryStore = useItineraryStore();
  const filesStore = useFilesStore();

  const emit = defineEmits(['onRefreshItineraryCache', 'onHandleGoToReservations']);

  const clickActiveSelectDate = (service) => {
    const new_date_in = JSON.parse(JSON.stringify(service.date_in));
    service.new_date_in = dayjs(new_date_in);

    setTimeout(() => {
      service.isEditable = true;
    }, 10);
  };

  const handleSelectDateBlur = async (service, flagUpdate) => {
    if (flagUpdate) {
      const newDate = dayjs(JSON.parse(JSON.stringify(service.new_date_in))).format('YYYY-MM-DD');

      await itineraryStore.putUpdateDate({
        type: 'master-service',
        fileId: filesStore.getFile.id,
        itineraryId: itinerary.value.id,
        serviceId: service.id,
        date: newDate,
      });
      service.date_in = newDate;
      localStorage.setItem(`ignore_itinerary_${itinerary.value.id}`, true);
    }

    setTimeout(() => {
      service.isEditable = false;
    }, 10);
  };

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const { itinerary } = toRefs(props.data);

  onBeforeMount(() => {
    props.data.services.map((service) => {
      if (isScheduleActive(service.code_ifx) && isScheduleActive(service.code_ifx).length > 0) {
        return false;
      }
      if (isGroupActive(service.code_ifx) && service.frecuency_code) {
        setGroup(service.code_ifx, service.frecuency_code);
      }
    });
  });

  /*
  const timeRange = (_start_time, _departure_time) => {
    let data = [];

    data.push(_start_time);
    data.push(_departure_time);

    return data;
  };
  */

  const toggleShowItinerary = (_service) => {
    _service.flag_show = !(_service.flag_show ?? false);
    localStorage.setItem(`itinerary_service_${_service.id}_flag_show`, _service.flag_show);
  };

  const handleRefreshItineraryCache = () => {
    emit('onRefreshItineraryCache');
  };

  const isScheduleFrequences = (code) => {
    const frequences = filesStore.getServiceFrequences;

    const options = (frequences[code] || []).map((option) => {
      return {
        value: `${option.horin} - ${option.horout}`,
        label: `${option.horin} - ${option.horout} | (${option.grupo}) ${option.desgru}`,
      };
    });

    return options;
  };

  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  const setGroup = async (code, group_code) => {
    await filesStore.searchGroupSchedule({ code: code, group_code: group_code });
    await sleep(500);
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

  const isScheduleActive = (code) => {
    const group_schedules = filesStore.getGroupSchedule;

    const schedule = (group_schedules[code] || []).map((schedule) => ({
      value: `${schedule.hordes} - ${schedule.horhas}`,
      label: `${schedule.hordes} - ${schedule.horhas}`,
    }));

    return schedule;
  };

  const updateTimeServiceSchedule = ($event, type, service, format) => {
    const schedule = $event;

    if (format === 'frequency') {
      const option = isScheduleFrequences(service.code_ifx)
        .find((option) => option.value === schedule)
        .label.split('|')[1];
      console.log(option);

      const match = option.match(/^(.*)\s\(([^)]+)\)$/);

      if (match) {
        const texto = match[1].trim();
        const codigo = match[2];

        service.frequency_name = texto;
        service.frequency_code = codigo;
      }
    }

    updateTimeService(schedule.split(' - '), type, service, 1);
  };

  const isValidTime = (value) => {
    const regex = /^([01]\d|2[0-3]):([0-5]\d)$/;
    return regex.test(value);
  };

  const updateTimeServiceSingle = debounce(async ($event, format, service, type) => {
    if (service[type].length === 5 && isValidTime(service[type])) {
      updateTimeService([service.start_time, service.departure_time], format, service);
    }
  }, 350);

  const updateTimeService = async (newTime, type, service, flag_ignore_duration) => {
    let start_time = typeof newTime[0] != 'undefined' ? newTime[0] : '';
    let departure_time = typeof newTime[1] != 'undefined' ? newTime[1] : '';

    const group = isGroupActive(service.code_ifx).find(
      (group) => group.value === service.frecuency_code
    );
    let frequency_code = null,
      frequency_name = null;

    if (group) {
      frequency_code = group.value;
      frequency_name = group.label.replace(`${group.value} - `, '').trim();
    } else {
      frequency_code = service.frequency_code ?? '';
      frequency_name = service.frequency_name ?? '';
    }

    const data = {
      id: service.id,
      new_start_time: start_time,
      new_departure_time: departure_time,
      type: type,
      file_number: filesStore.getFile.fileNumber,
      itinerary_id: props.data.itinerary.id,
      frequency_code: frequency_code,
      frequency_name: frequency_name,
      flag_ignore_duration: flag_ignore_duration ?? 0,
    };

    await itineraryStore.putUpdateSchedule(data);
    handleRefreshItineraryCache();
  };

  const goToReservations = () => {
    emit('onHandleGoToReservations');
  };
</script>
