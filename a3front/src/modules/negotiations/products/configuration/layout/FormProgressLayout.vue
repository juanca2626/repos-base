<template>
  <div class="progress-layout">
    <!-- Top config tabs -->
    <ServiceConfigurationTabsComponent />

    <!-- Main content with sidebar and slot -->
    <div class="main-container">
      <ServiceConfigurationSidebarComponent />
      <div class="content">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ServiceConfigurationTabsComponent from '@/modules/negotiations/products/configuration/components/ServiceConfigurationTabsComponent.vue';
  import ServiceConfigurationSidebarComponent from '@/modules/negotiations/products/configuration/components/ServiceConfigurationSidebarComponent.vue';
</script>

<style scoped>
  * {
    font-family: 'Inter', sans-serif !important;
  }

  .progress-layout {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: #f5f5f5;
  }

  .main-container {
    display: flex;
    flex: 1;
    overflow: hidden;
    /* height: calc(100vh - 55px); */
    height: 100%;
    width: 100%;
  }

  .content {
    flex: 1;
    height: 100%;
    background-color: #ffffff;
    padding: 32px 0px 32px 32px;
    overflow: auto;
  }
</style>
