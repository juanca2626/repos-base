<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, onMounted, reactive, ref, watch } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';

  import { useSiderBarStore } from '../../store/sidebar';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';

  import type {
    DestinationsCountry,
    DestinationsState,
    DestinationsZone,
  } from '@/quotes/interfaces';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type { ServicesType } from '@/quotes/interfaces/services';
  import type { Rule } from 'ant-design-vue/es/form';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useFilesStore } from '@/stores/files';

  import { useI18n } from 'vue-i18n';

  const { t, locale } = useI18n();
  const { getLang } = useQuoteTranslations();
  const seeAllLabel = computed(() => t('quote.see_all_items'));
  // store
  // TODO: refactor to use composable instead
  const storeSidebar = useSiderBarStore();
  const quoteStore = useQuoteStore();
  const { isLoading } = storeToRefs(quoteStore);

  const myButton = ref(null);
  // Composable
  const { quote, deleteServiceSelected } = useQuote();
  const {
    servicesTypes,
    servicesZones,
    getTransferAvailable,
    getCountryStates,
    getServiceZones,
    iniServiceZones,
  } = useQuoteServices();

  const originStates = computed(() => [
    { label: seeAllLabel.value, code: 'all' } as any,
    ...getCountryStates(searchFormState.country.code),
  ]);
  const destinationsStates = computed(() => [
    { label: seeAllLabel.value, code: 'all' } as any,
    ...getCountryStates(searchFormState.country.code),
  ]);
  const arrivalPoints = computed(() => [
    { label: seeAllLabel.value, id: '' } as any,
    ...servicesZones.value,
  ]);
  const servicesTypesFiltered = computed(() => [
    { id: '', label: seeAllLabel.value, code: '' } as unknown as ServicesType,
    ...servicesTypes.value.filter((t) => t.code !== 'NA'),
  ]);

  const filesStore = useFilesStore();

  const props = defineProps({
    items: { type: Object, default: null },
    isFile: { type: Boolean, default: false },
    itinerary: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  // Form
  const searchFormRef = ref();

  interface SearchForm {
    date_from: Dayjs | undefined;
    country: DestinationsCountry;
    stateOrigin: DestinationsState | null;
    stateDestination: DestinationsState | null;
    zoneDestination: DestinationsZone | null;
    service_type: ServicesType | null;
    service_name: string;
    servicePremium: boolean;
    include_temporary: boolean;
    includeTransferDriver: boolean;
    passengers: [];
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    date_from: dayjs(quote.value.date_in),
    country: {
      code: '89',
      label: 'Perú',
    },
    stateOrigin: { code: 'all', label: seeAllLabel.value },
    stateDestination: { code: 'all', label: seeAllLabel.value },
    zoneDestination: { id: '', label: seeAllLabel.value } as any,
    service_type: { id: '', label: seeAllLabel.value } as any,
    service_name: '',
    servicePremium: false,
    include_temporary: false,
    includeTransferDriver: false,
    passengers: [],
  });

  const searchFormRules: Record<string, Rule[]> = {
    date_from: [
      {
        required: true,
        message: t('quote.label.select_start_day'),
        trigger: 'change',
        type: 'object',
      },
    ],
    stateOrigin: [
      {
        required: true,
        message: t('quote.label.select_city'),
        trigger: 'change',
      },
    ],
    stateDestination: [
      {
        required: true,
        message: t('quote.label.select_city'),
        trigger: 'change',
      },
    ],
  };

  const dateFormat = 'DD/MM/YYYY';

  const stateOriginChange = (value: { option: DestinationsState }) => {
    searchFormState.stateOrigin = value.option;
  };
  const stateDestinationChange = (value: { option: DestinationsState }) => {
    searchFormState.stateDestination = value.option;
    searchFormState.zoneDestination = { id: '', label: t('quote.see_all_items') } as any;
    if (searchFormState.stateDestination?.code && searchFormState.stateDestination.code !== 'all') {
      getServiceZones(searchFormState.stateDestination.code);
    } else {
      iniServiceZones();
    }
  };
  const zonaChange = (value: { option: DestinationsZone }) => {
    searchFormState.zoneDestination = value.option;
  };
  const categoryChange = (value: { option: ServicesType }) => {
    searchFormState.service_type = value.option;
  };

  watch(locale, () => {
    if (searchFormState.stateOrigin?.code === 'all') {
      searchFormState.stateOrigin.label = seeAllLabel.value;
    }
    if (searchFormState.stateDestination?.code === 'all') {
      searchFormState.stateDestination.label = seeAllLabel.value;
    }
    if (searchFormState.zoneDestination?.id === '') {
      searchFormState.zoneDestination.label = seeAllLabel.value;
    }
    if (searchFormState.service_type?.id === '') {
      searchFormState.service_type.label = seeAllLabel.value;
    }
  });
  // const checkedServicePremium = (value: boolean) => {
  //   searchFormState.servicePremium = value;
  // };
  // const checkedIncludeTransferDriver = (value: boolean) => {
  //   searchFormState.includeTransferDriver = value;
  // };
  const search = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();

    const origin = {
      code:
        searchFormState.country.code +
        ',' +
        (searchFormState.stateOrigin?.code === 'all'
          ? ''
          : (searchFormState.stateOrigin?.code ?? '')),
      label: searchFormState.country.label + ',' + (searchFormState.stateOrigin?.label ?? ''),
    };

    const destiny = {
      code:
        searchFormState.country.code +
        ',' +
        (searchFormState.stateDestination?.code === 'all'
          ? ''
          : (searchFormState.stateDestination?.code ?? '')),
      label: searchFormState.country.label + ',' + (searchFormState.stateDestination?.label ?? ''),
    };

    searchFormRef.value
      .validate()
      .then(async () => {
        if (props.isFile) {
          filesStore.initedAsync();
          let adults_ = filesStore.getQuantityAdults(searchFormState.passengers);
          let children_ = filesStore.getQuantityChildren(searchFormState.passengers);
          filesStore.putSearchPassengers(searchFormState.passengers);

          let service_type = searchFormState.service_type?.id ?? 'all';

          await filesStore.fetchServices({
            quantity_persons: {
              adults: adults_,
              child: children_,
              age_childs: [
                {
                  age: 1,
                },
              ],
            },
            date: searchFormState.date_from!.format('YYYY-MM-DD'),
            destiny: '', //destiny
            lang: getLang(),
            client_id: localStorage.getItem('client_id'),
            origin: destiny,
            filter: searchFormState.service_name,
            type: service_type === 'all' ? 'all' : [service_type],
            experience: 'all',
            classification: 'all',
            category: [20, 18],
            limit: 10,
            page: 1,
          });
          filesStore.finished();
        } else {
          await getTransferAvailable(
            {
              adults: quote.value.people[0].adults ? quote.value.people[0].adults : 1,
              allWords: 1, // true
              children: quote.value.people[0].child ? quote.value.people[0].child : 0,
              date_from: searchFormState.date_from!.format('YYYY-MM-DD'),
              destiny: destiny,
              lang: getLang(),
              origin: origin,
              zone_destination: searchFormState.zoneDestination?.id ?? '',
              service_name: searchFormState.service_name,
              service_type: searchFormState.service_type?.id ?? '',
              service_premium: searchFormState.servicePremium ? 1 : '',
              include_transfer_driver: searchFormState.includeTransferDriver ? 1 : '',
            },
            props.modalMode
          );

          if (!props.modalMode) {
            storeSidebar.setStatus(true, 'service', 'search');
          }
        }
      })
      .catch((error: string) => {
        console.log('error', error);
      });
  };

  const clearForm = () => {
    searchFormRef.value.resetFields();
  };

  const includeTemporary = () => {
    searchFormState.include_temporary = !searchFormState.include_temporary;
    filesStore.setIncludeTemporaryInSearch(searchFormState.include_temporary);
  };

  onMounted(() => {
    if (
      props.items &&
      props.items.service &&
      props.items.service.service_origin &&
      props.items.service.service_type
    ) {
      let origin = originStates.value.find(
        (country) =>
          country.country_code == props.items.service.service_origin[0].country_id &&
          country.code == props.items.service.service_origin[0].state_id
      );
      let destination = originStates.value.find(
        (country) =>
          country.country_code == props.items.service.service_destination[0].country_id &&
          country.code == props.items.service.service_destination[0].state_id
      );

      searchFormState.date_from = dayjs(props.items.date_in_format);
      searchFormState.stateOrigin = origin;
      searchFormState.stateDestination = destination;
      props.items.service.service_type.label =
        props.items.service.service_type.translations[0].value;
      searchFormState.service_type = props.items.service.service_type;

      myButton.value.click();
    }

    updateFormSearch();
  });

  watch(
    () => filesStore.getFile,
    () => {
      updateFormSearch();
    },
    { deep: true }
  );

  watch(destinationsStates, () => {
    updateFormSearch();
  });

  watch(filesStore.getFilePassengers, () => {
    updateFormSearch();
  });

  const all_passengers = ref(false);

  const selectAllPassengers = () => {
    searchFormState.passengers = [];
    all_passengers.value = !all_passengers.value;

    if (all_passengers.value) {
      searchFormState.passengers = filesStore.getFilePassengers.map(
        (passenger: any) => passenger.id
      );
    }
  };

  const updateFormSearch = () => {
    if (props.isFile) {
      if (typeof props.itinerary.id !== 'undefined') {
        searchFormState.date_from = dayjs(props.itinerary.date_in, 'YYYY-MM-DD');

        const destiniesOrigin = destinationsStates.value.filter((destiny) => {
          const destinyLabel = destiny.label.toLowerCase();
          const destinyIso = props.itinerary.city_in_iso.toLowerCase();
          return destinyLabel.indexOf(destinyIso) > -1;
        });

        if (destiniesOrigin.length > 0) {
          searchFormState.stateOrigin = destiniesOrigin[0];
        }

        const serviceTypeFiltered = servicesTypesFiltered.value.filter(
          (service_type) => service_type.id == Number(props.itinerary.service_type_id)
        );
        searchFormState.service_type = serviceTypeFiltered[0] ?? null;

        const destinies = destinationsStates.value.filter((destiny) => {
          const destinyLabel = destiny.label.toLowerCase();
          const destinyIso = props.itinerary.city_out_iso.toLowerCase();
          return destinyLabel.indexOf(destinyIso) > -1;
        });

        if (destinies.length > 0) {
          searchFormState.stateDestination = destinies[0];
        }

        const accommodations = props.itinerary.accommodations || [];
        searchFormState.passengers = filesStore.getFilePassengers
          .filter((passenger: any) => {
            return accommodations.some(
              (accommodation: any) => accommodation.file_passenger_id === passenger.id
            );
          })
          .map((passenger: any) => passenger.id);
      } else {
        searchFormState.date_from = dayjs(filesStore.getFile.dateIn, 'YYYY-MM-DD');
        selectAllPassengers();
      }
    }
  };

  iniServiceZones();

  const disabledDate = (current: any) => {
    return current && current < dayjs().startOf('day');
  };
