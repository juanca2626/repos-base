<template>
  <div class="wizard-header">
    <h3 class="section-title">{{ title }}</h3>
    <p class="section-subtitle">({{ completed }} de {{ total }} completados)</p>
  </div>
</template>

<script setup lang="ts">
  interface Props {
    title: string;
    completed: number;
    total: number;
  }

  defineOptions({
    name: 'WizardHeaderComponent',
  });

  defineProps<Props>();
</script>

<style scoped lang="scss">
  .wizard-header {
    width: 100%;
    height: 68px;
    border-bottom: #babcbd 1px solid;
    margin-bottom: 20px;
    margin-top: 20px;

    .section-title {
      font-weight: 600;
      font-size: 16px !important;
      line-height: 28px;
      color: #2f353a;
      margin-bottom: 4px;
    }

    .section-subtitle {
      font-weight: 400;
      font-size: 16px !important;
      line-height: 24px;
      color: #7e8285;
      margin-bottom: 20px;
    }
  }
</style>
