import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export const useNegotiationSupplierTributaryInformationStore = defineStore(
  'useNegotiationSupplierTributaryInformationStore',
  () => {
    const initialFormStateTributaryInformation: any = {
      sub_classification_supplier_id: undefined,
      city_id: undefined,
      types_tax_documents_id: undefined,
      observations: undefined,
    };

    const formStateTributaryInformation = reactive<any>(
      structuredClone(initialFormStateTributaryInformation)
    );

    const formRules = reactive({
      city_id: [{ required: true, message: 'Selecciona una ciudad', trigger: 'change' }],
      types_tax_documents_id: [
        { required: true, message: 'Selecciona un tipo de documento', trigger: 'change' },
      ],
      observations: [{ required: false, message: 'Agregar observaciones', trigger: 'blur' }],
    });

    const resetFormTributaryInformation = () => {
      Object.assign(
        formStateTributaryInformation,
        structuredClone(initialFormStateTributaryInformation)
      );
    };

    const isLoadingForm = ref<boolean>(false);
    const setIsLoadingForm = (value: boolean) => {
      isLoadingForm.value = value;
    };

    const showBannerAlert = ref<boolean>(false);
    const setShowBannerAlert = (value: boolean) => {
      showBannerAlert.value = value;
    };

    return {
      formStateTributaryInformation,
      formRules,
      resetFormTributaryInformation,
      isLoadingForm,
      setIsLoadingForm,
      showBannerAlert,
      setShowBannerAlert,
    };
  }
);
