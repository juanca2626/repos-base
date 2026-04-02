import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';

export interface PlaceOperationFormProps {
  selectedLocation: string;
}

export interface PlaceOperationForm {
  id: number | null;
  primaryLocationKey: string | null;
  zoneLocationKey: string | null;
  zoneLocations: SelectOption[];
}
