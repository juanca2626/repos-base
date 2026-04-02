<template>
  <div class="service-pricing-plans-form-container">
    <!-- Modo lectura -->
    <ReadModeComponent
      v-if="!isEditMode"
      title="Planes tarifarios"
      :read-only="readOnly"
      @edit="handleEditMode"
    >
      <div class="read-item">
        <span class="read-item-label">Planes tarifarios</span>
        <span class="read-item-value">Planes completados</span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="edit-mode-container">
      <StepContainer />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import { storeToRefs } from 'pinia';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
  import StepContainer from './StepContainer.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
    readOnly?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    readOnly: false,
  });

  const navigationStore = useNavigationStore();
  // const { setCompletedItem } = navigationStore;
  const { getSectionsItemActive } = storeToRefs(navigationStore);

  const isItemCompleted = getSectionsItemActive.value?.completed ?? false;

  // en caso se quiera usar la parte cuando esta en modo edición
  // const isEditingContent = computed(() => {
  //   return getSectionsItemActive.value?.editing ?? false;
  // });

  const isEditMode = ref<boolean>(!props.readOnly && !isItemCompleted);

  const handleEditMode = () => {
    if (props.readOnly) return; // No permitir edición en modo readOnly
    isEditMode.value = true;
  };

  // const handleSave = () => {
  //   isEditMode.value = false;
  //   // esto es para marcar el item como completado
  //   setCompletedItem(
  //     props.currentKey,
  //     props.currentCode,
  //     getSectionsItemActive.value?.id ?? null
  //   );
  // };
</script>

<style scoped lang="scss">
  .service-pricing-plans-form-container {
    background-color: #ffffff;
  }

  .edit-mode-container {
    padding: 24px;
  }

  .form-content {
    margin-top: 24px;
  }
</style>

<style lang="scss">
  /* Global: remove focus outlines from all icons in pricing plans */
  .service-pricing-plans-form-container {
    svg:focus,
    svg:active,
    svg:focus-visible,
    .svg-inline--fa:focus,
    .svg-inline--fa:active,
    .svg-inline--fa:focus-visible,
    .action-icon:focus,
    .action-icon:active,
    .action-icon:focus-visible,
    .info-icon-inline:focus,
    .info-icon-inline:active,
    .info-icon-inline:focus-visible,
    .col-actions *:focus,
    .col-actions *:active,
    .col-actions *:focus-visible {
      outline: none !important;
      box-shadow: none !important;
      border: none !important;
    }
  }
</style>
