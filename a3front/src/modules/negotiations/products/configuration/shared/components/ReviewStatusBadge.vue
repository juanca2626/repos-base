<template>
  <div class="review-status-badge" :class="badgeClass">
    <component :is="iconComponent" class="badge-icon" />
    <span class="badge-text">{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import IconCircleCheck from '@/modules/negotiations/products/configuration/icons/IconCircleCheck.vue';
  import IconClockCircleOutlined from '@/modules/negotiations/products/configuration/icons/IconClockCircleOutlined.vue';

  interface Props {
    status: 'reviewed' | 'pending';
  }

  const props = defineProps<Props>();

  const text = computed(() => {
    return props.status === 'reviewed' ? 'Revisado' : 'En espera de revisión';
  });

  const badgeClass = computed(() => {
    return `badge-${props.status}`;
  });

  const iconComponent = computed(() => {
    return props.status === 'reviewed' ? IconCircleCheck : IconClockCircleOutlined;
  });
</script>

<style scoped lang="scss">
  .review-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
  }

  .badge-icon {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
  }

  .badge-text {
    white-space: nowrap;
  }

  .badge-reviewed {
    background-color: #dfffe9;
    color: #00a15b;

    .badge-icon {
      color: #00a15b;
    }
  }

  .badge-pending {
    background-color: #fff2dd;
    color: #f97800;

    .badge-icon {
      color: #f97800;
    }
  }
</style>
