<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, onMounted, reactive, watchEffect } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { notification } from 'ant-design-vue';

  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconMagnifyingGlass from '@/quotes/components/icons/IconMagnifyingGlass.vue';
  import type { GroupedServices } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import { getLang } from '@/quotes/helpers/get-lang';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';

  import { useI18n } from 'vue-i18n';
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  const quoteStore = useQuoteStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));
  const { t } = useI18n();

  const state = reactive({
    modalHotelDetail: {
      isOpen: false,
    },
    modalActive: '',
  });
  console.log(state);

  const { serviceSelected: groupedService, updateServiceDate, unsetServiceEdit } = useQuote();
  const { getHotels } = useQuoteHotels();

  const service = computed(() => (groupedService.value as GroupedServices).service);
  const nights = computed(() => service.value.nights);
  const cityName = computed(() => service.value.hotel?.city.translations[0].value);
  const price = computed(() =>
    service.value.amount && service.value.amount.length > 0
      ? service.value.amount[0].price_adult
      : 0
  );

  interface SearchHotelsForm {
    checkIn: Dayjs | undefined;
    nights: number;
  }

  const hotelsFormState: UnwrapRef<SearchHotelsForm> = reactive({
    checkIn: dayjs(),
    nights: 0,
  });

  onMounted(() => {
    hotelsFormState.checkIn = dayjs(service.value.date_in, 'DD/MM/YYYY');
    hotelsFormState.nights = nights.value;
  });

  watchEffect(() => {
    if (service.value) {
      hotelsFormState.checkIn = dayjs(service.value.date_in, 'DD/MM/YYYY');
      hotelsFormState.nights = nights.value;
    }
  });

  const storeSidebar = useSiderBarStore();
  const updateSelectedService = async () => {
    await updateServiceDate(
      dayjs(hotelsFormState.checkIn).format('YYYY-MM-DD'),
      hotelsFormState.nights
    )
      .then(() => {
        unsetServiceEdit();

        storeSidebar.setStatus(false, 'search', '');
      })
      .catch((e) => {
        openNotificationWithIcon(e.message);
      });
  };
  const openNotificationWithIcon = (message: string) => {
    notification['error']({
      message: 'Error',
      description: message,
    });
  };
  const searchHotelsToReplace = async () => {
    await getHotels({
      // We look for the available hotels to be able to add the room
      date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      date_to: dayjs(service.value.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      destiny: {
        code: service.value.hotel!.country.iso + ',' + service.value.hotel!.state.iso,
        label:
          service.value.hotel!.country.translations[0].value +
          ',' +
          service.value.hotel!.state.translations[0].value,
      },
      hotels_id: [],
      lang: getLang(),
      quantity_persons_rooms: [],
      quantity_rooms: 1,
      set_markup: 0,
      typeclass_id: service.value.hotel!.typeclass_id,
      zero_rates: true,
    });

    storeSidebar.setStatus(true, 'hotel', 'search');
  };
</script>

<template>
  <div>
    <div class="editComponent">
      <div class="place">
        <div>
          <icon-magnifying-glass />
          {{ cityName }}
        </div>
        <!--<div>{{ nights + 1 }}D / {{ nights }}N</div>-->
      </div>

      <div class="description">
        <div class="input-box">
          <a-date-picker
            v-model:value="hotelsFormState.checkIn"
            id="start-date"
            :format="'DD/MM/YYYY'"
          />
        </div>

        <div class="block">
          <div class="input">
            <label>{{ t('quote.label.nights') }}</label>
            <div class="box">
              <AmountComponent v-model:amount="hotelsFormState.nights" :min="0" :max="80" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row-flex">
      <div class="quotes-actions-btn save" @click="updateSelectedService">
        <div class="content">
          <div class="text">{{ t('quote.label.save') }}</div>
        </div>
      </div>

      <div class="quotes-actions-btn" @click="searchHotelsToReplace">
        <div class="content">
          <div class="text">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M17 2.29419L21 5.8236L17 9.35301"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M3 11.1178V9.35314C3 8.41708 3.42143 7.51936 4.17157 6.85747C4.92172 6.19558 5.93913 5.82373 7 5.82373H21"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M7 21.706L3 18.1766L7 14.6472"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M21 12.8826V14.6473C21 15.5833 20.5786 16.4811 19.8284 17.1429C19.0783 17.8048 18.0609 18.1767 17 18.1767H3"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            {{ t('quote.label.replace') }}
          </div>
        </div>
      </div>

      <div class="price">
        ${{ displayPrice(price) }} <span>{{ t('quote.label.per_room') }}</span>
        <span
          v-if="showCommissionBadge"
          class="badge-warning ml-2"
          style="font-size: 10px; padding: 1px 2px"
          >{{ t('global.label.with_commission') }}</span
        >
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .quotes-actions-btn.save .content .text {
    color: #fff !important;
  }

  .row-flex {
    padding: 21px 18px;
    border-top: 1px solid #c4c4c4;
  }

  .description {
    height: 350px;
    padding: 0 16px 0 16px;
  }

  .block {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 65px;
    margin-top: 24px;

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
      }
    }
  }

  :deep(.ant-picker) {
    width: 100%;
    font-size: 12px;

    .ant-picker-suffix {
      color: #eb5757;
    }
  }
</style>
