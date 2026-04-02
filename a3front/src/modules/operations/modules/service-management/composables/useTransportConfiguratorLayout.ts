//useTransportConfiguratorLayout.ts
import { ref, computed } from 'vue';
import { on, off } from '../../shared/api/eventBus';
import type { UnitTransportConfigurator } from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';
import { useTransportConfiguratorStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorStore';

// Composable para manejar el layout de configuración de transporte
export const useTransportConfiguratorLayout = () => {
  // Referencia a un estado para mostrar/ocultar el drawer
  const showDrawer = ref<boolean>(false);
  const editUnit = ref<UnitTransportConfigurator | null>(null);
  const isEditMode = ref(false);
  const transportConfiguratorStore = useTransportConfiguratorStore();

  // Opciones para el a-select, basado en las locaciones del editUnit
  const locationOptions = computed(() => {
    return (
      editUnit.value?.locations.map((location) => ({
        label: location.state, // Asume que 'state' es la propiedad a mostrar
        value: location.id,
      })) || []
    );
  });

  // Función para mostrar/ocultar el drawer
  const handlerShowDrawer = (show: boolean) => {
    if (!show) {
      transportConfiguratorStore.closeDrawer();
      editUnit.value = null;
      isEditMode.value = false;
    } else {
      transportConfiguratorStore.openDrawer();
    }
  };

  const handleAddUnit = () => {
    editUnit.value = null;
    isEditMode.value = false;
    handlerShowDrawer(true);
  };

  const handleSaveUnit = () => {
    handlerShowDrawer(false);
  };

  const closeDrawer = () => {
    handlerShowDrawer(false);
  };

  const setupEventListeners = () => {
    on('addUnit', handleAddUnit);
  };

  const cleanupEventListeners = () => {
    off('addUnit', handleAddUnit);
  };

  return {
    showDrawer,
    editUnit,
    isEditMode,
    locationOptions, // Exponer las opciones para el a-select
    handlerShowDrawer,
    handleAddUnit,
    handleSaveUnit,
    closeDrawer,
    setupEventListeners,
    cleanupEventListeners,
  };
};
