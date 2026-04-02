<script lang="ts" setup>
  import { reactive, ref, watch, defineProps, onMounted, computed } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';

  import type { UnwrapRef } from 'vue';
  import type { Rule } from 'ant-design-vue/es/form';

  import IconSearch from '@/quotes/components/icons/IconSearch.vue';
  import IconClear from '@/quotes/components/icons/IconClear.vue';
  import { useSiderBarStore } from '../../store/sidebar';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import useQuoteDestinations from '@/quotes/composables/useQuoteDestinations';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import { useI18n } from 'vue-i18n';
  const { t, locale } = useI18n();
  const { getLang } = useQuoteTranslations();
  const seeAllLabel = computed(() => t('quote.see_all_items'));
  import type {
    QuoteHotelsSearchRequest,
    DestinationsState,
    DestinationsCountry,
    QuoteHotelCategory,
  } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';

  const storeSidebar = useSiderBarStore();
  const quoteStore = useQuoteStore();
  const { isLoading } = storeToRefs(quoteStore);
  const priceRange = ref<Record<number, number>>({
    0: 0,
    150: 150,
    500: 500,
    950: 950,
  });
  const maxRange = 1000;

  // destinations
  const { destinations, getStatesByCountryCode } = useQuoteDestinations();
  // const destinationsCountries = ref<DestinationsCountry[]>([])
  /*
  const destinationsCountry = ref<DestinationsCountry>({
    code: 'PE',
    label: 'Perú',
  });
  */

  const destinationsStates = ref<DestinationsState[]>([]);
  // const destinationsStatesSelected = ref<DestinationsState[]>([])
  // const handleDestinationsStatesChange = (value: { option:DestinationsState }[]) => {
  // destinationsStatesSelected.value = value.length ? value.map(s => s.option):[]
  // };
  const destinationsStatesSelected = ref<DestinationsState>();
  const handleDestinationsStatesChange = (value: { option: DestinationsState }) => {
    destinationsStatesSelected.value = value.option;
  };

  const handleDestinationsCountriesChange = (value: { option: DestinationsCountry }) => {
    destinationsStates.value = getStatesByCountryCode(value.option.code);
    HotelsFormState.destiny = {
      code: 'all',
      label: seeAllLabel.value,
    } as any;
  };

  const props = defineProps({
    items: { type: Object, default: null },
    modalMode: { type: Boolean, default: false },
  });

  // hotels search
  const { getHotels } = useQuoteHotels();
  const { quote, deleteServiceSelected } = useQuote();

  // form
  const HotelsFormRef = ref();

  interface SearchHotelsForm {
    checkIn: Dayjs | undefined;
    checkOut: Dayjs | undefined;
    destiny: DestinationsState | undefined;
    country: DestinationsCountry | undefined;
    categories: QuoteHotelCategory | undefined;
    priceRange: { min: number; max: number };
    keywordFilter: string;
  }

  const HotelsFormState: UnwrapRef<SearchHotelsForm> = reactive({
    checkIn: dayjs(quote.value.date_in),
    checkOut: dayjs(quote.value.date_in).add(quote.value.nights, 'day'),
    destiny: {
      code: 'all',
      label: seeAllLabel.value,
    } as any,
    country: {
      code: 'PE',
      label: 'Perú',
    },
    categories: { value: 'all', label: seeAllLabel.value, selected: false } as any,
    priceRange: {
      min: 0,
      max: 850,
    },
    keywordFilter: '',
  });

  // watch(destinationsCountry,  ()=> {
  watch(
    destinations,
    () => {
      destinationsStates.value = getStatesByCountryCode(HotelsFormState?.country?.code ?? 'PE');
    },
    { immediate: true }
  );

  // categories
  const { quoteHotelCategories: categories } = useQuoteHotelCategories();
  // const selectedCategories = ref<QuoteHotelCategory[]>([])
  // const handleDestinationsCategoriesChange = (value: { option:QuoteHotelCategory }[]) => {
  // selectedCategories.value = value.length ? value.map(s => s.option):[]
  // };
  const selectedCategories = ref<QuoteHotelCategory>({
    value: 'all',
    label: t('quote.see_all_items'),
    selected: false,
  });
  const handleDestinationsCategoriesChange = (value: { option: QuoteHotelCategory }) => {
    selectedCategories.value = value.option;
  };

  const categoryOptions = computed(() => {
    return [{ value: 'all', label: seeAllLabel.value, selected: false }, ...categories.value];
  });

  const computedDestinationsCountries = computed(() => [
    ...(destinations.value?.destinationsCountries ?? []),
  ]);

  const computedDestinationsStates = computed(() => [
    { label: seeAllLabel.value, code: 'all', value: 'all' } as any,
    ...(destinationsStates.value ?? []),
  ]);

  watch(locale, () => {
    if (HotelsFormState.destiny?.code === 'all') {
      HotelsFormState.destiny.label = seeAllLabel.value;
    }
    if (HotelsFormState.categories?.value === 'all') {
      HotelsFormState.categories.label = seeAllLabel.value;
    }
    if (selectedCategories.value?.value === 'all') {
      selectedCategories.value.label = seeAllLabel.value;
    }
  });

  const myButton = ref(null);

  // Dates
  const dateFormat = 'DD/MM/YYYY';

  const HotelFormRules: Record<string, Rule[]> = {
    checkIn: [
      {
        required: true,
        message: t('quote.label.select_Check_in'),
        trigger: 'change',
        type: 'object',
      },
    ],
    checkOut: [
      {
        required: true,
        message: t('quote.label.select_Check_out'),
        trigger: 'change',
        type: 'object',
      },
    ],
    destiny: [
      {
        required: true,
        message: t('quote.label.select_destination'),
        trigger: 'change',
      },
    ],
  };

  const quoteHotelsSearchRequest: UnwrapRef<QuoteHotelsSearchRequest> = reactive({
    date_from: '',
    date_to: '',
    quantity_persons_rooms: [],
    quantity_rooms: 1,
    set_markup: 0,
    zero_rates: true,
    hotels_search_code: '',
    allWords: false,
    type_classes: ['all'],
    typeclass_id: 'all',
    lang: getLang(),
    price_range: {
      min: 0,
      max: 850,
    },
    destiny: {
      code: '',
      label: '',
    },
  });

  const searchHotels = () => {
    storeSidebar.setStatus(false, 'hotel', 'search');
    deleteServiceSelected();
    HotelsFormRef.value
      .validate()
      .then(async () => {
        quoteHotelsSearchRequest.date_from = HotelsFormState.checkIn?.format('YYYY-MM-DD') ?? '';
        quoteHotelsSearchRequest.date_to = HotelsFormState.checkOut?.format('YYYY-MM-DD') ?? '';

        quoteHotelsSearchRequest.typeclass_id = selectedCategories.value
          ? selectedCategories.value.value
          : 'all';
        // quoteHotelsSearchRequest.type_classes = selectedCategories.value.length ? selectedCategories.value.map((c) => parseInt(c.code)) : ['all']
        quoteHotelsSearchRequest.type_classes = [quoteHotelsSearchRequest.typeclass_id];

        // quoteHotelsSearchRequest.destiny = destinationsStatesSelected.value?.map((d) => ({
        //   code: destinationsCountry.value.code + ',' + d.code,
        //   label: destinationsCountry.value.label + ', ' + d.label,
        // })) ?? []
        const countryCode = HotelsFormState.country.code;
        const destinyCode =
          destinationsStatesSelected.value?.code === 'all'
            ? ''
            : (destinationsStatesSelected.value?.code ?? '');

        quoteHotelsSearchRequest.destiny = {
          code: countryCode + ',' + destinyCode,
          label:
            HotelsFormState.country.label + ', ' + (destinationsStatesSelected.value?.label ?? ''),
        };

        quoteHotelsSearchRequest.hotels_search_code = HotelsFormState.keywordFilter;
        quoteHotelsSearchRequest.price_range = HotelsFormState.priceRange;

        isLoading.value = true;
        await getHotels(quoteHotelsSearchRequest, props.modalMode);
        isLoading.value = false;

        if (!props.modalMode) {
          storeSidebar.setStatus(true, 'hotel', 'search');
        }
      })
      .catch((error: string) => {
        console.log('error', error);
      });
  };
  const clearForm = () => {
    HotelsFormRef.value.resetFields();
  };

  onMounted(() => {
    if (props.items && props.items.hotel) {
      HotelsFormState.checkIn = dayjs(props.items.date_in_format);
      HotelsFormState.checkOut = dayjs(props.items.date_in_format).add(props.items.nights, 'day');
      let destiny = {
        code: props.items.hotel.state.iso,
        label: props.items.hotel.state.translations[0].value,
        country_code: props.items.hotel.country.iso,
      };

      HotelsFormState.destiny = destiny;
      destinationsStatesSelected.value = destiny;

      let categorySearch = categories.value.find(
        (cate) => cate.value == props.items.hotel.hotelcategory_id
      );

      let categoryButtonSearch = {
        selected: false,
        label: categorySearch.label,
        value: props.items.hotel.hotelcategory_id,
      };
      selectedCategories.value = categoryButtonSearch;
      HotelsFormState.categories = categoryButtonSearch;

      myButton.value.click();
    }
  });
