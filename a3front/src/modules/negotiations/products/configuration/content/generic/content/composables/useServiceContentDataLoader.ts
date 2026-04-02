import type { Ref } from 'vue';
import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';

export const useServiceContentDataLoader = (
  formState: { textTypeId: any[]; textTypes: any[]; schedules: any[] },
  inclusions: Ref<Inclusion[]>,
  requirements: Ref<Requirement[]>
) => {
  const genericStore = useGenericConfigurationStore();

  const normalizeTextTypeCode = (code: string): string => (code === 'REMARKS' ? 'REMARK' : code);

  const loadContentData = (currentKey: string, currentCode: string): void => {
    const content = genericStore.getServiceContent(currentKey, currentCode);
    if (!content) return;
    // Texts
    if (content.texts && content.texts.length > 0 && Array.isArray(content.texts)) {
      formState.textTypeId = content.texts.map((t: any) => normalizeTextTypeCode(t.textTypeCode));
      formState.textTypes = content.texts.map((t: any) => ({
        textTypeId: normalizeTextTypeCode(t.textTypeCode),
        html: t.html ?? '',
        status: t.status || 'PENDING',
      }));
    }

    // Content operability: mergear en los schedules existentes (por scheduleId)
    if (
      content.contentOperability?.items &&
      content.contentOperability.items.length > 0 &&
      formState.schedules?.length
    ) {
      const items = content.contentOperability.items;
      items.forEach((item: any) => {
        const schedule = formState.schedules.find(
          (s: any) => String(s.id) === String(item.scheduleId)
        );
        if (schedule && item.activities?.length) {
          schedule.activities = item.activities.map((a: any) => ({
            day: schedule.day,
            duration: a.duration ?? null,
            activityId: a.activityCode ?? null,
            calculatedSchedule: a.calculatedSchedule ?? null,
          }));
        }
      });
    }

    // Inclusions
    if (content.inclusions && content.inclusions.length > 0 && Array.isArray(content.inclusions)) {
      inclusions.value = content.inclusions.map((inc: any) => ({
        id: inc.inclusionCode ?? null,
        description: inc.inclusionCode,
        incluye: inc.included ?? false,
        visibleCliente: inc.visibleToClient ?? false,
        editMode: true,
      }));
    }

    // Requirements
    if (
      content.requirements &&
      content.requirements.length > 0 &&
      Array.isArray(content.requirements)
    ) {
      requirements.value = content.requirements.map((req: any) => ({
        id: null,
        description: req.requirementCode,
        visibleCliente: req.visibleToClient ?? false,
        editMode: false,
      }));
    }
  };

  return { loadContentData };
};
