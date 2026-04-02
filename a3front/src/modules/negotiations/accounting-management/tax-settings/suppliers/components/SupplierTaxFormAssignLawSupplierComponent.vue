<template>
  <a-drawer
    v-model:open="showDrawerLaw"
    title="Asignar ley a proveedor"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawerAssignLawSupplier"
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
        class="mt-4 filter-form module-negotiations"
        ref="formRefExchangeRates"
        :rules="rules"
      >
        <a-row :gutter="16">
          <a-col class="gutter-row" :span="24">
            <a-form-item ref="law_id" label="Seleccione una ley" name="lay_id">
              <a-select style="width: 100%" v-model:value="formState.law_id" name="law_id">
                <a-select-option
                  v-for="(item, index) in typeLawOptions"
                  :key="index"
                  :loading="isLoadingTypeLaws"
                  :value="item.id"
                >
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col class="gutter-row" :span="24">
            <a-form-item label="Clasificación de proveedor" name="service_classification_id">
              <a-select
                mode="multiple"
                class="select-multiple-negotiation"
                style="width: 100%"
                :loading="isLoading"
                v-model:value="formState.supplier_classifications"
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
                      :class="[
                        isSelected(value) ? 'icon-color-selected' : 'icon-color-not-selected',
                      ]"
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
  import { storeToRefs } from 'pinia';
  import { useSupplierTaxFormAssignLawSupplier } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierTaxFormAssignLawSupplier';
  import { useSupplierSubClassificationsStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierSubClassifications.store';
  import { useTypeLawsStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/typeLaws.store';
  import { computed, ref, onMounted } from 'vue';

  const maxTagCount = ref(4);

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

  const emit = defineEmits(['updateFilters']);

  const {
    showDrawerLaw,
    formRefLaw,
    formState,
    rules,
    isLoading,
    handlerShowDrawerAssignLawSupplier,
    saveForm,
  } = useSupplierTaxFormAssignLawSupplier(props, emit);

  const { supplierSubClassificationList } = storeToRefs(useSupplierSubClassificationsStore());
  const typeLawsStore = useTypeLawsStore();
  const { typeLawList, isLoading: isLoadingTypeLaws } = storeToRefs(useTypeLawsStore());

  const supplierSubClassificationOptions = computed(() =>
    supplierSubClassificationList.value.map((item) => ({
      value: item.id,
      label: item.name,
      name: item.name,
    }))
  );

  const typeLawOptions = computed(() =>
    typeLawList.value.map((item) => ({
      id: item.id,
      value: item.id,
      label: item.name,
      name: item.name,
    }))
  );

  const isSelected = (value: number) => {
    return formState.supplier_classifications.includes(value);
  };

  const handleSubmit = async () => {
    try {
      await formRefLaw.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  onMounted(() => {
    typeLawsStore.fetchTypeLaws();
  });
</script>
<style scoped lang="scss">
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
