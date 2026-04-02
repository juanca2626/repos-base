import type { StaffState } from '../../types/staff.types';

export function isStaffComplete(staffState: StaffState) {
  if (staffState.taxes.affectedByIGV && staffState.taxes.igvPercent === null) {
    return false;
  }

  if (staffState.taxes.additionalPercentage) {
    staffState.taxes.additionalPercentages.forEach((additionalPercentage) => {
      if (additionalPercentage.percentage === null || additionalPercentage.name.trim() === '') {
        return false;
      }
    });
  }

  if (staffState.staff.selectedStaff.length > 0) {
    const hasInvalidStaffTaxes = staffState.staff.selectedStaff.some((staffId) => {
      const staffTaxes = staffState.staff.staffTaxes[staffId];

      if (!staffTaxes) {
        return true;
      }

      return !Object.values(staffTaxes).some(Boolean);
    });

    if (hasInvalidStaffTaxes) {
      return false;
    }
  }

  return true;
}
