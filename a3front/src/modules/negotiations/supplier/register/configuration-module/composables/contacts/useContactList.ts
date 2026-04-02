import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { onMounted, onUnmounted, ref, h } from 'vue';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  Contact,
  ContactResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';
import { joinOptionalLocationNames } from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { off, on, emit } from '@/modules/negotiations/api/eventBus';
import { handleDeleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useContactList() {
  const [modal, contextHolder] = Modal.useModal();
  const cancelDialog = ref({ disabled: false });

  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const { getRouteSupplierId } = useSupplierForm();

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<Contact[]>([]);

  const columns = [
    {
      title: 'Cargo',
      dataIndex: 'position',
      key: 'position',
    },
    {
      title: 'Nombre y apellido',
      dataIndex: 'fullName',
      key: 'fullName',
    },
    {
      title: 'Tipo',
      dataIndex: 'type',
      key: 'type',
    },
    {
      title: 'Lugar de operación',
      dataIndex: 'operationLocationName',
      key: 'operationLocationName',
    },
    {
      title: 'Correo',
      dataIndex: 'email',
      key: 'email',
    },
    {
      title: 'Teléfono',
      dataIndex: 'phone',
      key: 'phone',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const handleEdit = (item: Contact) => {
    emit('editContact', item);
  };

  const onChange = (page: number, perSize: number) => {
    fetchContactListData(page, perSize);
  };

  const fetchContactListData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;

    try {
      const { data } = await supplierApi.get(`supplier-contacts/${getRouteSupplierId()}`, {
        params: {
          perPage: pageSize,
          page,
          subClassificationSupplierId: subClassificationSupplierId.value,
        },
      });

      transformListData(data.data);

      pagination.value = {
        current: data.pagination.current_page,
        pageSize: data.pagination.per_page,
        total: data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching contact list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleDestroy = (id: number) => {
    modal.confirm({
      title: '¿Quieres eliminar el registro?',
      icon: h(ExclamationCircleOutlined),
      content: 'Al hacer clic en el botón Eliminar, se eliminará el registro',
      okText: 'Eliminar',
      cancelText: 'Cancelar',
      okType: 'primary',
      keyboard: false,
      cancelButtonProps: cancelDialog.value,
      async onOk() {
        try {
          cancelDialog.value.disabled = true;
          const { data } = await supplierApi.delete(`supplier-contact/${id}`);
          handleDeleteResponse(data);
          fetchContactListData();
        } catch (error: any) {
          handleError(error);
          console.error('Error delete contact', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  const transformListData = (responseData: ContactResponse[]) => {
    data.value = responseData.map((row: ContactResponse) => {
      const supplierBranchOffice = row.supplierBranchOffice;
      const locationName = joinOptionalLocationNames(
        ', ',
        undefined,
        supplierBranchOffice.state.name,
        supplierBranchOffice.city?.name,
        supplierBranchOffice.zone?.name
      );

      return {
        id: row.id,
        position: row.department.name,
        fullName: [row.firstname, row.surname].filter(Boolean).join(' '),
        type: row.typeContact.name,
        operationLocationName: locationName,
        email: row.email,
        phone: row.phone,
        supplierBranchOfficeId: row.supplierBranchOffice.id,
        firstname: row.firstname,
        surname: row.surname,
        departmentId: row.department.id,
        typeContactId: row.typeContact.id,
      };
    });
  };

  const setupEventListeners = () => {
    on('reloadContactListData', fetchContactListData);
  };

  const cleanupEventListeners = () => {
    off('reloadContactListData', fetchContactListData);
  };

  onMounted(() => {
    setupEventListeners();
    fetchContactListData();
  });

  onUnmounted(() => {
    cleanupEventListeners();
  });

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    handleEdit,
    contextHolder,
    handleDestroy,
  };
}