</script>

<template>
  <a-form
    id="tours"
    class="container"
    ref="searchFormRef"
    :model="searchFormState"
    :rules="searchFormRules"
  >
    <div class="row-box">
      <div class="input-box">
        <label for="date_from">{{ t('quote.label.date') }}: *</label>
        <a-form-item
          name="date_from"
          :rules="{
            required: true,
            message: t('quote.label.select_start_day'),
          }"
        >
          <a-date-picker
            v-model:value="searchFormState.date_from"
            id="start-date"
            :disabledDate="disabledDate"
            :format="dateFormat"
          />
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="stateOrigin">{{ t('quote.label.origin') }}: *</label>
        <a-form-item
          name="stateOrigin"
          :rules="{
            required: searchFormState.service_name == '',
            message: t('quote.label.select_city'),
          }"
        >
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="searchFormState.stateOrigin"
            :options="originStates"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="stateOriginChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="stateDestination">{{ t('quote.label.destination') }}: *</label>
        <a-form-item
          name="stateDestination"
          :rules="{
            required: searchFormState.service_name == '',
            message: t('quote.label.select_city'),
          }"
        >
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="searchFormState.stateDestination"
            :options="destinationsStates"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="stateDestinationChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="experience_type">{{ t('quote.label.arrival_point') }}: </label>
        <a-form-item name="experience_type">
          <a-select
            v-model:value="searchFormState.zoneDestination"
            :options="arrivalPoints"
            :field-names="{ label: 'label', value: 'id' }"
            label-in-value
            :allow-clear="true"
            @change="zonaChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="service_type">{{ t('quote.label.type_services') }}: </label>
        <a-form-item name="service_type">
          <a-select
            v-model:value="searchFormState.service_type"
            :options="servicesTypesFiltered"
            :field-names="{ label: 'label', value: 'id' }"
            label-in-value
            @change="categoryChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box" v-if="props.isFile">
        <label for="passengers">
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
        </label>
        <a-form-item
          name="passengers"
          :rules="{
            required: true,
            message: t('files.message.select_passengers'),
          }"
        >
          <a-select
            mode="tags"
            id="passengers"
            v-model:value="searchFormState.passengers"
            :fieldNames="{ label: 'label', value: 'id' }"
            max-tag-count="responsive"
            :options="filesStore.getFilePassengers"
          >
          </a-select>
        </a-form-item>
      </div>
    </div>
    <div class="row-box">
      <div class="input-box search meals">
        <label for="service_name">{{ t('quote.label.filter_by_words') }}: </label>
        <a-form-item name="service_name" class="mb-0 d-block w-50">
          <a-input
            v-model:value="searchFormState.service_name"
            :placeholder="t('quote.label.write_here')"
          />
        </a-form-item>
      </div>
      <!-- <div class="input-box">
        <CheckBoxComponent label="Servicio Premium" @checked="checkedServicePremium"/>        
      </div>
      <div class="input-box">
        <CheckBoxComponent label="Incluir Trasladista" @checked="checkedIncludeTransferDriver"/>        
      </div>       -->
      <div class="actions_buttons">
        <div class="text" @click="includeTemporary" v-if="props.isFile">
          <template v-if="filesStore.getIsIncludeTemporaryInSearch">
            <i class="bi bi-check-square-fill text-danger" style="font-size: 1.5rem"></i>
            <font-awesome-icon :icon="['fas', 'stopwatch']" />
            Incluir servicios temporales
          </template>
          <template v-else>
            <i class="bi bi-square text-danger text-dark-light" style="font-size: 1.5rem"></i>
            <font-awesome-icon :icon="['fas', 'stopwatch']" />
            Incluir servicios temporales
          </template>
        </div>
        <div class="text" :class="{ disabled: isLoading }" @click="!isLoading && clearForm()">
          <icon-clear />
          <span>{{ t('quote.label.clean_filters') }}</span>
        </div>
        <div
          class="search_button_container"
          :class="{ disabled: isLoading }"
          ref="myButton"
          @click="!isLoading && search()"
        >
          <div class="search-button">
            <div class="content">
              <div class="icon">
                <icon-search />
              </div>
              <div class="text">{{ t('quote.label.search') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-form>
</template>

<style lang="scss" scoped>
  .input-box {
    &.duration {
      flex-grow: 0 !important;
      /* flex-shrink: 0 !important; */
      flex-basis: 19.2% !important;
    }
  }

  form#tours.container {
    .input-box.search {
      .ant-row.ant-form-item {
        width: 26.5% !important;
      }
    }
  }

  @media only screen and (max-width: 1400px) {
    form#tours.container {
      .input-box.search {
        .ant-row.ant-form-item {
          width: 28% !important;
        }
      }
    }
  }
</style>
