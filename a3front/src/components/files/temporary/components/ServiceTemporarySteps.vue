<template>
  <step-component :step="currentStep" :steps="stepsData" class="p-5" @stepClicked="goToStep" />
  <template v-if="isCurrentStep(0)">
    <ServiceTemporaryCreate
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(0)"
      @goToFinalStep="goToFinalStep"
    />
  </template>
  <template v-else-if="isCurrentStep(1)">
    <ServiceTemporaryCommunications
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(1)"
      @goToFinalStep="goToFinalStep"
    />
  </template>
  <template v-else-if="isCurrentStep(2)">
    <ServiceTemporaryFinished
      @nextStep="goToNextStep"
      @backStep="goToPreviousStep"
      @complete="setStepCompleted(2)"
    />
  </template>
</template>
<script setup lang="ts">
  import StepComponent from '@/components/global/StepComponent.vue';
  import { onMounted, ref, watch } from 'vue';
  import ServiceTemporaryCreate from '@/components/files/temporary/components/ServiceTemporaryCreate.vue';
  import ServiceTemporaryCommunications from '@/components/files/temporary/components/ServiceTemporaryCommunications.vue';
  import ServiceTemporaryFinished from '@/components/files/temporary/components/ServiceTemporaryFinished.vue';

  const currentStep = ref(0);

  const stepsData = ref([
    { title: 'En proceso', description: 'Datos para servicio temporal', completed: false },
    { title: 'En espera', description: 'Comunicaciónes', completed: false },
    { title: 'En espera', description: 'Servcio temporal creado', completed: false },
  ]);

  const isCurrentStep = (step) => currentStep.value === step;

  // Función para avanzar al siguiente paso
  const goToNextStep = () => {
    console.log('goToNextStep called in ServiceTemporarySteps');
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
