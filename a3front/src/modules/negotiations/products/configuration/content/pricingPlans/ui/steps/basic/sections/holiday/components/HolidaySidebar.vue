<template>
  <div class="sidebar">
    <div class="holiday-header mb-3">
      <span class="title">Tarifas festivas {{ selectedYear }}</span>
      <a-dropdown trigger="click" placement="bottomRight">
        <PlusCircleIcon class="cursor-pointer" />
        <template #overlay>
          <a-menu @click="handleMenuClick">
            <a-menu-item key="CUSTOM">Nuevo grupo</a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>

    <HolidayCategoryCard
      v-for="category in categories"
      :key="category.id ?? category.uuid"
      :category="category"
      :selected="(category.id ?? category.uuid) === selectedCategoryId"
      :editMode="editMode"
      :editingItem="editingItem"
      @selectCategory="$emit('selectCategory', category.id ?? category.uuid)"
      @selectItem="$emit('selectItem', $event)"
      @toggleEdit="$emit('toggleEdit', $event)"
      @saveItem="$emit('saveItem', $event)"
      @toggleCategoryCheck="$emit('toggleCategoryCheck', $event)"
      @moveItemToCategory="$emit('moveItemToCategory', $event)"
    />
  </div>
</template>

<script setup lang="ts">
  import HolidayCategoryCard from './HolidayCategoryCard.vue';
  import { PlusCircleIcon } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  defineProps<{
    categories: any[];
    selectedCategoryId: string | null;
    editMode: boolean;
    editingItem: string | number | null;
    selectedYear: number | null;
  }>();

  const emit = defineEmits([
    'toggleCategoryCheck',
    'selectCategory',
    'selectItem',
    'toggleEdit',
    'saveItem',
    'addCategory',
    'moveItemToCategory',
  ]);

  const handleMenuClick = (info: { key: any }) => {
    emit('addCategory', info.key);
  };
</script>

<style scoped>
  .sidebar {
    max-width: 330px;
    border-right: 1px solid #eee;
    padding-right: 16px;
    padding-top: 16px;
    padding-left: 16px;
    background-color: #f9f9f9;
    user-select: none;
  }

  .holiday-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .holiday-header .title {
    font-size: 14px;
    font-weight: 700;
    color: #7e8285;
  }
</style>
