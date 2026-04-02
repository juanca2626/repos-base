<script lang="ts" setup>
  import { computed, toRef } from 'vue';

  import type { ServiceExtensionsResponse } from '@/quotes/interfaces/services';
  import IconFile from '@/quotes/components/icons/IconFile.vue';
  import { useI18n } from 'vue-i18n';
  import { getUserClientId } from '@/utils/auth';

  const { t } = useI18n();

  // const { page, selectedHotelDetails, quote } = useQuote();

  interface Props {
    extension?: ServiceExtensionsResponse;
  }

  const props = defineProps<Props>();

  const extension = toRef(props, 'extension');

  const name = computed(() => {
    return extension.value?.translations && extension.value.translations.length > 0
      ? extension.value.translations[0].tradename
      : '';
  });

  const nights = computed(() => {
    return extension.value
      ? extension.value.nights + 1 + 'D / ' + extension.value.nights + 'N'
      : '';
  });

  const description = computed(() => {
    return extension.value?.translations && extension.value.translations.length > 0
      ? extension.value.translations[0].description_commercial
      : '';
  });

  const itinerary_link = computed(() => {
    if (!extension.value) return '';

    const baseURL = import.meta.env.VITE_APP_BACKEND_URL || '';
    const clientId = getUserClientId() || '';
    const lang = localStorage.getItem('lang') || 'en';
    const packageId = extension.value.id || '';
    const year = new Date().getFullYear();
    const portada = ''; // Vacío por defecto
    const days = extension.value.nights + 1 || 1; // Calculado desde nights

    // Extraer category del primer plan_rate_categories si existe
    let category = '';
    if (extension.value.plan_rates && extension.value.plan_rates.length > 0) {
      const planRate = extension.value.plan_rates[0];
      if (planRate.plan_rate_categories && planRate.plan_rate_categories.length > 0) {
        category = String(planRate.plan_rate_categories[0].type_class_id || '');
      }
    }

    // Extraer type_service del primer plan_rate si existe
    let typeService = '';
    if (extension.value.plan_rates && extension.value.plan_rates.length > 0) {
      typeService = String(extension.value.plan_rates[0].service_type_id || '');
    }

    const usePrices = 1; // 0 por defecto
    const userTypeId = 4; // Fijo por defecto

    return `${baseURL}api/public_link/itinerary?client_id=${clientId}&lang=${lang}&package_id=${packageId}&year=${year}&portada=${portada}&days=${days}&category=${category}&type_service=${typeService}&use_prices=${usePrices}&user_type_id=${userTypeId}`;
  });

  const itinerary = computed(() => {
    return extension.value?.translations && extension.value.translations.length > 0
      ? extension.value.translations[0].itinerary_commercial
      : '';
  });

  // const changePage = async (newView: string, hotel_id: number) => {
  //   page.value = newView;
  //   selectedHotelDetails.value = hotel_id;
  // };
</script>

<template>
  <div class="container">
    <div class="titlePopup">
      <h4>
        {{ name }}
        <span>{{ nights }}</span>
      </h4>
    </div>

    <div class="container-flex">
      <div class="item-flex-left">
        <p>{{ description }}</p>

        <!-- <RouterLink :to="{ name: 'quotes-hotel-details', params: {id: hotelId} }" target='_blank'> -->
        <a :href="itinerary_link" target="_blank" v-if="itinerary_link">
          <icon-file />
          <span>{{ t('quote.label.itinerary') }}</span>
        </a>
        <!-- </RouterLink> -->
      </div>

      <div class="item-flex-right">
        <!-- <div class="item">
          <icon-map-decal/>
          {{ address }}
        </div>
        <div class="item">
          <icon-clock/>
          <div>{{ t("local.in") }}: <span>{{ getHours(checkIn) }}</span></div>
          <div>{{ t("local.out") }}: <span>{{ getHours(checkOut) }}</span></div>
        </div> -->

        <div class="item title">
          <p>{{ t('quote.label.itinerary') }}</p>

          <div class="icons text-itineario">
            {{ itinerary }}
            <!-- <template v-for="(amenity,index) of amenities" >
              <img
                  v-if="amenity.image != '' "
                  :key="index"
                  :src="amenity.image"
                  :alt="amenity.name"
              />
            </template> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .text-itineario {
    font-size: 14px !important;
    text-align: left;
    white-space: pre-line;
  }

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
          font-size: 24px;

          p {
            font-size: 24px;
          }
        }

        p {
          font-weight: 700;
          margin-bottom: 0;
          color: #212529;
          margin-bottom: 5px !important;
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
