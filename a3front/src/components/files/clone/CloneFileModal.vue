<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!isSaving"
      :maskClosable="!isSaving"
      :focusTriggerAfterClose="false"
      @open="handleModalOpen"
    >
      <template #title>
        <p>Clonar file</p>
        <hr />
      </template>
      <a-form
        :model="formState"
        ref="formRefFile"
        layout="vertical"
        :rules="rules"
        @finish="onCheckQuote"
      >
        <a-spin :spinning="loading || isSaving">
          <a-row :gutter="16">
            <a-col :span="24" class="mb-4" v-if="showFileSelect">
              <base-select
                style="width: 100%"
                name="fileId"
                label="Selecciona file a clonar"
                placeholder="Selecciona..."
                size="large"
                :showSearch="true"
                :filter-option="false"
                :allowClear="true"
                :options="filesStore.getFilesByClone"
                v-model:value="formState.fileId"
                @search="handleSearchFiles"
                :loading="filesStore.isLoading"
              />
            </a-col>
          </a-row>
          <a-row :gutter="16">
            <a-col :span="24">
              <base-input
                v-model="formState.description"
                placeholder="Escribe aquí..."
                label="Nombre para el file"
                name="description"
                size="small"
              />
            </a-col>
          </a-row>
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
        </a-spin>
        <a-row :gutter="16">
          <a-col :span="24" class="text-right mt-4">
            <a-button @click="handleCancel" class="btn-cancel" :disabled="loading">
              Cancelar
            </a-button>
            <a-button class="btn-save" html-type="submit" :loading="loading">
              Ir a cotizar
            </a-button>
          </a-col>
        </a-row>
      </a-form>
    </a-modal>
    <a-modal v-model:visible="showNotify" title="Clonar file" :width="400">
      <a-alert
        class="text-danger"
        type="error"
        show-icon
        description="Se encontró una cotización abierta en el tablero."
      >
        <template #icon>
          <exclamation-circle-outlined />
        </template>
      </a-alert>
      <template #footer>
        <div class="text-center">
          <a-button
            type="primary"
            ghost
            :loading="loading"
            @click="handleA2"
            v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            Ir al tablero
          </a-button>
          <a-button
            type="primary"
            default
            :loading="loading"
            @click="handleA2Force"
            v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            Reemplazar
          </a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, reactive, watch } from 'vue';
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
  import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
  import Cookies from 'js-cookie';

  const clientsStore = useClientsStore();
  const languagesStore = useLanguagesStore();
  const filesStore = useFilesStore();
  const loading = ref(false);
  const isSaving = ref(false);
  const router = useRouter();
  const dataLoaded = ref(false);
  const showNotify = ref(false);

  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
    showFileSelect: {
      type: Boolean,
      default: true,
    },
    setFileId: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);

  const handleModalOpen = async () => {
    if (!dataLoaded.value) {
      loading.value = true;
      try {
        const requests = [
          clientsStore.fetchAll(),
          filesStore.fetchFileCategories(),
          filesStore.fetchFileReasonStatement(),
        ];
        if (props.showFileSelect) {
          requests.push(filesStore.searchCompleted({ perPage: 10 }));
        }
        await Promise.all(requests);
        dataLoaded.value = true;
      } catch (error) {
        console.error('Error loading initial data:', error);
        notification.error({
          message: 'Error',
          description: 'No se pudieron cargar los datos iniciales. Por favor, inténtelo de nuevo.',
        });
      } finally {
        loading.value = false;
      }
    }
  };

  // Estado reactivo para el formulario
  const formState = reactive({
    fileId: null,
    description: '',
    dateInit: null,
    clientId: undefined,
    clientCode: undefined,
    clientName: undefined,
    passengers: { ADL: 1, CHD: 0 },
    accommodation: { SGL: 0, DBL: 0, TPL: 0 },
    lang: undefined,
    fileCategoryId: undefined,
    force: false,
  });

  const resetFormState = () => {
    Object.assign(formState, {
      fileId: null,
      description: '',
      dateInit: null,
      clientId: undefined,
      clientCode: undefined,
      clientName: undefined,
      passengers: { ADL: 1, CHD: 0 },
      accommodation: { SGL: 0, DBL: 0, TPL: 0 },
      lang: undefined,
      fileCategoryId: undefined,
      force: false,
    });
    showNotify.value = false;
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

  const handleSearchClients = debounce((value) => {
    if (value && value.length >= 2) {
      clientsStore.fetchAll(value);
    }
  }, 500);

  const handleSearchFiles = debounce((value) => {
    if (value && value.length >= 2) {
      // clientsStore.fetchAll(value);
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
      formState.clientId = value;
    } else {
      formState.clientId = undefined;
      formState.clientCode = undefined;
      formState.clientName = undefined;
    }
  };

  const onSaveFileClone = async () => {
    showNotify.value = false;
    loading.value = true;
    isSaving.value = true;
    try {
      const data = {
        description: formState.description,
        date_init: formState.dateInit ? dayjs(formState.dateInit).format('YYYY-MM-DD') : null,
        client_id: formState.clientId,
        client_code: formState.clientCode,
        client_name: formState.clientName,
        adults: formState.passengers.ADL,
        children: formState.passengers.CHD,
        accommodation_sgl: formState.accommodation.SGL,
        accommodation_dbl: formState.accommodation.DBL,
        accommodation_tpl: formState.accommodation.TPL,
        categories: formState.fileCategoryId,
        force: formState.force,
        lang: formState.lang ? formState.lang.toUpperCase() : 'EN',
      };
      if (!props.showFileSelect) {
        formState.fileId =
          props.setFileId === '' ? router.currentRoute.value.params.id : props.setFileId;
      }
      const response = await filesStore.cloneBasicFile(formState.fileId, data);
      if (response && response.success && response.data) {
        isSaving.value = false;
        notification.success({
          message: 'Éxito',
          description: 'File clonado correctamente',
        });
        handleCancel();
        window.open(window.url_front_a2 + 'packages/cotizacion', '_blank');
      } else {
        throw new Error('No se pudo obtener el ID del file clonado');
      }
      isSaving.value = false;
      loading.value = false;
    } catch (error) {
      loading.value = false;
      isSaving.value = false;
      // El error ya ha sido manejado en el store, no necesitamos hacer nada aquí
      console.error('Error al clonar el file:', error);
    }
  };

  const onCheckQuote = async () => {
    loading.value = true;
    isSaving.value = true;
    try {
      await filesStore.verifyQuote();
      if (filesStore.getFlagBoard) {
        showNotify.value = true;
      } else {
        showNotify.value = false;
        formState.force = true;
        await onSaveFileClone();
      }
      isSaving.value = false;
      loading.value = false;
    } catch (error) {
      loading.value = false;
      isSaving.value = false;
      // El error ya ha sido manejado en el store, no necesitamos hacer nada aquí
      console.error('Error al clonar el file:', error);
    }
  };

  const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
  };

  const handleA2 = () => {
    if (!props.showFileSelect) {
      formState.fileId =
        props.setFileId === '' ? router.currentRoute.value.params.id : props.setFileId;
    }

    Cookies.set('a3_client_code', formState.clientCode, { domain: window.DOMAIN });
    Cookies.set('a3_client_id', formState.clientName, { domain: window.DOMAIN });

    localStorage.setItem('a3_file_id', formState.fileId);
    window.location.href = window.url_app + 'quotes';
  };

  const handleA2Force = async () => {
    formState.force = true;
    await onSaveFileClone();
  };

  watch(
    () => props.isOpen,
    (newValue) => {
      if (newValue) {
        handleModalOpen();
      }
    }
  );
</script>

<style scoped lang="scss">
  .btn-save {
    margin-left: 10px;
    background-color: #eb5757;
    color: #fff;
    border-radius: 6px;
    padding: 12px 20px 12px 20px !important;
    height: 45px;
    width: auto !important;
  }

  .btn-cancel {
    background-color: #fafafa;
    color: #575757;
    border-radius: 6px;
    padding: 12px 20px 12px 20px !important;
    height: 45px;
  }
</style>
