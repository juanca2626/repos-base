import { usePolicyFormStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-form-store-facade.composable';
import type {
  PolicyRuleCloneActions,
  SupplierPolicyCloneResponse,
  SupplierPolicyRuleForm,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export function usePolicyRuleCloneComposable(
  formState: SupplierPolicyRuleForm,
  actions: PolicyRuleCloneActions
) {
  const {
    addCancellation,
    addReconfirmation,
    addReleased,
    addCancellationError,
    addReconfirmationError,
    addReleasedError,
  } = actions;

  const { policyCloneResponse } = usePolicyFormStoreFacade();

  const isEditable: boolean = true;

  const setPaymentTerm = (data: SupplierPolicyCloneResponse) => {
    if (data.payment_term) {
      const pt = data.payment_term;

      // Handle partial payments array - check if it exists in the response
      // The API might return it as partial_payments array or as individual fields
      let partialPayments: any[] = [];

      // Check if partial_payments exists as an array (from API response)
      if ((pt as any).partial_payments && Array.isArray((pt as any).partial_payments)) {
        // Create deep copies of partial payments to avoid reference issues
        partialPayments = (pt as any).partial_payments.map((pp: any) => ({
          partialConditionTypeId: pp.partial_condition_type_id ?? pp.partialConditionTypeId ?? null,
          partialConditionValue: pp.partial_condition_value ?? pp.partialConditionValue ?? null,
          partialAmountType: pp.partial_amount_type ?? pp.partialAmountType ?? null,
          partialAmount: pp.partial_amount ?? pp.partialAmount ?? null,
        }));
      } else if (pt.partial_payments_enabled && pt.partial_condition_type_id) {
        // If only individual fields exist, create a single partial payment entry
        partialPayments = [
          {
            partialConditionTypeId: pt.partial_condition_type_id,
            partialConditionValue: pt.partial_condition_value ?? null,
            partialAmountType: pt.partial_amount_type ?? null,
            partialAmount: pt.partial_amount ?? null,
          },
        ];
      }

      Object.assign(formState.paymentTerm, {
        paymentTypeId: pt.payment_type_id,
        conditionTypeId: pt.condition_type_id,
        conditionValue: pt.condition_value,
        partialPaymentsEnabled: pt.partial_payments_enabled ?? false,
        partialPayments: partialPayments,
        isEditable,
      });
    }
  };

  const setCancellations = (data: SupplierPolicyCloneResponse) => {
    // Clear existing cancellations first to avoid duplicates
    formState.cancellations = [];

    if (data.cancellations.length === 0) {
      addCancellation();
      return;
    }

    data.cancellations.forEach((cancellation) => {
      // Create a deep copy of the cancellation object to avoid reference issues
      formState.cancellations.push({
        timeLimitUnit: cancellation.time_limit_unit,
        timeLimitValue: cancellation.time_limit_value,
        penaltyType: cancellation.penalty_type,
        penaltyValue: cancellation.penalty_value,
        automaticReconfirmation: cancellation.automatic_reconfirmation ?? false,
        // Handle cancellation scope fields if they exist in the response
        cancellationScope:
          (cancellation as any).cancellation_scope ??
          (cancellation as any).cancellationScope ??
          'total',
        cancellationPartialValue:
          (cancellation as any).cancellation_partial_value ??
          (cancellation as any).cancellationPartialValue ??
          null,
        cancellationPartialUnit:
          (cancellation as any).cancellation_partial_unit ??
          (cancellation as any).cancellationPartialUnit ??
          null,
        isEditable,
      });

      addCancellationError();
    });
  };

  const setReconfirmations = (data: SupplierPolicyCloneResponse) => {
    // Clear existing reconfirmations first to avoid duplicates
    formState.reconfirmations = [];

    if (data.reconfirmations.length === 0) {
      addReconfirmation();
      return;
    }

    data.reconfirmations.forEach((reconfirmation) => {
      formState.reconfirmations.push({
        confirmationType: reconfirmation.confirmation_type,
        timeUnit: reconfirmation.time_unit,
        timeValue: reconfirmation.time_value,
        sendListEnabled: reconfirmation.send_list_enabled ?? false,
        listType: reconfirmation.list_type ?? null,
        listSendTimeValue: reconfirmation.list_send_time_value ?? null,
        listSendTimeUnit: reconfirmation.list_send_time_unit ?? null,
        unassignedQuota: reconfirmation.unassigned_quota ?? null,
        isEditable,
      });

      addReconfirmationError();
    });
  };

  const setReleased = (data: SupplierPolicyCloneResponse) => {
    // Clear existing released rules first to avoid duplicates
    formState.released = [];

    if (data.released.length === 0) {
      addReleased();
      return;
    }

    data.released.forEach((released) => {
      formState.released.push({
        timeLimitValue: released.time_limit_value,
        releaseType: released.release_type,
        releaseQuantity: released.release_quantity,
        benefitType: released.benefit_type ?? null,
        roomOccupancyType:
          (released as any).room_occupancy_type ?? (released as any).roomOccupancyType ?? null,
        roomBenefitType:
          (released as any).room_benefit_type ?? (released as any).roomBenefitType ?? null,
        hasMaximumCap: released.has_maximum_cap ?? false,
        maximumCapValue: released.maximum_cap_value ?? null,
        isEditable,
      });

      addReleasedError();
    });
  };

  const setChildren = (data: SupplierPolicyCloneResponse) => {
    if (data.children) {
      const child = data.children;

      // Handle inclusions - separate child and infant inclusions
      // Create deep copies to avoid reference issues
      const allInclusions = child.inclusions || [];
      const childInclusions = allInclusions
        .filter((inc: any) => (inc.applies_to_category ?? inc.appliesToCategory) === 'child')
        .map((inc: any) => ({
          id: inc.id,
          description: inc.item_code ?? inc.itemCode ?? inc.description ?? null,
          isIncluded: inc.is_included ?? inc.isIncluded ?? false,
          isVisible: inc.is_visible ?? inc.isVisible ?? false,
        }));

      const infantInclusions = allInclusions
        .filter((inc: any) => (inc.applies_to_category ?? inc.appliesToCategory) === 'infant')
        .map((inc: any) => ({
          id: inc.id,
          description: inc.item_code ?? inc.itemCode ?? inc.description ?? null,
          isIncluded: inc.is_included ?? inc.isIncluded ?? false,
          isVisible: inc.is_visible ?? inc.isVisible ?? false,
        }));

      // Determine selected age type based on which inclusions exist
      const selectedAgeType =
        childInclusions.length > 0 ? 'child' : infantInclusions.length > 0 ? 'infant' : null;

      Object.assign(formState.children, {
        infantAgeMin: child.infant_age_min ?? null,
        infantAgeMax: child.infant_age_max ?? null,
        childAgeMin: child.child_age_min ?? null,
        childAgeMax: child.child_age_max ?? null,
        additionalInformation: child.additional_information ?? null,
        inclusionsEnabled: child.inclusions_enabled ?? allInclusions.length > 0,
        selectedAgeType: selectedAgeType,
        inclusions: childInclusions,
        infantInclusions: infantInclusions,
        isEditable,
      });
    }
  };

  const setDataToClone = () => {
    const data = policyCloneResponse.value;

    if (!data) return;

    setPaymentTerm(data);
    setCancellations(data);
    setReconfirmations(data);
    setReleased(data);
    setChildren(data);
  };

  return {
    setDataToClone,
  };
}
