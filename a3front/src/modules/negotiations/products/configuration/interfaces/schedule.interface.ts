export interface Schedule {
  id: number | null;
  open: string;
  close: string;
  twenty_four_hours?: boolean;
  single_time?: boolean;
}

export interface ScheduleDay {
  label: string;
  available_day: boolean;
  schedules: Schedule[];
  twenty_four_hours: boolean;
  single_time: boolean;
}
