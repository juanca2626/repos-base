import { ref, computed, watch, nextTick, type Ref, type ComputedRef } from 'vue';
import type { Dayjs } from 'dayjs';
import dayjs from 'dayjs';
import type { TrainSchedule, ValidityPeriod } from '../interfaces/train-service.interface';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import {
  completeTimeValue,
  validateTimeValue,
} from '@/modules/negotiations/products/configuration/utils/time.utils';

export interface SelectOption {
  label: string;
  value: string;
}

export const calculateDuration = (startTime: string, endTime: string): string => {
  if (!startTime || !endTime || startTime.length < 5 || endTime.length < 5) {
    return '';
  }

  try {
    const [startHours, startMinutes] = startTime.split(':').map(Number);
    const [endHours, endMinutes] = endTime.split(':').map(Number);

    const startTotalMinutes = startHours * 60 + startMinutes;
    const endTotalMinutes = endHours * 60 + endMinutes;

    // Si la hora de fin es menor que la de inicio, asumimos que es del día siguiente
    let diffMinutes = endTotalMinutes - startTotalMinutes;
    if (diffMinutes < 0) {
      diffMinutes += 24 * 60; // Agregar 24 horas
    }

    const hours = Math.floor(diffMinutes / 60);
    const minutes = diffMinutes % 60;

    if (hours === 0) {
      return `${minutes} min`;
    } else if (minutes === 0) {
      return hours === 1 ? '1 Hora' : `${hours} Horas`;
    } else {
      const hoursText = hours === 1 ? '1 Hora' : `${hours} Horas`;
      return `${hoursText} ${minutes} min`;
    }
  } catch {
    return '';
  }
};

