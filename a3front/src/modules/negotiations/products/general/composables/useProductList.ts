import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  Product,
  ProductResponse,
} from '@/modules/negotiations/products/general/interfaces/list';
import { productService } from '@/modules/negotiations/products/general/services/productService';
import { useProductFilterStore } from '@/modules/negotiations/products/general/store/useProductFilterStore';
import { useDeleteConfirm } from '@/modules/negotiations/composables/useDeleteConfirm';
import { handleDeleteResponse } from '@/modules/negotiations/api/responseApi';

export function useProductList() {
  const { fetchProducts, destroyProduct } = productService;

  const { searchTerm } = storeToRefs(useProductFilterStore());

  const { showDeleteConfirm, contextHolder } = useDeleteConfirm();

  const router = useRouter();

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<Product[]>([]);

  const columns = [
    {
      title: 'Nombre del servicio',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Código del producto',
      dataIndex: 'code',
      key: 'code',
    },
    {
      title: 'Tipo de servicio',
      dataIndex: 'serviceTypeName',
      key: 'serviceTypeName',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      width: '10%',
      align: 'center',
    },
  ];

  const onChange = (page: number, perSize: number) => {
    fetchListData(page, perSize);
  };

  const fetchListData = async (page: number = 1, pageSize: number = 10) => {
    try {
      isLoading.value = true;
      data.value = [];

      const response = await fetchProducts({
        page,
        pageSize,
        searchTerm: searchTerm.value || undefined,
      });

      transformListData(response.data);

      pagination.value = {
        current: response.pagination.page,
        pageSize: response.pagination.limit,
        total: response.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching products list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: ProductResponse[]) => {
    data.value = responseData.map((row) => {
      return {
        id: row.id,
        code: row.code,
        name: row.name,
        serviceTypeName: row.serviceType.name,
      };
    });
  };

  const handleDestroy = (id: string) => {
    showDeleteConfirm({
      deleteRequest: () => destroyProduct(id),
      onSuccess: () => {
        handleDeleteResponse();
        fetchListData();
      },
    });
  };

  const handleEdit = (id: string) => {
    router.push({
      name: 'productEdit',
      params: { id },
    });
  };

  onMounted(() => {
    fetchListData();
  });

  watch(
    () => searchTerm.value,
    () => {
      fetchListData();
    }
  );

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    contextHolder,
    handleDestroy,
    handleEdit,
  };
}
