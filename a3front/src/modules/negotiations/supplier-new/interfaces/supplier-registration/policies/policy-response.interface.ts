export interface SegmentationSpecificationResponse {
  id: number;
  object_id: number | null;
  name: string | null;
}

export interface SegmentationResponse {
  id: number;
  name: string;
}
export interface SupplierPolicySegmentationResponse {
  id: number;
  segmentation: SegmentationResponse;
  segmentation_specifications: SegmentationSpecificationResponse[];
}

export interface BusinessGroup {
  id: number;
  name: string;
}

export interface SupplierPolicyBaseResponse {
  id: number;
  name: string;
  date_from: string;
  date_to: string;
  pax_min: number;
  pax_max: number;
  measurement_unit: string | null;
}

export interface SupplierPolicyResponse extends SupplierPolicyBaseResponse {
  supplier_id: number;
  business_group: BusinessGroup;
  status: boolean;
  policy_segmentations: SupplierPolicySegmentationResponse[];
}

export interface ClientResponse {
  id: number;
  code: string;
  name: string;
}
