import { onMounted, onUnmounted, ref, watch, h } from 'vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { storeToRefs } from 'pinia';
import { on, off, emit } from '@/modules/negotiations/api/eventBus';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  TypeUnit,
  TypeUnitResponse,
} from '@/modules/negotiations/type-unit-configurator/type-units/interfaces';
import { useTypeUnitFilterStore } from '@/modules/negotiations/type-unit-configurator/type-units/store/typeUnitFilterStore';
import { useTypeUnitUpdate } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitUpdate';
import { handleDeleteResponse, handleError } from '@/modules/negotiations/api/responseApi';

export function useTypeUnitList() {
  const resource = 'units';

  const [modal, contextHolder] = Modal.useModal();
  const cancelDialog = ref({ disabled: false });

  const isLoading = ref<boolean>(false);

  const { update } = useTypeUnitUpdate(isLoading);

  const typeUnitFilterStore = useTypeUnitFilterStore();

  const { codeOrName, status } = storeToRefs(typeUnitFilterStore);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<TypeUnit[]>([]);

  const columns = [
    {
      title: 'ESTADO',
      dataIndex: 'statusDescription',
      key: 'statusDescription',
      align: 'center',
    },
    {
      title: 'CODIGO',
      dataIndex: 'code',
      key: 'code',
      align: 'center',
    },
    {
      title: 'TIPO DE UNIDAD',
      dataIndex: 'typeUnitName',
      key: 'typeUnitName',
      align: 'center',
    },
    {
      title: 'MALETERO',
      dataIndex: 'isTrunk',
      key: 'isTrunk',
      align: 'center',
    },
    {
      title: '',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const onChange = (page: number, perSize: number) => {
    fetchListData(page, perSize);
  };

  const fetchListData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;

    try {
      const response = await supportApi.get<ApiListResponse<TypeUnitResponse[]>>(resource, {
        params: {
          perPage: pageSize,
          page,
          name: codeOrName.value,
          status: status.value,
        },
      });

      transformListData(response.data.data);

      pagination.value = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching unit type list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: TypeUnitResponse[]) => {
    data.value = responseData.map((row) => {
      return {
        id: row.id,
        code: row.code,
        typeUnitName: row.name,
        isTrunk: Boolean(row.is_trunk),
        status: Boolean(row.status),
        statusDescription: row.status ? 'Activo' : 'Inactivo',
      };
    });
  };

  const handleChangeIsTrunk = async (record: TypeUnit) => {
    const success = await update({
      id: record.id,
      name: record.typeUnitName,
      code: record.code,
      status: record.status,
      isTrunk: record.isTrunk,
    });

    if (success) {
      fetchListData();
    }
  };

  const handleEdit = (item: TypeUnit) => {
    emit('editTypeUnit', item);
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
          const { data } = await supportApi.post(`${resource}/delete-multiple`, {
            ids: [id],
          });
          handleDeleteResponse(data);
          fetchListData();
        } catch (error: any) {
          handleError(error);
          console.error('Error delete type unit', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  watch(
    () => [codeOrName, status],
    () => {
      fetchListData();
    },
    { deep: true }
  );

  const setupEventListeners = () => {
    on('reloadTypeUnitList', fetchListData);
  };

  const cleanupEventListeners = () => {
    off('reloadTypeUnitList', fetchListData);
  };

  onMounted(() => {
    setupEventListeners();
    fetchListData();
  });

  onUnmounted(() => {
    cleanupEventListeners();
  });

  return {
    isLoading,
    data,
    columns,
    pagination,
    contextHolder,
    onChange,
    handleChangeIsTrunk,
    handleEdit,
    handleDestroy,
  };
}
