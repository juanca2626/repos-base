<template>
  <div class="list-items-form">
    <ul>
      <li v-for="(item, index) in items" :key="index">
        <div class="container-item">
          <div class="title-item">{{ item.title }}</div>
          <div class="value-item">{{ item.value }}</div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
  import { defineProps, type PropType } from 'vue';
  import type { ListItemGlobal } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

  defineOptions({
    name: 'ListItemsGlobalComponent',
  });

  defineProps({
    items: {
      type: Array as PropType<ListItemGlobal[]>,
      required: true,
      default: () => [],
    },
  });
</script>

<style scoped lang="scss">
  .list-items-form {
    .container-item {
      display: flex;
      gap: 0.5rem;
    }

    .title-item {
      font-weight: 600;
      font-size: 14px;
      line-height: 24px;
      color: #7e8285;
    }

    .value-item {
      font-weight: 400;
      font-size: 14px;
      line-height: 24px;
      color: #7e8285;
    }
  }
</style>
