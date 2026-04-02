<!-- TouristTransportListPage.vue -->
<template>
  <menu-bar-transport-component></menu-bar-transport-component>
  <menu-land-transport-component></menu-land-transport-component>
  <div class="p-5 module-negotiations">
    <TouristTransportLinksComponent />
    <div class="mt-5">
      <a-row>
        <a-col span="12">
          <p class="text-title-filter">Filtra tus proveedores</p>
        </a-col>
        <a-col span="12" class="text-right">
          <!-- Enlace que abre el modal -->
          <a-typography-link class="link-title-download" @click="showModal">
            <font-awesome-icon :icon="['fas', 'download']" /> Descargar resultados
          </a-typography-link>

          <!-- Componente modal de descarga -->
          <download-results-modal-component
            :visible="modalVisible"
            @update:visible="modalVisible = $event"
            :initialFileName="getTransportDefaultFileName()"
            :onDownload="handleDownload"
          />
        </a-col>
      </a-row>
    </div>
    <div class="mt-1">
      <tourist-transport-tab-cities-component></tourist-transport-tab-cities-component>
      <tourist-transport-filter-component></tourist-transport-filter-component>
      <tourist-transport-list-component></tourist-transport-list-component>
    </div>
  </div>
</template>

<script setup lang="ts">
  // Importación de componentes
  import MenuBarTransportComponent from '@/modules/negotiations/supplier/components/MenuBarTransportComponent.vue';
  import MenuLandTransportComponent from '@/modules/negotiations/supplier/land/components/MenuLandTransportComponent.vue';
  import TouristTransportLinksComponent from '@/modules/negotiations/supplier/land/tourist-transport/components/TouristTransportLinksComponent.vue';
  import TouristTransportTabCitiesComponent from '@/modules/negotiations/supplier/land/tourist-transport/components/TouristTransportTabCitiesComponent.vue';
  import TouristTransportFilterComponent from '@/modules/negotiations/supplier/land/tourist-transport/components/TouristTransportFilterComponent.vue';
  import TouristTransportListComponent from '@/modules/negotiations/supplier/land/tourist-transport/components/TouristTransportListComponent.vue';
  import DownloadResultsModalComponent from '@/modules/negotiations/supplier/components/DownloadResultsModalComponent.vue';
  import { useTouristTransportListPage } from '@/modules/negotiations/supplier/land/tourist-transport/composables/useTouristTransportListPage';

  const { modalVisible, showModal, handleDownload, getTransportDefaultFileName } =
    useTouristTransportListPage();
</script>

<style scoped lang="scss">
  .text-title-filter {
    font-size: 14px;
    font-weight: 600;
    color: #575b5f;
  }

  .link-title-download {
    font-size: 14px;
    font-weight: 600;
    color: #1284ed;
    margin-left: 10px;
  }
</style>
