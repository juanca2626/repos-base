import { computed } from 'vue';
import { getDays } from '@/modules/negotiations/products/configuration/utils/date.utils';
import type { SelectOptionTextType } from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import type { TextTypeDay, ContentTextType, FormState } from '../interfaces';

export interface UseMultiDayContentTextsOptions {
  formState: FormState;
  supplierTextTypes: any[];
  programDurationAmountDays: number;
}

export const useMultiDayContentTexts = (opts: UseMultiDayContentTextsOptions) => {
  const { formState, supplierTextTypes, programDurationAmountDays } = opts;

  const textTypes = computed<SelectOptionTextType[]>(() => {
    const textTypesList = supplierTextTypes;
    if (!textTypesList || textTypesList.length === 0) {
      return [];
    }

    return textTypesList.map((textType) => ({
      label: textType.name,
      value: textType.code,
      contentLength: textType.contentLength,
    }));
  });

  const getLabelFromTextTypes = (value: number | string) => {
    return textTypes.value.find((textType) => textType.value === value)?.label;
  };

  const getTextTypeHtml = (textTypeCode: string, dayNumber?: number): string => {
    const textType = formState.textTypes.find((tt) => tt.textTypeCode === textTypeCode);
    if (!textType?.days?.length) return '';
    if (dayNumber != null) {
      const dayData = textType.days.find((day) => day.dayNumber === dayNumber);
      return dayData?.html ?? '';
    }
    const firstDay = textType.days.slice().sort((a, b) => a.dayNumber - b.dayNumber)[0];
    return firstDay?.html ?? '';
  };

  const getTextTypeExcerpt = (textTypeCode: string, maxLength = 120): string => {
    const html = getTextTypeHtml(textTypeCode);
    if (!html) return '';
    const div = document.createElement('div');
    div.innerHTML = html;
    const text = (div.textContent ?? '').trim();
    return text.length <= maxLength ? text : `${text.slice(0, maxLength)}...`;
  };

  const isSelectedTextType = (value: number | string): boolean => {
    return formState.textTypeId.includes(value);
  };

  const getTextTypeStatus = (textTypeCode: string): string | undefined => {
    const textType = formState.textTypes.find((tt) => tt.textTypeCode === textTypeCode);
    return textType?.status;
  };

  const getTextTypeDays = (textTypeCode: string): { dayNumber: number; html: string }[] => {
    const textType = formState.textTypes.find((tt) => tt.textTypeCode === textTypeCode);
    if (!textType?.days?.length) return [];
    return textType.days
      .slice()
      .sort((a, b) => a.dayNumber - b.dayNumber)
      .map((d) => ({ dayNumber: d.dayNumber, html: d.html ?? '' }));
  };

  const updateTextTypeHtml = (textTypeCode: string, dayNumber: number, html: string) => {
    let textType = formState.textTypes.find((tt) => tt.textTypeCode === textTypeCode) as
      | ContentTextType
      | undefined;

    if (!textType) {
      textType = {
        textTypeCode,
        status: 'PENDING',
        days: [],
      };
      formState.textTypes.push(textType);
    }

    if (!textType.days) textType.days = [];

    let dayData = textType.days.find((day) => day.dayNumber === dayNumber);
    if (!dayData) {
      dayData = { dayNumber, html };
      textType.days.push(dayData);
    } else {
      dayData.html = html;
    }
  };

  const buildDaysForTextType = (
    textTypeCode: string,
    amountDays: number,
    existing: ContentTextType | undefined
  ): TextTypeDay[] => {
    if (textTypeCode === 'ITINERARY') {
      return Array.from({ length: amountDays }, (_, i) => {
        const dayNumber = i + 1;
        const day = existing?.days?.find((d) => d.dayNumber === dayNumber);
        return { dayNumber, html: day?.html ?? '' };
      });
    }
    // Los que no son ITINERARY no tienen días; solo preservar un bloque si ya existía
    const existingBlock = existing?.days?.slice().sort((a, b) => a.dayNumber - b.dayNumber)[0];
    return existingBlock ? [{ dayNumber: 1, html: existingBlock.html ?? '' }] : [];
  };

  const handleChangeTextType = () => {
    const selectedCodes = (formState.textTypeId ?? []) as (string | number)[];
    const existingByCode = new Map(
      formState.textTypes.map((tt) => [tt.textTypeCode, tt as ContentTextType])
    );
    const amountDays = Math.max(programDurationAmountDays ?? 0, formState.days?.length ?? 0);

    formState.textTypes = selectedCodes.map((code) => {
      const textTypeCode = String(code);
      const existing = existingByCode.get(textTypeCode);
      return {
        textTypeCode,
        status: existing?.status ?? 'PENDING',
        days: buildDaysForTextType(textTypeCode, amountDays, existing),
      };
    });
  };

  const loadDays = () => {
    const days = getDays(programDurationAmountDays);
    formState.days = structuredClone(days);
  };

  return {
    // Computed
    textTypes,
    // Helpers
    getLabelFromTextTypes,
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    getTextTypeDays,
    isSelectedTextType,
    // Handlers
    updateTextTypeHtml,
    handleChangeTextType,
    // Inicialización
    loadDays,
  };
};
