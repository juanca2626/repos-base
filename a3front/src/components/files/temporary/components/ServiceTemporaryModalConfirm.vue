<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!fileServiceStore.isLoading"
      :maskClosable="!fileServiceStore.isLoading"
      :focusTriggerAfterClose="false"
      @open="handleModalOpen"
    >
      <template #title>
        <div class="title-confirm">
          <div class="title-confirm__number">2</div>
          Comunicaciones
        </div>
        <hr />
      </template>
      <a-row :gutter="16">
        <a-col :span="24">
          <p class="text-center title-confirm__subtitle">
            Está a punto de crear un servicio temporal
          </p>
        </a-col>
        <a-col :span="24">
          <a-alert class="text-info" type="info" show-icon closable>
            <template #icon>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-info"
              >
                <circle cx="12" cy="12" r="10" />
                <path d="M12 16v-4M12 8h.01" />
              </svg>
            </template>
            <template #message>
              <div class="text-info">Paso 2 omitido</div>
            </template>
            <template #description>
              <div class="text-info">
                Los servicios maestros que componen la equivalencia no poseen comunicaciones
              </div>
            </template>
          </a-alert>
        </a-col>
        <a-col :span="24">
          <p class="text-center text-confirm">¿Desea continuar?</p>
        </a-col>
      </a-row>
      <a-row :gutter="16">
        <a-col :span="24" class="text-center mt-2">
          <a-button @click="handleCancel" class="btn-cancel" :disabled="fileServiceStore.isLoading">
            Cancelar
          </a-button>
          <a-button
            @click="handleContinue"
            class="btn-save"
            html-type="submit"
            :loading="fileServiceStore.isLoading"
          >
            Continuar
          </a-button>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script setup lang="ts">
  import { notification } from 'ant-design-vue';
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import { useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';

  const fileServiceStore = useFileServiceStore();
  const filesStore = useFilesStore();
  const router = useRouter();

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);

  const handleCancel = () => {
    if (!fileServiceStore.isLoading) {
      emit('update:isOpen', false);
    }
  };

  const onSaveServiceTemporary = async () => {
    try {
      const itinerary = filesStore.serviceEdit.itinerary;
      if (!itinerary || !itinerary.id || !itinerary.services || itinerary.services.length === 0) {
        throw new Error('Itinerario o servicios no están correctamente definidos');
      }

      await fileServiceStore.sendFileService(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        itinerary
      );
      if (fileServiceStore.isSuccess) {
        const currentDateTime = fileServiceStore.getCurrentDateTime();
        filesStore.serviceEdit.itinerary.created_date_at = currentDateTime.date;
        filesStore.serviceEdit.itinerary.created_time_at = currentDateTime.time;
        notification.success({
          message: 'Éxito',
          description: 'El servicio temporal se ha creado correctamente.',
        });
        emit('serviceSaved');
        emit('update:isOpen', false);
      }
    } catch (error) {
      notification.error({
        message: 'Error',
        description: error.message || 'Ocurrió un error al guardar el servicio temporal',
      });
      console.error('Error:', error);
    } finally {
    }
  };

  const handleContinue = () => {
    onSaveServiceTemporary();
  };
</script>
<style scoped lang="scss">
  .file-modal {
    .title-confirm {
      padding-top: 35px;
    }

    font-family: Montserrat, serif !important;

    .btn-save {
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      margin-left: 10px;
      width: auto !important;

      &:hover {
        background-color: #c63838 !important;
        border: 1px solid #c63838 !important;
      }
    }

    .btn-cancel {
      margin-right: 10px;
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      width: auto !important;

      &:hover {
        color: #575757 !important;
        background-color: #e9e9e9 !important;
        border: 1px solid #e9e9e9 !important;
      }
    }
  }
</style>
