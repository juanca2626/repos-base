<template>
  <a-drawer
    :open="showDrawerForm"
    title="Revisar imágenes"
    :width="760"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <a-spin :spinning="isLoading">
      <div class="container-drawer-title">
        <span class="drawer-title custom-primary-font"> Revisar imágenes adjuntadas </span>
      </div>

      <div v-if="formState.images" class="image-upload-container">
        <template v-for="(imageKey, index) in Object.keys(formState.images)" :key="index">
          <div class="image-upload-item">
            <span class="image-title custom-primary-font">
              {{ index + 1 }}. {{ formState.images[imageKey].title }}
            </span>
            <UploadPhotoComponent
              :fileUploadData="formState.images[imageKey].fileUploadData"
              v-model:fileList="formState.images[imageKey].files"
              :imageKey="imageKey"
              :showRemoveIcon="false"
              :disabledUpload="true"
            />
          </div>
        </template>
      </div>

      <div class="mt-4">
        <ObservationFormComponent
          :formState="formState"
          :formRules="formRules"
          ref="formRefObservation"
        />
      </div>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <RejectedButtonComponent :disabled="isLoading" @onRejected="handleRejected" />
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            :disabled="isLoading"
            @click="handleApproved"
          >
            <div class="container-buttons">
              <font-awesome-icon
                :icon="['fas', 'circle-check']"
                class="icon-button icon-button-approved"
              />
              <span class="text-button text-button-approved"> Aprobar </span>
            </div>
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import UploadPhotoComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/partials/UploadPhotoComponent.vue';
  import ObservationFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ObservationFormComponent.vue';
  import RejectedButtonComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/RejectedButtonComponent.vue';
  import type { VehiclePhotoFormProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { useTransportVehiclePhotoReviewForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/photos/useTransportVehiclePhotoReviewForm';

  const props = defineProps<VehiclePhotoFormProps>();
  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formState,
    isLoading,
    formRules,
    formRefObservation,
    handleClose,
    handleApproved,
    handleRejected,
  } = useTransportVehiclePhotoReviewForm(emit, props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-drawer-title {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
    margin-bottom: 25px;

    .drawer-title {
      font-weight: 700;
      font-size: 18px;
    }
  }

  .image-upload-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;

    .image-upload-item {
      display: flex;
      flex-direction: column;
    }

    .image-title {
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 4px;
    }
  }

  .icon-button {
    width: 20px;
    height: 20px;

    &-rejected {
      color: $color-primary-strong;
    }

    &-approved {
      color: $color-white;
    }
  }

  .text-button {
    font-size: 16px;
    font-weight: 600;

    &-rejected {
      color: $color-primary-strong;
    }

    &-approved {
      color: $color-white;
    }
  }

  .container-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
  }
</style>
