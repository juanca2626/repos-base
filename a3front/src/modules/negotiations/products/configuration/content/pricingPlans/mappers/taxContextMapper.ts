import { nanoid } from 'nanoid';
import type { Tax } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/tax.types';

export function mapTaxContext(staffState: any, serviceType: string): Tax[] {
  const taxes: Tax[] = [];

  if (staffState.affectedByIGV) {
    taxes.push({
      id: nanoid(),
      name: 'igv',
      percentage: staffState.igvPercent ?? 18,
    });
  }

  if (staffState.servicePercentage) {
    taxes.push({
      id: nanoid(),
      name: 'services',
      percentage: staffState.servicePercentage,
    });
  }

  if (staffState.additionalPercentage) {
    staffState.additionalPercentages?.forEach((tax: any) => {
      if (tax.name && tax.percentage) {
        taxes.push({
          id: nanoid(),
          name: tax.name,
          percentage: tax.percentage,
        });
      }
    });
  }

  if (serviceType === 'PACKAGE') {
    if (staffState.commissionPercent) {
      taxes.push({
        id: nanoid(),
        name: 'commission',
        percentage: staffState.commissionPercent,
      });
    }
  }

  return taxes;
}
