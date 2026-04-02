import type {
  Schedule,
  ScheduleDay,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';
import type { ValidityPeriod } from '@/modules/negotiations/products/configuration/content/train/serviceDetails/interfaces/train-service.interface';
import type { TrainSchedule } from '@/modules/negotiations/products/configuration/content/train/serviceDetails/interfaces/train-service.interface';

// Tipo para la clave de schedule (currentKey-currentCode)
export type ScheduleKey = string;

// Interfaz para los datos de schedule guardados
export interface ScheduleData {
  scheduleType: number;
  scheduleGeneral: Schedule[];
  schedule: ScheduleDay[];
}

// Interfaz para los datos de train schedule guardados
export interface TrainScheduleData {
  schedules: TrainSchedule[];
  validityRows: Record<string, ValidityPeriod[]>;
}
