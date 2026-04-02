// Filtro para búsqueda en el selector
export const filterOption = (input: string, option: any) => {
  return (
    option.label.toLowerCase().includes(input.toLowerCase()) ||
    option.value.toString().toLowerCase().includes(input.toLowerCase())
  );
};

export const mapItemsToOptions = (data: any[]) => {
  return data?.map((item: any) => ({
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
