import type {
  StaffState,
  StaffTaxDefinition,
} from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

export function buildStaffTaxDefinitions(taxes: StaffState['taxes']): StaffTaxDefinition[] {
  const definitions: StaffTaxDefinition[] = [];

  if (taxes.affectedByIGV && taxes.igvPercent !== null && taxes.igvPercent > 0) {
    definitions.push({
      key: 'main_tax',
      label: 'IGV',
      kind: 'igv',
    });
  }

  if (taxes.servicePercentage !== null && taxes.servicePercentage > 0) {
    definitions.push({
      key: 'service_fee',
      label: '% Servicios',
      kind: 'service',
    });
  }

  if (taxes.additionalPercentage) {
    taxes.additionalPercentages.forEach((item) => {
      const hasValidName = item.name.trim().length > 0;
      const hasValidPercentage = item.percentage !== null && item.percentage > 0;

      if (!item.id || (!hasValidName && !hasValidPercentage)) return;

      definitions.push({
        key: `${item.id}`,
        label: item.name.trim() || '% Adicional',
        kind: 'additional',
        additionalId: `${item.id}`,
      });
    });
  }

  return definitions;
}
