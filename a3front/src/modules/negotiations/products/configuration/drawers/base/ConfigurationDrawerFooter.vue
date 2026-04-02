<template>
  <div class="buttons-container">
    <a-button type="link" class="button-cancel-white cancel-button" @click="handleCancel">
      {{ cancelButtonText }}
    </a-button>
    <a-button
      class="button-action-primary-strong next-button"
      :disabled="isNextButtonDisabled"
      @click="handleNext"
    >
      {{ nextButtonText }}
    </a-button>
  </div>
</template>

<script setup lang="ts">
  defineProps<{
    cancelButtonText: string;
    nextButtonText: string;
    isNextButtonDisabled: boolean;
  }>();

  const emit = defineEmits<{
    cancel: [];
    next: [];
  }>();

  const handleCancel = () => {
    emit('cancel');
  };

  const handleNext = () => {
    emit('next');
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';
</style>
