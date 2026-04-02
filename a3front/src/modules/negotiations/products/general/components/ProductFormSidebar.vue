<template>
  <div class="container-sidebar">
    <div class="progress-sidebar">
      <span class="progress-title"> Registro del producto </span>
      <!-- <div class="container-progress-status">
        <span class="progress-status">10% Completado</span>
        <div>
          <div class="progress-bar-container mt-1">
            <div class="progress-bar" :style="{ width: 11 + '%' }"></div>
          </div>
        </div>
      </div> -->
    </div>

    <div class="sidebar-menu">
      <div class="container-title">
        <span class="title"> Información general </span>
      </div>

      <ul class="custom-menu">
        <li
          v-for="item in menuItems"
          :key="item.menuKey"
          :class="['menu-item', item.isActive ? 'is-active' : 'is-disabled']"
          @click="handleSelectMenuItem(item.menuKey)"
        >
          <font-awesome-icon
            :icon="['fas', 'circle-check']"
            :class="item.isComplete ? 'icon-complete' : 'icon-incomplete'"
          />
          <span class="item-title">{{ item.title }}</span>
          <font-awesome-icon :icon="['fas', 'chevron-right']" class="menu-arrow" />
        </li>
      </ul>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { useProductFormSidebar } from '@/modules/negotiations/products/general/composables/useProductFormSidebar';

  const { menuItems, handleSelectMenuItem } = useProductFormSidebar();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-sidebar {
    border-right: 1px solid $color-gray-ligth-4;
    min-height: calc(100vh - 167px);
  }

  .sidebar-menu {
    .container-title {
      display: flex;
      align-items: center;
      padding: 8px 15px 8px 12px;
      height: 42px;
    }

    .title {
      font-size: 14px;
      font-weight: 600;
    }

    .custom-menu {
      border-bottom: 1px solid $color-gray-ligth-4;
      list-style: none;
      padding: 0;
      margin: 0;

      .menu-item {
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 16px;
        cursor: pointer;

        &.is-active {
          border-left: 4px solid $color-black-dark;
          background: $color-gray-ice-2;
        }

        &.is-disabled {
          margin-left: 4px;
        }

        .icon-complete {
          color: $color-green-2 !important;
        }

        .icon-incomplete {
          color: $color-gray-soft-2 !important;
        }

        .item-title {
          margin-left: 8px;
          font-size: 12px;
          font-weight: 600;
          color: $color-black;
          flex-grow: 1;
        }

        .menu-arrow {
          font-size: 12px;
          color: $color-black;
          padding-right: 2px;
        }
      }
    }
  }

  .progress-sidebar {
    padding: 24px 16px 16px 16px;
    border-bottom: 1px solid $color-gray-ligth-4;

    .progress-title {
      font-size: 16px;
      color: $color-black-dark-2;
      font-weight: 600;
    }

    .container-progress-status {
      margin-top: 12px;
      margin-bottom: 1px;
    }

    .progress-status {
      font-size: 12px;
      font-weight: 600;
      color: $color-black;
    }

    .progress-bar-container {
      width: 100%;
      background-color: $color-blue-ultra-light-2;
      border-radius: 4px;
      overflow: hidden;
      height: 8px;

      .progress-bar {
        height: 100%;
        background-color: $color-blue;
        width: 0;
        transition: width 0.3s ease;
      }
    }
  }
</style>
