import { inject, onMounted, reactive, type Ref, ref, type UnwrapRef, watch } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { taxSettingStore } from '@/modules/negotiations/accounting-management/tax-settings/general/store/taxSetting.store';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import type { Rule } from 'ant-design-vue/es/form';
import { on } from '@/modules/negotiations/api/eventBus';
import dayjs from 'dayjs';
import type { FormCostSaleAccountsInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/form-cost-sale-accounts.interface';
import { useSupportModuleResources } from '@/modules/negotiations/composables/useSupportModuleResources';
import type { CostSaleAccountsResponseInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/cost-sale-accounts-response.interface';

interface CostSaleAccountsFormPropsInterface {
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

export const useCostSaleAccountsForm = (
  props: CostSaleAccountsFormPropsInterface,
  emit: EmitType
) => {
  const isLoading = inject<Ref<boolean>>('isLoading', ref(false));
  const formRefExchangeRates = ref<FormInstance | null>(null);
  const formState: UnwrapRef<FormCostSaleAccountsInterface> = reactive({
    id: null,
    service_classification_id: null,
    date: [null, null],
    cost_account: '',
    sale_account: '',
    method: 'POST',
  });
  const { resources, fetchSupportResources } = useSupportModuleResources();

  const serviceClassificationList = ref<(string | number)[]>([]);
  const formDrawer = ref<boolean>(props.showDrawer);

  const handlerShowDrawer = () => {
    formState.method = 'POST';
    formState.id = null;
    formState.date = [null, null];
    formState.service_classification_id = null;
    formState.cost_account = '';
    formState.sale_account = '';
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
        service_classification_id: formState.service_classification_id,
        cost_account: formState.cost_account.toString(),
        sale_account: formState.sale_account.toString(),
      };

      if (formState.method === 'POST') {
        const { response } = await supportApi.post('cost-sale-accounts/', data);
        handleSuccessResponse(response);
      } else {
        const { response } = await supportApi.put('cost-sale-accounts/' + formState.id, data);
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
    service_classification_id: [
      {
        required: true,
        message: 'Debe seleccionar una clasificación',
        trigger: 'change',
      },
    ],
    cost_account: [
      {
        required: true,
        message: 'Debe ingresar la cuenta costo',
        trigger: 'change',
      },
    ],
    sale_account: [
      {
        required: true,
        message: 'Debe ingresar la cuenta venta',
        trigger: 'change',
      },
    ],
  };

  // Añadir escucha para el evento
  on('editSettingIgv', (item: CostSaleAccountsResponseInterface) => {
    const dateFromConvert = dayjs(item.date_from, 'DD/MM/YYYY');
    const dateToConvert = dayjs(item.date_to, 'DD/MM/YYYY');
    formState.date = [dateFromConvert, dateToConvert];
    formState.service_classification_id = item.service_classification_id;
    formState.cost_account = item.cost_account;
    formState.sale_account = item.sale_account;
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

  onMounted(() => {
    return fetchSupportResources('service_classifications')
      .then(() => {
        if (
          resources.value &&
          resources.value.success &&
          resources.value.data.service_classifications
        ) {
          serviceClassificationList.value = resources.value.data.service_classifications;
        }
      })
      .catch((err) => {
        console.error('Error fetching service classifications:', err);
      })
      .finally(() => {
        if (isLoading && typeof isLoading.value !== 'undefined') {
          isLoading.value = false;
        }
      });
  });

  return {
    formDrawer,
    formRefExchangeRates,
    formState,
    rules,
    isLoading,
    serviceClassificationList,
    handlerShowDrawer,
    saveForm,
  };
};
