import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import { validateAllIfAnyFieldPresent } from '@/modules/negotiations/supplier-new/validators/supplier-registration/general-validator';
import type { SupplierPolicyCancellationForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { cancellationRequiredFields } from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';

export const validateCancellationsSection = (
  cancellations: SupplierPolicyCancellationForm[],
  formSectionErrors: FormSectionErrors[]
): boolean => {
  let isValid = true;

  for (let index = 0; index < cancellations.length; index++) {
    const currentValid = validateAllIfAnyFieldPresent(
      cancellationRequiredFields,
      cancellations[index],
      formSectionErrors[index]
    );

    if (!currentValid) {
      isValid = false;
    }

    // Check for any invalid field in the section to update overall validity
    const isSectionInvalid = Object.values(formSectionErrors[index]).some((f) => f.isInvalid);
    if (isSectionInvalid) isValid = false;
  }

  return isValid;
};
