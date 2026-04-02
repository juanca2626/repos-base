import { ref, onMounted, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useTechnicalSheetLocations } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/useTechnicalSheetLocations';
import { useTechnicalSheetStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTechnicalSheetStore';
import type {
  DownloadResultForm,
  NotificationOption,
  OptionData,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';
import { formatRegisteredText } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

export function useTechnicalSheet() {
  const automaticNotifications = ref<boolean>(false);
  const showDrawerVehicleForm = ref<boolean>(false);
  const showDrawerDriverForm = ref<boolean>(false);
  const showDownloadResult = ref<boolean>(false);

  const { showNotificationSuccess } = useNotifications();

  const {
    isLoading: isLoadingLocation,
    locationData,
    selectedLocation,
    resetSelectedLocation,
    handleTabClick,
    fetchLocationData,
  } = useTechnicalSheetLocations();

  const { subClassificationSupplierId, formStateNegotiation } = useSupplierFormStoreFacade();

  const technicalSheetStore = useTechnicalSheetStore();

  const {
    isLoading,
    typeTechnicalSheet,
    isTransportVehicleActive,
    registeredVehiclesCount,
    registeredDriversCount,
  } = storeToRefs(technicalSheetStore);

  const { toggleTypeTechnicalSheet } = technicalSheetStore;

  const notificationOptions: NotificationOption[] = [
    {
      category: 'notifications',
      data: [
        { name: 'Notificar - Falta documentos', key: 'no-documents', icon: 'paper-plane' },
        { name: 'Notificar - Rechazados', key: 'rejected', icon: 'paper-plane' },
        { name: 'Notificar - Por vencer', key: 'near-expiration', icon: 'paper-plane' },
        { name: 'Notificar - Vencidos', key: 'expired', icon: 'paper-plane' },
      ],
    },
    {
      category: 'logs',
      data: [{ name: 'Registro de actividades', key: 'activity-log', icon: 'activity' }],
    },
  ];

  const handleNotificationOption = (item: OptionData) => {
    console.log('handleNotificationOption', item);
  };

  const handleTypeTechnicalSheet = () => {
    toggleTypeTechnicalSheet();
  };

  const registeredVehiclesText = computed(() =>
    formatRegisteredText(registeredVehiclesCount.value, 'Unidad añadida', 'Unidades añadidas')
  );

  const registeredDriversText = computed(() =>
    formatRegisteredText(registeredDriversCount.value, 'Conductor añadido', 'Conductores añadidos')
  );

  const registeredRecordsText = computed(() =>
    isTransportVehicleActive.value ? registeredVehiclesText.value : registeredDriversText.value
  );

  const handleDownloadResult = async (data: DownloadResultForm) => {
    const resource = isTransportVehicleActive.value
      ? 'supplier-transport-vehicles'
      : 'supplier-vehicle-drivers';

    const params = {
      subClassificationSupplierId: subClassificationSupplierId.value,
      supplierBranchOfficeIds: isTransportVehicleActive.value
        ? data.supplierBranchOfficeIds
        : undefined,
    };

    const response = await technicalSheetApi.get(`${resource}/download-result`, {
      responseType: 'blob',
      params,
    });

    const fileBlob = new Blob([response.data]);
    const link = document.createElement('a');
    link.href = URL.createObjectURL(fileBlob);
    link.download = `${data.filename}.${data.extension}`;
    link.click();

    showNotificationSuccess('Archivo descargado exitosamente.');
  };

  const onTransportVehicleListUnmounted = () => {
    resetSelectedLocation();
  };

  const initialFilename = computed(
    () =>
      `Ficha tecnica de ${formStateNegotiation.businessName} - ${isTransportVehicleActive.value ? 'Unidades' : 'Conductores'}`
  );

  const listTitle = computed(
    () => `Listado de ${isTransportVehicleActive.value ? 'unidades' : 'conductores'}`
  );

  const handleNewRecord = () => {
    if (isTransportVehicleActive.value) {
      showDrawerVehicleForm.value = true;
    } else {
      showDrawerDriverForm.value = true;
    }
  };

  onMounted(async () => {
    await fetchLocationData();
  });

  return {
    isLoading,
    isLoadingLocation,
    locationData,
    selectedLocation,
    automaticNotifications,
    notificationOptions,
    typeTechnicalSheet,
    isTransportVehicleActive,
    showDrawerVehicleForm,
    showDownloadResult,
    registeredRecordsText,
    initialFilename,
    listTitle,
    showDrawerDriverForm,
    handleNotificationOption,
    handleTypeTechnicalSheet,
    handleTabClick,
    handleDownloadResult,
    onTransportVehicleListUnmounted,
    handleNewRecord,
  };
}
