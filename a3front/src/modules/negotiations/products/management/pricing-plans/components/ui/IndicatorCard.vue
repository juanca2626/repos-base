<template>
  <div :class="['indicator-card', variant]">
    <div class="icon-wrapper">
      <font-awesome-icon :icon="iconMap[icon]" />
    </div>

    <div class="content">
      <span class="value">{{ formattedValue }}</span>
      <span class="label">{{ label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';

  interface Props {
    label: string;
    value: number | string;
    variant?: 'success' | 'warning' | 'info';
    icon?: 'shield' | 'check';
  }

  const props = withDefaults(defineProps<Props>(), {
    variant: 'info',
    icon: 'shield',
  });

  const formattedValue = computed(() =>
    typeof props.value === 'number' ? props.value.toString().padStart(2, '0') : props.value
  );

  const iconMap = {
    shield: ['fas', 'shield'],
    check: ['fas', 'circle-check'],
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .indicator-card {
    display: flex;
    align-items: center;
    gap: 12px;

    height: 64px;
    padding: 0 20px;

    border-radius: 6px;
    border: 1px solid;

    background: $color-white;

    .icon-wrapper {
      width: 30px;
      height: 30px;
      border-radius: 50%;

      display: flex;
      align-items: center;
      justify-content: center;

      font-size: 16px;
    }

    .content {
      display: flex;
      align-items: center;
      gap: 6px;

      .value {
        font-weight: 700;
        font-size: 15px;
        color: $color-black-3;
      }

      .label {
        font-weight: 700;
        font-size: 15px;
        color: $color-black-3;
      }
    }
  }

  .success {
    border-color: #00a15b;

    .icon-wrapper {
      background: rgba(#00a15b, 0.12);
      color: #00a15b;
    }
  }

  .warning {
    border-color: #e4b804;

    .icon-wrapper {
      background: #fffbdb;
      color: #ffcc00;
    }
  }

  .info {
    border-color: $color-primary;

    .icon-wrapper {
      background: rgba($color-primary, 0.12);
      color: $color-primary;
    }
  }
</style>
