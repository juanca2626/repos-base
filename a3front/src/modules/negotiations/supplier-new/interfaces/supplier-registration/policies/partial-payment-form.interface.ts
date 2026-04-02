import type { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';

export interface SupplierPolicyPartialPaymentForm {
  id?: number;
  _uid?: string;
  partialConditionTypeId: number | null;
  partialConditionValue: number | null;
  partialAmountType: ValueTypeEnum | string | null;
  partialAmount: number | null;
}
