<!-- SupplierNegotiations.vue -->
<template>
  <div>
    <a-alert
      class="custom-alert-description"
      description="Al editar el proveedor debes guardar los cambios realizados y notificar a los departamentos involucrados en el proceso."
      type="warning"
      show-icon
      closable
    >
      <template #icon>
        <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />
      </template>
    </a-alert>

    <!-- Añadir Observaciones -->
    <a-collapse v-model:activeKey="collapseSectionKeys" expand-icon-position="start" ghost>
      <template #expandIcon="{ isActive }">
        <font-awesome-icon :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" />
      </template>

      <a-collapse-panel key="observations">
        <template #header>
          <span class="text-observations">Añadir observaciones</span>
        </template>
        <a-textarea
          v-model:value="formStateNegotiation.observations"
          :rows="2"
          :maxlength="250"
          placeholder="Escribe tus observaciones aquí (máximo 250 caracteres)"
          class="textarea-custom"
          :disabled="!isEditable(EditableNegotiationSectionEnum.OBSERVATIONS)"
        />
        <div class="observations-info" style="text-align: right; margin-top: 5px">
          <span>{{ formStateNegotiation.observations.length }} / 250 caracteres</span>
        </div>
      </a-collapse-panel>
    </a-collapse>

    <a-collapse v-model:activeKey="collapseSectionKeys" expand-icon-position="end" ghost>
      <template #expandIcon="{ isActive }">
        <font-awesome-icon :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" />
      </template>

      <!-- Información general -->
      <a-collapse-panel key="general-information">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            1. Información general
          </a-divider>
        </template>
        <GeneralInformation
          :isEditable="isEditable(EditableNegotiationSectionEnum.GENERAL_INFORMATION)"
        />
      </a-collapse-panel>

      <!-- Ubicación comercial -->
      <a-collapse-panel key="commercial-location">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            2. Ubicación comercial
          </a-divider>
        </template>
        <CommercialLocation
          :isEditable="isEditable(EditableNegotiationSectionEnum.COMMERCIAL_LOCATION)"
        />
      </a-collapse-panel>

      <!-- Lugar(es) de operación -->
      <a-collapse-panel key="operation-location">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            3. Lugar(es) de operación
          </a-divider>
        </template>
        <OperationLocations
          :isEditable="isEditable(EditableNegotiationSectionEnum.OPERATION_LOCATIONS)"
        />
      </a-collapse-panel>

      <!-- Información del contacto -->
      <a-collapse-panel key="contact-information">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            4. Información del contacto
          </a-divider>
        </template>
        <ContactInformation
          :isEditable="isEditable(EditableNegotiationSectionEnum.CONTACT_INFORMATION)"
        />
      </a-collapse-panel>
    </a-collapse>

    <SupplierDataRegistrationNotifyComponent
      v-model:showModal="registrationNotifyModals.negotiation"
      :bodyTitle="dataRegistrationNotify.bodyTitle"
      :bodyMessage="dataRegistrationNotify.bodyMessage"
    />
  </div>
</template>

<script setup lang="ts">
  import { defineProps, onMounted, onBeforeUnmount, type PropType } from 'vue';
  import GeneralInformation from '@/modules/negotiations/supplier/register/components/GeneralInformationComponent.vue';
  import CommercialLocation from '@/modules/negotiations/supplier/register/components/CommercialLocationComponent.vue';
  import OperationLocations from '@/modules/negotiations/supplier/register/components/OperationLocationsComponent.vue';
  import ContactInformation from '@/modules/negotiations/supplier/register/components/ContactInformationComponent.vue';
  import SupplierDataRegistrationNotifyComponent from '@/modules/negotiations/supplier/register/components/SupplierDataRegistrationNotifyComponent.vue';
  import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
  import { useSupplierNegotiation } from '@/modules/negotiations/supplier/register/composables/useSupplierNegotiation';
  import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
  import { EditableNegotiationSectionEnum } from '@/modules/negotiations/supplier/register/enums/editable-negotiation-section.enum';

  const props = defineProps({
    editableSections: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  });

  const { dataRegistrationNotify, registrationNotifyModals } = useSupplierNegotiation();

  const { formStateNegotiation } = useSupplierFormStoreFacade();

  const { collapseSectionKeys, isEditableSection } = useSupplierFormView();

  const isEditable = (section: string): boolean => {
    return isEditableSection(section, props.editableSections);
  };

  onMounted(() => {
    document.body.classList.add('module-negotiations');
  });

  onBeforeUnmount(() => {
    document.body.classList.remove('module-negotiations');
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .text-observations {
    font-size: 14px;
    font-weight: 500;
  }

  .custom-alert-description {
    :deep(.ant-alert-description) {
      font-size: 14px;
      font-weight: 400;
      color: $color-black !important;
    }
  }
</style>
