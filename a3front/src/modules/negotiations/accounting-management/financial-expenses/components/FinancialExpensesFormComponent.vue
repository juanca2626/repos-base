<template>
  <a-drawer
    v-model:open="formDrawer"
    title="Añadir Gasto Financiero"
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
      <a-form
        layout="vertical"
        :model="formState"
        class="mt-4"
        ref="formRefFinancialExpenses"
        :rules="rules"
      >
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="12">
            <a-form-item ref="type_amount" label="Tipo de importe" name="type_amount">
              <a-select v-model:value="formState.type_amount" name="type_amount">
                <a-select-option value="AMOUNT">Importe</a-select-option>
                <a-select-option value="PERCENTAGE">Porcentaje</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="12">
            <a-form-item ref="amount_value" label="Indique el monto" name="amount_value">
              <a-input-number v-model:value="formState.amount_value" style="width: 100%">
                <template #addonBefore>
                  <font-awesome-icon
                    :icon="['fas', 'dollar-sign']"
                    v-if="formState.type_amount === 'AMOUNT'"
                  />
                  <font-awesome-icon
                    :icon="['fas', 'percent']"
                    v-if="formState.type_amount === 'PERCENTAGE'"
                  />
                </template>
              </a-input-number>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="24">
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

<script lang="ts" setup>
  import { useFinancialExpensesForm } from '@/modules/negotiations/accounting-management/financial-expenses/composables/useFinancialExpensesForm';

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

  const {
    formDrawer,
    formRefFinancialExpenses,
    formState,
    rules,
    isLoading,
    handlerShowDrawer,
    saveForm,
  } = useFinancialExpensesForm(props, emit);

  const handleSubmit = async () => {
    try {
      await formRefFinancialExpenses.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };
</script>

<style scoped></style>
