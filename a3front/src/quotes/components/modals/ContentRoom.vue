<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import type { RateRate, RoomRate } from '@/quotes/interfaces';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import HotelRatePlanPoliciesPopver from '@/quotes/components/modals/HotelRatePlanPoliciesPopver.vue';
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

  const emits = defineEmits(['checked']);
  const toggleCheck = () => {
    emits('checked', !isChecked.value);
  };

  interface Props {
    roomName: string;
    roomDescription: string;
    ratePlan: RoomRate;
    isChecked: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    roomName: '',
    roomDescription: '',
    ratePlan: undefined,
    isChecked: false,
  });

  const roomName = toRef(props, 'roomName');
  const roomDescription = toRef(props, 'roomDescription');
  const ratePlan = toRef(props, 'ratePlan');
  const isChecked = toRef(props, 'isChecked');

  const rateName = computed(() => ratePlan.value.name);
  const ratePrice = computed(() => parseFloat(ratePlan.value.total));
  const rate = computed<RateRate>(() => ratePlan.value.rate[0]);
  const onRequest = computed(() => ratePlan.value.onRequest);
</script>

<template>
  <div class="row-room">
    <div class="name">
      <div>
        {{ t('quote.label.name') }}: <span>{{ roomName }}</span>
      </div>
      <div>
        {{ t('quote.label.description') }}: <span>{{ roomDescription }}</span>
      </div>
    </div>

    <div class="description">
      <div class="icon">
        <icon-confirmed :height="22" :width="22" v-if="onRequest" />
        <icon-on-request :height="22" :width="22" v-else />
      </div>

      <div class="icon">
        <hotel-rate-plan-policies-popver :rate-plan="ratePlan" />
      </div>

      <div class="content">
        <div>{{ rateName }}:</div>
        <div>{{ rate.amount_days[0].date }}</div>
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
  .row-room {
    display: flex;
    padding: 20px 50px 30px 12px !important;
    align-items: flex-start;
    gap: 38px;
    flex: 1 0 0;
    align-self: stretch;
    position: relative;
    border-bottom: 1px solid #bdbdbd !important;

    &:last-child {
      border-bottom: 0 !important;
    }

    .name {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 5px;
      align-self: stretch;
      flex-basis: 38%;

      div {
        display: flex;
        align-items: center;
        gap: 14px;
        align-self: stretch;
        color: #4f4b4b;
        font-size: 12px;
        font-style: normal;
        font-weight: 700;
        line-height: 19px; /* 158.333% */
        letter-spacing: 0.18px;

        span {
          font-weight: 400;
        }
      }
    }

    .description {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 7px;
      align-self: stretch;
      margin-bottom: 0 !important;

      .content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        color: #000;
        font-size: 12px;
        font-style: normal;
        font-weight: 400;
        line-height: 19px; /* 158.333% */
        letter-spacing: 0.18px;
        height: 10vh !important;

        div:first-child {
          font-weight: bold;
        }

        .price {
          color: #eb5757;
          font-size: 16px;
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
      right: 15px;
      top: 30px !important;
      border-radius: 4px;

      &.checked {
        border: 1px solid #eb5757;
        background: #eb5757;
      }
    }
  }
</style>
