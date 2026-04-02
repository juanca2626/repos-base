import { onMounted, reactive, ref } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SupplierVehicleDriver } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { emit as emitBus, on } from '@/modules/negotiations/api/eventBus';
import type { TransportDriverForm } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useTransportDriverForm = (emit: DrawerEmitTypeInterface) => {
  const resource = 'supplier-vehicle-drivers';
  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const isLoading = ref<boolean>(false);
  const formRefTransportDriver = ref<FormInstance | null>(null);

  const initFormData: TransportDriverForm = {
    id: null,
    subClassificationSupplierId: null,
    name: null,
    surnames: null,
    phone: null,
    method: 'POST',
  };

  const formState = reactive<TransportDriverForm>({ ...initFormData });

  const initForm = (): void => {
    Object.assign(formState, { ...initFormData });
  };

  const resetForm = () => {
    formRefTransportDriver.value?.resetFields();
  };

  const buildRequest = () => {
    return {
      sub_classification_supplier_id: subClassificationSupplierId.value,
      name: formState.name,
      surnames: formState.surnames,
      phone: formState.phone,
    };
  };

  const handleClose = (): void => {
    resetForm();
    initForm();
    emit('update:showDrawerForm', false);
  };

  on('editTransportDriver', (item: SupplierVehicleDriver) => {
    setFormStateToUpdate(item);
  });

  const setFormStateToUpdate = async (item: SupplierVehicleDriver) => {
    formState.id = item.id;
    formState.subClassificationSupplierId = item.subClassificationSupplierId;
    formState.name = item.name;
    formState.surnames = item.surnames;
    formState.phone = item.phone;
    formState.method = 'PUT';
    emit('update:showDrawerForm', true);
  };

  const getHttpSaveConfig = () => {
    const isCreating = formState.method === 'POST';

    return {
      endpoint: isCreating ? resource : `${resource}/${formState.id}`,
      httpRequest: isCreating ? technicalSheetApi.post : technicalSheetApi.put,
    };
  };

  const saveForm = async () => {
    const request = buildRequest();

    try {
      isLoading.value = true;

      const { endpoint, httpRequest } = getHttpSaveConfig();
      const { data } = await httpRequest(endpoint, request);

      if (data.success) {
        handleSuccessResponse(data);
        emitBus('reloadTransportDriverList');
        resetForm();
        handleClose();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save supplier vehicle driver:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const formRules: Record<string, Rule[]> = {
    name: [{ required: true, message: 'Debe ingresar los nombres', trigger: 'change' }],
    surnames: [{ required: true, message: 'Debe ingresar los apellidos', trigger: 'change' }],
    phone: [
      {
        pattern: /^\d{9}$/,
        message: 'El número de celular debe tener 9 dígitos',
        trigger: 'change',
      },
    ],
  };

  const handleSubmit = async () => {
    try {
      await formRefTransportDriver.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  onMounted(async () => {
    initForm();
  });

  return {
    formRefTransportDriver,
    formState,
    isLoading,
    formRules,
    saveForm,
    handleClose,
    handleSubmit,
  };
};
