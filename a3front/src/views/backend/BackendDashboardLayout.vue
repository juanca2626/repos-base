<template>
  <a-layout class="backend-dashboard">
    <a-layout-header>
      <b-header-component></b-header-component>
    </a-layout-header>
    <a-layout class="layout-content">
      <a-layout-sider
        class="sidebar-fixed"
        v-model:collapsed="collapsed"
        style="overflow: auto"
        collapsible
        @update:collapsed="handleCollapseChange"
      >
        <b-sidebar-menu :collapsed="collapsed"></b-sidebar-menu>
      </a-layout-sider>
      <a-layout-content
        :class="{ 'collapsed-content': collapsed, 'expanded-content': !collapsed }"
        class="main-content-container"
      >
        <div class="dashboard-container">
          <b-breadcrumb class="breadcrumb-routes"></b-breadcrumb>
          <div class="content-wrapper">
            <router-view></router-view>
          </div>
        </div>
      </a-layout-content>
    </a-layout>
    <a-layout-footer
      :class="{ 'collapsed-content': collapsed, 'expanded-content': !collapsed }"
      class="footer-fixed"
    >
      <a-row justify="space-around" align="middle" class="text-white">
        <a-col :span="10" class="pt-2">
          Av. Juan de Arona 755 Piso 11, San Isidro 15046 - Oficinas de Spaces - Perú<br />
          Razón Social: LimaTours SAC | RUC: 20536830376
        </a-col>
        <a-col :span="6" :offset="6" class="text-right pr-2"
          >Copyright © {{ year }} - All rights reserved
        </a-col>
      </a-row>
    </a-layout-footer>
  </a-layout>
</template>

<script setup>
  import { ref } from 'vue';
  import BHeaderComponent from '@/components/backend/BackendHeaderComponent.vue';
  import BSidebarMenu from '@/components/backend/BackendSidebarMenu.vue';
  import BBreadcrumb from '@/components/backend/BackendBreadcrumbRoutes.vue';

  const year = new Date().getFullYear();
  const collapsed = ref(false);

  const handleCollapseChange = (value) => {
    collapsed.value = value;
    console.log('El estado de collapsed ha cambiado a:', value);
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .dashboard-container {
    font-family: 'Inter', sans-serif !important;
    padding: 24px 48px;

    background-color: #e4e5e6;
  }

  /* Sidebar con scroll visible */
  .sidebar-fixed {
    height: calc(100vh - 64px) !important;
    max-height: calc(100vh - 64px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;

    :deep(.ant-layout-sider-children) {
      height: 100%;
      overflow-y: auto;
      overflow-x: hidden;
    }
  }

  /* Contenido principal con scroll visible */
  .main-content-container {
    height: calc(100vh - 64px) !important; // Altura fija menos el header
    max-height: calc(100vh - 64px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    transition: margin-left 0.2s ease-in-out;
  }

  /* El dashboard-container debe ocupar todo el espacio disponible */
  .dashboard-container {
    min-height: 100%;
    // min-height: calc(100% - 24px);
    display: flex;
    flex-direction: column;
  }

  /* Breadcrumb fijo arriba */
  .breadcrumb-routes {
    flex-shrink: 0;
    margin-bottom: 20px;
  }

  /* Content wrapper que puede crecer y tener scroll interno */
  .content-wrapper {
    flex: 1;
    min-height: 0;
  }

  .layout-content {
    .ant-layout-content {
      transition: margin-left 0.2s ease-in-out;
    }
  }

  .expanded-content {
    margin-left: 200px;
  }

  .collapsed-content {
    margin-left: 80px;
  }
</style>
