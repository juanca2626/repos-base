export const createStatusOptions = (statusData: Record<number, string>) => [
  { value: null, label: 'Todos' },
  ...Object.entries(statusData).map(([key, label]) => ({
    value: Number(key),
    label,
  })),
];
