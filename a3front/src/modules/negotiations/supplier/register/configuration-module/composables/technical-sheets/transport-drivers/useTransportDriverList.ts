import { onMounted, onUnmounted, ref, h, watch, reactive } from 'vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { storeToRefs } from 'pinia';
import { on, off, emit as emitBus } from '@/modules/negotiations/api/eventBus';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  DocumentExtensionInfo,
  DriverDocumentTransform,
  DriverExtensionTransform,
  SelectedDocument,
  SupplierVehicleDriver,
  SupplierVehicleDriverResponse,
  VehicleDriverDocumentResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { useTransportDriverFiltersStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTransportDriverFiltersStore';
import { useTechnicalSheetStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTechnicalSheetStore';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';
import type { DriverDocumentStatusKey } from '@/modules/negotiations/supplier/register/configuration-module/types';
import { handleDeleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import {
  isDriverDocumentDrawerVisible,
  isToBeReviewed,
} from '@/modules/negotiations/supplier/register/configuration-module/helpers/driverDocumentStatusHelper';

export function useTransportDriverList() {
  const resource = 'supplier-vehicle-drivers';

  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const transportDriverFiltersStore = useTransportDriverFiltersStore();

  const { name, documentStatus, surnames } = storeToRefs(transportDriverFiltersStore);

  const { setIsLoading, setRegisteredDriversCount } = useTechnicalSheetStore();

  const showDrawerDriverDocumentForm = ref<boolean>(false);
  const showDrawerDriverDocumentReviewForm = ref<boolean>(false);
  const showDrawerDocumentExtensionForm = ref<boolean>(false);

  const showActiveExtensionNotify = ref<boolean>(false);
  const documentExtensionIds = ref<string[]>([]);

  const supplierVehicleDriverId = ref<string>('');

  const documentExtensionInfo = reactive<DocumentExtensionInfo>({
    driverFullName: undefined,
  });

  const [modal, contextHolder] = Modal.useModal();
  const cancelDialog = ref({ disabled: false });

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<SupplierVehicleDriver[]>([]);

  const columns = [
    {
      title: 'NOMBRE',
      dataIndex: 'fullName',
      key: 'fullName',
      width: 270,
    },
    {
      title: 'CELULAR',
      dataIndex: 'phone',
      key: 'phone',
    },
    {
      title: 'DOCUMENTO DE IDENTIDAD',
      dataIndex: 'statusDni',
      key: 'statusDni',
      align: 'center',
      width: 140,
    },
    {
      title: 'LICENCIA DE CONDUCIR',
      dataIndex: 'statusDriverLicense',
      key: 'statusDriverLicense',
      align: 'center',
    },
    {
      title: 'ANTEC. PENALES',
      dataIndex: 'statusCriminalRecord',
      key: 'statusCriminalRecord',
      align: 'center',
    },
    {
      title: 'ANTEC. POLICIALES',
      dataIndex: 'statusPoliceRecord',
      key: 'statusPoliceRecord',
      align: 'center',
    },
    {
      title: 'RECORD DEL CONDUCTOR',
      dataIndex: 'statusDriverRecord',
      key: 'statusDriverRecord',
      align: 'center',
    },
    {
      title: 'OTROS',
      dataIndex: 'statusOthers',
      key: 'statusOthers',
      align: 'center',
    },
    {
      title: '',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const initSelectedDocument: SelectedDocument = {
    parentId: null,
    typeDocumentId: null,
    status: DriverDocumentStatusEnum.NO_DOCUMENTS,
    id: undefined,
    expirationDate: undefined,
    lastObservation: undefined,
    typeDocumentName: undefined,
  };

  const selectedDocument = reactive<SelectedDocument>({
    ...initSelectedDocument,
  });

  const onChange = (page: number, perSize: number) => {
    fetchSupplierVehicleDrivers(page, perSize);
  };

  const fetchSupplierVehicleDrivers = async (page: number = 1, pageSize: number = 10) => {
    setIsLoading(true);

    try {
      const response = await technicalSheetApi.get<
        ApiListResponse<SupplierVehicleDriverResponse[]>
      >(resource, {
        params: {
          perPage: pageSize,
          page,
          subClassificationSupplierId: subClassificationSupplierId.value,
          name: name.value,
          surnames: surnames.value,
          vehicleDriverDocumentStatus: getParamsDriverDocumentStatus(),
        },
      });

      setRegisteredDriversCount(response.data.pagination.total);
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

  const getParamsDriverDocumentStatus = () => {
    const fieldsStatus = [
      { field: 'status_dni', status: documentStatus.value },
      { field: 'status_driver_license', status: documentStatus.value },
      { field: 'status_criminal_record', status: documentStatus.value },
      { field: 'status_police_record', status: documentStatus.value },
      { field: 'status_driver_record', status: documentStatus.value },
      { field: 'status_others', status: documentStatus.value },
    ];

    if (documentStatus.value != null) {
      return JSON.stringify(fieldsStatus);
    }

    return undefined;
  };

  const transformListData = (responseData: SupplierVehicleDriverResponse[]) => {
    data.value = responseData.map((row: SupplierVehicleDriverResponse) => {
      return {
        id: row.id,
        subClassificationSupplierId: row.sub_classification_supplier_id,
        fullName: `${row.name} ${row.surnames}`,
        name: row.name,
        surnames: row.surnames,
        phone: row.phone,
        statusDni: row.status_dni,
        statusDriverLicense: row.status_driver_license,
        statusCriminalRecord: row.status_criminal_record,
        statusPoliceRecord: row.status_police_record,
        statusDriverRecord: row.status_driver_record,
        statusOthers: row.status_others,
        driverDocuments: getDriverDocuments(row),
        status: row.status,
      };
    });
  };

  const getDriverDocuments = (item: SupplierVehicleDriverResponse) => {
    return {
      dni: getVehicleDriverDocument(item, TypeVehicleDriverDocumentEnum.DNI, 'status_dni'),
      driverLicense: getVehicleDriverDocument(
        item,
        TypeVehicleDriverDocumentEnum.DRIVER_LICENSE,
        'status_driver_license'
      ),
      criminalRecord: getVehicleDriverDocument(
        item,
        TypeVehicleDriverDocumentEnum.CRIMINAL_RECORD,
        'status_criminal_record'
      ),
      policeRecord: getVehicleDriverDocument(
        item,
        TypeVehicleDriverDocumentEnum.POLICE_RECORD,
        'status_police_record'
      ),
      driverRecord: getVehicleDriverDocument(
        item,
        TypeVehicleDriverDocumentEnum.DRIVER_RECORD,
        'status_driver_record'
      ),
      others: getVehicleDriverDocument(item, TypeVehicleDriverDocumentEnum.OTHERS, 'status_others'),
    };
  };

  const getVehicleDriverDocument = (
    item: SupplierVehicleDriverResponse,
    type: TypeVehicleDriverDocumentEnum,
    statusKey: DriverDocumentStatusKey
  ): DriverDocumentTransform => {
    const data: DriverDocumentTransform = {
      supplierVehicleDriverId: item.id,
      typeVehicleDriverDocumentId: type,
      status: item[statusKey],
    };

    const driverDocument = findVehicleDriverDocument(item.vehicle_driver_documents, type);

    if (driverDocument) {
      data.id = driverDocument.id;
      data.expirationDate = driverDocument.expiration_date;
      data.lastObservation = driverDocument.last_observation;
      data.typeVehicleDriverDocumentName = driverDocument.type_vehicle_driver_document.name;
      data.extension = getExtension(item, driverDocument.type_vehicle_driver_document.id);
    }

    return data;
  };

  const getExtension = (
    item: SupplierVehicleDriverResponse,
    typeVehicleDriverDocumentId: number
  ): DriverExtensionTransform | null => {
    const extension = item.driver_extensions.find((row) => {
      return row.type_vehicle_driver_document_id === typeVehicleDriverDocumentId;
    });

    if (extension) {
      return {
        id: extension.id,
        dateTo: extension.date_to,
        typeVehicleDriverDocumentId: extension.type_vehicle_driver_document_id,
      };
    }

    return null;
  };

  const findVehicleDriverDocument = (
    vehicleDriverDocuments: VehicleDriverDocumentResponse[],
    typeVehicleDriverDocument: TypeVehicleDriverDocumentEnum
  ): VehicleDriverDocumentResponse | undefined => {
    return vehicleDriverDocuments.find((row) => {
      return row.type_vehicle_driver_document.id == typeVehicleDriverDocument;
    });
  };

  const resetSelectedDocument = () => {
    Object.assign(selectedDocument, { ...initSelectedDocument });
  };

  const setSelectedDocument = (driverDocument: DriverDocumentTransform) => {
    Object.assign(selectedDocument, {
      parentId: driverDocument.supplierVehicleDriverId,
      typeDocumentId: driverDocument.typeVehicleDriverDocumentId,
      status: driverDocument.status,
      id: driverDocument.id,
      expirationDate: driverDocument.expirationDate,
      lastObservation: driverDocument.lastObservation,
      typeDocumentName: driverDocument.typeVehicleDriverDocumentName,
    });
  };

  const handleDocumentStatus = (driverDocument: DriverDocumentTransform) => {
    resetSelectedDocument();

    const { status } = driverDocument;

    if (isDriverDocumentDrawerVisible(status)) {
      showDrawerDriverDocumentForm.value = true;
    } else if (isToBeReviewed(status)) {
      showDrawerDriverDocumentReviewForm.value = true;
    }

    setSelectedDocument(driverDocument);
  };

  const getDriverDocumentKey = (columnKey: string) => {
    // key column : key driver documents data
    const mapKeys: Record<string, string> = {
      statusDni: 'dni',
      statusDriverLicense: 'driverLicense',
      statusCriminalRecord: 'criminalRecord',
      statusPoliceRecord: 'policeRecord',
      statusDriverRecord: 'driverRecord',
      statusOthers: 'others',
    };
    return mapKeys[columnKey];
  };

  const handleEdit = (item: SupplierVehicleDriver) => {
    emitBus('editTransportDriver', item);
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

          if (data.success) {
            handleDeleteResponse(data);
            fetchSupplierVehicleDrivers();
          }
        } catch (error: any) {
          handleError(error);
          console.error('Error delete supplier vehicle driver', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  const setDocumentExtensionInfo = (item: SupplierVehicleDriver) => {
    Object.assign(documentExtensionInfo, {
      driverFullName: item.fullName,
    });
  };

  const handleExtensionSuccessNotify = (extensionId: string, item: SupplierVehicleDriver) => {
    setDocumentExtensionInfo(item);
    documentExtensionIds.value = [extensionId];
    showActiveExtensionNotify.value = true;
  };

  const handleDocumentExtension = (item: SupplierVehicleDriver) => {
    setDocumentExtensionInfo(item);
    supplierVehicleDriverId.value = item.id;
    showDrawerDocumentExtensionForm.value = true;
  };

  watch(
    () => [name, surnames, documentStatus],
    () => {
      fetchSupplierVehicleDrivers();
    },
    { deep: true }
  );

  const setupEventListeners = () => {
    on('reloadTransportDriverList', fetchSupplierVehicleDrivers);
  };

  const cleanupEventListeners = () => {
    off('reloadTransportDriverList', fetchSupplierVehicleDrivers);
  };

  onMounted(() => {
    setupEventListeners();
    fetchSupplierVehicleDrivers();
  });

  onUnmounted(() => {
    cleanupEventListeners();
  });

  return {
    data,
    columns,
    pagination,
    showDrawerDriverDocumentForm,
    showDrawerDriverDocumentReviewForm,
    selectedDocument,
    supplierVehicleDriverId,
    showDrawerDocumentExtensionForm,
    documentExtensionInfo,
    documentExtensionIds,
    showActiveExtensionNotify,
    contextHolder,
    handleEdit,
    handleDestroy,
    onChange,
    getDriverDocumentKey,
    handleDocumentStatus,
    handleDocumentExtension,
    handleExtensionSuccessNotify,
  };
}
