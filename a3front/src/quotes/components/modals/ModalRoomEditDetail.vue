<script lang="ts" setup>
  import { computed, ref, watchEffect } from 'vue';
  import dayjs from 'dayjs';

  import { useQuote } from '@/quotes/composables/useQuote';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import type { Hotel, HotelService, HotelTypeClass, Room, RoomRate } from '@/quotes/interfaces';
  import ContentRoom from '@/quotes/components/modals/ContentRoom.vue';
  import IconCloseGray from '@/quotes/components/global/IconCloseGray.vue';
  import { getLang } from '@/quotes/helpers/get-lang';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { serviceSelected: service, unsetServiceEdit, replaceServiceHotelRoom } = useQuote();
  const { getHotels, hotels } = useQuoteHotels();

  const show = computed(() => service.value.type === 'hotel');
  const hotelSelected = computed<HotelService>(() => service.value.hotel!);

  watchEffect(() => {
    if (show.value) {
      getHotels({
        // We look for the available hotels to be able to add the room
        date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
        date_to: dayjs(service.value.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
        destiny: {
          code: hotelSelected.value.country.iso + ',' + hotelSelected.value.state.iso,
          label:
            hotelSelected.value.country.translations[0].value +
            ',' +
            hotelSelected.value.state.translations[0].value,
        },
        hotels_id: [service.value.object_id],
        lang: getLang(),
        quantity_persons_rooms: [],
        quantity_rooms: 1,
        set_markup: 0,
        typeclass_id: hotelSelected.value.typeclass_id,
        zero_rates: true,
      }).then(() => {
        const data = hotels.value[0];

        // Rearrange
        if (service.value.service_rooms.length > 0 && data != undefined) {
          let selectedRooms: Room[] = [];
          let noSelectedRooms: Room[] = [];

          // First put in a new array only the ones that we do not have selected
          data.rooms.forEach((room) => {
            let _verified = 0;
            for (let r_p_r = 0; r_p_r < room.rates.length; r_p_r++) {
              service.value.service_rooms.forEach((s_r) => {
                if (s_r.rate_plan_room_id == room.rates[r_p_r].ratePlanId) {
                  _verified++;
                }
              });
            }

            if (_verified > 0) {
              selectedRooms.push(room);
            } else {
              noSelectedRooms.push(room);
            }
          });

          data.rooms = selectedRooms.concat(noSelectedRooms);
        }

        // for show rates
        if (data != undefined) {
          for (let r = 0; r < data.rooms.length; r++) {
            data.rooms[r].countCalendars = 0;
            for (let r_p_r = 0; r_p_r < data.rooms[r].rates.length; r_p_r++) {
              data.rooms[r].rates[r_p_r].showAllRates = 1;
              for (
                let r_p_r_c = 0;
                r_p_r_c < data.rooms[r].rates[r_p_r].rate[0].amount_days.length;
                r_p_r_c++
              ) {
                data.rooms[r].countCalendars++;
              }
            }
          }
        }

        hotel.value = data;
      });
    }
  });

  const hotel = ref<Hotel>();
  const rooms = computed(() => hotel.value?.rooms ?? []);

  const serviceName = computed(() => hotelSelected.value?.name ?? '');
  const serviceClass = computed<HotelTypeClass>(() => hotelSelected.value?.typeclass);
  const serviceClassName = computed(() => serviceClass.value?.translations[0].value ?? '');

  const selectedRoom = ref<Room>();
  const selectedRate = ref<RoomRate>();

  const toggleRateSelected = (isChecked: boolean, room?: Room, rate?: RoomRate) => {
    if (isChecked) {
      selectedRoom.value = room;
      selectedRate.value = rate;
    } else {
      selectedRoom.value = undefined;
      selectedRate.value = undefined;
    }
  };

  // replace service
  const replaceServiceSelected = () => {
    if (selectedRate.value && selectedRoom.value) {
      replaceServiceHotelRoom(
        selectedRoom.value.occupation,
        selectedRate.value?.rateId,
        selectedRate.value?.onRequest
      );
      toggleRateSelected(false);
      unsetServiceEdit();
    }
  };
</script>

<template>
  <ModalComponent
    v-if="show"
    :modal-active="true"
    class="modal-itinerario-detail"
    @close="unsetServiceEdit"
  >
    <template #body>
      <div class="icon-close-modal" @click="unsetServiceEdit">
        <icon-close />
      </div>

      <div class="container">
        <div class="titlePopup">
          <h4>{{ t('quote.label.hotel_rates') }}: {{ serviceName }}</h4>
          <div class="clases" v-if="serviceClassName">
            <div class="categoria">{{ serviceClassName }}</div>
          </div>
        </div>

        <div class="infoSelect asds" v-if="selectedRate && selectedRoom">
          <div class="item">{{ t('quote.label.you_have_selected') }}</div>
          <div class="item">
            <div>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
              >
                <path
                  d="M21.4444 5.33355C21.7514 5.33355 22 5.08494 22 4.77799V3.66688C22 3.35994 21.7514 3.11133 21.4444 3.11133H2.55556C2.24861 3.11133 2 3.35994 2 3.66688V4.77799C2 5.08494 2.24861 5.33355 2.55556 5.33355H3.11042V18.6669H2.55556C2.24861 18.6669 2 18.9155 2 19.2224V20.3336C2 20.6405 2.24861 20.8891 2.55556 20.8891H10.8889V18.1113C10.8889 17.8058 11.1389 17.5558 11.4444 17.5558H12.5556C12.8611 17.5558 13.1111 17.8058 13.1111 18.1113V20.8891H21.4444C21.7514 20.8891 22 20.6405 22 20.3336V19.2224C22 18.9155 21.7514 18.6669 21.4444 18.6669H20.8889V5.33355H21.4444ZM10.8889 6.88911C10.8889 6.66688 11.1111 6.44466 11.3333 6.44466H12.6667C12.8889 6.44466 13.1111 6.66688 13.1111 6.88911V8.22244C13.1111 8.44466 12.8889 8.66688 12.6667 8.66688H11.3333C11.1111 8.66688 10.8889 8.44466 10.8889 8.22244V6.88911ZM10.8889 10.2224C10.8889 10.0002 11.1111 9.778 11.3333 9.778H12.6667C12.8889 9.778 13.1111 10.0002 13.1111 10.2224V11.5558C13.1111 11.778 12.8889 12.0002 12.6667 12.0002H11.3333C11.1111 12.0002 10.8889 11.778 10.8889 11.5558V10.2224ZM6.44444 6.88911C6.44444 6.66688 6.66667 6.44466 6.88889 6.44466H8.22222C8.44444 6.44466 8.66667 6.66688 8.66667 6.88911V8.22244C8.66667 8.44466 8.44444 8.66688 8.22222 8.66688H6.88889C6.66667 8.66688 6.44444 8.44466 6.44444 8.22244V6.88911ZM8.22222 12.0002H6.88889C6.66667 12.0002 6.44444 11.778 6.44444 11.5558V10.2224C6.44444 10.0002 6.66667 9.778 6.88889 9.778H8.22222C8.44444 9.778 8.66667 10.0002 8.66667 10.2224V11.5558C8.66667 11.778 8.44444 12.0002 8.22222 12.0002ZM8.66667 16.4447C8.66667 14.6037 10.159 13.1113 12 13.1113C13.841 13.1113 15.3333 14.6037 15.3333 16.4447H8.66667ZM17.5556 11.5558C17.5556 11.778 17.3333 12.0002 17.1111 12.0002H15.7778C15.5556 12.0002 15.3333 11.778 15.3333 11.5558V10.2224C15.3333 10.0002 15.5556 9.778 15.7778 9.778H17.1111C17.3333 9.778 17.5556 10.0002 17.5556 10.2224V11.5558ZM17.5556 8.22244C17.5556 8.44466 17.3333 8.66688 17.1111 8.66688H15.7778C15.5556 8.66688 15.3333 8.44466 15.3333 8.22244V6.88911C15.3333 6.66688 15.5556 6.44466 15.7778 6.44466H17.1111C17.3333 6.44466 17.5556 6.66688 17.5556 6.88911V8.22244Z"
                  fill="#3D3D3D"
                />
              </svg>
            </div>

            <div>{{ selectedRoom.occupation }}</div>
            <div class="bar">|</div>
            <div>{{ selectedRoom.room_type }}</div>
            <div class="bar">|</div>
            <div>{{ hotelSelected.name }}</div>
            <div>
              <icon-close-gray @click="toggleRateSelected(false)" />
            </div>
          </div>
        </div>

        <div class="contentRooms">
          <template v-for="room of rooms" :key="`${hotel.id}-${room.id}`">
            <template v-for="rate of room.rates" :key="`${hotel.id}-${room.id}-${rate.id}`">
              <ContentRoom
                :room-name="room.name"
                :room-description="room.description"
                :rate-plan="rate"
                :is-checked="selectedRate && selectedRate.rateId === rate.rateId"
                @checked="(isChecked: boolean) => toggleRateSelected(isChecked, room, rate)"
              />
            </template>
          </template>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="unsetServiceEdit">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="false" class="ok" @click="replaceServiceSelected">
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
      padding: 0 12px 0;
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
