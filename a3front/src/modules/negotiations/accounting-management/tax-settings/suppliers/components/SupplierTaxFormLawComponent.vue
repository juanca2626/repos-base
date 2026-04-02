<template>
  <a-drawer
    v-model:open="showDrawerLaw"
    title="Nuevo tipo de ley"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawerLaw"
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
      <a-form layout="vertical" :model="formState" class="mt-4" ref="formRefLaw" :rules="rules">
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="16">
            <a-form-item ref="name" label="Nombre del tipo de ley" name="name">
              <a-input v-model:value="formState.name" style="width: 100%"></a-input>
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="8">
            <a-form-item ref="percentage" label="Indica el porcentaje" name="percentage">
              <a-input-number v-model:value="formState.percentage" style="width: 100%" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="24">
            <a-form-item ref="date" label="Selecciona las fechas del periodo" name="date">
              <a-range-picker
                style="width: 100%"
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
          </a-col>
        </a-row>
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
<script setup lang="ts">
  import { useSupplierTaxFormLaw } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierTaxFormLaw';

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
    showDrawerLaw: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['handlerShowDrawer', 'updateFilters']);

  const { showDrawerLaw, formRefLaw, formState, rules, handlerShowDrawerLaw, saveForm, isLoading } =
    useSupplierTaxFormLaw(props, emit);

  const handleSubmit = async () => {
    try {
      await formRefLaw.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };
</script>
<style scoped lang="scss"></style>
