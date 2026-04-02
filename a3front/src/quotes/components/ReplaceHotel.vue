<script lang="ts" setup>
  import { computed } from 'vue';
  import dayjs from 'dayjs';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { GroupedServices } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import IconReplaceList from '@/quotes/components/icons/IconReplaceList.vue';

  interface Emits {
    (e: 'editHotel'): void;
  }

  const emits = defineEmits<Emits>();

  const { getLang } = useQuoteTranslations();
  const { serviceSelected: groupedService } = useQuote();
  const { getHotels } = useQuoteHotels();

  const service = computed(() => (groupedService.value as GroupedServices).service);

  const storeSidebar = useSiderBarStore();

  const searchHotelsToReplace = async () => {
    emits('editHotel');

    await getHotels({
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

    storeSidebar.setStatus(true, 'hotel', 'search');
  };
</script>

<template>
  <icon-replace-list @click="searchHotelsToReplace" />
</template>

<style lang="scss" scoped></style>
