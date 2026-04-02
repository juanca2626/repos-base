<script setup lang="ts">
  import { computed, ref, toRef } from 'vue';

  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import type { ServiceRoom, QuoteService } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  const { operation } = useQuote();

  const { t } = useI18n();

  // Props
  interface Props {
    roomType: ServiceRoom;
    rooms: QuoteService[];
  }

  const props = defineProps<Props>();

  // Emits
  interface Emits {
    (e: 'removeRoomType', serviceRoom: ServiceRoom): void;
  }

  const emits = defineEmits<Emits>();

  // Room type
  const roomType = toRef(props, 'roomType');
  const roomName = computed(() => roomType.value.rate_plan_room.room.translations[0].value);
  const roomTypeName = computed(() => roomType.value.rate_plan_room.room.translations[0].value);

  const onRequestRoomType = computed(() => {
    return rooms.value.some(({ on_request }) => on_request === 1);
  });
  const totalPriceRoomType = computed(() => {
    let total = 0.0;

    rooms.value.forEach(({ import_amount }) => {
      total += Number(import_amount?.price_ADL ?? 0.0);
    });

    return total;
  });

  // Rooms
  const rooms = toRef(props, 'rooms');
  const totalRooms = computed(() => {
    return rooms.value.length;
  });

  const roomsWithErrors = computed(() => {
    return rooms.value.filter((r) => r.validations.length);
  });

  // Show hide rooms
  const showRooms = ref<boolean>(false);
  const toggleRoomList = () => {
    showRooms.value = !showRooms.value;
  };

  // Passengers Count
  const totalADL = computed(() => {
    let adultCount = 0;

    for (const { adult } of rooms.value) {
      adultCount += adult;
    }

    return adultCount;
  });
  const totalCHL = computed(() => {
    let childCount = 0;

    for (const { child } of rooms.value) {
      childCount += child;
    }

    return childCount;
  });
</script>

<template>
  <div class="room" :class="{ 'room-error': roomsWithErrors.length }" v-if="rooms.length > 1">
    <div class="top">
      <div class="name hotelChange">
        <div class="data">
          <div class="name-box">
            <a-popover v-if="roomsWithErrors.length" placement="rightTop">
              <template #content>
                <div
                  v-for="(roomErrors, index) of roomsWithErrors"
                  :key="`room-errors-${roomErrors.id}`"
                >
                  <p>{{ t('quote.label.room') }} {{ index + 1 }}</p>

                  <a-tag
                    color="red"
                    v-for="(error, eIndex) of roomErrors.validations"
                    :key="`room-error-${roomErrors.id}-${eIndex}`"
                  >
                    {{ error.error }}
                  </a-tag>
                </div>
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
            <span class="type">{{ roomTypeName }}</span>
          </div>
          <div class="tag">
            <span>{{ t('quote.label.rooms_num') }}: {{ totalRooms }}</span>
          </div>
        </div>
        <div class="actions-box">
          <div class="actions">
            <div @click="toggleRoomList">
              <font-awesome-icon icon="chevron-down" />
            </div>
            <font-awesome-icon icon="trash-can" @click="emits('removeRoomType', roomType)" />
          </div>
        </div>
      </div>
      <template v-if="operation == 'passengers'">
        <div class="pax">
          <font-awesome-icon :style="{ fontSize: '13px' }" icon="user" />
          <span>{{ totalADL }}</span> ADL
          <font-awesome-icon class="icon-right" :style="{ fontSize: '16px' }" icon="child" />
          <span>{{ totalCHL }}</span> CHD
        </div>
      </template>

      <!--<div class="confirmation">
        <font-awesome-icon
            :style="{color: '#1ED790'}"
            icon="circle-check"
        />
      </div>-->
      <div class="cost">
        <icon-confirmed v-if="!onRequestRoomType" />
        <template v-else> <icon-on-request />RQ </template>
      </div>
      <div class="cost" v-if="operation == 'passengers'">
        <span>US${{ totalPriceRoomType }}</span>
        <!--<span>ADL</span> -->
      </div>
    </div>
    <div class="room-detail" v-if="showRooms">
      <hr />
      <div class="detail-header">
        <div class="title">
          <font-awesome-icon icon="code-compare" />
          <span class="txt-desglo">{{ t('quote.label.room_breakdown') }}</span>
        </div>
      </div>
      <div class="detail-body">
        <div class="rooms">
          <slot name="rooms" :rooms="rooms"></slot>
        </div>
      </div>
    </div>
  </div>

  <slot v-else name="rooms" :rooms="rooms"></slot>
</template>

<style scoped lang="scss">
  .iconInfo {
    display: flex;
    align-items: center;
  }
  .txt-desglo {
    font-size: 12px;
    text-decoration: underline;
    margin-left: 5px;
  }

  :deep(.room) {
    margin-bottom: 10px;

    &:last-child() {
      margin-botom: 0;
    }
  }

  :deep(.room:last-child) {
    margin-bottom: 0px;
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

    .title {
      padding-bottom: 10px;
    }

    &-error {
      background: #fff2f2 !important;

      > .top {
        background: #fff2f2 !important;
      }
    }

    .top {
      display: flex;
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
        flex-basis: 75%;

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

        .actions-box {
          display: flex;
          width: 60px;
          justify-content: left;
          align-items: center;
          gap: 33px;
          padding-left: 15px;

          .actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;

            div {
              cursor: pointer;

              svg {
                color: #eb5757;
              }
            }
          }
        }
      }

      .pax-check-list {
        padding: 15px;

        &.dropdown-select {
          position: relative;
          display: flex;
          width: 260px;
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

    .room-detail {
      padding: 0 0 25px 0;
      width: 100%;

      .detail-header {
        padding-top: 10px;
        padding-bottom: 5px;
        height: auto;
      }

      .detail-body {
        padding: 0;
      }
    }

    hr {
      height: 1px;
      stroke-width: 1px;
      stroke: #c4c4c4;
    }

    .pax {
      display: flex;
      /*width: 102px;*/
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
    }

    .confirmation {
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

  .name {
    &.hotelChange {
      flex-basis: 72% !important;
      .actions-box {
        padding-left: 0 !important;
        gap: 0 !important;
        width: 40px !important;
      }
    }
  }

  .openSideBar {
    .room .top {
      gap: 15px !important;
    }
    .name {
      &.hotelChange {
        flex-basis: 64% !important;

        .data {
          .name-box {
            .type {
              flex-basis: 50%;
              padding-right: 0;
            }
            .name {
              padding-right: 15px;
            }
          }
        }
      }
    }
  }
</style>
