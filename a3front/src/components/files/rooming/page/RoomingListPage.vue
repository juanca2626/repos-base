<template>
  <div>
    <div class="files-edit__sort">
      <div class="files-edit__sort-col1">
        <base-popover placement="topLeft">
          <a-button
            data-html2canvas-ignore="true"
            @click="goBackPassengersPage()"
            class="btn-primary"
            type="primary"
            default
            size="large"
          >
            <font-awesome-icon :icon="['fas', 'arrow-left']" />
          </a-button>
          <template #content>Volver al programa</template>
        </base-popover>
        <div class="title-module">
          <IconUsers color="#3D3D3D" width="1.2em" height="1.2em" class="icon" />
          Rooming list
        </div>
      </div>
      <div class="files-edit__sort-col2" data-html2canvas-ignore="true">
        <a-dropdown>
          <a-button
            :loading="loadingDownload || downloadStore.isFileLoading(fileId)"
            class="text-600 btn-download"
          >
            <IconDownloadCloud
              v-show="!loadingDownload && !downloadStore.isFileLoading(fileId)"
              width="22"
              height="18"
            />
            Descargar
          </a-button>
          <template #overlay>
            <a-menu>
              <a-menu-item>
                <a href="javascript:;" @click="exportToExcel">Excel</a>
              </a-menu-item>
              <a-menu-item>
                <a href="javascript:;" @click="exportToPDF">PDF</a>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
        <base-button type="primary" size="large" v-if="isAdmin()">
          <div style="display: flex; gap: 4px">
            <span>Enviar a hoteles</span>
          </div>
        </base-button>
      </div>
    </div>
    <div class="container-pdf">
      <div class="files-edit__services mt-5">
        <!-- Tabs superiores -->
        <a-tabs
          v-model:activeKey="activeTab"
          class="service-schedule-tabs-hotels"
          type="card"
          v-if="listHotels.length > 0"
        >
          <a-tab-pane v-for="(hotel, h) in listHotels" :key="`hotel-${h}`">
            <template #tab>
              <span class="tab-title">{{ hotel.hotel }}</span>
            </template>
            <HotelRoomingList :hotel="hotel" />
          </a-tab-pane>
        </a-tabs>
        <div v-else>
          <a-empty v-if="!fileStore.loading_async">
            <template #description>
              <span> No se encontraron hoteles para este servicio </span>
            </template>
          </a-empty>
          <div v-else class="loading-rooming-list">Cargando Rooming List...</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import { defineEmits, onMounted, ref } from 'vue';
  import IconUsers from '@/components/icons/IconUsers.vue';
  import BaseButton from '@/components/files/reusables/BaseButton.vue';
  import IconDownloadCloud from '@/components/icons/IconDownloadCloud.vue';
  import HotelRoomingList from '@/components/files/rooming/components/HotelRoomingList.vue';
  import html2canvas from 'html2canvas';
  import jsPDF from 'jspdf';
  import { notification } from 'ant-design-vue';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';
  import { isAdmin } from '@/utils/auth';

  const emit = defineEmits(['onBack']);

  const activeTab = ref('hotel-0');
  const loadingDownload = ref(false);
  const fileStore = useFilesStore();
  const router = useRouter();
  const fileId = router.currentRoute.value.params.id;
  const listHotels = ref([]);
  const downloadStore = useDownloadStore();

  const exportToPDF = async () => {
    try {
      loadingDownload.value = true;

      // Identificar el contenedor del contenido activo
      const activeTabContent = document.querySelector(
        `.service-schedule-tabs-hotels .ant-tabs-tabpane-active`
      );

      if (!activeTabContent) {
        throw new Error('No se encontró contenido del tab activo para exportar.');
      }

      // Crear un contenedor temporal para exportar
      const tempContainer = document.createElement('div');
      tempContainer.style.position = 'absolute';
      tempContainer.style.top = '0';
      tempContainer.style.left = '0';
      tempContainer.style.width = '100%';
      tempContainer.style.backgroundColor = '#ffffff'; // Asegurar fondo blanco
      tempContainer.style.zIndex = '-1'; // Asegurar que no interfiera con el diseño

      // Crear encabezado
      const header = document.createElement('div');
      header.style.textAlign = 'left';
      header.style.marginBottom = '10px';
      header.innerHTML = `
      <h2 style="font-size: 30px; margin: 0; padding: 0;">Rooming List</h2>
      <hr style="border: 0; border-top: 1px solid #ccc; margin: 10px 0 25px 0;" />
    `;

      // Clonar el contenido activo y agregarlo al contenedor temporal
      const clonedContent = activeTabContent.cloneNode(true);
      tempContainer.appendChild(header); // Agregar encabezado
      tempContainer.appendChild(clonedContent); // Agregar contenido

      document.body.appendChild(tempContainer);

      // Usar html2canvas en el contenedor temporal
      const canvas = await html2canvas(tempContainer, {
        scale: 3, // Alta resolución
        useCORS: true, // Permite cargar imágenes externas
      });

      // Crear el PDF
      const pdf = new jsPDF('p', 'mm', 'a4');
      const marginX = 15; // Márgenes izquierdo y derecho
      const marginY = 10; // Márgenes superior e inferior
      const pageWidth = pdf.internal.pageSize.getWidth() - marginX * 2;
      const imageHeight = (canvas.height * pageWidth) / canvas.width;

      pdf.addImage(canvas.toDataURL('image/png'), 'PNG', marginX, marginY, pageWidth, imageHeight);

      // Descargar el PDF
      pdf.save('rooming_list.pdf');

      notification.success({
        message: 'Éxito',
        description: 'Archivo PDF generado exitosamente.',
      });
    } catch (error) {
      notification.error({
        message: 'Error',
        description: `Error al generar el archivo PDF: ${error.message}`,
      });
    } finally {
      loadingDownload.value = false;

      // Eliminar el contenedor temporal
      const tempContainer = document.querySelector('#temp-container');
      if (tempContainer) {
        document.body.removeChild(tempContainer);
      }
    }
  };

  const exportToExcel = async () => {
    try {
      loadingDownload.value = true;
      downloadStore.downloadFileDocuments(fileId, 'roomingListExcel');
      notification.success({
        message: 'Éxito',
        description: 'Archivo Excel generado exitosamente.',
      });
    } catch (error) {
      notification.error({
        message: 'Error',
        description: `Error al generar el archivo Excel: ${error.message}`,
      });
    } finally {
      loadingDownload.value = false;
    }
  };

  const goBackPassengersPage = () => {
    emit('onBack', false);
  };

  onMounted(async () => {
    await fileStore.fetchRoomingList(fileId);
    listHotels.value = fileStore.getRoomingList;
  });
