<template>
  <a-drawer
    :open="showDrawerForm"
    :width="526"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
    class="product-configuration-form-drawer"
  >
    <template #title>
      <ConfigurationDrawerHeader :title="title" />
    </template>

    <a-spin :spinning="isLoading">
      <slot name="steps" />
      <div class="form-container form-container-is-general">
        <slot />
      </div>
    </a-spin>

    <template #footer>
      <ConfigurationDrawerFooter
        :cancel-button-text="cancelButtonText"
        :next-button-text="nextButtonText"
        :is-next-button-disabled="isNextButtonDisabled"
        @cancel="handleCancel"
        @next="handleNext"
      />
    </template>
  </a-drawer>
</template>

<script setup lang="ts">
  import ConfigurationDrawerHeader from './ConfigurationDrawerHeader.vue';
  import ConfigurationDrawerFooter from './ConfigurationDrawerFooter.vue';
  import type { BaseDrawerProps } from './interfaces';

  interface Props extends BaseDrawerProps {
    title: string;
    isLoading: boolean;
    stepNumber: number;
    cancelButtonText: string;
    nextButtonText: string;
    isNextButtonDisabled: boolean;
  }

  defineProps<Props>();

  const emit = defineEmits<{
    close: [];
    cancel: [];
    next: [];
  }>();

  const handleClose = () => {
    emit('close');
  };

  const handleCancel = () => {
    emit('cancel');
  };

  const handleNext = () => {
    emit('next');
  };
</script>

<style lang="scss">
  @import '@/scss/components/negotiations/_productComponent.scss';
</style>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';
  @import '@/scss/components/negotiations/_productComponentScoped.scss';
</style>
