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
  import type { ServicesSubType } from '@/quotes/interfaces/services';
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
  const { serviceTypeMeals, getMealsAvailable, getCountryStates } = useQuoteServices();

  // const countries = computed(() => servicesDestinations.value!.destinationsCountries);
  const destinationsStates = computed(() => [
    { label: seeAllLabel.value, code: 'all' } as any,
    ...getCountryStates(searchFormState.country.code),
  ]);
  const typeMeals = computed(() => [
    { id: '', label: seeAllLabel.value } as unknown as ServicesSubType,
    ...serviceTypeMeals.value,
  ]);

  // const default_value = ref<number>(70);

  const priceRange = ref<Record<number, number>>({
    0: 0,
    150: 150,
    500: 500,
    950: 950,
  });
  const maxRange = 1000;
  // Form
  const searchFormRef = ref();

  const filesStore = useFilesStore();

  const props = defineProps({
    isFile: { type: Boolean, default: false },
    items: { type: Object, default: null },
    itinerary: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  interface SearchForm {
    date_from: Dayjs | undefined;
    country: DestinationsCountry;
    state: DestinationsState | null;
    service_sub_category: ServicesSubType | null;
    service_name: string;
    include_temporary: boolean;
    priceRange: { min: number; max: number };
    passengers: [];
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    date_from: dayjs(quote.value.date_in),
    country: {
      code: '89',
      label: 'Perú',
    },
    state: { code: 'all', label: seeAllLabel.value },
    service_sub_category: { id: '', label: seeAllLabel.value } as any,
    service_name: '',
    include_temporary: false,
    priceRange: {
      min: 0,
      max: 850,
    },
    passengers: [],
  });

  // const searchFormRules: Record<string, Rule[]> = {
  //   date_from: [
  //     {
  //       required: true,
  //       message: t('quote.label.select_start_day'),
  //       trigger: 'change',
  //       type: 'object',
  //     },
  //   ],
  //   country: [
  //     {
  //       required: true,
  //       message: t('quote.label.select_country'),
  //       trigger: 'change',
  //     },
  //   ],
  //   state: [
  //     {
  //       required: true,
  //       message: t('quote.label.select_city'),
  //       trigger: 'change',
  //     },
  //   ],
  // };

  const dateFormat = 'DD/MM/YYYY';

  // const countryChange = (value: { option: DestinationsCountry }) => {
  //   searchFormState.country = value.option;
  //   searchFormState.state = null;
  // };
  const stateChange = (value: { option: DestinationsState }) => {
    searchFormState.state = value.option;
  };
  const typeMealChange = (value: { option: ServicesSubType }) => {
    searchFormState.service_sub_category = value.option;
  };

  watch(locale, () => {
    if (searchFormState.state?.code === 'all') {
      searchFormState.state.label = seeAllLabel.value;
    }
    if (searchFormState.service_sub_category?.id === '') {
      searchFormState.service_sub_category.label = seeAllLabel.value;
    }
  });
  const search = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();

    const destiny = {
      code:
        searchFormState.country.code +
        ',' +
        (searchFormState.state?.code === 'all' ? '' : (searchFormState.state?.code ?? '')),
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

          let categories =
            searchFormState.service_sub_category?.id ?? typeMeals.value.map((meal) => meal.id);

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
            type: 'all',
            experience: 'all',
            classification: 'all',
            category: categories,
            limit: 10,
            page: 1,
          });
          filesStore.finished();
        } else {
          await getMealsAvailable(
            {
              adults: quote.value.people[0].adults | 1,
              allWords: 1, // true
              children: quote.value.people[0].child,
              date_from: searchFormState.date_from!.format('YYYY-MM-DD'),
              destiny: '', //destiny
              lang: getLang(),
              origin: destiny,
              service_name: searchFormState.service_name,
              service_sub_category: searchFormState.service_sub_category?.id ?? '',
              price_range: searchFormState.priceRange,
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
      searchFormState.date_from = dayjs(props.items.date_in_format);
      //let country = destinationsStates.value.find(country => country.country_code == props.items.service.service_origin[0].country_id  && country.code == props.items.service.service_origin[0].state_id)
      let city = {
        code: props.items.service.service_origin[0].state.id,
        label: props.items.service.service_origin[0].state.translations[0].value,
        country_code: props.items.service.service_origin[0].country_id,
      };
      searchFormState.state = city;

      let data = props.items.service.service_sub_category;
      searchFormState.service_sub_category = data;

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

  const disabledDate = (current: any) => {
    return current && current < dayjs().startOf('day');
  };
</script>

<template>
  <a-form id="tours" class="container" ref="searchFormRef" :model="searchFormState">
    <div id="meals" class="container">
      <div class="row-box">
        <div class="input-box">
          <label for="start-date">{{ t('quote.label.date') }}: *</label>
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
          <label for="country">{{ t('quote.label.city') }}: *</label>
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
          <label for="country">{{ t('quote.label.kind_of_food') }}: </label>
          <a-form-item name="type_meal">
            <a-select
              v-model:value="searchFormState.service_sub_category"
              :options="typeMeals"
              :field-names="{ label: 'label', value: 'id' }"
              label-in-value
              @change="typeMealChange"
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
        <div class="input-box">
          <label for="start-date">{{ t('quote.label.price_range') }}: *</label>
          <a-slider
            v-model:value="searchFormState.priceRange.max"
            :marks="priceRange"
            :max="maxRange"
          />
        </div>
      </div>
      <div class="row-box">
        <div class="input-box search meals">
          <label for="country">{{ t('quote.label.filter_by_words') }}: </label>
          <a-form-item name="service_name" class="mb-0 d-block w-50">
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
    </div>
  </a-form>
</template>

<style lang="scss" scoped>
  form#tours.container {
    .input-box.search {
      .ant-row.ant-form-item {
        width: 33.5% !important;
      }
    }
  }

  @media only screen and (max-width: 1400px) {
    form#tours.container {
      .input-box.search {
        .ant-row.ant-form-item {
          width: 35.5% !important;
        }
      }
    }
  }
</style>
