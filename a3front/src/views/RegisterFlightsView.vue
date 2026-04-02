<template>
  <a-spin :tip="t('files.message.waiting')" :spinning="loading" size="large">
    <a-layout id="files-layout">
      <div class="files-layout-row">
        <div class="files-edit">
          <div class="files-layout bg-white h-screen">
            <div class="files-flights-info">
              <header class="files-flights-info-header">
                <h3 class="files-flights-info-title text-uppercase mb-0">
                  <font-awesome-icon :icon="['fas', 'plane-lock']" class="me-3" />
                  <small>{{ t('files.label.register_flights') }}</small>
                </h3>

                <a-row type="flex" align="middle" style="gap: 7px">
                  <a-col>
                    <a-select
                      v-if="languagesStore.getLanguages.length > 0"
                      v-model:value="currentLang"
                      style="min-width: 60px"
                      ghost
                      size="large"
                      @change="handleChangeLanguage"
                    >
                      <template #suffixIcon>
                        <font-awesome-icon :icon="['fas', 'language']" />
                      </template>
                      <a-select-option
                        v-for="lang in languagesStore.getLanguages"
                        :key="lang.id"
                        :value="lang.value"
                      >
                        {{ lang.value.toUpperCase() }}
                      </a-select-option>
                    </a-select>
                  </a-col>
                </a-row>
              </header>

              <a-alert type="info">
                <template #message>
                  <a-row type="flex" align="middle" style="gap: 15px; flex-flow: nowrap">
                    <a-col>
                      <font-awesome-icon :icon="['fas', 'circle-info']" size="xl" beat />
                    </a-col>
                    <a-col>
                      <strong class="m-0">{{ t('files.label.save_time') }}</strong>
                      {{ t('files.label.save_time_flights') }}
                    </a-col>
                  </a-row>
                </template>
              </a-alert>

              <a-input-group style="display: flex" v-if="1 != 1">
                <a-input v-model:value="url" disabled />
                <a-button type="primary" danger @click="handleClick">
                  <span v-if="!copied">Copiar</span>
                  <span v-else>Copiado!</span>
                </a-button>
              </a-input-group>

              <hr class="mb-3 w-100 border-0 height-1" style="background-color: #c4c4c4" />

              <h4 class="files-flights-info-title-info text-uppercase">
                {{ t('global.label.information') }} {{ t('global.label.of') }}
                {{ t('files.button.flight') }}
              </h4>

              <div class="mb-3" v-if="flights.length > 0">
                <a-button
                  type="primary"
                  size="large"
                  class="text-500"
                  :loading="filesStore.isLoading || itineraryStore.isLoadingAsync"
                  danger
                  @click="showAddFlight"
                >
                  <span
                    :class="filesStore.isLoading || itineraryStore.isLoadingAsync ? 'ms-2' : ''"
                  >
                    {{ t('files.button.add') }} {{ t('files.button.flight') }}
                  </span>
                </a-button>
              </div>

              <a-alert type="warning" v-if="filesStore.isLoading">
                <template #message>
                  <span class="text-dark-warning"> Cargando información. Espere un momento.. </span>
                </template>
              </a-alert>

              <template v-if="itineraryStore.isLoadingAsync">
                <loading-skeleton />
              </template>
              <flights-info-page
                v-else
                @onHandleRefreshCache="handleRefreshCache"
                :flightsData="flightsData"
                :flights="flights"
                :flag_external_link="true"
              />
            </div>
          </div>
        </div>
      </div>
    </a-layout>
  </a-spin>

  <a-modal v-model:visible="modalAddFlight" style="width: 600px" @cancel="handleCancel">
    <template #title>
      <div class="modal-title">
        <font-awesome-icon :icon="['fas', 'plane']" size="lg" class="me-2 text-danger" />
        <span class="title-add-flight"
          >{{ t('files.button.add') }} {{ t('files.button.flight') }}</span
        >
      </div>
    </template>

    <div class="flight-form">
      <a-alert type="warning" v-if="itineraryStore.isLoading || flightsStore.isLoading">
        <template #description>
          {{ t('files.message.loading') }}.. {{ t('files.message.waiting') }}.</template
        >
      </a-alert>

      <div class="form-group mb-3">
        <a-select
          class="w-100"
          v-model:value="flight_id"
          showSearch
          placeholder="Selecciona una Fecha"
          :options="flights"
          :disabled="itineraryStore.isLoading || flightsStore.isLoading"
          @change="setFlight()"
        >
        </a-select>
      </div>

      <template v-if="flight_id > 0">
        <div class="flight-options">
          <a-radio-group v-model:value="codeFlight" disabled>
            <a-radio value="AEIFLT"
              ><span class="text-capitalize">{{ t('global.label.international') }}</span></a-radio
            >
            <a-radio value="AECFLT"
              ><span class="text-capitalize">{{ t('global.label.domestic') }}</span></a-radio
            >
          </a-radio-group>
        </div>

        <div class="back-gray">
          <div class="form-row w-100">
            <div class="form-group" v-if="codeFlight === 'AEIFLT'">
              <label>Origen Internacional</label>
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
                :disabled="itineraryStore.isLoading || flightsStore.isLoading || lockOrigin"
              >
                <template v-if="select_search_origin_international.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
            <div class="form-group" v-if="codeFlight === 'AEIFLT'">
              <label>Destino Internacional</label>
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
                :disabled="itineraryStore.isLoading || flightsStore.isLoading || lockDestiny"
              >
                <template v-if="select_search_origin_international.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>

            <div class="form-group" v-if="codeFlight === 'AECFLT'">
              <label>Origen domestic</label>
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
                :disabled="true"
              >
                <template v-if="select_search_origin_domestic.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
            <div class="form-group" v-if="codeFlight === 'AECFLT'">
              <label>Destino domestic</label>
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
                :disabled="true"
              >
                <template v-if="select_search_origin_domestic.fetching" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </div>
          </div>

          <div class="form-row w-100">
            <div class="form-group">
              <label for="fecha">{{ t('global.column.date') }}</label>
              <a-date-picker
                v-model:value="date_flight"
                id="date_flight"
                :format="dateFormat"
                :disabled="true"
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
              <a-select
                v-model:value="flight_passengers"
                mode="multiple"
                style="width: 100%"
                max-tag-count="responsive"
                :options="passenger_options"
                :disabled="flight_disabled || itineraryStore.isLoading || flightsStore.isLoading"
              >
              </a-select>
            </div>
          </div>

          <div class="form-row w-100">
            <div class="form-group">
              <label for=""> PNR </label>
              <a-input
                :disabled="flightsStore.isLoading"
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
                :disabled="flightsStore.isLoading"
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
                    @input="updateTimeFlight($event, flightDepartureTime)"
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
                    @input="updateTimeFlight($event, flightArrivalTime)"
                  />
                </a-col>
              </a-row>
            </div>
          </div>
        </div>
      </template>
    </div>

    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          default
          class="text-500"
          @click="handleCancel"
          v-bind:disabled="
            itineraryStore.isLoading || itineraryStore.isLoadingAsync || flightsStore.isLoading
          "
          size="large"
        >
          {{ t('global.button.back') }}
        </a-button>
        <a-button
          type="primary"
          primary
          class="text-500"
          :loading="loading"
          @click="saveFlight"
          v-bind:disabled="
            !isFormValid ||
            itineraryStore.isLoading ||
            itineraryStore.isLoadingAsync ||
            flightsStore.isLoading
          "
          size="large"
        >
          <span :class="loading ? 'ms-2' : ''">
            {{ t('files.button.add') }} {{ t('files.button.flight') }}
          </span>
        </a-button>
      </div>
    </template>
  </a-modal>

  <users-connected></users-connected>
  <div class="notifications-floating" v-if="socketsStore.isConnected">
    <header-notifications />
  </div>
