import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import { validateRequiredFields } from '@/modules/negotiations/supplier-new/validators/supplier-registration/general-validator';
import type { SupplierPolicyReleasedForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { releasedRequiredFields } from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';

import { ReleaseTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/release-type.enum';

export const validateReleasedSection = (
  released: SupplierPolicyReleasedForm[],
  formSectionErrors: FormSectionErrors[]
): boolean => {
  let isValid = true;

  for (let index = 0; index < released.length; index++) {
    const currentValid = validateRequiredFields(
      releasedRequiredFields,
      released[index],
      formSectionErrors[index]
    );

    if (!currentValid) {
      isValid = false;
    }

    // Validación condicional para maximumCapValue cuando hasMaximumCap es true
    if (released[index].hasMaximumCap) {
      const maximumCapValue = released[index].maximumCapValue;
      if (maximumCapValue === null || maximumCapValue === undefined) {
        formSectionErrors[index]['maximumCapValue'].isInvalid = true;
        isValid = false;
      }
    }

    // Validación condicional para campos de habitaciones
    if (released[index].releaseType === ReleaseTypeEnum.ROOM) {
      if (!released[index].benefitType) {
        formSectionErrors[index]['benefitType'].isInvalid = true;
        isValid = false;
      }
      if (
        released[index].breakfastIncluded === null ||
        released[index].breakfastIncluded === undefined
      ) {
        formSectionErrors[index]['breakfastIncluded'].isInvalid = true;
        isValid = false;
      }
    }
  }

  return isValid;
};
