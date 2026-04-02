import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { ref, onMounted } from 'vue';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type {
  SelectOption,
  DrawerEmitTypeInterface,
} from '@/modules/negotiations/suppliers/interfaces';
import { supplierStatusOptions } from '@/modules/negotiations/suppliers/constants/supplier-status';
import { useLocalOperatorFilterStore } from '@/modules/negotiations/suppliers/local-operators/store/local-operator-filter.store';

export function useLocalOperatorFilterForm(emit: DrawerEmitTypeInterface) {
  const { filterState, resetFilterState, setApplyFilters } = useLocalOperatorFilterStore();

  const isLoading = ref<boolean>(false);

  const collapseSectionKeys = ref<string[]>(['chains', 'country-states', 'status']);

  const chains = ref<SelectOption[]>([]);
  const countryStateOptions = ref<SelectOption[]>([]);
  const statusOptions = ref<SelectOption[]>(supplierStatusOptions);

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

  const fetchResources = async () => {
    try {
      isLoading.value = true;

      const response = await supplierApi.get('suppliers/local-operators/filter-resources', {
        params: {
          keys: ['chains', 'country_state_options'],
        },
      });

      chains.value = mapItemsToOptions(response.data.data.chains);
      countryStateOptions.value = response.data.data.country_state_options.map((item: any) => ({
        label: item.full_name,
        value: item.key,
      }));
    } catch (error) {
      console.error('Error fetching resource data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  onMounted(async () => {
    await fetchResources();
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
