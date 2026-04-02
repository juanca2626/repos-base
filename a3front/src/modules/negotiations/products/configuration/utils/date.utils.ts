export interface day {
  id: number;
  day: string;
}

export function getDays(amountDays: number): day[] {
  const days = [];
  for (let i = 0; i < amountDays; i++) {
    days.push({
      id: i + 1,
      day: 'Día ' + (i + 1),
    });
  }
  return days;
}
