import { onMounted, reactive, ref } from 'vue';
import { debounce } from 'lodash-es';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { TypeUnitTransportsResponseInterface } from '@/modules/negotiations/interfaces/type-unit-transports-response.interface';
import type { SelectMultipleOption } from '@/modules/negotiations/supplier/interfaces';
import { vehicleDocumentStatusOptions } from '@/modules/negotiations/supplier/register/configuration-module/constants/vehicle-document-status';
import { useTransportVehicleFiltersStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTransportVehicleFiltersStore';

export const useTransportVehicleFilter = () => {
  const maxTagCount = ref(2);
  const keyAllSelectTypeUnit = -1;
  const typeUnitOptions = ref<SelectMultipleOption[]>([]);
  const isLoading = ref<boolean>(false);

  const formState = reactive({
    licensePlate: '',
    documentStatus: null,
    typeUnitTransportId: [keyAllSelectTypeUnit] as number[],
  });

  const { setLicensePlate, setDocumentStatus, setTypeUnitTransportId, resetFilters } =
    useTransportVehicleFiltersStore();

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

  const handleLicensePlate = debounce(() => {
    setLicensePlate(formState.licensePlate);
  }, 500);

  const isSelected = (value: number): boolean => {
    return formState.typeUnitTransportId.includes(value);
  };

  const handleChangeTypeUnit = () => {
    setTypeUnitTransportId(formState.typeUnitTransportId);
  };

  const handleDocumentStatus = () => {
    setDocumentStatus(formState.documentStatus);
  };

  const cleanFilters = () => {
    formState.licensePlate = '';
    formState.documentStatus = null;
    formState.typeUnitTransportId = [keyAllSelectTypeUnit];

    resetFilters();
  };

  const fetchTypeUnits = async () => {
    isLoading.value = true;

    try {
      const { data } =
        await technicalSheetApi.get<TypeUnitTransportsResponseInterface>('type-unit-transports');
      const baseItems = data.data.map((item: any) => {
        const description = `${item.code} - ${item.name}`;
        return {
          value: item.id,
          label: description,
          name: description,
        };
      });

      typeUnitOptions.value = [
        { value: keyAllSelectTypeUnit, label: 'Todos', name: 'Todos' },
        ...baseItems,
      ];
    } catch (error) {
      console.error('Error fetching type unit transports data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  onMounted(() => {
    fetchTypeUnits();
  });

  return {
    formState,
    typeUnitOptions,
    maxTagCount,
    vehicleDocumentStatusOptions,
    isLoading,
    handleLicensePlate,
    cleanFilters,
    isSelected,
    handleDocumentStatus,
    handleSelectTypeUnit,
    handleDeselectTypeUnit,
  };
};
