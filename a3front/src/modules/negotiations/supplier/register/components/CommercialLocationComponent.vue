<!-- commercial-location-component.vue -->
<template>
  <a-row :gutter="24">
    <!-- Dirección principal -->
    <a-col :span="12">
      <a-form-item name="commercialAddress" :rules="formRules.commercialAddress">
        <template #label>
          <required-label label="Dirección comercial:" />
        </template>
        <a-input
          v-model:value="formStateNegotiation.commercialAddress"
          placeholder="Escribe"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>
    <!-- País / Departamento / Ciudad / Distrito -->
    <a-col :span="12">
      <a-form-item name="comercialLocation" :rules="formRules.comercialLocation">
        <template #label>
          <required-label label="País / Departamento / Ciudad / Distrito:" />
        </template>
        <a-select
          v-model:value="formStateNegotiation.comercialLocation"
          placeholder="Seleccione"
          popupClassName="custom-dropdown-backend"
          :options="locationCommercialOptions"
          :filter-option="filterOption"
          show-search
          :loading="isLoadingLocations"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>
  </a-row>
</template>

<script setup lang="ts">
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';
  import { useCommercialLocation } from '@/modules/negotiations/supplier/register/composables/useCommercialLocation';

  defineProps({
    isEditable: {
      type: Boolean,
      default: false,
    },
  });

  const {
    locationCommercialOptions,
    isLoadingLocations,
    formStateNegotiation,
    filterOption,
    formRules,
  } = useCommercialLocation();
</script>
<style scoped></style>
