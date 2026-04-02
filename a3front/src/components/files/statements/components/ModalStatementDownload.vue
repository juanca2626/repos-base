<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :focusTriggerAfterClose="false"
      width="1026px"
    >
      <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
          <div class="title-section">
            <IconFileText class="icon-title" color="#3D3D3D" />
            <span class="modal-title text-uppercase">Statement</span>
          </div>
          <div
            class="actions-section d-block text-right"
            v-if="statementDetails.details.length > 0"
          >
            <span class="d-block m-0">{{ t('files.label.download_in_format') }}:</span>
            <div class="d-block my-2">
              <a-row type="flex" justify="end" align="middle" style="gap: 7px">
                <template v-for="language in languagesStore.getLanguages">
                  <a-col v-if="language.value != 'pt'">
                    <span
                      :class="['m-0 cursor-pointer d-flex text-500']"
                      style="gap: 5px"
                      @click="changeDownloadLanguage(language.value)"
                    >
                      <font-awesome-icon
                        :icon="[
                          'far',
                          activeLanguage === language.value ? 'circle-check' : 'circle',
                        ]"
                      />
                      <small>{{ language.label }}</small>
                    </span>
                  </a-col>
                </template>
              </a-row>
            </div>
            <div class="d-block">
              <a-button
                type="primary"
                danger
                size="large"
                @click="exportToPDF"
                :loading="loadingDownload"
              >
                <font-awesome-icon :icon="['fas', 'download']" class="me-2" />
                <small class="text-uppercase">{{ t('global.button.download') }}</small>
              </a-button>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div
            class="container-statement"
            v-if="statementDetails.details.length > 0 && !downloadStore.loading"
          >
            <a-row :gutter="16" align="stretch" justify="space-evenly">
              <a-col :span="12">
                <img src="/images/logo_limatours.png" alt="Limatours" class="logo" />
              </a-col>
              <a-col :span="12" class="pull-left">
                <img src="/images/ISO-9001-01.jpg" alt="CSR" class="logo-iso" height="100px" />
                <img src="/images/CSR.jpg" alt="CSR" class="logo-iso" height="90px" />
                <img src="/images/Gold-Member.png" alt="CSR" class="logo-iso" height="100px" />
              </a-col>
            </a-row>

            <a-row :gutter="16" align="middle" justify="space-between">
              <a-col :span="14">
                <div class="info-section">
                  <div class="client-info">
                    <div>
                      <div style="font-size: 12px; font-weight: 400">
                        {{ statementDetails.date }}
                      </div>
                      <div style="font-size: 14px; font-weight: 700" class="my-1">
                        {{ statementDetails.file_client.name }}
                      </div>
                      <div style="font-size: 12px; font-weight: 400">
                        {{ statementDetails.file_client.country }}
                      </div>
                    </div>
                  </div>
                </div>
              </a-col>
              <a-col :span="10">
                <div class="info-section">
                  <div class="invoice-info">
                    <div>
                      <div style="font-size: 12px; font-weight: 400">
                        {{ statementDetails.limatours.address }}
                      </div>
                      <div style="font-size: 12px; font-weight: 400">
                        {{ statementDetails.limatours.city }}
                      </div>
                      <div style="font-size: 12px; font-weight: 400">
                        RUC : {{ statementDetails.limatours.ruc }}
                      </div>
                    </div>
                  </div>
                </div>
              </a-col>
            </a-row>

            <a-row :gutter="16" align="middle" justify="space-between">
              <a-col :span="24">
                <div class="statement-nif my-5">
                  VAT/NIF/ID : {{ statementDetails.file_client.ruc }}
                </div>
              </a-col>
            </a-row>

            <a-row :gutter="16" align="middle" justify="space-between">
              <a-col :span="24">
                <div class="invoice-number">
                  <span class="text-uppercase">
                    {{ t('global.label.bill') }}
                  </span>
                  No: {{ statementDetails.file_number }}
                </div>
              </a-col>
            </a-row>

            <a-row align="middle" justify="space-between" class="my-3">
              <a-col>
                <div class="invoice-ref">REF: {{ statementDetails.file_ref }}</div>
              </a-col>
              <a-col>
                <div class="invoice-group text-uppercase">
                  {{ t('files.label.pax_name_group') }} :
                  <span class="invoice-data">{{ statementDetails.file_name }}</span>
                </div>
              </a-col>
            </a-row>

            <a-row :gutter="16" align="middle" justify="space-between" class="my-3">
              <a-col :span="12" class="text-left">
                <div class="invoice-ref text-uppercase">
                  {{ t('files.label.arrival_date') }}:
                  <span class="invoice-data">{{ statementDetails.file_date_in }}</span>
                </div>
              </a-col>
              <a-col :span="12" class="text-right">
                <div class="invoice-ref text-uppercase">
                  {{ t('files.label.departure_date') }}:
                  <span class="invoice-data">{{ statementDetails.file_date_out }}</span>
                </div>
              </a-col>
            </a-row>
            <!-- Logo and Invoice Info -->

            <!-- Due Date -->
            <div class="due-date">
              <a-alert class="due-date-alert" type="info">
                <template #message>
                  <div class="message">
                    <font-awesome-icon :icon="['far', 'clock']" />
                    {{ t('global.label.deadline') }}: <span>{{ statementDetails.deadline }}</span>
                  </div>
                </template>
              </a-alert>
            </div>

            <a-divider
              style="margin-top: 15px; margin-bottom: 15px; background-color: #e9e9e9; height: 1px"
            />
            <div class="payment-details-message text-uppercase">
              {{ t('global.message.payment_details_title') }}:
            </div>
            <a-divider
              style="margin-top: 15px; margin-bottom: 15px; background-color: #e9e9e9; height: 1px"
            />

            <!-- Table Section -->
            <div class="table-section">
              <div class="invoice-grid header">
                <div class="mx-3 text-uppercase">{{ t('global.column.description') }}</div>
                <div class="text-center text-uppercase">{{ t('global.column.quantity') }}</div>
                <div class="text-center text-uppercase">{{ t('global.column.unit_price') }}</div>
                <div class="text-center text-uppercase">{{ t('global.column.amount') }}</div>
              </div>
              <!-- Rows -->
              <div
                class="invoice-grid row"
                v-for="(item, index) in statementDetails.details"
                :key="index"
              >
                <div class="mx-3 text-left text-uppercase">{{ item.description }}</div>
                <div class="text-center">{{ item.quantity }}</div>
                <div class="text-center">
                  $ {{ formatNumber({ number: item.unit_price, digits: 2 }) }}
                </div>
                <div class="text-center">
                  $ {{ formatNumber({ number: item.amount, digits: 2 }) }}
                </div>
              </div>

              <div class="invoice-grid-footer">
                <div></div>
                <div class="text-center total-label">{{ t('global.column.total') }}:</div>
                <div class="text-center">&nbsp;</div>
                <div class="text-center row-total-amount">
                  $ {{ formatNumber({ number: statementDetails.total, digits: 2 }) }}
                </div>
              </div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
              <div class="payment-details-header">
                <div class="payment-details-title">{{ t('global.label.payment_details') }}:</div>
                <a href="https://www.litopay.pe/" class="payment-details-litopay" target="_blank">
                  LITOPAY
                </a>
              </div>
              <a-divider
                style="
                  margin-top: 15px;
                  margin-bottom: 15px;
                  background-color: #e9e9e9;
                  height: 1px;
                "
              />
              <div class="payment-details-message">
                {{ t('global.message.payment_details') }}
              </div>
              <a-divider
                style="
                  margin-top: 15px;
                  margin-bottom: 15px;
                  background-color: #e9e9e9;
                  height: 1px;
                "
              />

              <a-row :gutter="24">
                <a-col :span="3">
                  <span class="payment-details-bank">{{ t('global.label.bank') }}:</span>
                </a-col>
                <a-col :span="9">
                  <div class="payment-details-bank-name">
                    {{ statementDetails.limatours.bank.name }}
                  </div>
                  <span class="payment-details-bank">
                    {{ statementDetails.limatours.bank.address }}
                  </span>
                  <br />
                  <span class="payment-details-bank">
                    {{ statementDetails.limatours.bank.city }}
                  </span>
                </a-col>
                <a-col :span="3">
                  <a-row :gutter="24">
                    <a-col :span="24">
                      <span class="payment-details-name"> SWIFT: </span>
                    </a-col>
                    <a-col :span="24">
                      <span class="payment-details-group-name">
                        {{ statementDetails.limatours.swift }}
                      </span>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col :span="5">
                  <a-row :gutter="24">
                    <a-col :span="24">
                      <span class="payment-details-name text-uppercase"
                        >{{ t('global.label.credit_to') }}:</span
                      >
                    </a-col>
                    <a-col :span="24">
                      <span class="payment-details-number">
                        {{ statementDetails.limatours.credit_to }}
                      </span>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col :span="4">
                  <a-row :gutter="24">
                    <a-col :span="24">
                      <span class="payment-details-name">
                        {{ t('global.label.account_number') }}
                      </span>
                    </a-col>
                    <a-col :span="24">
                      <span class="payment-details-number">
                        {{ statementDetails.limatours.account }}
                      </span>
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>

              <div class="payment-details-footer">
                <a-row type="flex" style="gap: 7px" justify="space-between">
                  <a-col>
                    <div class="payment-details-message text-uppercase">
                      <IconCircleExclamation color="#eb5757" width="1.2em" height="1.2em" />
                      {{ t('global.label.for') }}
                      <span
                        style="
                          color: #212529;
                          font-weight: 700;
                          padding-left: 5px;
                          padding-right: 5px;
                        "
                      >
                        {{ t('global.label.identify_your_payment') }}</span
                      >
                      {{ t('global.label.please') }}
                      <span
                        style="
                          color: #212529;
                          font-weight: 700;
                          padding-left: 5px;
                          padding-right: 5px;
                        "
                        >{{ t('global.label.include') }}</span
                      >
                      {{ t('global.label.the_reference') }}:
                    </div>
                  </a-col>
                  <a-col>
                    <ul class="numbered-list">
                      <li>
                        <div class="circle">1</div>
                        <span>{{ t('files.label.number_file') }}</span>
                      </li>
                      <li>
                        <div class="circle">2</div>
                        <span>{{ t('global.label.account_state') }}</span>
                      </li>
                      <li>
                        <div class="circle">3</div>
                        <span>{{ t('files.label.pax_group') }}</span>
                      </li>
                    </ul>
                  </a-col>
                </a-row>
              </div>
            </div>
          </div>
          <div v-else>
            <a-empty v-if="!downloadStore.loading">
              <template #description>
                <span> No se encuentra disponible ningún detalle de pago </span>
              </template>
            </a-empty>
            <div v-else class="loading-statement">
              <a-spin size="large" :tip="`${t('files.message.loading')}...`" />
            </div>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
  import { useI18n } from 'vue-i18n';
  import { ref, watch, onMounted } from 'vue';
  import IconFileText from '@/components/icons/IconFileText.vue';
  import IconCircleExclamation from '@/components/icons/IconCircleExclamation.vue';
  import { notification } from 'ant-design-vue';

  import { useLanguagesStore } from '@store/global';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';

  import { formatNumber } from '@/utils/files.js';

  const { t } = useI18n({
    useScope: 'global',
  });

  const loadingDownload = ref(false);
  const activeLanguage = ref('');
  const languagesStore = useLanguagesStore();
  const downloadStore = useDownloadStore();
  const filesStore = useFilesStore();
  const emit = defineEmits(['update:isOpen', 'submit']);
  const router = useRouter();
  const file_id = router.currentRoute.value.params.id;
  const statementDetails = ref({
    date: '',
    deadline: '',
    file_number: '',
    file_name: '',
    file_ref: '',
    file_date_in: '',
    file_date_out: '',
    limatours: {
      address: '',
      city: '',
      ruc: '',
    },
    file_client: {
      name: '',
      address: '',
      ruc: '',
    },
    details: [],
    total: 0,
  });
  // Propiedades
  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  onMounted(() => {
    activeLanguage.value = filesStore.getFile.lang;
  });

  const changeDownloadLanguage = (lang) => {
    activeLanguage.value = lang;
  };

  // Formateador de fechas
  const formatDate = (dateString, format) => {
    const date = new Date(dateString);

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

  const exportToPDF = async () => {
    try {
      downloadStore.downloadFileDocuments(file_id, 'invoice', 'pdf', activeLanguage.value);
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

  watch(
    () => props.isOpen,
    async (newValue) => {
      if (newValue) {
        try {
          const details = await downloadStore.getStatementByFileId(file_id);

          // Formatear fechas
          statementDetails.value = {
            ...details,
            date: formatDate(details.date, 'long'),
            file_date_in: formatDate(details.file_date_in, 'long-comma'),
            file_date_out: formatDate(details.file_date_in, 'long-comma'),
            deadline: formatDate(details.deadline, 'short'),
          };
        } catch (error) {
          console.error('Error al cargar detalles del statement:', error);
        }
      }
    }
  );

  defineExpose({
    exportToPDF,
  });
</script>

<style scoped lang="scss">
  .file-modal {
    .font-w-700 {
      font-weight: 700;
    }

    .pull-left {
      display: flex;
      flex-direction: row;
      justify-content: flex-end;
    }

    .modal-container {
      padding: 20px;
    }

    .modal-header {
      font-family: Montserrat, sans-serif;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      background-color: #ffffff;
      color: #3d3d3d;

      .title-section {
        display: flex;
        align-items: center;

        .icon-title {
          font-size: 36px;
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
      padding: 30px !important;

      .logo {
        max-width: 198px;
      }

      .logo-iso {
        margin-left: 15px;
      }
    }

    .info-section {
      .client-title-info {
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 16px;
        margin-top: 50px;
      }

      .client-info {
        font-family: Montserrat, serif;
        font-weight: 700;
        font-size: 18px;
      }

      .invoice-info {
        font-family: Montserrat, serif;
        display: flex;
        justify-content: flex-end;
        width: 100%;
        flex-direction: row;
        text-align: right;
      }
    }

    .invoice-number {
      font-family: Montserrat, serif;
      font-weight: 700;
      font-size: 18px;
      text-align: center;
    }

    .invoice-ref,
    .invoice-data {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 14px;
    }

    .invoice-data {
      font-weight: 700;
    }

    .statement-nif {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 14px;
    }

    .loading-statement {
      display: flex;
      justify-content: center;
      flex-direction: column;
      align-content: center;
      align-items: center;
      gap: 10px;
    }

    .due-date {
      margin-top: 40px;
      margin-bottom: 20px;

      .due-date-alert {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 16px;
        background-color: #fafafa;
        border: 1px solid #fafafa;

        .message {
          display: flex;
          justify-content: flex-end;
          align-items: center;
          font-family: Montserrat, serif;
          font-weight: 400;
          font-size: 16px;
          gap: 5px;
        }

        :deep(.ant-alert-message) {
          color: #4f4b4b !important;
          font-size: 14px;
          font-weight: 400;
          text-align: right;

          span {
            font-weight: 600;
            font-size: 14px;
            text-align: right;
          }

          svg {
            display: flex;
            justify-content: flex-end;
            color: #4f4b4b !important;
          }
        }
      }
    }

    .table-section {
      margin-bottom: 20px;

      .invoice-grid {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
      }

      .invoice-grid-footer {
        font-family: Montserrat, serif;
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr;
        align-items: center;

        /* Fondo de color a partir de la segunda columna */
        > div:nth-child(2) {
          padding: 10px 12px;
          background-color: #e9e9e9;
        }

        > div:nth-child(3) {
          padding: 7.5px;
          background-color: #e9e9e9;
        }

        > div:nth-child(2) {
          border-bottom-left-radius: 6px;
          border-top-left-radius: 6px;
        }

        > div:nth-child(4) {
          padding: 7px;
          background-color: #e9e9e9;
        }

        > div:nth-child(4) {
          border-bottom-right-radius: 6px;
          border-top-right-radius: 6px;
        }

        > div:nth-child(1) {
          padding: 10px 0;
          background-color: transparent; /* La primera columna sin fondo */
        }
      }

      .total-label {
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 14px;
      }

      .header {
        font-family: Montserrat, serif;
        font-weight: 700;
        font-size: 14px;
      }

      .row {
        background-color: #fafafa;
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 14px;
        padding: 10px 0;
        margin-bottom: 15px;
        border-radius: 6px;
      }

      .row-total-amount {
        font-weight: 700;
        font-size: 18px;
      }
    }

    .total-section {
      font-family: Montserrat, serif;
      display: flex;
      justify-content: space-between;
      font-size: 16px;
      font-weight: bold;
      margin-top: 20px;

      .total-amount {
        color: #ff4d4f;
      }
    }

    .payment-details {
      margin-top: 50px;

      &-header {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-content: center;
        align-items: center;
        justify-content: space-between;
      }

      .payment-details-bank,
      .payment-details-name,
      .payment-details-group-name,
      .payment-details-name,
      .payment-details-number,
      .payment-details-bank-name {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 400;
        color: #737373;
      }

      .payment-details-bank-name,
      .payment-details-number,
      .payment-details-group-name {
        font-weight: 600;
      }

      &-footer {
        background-color: #fafafa;
        margin-top: 20px;
        padding: 20px 22px;
        border-radius: 6px;

        .payment-details-message {
          display: flex;
          font-family: Montserrat, serif;
          font-size: 12px;
          font-weight: 400;
          color: #212529;
          margin-bottom: 10px;
          align-items: center;

          svg {
            color: #eb5757;
            font-size: 1.2rem;
            margin-right: 5px;
          }
        }

        .numbered-list {
          list-style: none;
          padding: 0;
          margin: 0;
        }

        .numbered-list li {
          font-family: Montserrat, serif;
          display: flex;
          align-items: center;
          margin-bottom: 10px;
          font-size: 12px;
          color: #333;
        }

        .numbered-list .circle {
          font-family: Montserrat, serif;
          width: 21px;
          height: 21px;
          background-color: #55a3ff;
          color: #fff;
          font-size: 12px;
          font-weight: 600;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-right: 5px;
        }
      }

      &-litopay {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 700;
        color: #eb5757;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 3px;
      }

      &-title {
        font-family: Montserrat, serif;
        font-size: 16px;
        font-weight: 600;
        color: #737373;
      }

      &-message {
        font-family: Montserrat, serif;
        font-size: 12px;
        font-weight: 400;
        color: #212529;
      }

      ul {
        margin: 10px 0 0 20px;
      }
    }
  }
</style>
