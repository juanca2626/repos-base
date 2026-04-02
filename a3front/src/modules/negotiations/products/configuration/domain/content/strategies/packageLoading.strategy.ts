import type { PackageLoadingDto } from '@/modules/negotiations/products/configuration/infrastructure/content/dtos/loading/packageLoading.dto';
import type { PackageContentModel } from '../models/packageContent.model';

export function packageLoadingStrategy(dto: PackageLoadingDto): PackageContentModel {
  return {
    texts: (dto.texts ?? []).map((text) => ({
      textTypeCode: text.textTypeCode,
      html: text.html,
      status: text.status,
      days:
        text.days?.map((day) => ({
          dayNumber: day.dayNumber,
          html: day.html,
        })) ?? [],
    })),

    contentOperability: {
      items: (dto.contentOperability?.items ?? []).map((item) => ({
        scheduleId: item.scheduleId,
        days: (item.days ?? []).map((day) => ({
          dayNumber: day.dayNumber,
          activities: (day.activities ?? []).map((activity) => ({
            activityCode: activity.activityCode,
            duration: activity.duration,
            durationInMinutes: activity.durationInMinutes,
            calculatedTime: activity.calculatedTime,
          })),
        })),
      })),
    },
  };
}