</template>

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

<script setup>
  import { onMounted, ref, watch, computed, reactive } from 'vue';
  import dayjs from 'dayjs';
  import axios from 'axios';
  import { debounce } from 'lodash-es';
  import { useI18n } from 'vue-i18n';
  import { getUrlAuroraFront } from '@/utils/auth';
  import FlightsInfoPage from '@/components/files/flights/FilesFlightsInfo.vue';
  import { useFilesStore, useFlightStore, useItineraryStore } from '@store/files';
  import { useRoute, useRouter } from 'vue-router';
  import { useLanguagesStore, useSocketsStore } from '@/stores/global';
  import useOrigins from '@/quotes/composables/useOrigins';
  import { notification } from 'ant-design-vue';
  import BaseInputTime from '@/components/files/reusables/BaseInputTime.vue';
  import UsersConnected from '@/components/global/UsersConnected.vue';
  import HeaderNotifications from '@/components/global/HeaderNotifications.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import { useSocketHelper } from '@/utils/socketHelper';
  const { connectSocketFileId, reconnectOnVisibility } = useSocketHelper();

  const { getLocaleMessage, locale, mergeLocaleMessage, t } = useI18n({
    useScope: 'global',
  });

  const socketsStore = useSocketsStore();
  const languagesStore = useLanguagesStore();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const flightsStore = useFlightStore();

  const route = useRoute();
  const router = useRouter();

  const flightsData = ref({});
  const flights = ref([]);

  const airlinesOptions = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const { origins, getOrigins, getOriginsDomestic } = useOrigins();

  const generateItineraryDetail = async (itinerary_id) => {
    const itineraryId = itinerary_id;
    await itineraryStore.getById({
      fileId: filesStore.getFile.id,
      itineraryId: itineraryId,
    });
    const newItinerary = itineraryStore.getItinerary;

    if (newItinerary?.id === itineraryId) {
      filesStore.updateItinerary(newItinerary);
    } else {
      filesStore.removeItinerary(itineraryId);
    }
  };

  const handleRefresh = () => {
    const seenLabels = new Set();

    flights.value = filesStore.getFileItineraries
      .filter((itinerary) => itinerary.entity === 'flight' && !itinerary.flights_completed)
      .map((flight) => {
        return {
          label: `VUELO EN: ${dayjs(flight.date_in).format('DD/MM/YYYY')} - (${flight.object_code === 'AEIFLT' ? 'Internacional' : 'Doméstico'})`,
          value: flight.id,
        };
      })
      .filter((flight) => {
        if (seenLabels.has(flight.label)) return false;
        seenLabels.add(flight.label);
        return true;
      });

    const items = filesStore.getFileItineraries.filter(
      (itinerary) => itinerary.entity === 'flight'
    );

    flightsData.value = items.reduce((acc, item) => {
      const key = `${item.date_in}_${item.object_code}`; // clave única
      if (!acc[key]) {
        acc[key] = { date: item.date_in, items: [] };
      }
      acc[key].items.push(item);
      return acc;
    }, {});

    setTimeout(() => {
      itineraryStore.finished();
      filesStore.finished();
    }, 1000);
  };

  const loading = ref(true);

  watch(
    () => filesStore.getFileItineraries,
    (newValue, oldValue) => {
      if (newValue === oldValue) return;
      setTimeout(() => {
        handleRefresh();
      }, 1000);
    },
    { deep: true }
  ); // Agrega deep si es necesario

  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  const validateFile = async () => {
    if (filesStore.getFile?.id > 0) {
      if (filesStore.getFile.dateIn <= dayjs().format('YYYY-MM-DD')) {
        router.push({ name: 'link_completed' });
      }
    } else {
      router.push({ name: 'error_404' });
    }

    await sleep(500);
  };

  onMounted(async () => {
    const { nrofile } = route.params;
    const { lang = 'en' } = route.query;

    localStorage.setItem('lang', lang);

    filesStore.initedAsync();
    itineraryStore.initedAsync();

    await filesStore.getByNumber({ nrofile });

    if (filesStore.getFile?.id > 0) {
      await validateFile();
      await filesStore.getPassengersById({ fileId: filesStore.getFile.id });

      await Promise.all(
        filesStore.getFileItineraries
          .filter((itinerary) => itinerary.entity === 'flight')
          .map((itinerary) => generateItineraryDetail(itinerary.id))
      );
    } else {
    }

    await validateFile();
    await flightsStore.getListAirlines({ query: '' });
    airlinesOptions.data = flightsStore.listAirlines;

    languagesStore.setCurrentLanguage(lang);
    locale.value = languagesStore.getLanguage;

    let data,
      iso = '';

    data = import.meta.glob('../lang/**/files.json');
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          files: JSON.parse(JSON.stringify(mod)),
        };

        addTranslations(iso, messages);
      });
    }

    data = import.meta.glob('../lang/**/global.json');
    iso = '';
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          global: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);
      });
    }

    data = import.meta.glob('../lang/**/quotes.json');
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          quote: JSON.parse(JSON.stringify(mod)),
        };

        addTranslations(iso, messages);
      });
    }

    if (filesStore.getFile?.id) {
      connectSocketFileId(filesStore.getFile.id);
    }

    loading.value = false;
    await getLanguagesFiles();
    filesStore.finished();

    setTimeout(() => {
      handleRefresh();
    }, 1000);
  });

  const addTranslations = (iso, messages) => {
    let currentMessages = getLocaleMessage(iso);
    currentMessages = { ...currentMessages, ...messages };
    mergeLocaleMessage(iso, currentMessages);
  };

  const getLanguagesFiles = async () => {
    currentLang.value = languagesStore.getLanguage;

    const files = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.getLanguage + '/slug/files'
    );

    const quotes = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.currentLanguage + '/slug/quote'
    );

    const flights = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.currentLanguage + '/slug/flights'
    );

    const messages = {
      files: files.data,
      quote: quotes.data,
      flights: flights.data,
    };

    addTranslations(languagesStore.currentLanguage, messages);
  };

  document.addEventListener('visibilitychange', reconnectOnVisibility);
  window.addEventListener('beforeunload', () => {
    socketsStore.disconnect();
  });

  const codeFlight = ref('AEIFLT');
  const flight_origin = ref(null);
  const flight_destiny = ref(null);
  const flight_origin_domestic = ref(null);
  const flight_destiny_domestic = ref(null);
  const date_flight = ref('');
  const flight_passengers = ref([]);
  const dateFormat = 'DD/MM/YYYY';
  const flightPnr = ref('');
  const flightAirlineCode = ref('');
  const flightAirlineNumber = ref('');
  const flightArrivalTime = ref('');
  const flightDepartureTime = ref('');
  const flight = ref({});

  const select_search_origin_domestic = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const showAddFlight = () => {
    clearForm();
    clearSearches();
    modalAddFlight.value = true;
  };

  const handleCancel = () => {
    modalAddFlight.value = false;
    flight.value = null;
    clearForm();
  };

  const searchAirline = debounce(async (value) => {
    if (value !== '' || (value === '' && airlinesOptions.data.length == 0)) {
      airlinesOptions.data = [];
      airlinesOptions.fetching = true;

      await flightsStore.getListAirlines({ query: value.toUpperCase() });
      const results = flightsStore.listAirlines;

      airlinesOptions.data = results;
      airlinesOptions.fetching = false;
    }
  }, 300);

  const saveFlight = async () => {
    let itineraries_ = filesStore.getFileItineraries;
    let itinerary_id_ = '';

    const file_id = filesStore.getFile.id;

    itineraries_.forEach((s) => {
      if (s.entity === 'flight') {
        if (
          dayjs(s.date_in).format('YYYY-MM-DD') === dayjs(date_flight.value).format('YYYY-MM-DD') &&
          s.object_code == codeFlight.value
        ) {
          // domestic = AECFLT / intern = AEIFLT
          if (
            (flight_origin.value.value == s.city_in_iso &&
              flight_destiny.value.value == s.city_out_iso &&
              codeFlight.value === 'AEIFLT') ||
            (flight_origin_domestic.value.value == s.city_in_iso &&
              flight_destiny_domestic.value.value == s.city_out_iso &&
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
      if (
        flight_origin.value.option.codpais == 'PE' &&
        flight_destiny.value.option.codpais == 'PE'
      ) {
        notification.error({
          message: 'Error',
          description: `El origen y destino no pueden ser nacionales en un vuelo internacional`,
        });
        return false;
      }
    }

    let flag_new = true;

    if (itinerary_id_ != '') {
      await itineraryStore.updatePublic({
        fileId: file_id,
        fileItineraryId: itinerary_id_,
        data: data_,
      });

      flag_new = false;
    } else {
      await itineraryStore.addPublic({ fileId: file_id, data: data_ });
      itinerary_id_ = itineraryStore.getNewId;
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

    if (flag_new) {
      await flightsStore.add({
        fileId: file_id,
        fileItineraryId: itinerary_id_,
        data: data_flight_,
      });
    } else {
      await flightsStore.storeSub({
        fileId: file_id,
        fileItineraryId: itinerary_id_,
        data: data_flight_,
      });
    }

    date_flight.value = null;
    flight_passengers.value = [];
    modalAddFlight.value = false;
    clearForm();
    handleRefreshCache();
  };

  const handleRefreshCache = () => {
    itineraryStore.initedAsync();
  };

  const select_search_origin_international = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const selectAllPassengers = () => {
    flight_passengers.value = passenger_options.value
      .filter((option) => !option.disabled)
      .map((option) => {
        return option.value;
      });
  };

  const clearSearches = () => {
    if (codeFlight.value === 'AEIFLT') {
      search_destiny('');
    }

    if (codeFlight.value === 'AECFLT') {
      search_domestic_destiny('');
    }
  };

  const modalAddFlight = ref(false);

  const clearForm = () => {
    flight_id.value = '';
    flight.value = {};

    codeFlight.value = 'AEIFLT';
    flight_origin.value = null;
    flight_origin_domestic.value = null;
    flight_destiny.value = null;
    flight_destiny_domestic.value = null;
    date_flight.value = '';
    flight_passengers.value = [];
    flight.value = {};

    flightPnr.value = '';
    flightAirlineCode.value = '';
    flightAirlineNumber.value = '';
    flightArrivalTime.value = '';
    flightDepartureTime.value = '';
    // flightTimeRange.value = [];
  };

  const search_destiny = debounce(async (value) => {
    select_search_origin_international.data = [];
    select_search_origin_international.fetching = true;

    if ((origins.value.length == 0 && value == '') || value != '') {
      await getOrigins(value.toUpperCase());
    }

    // let code_in_use =
    //   flight_origin.value.value !== undefined && flight_origin.value.value !== ''
    //     ? flight_origin.value.value
    //     : flight_destiny.value.value !== undefined && flight_destiny.value.value !== ''
    //       ? flight_destiny.value.value
    //       : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
      // disabled: code_in_use !== '' ? row.code === code_in_use : false,
    }));

    select_search_origin_international.data = results;
    select_search_origin_international.fetching = false;
  }, 300);

  const search_domestic_destiny = debounce(async (value) => {
    select_search_origin_domestic.data = [];
    select_search_origin_domestic.fetching = true;
    // console.log("flight_origin_domestic:",flight_origin_domestic);
    if (value == '' || value != '') {
      await getOriginsDomestic(value.toUpperCase());
    }

    // let code_in_use =
    //   flight_origin_domestic.value.value !== undefined && flight_origin_domestic.value.value !== ''
    //     ? flight_origin_domestic.value.value
    //     : flight_destiny_domestic.value.value !== undefined &&
    //         flight_destiny_domestic.value.value !== ''
    //       ? flight_destiny_domestic.value.value
    //       : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
      // disabled: code_in_use !== '' ? row.code === code_in_use : false,
    }));

    select_search_origin_domestic.data = results;
    select_search_origin_domestic.fetching = false;
  }, 300);

  const flight_disabled = ref(true);

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

  const flight_id = ref('');
  const lockOrigin = ref(false);
  const lockDestiny = ref(false);

  const setFlight = () => {
    const flight = filesStore.getFileItineraries.filter(
      (itinerary) => itinerary.entity === 'flight' && itinerary.id === flight_id.value
    );
    console.log(flight_id, flight);
    codeFlight.value = flight[0].object_code;
    lockOrigin.value = false;
    lockDestiny.value = false;

    if (codeFlight.value === 'AEIFLT') {
      flight_origin.value = {
        value: flight[0].city_in_iso,
        label: flight[0].city_in_iso,
        option: {
          pais: flight[0].country_in_name,
          codpais: flight[0].country_in_iso,
          codciu: flight[0].city_in_iso,
          ciudad: flight[0].city_in_name,
        },
      };
      flight_destiny.value = {
        value: flight[0].city_out_iso,
        label: flight[0].city_out_iso,
        option: {
          pais: flight[0].country_out_name,
          codpais: flight[0].country_out_iso,
          codciu: flight[0].city_out_iso,
          ciudad: flight[0].city_out_name,
        },
      };

      if (flight[0].date_in === filesStore.getFile.dateIn) {
        lockOrigin.value = false;
        lockDestiny.value = true;
      }

      if (flight[0].date_in === filesStore.getFile.dateOut) {
        lockOrigin.value = true;
        lockDestiny.value = false;
      }
    } else {
      flight_origin_domestic.value = {
        value: flight[0].city_in_iso,
        label: flight[0].city_in_iso,
        option: {
          pais: flight[0].country_in_name,
          codpais: flight[0].country_in_iso,
          codciu: flight[0].city_in_iso,
          ciudad: flight[0].city_in_name,
        },
      };
      flight_destiny_domestic.value = {
        value: flight[0].city_out_iso,
        label: flight[0].city_out_iso,
        option: {
          pais: flight[0].country_out_name,
          codpais: flight[0].country_out_iso,
          codciu: flight[0].city_out_iso,
          ciudad: flight[0].city_out_name,
        },
      };
    }

    date_flight.value = dayjs(flight[0].date_in, 'YYYY-MM-DD');

    setTimeout(() => {
      changeDate();
    }, 10);
  };

  const changeDate = () => {
    if (date_flight.value) {
      const selectedOptions = new Set();

      // Habilita todas las opciones de pasajero inicialmente
      passenger_options.value.forEach((option) => (option.disabled = false));

      const selectedDate = dayjs(date_flight.value).format('YYYY-MM-DD');
      const selectedCodeFlight = codeFlight.value;

      Object.values(flightsData.value).forEach((group) => {
        group.items
          .filter(
            (item) =>
              item.entity === 'flight' &&
              dayjs(item.date_in).format('YYYY-MM-DD') === selectedDate &&
              item.object_code === selectedCodeFlight
          )
          .forEach((flightItem) => {
            flightItem.flights.forEach((flight) => {
              flight.accommodations.forEach((accommodation) => {
                selectedOptions.add(accommodation.file_passenger_id);
              });
            });
          });
      });

      // Deshabilita las opciones de pasajero que ya están seleccionadas en vuelos
      passenger_options.value.forEach((option) => {
        option.disabled = selectedOptions.has(option.value);
      });

      // Actualiza la lista de pasajeros válidos
      flight_passengers.value = passenger_options.value
        .filter((option) => !option.disabled)
        .map((option) => option.value);

      flight_disabled.value = flight_passengers.value.length === 0;
    } else {
      flight_disabled.value = true;
    }
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

  const currentLang = ref('');

  const handleChangeLanguage = (value) => {
    locale.value = value;
    localStorage.setItem('lang', value.toLowerCase());
    languagesStore.setCurrentLanguage(value);
    setTimeout(() => {
      getLanguagesFiles();
    }, 10);
  };
</script>

<style scoped lang="scss">
  .files-flights-info {
    min-width: 1040px;

    &-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
    }

    &-title {
      color: #eb5757;
      font-weight: 400;
      font-size: 48px !important;
      line-height: 55px;
    }
    &-title-info {
      color: #3d3d3d;
      font-weight: 700;
      font-size: 24px !important;
      line-height: 31px;
    }
  }
