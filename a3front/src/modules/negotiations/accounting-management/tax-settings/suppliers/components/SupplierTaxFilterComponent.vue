<template>
  <a-typography-text strong> Completa los siguientes campos para filtrar:</a-typography-text>
  <a-form layout="vertical" :model="formState" class="mt-4 filter-form">
    <a-row :gutter="16">
      <a-col :span="7">
        <a-space direction="vertical" style="width: 100%">
          <a-form-item label="Clasificación de proveedor" name="service_classification_id">
            <a-select
              mode="multiple"
              class="select-multiple-negotiation"
              style="width: 100%"
              :loading="isLoading"
              v-model:value="formState.supplier_sub_classification_id"
              :options="supplierSubClassificationOptions"
              name="supplier_sub_classification_id"
              :max-tag-count="maxTagCount"
            >
              <template #maxTagPlaceholder="omittedValues">
                <span>+ {{ omittedValues.length }} ...</span>
              </template>
              <template #option="{ value, name }">
                <div class="select-multiple-opt-negotiation">
                  <font-awesome-icon
                    :class="[isSelected(value) ? 'icon-color-selected' : 'icon-color-not-selected']"
                    :icon="[
                      isSelected(value) ? 'fas' : 'far',
                      isSelected(value) ? 'square-check' : 'square',
                    ]"
                    size="xl"
                  />
                  <span style="margin-left: 8px">{{ name }}</span>
                </div>
              </template>
              <template #tagRender="{ label, onClose }">
                <a-tag class="tag-selected-multiple" @close="onClose"> {{ label }}</a-tag>
              </template>
              <template #menuItemSelectedIcon />
            </a-select>
          </a-form-item>
        </a-space>
      </a-col>
      <a-col :span="5">
        <a-form-item label="Selecciona el rango de fechas">
          <a-space direction="vertical" :size="12">
            <a-range-picker
              v-model:value="date"
              :placeholder="placeholder"
              :format="format"
              :allowClear="false"
              @change="changeDate"
            >
              <template #dateRender="{ current }">
                <div class="ant-picker-cell-inner">
                  {{ current.date() }}
                </div>
              </template>
            </a-range-picker>
          </a-space>
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item>
          <a-flex justify="flex-end" align="middle" class="mt-4">
            <a-button
              type="link"
              :style="{ color: '#bd0d12' }"
              @click="cleanFilters()"
              :disabled="isLoading"
            >
              <svg
                class="colorPrimary_SVG"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 576 512"
                width="24"
                height="24"
              >
                <path
                  d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"
                />
              </svg>
              Limpiar filtros
            </a-button>
          </a-flex>
        </a-form-item>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { storeToRefs } from 'pinia';
  import { reactive, watch, defineEmits, ref, computed, onMounted } from 'vue';
  import { useSupplierSubClassificationsStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierSubClassifications.store';

  const emit = defineEmits(['updateFiltersSupplierTax']);
  const date = ref('');
  const maxTagCount = ref(3);
  const supplierSubClassificationsStore = useSupplierSubClassificationsStore();
  const { supplierSubClassificationList, isLoading } = storeToRefs(
    useSupplierSubClassificationsStore()
  );

  const supplierSubClassificationOptions = computed(() =>
    supplierSubClassificationList.value.map((item) => ({
      value: item.id,
      label: item.name,
      name: item.name,
    }))
  );

  defineProps({
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
      default: () => ['dd/mm/aa', 'dd/mm/aa'],
    },
    format: {
      type: String,
      default: 'DD/MM/YYYY',
    },
  });

  const formState = reactive({
    supplier_sub_classification_id: [] as number[],
    from: '',
    to: '',
  });

  const changeDate = () => {
    formState.from = date.value[0].toDate();
    formState.to = date.value[1].toDate();
  };

  const cleanFilters = () => {
    date.value = '';
    formState.from = '';
    formState.to = '';
    formState.supplier_sub_classification_id = [];
  };

  const isSelected = (value: number) => {
    return formState.supplier_sub_classification_id.includes(value);
  };

  onMounted(() => {
    supplierSubClassificationsStore.fetchServiceClassifications();
  });

  watch(
    () => ({ ...formState }),
    (newValues) => {
      emit('updateFiltersSupplierTax', newValues);
    },
    { deep: true }
  );
</script>

<style scoped lang="scss">
  .colorPrimary_SVG {
    fill: #bd0d12;
    margin-right: 5px;
  }

  .ant-select-item-option-selected:not(.ant-select-item-option-disabled) {
    background-color: #f9f9f9 !important;
  }

  .select-multiple-opt-negotiation {
    display: flex;
    align-items: center;
    gap: 8px;

    .icon-color-selected {
      color: #bd0d12;
    }

    .icon-color-not-selected {
      color: #bec0c2;
    }

    span {
      font-weight: 400;
      color: #2f353a;
    }
  }
</style>
