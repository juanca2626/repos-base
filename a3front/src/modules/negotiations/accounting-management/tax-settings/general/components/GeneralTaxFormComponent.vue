<template>
  <a-drawer
    v-model:open="formDrawer"
    title="Añadir Configuración de IGV"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawer"
  >
    <a-flex justify="center">
      <a-typography-title :level="5" :style="{ color: '#1284ED' }">
        <a-badge
          count="1"
          :number-style="{
            backgroundColor: '#1284ED',
          }"
        />
        Ingresar los siguientes datos:
      </a-typography-title>
    </a-flex>

    <a-flex gap="middle" vertical class="my-5">
      <a-form layout="vertical" :model="formState" class="mt-4" ref="formRef" :rules="rules">
        <a-form-item ref="date" label="Selecciona las fechas del periodo" name="date">
          <a-range-picker
            v-model:value="formState.date"
            :placeholder="placeholder"
            :format="format"
            :allowClear="false"
          >
            <template #dateRender="{ current }">
              <div class="ant-picker-cell-inner">
                {{ current.date() }}
              </div>
            </template>
          </a-range-picker>
        </a-form-item>
        <a-form-item ref="percentage" label="Indique el porcentage" name="percentage">
          <a-input-number v-model:value="formState.percentage" />
        </a-form-item>
      </a-form>
    </a-flex>
    <template #footer>
      <a-row>
        <a-col :span="24">
          <a-button type="primary" block @click="handleSubmit()" :disabled="isLoading">
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { useGeneralTaxForm } from '@/modules/negotiations/accounting-management/tax-settings/general/composables/useGeneralTaxForm';

  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    placeholder: {
      type: Array,
      default: () => ['DD/MM/YYYY', 'DD/MM/YYYY'],
    },
    format: {
      type: String,
      default: 'DD/MM/YYYY',
    },
    showDrawer: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['handlerShowDrawer', 'updateFilters']);

  const { formDrawer, formRef, formState, rules, handlerShowDrawer, saveForm, isLoading } =
    useGeneralTaxForm(props, emit);

  const handleSubmit = async () => {
    try {
      await formRef.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };
</script>

<style scoped></style>