</script>
<style scoped lang="scss">
  .title-module {
    margin-left: 10px;
  }

  .loading-rooming-list {
    color: #eb5757 !important;
    display: flex;
    justify-content: center;
    flex-direction: column;
    align-content: center;
    align-items: center;
    gap: 10px;
  }

  .btn-download {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #eb5757 !important;
    border: 1px solid #eb5757 !important;
    margin-right: 10px;

    svg {
      color: #eb5757 !important;
      margin-right: 10px;
    }

    &:hover {
      color: #eb5757 !important;
      background-color: #fff6f6 !important;
      border: 1px solid #eb5757 !important;
    }
  }

  /* Tabs */

  .service-schedule-tabs-hotels {
    .tab-title {
      text-transform: capitalize;
    }

    :deep(.ant-tabs-nav-list) {
      background-color: #ffffff !important;
    }

    :deep(.ant-tabs-tab-btn) {
      color: #ffffff !important;
    }

    :deep(.ant-tabs-tab) {
      background-color: #e9e9e9;
      color: #979797 !important;
      font-weight: 600;
      text-transform: capitalize !important;
      border-top-left-radius: 6px;
      border-top-right-radius: 6px;
      font-size: 14px !important;
      margin-left: 10px !important;
    }

    :deep(.ant-tabs-tab):nth-child(1) {
      margin-left: 15px !important;
    }

    :deep(.ant-tabs-tab-active) {
      background-color: #737373 !important;
      text-transform: capitalize;
      border-top-left-radius: 6px !important;
      border-top-right-radius: 6px !important;
    }

    :deep(.ant-tabs-nav) {
      margin-bottom: 0 !important;

      ::before {
        bottom: 0 !important;
      }
    }
  }
</style>
