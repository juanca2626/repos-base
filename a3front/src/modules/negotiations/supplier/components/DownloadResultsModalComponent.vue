<!-- DownloadResultsModalComponent.vue -->
<template>
  <a-modal
    class="module-negotiations custom-modal"
    :open="visible"
    :title="title"
    @ok="handleDownload"
    @cancel="handleCancel"
  >
    <a-form layout="vertical">
      <p>Prepara tu archivo de descarga:</p>
      <a-form-item label="Nombre del archivo">
        <a-input v-model:value="fileName" placeholder="Nombre del archivo" :allow-clear="true" />
      </a-form-item>

      <a-form-item label="Formato de archivo">
        <a-radio-group v-model:value="fileFormat">
          <template v-for="row in availableFileFormats">
            <a-radio :value="row.value">{{ row.name }}</a-radio>
          </template>
        </a-radio-group>
      </a-form-item>
    </a-form>

    <template #footer>
      <a-button key="back" @click="handleCancel" size="large">Cancelar</a-button>
      <a-button key="submit" type="primary" :loading="loading" @click="handleDownload" size="large">
        Guardar
      </a-button>
    </template>
  </a-modal>
</template>

<script lang="ts">
  import { defineComponent, ref, watch } from 'vue';
  import { message } from 'ant-design-vue';
  import type { PropType } from 'vue';

  export default defineComponent({
    name: 'DownloadResultsModalComponent',
    props: {
      visible: {
        type: Boolean,
        required: true,
      },
      title: {
        type: String,
        default: 'Descargar resultados',
      },
      initialFileName: {
        type: String,
      },
      onDownload: {
        type: Function as PropType<
          (fileName: string, fileFormat: string, fileExtension: string) => void
        >,
        required: true,
      },
    },
    setup(props, { emit }) {
      const fileName = ref(props.initialFileName ?? '');
      const fileFormat = ref('excel');
      const loading = ref(false);
      const availableFileFormats = [
        { value: 'excel', name: 'MS EXCEL', extension: 'xlsx' },
        // { value: 'pdf', name: 'PDF', extension: 'pdf'},
      ];

      const handleDownload = async () => {
        if (!fileName.value.trim()) {
          message.error('El nombre del archivo no puede estar vacío.');
          return;
        }

        loading.value = true;

        try {
          const findFormat = availableFileFormats.find((row) => row.value === fileFormat.value);

          if (!findFormat) {
            message.error('No se encontró un formato disponible.');
            return;
          }

          await props.onDownload(fileName.value, fileFormat.value, findFormat.extension);

          message.success('Archivo descargado exitosamente.');
          emit('update:visible', false);
        } catch (error) {
          console.log(error);
          message.error('Ocurrió un error al descargar el archivo.');
        } finally {
          loading.value = false;
        }
      };

      const handleCancel = () => {
        emit('update:visible', false);
      };

      watch(
        () => props.visible,
        (newVal) => {
          if (newVal) {
            fileName.value = props.initialFileName ?? '';
            fileFormat.value = 'excel';
          }
        }
      );

      return {
        fileName,
        fileFormat,
        loading,
        handleDownload,
        handleCancel,
        availableFileFormats,
      };
    },
  });
</script>
