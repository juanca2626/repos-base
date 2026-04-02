<template>
  <div class="content-card-header">
    <span class="content-card-header-bullet" />
    <span class="content-card-header-title">{{ title }}</span>
    <span
      v-if="status === 'PENDING'"
      class="content-card-header-status-pill content-card-header-status-pill--pending"
    >
      <ClockCircleOutlined class="content-card-header-status-icon" />
      En espera de revisión
    </span>
    <span
      v-else-if="status === 'SENT_FOR_REVIEW'"
      class="content-card-header-status-pill content-card-header-status-pill--sent"
    >
      <CheckCircleOutlined class="content-card-header-status-icon" />
      Enviado a revisión
    </span>
  </div>
</template>

<script setup lang="ts">
  import { ClockCircleOutlined, CheckCircleOutlined } from '@ant-design/icons-vue';

  interface Props {
    title: string;
    status?: string;
  }

  withDefaults(defineProps<Props>(), {
    status: undefined,
  });
</script>

<style scoped lang="scss">
  .content-card-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;

    .content-card-header-status-pill {
      margin-left: auto;
    }
  }

  .content-card-header-bullet {
    display: inline-block;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background-color: #7e8285;
    flex-shrink: 0;
  }

  .content-card-header-title {
    font-size: 14px;
    font-weight: 600;
    color: #7e8285;
  }

  .content-card-header-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;

    &--pending {
      background: #fff2dd;
      color: #f97800;

      .content-card-header-status-icon {
        color: #f97800;
      }
    }

    &--sent {
      background: #e6f4ff;
      color: #1890ff;

      .content-card-header-status-icon {
        color: #1890ff;
      }
    }
  }

  .content-card-header-status-icon {
    font-size: 12px;
  }
</style>
