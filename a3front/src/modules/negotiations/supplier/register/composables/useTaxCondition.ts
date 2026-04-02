import type { Rule } from 'ant-design-vue/es/form';
import { onMounted, ref } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useTaxCondition() {
  const taxRates = ref<SelectOption[]>([]);
  const ivaOptions = ref<SelectOption[]>([]);

  const { formStateAccounting, isLoadingForm, setIsLoadingForm } = useSupplierFormStoreFacade();

  const loadTaxResources = async () => {
    setIsLoadingForm(true);

    try {
      const response = await supplierApi.get('supplier/resources', {
        params: { 'keys[]': ['taxRates', 'ivaOptions'] },
      });

      const responseData = response.data.data;
      taxRates.value = mapItemsToOptions(responseData.taxRates);
      ivaOptions.value = mapItemsToOptions(responseData.ivaOptions);
    } catch (error) {
      console.error('Error fetching tax resources:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const formRules: Record<string, Rule[]> = {
    ivaOptionsId: [
      { required: true, message: 'El campo Tipo de IGV / IVA es obligatorio', trigger: 'change' },
    ],
    taxRatesId: [
      { required: true, message: 'El campo IGV / IVA es obligatorio', trigger: 'change' },
    ],
    lastBillingDate: [
      {
        required: true,
        message: 'La Última fecha de facturación es obligatoria',
        trigger: 'change',
      },
    ],
  };

  onMounted(() => {
    loadTaxResources();
  });

  return {
    isLoadingForm,
    taxRates,
    ivaOptions,
    filterOption,
    formStateAccounting,
    formRules,
  };
}
