<template>
  <div class="pricing-plan-container">
    <div class="custom-header-row">
      <h2 class="header-title">Servicio compartido - Plan Tarifario</h2>
      <div v-if="currentStepIndex <= 4" class="step-badge">Paso {{ currentStepIndex }} de 4</div>
    </div>

    <PricingPlanStepper
      :steps="steps"
      :currentStepIndex="currentStepIndex"
      @step-click="(index) => (currentStepIndex = index)"
    />

    <component
      :is="stepComponent"
      ref="stepRef"
      :model="currentSection"
      :errors="errors"
      :ratePlanId="ratePlanId"
      :serviceType="serviceType"
    />

    <PricingPlanNavigation
      :currentStepIndex="currentStepIndex"
      :totalSteps="steps.length"
      :steps="steps"
      :loading="isLoading"
      :isStepValid="isStepValid"
      :nextLabel="nextLabel"
      @back="back"
      @next="next"
    />
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { storeToRefs } from 'pinia';
  import PricingPlanStepper from './PricingPlanStepper.vue';
  import PricingPlanNavigation from './PricingPlanNavigation.vue';
  import { usePricingPlanFlow } from '../composables/usePricingPlanFlow';
  import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
  import { ServiceTypeEnum } from '@/modules/negotiations/products/configuration/enums/ServiceType.enum';
  const configurationStore = useConfigurationStore();
  const { productSupplierType } = storeToRefs(configurationStore);
  const serviceType = computed(() => productSupplierType.value || ServiceTypeEnum.GENERIC);

  const {
    stepRef,
    ratePlanId,
    steps,
    currentStepIndex,
    stepComponent,
    currentSection,
    errors,
    next,
    back,
    isLoading,
    isStepValid,
    nextLabel,
  } = usePricingPlanFlow(serviceType);
</script>

<style scoped>
  .pricing-plan-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .stepper-container {
    margin-bottom: 32px;
    display: flex;
    justify-content: center;
  }

  /* Stepper Personalizado */
  .custom-stepper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    width: 100%;
    max-width: 1000px;
    position: relative;
  }

  /* Línea de fondo única - atraviesa todo el stepper */
  .stepper-line-background {
    position: absolute;
    top: 19px; /* Centro del icono de 40px, ajustado para grosor de 3px */
    left: 60px; /* Centro del primer icono (120px min-width / 2) */
    right: 60px; /* Centro del último icono */
    height: 3px;
    background-color: #d9d9d9;
    z-index: 0;
  }

  /* Línea de progreso azul */
  .stepper-line-progress {
    position: absolute;
    top: 19px;
    left: 60px;
    height: 3px;
    background-color: #1284ed;
    z-index: 1;
    transition: width 0.3s ease;
  }

  .stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 120px;
    position: relative;
    z-index: 1; /* Encima de la línea */
    cursor: pointer;

    &:hover .step-title {
      color: #575b5f;
      user-select: none;
    }
  }

  .step-icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2; /* El icono tapa la línea */
  }

  .step-title {
    margin-top: 8px;
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 14px;
    line-height: 20px;
    color: #8c8c8c;
    text-align: center;
    white-space: nowrap;
  }

  .stepper-step.active .step-title {
    color: #000000e0;
    font-weight: 500;
  }

  .stepper-step.completed .step-title {
    color: #1284ed;
    font-weight: 500;
  }

  /* Custom Header Styles */
  .custom-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    width: 100%;
  }

  .header-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 24px !important;
    line-height: 28px;
    color: #2f353a;
    margin: 0;
  }

  .step-badge {
    background-color: #ebf5ff;
    color: #1284ed;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    line-height: 20px;
    padding: 2px 8px;
    border-radius: 4px;
  }
</style>
