<template>
  <button :class="['view-type-button', { active: isActive }]" @click="$emit('click')">
    {{ label }}
  </button>
</template>

<script setup lang="ts">
  defineProps<{
    label: string;
    isActive: boolean;
  }>();

  defineEmits<{
    (e: 'click'): void;
  }>();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .view-type-button {
    padding: 8px 16px;
    border: 1px solid #d9d9d9;
    background-color: #ffffff;
    color: rgba(0, 0, 0, 0.88);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    outline: none;

    &:first-child {
      border-top-left-radius: 6px;
      border-bottom-left-radius: 6px;
      border-right: none;
    }

    &:last-child {
      border-top-right-radius: 6px;
      border-bottom-right-radius: 6px;
    }

    &:hover {
      color: #4096ff;
      border-color: #4096ff;
    }

    &.active {
      background-color: #575b5f;
      color: #ffffff;
      border: 2px solid #575b5f;
      z-index: 1;
      position: relative;

      &:hover {
        background-color: #575b5f;
        border-color: #575b5f;
      }
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .view-type-button {
      padding: 6px 12px;
      font-size: 13px;
    }
  }
</style>
