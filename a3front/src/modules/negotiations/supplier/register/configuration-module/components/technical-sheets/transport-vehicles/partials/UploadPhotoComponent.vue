<template>
  <template v-if="!fileUploadData.uploading">
    <div>
      <a-upload
        class="custom-upload"
        v-model:file-list="localFileList"
        :customRequest="handleUpload"
        @preview="handlePreview"
        @remove="handleRemove"
        list-type="picture-card"
        :show-upload-list="{ showRemoveIcon }"
        :disabled="disabledUpload"
      >
        <div v-if="fileList.length === 0">
          <CustomImagePlusIcon :width="30" :height="30" stroke="#1284ED" />
          <div class="upload-text custom-primary-font">Subir imagen</div>
        </div>
      </a-upload>

      <a-modal
        :open="formPreview.visible"
        :title="formPreview.title"
        :footer="null"
        @cancel="handleCancelPreview"
      >
        <img class="w-100" :src="formPreview.image" />
      </a-modal>
    </div>
  </template>
  <template v-else>
    <div class="container-uploading">
      <div>
        <span class="custom-primary-font title-uploading"> Subiendo imagen </span>
      </div>
      <div>
        <span class="custom-primary-font progress-uploading">
          {{ formatFileSize(fileUploadData.uploadedSize ?? 0) }} of
          {{ formatFileSize(fileUploadData.fileSize) }}
        </span>
        <span class="custom-primary-font d-block progress-uploading">
          ({{ fileUploadData.progress }}% Done)
        </span>
      </div>
      <div class="container-progress">
        <a-progress
          :percent="fileUploadData.progress"
          :show-info="false"
          :stroke-width="2"
          stroke-color="#1284ED"
        />
      </div>
    </div>
  </template>
</template>
<script setup lang="ts">
  import type {
    UploadPhotoProps,
    UploadPhotoEmits,
  } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { formatFileSize } from '@/modules/negotiations/supplier/register/helpers/fileHelper';
  import { useUploadPhoto } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useUploadPhoto';
  import CustomImagePlusIcon from '@/modules/negotiations/supplier/components/icons/CustomImagePlusIcon.vue';

  const props = withDefaults(defineProps<UploadPhotoProps>(), {
    showRemoveIcon: true,
    disabledUpload: false,
  });

  const emit = defineEmits<UploadPhotoEmits>();

  const {
    formPreview,
    localFileList,
    handleCancelPreview,
    handlePreview,
    handleUpload,
    handleRemove,
  } = useUploadPhoto(props, emit);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .upload-text {
    font-size: 13px;
    font-weight: 500;
    color: $color-blue;
  }

  .custom-upload {
    :deep(.ant-upload-select-picture-card:hover) {
      border-color: $color-primary-strong !important;
    }

    :deep(.ant-upload-list.ant-upload-list-picture-card) {
      width: 160px !important;
      height: 160px !important;
    }

    :deep(.ant-upload-select-picture-card) {
      border: 1px dashed $color-black-5 !important;
      border-radius: 2px !important;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: $color-white-2 !important;
    }

    :deep(.ant-upload) {
      width: 160px !important;
      height: 160px !important;
    }

    :deep(.ant-upload-list-item) {
      border-radius: 4px !important;
      border: 1px solid $color-black-8 !important;
      width: 160px !important;
      height: 160px !important;
    }

    :deep(.ant-upload-list-item::before) {
      border-radius: 4px !important;
    }
  }

  .container-uploading {
    width: 160px;
    height: 160px;
    background-color: $color-white-2;
    border-radius: 4px;
    border: 1px dashed $color-black-5;
    padding: 8px;
    gap: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;

    .title-uploading {
      font-size: 13px;
      font-weight: 500;
    }

    .container-progress {
      width: 100%;
    }

    .progress-uploading {
      font-size: 12px;
      font-weight: 500;
      color: $color-black-5;
    }
  }
</style>
@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useUploadPhoto
