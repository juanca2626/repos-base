<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :focusTriggerAfterClose="false"
      width="688px"
    >
      <div class="modal-container">
        <div class="modal-header">
          <div class="title-section">
            <span class="modal-title">Descargar itinerario</span>
          </div>
        </div>
        <a-form :model="formState" ref="formRefFile" layout="vertical" :rules="rules">
          <a-spin :spinning="itineraryDownloadStore.isLoading">
            <a-row :gutter="16">
              <a-col :span="24" class="mb-4">
                <a-radio-group
                  v-model:value="formState.typeDoc"
                  class="text-label"
                  name="radioGroup"
                >
                  <a-radio value="pdf">Itinerario</a-radio>
                  <a-radio value="word">Programa día a día</a-radio>
                </a-radio-group>
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
                  :options="languagesStore.getLanguages"
                  class="text-label"
                  v-model:value="formState.language"
                />
                <a-alert class="text-info mt-3" type="info" show-icon closable>
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
                  <template #message> El formato de descarga es {{ formState.typeDoc }}</template>
                </a-alert>
              </a-col>
              <a-col :span="12">
                <a-row :gutter="16">
                  <a-col :span="24">
                    <a-checkbox class="text-label" v-model:checked="formState.includeCoverage">
                      Incluir portada
                    </a-checkbox>
                  </a-col>
                  <a-col :span="24" class="mt-2" v-if="formState.includeCoverage">
                    <base-select
                      style="width: 100%"
                      name="lang"
                      label="Imagen de portada"
                      placeholder="Selecciona una imagen"
                      size="small"
                      :allowClear="true"
                      :comma="false"
                      :options="covers"
                      class="text-label"
                      @change="handleChangeCover"
                      v-model:value="formState.coverName"
                    />
                  </a-col>
                  <a-col :span="24" class="mt-2" v-if="formState.includeCoverage">
                    <img :src="imageCoverUrl" alt="" class="w-50" />
                  </a-col>
                  <a-col :span="24" class="mt-2 mb-2 text-label" v-if="formState.includeCoverage">
                    <span>¿Desea con logo de cliente?:</span>
                  </a-col>
                  <a-col :span="24" v-if="formState.includeCoverage">
                    <a-radio-group
                      class="text-label"
                      v-model:value="formState.includeLogoClient"
                      name="radioGroup"
                    >
                      <a-radio value="1">Si</a-radio>
                      <a-radio value="3">No</a-radio>
                      <a-radio value="2">Ninguno</a-radio>
                    </a-radio-group>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </a-spin>
          <a-row :gutter="16">
            <a-col :span="24" class="text-center mt-5">
              <a-button
                @click="handleCancel"
                class="btn-cancel"
                :loading="itineraryDownloadStore.isLoading"
              >
                Cancelar
              </a-button>
              <a-button
                @click="handleDownload"
                class="btn-save"
                html-type="submit"
                :loading="itineraryDownloadStore.isLoading"
              >
                Descargar
              </a-button>
            </a-col>
          </a-row>
        </a-form>
      </div>
    </a-modal>
  </div>
