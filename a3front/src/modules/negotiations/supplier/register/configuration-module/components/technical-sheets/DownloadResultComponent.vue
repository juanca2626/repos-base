<template>
  <a-modal
    class="module-negotiations custom-modal"
    :open="showModal"
    @ok="handleDownload"
    @cancel="handleCancel"
    :width="550"
  >
    <template #title>
      <span class="custom-primary-font"> Descargar resultados </span>
    </template>

    <a-spin :spinning="isLoading">
      <a-form layout="vertical" :model="formState" ref="formRefDownloadResult" :rules="formRules">
        <p class="custom-primary-font body-title">Prepara tu archivo de descarga:</p>

        <a-form-item class="mt-4" name="filename">
          <template #label>
            <span class="custom-primary-font"> Nombre del archivo </span>
          </template>
          <a-input
            v-model:value="formState.filename"
            placeholder="Nombre del archivo"
            :allow-clear="true"
          />
        </a-form-item>

        <a-form-item>
          <template #label>
            <span class="custom-primary-font"> Formato de archivo </span>
          </template>
          <a-radio-group v-model:value="formState.format">
            <template v-for="row in availableFileFormats">
              <a-radio :value="row.value">
                <span class="custom-primary-font">
                  {{ row.name }}
                </span>
              </a-radio>
            </template>
          </a-radio-group>
        </a-form-item>

        <template v-if="isTransportVehicleActive">
          <a-divider />

          <a-form-item>
            <template #label>
              <span class="custom-primary-font"> Selecciona la ciudad: </span>
            </template>

            <a-checkbox-group v-model:value="formState.supplierBranchOfficeIds" class="mt-2">
              <a-row :gutter="[15, 15]">
                <a-col
                  v-for="row in locationData"
                  :key="row.supplier_branch_office_id"
                  :span="12"
                  class="container-branch-offices"
                >
                  <a-checkbox :value="row.supplier_branch_office_id">
                    <span class="custom-primary-font display-name">
                      {{ row.display_name }}
                    </span>
                  </a-checkbox>
                </a-col>
              </a-row>
            </a-checkbox-group>
          </a-form-item>
        </template>
      </a-form>

      <a-alert type="warning" v-if="visibleAlert">
        <template #description>
          <div class="container-alert-description">
            <div class="alert-description">
              <div>
                <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="info-icon mt-1" />
              </div>
              <div>
                <span class="title-info custom-primary-font"> ¡Toma en cuenta! </span>
                <div class="mt-2 detail-info custom-primary-font">
                  <span>
                    La descarga del archivo puede tardar unos minutos por la cantidad de información
                    seleccionada.
                  </span>
                  <span class="d-block mt-2"> Mientras puedes aprovechar e ir por un café :) </span>
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
    </a-spin>

    <template #footer>
      <div class="container-actions">
        <a-button
          type="primary"
          class="btn-secondary ant-btn-md custom-button"
          @click="handleCancel"
        >
          Cancelar
        </a-button>
        <a-button
          type="primary"
          class="ant-btn-md custom-button"
          @click="handleDownload()"
          :disabled="isLoading"
        >
          <CustomDownloadIcon stroke="#FFFFFF" :strokeWidth="2" />
          Descargar
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup lang="ts">
  import type { DownloadResultProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { useDownloadResult } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/useDownloadResult';
  import CustomDownloadIcon from '@/modules/negotiations/supplier/components/icons/CustomDownloadIcon.vue';

  const props = defineProps<DownloadResultProps>();

  const emit = defineEmits(['update:showModal']);

  const {
    isLoading,
    formState,
    availableFileFormats,
    visibleAlert,
    formRefDownloadResult,
    formRules,
    isTransportVehicleActive,
    handleDownload,
    handleCancel,
    handleCloseAlert,
  } = useDownloadResult(emit, props);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-actions {
    display: flex;
    justify-content: end;

    .custom-button {
      width: 140px;
      height: 50px;
    }
  }

  .container-alert-description {
    display: flex;
    justify-content: space-between;

    .alert-description {
      display: flex;
      gap: 10px;

      .info-icon {
        flex-shrink: 0;
        width: 17px;
        height: 15px;
        color: $color-warning;
      }

      .title-info {
        font-size: 15px;
        font-weight: 600;
        color: $color-black;
      }

      .detail-info {
        font-size: 13px;
        font-weight: 400;
        color: $color-black;
      }
    }

    .close-icon {
      color: $color-black-5 !important;
      cursor: pointer;
      width: 18px;
      height: 18px;
      margin-left: 10px;
    }
  }

  .container-branch-offices {
    word-wrap: break-word;
    white-space: normal;

    .display-name {
      font-size: 13px;
    }
  }

  .body-title {
    font-size: 15px;
    font-weight: 700;
  }
</style>
