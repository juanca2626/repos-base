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

    <a-spin :spinning="isLoadingMain">
      <a-form layout="vertical" :model="formState" ref="formRefDownloadResult" :rules="formRules">
        <p class="custom-primary-font body-title">Prepara tu archivo de descarga:</p>

        <a-row :gutter="16" class="mt-4">
          <a-col class="gutter-row" :span="6">
            <a-form-item label="Periodo" name="periodYear">
              <a-select class="w-100" v-model:value="formState.periodYear" :options="periodYears" />
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="18">
            <a-form-item name="filename">
              <template #label>
                <span class="custom-primary-font"> Nombre del archivo </span>
              </template>
              <a-input
                v-model:value="formState.filename"
                placeholder="Nombre del archivo"
                :allow-clear="true"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
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
          :disabled="isLoadingMain"
        >
          <CustomDownloadIcon stroke="#FFFFFF" :strokeWidth="2" />
          Descargar
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup lang="ts">
  import CustomDownloadIcon from '@/modules/negotiations/supplier/components/icons/CustomDownloadIcon.vue';
  import type { DownloadResultProps } from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
  import { useDownloadResult } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useDownloadResult';

  const props = defineProps<DownloadResultProps>();

  const emit = defineEmits(['update:showModal']);

  const {
    isLoadingMain,
    formState,
    formRefDownloadResult,
    formRules,
    periodYears,
    handleDownload,
    handleCancel,
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

  .body-title {
    font-size: 15px;
    font-weight: 700;
  }
</style>