</script>

<template>
  <a-form
    id="hotels"
    class="container"
    ref="HotelsFormRef"
    :model="HotelsFormState"
    :rules="HotelFormRules"
  >
    <div class="row-box">
      <div class="input-box">
        <label for="start-date">{{ t('quote.label.check_in') }}: *</label>
        <a-form-item
          name="checkIn"
          :rules="{ required: true, message: t('quote.label.select_Check_in') }"
        >
          <a-date-picker
            v-model:value="HotelsFormState.checkIn"
            id="start-date"
            :format="dateFormat"
          />
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="end-date">{{ t('quote.label.check_out') }}: *</label>
        <a-form-item
          name="checkOut"
          :rules="{
            required: true,
            message: t('quote.label.select_Check_out'),
          }"
        >
          <a-date-picker
            v-model:value="HotelsFormState.checkOut"
            id="end-date"
            :format="dateFormat"
          />
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="country">{{ t('quote.label.country') }}: *</label>
        <a-form-item
          name="country"
          :rules="{
            required: HotelsFormState.keywordFilter == '',
            message: t('quote.label.select_country'),
          }"
        >
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="HotelsFormState.country"
            :options="computedDestinationsCountries"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="handleDestinationsCountriesChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="country">{{ t('quote.label.destination') }}: *</label>
        <a-form-item
          name="destiny"
          :rules="{
            required: HotelsFormState.keywordFilter == '',
            message: t('quote.label.select_destination'),
          }"
        >
          <a-select
            showSearch
            optionFilterProp="label"
            v-model:value="HotelsFormState.destiny"
            :options="computedDestinationsStates"
            :field-names="{ label: 'label', value: 'code' }"
            label-in-value
            @change="handleDestinationsStatesChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="country">{{ t('quote.label.category') }}: *</label>
        <a-form-item name="categories">
          <a-select
            v-model:value="HotelsFormState.categories"
            :options="categoryOptions"
            label-in-value
            @change="handleDestinationsCategoriesChange"
          ></a-select>
        </a-form-item>
      </div>
      <div class="input-box">
        <label for="slider-range">{{ t('quote.label.price_range') }}: *</label>
        <a-form-item name="priceRange">
          <a-slider
            v-model:value="HotelsFormState.priceRange.max"
            :marks="priceRange"
            :max="maxRange"
          >
            <template #mark="{ label, point }">
              <template v-if="point === 1000">
                <strong>{{ label }}</strong>
              </template>
              <template v-else>{{ label }}</template>
            </template>
          </a-slider>
        </a-form-item>
      </div>
    </div>
    <div class="row-box">
      <div class="input-box search">
        <label for="country">{{ t('quote.label.filter_by_words') }}: </label>
        <a-form-item name="keywordFilter" class="mb-0 d-block w-50">
          <a-input v-model:value="HotelsFormState.keywordFilter" placeholder="Escribe aquí" />
        </a-form-item>
      </div>
      <div class="actions_buttons">
        <div class="text" :class="{ disabled: isLoading }" @click="!isLoading && clearForm()">
          <icon-clear />
          <span>{{ t('quote.label.clean_filters') }}</span>
        </div>
        <div class="search_button_container" :class="{ disabled: isLoading }">
          <div class="search-button" ref="myButton" @click="!isLoading && searchHotels()">
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
  form#hotels.container {
    gap: 12px;

    .input-box.search {
      .ant-row.ant-form-item {
        width: 26.5% !important;
      }
    }
  }

  .mb-0 {
    margin-bottom: 0px !important;
  }

  @media only screen and (max-width: 1400px) {
    form#hotels.container {
      .input-box.search {
        .ant-row.ant-form-item {
          width: 28.5% !important;
        }
      }
    }
  }
</style>
