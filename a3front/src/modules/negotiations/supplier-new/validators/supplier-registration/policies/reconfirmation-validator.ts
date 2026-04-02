import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import { validateRequiredFields } from '@/modules/negotiations/supplier-new/validators/supplier-registration/general-validator';
import type { SupplierPolicyReconfirmationForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { reconfirmationRequiredFields } from '@/modules/negotiations/supplier-new/constants/supplier-registration/policies/form-policy-rule-fields';

export const validateReconfirmationsSection = (
  reconfirmations: SupplierPolicyReconfirmationForm[],
  formSectionErrors: FormSectionErrors[]
): boolean => {
  let isValid = true;

  for (let index = 0; index < reconfirmations.length; index++) {
    let currentValid = validateRequiredFields(
      reconfirmationRequiredFields,
      reconfirmations[index],
      formSectionErrors[index]
    );

    // Validate send list fields conditionally when enabled
    if (reconfirmations[index].sendListEnabled) {
      if (!reconfirmations[index].listSendTimeValue) {
        if (formSectionErrors[index]?.listSendTimeValue) {
          formSectionErrors[index].listSendTimeValue.isInvalid = true;
        }
        currentValid = false;
      }

      if (!reconfirmations[index].listSendTimeUnit) {
        if (formSectionErrors[index]?.listSendTimeUnit) {
          formSectionErrors[index].listSendTimeUnit.isInvalid = true;
        }
        currentValid = false;
      }

      // Validate unassignedQuota only for 'preliminary' type
      if (reconfirmations[index].listType === 'preliminary') {
        if (
          reconfirmations[index].unassignedQuota === null ||
          reconfirmations[index].unassignedQuota === undefined
        ) {
          if (formSectionErrors[index]?.unassignedQuota) {
            formSectionErrors[index].unassignedQuota.isInvalid = true;
          }
          currentValid = false;
        }
      }
    }

    if (!currentValid) {
      isValid = false;
    }
  }

  return isValid;
};
