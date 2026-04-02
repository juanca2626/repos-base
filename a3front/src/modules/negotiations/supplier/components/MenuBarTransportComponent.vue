<template>
  <a-row class="menu-bar">
    <a-col>
      <a-menu
        mode="horizontal"
        :selectedKeys="[currentMode]"
        @select="handleSelect"
        class="transport-mode-menu"
      >
        <a-menu-item key="terrestrial">
          <template #icon>
            <font-awesome-icon :icon="['fas', 'bus']" />
          </template>
          <span>Terrestre</span>
        </a-menu-item>
        <a-menu-item key="fluvial">
          <template #icon>
            <font-awesome-icon :icon="['fas', 'ship']" />
          </template>
          Fluvial
        </a-menu-item>
        <a-menu-item key="aerial">
          <template #icon>
            <font-awesome-icon :icon="['fas', 'plane']" />
          </template>
          Aéreo
        </a-menu-item>
      </a-menu>
    </a-col>
  </a-row>
</template>
<script setup lang="ts">
  import { ref } from 'vue';

  const currentMode = ref('terrestrial');

  const handleSelect = ({ key }) => {
    currentMode.value = key;
  };
</script>

<style scoped lang="scss">
  .menu-bar {
    background-color: #f9f9f9;
  }
  .transport-mode-menu {
    display: flex;
    justify-content: center;
    background-color: #f9f9f9;
    border-bottom: none;
    color: #7e8285;
  }

  .transport-mode-menu :deep(.ant-menu-item) {
    width: 180px;
    text-align: center;
    margin: 0 10px;
  }

  .transport-mode-menu :deep(.ant-menu-item-selected) {
    border-bottom: 2px solid #cf1322;
    font-weight: 600;
    font-size: 16px;
  }

  .transport-mode-menu :deep(.ant-menu-item-selected::after) {
    border-bottom: none;
  }

  .transport-mode-menu :deep(.ant-menu-item:hover) {
    color: #cf1322;
    width: 180px;
  }

  .transport-mode-menu :deep(.ant-menu-item-icon) {
    font-size: 16px;
  }
</style>
