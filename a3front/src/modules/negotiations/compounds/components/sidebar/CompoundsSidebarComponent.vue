<template>
  <div class="compounds-sidebar-wrapper">
    <div class="compounds-sidebar" :class="{ collapsed: isSidebarCollapsed }">
      <!-- Barra de progreso -->
      <CompoundsSidebarProgressComponent
        :total-progress="totalProgress"
        :is-sidebar-collapsed="isSidebarCollapsed"
      />

      <!-- Lista de secciones (top-level nodes) -->
      <div class="sidebar-menu">
        <div
          v-for="section in sections"
          :key="section.code"
          :class="['sidebar-item', { active: section.active, disabled: section.disabled }]"
          @click="!section.disabled && handleSelectSection(section.code)"
        >
          <!-- MODO COLAPSADO -->
          <template v-if="isSidebarCollapsed">
            <!-- Sección con subitems → ícono bus -->
            <CarOutlined v-if="section.source === 'section'" class="bus-icon" />
            <!-- Ítem plano → check circle -->
            <CheckCircleFilled v-else-if="section.completed" class="check-icon completed" />
            <CheckCircleOutlined v-else class="check-icon" />
          </template>

          <!-- MODO EXPANDIDO -->
          <template v-else>
            <CheckCircleFilled v-if="section.completed" class="check-icon completed" />
            <CheckCircleOutlined v-else class="check-icon" />
            <span class="item-label">{{ section.title }}</span>
            <RightOutlined class="arrow-icon" />
          </template>
        </div>
      </div>

      <!-- Botón colapsar — flotante, posición original -->
      <button class="sidebar-toggle-btn" @click="toggleSidebar">
        <LeftOutlined v-if="!isSidebarCollapsed" />
        <RightOutlined v-else />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import {
    CheckCircleOutlined,
    CheckCircleFilled,
    RightOutlined,
    LeftOutlined,
    CarOutlined,
  } from '@ant-design/icons-vue';
  import { useCompoundsSidebar } from '@/modules/negotiations/compounds/composables/use-compounds-sidebar.composable';
  import CompoundsSidebarProgressComponent from './partials/CompoundsSidebarProgressComponent.vue';

  const { sections, isSidebarCollapsed, totalProgress, handleSelectSection, toggleSidebar } =
    useCompoundsSidebar();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .compounds-sidebar-wrapper {
    position: relative;
    flex-shrink: 0;
    height: 100%;
  }

  .compounds-sidebar {
    width: 220px;
    background-color: $color-white;
    border-right: 1px solid $color-black-8;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: calc(100vh - 140px);
    transition: width 0.28s ease;
    position: relative;
    overflow: visible;

    &.collapsed {
      width: 56px;
    }
  }

  /* ── Secciones ─────────────────────────────── */
  .sidebar-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .sidebar-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 16px;
    height: 44px;
    cursor: pointer;
    border-bottom: 1px solid $color-black-8;
    transition: background-color 0.15s;

    &:last-child {
      border-bottom: none;
    }

    &:hover:not(.disabled) {
      background-color: #fafafa;
    }

    &.active {
      background-color: #f5f5f5;
      border-left: 4px solid #bd0d12;
      padding-left: 12px;
    }

    &.disabled {
      opacity: 0.45;
      cursor: not-allowed;
      pointer-events: none;
    }
  }

  /* modo colapsado: centrar ícono */
  .collapsed .sidebar-item {
    justify-content: center;
    padding: 0;
  }

  /* Ícono bus (sección con subitems) */
  .bus-icon {
    font-size: 18px;
    color: #595959;
    flex-shrink: 0;
  }

  /* Íconos check */
  .check-icon {
    font-size: 16px;
    color: #d9d9d9;
    flex-shrink: 0;

    &.completed {
      color: $color-green-2;
    }
  }

  .item-label {
    flex: 1;
    font-size: 12px;
    font-weight: 400;
    color: $color-black-2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .arrow-icon {
    font-size: 10px;
    color: #bfbfbf;
    flex-shrink: 0;
  }

  /* ── Botón colapsar — flotante original ─────── */
  .sidebar-toggle-btn {
    position: absolute;
    bottom: 60px;
    right: -14px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ebeff2;
    border: 1px solid $color-black-8;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    transition: background 0.2s;

    &:hover {
      background: #d5d9db;
    }

    svg {
      color: #595959;
      font-size: 11px;
    }
  }
</style>
