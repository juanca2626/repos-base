<template>
  <template v-if="filesStore.isLoading">
    <loading-skeleton />
  </template>
  <template v-else>
    <div class="files-edit__sort">
      <div class="files-edit__sort-col1">
        <template v-if="activeKey !== '3'">
          <base-popover placement="topLeft" v-if="!showScheduledServices">
            <a-button
              @click="toggleCompactList"
              class="btn-default"
              type="default"
              default
              :disabled="filesStore.isLoading || filesStore.getFile.status == 'xl'"
              size="large"
            >
              <font-awesome-icon v-if="toggleItineraryListCompact" icon="bars" />
              <font-awesome-icon v-else icon="table-list" />
            </a-button>
            <template #content>{{ t('files.label.program_change_view') }}</template>
          </base-popover>
          <template v-if="filesStore.getFile.revisionStages === 2">
            <base-popover placement="topLeft" v-if="!showScheduledServices">
              <a-button
                @click="viewScheduledServices()"
                class="btn-default"
                type="default"
                default
                :disabled="filesStore.isLoading || filesStore.getFile.status == 'xl'"
                size="large"
              >
                <font-awesome-icon :icon="['fas', 'book-open']" class="text-dark-gray" size="lg" />
              </a-button>
              <template #content>{{ t('files.label.programmed_services') }}</template>
            </base-popover>
          </template>
          <base-select
            :options="optionsSort"
            size="large"
            width="210"
            :disabled="filesStore.isLoading || filesStore.getFile.status == 'xl'"
            :placeholder="t('files.label.sort_program')"
            @change="orderingItineraries"
            v-model:value="selectedItineraryListFilter"
            v-if="!showScheduledServices"
          />
          <span
            :class="[
              'cursor-pointer files-switch-serie',
              checkedCompuestos ? 'text-danger' : 'text-gray',
            ]"
            v-if="!toggleItineraryListCompact || !showScheduledServices"
          >
            <span @click="toggleComposables">
              <font-awesome-icon :icon="['fas', 'toggle-on']" v-if="checkedCompuestos" size="2xl" />
              <font-awesome-icon :icon="['fas', 'toggle-off']" v-else size="2xl" />
            </span>
            <b
              class="files-switch-serie-label text-uppercase"
              v-if="!checkedCompuestos"
              @click="toggleComposables"
              >{{ t('files.label.breaking_down_compounds') }}</b
            >
            <b
              class="files-switch-serie-label text-uppercase"
              v-if="checkedCompuestos"
              @click="toggleComposables"
              >{{ t('files.label.collapse_compounds') }}</b
            >
          </span>
        </template>
      </div>

      <div class="files-edit__sort-col2" style="gap: 10px" v-if="!showScheduledServices">
        <template v-if="listItineraries.length > 0">
          <div
            @click="copyLinkToClipboard()"
            v-if="activeKey === '3'"
            style="cursor: pointer; display: flex; justify-content: center; align-items: center"
          >
            <font-awesome-icon :icon="['fas', 'link']" size="xl" class="text-danger" />
          </div>
        </template>

        <base-popover placement="topRight" v-if="listItineraries.length > 0">
          <a-button
            type="default"
            default
            size="large"
            class="text-600"
            v-on:click="deleteMultiple"
            :disabled="
              filesStore.isLoading ||
              filesStore.getFile.status == 'xl' ||
              filesStore.getItinerariesTrash.length == 0
            "
          >
            <font-awesome-icon :icon="['far', 'trash-can']" />
          </a-button>
          <template #content>{{ t('files.label.select_multiple_to_delete') }}</template>
        </base-popover>

        <popover-hover-and-click v-if="activeKey != '3'" placement-click="bottom">
          <a-button
            type="primary"
            default
            size="large"
            class="text-600 text-capitalize"
            :disabled="
              filesStore.isLoading ||
              filesStore.getFile.status === 'xl' ||
              filesStore.getFile.status === 'ce' ||
              filesStore.getFile.status === 'bl' ||
              filesStore.getFilePendingProcesses
            "
          >
            <a-tooltip>
              <template #title v-if="filesStore.getFilePendingProcesses">{{
                t('files.message.title_system_failed')
              }}</template>
              {{ t('files.button.add') }}
            </a-tooltip>
          </a-button>
          <template #content-hover>{{ t('files.button.add_hotels_or_services') }}</template>
          <template #content-click>
            <div class="files-popover-add-menu">
              <a-button
                class="files-popover-add-button text-capitalize"
                type="text"
                size="small"
                v-on:click="handleGoTo('add-new-hotel')"
              >
                <template #icon>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    class="feather feather-home"
                    viewBox="0 0 24 24"
                  >
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <path d="M9 22V12h6v10" />
                  </svg>
                </template>
                {{ t('files.button.add') }} {{ t('files.button.hotel') }}
              </a-button>
              <a-button
                class="files-popover-add-button text-capitalize"
                type="text"
                size="small"
                v-on:click="handleGoTo('add-new-service')"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  class="feather feather-plus-square"
                  viewBox="0 0 24 24"
                >
                  <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                  <path d="M12 8v8M8 12h8" />
                </svg>
                {{ t('files.button.add') }} {{ t('files.button.service') }}
              </a-button>
              <a-button
                class="files-popover-add-button text-capitalize"
                type="text"
                size="small"
                v-on:click="showAddFlight()"
              >
                <template #icon>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    class="feather feather-airplane"
                    viewBox="0 0 24 24"
                  >
                    <path d="M21 16l-9-5-9 5m18-5l-9-5-9 5m9-5v10" />
                  </svg>
                </template>
                {{ t('files.button.add') }} {{ t('files.button.flight') }}
              </a-button>
            </div>
          </template>
          <template #content-buttons>
            <div class="d-none">&nbsp;</div>
          </template>
        </popover-hover-and-click>

        <template v-else>
          <a-button
            type="primary"
            default
            size="large"
            class="text-600"
            v-on:click="showAddFlight()"
            :disabled="filesStore.isLoading || filesStore.getFile.status == 'xl'"
          >
            {{ t('files.button.add') }}
          </a-button>
        </template>
      </div>
    </div>

    <a-row
      type="flex"
      justify="space-between"
      align="middle"
      class="bg-light px-4 py-2 my-4"
      style="gap: 15px; border-radius: 6px"
      v-if="
        (filesStore.getFileItineraries.filter((itinerary) => itinerary.entity === 'flight').length >
          0 &&
          activeKey === '3') ||
        activeKey !== '3'
      "
    >
      <template v-if="false">
        <a-col v-if="!(activeKey === '3')" class="d-flex ant-row-middle">
          <a-tooltip>
            <span class="me-3 text-dark-gray">
              <font-awesome-icon :icon="['fas', 'map-location-dot']" size="lg" />
            </span>
            <span v-for="(city, c) in uniqueCities">
              <a-badge
                :offset="[-12, -3]"
                :count="
                  filesStore.getFileItineraries.filter(
                    (itinerary) => itinerary.city_in_name === city && itinerary.validationOpe
                  ).length
                "
              >
                <a-tag
                  :color="
                    filesStore.getFileItineraries.filter(
                      (itinerary) => itinerary.city_in_name === city && itinerary.validationOpe
                    ).length
                      ? 'warning'
                      : 'success'
                  "
                >
                  <small class="text-uppercase text-700">{{ city }}</small>
                </a-tag>
              </a-badge>
            </span>
          </a-tooltip>
        </a-col>
      </template>
      <a-col flex="auto">
        <a-row type="flex" align="middle" justify="end" style="gap: 10px">
          <template v-if="!(activeKey === '3')">
            <a-col
              v-if="
                filesStore.getFileItineraries.filter((itinerary) => itinerary.entity === 'hotel')
                  .length > 0
              "
            >
              <a-tooltip>
                <template #title v-if="false">
                  <small class="text-uppercase">{{ t('files.button.hotel') }}</small>
                </template>
                <small class="text-500 text-uppercase">
                  <font-awesome-icon :icon="['fas', 'hotel']" class="text-gray me-1" size="lg" />
                  <span class="text-gray mx-1">{{ t('files.button.hotel') }}</span>
                  <a-badge
                    :count="
                      filesStore.getFileItineraries.filter(
                        (itinerary) => itinerary.entity === 'hotel'
                      ).length
                    "
                    :number-style="{
                      backgroundColor: '#fff',
                      color: '#575757',
                      boxShadow: '0 0 0 1px #E9E9E9 inset',
                    }"
                  />
                </small>
              </a-tooltip>
            </a-col>
            <a-col
              v-if="
                filesStore.getFileItineraries.filter(
                  (itinerary) =>
                    itinerary.entity === 'service' || itinerary.entity === 'service-mask'
                ).length > 0
              "
            >
              <a-tooltip>
                <template #title v-if="false">
                  <small class="text-uppercase">{{ t('files.button.service') }}</small>
                </template>
                <small class="text-500 text-uppercase">
                  <font-awesome-icon :icon="['fas', 'star']" class="text-gray me-1" size="lg" />
                  <span class="text-gray mx-1">{{ t('files.button.service') }}</span>
                  <a-badge
                    :count="
                      filesStore.getFileItineraries.filter(
                        (itinerary) =>
                          itinerary.entity === 'service' || itinerary.entity === 'service-mask'
                      ).length
                    "
                    :number-style="{
                      backgroundColor: '#fff',
                      color: '#575757',
                      boxShadow: '0 0 0 1px #E9E9E9 inset',
                    }"
                  />
                </small>
              </a-tooltip>
            </a-col>
          </template>
          <a-col
            v-if="
              filesStore.getFileItineraries.filter((itinerary) => itinerary.entity === 'flight')
                .length > 0
            "
          >
            <a-tooltip>
              <template #title v-if="false">
                <small class="text-uppercase">{{ t('files.button.flight') }}</small>
              </template>
              <small class="text-500 text-uppercase">
                <font-awesome-icon
                  :icon="['fas', 'jet-fighter']"
                  class="text-gray me-1"
                  size="lg"
                />
                <span class="text-gray mx-1">{{ t('files.button.flight') }}</span>
                <a-badge
                  :count="
                    filesStore.getFileItineraries.filter(
                      (itinerary) => itinerary.entity === 'flight'
                    ).length
                  "
                  :number-style="{
                    backgroundColor: '#fff',
                    color: '#575757',
                    boxShadow: '0 0 0 1px #E9E9E9 inset',
                  }"
                />
              </small>
            </a-tooltip>
          </a-col>
        </a-row>
      </a-col>
    </a-row>

    <a-alert class="mt-3" v-if="filesStore.getFilePendingProcesses" type="error">
      <template #message>
        <b class="text-danger">{{ t('files.message.title_system_failed') }}</b>
      </template>
      <template #description>
        <p class="text-danger text-500">
          {{ t('files.message.content_system_failed') }}
          <br />
          <b>{{ t('files.message.report_system_failed') }}:</b>
          {{ t(`files.error.${filesStore.getFileProcess}`) }}
        </p>
        <p class="text-danger text-500">
          {{ t('files.message.thanks_system_failed') }}
        </p>
      </template>
    </a-alert>

    <div v-if="!showScheduledServices" class="files-edit__services">
      <template v-if="listItineraries.length === 0">
        <a-empty :description="null" class="py-3 my-3 bg-light" />
      </template>
      <template
        v-if="
          socketsStore.getNotifications.filter((item) => item.type === 'processing-reservation')
            .length > 0
        "
      >
        <a-alert type="warning" class="text-dark-warning">
          <template #message>
            <a-row type="flex" style="gap: 10px; flex-flow: nowrap" align="middle" justify="start">
              <a-col>
                <LoadingMaca size="small" />
              </a-col>
              <a-col>
                <font-awesome-icon :icon="['fas', 'gear']" spin-pulse size="xl" />
              </a-col>
              <a-col>
                <p class="mb-0 text-600">{{ t('files.message.title_loading_maca') }}</p>
                <p class="mb-0">
                  {{ t('files.message.content_loading_maca') }}
                </p>
              </a-col>
            </a-row>
          </template>
        </a-alert>
      </template>
      <template :key="itinerary.id" v-for="itinerary in listItineraries">
        <files-edit-service
          @onHandleGoToReservations="goToReservations"
          @onHandleRefreshCache="handleRefreshCache"
          :toggleServices="toggleItineraryListCompact"
          :itinerary="itinerary"
          :activeKeyItineraries="activeKey"
          :checked-compuestos="checkedCompuestos"
        />
      </template>
    </div>

    <!-- Vista de servicios programados -->
    <div v-if="showScheduledServices">
      <ScheduledServicesPage @onBack="goBackItineraryPage" />
    </div>

    <a-modal v-model:visible="modalAddFlight" style="width: 600px">
      <template #title>
        <div class="modal-title">
          <font-awesome-icon :icon="['fas', 'plane']" size="lg" class="me-2 text-danger" />
          <span class="title-add-flight"
            >{{ t('files.button.add') }} {{ t('files.button.flight') }}</span
          >
        </div>
      </template>

      <div class="flight-form">
        <a-alert type="warning" v-if="itineraryStore.isLoading || flightStore.isLoading">
          <template #description>
            {{ t('files.message.loading') }}.. {{ t('files.message.waiting') }}.
          </template>
        </a-alert>

        <div class="flight-options">
          <a-radio-group v-model:value="codeFlight" @change="clearSearches">
            <a-radio value="AEIFLT"
              ><span class="text-capitalize">{{ t('global.label.international') }}</span></a-radio
            >
            <a-radio value="AECFLT"
              ><span class="text-capitalize">{{ t('global.label.domestic') }}</span></a-radio
            >
          </a-radio-group>
        </div>

        <div class="back-gray">
          <div class="form-row">
            <div class="form-group" v-if="codeFlight === 'AEIFLT'">
              <label>Origen</label>
              <a-select
                v-model:value="flight_origin"
                showSearch
                label-in-value
                placeholder="Selecciona Origen"
                :filter-option="false"
                :not-found-content="select_search_origin_international.fetching ? undefined : null"
                :options="select_search_origin_international.data"
                @search="search_destiny"
                @change="clearSearches"
                :disabled="itineraryStore.isLoading || flightStore.isLoading"
              >
                <template v-if="select_search_origin_international.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
            <div class="form-group" v-if="codeFlight === 'AEIFLT'">
              <label>Destino</label>
              <a-select
                v-model:value="flight_destiny"
                showSearch
                label-in-value
                placeholder="Selecciona Destino"
                :filter-option="false"
                :not-found-content="select_search_origin_international.fetching ? undefined : null"
                :options="select_search_origin_international.data"
                @search="search_destiny"
                @change="clearSearches"
                :disabled="itineraryStore.isLoading || flightStore.isLoading"
              >
                <template v-if="select_search_origin_international.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>

            <div class="form-group" v-if="codeFlight === 'AECFLT'">
              <label>Origen</label>
              <a-select
                v-model:value="flight_origin_domestic"
                showSearch
                label-in-value
                placeholder="Selecciona Origen"
                :filter-option="false"
                :not-found-content="select_search_origin_domestic.fetching ? undefined : null"
                :options="select_search_origin_domestic.data"
                @search="search_domestic_destiny"
                @change="clearSearches"
                :disabled="itineraryStore.isLoading || flightStore.isLoading"
              >
                <template v-if="select_search_origin_domestic.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
            <div class="form-group" v-if="codeFlight === 'AECFLT'">
              <label>Destino</label>
              <a-select
                v-model:value="flight_destiny_domestic"
                showSearch
                label-in-value
                placeholder="Selecciona Destino"
                :filter-option="false"
                :not-found-content="select_search_origin_domestic.fetching ? undefined : null"
                :options="select_search_origin_domestic.data"
                @search="search_domestic_destiny"
                @change="clearSearches"
                :disabled="itineraryStore.isLoading || flightStore.isLoading"
              >
                <template v-if="select_search_origin_domestic.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="fecha">{{ t('global.column.date') }}</label>
              <a-date-picker
                v-model:value="date_flight"
                id="date_flight"
                :defaultPickerValue="dayjs(filesStore.getFile.dateIn)"
                :format="dateFormat"
                @change="changeDate"
                :disabled="itineraryStore.isLoading || flightStore.isLoading"
                :disabled-date="disabledDate"
              />
            </div>

            <div class="form-group">
              <a-row type="flex" justify="space-between" align="middle">
                <a-col>
                  <label for="passengers" class="d-block text-capitalize"
                    >{{ t('global.label.passengers') }} <b class="text-danger">*</b></label
                  >
                </a-col>
                <a-col>
                  <span class="cursor-pointer me-2" @click="selectAllPassengers">
                    <font-awesome-icon :icon="['fas', 'user-check']" />
                  </span>
                </a-col>
              </a-row>
              <a-tooltip
                :title="
                  flight_disabled || itineraryStore.isLoading || flightStore.isLoading
                    ? t('global.message.you_must_select_a_date')
                    : ''
                "
                placement="top"
                :visible="showDateTooltip"
              >
                <a-select
                  v-model:value="flight_passengers"
                  mode="multiple"
                  style="width: 100%"
                  max-tag-count="responsive"
                  :options="passenger_options"
                  :disabled="flight_disabled || itineraryStore.isLoading || flightStore.isLoading"
                >
                </a-select>
              </a-tooltip>
            </div>
          </div>

          <div class="form-row w-100">
            <div class="form-group">
              <label for=""> PNR </label>
              <a-input
                :disabled="flightStore.isLoading"
                size="middle"
                type="text"
                min="0"
                class="form-control input-flight"
                v-model:value="flightPnr"
                placeholder="Escribe aquí .."
                maxlength="20"
              />
            </div>
            <div class="form-group">
              <label for=""> {{ t('files.label.airline') }} </label>
              <a-select
                size="middle"
                :options="airlinesOptions.data"
                v-model:value="flightAirlineCode"
                label="name"
                placeholder="Seleccione"
                showSearch
                label-in-value
                :filter-option="false"
                :not-found-content="airlinesOptions.fetching ? undefined : null"
                @search="searchAirline"
              >
                <template v-if="airlinesOptions.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
          </div>

          <div class="form-row w-100">
            <div class="form-group">
              <label for=""> {{ t('files.label.number_flight') }} </label>
              <a-input
                v-model:value="flightAirlineNumber"
                :disabled="flightStore.isLoading"
                size="middle"
                type="text"
                min="0"
                class="form-control input-flight"
                placeholder="Escribe aquí .."
                maxlength="40"
              />
            </div>
            <div class="form-group">
              <label for=""> {{ t('files.label.arrival_departure_hour') }} </label>
              <a-row type="flex" align="middle" justify="space-between">
                <a-col>
                  <font-awesome-icon :icon="['fas', 'plane-departure']" class="text-dark-gray" />
                </a-col>
                <a-col>
                  <base-input-time
                    :allowClear="false"
                    value-format="HH:mm"
                    v-model="flightDepartureTime"
                    format="HH:mm"
                  />
                </a-col>
                <a-col>
                  <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-gray" />
                </a-col>
                <a-col>
                  <font-awesome-icon :icon="['fas', 'plane-arrival']" class="text-dark-gray" />
                </a-col>
                <a-col>
                  <base-input-time
                    :allowClear="false"
                    value-format="HH:mm"
                    v-model="flightArrivalTime"
                    format="HH:mm"
                  />
                </a-col>
              </a-row>
              <!-- a-time-range-picker
                value-format="HH:mm"
                v-model:value="flightTimeRange"
                format="HH:mm"
              / -->
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="text-center">
          <a-button
            type="default"
            default
            class="text-600"
            @click="handleCancel"
            v-bind:disabled="
              itineraryStore.isLoading || itineraryStore.isLoadingAsync || flightStore.isLoading
            "
            size="large"
          >
            {{ t('global.button.cancel') }}
          </a-button>
          <a-button
            type="primary"
            primary
            class="text-600"
            :loading="loading"
            @click="saveFlight"
            v-bind:disabled="
              !isFormValid ||
              itineraryStore.isLoading ||
              itineraryStore.isLoadingAsync ||
              flightStore.isLoading
            "
            size="large"
          >
            {{ t('files.button.add') }} {{ t('files.button.flight') }}
          </a-button>
        </div>
      </template>
    </a-modal>
  </template>
