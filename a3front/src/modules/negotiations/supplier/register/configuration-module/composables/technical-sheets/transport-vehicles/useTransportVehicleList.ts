import { onMounted, onUnmounted, ref, watch, h, reactive } from 'vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { storeToRefs } from 'pinia';
import { on, off, emit as emitBus } from '@/modules/negotiations/api/eventBus';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  DocumentExtensionInfo,
  OperationLocationData,
  SelectedDocument,
  SupplierTransportVehicle,
  SupplierTransportVehicleResponse,
  TransportVehicleListEmitInterface,
  VehicleDocumentResponse,
  VehicleDocumentTransform,
  VehicleExtensionTransform,
  VehiclePhotoTransform,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
import type { DocumentStatusKey } from '@/modules/negotiations/supplier/register/configuration-module/types';
import { useTransportVehicleFiltersStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTransportVehicleFiltersStore';
import { useTechnicalSheetStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTechnicalSheetStore';
import { handleDeleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';
import { isVehiclePhotoDrawerVisible } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehiclePhotoStatusHelper';
import { isVehicleDocumentDrawerVisible } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehicleDocumentStatusHelper';

export function useTransportVehicleList(
  emit: TransportVehicleListEmitInterface,
  selectedLocation: OperationLocationData
) {
  const resource = 'supplier-transport-vehicles';

  const transportVehicleFiltersStore = useTransportVehicleFiltersStore();

  const { licensePlate, documentStatus, typeUnitTransportId } = storeToRefs(
    transportVehicleFiltersStore
  );

  const technicalSheetStore = useTechnicalSheetStore();
  const { setIsLoading, setRegisteredVehiclesCount } = technicalSheetStore;

  const showDrawerVehicleDocumentForm = ref<boolean>(false);
  const showDrawerVehicleDocumentReviewForm = ref<boolean>(false);

  const showDrawerVehiclePhotoForm = ref<boolean>(false);
  const showDrawerVehiclePhotoReviewForm = ref<boolean>(false);

  const showActiveExtensionNotify = ref<boolean>(false);
  const showDrawerDocumentExtensionForm = ref<boolean>(false);
  const documentExtensionIds = ref<string[]>([]);

  const documentExtensionInfo = reactive<DocumentExtensionInfo>({
    licensePlate: undefined,
    typeUnitCode: undefined,
  });

  const [modal, contextHolder] = Modal.useModal();
  const cancelDialog = ref({ disabled: false });

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<SupplierTransportVehicle[]>([]);

  const columns = [
    {
      title: 'PLACA',
      dataIndex: 'licensePlate',
      key: 'licensePlate',
      align: 'center',
    },
    {
      title: 'UNIDAD',
      dataIndex: 'typeUnitCode',
      key: 'typeUnitCode',
      align: 'center',
    },
    {
      title: 'MARCA/ AÑO',
      dataIndex: 'vehicleInfo',
      key: 'vehicleInfo',
      align: 'center',
    },
    {
      title: 'CNT. AS.',
      dataIndex: 'numberSeats',
      key: 'numberSeats',
      align: 'center',
    },
    {
      title: 'DESCRIPCIÓN',
      dataIndex: 'description',
      key: 'description',
    },
    {
      title: 'FOTOS UNIDAD',
      dataIndex: 'statusPhotos',
      key: 'statusPhotos',
      align: 'center',
    },
    {
      title: 'SOAT',
      dataIndex: 'statusSoat',
      key: 'statusSoat',
      align: 'center',
    },
    {
      title: 'CERT. INSPECCIÓN',
      dataIndex: 'statusInspectionCertificate',
      key: 'statusInspectionCertificate',
      align: 'center',
    },
    {
      title: 'SEGURO',
      dataIndex: 'statusSecure',
      key: 'statusSecure',
      align: 'center',
    },
    {
      title: 'TARJ. PROPIEDAD',
      dataIndex: 'statusPropertyCard',
      key: 'statusPropertyCard',
      align: 'center',
    },
    {
      title: 'TUC',
      dataIndex: 'statusCirculationCard',
      key: 'statusCirculationCard',
      align: 'center',
    },
    {
      title: 'CERT. GPS',
      dataIndex: 'statusGpsCertificate',
      key: 'statusGpsCertificate',
      align: 'center',
    },
    {
      title: '',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const initSelectedVehiclePhoto: VehiclePhotoTransform = {
    supplierTransportVehicleId: null,
    status: VehiclePhotoStatusEnum.NO_DOCUMENTS,
    id: undefined,
    lastObservation: undefined,
  };

  const selectedVehiclePhoto = reactive<VehiclePhotoTransform>({
    ...initSelectedVehiclePhoto,
  });

  const initSelectedDocument: SelectedDocument = {
    parentId: null,
    typeDocumentId: null,
    status: VehicleDocumentStatusEnum.NO_DOCUMENTS,
    id: undefined,
    expirationDate: undefined,
    lastObservation: undefined,
    typeDocumentName: undefined,
  };

  const selectedDocument = reactive<SelectedDocument>({
    ...initSelectedDocument,
  });

  const supplierTransportVehicleId = ref<string>('');

  const onChange = (page: number, perSize: number) => {
    fetchSupplierTransportVehicles(page, perSize);
  };

  const fetchSupplierTransportVehicles = async (page: number = 1, pageSize: number = 10) => {
    if (!selectedLocation.supplier_branch_office_id) return;

    setIsLoading(true);

    try {
      const response = await technicalSheetApi.get(resource, {
        params: {
          perPage: pageSize,
          page,
          supplierBranchOfficeId: selectedLocation.supplier_branch_office_id,
          licensePlate: licensePlate.value,
          typeUnitTransportIds: getParamsTypeUnitTransportId(),
          vehicleDocumentStatus: getParamsVehicleDocumentStatus(),
        },
      });

      setRegisteredVehiclesCount(response.data.pagination.total);
      transformListData(response.data.data);

      pagination.value = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching technical list data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const getParamsVehicleDocumentStatus = () => {
    const fieldsStatus = [
      { field: 'status_photos', status: documentStatus.value },
      { field: 'status_soat', status: documentStatus.value },
      { field: 'status_inspection_certificate', status: documentStatus.value },
      { field: 'status_secure', status: documentStatus.value },
      { field: 'status_property_card', status: documentStatus.value },
      { field: 'status_circulation_card', status: documentStatus.value },
      { field: 'status_gps_certificate', status: documentStatus.value },
    ];

    if (documentStatus.value != null) {
      return JSON.stringify(fieldsStatus);
    }

    return undefined;
  };

  const getParamsTypeUnitTransportId = () => {
    return typeUnitTransportId.value.includes(-1)
      ? undefined
      : typeUnitTransportId.value.length > 0
        ? JSON.stringify(typeUnitTransportId.value)
        : undefined;
  };

  const transformListData = (responseData: SupplierTransportVehicleResponse[]) => {
    data.value = responseData.map((row: SupplierTransportVehicleResponse) => {
      return {
        id: row.id,
        supplierBranchOfficeId: row.supplier_branch_office_id,
        typeUnit: row.type_unit_transport,
        autoBrand: row.auto_brand,
        manufacturingYear: row.manufacturing_year,
        licensePlate: row.license_plate,
        numberSeats: row.number_seats,
        description: row.description,
        statusPhotos: row.status_photos,
        statusSoat: row.status_soat,
        statusInspectionCertificate: row.status_inspection_certificate,
        statusSecure: row.status_secure,
        statusPropertyCard: row.status_property_card,
        statusCirculationCard: row.status_circulation_card,
        statusGpsCertificate: row.status_gps_certificate,
        status: row.status,
        vehiclePhoto: getVehiclePhoto(row),
        vehicleDocuments: getVehicleDocuments(row),
      };
    });
  };

  const getVehicleDocuments = (item: SupplierTransportVehicleResponse) => {
    return {
      soat: getVehicleDocument(item, TypeVehicleDocumentEnum.SOAT, 'status_soat'),
      inspection_certificate: getVehicleDocument(
        item,
        TypeVehicleDocumentEnum.INSPECTION_CERTIFICATE,
        'status_inspection_certificate'
      ),
      secure: getVehicleDocument(item, TypeVehicleDocumentEnum.SECURE, 'status_secure'),
      property_card: getVehicleDocument(
        item,
        TypeVehicleDocumentEnum.PROPERTY_CARD,
        'status_property_card'
      ),
      circulation_card: getVehicleDocument(
        item,
        TypeVehicleDocumentEnum.CIRCULATION_CARD,
        'status_circulation_card'
      ),
      gps_certificate: getVehicleDocument(
        item,
        TypeVehicleDocumentEnum.GPS_CERTIFICATE,
        'status_gps_certificate'
      ),
    };
  };

  const getVehicleDocument = (
    item: SupplierTransportVehicleResponse,
    type: TypeVehicleDocumentEnum,
    statusKey: DocumentStatusKey
  ): VehicleDocumentTransform => {
    const data: VehicleDocumentTransform = {
      supplierTransportVehicleId: item.id,
      typeVehicleDocumentId: type,
      status: item[statusKey],
    };

    const vehicleDocument = findVehicleDocument(item.vehicle_documents, type);

    if (vehicleDocument) {
      data.id = vehicleDocument.id;
      data.expirationDate = vehicleDocument.expiration_date;
      data.lastObservation = vehicleDocument.last_observation;
      data.typeVehicleDocumentName = vehicleDocument.type_vehicle_document.name;
      data.extension = getExtension(item, vehicleDocument.type_vehicle_document.id);
    }

    return data;
  };

  const getExtension = (
    item: SupplierTransportVehicleResponse,
    typeVehicleDocumentId: number
  ): VehicleExtensionTransform | null => {
    const extension = item.vehicle_extensions.find((row) => {
      return row.type_vehicle_document_id === typeVehicleDocumentId;
    });

    if (extension) {
      return {
        id: extension.id,
        dateTo: extension.date_to,
        typeVehicleDocumentId: extension.type_vehicle_document_id,
      };
    }

    return null;
  };

  const findVehicleDocument = (
    vehicleDocuments: VehicleDocumentResponse[],
    typeVehicleDocument: TypeVehicleDocumentEnum
  ): VehicleDocumentResponse | undefined => {
    return vehicleDocuments.find((row) => {
      return row.type_vehicle_document.id == typeVehicleDocument;
    });
  };

  const getVehiclePhoto = (item: SupplierTransportVehicleResponse): VehiclePhotoTransform => {
    return {
      supplierTransportVehicleId: item.id,
      status: item.status_photos,
      id: item.vehicle_photo?.id,
      lastObservation: item.vehicle_photo?.last_observation,
    };
  };

  const handleEdit = (item: SupplierTransportVehicle) => {
    emitBus('editTransportVehicle', item);
  };

  const handleDestroy = (id: string) => {
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
          const { data } = await technicalSheetApi.delete(`${resource}/${id}`);
          handleDeleteResponse(data);
          fetchSupplierTransportVehicles();
        } catch (error: any) {
          handleError(error);
          console.error('Error delete supplier transport vehicle', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  const resetSelectedDocument = () => {
    Object.assign(selectedDocument, { ...initSelectedDocument });
  };

  const setSelectedDocument = (vehicleDocument: VehicleDocumentTransform) => {
    Object.assign(selectedDocument, {
      parentId: vehicleDocument.supplierTransportVehicleId,
      typeDocumentId: vehicleDocument.typeVehicleDocumentId,
      status: vehicleDocument.status,
      id: vehicleDocument.id,
      expirationDate: vehicleDocument.expirationDate,
      lastObservation: vehicleDocument.lastObservation,
      typeDocumentName: vehicleDocument.typeVehicleDocumentName,
    });
  };

  const handleDocumentStatus = (vehicleDocument: VehicleDocumentTransform) => {
    resetSelectedDocument();

    const { status } = vehicleDocument;

    if (isVehicleDocumentDrawerVisible(status)) {
      showDrawerVehicleDocumentForm.value = true;
    } else if (status === VehicleDocumentStatusEnum.TO_BE_REVIEWED) {
      showDrawerVehicleDocumentReviewForm.value = true;
    }

    setSelectedDocument(vehicleDocument);
  };

  const resetSelectedPhoto = () => {
    Object.assign(selectedVehiclePhoto, { ...initSelectedVehiclePhoto });
  };

  const handleVehiclePhoto = (vehiclePhoto: VehiclePhotoTransform) => {
    resetSelectedPhoto();

    const { status } = vehiclePhoto;

    if (isVehiclePhotoDrawerVisible(status)) {
      showDrawerVehiclePhotoForm.value = true;
    } else if (status === VehiclePhotoStatusEnum.TO_BE_REVIEWED) {
      showDrawerVehiclePhotoReviewForm.value = true;
    }

    Object.assign(selectedVehiclePhoto, vehiclePhoto);
  };

  const setDocumentExtensionInfo = (item: SupplierTransportVehicle) => {
    Object.assign(documentExtensionInfo, {
      licensePlate: item.licensePlate,
      typeUnitCode: item.typeUnit.code,
    });
  };

  const handleExtensionSuccessNotify = (extensionId: string, item: SupplierTransportVehicle) => {
    setDocumentExtensionInfo(item);
    documentExtensionIds.value = [extensionId];
    showActiveExtensionNotify.value = true;
  };

  const handleDocumentExtension = (item: SupplierTransportVehicle) => {
    setDocumentExtensionInfo(item);
    supplierTransportVehicleId.value = item.id;
    showDrawerDocumentExtensionForm.value = true;
  };

  watch(
    () => selectedLocation.supplier_branch_office_id,
    () => {
      fetchSupplierTransportVehicles();
    }
  );

  watch(
    () => [licensePlate, documentStatus, typeUnitTransportId],
    () => {
      fetchSupplierTransportVehicles();
    },
    { deep: true }
  );

  const setupEventListeners = () => {
    on('reloadTransportVehicleList', fetchSupplierTransportVehicles);
  };

  const cleanupEventListeners = () => {
    off('reloadTransportVehicleList', fetchSupplierTransportVehicles);
  };

  onMounted(() => {
    setupEventListeners();
  });

  onUnmounted(() => {
    cleanupEventListeners();
    emit('onTransportVehicleListUnmounted');
  });

  return {
    data,
    columns,
    pagination,
    showDrawerVehicleDocumentForm,
    showDrawerVehicleDocumentReviewForm,
    showDrawerVehiclePhotoForm,
    showDrawerVehiclePhotoReviewForm,
    selectedDocument,
    selectedVehiclePhoto,
    showActiveExtensionNotify,
    supplierTransportVehicleId,
    documentExtensionInfo,
    showDrawerDocumentExtensionForm,
    documentExtensionIds,
    contextHolder,
    onChange,
    handleEdit,
    handleDestroy,
    handleDocumentStatus,
    handleVehiclePhoto,
    handleExtensionSuccessNotify,
    handleDocumentExtension,
  };
}
