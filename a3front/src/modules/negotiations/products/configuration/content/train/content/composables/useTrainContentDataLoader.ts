import type { Ref } from 'vue';
import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';

export const useTrainContentDataLoader = (
  textRemark: Ref<string>,
  inclusions: Ref<Inclusion[]>,
  requirements: Ref<Requirement[]>
) => {
  const trainStore = useTrainConfigurationStore();

  const loadContentData = (currentKey: string, currentCode: string): void => {
    const content = trainStore.getServiceContent(currentKey, currentCode);
    if (!content) return;

    if (content.text?.html != null && content.text.html !== '') {
      textRemark.value = content.text.html;
    }

    if (content.inclusions && content.inclusions.length > 0 && Array.isArray(content.inclusions)) {
      inclusions.value = content.inclusions.map((inc: any) => ({
        id: inc.inclusionCode ?? null,
        description: inc.inclusionCode,
        incluye: inc.included ?? false,
        visibleCliente: inc.visibleToClient ?? false,
        editMode: true,
      }));
    }

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
