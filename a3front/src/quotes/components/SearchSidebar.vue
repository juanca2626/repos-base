<script lang="ts" setup>
  import { computed, onMounted, reactive, ref, toRef, UnwrapRef, watch } from 'vue';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useI18n } from 'vue-i18n';
  import useQuoteDestinations from '@/quotes/composables/useQuoteDestinations';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { DestinationsState } from '@/quotes/interfaces';

  const { t } = useI18n();
  const { serviceSelected } = useQuote();
  const { destinations, getStatesByCountryCode } = useQuoteDestinations();
  const { quoteHotelCategories: categories } = useQuoteHotelCategories();

  const destinationsStateHotels = ref<DestinationsState[]>([]);

  interface SearchForm {
    country: DestinationsCountry;
  }

  const HotelsFormRef = ref();

  const searchFormState: UnwrapRef<SearchForm> = reactive({
    country: {
      iso: 'PE',
      code: '89',
      label: 'Perú',
    },
    state: null,
    service_type: null,
    service_name: '',
  });

  // const HotelFormRules: Record<string, Rule[]> = {
  //   state: [
  //     {
  //       required: true,
  //       message: t('quote.label.select_destination'),
  //       trigger: 'change',
  //       type: 'object',
  //     },
  //   ],
  //   service_name: [
  //     {
  //       required: true,
  //       message: t('quote.label.select_destination'),
  //       trigger: 'change',
  //     },
  //   ],
  // };

  interface Emits {
    (e: 'change', value: number): void;
  }

  const emits = defineEmits<Emits>();

  interface Props {
    show: boolean;
    page: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    show: false,
    page: '',
  });

  const show = toRef(props, 'show');
  const page = toRef(props, 'page');

  // const show_occupations = inject('show-occupations');

  const { servicesTypes, getCountryStates } = useQuoteServices();
  const destinationsStates = computed(() => getCountryStates(searchFormState.country.code));
  const servicesTypesFiltered = computed(() => servicesTypes.value.filter((t) => t.code !== 'NA'));

  const searchServiceReplace = async () => {
    HotelsFormRef.value
      .validate()
      .then(async () => {
        emits(
          'change',
          false,
          searchFormState.country,
          searchFormState.state,
          searchFormState.service_type,
          searchFormState.service_name
        );
      })
      .catch((error) => {
        console.log('error', error);
      });
  };

  watch(
    destinations,
    () => {
      destinationsStateHotels.value = getStatesByCountryCode();
    },
    { immediate: true }
  );

  onMounted(() => {
    if (Object.keys(serviceSelected.value).length > 0) {
      if (page.value == 'hotel') {
        searchFormState.state = {
          value: serviceSelected.value.service.hotel.state.iso,
          label: serviceSelected.value.service.hotel.state.translations[0].value,
        };

        searchFormState.service_type = {
          value: serviceSelected.value.service.hotel.typeclass.id,
          label: serviceSelected.value.service.hotel.typeclass.translations[0].value,
        };
      }

      if (page.value == 'service') {
        searchFormState.state = {
          value: serviceSelected.value.service.service.service_origin[0].state.id,
          label:
            serviceSelected.value.service.service.service_origin[0].state.translations[0].value,
        };

        searchFormState.service_type = {
          value: serviceSelected.value.service.service.service_type.id,
          label: serviceSelected.value.service.service.service_type.translations[0].value,
        };
      }
    }
  });
</script>

<template>
  <div v-if="show">
    <a-form id="hotels" class="container" ref="HotelsFormRef" :model="searchFormState">
      <div class="top" style="width: 260px; height: auto; padding: 12px 16px">
        <div class="block">
          <a-form-item
            label=""
            name="state"
            :rules="[{ required: true, message: 'select a city' }]"
          >
            <a-select
              v-if="page == 'hotel'"
              style="width: 100%"
              showSearch
              optionFilterProp="label"
              v-model:value="searchFormState.state"
              :options="destinationsStateHotels"
              :field-names="{ label: 'label', value: 'code' }"
              label-in-value
            ></a-select>

            <a-select
              v-if="page == 'service'"
              style="width: 100%"
              showSearch
              optionFilterProp="label"
              v-model:value="searchFormState.state"
              :options="destinationsStates"
              :field-names="{ label: 'label', value: 'code' }"
              label-in-value
            ></a-select>
          </a-form-item>
        </div>
        <div class="block">
          <a-select
            v-if="page == 'hotel'"
            style="width: 100%"
            v-model:value="searchFormState.service_type"
            :options="categories"
            :field-names="{ label: 'label', value: 'value' }"
            label-in-value
            :allow-clear="true"
          ></a-select>

          <a-select
            v-if="page == 'service'"
            style="width: 100%"
            v-model:value="searchFormState.service_type"
            :options="servicesTypesFiltered"
            :field-names="{ label: 'label', value: 'id' }"
            label-in-value
            :allow-clear="true"
          ></a-select>
        </div>

        <div class="block">
          <a-input
            :placeholder="t('quote.label.search')"
            v-model:value="searchFormState.service_name"
            size="large"
          />
        </div>
      </div>

      <div class="bottom widthFull">
        <button class="ok normal button-component" @click="searchServiceReplace()">
          {{ t('quote.label.search') }}
        </button>
      </div>
    </a-form>
  </div>
</template>

<style lang="scss" scoped>
  .child_error {
    .box {
      border: 1px solid red !important;

      :deep(.amountN) {
        color: red !important;
      }
    }
  }

  .box-passengers {
    display: inline-flex;
    padding: 12px 16px;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    border-radius: 0 0 6px 6px;
    background: #ffffff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    width: 250px;
    position: absolute;
    top: 35px;

    .box {
      display: flex;
      align-items: center;
      border: 1px solid #c4c4c4;
      border-radius: 4px;
      gap: 8px;
      width: 100%;
      justify-content: center;
      align-items: center;

      span {
        color: #575757;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 21px;
        letter-spacing: 0.21px;
      }

      input {
        border: none;
        width: 100%;
      }

      :deep(.ant-input-number) {
        input {
          text-align: center;
          padding: 0;
        }
      }
    }

    .top {
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .bottom {
      display: flex;
      align-items: center;
      gap: 0;

      span {
        color: #4f4b4b;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.21px;
        text-align: right;
        width: 100px;
        margin-top: -7px;
      }

      .block {
        display: flex;
        height: 45px;
        padding: 5px 10px;
        align-items: center;
        gap: 16px;
        border-radius: 4px;
        background: #ffffff;
        width: 75px;
        padding-right: 0;
      }

      .ok {
        min-width: 117px;
        width: 117px;
        margin: 0 auto;
      }
    }

    .block {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 65px;

      .input {
        display: flex;
        padding: 0 1px;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        width: 100%;

        label {
          color: #575757;
          font-size: 14px;
          font-style: normal;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.21px;
          align-self: stretch;
        }

        input {
          visibility: hidden;
          height: 0;
          width: 0;
        }
      }
    }
  }

  .block {
    margin-bottom: 10px;

    .ant-select {
      height: 40px;

      :deep(.ant-select-selector) {
        height: 40px;
      }

      :deep(.ant-select-selection-item) {
        line-height: 40px;
      }
    }

    :deep(.ant-input) {
      height: 40px;
      font-size: 16px;
    }

    :deep(.ant-input-search-large .ant-input-search-button) {
      height: 40px;
      border: 1px solid #d9d9d9;
      border-left: 0;
    }
  }

  .bottom.widthFull {
    padding: 0 0 15px 0;

    button {
      width: 120px;
      min-width: 120px;
      margin: 0 auto;
    }
  }
</style>
