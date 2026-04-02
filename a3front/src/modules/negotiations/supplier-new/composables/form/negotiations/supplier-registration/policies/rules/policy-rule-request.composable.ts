import type { SupplierPolicyRuleForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';

export function usePolicyRuleRequestComposable(formState: SupplierPolicyRuleForm) {
  const buildReconfirmationsRequest = () => {
    // Si "No aplica" está marcado, enabled es false y rules vacío
    if (formState.reconfirmationsNotApplicable) {
      return {
        enabled: false,
        rules: [],
      };
    }

    const rules = formState.reconfirmations.map((reconfirmation) => {
      // Build passengerList details based on listType
      let passengerListDetails = null;
      if (reconfirmation.sendListEnabled && reconfirmation.listType) {
        if (reconfirmation.listType === 'preliminary') {
          passengerListDetails = {
            type: 'preliminary',
            deadline: {
              value: reconfirmation.listSendTimeValue ?? undefined,
              unit: reconfirmation.listSendTimeUnit ?? undefined,
            },
            unassignedQuota: reconfirmation.unassignedQuota ?? undefined,
          };
        } else if (reconfirmation.listType === 'final') {
          passengerListDetails = {
            type: 'final',
            deadline: {
              value: reconfirmation.listSendTimeValue ?? undefined,
              unit: reconfirmation.listSendTimeUnit ?? undefined,
            },
          };
        }
      }

      return {
        type: reconfirmation.confirmationType ?? undefined,
        deadline: {
          value: reconfirmation.timeValue ?? undefined,
          unit: reconfirmation.timeUnit ?? undefined,
        },
        passengerList: {
          enabled: reconfirmation.sendListEnabled ?? false,
          details: passengerListDetails,
        },
      };
    });

    return {
      enabled: true,
      rules: rules,
    };
  };

  const buildReleasedRequest = () => {
    // Si "No aplica" está marcado, enabled es false y rules vacío
    if (formState.releasedNotApplicable) {
      return {
        enabled: false,
        rules: [],
      };
    }

    const rules = formState.released.map((released) => ({
      time_limit_value: released.timeLimitValue ?? undefined,
      release_type: released.releaseType ?? undefined,
      release_quantity: released.releaseQuantity ?? undefined,
      has_maximum_cap: released.hasMaximumCap ?? false,
      maximum_cap_value: released.hasMaximumCap
        ? (released.maximumCapValue ?? undefined)
        : undefined,
    }));

    return {
      enabled: true,
      rules: rules,
    };
  };

  const buildPaymentTermRequest = () => {
    // Build partial payments array from all items
    const partialPayments = formState.paymentTerm.partialPayments.map((partial) => ({
      id: partial.id ?? undefined,
      partial_condition_type_id: partial.partialConditionTypeId ?? undefined,
      // partial_condition_value solo es requerido si partial_condition_type_id != 3 (at_booking)
      partial_condition_value:
        partial.partialConditionTypeId !== 3
          ? (partial.partialConditionValue ?? undefined)
          : undefined,
      partial_amount_type: partial.partialAmountType ?? undefined,
      partial_amount: partial.partialAmount ?? undefined,
    }));

    return {
      id: formState.paymentTerm.id ?? undefined,
      payment_type_id: formState.paymentTerm.paymentTypeId ?? undefined,
      condition_type_id: formState.paymentTerm.conditionTypeId ?? undefined,
      condition_value: formState.paymentTerm.conditionValue ?? undefined,
      partial_payments_enabled: formState.paymentTerm.partialPaymentsEnabled,
      partial_payments: formState.paymentTerm.partialPaymentsEnabled ? partialPayments : [],
    };
  };

  const buildChildrenRequest = () => {
    // Combinar inclusiones de niños e infantes en un solo array con campo type
    const childInclusions = formState.children.inclusions.map((inc) => ({
      id: inc.id ?? undefined,
      description: inc.description ?? undefined,
      isIncluded: inc.isIncluded,
      isVisible: inc.isVisible,
      type: 'child' as const,
    }));

    const infantInclusionsMapped = formState.children.infantInclusions.map((inc) => ({
      id: inc.id ?? undefined,
      description: inc.description ?? undefined,
      isIncluded: inc.isIncluded,
      isVisible: inc.isVisible,
      type: 'infant' as const,
    }));

    // Combinar ambos arrays
    const allInclusions = [...childInclusions, ...infantInclusionsMapped];

    return {
      id: formState.children.id ?? undefined,
      infantAgeMin: formState.children.infantAgeMin ?? undefined,
      infantAgeMax: formState.children.infantAgeMax ?? undefined,
      childAgeMin: formState.children.childAgeMin ?? undefined,
      childAgeMax: formState.children.childAgeMax ?? undefined,
      additionalInformation: formState.children.additionalInformation || undefined,
      inclusionsEnabled: formState.children.inclusionsEnabled,
      inclusions: allInclusions,
    };
  };

  const buildCancellationsRequest = () => {
    const rules = formState.cancellations.map((cancellation) => {
      // Determinar el tipo de penalidad
      const penaltyUnit =
        cancellation.penaltyType === ValueTypeEnum.PERCENTAGE
          ? 'percent'
          : (cancellation.penaltyType ?? 'amount');

      // Construir scope según el tipo de cancelación
      let scope: { type: string; quantity?: number; unit?: string } | undefined;
      if (cancellation.cancellationScope === 'partial') {
        scope = {
          type: 'partial',
          quantity: cancellation.cancellationPartialValue ?? undefined,
          unit: cancellation.cancellationPartialUnit ?? undefined,
        };
      } else {
        scope = {
          type: cancellation.cancellationScope ?? 'total',
        };
      }

      return {
        deadline: {
          value: cancellation.timeLimitValue ?? undefined,
          unit: cancellation.timeLimitUnit ?? undefined,
        },
        penalty: {
          value: cancellation.penaltyValue ?? undefined,
          unit: penaltyUnit,
        },
        scope: scope,
      };
    });

    // Assuming automaticReconfirmation is global or taking from the first one if rules exist
    const automaticReconfirmation =
      formState.cancellations.length > 0
        ? formState.cancellations[0].automaticReconfirmation
        : false;

    return {
      automaticReconfirmation: automaticReconfirmation ?? false,
      rules: rules,
    };
  };

  const buildAdditionalRulesRequest = () => {
    return formState.additionalRules.map((rule) => ({
      ruleCode: rule.ruleCode || rule.policyName?.toLowerCase().replace(/\s+/g, '_') || '',
      isActive: rule.isActive ?? true,
      contentHtml: rule.contentHtml || rule.additionalInformation || '',
    }));
  };

  const buildRequestData = () => {
    const cancellation = buildCancellationsRequest();
    const automaticReconfirmation = cancellation.automaticReconfirmation;

    // Si automaticReconfirmation es true, reconfirmations se ignora (enabled: false)
    const reconfirmationData = automaticReconfirmation
      ? { enabled: false, rules: [] }
      : buildReconfirmationsRequest();

    const releasedData = buildReleasedRequest();
    const paymentData = buildPaymentTermRequest();
    const childrenData = buildChildrenRequest();
    const additionalRulesData = buildAdditionalRulesRequest();

    return {
      supplier_policy_id: formState.supplierPolicyId,
      payment: {
        enabled: true,
        ...paymentData,
      },
      cancellation: {
        enabled: true,
        ...cancellation,
      },
      reconfirmation: reconfirmationData,
      released: releasedData,
      children: {
        enabled: true,
        ...childrenData,
      },
      additionalRules: {
        enabled: true,
        rules: additionalRulesData,
      },
    };
  };

  return {
    buildRequestData,
  };
}
