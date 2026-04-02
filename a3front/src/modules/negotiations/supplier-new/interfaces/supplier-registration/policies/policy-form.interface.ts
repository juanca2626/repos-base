import type {
  BusinessGroup,
  SegmentationSpecificationResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import type { MeasurementUnitEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/measurement-unit.enum';

export interface PolicySegmentationSpecification {
  segmentationId: number;
  objectIds: number[];
  inputValue?: string;
  policySegmentationId?: number; // from API
  responseSegmentationSpecifications?: SegmentationSpecificationResponse[]; // from API
}

export interface SupplierPolicyForm {
  id?: number | null;
  supplierId: number | null;
  businessGroupId: number | null;
  businessGroup?: BusinessGroup | null;
  name: string | null;
  dateFrom: string | null;
  dateTo: string | null;
  paxMin: number | null;
  paxMax: number | null;
  measurementUnit: MeasurementUnitEnum | null; // Unidad de medida: Personas o Habitaciones
  isHotel?: boolean; // Para mostrar campos específicos de hotel
  status?: boolean;
  policySegmentationIds: number[]; // for UI
  segmentationSpecifications: PolicySegmentationSpecification[]; // for UI
  segmentationNamesSummary: string | null; // from API
}

export interface SelectOptionsLoading {
  markets: boolean;
  clients: boolean;
  holidayCalendars: boolean;
  serviceTypes: boolean;
  seasons: boolean;
}
