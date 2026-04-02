<template>
  <step-component :step="currentStep" :steps="stepsData" class="p-5" @stepClicked="goToStep" />
  <template v-if="isCurrentStep(0)">
    <ServiceMaskAddSupplierComponent
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(0)"
      @goToFinalStep="goToFinalStep"
    />
  </template>
  <template v-else-if="isCurrentStep(1)">
    <ServiceMaskAddRateComponent
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(1)"
      @goToFinalStep="goToFinalStep"
    />
  </template>
  <template v-else-if="isCurrentStep(2)">
    <ServiceMaskFinishedComponent
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(2)"
    />
  </template>
</template>
<script setup lang="ts">
  import StepComponent from '@/components/global/StepComponent.vue';
  import { onMounted, ref, watch } from 'vue';
  import ServiceMaskAddSupplierComponent from '@/components/files/services-masks/components/ServiceMaskAddSupplierComponent.vue';
  import ServiceMaskAddRateComponent from '@/components/files/services-masks/components/ServiceMaskAddRateComponent.vue';
  import ServiceMaskFinishedComponent from '@/components/files/services-masks/components/ServiceMaskFinishedComponent.vue';

  const currentStep = ref(0);

  const stepsData = ref([
    { title: 'En proceso', description: 'Proveedor del servicio', completed: false },
    { title: 'En espera', description: 'Detalles para regalo', completed: false },
    { title: 'En espera', description: 'Regalo agregado', completed: false },
  ]);

  const isCurrentStep = (step) => currentStep.value === step;

  // Función para avanzar al siguiente paso
  const goToNextStep = () => {
    console.log('goToNextStep called in ServiceMaskSteps');
    console.log(
      stepsData.value[currentStep.value].completed,
      currentStep.value < stepsData.value.length - 1
    );
    if (
      stepsData.value[currentStep.value].completed &&
      currentStep.value < stepsData.value.length - 1
    ) {
      currentStep.value += 1;
    }
  };

  // Función para ir a un paso específico, solo permitir si ya fue completado
  const goToStep = (stepIndex) => {
    if (stepsData.value[stepIndex].completed || stepIndex === currentStep.value) {
      currentStep.value = stepIndex;
    }
  };

  // Función para retroceder al paso anterior
  const goToPreviousStep = () => {
    if (currentStep.value > 0 && currentStep.value !== stepsData.value.length - 1) {
      currentStep.value -= 1;
    }
  };

  // Función para marcar un paso como completado
  const setStepCompleted = (stepIndex) => {
    stepsData.value[stepIndex].completed = true;
  };

  // Función para avanzar al paso final y marcar los pasos anteriores como completados
  const goToFinalStep = () => {
    stepsData.value[0].completed = true;
    stepsData.value[1].completed = true;
    currentStep.value = 2;
  };

  // Restaurar el valor de currentStep desde localStorage cuando se monte el componente
  onMounted(() => {
    const savedStepsData = localStorage.getItem('stepsData');
    if (savedStepsData !== null) {
      stepsData.value = JSON.parse(savedStepsData);
    }

    const savedStep = localStorage.getItem('currentStep');
    if (savedStep !== null) {
      currentStep.value = parseInt(savedStep, 10);
    }
  });

  // Guardar el valor de stepsData en localStorage cada vez que cambie
  watch(
    stepsData,
    (newStepsData) => {
      localStorage.setItem('stepsData', JSON.stringify(newStepsData));
    },
    { deep: true }
  );

  watch(currentStep, (newStep) => {
    localStorage.setItem('currentStep', newStep.toString());
  });
</script>
<style scoped lang="scss"></style>
