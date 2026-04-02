import type { SaveStaffPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveStaffPricingRequest.types';
import type { SaveStaffPricingDto } from '@/modules/negotiations/products/configuration/infrastructure/pricingPlans/dtos/saveStaffPricing.dto';

function getOrderedStaffTaxKeys(taxes: SaveStaffPricingRequest['taxes']): string[] {
  const keys: string[] = [];

  if (taxes.affectedByIGV && (taxes.igvPercent ?? 0) > 0) {
    keys.push('main_tax');
  }

  if (taxes.servicePercentage !== null && taxes.servicePercentage > 0) {
    keys.push('service_fee');
  }

  if (taxes.additionalPercentage) {
    for (const item of taxes.additionalPercentages) {
      const hasValidName = item.name.trim().length > 0;
      const hasValidPercentage = item.percentage !== null && item.percentage > 0;

      if (!item.id || (!hasValidName && !hasValidPercentage)) continue;

      keys.push(`${item.id}`);
    }
  }

  return keys;
}

function mapStaffRules(
  payload: SaveStaffPricingRequest
): SaveStaffPricingDto['taxAndStaffConfig']['staffRules'] {
  const staff = payload.staff;
  if (!staff?.selectedStaff?.length) {
    return [];
  }

  const taxKeys = getOrderedStaffTaxKeys(payload.taxes);

  return staff.selectedStaff.map((staffId) => {
    const row = staff.staffTaxes?.[staffId] ?? {};

    return {
      role: staffId,
      label: staffId,
      taxSettings: taxKeys.map((key) => ({
        feeId: key,
        isAffected: Boolean(row[key]),
      })),
    };
  });
}

export const mapToSaveStaffPricingDto = (payload: SaveStaffPricingRequest): SaveStaffPricingDto => {
  const taxes = payload.taxes;

  const taxAndStaffConfig: SaveStaffPricingDto['taxAndStaffConfig'] = {
    globalDefinitions: {
      mainTax: {
        isAffected: taxes.affectedByIGV,
        label: taxes.labelTax ?? 'IGV',
        value: taxes.igvPercent,
        hasRecovery: taxes.igvRecovery,
      },
      serviceFee: {
        isAffected: taxes.servicePercentage !== null && taxes.servicePercentage > 0,
        value: taxes.servicePercentage,
      },
      additionalFees: taxes.additionalPercentage
        ? taxes.additionalPercentages.map((tax) => ({
            id: `${tax.id}`,
            name: tax.name,
            value: tax.percentage,
          }))
        : [],
    },
    staffRules: mapStaffRules(payload),
    commissions: {
      percentage: payload.commissions?.percentage ?? null,
    },
  };

  return {
    taxAndStaffConfig,
  };
};
