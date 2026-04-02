import { ref, onMounted } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type {
  SelectOption,
  DrawerEmitTypeInterface,
} from '@/modules/negotiations/suppliers/interfaces';
import { useWaterTransportFilterStore } from '@/modules/negotiations/suppliers/water-transports/store/water-transport-filter.store';

export function useWaterTransportFilterForm(emit: DrawerEmitTypeInterface) {
  const { filterState, resetFilterState, setApplyFilters } = useWaterTransportFilterStore();

  const isLoading = ref<boolean>(false);

  const collapseSectionKeys = ref<string[]>(['chains', 'country-states', 'statuses']);

  const chains = ref<SelectOption[]>([]);
  const countryStateOptions = ref<SelectOption[]>([]);
  const statusOptions = ref<SelectOption[]>([]);

  const handleClose = (): void => {
    emit('update:showDrawerForm', false);
  };

  const cleanFilters = () => {
    resetFilterState();
  };

  const handleCleanFilters = () => {
    cleanFilters();
    handleClose();
    setApplyFilters(true);
  };

  const handleApplyFilters = () => {
    setApplyFilters(true);
    handleClose();
  };

  const mapCountryStateOptions = (countryStateOptions: any[]) => {
    return countryStateOptions.map((item: any) => ({
      label: item.full_name,
      value: item.key,
    }));
  };

  const fetchResources = async () => {
    try {
      isLoading.value = true;

      const response = await supplierApi.get('suppliers/water-transports/filter-resources', {
        params: {
          keys: ['chains', 'country_state_options', 'statuses'],
        },
      });

      statusOptions.value = mapItemsToOptions(response.data.data.statuses);
      chains.value = mapItemsToOptions(response.data.data.chains);
      countryStateOptions.value = mapCountryStateOptions(response.data.data.country_state_options);
    } catch (error) {
      console.error('Error fetching resource data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  onMounted(() => {
    fetchResources();
  });

  return {
    isLoading,
    collapseSectionKeys,
    filterState,
    chains,
    countryStateOptions,
    statusOptions,
    handleClose,
    handleCleanFilters,
    handleApplyFilters,
  };
}
