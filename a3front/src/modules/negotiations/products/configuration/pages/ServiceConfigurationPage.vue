<template>
  <div>
    <ServiceConfigurationHeaderComponent />

    <a-spin :spinning="isLoading" tip="Cargando configuración..." size="large">
      <div class="service-configuration-page">
        <FormProgressLayout>
          <!-- Título estático (sticky) -->
          <div class="title-container">
            <ServiceConfigurationTitleComponent />
          </div>

          <!-- Contenedor con scroll vertical -->
          <div class="content-container">
            <!-- aqui el contenido de las paginas -->
            <component
              v-if="activeComponent && !isLoading"
              :is="activeComponent"
              :current-key="currentKey"
              :current-code="currentCode"
            />
          </div>
        </FormProgressLayout>
      </div>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import FormProgressLayout from '@/modules/negotiations/products/configuration/layout/FormProgressLayout.vue';
  import ServiceConfigurationHeaderComponent from '@/modules/negotiations/products/configuration/components/partials/ServiceConfigurationHeaderComponent.vue';
  import ServiceConfigurationTitleComponent from '@/modules/negotiations/products/configuration/components/partials/ServiceConfigurationTitleComponent.vue';
  import { useActiveComponent } from '@/modules/negotiations/products/configuration/navigation/resolvers/useActiveComponent';
  import { useServiceConfigurationPage } from '@/modules/negotiations/products/configuration/composables/useServiceConfigurationPage';

  const { activeComponent, currentKey, currentCode } = useActiveComponent();

  const { isLoading } = useServiceConfigurationPage();
</script>

<style scoped lang="scss">
  .service-configuration-page {
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .title-container {
    top: 0;
    z-index: 10;
    background-color: #ffffff;
  }

  .content-container {
    flex: 1;
    overflow-y: auto;
    // overflow-x: hidden;
    // min-height: 0; // Necesario para que flex funcione correctamente con overflow
    -webkit-overflow-scrolling: touch; // Scroll suave en iOS
    padding-right: 32px;
  }
</style>
