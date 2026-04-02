import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { ref, onMounted } from 'vue';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type {
  SelectOption,
  DrawerEmitTypeInterface,
} from '@/modules/negotiations/suppliers/interfaces';
import { useLodgeFilterStore } from '@/modules/negotiations/suppliers/lodges/store/lodge-filter.store';

export function useLodgeFilterForm(emit: DrawerEmitTypeInterface) {
  const { filterState, resetFilterState, setApplyFilters } = useLodgeFilterStore();

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

  const fetchResources = async () => {
    try {
      isLoading.value = true;

      const response = await supplierApi.get('suppliers/lodges/filter-resources', {
        params: {
          keys: ['chains', 'country_state_options', 'statuses'],
        },
      });

      statusOptions.value = mapItemsToOptions(response.data.data.statuses);
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
