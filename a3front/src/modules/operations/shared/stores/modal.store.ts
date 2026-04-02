import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useDataStore } from '@operations/modules/service-management/store/data.store';
import { useDataStore as useDataProvidersStore } from '@operations/modules/providers/store/data.store';
import { useSelectionStore } from '@operations/shared/stores/selection.store';

export const useModalStore = defineStore('modal', () => {
  const dataStore = useDataStore();
  const dataProvidersStore = useDataProvidersStore();

  const selectionStore = useSelectionStore();
  // Estado del modal
  const isModalVisible = ref(false);
  const modalTitle = ref('');
  const modalData = ref<any>({}); // Incluye `item` dinámico
  const modalWidth = ref(488);
  const loading = ref(false);

  // Estado del proceso actual
  const currentProcess = ref<string | null>(null); // Identifica el proceso activo

  // Función para mostrar el modal
  const showModal = (
    process: string | null = null,
    title: string,
    data: any = {},
    width: number = 488
  ) => {
    currentProcess.value = process;
    modalTitle.value = title;
    modalData.value = data;
    modalWidth.value = width;
    isModalVisible.value = true;
  };

  // Confirmar acción en el modal
  const handleModalOk = async () => {
    loading.value = true;
    try {
      const result = await resolveProcess(currentProcess.value, modalData.value);
      console.log('🚀 ~ handleModalOk ~ result:', result);
      // loading.value = false;
      // resetModal();
    } catch (error) {
      console.error('Error:', error);
    } finally {
      loading.value = false;
      resetModal();
    }
  };

  // Cerrar el modal y limpiar su estado
  const handleModalCancel = () => {
    resetModal();
  };

  // Restablecer el estado del modal
  const resetModal = () => {
    isModalVisible.value = false;
    modalTitle.value = '';
    modalData.value = {};
    currentProcess.value = null;
    modalWidth.value = 488;
    loading.value = false;
    // Eliminando todos los seleccionados
    selectionStore.selectedItems = [];
  };

  // const updateServiceInfo = async (data: any) => {
  //   console.log('🚀 ~ updateServiceInfo ~ data:', data);
  // };

  const processActions: Record<string, (process: string, data: any) => Promise<void>> = {
    guiAssignment: dataStore.handleProvidersAssignment,
    trpAssignment: dataStore.handleProvidersAssignment,
    removeAssignment: dataStore.removeAssignment,
    updateVehicleType: dataStore.updateAssignment,
    createReturn: dataStore.createReturn,
    unitAssignment: dataProvidersStore.handleUnitAssignment,
    // informationService: updateServiceInfo,

    // Añade más procesos aquí...
    // if(process==='guiAssignment'||process==='trpAssignment') dataStore.handleProvidersAssignment(process, data);
    // else console.log('...');
  };

  const resolveProcess = async (process: string | null, data: any) => {
    // console.log('🚀 ~ resolveProcess ~ process:', process);
    // console.log('🚀 ~ resolveProcess ~ data:', data.data);
    // Agregar una demora de 2 segundos (2000 ms)
    // await delay(2000);
    if (process && processActions[process]) {
      try {
        // const { data } = data;
        await processActions[process](process, data.data);
        // notification.success({
        //   message: 'Operación exitosa',
        //   description: `El proceso "${process}" se completó correctamente.`,
        // });
      } catch (error) {
        // notification.error({
        //   message: 'Error',
        //   description: `Hubo un problema con el proceso "${process}".`,
        // });
        console.error(`Error en el proceso "${process}":`, error);
      }
    } else {
      console.warn(`No action defined for process: ${process}`);
    }
  };

  // const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

  return {
    isModalVisible,
    modalTitle,
    modalData,
    modalWidth,
    loading,
    currentProcess, // Estado del proceso actual
    showModal,
    handleModalOk,
    handleModalCancel,
    resetModal,
  };
});
