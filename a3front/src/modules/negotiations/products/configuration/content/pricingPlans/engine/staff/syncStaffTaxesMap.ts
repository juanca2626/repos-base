import type {
  StaffTaxesMap,
  StaffTaxDefinition,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

interface SyncStaffTaxesMapParams {
  selectedStaff: string[];
  currentStaffTaxes: StaffTaxesMap;
  definitions: StaffTaxDefinition[];
}

export function syncStaffTaxesMap({
  selectedStaff,
  currentStaffTaxes,
  definitions,
}: SyncStaffTaxesMapParams): StaffTaxesMap {
  const next: StaffTaxesMap = {};

  selectedStaff.forEach((staffId) => {
    const currentTaxes = currentStaffTaxes[staffId] ?? {};

    next[staffId] = definitions.reduce<Record<string, boolean>>((acc, definition) => {
      acc[definition.key] = currentTaxes[definition.key] ?? false;
      return acc;
    }, {});
  });

  return next;
}
