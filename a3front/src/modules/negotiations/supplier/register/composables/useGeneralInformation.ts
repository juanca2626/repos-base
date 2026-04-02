import { computed, reactive } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { parseKeyOperationLocation } from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { filterOption } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier/store/supplier-classification.store';

export function useGeneralInformation() {
  const suggestedCodeData = reactive({
    prefix: '',
    code: '',
  });

  const { getConditionalFormRules } = useSupplierFormView();

  const { formStateNegotiation, setIsLoadingForm, isFormEditMode } = useSupplierFormStoreFacade();

  const supplierClassificationStore = useSupplierClassificationStore();

  const supplierClassificationOptions = computed(
    () => supplierClassificationStore.supplierClassificationOptions
  );

  const isLoadingClassifications = computed(() => supplierClassificationStore.isLoading);

  // Función para cargar las opciones de clasificación del proveedor
  const loadSupplierClassifications = async () => {
    await supplierClassificationStore.fetchSupplierClassifications();
  };

  const getConfirmOptions = () => {
    return [
      {
        value: true,
        label: 'Si',
      },
      {
        value: false,
        label: 'No',
      },
    ];
  };

  const handleChangeSuggestedCode = async () => {
    if (formStateNegotiation.showSuggestedCodes) {
      await fetchSuggestedCode(false);
    } else {
      cleanSuggestedCodeData();
    }
  };

  const cleanSuggestedCodeData = () => {
    suggestedCodeData.code = '';
    suggestedCodeData.prefix = '';
  };

  const fetchSuggestedCode = async (automatic: boolean) => {
    try {
      setIsLoadingForm(true);

      const comercialLocation = parseKeyOperationLocation(
        formStateNegotiation.comercialLocation,
        '-'
      );

      const response = await supplierApi.post('supplier/generateCode', {
        business_name: formStateNegotiation.businessName,
        state_id: comercialLocation.stateId,
        automatic,
      });

      suggestedCodeData.code = response.data.data.code;
      suggestedCodeData.prefix = response.data.data.prefix;
    } catch (error: any) {
      handleError(error);
      formStateNegotiation.showSuggestedCodes = false;
    } finally {
      setIsLoadingForm(false);
    }
  };

  const handleReloadSuggestedCode = async () => {
    await fetchSuggestedCode(true);
  };

  const applySuggestedCode = async () => {
    if (suggestedCodeData) {
      formStateNegotiation.cityCode = suggestedCodeData.prefix;
      formStateNegotiation.supplierCode = suggestedCodeData.code;
    }
  };

  const baseRules: Record<string, Rule[]> = {
    cityCode: [
      { required: true, message: 'El código de ciudad es obligatorio', trigger: 'change' },
      {
        pattern: /^[a-zA-Z0-9]{3}$/,
        message: 'El código de ciudad debe tener 3 caracteres',
        trigger: 'change',
      },
    ],
    supplierCode: [
      { required: true, message: 'El código de proveedor es obligatorio', trigger: 'change' },
      {
        pattern: /^[a-zA-Z0-9]{3}$/,
        message: 'El código de proveedor debe tener 3 caracteres',
        trigger: 'change',
      },
    ],
    authorizedManagement: [
      {
        required: true,
        message: 'El campo autorizado por gerencia es obligatorio',
        trigger: 'change',
      },
    ],
    businessName: [
      { required: true, message: 'El nombre comercial es obligatorio', trigger: 'change' },
    ],
    name: [{ required: true, message: 'La razón social es obligatoria', trigger: 'change' }],
    supplierClassifications: [
      {
        required: true,
        message: 'La clasificación del proveedor es obligatoria',
        trigger: 'change',
      },
    ],
    belongsCompany: [
      { required: true, message: 'El campo pertenece a planta es obligatorio', trigger: 'change' },
    ],
    rucNumber: [
      { required: true, message: 'El número de RUC es obligatorio', trigger: 'change' },
      { pattern: /^\d{11}$/, message: 'El número de RUC debe tener 11 dígitos', trigger: 'change' },
    ],
    fiscalAddress: [
      { required: true, message: 'La dirección fiscal es obligatoria', trigger: 'change' },
    ],
    applyServicePercentage: [
      {
        required: true,
        message: 'El campo aplica % de servicio es obligatorio',
        trigger: 'change',
      },
    ],
    applyCommissionPercentage: [
      {
        required: true,
        message: 'El campo aplica % de comisión es obligatorio',
        trigger: 'change',
      },
    ],
    applyFinancialExpenses: [
      {
        required: true,
        message: 'El campo aplica % de gastos financieros es obligatorio',
        trigger: 'change',
      },
    ],
    serviceCharges: [
      {
        validator: (_: unknown, value: number) => {
          if (formStateNegotiation.applyServicePercentage && (value <= 0 || !value)) {
            return Promise.reject('El porcentaje de servicio es obligatorio y mayor a 0');
          }
          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
    commissionCharges: [
      {
        validator: (_: unknown, value: number) => {
          if (formStateNegotiation.applyCommissionPercentage && (value <= 0 || !value)) {
            return Promise.reject('El porcentaje de comisión es obligatorio y mayor a 0');
          }
          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
  };

  // Función para obtener las rules del form si estan en el tab negotiation (en otros tabs son de lectura)
  const formRules: Record<string, Rule[]> = getConditionalFormRules('negotiation', baseRules);

  return {
    supplierClassificationOptions,
    isLoadingClassifications,
    suggestedCodeData,
    formStateNegotiation,
    formRules,
    isFormEditMode,
    getConfirmOptions,
    handleChangeSuggestedCode,
    filterOption,
    handleReloadSuggestedCode,
    applySuggestedCode,
    loadSupplierClassifications,
  };
}
