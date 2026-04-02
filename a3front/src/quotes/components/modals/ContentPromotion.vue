<script lang="ts" setup>
  import { computed, toRef, watch } from 'vue';
  import IconHotelsDark from '@/quotes/components/icons/IconHotelsDark.vue';
  import type { Hotel, RoomRate } from '@/quotes/interfaces';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import IconCalendarLight from '@/quotes/components/icons/IconCalendarLight.vue';
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
  // const { quote } = useQuote();

  // Emits
  interface Emits {
    (e: 'update:accommodation', value: PropAccommodation): void;

    (e: 'checked', value: checked): void;
  }

  const emits = defineEmits<Emits>();

  //const emits = defineEmits(["checked"]);
  const toggleCheck = () => {
    emits('checked', !isChecked.value);
  };

  interface Props {
    hotel: Hotel;
    roomName: string;
    roomDescription: string;
    ratePlan: RoomRate;
    isChecked: boolean;
    accommodation: PropAccommodation;
  }

  interface PropAccommodation {
    single: AccommodationContent;
    double: AccommodationContent;
    triple: AccommodationContent;
  }

  interface AccommodationContent {
    checked: boolean;
    quantity: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    hotel: undefined,
    roomName: '',
    roomDescription: '',
    ratePlan: undefined,

    rateName: '',
    ratePrice: 0.0,
    rate: undefined,
    onRequest: false,

    isChecked: false,

    accommodation: {
      single: {
        checked: false,
        quantity: 0,
      },
      double: {
        checked: false,
        quantity: 0,
      },
      triple: {
        checked: false,
        quantity: 0,
      },
    },
  });

  const hotel = toRef(props, 'hotel');
  const roomName = toRef(props, 'roomName');
  const ratePlan = toRef(props, 'ratePlan');
  const isChecked = toRef(props, 'isChecked');
  const accommodation = toRef(props, 'accommodation');

  const rateName = computed(() => ratePlan.value.name);
  const ratePrice = computed(() => parseFloat(ratePlan.value.total));
  const rate = computed(() => ratePlan.value.rate[0]);
  const onRequest = computed(() => ratePlan.value.onRequest);

  const startDate = computed(() => rate.value.amount_days[0].date);
  const endDate = computed(() => rate.value.amount_days[rate.value.amount_days.length - 1].date);

  const endDateFormat = computed(() => {
    let maxDate = new Date(endDate.value);
    maxDate.setDate(maxDate.getDate() + 1);

    return maxDate.toISOString().slice(0, 10);
  });

  const nights = computed(() => rate.value.amount_days.length);

  // Accommodation update handler
  watch(accommodation, (value: PropAccommodation) => {
    emits('update:accommodation', value);
  });

  console.log(accommodation);
  console.log('Entraaaaaaaaaaaaa');
</script>

<template>
  <div class="row-promotion">
    <div class="hotel">
      <div>
        <icon-hotels-dark />
        {{ hotel.name }}
      </div>

      <div class="category">{{ hotel.type }}</div>

      <!-- <div class="price">${{ hotel.price }}</div> -->
    </div>

    <div class="description">
      <div class="date">
        <icon-calendar-light :color="'#575757'" :width="26" :height="26" />
        {{ startDate }}
        <span>|</span>
        {{ endDateFormat }}
      </div>

      <div class="night">{{ nights }} {{ t('quote.label.nights') }}</div>

      <div class="typeRoom">
        {{ t('quote.label.room') }}:<span>{{ roomName }}</span>
      </div>

      <div class="prices">
        <div>
          <icon-confirmed :height="25" :width="24" v-if="onRequest" />
          <icon-on-request :height="25" :width="24" v-else />

          {{ rateName }}
        </div>

        <div class="price">
          ${{ displayPrice(ratePrice) }}
          <span
            v-if="showCommissionBadge"
            class="badge-warning ml-2"
            style="font-size: 10px; padding: 1px 2px"
            >{{ t('global.label.with_commission') }}</span
          >
        </div>
      </div>
    </div>

    <div class="check" :class="{ checked: isChecked }" @click="toggleCheck">
      <svg
        v-if="isChecked"
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 20 20"
        fill="none"
      >
        <path
          d="M17.5 4.63037L7.1875 15.2418L2.5 10.4184"
          stroke="white"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .row-promotion {
    background: #fafafa;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: 12px;
    flex: 1 0 0;
    align-self: stretch;
    position: relative;
    padding: 25px 25px 25px 20px;
    border-radius: 8px;
    margin-bottom: 15px;

    &:last-child {
      margin-bottom: 0;
    }

    .hotel {
      display: flex;
      align-items: center;
      gap: 20px;
      flex: 1 0 0;
      align-self: stretch;
      font-size: 18px;
      font-weight: 700;

      div {
        display: flex;
        align-items: center;
        gap: 8px;

        &.category {
          background: #ea3469;
          color: #fff;
          font-size: 12px;
          font-style: normal;
          width: 160px;
          height: 19px;
          font-weight: 700;
          line-height: 19px; /* 158.333% */
          letter-spacing: 0.18px;
          border-radius: 5px;
          justify-content: center;
        }

        &.price {
          background: #1ed790;
          color: #fff;
          text-align: center;
          font-size: 12px;
          font-style: normal;
          font-weight: 700;
          line-height: 19px; /* 158.333% */
          letter-spacing: 0.18px;
          border-radius: 5px;
          padding: 0 8px;
        }
      }
    }

    .description {
      display: flex;
      align-items: center;
      gap: 28px;
      flex: 1 0 0;
      align-self: stretch;
      font-size: 14px;
      margin-bottom: 0 !important;

      div {
        display: flex;
        align-items: center;
        gap: 2px;
        color: #737373;
        font-size: 12px;
        font-style: normal;
        font-weight: 700;
        line-height: 21px; /* 150% */
        letter-spacing: 0.21px;

        span {
          font-size: 18px;
        }

        &.date {
          width: 225px;
          font-size: 14px;
        }

        &.night {
          color: #eb5757;
        }

        &.typeRoom {
          font-size: 12px;
          max-width: 250px;
          text-align: left;
          gap: 10px;

          span {
            font-size: 14px;
            font-weight: 400;
          }
        }

        &:last-child {
          color: #3d3d3d;
          gap: 7px;
        }

        &.prices {
          width: 235px;
          justify-content: space-between;
        }

        .price {
          color: #eb5757;
          font-size: 18px;
          font-style: normal;
          font-weight: 700;
          line-height: 23px; /* 143.75% */
          letter-spacing: -0.24px;
        }
      }
    }

    .check {
      width: 22px;
      height: 22px;
      border: 1px solid #c4c4c4;
      cursor: pointer;
      position: absolute;
      right: 20px;
      top: 50%;
      margin-top: -10px;
      border-radius: 4px;

      &.checked {
        border: 1px solid #eb5757;
        background: #eb5757;
      }
    }
  }
</style>
