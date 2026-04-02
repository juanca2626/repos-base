export interface SettingDetailForm {
  id?: number | null;
  typeUnitTransportId: number | null;
  minimumCapacity: number;
  maximumCapacity: number;
  representativeQuantity: number;
  trunkCarQuantity: number;
  trunkRepresentativeQuantity: number;
  quantityGuides: number;
  quantityUnitsRequired: number;
}

export interface TypeUnitSettingForm {
  id?: number | null;
  locationKey: string;
  periodYear: number;
  transferId: number | null;
  settingDetails: SettingDetailForm[];
}

export interface ResultByLocation {
  location: string;
  message: string;
  errors: any[];
}
