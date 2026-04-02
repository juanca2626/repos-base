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
      <PricingPlanContainer />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import PricingPlanContainer from '@/modules/negotiations/products/configuration/content/pricingPlans/ui/PricingPlanContainer.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
    readOnly?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    readOnly: false,
  });

  const isItemCompleted = false; // Temporal

  const isEditMode = ref<boolean>(!props.readOnly && !isItemCompleted);

  const handleEditMode = () => {
    if (props.readOnly) return; // No permitir edición en modo readOnly
    isEditMode.value = true;
  };

  // const handleSave = () => {
  //   isEditMode.value = false;
  // };
</script>

<style scoped lang="scss">
  .service-pricing-plans-form-container {
    background-color: #ffffff;
  }

  .edit-mode-container {
    padding: 0px;
  }

  .form-content {
    margin-top: 24px;
  }
</style>
