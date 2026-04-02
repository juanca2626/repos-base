import type { GenericContentModel } from './models/genericContent.model';
import type { PackageContentModel } from './models/packageContent.model';
import type { TrainContentModel } from './models/trainContent.model';

export interface MarketingContentStrategy {
  (data: any): GenericContentModel | PackageContentModel | TrainContentModel;
}
