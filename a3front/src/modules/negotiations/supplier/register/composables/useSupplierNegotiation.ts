import { useRoute } from 'vue-router';
import type { Rule } from 'ant-design-vue/es/form';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { useSupplierDataTransformer } from '@/modules/negotiations/supplier/register/composables/useSupplierDataTransformer';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useOperationLocation } from '@/modules/negotiations/supplier/register/composables/useOperationLocation';
import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
import type { SupplierResponse } from '@/modules/negotiations/supplier/interfaces';

export function useSupplierNegotiation() {
  const resource = 'supplier';

  const route = useRoute();

  const { getRouteSupplierId } = useSupplierForm();
  const { registrationNotifyModals } = useSupplierFormView();
  const { validateUniqueOperationLocation } = useOperationLocation();

  const { setSupplierToForm, buildRequestPayload } = useSupplierDataTransformer();

  const {
    formStateNegotiation,
    resetFormData,
    setIsLoadingForm,
    extraValidations,
    resetExtraValidationByKey,
  } = useSupplierFormStoreFacade();

  const dataRegistrationNotify = {
    bodyTitle: '¡Registro del proveedor realizado!',
    bodyMessage:
      'Se está notificando a los departamentos de Contabilidad y Tesorería para continuar con el registro y configuración del proveedor.',
  };

  const executeExtraValidations = (): boolean => {
    let errors = 0;

    const validateUniqueLocation = validateUniqueOperationLocation();

    if (validateUniqueLocation.hasDuplicate) {
      extraValidations.errorUniqueLocationKey = {
        error: validateUniqueLocation.hasDuplicate,
        index: validateUniqueLocation.index,
      };

      errors++;
    }

    return errors > 0;
  };

  const saveSupplierNegotiation = async () => {
    const extraValidations = executeExtraValidations();
    if (extraValidations) return;

    resetExtraValidationByKey('errorUniqueLocationKey');

    const supplierId = getRouteSupplierId();
    const request = buildRequestPayload(supplierId);

    try {
      setIsLoadingForm(true);

      const response = supplierId
        ? await supplierApi.put(`${resource}/${supplierId}`, request)
        : await supplierApi.post(`${resource}`, request);

      const { data } = response;

      if (data.success) {
        resetFormData();

        if (supplierId) {
          await loadSupplierData();
          handleSuccessResponse(data);
        } else {
          registrationNotifyModals.negotiation = true;
        }
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save supplier:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const rulesNegotiation: Record<string, Rule[]> = {
    // add common rules
  };

  const getRouteSupplierClassificationId = () => {
    return route.params.idClassification;
  };

  const loadSupplierData = async () => {
    try {
      setIsLoadingForm(true);

      const { data } = await supplierApi.get(`${resource}/${getRouteSupplierId()}`);
      const supplier: SupplierResponse = data.data;
      setSupplierToForm(supplier);
    } catch (error: any) {
      handleError(error);
      console.error('Error show data supplier:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  return {
    formStateNegotiation,
    saveSupplierNegotiation,
    rulesNegotiation,
    dataRegistrationNotify,
    registrationNotifyModals,
    loadSupplierData,
    getRouteSupplierId,
    getRouteSupplierClassificationId,
  };
}
