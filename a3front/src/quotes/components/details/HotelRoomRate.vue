<script setup lang="ts">
  import { computed, ref, toRef } from 'vue';

  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import type { Passenger, QuoteService } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import { useI18n } from 'vue-i18n';
  import { getPriceWithCommission } from '@/utils/price';
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

  const { operation } = useQuote();

  const { t } = useI18n();

  // Props
  interface Props {
    room: QuoteService;
  }

  const props = defineProps<Props>();

  // Emits
  interface Emits {
    (e: 'removeRoom', quoteService: QuoteService): void;

    (e: 'editRoom', quoteService: QuoteService): void;

    (e: 'updateRoomAccommodation', service: QuoteService, accommodation: Passenger[]): void;
  }

  const emits = defineEmits<Emits>();

  // Room
  const room = toRef(props, 'room');
  const roomName = computed(
    () => room.value.service_rooms[0].rate_plan_room.room.translations[0].value
  );
  const roomTypeName = computed(
    () => room.value.service_rooms[0].rate_plan_room.room.translations[0].value
  );
  const availableRoom = computed(() => {
    if (!room.value.service_rooms || room.value.service_rooms.length === 0) return [];

    return room.value.service_rooms.flatMap((serviceRoom) => serviceRoom.available_rooms);
  });

  const onRequest = computed(() => room.value.service_rooms[0].on_request);

  const roomsWithErrors = computed(() => {
    return room.value.validations;
  });

  // Variable computada para
  const DYNAMIC_RATE = 3; // Variable equivalente ID TARIFA DINÁMICA
  const hasRateDynamic = computed(() => {
    const currentRoom = room.value.service_rooms[0];
    console.log('currentRoom', currentRoom);
    return currentRoom.rate_plan_room.rate_plan.rates_plans_type_id === DYNAMIC_RATE;
  });

  // Room passengers accommodation
  const showAccommodation = ref<boolean>(false);
  const toggleServiceAccommodation = () => {
    showAccommodation.value = !showAccommodation.value;
  };
  const savePassengerService = () => {
    // console.log('updateRoomAccommodation',room.value, room.value.passengers_front)
    toggleServiceAccommodation();
    emits('updateRoomAccommodation', room.value, room.value.passengers_front);
  };
</script>

