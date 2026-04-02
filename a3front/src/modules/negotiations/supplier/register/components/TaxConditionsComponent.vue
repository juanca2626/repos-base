<!-- TaxConditionsComponent.vue -->
<template>
  <div class="tax-conditions">
    <a-row :gutter="24">
      <!-- Tipo de IGV / IVA -->
      <a-col :span="12">
        <a-form-item name="ivaOptionsId" :rules="formRules.ivaOptionsId">
          <template #label>
            <required-label label="Tipo de IGV / IVA:" />
          </template>
          <a-select
            v-model:value="formStateAccounting.ivaOptionsId"
            placeholder="Selecciona tipo de IVA"
            :options="ivaOptions"
            :filter-option="filterOption"
            :showArrow="true"
            show-search
            class="custom-full-width"
          />
        </a-form-item>
      </a-col>

      <!-- IGV / IVA -->
      <a-col :span="12">
        <a-form-item name="taxRatesId" :rules="formRules.taxRatesId">
          <template #label>
            <required-label label="IGV / IVA:" />
          </template>
          <a-select
            v-model:value="formStateAccounting.taxRatesId"
            placeholder="Selecciona IVA"
            :options="taxRates"
            :filter-option="filterOption"
            :showArrow="true"
            show-search
            class="custom-full-width"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <!-- Última fecha de facturación -->
    <a-row>
      <a-col :span="12">
        <a-form-item name="lastBillingDate" :rules="formRules.lastBillingDate">
          <template #label>
            <required-label label="Última fecha de facturación:" />
          </template>
          <a-date-picker
            v-model:value="formStateAccounting.lastBillingDate"
            placeholder="Selecciona fecha"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
            class="custom-full-width"
          />
        </a-form-item>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';
  import { useTaxCondition } from '@/modules/negotiations/supplier/register/composables/useTaxCondition';

  const { taxRates, ivaOptions, filterOption, formStateAccounting, formRules } = useTaxCondition();
</script>

<style scoped lang="scss">
  .tax-conditions {
    .form-item {
      margin-bottom: 1rem;
    }
    .form-label {
      font-weight: bold;
      margin-bottom: 0.5rem;
      display: block;
    }
    .ant-select,
    .ant-picker {
      width: 100%;
    }
  }
</style>
