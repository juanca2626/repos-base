import dayjs from 'dayjs';

export function getYearsFromTravelRange(travelFrom: string, travelTo: string): number[] {
  if (!travelFrom || !travelTo) return [];

  const start = dayjs(travelFrom);
  const end = dayjs(travelTo);

  const startYear = start.year();
  const endYear = end.year();

  const years: number[] = [];

  for (let y = startYear; y <= endYear; y++) {
    years.push(y);
  }

  return years;
}
