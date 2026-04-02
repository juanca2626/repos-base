import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { ref, onMounted } from 'vue';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type {
  SelectOption,
  DrawerEmitTypeInterface,
} from '@/modules/negotiations/suppliers/interfaces';
import { useTourOperatorFilterStore } from '@/modules/negotiations/suppliers/tour-operators/store/tour-operator-filter.store';

export function useTourOperatorFilterForm(emit: DrawerEmitTypeInterface) {
  const { filterState, resetFilterState, setApplyFilters } = useTourOperatorFilterStore();

  const isLoading = ref<boolean>(false);

  const collapseSectionKeys = ref<string[]>(['chains', 'country-states', 'status']);

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

      const response = await supplierApi.get('suppliers/tour-operators/filter-resources', {
        params: {
          keys: ['chains', 'country_state_options', 'status'],
        },
      });

      const filteredStatus = response.data.data.status.filter(
        (status: any) =>
          status.id && status.id.trim() !== '' && status.name && status.name.trim() !== ''
      );

      statusOptions.value = mapItemsToOptions(filteredStatus);
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
