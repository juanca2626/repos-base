import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import type { DataObject } from '@/modules/negotiations/suppliers/types';
import { validateRequiredFields } from '@/modules/negotiations/supplier-new/validators/supplier-registration/general-validator';
import { requiredMainPaymentTermFields } from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';

// Campos requeridos para pagos parciales (sin partialConditionValue que tiene excepción)
const partialPaymentRequiredFields = [
  'partialConditionTypeId',
  'partialAmount',
  'partialAmountType',
];

/**
 * Valida los pagos parciales aplicando la excepción:
 * - partialConditionValue NO es requerido si partialConditionTypeId === 3 (Al momento de la reserva)
 */
export const validatePartialPaymentsSection = (
  partialPayments: any[],
  formSectionErrors: FormSectionErrors[]
): boolean => {
  if (!partialPayments || partialPayments.length === 0) {
    return true; // No hay pagos parciales que validar
  }

  let isValid = true;

  partialPayments.forEach((payment, index) => {
    const errorRow = formSectionErrors[index];
    if (!errorRow) return;

    // Validar campos siempre requeridos
    partialPaymentRequiredFields.forEach((field) => {
      const value = payment[field];
      if (value === null || value === undefined || value === '') {
        errorRow[field].isInvalid = true;
        isValid = false;
      }
    });

    // Validar partialConditionValue con excepción para "Al momento de la reserva" (ID: 3)
    const conditionTypeId = payment.partialConditionTypeId;
    const conditionValue = payment.partialConditionValue;

    // Solo es requerido si el conditionTypeId NO es 3 (Al momento de la reserva)
    if (conditionTypeId !== 3) {
      if (conditionValue === null || conditionValue === undefined || conditionValue === '') {
        errorRow.partialConditionValue.isInvalid = true;
        isValid = false;
      }
    }
  });

  return isValid;
};

export const validatePaymentTermSection = (
  data: DataObject,
  formSectionErrors: FormSectionErrors
): boolean => {
  return validateRequiredFields(requiredMainPaymentTermFields, data, formSectionErrors);
};
