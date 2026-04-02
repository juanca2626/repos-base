<template>
  <a-drawer
    v-model:open="isDrawerVisible"
    :title="drawerTitle"
    :width="drawerWidth"
    :maskClosable="maskClosable"
    :keyboard="keyboard"
    @close="handleDrawerClose"
  >
    <!-- Contenido dinámico definido en el componente padre -->
    <slot></slot>

    <template #footer>
      <a-row>
        <a-col :span="24">
          <a-button type="primary" block @click="handleSave" :loading="loading">
            {{ saveButtonText }}
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { storeToRefs } from 'pinia';
  import { useADrawerStore } from '@operations/shared/stores/drawer.store';
  import { useAdditionalServiceStore } from '../../modules/service-management/store/additional-service.store';

  const aDrawerStore = useADrawerStore();
  const addServiceStore = useAdditionalServiceStore();

  // Reactive bindings desde el store
  const {
    isDrawerVisible,
    drawerTitle,
    drawerWidth,
    maskClosable,
    keyboard,
    saveButtonText,
    loading,
  } = storeToRefs(aDrawerStore);

  // Props para recibir la lógica del componente padre
  // Declaración de props
  const props = defineProps<{
    onSave: () => Promise<void>; // Especifica el tipo como una función asíncrona
  }>();

  // Función para cerrar el drawer
  const handleDrawerClose = () => {
    aDrawerStore.handleDrawerClose();
    addServiceStore.is_required_carrier = false;
    addServiceStore.is_required_guide = false;
  };

  // Lógica para guardar y ejecutar la callback
  const handleSave = async () => {
    console.log('handleSave');
    // loading.value = true;
    try {
      await props.onSave(); // Utiliza props para acceder a la función
      // handleDrawerClose();
    } catch (error) {
      console.error('Error en el proceso de guardar:', error);
    } finally {
      // loading.value = false;
    }
  };
</script>
