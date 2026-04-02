import { inject, reactive, ref, type UnwrapRef, watch } from 'vue';
import { on } from '@/modules/negotiations/api/eventBus';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import dayjs from 'dayjs';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { handleSuccessResponse, handleError } from '@/modules/negotiations/api/responseApi';
import type { IgvRecordInterface } from '@/modules/negotiations/accounting-management/tax-settings/general/interfaces/igv-record.interface';
import { taxSettingStore } from '@/modules/negotiations/accounting-management/tax-settings/general/store/taxSetting.store';

interface FormState {
  id?: number | null;
  date: [string | null, string | null];
  percentage: number;
  method: string;
}

interface GeneralTaxFormPropsInterface {
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

export const useGeneralTaxForm = (props: GeneralTaxFormPropsInterface, emit: EmitType) => {
  const isLoading = inject('isLoading');
  const formRef = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormState> = reactive({
    id: null,
    date: [null, null],
    percentage: 0,
    method: 'POST',
  });

  const formDrawer = ref<boolean>(props.showDrawer);

  const handlerShowDrawer = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.date = [null, null];
    formState.percentage = 0;
    emit('handlerShowDrawer', false);
    resetForm();
  };

  const resetForm = () => {
    formRef.value?.resetFields();
  };

  const saveForm = async () => {
    isLoading.value = true;
    await formRef.value?.validate();
    try {
      const dateFrom = formState.date[0];
      const dateTo = formState.date[1];
      const store = taxSettingStore();
      const data = {
        date_from: dateFrom ? dateFrom.format('YYYY-MM-DD') : '',
        date_to: dateTo ? dateTo.format('YYYY-MM-DD') : '',
        igv_tax: formState.percentage,
      };

      if (formState.method === 'POST') {
        const { response } = await supportApi.post('general-taxes/', data);
        handleSuccessResponse(response);
      } else {
        const { response } = await supportApi.put('general-taxes/' + formState.id, data);
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

  // Añadir escucha para el evento
  on('editSettingIgv', (item: IgvRecordInterface) => {
    const dateFromConvert = dayjs(item.date_from, 'DD/MM/YYYY');
    const dateToConvert = dayjs(item.date_to, 'DD/MM/YYYY');
    formState.date = [dateFromConvert, dateToConvert];
    formState.percentage = parseFloat(String(item.igv_tax));
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
    formRef,
    formState,
    rules,
    handlerShowDrawer,
    saveForm,
    isLoading,
  };
};
