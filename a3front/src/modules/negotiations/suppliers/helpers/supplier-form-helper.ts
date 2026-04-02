// Filtro para búsqueda en el selector
export const filterOption = (input: string, option: any) => {
  return (
    option.label.toLowerCase().includes(input.toLowerCase()) ||
    option.value.toString().toLowerCase().includes(input.toLowerCase())
  );
};

export const filterOptionByName = (input: string, option: any) => {
  return option.name?.toLowerCase().includes(input.toLowerCase());
};

export const mapItemsToOptions = (data: any[]) => {
  return data.map((item: any) => ({
    label: item.name,
    value: item.id,
  }));
};

export const toSnakeCase = (key: string): string => {
  return key.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
};

export const sleep = (ms: number) => {
  return new Promise((resolve) => setTimeout(resolve, ms));
};

export const formatRegisteredText = (count: number, singular: string, plural: string) => {
  return `${count} ${count === 1 ? singular : plural}`;
};

export const toLowerText = (label?: string): string => {
  if (!label) return '';
  return label.trim().toLowerCase();
};

export const pluralizeText = (value: number, singular: string, plural: string): string => {
  return `${value} ${value === 1 ? singular : plural}`;
};

export const applyRegularPlural = (word: string, count: number): string => {
  return `${word}${count === 1 ? '' : 's'}`;
};
