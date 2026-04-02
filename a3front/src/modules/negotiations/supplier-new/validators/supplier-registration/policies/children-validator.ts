import {
  requiredChildrenChildFields,
  requiredChildrenInfantFields,
} from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';
import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import {
  validateAllIfAnyFieldPresent,
  validateRequiredFields,
} from '@/modules/negotiations/supplier-new/validators/supplier-registration/general-validator';
import {
  hasAnyValue,
  hasValue,
} from '@/modules/negotiations/suppliers/helpers/field-validation-helper';
import type { DataObject } from '@/modules/negotiations/suppliers/types';

export const validateChildrenSection = (
  data: DataObject,
  formSectionErrors: FormSectionErrors
): boolean => {
  // Infantes: siempre obligatorio
  const validInfant = validateRequiredFields(requiredChildrenInfantFields, data, formSectionErrors);

  // Ninos: opcional, pero si se completa uno de los campos se requieren ambos
  const validChild = validateAllIfAnyFieldPresent(
    requiredChildrenChildFields,
    data,
    formSectionErrors
  );

  let validChildRange = true;
  const hasChildInfo = hasAnyValue(requiredChildrenChildFields, data);

  if (hasChildInfo) {
    if (hasValue('childAgeMin', data) && hasValue('infantAgeMax', data)) {
      const childAgeMin = Number(data.childAgeMin);
      const infantAgeMax = Number(data.infantAgeMax);

      if (
        !Number.isNaN(childAgeMin) &&
        !Number.isNaN(infantAgeMax) &&
        childAgeMin <= infantAgeMax
      ) {
        formSectionErrors.childAgeMin.isInvalid = true;
        formSectionErrors.childAgeMin.message =
          'La edad minima de nino debe ser mayor a la edad maxima de infante';
        validChildRange = false;
      }
    }

    if (hasValue('childAgeMin', data) && hasValue('childAgeMax', data)) {
      const childAgeMin = Number(data.childAgeMin);
      const childAgeMax = Number(data.childAgeMax);

      if (!Number.isNaN(childAgeMin) && !Number.isNaN(childAgeMax) && childAgeMax < childAgeMin) {
        formSectionErrors.childAgeMax.isInvalid = true;
        formSectionErrors.childAgeMax.message =
          'La edad maxima de nino debe ser mayor o igual a la edad minima de nino';
        validChildRange = false;
      }
    }
  }

  return validInfant && validChild && validChildRange;
};
