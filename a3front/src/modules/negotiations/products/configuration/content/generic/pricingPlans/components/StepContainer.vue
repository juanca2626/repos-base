<template>
  <div class="step-container">
    <!-- DEV: Botones temporales para testing -->
    <div class="dev-controls">
      <span style="font-size: 12px; color: #888; margin-right: 8px">DEV:</span>
      <button
        v-for="step in 5"
        :key="step"
        :class="{ active: currentStep === step }"
        @click="currentStep = step"
      >
        Step {{ step }}
      </button>
    </div>

    <!-- Header Personalizado -->
    <div class="custom-header-row">
      <h2 class="header-title">Servicio compartido - Plan Tarifario</h2>
      <div v-if="currentStep <= 4" class="step-badge">Paso {{ currentStep }} de 4</div>
    </div>

    <!-- Stepper -->
    <PricingPlanStepper :current-step="currentStep" @step-click="handleStepClick" />

    <!-- Header dinámico según el step -->
    <!-- <div class="step-header">
      <h3 class="step-title">{{ stepTitle }}</h3>
      <p class="step-subtitle">({{ completedItems }} de {{ totalItems }} completados)</p>
    </div> -->

    <!-- Contenido del step actual -->
    <div class="step-content">
      <BasicDataForm v-if="currentStep === 1" ref="basicDataFormRef" />
      <TaxStaffForm v-else-if="currentStep === 2" ref="taxStaffFormRef" />
      <!-- Aquí se agregarán los demás steps en el futuro -->
      <AmountsForm v-else-if="currentStep === 3" ref="amountsFormRef" />
      <CuposForm v-else-if="currentStep === 4" ref="cuposFormRef" />
      <SummaryStep
        v-else-if="currentStep === 5"
        ref="summaryStepRef"
        v-model:confirmed="isConfirmed"
      />
    </div>

    <!-- Footer con acciones -->
    <div class="footer-actions">
      <a-button v-if="currentStep > 1" class="back-button" @click="handleBack">Atrás</a-button>
      <a-button
        class="save-button"
        :disabled="!canProceed || (currentStep === 5 && !isConfirmed)"
        @click="handleNext"
      >
        {{ currentStep === 5 ? 'Crear Plan Tarifario' : 'Guardar Datos' }}
      </a-button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import PricingPlanStepper from './steps/PricingPlanStepper.vue';
  import BasicDataForm from './steps/BasicDataForm.vue';
  import TaxStaffForm from './steps/TaxStaffForm.vue';
  import AmountsForm from './steps/AmountsForm.vue';
  import CuposForm from './steps/CuposForm.vue';
  import SummaryStep from './steps/SummaryStep.vue';

  defineOptions({
    name: 'StepContainer',
  });

  const currentStep = ref<number>(1);
  const totalSteps = 5;
  const isConfirmed = ref(false);

  // Referencias a los formularios de cada step
  const basicDataFormRef = ref<InstanceType<typeof BasicDataForm> | null>(null);
  const taxStaffFormRef = ref<InstanceType<typeof TaxStaffForm> | null>(null);
  const amountsFormRef = ref<InstanceType<typeof AmountsForm> | null>(null);
  const cuposFormRef = ref<InstanceType<typeof CuposForm> | null>(null);
  const summaryStepRef = ref<InstanceType<typeof SummaryStep> | null>(null);

  // Configuración de cada step (comentado temporalmente)
  // const stepConfig = {
  //   1: { title: 'Datos básicos', totalItems: 5 },
  //   2: { title: 'Staff e Impuestos', totalItems: 3 },
  //   3: { title: 'Montos', totalItems: 4 },
  //   4: { title: 'Cupos del servicio', totalItems: 2 },
  //   5: { title: 'Resumen', totalItems: 1 },
  // };

  // Computed properties (comentadas temporalmente mientras el header está deshabilitado)
  // const stepTitle = computed(() => {
  //   return stepConfig[currentStep.value as keyof typeof stepConfig]?.title || '';
  // });

  // const totalItems = computed(() => {
  //   return stepConfig[currentStep.value as keyof typeof stepConfig]?.totalItems || 0;
  // });

  // const completedItems = computed(() => {
  //   // Por ahora retorna 0, en el futuro se calculará según el estado del formulario
  //   return 0;
  // });

  // const isLastStep = computed(() => {
  //   return currentStep.value === totalSteps;
  // });

  const canProceed = computed(() => {
    // Por ahora siempre permite proceder, en el futuro se validará el formulario
    return true;
  });

  // Handlers
  const handleStepClick = (step: number) => {
    if (step <= currentStep.value) {
      currentStep.value = step;
    }
  };

  const handleBack = () => {
    if (currentStep.value > 1) {
      currentStep.value--;
    }
  };

  const handleNext = () => {
    if (currentStep.value < totalSteps) {
      currentStep.value++;
    } else {
      // Último step: guardar datos
      handleSave();
    }
  };

  const handleSave = () => {
    // Lógica para guardar todos los datos
    console.log('Guardando datos...');
    // Aquí se integraría con el store o API
  };

  // Exponer métodos para el componente padre
  defineExpose({
    currentStep,
    handleSave,
  });
