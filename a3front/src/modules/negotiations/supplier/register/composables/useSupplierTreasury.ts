import { useSupplierFormView } from '@/modules/negotiations/supplier/register/composables/useSupplierFormView';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
import type { SupplierPaymentTermResponse } from '@/modules/negotiations/supplier/interfaces';

export function useSupplierTreasury() {
  const resource = 'supplierPaymentTerm';

  const dataRegistrationNotify = {
    bodyTitle: '¡Registro de tesoreria realizado!',
    bodyMessage:
      'Se está notificando al departamento de Negociaciones que el registro y la configuración del proveedor han finalizado.',
  };

  const { getRouteSupplierId } = useSupplierForm();

  const { registrationNotifyModals } = useSupplierFormView();

  const {
    formStateTreasury,
    resetFormStateTreasury,
    setIsLoadingForm,
    isEditFormTreasury,
    setIsEditFormTreasury,
  } = useSupplierFormStoreFacade();

  const getRequestData = () => {
    return {
      credit_days: formStateTreasury.creditDays,
      credit_days_sunat: formStateTreasury.creditDaysSunat,
      sunat_start_date: formStateTreasury.startDateSunat,
    };
  };

  const saveSupplierTreasury = async () => {
    const request = getRequestData();

    try {
      setIsLoadingForm(true);

      const supplierId = getRouteSupplierId();

      const { data } = await supplierApi.post(`${resource}/${supplierId}`, request);

      if (data.success) {
        if (isEditFormTreasury.value) {
          handleSuccessResponse(data);
        } else {
          registrationNotifyModals.treasury = true;
          resetFormStateTreasury();
        }

        loadSupplierPaymentTerm();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save supplier payment term:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const loadSupplierPaymentTerm = async () => {
    try {
      setIsLoadingForm(true);

      const { data } = await supplierApi.get(`${resource}/${getRouteSupplierId()}`);

      const paymentTerm: SupplierPaymentTermResponse = data.data?.payment_Term ?? null;

      setDataToForm(data.success, paymentTerm);
    } catch (error: any) {
      handleError(error);
      console.error('Error show data supplier payment term:', error);
    } finally {
      setIsLoadingForm(false);
    }
  };

  const setDataToForm = (success: boolean, paymentTerm: SupplierPaymentTermResponse) => {
    if (success && paymentTerm) {
      formStateTreasury.creditDays = paymentTerm.credit_days;
      formStateTreasury.creditDaysSunat = paymentTerm.credit_days_sunat;
      formStateTreasury.startDateSunat = paymentTerm.sunat_start_date;
      setIsEditFormTreasury(true);
    }
  };

  return {
    formStateTreasury,
    dataRegistrationNotify,
    registrationNotifyModals,
    saveSupplierTreasury,
    loadSupplierPaymentTerm,
  };
}
