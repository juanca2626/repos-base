import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useADrawerStore = defineStore('aDrawer', () => {
  const isDrawerVisible = ref(false);
  const drawerTitle = ref('');
  const drawerWidth = ref(525); // Ancho predeterminado
  const maskClosable = ref(false);
  const keyboard = ref(false);
  const saveButtonText = ref('Guardar');
  const loading = ref(false);

  // Función para mostrar el drawer con datos dinámicos
  const showDrawer = (
    title: string,
    config: {
      width?: number;
      maskClosable?: boolean;
      keyboard?: boolean;
      saveText?: string;
    } = {}
  ) => {
    drawerTitle.value = title;
    drawerWidth.value = config.width ?? 525;
    maskClosable.value = config.maskClosable ?? false;
    keyboard.value = config.keyboard ?? false;
    saveButtonText.value = config.saveText ?? 'Guardar';
    isDrawerVisible.value = true;
  };

  // Función para manejar la acción de guardar
  const handleDrawerSave = async (saveAction: () => Promise<void>) => {
    console.log('🚀 ~ handleDrawerSave');
    // loading.value = true;
    try {
      await saveAction();
      // loading.value = false;
      // isDrawerVisible.value = false;
    } catch (error) {
      console.error('Error al ejecutar acción de guardar en el drawer:', error);
    } finally {
      // loading.value = false;
      // isDrawerVisible.value = false;
    }
  };

  // Función para cerrar el drawer
  const handleDrawerClose = () => {
    loading.value = false;
    isDrawerVisible.value = false;
  };

  return {
    isDrawerVisible,
    drawerTitle,
    drawerWidth,
    maskClosable,
    keyboard,
    saveButtonText,
    loading,
    showDrawer,
    handleDrawerSave,
    handleDrawerClose,
  };
});
