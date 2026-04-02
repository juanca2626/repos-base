<template>
  <a-alert type="error" show-icon class="custom-alert">
    <template #icon>
      <font-awesome-icon icon="fa-solid fa-circle-exclamation" />
    </template>
    <template #message>
      <span class="custom-alert-message"> Por favor, revisa los siguientes campos </span>
    </template>
    <template #description>
      <ul class="item-list">
        <li v-for="error in errors">
          {{ error }}
        </li>
      </ul>
    </template>
  </a-alert>
</template>
<script setup lang="ts">
  import type { PropType } from 'vue';

  defineProps({
    errors: {
      required: true,
      type: Array as PropType<string[]>,
    },
  });
</script>
<style scoped lang="scss">
  .custom-alert {
    :deep(.ant-alert-icon) {
      font-size: 16px;
      align-self: flex-start;
      margin-top: 4px;
    }

    .item-list {
      padding-left: 16px;
      font-size: 14px;
    }

    .custom-alert-message {
      font-weight: 600;
      font-size: 14px;
    }
  }
</style>
