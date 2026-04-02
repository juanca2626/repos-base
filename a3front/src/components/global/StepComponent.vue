<template>
  <a-steps :current="props.step" @change="handleStepClick">
    <a-step
      v-for="(step, index) in stepTitles"
      :key="index"
      :title="step.title"
      :description="step.description"
      :disabled="isStepDisabled(index)"
    />
  </a-steps>
</template>

<script setup>
  import { defineProps, defineEmits, computed } from 'vue';

  // Definir las props que recibe el componente
  const props = defineProps({
    step: {
      type: Number,
      required: true,
    },
    steps: {
      type: Array,
      required: true,
      default: () => [],
    },
  });

  // Definir el evento que emitiremos
  const emit = defineEmits(['stepClicked']);

  // Función para manejar el clic en un paso
  const handleStepClick = (stepIndex) => {
    if (isStepDisabled(stepIndex)) return;
    emit('stepClicked', stepIndex);
  };

  const stepTitles = computed(() => {
    return props.steps.map((step, index) => ({
      title: index < props.step ? 'Finalizado' : step.title,
      description: step.description,
      completed: step.completed ?? false,
    }));
  });

  const isStepDisabled = (index) => {
    if (props.step === props.steps.length - 1) {
      // Si estás en el último paso, deshabilita todos los pasos anteriores
      return index < props.step;
    }
    // Deshabilitar pasos incompletos que están después del paso actual
    return !props.steps[index].completed && index > props.step;
  };
</script>
