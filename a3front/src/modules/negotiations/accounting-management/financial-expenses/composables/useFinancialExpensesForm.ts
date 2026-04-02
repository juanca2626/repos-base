import { inject, reactive, ref, type UnwrapRef, watch } from 'vue';
import { on } from '@/modules/negotiations/api/eventBus';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import dayjs from 'dayjs';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { FormFinancialExpensesInterface } from '@/modules/negotiations/accounting-management/financial-expenses/interfaces/form-financial-expenses.interface';
import { financialExpensesStore } from '@/modules/negotiations/accounting-management/financial-expenses/store/financialExpenses.store';
import type { FinancialExpensesResponseInterface } from '@/modules/negotiations/accounting-management/financial-expenses/interfaces/financial-expenses-response.interface';

interface FinancialExpensesFormPropsInterface {
  modelValue: string | number;
  label: string;
  placeholder: string[];
  format: string;
  showDrawer: boolean;
}

type EmitType = (
  event: 'handlerShowDrawer' | 'updateFilters',
  ...args: (string | number)[]
) => void;

export const useFinancialExpensesForm = (
  props: FinancialExpensesFormPropsInterface,
  emit: EmitType
) => {
  const isLoading = inject('isLoading');
  const formRefFinancialExpenses = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormFinancialExpensesInterface> = reactive({
    id: null,
    date: [null, null],
    amount_value: 0,
    type_amount: 'AMOUNT',
    method: 'POST',
  });

  const formDrawer = ref<boolean>(props.showDrawer);

  const handlerShowDrawer = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.date = [null, null];
    formState.amount_value = 0;
    formState.type_amount = 'AMOUNT';
    emit('handlerShowDrawer', false);
    resetForm();
  };

  const resetForm = () => {
    formRefFinancialExpenses.value?.resetFields();
  };

  const validateAmount = (_: unknown, value: number) => {
    if (formState.type_amount === 'AMOUNT') {
      if (typeof value === 'number' && !isNaN(value)) {
        if (value >= 1) {
          return Promise.resolve();
        } else {
          return Promise.reject('El monto debe ser mayor a 0');
        }
      } else {
        return Promise.reject('El monto debe ser un número');
      }
    }
    if (formState.type_amount === 'PERCENTAGE') {
      if (typeof value === 'number' && !isNaN(value)) {
        if (value >= 1 && value <= 100) {
          return Promise.resolve();
        } else {
          return Promise.reject('El monto debe estar entre 1 y 100');
        }
      } else {
        return Promise.reject('El monto debe ser un número');
      }
    }
  };

  const rules: Record<string, Rule[]> = {
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
    type_amount: [
      {
        required: true,
        message: 'Debe seleccionar un tipo de monto',
        trigger: 'change',
      },
    ],
    amount_value: [
      {
        required: true,
        message: 'Debe ingresar un monto',
        trigger: 'change',
      },
      {
        validator: validateAmount,
        trigger: 'change',
      },
    ],
  };

  const saveForm = async () => {
    try {
      isLoading.value = true;
      console.log('paso la validaciones');
      const dateFrom = formState.date[0];
      const dateTo = formState.date[1];
      const store = financialExpensesStore();

      const data = {
        date_from: dateFrom ? dateFrom.format('YYYY-MM-DD') : '',
        date_to: dateTo ? dateTo.format('YYYY-MM-DD') : '',
        amount_value: formState.amount_value,
        type_amount: formState.type_amount,
      };

      if (formState.method === 'POST') {
        const { response } = await supportApi.post('financial-expenses/', data);
        handleSuccessResponse(response);
      } else {
        const { response } = await supportApi.put('financial-expenses/' + formState.id, data);
        handleSuccessResponse(response);
      }
      resetForm();
      store.updateFilters({ from: '', to: '' });
      formDrawer.value = false;
    } catch (error) {
      handleError(error);
      console.error('Error fetching IGV data:', error);
    } finally {
      isLoading.value = false; // Ocultar loader después de completar la solicitud
    }
  };

  // Añadir escucha para el evento
  on('editSettingIgv', (item: FinancialExpensesResponseInterface) => {
    const dateFromConvert = dayjs(item.date_from, 'DD/MM/YYYY');
    const dateToConvert = dayjs(item.date_to, 'DD/MM/YYYY');
    formState.date = [dateFromConvert, dateToConvert];
    formState.amount_value = parseFloat(String(item.amount_value));
    formState.type_amount = item.type_amount;
    formState.id = item.id;
    formState.method = 'PUT';
    formDrawer.value = true;
  });

  watch(
    () => props.showDrawer,
    (newVal) => {
      formDrawer.value = newVal;
    }
  );

  return {
    formDrawer,
    formRefFinancialExpenses,
    formState,
    rules,
    isLoading,
    handlerShowDrawer,
    saveForm,
  };
};
