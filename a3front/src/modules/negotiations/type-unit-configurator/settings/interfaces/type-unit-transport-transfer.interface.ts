export interface TypeUnitTransport {
  id: number;
  code: string;
  name: string;
}

export interface SettingDetail {
  id: number;
  typeUnitTransport: TypeUnitTransport;
  minimumCapacity: number;
  maximumCapacity: number;
  representativeQuantity: number;
  trunkCarQuantity: number;
  trunkRepresentativeQuantity: number;
  quantityGuides: number;
  quantityUnitsRequired: number;
}

export interface TypeUnitTransportTransfer {
  id: number | null;
  typeUnitTransportSettingId: number | null;
  transferId: number | null;
  settingDetails: SettingDetail[];
}
