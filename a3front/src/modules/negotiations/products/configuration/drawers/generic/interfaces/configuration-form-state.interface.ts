import type { ProductSupplierBehaviorMode } from './product-supplier-behavior-mode.type';

export interface ConfigurationFormState {
  placeOperationIds: string[];
  supplierCategoryCodes: string[];
  isGeneral: boolean;
  type: string;
  behaviorMatrix: Record<string, ProductSupplierBehaviorMode>;
}
