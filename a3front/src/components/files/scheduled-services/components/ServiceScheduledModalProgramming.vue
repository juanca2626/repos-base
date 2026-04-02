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
      <div class="modal-container-service">
        <!-- Header -->
        <div class="modal-header">
          <div class="title-section">
            <IconCircleCheck class="icon-title" color="#3D3D3D" />
            <span class="modal-title">Reporte de servicios</span>
          </div>
          <div
            class="actions-section"
            data-html2canvas-ignore="true"
            v-if="reportServices.length > 0"
          >
            <span>Descarga en formato:</span>
            <a-button
              type="default"
              size="large"
              class="btn-download"
              @click="exportToWord"
              :loading="loadingDownload"
            >
              <font-awesome-icon :icon="['fa', 'file-word']" />
            </a-button>
            <a-button
              type="default"
              size="large"
              class="btn-download-pdf"
              @click="exportToPDF"
              :disabled="loadingDownload || downloadStore.isFileLoading(fileStore.getFile.id)"
            >
              <IconFilePdf color="#FFFFFF" />
            </a-button>
          </div>
        </div>

        <div v-if="reportServices.length > 0">
          <a-row :gutter="16" align="middle">
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
          <a-row :gutter="16" align="middle">
            <a-col :span="24">
              <a-divider
                style="
                  height: 1px;
                  background-color: #e9e9e9;
                  margin-top: 20px;
                  margin-bottom: 30px;
                "
              />
            </a-col>
          </a-row>

          <div class="box-title-services">
            <a-row :gutter="16" align="middle">
              <a-col :span="24">
                <a-divider
                  orientation="left"
                  orientation-margin="1px"
                  style="border-color: #e9e9e9; height: 1px"
                >
                  <div class="sub-title text-uppercase">Servicios</div>
                </a-divider>
              </a-col>
            </a-row>
          </div>
          <!-- Body -->
          <div class="modal-body">
            <!-- table class="table-header-services">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Ciudad</th>
                </tr>
              </thead>
            </table -->
            <!-- Servicios -->
            <table class="table-row-services" v-for="service in reportServices">
              <tbody>
                <tr>
                  <td align="center" class="td-center-top">
                    <div class="service-date text-600">{{ formatDate(service.date, 'short') }}</div>
                  </td>
                  <td align="center" class="td-center-top">
                    <div class="service-city text-uppercase text-600">
                      {{ service.city_in_name }}
                    </div>
                  </td>
                </tr>
                <tr v-for="itinerary in service.itineraries">
                  <td colspan="2" class="td-center-top py-0">
                    <a-row align="middle" type="flex" style="gap: 10px">
                      <a-col>
                        <div class="service-time">
                          <font-awesome-icon :icon="['far', 'clock']" size="sm" />
                          {{ itinerary.start_time }}
                        </div>
                      </a-col>
                      <a-col flex="auto">
                        <div class="service-name">{{ itinerary.name }}</div>
                      </a-col>
                    </a-row>
                    <a-row align="top" type="flex" style="gap: 10px">
                      <a-col flex="auto">
                        <div class="text-description">
                          <template v-if="itinerary.description">
                            <div v-html="itinerary.description"></div>
                          </template>
                          <a-empty v-else>
                            <template #description></template>
                          </a-empty>
                        </div>
                      </a-col>
                    </a-row>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else>
          <a-empty v-if="!downloadStore.loading">
            <template #description>
              <span> No se encontraron servicios para este file </span>
            </template>
          </a-empty>
          <div v-else class="loading-list">Cargando servicios...</div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { ref, watch } from 'vue';
  import IconFilePdf from '@/components/icons/IconFilePdf.vue';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import html2canvas from 'html2canvas';
  import jsPDF from 'jspdf';
  import { notification } from 'ant-design-vue';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';
  import { AlignmentType, Document, Packer, Paragraph, TextRun } from 'docx';

  const loadingDownload = ref(false);
  const emit = defineEmits(['update:isOpen', 'submit']);
  const downloadStore = useDownloadStore();
  const fileStore = useFilesStore();
  const file = fileStore.getFile;
  const router = useRouter();
  const fileId = router.currentRoute.value.params.id;
  const reportServices = ref([]);
  // Propiedades
  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  // Capturar contenido del modal como imagen
  const captureModalContent = async (): Promise<HTMLCanvasElement | null> => {
    const modalBody = document.querySelector('.modal-container-service');
    if (!modalBody) {
      notification.error({
        message: 'Error',
        description: 'No se encontró el contenido del modal para exportar.',
      });
      return null;
    }
    return await html2canvas(modalBody, {
      scale: 3,
      useCORS: true,
      backgroundColor: '#ffffff',
    });
  };

  const addContentToPDF = (
    canvas: HTMLCanvasElement,
    pdf: jsPDF,
    pdfWidth: number,
    pageHeight: number
  ): void => {
    const margins = {
      top: 20,
      right: 20,
      bottom: 20,
      left: 20,
    };

    const contentWidth = pdfWidth - margins.left - margins.right;
    const pdfHeight = (canvas.height * contentWidth) / canvas.width;
    const context = canvas.getContext('2d');
    if (!context) return;

    if (pdfHeight <= pageHeight - margins.top - margins.bottom) {
      // Si el contenido cabe en una sola página
      pdf.addImage(
        canvas.toDataURL('image/png'),
        'PNG',
        margins.left,
        margins.top,
        contentWidth,
        pdfHeight
      );
      return;
    }

    const lineHeight = 20;
    let yOffset = 0;
    let currentPageY = margins.top;

    while (yOffset < canvas.height) {
      const pageCanvas = document.createElement('canvas');
      pageCanvas.width = canvas.width;
      const availableHeight = pageHeight - margins.top - margins.bottom;
      pageCanvas.height = availableHeight * (canvas.width / contentWidth);

      const pageContext = pageCanvas.getContext('2d');
      if (!pageContext) break;

      pageContext.drawImage(
        canvas,
        0,
        yOffset,
        canvas.width,
        pageCanvas.height,
        0,
        0,
        pageCanvas.width,
        pageCanvas.height
      );

      pdf.addImage(
        pageCanvas.toDataURL('image/png'),
        'PNG',
        margins.left,
        currentPageY,
        contentWidth,
        availableHeight
      );

      const imageData = context.getImageData(
        0,
        yOffset + pageCanvas.height - lineHeight,
        canvas.width,
        lineHeight
      );
      const hasCutWords = checkCutWords(imageData);

      if (hasCutWords) {
        yOffset -= lineHeight;
      }

      if (yOffset + pageCanvas.height < canvas.height) {
        pdf.addPage();
        currentPageY = margins.top;
      }

      yOffset += pageCanvas.height;
    }
  };

  // Función para verificar si hay palabras cortadas en una línea
  const checkCutWords = (imageData: ImageData): boolean => {
    const { data, width } = imageData;
    for (let i = 0; i < data.length; i += 4 * width) {
      const alpha = data[i + 3];
      if (alpha > 0) {
        return true;
      }
    }
    return false;
  };

  // Método para exportar a Word
  const exportToWord = async () => {
    try {
      loadingDownload.value = true;

      const documentChildren = [];

      // Título del reporte
      documentChildren.push(
        new Paragraph({
          children: [
            new TextRun({
              text: 'Reporte de servicios',
              bold: true,
              size: 28,
              font: 'Aptos',
            }),
          ],
          spacing: {
            after: 400,
          },
          alignment: AlignmentType.CENTER,
        })
      );

      // Nombre del cliente
      documentChildren.push(
        new Paragraph({
          children: [
            new TextRun({
              text: file.clientName,
              size: 24,
              font: 'Aptos',
            }),
          ],
          spacing: {
            after: 300,
          },
          alignment: AlignmentType.RIGHT,
        })
      );

      // Referencia
      documentChildren.push(
        new Paragraph({
          children: [
            new TextRun({
              text: `REF: ${file.clientCode} - ${file.description}`,
              size: 24,
              font: 'Aptos',
            }),
          ],
          spacing: {
            after: 400,
          },
          alignment: AlignmentType.RIGHT,
        })
      );

      // Agregar servicios
      reportServices.value.forEach((service) => {
        // Fecha y ciudad
        documentChildren.push(
          new Paragraph({
            children: [
              new TextRun({
                text: `${formatDate(service.date, 'short')} | ${service.city_in_name}`,
                size: 24,
                font: 'Arial',
              }),
            ],
            spacing: {
              before: 400,
              after: 200,
            },
          })
        );

        // Itinerarios
        service.itineraries.forEach((itinerary) => {
          // Hora y nombre del servicio
          documentChildren.push(
            new Paragraph({
              children: [
                new TextRun({
                  text: `${itinerary.start_time} ${itinerary.name}`,
                  size: 24,
                  font: 'Arial',
                }),
              ],
              spacing: {
                before: 200,
                after: 200,
              },
            })
          );

          // Guía (si existe)
          if (itinerary.guide) {
            documentChildren.push(
              new Paragraph({
                children: [
                  new TextRun({
                    text: `Guía: ${itinerary.guide}`,
                    bold: true,
                    size: 24,
                    font: 'Arial',
                  }),
                ],
                spacing: {
                  before: 100,
                  after: 200,
                },
              })
            );
          }

          // Descripción con sangría
          const descriptionText = itinerary.description.replace(/<[^>]*>/g, '');
          documentChildren.push(
            new Paragraph({
              children: [
                new TextRun({
                  text: descriptionText,
                  size: 24,
                  font: 'Arial',
                }),
              ],
              indent: {
                left: 720, // 0.5 pulgadas en twips (1440 twips = 1 pulgada)
              },
              spacing: {
                before: 200,
                after: 200,
                line: 360, // Espaciado entre líneas (1.5 líneas)
              },
              alignment: AlignmentType.JUSTIFIED,
            })
          );
        });
      });

      // Crear el documento con todos los children
      const doc = new Document({
        sections: [
          {
            properties: {
              page: {
                margin: {
                  top: 1440, // 1 pulgada
                  right: 1440,
                  bottom: 1440,
                  left: 1440,
                },
              },
            },
            children: documentChildren,
          },
        ],
      });

      // Generar y descargar el archivo
      const buffer = await Packer.toBuffer(doc);
      const blob = new Blob([buffer], {
        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'report_services.docx';
      link.click();
      window.URL.revokeObjectURL(url);

      notification.success({
        message: 'Éxito',
        description: 'El archivo Word se generó exitosamente.',
      });
    } catch (error) {
      notification.error({
        message: 'Error',
        description: `Error al generar el archivo Word: ${error}`,
      });
    } finally {
      loadingDownload.value = false;
    }
  };

  // Método para exportar a PDF
  const exportToPDF = async () => {
    try {
      loadingDownload.value = true;

      const canvas = await captureModalContent();
      if (!canvas) return;

      const pdf = new jsPDF('p', 'mm', 'a4');
      const pdfWidth = 210; // Tamaño en mm
      const pageHeight = 297; // Tamaño en mm
      addContentToPDF(canvas, pdf, pdfWidth, pageHeight);

      pdf.save('report_services.pdf');
      notification.success({
        message: 'Éxito',
        description: 'El archivo PDF se generó exitosamente.',
      });
    } catch (error) {
      notification.error({
        message: 'Error',
        description: `Error al generar el archivo PDF: ${error}`,
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
          reportServices.value = await downloadStore.getReportServiceByFileId(fileId);
        } catch (error) {
          console.error('Error al cargar detalles del statement:', error);
        }
      }
    }
  );
</script>

<style lang="scss">
  .file-modal {
    .loading-list {
      color: #eb5757 !important;
      display: flex;
      justify-content: center;
      flex-direction: column;
      align-content: center;
      align-items: center;
      gap: 10px;
    }

    .sub-title {
      font-family: Montserrat, sans-serif;
      font-weight: 600;
      font-size: 14px;
      color: #0d0d0d;
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

    .modal-container-service {
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

      .table-header-services {
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
              text-align: center;
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

      .table-row-services {
        background-color: #fafafa;
        border-radius: 8px;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;

        .td-center-top {
          vertical-align: top; /* Alinea el contenido en la parte superior */
          padding-top: 15px; /* Añade un pequeño margen superior opcional */
        }

        tbody {
          tr {
            td {
              padding: 10px 20px;
              font-weight: 400;
              font-size: 14px;
              color: #3d3d3d;

              .service-time {
                font-family: Montserrat, sans-serif;
                font-weight: 500;
                font-size: 14px;
                color: #212529;
                margin-right: 10px;
              }

              .service-date,
              .service-city {
                font-family: Montserrat, sans-serif;
                font-weight: 400;
                font-size: 14px;
                color: #4f4b4b;
              }

              .service-name {
                font-family: Montserrat, sans-serif;
                font-weight: 400;
                font-size: 14px;
                color: #373737;
              }

              .guide-name {
                font-family: Montserrat, sans-serif;
                font-weight: 500;
                font-size: 14px;
                color: #737373;
                margin-bottom: 10px;
              }

              .sub-title {
                font-family: Montserrat, sans-serif;
                font-weight: 600;
                font-size: 12px;
                color: #4f4b4b;
              }

              .sub-title-description {
                font-family: Montserrat, sans-serif;
                font-weight: 600;
                font-size: 12px;
                color: #4f4b4b;
                margin-top: 10px;
              }

              .text-description {
                font-family: Montserrat, sans-serif;
                text-align: justify;
                font-weight: 400;
                font-size: 13px;
                color: #737373;

                h5 {
                  font-size: 11px !important;
                }
              }
            }
          }
        }
      }
    }
  }
</style>
