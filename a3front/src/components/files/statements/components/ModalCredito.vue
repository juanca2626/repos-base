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
      <div class="modal-container" v-if="!loading">
        <!-- Header -->
        <div class="modal-header">
          <div class="title-section">
            <IconFileText class="icon-title" color="#3D3D3D" />
            <span class="modal-title text-uppercase">{{ t('global.label.credit_note') }}</span>
          </div>
        </div>

        <!-- Body -->
        <div class="d-block">
          <div v-bind:class="['mb-5']" style="border-radius: 10px">
            <a-row
              type="flex"
              justify="space-between"
              align="top"
              style="border: 1px solid #e9e9e9; border-radius: 8px; flex-flow: nowrap; gap: 1rem"
              class="p-4"
            >
              <a-col>
                <b>{{ statementDetails.file_client.name }}</b>
                <p class="m-0 p-0">
                  {{ statementDetails.file_client.address }},
                  {{ statementDetails.file_client.city }}
                </p>
                <p class="m-0 p-0">{{ statementDetails.file_client.country }}</p>
                <br />
                <p>VAT/NIF/ID : {{ statementDetails.file_client.ruc }}</p>
              </a-col>
              <a-col>
                <p class="m-0 p-0">{{ statementDetails.limatours.address }}</p>
                <p class="m-0 p-0">{{ statementDetails.limatours.city }}</p>
                <p class="m-0 p-0">RUC : {{ statementDetails.limatours.ruc }}</p>
              </a-col>
            </a-row>
          </div>

          <div v-bind:class="['mb-5']" style="border-radius: 10px">
            <div style="border: 1px solid #e9e9e9; border-radius: 8px" class="p-4">
              <a-row :gutter="16" align="middle" justify="space-between">
                <a-col :span="24">
                  <div class="invoice-number text-uppercase">
                    {{ t('global.label.credit_note') }}
                  </div>
                </a-col>
              </a-row>

              <a-row align="middle" justify="space-between" class="my-3">
                <a-col>
                  <div class="invoice-ref">YOUR REF: {{ statementDetails.file_ref }}</div>
                </a-col>
                <a-col>
                  <div class="invoice-group">
                    {{ t('files.label.pax_name_group') }} :
                    <span class="invoice-data">{{ statementDetails.file_name }}</span>
                  </div>
                </a-col>
              </a-row>

              <a-row :gutter="16" align="middle" justify="space-between" class="my-3">
                <a-col :span="12" class="text-left">
                  <div class="invoice-ref">
                    {{ t('files.label.arrival_date') }}:
                    <span class="invoice-data">{{ statementDetails.file_date_in }}</span>
                  </div>
                </a-col>
                <a-col :span="12" class="text-right">
                  <div class="invoice-ref">
                    {{ t('files.label.departure_date') }}:
                    <span class="invoice-data">{{ statementDetails.file_date_out }}</span>
                  </div>
                </a-col>
              </a-row>
              <!-- Logo and Invoice Info -->
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-section" v-if="items.length > 0">
            <div class="invoice-grid header">
              <div class="text-center text-uppercase">{{ t('global.column.description') }}</div>
              <div class="text-center text-uppercase">{{ t('global.column.quantity') }}</div>
              <div class="text-center text-uppercase">{{ t('global.column.unit_price') }}</div>
              <div class="text-center text-uppercase">{{ t('global.column.amount') }}</div>
              <div class="text-center text-uppercase">{{ t('global.column.actions') }}</div>
            </div>
            <!-- Rows -->
            <div class="invoice-grid row" v-for="(item, index) in items" :key="index">
              <div class="mx-2 text-left">
                <a-select
                  :allowClear="false"
                  class="w-100"
                  v-model:value="item.detail_id"
                  :showSearch="true"
                  :field-names="{ label: 'description', value: 'id' }"
                  :options="statementDetails.details"
                  @change="setDetail(item)"
                >
                </a-select>
              </div>
              <div class="text-center">
                <template v-if="item.detail">
                  {{ item.detail.quantity }}
                </template>
              </div>
              <div class="text-center">
                <template v-if="item.detail">
                  $ {{ formatNumber({ number: item.detail.unit_price, digits: 2 }) }}
                </template>
              </div>
              <div class="text-center">
                <template v-if="item.detail">
                  $
                  {{
                    formatNumber({
                      number: item.detail.quantity * item.detail.unit_price || 0,
                      digits: 2,
                    })
                  }}
                </template>
              </div>
              <div class="text-center"></div>
              <div class="mx-2 text-left">
                <a-input v-model:value="item.description" style="font-size: 12px" />
              </div>
              <div class="text-center">
                <a-input type="number" placeholder="0" v-model:value="item.quantity" />
              </div>
              <div class="text-center">
                <a-input type="number" placeholder="0.00" v-model:value="item.unit_price" />
              </div>
              <div class="text-center">
                $ {{ formatNumber({ number: item.quantity * item.unit_price || 0, digits: 2 }) }}
              </div>
              <div class="text-center mx-5">
                <a-row type="flex" justify="space-between" align="middle" style="gap: 3px">
                  <template v-if="items.length > 1">
                    <a-col>
                      <span class="d-flex cursor-pointer" @click="removeItem(index)">
                        <font-awesome-icon :icon="['fas', 'minus']" />
                      </span>
                    </a-col>
                  </template>
                  <template v-if="index == items.length - 1">
                    <a-col>
                      <span
                        class="d-flex cursor-pointer"
                        @click="addItem"
                        v-if="item.quantity * item.unit_price > 0"
                      >
                        <font-awesome-icon :icon="['fas', 'plus']" />
                      </span>
                    </a-col>
                  </template>
                </a-row>
              </div>
            </div>

            <div class="invoice-grid-footer">
              <div></div>
              <div class="text-center total-label">{{ t('global.column.total') }}:</div>
              <div class="text-center">&nbsp;</div>
              <div class="text-center">&nbsp;</div>
              <div class="text-center row-total-amount">
                $ {{ formatNumber({ number: totalAmount, digits: 2 }) }}
              </div>
            </div>
          </div>

          <div class="text-center my-5 pt-5">
            <a-button class="text-600" @click="handleCancel" danger size="large">
              {{ t('global.button.cancel') }}
            </a-button>
            <a-button
              type="primary"
              v-bind:disabled="totalAmount == 0 || filesStore.isLoadingAsync"
              v-on:click="handleProcess()"
              class="mx-2 px-4 text-600"
              default
              size="large"
            >
              {{ t('global.button.save') }}
            </a-button>
          </div>
        </div>
      </div>
      <template v-if="loading">
        <a-alert type="warning" class="mb-0">
          <template #description>
            <a-row type="flex" justify="start" align="top" style="gap: 10px">
              <a-col>
                <p class="m-0 p-0">{{ t('files.message.loading') }}...</p>
              </a-col>
            </a-row>
          </template>
        </a-alert>
      </template>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, onBeforeMount, computed } from 'vue';
  import IconFileText from '@/components/icons/IconFileText.vue';
  import { formatNumber } from '@/utils/files.js';
  import { useFilesStore } from '@store/files';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();

  const emit = defineEmits(['update:isOpen', 'handleSearchStatements']);
  // Propiedades
  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
    statementDetails: {
      type: Object,
      default: () => null,
    },
  });

  const items = ref([]);

  const handleCancel = () => {
    emit('update:isOpen', false);
  };

  const totalAmount = computed(() => {
    const new_items = items.value;
    const total = new_items.reduce((sum, item) => {
      const amount = parseFloat(item.unit_price * item.quantity) || 0; // Asegura que sea un número válido
      return sum + amount;
    }, 0);

    return total;
  });

  const addItem = () => {
    const item = {
      description: '',
      quantity: '',
      unit_price: '',
      amount: '',
    };

    items.value = [...items.value, item];
  };

  const removeItem = (index) => {
    items.value.splice(index, 1);
  };

  onBeforeMount(async () => {
    addItem();
  });

  const handleProcess = async () => {
    await filesStore.storeCreditNote(filesStore.getFile.id, items.value);

    if (filesStore.getError == null || filesStore.getError == '') {
      handleCancel();
      filesStore.initedStatements();
      emit('handleSearchStatements');
    }
  };

  const setDetail = (item) => {
    item.detail = props.statementDetails.details.find((detail) => detail.id === item.detail_id);
  };
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
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr 1fr;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
      }

      .invoice-grid-footer {
        font-family: Montserrat, serif;
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr 1fr;
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
