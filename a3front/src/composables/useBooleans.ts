import { ref, computed } from 'vue';

// Estado global compartido
const globalBooleans = ref<Record<string, boolean>>({});

export const useBooleans = () => {
  // Obtener valor reactivo de un booleano
  const useBoolean = (key: string) => {
    return computed({
      get: () => globalBooleans.value[key] ?? false,
      set: (value: boolean) => {
        globalBooleans.value[key] = value;
      },
    });
  };

  // Obtener valor simple de un booleano
  const getValue = (key: string): boolean => {
    return globalBooleans.value[key] ?? false;
  };

  // Establecer valor de un booleano
  const setValue = (key: string, value: boolean): void => {
    globalBooleans.value[key] = value;
  };

  // Alternar valor de un booleano
  const toggle = (key: string): void => {
    globalBooleans.value[key] = !getValue(key);
  };

  // Limpiar un booleano específico
  const clear = (key: string): void => {
    delete globalBooleans.value[key];
  };

  // Limpiar todos los booleanos
  const clearAll = (): void => {
    globalBooleans.value = {};
  };

  // Obtener computed reactivo de todos los booleanos
  const all = computed(() => globalBooleans.value);

  return {
    useBoolean,
    getValue,
    setValue,
    toggle,
    clear,
    clearAll,
    all,
  };
};