<template>
  <div class="room" :class="{ 'room-error': roomsWithErrors.length }">
    <div class="top">
      <div class="name">
        <div class="data">
          <div class="name-box">
            <a-popover v-if="roomsWithErrors.length" placement="rightTop">
              <template #content>
                <a-tag
                  color="red"
                  v-for="(error, eIndex) of roomsWithErrors"
                  :key="`room-error-${eIndex}`"
                >
                  {{ error.error }}
                </a-tag>
              </template>
              <div class="iconInfo">
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.information') }}</span>
                  </template>
                  <icon-alert color="#FF3B3B" width="22px" height="22px" />
                </a-tooltip>
              </div>
            </a-popover>
            <span class="name">{{ roomName }}</span>
            <div class="type">
              <span>{{ roomTypeName }}</span>
              <template v-if="hasRateDynamic">
                <div style="color: #8d0a0d; font-weight: bold; font-size: 10px">
                  <span>* {{ t('quote.has_rate_dynamic_description') }}</span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <template v-if="operation == 'passengers'">
        <a-tooltip placement="top">
          <template #title>
            <span> {{ t('quote.label.assign_room') }} </span>
          </template>
          <div class="pax" @click="toggleServiceAccommodation">
            <font-awesome-icon :style="{ fontSize: '13px' }" icon="user" />
            <span>{{ room.adult }}</span
            >ADL
            <font-awesome-icon class="icon-right" :style="{ fontSize: '16px' }" icon="child" />
            <span>{{ room.child }}</span> CHD
          </div>
        </a-tooltip>

        <div class="pax pax-distribution" v-if="showAccommodation">
          <div class="pax-check-list dropdown-select" v-if="operation == 'passengers'">
            <div class="icon-close" @click="toggleServiceAccommodation">
              <font-awesome-icon icon="xmark" />
            </div>

            <div
              v-for="(passenger, index_passenger) in room.passengers_front"
              :key="`${room.id}_${index_passenger}`"
              :class="{ checked: passenger.checked }"
              class="item"
              @click="passenger.checked = !passenger.checked"
            >
              <div class="controls">
                <div class="icon">
                  <font-awesome-icon icon="check" />
                </div>

                <span v-if="!(!!passenger.first_name && !!passenger.last_name)">
                  <span v-if="passenger.type == 'ADL'">
                    {{ t('quote.label.adult') }} {{ passenger.index }}
                  </span>
                  <span v-if="passenger.type == 'CHD'">
                    {{ t('quote.label.child') }}
                    {{
                      ` ${passenger.index} ${
                        passenger.age_child ? '(' + passenger.age_child.age + ') a' : ''
                      }`
                    }}
                  </span>
                  <span v-if="!!!passenger.type"
                    >{{ t('quote.label.adult') }} {{ passenger.index }}</span
                  >
                </span>
                <span v-else> {{ passenger.first_name }} {{ passenger.last_name }} </span>
              </div>
            </div>

            <button
              class="normal button-component quotes-detail-three"
              @click="savePassengerService"
            >
              <i class="icon icon-save"></i>
              {{ t('quote.label.save') }}
            </button>
          </div>
        </div>
      </template>
      <div class="actions-box">
        <div class="actions">
          <font-awesome-icon icon="pen-to-square" @click="emits('editRoom', room)" />
          <!-- ....... room -->
          <font-awesome-icon icon="trash-can" @click="emits('removeRoom', room)" />
        </div>
      </div>
      <!-- <div class="confirmation">
        <font-awesome-icon :style="{ color: '#1ED790' }" icon="circle-check" />
      </div> -->

      <a-popover v-if="availableRoom.length" placement="top">
        <template #content>
          <ul class="list-unstyled mb-0">
            <li v-for="(available, index) in availableRoom" :key="index">
              📅 <strong>{{ available.date }}</strong> - 🏨 Disponibles:
              {{ onRequest == 1 ? 0 : available.available }}
            </li>
          </ul>
        </template>
        <div class="iconInfo">
          <a-tooltip placement="top">
            <!-- <template #title>
              <span> {{ t('quote.label.information') }}</span>
            </template> -->
            <icon-alert color="#007BFF" width="22px" height="22px" />
          </a-tooltip>
        </div>
      </a-popover>

      <div class="confirmation" v-if="onRequest == 0">
        <font-awesome-icon :style="{ color: '#1ED790' }" icon="circle-check" />
      </div>
      <div v-else class="rq">
        <font-awesome-icon :style="{ color: '#FFD966' }" icon="circle-exclamation" />
      </div>

      <div class="cost">
        <icon-confirmed v-if="!room.on_request" />
        <template v-else>
          <icon-on-request />
          RQ
        </template>
      </div>
      <div class="cost">
        <span v-if="operation == 'passengers'"
          >US${{ displayPrice(room.import_amount?.price_ADL ?? 0) }}
        </span>
        <!--<span>ADL</span> -->
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .iconInfo {
    display: flex;
    align-items: center;
  }

  .pax-distribution {
    position: relative;

    .pax-check-list {
      position: absolute;
      left: -195px;
      top: 11px;
      z-index: 1;
    }
  }

  .room {
    display: flex;
    justify-content: center;
    align-items: center;
    align-self: stretch;
    padding: 10px 20px;
    border-radius: 8px;
    border: 0.8px solid #c4c4c4;
    background: #fff;
    flex-direction: column;

    &-error {
      background: #fff2f2 !important;

      > .top {
        background: #fff2f2 !important;
      }
    }

    .top {
      display: flex;
      /*padding: 10px 20px;
    border-radius: 8px;
    border: 0.8px solid #c4c4c4;*/
      justify-content: center;
      align-items: center;
      gap: 29px;
      align-self: stretch;
      background: #fff;
      width: 100%;

      .name {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1 0 0;
        align-self: stretch;
        flex-grow: 0;
        flex-basis: 65%;

        .data {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 8px;
          flex: 1 0 0;

          .name-box {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1 0 0;

            .name {
              color: #3d3d3d;
              font-size: 12px;
              font-style: normal;
              font-weight: 400;
              line-height: 19px;
              letter-spacing: 0.18px;
              flex: 1 0 0;
              flex-grow: 0;
              flex-basis: 40%;
              padding-right: 20px;
            }

            .type {
              color: #3d3d3d;
              font-size: 12px;
              font-style: normal;
              font-weight: 600;
              line-height: 19px;
              letter-spacing: 0.18px;
              flex: 1 0 0;
              flex-grow: 0;
              flex-basis: 60%;
              padding-right: 20px;
            }
          }

          .tag {
            display: flex;
            padding: 2px 10px;
            align-items: flex-start;
            gap: 10px;
            border-radius: 6px;
            background: #ededff;

            span {
              color: #5c5ab4;
              font-size: 12px;
              font-style: normal;
              line-height: 19px;
              letter-spacing: 0.18px;
              font-weight: 600;
            }
          }
        }
      }

      .actions-box {
        flex-grow: 0;
        flex-basis: 15%;

        .actions {
          display: flex;
          gap: 8px;

          svg {
            cursor: pointer;
          }
        }
      }

      .pax-check-list {
        padding: 15px;

        &.dropdown-select {
          display: flex;
          width: 200px;
          align-items: flex-start;
          border-radius: 6px;
          background: #ffffff;
          box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
          flex-direction: column;

          .icon-close {
            position: absolute;
            font-size: 30px;
            color: #eb5757;
            top: 5px;
            right: 5px;
            cursor: pointer;
            z-index: 1000;
          }

          .item {
            display: flex;
            padding: 12px 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            align-self: stretch;
            background: #ffffff;
            color: #212529;

            &.checked {
              color: #eb5757;

              .controls {
                .icon {
                  border: 1px solid #eb5757;
                  color: #eb5757;
                }
              }
            }

            &:hover {
              cursor: pointer;
              color: #eb5757;

              .controls {
                .icon {
                  border: 1px solid #eb5757;
                }
              }
            }

            .controls {
              display: flex;
              align-items: center;
              gap: 10px;
              align-self: stretch;

              .icon {
                border: 1px solid #c4c4c4;
                width: 24px;
                height: 24px;
                color: transparent;
                padding-left: 4px;
              }

              span {
                font-size: 16px;
                font-style: normal;
                font-weight: 500;
                line-height: 23px;
                letter-spacing: -0.24px;
              }
            }
          }
        }
      }
    }

    hr {
      height: 1px;
      stroke-width: 1px;
      stroke: #c4c4c4;
    }

    .pax {
      display: flex;
      width: 102px;
      padding-left: 0;
      justify-content: flex-end;
      align-items: center;
      gap: 4px;
      flex-grow: 0;
      flex-basis: 10%;

      span {
        color: #3d3d3d;
        font-size: 12px;
        font-style: normal;
        font-weight: 400;
        line-height: 19px;
        letter-spacing: 0.18px;
      }

      // &:hover{
      //   svg {
      //     color: #eb5757;
      //   }
      // }
    }

    .confirmation {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      flex-grow: 0;
      flex-basis: 10%;
    }

    .rq {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      flex-grow: 0;
      flex-basis: 10%;
    }

    .cost {
      display: flex;
      justify-content: flex-end;
      align-items: flex-end;
      flex-grow: 0;
      flex-basis: 15%;

      span {
        display: flex;
        align-items: center;
        color: #4f4b4b;
        text-align: right;
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: 23px;
        letter-spacing: -0.24px;
      }
    }
  }

  .openSideBar {
    .room {
      .top {
        gap: 15px;

        .name {
          flex-basis: 60%;
        }
      }
    }
  }
</style>
