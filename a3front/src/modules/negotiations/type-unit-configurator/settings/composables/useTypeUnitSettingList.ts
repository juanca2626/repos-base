import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { on, off } from '@/modules/negotiations/api/eventBus';
import { useTypeUnitSettingFilterStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingFilterStore';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleDeleteResponse } from '@/modules/negotiations/api/responseApi';
import { useTypeUnitSettingResourceStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingResourceStore';
import { useTypeUnitSettingStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingStore';
import { useTypeUnitSettingListStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingListStore';
import { useDeleteConfirm } from '@/modules/negotiations/composables/useDeleteConfirm';

export function useTypeUnitSettingList() {
  const resource = 'unit-settings/transfers';
  const isLoading = ref<boolean>(false);

  const { showDeleteConfirm, contextHolder } = useDeleteConfirm();

  const { typeUnitTransportLocationId, periodYear, transferId } = storeToRefs(
    useTypeUnitSettingFilterStore()
  );

  const { typeUnitTransportLocations } = storeToRefs(useTypeUnitSettingResourceStore());

  const { setShowActionButtons } = useTypeUnitSettingStore();

  const typeUnitSettingListStore = useTypeUnitSettingListStore();
  const { fetchTransferData } = typeUnitSettingListStore;
  const { transferData, isLoading: isLoadingList } = storeToRefs(typeUnitSettingListStore);

  const resetFields = () => {
    setShowActionButtons(false);
  };

  const isLoadingMain = computed(() => isLoading.value || isLoadingList.value);

  const columns = [
    {
      title: 'Tipo de unidad',
      dataIndex: 'typeUnit',
      key: 'typeUnit',
      align: 'center',
    },
    {
      title: 'Capacidad Mínima',
      dataIndex: 'minimumCapacity',
      key: 'minimumCapacity',
      align: 'center',
    },
    {
      title: 'Capacidad Máxima',
      dataIndex: 'maximumCapacity',
      key: 'maximumCapacity',
      align: 'center',
    },
    {
      title: 'Cantidad Unidades',
      dataIndex: 'quantityUnitsRequired',
      key: 'quantityUnitsRequired',
      align: 'center',
    },
    {
      title: 'Representante de la Unidad',
      dataIndex: 'representativeQuantity',
      key: 'representativeQuantity',
      align: 'center',
    },
    {
      title: 'Maletero',
      dataIndex: 'trunkCarQuantity',
      key: 'trunkCarQuantity',
      align: 'center',
    },
    {
      title: 'Representante del maletero',
      dataIndex: 'trunkRepresentativeQuantity',
      key: 'trunkRepresentativeQuantity',
      align: 'center',
    },
    {
      title: 'Guía',
      dataIndex: 'quantityGuides',
      key: 'quantityGuides',
      align: 'center',
    },
  ];

  const isValidFilters = () => {
    return [transferId.value, typeUnitTransportLocationId.value, periodYear.value].every(Boolean);
  };

  const fetchData = async () => {
    if (!isValidFilters()) return;

    resetFields();

    await fetchTransferData(
      periodYear.value,
      typeUnitTransportLocationId.value!,
      transferId.value!
    );

    if (transferData.value.settingDetails.length > 0) {
      setShowActionButtons(true);
    }
  };

  const existsLocations = computed(() => typeUnitTransportLocations.value.length > 0);

  const quantityKeys = [
    'representativeQuantity',
    'trunkCarQuantity',
    'trunkRepresentativeQuantity',
    'quantityGuides',
  ];

  const isQuantityColumn = (key: string): boolean => {
    return quantityKeys.includes(key);
  };

  const getTagClass = (value: number): string => {
    return value > 0 ? 'success-light' : 'disabled-light';
  };

  const getTagText = (value: number): string => {
    return value > 0 ? `${value} requerido(s)` : 'No requerido';
  };

  const handleDestroy = () => {
    showDeleteConfirm({
      deleteRequest: () => supportApi.delete(`${resource}/${transferData.value.id}`),
      onSuccess: (data) => {
        handleDeleteResponse(data);
        fetchData();
      },
    });
  };

  on('deleteTypeUnitSetting', () => {
    handleDestroy();
  });

  watch(
    () => [typeUnitTransportLocationId, periodYear, transferId],
    () => {
      fetchData();
    },
    { deep: true }
  );

  const setupEventListeners = () => {
    on('reloadTypeUnitSettingList', fetchData);
  };

  const cleanupEventListeners = () => {
    off('reloadTypeUnitSettingList', fetchData);
  };

  onMounted(() => {
    setupEventListeners();
  });

  onUnmounted(() => {
    cleanupEventListeners();
  });

  return {
    isLoadingMain,
    transferData,
    columns,
    existsLocations,
    contextHolder,
    isQuantityColumn,
    getTagClass,
    getTagText,
  };
}
