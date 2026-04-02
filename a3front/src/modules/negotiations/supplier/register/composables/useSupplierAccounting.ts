import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
import type { SupplierTaxConditionResponse } from '@/modules/negotiations/supplier/interfaces';

export function useSupplierAccounting() {
  const resource = 'supplierTaxCondition';

  const dataRegistrationNotify = {
    bodyTitle: '¡Registro de contabilidad realizado!',
    bodyMessage:
      'Se está notificando al departamento de Negociaciones que el registro y la configuración del proveedor han finalizado.',
  };

  const { getRouteSupplierId } = useSupplierForm();

  const { registrationNotifyModals } = useSupplierFormView();

  const {
    formStateAccounting,
    resetFormStateAccounting,
    setIsLoadingForm,
    isEditFormAccounting,
    setIsEditFormAccounting,
  } = useSupplierFormStoreFacade();

  const getRequestData = () => {
    return {
      tax_rates_id: formStateAccounting.taxRatesId,
      iva_options_id: formStateAccounting.ivaOptionsId,
      last_billing_date: formStateAccounting.lastBillingDate,
    };
  };

  const saveSupplierAccounting = async () => {
    const request = getRequestData();

    try {
      setIsLoadingForm(true);

      const supplierId = getRouteSupplierId();

      const { data } = await supplierApi.post(`${resource}/${supplierId}`, request);

      if (data.success) {
        if (isEditFormAccounting.value) {
          handleSuccessResponse(data);
        } else {
          registrationNotifyModals.accounting = true;
          resetFormStateAccounting();
        }

        loadSupplierTaxCondition();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save supplier tax condition:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const loadSupplierTaxCondition = async () => {
    try {
      setIsLoadingForm(true);

      const { data } = await supplierApi.get(`${resource}/${getRouteSupplierId()}`);

      const taxCondition: SupplierTaxConditionResponse = data.data?.tax_condition ?? null;

      setDataToForm(data.success, taxCondition);
    } catch (error: any) {
      handleError(error);
      console.error('Error show data supplier tax condition:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const setDataToForm = (success: boolean, taxCondition: SupplierTaxConditionResponse) => {
    if (success && taxCondition) {
      formStateAccounting.ivaOptionsId = taxCondition.iva_option.id;
      formStateAccounting.taxRatesId = taxCondition.tax_rate.id;
      formStateAccounting.lastBillingDate = taxCondition.last_billing_date;
      setIsEditFormAccounting(true);
    }
  };

  return {
    formStateAccounting,
    registrationNotifyModals,
    dataRegistrationNotify,
    saveSupplierAccounting,
    loadSupplierTaxCondition,
  };
}
