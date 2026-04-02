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
          :key="item.ids"
          class="tab-item"
          :class="{ active: activeKey === item.ids }"
          @click="handleTabClick(item)"
        >
          {{ item.display_name }}
        </div>
      </div>
    </div>
    <a-dropdown :trigger="['click']">
      <a-button class="list-button">
        <font-awesome-icon :icon="['fas', 'ellipsis']" />
      </a-button>
      <template #overlay>
        <a-menu class="scrollable-menu custom-scrollbar" :selectedKeys="[activeKey]">
          <a-menu-item v-for="item in data" :key="item.ids" @click="handleTabClick(item)">
            {{ item.display_name }}
          </a-menu-item>
        </a-menu>
      </template>
    </a-dropdown>
    <button class="scroll-button right" @click="scrollRight" :disabled="!canScrollRight">
      <font-awesome-icon :icon="['fas', 'chevron-right']" />
    </button>
  </div>
</template>

<script setup lang="ts">
  import { useTouristTransportTabCities } from '@/modules/negotiations/supplier/land/tourist-transport/composables/useTouristTransportTabCities';

  const {
    data,
    isLoading,
    activeKey,
    tabsWrapper,
    scrollPosition,
    block,
    scrollLeft,
    scrollRight,
    handleTabClick,
    canScrollRight,
  } = useTouristTransportTabCities();
</script>

<style scoped lang="scss">
  .scrollable-tabs-container {
    display: flex;
    align-items: center;
    width: 100%;
    border: 1px solid #e8e8e8;
    background-color: white;
    padding: 10px;
    border-top-right-radius: 5px;
    border-top-left-radius: 5px;
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
    padding: 15px 20px;
    cursor: pointer;
    white-space: nowrap;
    font-size: 14px;
    position: relative;
  }

  .tab-item.active {
    color: #bd0d12;
    font-weight: 700;
    background-color: #f9f9f9;
    border-radius: 6px;
  }

  .tab-item.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3px;
  }

  .scroll-button {
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: 0 10px;
    color: #bd0d12;
  }

  .scroll-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    color: #999;
  }

  .list-button {
    margin-left: 10px;
    border: none;
    box-shadow: none;
  }

  .scrollable-menu {
    max-height: 300px;
    overflow-y: auto;
    border-radius: 0;
    padding: 0;
    padding-right: 5px; // Añadimos un poco de padding para evitar que el contenido toque la barra de scroll
  }

  :deep(.ant-dropdown-menu-item-selected) {
    background-color: #c83b3f !important;
    color: white !important;
    border-radius: 0 !important;
    font-weight: 600 !important;
  }

  :deep(.ant-dropdown-menu-item:hover) {
    background-color: #c83b3f !important;
    color: white !important;
    border-radius: 0 !important;
    font-weight: 600 !important;
  }

  :deep(.ant-dropdown-menu-item) {
    padding: 10px !important;
  }

  .skeleton-container {
    display: flex;
    gap: 8px;
    .ant-skeleton-element {
      width: 100%;
      padding: 12px;
    }
  }

  .custom-scrollbar {
    &::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }

    &::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    &::-webkit-scrollbar-thumb {
      background-color: #e4e5e6;
      border-radius: 10px;
    }

    &::-webkit-scrollbar-thumb:hover {
      background-color: #e4e5e6;
    }

    // Para Firefox
    scrollbar-width: thin;
    scrollbar-color: #e4e5e6 #ffffff;

    // Para Internet Explorer y Edge (versiones antiguas)
    -ms-overflow-style: -ms-autohiding-scrollbar;

    // Aseguramos que el scroll sea visible en todos los navegadores
    overflow-y: scroll;
  }
</style>
