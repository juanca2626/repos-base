import type { EnginePeriod } from '../types/period.types';
import dayjs from 'dayjs';

export function formatPeriodDisplay(period: EnginePeriod): string {
  if (period.type === 'range') {
    const from = dayjs(period.rangeFrom).format('MMM DD');
    const to = dayjs(period.rangeTo).format('MMM DD');
    return `${from} - ${to}`;
  }
  if (period.type === 'days') return period.days?.join('-') ?? '';
  if (period.type === 'dayOfMonth') return dayjs(period.date).format('MMM DD');
  return '';
}
