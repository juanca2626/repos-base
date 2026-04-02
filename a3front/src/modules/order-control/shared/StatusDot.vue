<template>
  <div v-if="minimal" class="status-content-minimal">
    <div class="status-indicator">
      <div class="status-dot" :class="dotColor"></div>
    </div>
  </div>

  <div v-else class="status-wrapper">
    <div class="status-content">
      <div class="status-indicator">
        <div class="status-dot" :class="dotColor"></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';

  type Status = 'success' | 'warning' | 'danger' | 'pending' | 'info';

  const props = defineProps<{
    status: Status;
    minimal?: boolean;
  }>();

  const dotColor = computed(() => {
    switch (props.status) {
      case 'success':
        return 'dot-success';
      case 'danger':
        return 'dot-error';
      case 'warning':
        return 'dot-warning';
      case 'pending':
        return 'dot-warning';
      case 'info':
        return 'dot-info';
      default:
        return 'dot-warning';
    }
  });
</script>

<style scoped>
  .status-wrapper {
    width: 100%;
    height: 100%;
    background: #fafafa;
    padding: 7.5px 10px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
  }

  .status-content {
    flex: 1 1 0;
    align-self: stretch;
    padding: 14px 10px;
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }

  /* Clase simple para el modo minimal */
  .status-content-minimal {
    display: inline-flex;
    justify-content: center;
    align-items: center;
  }

  .status-indicator {
    width: 20px; /* Reducido un poco para versiones si deseas, o mantener 20px */
    height: 20px;
    position: relative;
  }

  .status-dot {
    width: 19.5px;
    height: 19px;
    border-radius: 9999px;
    position: absolute;
    left: 4px;
  }

  .dot-success {
    background-color: #1ed790;
  }
  .dot-error {
    background-color: #d80404;
  }
  .dot-processing {
    background-color: #5c5ab4;
  }
  .dot-warning {
    background-color: #fec109;
  }
  .dot-info {
    background-color: #51a5fe;
  }
  .dot-default {
    background-color: #ccc;
  }
  .pending {
    background-color: #fec109;
  }
</style>
