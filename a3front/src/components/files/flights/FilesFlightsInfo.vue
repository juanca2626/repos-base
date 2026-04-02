<template>
  <a-collapse
    v-if="!filesStore.isLoading"
    v-model:activeKey="activeKey"
    :bordered="false"
    style="width: 100%; background-color: #fff"
  >
    <template v-for="group in flightsData" :key="group.id">
      <a-collapse-panel :show-arrow="false" :style="customStyle">
        <template #header>
          <a-row type="flex" align="middle" justify="start" style="gap: 7px">
            <a-col>
              <font-awesome-icon :icon="['fas', 'plane']" size="xl" />
            </a-col>
            <a-col>
              {{ t('files.button.flight') }}: <b>{{ formatDate(group.date) }}</b>
            </a-col>
            <a-col>
              <template v-if="group.items.some((itinerary) => !itinerary.is_valid)">
                <font-awesome-icon
                  :icon="['fas', 'triangle-exclamation']"
                  class="text-warning"
                  size="xl"
                  fade
                />
              </template>
              <template v-else>
                <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" class="text-success" />
              </template>
            </a-col>
          </a-row>
        </template>

        <template v-for="(item, i) in group.items">
          <div>
            <hr class="line-dashed size-2 my-4" v-if="i > 0" />
          </div>
          <div>
            <a-checkbox
              :checked="item.object_code === 'AECFLT' || item.object_code === 'AEC'"
              disabled="disabled"
            >
              <span class="text-capitalize">{{ t('global.label.domestic') }}</span></a-checkbox
            >
            <a-checkbox
              :checked="item.object_code === 'AEIFLT' || item.object_code === 'AEI'"
              disabled="disabled"
            >
              <span class="text-capitalize">{{ t('global.label.international') }}</span></a-checkbox
            >
          </div>
          <a-form :model="formState" layout="vertical" style="margin-top: 20px">
            <a-row>
              <a-col>
                <a-row type="flex" align="middle" style="gap: 15px" class="mb-3">
                  <a-col>
                    <template v-if="!item.editable_in">
                      <b class="text-danger">{{ item.city_in_name }}</b>
                      <template v-if="!item.flights_completed || item.is_valid">
                        <span
                          class="cursor-pointer ms-2"
                          @click="item.editable_in = !item.editable_in"
                        >
                          <font-awesome-icon :icon="['far', 'pen-to-square']" />
                        </span>
                      </template>
                    </template>
                    <template v-else>
                      <a-select
                        style="min-width: 195px"
                        v-model:value="item.city_in_iso"
                        showSearch
                        label-in-value
                        placeholder="Selecciona Origen"
                        :filter-option="false"
                        @change="updateFlight(item, 'in')"
                        @blur="item.editable_in = false"
                        :not-found-content="
                          select_search_origin_international.fetching ? undefined : null
                        "
                        :options="select_search_origin_international.data"
                        @search="search_destiny_update"
                      >
                        <template
                          v-if="select_search_origin_international.fetching"
                          #notFoundContent
                        >
                          <a-spin size="small" />
                        </template>
                      </a-select>
                    </template>
                  </a-col>
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-dark-gray" />
                  </a-col>
                  <a-col>
                    <template v-if="!item.editable_out">
                      <b class="text-danger">{{ item.city_out_name ?? '-' }}</b>
                      <template v-if="!item.flights_completed || item.is_valid">
                        <span
                          class="cursor-pointer ms-2"
                          @click="item.editable_out = !item.editable_out"
                        >
                          <font-awesome-icon :icon="['far', 'pen-to-square']" />
                        </span>
                      </template>
                    </template>
                    <template v-else>
                      <a-select
                        style="min-width: 195px"
                        v-model:value="item.city_out_iso"
                        showSearch
                        label-in-value
                        placeholder="Selecciona Destino"
                        :filter-option="false"
                        @change="updateFlight(item, 'out')"
                        @blur="item.editable_out = false"
                        :not-found-content="
                          select_search_origin_international.fetching ? undefined : null
                        "
                        :options="select_search_origin_international.data"
                        @search="search_destiny_update"
                      >
                        <template
                          v-if="select_search_origin_international.fetching"
                          #notFoundContent
                        >
                          <a-spin size="small" />
                        </template>
                      </a-select>
                    </template>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
            <template
              v-if="
                !item.city_in_iso ||
                !item.city_out_iso ||
                item.city_in_iso === '' ||
                item.city_out_iso === ''
              "
            >
              <a-alert type="info">
                <template #description>
                  <a-row type="flex" justify="start" align="middle" style="gap: 15px">
                    <a-col>
                      <font-awesome-icon :icon="['fas', 'circle-info']" size="xl" beat />
                    </a-col>
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
            <template v-else>
              <file-itinerary-flights
                @onRefreshItineraryCache="handleRefreshCache"
                :data="{
                  class: 'mt-0',
                  fileId: filesStore.getFile.id,
                  flights: item.flights,
                  itinerary: item,
                  required: true,
                }"
              />
            </template>
          </a-form>
        </template>
      </a-collapse-panel>
    </template>
  </a-collapse>
</template>

<script setup>
  import { formatDate } from '@/utils/files.js';
  import { ref, reactive, toRefs, onMounted } from 'vue';
  import { useFilesStore, useFlightStore, useItineraryStore } from '@store/files';
  import { debounce } from 'lodash-es';
  import FileItineraryFlights from '@/components/files/edit/FileItineraryFlights.vue';
  import useOrigins from '@/quotes/composables/useOrigins';
  import { useI18n } from 'vue-i18n';

  const emit = defineEmits(['onHandleRefreshCache']);

  const { t } = useI18n({
    useScope: 'global',
  });

  const props = defineProps({
    flightsData: {
      type: Object,
      default: {},
    },
    flights: {
      type: Array,
      default: [],
    },
  });

  const { flightsData } = toRefs(props);

  const { origins, getOrigins } = useOrigins();

  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const flightsStore = useFlightStore();

  const select_search_origin_international = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const activeKey = ref('');
  const formState = ref({});

  const search_destiny_update = debounce(async (value) => {
    select_search_origin_international.data = [];
    select_search_origin_international.fetching = true;

    if ((origins.value.length == 0 && value == '') || value != '') {
      await getOrigins(value.toUpperCase());
    }

    const results = origins.value.map((row) => ({
      label: row.label,
      value: row.code,
      disabled: false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
    }));

    select_search_origin_international.data = results;
    select_search_origin_international.fetching = false;
  }, 300);

  const customStyle =
    'background: #f7f7f7;border-radius: 6px;margin-bottom: 10px;border: 1px dashed #ccc;overflow: hidden';

  const updateFlight = debounce(async (item, type) => {
    if (type === 'in') {
      item.editable_in = false;
    }

    if (type === 'out') {
      item.editable_out = false;
    }

    const data = {
      type_flight: type,
      city_iso: item[`city_${type}_iso`].value,
      city_name: item[`city_${type}_iso`].option.ciudad,
      country_iso: item[`city_${type}_iso`].option.codpais,
      country_name: item[`city_${type}_iso`].option.pais,
    };

    console.log(data);
    // return false;

    itineraryStore.initedAsync();

    await flightsStore.updateCityIso({
      fileId: filesStore.getFile.id,
      fileItineraryId: item.id,
      data: data,
    });

    handleRefreshCache();
  }, 350);

  const handleRefreshCache = async () => {
    emit('onHandleRefreshCache');
  };

  onMounted(async () => {
    if (flightsStore.listAirlines.length === 0 && !flightsStore.isLoading) {
      await flightsStore.getListAirlines({ query: '' });
    }
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
