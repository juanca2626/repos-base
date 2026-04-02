import type { ConfirmationTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/confirmation-type.enum';
import type { ReleaseTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/release-type.enum';
import type { TimeUnitEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/time-unit.enum';
import type { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';

export interface PaymentType {
  id: number;
  name: string;
}

export interface ConditionType {
  id: number;
  name: string;
}

export interface PaymentTermBaseResponse {
  payment_type_id: number;
  condition_type_id: number;
  condition_value: number;
  partial_payments_enabled: boolean;
  partial_condition_type_id: number;
  partial_condition_value: number;
  partial_amount_type: ValueTypeEnum;
  partial_amount: number;
}

export interface PaymentTermResponse extends PaymentTermBaseResponse {
  id: number;
  supplier_policy_id: number;
  payment_type: PaymentType;
  condition_type: ConditionType;
  partial_condition_type: ConditionType | null;
}

export interface CancellationBaseResponse {
  time_limit_unit: TimeUnitEnum;
  time_limit_value: number;
  penalty_type: ValueTypeEnum;
  penalty_value: number;
  automatic_reconfirmation?: boolean;
}

export interface CancellationResponse extends CancellationBaseResponse {
  id: number;
  supplier_policy_id: number;
}

export interface ReconfirmationBaseResponse {
  confirmation_type: ConfirmationTypeEnum;
  time_unit: TimeUnitEnum;
  time_value: number;
  send_list_enabled?: boolean;
  list_type?: 'preliminary' | 'final' | null;
  list_send_time_value?: number | null;
  list_send_time_unit?: TimeUnitEnum | null;
  unassigned_quota?: number | null;
}

export interface ReconfirmationResponse extends ReconfirmationBaseResponse {
  id: number;
  supplier_policy_id: number;
}

export interface ReleasedBaseResponse {
  time_limit_value: number;
  release_type: ReleaseTypeEnum;
  release_quantity: number;
  benefit_type?: string | null;
  has_maximum_cap?: boolean;
  maximum_cap_value?: number | null;
}

export interface ReleasedResponse extends ReleasedBaseResponse {
  id: number;
  supplier_policy_id: number;
}

export interface ChildrenInclusionResponse {
  id: number;
  description: string;
  is_included: boolean;
  is_visible: boolean;
}

export interface ChildrenBaseResponse {
  infant_age_min: number;
  infant_age_max: number;
  child_age_min: number;
  child_age_max: number;
  additional_information: string | null;
  inclusions_enabled?: boolean;
  inclusions?: ChildrenInclusionResponse[];
}

export interface ChildrenResponse extends ChildrenBaseResponse {
  id: number;
  supplier_policy_id: number;
}

export interface SupplierPolicyRuleResponse {
  supplier_policy_id: number;
  payment_term: PaymentTermResponse | null;
  cancellations: CancellationResponse[];
  reconfirmations: ReconfirmationResponse[];
  released: ReleasedResponse[];
  children: ChildrenResponse | null;
}
