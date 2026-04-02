<!-- PaymentConditionsComponent.vue -->
<template>
  <a-row :gutter="24">
    <!-- Días de crédito -->
    <a-col :span="12">
      <a-form-item name="creditDays" :rules="formRules.creditDays">
        <template #label>
          <required-label label="Días de crédito:" />
        </template>
        <a-input-number
          v-model:value="formStateTreasury.creditDays"
          min="0"
          placeholder="Días de crédito"
          class="custom-full-width"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>

    <!-- Fecha de inicio SUNAT -->
    <a-col :span="12">
      <a-form-item name="startDateSunat" :rules="formRules.startDateSunat">
        <template #label>
          <required-label label="Fecha de inicio SUNAT:" />
        </template>
        <a-date-picker
          v-model:value="formStateTreasury.startDateSunat"
          placeholder="Selecciona fecha"
          :allowClear="false"
          format="DD/MM/YYYY"
          value-format="YYYY-MM-DD"
          class="custom-full-width"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>

    <!-- Días de crédito SUNAT -->
    <a-col :span="12">
      <a-form-item name="creditDaysSunat" :rules="formRules.creditDaysSunat">
        <template #label>
          <required-label label="Días de crédito SUNAT:" />
        </template>
        <a-input-number
          v-model:value="formStateTreasury.creditDaysSunat"
          min="0"
          placeholder="Días de crédito SUNAT"
          class="custom-full-width"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>
  </a-row>
</template>

<script setup lang="ts">
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';
  import { usePaymentCondition } from '@/modules/negotiations/supplier/register/composables/usePaymentCondition';

  defineProps({
    isEditable: {
      type: Boolean,
      default: false,
    },
  });

  const { formStateTreasury, formRules } = usePaymentCondition();
</script>
<style scoped></style>
