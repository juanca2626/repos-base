import { defineStore } from 'pinia';
import { computed, reactive, ref } from 'vue';
import type {
  LocationOption,
  FormStateNegotiation,
  RequestSupplierLocationUpdate,
  FormStateTreasury,
  FormStateAccounting,
} from '@/modules/negotiations/supplier/interfaces';

export const useSupplierFormStore = defineStore('supplierFormStore', () => {
  const isLoadingForm = ref<boolean>(false);
  const isFormEditMode = ref<boolean>(false);
  const isEditFormTreasury = ref<boolean>(false);
  const isEditFormAccounting = ref<boolean>(false);
  const configSubClassification = ref<number | null>(null);

  const initialFormNegotiation: FormStateNegotiation = {
    classifications: null,
    cityCode: null,
    supplierCode: null,
    observations: '',
    showSuggestedCodes: false,
    authorizedManagement: false,
    businessName: null,
    name: null,
    supplierClassifications: [],
    rucNumber: null,
    belongsCompany: false,
    fiscalAddress: null,
    applyServicePercentage: false,
    serviceCharges: null,
    applyCommissionPercentage: false,
    commissionCharges: null,
    applyFinancialExpenses: false,
    phone: null,
    email: null,
    commercialAddress: null,
    comercialLocation: '',
    operationLocations: [
      {
        primaryLocationKey: null,
        zoneKey: null,
        locationOptionsByZone: [] as LocationOption[],
        supplierBranchOfficeIds: [], // usado para edición
      },
    ],
  };

  const formStateNegotiation = reactive<FormStateNegotiation>(
    structuredClone(initialFormNegotiation)
  );

  const initialFormTreasury: FormStateTreasury = {
    creditDays: null,
    creditDaysSunat: null,
    startDateSunat: null,
  };

  const formStateTreasury = reactive<FormStateTreasury>({ ...initialFormTreasury });

  const initialFormAccounting: FormStateAccounting = {
    ivaOptionsId: null,
    taxRatesId: null,
    lastBillingDate: null,
  };

  const formStateAccounting = reactive<FormStateAccounting>({ ...initialFormAccounting });

  const initialExtraValidations: Record<string, any> = {
    errorUniqueLocationKey: {
      error: false,
      index: -1,
    },
  };

  const extraValidations = reactive(structuredClone(initialExtraValidations));

  const supplierLocationsUpdate = ref<RequestSupplierLocationUpdate[]>([]);

  const setIsLoadingForm = (value: boolean) => {
    isLoadingForm.value = value;
  };

  const setIsFormEditMode = (value: boolean) => {
    isFormEditMode.value = value;
  };

  const setIsEditFormTreasury = (value: boolean) => {
    isEditFormTreasury.value = value;
  };

  const setIsEditFormAccounting = (value: boolean) => {
    isEditFormAccounting.value = value;
  };

  const resetFormStateNegotiation = () => {
    Object.assign(formStateNegotiation, structuredClone(initialFormNegotiation));
  };

  const resetFormStateTreasury = () => {
    Object.assign(formStateTreasury, { ...initialFormTreasury });
  };

  const resetFormStateAccounting = () => {
    Object.assign(formStateAccounting, { ...initialFormAccounting });
  };

  const resetExtraValidations = () => {
    Object.assign(extraValidations, structuredClone(initialExtraValidations));
  };

  const resetExtraValidationByKey = (key: string) => {
    if (key in initialExtraValidations) {
      extraValidations[key] = structuredClone(initialExtraValidations[key]);
    } else {
      console.warn(`La clave "${key}" no existe en initialExtraValidations`);
    }
  };

  const resetSupplierLocationsUpdate = () => {
    supplierLocationsUpdate.value = [];
  };

  const applySupplierLocationsUpdate = (
    index: number,
    updater: (location: RequestSupplierLocationUpdate) => void
  ) => {
    const { supplierBranchOfficeIds } = formStateNegotiation.operationLocations[index];

    supplierBranchOfficeIds.forEach((supplierBranchOfficeId) => {
      const location = supplierLocationsUpdate.value.find(
        (location) => location.supplier_branch_office_id === supplierBranchOfficeId
      );

      if (location) {
        updater(location);
      }
    });
  };

  const setConfigSubClassification = (value: number | null = null) => {
    configSubClassification.value = value;
  };

  const subClassificationSupplierId = computed((): number | null => {
    if (!Array.isArray(formStateNegotiation.classifications)) return null;

    const classification = formStateNegotiation.classifications.find(
      (item: any) => item.supplier_sub_classification_id === configSubClassification.value
    );

    // obtener sub_classification_supplier_id del operation
    return classification?.operations[0]?.sub_classification_supplier_id ?? null;
  });

  return {
    formStateNegotiation,
    formStateTreasury,
    formStateAccounting,
    isLoadingForm,
    isFormEditMode,
    extraValidations,
    supplierLocationsUpdate,
    isEditFormTreasury,
    isEditFormAccounting,
    configSubClassification,
    subClassificationSupplierId,
    setIsLoadingForm,
    resetFormStateNegotiation,
    resetFormStateTreasury,
    resetFormStateAccounting,
    setIsFormEditMode,
    resetExtraValidations,
    resetExtraValidationByKey,
    applySupplierLocationsUpdate,
    resetSupplierLocationsUpdate,
    setIsEditFormTreasury,
    setIsEditFormAccounting,
    setConfigSubClassification,
  };
});
