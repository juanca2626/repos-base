import { inject, onMounted, reactive, ref, type UnwrapRef, watch } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { taxSettingStore } from '@/modules/negotiations/accounting-management/tax-settings/general/store/taxSetting.store';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import type { Rule } from 'ant-design-vue/es/form';
import { on } from '@/modules/negotiations/api/eventBus';
import dayjs from 'dayjs';
import type { FormExchangeRatesInterface } from '@/modules/negotiations/accounting-management/exchange-rates/interfaces/form-exchange-rates.interface';
import type { ExchangeRatesResponseInterface } from '@/modules/negotiations/accounting-management/exchange-rates/interfaces/exchange-rates-response.interface';
import { useCurrencyStore } from '@/modules/negotiations/store/currencyStore';

interface ExchangeRatesFormPropsInterface {
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

export const useExchangeRatesForm = (props: ExchangeRatesFormPropsInterface, emit: EmitType) => {
  const isLoading = inject('isLoading');
  const formRefExchangeRates = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormExchangeRatesInterface> = reactive({
    id: null,
    date: [null, null],
    exchange_rate: 0,
    currency_id: null,
    method: 'POST',
  });

  const currencyList = ref<never[]>([]);
  const formDrawer = ref<boolean>(props.showDrawer);
  const currencyStore = useCurrencyStore();

  const handlerShowDrawer = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.date = [null, null];
    formState.exchange_rate = 0;
    formState.currency_id = null;
    resetForm();
    emit('handlerShowDrawer', false);
  };

  const resetForm = () => {
    formRefExchangeRates.value?.resetFields();
  };

  const saveForm = async () => {
    await formRefExchangeRates.value?.validate();
    try {
      isLoading.value = true;
      const dateFrom = formState.date[0];
      const dateTo = formState.date[1];
      const store = taxSettingStore();
      const data = {
        date_from: dateFrom ? dateFrom.format('YYYY-MM-DD') : '',
        date_to: dateTo ? dateTo.format('YYYY-MM-DD') : '',
        exchange_rate: formState.exchange_rate,
        currency_id: formState.currency_id,
      };

      if (formState.method === 'POST') {
        const { response } = await supportApi.post('exchange-rates/', data);
        handleSuccessResponse(response);
      } else {
        const { response } = await supportApi.put('exchange-rates/' + formState.id, data);
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

  const validateExchangeRate = (_: unknown, value: number) => {
    if (typeof value == 'number' && !isNaN(value)) {
      if (value >= 1) {
        return Promise.resolve();
      } else {
        return Promise.reject('El tipo de cambio debe ser mayor a 1');
      }
    } else {
      return Promise.reject('El tipo de cambio debe ser un número');
    }
  };

  const rules: Record<string, Rule[]> = {
    date: [
      {
        required: true,
        validator: (_: unknown, value: Array<T>) => {
          if (value && value.length === 2 && value[0] && value[1]) {
            return Promise.resolve();
          } else {
            return Promise.reject('Debe seleccionar ambas fechas');
          }
        },
        trigger: 'change',
      },
    ],
    currency_id: [
      {
        required: true,
        message: 'Debe seleccionar una moneda',
        trigger: 'change',
      },
    ],
    exchange_rate: [
      {
        required: true,
        message: 'Debe ingresar el tipo de cambio',
        trigger: 'change',
      },
      {
        validator: validateExchangeRate,
        trigger: 'change',
      },
    ],
  };

  // Añadir escucha para el evento
  on('editSettingIgv', (item: ExchangeRatesResponseInterface) => {
    const dateFromConvert = dayjs(item.date_from, 'DD/MM/YYYY');
    const dateToConvert = dayjs(item.date_to, 'DD/MM/YYYY');
    formState.date = [dateFromConvert, dateToConvert];
    formState.exchange_rate = parseFloat(String(item.exchange_rate));
    formState.currency_id = item.currency_id;
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

  onMounted(async () => {
    try {
      await currencyStore.fetchCurrencies();
      console.log(currencyStore.currencies);
      currencyList.value = currencyStore.currencies;
    } catch (error) {
      console.error('Error fetching currencies:', error);
    }
  });

  return {
    formDrawer,
    formRefExchangeRates,
    formState,
    rules,
    isLoading,
    currencyList,
    handlerShowDrawer,
    saveForm,
  };
};
