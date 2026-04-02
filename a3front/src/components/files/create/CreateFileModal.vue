<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!isSaving"
      :maskClosable="false"
      :focusTriggerAfterClose="false"
    >
      <template #title>
        <p>Crear file desde 0</p>
        <hr />
      </template>
      <a-form
        :model="formState"
        ref="formRefFile"
        layout="vertical"
        :rules="rules"
        @finish="onSaveFileBasic"
      >
        <a-spin :spinning="loading || isSaving">
          <base-input
            v-model="formState.description"
            placeholder="Escribe aquí..."
            label="Nombre del file"
            name="description"
            size="small"
          />

          <a-row :gutter="16">
            <a-col :span="12">
              <base-date-picker
                label="Fecha de inicio"
                name="dateInit"
                v-model:value="formState.dateInit"
                :disabledDate="disabledDate"
              />
            </a-col>
            <a-col :span="12">
              <base-select
                style="width: 100%"
                name="clientId"
                label="Cliente"
                placeholder="Selecciona"
                size="large"
                :showSearch="true"
                :filter-option="false"
                :allowClear="true"
                :options="clientsStore.getClients"
                v-model:value="formState.clientId"
                @search="handleSearchClients"
                :loading="clientsStore.isLoading"
                @change="handleClientChange"
              />
            </a-col>
          </a-row>

          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item name="passengers" label="Pasajeros" required>
                <a-form-item-rest>
                  <BaseInputPassengers v-model="formState.passengers" />
                </a-form-item-rest>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item name="accommodation" label="Acomodo" required>
                <a-form-item-rest class="custom-form-item-ctm">
                  <BaseSelectAccommodations v-model="formState.accommodation" />
                </a-form-item-rest>
              </a-form-item>
            </a-col>
          </a-row>

          <a-row :gutter="16">
            <a-col :span="12">
              <base-select
                style="width: 100%"
                name="lang"
                label="Idioma"
                placeholder="Selecciona"
                size="large"
                :allowClear="true"
                :comma="false"
                :options="languagesStore.getAllLanguages"
                v-model:value="formState.lang"
              />
            </a-col>
            <a-col :span="12">
              <base-select-multiple
                style="width: 100%"
                name="category"
                label="Categoría"
                placeholder="Selecciona"
                :allowClear="true"
                :options="filesStore.getFileCategories"
                :multiple="true"
                :comma="false"
                v-model:value="formState.fileCategoryId"
              />
            </a-col>
          </a-row>

          <a-row :gutter="16" class="mt-4">
            <a-col :span="12">
              <a-form-item name="generateStatement">
                <a-checkbox v-model:checked="formState.generateStatement">
                  Generar statement
                </a-checkbox>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <base-select
                style="width: 100%"
                name="reasonStatementId"
                label="Motivo"
                :options="filesStore.getFileReasonStatement"
                placeholder="Selecciona"
                size="large"
                :allowClear="true"
                :comma="false"
                :disabled="formState.generateStatement"
                v-model:value="formState.reasonStatementId"
              />
            </a-col>
          </a-row>
        </a-spin>
        <a-row :gutter="16">
          <a-col :span="24" class="text-right mt-4">
            <a-button @click="handleCancel" class="btn-cancel text-500" :disabled="loading">
              Cancelar
            </a-button>
            <a-button class="btn-save text-500" html-type="submit" :loading="loading">
              Guardar</a-button
            >
          </a-col>
        </a-row>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, reactive, onBeforeMount, watch } from 'vue';
  import BaseInputPassengers from '@/components/files/reusables/BaseInputPassengers.vue';
  import BaseInput from '@/components/files/reusables/BaseInput.vue';
  import BaseDatePicker from '@/components/files/reusables/BaseDatePicker.vue';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import BaseSelectMultiple from '@/components/files/reusables/BaseSelectMultiple.vue';
  import BaseSelectAccommodations from '@/components/files/reusables/BaseSelectAccommodations.vue';
  import { useClientsStore } from '@store/files/clients-store';
  import { useFilesStore } from '@store/files';
  import { debounce } from 'lodash-es';
  import { useLanguagesStore } from '@/stores/global/index.js';
  import { notification } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { useRouter } from 'vue-router';

  const clientsStore = useClientsStore();
  const languagesStore = useLanguagesStore();
  const filesStore = useFilesStore();
  const loading = ref(false);
  const isSaving = ref(false);
  const router = useRouter();

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);

  // Estado reactivo para el formulario
  const formState = reactive({
    description: '',
    dateInit: null,
    clientId: undefined,
    clientCode: undefined,
    clientName: undefined,
    passengers: { ADL: 1, CHD: 0 },
    accommodation: { SGL: 0, DBL: 0, TPL: 0 },
    lang: undefined,
    fileCategoryId: undefined,
    generateStatement: true,
    reasonStatementId: null,
    haveCredit: 0,
    creditLine: null,
  });

  const resetFormState = () => {
    Object.assign(formState, {
      description: '',
      dateInit: null,
      clientId: undefined,
      clientCode: undefined,
      clientName: undefined,
      passengers: { ADL: 1, CHD: 0 },
      accommodation: { SGL: 0, DBL: 0, TPL: 0 },
      lang: undefined,
      fileCategoryId: undefined,
      generateStatement: true,
      reasonStatementId: null,
      haveCredit: 0,
      creditLine: null,
    });
  };

  // Referencia al formulario
  const formRefFile = ref(null);

  const validatePassengers = (rule, value) => {
    if (!value || (value.ADL === 0 && value.CHD === 0)) {
      return Promise.reject('Debe haber al menos un pasajero');
    }
    if (value.ADL < 1) {
      return Promise.reject('Debe haber al menos un pasajero adulto');
    }
    return Promise.resolve();
  };

  const validateAccommodation = (rule, value) => {
    const total = value.SGL + value.DBL + value.TPL;
    if (total === 0) {
      return Promise.reject('Debe seleccionar al menos un tipo de acomodo');
    }
    return Promise.resolve();
  };

  const validateReasonStatement = (rule, value) => {
    if (value == null && !formState.generateStatement) {
      return Promise.reject('Debe seleccionar un motivo cuando no se genera un statement');
    }
    return Promise.resolve();
  };

  // Reglas de validación
  const rules = {
    description: [
      { required: true, message: 'Debe ingrese el nombre del file' },
      { min: 3, max: 100, message: 'El nombre debe tener entre 3 y 100 caracteres' },
    ],
    dateInit: [{ required: true, message: 'Debe seleccionar la fecha de inicio' }],
    clientId: [{ required: true, message: 'Debe seleccionar un cliente' }],
    passengers: [
      {
        validator: validatePassengers,
        trigger: 'change',
      },
    ],
    accommodation: [
      {
        validator: validateAccommodation,
        trigger: 'change',
      },
    ],
    reasonStatementId: [
      {
        required: !formState.generateStatement,
        validator: validateReasonStatement,
        trigger: ['change'],
      },
    ],
    lang: [{ required: true, message: 'Debe seleccione un idioma' }],
  };

  // Maneja la cancelación del modal
  const handleCancel = () => {
    if (!isSaving.value) {
      formRefFile.value?.resetFields();
      resetFormState();
      emit('update:isOpen', false);
      // Añade un pequeño retraso antes de enfocar un elemento seguro
      setTimeout(() => {
        document.body.focus();
      }, 0);
    }
  };

  // Observar cambios en generateStatement
  watch(
    () => formState.generateStatement,
    (newValue) => {
      if (newValue) {
        formState.reasonStatementId = null;
      }
      // Validar el campo reasonStatementId cuando cambie generateStatement
      if (formRefFile.value) {
        formRefFile.value.validateFields(['reasonStatementId']);
      }
    }
  );

  const handleSearchClients = debounce((value) => {
    if (value && value.length >= 2) {
      clientsStore.fetchAll(value);
    }
  }, 500);

  watch(
    () => clientsStore.isLoading,
    (newValue) => {
      if (!newValue && clientsStore.getClients.length === 0) {
        // Si la carga ha terminado y no hay resultados, puedes mostrar un mensaje o realizar alguna acción
        console.log('No se encontraron clientes');
      }
    }
  );

  const handleClientChange = (value) => {
    const selectedClient = clientsStore.getClients.find((client) => client.value === value);
    if (selectedClient) {
      const labelParts = selectedClient.label.split('-');
      if (labelParts.length > 1) {
        formState.clientCode = labelParts[0].trim();
        formState.clientName = labelParts.slice(1).join('-').trim();
      } else {
        formState.clientCode = '';
        formState.clientName = selectedClient.label;
      }
      formState.clientId = selectedClient.value;
      formState.haveCredit = selectedClient.haveCredit;
      formState.creditLine = selectedClient.creditLine;
    } else {
      formState.clientId = undefined;
      formState.clientCode = undefined;
      formState.clientName = undefined;
      formState.haveCredit = undefined;
      formState.creditLine = undefined;
    }
  };

  const onSaveFileBasic = async () => {
    try {
      await formRefFile.value?.validate();
      loading.value = true;
      isSaving.value = true;
      const data = {
        description: formState.description,
        date_init: formState.dateInit ? dayjs(formState.dateInit).format('YYYY-MM-DD') : null,
        client_id: formState.clientId.id,
        client_code: formState.clientCode,
        client_name: formState.clientName,
        have_credit: formState.haveCredit,
        credit_line: formState.creditLine ?? 0,
        adults: formState.passengers.ADL,
        children: formState.passengers.CHD,
        accommodation_sgl: formState.accommodation.SGL,
        accommodation_dbl: formState.accommodation.DBL,
        accommodation_tpl: formState.accommodation.TPL,
        categories: formState.fileCategoryId,
        generate_statement: formState.generateStatement,
        reason_statement_id: formState.reasonStatementId,
        lang: formState.lang ? formState.lang.toUpperCase() : 'EN',
      };
      const response = await filesStore.storeBasicFile(data);
      if (response && response.success && response.data) {
        isSaving.value = false;
        notification.success({
          message: 'Éxito',
          description: 'File creado correctamente',
        });
        handleCancel();

        if (response.data.id) {
          router.push({ name: 'files-edit', params: { id: response.data.id } });
        }
      }
      isSaving.value = false;
      loading.value = false;
    } catch (error) {
      loading.value = false;
      isSaving.value = false;
      // El error ya ha sido manejado en el store, no necesitamos hacer nada aquí
      console.error('Error al crear el file:', error);
    }
  };

  const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
  };

  onBeforeMount(async () => {
    await clientsStore.fetchAll();
    await filesStore.fetchFileCategories();
    await filesStore.fetchFileReasonStatement();
  });
</script>

<style scoped>
  .btn-save {
    margin-left: 10px;
    background-color: #eb5757;
    color: #fff;
    border-radius: 6px;
    padding: 12px 20px 12px 20px !important;
    height: 45px;
  }

  .btn-cancel {
    background-color: #fafafa;
    color: #575757;
    border-radius: 6px;
    padding: 12px 20px 12px 20px !important;
    height: 45px;
  }
</style>
