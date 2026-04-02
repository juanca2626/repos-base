<template>
  <a-row :gutter="24">
    <a-col :span="12">
      <a-form-item name="phone" :rules="formRules.phone">
        <template #label>
          <required-label label="Teléfono o celular principal:" />
        </template>
        <a-input
          v-model:value="formStateNegotiation.phone"
          :maxLength="9"
          placeholder="Escribe"
          type="tel"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>
    <a-col :span="12">
      <a-form-item name="email" :rules="formRules.email">
        <template #label>
          <required-label label="Correo electrónico:" />
        </template>
        <a-input
          v-model:value="formStateNegotiation.email"
          placeholder="Escribe"
          type="email"
          :disabled="!isEditable"
        />
      </a-form-item>
    </a-col>
  </a-row>
</template>
<script setup lang="ts">
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';
  import { useContactInformation } from '@/modules/negotiations/supplier/register/composables/useContactInformation';

  defineProps({
    isEditable: {
      type: Boolean,
      default: false,
    },
  });

  const { formStateNegotiation, formRules } = useContactInformation();
</script>
<style scoped lang="scss"></style>