</style>

<style lang="scss" scoped>
  :deep(.ant-spin) {
    background: none !important;
    border: 0 !important;
  }

  :deep(.ant-spin-text) {
    padding-top: 60px !important;
    color: #bababa;
    text-shadow: none;
    font-size: 16px;
  }

  :deep(.ant-spin-lg .ant-spin-dot i) {
    display: none;

    &:first-child {
      display: block !important;
      opacity: 1;
      width: auto;
      height: auto;
    }
  }

  :deep(.ant-spin-dot-spin) {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto auto;
    width: 60px;
    height: 60px;
    animation: rotate 2s infinite ease-in-out;
    overflow: hidden;
    background-color: #c3141a;
    margin: -35px 0 0 -30px !important;
  }

  :deep(.ant-spin-blur) {
    opacity: 0.999;
  }

  :deep(.ant-spin-dot-item) {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto auto;
    background: red url('../images/quotes/logo.png') 100% 100%;
    background-size: cover;
    animation: rotate 2s infinite ease-in-out;
    border-radius: 0 !important;
  }

  :deep(.spin-white .ant-spin-container.ant-spin-blur:after) {
    background: rgba(255, 255, 255, 0.95) !important;
    opacity: 0.8 !important;
    z-index: 25;
  }

  :deep(.spin-white .ant-spin-spinning) {
    position: absolute !important;
  }

  :deep(.ant-spin-container.ant-spin-blur:after) {
    background: rgba(0, 0, 0, 0.95) !important;
    opacity: 1 !important;
    z-index: 25;
  }

  :deep(.ant-spin-text) {
    text-shadow: none !important;
  }

  :deep(.text-carga) {
    z-index: 28 !important;
    display: none !important;
    .ant-spin-text {
      padding-top: 90px !important;
      animation: opacity 1.5s infinite ease-out;
      font-size: 80%;
      font-weight: 400;
      text-shadow: 0 1px 2px #fff !important;
    }
  }

  :deep(.ant-spin-container.ant-spin-blur .text-carga) {
    display: block !important;
  }

  :deep(.text-carga .ant-spin-dot-spin) {
    display: none !important;
  }
</style>
