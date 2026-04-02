<template>
  <div class="stepper-container">
    <div class="custom-stepper">
      <!-- Línea fondo -->
      <div class="stepper-line-background"></div>

      <!-- Línea progreso -->
      <div class="stepper-line-progress" :style="progressLineStyle"></div>

      <div
        v-for="(step, index) in steps"
        :key="step.key"
        class="stepper-step"
        :class="{
          active: index === currentStepIndex,
          completed: index < currentStepIndex,
        }"
      >
        <div class="step-icon-wrapper">
          <!-- ✅ SOLO CAMBIA CUANDO ESTÁ COMPLETADO -->
          <template v-if="index < currentStepIndex">
            <svg width="48" height="48" viewBox="0 0 48 48">
              <!-- Outer ring -->
              <circle cx="24" cy="24" r="22" fill="white" stroke="#1284ED" stroke-width="3" />

              <!-- Inner circle -->
              <circle cx="24" cy="24" r="14" fill="#1284ED" />

              <!-- Check -->
              <path
                d="M19 24L22.5 27.5L30 20"
                stroke="white"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </template>

          <!-- 🔵 TODO LO DEMÁS SE QUEDA IGUAL -->
          <template v-else>
            <svg class="step-circle-svg" width="40" height="40" viewBox="0 0 40 40">
              <rect
                x="1"
                y="1"
                width="38"
                height="38"
                rx="19"
                fill="white"
                :stroke="index === currentStepIndex ? '#575B5F' : '#BABCBD'"
                stroke-width="2"
              />
            </svg>

            <div v-if="step.icon" class="step-icon-overlay">
              <component
                :is="step.icon"
                :color="index === currentStepIndex ? '#575B5F' : '#BABCBD'"
              />
            </div>
          </template>
        </div>

        <span class="step-title">
          {{ step.label }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';

  interface Step {
    key: string;
    label: string;
    icon?: any;
  }

  interface Props {
    steps: Step[];
    currentStepIndex: number;
  }

  const props = defineProps<Props>();

  // const emit = defineEmits<{
  //   (e: 'step-click', stepIndex: number): void;
  // }>();

  // const handleStepClick = (index: number) => {
  // if (index <= props.currentStepIndex) {
  //   emit('step-click', index);
  // }
  // };

  const progressLineStyle = computed(() => {
    const totalSteps = props.steps.length;
    if (totalSteps <= 1) return { width: '0%' };
    const progress = props.currentStepIndex / (totalSteps - 1);
    const progressPercent = progress * 100;
    const offsetPx = progress * 120;
    return {
      width: `calc(${progressPercent}% - ${offsetPx}px)`,
    };
  });
</script>

<style scoped lang="scss">
  .stepper-container {
    margin-bottom: 32px;
    display: flex;
    justify-content: center;
  }

  .custom-stepper {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    width: 100%;
    max-width: 1000px;
    position: relative;
  }

  .stepper-line-background {
    position: absolute;
    top: 19px;
    left: 60px;
    right: 60px;
    height: 3px;
    background-color: #d9d9d9;
    z-index: 0;
  }

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
    z-index: 1;
    cursor: pointer;
  }

  .step-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  .step-icon-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
  }

  .step-title {
    margin-top: 8px;
    font-size: 14px;
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
</style>
