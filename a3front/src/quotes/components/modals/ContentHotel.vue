<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import { getUserType } from '@/utils/auth';
  import type { Hotel } from '@/quotes/interfaces';
  import { getHours } from '@/quotes/helpers/get-hours';
  import IconMapDecal from '@/quotes/components/icons/IconMapDecal.vue';
  import IconFile from '@/quotes/components/icons/IconFile.vue';
  import IconClock from '@/quotes/components/icons/IconClock.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  const { page, selectedHotelDetails } = useQuote();

  interface Props {
    hotel: Hotel;
    flagRemarks: {
      type: Boolean;
      default: false;
    };
  }

  const props = withDefaults(defineProps<Props>(), {
    type: 'transfers',
    title: '',
    hotel: undefined,
  });

  const hotel = toRef(props, 'hotel');
  const flagRemarks = toRef(props, 'flagRemarks');

  const hotelName = computed(() => {
    return hotel.value?.name ?? '';
  });
  const hotelId = computed(() => {
    return hotel.value?.id ?? '';
  });
  const stars = computed(() => {
    return hotel.value?.stars ? hotel.value?.stars : 1;
  });
  const typeClass = computed(() => {
    return hotel.value?.class ?? '';
  });
  const typeClassColor = computed(() => {
    return hotel.value?.color_class ?? '';
  });
  const description = computed(() => {
    return hotel.value?.description ?? '';
  });

  const notes = computed(() => hotel.value?.alerts?.[0]?.notes || '');
  const remarks = computed(() => hotel.value?.alerts?.[0]?.remarks || '');

  const address = computed(() => {
    return hotel.value?.address ?? '';
  });
  const checkIn = computed(() => {
    return hotel.value?.checkIn ?? '';
  });
  const checkOut = computed(() => {
    return hotel.value?.checkOut ?? '';
  });
  const amenities = computed(() => {
    return hotel.value?.amenities ?? [];
  });

  const changePage = async (newView: string, hotel_id: number) => {
    page.value = newView;
    selectedHotelDetails.value = hotel_id;
  };
  console.log(changePage);
</script>

<template>
  <div class="container">
    <div class="titlePopup">
      <h4>{{ hotelName }}</h4>

      <div class="clases">
        <div class="categoria" :style="{ 'background-color': typeClassColor }">
          {{ typeClass }}
        </div>

        <div class="estrellas">
          <div class="item" v-for="i in parseInt(stars)" :key="i">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="21"
              height="22"
              viewBox="0 0 21 22"
              fill="none"
            >
              <path
                d="M10.5 2.25L13.2038 7.7275L19.25 8.61125L14.875 12.8725L15.9075 18.8925L10.5 16.0488L5.0925 18.8925L6.125 12.8725L1.75 8.61125L7.79625 7.7275L10.5 2.25Z"
                stroke="#C4C4C4"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="container-flex">
      <div class="item-flex-left">
        <p>{{ description }}</p>

        <div class="politics-modal" v-if="flagRemarks && notes && getUserType() == '4'">
          <div class="title-politics">{{ t('quote.label.notes') }}:</div>
          <ul>
            <li v-html="notes"></li>
          </ul>
        </div>

        <div class="politics-modal" v-if="flagRemarks && remarks && getUserType() == '3'">
          <div class="title-politics">Remarks:</div>
          <div v-html="remarks"></div>
        </div>

        <RouterLink :to="{ name: 'quotes-hotel-details', params: { id: hotelId } }" target="_blank">
          <!-- <a href="javascript:void(0)" @click="changePage('hotel-details', hotelId)">
            <icon-file/>
            <span>{{ t("quote.label.view_technical") }}</span>
          </a> -->
          <icon-file />
          <span>{{ t('quote.label.view_technical') }}</span>
        </RouterLink>
      </div>

      <div class="item-flex-right">
        <div class="item">
          <icon-map-decal />
          {{ address }}
        </div>
        <div class="item">
          <icon-clock />
          <div>
            {{ t('quote.label.in') }}: <span>{{ getHours(checkIn) }}</span>
          </div>
          <div>
            {{ t('quote.label.out') }}: <span>{{ getHours(checkOut) }}</span>
          </div>
        </div>

        <div class="item title">
          <p>{{ t('quote.label.include') }}</p>

          <div class="icons">
            <template v-for="(amenity, index) of amenities">
              <a-tooltip placement="top">
                <template #title>
                  <span> {{ amenity.name }}</span>
                </template>

                <img v-if="amenity.image != ''" :key="index" :src="amenity.image" />
              </a-tooltip>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .container {
    display: flex;
    flex-direction: column;
    padding: 0 20px 30px;
    gap: 35px;

    .type-botton {
      position: absolute;
      right: 120px;
      top: 0;
      display: flex;
      justify-content: flex-end;
      align-items: flex-end;
      gap: 40px;

      span {
        border-radius: 0px 0px 6px 6px;
        padding: 13px 18px;
        color: #fff;
      }
    }

    p {
      margin: 0;
    }
  }

  .titlePopup {
    display: flex;
    flex-direction: column;
    padding: 31px 0 0 0;
  }

  h4 {
    font-size: 36px;
    font-style: normal;
    font-weight: 400;
    line-height: 43px; /* 119.444% */
    letter-spacing: -0.36px;
    color: #212529;
    margin: 0;
  }

  .clases {
    display: flex;
    align-items: center;
    padding: 15px 0 0 0;
    gap: 10px;

    .categoria {
      display: flex;
      height: 27px;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: #4ba3b2;
      border-radius: 6px;
      color: #fff;
      padding: 10px;
      font-size: 12px;
      width: 160px;
    }

    .estrellas {
      display: flex;
      gap: 10px;

      .item {
        display: flex;
        width: 21px;
        height: 21px;
        justify-content: center;
        align-items: center;
      }
    }
  }

  .container-flex {
    p {
      font-size: 18px;
      font-style: normal;
      font-weight: 400;
      line-height: 25px;
    }

    a {
      color: #eb5757;
      font-size: 16px;
      font-style: normal;
      font-weight: 500;
      line-height: 23px; /* 143.75% */
      letter-spacing: -0.24px;

      svg {
        vertical-align: middle;
        margin-right: 5px;
      }

      span {
        position: relative;

        &:before {
          content: '';
          position: absolute;
          left: 0;
          right: 0;
          bottom: -3px;
          height: 1.5px;
          background: #eb5757;
          border-radius: 2px;
        }
      }
    }

    .item-flex-right {
      div {
        display: flex;
        align-items: center;
        gap: 10px;
        align-self: stretch;
        font-weight: 400;
        font-size: 18px;
        color: #212529;

        &.title {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 6px;
          align-self: stretch;
          padding: 0;
        }

        p {
          font-weight: 700;
          margin-bottom: 0;
        }

        span {
          color: #eb5757;
        }
      }

      .icons {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 6px;

        img {
          display: flex;
        }
      }

      ul {
        list-style: none;
        margin: 0;
        padding: 0;
        font-weight: 400;
      }
    }
  }
</style>
