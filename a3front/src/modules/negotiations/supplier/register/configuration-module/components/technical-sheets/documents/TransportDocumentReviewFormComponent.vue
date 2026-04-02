<template>
  <a-drawer
    :open="showDrawerForm"
    title="Revisar documento"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <a-spin :spinning="isLoading">
      <div>
        <div>
          <a-date-picker
            placeholder="Seleccionar"
            class="w-100"
            v-model:value="formState.expirationDate"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            disabled
          />
        </div>
        <div class="mt-4">
          <UploadFileStatusComponent
            :fileUploadData="documentFileData"
            :showRemoveButton="false"
            @handleDownload="handleDownloadDocument"
          />
        </div>
        <div class="mt-3">
          <ObservationFormComponent
            :formState="formState"
            :formRules="formRules"
            ref="formRefObservation"
          />
        </div>
      </div>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <RejectedButtonComponent :disabled="isLoading" @onRejected="handleRejected" />
        </a-col>
        <a-col :span="12">
          <ApprovedButtonComponent :disabled="isLoading" @onApproved="handleApproved" />
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import UploadFileStatusComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/UploadFileStatusComponent.vue';
  import RejectedButtonComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/RejectedButtonComponent.vue';
  import ApprovedButtonComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ApprovedButtonComponent.vue';
  import ObservationFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ObservationFormComponent.vue';
  import { useTransportDocumentReviewForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/documents/useTransportDocumentReviewForm';
  import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
  import type { TransportDocumentReviewFormProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const props = defineProps<TransportDocumentReviewFormProps<TypeTechnicalSheetEnum>>();
  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formRefObservation,
    formState,
    isLoading,
    formRules,
    documentFileData,
    handleClose,
    handleApproved,
    handleRejected,
    handleDownloadDocument,
  } = useTransportDocumentReviewForm(emit, props);
</script>

<style scoped lang="scss"></style>
