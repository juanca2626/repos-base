<template>
  <div>
    <template v-if="!fileUploadData.uploading && !fileUploadData.isFileUploaded">
      <a-form-item name="file">
        <a-upload-dragger
          class="custom-upload"
          :multiple="false"
          :before-upload="validateUploadFile"
          :customRequest="(fileData: any) => customRequest(fileData, fileUploadData)"
          :showUploadList="false"
        >
          <p>
            <CustomUploadIcon :width="50" :height="50" stroke="#1284ED" />
          </p>
          <span class="upload-dragger-text d-block">
            Haga clic o arrastre el archivo a esta área para adjuntar
          </span>
          <span class="upload-dragger-hint d-block mt-1">
            Soporte para todo tipo de documentos.
          </span>
        </a-upload-dragger>
      </a-form-item>
    </template>
    <template v-else>
      <UploadFileStatusComponent
        :fileUploadData="fileUploadData"
        @handleRemove="emit('onRemoveFile')"
        @handleDownload="emit('onDownloadFile')"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
  import type { PropType } from 'vue';
  import CustomUploadIcon from '@/modules/negotiations/supplier/components/icons/CustomUploadIcon.vue';
  import UploadFileStatusComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/UploadFileStatusComponent.vue';
  import type { FileUploadData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  defineProps({
    fileUploadData: {
      type: Object as PropType<FileUploadData>,
      required: true,
    },
    validateUploadFile: { type: Function, required: true },
    customRequest: { type: Function, required: true },
  });

  const emit = defineEmits(['onRemoveFile', 'onDownloadFile']);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  .custom-upload {
    :deep(.ant-upload) {
      background-color: $color-white-2;
      border-radius: 2px;
      padding: 16px 10px;
    }

    :deep(.ant-upload-drag) {
      border: 1px dashed $color-black-5;
    }
  }

  .upload-dragger-text {
    font-size: 16px;
    font-weight: 500;
    line-height: 24px;
    color: $color-black;
  }

  .upload-dragger-hint {
    font-size: 14px;
    font-weight: 400;
    line-height: 22px;
    color: $color-black-3;
  }
</style>
