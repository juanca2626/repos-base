import { inject, reactive, ref, type UnwrapRef, watch } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import type { responseLaw } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/response-law.interface';
import { filterSupplierTaxStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/filterSupplierTax.store';
import { on } from '@/modules/negotiations/api/eventBus';
import type { SupplierTaxResponseInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/supplier-tax-response.interface';

interface FormState {
  id?: number | null;
  law_id: number | null;
  supplier_classifications: [];
  method: string;
}

interface GeneralSupplierFormLawPropsInterface {
  modelValue: string | number;
  label: string;
  placeholder: string[];
  format: string;
  showDrawerLaw: boolean;
}

type EmitType = (
  event: 'handlerShowDrawerAssignLawSupplier' | 'updateFilters',
  ...args: (boolean | number)[]
) => void;

export const useSupplierTaxFormAssignLawSupplier = (
  props: GeneralSupplierFormLawPropsInterface,
  emit: EmitType
) => {
  const isLoading = inject('isLoading');
  const formRefLaw = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormState> = reactive({
    id: null,
    law_id: null,
    supplier_classifications: [],
    method: 'POST',
  });

  const showDrawerLaw = ref<boolean>(props.showDrawerLaw);

  const handlerShowDrawerAssignLawSupplier = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.law_id = null;
    formState.supplier_classifications = [];
    emit('handlerShowDrawerAssignLawSupplier', false);
    resetForm();
  };

  const resetForm = () => {
    formRefLaw.value?.resetFields();
  };

  const saveForm = async () => {
    isLoading.value = true;
    await formRefLaw.value?.validate();
    try {
      const store = filterSupplierTaxStore();
      const data = {
        type_law_id: formState.law_id,
        supplier_classifications: formState.supplier_classifications,
      };

      if (formState.method === 'POST') {
        const response: responseLaw = await supportApi.post('taxes-suppliers', data);
        handleSuccessResponse(response);
      } else {
        const response: responseLaw = await supportApi.put('taxes-suppliers/' + formState.id, data);
        handleSuccessResponse(response);
      }
      resetForm();
      store.updateFiltersSupplierTax({ supplier_sub_classification_id: [], from: '', to: '' });
      showDrawerLaw.value = false;
    } catch (error) {
      handleError(error);
      console.error('Error fetching IGV data:', error);
    } finally {
      isLoading.value = false; // Ocultar loader después de completar la solicitud
    }
  };

  const validatePercentage = (_: unknown, value: number) => {
    if (typeof value === 'number' && !isNaN(value)) {
      if (value >= 1 && value <= 100) {
        return Promise.resolve();
      } else {
        return Promise.reject('El porcentaje debe estar entre 1 y 100');
      }
    } else {
      return Promise.reject('El porcentaje debe ser un número');
    }
  };

  const rules: Record<string, Rule[]> = {
    name: [
      {
        required: true,
        message: 'Debe ingresar el nombre',
        trigger: 'change',
      },
    ],
    date: [
      {
        required: true,
        validator: (_: unknown, value: (string | number)[]) => {
          if (value && value.length === 2 && value[0] && value[1]) {
            return Promise.resolve();
          } else {
            return Promise.reject('Debe seleccionar ambas fechas');
          }
        },
        trigger: 'change',
      },
    ],
    percentage: [
      {
        required: true,
        message: 'Debe ingresar un porcentaje',
        trigger: 'change',
      },
      {
        validator: validatePercentage,
        trigger: 'change',
      },
    ],
  };

  on('editTaxSupplierAssignLaw', (item: SupplierTaxResponseInterface) => {
    formState.supplier_classifications = item.taxes_supplier_classifications.map((item) => {
      return item.supplier_sub_classification_id;
    });
    formState.law_id = item.law.id;
    formState.id = item.id;
    formState.method = 'PUT';
    showDrawerLaw.value = true;
  });

  watch(
    () => props.showDrawerLaw,
    (newVal) => {
      showDrawerLaw.value = newVal;
    }
  );

  return {
    showDrawerLaw,
    formRefLaw,
    formState,
    rules,
    isLoading,
    handlerShowDrawerAssignLawSupplier,
    saveForm,
  };
};
