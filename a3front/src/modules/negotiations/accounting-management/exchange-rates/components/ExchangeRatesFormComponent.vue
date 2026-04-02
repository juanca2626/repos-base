<template>
  <a-drawer
    v-model:open="formDrawer"
    title="Añadir tipo de cambio estimado"
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
        ref="formRefExchangeRates"
        :rules="rules"
      >
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="12">
            <a-form-item ref="currency_id" label="Moneda" name="currency_id">
              <a-select v-model:value="formState.currency_id" name="currency_id">
                <a-select-option
                  v-for="(item, index) in currencyList"
                  :key="index"
                  :value="item.id"
                >
                  {{ item.symbol }} - {{ item.iso }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="12">
            <a-form-item ref="exchange_rate" label="Tipo de cambio" name="exchange_rate">
              <a-input-number v-model:value="formState.exchange_rate" style="width: 100%" />
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
<script setup lang="ts">
  import { useExchangeRatesForm } from '@/modules/negotiations/accounting-management/exchange-rates/composables/useExchangeRatesForm';

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
    formRefExchangeRates,
    formState,
    rules,
    isLoading,
    currencyList,
    handlerShowDrawer,
    saveForm,
  } = useExchangeRatesForm(props, emit);

  const handleSubmit = async () => {
    try {
      await formRefExchangeRates.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };
</script>
<style scoped lang="scss"></style>
