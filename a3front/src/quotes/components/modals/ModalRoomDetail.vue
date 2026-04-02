<script lang="ts" setup>
  import { computed, onMounted, ref, toRef } from 'vue';

  import ContentRoom from '@/quotes/components/modals/ContentRoom.vue';
  import ContentPromotion from '@/quotes/components/modals/ContentPromotion.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import IconHotelsDark from '@/quotes/components/icons/IconHotelsDark.vue';
  import HotelPoliciesPopver from '@/quotes/components/modals/HotelPoliciesPopver.vue';
  import ModalRoomTypesAndQuantity from '@/quotes/components/modals/ModalRoomTypesAndQuantity.vue';
  import IconMagnifyingDollar from '@/quotes/components/icons/IconMagnifyingDollar.vue';
  import type { Hotel } from '@/quotes/interfaces';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  // const { quote } = useQuote();

  // Props
  interface Props {
    hotel: Hotel;
    accommodation: PropAccommodation;
    hotelRatesSelected: { [key: string | number]: boolean };
    hotelRatesPromoSelected: { [key: string | number]: boolean };
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
  const accommodation = toRef(props, 'accommodation');
  const hotelRatesSelected = toRef(props, 'hotelRatesSelected');
  const hotelRatesPromoSelected = toRef(props, 'hotelRatesPromoSelected');

  console.log(accommodation);

  const {
    // hotelSelected: hotel,
    promotions,
  } = useQuoteHotels();

  const activePromo = ref([`promo-hotel-${hotel.value!.id}`]);

  // Emits
  interface Emits {
    (e: 'changeTab', val: string): void;

    (e: 'update:hotelRatesSelected', value: { [key: string | number]: boolean }): void;

    (e: 'update:hotelRatesPromoSelected', value: { [key: string | number]: boolean }): void;
  }

  const emits = defineEmits<Emits>();

  // Change tab handler
  const activeTab = ref<string>('');
  const changeTab = (val: string) => {
    emits('changeTab', val);
  };

  // Reta plan selected handler
  const hotelRatesCheck = (isChecked: boolean, rateId: number) => {
    hotelRatesSelected.value[rateId] = isChecked;

    emits('update:hotelRatesSelected', hotelRatesSelected.value);
  };
  const hotelRatesPromotionCheck = (isChecked: boolean, rateId: string) => {
    hotelRatesPromoSelected.value[rateId] = isChecked;

    emits('update:hotelRatesPromoSelected', hotelRatesPromoSelected.value);
  };

  // Promotions available
  const promotionsAvailable = computed(() =>
    promotions.value.filter(
      (h) => h.id !== hotel.value?.id && h.order_class >= hotel.value.order_class
    )
  );

  const visibilityRoom = (occupation: number) => {
    if (occupation == 1 && accommodation.value.single.checked === true) {
      return true;
    }

    if (occupation == 2 && accommodation.value.double.checked === true) {
      return true;
    }

    if (occupation == 3 && accommodation.value.triple.checked === true) {
      return true;
    }

    if (
      accommodation.value.single.checked === false &&
      accommodation.value.double.checked === false &&
      accommodation.value.triple.checked === false
    ) {
      return true;
    }

    return false;
  };

  // show data once the dom is ready
  onMounted(() => {
    setTimeout(() => {
      activeTab.value = 'hotel';
    }, 50);
  });
</script>

<template>
  <div class="container">
    <a-tabs v-model:activeKey="activeTab" type="card" @change="changeTab">
      <a-tab-pane key="hotel" :tab="t('quote.label.chosen_hotel')">
        <div class="titlePopup">
          <h4>
            {{ t('quote.label.hotel_rates') }}: {{ hotel.name }}
            <hotel-policies-popver :hotel="hotel" />
          </h4>

          <div class="clases">
            <div class="categoria">{{ hotel.class }}</div>
          </div>
        </div>

        <modal-room-types-and-quantity :accommodation="accommodation" />

        <div class="contentRooms">
          <template v-for="room of hotel.rooms" :key="`${hotel.id}-${room.room_id}`">
            <div v-if="visibilityRoom(room.occupation)">
              <ContentRoom
                v-for="rate of room.rates"
                :key="`${hotel.id}-${room.room_id}-${rate.rateId}`"
                :room-name="room.name"
                :room-description="room.description"
                :rate-plan="rate"
                :is-checked="hotelRatesSelected[rate.rateId]"
                @checked="(isChecked) => hotelRatesCheck(isChecked, rate.rateId)"
              />
            </div>
          </template>
        </div>
      </a-tab-pane>

      <a-tab-pane key="promos">
        <template #tab>
          <span class="iconTab">
            <icon-magnifying-dollar :width="36" :height="37" color="#1ED790" />
            {{ t('quote.label.promotions') }}
          </span>
        </template>

        <div class="titlePopup">
          <h4>
            <icon-magnifying-dollar />
            {{ t('quote.label.promotions') }}
          </h4>

          <div class="clases">
            <div class="categoria promo">
              {{ t('quote.label.all_categories') }}
            </div>
          </div>
        </div>

        <modal-room-types-and-quantity :accommodation="accommodation" />

        <div class="contentRooms">
          <a-collapse v-model:activeKey="activePromo" expand-icon-position="right">
            <a-collapse-panel v-for="promo of promotionsAvailable" :key="`promo-hotel-${promo.id}`">
              <template #extra>
                <div class="hotel-header">
                  <div class="flex">
                    <icon-hotels-dark />
                    {{ promo.name }}
                  </div>
                </div>
              </template>

              <div v-for="room of promo.rooms" :key="`promo-room-${promo.id}-${room.room_id}`">
                <div v-if="visibilityRoom(room.occupation)">
                  <ContentPromotion
                    v-for="rate of room.rates"
                    :key="`promo-room-${promo.id}-${room.room_id}-${rate.rateId}`"
                    :accommodation="accommodation"
                    :hotel="promo"
                    :room-name="room.name"
                    :room-description="room.description"
                    :rate-plan="rate"
                    :is-checked="hotelRatesPromoSelected[`${promo.id}-${rate.rateId}`]"
                    @checked="
                      (isChecked) =>
                        hotelRatesPromotionCheck(isChecked, `${promo.id}-${rate.rateId}`)
                    "
                  />
                </div>
              </div>
            </a-collapse-panel>
          </a-collapse>
        </div>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<style lang="scss" scoped>
  .flex {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 700;
    color: #3d3d3d;
  }

  .iconTab {
    align-items: center;
    display: flex;
    text-transform: uppercase;
    gap: 8px;

    svg {
      fill: #1ed790;
      width: 24px;
      height: 24px;
    }

    svg path {
      fill: #1ed790;
    }
  }

  .contentRooms {
    max-height: 35vh;
    overflow: auto;
    padding-bottom: 20px;

    /* width */
    &::-webkit-scrollbar {
      width: 5px;
      margin-right: 4px;
      padding-right: 2px;
    }

    /* Track */
    &::-webkit-scrollbar-track {
      border-radius: 10px;
    }

    /* Handle */
    &::-webkit-scrollbar-thumb {
      border-radius: 4px;
      background: #c4c4c4;
      margin-right: 4px;
    }

    /* Handle on hover */
    &::-webkit-scrollbar-thumb:hover {
      background: #eb5757;
    }

    .row-room {
      padding-bottom: 35px;
      margin-bottom: 20px;
      border-bottom: 1px solid #c4c4c4;

      &:last-child {
        margin-bottom: 0;
        border-bottom: 0;
      }
    }
  }

  .container {
    display: flex;
    flex-direction: column;
    padding: 0 20px;
    gap: 35px;

    .titlePopup {
      display: flex;
      flex-direction: column;
      padding: 42px 12px 0;

      h4 {
        color: #3d3d3d;
        font-size: 36px;
        font-style: normal;
        font-weight: 600;
        line-height: 43px; /* 119.444% */
        letter-spacing: -0.36px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
      }

      svg {
        outline: none !important;
      }
    }

    .clases {
      display: flex;
      align-items: center;
      padding: 5px 0 0 0;
      gap: 10px;

      .categoria {
        display: flex;
        height: 27px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #4c90e0;
        border-radius: 6px;
        color: #fff;
        font-weight: 400;
        font-size: 14px;
        padding: 10px 20px;
        min-width: 120px;

        &.promo {
          background: rgba(224, 69, 61, 1);
        }
      }
    }

    .infoSelect {
      display: flex;
      padding: 10px 12px 20px 12px;
      align-items: center;
      gap: 20px;
      align-self: stretch;

      .box-passengers {
        display: inline-flex;
        padding: 0;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
        border-radius: 0 0 6px 6px;
        background: #ffffff;
        width: 100%;

        .box {
          display: flex;
          align-items: center;
          border: 1px solid #c4c4c4;
          border-radius: 4px;
          gap: 8px;
          width: 100%;
          justify-content: center;
          align-items: center;
          padding: 4px;
          box-sizing: border-box;
          height: 31px;

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

          :deep(.buttons) {
            gap: 0;
          }

          &.check {
            width: 30px;
            height: 30px;
            border: 1px solid #c4c4c4;
            cursor: pointer;

            &.checked {
              border: 1px solid #eb5757;
              background: #eb5757;
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
        }

        .block {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: 14px;

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
    }
  }

  :deep(.ant-tabs-top > .ant-tabs-nav::before) {
    border-bottom: 0;
    margin: 0 40px;
  }

  :deep(.ant-tabs-content-holder) {
    margin: 0 30px;
  }

  :deep(.ant-tabs-nav-list) {
    width: 100%;
  }

  :deep(.ant-tabs-tab) {
    background: none !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 1px solid #f0f0f0;
    margin: 0;
    padding: 0 100px !important;
    height: 58px;
    color: #4f4b4b !important;
    text-align: center;
    font-size: 18px;
    font-style: normal;
    font-weight: 600;
    line-height: 25px;
    width: 50%;

    &.ant-tabs-tab-active {
      border-top: 1px solid #f0f0f0 !important;
      border-left: 1px solid #f0f0f0 !important;
      border-right: 1px solid #f0f0f0 !important;
      border-bottom: 0;
      color: inherit;

      .ant-tabs-tab-btn {
        color: inherit;
      }
    }
  }

  :deep(.ant-tabs-tab:nth-child(2n)) {
    color: #1ed790 !important;
  }

  :deep(.ant-collapse) {
    .ant-collapse-item > .ant-collapse-header .ant-collapse-extra {
      margin: 0;
    }
  }

  :deep(.ant-collapse) {
    .ant-collapse-content-box {
      background: unset;
    }
  }

  .hotel-policies {
    font-family: Montserrat;
    font-size: 0.75rem;
    font-style: normal;
    font-weight: 400;
    line-height: 1.188rem; /* 158.333% */
    letter-spacing: 0.011rem;
    max-width: 240px;

    h4 {
      font-size: 0.75rem;
      font-weight: 700;
      margin-bottom: 0;
    }

    &-general,
    &-cancelation {
      padding: 10px;
    }

    &-cancelation {
      background-color: #fff2f2;
    }
  }

  .hotel-header {
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
    }
  }
</style>
