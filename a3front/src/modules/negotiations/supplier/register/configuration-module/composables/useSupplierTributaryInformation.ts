import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { onMounted, reactive, watch } from 'vue';
import { Form } from 'ant-design-vue';
import { storeToRefs } from 'pinia';
import { useNegotiationSupplierTributaryInformationStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useNegotiationSupplierTributaryInformationStore';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import useNotification from '@/quotes/composables/useNotification';

export function UseSupplierTributaryInformation() {
  const useForm = Form.useForm;

  const { showErrorNotification, showSuccessNotification } = useNotification();

  const negotiationSupplierTributaryInformationStore =
    useNegotiationSupplierTributaryInformationStore();

  const { formStateTributaryInformation, formRules, isLoadingForm, showBannerAlert } = storeToRefs(
    negotiationSupplierTributaryInformationStore
  );

  const { resetFormTributaryInformation, setIsLoadingForm, setShowBannerAlert } =
    negotiationSupplierTributaryInformationStore;

  const { resetFields, validate, validateInfos } = useForm(
    formStateTributaryInformation,
    formRules
  );

  const { formStateNegotiation, configSubClassification } = useSupplierFormStoreFacade();

  const state = reactive<{
    typeTaxDocument: any[];
    cities: any[];
    isLoadingButton: boolean;
    isLoadingButtonObservation: boolean;
    idForm: number | undefined;
    showObservation: boolean;
    observationMessage: string;
  }>({
    typeTaxDocument: [],
    cities: [],
    isLoadingButton: false,
    isLoadingButtonObservation: false,
    idForm: undefined,
    showObservation: false,
    observationMessage: '',
  });

  const handleOk = (form = null) => {
    validate()
      .then(async (attributes) => {
        if (state.idForm) {
          await editApiTributaryInformation(state.idForm, attributes, form);
        } else {
          await storeApiTributaryInformation(attributes);
        }
      })
      .catch((err) => {
        console.warn('Form incomplete 😕', err);
      });
  };

  const handleCancel = () => {
    resetFormTributaryInformation();
    resetFields();
  };

  const fetchResourceTributaryInformationData = async () => {
    try {
      const response = await supplierApi.get('supplier/tributary-information/resources', {
        params: {
          'keys[]': ['typeTaxDocument', 'cities'],
        },
      });

      state.typeTaxDocument = response.data.data.typeTaxDocument;
      state.cities = response.data.data.cities;
    } catch (error) {
      console.error('Error fetching resource contact data:', error);
    }
  };

  const getSubClassificationSupplierId = () => {
    return formStateNegotiation.classifications.find(
      (item: any) => item.supplier_sub_classification_id === configSubClassification.value
    )?.operations?.[0]?.sub_classification_supplier_id;
  };

  const storeApiTributaryInformation = async (attributes: any) => {
    try {
      state.isLoadingButton = true;
      state.isLoadingButtonObservation = true;

      const observations = attributes.observations;
      attributes.sub_classification_supplier_id = getSubClassificationSupplierId();

      const { data } = await supplierApi.post(`supplier/tributary-information`, attributes);
      showSuccessNotification('La información SUNAT se ha creado satisfactoriamente.');

      if (state.showObservation && observations?.length > 0) {
        await supplierApi.put(`supplier/tributary-information/${data.data.id}/observation`, {
          observations,
        });
      }
    } catch (error) {
      showErrorNotification('Error al guardar la información SUNAT.');
      console.error('Error store tributary information data:', error);
    } finally {
      state.isLoadingButton = false;
      state.isLoadingButtonObservation = false;
    }
  };

  const showApiTributaryInformation = async (): Promise<any> => {
    try {
      setIsLoadingForm(true);
      const idClassifications: number = getSubClassificationSupplierId();

      const { data } = await supplierApi.post(`supplier/tributary-information-show`, {
        sub_classification_supplier_id: idClassifications,
      });

      const {
        sub_classification_supplier,
        city,
        types_tax_documents,
        id,
        has_observation,
        observations,
      } = data.data;

      state.idForm = id;

      if (sub_classification_supplier?.sub_classification_supplier_id) {
        Object.assign(formStateTributaryInformation.value, {
          sub_classification_supplier_id:
            sub_classification_supplier.sub_classification_supplier_id,
          city_id: city.city_id,
          types_tax_documents_id: types_tax_documents.types_tax_document_id,
          observations: observations?.observations,
        });

        setShowBannerAlert(has_observation);
        state.observationMessage = observations?.observations;
        state.showObservation = has_observation;

        setTimeout(() => {
          setIsLoadingForm(false);
        }, 500);
      } else {
        state.idForm = undefined;
        setIsLoadingForm(false);
      }
    } catch (error) {
      setIsLoadingForm(false);
      console.error('Error show tributary information data:', error);
    }
  };

  const editApiTributaryInformation = async (id: any, attributes: any, form = null) => {
    try {
      state.isLoadingButton = true;
      state.isLoadingButtonObservation = true;

      const observations = attributes.observations;
      if (observations?.length > 0) {
        delete attributes.observations;
      }

      await supplierApi.put(`supplier/tributary-information/${id}`, attributes);

      if (state.showObservation && observations?.length > 0) {
        await supplierApi.put(`supplier/tributary-information/${id}/observation`, { observations });
      }

      if (showBannerAlert.value && form === 'NEG') {
        await supplierApi.post(`supplier/tributary-information/${id}/disable-observation`);
        setShowBannerAlert(false);
      }

      showSuccessNotification('La información SUNAT se ha actualizado satisfactoriamente.');
    } catch (error) {
      showErrorNotification('Error al guardar la información SUNAT.');
      console.error('Error editing tributary information data:', error);
    } finally {
      state.isLoadingButton = false;
      state.isLoadingButtonObservation = false;
    }
  };

  const closeAlert = async () => {
    const id = state.idForm;
    await supplierApi.post(`supplier/tributary-information/${id}/disable-observation`);
    setShowBannerAlert(false);
  };

  const changeSubClassification = async (): Promise<void> => {
    try {
      setIsLoadingForm(true);
      const idClassifications = getSubClassificationSupplierId();

      const { data } = await supplierApi.post('supplier/tributary-information-show', {
        sub_classification_supplier_id: idClassifications,
      });

      const { sub_classification_supplier, city, types_tax_documents, id } = data.data;
      state.idForm = id;

      if (!sub_classification_supplier?.sub_classification_supplier_id) {
        state.idForm = undefined;
        Object.assign(formStateTributaryInformation.value, {
          sub_classification_supplier_id: undefined,
          city_id: undefined,
          types_tax_documents_id: undefined,
        });
        return;
      }

      Object.assign(formStateTributaryInformation.value, {
        sub_classification_supplier_id: sub_classification_supplier.sub_classification_supplier_id,
        city_id: city.city_id,
        types_tax_documents_id: types_tax_documents.types_tax_document_id,
      });

      setTimeout(() => {
        setIsLoadingForm(false);
      }, 500);
    } catch (error) {
      console.error('Error change sub classification data:', error);
      setIsLoadingForm(false);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const showObservation = () => {
    state.showObservation = !state.showObservation;
  };

  onMounted(async () => {
    await Promise.all([fetchResourceTributaryInformationData(), showApiTributaryInformation()]);
  });

  watch(
    configSubClassification,
    async (newSubClassification) => {
      if (newSubClassification !== null) {
        await changeSubClassification();
      }
    },
    { immediate: true }
  );

  return {
    state,
    handleOk,
    handleCancel,
    validateInfos,
    formStateTributaryInformation,
    isLoadingForm,
    showObservation,
    showBannerAlert,
    closeAlert,
  };
}
