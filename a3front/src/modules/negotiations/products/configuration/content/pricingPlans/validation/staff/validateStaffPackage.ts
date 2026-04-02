export function validateStaffPackage(state: any) {
  const errors: any = {};

  if (!state.commissions?.percentage) {
    errors.commission = 'La comisión es requerida';
  }

  if (state.commissions?.percentage <= 0) {
    errors.commission = 'La comisión no puede ser negativa';
  }

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}
