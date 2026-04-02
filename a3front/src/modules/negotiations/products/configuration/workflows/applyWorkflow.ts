import type { Workflow, WorkflowStepRule } from './types';
import type { ApiNavigationItem, ApiItem } from '../navigation/types';

const areDependenciesCompleted = (rule: WorkflowStepRule, items: ApiItem[]): boolean => {
  if (!rule.dependsOn || rule.dependsOn.length === 0) return true;

  const results = rule.dependsOn.map((depId) => {
    const dep = items.find((i) => i.id === depId);
    return dep?.completed === true;
  });

  return rule.mode === 'ANY' ? results.some(Boolean) : results.every(Boolean);
};

const filterSectionsByKey = (
  sections: ApiNavigationItem[],
  activeKey: string,
  activeCode: string
): ApiNavigationItem[] => {
  if (activeCode) {
    return sections.filter((section) => section.key === activeKey && section.code === activeCode);
  }
  return sections.filter((section) => section.key === activeKey);
};

const groupByCode = (sections: ApiNavigationItem[]): Map<string, ApiNavigationItem[]> => {
  const map = new Map<string, ApiNavigationItem[]>();

  sections.forEach((section) => {
    const group = map.get(section.code) ?? [];
    group.push(section);
    map.set(section.code, group);
  });

  return map;
};

const applyDisabledStateToItems = (
  items: ApiItem[],
  rules: Map<string, WorkflowStepRule>
): void => {
  items.forEach((item) => {
    const rule = rules.get(item.id);
    if (!rule) {
      item.disabled = false;
      return;
    }
    item.disabled = !areDependenciesCompleted(rule, items);
  });
};

const resolveActiveItemByCode = (
  codes: Map<string, ApiNavigationItem[]>
): { sectionKey: string; sectionCode: string; itemId: string } | null => {
  for (const sections of codes.values()) {
    const items = sections.flatMap((s) => s.items ?? []);
    const firstIncomplete = items.find((item) => !item.completed);

    if (firstIncomplete) {
      const section = sections.find((s) => s.items?.some((i) => i.id === firstIncomplete.id));

      if (!section) continue;

      return {
        sectionKey: section.key,
        sectionCode: section.code,
        itemId: firstIncomplete.id,
      };
    }
  }

  const firstSections = codes.values().next().value;
  const firstSection = firstSections?.[0];
  const firstItem = firstSection?.items?.[0];

  if (!firstSection || !firstItem) return null;

  return {
    sectionKey: firstSection.key,
    sectionCode: firstSection.code,
    itemId: firstItem.id,
  };
};

/** Aplica el workflow mutando sections in-place (disabled y active por dependencias y tab activo). */
export const applyWorkflow = (
  sections: ApiNavigationItem[],
  workflow: Workflow,
  activeCode: string,
  activeKey: string
): void => {
  const rulesMap = new Map(workflow.map((rule) => [rule.id, rule]));

  const filteredSections = filterSectionsByKey(sections, activeKey, activeCode);
  const sectionsByCode = groupByCode(filteredSections);
  const activeTarget = resolveActiveItemByCode(sectionsByCode);

  sections.forEach((section) => {
    if (section.key !== activeKey) {
      // si la section no es la activa, desactivarla
      section.active = false; // desactivar la section
      section.items?.forEach((i) => {
        i.active = false;
        i.disabled = true; // desactivar los items
      });
      return;
    }

    const items = section.items ?? [];
    applyDisabledStateToItems(items, rulesMap);

    let sectionHasActive = false;
    items.forEach((item) => {
      const isActive =
        activeTarget?.itemId === item.id &&
        activeTarget.sectionKey === section.key &&
        activeTarget.sectionCode === section.code;
      item.active = isActive;
      if (isActive) sectionHasActive = true;
    });
    section.active = sectionHasActive;
  });
};
