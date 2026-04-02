export function validateStaffBase(state: any) {
  const errors: any = {};

  // Si el estado o la sección de impuestos no existen, no hay nada que validar.
  if (!state || !state.taxes) {
    return {
      valid: true,
      errors,
    };
  }

  if (state.taxes.affectedByIGV && state.taxes.igvPercent <= 0) {
    errors.igvPercent = 'El porcentaje de IGV debe ser mayor a 0';
  }

  if (state.taxes.affectedByIGV && state.taxes.igvPercent > 100) {
    errors.igvPercent = 'El porcentaje de IGV debe ser menor o igual a 100';
  }

  // if (state.taxes.igvRecovery && state.taxes.igvRecoveryPercent <= 0) {
  //   errors.igvRecoveryPercent = 'El porcentaje de recuperación de IGV debe ser mayor a 0';
  // }

  if (state.taxes.igvRecovery && state.taxes.igvRecoveryPercent > 100) {
    errors.igvRecoveryPercent = 'El porcentaje de recuperación de IGV debe ser menor o igual a 100';
  }

  // if (state.taxes.servicePercentage <= 0) {
  //   errors.servicePercentage = 'El porcentaje no puede ser negativo';
  // }

  // if (state.taxes.servicePercentage > 100) {
  //   errors.servicePercentage = 'El porcentaje no puede ser mayor a 100';
  // }

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}
