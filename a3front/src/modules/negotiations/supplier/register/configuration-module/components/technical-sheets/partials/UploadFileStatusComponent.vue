<template>
  <div :class="['main-container', { 'main-container-center': fileUploadData.isFileUploaded }]">
    <div>
      <font-awesome-icon :icon="['far', 'file-lines']" class="file-icon" />
    </div>
    <div class="status-container">
      <div
        :class="[
          'filename-container',
          { 'filename-container-center': fileUploadData.isFileUploaded },
        ]"
      >
        <template v-if="fileUploadData.uploading">
          <span class="filename">{{ fileUploadData.uploadedFile?.name }}</span>
          <span>
            <font-awesome-icon
              :icon="['far', 'circle-xmark']"
              class="close-icon"
              @click="handleRemove"
            />
          </span>
        </template>
        <template v-else>
          <div>
            <span class="filename filename-download d-block" @click="emit('handleDownload')">
              {{ fileUploadData.uploadedFile?.name ?? fileUploadData.filename }}
            </span>
            <span class="info-upload-progress-text">{{
              formatFileSize(fileUploadData.fileSize)
            }}</span>
          </div>
          <span>
            <template v-if="showRemoveButton">
              <font-awesome-icon
                :icon="['far', 'circle-xmark']"
                class="close-icon"
                @click="handleRemove"
              />
            </template>
          </span>
        </template>
      </div>
      <template v-if="fileUploadData.uploading">
        <div>
          <a-progress
            :percent="fileUploadData.progress"
            :show-info="false"
            :stroke-width="4"
            stroke-color="#1284ED"
          />
        </div>
        <div class="info-upload-progress">
          <span class="info-upload-progress-text">
            {{ formatFileSize(fileUploadData.uploadedSize ?? 0) }} of
            {{ formatFileSize(fileUploadData.fileSize) }} ({{ fileUploadData.progress }}% Done)
          </span>
          <span class="info-upload-progress-text"> {{ fileUploadData.uploadSpeed }} KB/sec </span>
        </div>
      </template>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { type PropType } from 'vue';
  import type { FileUploadData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { formatFileSize } from '@/modules/negotiations/supplier/register/helpers/fileHelper';

  defineProps({
    fileUploadData: {
      type: Object as PropType<FileUploadData>,
      required: true,
    },
    showRemoveButton: {
      type: Boolean,
      default: true,
      required: false,
    },
  });

  const emit = defineEmits(['handleRemove', 'handleDownload']);

  const handleRemove = () => {
    emit('handleRemove');
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    min-height: 99px;
    border-radius: 6px;
    background-color: $color-white;
    border: 1px solid $color-black-5;
    display: flex;
    justify-content: space-between;
    padding: 16px;
    gap: 16px;

    &-center {
      align-items: center;
    }
  }

  .file-icon {
    color: $color-blue;
    width: 20px;
    height: 27px;
  }

  .status-container {
    flex-grow: 1;
  }

  .filename-container {
    display: flex;
    justify-content: space-between;

    &-center {
      align-items: center;
    }
  }

  .filename {
    font-size: 14px;
    font-weight: 500;
    color: $color-black;

    &-download {
      cursor: pointer;
    }
  }

  .close-icon {
    color: $color-primary-strong;
    width: 20px;
    height: 20px;
    cursor: pointer;
  }

  .info-upload-progress {
    display: flex;
    justify-content: space-between;
  }

  .info-upload-progress-text {
    font-size: 12px;
    font-weight: 500;
    color: $color-black-3;
  }
</style>
