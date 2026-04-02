import type { GenericLoadingDto } from '@/modules/negotiations/products/configuration/infrastructure/content/dtos/loading/genericLoading.dto';
import type { GenericContentModel } from '../models/genericContent.model';

export function genericLoadingStrategy(dto: GenericLoadingDto): GenericContentModel {
  return {
    texts: (dto.texts ?? []).map((text) => ({
      textTypeCode: text.textTypeCode,
      html: text.html,
      status: text.status,
    })),

    inclusions: (dto.inclusions ?? []).map((inclusion) => ({
      inclusionCode: inclusion.inclusionCode,
      included: inclusion.included,
      visibleToClient: inclusion.visibleToClient,
    })),

    requirements: (dto.requirements ?? []).map((req) => ({
      requirementCode: req.requirementCode,
      visibleToClient: req.visibleToClient,
    })),

    contentOperability: {
      items: (dto.contentOperability?.items ?? []).map((item) => ({
        scheduleId: item.scheduleId,
        activities: (item.activities ?? []).map((activity) => ({
          activityCode: activity.activityCode,
          duration: activity.duration,
          durationInMinutes: activity.durationInMinutes,
          calculatedTime: activity.calculatedTime,
        })),
        totalDuration: item.totalDuration,
      })),
    },
  };
}
