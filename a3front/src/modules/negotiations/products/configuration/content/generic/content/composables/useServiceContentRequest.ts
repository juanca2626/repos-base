import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import type { ServiceContentRequest } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content/request.interface';

export const useServiceContentRequest = (
  formState: any,
  groupedSchedules: any[],
  inclusions: Inclusion[],
  requirements: Requirement[]
) => {
  const buildRequest = (): ServiceContentRequest => {
    // Construir items de operatividad a partir de los horarios agrupados.
    // Por cada grupo (mismo rango horario y días consecutivos), replicamos las mismas
    // actividades válidas para cada scheduleId real contenido en group.ids.
    const contentOperabilityItems = groupedSchedules
      .flatMap((group: any) => {
        const validActivities =
          group.activities
            ?.filter(
              (activity: any) =>
                activity.duration !== null &&
                activity.duration !== undefined &&
                activity.activityId !== null &&
                activity.activityId !== undefined
            )
            .map((activity: any) => ({
              duration: activity.duration,
              activityCode: String(activity.activityId),
              calculatedSchedule: activity.calculatedSchedule ?? null,
            })) ?? [];

        if (!validActivities.length || !Array.isArray(group.ids) || !group.ids.length) {
          return [];
        }

        return group.ids.map((scheduleId: string) => ({
          scheduleId: String(scheduleId),
          activities: validActivities,
        }));
      })
      .filter((item: any) => item.activities.length > 0);

    const texts = formState.textTypes
      .filter((textType: any) => textType.html !== null && textType.html !== '')
      .map((textType: any) => {
        return {
          textTypeCode: String(textType.textTypeId),
          html: textType.html,
        };
      });

    const mappedInclusions = inclusions
      .filter(
        (inclusion: Inclusion) =>
          inclusion.description !== null && inclusion.description !== undefined
      )
      .map((inclusion: Inclusion) => {
        return {
          inclusionCode: String(inclusion.description),
          included: inclusion.incluye,
          visibleToClient: inclusion.visibleCliente,
        };
      });

    const mappedRequirements = requirements
      .filter(
        (requirement: Requirement) =>
          requirement.description !== null && requirement.description !== undefined
      )
      .map((requirement: Requirement) => {
        return {
          requirementCode: String(requirement.description),
          visibleToClient: requirement.visibleCliente,
        };
      });

    return {
      contentOperability: {
        items: contentOperabilityItems,
      },
      texts,
      inclusions: mappedInclusions,
      requirements: mappedRequirements,
    };
  };

  return {
    buildRequest,
  };
};
