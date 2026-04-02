import type { BackendCompoundSidebarResponse } from '../sidebar.backend.types';
import type {
  CompoundSidebarModel,
  CompoundSidebarNode,
  CompoundSidebarItem,
} from '../sidebar.types';

export function baseCompoundSidebarStrategy(
  data: BackendCompoundSidebarResponse
): CompoundSidebarModel {
  const sections: CompoundSidebarNode[] = data.sections.map((section) => {
    const hasItems = section.subSections?.length > 0;
    const isCompleted = section.status === 'COMPLETED';

    const items: CompoundSidebarItem[] =
      section.subSections?.map((item) => {
        const itemCompleted = item.status === 'COMPLETED';

        return {
          id: item.code,
          label: item.name,
          editing: !itemCompleted,
          completed: itemCompleted,
          active: false,
          icon: null,
          disabled: false,
          source: 'item',
          status: item.status,
        };
      }) ?? [];

    return {
      code: section.code,
      title: section.name,
      icon: null,
      active: false,
      editing: !isCompleted,
      completed: isCompleted,
      disabled: false,
      source: hasItems ? 'section' : 'item',
      status: section.status,
      items,
    };
  });

  return {
    totalProgress: data.totalProgress,
    sections,
  };
}
