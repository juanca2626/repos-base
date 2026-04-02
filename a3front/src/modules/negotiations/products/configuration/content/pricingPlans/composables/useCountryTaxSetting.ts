import { computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { useConfigurationLocations } from '@/modules/negotiations/products/configuration/composables/useConfigurationLocations';

interface Props {
  model: any;
}

export const useCountryTaxSetting = (props: Props) => {
  const supportResourcesStore = useSupportResourcesStore();
  const { countryTaxSettings } = storeToRefs(supportResourcesStore);

  const { location } = useConfigurationLocations();

  const countryCode = computed(() => location.value.countryCode ?? 'PE');

  const countryTaxSetting = computed(() => {
    return countryTaxSettings.value.find((item) => item.isoCode === countryCode.value);
  });

  watch(
    () => countryTaxSetting.value?.taxPercentage,
    (taxPercentage) => {
      if (props.model.taxes.igvPercent == null && taxPercentage != null) {
        props.model.taxes.igvPercent = taxPercentage;
        props.model.taxes.labelTax = countryTaxSetting.value?.taxCode ?? 'IGV';
      }
    },
    { immediate: true }
  );

  return {
    countryCode,
    countryTaxSetting,
    countryTaxSettings,
  };
};
