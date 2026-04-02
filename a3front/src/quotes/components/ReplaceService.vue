<script lang="ts" setup>
  import { computed } from 'vue';
  import dayjs from 'dayjs';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { GroupedServices } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import IconReplaceList from '@/quotes/components/icons/IconReplaceList.vue';

  interface Emits {
    (e: 'editHotel'): void;
  }

  const emits = defineEmits<Emits>();

  const { getLang } = useQuoteTranslations();
  const { serviceSelected: groupedService, quote } = useQuote();
  const {
    servicesDestinations,
    getToursAvailable,
    getMealsAvailable,
    getTransferAvailable,
    getMiselaniosAvailable,
    getServiceByCategory,
  } = useQuoteServices();

  const service = computed(() => (groupedService.value as GroupedServices).service);

  const storeSidebar = useSiderBarStore();

  const searchServicesToReplace = async () => {
    emits('editHotel');

    // Destin
    const destinyCountry = servicesDestinations.value.destinationsCountries.find((d) => {
      return d.code == service.value.service!.service_destination[0].country_id.toString();
    });
    const destinyState = servicesDestinations.value.destinationsStates.find((d) => {
      return (
        d.country_code == service.value.service!.service_destination[0].country_id.toString() &&
        d.code == service.value.service!.service_destination[0].state_id.toString()
      );
    });
    let destinyCity = null;
    if (service.value.service!.service_destination[0].city_id) {
      destinyCity = servicesDestinations.value.destinationsCities.find((d) => {
        return (
          d.state_code == service.value.service!.service_destination[0].state_id.toString() &&
          d.code == service.value.service!.service_destination[0].city_id.toString()
        );
      });
    }

    // Origin
    const originCountry = servicesDestinations.value.destinationsCountries.find((d) => {
      return d.code == service.value.service!.service_origin[0].country_id.toString();
    });
    const originState = servicesDestinations.value.destinationsStates.find((d) => {
      return (
        d.country_code == service.value.service!.service_origin[0].country_id.toString() &&
        d.code == service.value.service!.service_origin[0].state_id.toString()
      );
    });
    let originCity = null;
    if (service.value.service!.service_origin[0].city_id) {
      originCity = servicesDestinations.value.destinationsCities.find((d) => {
        return (
          d.state_code == service.value.service!.service_origin[0].state_id.toString() &&
          d.code == service.value.service!.service_origin[0].city_id.toString()
        );
      });
    }

    const destiny = {
      code:
        destinyCountry!.code + ',' + (destinyState?.code ?? '') + ',' + (destinyCity?.code ?? ''),
      label:
        destinyCountry!.label +
        ',' +
        (destinyState?.label ?? '') +
        ',' +
        (destinyCity?.label ?? ''),
    };
    const origin = {
      code: originCountry!.code + ',' + (originState?.code ?? '') + ',' + (originCity?.code ?? ''),
      label:
        originCountry!.label + ',' + (originState?.label ?? '') + ',' + (originCity?.label ?? ''),
    };

    switch (service.value.service!.service_sub_category!.service_category_id) {
      case 9:
        await getToursAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          experience_type: '',
          service_sub_category: '',
        });
        break;

      case 10:
        await getMealsAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          service_sub_category: '',
          // price_range:
        });
        break;

      case 1:
        await getTransferAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: destiny,
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          service_premium: '',
          include_transfer_driver: '',
        });
        break;

      case 14:
        await getMiselaniosAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: '',
          service_name: '',
        });
        break;

      default:
        await getServiceByCategory({
          service_category: [service.value.service!.service_sub_category!.service_category_id],
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: destiny,
          lang: getLang(),
          origin: origin,
          service_name: '',
        });
        break;
    }

    storeSidebar.setStatus(true, 'service', 'search');
  };
</script>

<template>
  <icon-replace-list @click="searchServicesToReplace" />
</template>

<style lang="scss" scoped></style>
