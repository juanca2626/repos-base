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
      <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
          <div class="title-section">
            <IconCircleCheck class="icon-title" color="#3D3D3D" />
            <span class="modal-title">Servicios Programados</span>
          </div>
          <div
            class="actions-section"
            data-html2canvas-ignore="true"
            v-if="
              reportServices.services.length > 0 ||
              reportServices.hotels.length > 0 ||
              reportServices.trains.length > 0 ||
              reportServices.flights.length > 0
            "
          >
            <span>Descarga en formato:</span>

            <a-button
              type="default"
              size="large"
              class="btn-download-pdf"
              @click="exportToPDF"
              :disabled="loadingDownload || downloadStore.isFileLoading(fileStore.getFile.id)"
            >
              <a-spin v-if="downloadStore.isFileLoading(fileId)" :indicator="indicator" />
              <IconFilePdf v-else color="#FFFFFF" />
            </a-button>
          </div>
        </div>

        <div
          v-if="
            reportServices.services.length > 0 ||
            reportServices.hotels.length > 0 ||
            reportServices.trains.length > 0 ||
            reportServices.flights.length > 0
          "
        >
          <!-- Datos de file -->
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

          <!-- Servicios -->
          <div class="box-title-services" v-if="reportServices.services.length > 0">
            <a-row :gutter="16" align="middle">
              <a-col :span="24">
                <a-divider
                  orientation="left"
                  orientation-margin="1px"
                  style="border-color: #e9e9e9; height: 1px"
                >
                  <div class="sub-title">Servicios</div>
                </a-divider>
              </a-col>
            </a-row>
          </div>
          <div class="modal-body" v-if="reportServices.services.length > 0">
            <table class="table-row-services">
              <thead class="table-header-services">
                <tr>
                  <th width="12%">Fecha</th>
                  <th width="20%">Ciudad</th>
                  <th width="68%">Descripción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="service in reportServices.services">
                  <td width="12%" align="center" class="td-center-top">
                    <div class="service-date">{{ formatDate(service.date, 'short') }}</div>
                  </td>
                  <td width="20%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ service.city_in_name }}
                      <font-awesome-icon :icon="['fas', 'chevron-right']" class="mx-1" />
                      {{ service.city_out_name }}
                    </div>
                  </td>
                  <td width="68%" class="td-center-top">
                    <a-row :gutter="16" align="top" v-for="itinerary in service.itineraries">
                      <a-col :span="4">
                        <div class="service-time" v-if="itinerary.start_time">
                          <font-awesome-icon :icon="['far', 'clock']" />
                          {{ itinerary.start_time }}
                        </div>
                        <div class="service-time" v-else>
                          <font-awesome-icon :icon="['far', 'clock']" />
                          --:--
                        </div>
                      </a-col>
                      <a-col :span="20">
                        <div class="service-name">{{ itinerary.name }}</div>
                      </a-col>
                    </a-row>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Hoteles -->
          <div class="box-title-services mt-3" v-if="reportServices.hotels.length > 0">
            <a-row :gutter="16" align="middle">
              <a-col :span="24">
                <a-divider
                  orientation="left"
                  orientation-margin="1px"
                  style="border-color: #e9e9e9; height: 1px"
                >
                  <div class="sub-title">Hoteles</div>
                </a-divider>
              </a-col>
            </a-row>
          </div>
          <div class="modal-body" v-if="reportServices.hotels.length > 0">
            <table class="table-row-services">
              <thead class="table-header-services">
                <tr>
                  <th width="5%">Ciudad</th>
                  <th width="30%">Hotel</th>
                  <th width="10%">Confirmación</th>
                  <th width="25%">Tipo de habitación</th>
                  <th width="10%">Fecha In</th>
                  <th width="10%">Fecha Out</th>
                  <th width="10%">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="hotel in reportServices.hotels">
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">{{ hotel.city }}</div>
                  </td>
                  <td width="30%" align="center" class="td-center-top">
                    <div class="service-city">
                      <IconHotel color="#4F4B4B" width="1.2em" height="1.2em" />
                      {{ hotel.hotel }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ hotel.confirmation }}
                    </div>
                  </td>
                  <td width="25%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ hotel.room }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(hotel.date_in, 'short') }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(hotel.date_out, 'short') }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      <a-tag color="#28A745" class="tag-status" v-if="hotel.status == 'OK'"
                        >{{ hotel.status }}
                      </a-tag>
                      <a-tag color="#D80404" class="tag-status" v-else>{{ hotel.status }}</a-tag>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Trenes -->
          <div class="box-title-services mt-3" v-if="reportServices.trains.length > 0">
            <a-row :gutter="16" align="middle">
              <a-col :span="24">
                <a-divider
                  orientation="left"
                  orientation-margin="1px"
                  style="border-color: #e9e9e9; height: 1px"
                >
                  <div class="sub-title">Trenes</div>
                </a-divider>
              </a-col>
            </a-row>
          </div>
          <div class="modal-body" v-if="reportServices.trains.length > 0">
            <table class="table-row-services">
              <thead class="table-header-services">
                <tr>
                  <th>Ciudad</th>
                  <th>Servicios</th>
                  <th>Confirmación</th>
                  <th>Pax</th>
                  <th>Salida</th>
                  <th>Horario</th>
                  <th>Llegada</th>
                  <th>Horario</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="train in reportServices.trains">
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">{{ train.city }}</div>
                  </td>
                  <td width="50%" align="center" class="td-center-top">
                    <div class="service-city">
                      <font-awesome-icon :icon="['fas', 'train']" />
                      {{ train.name }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ train.confirmation }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ train.pax }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(train.date_in, 'short') }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ train.start_time }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(train.date_out, 'short') }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ train.departure_time }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      <a-tag color="#28A745" class="tag-status" v-if="train.status == 'OK'"
                        >{{ train.status }}
                      </a-tag>
                      <a-tag color="#D80404" class="tag-status" v-else>{{ train.status }}</a-tag>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Vuelos -->
          <div class="box-title-services mt-3" v-if="reportServices.flights.length > 0">
            <a-row :gutter="16" align="middle">
              <a-col :span="24">
                <a-divider
                  orientation="left"
                  orientation-margin="1px"
                  style="border-color: #e9e9e9; height: 1px"
                >
                  <div class="sub-title">Vuelos</div>
                </a-divider>
              </a-col>
            </a-row>
          </div>
          <div class="modal-body" v-if="reportServices.flights.length > 0">
            <table class="table-row-services">
              <thead class="table-header-services">
                <tr>
                  <th>Ciudad</th>
                  <th>Servicios</th>
                  <th>Confirmación</th>
                  <th>Pax</th>
                  <th>Salida</th>
                  <th>Horario</th>
                  <th>Llegada</th>
                  <th>Horario</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="flight in reportServices.flights">
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">{{ flight.city_in_name }}</div>
                  </td>
                  <td width="50%" align="center" class="td-center-top">
                    <div class="service-city">
                      <font-awesome-icon :icon="['fas', 'plane']" />
                      ({{ flight.airline_code }}) {{ flight.airline_name }} -
                      {{ flight.airline_number }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ flight.confirmation }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ flight.pax }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(flight.date_in, 'short') }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ flight.start_time }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ formatDate(flight.date_out, 'short') }}
                    </div>
                  </td>
                  <td width="5%" align="center" class="td-center-top">
                    <div class="service-city">
                      {{ flight.departure_time }}
                    </div>
                  </td>
                  <td width="10%" align="center" class="td-center-top">
                    <div class="service-city">
                      <a-tag color="#28A745" class="tag-status" v-if="flight.status == 'OK'">
                        {{ flight.status }}
                      </a-tag>
                      <a-tag color="#D80404" class="tag-status" v-if="flight.status == 'RQ'"
                        >{{ flight.status }}
                      </a-tag>
                    </div>
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
  import { h, ref, watch } from 'vue';
  import IconFilePdf from '@/components/icons/IconFilePdf.vue';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import { notification } from 'ant-design-vue';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';
  import IconHotel from '@/components/icons/IconHotel.vue';
  import { LoadingOutlined } from '@ant-design/icons-vue';

  const loadingDownload = ref(false);
  const emit = defineEmits(['update:isOpen', 'submit']);
  const downloadStore = useDownloadStore();
  const fileStore = useFilesStore();
  const file = fileStore.getFile;
  const router = useRouter();
  const fileId = router.currentRoute.value.params.id;
  const reportServices = ref({
    services: [],
    hotels: [],
    trains: [],
    flights: [],
  });

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

  // Método para exportar a PDF
  const exportToPDF = async () => {
    try {
      downloadStore.downloadFileDocuments(fileId, 'skeleton', 'pdf', file.lang);
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
          reportServices.value = await downloadStore.getReportServiceByFileId(fileId, 'all');
        } catch (error) {
          console.error('Error al cargar detalles del statement:', error);
        }
      }
    }
  );
</script>

<style scoped lang="scss">
  .file-modal {
    .tag-status {
      font-family: Montserrat, serif;
      font-size: 10px;
      font-weight: 700;
    }

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

    .modal-container {
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

      .table-row-services {
        background-color: #fafafa;
        border-radius: 8px;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;

        .table-header-services {
          font-family: Montserrat, sans-serif;
          width: 100%;
          border-collapse: collapse;

          tr {
            th {
              padding: 10px 20px;
              font-weight: 700;
              font-size: 14px;
              color: #3d3d3d;
              text-align: center;
              background: #ffffff;
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
                //display: flex;
                //flex-direction: row;
                //align-content: center;
                //justify-content: center;
                //align-items: center;
                //gap: 10px;
              }

              .service-name {
                font-family: Montserrat, sans-serif;
                font-weight: 400;
                font-size: 12px;
                color: #737373;
                margin-bottom: 10px;
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
                font-size: 12px;
                color: #3d3d3d;
                margin-top: 10px;
                margin-bottom: 10px;
              }
            }
          }
        }
      }
    }
  }
</style>
