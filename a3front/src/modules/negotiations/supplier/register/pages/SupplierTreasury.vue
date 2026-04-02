<!-- SupplierTreasury.vue -->
<template>
  <div class="supplier-treasury">
    <!-- Mostrar SupplierNegotiations en modo solo lectura -->
    <SupplierNegotiations />

    <!-- Colapsable de Condiciones de Pago (Editable) -->
    <a-collapse
      v-model:activeKey="collapseSectionKeys"
      expand-icon-position="end"
      ghost
      class="payment-conditions-collapse"
    >
      <template #expandIcon="{ isActive }">
        <font-awesome-icon :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" />
      </template>

      <a-collapse-panel key="payment-condition">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            <span>5. Condiciones de pago</span>
          </a-divider>
        </template>
        <PaymentConditionsComponent
          :isEditable="isEditable(EditableTreasurySectionEnum.PAYMENT_CONDITION)"
        />
      </a-collapse-panel>
    </a-collapse>

    <SupplierDataRegistrationNotifyComponent
      v-model:showModal="registrationNotifyModals.treasury"
      :bodyTitle="dataRegistrationNotify.bodyTitle"
      :bodyMessage="dataRegistrationNotify.bodyMessage"
    />
  </div>
</template>

<script setup lang="ts">
  import { onMounted, type PropType } from 'vue';
  import SupplierNegotiations from '@/modules/negotiations/supplier/register/pages/SupplierNegotiations.vue';
  import PaymentConditionsComponent from '@/modules/negotiations/supplier/register/components/PaymentConditionsComponent.vue';
  import SupplierDataRegistrationNotifyComponent from '@/modules/negotiations/supplier/register/components/SupplierDataRegistrationNotifyComponent.vue';
  import { useSupplierTreasury } from '@/modules/negotiations/supplier/register/composables/useSupplierTreasury';
  import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
  import { EditableTreasurySectionEnum } from '@/modules/negotiations/supplier/register/enums/editable-treasury-section.enum';

  const props = defineProps({
    editableSections: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  });

  const { dataRegistrationNotify, registrationNotifyModals, loadSupplierPaymentTerm } =
    useSupplierTreasury();

  const { collapseSectionKeys, isEditableSection } = useSupplierFormView();

  const isEditable = (section: string): boolean => {
    return isEditableSection(section, props.editableSections);
  };

  onMounted(() => {
    loadSupplierPaymentTerm();
  });
</script>

<style scoped lang="scss"></style>
