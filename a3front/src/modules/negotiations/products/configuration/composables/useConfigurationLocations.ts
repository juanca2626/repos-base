import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { ProductSupplierTypeEnum } from '@/modules/negotiations/products/general/enums/product-supplier-type.enum';

type LocationResult = {
  countryCode: string;
  stateCode: string;
};

export function useConfigurationLocations() {
  const configurationStore = useConfigurationStore();
  const navigationStore = useNavigationStore();

  const { productSupplierType, items } = storeToRefs(configurationStore);

  const location = computed<LocationResult>(() => {
    const currentKey = navigationStore.currentKey;

    if (!currentKey) {
      return { countryCode: '', stateCode: '' };
    }

    if (productSupplierType.value === ProductSupplierTypeEnum.PACKAGE) {
      return { countryCode: '', stateCode: '' };
    }

    const item = items.value.find((i) => i.key === currentKey);

    if (!item?.raw) {
      return { countryCode: '', stateCode: '' };
    }

    const raw = item.raw as {
      operatingLocation?: {
        country?: { code?: string };
        state?: { code?: string };
      };
    };

    const operatingLocation = raw.operatingLocation;

    return {
      countryCode: operatingLocation?.country?.code ?? '',
      stateCode: operatingLocation?.state?.code ?? '',
    };
  });

  return {
    location,
  };
}
