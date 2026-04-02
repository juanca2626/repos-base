<template>
  <div class="module-negotiations p-4">
    <a-tabs v-model:activeKey="activeKey" class="px-2">
      <a-tab-pane
        key="1"
        :tab="isFormEditMode ? 'Edición del proveedor' : 'Registro del proveedor'"
      >
        <div>
          <a-spin :spinning="isLoadingForm">
            <div class="header-container">
              <div
                :style="{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  marginRight: `${marginRightInfoCollaborators}px`,
                }"
              >
                <span class="info-tabs info-collaborators">Colaboradores:</span>
              </div>

              <a-tabs
                v-model:activeKey="activeCollaboratorTab"
                class="collaborators-tabs"
                :tabBarGutter="4"
                @change="handleChangeCollaboratorTab"
              >
                <template #leftExtra>
                  <div class="title-header-container">
                    <i class="icon-layout title-icon"></i>
                    <span class="title-header">Completar el formulario del registro</span>
                  </div>
                </template>

                <a-tab-pane key="negotiation" tab="Negociaciones">
                  <SupplierFormWrapper
                    v-if="activeCollaboratorTab === 'negotiation'"
                    :formModel="formStateNegotiation"
                    :rules="rulesNegotiation"
                    :saveFormHandler="saveSupplierNegotiation"
                    :onCancel="handleCancel"
                  >
                    <SupplierNegotiations :editableSections="editableNegotiationSections" />
                  </SupplierFormWrapper>
                </a-tab-pane>
                <a-tab-pane key="treasury" tab="Tesorería" :disabled="!isFormEditMode">
                  <SupplierFormWrapper
                    v-if="activeCollaboratorTab === 'treasury'"
                    :formModel="formStateTreasury"
                    :saveFormHandler="saveSupplierTreasury"
                    :onCancel="handleCancel"
                  >
                    <SupplierTreasury :editableSections="editableTreasurySections" />
                  </SupplierFormWrapper>
                </a-tab-pane>
                <a-tab-pane key="accounting" tab="Contabilidad" :disabled="!isFormEditMode">
                  <SupplierFormWrapper
                    v-if="activeCollaboratorTab === 'accounting'"
                    :formModel="formStateAccounting"
                    :saveFormHandler="saveSupplierAccounting"
                    :onCancel="handleCancel"
                  >
                    <SupplierAccounting />
                  </SupplierFormWrapper>
                </a-tab-pane>
              </a-tabs>
            </div>
          </a-spin>
        </div>
      </a-tab-pane>
      <a-tab-pane key="2" tab="Configuración de módulos" :disabled="disabledConfigTab">
        <template v-if="!configSubClassification">
          <SupplierConfigurationModuleComponent />
        </template>
        <template v-else>
          <SupplierConfigurationModuleViewComponent />
        </template>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, computed } from 'vue';
  import SupplierNegotiations from '@/modules/negotiations/supplier/register/pages/SupplierNegotiations.vue';
  import SupplierTreasury from '@/modules/negotiations/supplier/register/pages/SupplierTreasury.vue';
  import SupplierAccounting from '@/modules/negotiations/supplier/register/pages/SupplierAccounting.vue';
  import SupplierFormWrapper from '@/modules/negotiations/supplier/register/components/SupplierFormWrapper.vue';
  import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
  import { useOperationLocation } from '@/modules/negotiations/supplier/register/composables/useOperationLocation';
  import { useSupplierNegotiation } from '@/modules/negotiations/supplier/register/composables/useSupplierNegotiation';
  import { useSupplierTreasury } from '@/modules/negotiations/supplier/register/composables/useSupplierTreasury';
  import { useSupplierAccounting } from '@/modules/negotiations/supplier/register/composables/useSupplierAccounting';
  import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
  import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
  import SupplierConfigurationModuleComponent from '@/modules/negotiations/supplier/register/components/SupplierConfigurationModuleComponent.vue';
  import { useGeneralInformation } from '@/modules/negotiations/supplier/register/composables/useGeneralInformation';
  import SupplierConfigurationModuleViewComponent from '@/modules/negotiations/supplier/register/components/SupplierConfigurationModuleViewComponent.vue';

  const {
    saveSupplierNegotiation,
    formStateNegotiation,
    rulesNegotiation,
    loadSupplierData,
    getRouteSupplierId,
  } = useSupplierNegotiation();

  const {
    setIsFormEditMode,
    isFormEditMode,
    setIsLoadingForm,
    configSubClassification,
    setConfigSubClassification,
  } = useSupplierFormStoreFacade();

  const { saveSupplierTreasury, formStateTreasury } = useSupplierTreasury();

  const { saveSupplierAccounting, formStateAccounting } = useSupplierAccounting();

  const { handleCancel, isLoadingForm } = useSupplierForm();

  const {
    marginRightInfoCollaborators,
    activeCollaboratorTab,
    activeKey,
    editableNegotiationSections,
    editableTreasurySections,
    handleChangeCollaboratorTab,
  } = useSupplierFormView();

  const { loadLocationOptions } = useOperationLocation();

  const { loadSupplierClassifications } = useGeneralInformation();

  const loadData = async () => {
    const isEditMode = !!getRouteSupplierId();

    setIsLoadingForm(true);
    setIsFormEditMode(isEditMode);

    await loadSupplierClassifications();
    await loadLocationOptions();
    setIsLoadingForm(false);

    if (isEditMode) {
      await loadSupplierData();
    }

    // inicializar subclasificacion del proveedor
    setConfigSubClassification();
  };

  const disabledConfigTab = computed(
    () => !isFormEditMode || !formStateNegotiation.classifications
  );

  onMounted(loadData);
</script>

<style scoped>
  .header-container {
    :deep(.ant-tabs-nav-wrap) {
      justify-content: flex-end;
    }
  }

  .title-icon {
    font-weight: 600;
  }

  .title-header {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.5;
  }

  .title-header-container {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .divider {
    flex-grow: 1;
    height: 1px;
    background-color: #d9d9d9;
    margin-left: 0.5rem;
    margin-right: 1rem;
  }
</style>
