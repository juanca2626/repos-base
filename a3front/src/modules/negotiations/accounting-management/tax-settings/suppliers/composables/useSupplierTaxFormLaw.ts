import { inject, reactive, ref, type UnwrapRef, watch } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { responseLaw } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/response-law.interface';

interface FormState {
  id?: number | null;
  name: string;
  date: [string | null, string | null];
  percentage: number;
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
  event: 'handlerShowDrawerLaw' | 'updateFilters',
  ...args: (boolean | number)[]
) => void;

export const useSupplierTaxFormLaw = (
  props: GeneralSupplierFormLawPropsInterface,
  emit: EmitType
) => {
  const isLoading = inject('isLoading');
  const formRefLaw = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormState> = reactive({
    id: null,
    name: '',
    date: [null, null],
    percentage: 0,
    method: 'POST',
  });

  const showDrawerLaw = ref<boolean>(props.showDrawerLaw);

  const handlerShowDrawerLaw = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.name = '';
    formState.date = [null, null];
    formState.percentage = 0;
    emit('handlerShowDrawerLaw', false);
    resetForm();
  };

  const resetForm = () => {
    formRefLaw.value?.resetFields();
  };

  const saveForm = async () => {
    isLoading.value = true;
    await formRefLaw.value?.validate();
    try {
      const dateFrom = formState.date[0];
      const dateTo = formState.date[1];
      const data = {
        name: formState.name,
        description: formState.name,
        date_from: dateFrom ? dateFrom.format('YYYY-MM-DD') : '',
        date_to: dateTo ? dateTo.format('YYYY-MM-DD') : '',
        igv_tax: formState.percentage,
      };

      if (formState.method === 'POST') {
        const response: responseLaw = await supportApi.post('type-laws/', data);
        handleSuccessResponse(response);
      } else {
        const response: responseLaw = await supportApi.put('type-laws/' + formState.id, data);
        handleSuccessResponse(response);
      }
      resetForm();
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
    handlerShowDrawerLaw,
    saveForm,
    isLoading,
  };
};
