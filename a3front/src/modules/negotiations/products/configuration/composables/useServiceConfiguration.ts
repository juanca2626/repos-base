import { ref } from 'vue';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';

export const useServiceConfiguration = () => {
  const { isServiceTypeMultiDays } = useSelectedServiceType();

  const measurementUnits = ref<SelectOption[]>([]);

  const measurementUnitsByServiceType = {
    multiDays: [{ label: 'Paquete', value: 'package' }],
    default: [
      { label: 'Pasajeros', value: 'passenger' },
      { label: 'Unidad', value: 'unit' },
    ],
  };

  const initData = () => {
    measurementUnits.value = isServiceTypeMultiDays.value
      ? measurementUnitsByServiceType.multiDays
      : measurementUnitsByServiceType.default;
  };

  const getMeasurementUnitLabel = (value: any) => {
    return measurementUnits.value.find((opt) => opt.value === value)?.label || '';
  };

  return {
    measurementUnits,
    getMeasurementUnitLabel,
    initData,
  };
};
