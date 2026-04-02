<template>
  <div class="stepper-container">
    <div class="stepper-row">
      <div class="step-item" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
        <StepCompletedIcon v-if="currentStep > 1" />
        <SettingsStepIcon v-else />
        <span class="step-text">Configuración</span>
      </div>
      <div class="step-separator" :class="{ completed: currentStep > 1 }">
        <StepSeparatorIcon />
      </div>
      <div class="step-item" :class="{ active: currentStep === 2 }">
        <VehicleStepIcon />
        <span class="step-text">Unidad</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import {
    SettingsStepIcon,
    VehicleStepIcon,
    StepSeparatorIcon,
    StepCompletedIcon,
  } from '../icons';

  defineProps<{
    currentStep: number;
  }>();
</script>

<style lang="scss" scoped>
  .stepper-container {
    width: 100%;
    height: 116px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 25px 100px 40px 100px;
    background: #ffffff;
    border-bottom: 1px solid #e9e9e9;
  }

  .stepper-row {
    display: flex;
    align-items: flex-start;
    justify-content: center;
  }

  .step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 0 0 auto;
    opacity: 0.5;

    &.active,
    &.completed {
      opacity: 1;
    }
  }

  .step-separator {
    margin-top: 18.5px;
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    color: #e9e9e9;

    &.completed {
      color: #1284ed;
    }
  }

  .step-text {
    position: absolute;
    top: 50px;
    white-space: nowrap;

    background:
      linear-gradient(0deg, #575b5f, #575b5f),
      linear-gradient(0deg, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 14px;
    line-height: 20px;
    text-align: center;

    &.text-completed {
      background: #1284ed;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  }
</style>
