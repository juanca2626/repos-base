<template>
  <a-modal :visible="visible" :footer="null" width="1000px" :centered="true" @cancel="handleCancel">
    <div class="modal-reserve-body">
      <!-- Header Info -->
      <a-row type="flex" justify="space-between" align="top" style="margin-bottom: 20px">
        <a-col :span="8" style="text-align: center">
          <div class="modal-reserve-title">{{ entrance.departureDate }}</div>
          <div class="modal-reserve-subtitle">{{ entrance.templateName }}<br />FECHA SALIDA</div>
        </a-col>
        <a-col :span="8" style="text-align: center">
          <div class="modal-reserve-title">{{ entrance.startDate }}</div>
          <div class="modal-reserve-subtitle">{{ entrance.name }}<br />FECHA ENTRADA</div>
        </a-col>
        <a-col :span="8" style="text-align: center">
          <div class="modal-reserve-title">{{ entrance.totalPax }}</div>
          <div class="modal-reserve-subtitle">
            MONTO POR {{ entrance.paxs }} PAX(S): {{ entrance.costPax }}
          </div>
        </a-col>
      </a-row>

      <!-- Form Info -->
      <a-form :model="form" layout="vertical" style="margin-top: 20px">
        <a-row :gutter="16" align="bottom">
          <a-col :span="5">
            <a-form-item label="CÓDIGO:" class="mb-0">
              <a-input v-model:value="form.code" size="large" />
            </a-form-item>
          </a-col>
          <a-col :span="5">
            <a-form-item label="PROVEEDOR:" class="mb-0">
              <a-input v-model:value="form.provider" size="large" />
            </a-form-item>
          </a-col>
          <a-col :span="14">
            <a-form-item label="DESCRIPCIÓN:" class="mb-0">
              <a-input v-model:value="form.description" size="large" />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <br />

      <table class="table table-bordered full-width-table">
        <thead class="table-header-red">
          <tr>
            <th>FILE COMERCIAL</th>
            <th>ID</th>
            <th>NOMBRE</th>
            <th>TD</th>
            <th>DOCUMENTO</th>
            <th>FEC. NAC.</th>
            <th>CODPAI</th>
            <th>SEXO</th>
            <th>CAT</th>
            <th>OBSERVACIÓN</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="entrance.commercialFiles && entrance.commercialFiles.length">
            <template v-for="file in entrance.commercialFiles" :key="file.file.number">
              <tr v-for="(pax, index) in file.paxs" :key="pax.documentNumber">
                <td
                  v-if="index === 0"
                  :rowspan="file.paxs.length"
                  style="vertical-align: middle; text-align: center; font-weight: bold"
                >
                  {{ file.file.number }}
                </td>
                <td>{{ index + 1 }}</td>
                <td>{{ pax.name }}</td>
                <td>{{ pax.documentType || 1 }}</td>
                <td>{{ pax.documentNumber }}</td>
                <td>{{ pax.birthDate }}</td>
                <td>{{ pax.countryCode || 154 }}</td>
                <td>{{ pax.gender }}</td>
                <td>{{ pax.category || '-' }}</td>
                <td>{{ pax.observation || '' }}</td>
              </tr>
            </template>
          </template>
          <tr v-else>
            <td colspan="10" style="text-align: center; padding: 20px">
              No hay pasajeros asociados
            </td>
          </tr>
        </tbody>
      </table>

      <div class="my-3">
        <span class="d-block text-500">Archivos Anexos</span>
        <FileUploadComponent
          :title="'Seleccionar archivos (anexos)'"
          :folder="'entrances'"
          @onResponseFiles="handleResponseFiles"
        />
      </div>

      <button class="reserve-btn" :disabled="loading" @click="processReserve">
        {{ loading ? 'PROCESANDO...' : 'RESERVAR' }}
      </button>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, watch, toRefs } from 'vue';
  import { useEntrances } from '@/composables/adventure';
  import FileUploadComponent from '@/components/global/FileUploadComponent.vue';
  import { notification } from 'ant-design-vue';

  const props = defineProps({
    visible: {
      type: Boolean,
      default: false,
    },
    entrance: {
      type: Object,
      required: true,
      default: () => ({}),
    },
  });

  const emit = defineEmits(['update:visible', 'success']);

  const { visible, entrance } = toRefs(props);
  const { reserveEntrance, error } = useEntrances();
  const loading = ref(false);

  const form = ref({
    code: '',
    provider: '',
    description: '',
    files: [],
  });

  watch(
    () => props.visible,
    (newVal) => {
      if (newVal) {
        initForm();
      }
    }
  );

  const initForm = () => {
    console.log(entrance.value.providers);
    form.value = {
      code: '',
      provider: entrance.value.providers?.[0].code ?? '',
      description: '',
      files: [],
    };
  };

  const handleCancel = () => {
    emit('update:visible', false);
  };

  const handleResponseFiles = (files: any) => {
    form.value.files = files;
  };

  const validateForm = () => {
    const { code, provider, description, files } = form.value;

    if (!code) return 'El código es obligatorio.';
    if (!provider) return 'Debe seleccionar un proveedor.';
    if (!description) return 'La descripción no puede estar vacía.';
    if (!files?.length) return 'Debe adjuntar al menos un archivo.';

    return null; // Todo está bien
  };

  const processReserve = async () => {
    const errorMessage = validateForm();
    if (errorMessage) {
      notification.error({
        message: 'Error',
        description: errorMessage,
      });
      return;
    }

    try {
      loading.value = true;
      const params = {
        reservationCode: form.value.code,
        provider: form.value.provider,
        reservationDescription: form.value.description,
        attachments: form.value.files.map((file: any) => file.link),
      };

      await reserveEntrance(entrance.value._id, params);

      if (!error.value) {
        emit('success');
        emit('update:visible', false);
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading.value = false;
    }
  };

  initForm();
</script>

<style scoped>
  @import '../../../scss/views/adventure.scss';

  .modal-reserve-title {
    color: #ce1126;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
  }

  .modal-reserve-subtitle {
    text-align: center;
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
  }

  .reserve-btn {
    width: 100%;
    background-color: #ce1126;
    color: #fff;
    height: 50px;
    font-size: 18px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .reserve-btn:hover {
    background-color: #a30d1e;
  }
  .reserve-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
  }

  .full-width-table {
    width: 100%;
    border-collapse: collapse;
  }

  .full-width-table th,
  .full-width-table td {
    padding: 8px;
    border: 1px solid #e8e8e8;
    text-align: center;
    font-size: 12px;
  }

  .table-header-red {
    background-color: #ce1126;
    color: white;
  }

  .table-header-red th {
    background-color: #ce1126 !important;
    color: white !important;
  }
</style>
