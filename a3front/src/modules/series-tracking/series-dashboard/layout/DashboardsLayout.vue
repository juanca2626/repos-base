<template>
  <a-spin :spinning="dashboardStore.isLoading" size="large">
    <a-layout id="dashboards-layout">
      <a-layout-header class="custom-layout-header sticky">
        <HeaderComponent />
      </a-layout-header>

      <a-layout>
        <a-layout-content>
          <div class="dashboards-layout">
            <div class="dashboards-layout-container">
              <a-breadcrumb class="breadcrumb-component">
                <template #separator>
                  <span> / </span>
                </template>
              </a-breadcrumb>

              <router-view />
            </div>
          </div>
        </a-layout-content>
      </a-layout>
    </a-layout>
  </a-spin>
</template>

<script setup>
  import HeaderComponent from '@/components/global/HeaderComponent.vue';
  import ABreadcrumb from '@/components/global/ABreadcrumbRoutes.vue';
  import { useDashboardsStore } from '@/modules/series-tracking/series-dashboard/store';

  const dashboardStore = useDashboardsStore();
</script>

<style lang="scss">
  #dashboards-layout {
    min-height: 100vh;
  }

  .dashboards-layout {
    padding: 16px;
    display: flex;
    justify-content: center;
  }

  .dashboards-layout-container {
    width: 100%;
    max-width: 1400px;
  }

  .breadcrumb-component {
    background: #fff;
    border: 1px solid #e9e9e9;
    border-radius: 6px;
    padding: 12px 24px;
    margin-bottom: 16px;
  }
</style>
