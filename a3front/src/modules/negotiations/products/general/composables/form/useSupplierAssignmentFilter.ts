import { debounce } from 'lodash';
import { onMounted, reactive, ref } from 'vue';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import { supplierAssignmentResourceService } from '@/modules/negotiations/products/general/services/supplierAssignmentResourceService';
import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';
import type {
  LocationOption,
  SupplierAssignmentFilter,
} from '@/modules/negotiations/products/general/interfaces/form';
import { useSupplierAssignmentFilterStore } from '@/modules/negotiations/products/general/store/useSupplierAssignmentFilterStore';

export function useSupplierAssignmentFilter() {
  const { setLocationKey, setSearchTerm, setSupplierClassificationId } =
    useSupplierAssignmentFilterStore();

  const { fetchSupplierClassifications, fetchLocationsByCity } = supplierAssignmentResourceService;

  const { startLoading, stopLoading } = useProductFormStoreFacade();

  const formState = reactive<SupplierAssignmentFilter>({
    locationKey: null,
    searchTerm: null,
    supplierClassificationId: null,
  });

  const searchCityLoading = ref<boolean>(false);

  const supplierClassifications = ref<SelectOption[]>([]);

  const locations = ref<LocationOption[]>([]);

  const getSupplierClassifications = async () => {
    supplierClassifications.value = [];
    const { data } = await fetchSupplierClassifications();
    supplierClassifications.value = mapItemsToOptions(data);
  };

  const searchLocationsByCity = async (value: string) => {
    locations.value = [];

    try {
      searchCityLoading.value = true;
      const { data } = await fetchLocationsByCity(value);

      locations.value = data.map((item) => {
        return {
          label: item.location_name,
          value: `${item.country.id}-${item.state.id}-${item.city.id}`,
          ...item,
        };
      });
    } catch (error) {
      console.error('Error search data:', error);
    } finally {
      searchCityLoading.value = false;
    }
  };

  const handleSearchCity = debounce((value: string) => {
    if (value.length > 1) {
      searchLocationsByCity(value);
    }
  }, 400);

  const fetchResources = async () => {
    try {
      startLoading();
      await getSupplierClassifications();
    } catch (error) {
      console.error('Error fetching resources:', error);
    } finally {
      stopLoading();
    }
  };

  const handleChangeClassification = () => {
    setSupplierClassificationId(formState.supplierClassificationId);
  };

  const handleChangeLocation = () => {
    setLocationKey(formState.locationKey);
  };

  const handleInputSearchTerm = debounce(() => {
    const term = formState.searchTerm?.trim() ?? '';

    if (term.length > 2) {
      setSearchTerm(formState.searchTerm);
    }
  }, 500);

  onMounted(async () => {
    await fetchResources();
  });

  return {
    supplierClassifications,
    locations,
    formState,
    searchCityLoading,
    handleSearchCity,
    filterOption,
    handleChangeClassification,
    handleChangeLocation,
    handleInputSearchTerm,
  };
}
