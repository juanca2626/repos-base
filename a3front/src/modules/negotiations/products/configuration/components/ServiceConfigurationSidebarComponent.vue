<template>
  <div class="sidebar-wrapper">
    <div class="sidebar" :class="{ collapsed: isSidebarCollapsed }">
      <SidebarProgressComponent :isSidebarCollapsed="isSidebarCollapsed" />

      <div class="menu-sections">
        <div v-for="section in getSectionsKeyActive" :key="section.key" class="menu-section">
          <div
            v-if="section.items?.length === 0 && section.source === 'item'"
            @click="toggleSection(section)"
          >
            <div class="section-items">
              <div :class="['menu-item', { active: section.active }]">
                <template v-if="isSidebarCollapsed">
                  <CheckCircleFilled v-if="section.completed" class="check-icon completed" />
                  <CheckCircleOutlined v-else class="check-icon" />
                </template>
                <template v-else>
                  <CheckCircleFilled v-if="section.completed" class="check-icon completed" />
                  <CheckCircleOutlined v-else class="check-icon" />
                  <span class="item-label">{{ section.title }}</span>
                  <RightOutlined class="arrow-right" />
                </template>
              </div>
            </div>
          </div>
          <div v-else>
            <div v-if="section.title" class="section-header" @click="toggleSection(section)">
              <template v-if="isSidebarCollapsed">
                <!-- colocar icono del tipo de servicio especifico -->
                <IconTrain v-if="isServiceTypeTrain" />
                <IconShared v-if="isServiceTypeGeneral" />
                <IconSeason v-if="isServiceTypeMultiDays" />
              </template>
              <template v-else>
                <span class="section-title">{{ section.title }}</span>
                <DownOutlined :class="['arrow-icon', { 'arrow-rotated': !section.active }]" />
              </template>
            </div>

            <transition name="slide">
              <div v-show="section.title ? section.active : true" class="section-items">
                <div
                  v-for="item in section.items"
                  :key="item.id"
                  :class="[
                    'menu-item',
                    {
                      active: item.active,
                      disabled: item.disabled,
                    },
                  ]"
                  @click="setActiveSectionItem(section.key, section.code, item.id)"
                >
                  <template v-if="isSidebarCollapsed">
                    <CheckCircleFilled v-if="item.completed" class="check-icon completed" />
                    <CheckCircleOutlined v-else class="check-icon" />
                  </template>
                  <template v-else>
                    <CheckCircleFilled v-if="item.completed" class="check-icon completed" />
                    <CheckCircleOutlined v-else class="check-icon" />
                    <span class="item-label">{{ item.label }}</span>
                    <RightOutlined class="arrow-right" />
                  </template>
                </div>
              </div>
            </transition>
          </div>
        </div>
        <div class="category-button-container" v-if="!isSidebarCollapsed && !isMarketingPage">
          <span type="text" class="category-button" @click="handleOpenCategoryDrawer">
            <PlusCircleOutlined />
          </span>
        </div>
      </div>

      <button class="sidebar-toggle-btn" @click="toggleSidebar">
        <LeftOutlined v-if="!isSidebarCollapsed" />
        <RightOutlined v-else />
      </button>

      <componentDrawer
        v-if="componentDrawer"
        :is="componentDrawer"
        :show-drawer-form="showCategoryDrawer"
        :supplier-original-id="supplierOriginalId"
        :product-supplier-id="productSupplierId"
        @update:show-drawer-form="showCategoryDrawer = $event"
      />
    </div>
  </div>
</template>
<script setup lang="ts">
  interface Props {
    isMarketingPage?: boolean;
  }

  defineProps<Props>();

  import {
    CheckCircleOutlined,
    CheckCircleFilled,
    DownOutlined,
    RightOutlined,
    LeftOutlined,
    PlusCircleOutlined,
  } from '@ant-design/icons-vue';
  import { useServiceConfigurationSidebar } from '@/modules/negotiations/products/configuration/composables/useServiceConfigurationSidebar';
  import SidebarProgressComponent from '@/modules/negotiations/products/configuration/components/partials/SidebarProgressComponent.vue';
  import IconTrain from '@/modules/negotiations/products/configuration/icons/IconTrain.vue';
  import IconShared from '@/modules/negotiations/products/configuration/icons/IconShared.vue';
  import IconSeason from '@/modules/negotiations/products/configuration/icons/IconSeason.vue';

  const {
    isSidebarCollapsed,
    getSectionsKeyActive,
    showCategoryDrawer,
    productSupplierId,
    supplierOriginalId,
    isServiceTypeTrain,
    isServiceTypeMultiDays,
    isServiceTypeGeneral,
    toggleSection,
    handleOpenCategoryDrawer,
    setActiveSectionItem,
    toggleSidebar,

    componentDrawer,
  } = useServiceConfigurationSidebar();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .sidebar-wrapper {
    position: relative;
    height: 100%;
    display: flex;
    align-items: flex-start;
  }

  .sidebar {
    width: 250px;
    background-color: $color-white;
    border-right: 1px solid $color-black-8;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    height: 100%;
    transition: width 0.3s ease;

    &.collapsed {
      width: 60px;
    }
  }

  .sidebar-toggle-btn {
    position: absolute;
    top: 65%;
    right: 0;
    width: 32px;
    height: 32px;
    border-radius: 24px 0 0 24px;
    background: #ebeff2;
    border: 0;
    cursor: pointer;
    z-index: 1000;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

    &:hover {
      background: #d0d0d0;
    }

    svg {
      color: #595959;
      font-size: 16px;
    }
  }

  .menu-sections {
    flex: 1;
    padding: 8px 0;
  }

  .menu-section {
    margin-bottom: 0;
    border-bottom: 1px solid $color-black-8;

    &:last-child {
      border-bottom: none;
    }
  }

  .section-header {
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
  }

  .section-header:hover {
    background-color: #fafafa;
  }

  .section-title {
    font-size: 14px;
    font-weight: 500;
    color: $color-black;
  }

  .arrow-icon {
    font-size: 12px;
    color: #8c8c8c;
    transition: transform 0.3s;
  }

  .arrow-rotated {
    transform: rotate(-90deg);
  }

  .section-items {
    overflow: hidden;
  }

  .menu-item {
    padding: 12px 20px 12px 20px;
    height: 40px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.2s;
    gap: 12px;
  }

  .menu-item:hover:not(.disabled) {
    background-color: #ebeff2;
  }

  .menu-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
  }

  .menu-item.active {
    background-color: #ebeff2;
    border-left: 4px solid #24292d;
  }

  .check-icon {
    font-size: 16px;
    color: $color-gray-soft;
    flex-shrink: 0;
  }

  .check-icon.completed {
    color: $color-green-2;
  }

  .item-label {
    flex: 1;
    font-size: 12px;
    line-height: 24px;
    font-weight: 400;
    color: $color-black-2;
  }

  .arrow-right {
    font-size: 12px;
    color: #bfbfbf;
    flex-shrink: 0;
  }

  .category-button-container {
    padding: 8px 20px;
  }

  .category-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: end;
    gap: 8px;
    color: #595959;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
  }

  .slide-enter-active,
  .slide-leave-active {
    transition: all 0.3s ease;
  }

  .slide-enter-from,
  .slide-leave-to {
    opacity: 0;
    max-height: 0;
  }

  .slide-enter-to,
  .slide-leave-from {
    opacity: 1;
    max-height: 500px;
  }
</style>
