import { computed } from 'vue';

export function usePassengerPricing(rate, servicePercent, igvPercent) {
  const services = computed(() => {
    return (rate.value * servicePercent.value) / 100;
  });

  const igv = computed(() => {
    return (rate.value * igvPercent.value) / 100;
  });

  const total = computed(() => {
    return rate.value + services.value + igv.value;
  });

  return {
    services,
    igv,
    total,
  };
}