</template>
<script setup lang="ts">
  import { ref, watch } from 'vue';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import { useDownloadStore, useFilesStore } from '@/stores/files';
  import { useLanguagesStore } from '@/stores/global/index.js';
  import { useItineraryDownloadStore } from '@/components/files/itinerary/store/itineraryDownloadStore';
  import { notification } from 'ant-design-vue';

  const emit = defineEmits(['update:isOpen', 'submit']);
  const languagesStore = useLanguagesStore();
  const itineraryDownloadStore = useItineraryDownloadStore();
  const fileStore = useFilesStore();
  const rules = {};
  const downloadStore = useDownloadStore();

  // Propiedades
  const props = defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const covers = [
    {
      label: 'Amazonas',
      value: 'amazonas',
    },
    {
      label: 'Arequipa',
      value: 'arequipa',
    },
    {
      label: 'Arequipa Catedral',
      value: 'arequipa-catedral',
    },
    {
      label: 'Argentina',
      value: 'argentina',
    },
    {
      label: 'Aventura',
      value: 'aventura',
    },
    {
      label: 'Ballestas',
      value: 'ballestas',
    },
    {
      label: 'Bolivia',
      value: 'bolivia',
    },
    {
      label: 'Brasil',
      value: 'brasil',
    },
    {
      label: 'Camino Inca',
      value: 'camino-inca',
    },
    {
      label: 'Chile',
      value: 'chile',
    },
    {
      label: 'Colca',
      value: 'colca',
    },
    {
      label: 'Comunidad Local',
      value: 'comunidad-local',
    },
    {
      label: 'Cusco',
      value: 'cusco',
    },
    {
      label: 'Cusco Iglesia',
      value: 'cusco-iglesia',
    },
    {
      label: 'Familia 1',
      value: 'familia1',
    },
    {
      label: 'Familia 2',
      value: 'familia2',
    },
    {
      label: 'Familia 3',
      value: 'familia3',
    },
    {
      label: 'Familia 4',
      value: 'familia4',
    },
    {
      label: 'Huaraz',
      value: 'huaraz',
    },
    {
      label: 'Kuelap',
      value: 'kuelap',
    },
    {
      label: 'Lima 1',
      value: 'lima1',
    },
    {
      label: 'Lima 2',
      value: 'lima2',
    },
    {
      label: 'Lima 3',
      value: 'lima3',
    },
    {
      label: 'Lujo',
      value: 'lujo',
    },
    {
      label: 'Machupicchu',
      value: 'machupicchu',
    },
    {
      label: 'Mapi',
      value: 'mapi',
    },
    {
      label: 'Maras',
      value: 'maras',
    },
    {
      label: 'Moray',
      value: 'moray',
    },
    {
      label: 'Mpi2',
      value: 'mpi2',
    },
    {
      label: 'Nasca',
      value: 'nasca',
    },
    {
      label: 'Playas del Norte',
      value: 'playas-del-norte',
    },
    {
      label: 'Portada',
      value: 'portada',
    },
    {
      label: 'Puerto Maldonado',
      value: 'puerto-maldonado',
    },
    {
      label: 'Puno',
      value: 'puno',
    },
    {
      label: 'Trujillo',
      value: 'trujillo',
    },
    {
      label: 'Valle',
      value: 'valle',
    },
    {
      label: 'Vinicunca',
      value: 'vinicunca',
    },
  ];

  const imageCoverUrl = ref('');
  const imageCover = ref('');

  const formState = ref({
    typeDoc: 'pdf',
    language: 'es',
    includeCoverage: true,
    includeLogoClient: '3',
    coverName: 'amazonas',
  });

  const handleChangeCover = async (cover: string) => {
    try {
      const file = fileStore.getFile;
      const resultImg = await itineraryDownloadStore.setComboPortada({
        cover,
        language: formState.value.language,
        logoWidth: formState.value.includeLogoClient,
        clientId: file.clientId,
        clientName: file.clientName,
        nameDocument: file.description,
      });

      if (resultImg?.data?.image) {
        imageCoverUrl.value = `${window.url_back_quote_a3}${resultImg.data.image}.jpg`;
        imageCover.value = resultImg.data.portada;
      } else {
        console.warn('No se pudo obtener la imagen de portada.');
      }
    } catch (error) {
      console.error('Error al cambiar la portada:', error);
    }
  };
  const handleCancel = () => {
    emit('update:isOpen', false);
  };

  const handleDownload = async () => {
    try {
      itineraryDownloadStore.setLoading(true);
      await downloadStore.downloadFileItinerary(
        fileStore.getFile.id,
        imageCover.value,
        formState.value.language,
        formState.value.typeDoc
      );
      notification.success({
        message: 'Éxito',
        description: 'Archivo se genero correctamente.',
      });
      itineraryDownloadStore.setLoading(false);
    } catch (error) {
      notification.error({
        message: 'Error',
        description: `Error al generar el archivo Excel: ${error.message}`,
      });
    } finally {
      itineraryDownloadStore.setLoading(false);
    }
  };

  watch(
    () => formState.value.includeCoverage,
    (newValue, oldValue) => {
      console.log('Cambio en includeCoverage:', { nuevo: newValue, anterior: oldValue });
      if (newValue) {
        handleChangeCover(formState.value.coverName);
      } else {
        imageCover.value = '';
        imageCoverUrl.value = '';
      }
    }
  );

  watch(
    () => formState.value.includeLogoClient,
    (newValue, oldValue) => {
      console.log('Cambio en includeLogoClient:', { nuevo: newValue, anterior: oldValue });
      // Llamar a handleChangeCover con la portada actual
      handleChangeCover(formState.value.coverName);
    }
  );

  watch(
    () => props.isOpen,
    (newValue) => {
      if (newValue) {
        handleChangeCover(formState.value.coverName);
      }
    }
  );

  handleChangeCover(formState.value.coverName);
</script>
<style scoped lang="scss">
  .file-modal {
    .text-label {
      ::v-deep(label) {
        font-family: Montserrat, sans-serif;
      }

      ::v-deep(span) {
        font-family: Montserrat, sans-serif;
      }
    }

    .font-w-700 {
      font-weight: 700;
    }

    .modal-container {
      padding: 10px;
    }

    .modal-header {
      font-family: Montserrat, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 20px;
      background-color: #ffffff;
      color: #3d3d3d;
      flex-direction: row;
      padding: 10px !important;

      .title-section {
        display: flex;
        align-items: center;

        .modal-title {
          font-size: 24px;
          font-weight: 700;
        }
      }
    }

    .btn-save {
      font-family: Montserrat, sans-serif;
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      margin-left: 10px;
      width: auto !important;
      background-color: #eb5757 !important;

      &:hover {
        background-color: #c63838 !important;
        border: 1px solid #c63838 !important;
      }
    }

    .btn-cancel {
      font-family: Montserrat, sans-serif;
      margin-right: 10px;
      height: 54px !important;
      font-size: 16px !important;
      font-weight: 500 !important;
      width: auto !important;
      color: #eb5757 !important;
      background-color: #ffffff !important;
      border: 1px solid #eb5757 !important;

      &:hover {
        font-weight: 600;
        color: #c63838 !important;
        background-color: #fff6f6 !important;
        border: 1px solid #c63838 !important;
      }
    }

    .text-info {
      font-family: Montserrat, sans-serif;
    }
  }
</style>
