import { nanoid } from 'nanoid';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import type { StaffState } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

export function createInitialStaffState(_serviceType: ServiceType | null = null): StaffState {
  return {
    taxes: {
      labelTax: null,
      affectedByIGV: true,
      igvPercent: null,
      igvRecovery: false,
      igvRecoveryPercent: null,
      servicePercentage: null,
      additionalPercentage: false,
      additionalPercentages: [
        {
          id: `additional_${nanoid()}`,
          name: '',
          percentage: null,
        },
      ],
    },

    staff: {
      selectedStaff: [],
      staffTaxes: {},
    },

    commissions: {
      percentage: null,
    },
  };
}
