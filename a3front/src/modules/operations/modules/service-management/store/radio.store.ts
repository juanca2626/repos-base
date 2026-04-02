import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useRadioStore = defineStore('radioStore', () => {
  const vehicle_type = ref<string | null>(null);
  const provider_code = ref<string[]>([]);

  // Funciones para vehicle_type
  const selectVehicleType = (type: string): void => {
    vehicle_type.value = type;
  };

  const clearVehicleType = (): void => {
    vehicle_type.value = null;
  };

  const isVehicleTypeSelected = (type: string): boolean => {
    return vehicle_type.value === type;
  };

  // Funciones para provider_code
  const selectProviderCode = (code: string, allowMultiple: boolean = false): void => {
    if (allowMultiple) {
      // Comportamiento original para múltiples selecciones
      if (!provider_code.value.includes(code)) {
        provider_code.value.push(code);
      }
    } else {
      // Comportamiento de radio button (un solo elemento)
      provider_code.value = [code];
    }
  };

  const removeProviderCode = (id: string): void => {
    provider_code.value = provider_code.value.filter((code) => code !== id);
    // Si queremos mantener siempre al menos un elemento y el array quedó vacío
    if (provider_code.value.length === 0 && provider_code.value.length > 0) {
      provider_code.value = [provider_code.value[0]];
    }
  };

  const clearProviderCode = (): void => {
    provider_code.value = [];
  };

  const isProviderSelected = (id: string): boolean => {
    return provider_code.value.includes(id);
  };

  const getSingleProviderCode = (): string | null => {
    return provider_code.value.length > 0 ? provider_code.value[0] : null;
  };

  // Función para limpiar todos los estados
  const clearAll = (): void => {
    vehicle_type.value = null;
    provider_code.value = [];
  };

  return {
    // Estados
    vehicle_type,
    provider_code,

    // Métodos para vehicle_type
    selectVehicleType,
    clearVehicleType,
    isVehicleTypeSelected,

    // Métodos para provider_code
    selectProviderCode,
    removeProviderCode,
    clearProviderCode,
    isProviderSelected,
    getSingleProviderCode,

    // Método general
    clearAll,
  };
});
