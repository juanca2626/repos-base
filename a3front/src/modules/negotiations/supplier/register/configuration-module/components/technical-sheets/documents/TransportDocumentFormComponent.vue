<template>
  <a-drawer
    :open="showDrawerForm"
    :title="isEditMode ? 'Actualizar documento' : 'Subir documento'"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <template v-if="selectedDocument.typeDocumentId">
      <DocumentUploadGuidelinesAlert
        :typeDocumentId="selectedDocument.typeDocumentId"
        :isTransportVehicle="isTransportVehicle"
        :isDrawerOpen="showDrawerForm"
      />
    </template>

    <a-spin :spinning="isLoading">
      <a-flex gap="middle" vertical>
        <a-form
          layout="vertical"
          :model="formState"
          ref="formRefTransportDocument"
          :rules="formRules"
        >
          <a-row :gutter="16">
            <a-col class="gutter-row" :span="24" v-if="showNotApplicable">
              <a-checkbox v-model:checked="formState.notApplicable" class="mb-3">
                No aplica certificado para esta unidad
              </a-checkbox>
            </a-col>

            <template v-if="!formState.notApplicable">
              <a-col class="gutter-row" :span="24">
                <a-form-item label="Fecha de vencimiento del documento:" name="expirationDate">
                  <a-date-picker
                    placeholder="Seleccionar"
                    class="w-100"
                    v-model:value="formState.expirationDate"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                    :disabled-date="disabledExpirationDate"
                  />
                </a-form-item>
              </a-col>
              <a-col class="gutter-row" :span="24">
                <FileUploadInput
                  :fileUploadData="fileUploadData"
                  :validateUploadFile="validateUploadFile"
                  :customRequest="handleCustomUpload"
                  @onRemoveFile="handleRemoveFile"
                  @onDownloadFile="handleDownloadDocument"
                />
              </a-col>
            </template>
          </a-row>

          <template v-if="isApproved(selectedDocument.status)">
            <div :class="{ 'mt-3': fileUploadData.isFileUploaded || fileUploadData.uploading }">
              <ObservationFormComponent
                :formState="formState"
                :formRules="formRulesObservation"
                ref="formRefObservation"
              />
            </div>
          </template>
        </a-form>
      </a-flex>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <template v-if="isApproved(selectedDocument.status)">
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
  import { useTransportDocumentForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/documents/useTransportDocumentForm';
  import RejectedButtonComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/RejectedButtonComponent.vue';
  import ObservationFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ObservationFormComponent.vue';
  import FileUploadInput from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/FileUploadInput.vue';
  import DocumentUploadGuidelinesAlert from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/DocumentUploadGuidelinesAlert.vue';
  import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
  import type { TransportDocumentFormProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const props = defineProps<TransportDocumentFormProps<TypeTechnicalSheetEnum>>();
  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formRefTransportDocument,
    formState,
    isLoading,
    formRules,
    fileUploadData,
    showNotApplicable,
    isEditMode,
    formRulesObservation,
    formRefObservation,
    isTransportVehicle,
    disabledActionButton,
    handleClose,
    handleSubmit,
    handleCustomUpload,
    handleRemoveFile,
    disabledExpirationDate,
    validateUploadFile,
    handleRejected,
    handleDownloadDocument,
    isApproved,
  } = useTransportDocumentForm(emit, props);
</script>

<style scoped lang="scss"></style>
