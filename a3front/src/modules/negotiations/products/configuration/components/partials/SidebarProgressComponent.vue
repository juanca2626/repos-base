<template>
  <div class="header">
    <span v-if="!isSidebarCollapsed" class="progress-title">
      {{ progressTitle }}
    </span>
    <span v-if="!isSidebarCollapsed" class="progress-text"
      >{{ totalProgress || 0 }}% Completado</span
    >
    <span v-else class="progress-text">{{ totalProgress || 0 }}%</span>
    <a-progress
      :percent="totalProgress || 0"
      :show-info="false"
      :stroke-color="'#1890ff'"
      :trail-color="'#f0f0f0'"
    />
  </div>
</template>
<script setup lang="ts">
  import { computed } from 'vue';
  import { storeToRefs } from 'pinia';
  import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
  import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

  interface Props {
    isSidebarCollapsed: boolean;
  }

  withDefaults(defineProps<Props>(), {
    isSidebarCollapsed: false,
  });

  const { totalProgress } = storeToRefs(useNavigationStore());

  const { isServiceTypeMultiDays } = useSelectedServiceType();

  const progressTitle = computed(() => {
    return isServiceTypeMultiDays.value ? 'Creación del programa' : 'Creación en la ciudad';
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .header {
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
  }
</style>
