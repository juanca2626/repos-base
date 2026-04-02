import type { Dayjs } from 'dayjs';

export interface TrainServiceFormState {
  serviceName: string;
  startPoint: string | undefined;
  endPoint: string | undefined;
  status: string | undefined;
  showToClient: boolean | undefined;
  reason: string;
}

export interface ValidityPeriod {
  startDate: Dayjs | null;
  endDate: Dayjs | null;
}

export interface TrainSchedule {
  id: string;
  frequency: string;
  fareType: string;
  startTime: string;
  endTime: string;
  duration: string;
  daysOfWeek: string[];
}

export interface DayOfWeek {
  label: string;
  value: string;
  selected: boolean;
}
