import type { TrainContentModel } from '../models/trainContent.model';
import type { TrainLoadingDto } from '@/modules/negotiations/products/configuration/infrastructure/content/dtos/loading/trainLoading.dto';

export function trainLoadingStrategy(dto: TrainLoadingDto): TrainContentModel {
  return {
    text: {
      textTypeCode: dto.text?.textTypeCode ?? '',
      html: dto.text?.html ?? '',
      status: dto.text?.status ?? '',
    },

    inclusions: (dto.inclusions ?? []).map((inclusion) => ({
      inclusionCode: inclusion.inclusionCode,
      included: inclusion.included,
      visibleToClient: inclusion.visibleToClient,
    })),

    requirements: (dto.requirements ?? []).map((req) => ({
      requirementCode: req.requirementCode,
      visibleToClient: req.visibleToClient,
    })),
  };
}
