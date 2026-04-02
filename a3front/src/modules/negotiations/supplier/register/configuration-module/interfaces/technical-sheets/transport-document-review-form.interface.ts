import type { Ref } from 'vue';
import { ReviewEntityEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/review-entity-enum';
import type {
  BaseTransportDocumentForm,
  BaseTransportVehiclePhotoForm,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface ProcessReviewInputs {
  reviewEntity: ReviewEntityEnum;
  formState: BaseTransportDocumentForm | BaseTransportVehiclePhotoForm;
  isLoading: Ref<boolean>;
  handleClose: () => void;
}
