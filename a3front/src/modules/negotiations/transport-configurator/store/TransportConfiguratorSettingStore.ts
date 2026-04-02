// TransportConfiguratorSettingStore.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import {
  handleSuccessResponse,
  handleError as handleApiError,
  handleDeleteResponse,
} from '@/modules/negotiations/api/responseApi';
import type {
  UnitTransportConfigurationSetting,
  SettingDetail,
  SaveTransportConfigurationPayload,
  TransferItem,
} from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator-setting.interface';

export const useTransportConfiguratorSettingStore = defineStore(
  'transportConfiguratorSetting',
  () => {
    // State: Estados

    const baseResource = 'unit-settings';
    const transportConfigurationSettings = ref<UnitTransportConfigurationSetting[]>([]); //Detalles de la configuración actual
    const setting = ref<SettingDetail[]>([]);

    const isDrawerOpen = ref(false); //Controla la visibilidad del drawer
    const isLoading = ref(false); //Indica si hay una operación de carga en curso
    const editingConfiguration = ref<UnitTransportConfigurationSetting | null>(null); //Edición de la configuración seleccionada
    const expandedRows = ref<number[]>([]); //Conjunto deIDs de filas expandidas en la tabla de configuraciones
    const isLoadingResource = ref<boolean>(false);

    // Getters:
    const getTransportConfigurationSettings = computed(() => transportConfigurationSettings.value); //Obtiene la lista de configuraciones de transporte
    const expandedRowKeys = computed(() => expandedRows.value); //Obtiene las claves de las filas expandidas

    const transferItems = ref<TransferItem[]>([]);

    // Actions: Acciones de los detalles de la configuración
    const openDrawer = (configuration: UnitTransportConfigurationSetting | null = null) => {
      editingConfiguration.value = configuration;
      isDrawerOpen.value = true;
    };

    const closeDrawer = () => {
      editingConfiguration.value = null;
      isDrawerOpen.value = false;
    };

    //obtiene la lista completa de configuraciones de transporte desde la API
    const fetchTransportConfigurationSettings = async () => {
      if (isLoading.value) return; // Evita llamadas adicionales si ya se está cargando

      isLoading.value = true;
      try {
        const response = await supportApi.get(`${baseResource}?status=1`);
        console.log(response);
      } catch (error: any) {
        console.error('Error al obtener configuraciones de transporte:', error);
        handleApiError(error);
      } finally {
        isLoading.value = false;
      }
    };

    // Acción: Eliminar múltiples configuraciones
    const deleteMultipleSettingDetails = async (settingDetailsIds: number[]) => {
      try {
        const { data } = await supportApi.post(`${baseResource}/setting-details/delete-multiple`, {
          ids: settingDetailsIds,
        });

        handleDeleteResponse(data);
      } catch (error: any) {
        console.error('Error al eliminar detalle de configuraciones:', error);
        handleApiError(error);
      }
    };

    // Acción: Eliminar configuraciones por bloque/setting
    const deleteBlockTransportSetting = async (
      locationId: number,
      typeUnitTransportSettingId: number
    ) => {
      try {
        const { data } = await supportApi.post(`${baseResource}/${locationId}/delete-multiple`, {
          ids: [typeUnitTransportSettingId],
        });

        handleDeleteResponse(data);
      } catch (error: any) {
        console.error('Error al eliminar el bloque de configuraciones:', error);
        handleApiError(error);
      }
    };

    // Acción: guarda o actualiza una configuración de transporte a través de la API
    const saveTransportConfiguration = async (
      payload: SaveTransportConfigurationPayload,
      mode: string,
      typeUnitSettingId: number | null
    ) => {
      isLoading.value = true;

      try {
        const response =
          mode === 'edit'
            ? await supportApi.put(`${baseResource}/${typeUnitSettingId}`, payload)
            : await supportApi.post(baseResource, payload);

        handleSuccessResponse(response.data);
        return true;
      } catch (error: any) {
        console.error('Error al guardar la configuración:', error);
        handleApiError(error);
        return false;
      } finally {
        isLoading.value = false;
      }
    };

    const fetchResources = async () => {
      isLoadingResource.value = true;

      try {
        const { data } = await supportApi.get(`${baseResource}/resources`, {
          params: {
            keys: ['transfers'],
          },
        });

        transferItems.value = data.data.transfers;
      } catch (error) {
        console.error('Error fetching resources:', error);
      } finally {
        isLoadingResource.value = false;
      }
    };

    return {
      transportConfigurationSettings,
      setting,
      isDrawerOpen,
      isLoading,
      isLoadingResource,
      editingConfiguration,
      expandedRows,
      expandedRowKeys,
      getTransportConfigurationSettings,
      transferItems,
      openDrawer,
      closeDrawer,
      fetchTransportConfigurationSettings,
      deleteMultipleSettingDetails,
      deleteBlockTransportSetting,
      saveTransportConfiguration,
      fetchResources,
    };
  }
);
