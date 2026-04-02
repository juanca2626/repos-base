<template>
  <div class="cost-cell">
    <span>{{ currency }} {{ cost }}</span>

    <font-awesome-icon
      v-if="showWarning"
      :icon="['fas', 'triangle-exclamation']"
      class="warning-icon"
    />
  </div>
</template>

<script setup lang="ts">
  interface Props {
    cost?: number;
    currency?: string;
  }

  const props = defineProps<Props>();

  const showWarning = props.cost !== undefined && props.cost < 30;
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .cost-cell {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .warning-icon {
    color: $color-warning;
  }
</style>
