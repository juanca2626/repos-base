<script lang="ts" setup>
  import { SearchOutlined } from '@ant-design/icons-vue';
  import { computed, ref, toRef } from 'vue';
  import dayjs from 'dayjs';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { GroupedServices, Hotel } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import HotelsWithBestPrices from '@/quotes/components/modals/HotelsWithBestPrices.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  interface Props {
    hotel: Hotel;
  }

  const props = defineProps<Props>();
  const hotel = toRef(props, 'hotel');
  const showBestPriceHotels = ref<boolean>(false);

  interface Emits {
    (e: 'editHotel'): void;
  }

  const emits = defineEmits<Emits>();

  const { getLang } = useQuoteTranslations();
  const { serviceSelected: groupedService, deleteServiceSelected } = useQuote();
  const { getHotelsNoPromotions, hotels } = useQuoteHotels();

  const service = computed(() => (groupedService.value as GroupedServices).service);

  const storeSidebar = useSiderBarStore();

  const searchHotelsToReplace = async () => {
    // console.log(hotelPrice.value);
    // return false;
    emits('editHotel');

    await getHotelsNoPromotions({
      // We look for the available hotels to be able to add the room
      date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      date_to: dayjs(service.value.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
      destiny: {
        code: service.value.hotel!.country.iso + ',' + service.value.hotel!.state.iso,
        label:
          service.value.hotel!.country.translations[0].value +
          ',' +
          service.value.hotel!.state.translations[0].value,
      },
      hotels_id: [],
      lang: getLang(),
      quantity_persons_rooms: [],
      quantity_rooms: 1,
      set_markup: 0,
      typeclass_id: service.value.hotel!.typeclass_id,
      zero_rates: true,
    });

    storeSidebar.setStatus(false, 'hotel', 'search');
    showBestPriceHotels.value = true;
  };

  const hotelPrice = computed(() => {
    let price = 0;
    hotel.value.amount.forEach((amount) => {
      price = price + amount.price_per_night;
    });
    return price;
  });

  const hotelCategoryStars = computed(() => hotel.value.hotel.stars);

  // Best price hotels
  const bestPriceHotels = computed<Hotel[]>(() => {
    return hotels.value.filter(
      (h) =>
        h.id !== hotel.value.id &&
        h.price <= hotelPrice.value &&
        h.category <= hotelCategoryStars.value
    );
  });

  const closeBestPriceHotelsModal = async () => {
    showBestPriceHotels.value = false;

    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();
  };
</script>

<template>
  <div @click="searchHotelsToReplace">
    <search-outlined />
    {{ t('quote.label.promotion_hotels') }}
  </div>

  <hotels-with-best-prices
    v-if="showBestPriceHotels && bestPriceHotels.length > 0"
    :hotels="bestPriceHotels"
    @close="closeBestPriceHotelsModal"
  />
</template>

<style lang="scss" scoped></style>
