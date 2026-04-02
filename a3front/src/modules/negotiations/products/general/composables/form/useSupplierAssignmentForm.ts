import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type {
  ProductSupplierBatchRequest,
  SupplierForm,
  SupplierFormBatchRequest,
  SupplierFormResponse,
  SupplierQueryParams,
  SupplierResponse,
} from '@/modules/negotiations/products/general/interfaces/form';
import { filterOption } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import { useSupplierAssignmentStoreFacade } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentStoreFacade';
import { useSupplierAssignmentFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentFormStoreFacade';
import { supplierAssignmentResourceService } from '@/modules/negotiations/products/general/services/supplierAssignmentResourceService';
import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';
import { useSupplierAssignmentFilterStore } from '@/modules/negotiations/products/general/store/useSupplierAssignmentFilterStore';
import { parseKeyOperationLocation } from '@/modules/negotiations/suppliers/helpers/operation-location-helper';
import { supplierService } from '@/modules/negotiations/products/general/services/supplierService';
import { productSupplierService } from '@/modules/negotiations/products/general/services/productSupplierService';
import { ConfigurationStatusEnum } from '@/modules/negotiations/products/general/enums/configuration-status.enum';
import { handleFieldMessageErrors } from '@/modules/negotiations/api/responseApi';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { MenuItemEnum } from '@/modules/negotiations/products/general/enums/menu-item.enum';
import { useProductFormSidebarStore } from '@/modules/negotiations/products/general/store/useProductFormSidebarStore';

export function useSupplierAssignmentForm() {
  const { setIsSupplierAssignmentForm } = useSupplierAssignmentStoreFacade();
  const { markMenuComplete } = useProductFormSidebarStore();

  const { productSupplierType } = useSelectedServiceType();

  const { fetchSuppliers } = supplierAssignmentResourceService;

  const { syncBatchProductSuppliers, fetchProductSuppliersByProduct } = productSupplierService;

  const { upsertSupplier, syncBatchSuppliers } = supplierService;

  const supplierAssignmentFilterStore = useSupplierAssignmentFilterStore();

  const { locationKey, searchTerm, supplierClassificationId } = storeToRefs(
    supplierAssignmentFilterStore
  );

  const { resetFilters } = supplierAssignmentFilterStore;

  const {
    allSuppliers,
    suppliersToAssign,
    assignedSuppliers,
    selectedSupplierKeys,
    assignSupplier,
    deleteAssignedSuppliers,
    assignMultipleSuppliers,
    splitSuppliersBySelection,
  } = useSupplierAssignmentFormStoreFacade();

  const { startLoading, stopLoading, productId, productCode } = useProductFormStoreFacade();

  const columns = [
    {
      title: 'Seleccionar todos',
      dataIndex: 'all',
      key: 'all',
    },
  ];

  const isLoadingMultipleSuppliers = ref<boolean>(false);

  const rowHeight = 56; // altura por fila
  const headerHeight = 56; // altura cabecera
  const maxHeight = 560; // altura máxima para scroll

  const toAssignTableHeight = computed(() => {
    const rows = suppliersToAssign.value.length;
    const totalHeight = headerHeight + rows * rowHeight;

    return totalHeight > maxHeight ? maxHeight : undefined;
  });

  const handleDeleteAssignedSuppliers = (supplier: SupplierForm) => {
    deleteAssignedSuppliers(supplier);
  };

  const handleSupplierSelectChange = (selectedRowKeys: number[]) => {
    selectedSupplierKeys.value = selectedRowKeys;
  };

  const handleAssignSupplier = async (supplier: SupplierForm) => {
    try {
      supplier.isLoading = true;

      const { success, data } = await upsertSupplier({
        originalId: supplier.supplierOriginalId,
        code: supplier.code,
        name: supplier.name,
        providerTypeCode: supplier.supplierClassification.code,
        providerTypeName: supplier.supplierClassification.name,
        country: supplier.countryName,
        state: supplier.stateName,
        city: supplier.cityName,
        status: supplier.status,
      });

      if (success) {
        supplier.supplierId = data.id;
        assignSupplier(supplier);
      }
    } catch (error) {
      console.error('Error handle assign supplier:', error);
    } finally {
      supplier.isLoading = false;
    }
  };

  const buildSupplierFormBatchRequest = (
    assignedItems: SupplierForm[]
  ): SupplierFormBatchRequest => {
    return {
      items: assignedItems.map((item) => {
        return {
          originalId: item.supplierOriginalId,
          code: item.code,
          name: item.name,
          providerTypeCode: item.supplierClassification.code,
          providerTypeName: item.supplierClassification.name,
          country: item.countryName,
          state: item.stateName,
          city: item.cityName,
          status: item.status,
        };
      }),
    };
  };

  const applyIdsToAssignedSuppliers = (assigned: SupplierForm[], data: SupplierFormResponse[]) => {
    const dataMap = new Map(data.map((item) => [item.originalId, item.id]));

    assigned.forEach((assignedItem) => {
      const apiId = dataMap.get(assignedItem.supplierOriginalId);
      if (apiId) assignedItem.supplierId = apiId;
    });
  };

  const handleAssignMultipleSuppliers = async () => {
    try {
      isLoadingMultipleSuppliers.value = true;

      const { toAssign, assigned } = splitSuppliersBySelection();

      const { success, data } = await syncBatchSuppliers(buildSupplierFormBatchRequest(assigned));

      if (success) {
        applyIdsToAssignedSuppliers(assigned, data);
        assignMultipleSuppliers(toAssign, assigned);
      }
    } catch (error) {
      console.error('Error handle assign multiple supplier:', error);
    } finally {
      isLoadingMultipleSuppliers.value = false;
    }
  };

  const buildQueryParams = (): SupplierQueryParams => {
    const location = locationKey.value
      ? parseKeyOperationLocation(locationKey.value, '-')
      : { countryId: undefined, stateId: undefined, cityId: undefined };

    return {
      searchTerm: searchTerm.value || undefined,
      classificationId: supplierClassificationId.value || undefined,
      ...location,
    };
  };

  const getSuppliers = async () => {
    try {
      startLoading();
      suppliersToAssign.value = [];

      const { data } = await fetchSuppliers(buildQueryParams());

      prepareSupplierData(data);
    } catch (error) {
      console.error('Error fetching suppliers data:', error);
    } finally {
      stopLoading();
    }
  };

  const prepareSupplierData = (data: SupplierResponse[]) => {
    allSuppliers.value = data.map((row) => {
      return {
        supplierId: null,
        supplierOriginalId: row.id,
        productSupplierId: null,
        code: row.code,
        name: row.business_name,
        status: row.status,
        supplierClassification: {
          code: row.supplier_classification.code,
          name: row.supplier_classification.name,
        },
        countryName: row.country.name,
        stateName: row.state.name,
        cityName: row.city?.name ?? null,
        isLoading: false,
        productSupplier: null,
      };
    });

    const assignedIds = new Set(assignedSuppliers.value.map((item) => item.supplierOriginalId));

    suppliersToAssign.value = allSuppliers.value.filter(
      (supplier) => !assignedIds.has(supplier.supplierOriginalId)
    );
  };

  const resetData = () => {
    allSuppliers.value = [];
    suppliersToAssign.value = [];
    assignedSuppliers.value = [];
  };

  const buildProductSupplierBatchRequest = (): ProductSupplierBatchRequest => {
    const productSuppliers = assignedSuppliers.value.map((item) => {
      const productSupplier = item.productSupplier;

      return {
        id: productSupplier?.id,
        supplierId: item.supplierId,
        supplierCode: item.code,
        progress: productSupplier?.progress ?? 0,
        configurationStatus: ConfigurationStatusEnum.PENDING,
        status: productSupplier?.status ?? true,
      };
    });

    return {
      type: productSupplierType.value,
      productId: productId.value!,
      productCode: productCode.value!,
      productSuppliers,
    };
  };

  const loadProductSuppliers = async () => {
    if (!productId.value) return;

    const { data } = await fetchProductSuppliersByProduct(productId.value);

    if (data.length === 0) return;

    setIsSupplierAssignmentForm(false);

    markMenuComplete(MenuItemEnum.SUPPLIERS);

    assignedSuppliers.value = data.map((item) => {
      const supplier = item.supplier;

      return {
        supplierId: supplier.id,
        supplierOriginalId: supplier.originalId,
        productSupplierId: item.id,
        code: supplier.code,
        name: supplier.name,
        status: supplier.status,
        supplierClassification: {
          code: supplier.providerTypeCode,
          name: supplier.providerTypeName,
        },
        countryName: supplier.country,
        stateName: supplier.state,
        cityName: supplier.city,
        isLoading: false,
        productSupplier: {
          type: item.type,
          id: item.id,
          progress: item.progress == 0 ? 100 : item.progress,
          status: item.status,
        },
      };
    });
  };

  const handleSave = async () => {
    try {
      startLoading();

      const { success } = await syncBatchProductSuppliers(buildProductSupplierBatchRequest());

      if (success) {
        loadProductSuppliers();
        setIsSupplierAssignmentForm(false);
      }
    } catch (error: any) {
      handleFieldMessageErrors(error);
      console.error('Error sync batch product suppliers:', error);
    } finally {
      stopLoading();
    }
  };

  const isSelectAllSuppliers = computed(() => {
    return (
      selectedSupplierKeys.value.length > 0 &&
      selectedSupplierKeys.value.length === suppliersToAssign.value.length
    );
  });

  const isAssignMultiple = computed(() => {
    return selectedSupplierKeys.value.length > 0;
  });

  watch(
    () => [locationKey.value, searchTerm.value, supplierClassificationId.value],
    () => {
      getSuppliers();
    }
  );

  onMounted(async () => {
    resetData();
    await loadProductSuppliers();
    await getSuppliers();
  });

  const exitForm = () => {
    setIsSupplierAssignmentForm(true);
    resetFilters();
  };

  onUnmounted(() => {
    exitForm();
  });

  return {
    columns,
    suppliersToAssign,
    assignedSuppliers,
    selectedSupplierKeys,
    isSelectAllSuppliers,
    toAssignTableHeight,
    isAssignMultiple,
    isLoadingMultipleSuppliers,
    filterOption,
    handleSupplierSelectChange,
    handleAssignSupplier,
    handleDeleteAssignedSuppliers,
    handleAssignMultipleSuppliers,
    handleSave,
  };
}
