<template>
  <div class="scrollable-tabs-container">
    <div class="tabs-wrapper" ref="tabsWrapper">
      <div v-if="isLoading" class="skeleton-container">
        <a-skeleton-button
          v-for="i in 12"
          :key="i"
          class="skeleton-item"
          :loading="isLoading"
          active
          size="default"
          shape="default"
          block
        />
      </div>
      <div class="tabs-content" v-else>
        <div class="tabs-list" :style="{ transform: `translateX(-${scrollPosition}px)` }">
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useTabOperationLocations } from '@/modules/negotiations/supplier/register/configuration-module/composables/useTabOperationLocations';
  import type { OperationLocationProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const props = defineProps<OperationLocationProps>();

  const emit = defineEmits(['handleTabClick']);

  const { isLoading, activeKey, tabsWrapper, scrollPosition, handleTabClick } =
    useTabOperationLocations(props, emit);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .scrollable-tabs-container {
    display: flex;
    align-items: center;
    width: 100%;
    background-color: $color-white;
  }

  .tabs-wrapper {
    overflow: hidden;
    flex-grow: 1;
    border-bottom: 1px solid $color-black-8;
    padding-bottom: 1px;
  }

  .tabs-content {
    display: flex;
    justify-content: space-between;
  }

  .tabs-list {
    display: flex;
    transition: transform 0.3s ease;
    margin-left: 20px;
  }

  .tab-item {
    padding: 15px 20px;
    cursor: pointer;
    white-space: nowrap;
    font-size: 14px;
    position: relative;
    transition: color 0.3s ease-in-out;
  }

  .tab-item.active {
    color: $color-primary-strong;
    font-weight: 700;
  }

  .tab-item.active::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -1px;
    width: 100%;
    height: 3px;
    background-color: $color-primary-strong;
  }

  .list-button {
    margin-left: 10px;
    border: none;
    box-shadow: none;
    margin-top: 10px;
    position: absolute; /* Esto posicionará el botón de dropdown fuera del flujo normal */
    right: 0; // Alinear a la derecha
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