</template>

<script setup>
  import { ref, onMounted, reactive, computed, toRefs, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import FilesEditService from '@/components/files/edit/FilesEditService.vue';
  import useOrigins from '@/quotes/composables/useOrigins';
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';
  import { debounce } from 'lodash-es';
  import { useI18n } from 'vue-i18n';

  import { useFilesStore, useItineraryStore, useFlightStore } from '@store/files';
  import dayjs from 'dayjs';
  import ScheduledServicesPage from '@/components/files/scheduled-services/page/ScheduledServicesPage.vue';
  import { notification } from 'ant-design-vue';
  import BaseInputTime from '@/components/files/reusables/BaseInputTime.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';

  import { useSocketsStore } from '@/stores/global';
  const socketsStore = useSocketsStore();

  const { t } = useI18n({
    useScope: 'global',
  });

  const showScheduledServices = ref(false);

  const uniqueCities = computed(() => {
    const cities = filesStore.getFileItineraries
      .filter((itinerary) => itinerary.entity !== 'flight')
      .map((itinerary) => itinerary.city_in_name);
    return [...new Set(cities)]; // elimina duplicados
  });

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });
  const { activeKey } = toRefs(props.data);

  const emit = defineEmits(['onHandleGoToReservations', 'onRefreshCache']);

  const goToReservations = () => {
    emit('onHandleGoToReservations');
  };

  const handleRefreshCache = () => {
    emit('onRefreshCache');
  };

  // const serviceNotesStore = useServiceNotesStore()

  const copyLinkToClipboard = () => {
    let nrofile = filesStore.getFile.fileNumber;
    let lang = localStorage.getItem('lang');

    const link = `${window.url_app}register_flights/${nrofile}?lang=${lang}`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(link).then(() => {
        notification.success({
          message: 'Éxito',
          description: 'Enlace copiado al portapapeles',
        });
      });
    } else {
      notification.error({
        message: 'Falló',
        description: 'Clipboard API not available on Local',
      });
    }

    setTimeout(() => {
      window.open(link);
    }, 350);
  };

  const { origins, getOrigins, getOriginsDomestic } = useOrigins();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const flightStore = useFlightStore();
  const codeFlight = ref('AEIFLT');
  const flight_origin = ref(null);
  const flight_destiny = ref(null);
  const flight_origin_domestic = ref(null);
  const flight_destiny_domestic = ref(null);
  const date_flight = ref('');
  const flight_passengers = ref([]);

  const flightPnr = ref('');
  const flightAirlineCode = ref('');
  const flightAirlineNumber = ref('');
  const flightDepartureTime = ref('');
  const flightArrivalTime = ref('');

  const dateFormat = 'DD/MM/YYYY';
  const optionsSort = ref([
    { label: 'Fecha', value: 'date' },
    { label: 'Sedes orden falfabético', value: 'fecha' },
    { label: 'Servicios confirmados', value: 'servicios-confirmados' },
    { label: 'Servicios RQ', value: 'servicios-rq' },
  ]);

  const airlinesOptions = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const select_search_origin_international = reactive({
    data: [],
    value: [],
    fetching: false,
  });
  const select_search_origin_domestic = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const listItineraries = computed(() => {
    if (activeKey.value === '3') {
      return filesStore.getFileItineraries.filter((it) => it.entity === 'flight');
    }
    return filesStore.getFileItineraries;
  });

  const ini = async () => {
    filesStore.clearItinerariesTrash();
  };

  const isFormValid = computed(() => {
    return (
      date_flight.value !== null &&
      flight_passengers.value.length > 0 &&
      ((codeFlight.value === 'AEIFLT' &&
        flight_origin.value !== undefined &&
        flight_destiny.value !== undefined &&
        flight_origin.value !== '' &&
        flight_destiny.value !== '' &&
        flight_origin.value !== null &&
        flight_destiny.value !== null) ||
        (codeFlight.value === 'AECFLT' &&
          flight_origin_domestic.value !== undefined &&
          flight_destiny_domestic.value !== undefined &&
          flight_origin_domestic.value !== '' &&
          flight_destiny_domestic.value !== '' &&
          flight_origin_domestic.value !== null &&
          flight_destiny_domestic.value !== null))
    );
  });

  const selectAllPassengers = () => {
    flight_passengers.value = passenger_options.value.map((passenger) => {
      return passenger.value;
    });
  };

  const clearSearches = () => {
    if (codeFlight.value === 'AEIFLT') {
      search_destiny('');
    }

    if (codeFlight.value === 'AECFLT') {
      search_domestic_destiny('');
    }
    changeDate();
  };

  watch(codeFlight, (newValue, oldValue) => {
    if (newValue !== oldValue) {
      origins.value = [];
    }
  });

  const search_destiny = debounce(async (value) => {
    select_search_origin_international.data = [];
    select_search_origin_international.fetching = true;

    if ((origins.value.length === 0 && value === '') || value !== '') {
      await getOrigins(value.toUpperCase());
    }

    let code_in_use =
      flight_origin.value?.value !== undefined && flight_origin.value?.value !== ''
        ? flight_origin.value.value
        : flight_destiny.value?.value !== undefined && flight_destiny.value?.value !== ''
          ? flight_destiny.value.value
          : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: code_in_use !== '' ? row.code === code_in_use : false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
    }));

    select_search_origin_international.data = results;
    select_search_origin_international.fetching = false;
  }, 300);

  const search_domestic_destiny = debounce(async (value) => {
    select_search_origin_domestic.data = [];
    select_search_origin_domestic.fetching = true;

    if ((origins.value.length === 0 && value === '') || value !== '') {
      await getOriginsDomestic(value.toUpperCase());
    }

    let code_in_use =
      flight_origin_domestic.value?.value !== undefined &&
      flight_origin_domestic.value?.value !== ''
        ? flight_origin_domestic.value.value
        : flight_destiny_domestic.value?.value !== undefined &&
            flight_destiny_domestic.value?.value !== ''
          ? flight_destiny_domestic.value.value
          : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: code_in_use !== '' ? row.code === code_in_use : false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
    }));

    select_search_origin_domestic.data = results;
    select_search_origin_domestic.fetching = false;
  }, 300);

  let flight_disabled = true;
  const passenger_options = computed(() => {
    return filesStore.getFilePassengers.map((row, index) => ({
      label:
        (row.name ? row.name : 'Pasajero ' + (index + 1)) +
        ' (' +
        row.type +
        ') ' +
        (row.type == 'CHD' ? ' (' + calculateAge(row.date_birth) + 'y)' : ''),
      value: row.id,
      type: row.type,
      disabled: false,
      age_child: row.type == 'CHD' ? calculateAge(row.date_birth) : 0,
    }));
  });
  const calculateAge = (birthdate) => {
    // Convertir la cadena de fecha a un objeto Date
    const birthDate = new Date(birthdate);

    // Obtener la fecha actual
    const today = new Date();

    // Calcular la diferencia en años
    let age = today.getFullYear() - birthDate.getFullYear();

    // Ajustar la edad si la fecha de nacimiento aún no ha ocurrido este año
    const monthDifference = today.getMonth() - birthDate.getMonth();
    const dayDifference = today.getDate() - birthDate.getDate();

    if (monthDifference < 0 || (monthDifference === 0 && dayDifference < 0)) {
      age--;
    }

    return age;
  };
  const disabledDate = (current) => {
    // Deshabilita todas las fechas anteriores al día actual
    return (
      current &&
      (current < new Date().setHours(0, 0, 0, 0) || current < dayjs(filesStore.getFile.dateIn))
    );
  };

  const changeDate = () => {
    if (date_flight.value) {
      flight_disabled = false;

      let data_ = listItineraries.value;
      let selectedOptions = [];
      passenger_options.value.forEach((option, op_index) => {
        passenger_options.value[op_index].disabled = false;
      });

      data_.forEach((s) => {
        if (s.entity === 'flight') {
          if (
            dayjs(s.date_in).format('YYYY-MM-DD') ===
              dayjs(date_flight.value).format('YYYY-MM-DD') &&
            s.object_code === codeFlight.value
          ) {
            s.flights.forEach((fli) => {
              fli.accommodations.forEach((accomodation) => {
                selectedOptions.push(accomodation.file_passenger_id);
              });
            });

            passenger_options.value.forEach((option, op_index) => {
              if (selectedOptions.includes(option.value)) {
                passenger_options.value[op_index].disabled = true;
              } else {
                passenger_options.value[op_index].disabled = false;
              }
            });
          }
        }
      });

      flight_passengers.value = passenger_options.value
        .filter((option) => !option.disabled)
        .map((option) => option.value);
    } else {
      flight_disabled = true;
    }
  };

  const selectedItineraryListFilter = ref(null);

  const toggleItineraryListCompact = ref(true);

  const checkedCompuestos = ref(false);

  const modalAddFlight = ref(false);

  const file_id = filesStore.getFile.id;

  const showAddFlight = async () => {
    clearForm();
    clearSearches();

    await flightStore.getListAirlines({ query: '' });
    airlinesOptions.data = flightStore.listAirlines;

    modalAddFlight.value = true;
  };

  const handleCancel = () => {
    modalAddFlight.value = false;
    clearForm();
  };

  const clearForm = () => {
    codeFlight.value = 'AEIFLT';
    flight_origin.value = null;
    flight_origin_domestic.value = null;
    flight_destiny.value = null;
    flight_destiny_domestic.value = null;
    date_flight.value = '';
    flight_passengers.value = [];

    flightPnr.value = '';
    flightAirlineCode.value = '';
    flightAirlineNumber.value = '';
    flightDepartureTime.value = '';
    flightArrivalTime.value = '';
  };

  const saveFlight = async () => {
    let itineraries_ = listItineraries.value;
    let itinerary_id_ = '';

    itineraries_.forEach((s) => {
      if (s.entity === 'flight') {
        if (
          dayjs(s.date_in).format('YYYY-MM-DD') === dayjs(date_flight.value).format('YYYY-MM-DD') &&
          s.object_code == codeFlight.value
        ) {
          // domestic = AECFLT / intern = AEIFLT
          if (
            (flight_origin.value?.value == s.city_in_iso &&
              flight_destiny.value?.value == s.city_out_iso &&
              codeFlight.value === 'AEIFLT') ||
            (flight_origin_domestic.value?.value == s.city_in_iso &&
              flight_destiny_domestic.value?.value == s.city_out_iso &&
              codeFlight.value === 'AECFLT')
          ) {
            itinerary_id_ = s.id;
          }
        }
      }
    });

    let n_adls = 0;
    let n_chds = 0;
    flight_passengers.value.forEach((p_selected) => {
      filesStore.getFilePassengers.forEach((p) => {
        if (p.id === p_selected) {
          if (p.type === 'ADL') {
            n_adls++;
          } else {
            n_chds++;
          }
        }
      });
    });

    let data_ = {
      entity: 'flight',
      code_flight: codeFlight.value,
      origin:
        codeFlight.value === 'AEIFLT'
          ? flight_origin.value.value
          : flight_origin_domestic.value.value,
      origin_city:
        codeFlight.value === 'AEIFLT'
          ? flight_origin.value.option.ciudad
          : flight_origin_domestic.value.option.ciudad,
      origin_country:
        codeFlight.value === 'AEIFLT'
          ? flight_origin.value.option.pais
          : flight_origin_domestic.value.option.pais,
      origin_code_country:
        codeFlight.value === 'AEIFLT'
          ? flight_origin.value.option.codpais
          : flight_origin_domestic.value.option.codpais,
      destiny:
        codeFlight.value === 'AEIFLT'
          ? flight_destiny.value.value
          : flight_destiny_domestic.value.value,
      destiny_city:
        codeFlight.value === 'AEIFLT'
          ? flight_destiny.value.option.ciudad
          : flight_destiny_domestic.value.option.ciudad,
      destiny_country:
        codeFlight.value === 'AEIFLT'
          ? flight_destiny.value.option.pais
          : flight_destiny_domestic.value.option.pais,
      destiny_code_country:
        codeFlight.value === 'AEIFLT'
          ? flight_destiny.value.option.codpais
          : flight_destiny_domestic.value.option.codpais,
      date: dayjs(date_flight.value).format('YYYY-MM-DD'),
      adult_num: n_adls,
      child_num: n_chds,
    };

    if (codeFlight.value === 'AEIFLT') {
      if (flight_origin.value.option.iso == 'PE' && flight_destiny.value.option.iso == 'PE') {
        notification.error({
          message: 'Error',
          description: `El origen y destino no pueden ser nacionales en un vuelo internacional`,
        });
        return false;
      }
    }

    if (itinerary_id_ != '') {
      await itineraryStore.update({ fileId: file_id, fileItineraryId: itinerary_id_, data: data_ });
    } else {
      await itineraryStore.add({ fileId: file_id, data: data_ });
      itinerary_id_ = await itineraryStore.getNewId;
    }

    let airline_code_ = '';
    let airline_name_ = '';
    if (flightAirlineCode.value) {
      if (typeof flightAirlineCode.value === 'object') {
        // Si cambio desde el combo
        airline_code_ = flightAirlineCode.value.value;
        airline_name_ = flightAirlineCode.value.label;
      }
    }
    let airline_number_ = '';
    if (flightAirlineNumber.value) {
      airline_number_ = flightAirlineNumber.value;
    }
    let pnr_ = '';
    if (flightPnr.value) {
      pnr_ = flightPnr.value;
    }

    const departure_time_ = flightDepartureTime.value;
    const arrival_time_ = flightArrivalTime.value;

    const data_flight_ = {
      airline_code: airline_code_,
      airline_name: airline_name_,
      airline_number: airline_number_,
      pnr: pnr_,
      departure_time: departure_time_,
      arrival_time: arrival_time_,
      nro_pax: flight_passengers.value.length,
      accommodations: flight_passengers.value,
    };

    await flightStore.add({ fileId: file_id, fileItineraryId: itinerary_id_, data: data_flight_ });

    date_flight.value = null;
    flight_passengers.value = [];
    modalAddFlight.value = false;
    clearForm();
    emit('onRefreshCache');
  };

  const toggleCompactList = () => {
    toggleItineraryListCompact.value = !toggleItineraryListCompact.value;
  };

  const router = useRouter();

  const deleteMultiple = () => {
    const flag_continue = filesStore.getItinerariesTrash.some(
      (itinerary) => dayjs(itinerary.date_in) <= dayjs(new Date())
    );

    if (flag_continue) {
      notification.error({
        message: 'Anular masivo',
        description:
          'La fecha de inicio de algunos elementos para anular son pasadas, por lo que no podemos continuar con el proceso.',
      });
      return false;
    }
    handleGoTo('trash');
  };

  const handleGoTo = (_route) => {
    itineraryStore.removeItinerary();

    let route = 'files-' + _route;
    let params = {
      id: filesStore.getFile.id,
    };

    router.push({ name: route, params: params });
  };

  const viewScheduledServices = () => {
    showScheduledServices.value = true;
  };

  // Volver a la vista principal
  const goBackItineraryPage = () => {
    showScheduledServices.value = false;
  };

  const toggleComposables = () => {
    checkedCompuestos.value = !checkedCompuestos.value;
  };

  const orderingItineraries = () => {
    listItineraries.value.sort((a, b) => {
      // Maneja casos donde `city_in_iso` puede ser null o undefined
      let optionA, optionB;

      if (selectedItineraryListFilter.value == 'fecha') {
        optionA = a.city_in_iso || '';
        optionB = b.city_in_iso || '';

        return optionA.localeCompare(optionB);
      }

      if (selectedItineraryListFilter.value == 'date') {
        optionA = a.date_in || null;
        optionB = b.date_in || null;

        return new Date(optionA) - new Date(optionB);
      }

      if (selectedItineraryListFilter.value == 'servicios-confirmados') {
        optionA = a.confirmation_status || false;
        optionB = b.confirmation_status || false;

        return Number(optionB) - Number(optionA);
      }

      if (selectedItineraryListFilter.value == 'servicios-rq') {
        optionA = a.status || false;
        optionB = b.status || false;

        return Number(optionB) - Number(optionB);
      }
    });
  };

  const searchAirline = debounce(async (value) => {
    if (value !== '' || (value === '' && airlinesOptions.data.length == 0)) {
      airlinesOptions.data = [];
      airlinesOptions.fetching = true;

      await flightStore.getListAirlines({ query: value.toUpperCase() });
      const results = flightStore.listAirlines;

      airlinesOptions.data = results;
      airlinesOptions.fetching = false;
    }
  }, 300);

  onMounted(() => {
    ini();
  });
</script>
<style scoped>
  .modal-title {
    display: flex;
    align-items: center;
  }

  .modal-title .icon {
    margin-right: 10px;
    fill: #ffffff;
    color: #ef5555;
  }

  .modal-title .title-add-flight {
    font-size: 18px;
  }

  .flight-options {
    margin-bottom: 20px;
  }

  .flight-form .back-gray {
    padding: 20px;
    background: #fafafa;
    border-radius: 10px;
  }

  .flight-form .form-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 20px;
  }

  .flight-form .form-group {
    flex: 1;
  }

  .flight-form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
  }

  .flight-form .form-group .ant-select,
  .flight-form .form-group .ant-picker {
    width: 100%;
  }

  .ant-modal-content {
    z-index: 99999 !important;
  }

  .ant-popover-placement-bottom {
    z-index: 1 !important;
  }
</style>