</script>

<style lang="scss" scoped>
  /* DEV: Remover esta sección antes de producción */
  .dev-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #fff3cd;
    border: 1px dashed #ffc107;
    border-radius: 4px;
    margin-bottom: 16px;

    button {
      padding: 4px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background: #fff;
      cursor: pointer;
      font-size: 12px;

      &.active {
        background: #1284ed;
        color: #fff;
        border-color: #1284ed;
      }

      &:hover:not(.active) {
        background: #f0f0f0;
      }
    }
  }

  .step-container {
    width: 100%;
  }

  .step-header {
    margin-bottom: 24px;
    border-bottom: 1px solid #babcbd;
    padding-bottom: 16px;
  }

  .step-title {
    font-weight: 600;
    font-size: 16px;
    line-height: 28px;
    color: #2f353a;
    margin-bottom: 4px;
  }

  .step-subtitle {
    font-weight: 400;
    font-size: 16px;
    line-height: 24px;
    color: #7e8285;
    margin: 0;
  }

  .step-content {
    margin-bottom: 24px;
  }

  .placeholder-step {
    background: #f9f9f9;
    border: 1px dashed #d9d9d9;
    border-radius: 8px;
    padding: 48px;
    text-align: center;

    h3 {
      color: #2f353a;
      margin-bottom: 8px;
    }

    p {
      color: #7e8285;
    }
  }

  .footer-actions {
    display: flex;
    gap: 16px;
    padding-top: 0;
    /* border-top: 1px solid #e0e0e0; REMOVED */
  }

  .back-button {
    height: 48px;
    width: 160px;
    padding: 0 24px;
    border: 1px solid #2f353a;
    border-radius: 5px;
    background: #ffffff;
    color: #2f353a;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;

    &:hover,
    &:focus,
    &:active {
      border-color: #2f353a;
      color: #2f353a;
      background: #ffffff;
    }
  }

  .save-button {
    height: 48px;
    height: 48px;
    min-width: 160px; /* Use min-width instead of fixed width */
    padding: 0 40px; /* Increased padding */
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    background-color: #bd0d12;
    border-color: #bd0d12;
    color: #fff;
    cursor: pointer;

    &:hover,
    &:focus,
    &:active {
      background-color: #bd0d12;
      border-color: #bd0d12;
      color: #fff;
    }

    &:disabled {
      background-color: #bfbfbf;
      border-color: #bfbfbf;
      color: #fff;
      cursor: not-allowed;

      &:hover,
      &:focus {
        background-color: #bfbfbf;
        border-color: #bfbfbf;
        color: #fff;
      }
    }
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
