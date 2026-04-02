<template>
  <div class="scrollable-tabs-container">
    <button class="scroll-button left" @click="scrollLeft" :disabled="scrollPosition <= 0">
      <font-awesome-icon :icon="['fas', 'chevron-left']" />
    </button>
    <div class="tabs-wrapper" ref="tabsWrapper">
      <div v-if="isLoading" class="skeleton-container">
        <a-skeleton-button
          v-for="i in 12"
          :key="i"
          class="skeleton-item"
          :loading="isLoading"
          active
          size="default"
          shape="square"
          :block="block"
        />
      </div>

      <div class="tabs-list" :style="{ transform: `translateX(-${scrollPosition}px)` }" v-else>
        <div
          v-for="item in data"
          :key="item.id"
          class="tab-item"
          :class="{ active: activeKey === item.id }"
          @click="handleTabClick(item.id)"
        >
          <font-awesome-icon :icon="['fas', 'circle-check']" class="check-icon" />
          {{ item.name }}
        </div>
      </div>
    </div>
    <button
      class="scroll-button right"
      @click="scrollRight"
      :disabled="scrollPosition >= maxScroll"
    >
      <font-awesome-icon :icon="['fas', 'chevron-right']" />
    </button>
  </div>
</template>
<script setup lang="ts">
  import { useSupplierScrollableTab } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierScrollableTab';
  const {
    data,
    isLoading,
    activeKey,
    tabsWrapper,
    scrollPosition,
    maxScroll,
    block,
    scrollLeft,
    scrollRight,
    handleTabClick,
  } = useSupplierScrollableTab();
</script>

<style scoped lang="scss">
  .scrollable-tabs-container {
    display: flex;
    align-items: center;
    width: 100%;
    border-bottom: 1px solid #e8e8e8;
    background-color: white;
  }

  .tabs-wrapper {
    overflow: hidden;
    flex-grow: 1;
  }

  .tabs-list {
    display: flex;
    transition: transform 0.3s ease;
  }

  .tab-item {
    padding: 20px 20px;
    cursor: pointer;
    white-space: nowrap;
    font-size: 14px;
    position: relative;
  }

  .tab-item.active {
    color: #bd0d12;
    font-weight: 600;
  }

  .tab-item.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #bd0d12;
  }

  .check-icon {
    color: #07df81;
    margin-right: 5px;
    font-weight: bold;
  }

  .scroll-button {
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: 0 10px;
    color: #999;
  }

  .scroll-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .skeleton-container {
    display: flex;
    gap: 8px; /* Espacio entre cada skeleton */
    .ant-skeleton-element {
      width: 100%; /* O el ancho que prefieras */
      padding: 12px;
    }
  }
</style>
