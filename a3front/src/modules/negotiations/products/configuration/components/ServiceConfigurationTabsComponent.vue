<template>
  <div class="configuration-tabs">
    <template v-for="tab in tabs" :key="tab.key">
      <div
        class="configuration-tab"
        :class="{ active: tab.isActive }"
        @click="handleTabChange(tab.key!)"
      >
        <template v-if="isServiceTypeMultiDays">
          <IconFileText />
        </template>
        <template v-else>
          <IconMapPin />
        </template>
        <span>
          {{ tab.name }}
        </span>
      </div>
    </template>
    <div class="configuration-tab add-tab" @click="handleOpenDrawer">
      <IconCircleAdd />
    </div>
  </div>

  <component
    v-if="componentDrawer"
    :is="componentDrawer"
    :show-drawer-form="showDrawerForm"
    :supplier-original-id="supplierOriginalId"
    :product-supplier-id="productSupplierId"
    @update:show-drawer-form="showDrawerForm = $event"
  />
</template>
<script setup lang="ts">
  import IconMapPin from '@/modules/negotiations/products/configuration/icons/IconMapPin.vue';
  import IconCircleAdd from '@/modules/negotiations/products/configuration/icons/IconCircleAdd.vue';
  import IconFileText from '@/modules/negotiations/products/configuration/icons/IconFileText.vue';
  import { useServiceConfigurationTabs } from '@/modules/negotiations/products/configuration/composables/useServiceConfigurationTabs';

  const {
    tabs,
    isServiceTypeMultiDays,
    // isServiceTypeTrain,
    // isServiceTypeGeneral,
    showDrawerForm,
    productSupplierId,
    supplierOriginalId,
    handleTabChange,
    handleOpenDrawer,

    componentDrawer,
  } = useServiceConfigurationTabs();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .configuration-tabs {
    display: flex;
    align-items: flex-end;
    background-color: $color-black-8;
    height: 55px;
    flex-shrink: 0;
    width: 100%;
  }

  .configuration-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 24px;
    cursor: pointer;
    color: #595959;
    font-size: 14px;
    font-weight: 400;
    min-width: 250px;
    flex-shrink: 0;
    height: 45px;
    transition: background-color 0.2s;
    position: relative;

    svg {
      flex-shrink: 0;
      width: 16px;
      height: 16px;
    }

    span {
      white-space: nowrap;
    }

    &:hover:not(.active) {
      background-color: none;
    }

    &.active {
      background-color: $color-white;
      color: #262626;

      /* Set a border on the top, left, and right */
      border-top: 1px solid $color-gray-soft;
      border-left: 1px solid $color-gray-soft;
      border-right: 1px solid $color-gray-soft;

      /* Keep the bottom border straight (or non-existent) */
      border-bottom: none;

      /* Apply top-only rounding */
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;

      /* Ensure the bottom corners remain square (0) */
      border-bottom-left-radius: 0;
      border-bottom-right-radius: 0;
    }

    &.add-tab {
      padding: 0 16px;
      border-right: none;

      svg {
        width: 20px;
        height: 20px;
        color: #595959;
      }

      &:hover {
        background-color: none;
      }
    }
  }
</style>
