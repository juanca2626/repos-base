import { storeToRefs } from 'pinia';
import { onMounted, watch } from 'vue';
import debounce from 'lodash.debounce';
import { useContactsStore } from '@/modules/negotiations/supplier-new/store/negotiations/module-configuration/contacts.store';
import {
  handleCompleteResponse,
  handleError,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import type { Rule } from 'ant-design-vue/es/form';

export function useContactsComposable() {
  const { supplierId, subClassificationSupplierId } = useSupplierGlobalComposable();

  const contactsStore = useContactsStore();

  const {
    initialFormData,
    formState,
    formRef,
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    showForm,
    typeContacts,
    departments,
    operationLocations,
    loadingForm,
    loadingButton,
    disabledButton,
  } = storeToRefs(contactsStore);

  const {
    createSupplierContacts,
    updateSupplierContact,
    getSupplierContacts,
    deleteSupplierContact,
    fetchModuleContactResource,
    fetchOperationLocations,
  } = contactsStore;

  const formRules: Record<string, Rule[]> = {
    supplierBranchOfficeId: [
      { required: true, message: 'Debe seleccionar el lugar de operación', trigger: 'change' },
    ],
    departmentId: [{ required: true, message: 'Debe seleccionar el cargo', trigger: 'change' }],
    typeContactId: [{ required: true, message: 'Debe seleccionar el tipo', trigger: 'change' }],
    firstname: [{ required: true, message: 'Debe ingresar los nombres', trigger: 'change' }],
    surname: [
      { min: 2, message: 'El campo apellidos debe tener al menos 2 caracteres', trigger: 'change' },
    ],
    phone: [{ pattern: /^\d{9}$/, message: 'El teléfono debe tener 9 dígitos', trigger: 'change' }],
    email: [{ type: 'email', message: 'Ingresa un correo válido', trigger: 'change' }],
  };

  const fetchContactListData = async (
    page: number = 1,
    perPage: number = 10,
    searchQuery: string = ''
  ) => {
    loading.value = true;

    try {
      const { data, pagination } = await getSupplierContacts(supplierId.value, {
        page: page,
        per_page: perPage,
        subClassificationSupplierId: subClassificationSupplierId.value,
        ...(searchQuery ? { search: searchQuery } : {}),
      });

      sourceData.value = data.length > 0 ? data : [];
      currentPage.value = pagination.current_page;
      pageSize.value = pagination.per_page;
      total.value = pagination.total;
    } catch (error: any) {
      loading.value = false;
      console.log('⛔ Error al inicializar datos de contacts: ', error.message);
    } finally {
      loading.value = false;
    }
  };

  const fetchModuleContactResourceData = async () => {
    const { data } = await fetchModuleContactResource();
    typeContacts.value = data.typeContact;
    departments.value = data.departments;
  };

  const fetchOperationLocationsData = async () => {
    const { data } = await fetchOperationLocations(subClassificationSupplierId.value);
    operationLocations.value = data;
  };

  const filterOption = (input: string, option: any) => {
    return (
      option.label.toLowerCase().includes(input.toLowerCase()) ||
      option.value.toString().toLowerCase().includes(input.toLowerCase())
    );
  };

  const handleChangePagination = async (page: number, size: number): Promise<void> => {
    currentPage.value = page;
    pageSize.value = size;

    await fetchContactListData(page, size, searchQuery.value);
  };

  const handleDeleteContact = async (id: number): Promise<void> => {
    const { success, data } = await deleteSupplierContact(id);

    if (success) {
      await fetchContactListData();
      handleCompleteResponse(data, 'Contacto eliminada satisfactoriamente.');
    } else {
      handleErrorResponse();
    }
  };

  const handleShowFormContact = (state: boolean): void => {
    showForm.value = state;
  };

  const getRequestData = () => {
    return {
      supplier_branch_office_id: formState.value.supplierBranchOfficeId,
      department_id: formState.value.departmentId,
      type_contact_id: formState.value.typeContactId,
      firstname: formState.value.firstname,
      surname: formState.value.surname ?? null,
      email: formState.value.email ?? null,
      phone: formState.value.phone ?? null,
    };
  };

  const handleClose = () => {
    formState.value = { ...initialFormData.value };
    handleShowFormContact(false);
  };

  const handleEditContact = (record: any) => {
    const {
      id,
      firstname,
      surname = null,
      typeContact: { id: typeContactId },
      department: { id: departmentId },
      supplierBranchOffice: { id: supplierBranchOfficeId },
      email = null,
      phone = null,
    } = record;

    formState.value = {
      id: id,
      firstname,
      surname,
      typeContactId,
      departmentId,
      supplierBranchOfficeId,
      email,
      phone,
    };
    showForm.value = true;
  };

  const handleSaveForm = async () => {
    const request = getRequestData();
    loadingButton.value = true;
    try {
      const { data } = formState.value.id
        ? await updateSupplierContact(formState.value.id, request)
        : await createSupplierContacts(request);

      await fetchContactListData(currentPage.value, pageSize.value, searchQuery.value);
      handleCompleteResponse(data);

      handleShowFormContact(false);

      formState.value = { ...initialFormData.value };
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      loadingForm.value = false;
      loadingButton.value = false;
      disabledButton.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log('⛔ Validation error: ', error.message);
    }
  };

  const handleSearch = debounce(async ({ target }: any): Promise<void> => {
    searchQuery.value = target.value;
    await fetchContactListData(currentPage.value, pageSize.value, searchQuery.value);
  }, 300);

  onMounted(async () => {
    await Promise.all([
      fetchContactListData(),
      fetchModuleContactResourceData(),
      fetchOperationLocationsData(),
    ]);
  });

  watch(
    () => subClassificationSupplierId.value,
    async () => {
      await fetchOperationLocationsData();
    },
    { deep: true, immediate: true }
  );

  return {
    formRules,
    formState,
    formRef,
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    showForm,
    typeContacts,
    departments,
    operationLocations,
    loadingForm,
    loadingButton,
    disabledButton,
    filterOption,
    handleDeleteContact,
    handleChangePagination,
    handleSearch,
    handleShowFormContact,
    handleClose,
    handleSave,
    handleEditContact,
  };
}
