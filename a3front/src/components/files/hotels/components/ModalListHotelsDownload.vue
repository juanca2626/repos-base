<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :focusTriggerAfterClose="false"
      width="1176px"
    >
      <div class="modal-container-hotels">
        <!-- Header -->
        <div class="modal-header">
          <div class="title-section">
            <font-awesome-icon :icon="['fas', 'building']" class="icon-title" />
            <span class="modal-title">Lista de hoteles</span>
          </div>
          <div class="actions-section" data-html2canvas-ignore="true" v-if="hotels.length > 0">
            <span>Descarga en formato:</span>
            <a-button
              type="default"
              size="large"
              class="btn-download-pdf"
              @click="exportToPDF"
              :disabled="loadingDownload || downloadStore.isFileLoading(fileStore.getFile.id)"
            >
              <a-spin v-if="loadingDownload" :indicator="indicator" />
              <IconFilePdf v-else color="#FFFFFF" />
            </a-button>
          </div>
        </div>
        <a-row :gutter="16" align="middle" class="mb-5">
          <a-col :span="12">
            <div class="client-section">
              <div class="client-name">{{ file.clientCode }} - {{ file.clientName }}</div>
            </div>
          </a-col>
          <a-col :span="12" justify="flex-end">
            <div class="file-section">
              <div class="file-date">
                Fecha:<span class="date">{{ formatDate(file.dateIn, 'short') }}</span>
              </div>
            </div>
          </a-col>
          <a-col :span="24">
            <div class="file-section">
              <div class="file-info">
                File: <span class="file-number">{{ file.fileNumber }}</span> -
                {{ file.description }}
              </div>
            </div>
          </a-col>
        </a-row>
        <!-- Body -->
        <div class="modal-body">
          <div v-if="hotels.length > 0">
            <table class="table-header-hotels">
              <thead>
                <tr>
                  <th width="25%">Fechas in / out</th>
                  <th width="75%" align="left">Hotel con descripción</th>
                </tr>
              </thead>
            </table>
            <!-- Servicios -->
            <table class="table-row-hotels">
              <tbody>
                <tr v-for="(item, index) in hotels" :key="index">
                  <td width="25%" align="center" class="td-center-top">
                    <div class="date">
                      {{ formatDate(item.date_in, 'short') }} -
                      {{ formatDate(item.date_out, 'short') }}
                    </div>
                  </td>
                  <td width="75%" class="td-center-top">
                    <a-row :gutter="16" align="top">
                      <a-col :span="24">
                        <div class="hotel">
                          <div class="hotel-title">Nombre del hotel:</div>
                          <div class="hotel-name">{{ item.hotel }}</div>
                          <!-- div class="hotel-stars" v-for="i in parseInt(item.stars)" :key="i">
                            <font-awesome-icon :icon="['fas', 'star']" />
                          </div -->
                        </div>
                      </a-col>
                      <a-col :span="24">
                        <div class="hotel">
                          <div class="hotel-title">Web:</div>
                          <div class="hotel-link-address">
                            <a :href="item.url" target="_blank">{{ item.url }}</a>
                          </div>
                        </div>
                      </a-col>
                      <!--                      <a-col :span="24">-->
                      <!--                        <div class="hotel">-->
                      <!--                          <div class="hotel-title">Dirección:</div>-->
                      <!--                          <div class="hotel-link-address">-->
                      <!--                            {{ item.address }}-->
                      <!--                          </div>-->
                      <!--                        </div>-->
                      <!--                      </a-col>-->
                      <!--                      <a-col :span="24">-->
                      <!--                        <div class="hotel">-->
                      <!--                          <div class="hotel-title">Teléfono:</div>-->
                      <!--                          <div class="hotel-phone">-->
                      <!--                            <IconPhone color="#28A745" width="1.1em" height="1.1em" />-->
                      <!--                            {{ item.phone }}-->
                      <!--                          </div>-->
                      <!--                        </div>-->
                      <!--                      </a-col>-->
                      <a-col :span="24">
                        <div class="hotel">
                          <div class="hotel-title">Check in:</div>
                          <div class="hotel-hour">{{ item.start_time }}</div>
                          <div class="hotel-title">Check out:</div>
                          <div class="hotel-hour">{{ item.departure_time }}</div>
                        </div>
                      </a-col>
                      <a-col :span="24">
                        <div class="hotel">
                          <div class="hotel-title">Código de confirmación:</div>
                          <div class="hotel-hour">{{ item.confirmation_code }}</div>
                        </div>
                      </a-col>
                    </a-row>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else>
            <a-empty v-if="!downloadStore.loading">
              <template #description>
                <span> No se encontraron hoteles para este file </span>
              </template>
            </a-empty>
            <div v-else class="loading-rooming-list">Cargando Hoteles...</div>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { h, ref, watch } from 'vue';
  import IconFilePdf from '@/components/icons/IconFilePdf.vue';
  import html2canvas from 'html2canvas';
  import jsPDF from 'jspdf';
  import { notification } from 'ant-design-vue';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';
  import { LoadingOutlined } from '@ant-design/icons-vue';

  const loadingDownload = ref(false);
  const emit = defineEmits(['update:isOpen', 'submit']);
  const downloadStore = useDownloadStore();
  const fileStore = useFilesStore();
  const file = fileStore.getFile;
  const router = useRouter();
  const fileId = router.currentRoute.value.params.id;

  const indicator = h(LoadingOutlined, {
    style: {
      fontSize: '24px',
      color: '#ffffff',
    },
    spin: true,
  });

  // Propiedades
  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const hotels = ref([]);

  const exportToPDF = async () => {
    loadingDownload.value = true;

    try {
      const pdf = new jsPDF('p', 'mm', 'a4');

      const marginX = 10;
      const marginY = 10;
      const pageWidth = pdf.internal.pageSize.getWidth() - marginX * 2;
      const pageHeight = pdf.internal.pageSize.getHeight() - marginY * 2;

      const container = document.querySelector('.modal-container-hotels');
      if (!container) {
        throw new Error('No se encontró el contenido para exportar.');
      }

      const canvas = await html2canvas(container, {
        scale: 3,
        useCORS: true,
        backgroundColor: '#ffffff',
      });

      const canvasWidth = canvas.width;
      const canvasHeight = canvas.height;

      const imgHeightPerPage = (canvasWidth / pageWidth) * pageHeight; // en píxeles del canvas original
      let yOffset = 0;

      while (yOffset < canvasHeight) {
        const partHeight = Math.min(imgHeightPerPage, canvasHeight - yOffset);

        const partCanvas = document.createElement('canvas');
        partCanvas.width = canvasWidth;
        partCanvas.height = partHeight;

        const ctx = partCanvas.getContext('2d');
        ctx.drawImage(canvas, 0, yOffset, canvasWidth, partHeight, 0, 0, canvasWidth, partHeight);

        const partImgData = partCanvas.toDataURL('image/png');
        const pdfHeight = (partHeight * pageWidth) / canvasWidth;

        pdf.addImage(partImgData, 'PNG', marginX, marginY, pageWidth, pdfHeight);

        yOffset += partHeight;
        if (yOffset < canvasHeight) {
          pdf.addPage();
        }
      }

      pdf.save('hotel_list.pdf');

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
    }
  };

  const handleCancel = () => {
    emit('update:isOpen', false);
  };

  const formatDate = (dateString: string, format: string): string => {
    const date = new Date(dateString + 'T00:00:00'); // Forzar horario local

    if (isNaN(date.getTime())) return ''; // Retorna vacío si la fecha no es válida

    switch (format) {
      case 'long': // Ejemplo: November 05 2024
        return date
          .toLocaleDateString('es-ES', {
            month: 'long',
            day: '2-digit',
            year: 'numeric',
          })
          .replace(',', '');

      case 'long-comma': // Ejemplo: December 25, 2024
        return date.toLocaleDateString('es-ES', {
          month: 'long',
          day: '2-digit',
          year: 'numeric',
        });

      case 'short': // Ejemplo: 17/04/2022
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        });

      default:
        return dateString;
    }
  };

  watch(
    () => props.isOpen,
    async (newValue) => {
      if (newValue) {
        try {
          const details = await downloadStore.getListHotelsByFileId(fileId);
          hotels.value = details;
        } catch (error) {
          console.error('Error al cargar detalles del statement:', error);
        }
      }
    }
  );
