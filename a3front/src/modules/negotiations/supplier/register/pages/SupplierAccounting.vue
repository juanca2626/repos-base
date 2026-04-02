<!-- SupplierAccounting.vue -->
<template>
  <div class="supplier-accounting">
    <!-- Mostrar SupplierTreasury en modo solo lectura -->
    <SupplierTreasury />

    <!-- Colapsable de Condiciones Tributarias (Editable) -->
    <a-collapse
      v-model:activeKey="collapseSectionKeys"
      expand-icon-position="end"
      ghost
      class="tax-conditions-collapse"
    >
      <template #expandIcon="{ isActive }">
        <font-awesome-icon :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" />
      </template>

      <a-collapse-panel key="tax-condition">
        <template #header>
          <a-divider orientation="left" orientation-margin="0px">
            <span>6. Condiciones tributarias</span>
          </a-divider>
        </template>
        <TaxConditionsComponent />
      </a-collapse-panel>
    </a-collapse>

    <SupplierDataRegistrationNotifyComponent
      v-model:showModal="registrationNotifyModals.accounting"
      :bodyTitle="dataRegistrationNotify.bodyTitle"
      :bodyMessage="dataRegistrationNotify.bodyMessage"
    />
  </div>
</template>

<script setup lang="ts">
  import { onMounted } from 'vue';
  import SupplierTreasury from '@/modules/negotiations/supplier/register/pages/SupplierTreasury.vue';
  import TaxConditionsComponent from '@/modules/negotiations/supplier/register/components/TaxConditionsComponent.vue';
  import SupplierDataRegistrationNotifyComponent from '@/modules/negotiations/supplier/register/components/SupplierDataRegistrationNotifyComponent.vue';
  import { useSupplierAccounting } from '@/modules/negotiations/supplier/register/composables/useSupplierAccounting';
  import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';

  const { dataRegistrationNotify, registrationNotifyModals, loadSupplierTaxCondition } =
    useSupplierAccounting();

  const { collapseSectionKeys } = useSupplierFormView();

  onMounted(() => {
    loadSupplierTaxCondition();
  });
</script>

<style scoped lang="scss">
  .supplier-accounting {
    padding: 1rem;
  }

  .tax-conditions-collapse {
    margin-top: 1rem;
  }
</style>
