import { defineStore } from 'pinia';
import type { UnitTransportConfigurator } from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';
import { computed, ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import {
  handleError as handleApiError,
  handleSuccessResponse,
} from '@/modules/negotiations/api/responseApi';

export const useTransportConfiguratorStore = defineStore('transportConfigurator', () => {
  const units = ref<UnitTransportConfigurator[]>([]);
  const locationOptions = ref<[]>([]);
  const isLoading = ref(false);
  const isDrawerOpen = ref(false);
  const currentUnit = ref<UnitTransportConfigurator[]>([]);
  const editMode = ref(false);

  const isEditMode = computed(() => editMode.value);

  const openDrawer = (unit?: UnitTransportConfigurator) => {
    if (unit) {
      currentUnit.value = [{ ...unit }];
      editMode.value = true;
    } else {
      currentUnit.value = [createNewUnit()];
      editMode.value = false;
    }
    isDrawerOpen.value = true;
  };

  const closeDrawer = () => {
    isDrawerOpen.value = false;
    currentUnit.value = [];
    editMode.value = false;
  };

  const createNewUnit = (): UnitTransportConfigurator => ({
    id: null,
    code: '',
    name: '',
    status: false,
    is_trunk: false,
    created_at: new Date().toISOString(),
    locations: [],
    isValid: false,
  });

  const addUnit = () => {
    if (!editMode.value) {
      currentUnit.value.push(createNewUnit());
    }
  };

  const updateUnit = (updatedUnit: UnitTransportConfigurator, index: number) => {
    if (index !== -1) {
      units.value[index] = { ...updatedUnit };
      currentUnit.value[index] = { ...updatedUnit };
    }
  };

  const deleteUnit = (index: number) => {
    currentUnit.value = currentUnit.value.filter((_, i) => i !== index);
  };

  const setLocationOptions = (options: []) => {
    locationOptions.value = options;
  };

  const saveUnitsToApi = async (unitsToSave: UnitTransportConfigurator[]) => {
    isLoading.value = true;
    try {
      const promises = unitsToSave.map((unit) => {
        const transformedUnit = mapUnitForApi(unit);

        return unit.id
          ? supportApi.put(`units/${unit.id}`, transformedUnit)
          : supportApi.post('units', transformedUnit);
      });

      const responses = await Promise.all(promises);
      const allSuccess = responses.every((response) => response.data.success);

      if (allSuccess) {
        handleSuccessResponse({ message: 'Todas las unidades se guardaron correctamente' });
        return { success: true };
      } else {
        const errorMessage = 'Algunas unidades no se pudieron guardar';
        handleApiError(new Error(errorMessage));
        return { success: false };
      }
    } catch (error) {
      handleApiError(error);
      return { success: false, message: 'Ocurrió un error al guardar las unidades' };
    } finally {
      isLoading.value = false;
    }
  };

  const mapUnitForApi = (unit: UnitTransportConfigurator) => ({
    id: unit.id,
    code: unit.code,
    name: unit.name,
    status: unit.status ? 1 : 0,
    is_trunk: unit.is_trunk ? 1 : 0,
    locations: unit.locations.map((location) => ({
      id: location?.id,
      country_id: location.country_id,
      state_id: location.state_id,
      city_id: location.city_id,
      zone_id: location.zone_id,
    })),
  });

  const downloadUnitsExport = async () => {
    isLoading.value = true;
    try {
      // Realiza la solicitud al backend para obtener el archivo Excel
      const response = await supportApi.get('units/export', {
        responseType: 'blob', // Especifica que esperamos un archivo binario (blob)
        headers: {
          Accept: '*/*',
        },
      });

      // Crea un Blob con los datos del archivo Excel
      const fileBlob = new Blob([response.data], { type: 'application/octet-stream' });

      // Genera el nombre personalizado del archivo
      const customFileName = 'archivo_personalizado.xlsx'; // Cambia esto al nombre que desees

      // Crear un enlace de descarga
      const link = document.createElement('a');

      link.href = URL.createObjectURL(fileBlob); // Crea una URL para el blob
      link.setAttribute('download', customFileName); // Establece el nombre para la descarga

      // Agregar el enlace al documento y hacer clic para iniciar la descarga
      document.body.appendChild(link);
      link.click();

      // Limpiar recursos
      document.body.removeChild(link);

      handleSuccessResponse({ message: 'Archivo descargado correctamente' });
    } catch (error) {
      console.error('Error al descargar el archivo:', error);
      handleApiError(error);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    units,
    locationOptions,
    isLoading,
    isDrawerOpen,
    currentUnit,
    isEditMode,
    openDrawer,
    closeDrawer,
    setLocationOptions,
    addUnit,
    updateUnit,
    deleteUnit,
    saveUnitsToApi,
    downloadUnitsExport,
  };
});
