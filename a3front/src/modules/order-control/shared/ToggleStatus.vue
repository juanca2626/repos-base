<template>
  <div class="toggle-wrapper">
    <div :class="['status-badge', status ? 'active' : 'inactive']">
      {{ status ? 'Activa' : 'Inactiva' }}
    </div>

    <a-switch
      :checked="status"
      @change="emit('update:status', $event)"
      :disabled="disabled"
      :class="['custom-toggle', status ? 'active' : 'inactive']"
    />
  </div>
</template>

<script setup lang="ts">
  defineProps<{
    status: boolean;
    disabled?: boolean;
  }>();

  const emit = defineEmits<{
    (e: 'update:status', value: boolean): void;
  }>();
</script>

<style scoped>
  .toggle-wrapper {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    background: #fafafa;
    padding: 13px 10px;
    width: 100%;
    height: 100%;
  }

  .status-badge {
    padding: 1px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    line-height: 17px;
    letter-spacing: 0.15px;
  }

  .status-badge.active {
    background: #dfffe9;
    outline: 1px solid #1ed790;
    color: #44b089;
  }

  .status-badge.inactive {
    background: #ffe4e4;
    outline: 1px solid #d80404 !important;
    color: #c13a3a;
  }

  .custom-toggle {
    background-color: #1ed790 !important;
    border-radius: 100px;
    height: 19px;
    min-width: 38px;
  }

  .custom-toggle .ant-switch-handle {
    width: 14px;
    height: 13px;
    top: 3px;
    left: 3px;
    background: white;
    border-radius: 9999px;
  }

  .custom-toggle.ant-switch-checked .ant-switch-handle {
    left: calc(100% - 17px);
  }

  .custom-toggle .ant-switch-inner {
    display: none;
  }

  .custom-toggle.inactive {
    background-color: #d80404 !important;
  }

  .custom-toggle.inactive .ant-switch-handle {
    background: white;
  }

  .custom-toggle.inactive.ant-switch-checked .ant-switch-handle {
    left: calc(100% - 17px);
  }
</style>
