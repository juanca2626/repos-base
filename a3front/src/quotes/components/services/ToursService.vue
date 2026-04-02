<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, defineProps, onMounted, reactive, ref, watch } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';

  import { useSiderBarStore } from '../../store/sidebar';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';

  import type { DestinationsCountry, DestinationsState } from '@/quotes/interfaces';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type {
    ServiceExperience,
    ServicesSubType,
    ServicesType,
  } from '@/quotes/interfaces/services';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useFilesStore } from '@/stores/files';

  import { useI18n } from 'vue-i18n';
  import { isArray, isObject } from 'lodash';

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
    serviceExperiences,
    serviceDurations,
    servicesDestinations,
    getToursAvailable,
    getCountryStates,
  } = useQuoteServices();

  const countries = computed(() => servicesDestinations.value!.destinationsCountries);
  const destinationsStates = computed(() => [
    { label: seeAllLabel.value, code: 'all' } as any,
    ...getCountryStates(searchFormState.country.code),
  ]);
  const servicesTypesFiltered = computed(() => [
    { id: '', label: seeAllLabel.value, code: '' } as unknown as ServicesType,
    ...servicesTypes.value.filter((t) => t.code !== 'NA'),
  ]);
  const experienceTypes = computed(() => [
    { id: '', label: seeAllLabel.value } as unknown as ServiceExperience,
    ...serviceExperiences.value,
  ]);
  const durationsTypes = computed(() => [
    { id: '', label: seeAllLabel.value } as any,
    ...serviceDurations.value,
  ]);

  const filesStore = useFilesStore();

  const props = defineProps({
    isFile: { type: Boolean, default: false },
    items: { type: Object, default: null },
    itinerary: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  // Form
  const searchFormRef = ref();

  interface SearchForm {
    date_from: Dayjs | undefined;
    country: DestinationsCountry;
    state: DestinationsState | null;
    experience_type: ServiceExperience | null;
    service_type: ServicesType | null;
    service_sub_category: ServicesSubType | null;
    service_name: string;
    include_temporary: boolean;
    passengers: [];
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    date_from: dayjs(quote.value.date_in),
    country: {
      code: '89',
      label: 'Perú',
    },
    state: {
      code: 'all',
      label: seeAllLabel.value,
    },
    experience_type: { id: '', label: seeAllLabel.value } as any,
    service_type: { id: '', label: seeAllLabel.value } as any,
    service_sub_category: { id: '', label: seeAllLabel.value } as any,
    service_name: '',
    include_temporary: false,
    passengers: [],
  });

  const dateFormat = 'DD/MM/YYYY';

  const countryChange = (value: { option: DestinationsCountry }) => {
    searchFormState.country = value.option;
    searchFormState.state = { code: '', label: t('quote.see_all_items') };
  };
  const stateChange = (value: { option: DestinationsState }) => {
    searchFormState.state = value.option;
  };

  watch(locale, () => {
    if (searchFormState.state?.code === 'all') {
      searchFormState.state.label = seeAllLabel.value;
    }
    if (searchFormState.experience_type?.id === '') {
      searchFormState.experience_type.label = seeAllLabel.value;
    }
    if (searchFormState.service_type?.id === '') {
      searchFormState.service_type.label = seeAllLabel.value;
    }
    if (searchFormState.service_sub_category?.id === '') {
      searchFormState.service_sub_category.label = seeAllLabel.value;
    }
  });
  const categoryChange = (value: { option: ServicesType }) => {
    searchFormState.service_type = value.option;
  };
  const experienceChange = (value: { option: ServiceExperience }) => {
    searchFormState.experience_type = value.option;
  };
  const durationChange = (value: { option: ServicesSubType }) => {
    searchFormState.service_sub_category = value.option;
  };

  const search = () => {
    storeSidebar.setStatus(false, '', '');

    deleteServiceSelected();

    const countryCode = searchFormState.country.code;
    const stateCode =
      searchFormState.state?.code === 'all' ? '' : (searchFormState.state?.code ?? '');

    const destiny = {
      code: countryCode + ',' + stateCode,
      label: searchFormState.country.label + ',' + (searchFormState.state?.label ?? ''),
    };

    searchFormRef.value
      .validate()
      .then(async () => {
        if (props.isFile) {
          filesStore.initedAsync();
          let adults_ = filesStore.getQuantityAdults(searchFormState.passengers);
          let children_ = filesStore.getQuantityChildren(searchFormState.passengers);
          filesStore.putSearchPassengers(searchFormState.passengers);

          let type = searchFormState.service_type?.id;
          let experience =
            searchFormState.experience_type?.id === 'all'
              ? ''
              : (searchFormState.experience_type?.id ?? '');
          let categories =
            searchFormState.service_sub_category?.id &&
            searchFormState.service_sub_category?.id !== ''
              ? searchFormState.service_sub_category?.id
              : serviceDurations.value.map((duration) => duration.id);
          console.log('Categories: ', categories);

          if (!(isObject(categories) || isArray(categories))) {
            categories = [categories];
          }

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
            type: type ? [type] : 'all',
            experience: experience ? [experience] : 'all',
            classification: 'all',
            category: categories,
            limit: 10,
            page: 1,
          });
          filesStore.finished();
        } else {
          await getToursAvailable(
            {
              adults: quote.value.people[0].adults | 1,
              allWords: 1, // true
              children: quote.value.people[0].child,
              date_from: searchFormState.date_from!.format('YYYY-MM-DD'),
              destiny: '', //destiny
              lang: getLang(),
              origin: destiny,
              service_name: searchFormState.service_name,
              service_type: searchFormState.service_type?.id ?? '',
              experience_type: searchFormState.experience_type?.id ?? '',
              service_sub_category: searchFormState.service_sub_category?.id ?? '',
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

  const includeTemporary = () => {
    searchFormState.include_temporary = !searchFormState.include_temporary;
    filesStore.setIncludeTemporaryInSearch(searchFormState.include_temporary);
  };

  const clearForm = () => {
    searchFormRef.value.resetFields();
  };

  const disabledDate = (current: any) => {
    return current && current < dayjs().startOf('day');
  };

  onMounted(() => {
    if (
      props.items &&
      props.items.service &&
      props.items.service.service_origin &&
      props.items.service.service_type
    ) {
      let country = countries.value.find(
        (country) => country.code == props.items.service.service_origin[0].country_id
      );
      let city = {
        code: props.items.service.service_origin[0].state.id,
        label: props.items.service.service_origin[0].state.translations[0].value,
        country_code: country.code,
      };
      searchFormState.date_from = dayjs(props.items.date_in_format);
      searchFormState.country = country;
      searchFormState.state = city;
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

        const destinies = destinationsStates.value.filter((destiny) => {
          const destinyLabel = destiny.label.toLowerCase();
          const destinyIso = props.itinerary.city_in_iso.toLowerCase();
          return destinyLabel.indexOf(destinyIso) > -1;
        });

        if (destinies.length > 0) {
          searchFormState.state = destinies[0];
        }

        const serviceTypeFiltered = servicesTypesFiltered.value.filter((service_type) => {
          return service_type.id == Number(props.itinerary.service_type_id) ? service_type : {};
        });
        searchFormState.service_type = serviceTypeFiltered[0];

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
</script>

<template>
  <a-form id="tours" class="container" ref="searchFormRef" :model="searchFormState">
    <div class="row-box">
      <div class="input-box">
        <label for="date_from">{{ t('quote.label.day_start') }}: *</label>
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
      <div class="input-box" v-if="!props.isFile">
        <label for="country">{{ t('quote.label.country') }}: *</label>
        <a-form-item
          name="country"
          :rules="{ required: true, message: t('quote.label.select_country') }"
        >
          <a-select
            v-model:value="searchFormState.country"
            :options="countries"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="countryChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="state">{{ t('quote.label.city') }}: *</label>
        <a-form-item
          name="state"
          :rules="{
            required: searchFormState.service_name == '',
            message: t('quote.label.select_city'),
          }"
        >
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="searchFormState.state"
            :options="destinationsStates"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="stateChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="experience_type">{{ t('quote.label.type_experience') }}: </label>
        <!--        <a-select ref="select" @focus="focus">-->
        <!--          <a-select-option value="aventura">Aventura</a-select-option>-->
        <!--          <a-select-option value="gastronomia">Gastronomía</a-select-option>-->
        <!--          <a-select-option value="naturaleza">Naturaleza</a-select-option>-->
        <!--          <a-select-option value="historia-cultura">Historia y Cultura</a-select-option>-->
        <!--          <a-select-option value="familia">Familia</a-select-option>-->
        <!--        </a-select>-->
        <a-form-item name="experience_type">
          <a-select
            v-model:value="searchFormState.experience_type"
            :options="experienceTypes"
            :field-names="{ label: 'label', value: 'id' }"
            label-in-value
            @change="experienceChange"
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
          :rules="{ required: true, message: t('files.message.select_passengers') }"
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
      <div class="input-box duration">
        <label for="duration">{{ t('quote.label.duration') }}: </label>
        <!--        <a-select ref="select" @focus="focus">-->
        <!--          <a-select-option value="dia-entero">Día entero</a-select-option>-->
        <!--          <a-select-option value="medio-dia">Medio día</a-select-option>-->
        <!--        </a-select>-->
        <a-form-item name="duration">
          <a-select
            v-model:value="searchFormState.service_sub_category"
            :options="durationsTypes"
            :field-names="{ label: 'label', value: 'id' }"
            label-in-value
            @change="durationChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box search">
        <label for="service_name">{{ t('quote.label.filter_by_words') }}: </label>
        <a-form-item name="service_name" class="d-block w-50">
          <a-input
            v-model:value="searchFormState.service_name"
            :placeholder="t('quote.label.write_here')"
          />
        </a-form-item>
      </div>
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

  @media only screen and (max-width: 1400px) {
    form#tours.container {
      .input-box.search {
        .ant-row.ant-form-item {
          width: 39.5% !important;
        }
      }
    }
  }
</style>
