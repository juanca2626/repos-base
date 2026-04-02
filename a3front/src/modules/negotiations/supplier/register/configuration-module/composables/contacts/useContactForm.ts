import { onMounted, reactive, ref } from 'vue';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { emit as emitBus, on } from '@/modules/negotiations/api/eventBus';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import type {
  ContactForm,
  Contact,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/supplier/register//helpers/supplierFormHelper';
import type {
  DrawerEmitTypeInterface,
  SelectOption,
} from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import { joinOptionalLocationNames } from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import type { OperationLocationResponse } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export const useContactForm = (emit: DrawerEmitTypeInterface) => {
  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const resource = 'supplier-contact';
  const isLoading = ref<boolean>(false);
  const formRefContact = ref<FormInstance | null>(null);

  const initFormData: ContactForm = {
    id: null,
    supplierBranchOfficeId: null,
    departmentId: null,
    typeContactId: null,
    firstname: null,
    surname: null,
    email: null,
    phone: null,
    method: 'POST',
  };

  const formState = reactive<ContactForm>({ ...initFormData });
  const typeContacts = ref<SelectOption[]>([]);
  const departments = ref<SelectOption[]>([]);
  const operationLocations = ref<SelectOption[]>([]);

  const fetchSupplierResources = async () => {
    try {
      isLoading.value = true;

      const response = await supplierApi.get('supplier/resources', {
        params: {
          'keys[]': ['typeContact', 'departments'],
        },
      });

      typeContacts.value = mapItemsToOptions(response.data.data.typeContact);
      departments.value = mapItemsToOptions(response.data.data.departments);
    } catch (error) {
      console.error('Error fetching resource data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const fetchOperationLocations = async () => {
    try {
      isLoading.value = true;
      const response = await supplierApi.get(
        `supplier/operation-locations/by-sub-classification/${subClassificationSupplierId.value}`
      );
      const data: OperationLocationResponse[] = response.data.data;

      operationLocations.value = data.map((item) => {
        return {
          value: item.supplier_branch_office_id.toString(),
          label: joinOptionalLocationNames(
            ', ',
            undefined,
            item.state.name,
            item.city?.name,
            item.zone?.name
          ),
        };
      });
    } catch (error) {
      console.error('Error fetching operation locations data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const initForm = (): void => {
    Object.assign(formState, { ...initFormData });
  };

  const resetForm = () => {
    formRefContact.value?.resetFields();
  };

  const getRequestData = () => {
    return {
      supplier_branch_office_id: formState.supplierBranchOfficeId,
      department_id: formState.departmentId,
      type_contact_id: formState.typeContactId,
      firstname: formState.firstname,
      surname: formState.surname ?? null,
      email: formState.email ?? null,
      phone: formState.phone ?? null,
    };
  };

  const handleClose = (): void => {
    resetForm();
    initForm();
    emit('update:showDrawerForm', false);
  };

  const saveForm = async () => {
    const request = getRequestData();

    try {
      isLoading.value = true;

      if (formState.method === 'POST') {
        const { data } = await supplierApi.post(`${resource}`, request);
        handleSuccessResponse(data);
      } else {
        const { data } = await supplierApi.put(`${resource}/${formState.id}`, request);
        handleSuccessResponse(data);
      }

      emitBus('reloadContactListData');
      resetForm();
      handleClose();
    } catch (error: any) {
      handleError(error);
      console.error('Error save contact:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const formRules: Record<string, Rule[]> = {
    supplierBranchOfficeId: [
      { required: true, message: 'Debe seleccionar el lugar de operación', trigger: 'change' },
    ],
    departmentId: [{ required: true, message: 'Debe seleccionar el cargo', trigger: 'change' }],
    typeContactId: [{ required: true, message: 'Debe seleccionar el tipo', trigger: 'change' }],
    firstname: [{ required: true, message: 'Debe ingresar los nombres', trigger: 'change' }],
    surname: [
      { min: 2, message: 'El campo apellidos debe tener al menos 2 caracteres', trigger: 'change' },
      {
        pattern: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s-]+$/,
        message: 'Solo se permiten letras, espacios y guiones',
        trigger: 'change',
      },
    ],
    phone: [{ pattern: /^\d{9}$/, message: 'El teléfono debe tener 9 dígitos', trigger: 'change' }],
    email: [{ type: 'email', message: 'Ingresa un correo válido', trigger: 'change' }],
  };

  const handleSubmit = async () => {
    try {
      await formRefContact.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  on('editContact', (item: Contact) => {
    setFormStateToUpdate(item);
  });

  const setFormStateToUpdate = async (item: Contact) => {
    formState.id = item.id;
    formState.supplierBranchOfficeId = item.supplierBranchOfficeId.toString();
    formState.departmentId = item.departmentId;
    formState.typeContactId = item.typeContactId;
    formState.firstname = item.firstname;
    formState.surname = item.surname;
    formState.email = item.email;
    formState.phone = item.phone;
    formState.method = 'PUT';
    emit('update:showDrawerForm', true);
  };

  onMounted(async () => {
    await Promise.all([fetchSupplierResources(), fetchOperationLocations()]);

    initForm();
  });

  return {
    formRefContact,
    formState,
    isLoading,
    typeContacts,
    departments,
    formRules,
    operationLocations,
    filterOption,
    saveForm,
    handleClose,
    handleSubmit,
  };
};
