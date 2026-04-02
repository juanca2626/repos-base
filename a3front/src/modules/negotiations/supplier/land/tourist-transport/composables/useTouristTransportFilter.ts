import { computed, onMounted, reactive, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useTypeUnitsTransportsStore } from '@/modules/negotiations/supplier/land/tourist-transport/store/typeUnitsTransports.store';
import { useSearchTouristTransportFiltersStore } from '@/modules/negotiations/supplier/store/search-tourist-transport-filters.store';
import type { TypeUnitTransportInterface } from '@/modules/negotiations/interfaces/type-unit-transports-response.interface';

export const useTouristTransportFilter = () => {
  const isLoading = ref(false);
  const typeUnitsTransportsStore = useTypeUnitsTransportsStore();
  const { typeUnitsTransportsList } = storeToRefs(useTypeUnitsTransportsStore());

  const searchFiltersStore = useSearchTouristTransportFiltersStore();
  const maxTagCount = ref(2);
  const keyAllSelectTypeUnit = -1;

  const formState = reactive({
    name: '',
    status: null,
    typeUnitTransportId: [keyAllSelectTypeUnit] as number[],
  });

  const typeUnitOptions = computed(() => [
    { value: keyAllSelectTypeUnit, label: 'Todos', name: 'Todos' },
    ...typeUnitsTransportsList.value.map((item: TypeUnitTransportInterface) => ({
      value: item.id,
      label: item.name,
      name: item.name,
    })),
  ]);

  const handleSelectTypeUnit = (value: number) => {
    if (value === keyAllSelectTypeUnit) {
      formState.typeUnitTransportId = [keyAllSelectTypeUnit];
    } else {
      formState.typeUnitTransportId = formState.typeUnitTransportId.filter(
        (item) => item !== keyAllSelectTypeUnit
      );
    }

    handleChangeTypeUnit();
  };

  const handleDeselectTypeUnit = () => {
    if (formState.typeUnitTransportId.length == 0) {
      formState.typeUnitTransportId = [keyAllSelectTypeUnit];
    }

    handleChangeTypeUnit();
  };

  const isSelected = (value: number): boolean => {
    return formState.typeUnitTransportId.includes(value);
  };

  const handleChangeTypeUnit = () => {
    searchFiltersStore.setTypeUnitTransportId(formState.typeUnitTransportId);
  };

  const onSearch = (value: string) => {
    formState.name = value;
    searchFiltersStore.setName(formState.name);
  };

  const handleChangeStatus = () => {
    searchFiltersStore.setStatus(formState.status);
  };

  const cleanFilters = () => {
    formState.name = '';
    formState.status = null;
    formState.typeUnitTransportId = [keyAllSelectTypeUnit];

    handleChangeTypeUnit();
    onSearch(formState.name);
    handleChangeStatus();
  };

  onMounted(() => {
    typeUnitsTransportsStore.fetchTypeUnitsTransports();
  });

  return {
    formState,
    cleanFilters,
    isLoading,
    isSelected,
    onSearch,
    typeUnitOptions,
    maxTagCount,
    handleChangeStatus,
    handleSelectTypeUnit,
    handleDeselectTypeUnit,
  };
};
