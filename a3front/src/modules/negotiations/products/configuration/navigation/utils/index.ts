import type { ApiNavigationItem } from '../types';

export const findActiveSectionWithSourceItem = (
  sections: ApiNavigationItem[],
  key: string
): ApiNavigationItem | undefined =>
  sections.find((s) => s.key === key && s.active && s.source === 'item');

export const findSectionWithActiveItem = (
  sections: ApiNavigationItem[],
  key: string
): ApiNavigationItem | undefined =>
  sections.find(
    (section) =>
      section.key === key &&
      Array.isArray(section.items) &&
      section.items.some((item) => item.active)
  );

export const findActiveSection = (
  sections: ApiNavigationItem[],
  key: string
): ApiNavigationItem | undefined => sections.find((s) => s.key === key && s.active);

export const formatSectionTitle = (section: ApiNavigationItem): string => {
  if (SectionIsPricingPlan(section)) return '';

  const sectionTitle = section.title ?? '';
  return section.source === 'section' ? `Servicio ${sectionTitle.toLowerCase()}` : sectionTitle;
};

export const sectionHasItems = (section: ApiNavigationItem): boolean =>
  Array.isArray(section.items) && section.items.length > 0;

export const SectionIsPricingPlan = (section: ApiNavigationItem): boolean => {
  let item = section.items?.find((item) => item.id === 'pricing-plans' && item.active);
  return !!item;
};
