import { computed, toRefs } from 'vue';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import type { DocumentStatusData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import type {
  DocumentStatusDataMap,
  DocumentStatusEnum,
  DocumentStatusProps,
} from '@/modules/negotiations/supplier/register/configuration-module/types';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export function useDocumentStatus(props: DocumentStatusProps) {
  const { date, observations, extension } = toRefs(props);

  const baseStatusDataMap: Record<number, DocumentStatusData> = {
    [0]: {
      text: 'Adjuntar',
      statusClass: 'no-documents',
      showUploadIcon: true,
    },
    [1]: {
      text: 'Por revisar',
      statusClass: 'to-be-reviewed',
    },
    [2]: {
      text: 'Aprobado',
      statusClass: 'approved',
    },
    [3]: {
      text: 'Rechazado',
      statusClass: 'rejected',
    },
    [4]: {
      text: 'Por vencer',
      statusClass: 'to-expire',
    },
    [5]: {
      text: 'Vencido',
      statusClass: 'expired',
    },
    [6]: {
      text: 'No se aplica',
      statusClass: 'not-applicable',
    },
  };

  const createStatusDataMap = (documentStatusEnum: DocumentStatusEnum) => {
    return Object.fromEntries(
      Object.values(documentStatusEnum)
        .filter((value) => typeof value === 'number' && baseStatusDataMap[value])
        .map((value) => [value, baseStatusDataMap[value]])
    );
  };

  const isVehicleType = computed(
    () => props.technicalSheetType === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE
  );

  const vehicleStatusDataMap = createStatusDataMap(VehicleDocumentStatusEnum);
  const driverStatusDataMap = createStatusDataMap(DriverDocumentStatusEnum);

  const documentStatusEnum = isVehicleType.value
    ? VehicleDocumentStatusEnum
    : DriverDocumentStatusEnum;

  const statusDataMap = computed(() => {
    return isVehicleType.value
      ? (vehicleStatusDataMap as DocumentStatusDataMap)
      : (driverStatusDataMap as DocumentStatusDataMap);
  });

  const status = computed(() => {
    return isVehicleType.value
      ? (props.status as VehicleDocumentStatusEnum)
      : (props.status as DriverDocumentStatusEnum);
  });

  const statusText = computed(() => statusDataMap.value[status.value]?.text || '');

  const containerStatusClass = computed(
    () => `container-status-${statusDataMap.value[status.value]?.statusClass || ''}`
  );
  const textStatusClass = computed(
    () => `text-status-${statusDataMap.value[status.value]?.statusClass || ''}`
  );
  const dateStatusClass = computed(
    () => `date-status-${statusDataMap.value[status.value]?.statusClass || ''}`
  );
  const showUploadIcon = computed(() => statusDataMap.value[status.value]?.showUploadIcon);

  const notApplicableStatus =
    'NOT_APPLICABLE' in documentStatusEnum ? documentStatusEnum.NOT_APPLICABLE : undefined;

  const isDocumentUnavailable = () => {
    return [documentStatusEnum.NO_DOCUMENTS, notApplicableStatus].includes(status.value);
  };

  const showDate = computed(() => {
    return date && !isDocumentUnavailable();
  });

  const showVehicleExtension = computed(() => {
    return !isDocumentUnavailable() && extension?.value?.id;
  });

  const showObservations = computed(() => {
    return observations && status.value === documentStatusEnum.REJECTED;
  });

  const isClickable = computed(() => {
    return [
      documentStatusEnum.NO_DOCUMENTS,
      documentStatusEnum.TO_BE_REVIEWED,
      documentStatusEnum.REJECTED,
      documentStatusEnum.APPROVED,
    ].includes(status.value);
  });

  return {
    statusText,
    containerStatusClass,
    textStatusClass,
    dateStatusClass,
    showUploadIcon,
    showDate,
    showObservations,
    isClickable,
    showVehicleExtension,
    extension,
  };
}