export function useTrainScheduleTable(
  schedulesRef?: Ref<TrainSchedule[]> | ComputedRef<TrainSchedule[]>,
  currentKey?: string,
  currentCode?: string
) {
  const trainConfigurationStore = useTrainConfigurationStore();
  const validityRows = ref<Record<string, ValidityPeriod[]>>({});
  const validityErrors = ref<Record<string, Record<number, boolean>>>({});

  // Función para inicializar validityRows desde datos del store
  const initializeValidityRowsFromData = (
    schedulesData: TrainSchedule[],
    frequenciesData?: Array<{
      id: string;
      validityPeriods?: Array<{ startDate: string; endDate: string }>;
    }>
  ) => {
    validityRows.value = {};

    schedulesData.forEach((schedule) => {
      const frequency = frequenciesData?.find((f) => f.id === schedule.id);

      if (frequency?.validityPeriods && Array.isArray(frequency.validityPeriods)) {
        // Mapear validityPeriods del API a ValidityPeriod del formulario
        validityRows.value[schedule.id] = frequency.validityPeriods.map((vp) => ({
          startDate: vp.startDate ? dayjs(vp.startDate) : null,
          endDate: vp.endDate ? dayjs(vp.endDate) : null,
        }));
      } else {
        validityRows.value[schedule.id] = [];
      }
    });

    // Forzar reactividad
    validityRows.value = { ...validityRows.value };
  };

  // Inicializar desde el store si existe
  const initializeFromStore = () => {
    if (currentKey && currentCode && schedulesRef) {
      const storedData = trainConfigurationStore.getTrainServiceDetailsSchedule(
        currentKey,
        currentCode
      );

      if (
        storedData &&
        storedData.validityRows &&
        Object.keys(storedData.validityRows).length > 0
      ) {
        // Mapear las fechas del store (que pueden ser strings o Dayjs) a objetos Dayjs
        const mappedValidityRows: Record<string, ValidityPeriod[]> = {};
        Object.keys(storedData.validityRows).forEach((scheduleId) => {
          if (
            storedData.validityRows[scheduleId] &&
            Array.isArray(storedData.validityRows[scheduleId])
          ) {
            mappedValidityRows[scheduleId] = storedData.validityRows[scheduleId].map((vp: any) => ({
              startDate: vp.startDate
                ? dayjs.isDayjs(vp.startDate)
                  ? vp.startDate
                  : dayjs(vp.startDate)
                : null,
              endDate: vp.endDate
                ? dayjs.isDayjs(vp.endDate)
                  ? vp.endDate
                  : dayjs(vp.endDate)
                : null,
            }));
          } else {
            mappedValidityRows[scheduleId] = [];
          }
        });

        validityRows.value = mappedValidityRows;
        // Forzar reactividad
        validityRows.value = { ...validityRows.value };
        return;
      }
    }
    // Si no hay datos en el store, inicializar desde schedules
    if (schedulesRef) {
      schedulesRef.value.forEach((schedule) => {
        if (!validityRows.value[schedule.id]) {
          validityRows.value[schedule.id] = [];
        }
      });
      // Forzar reactividad
      validityRows.value = { ...validityRows.value };
    }
  };

  initializeFromStore();

  const isInitializing = ref(false);

  // Watch para reinicializar cuando cambien los schedules (por ejemplo, cuando se cargan desde el API)
  if (schedulesRef && currentKey && currentCode) {
    watch(
      () => schedulesRef.value,
      (newSchedules, _oldSchedules) => {
        if (newSchedules && newSchedules.length > 0) {
          // Verificar si hay datos en el store para estos schedules
          const storedData = trainConfigurationStore.getTrainServiceDetailsSchedule(
            currentKey,
            currentCode
          );
          if (
            storedData &&
            storedData.validityRows &&
            Object.keys(storedData.validityRows).length > 0
          ) {
            isInitializing.value = true;
            // Mapear las fechas del store a objetos Dayjs
            const mappedValidityRows: Record<string, ValidityPeriod[]> = {};
            newSchedules.forEach((schedule) => {
              if (
                storedData.validityRows[schedule.id] &&
                Array.isArray(storedData.validityRows[schedule.id]) &&
                storedData.validityRows[schedule.id].length > 0
              ) {
                mappedValidityRows[schedule.id] = storedData.validityRows[schedule.id].map(
                  (vp: any) => ({
                    startDate: vp.startDate
                      ? dayjs.isDayjs(vp.startDate)
                        ? vp.startDate
                        : dayjs(vp.startDate)
                      : null,
                    endDate: vp.endDate
                      ? dayjs.isDayjs(vp.endDate)
                        ? vp.endDate
                        : dayjs(vp.endDate)
                      : null,
                  })
                );
              } else {
                // Si no hay datos para este schedule, mantener lo que ya existe o inicializar vacío
                mappedValidityRows[schedule.id] = validityRows.value[schedule.id] || [];
              }
            });
            // Actualizar siempre para asegurar que se muestren los datos
            validityRows.value = mappedValidityRows;
            // Forzar reactividad
            validityRows.value = { ...validityRows.value };
            // Usar nextTick para asegurar que el watcher de guardado no se ejecute durante la inicialización
            nextTick(() => {
              isInitializing.value = false;
            });
          } else {
            // Si no hay datos en el store, inicializar arrays vacíos solo para nuevos schedules
            let hasNewSchedules = false;
            newSchedules.forEach((schedule) => {
              if (!validityRows.value[schedule.id]) {
                validityRows.value[schedule.id] = [];
                hasNewSchedules = true;
              }
            });
            // Solo forzar reactividad si hubo cambios
            if (hasNewSchedules) {
              validityRows.value = { ...validityRows.value };
            }
          }
        }
      },
      { immediate: true, deep: true }
    );
  }

  // Guardar en el store cuando cambien los valores (solo si no se está inicializando)
  if (currentKey && currentCode && schedulesRef) {
    watch(
      [() => schedulesRef.value, validityRows],
      () => {
        if (isInitializing.value) {
          return;
        }
        if (schedulesRef.value && schedulesRef.value.length > 0) {
          trainConfigurationStore.setTrainServiceDetailsSchedule(currentKey, currentCode, {
            schedules: schedulesRef.value,
            validityRows: validityRows.value,
          });
        }
      },
      { deep: true }
    );
  }

  // Watcher para inicializar validityRows cuando se agregan nuevos schedules
  if (schedulesRef) {
    watch(
      () => schedulesRef.value.map((s) => s.id),
      (newIds, oldIds) => {
        newIds.forEach((id) => {
          if (!oldIds || !oldIds.includes(id)) {
            // Nuevo schedule agregado
            if (!validityRows.value[id]) {
              validityRows.value[id] = [];
              validityRows.value = { ...validityRows.value }; // Forzar reactividad
            }
          }
        });
      },
      { immediate: false }
    );
  }

  const handleAddValidity = (scheduleId: string) => {
    // Asegurar que el array existe para este scheduleId
    if (!validityRows.value[scheduleId]) {
      validityRows.value[scheduleId] = [];
    }

    const newIndex = validityRows.value[scheduleId].length;

    // Agregar el nuevo validity period
    validityRows.value[scheduleId].push({
      startDate: null,
      endDate: null,
    });

    // Forzar la reactividad creando un nuevo objeto
    validityRows.value = { ...validityRows.value };

    // Inicializar el estado de error para la nueva fila
    if (!validityErrors.value[scheduleId]) {
      validityErrors.value[scheduleId] = {};
    }
    validityErrors.value[scheduleId][newIndex] = false;
  };

  const handleRemoveValidity = (scheduleId: string, index: number) => {
    if (validityRows.value[scheduleId] && validityRows.value[scheduleId].length > 1) {
      validityRows.value[scheduleId].splice(index, 1);

      // Limpiar errores de la fila eliminada
      if (validityErrors.value[scheduleId]) {
        delete validityErrors.value[scheduleId][index];
        // Reindexar errores
        const newErrors: Record<number, boolean> = {};
        Object.keys(validityErrors.value[scheduleId]).forEach((key) => {
          const oldIndex = Number(key);
          if (oldIndex < index) {
            newErrors[oldIndex] = validityErrors.value[scheduleId][oldIndex];
          } else if (oldIndex > index) {
            newErrors[oldIndex - 1] = validityErrors.value[scheduleId][oldIndex];
          }
        });
        validityErrors.value[scheduleId] = newErrors;
      }
    }
  };

  const isDateInRange = (
    date: Dayjs | any,
    startDate: Dayjs | null,
    endDate: Dayjs | null
  ): boolean => {
    if (!startDate || !endDate) return false;

    // Asegurarse de que date sea un objeto Dayjs
    const dateObj = dayjs.isDayjs(date) ? date : dayjs(date);
    const startObj = dayjs.isDayjs(startDate) ? startDate : dayjs(startDate);
    const endObj = dayjs.isDayjs(endDate) ? endDate : dayjs(endDate);

    if (!dateObj.isValid() || !startObj.isValid() || !endObj.isValid()) return false;

    // Usar métodos compatibles sin plugins adicionales
    return (
      (dateObj.isAfter(startObj, 'day') || dateObj.isSame(startObj, 'day')) &&
      (dateObj.isBefore(endObj, 'day') || dateObj.isSame(endObj, 'day'))
    );
  };

  const hasDateOverlap = (
    startDate1: Dayjs | null,
    endDate1: Dayjs | null,
    startDate2: Dayjs | null,
    endDate2: Dayjs | null
  ): boolean => {
    if (!startDate1 || !endDate1 || !startDate2 || !endDate2) return false;

    const start1 = dayjs.isDayjs(startDate1) ? startDate1 : dayjs(startDate1);
    const end1 = dayjs.isDayjs(endDate1) ? endDate1 : dayjs(endDate1);
    const start2 = dayjs.isDayjs(startDate2) ? startDate2 : dayjs(startDate2);
    const end2 = dayjs.isDayjs(endDate2) ? endDate2 : dayjs(endDate2);

    if (!start1.isValid() || !end1.isValid() || !start2.isValid() || !end2.isValid()) return false;

    return (
      isDateInRange(start1, start2, end2) ||
      isDateInRange(end1, start2, end2) ||
      isDateInRange(start2, start1, end1) ||
      isDateInRange(end2, start1, end1) ||
      ((start1.isBefore(start2, 'day') || start1.isSame(start2, 'day')) &&
        (end1.isAfter(end2, 'day') || end1.isSame(end2, 'day')))
    );
  };

  const validateValidityRow = (scheduleId: string, currentIndex: number): boolean => {
    const validities = validityRows.value[scheduleId] || [];
    if (validities.length <= 1) return true;

    const currentValidity = validities[currentIndex];
    if (!currentValidity.startDate || !currentValidity.endDate) return true;

    // Verificar solapamiento con otras filas
    for (let i = 0; i < validities.length; i++) {
      if (i === currentIndex) continue;

      const otherValidity = validities[i];
      if (
        hasDateOverlap(
          currentValidity.startDate,
          currentValidity.endDate,
          otherValidity.startDate,
          otherValidity.endDate
        )
      ) {
        return false;
      }
    }

    return true;
  };

  const getDisabledDates = (
    scheduleId: string,
    currentIndex: number,
    _field: 'startDate' | 'endDate'
  ) => {
    return (current: Dayjs | any) => {
      const validities = validityRows.value[scheduleId] || [];
      if (validities.length <= 1) return false;

      // Asegurarse de que current sea un objeto Dayjs válido
      if (!current) return false;
      const currentDate = dayjs.isDayjs(current) ? current : dayjs(current);
      if (!currentDate.isValid()) return false;

      // Deshabilitar fechas que están en el rango de otras filas
      for (let i = 0; i < validities.length; i++) {
        if (i === currentIndex) continue;

        const otherValidity = validities[i];
        if (otherValidity.startDate && otherValidity.endDate) {
          if (isDateInRange(currentDate, otherValidity.startDate, otherValidity.endDate)) {
            return true;
          }
        }
      }

      return false;
    };
  };

  const hasValidityError = (scheduleId: string, index: number): boolean => {
    if (!validityErrors.value[scheduleId]) return false;
    return validityErrors.value[scheduleId][index] === true;
  };

  const updateValidityValidation = (scheduleId: string, _index: number) => {
    if (!validityErrors.value[scheduleId]) {
      validityErrors.value[scheduleId] = {};
    }
    const validities = validityRows.value[scheduleId] || [];

    // Validar todas las filas ya que un cambio en una puede afectar a otras
    validities.forEach((_, i) => {
      validityErrors.value[scheduleId][i] = !validateValidityRow(scheduleId, i);
    });
  };

  const daysOptions: SelectOption[] = [
    { label: 'Lunes', value: 'MONDAY' },
    { label: 'Martes', value: 'TUESDAY' },
    { label: 'Miércoles', value: 'WEDNESDAY' },
    { label: 'Jueves', value: 'THURSDAY' },
    { label: 'Viernes', value: 'FRIDAY' },
    { label: 'Sábado', value: 'SATURDAY' },
  ];

  const fareTypeOptions = computed<SelectOption[]>(() => [
    { label: 'EXP confidencial', value: 'EXP confidencial' },
    { label: 'Dinamica', value: 'Dinamica' },
  ]);

  const getCalculatedDuration = (schedule: TrainSchedule): string => {
    if (schedule.startTime && schedule.endTime) {
      return calculateDuration(schedule.startTime, schedule.endTime);
    }
    return schedule.duration || '';
  };

  const handleTimeBlur = (schedule: TrainSchedule, field: 'startTime' | 'endTime') => {
    const rawValue = schedule[field] || '';
    const completedValue = completeTimeValue(rawValue);
    const validatedValue = validateTimeValue(completedValue);

    schedule[field] = validatedValue;
    schedule.duration = calculateDuration(schedule.startTime, schedule.endTime);
  };

  return {
    validityRows,
    validityErrors,
    handleAddValidity,
    handleRemoveValidity,
    daysOptions,
    fareTypeOptions,
    calculateDuration,
    getCalculatedDuration,
    handleTimeBlur,
    getDisabledDates,
    hasValidityError,
    updateValidityValidation,
    initializeValidityRowsFromData,
  };
}
