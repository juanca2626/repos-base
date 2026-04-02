<template>
  <div class="compounds-sidebar-progress">
    <template v-if="!isSidebarCollapsed">
      <span class="progress-title">Estructura del compuesto</span>
      <span class="progress-text">{{ totalProgress }}% Completado</span>
    </template>
    <template v-else>
      <span class="progress-text">{{ totalProgress }}%</span>
    </template>
    <a-progress
      :percent="totalProgress"
      :show-info="false"
      :stroke-color="'#BD0D12'"
      :trail-color="'#f0f0f0'"
    />
  </div>
</template>

<script setup lang="ts">
  interface Props {
    totalProgress: number;
    isSidebarCollapsed: boolean;
  }

  withDefaults(defineProps<Props>(), {
    totalProgress: 0,
    isSidebarCollapsed: false,
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .compounds-sidebar-progress {
    display: flex;
    flex-direction: column;
    padding: 20px 20px 16px 20px;
    border-bottom: 1px solid $color-black-8;
  }

  .progress-title {
    font-size: 16px;
    line-height: 24px;
    font-weight: 500;
    color: $color-black-dark-2;
    margin: 0 0 4px 0;
  }

  .progress-text {
    font-size: 12px;
    line-height: 20px;
    font-weight: 600;
    color: $color-black;
    margin-bottom: 6px;
  }
</style>
