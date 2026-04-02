<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, reactive, ref, onMounted, watch } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';

  import { useSiderBarStore } from '../../store/sidebar';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import IconSearch from '@/quotes/components/icons/IconSearch.vue';

  import type {
    DestinationsState,
    DestinationsCountry,
    QuoteHotelCategory,
  } from '@/quotes/interfaces';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type { ServicesType } from '@/quotes/interfaces/services';
  import type { Rule } from 'ant-design-vue/es/form';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import { useI18n } from 'vue-i18n';

  const { t, locale } = useI18n();
  const seeAllLabel = computed(() => t('quote.see_all_items'));
  // store
  // TODO: refactor to use composable instead
  const storeSidebar = useSiderBarStore();
  const quoteStore = useQuoteStore();
  const { isLoading } = storeToRefs(quoteStore);
  const { getLang } = useQuoteTranslations();
  // Composable
  const { quote, deleteServiceSelected, selectedCategory } = useQuote();
  const { servicesTypes, servicesDestinations, getCountryStates, getExtensionsAvailable } =
    useQuoteServices();

  // categories
  const { quoteHotelCategories: categories } = useQuoteHotelCategories();

  const destinationsStates = computed(() => [
    { label: seeAllLabel.value, code: 'all' } as any,
    ...(getCountryStates(searchFormState.country?.code ?? '89') || []),
  ]);
  const servicesTypesFiltered = computed(() => [
    { id: '', label: seeAllLabel.value, code: '' } as unknown as ServicesType,
    ...servicesTypes.value.filter((t) => t.code !== 'NA'),
  ]);

  const categoryOptions = computed(() => {
    return [
      { value: 'all', label: seeAllLabel.value, selected: false },
      ...(categories.value || []),
    ];
  });

  watch(locale, () => {
    if (searchFormState.state?.code === 'all') {
      searchFormState.state.label = seeAllLabel.value;
    }
    if (searchFormState.service_type?.id === '') {
      searchFormState.service_type.label = seeAllLabel.value;
    }
    if (searchFormState.categories?.value === 'all') {
      searchFormState.categories.label = seeAllLabel.value;
    }
  });

  const date_from = computed(() => {
    const categories = quote.value.categories.find((c) => {
      return c.type_class_id === selectedCategory.value;
    });

    let date_in = [];
    categories.services.forEach((element) => {
      if (element.type == 'group_extension') {
        element.extensions.forEach((element2) => {
          date_in.push(new Date(element2.service.date_in_format));
        });
      } else {
        date_in.push(new Date(element.service.date_in_format));
      }
    });

    let maxDate = new Date(Math.max.apply(null, date_in));

    return maxDate.setDate(maxDate.getDate() + 1);
  });

  // Form
  const searchFormRef = ref();

  interface SearchForm {
    date_from: Dayjs | undefined;
    state: DestinationsState | null;
    country: DestinationsCountry | null;
    service_type: ServicesType | null;
    service_name: string;
    categories: QuoteHotelCategory | undefined;
  }

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    date_from: dayjs(date_from.value),
    state: { code: 'all', label: seeAllLabel.value },
    country: { code: '89', label: 'Perú' } as any,
    service_type: { id: '', label: seeAllLabel.value } as any,
    service_name: '',
    categories: { value: 'all', label: seeAllLabel.value } as any,
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
    type_service: [
      {
        required: true,
        message: t('quote.label.select_type_service'),
        trigger: 'change',
      },
    ],
  };

  const myButton = ref(null);

  const props = defineProps({
    items: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  const dateFormat = 'DD/MM/YYYY';

  // const contentScroll = ref('content');

  const countryChange = (value: { option: DestinationsCountry }) => {
    searchFormState.country = value.option;
    searchFormState.state = { code: 'all', label: t('quote.see_all_items') };
  };

  const stateChange = (value: { option: DestinationsState }) => {
    searchFormState.state = value.option;
  };

  const categoryChange = (value: { option: ServicesType }) => {
    searchFormState.service_type = value.option;
  };

  const search = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();
    searchFormRef.value
      .validate()
      .then(async () => {
        const destinationLabel =
          searchFormState.state?.code === 'all' ? '' : (searchFormState.state?.label ?? '');
        const categoryValue =
          searchFormState.categories?.value === 'all'
            ? ''
            : (searchFormState.categories?.value ?? '');

        const requestParams = {
          date: searchFormState.date_from!.format('YYYY-MM-DD'),
          type_service: searchFormState.service_type?.id || [1, 2],
          lang: getLang(),
          filter: searchFormState.service_name,
          destination: destinationLabel,
          type_class_id: categoryValue,
        };

        await getExtensionsAvailable(requestParams, props.modalMode);

        if (!props.modalMode) {
          storeSidebar.setStatus(true, 'extension', 'search');
        }
      })
      .catch((error: string) => {
        console.log('error', error);
      });
  };
  const clearForm = () => {
    searchFormRef.value.resetFields();
  };

  const handleDestinationsCategoriesChange = (value: { option: QuoteHotelCategory }) => {
    searchFormState.categories = value.option;
  };

  onMounted(() => {
    if (props.items && props.items.extensions.length > 0) {
      searchFormState.date_from = dayjs(props.items.date_in_format);

      let origin = destinationsStates.value.find(
        (country) =>
          country.country_code ==
            props.items.service.service.service.service_origin[0].country_id &&
          country.code == props.items.service.service.service.service_origin[0].state_id
      );

      searchFormState.state = origin ?? null;

      myButton.value.click();
    }
  });
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
        <label for="date_from">{{ t('quote.label.day_start') }}: *</label>
        <a-form-item name="date_from">
          <a-date-picker
            v-model:value="searchFormState.date_from"
            id="start-date"
            :format="dateFormat"
          />
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="country">{{ t('quote.label.country') }}: </label>
        <a-form-item name="country">
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="searchFormState.country"
            :options="servicesDestinations.destinationsCountries"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="countryChange"
            :allow-clear="true"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="state">{{ t('quote.label.city') }}: </label>
        <a-form-item name="state">
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
      <div class="input-box">
        <label for="categories">{{ t('quote.label.category') }}: *</label>
        <a-form-item name="categories">
          <a-select
            v-model:value="searchFormState.categories"
            :options="categoryOptions"
            label-in-value
            @change="handleDestinationsCategoriesChange"
          ></a-select>
        </a-form-item>
      </div>
    </div>
    <div class="row-box">
      <div class="input-box search">
        <label for="service_name">{{ t('quote.label.filter_by_words') }}: </label>
        <a-form-item name="service_name" class="mb-0 d-block w-50">
          <a-input
            v-model:value="searchFormState.service_name"
            :placeholder="t('quote.label.write_here')"
          />
        </a-form-item>
      </div>
      <div class="actions_buttons">
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
