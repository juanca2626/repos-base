import type {
  CancellationBaseResponse,
  ChildrenBaseResponse,
  PaymentTermBaseResponse,
  ReconfirmationBaseResponse,
  ReleasedBaseResponse,
  SupplierPolicyBaseResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export interface PolicySegmentationCloneResponse {
  segmentation_id: number;
  specification_name: string | null;
  object_ids: number[];
}

export interface SupplierPolicyCloneResponse extends SupplierPolicyBaseResponse {
  id: number;
  business_group_id: number;
  name: string;
  date_from: string;
  date_to: string;
  pax_min: number;
  pax_max: number;
  policy_segmentations: PolicySegmentationCloneResponse[];
  payment_term: PaymentTermBaseResponse | null;
  cancellations: CancellationBaseResponse[];
  reconfirmations: ReconfirmationBaseResponse[];
  released: ReleasedBaseResponse[];
  children: ChildrenBaseResponse | null;
}
