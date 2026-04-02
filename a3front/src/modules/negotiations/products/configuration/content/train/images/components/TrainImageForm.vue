<template>
  <div class="service-content-form-container">
    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditMode" title="Imagenes" @edit="handleEditMode">
      <div class="read-item">
        <span class="read-item-label">Imágenes del servicio</span>
        <span class="read-item-value">Contenido completado</span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="edit-mode-container">
      <WizardHeaderComponent title="Imagenes" :completed="0" :total="1" />
      <div class="form-content">
        <p>Formulario de imágenes (por implementar)</p>
        <a-button type="primary" @click="handleSave">Guardar</a-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted } from 'vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    currentKey: '',
    currentCode: '',
  });

  console.log('props', props);

  const isEditMode = ref<boolean>(false);

  const initializeEditMode = () => {
    isEditMode.value = false;
  };

  const handleEditMode = () => {
    isEditMode.value = true;
  };

  const handleSave = () => {};

  onMounted(() => {
    initializeEditMode();
  });
</script>

<style scoped lang="scss">
  .service-content-form-container {
    background-color: #ffffff;
  }

  .edit-mode-container {
    padding: 24px;
  }

  .form-content {
    margin-top: 24px;
  }
</style>
