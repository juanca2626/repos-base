import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import type { FormState } from '../interfaces';

export const useMultiDayContentDataLoader = (formState: FormState) => {
  const packageStore = usePackageConfigurationStore();

  const loadContentData = (programDurationCode: string, operationalSeasonCode: string): void => {
    const content = packageStore.getServiceContent(programDurationCode, operationalSeasonCode);
    if (!content) return;

    // Texts: ITINERARY → { textTypeCode, status, days }; otros → { textTypeCode, status, html }
    if (content.texts && Array.isArray(content.texts)) {
      formState.textTypeId = content.texts.map((t: any) => t.textTypeCode);
      formState.textTypes = content.texts.map((t: any) => {
        const hasDays = Array.isArray(t.days) && t.days.length > 0;
        const days = hasDays
          ? (t.days as any[]).map((d: any) => ({
              dayNumber: d.dayNumber,
              html: d.html ?? '',
            }))
          : [{ dayNumber: 1, html: t.html ?? '' }];
        return {
          textTypeCode: t.textTypeCode,
          status: t.status || 'PENDING',
          days,
        };
      });
    }

    // Content operability: mergear en los schedules existentes (por scheduleId)
    if (content.contentOperability?.items && formState.schedules?.length) {
      const items = content.contentOperability.items;
      items.forEach((item: any) => {
        const schedule = formState.schedules.find(
          (s: any) => String(s.id) === String(item.scheduleId)
        );
        if (!schedule || !item.days?.length) return;

        const activities: any[] = [];
        item.days.forEach((dayBlock: any) => {
          const dayNumber = dayBlock.dayNumber ?? 0;
          (dayBlock.activities ?? []).forEach((a: any) => {
            activities.push({
              numberDay: dayNumber,
              day: schedule.day,
              duration: a.duration ?? null,
              activityId: a.activityCode ?? null,
              calculatedSchedule: a.calculatedSchedule ?? null,
            });
          });
        });
        schedule.activities = activities;
      });
    }
  };

  return { loadContentData };
};
