<template>
  <div class="navigation-container">
    <a-button
      class="back-button"
      @click="$emit('back')"
      :disabled="currentStepIndex === 0 || loading"
      v-if="currentStepIndex > 0"
    >
      Atrás
    </a-button>

    <a-button
      class="save-button"
      @click="$emit('next')"
      :loading="loading"
      :disabled="!isStepValid"
    >
      {{ isLastStep ? 'Finalizar' : nextLabel }}
    </a-button>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';

  interface Props {
    currentStepIndex: number;
    totalSteps: number;
    loading: boolean;
    isStepValid: boolean;
    nextLabel: string;
  }

  const props = defineProps<Props>();

  defineEmits(['next', 'back']);

  const isLastStep = computed(() => {
    return props.currentStepIndex === props.totalSteps - 1;
  });
</script>

<style scoped>
  .navigation-container {
    display: flex;
    gap: 16px;
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
</style>
