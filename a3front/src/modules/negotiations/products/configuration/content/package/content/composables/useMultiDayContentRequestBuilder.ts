import type {
  BuildRequestParams,
  MultiDayContentRequest,
  ContentOperabilityItem,
  ContentOperabilityDay,
  TextItem,
} from '../interfaces';

export function useMultiDayContentRequestBuilder() {
  function buildRequest(params: BuildRequestParams): MultiDayContentRequest {
    const { groupedSchedules, textTypes } = params;

    let schedulesArray: any[] = [];

    if (groupedSchedules) {
      if ('value' in groupedSchedules) {
        schedulesArray = Array.isArray(groupedSchedules.value) ? groupedSchedules.value : [];
      } else if (Array.isArray(groupedSchedules)) {
        schedulesArray = groupedSchedules;
      }
    }

    const items: ContentOperabilityItem[] = schedulesArray
      .flatMap((groupedSchedule: any) => {
        const activities = groupedSchedule.activities ?? [];
        const byDay = new Map<number, typeof activities>();
        for (const a of activities) {
          const d = a.numberDay ?? 0;
          if (!byDay.has(d)) byDay.set(d, []);
          byDay.get(d)!.push(a);
        }
        const days: ContentOperabilityDay[] = Array.from(byDay.entries())
          .sort(([a], [b]) => a - b)
          .map(([dayNumber, dayActivities]) => ({
            dayNumber,
            activities: dayActivities
              .filter(
                (a: any) =>
                  a.duration != null &&
                  String(a.duration).trim() !== '' &&
                  a.activityId != null &&
                  a.activityId !== ''
              )
              .map((a: any) => ({
                duration: String(a.duration).trim(),
                activityCode: String(a.activityId),
                calculatedSchedule: a.calculatedSchedule ?? null,
              })),
          }))
          .filter((d) => d.activities.length > 0);

        // Crear un item por cada scheduleId en el array ids
        const scheduleIds =
          groupedSchedule.ids && groupedSchedule.ids.length > 0
            ? groupedSchedule.ids
            : groupedSchedule.id
              ? [groupedSchedule.id]
              : [];

        return scheduleIds.map((scheduleId: string | number) => ({
          scheduleId: String(scheduleId),
          days,
        }));
      })
      .filter((item) => item.days.length > 0);

    const textsPayload: TextItem[] = (textTypes ?? []).map((tt) => {
      const isItinerary = tt.textTypeCode === 'ITINERARY';
      if (isItinerary && tt.days?.length) {
        return {
          textTypeCode: tt.textTypeCode,
          status: tt.status ?? 'PENDING',
          days: tt.days.map((d) => ({
            dayNumber: d.dayNumber,
            html: d.html ?? '',
          })),
        };
      }
      const firstDayHtml = tt.days?.[0]?.html ?? '';
      return {
        textTypeCode: tt.textTypeCode,
        status: tt.status ?? 'PENDING',
        html: firstDayHtml,
      };
    });

    return {
      contentOperability: { items },
      texts: textsPayload,
    };
  }

  return { buildRequest };
}
