<template>
  <div class="drawer-footer">
    <button class="btn-cancel" @click="$emit('cancel')">Cancelar</button>
    <button
      class="btn-next"
      :class="{ 'btn-disabled': disabled }"
      :disabled="disabled"
      @click="$emit('next')"
    >
      {{ nextText }}
    </button>
  </div>
</template>

<script setup lang="ts">
  withDefaults(
    defineProps<{
      nextText?: string;
      disabled?: boolean;
    }>(),
    {
      nextText: 'Siguiente',
      disabled: false,
    }
  );

  defineEmits(['cancel', 'next']);
</script>

<style lang="scss" scoped>
  .drawer-footer {
    height: 90px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 16px;
    background: #ffffff;
    border-top: 1px solid #e9e9e9;
    padding: 20px;

    button {
      width: 140px;
      height: 50px;
      border-radius: 4px;
      font-family: 'Inter', sans-serif;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: all 0.3s;
    }

    .btn-cancel {
      background: #ffffff;
      border: 1px solid #2f353a;
      color: #2f353a;

      &:hover {
        background: #f5f5f5;
      }
    }

    .btn-next {
      background: #bd0d12;
      border: 1px solid #bd0d12;
      color: #ffffff;

      &:hover {
        background: #a00b0f;
        border-color: #a00b0f;
      }

      &.btn-disabled {
        background: #e9e9e9 !important;
        border-color: #e9e9e9 !important;
        color: #babcbd !important;
        cursor: not-allowed;
      }
    }
  }
</style>
