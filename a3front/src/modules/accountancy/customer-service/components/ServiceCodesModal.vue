<template>
  <a-modal
    v-model:open="visible"
    title="Gestión de Códigos de Servicio"
    width="900px"
    :footer="null"
    @cancel="handleClose"
  >
    <div class="service-codes-modal-content">
      <!-- Add New Service Code Section -->
      <div class="add-service-section">
        <h4 class="section-title">Agregar Nuevo Código de Servicio</h4>
        <a-form layout="vertical">
          <a-row :gutter="16">
            <a-col :span="8">
              <a-form-item label="Código de Servicio">
                <a-input
                  v-model:value="newServiceCode.codsvs"
                  placeholder="Ej: CODSVS001"
                  :disabled="saving"
                />
              </a-form-item>
            </a-col>
            <a-col :span="16">
              <a-form-item label="Descripción">
                <a-input
                  v-model:value="newServiceCode.comment"
                  placeholder="Descripción del código de servicio"
                  :disabled="saving"
                />
              </a-form-item>
            </a-col>
          </a-row>
          <a-button
            type="primary"
            @click="handleAddServiceCode"
            :loading="saving"
            :disabled="!newServiceCode.codsvs.trim() || !newServiceCode.comment.trim()"
          >
            <template #icon><PlusOutlined /></template>
            Agregar Código de Servicio
          </a-button>
        </a-form>
      </div>

      <a-divider />

      <!-- Existing Service Codes List -->
      <div class="service-codes-list-section">
        <h4 class="section-title">Códigos de Servicio Registrados ({{ serviceCodes.length }})</h4>

        <a-spin :spinning="loading">
          <div v-if="serviceCodes.length === 0" class="empty-state">
            <InboxOutlined style="font-size: 48px; color: #d9d9d9" />
            <p>No hay códigos de servicio registrados</p>
          </div>

          <div v-else class="service-codes-table">
            <a-table
              :columns="columns"
              :data-source="serviceCodes"
              :pagination="false"
              size="small"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'actions'">
                  <a-popconfirm
                    title="¿Está seguro de eliminar este código de servicio?"
                    @confirm="handleRemoveServiceCode(record)"
                    ok-text="Sí"
                    cancel-text="No"
                  >
                    <a-button type="ghost" danger size="small">
                      <template #icon><DeleteOutlined /></template>
                    </a-button>
                  </a-popconfirm>
                </template>
              </template>
            </a-table>
          </div>
        </a-spin>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
  import { ref, reactive, watch } from 'vue';
  import { PlusOutlined, DeleteOutlined, InboxOutlined } from '@ant-design/icons-vue';
  import { useClaimsApi } from '../composables/claimsComposable';

  const props = defineProps({
    open: {
      type: Boolean,
      default: false,
    },
    codref: {
      type: Number,
      required: true,
    },
    nrocom: {
      type: String,
      required: true,
    },
  });

  const emit = defineEmits(['update:open', 'refresh']);

  const visible = ref(props.open);
  const newServiceCode = reactive({
    codsvs: '',
    comment: '',
  });
  const serviceCodes = ref([]);

  const { loading, saving, getServiceCodes, addServiceCode, removeServiceCode } = useClaimsApi();

  const columns = [
    {
      title: '#',
      dataIndex: 'nrolin',
      key: 'nrolin',
      width: 60,
      align: 'center',
    },
    {
      title: 'Código',
      dataIndex: 'codsvs',
      key: 'codsvs',
      width: 150,
    },
    {
      title: 'Descripción',
      dataIndex: 'texto',
      key: 'texto',
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: 120,
      align: 'center',
    },
  ];

  // Watch for prop changes
  watch(
    () => props.open,
    (newVal) => {
      visible.value = newVal;
      if (newVal) {
        loadServiceCodes();
      }
    }
  );

  watch(visible, (newVal) => {
    emit('update:open', newVal);
  });

  const loadServiceCodes = async () => {
    const data = await getServiceCodes(props.nrocom);
    serviceCodes.value = data.map((item) => {
      return {
        clave: item.clave,
        nrolin: item.nrolin,
        codsvs: item.codsvs || '',
        texto: item.texto || '',
        fecha: item.fecha,
        hora: item.hora,
      };
    });
  };

  const handleAddServiceCode = async () => {
    if (!newServiceCode.codsvs.trim() || !newServiceCode.comment.trim()) return;

    const nextLine =
      serviceCodes.value.length > 0 ? Math.max(...serviceCodes.value.map((s) => s.NROLIN)) + 1 : 1;

    const success = await addServiceCode({
      codref: props.codref,
      codsvs: newServiceCode.codsvs,
      comment: newServiceCode.comment,
      nrolin: nextLine,
    });

    if (success) {
      newServiceCode.codsvs = '';
      newServiceCode.comment = '';
      await loadServiceCodes();
      emit('refresh');
    }
  };

  const handleRemoveServiceCode = async (record) => {
    console.log(record);
    const nrocom = record.clave;
    const nrolin = record.nrolin;
    const success = await removeServiceCode(nrocom, nrolin);
    if (success) {
      await loadServiceCodes();
      emit('refresh');
    }
  };

  const handleClose = () => {
    visible.value = false;
    newServiceCode.codsvs = '';
    newServiceCode.comment = '';
  };
</script>

<style scoped lang="scss">
  .service-codes-modal-content {
    .add-service-section {
      margin-bottom: 24px;

      .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 16px;
      }
    }

    .service-codes-list-section {
      .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #262626;
        margin-bottom: 16px;
      }

      .empty-state {
        text-align: center;
        padding: 48px 0;
        color: #8c8c8c;

        p {
          margin-top: 16px;
          font-size: 14px;
        }
      }

      .service-codes-table {
        .service-code-badge {
          display: inline-block;
          padding: 4px 12px;
          background: #e6f7ff;
          border: 1px solid #91d5ff;
          border-radius: 4px;
          color: #0050b3;
          font-weight: 600;
          font-size: 13px;
          font-family: 'Courier New', monospace;
        }
      }
    }
  }
</style>