</script>

<style scoped lang="scss">
  .file-modal {
    .loading-rooming-list {
      color: #eb5757 !important;
      display: flex;
      justify-content: center;
      flex-direction: column;
      align-content: center;
      align-items: center;
      gap: 10px;
    }

    .font-w-700 {
      font-weight: 700;
    }

    .box-title-services {
      margin-bottom: 20px;
      padding: 10px 37px;
      background-color: #fafafa;
      border-radius: 6px;
    }

    .modal-container-hotels {
      padding: 20px;
    }

    .client-section {
      font-family: Montserrat, sans-serif;

      .client-name {
        font-weight: 700;
        font-size: 24px;
        color: #3d3d3d;
        margin-bottom: 10px;
      }
    }

    .file-section {
      font-family: Montserrat, sans-serif;

      .file-info {
        font-weight: 400;
        font-size: 16px;
        color: #3d3d3d;
        margin-bottom: 10px;

        .file-number {
          font-weight: 600;
        }
      }

      .file-date {
        font-weight: 400;
        font-size: 12px;
        color: #3d3d3d;
        text-align: right;

        .date {
          margin-left: 10px;
          font-weight: 700;
        }
      }
    }

    .modal-header {
      font-family: Montserrat, sans-serif;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      background-color: #ffffff;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;

      .title-section {
        display: flex;
        align-items: center;

        .icon-title {
          font-size: 28px;
          margin-right: 8px;
        }

        .modal-title {
          font-size: 36px;
          font-weight: 600;
        }
      }

      .actions-section {
        display: flex;
        align-items: center;
        font-size: 16px;

        span {
          margin-right: 10px;
        }

        .btn-download {
          margin-left: 5px;
          background-color: #fafafa;
          color: #575757;
          width: 50px;
          height: 45px;
          border-color: #fafafa;

          svg {
            font-size: 1.2rem;
          }

          &:hover {
            background-color: #e9e9e9;
            color: #575757;
            border-color: #e9e9e9;
          }
        }

        .btn-download-pdf {
          margin-left: 5px;
          background-color: #eb5757;
          border-color: #fafafa;
          width: 50px;
          height: 45px;
          color: #575757;
          display: flex;
          align-content: center;
          align-items: center;

          svg {
            font-size: 0.98rem;
          }

          &:hover {
            background-color: #c63838;
            color: #575757;
            border-color: #c63838;
          }
        }
      }
    }

    .modal-body {
      position: relative;
      border: 1px solid #e9e9e9;

      .hotel-link-address {
        color: #575757;
      }

      .table-header-hotels {
        font-family: Montserrat, sans-serif;
        width: 100%;
        border-collapse: collapse;

        thead {
          background-color: #ffffff;
          border-radius: 6px 6px 0 0;

          tr {
            th {
              padding: 10px 20px;
              font-weight: 700;
              font-size: 14px;
              color: #3d3d3d;
            }
          }
        }

        tbody {
          tr {
            td {
              padding: 10px 20px;
              font-weight: 400;
              font-size: 14px;
              color: #3d3d3d;
            }
          }
        }
      }

      .table-row-hotels {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;

        margin-bottom: 20px;

        .td-center-top {
          vertical-align: top; /* Alinea el contenido en la parte superior */
          padding-top: 15px; /* Añade un pequeño margen superior opcional */
        }

        tbody {
          font-family: Montserrat, serif;
          font-weight: 400;
          font-size: 14px;
          background-color: #fafafa;

          tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
          }

          tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
          }

          tr {
            td {
              padding: 10px 20px;
              font-weight: 400;
              font-size: 14px;
              color: #3d3d3d;

              .date {
                font-family: Montserrat, sans-serif;
                font-weight: 400;
                font-size: 14px;
                color: #212529;
              }

              .hotel {
                font-family: Montserrat, sans-serif;
                display: flex;
                flex-direction: row;
                gap: 10px;
                flex-wrap: nowrap;
                align-content: center;
                align-items: center;
                color: #4f4b4b;
                margin-bottom: 5px;

                &-title {
                  font-weight: 400;
                  font-size: 12px;
                  color: #212529;
                }

                &-name {
                  font-weight: 500;
                  font-size: 14px;
                  color: #212529;
                }

                &-stars {
                  font-weight: 500;
                  font-size: 16px;
                  color: #ffb001;
                }

                &-link-address {
                  font-weight: 500;
                  font-size: 12px;
                  color: #bd0d12 !important;
                  text-decoration: underline;
                  text-underline-position: under;
                  text-underline-offset: 1px;
                  cursor: pointer;
                }

                &-phone {
                  font-weight: 600;
                  font-size: 12px;
                  color: #28a745;
                  display: flex;
                  flex-direction: row;
                  align-items: center;

                  svg {
                    margin-right: 5px;
                  }
                }

                &-hour {
                  font-weight: 500;
                  font-size: 14px;
                  color: #212529;
                }
              }
            }
          }
        }
      }
    }
  }
</style>
