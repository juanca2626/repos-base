<script lang="ts" setup>
  import { computed, onMounted, ref, toRef } from 'vue';
  import { SearchOutlined } from '@ant-design/icons-vue';
  import type { Hotel } from '@/quotes/interfaces';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import IconMagnifyingGlass from '@/quotes/components/icons/IconMagnifyingGlass.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import { getHotelById } from '@/quotes/helpers/get-hotel-by-id';
  import useLoader from '@/quotes/composables/useLoader';
  import QuoteHotelRoomsAndPromotions from '@/quotes/components/modals/ModalRoomAndPromotions.vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import HotelsWithBestPrices from '@/quotes/components/modals/HotelsWithBestPrices.vue';
  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore } from '@/stores/global';
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
  const languageStore = useLanguagesStore();
  // const { quote } = useQuote();

  const { hotels } = useQuoteHotels();
  const { showIsLoading, closeIsLoading } = useLoader();

  interface Props {
    hotel: Hotel;
  }

  const props = defineProps<Props>();
  const hotel = toRef(props, 'hotel');
  const hotelSelected = ref<Hotel>();

  const hotelImage = ref<string>('../../../images/quotes/1.png');

  onMounted(() => {
    if (hotel.value.galleries.length) {
      hotelImage.value = hotel.value.galleries[0];
    }
  });

  // Best price hotels
  const bestPriceHotels = computed<Hotel[]>(() => {
    return hotels.value.filter(
      (h) =>
        h.id !== hotel.value.id &&
        h.price <= hotel.value.price &&
        h.category <= hotel.value.category
    );
  });
  const showBestPriceHotels = ref<boolean>(false);

  const openBestPriceHotelsModal = async () => {
    showBestPriceHotels.value = true;
  };
  const closeBestPriceHotelsModal = async () => {
    showBestPriceHotels.value = false;
  };

  // Hotel detail modal
  const showHotelDetailModal = ref<boolean>(false);

  const openHotelDetailModal = async () => {
    showIsLoading();
    hotelSelected.value = await getHotelById(hotel.value.id, languageStore.currentLanguage);
    showHotelDetailModal.value = true;
    closeIsLoading();
  };

  const hotelDetailModal = () => {
    showHotelDetailModal.value = false;
  };

  // Hotel selected rooms and promotions
  const showHotelRoomsAndPromotions = ref<boolean>(false);

  const openRoomsAndPromotions = async () => {
    showHotelRoomsAndPromotions.value = true;
  };
  const closeRoomsAndPromotions = () => {
    showHotelRoomsAndPromotions.value = false;
  };
</script>

<template>
  <div class="item">
    <h3>{{ hotel.name }}</h3>
    <!-- popular: {{  hotel.popularity }}, favoriy: {{  hotel.favorite }} -->

    <div class="img" :style="'background-image: url(' + hotelImage + ' )'">
      <!--<img :src="hotelImage" :alt="hotel.name"/>-->
    </div>

    <div class="place">
      <div>
        <icon-magnifying-glass />

        {{ hotel.state }}
      </div>

      <div>
        <a-tag
          v-if="bestPriceHotels.length"
          color="#1ED790"
          class="btn-best-price"
          role="button"
          @click="openBestPriceHotelsModal"
        >
          <template #icon>
            <search-outlined />
          </template>
          {{ t('quote.label.promotions') }}
        </a-tag>

        <a-tooltip placement="top">
          <template #title>
            <span> {{ t('quote.label.information') }}</span>
          </template>
          <icon-alert :height="20" :width="20" @click="openHotelDetailModal" />
        </a-tooltip>
      </div>
    </div>

    <div class="description"></div>

    <div class="row-flex">
      <div class="quotes-actions-btn" @click="openRoomsAndPromotions">
        <div class="content">
          <div class="text">{{ t('quote.label.escolha_o_quarto') }}</div>
        </div>
      </div>

      <div class="price">
        ${{ displayPrice(hotel.price) }} <span>{{ t('quote.label.per_room') }}</span>
        <span
          v-if="showCommissionBadge"
          class="badge-warning ml-2"
          style="font-size: 10px; padding: 1px 2px"
          >{{ t('global.label.with_commission') }}</span
        >
      </div>
    </div>
  </div>

  <modal-itinerario-detail
    v-if="showHotelDetailModal"
    :hotel="hotelSelected"
    :title="hotel.name"
    :type="'group_header'"
    :show="showHotelDetailModal"
    @close="hotelDetailModal"
  />

  <hotels-with-best-prices
    v-if="showBestPriceHotels"
    :hotels="bestPriceHotels"
    @close="closeBestPriceHotelsModal"
  />

  <quote-hotel-rooms-and-promotions
    v-if="showHotelRoomsAndPromotions"
    :hotel="hotel"
    @close="closeRoomsAndPromotions"
  />
</template>

<style lang="scss" scoped>
  .item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    flex-shrink: 0;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #909090;

    /*&:nth-last-child(2) {
    border-bottom: 0;
    margin-bottom: 0;
    padding-bottom: 0;
  }*/

    h3 {
      color: #4f4b4b;
      font-size: 18px !important;
      font-style: normal;
      font-weight: 400;
      line-height: normal;
      letter-spacing: -0.27px;
      margin-bottom: 0;
    }

    .img {
      /*position: relative;*/
      width: 100%;
      height: 125px;
      overflow: hidden;

      img {
        width: 100%;
      }

      .tag {
        /*position: absolute;
      left: 5px;
      top: 5px;*/
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.25);

        span {
          display: flex;
          height: 19px;
          padding: 0 5px;
          flex-direction: column;
          align-items: center;
          border-radius: 6px;
          background: #eb5757;
          color: #ffffff;
          text-align: center;
          font-size: 12px;
          font-style: normal;
          font-weight: 700;
          line-height: 19px;
          letter-spacing: 0.18px;
        }
      }
    }

    .place {
      display: flex;
      align-items: center;
      gap: 4px;
      align-self: stretch;
      justify-content: space-between;
      font-size: 14px;

      div {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #909090;

        .btn-best-price {
          cursor: pointer;
          font-size: 12px;
        }
      }

      div:last-child {
        .ant-tag {
          margin-right: 4px;
        }

        svg {
          cursor: pointer;
        }
      }
    }

    .description {
      align-self: stretch;
      display: flex;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      color: #2e2e2e;
      font-size: 14px;
      font-style: normal;
      font-weight: 400;
      line-height: 22px;
      letter-spacing: -0.21px;

      p {
        margin: 0;
      }
    }
  }

  .modal-footer {
    background: none !important;
  }
</style>
