import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useSupplierContactsService } from '@/modules/negotiations/supplier-new/service/supplier-contacts.service';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import type { FormInstance } from 'ant-design-vue';

export const useContactsStore = defineStore('contactsStore', () => {
  const initialFormData = ref<any>({
    id: null,
    supplierBranchOfficeId: null,
    departmentId: null,
    typeContactId: null,
    firstname: null,
    surname: null,
    email: null,
    phone: null,
  });
  const columns = ref<any>([
    {
      title: 'Cargo',
      dataIndex: 'department',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Nombre y apellidos',
      dataIndex: 'firstname',
      sorter: true,
      width: '20%',
    },
    {
      title: 'Tipo',
      dataIndex: 'typeContact',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Ciudad',
      dataIndex: 'supplierBranchOffice',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Correo',
      dataIndex: 'email',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Teléfono',
      dataIndex: 'phone',
      sorter: true,
      width: '10%',
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      width: '10%',
      align: 'center',
      fixed: 'right',
    },
  ]);
  const currentPage = ref<number>(1);
  const pageSize = ref<number>(10);
  const total = ref<number>(0);
  const sourceData = ref<any>([]);
  const loading = ref<boolean>(false);
  const loadingForm = ref<boolean>(false);
  const loadingButton = ref<boolean>(false);
  const disabledButton = ref<boolean>(false);
  const searchQuery = ref<string>('');
  const showForm = ref<boolean>(false);
  const formState = ref<any>({ ...initialFormData.value });
  const formRef = ref<FormInstance | null>(null);
  const typeContacts = ref<any>([]);
  const departments = ref<any>([]);
  const operationLocations = ref<any>([]);

  const {
    getSupplierContacts,
    deleteSupplierContact,
    createSupplierContacts,
    updateSupplierContact,
  } = useSupplierContactsService;

  const { fetchModuleContactResource, fetchOperationLocations } = useSupplierResourceService;

  return {
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
    createSupplierContacts,
    updateSupplierContact,
    getSupplierContacts,
    deleteSupplierContact,
    fetchModuleContactResource,
    fetchOperationLocations,
  };
});
