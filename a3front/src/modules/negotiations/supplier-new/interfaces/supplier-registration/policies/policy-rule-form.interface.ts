import type { ConfirmationTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/confirmation-type.enum';
import type { ReleaseTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/release-type.enum';
import type { TimeUnitEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/time-unit.enum';
import type { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';
import type { CancellationPenaltyScopeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/cancellation-penalty-scope.enum';

export interface AdditionalRuleForm {
  id?: number;
  policyId: number;
  policyName: string;
  ruleCode: string;
  isActive: boolean;
  contentHtml: string;
  additionalInformation: string;
  isEditable: boolean;
}

export interface SupplierPolicyRuleForm {
  supplierPolicyId: string | null; // MongoDB ObjectId
  paymentTerm: SupplierPolicyPaymentTermForm;
  cancellations: SupplierPolicyCancellationForm[];
  reconfirmations: SupplierPolicyReconfirmationForm[];
  reconfirmationsNotApplicable: boolean; // Checkbox "No aplica" para reconfirmaciones
  released: SupplierPolicyReleasedForm[];
  releasedNotApplicable: boolean; // Checkbox "No aplica" para liberados
  children: SupplierPolicyChildrenForm;
  additionalRules: AdditionalRuleForm[];
}

import type { SupplierPolicyPartialPaymentForm } from './partial-payment-form.interface';

export interface SupplierPolicyPaymentTermForm {
  id?: number;
  paymentTypeId: number | null;
  paymentTypeName?: string;
  conditionTypeId: number | null;
  conditionTypeName?: string;
  conditionValue: number | null;
  partialPaymentsEnabled: boolean;
  partialPayments: SupplierPolicyPartialPaymentForm[];
  isEditable: boolean;
}

export interface SupplierPolicyCancellationForm {
  id?: number;
  timeLimitUnit: TimeUnitEnum | null;
  timeLimitValue: number | null;
  penaltyType: ValueTypeEnum | null;
  penaltyValue: number | null;
  penaltyScope?: CancellationPenaltyScopeEnum | null; // Solo para alojamiento: Noches o Habitaciones
  automaticReconfirmation?: boolean;
  cancellationScope?: string | null;
  cancellationPartialValue?: number | null;
  cancellationPartialUnit?: string | null;
  isEditable: boolean;
}

export interface SupplierPolicyReconfirmationForm {
  id?: number;
  confirmationType: ConfirmationTypeEnum | null;
  timeUnit: TimeUnitEnum | null;
  timeValue: number | null;
  sendListEnabled?: boolean;
  listType?: 'preliminary' | 'final' | null;
  listSendTimeValue?: number | null;
  listSendTimeUnit?: TimeUnitEnum | null;
  unassignedQuota?: number | null;
  isEditable: boolean;
}

export interface SupplierPolicyReleasedForm {
  id?: number;
  timeLimitValue: number | null;
  releaseType: ReleaseTypeEnum | null;
  releaseQuantity: number | null;
  benefitType: string | null;
  // Campos adicionales para alojamiento cuando releaseType es ROOM (Habitaciones)
  roomOccupancyType?: string | null; // Tipo de ocupación de habitación
  roomBenefitType?: string | null; // Tipo de beneficio específico de habitación
  breakfastIncluded?: boolean | null; // Desayuno incluido
  hasMaximumCap: boolean;
  maximumCapValue: number | null;
  isEditable: boolean;
}

export interface SupplierPolicyChildrenForm {
  id?: number;
  infantAgeMin: number | null;
  infantAgeMax: number | null;
  childAgeMin: number | null;
  childAgeMax: number | null;
  additionalInformation: string | null;
  inclusionsEnabled: boolean;
  selectedAgeType: 'child' | 'infant' | null;
  inclusions: ChildrenInclusionForm[];
  infantInclusions: ChildrenInclusionForm[];
  isEditable: boolean;
}

export interface ChildrenInclusionForm {
  id?: number;
  description: string | null;
  isIncluded: boolean;
  isVisible: boolean;
}

export interface FormFieldError {
  isInvalid: boolean;
  message: string;
}

export interface PolicyRuleCloneActions {
  addCancellation: () => void;
  addReconfirmation: () => void;
  addReleased: () => void;
  addCancellationError: () => void;
  addReconfirmationError: () => void;
  addReleasedError: () => void;
}
