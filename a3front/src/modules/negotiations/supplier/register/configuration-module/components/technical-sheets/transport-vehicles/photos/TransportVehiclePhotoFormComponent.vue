<template>
  <a-drawer
    :open="showDrawerForm"
    :title="isEditMode ? 'Actualizar imágenes' : 'Adjuntar imágenes'"
    :width="760"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <a-spin :spinning="isLoading">
      <div class="images-guidelines">
        <a-alert v-if="visibleAlert">
          <template #description>
            <div class="container-alert-description">
              <div class="alert-description">
                <div>
                  <CustomInfoCircleIcon
                    :width="18"
                    :height="18"
                    fill="#5C5AB4"
                    class="info-icon mt-1"
                  />
                </div>
                <div>
                  <span class="title-info custom-primary-font">
                    Pautas para adjuntar las imágenes:
                  </span>
                  <div>
                    <ul class="ul-info custom-primary-font mt-2">
                      <li>
                        Utiliza imágenes de calidad y nítidas, coherentes con el título solicitado
                        en cada imagen.
                      </li>
                      <li>Considera los siguientes datos adicionales:</li>
                    </ul>
                    <div class="mt-2 container-detail-info custom-primary-font">
                      <div class="info-list-item">
                        <span class="prefix">*</span>
                        <span class="text"
                          >Mostrando asientos con cinturones de seguridad en buen estado.</span
                        >
                      </div>
                      <div class="info-list-item">
                        <span class="prefix">**</span>
                        <span class="text"
                          >Mostrando botiquín de primeros auxilios, triángulo de seguridad.</span
                        >
                      </div>
                      <div class="info-list-item">
                        <span class="prefix">***</span>
                        <span class="text">Mostrando la gata y linterna.</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <font-awesome-icon
                  :icon="['fas', 'xmark']"
                  class="close-icon"
                  @click="handleCloseAlert"
                />
              </div>
            </div>
          </template>
        </a-alert>
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
              :onUpload="handleUploadPhoto"
              :onRemove="handleRemovePhoto"
            />
          </div>
        </template>
      </div>

      <template v-if="isApproved(selectedVehiclePhoto.status)">
        <div class="mt-4">
          <ObservationFormComponent
            :formState="formState"
            :formRules="formRules"
            ref="formRefObservation"
          />
        </div>
      </template>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <template v-if="isApproved(selectedVehiclePhoto.status)">
            <RejectedButtonComponent
              :disabled="disabledActionButton"
              @onRejected="handleRejected"
            />
          </template>
          <template v-else>
            <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose">
              Cancelar
            </a-button>
          </template>
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="disabledActionButton"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import CustomInfoCircleIcon from '@/modules/negotiations/supplier/components/icons/CustomInfoCircleIcon.vue';
  import UploadPhotoComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/partials/UploadPhotoComponent.vue';
  import ObservationFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ObservationFormComponent.vue';
  import RejectedButtonComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/RejectedButtonComponent.vue';
  import type { VehiclePhotoFormProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { useTransportVehiclePhotoForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/photos/useTransportVehiclePhotoForm';

  const props = defineProps<VehiclePhotoFormProps>();
  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formState,
    isLoading,
    visibleAlert,
    isEditMode,
    formRules,
    formRefObservation,
    disabledActionButton,
    handleClose,
    handleSubmit,
    handleCloseAlert,
    handleUploadPhoto,
    handleRemovePhoto,
    handleRejected,
    isApproved,
  } = useTransportVehiclePhotoForm(emit, props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

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

  .images-guidelines {
    .container-alert-description {
      display: flex;
      justify-content: space-between;
    }

    .alert-description {
      display: flex;
      gap: 10px;

      > .info-icon {
        flex-shrink: 0;
      }
    }

    .close-icon {
      color: $color-black-5 !important;
      cursor: pointer;
      width: 18px;
      height: 18px;
      margin-left: 10px;
    }

    .title-info {
      font-size: 16px;
      font-weight: 600;
    }

    .ul-info {
      padding-left: 8px;
      margin: 0;
      list-style-position: inside;
      font-size: 13px;
      font-weight: 400;
    }

    .container-detail-info {
      margin-left: 30px;

      .info-list-item {
        display: flex;
        align-items: flex-start;

        font-size: 13px;
        font-weight: 400;

        .prefix {
          min-width: 20px;
          text-align: right;
          margin-right: 5px;
          font-weight: 700;
        }

        .text {
          flex: 1;
        }
      }
    }
  }
</style>
