<script lang="ts" setup>
  import { computed, onMounted, ref, toRef } from 'vue';
  import dayjs from 'dayjs';

  import { useQuote } from '@/quotes/composables/useQuote';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import type { Hotel, HotelService, HotelTypeClass, QuoteService } from '@/quotes/interfaces';
  import ContentRoom from '@/quotes/components/modals/ContentRoom.vue';
  import { getLang } from '@/quotes/helpers/get-lang';
  import ModalRoomTypesAndQuantity from '@/quotes/components/modals/ModalRoomTypesAndQuantity.vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();

  const { quote, addQuoteServiceHotelRoom } = useQuote();
  const { getHotels, hotels } = useQuoteHotels();

  // Props
  interface Props {
    service: QuoteService;
  }

  const props = defineProps<Props>();

  const service = toRef(props, 'service');
  const hotelService = computed<HotelService>(() => service.value.hotel!);
  const rooms = computed(() => hotel.value?.rooms ?? []);
  const hotelName = computed(() => hotelService.value?.name ?? '');
  const hotelClass = computed<HotelTypeClass>(() => hotelService.value?.typeclass);
  const hotelClassName = computed(() => hotelClass.value?.translations[0].value ?? '');

  // Hotel availability
  const hotel = ref<Hotel>();

  onMounted(() => {
    getHotels({
      // We look for the available hotels to be able to add the room
      date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      date_to: dayjs(service.value.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      destiny: {
        code: hotelService.value.country.iso + ',' + hotelService.value.state.iso,
        label:
          hotelService.value.country.translations[0].value +
          ',' +
          hotelService.value.state.translations[0].value,
      },
      hotels_id: [service.value.object_id],
      lang: getLang(),
      quantity_persons_rooms: [],
      quantity_rooms: 1,
      set_markup: 0,
      typeclass_id: hotelService.value.typeclass_id,
      zero_rates: true,
    }).then(() => {
      const data = hotels.value[0];

      data.rooms.forEach((room) => {
        room.rates.forEach((rate) => {
          rate.selected = false;
        });
      });

      hotel.value = data;
    });
  });

  // Emits
  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();

  const onClose = () => {
    emits('close');
  };

  // Add rooms
  const addServiceRooms = () => {
    // Validando que elija como máximo uno de cada tipo de habitación
    let error = 0;
    let total_sgl = 0;
    let total_dbl = 0;
    let total_tpl = 0;
    hotel.value!.rooms.forEach((r) => {
      r.rates.forEach((rp) => {
        if (rp.selected) {
          if (r.occupation == 1) {
            total_sgl++;
          }
          if (r.occupation == 2) {
            total_dbl++;
          }
          if (r.occupation == 3) {
            total_tpl++;
          }
          if (total_sgl > 1 || total_dbl > 1 || total_tpl > 1) {
            error++;
          }
        }
      });
    });

    if (error > 0) {
      alert(t('quote.label.allowed_per_room_type'));
      return;
    }

    const servicesToAdd = [];
    hotel.value!.rooms.forEach((r) => {
      r.rates.forEach((rp) => {
        if (rp.selected) {
          let quantity = 0;

          if (r.occupation == 1 && accommodation.value.single.checked) {
            quantity = accommodation.value.single.quantity;
          } else if (r.occupation == 2 && accommodation.value.double.checked) {
            quantity = accommodation.value.double.quantity;
          } else if (r.occupation == 3 && accommodation.value.triple.checked) {
            quantity = accommodation.value.triple.quantity;
          }

          for (let i = 0; i < quantity; i++) {
            servicesToAdd.push(addServiceRoomsRequest(rp.rateId, r.occupation, rp.onRequest));
          }
        }
      });
    });

    if (servicesToAdd.length > 0) {
      addQuoteServiceHotelRoom(servicesToAdd);
      onClose();
    }
  };

  const addServiceRoomsRequest = (rateId: number, occupation: number, onRequest: number) => {
    return {
      lang: getLang(),
      quote_id: quote.value.id,
      quote_service_id: service.value.group_quote_service_id,
      rate_plan_room_ids: [],
      rate_plan_rooms_choose: [
        {
          rate_plan_room_id: rateId,
          choose: true,
          occupation: occupation,
          on_request: onRequest,
        },
      ],
    };
  };

  const accommodation = ref({
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
  });

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
</script>

<template>
  <ModalComponent v-if="true" :modal-active="true" class="modal-itinerario-detail" @close="onClose">
    <template #body>
      <div class="icon-close-modal" @click="onClose">
        <icon-close />
      </div>

      <div class="container">
        <div class="titlePopup">
          <h4>{{ t('quote.label.hotel_rates') }}: {{ hotelName }}</h4>
          <div class="clases" v-if="hotelClassName">
            <div class="categoria">{{ hotelClassName }}</div>
          </div>
        </div>

        <modal-room-types-and-quantity :accommodation="accommodation" />

        <div class="contentRooms">
          <template v-for="room of rooms" :key="`${hotel.id}-${room.id}`">
            <template
              v-if="visibilityRoom(room.occupation)"
              v-for="rate of room.rates"
              :key="`${hotel.id}-${room.id}-${rate.id}`"
            >
              <ContentRoom
                :room-name="room.name"
                :room-description="room.description"
                :rate-plan="rate"
                :is-checked="rate.selected"
                @checked="rate.selected = !rate.selected"
              />
            </template>
          </template>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="onClose">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="false" class="ok" @click="addServiceRooms">
          {{ t('quote.label.save') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<style lang="scss" scoped>
  .iconTab {
    align-items: center;
    display: flex;
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

  .contentRooms {
    max-height: 40vh;
    overflow: auto;

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
      padding: 20px 12px;
      align-items: center;
      gap: 20px;
      align-self: stretch;

      .item {
        color: #3d3d3d;
        font-size: 16px;
        font-style: normal;
        font-weight: 700;
        line-height: 23px; /* 143.75% */
        letter-spacing: -0.24px;

        &:last-child {
          display: flex;
          padding: 10px 16px;
          align-items: center;
          gap: 10px;

          div {
            display: flex;
            align-items: flex-start;

            &.bar {
              color: #eb5757;
            }
          }
        }

        .promo {
          background: #dfffe9;
          display: flex;
          padding: 10px 16px;
          align-items: center;
          gap: 10px;
          margin-right: 10px;

          .imgPromo {
            display: flex;
            width: 24px;
            padding: 4px;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            background: #1ed790;
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
</style>
