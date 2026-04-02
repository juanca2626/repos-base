import { ref, computed, watch } from 'vue';
import { message } from 'ant-design-vue';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import type {
  Schedule,
  ScheduleDay,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';

// Valores por defecto
const defaultScheduleGeneral: Schedule[] = [
  {
    id: null,
    open: '',
    close: '',
    twenty_four_hours: false,
    single_time: false,
  },
];

const defaultSchedule: ScheduleDay[] = [
  {
    label: 'Lun',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Mar',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Mié',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Jue',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Vie',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Sáb',
    available_day: true,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
  {
    label: 'Dom',
    available_day: false,
    schedules: [{ id: null, open: '', close: '' }],
    twenty_four_hours: false,
    single_time: false,
  },
];

export const useSchedule = (currentKey?: string, currentCode?: string) => {
  const genericConfigurationStore = useGenericConfigurationStore();

  // Inicializar desde el store si existe, sino usar valores por defecto
  const initializeFromStore = () => {
    if (currentKey && currentCode) {
      const storedData = genericConfigurationStore.getServiceDetailsSchedule(
        currentKey,
        currentCode
      );
      if (storedData) {
        return {
          scheduleType: storedData.scheduleType,
          scheduleGeneral: JSON.parse(JSON.stringify(storedData.scheduleGeneral)),
          schedule: JSON.parse(JSON.stringify(storedData.schedule)),
        };
      }
    }
    return {
      scheduleType: 1,
      scheduleGeneral: JSON.parse(JSON.stringify(defaultScheduleGeneral)),
      schedule: JSON.parse(JSON.stringify(defaultSchedule)),
    };
  };

  const initialData = initializeFromStore();

  const scheduleType = ref<number>(initialData.scheduleType);
  const scheduleGeneral = ref<Schedule[]>(initialData.scheduleGeneral);
  const schedule = ref<ScheduleDay[]>(initialData.schedule);

  // Función para resetear los schedules a valores por defecto
  const resetSchedules = () => {
    const defaultData = {
      scheduleType: 1,
      scheduleGeneral: JSON.parse(JSON.stringify(defaultScheduleGeneral)),
      schedule: JSON.parse(JSON.stringify(defaultSchedule)),
    };

    scheduleType.value = defaultData.scheduleType;
    scheduleGeneral.value = defaultData.scheduleGeneral;
    schedule.value = defaultData.schedule;

    // Resetear estados de selección de 24 horas y hora única
    isSelectingTwentyFourHours.value = false;
    isSelectingSingleTime.value = false;
    selectedDaysFor24Hours.value = [false, false, false, false, false, false, false];
    selectedDaysForSingleTime.value = [false, false, false, false, false, false, false];

    // También limpiar del store si existe
    if (currentKey && currentCode) {
      genericConfigurationStore.setServiceDetailsSchedule(currentKey, currentCode, {
        scheduleType: defaultData.scheduleType,
        scheduleGeneral: defaultData.scheduleGeneral,
        schedule: defaultData.schedule,
      });
    }
  };

  // Guardar en el store cuando cambien los valores
  if (currentKey && currentCode) {
    watch(
      [scheduleType, scheduleGeneral, schedule],
      () => {
        // console.log('[useSchedule] Guardando en el store', {
        //   scheduleType: scheduleType.value,
        //   scheduleGeneral: scheduleGeneral.value,
        //   schedule: schedule.value,
        // });

        genericConfigurationStore.setServiceDetailsSchedule(currentKey, currentCode, {
          scheduleType: scheduleType.value,
          scheduleGeneral: scheduleGeneral.value,
          schedule: schedule.value,
        });
      },
      { deep: true }
    );
  }

  // Watch para detectar cambios en currentKey o currentCode y reinicializar
  watch(
    () => [currentKey, currentCode],
    ([newCurrentKey, newCurrentCode], [oldCurrentKey, oldCurrentCode]) => {
      // Si cambió la categoría o location, resetear los schedules
      // Verificar si realmente cambió (evitar ejecución en primera carga si no hay cambio)
      const locationChanged = oldCurrentKey !== undefined && newCurrentKey !== oldCurrentKey;
      const categoryChanged = oldCurrentCode !== undefined && newCurrentCode !== oldCurrentCode;

      if (locationChanged || categoryChanged) {
        console.log('[useSchedule] Cambio de categoría/location detectado, reseteando schedules', {
          oldCurrentKey,
          oldCurrentCode,
          newCurrentKey,
          newCurrentCode,
        });

        // Reinicializar desde el store de la nueva categoría
        const newData = initializeFromStore();
        scheduleType.value = newData.scheduleType;
        scheduleGeneral.value = newData.scheduleGeneral;
        schedule.value = newData.schedule;

        // Resetear estados de selección de 24 horas y hora única
        isSelectingTwentyFourHours.value = false;
        isSelectingSingleTime.value = false;
        selectedDaysFor24Hours.value = [false, false, false, false, false, false, false];
        selectedDaysForSingleTime.value = [false, false, false, false, false, false, false];
      }
    },
    { immediate: false }
  );

  // Función para formatear el valor del input de tiempo
  const formatTimeValue = (value: string): string => {
    // Eliminar caracteres no numéricos
    const numericValue = value.replace(/\D/g, '');

    if (numericValue.length === 0) {
      return '';
    }

    if (numericValue.length <= 2) {
      return numericValue;
    }

    // Formatear como HH:MM
    const hours = numericValue.substring(0, 2);
    const minutes = numericValue.substring(2, 4);

    return `${hours}:${minutes}`;
  };

  // Completar el tiempo con formato completo (autocompletado)
  const completeTimeValue = (value: string): string => {
    if (!value) {
      return '';
    }

    // Eliminar caracteres no numéricos
    const numericValue = value.replace(/\D/g, '');

    if (numericValue.length === 0) {
      return '';
    }

    // Si solo hay 1 dígito: 2 -> 02:00
    if (numericValue.length === 1) {
      return `0${numericValue}:00`;
    }

    // Si hay 2 dígitos: 23 -> 23:00
    if (numericValue.length === 2) {
      const hours = parseInt(numericValue, 10);
      if (hours > 23) {
        return '23:00';
      }
      return `${numericValue}:00`;
    }

    // Si hay 3 dígitos: 223 -> 22:30
    if (numericValue.length === 3) {
      const hours = parseInt(numericValue.substring(0, 2), 10);
      const firstMinuteDigit = numericValue.substring(2, 3);

      if (hours > 23) {
        return '23:00';
      }

      return `${hours.toString().padStart(2, '0')}:${firstMinuteDigit}0`;
    }

    // Si hay 4 o más dígitos: completar normalmente
    const hours = parseInt(numericValue.substring(0, 2), 10);
    const minutes = parseInt(numericValue.substring(2, 4), 10);

    const validHours = hours > 23 ? 23 : hours;
    const validMinutes = minutes > 59 ? 59 : minutes;

    return `${validHours.toString().padStart(2, '0')}:${validMinutes.toString().padStart(2, '0')}`;
  };

  // Validar y corregir el valor del tiempo
  const validateTimeValue = (value: string): string => {
    if (!value || value.length < 5) {
      return value;
    }

    const [hours, minutes] = value.split(':');
    let validHours = parseInt(hours, 10);
    let validMinutes = parseInt(minutes, 10);

    // Validar horas (0-23)
    if (validHours > 23) {
      validHours = 23;
    }

    // Validar minutos (0-59)
    if (validMinutes > 59) {
      validMinutes = 59;
    }

    return `${validHours.toString().padStart(2, '0')}:${validMinutes.toString().padStart(2, '0')}`;
  };

  // Manejadores de eventos para inputs de tiempo
  const handleTimeInputClick = (
    e: Event,
    _type: 'scheduleGeneral' | 'custom',
    _field: 'open' | 'close',
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalIndex?: number
  ) => {
    const input = e.target as HTMLInputElement;
    if (input.value === '') {
      input.select();
    }
  };

  const handleTimeInputFocus = (
    e: Event,
    _type: 'scheduleGeneral' | 'custom',
    _field: 'open' | 'close',
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalIndex?: number
  ) => {
    const input = e.target as HTMLInputElement;
    input.select();
  };

  const handleTimeInputChange = (
    e: Event,
    type: 'scheduleGeneral' | 'custom',
    field: 'open' | 'close',
    dayIndex?: number,
    scheduleIndex?: number,
    generalIndex?: number
  ) => {
    const input = e.target as HTMLInputElement;
    const formattedValue = formatTimeValue(input.value);

    if (type === 'scheduleGeneral' && generalIndex !== undefined) {
      scheduleGeneral.value[generalIndex][field] = formattedValue;
    } else if (type === 'custom' && dayIndex !== undefined && scheduleIndex !== undefined) {
      schedule.value[dayIndex].schedules[scheduleIndex][field] = formattedValue;
    }
  };

  // Validar que la hora final sea mayor a la hora inicial
  const validateTimeRange = (openTime: string, closeTime: string): boolean => {
    if (!openTime || !closeTime || openTime.length < 5 || closeTime.length < 5) {
      return true; // No validar si alguna hora está incompleta
    }

    const [openHours, openMinutes] = openTime.split(':').map(Number);
    const [closeHours, closeMinutes] = closeTime.split(':').map(Number);

    const openTotalMinutes = openHours * 60 + openMinutes;
    const closeTotalMinutes = closeHours * 60 + closeMinutes;

    return closeTotalMinutes > openTotalMinutes;
  };

  const handleTimeInputBlur = (
    e: Event,
    type: 'scheduleGeneral' | 'custom',
    field: 'open' | 'close',
    dayIndex?: number,
    scheduleIndex?: number,
    generalIndex?: number
  ) => {
    const input = e.target as HTMLInputElement;
    const completedValue = completeTimeValue(input.value);
    const validatedValue = validateTimeValue(completedValue);

    if (type === 'scheduleGeneral' && generalIndex !== undefined) {
      scheduleGeneral.value[generalIndex][field] = validatedValue;

      // Validar rango de tiempo
      const currentSchedule = scheduleGeneral.value[generalIndex];
      if (currentSchedule.open && currentSchedule.close) {
        if (!validateTimeRange(currentSchedule.open, currentSchedule.close)) {
          message.error('La hora final no puede ser menor o igual a la hora inicial');
          scheduleGeneral.value[generalIndex][field] = '';
        }
      }
    } else if (type === 'custom' && dayIndex !== undefined && scheduleIndex !== undefined) {
      schedule.value[dayIndex].schedules[scheduleIndex][field] = validatedValue;

      // Validar rango de tiempo
      const currentSchedule = schedule.value[dayIndex].schedules[scheduleIndex];
      if (currentSchedule.open && currentSchedule.close) {
        if (!validateTimeRange(currentSchedule.open, currentSchedule.close)) {
          message.error('La hora final no puede ser menor o igual a la hora inicial');
          schedule.value[dayIndex].schedules[scheduleIndex][field] = '';
        }
      }
    }
  };

  const handleTimeKeyDown = (e: KeyboardEvent) => {
    const key = e.key;

    // Si presiona Tab, completar el tiempo automáticamente
    if (key === 'Tab') {
      const input = e.target as HTMLInputElement;
      if (input.value) {
        const completedValue = completeTimeValue(input.value);
        const validatedValue = validateTimeValue(completedValue);
        input.value = validatedValue;

        // Trigger el evento blur manualmente para actualizar el modelo
        input.dispatchEvent(new Event('blur'));
      }
      return;
    }

    // Permitir teclas de control
    if (['Backspace', 'Delete', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(key)) {
      return;
    }

    // Permitir Ctrl/Cmd + A, C, V, X
    if ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x'].includes(key.toLowerCase())) {
      return;
    }

    // Solo permitir números
    if (!/^\d$/.test(key)) {
      e.preventDefault();
    }
  };

  // Agregar horario general
  const handleAddGeneralSchedule = (twentyFourHours: boolean, singleTime: boolean) => {
    scheduleGeneral.value.push({
      id: null,
      open: '',
      close: '',
      twenty_four_hours: twentyFourHours,
      single_time: singleTime,
    });
  };

  // Eliminar horario general
  const handleRemoveGeneralSchedule = (index: number) => {
    if (scheduleGeneral.value.length > 1) {
      scheduleGeneral.value.splice(index, 1);
    }
  };

  // Agregar horario personalizado
  const handleAddSchedule = (dayIndex: number) => {
    schedule.value[dayIndex].schedules.push({
      id: null,
      open: '',
      close: '',
    });
  };

  // Eliminar horario personalizado
  const handleRemoveSchedule = (dayIndex: number, scheduleIndex: number) => {
    if (schedule.value[dayIndex].schedules.length > 1) {
      schedule.value[dayIndex].schedules.splice(scheduleIndex, 1);
    }
  };

  // Toggle disponibilidad de día
  const handleToggleAvailableDay = (dayIndex: number) => {
    schedule.value[dayIndex].available_day = !schedule.value[dayIndex].available_day;

    // Si el día se marca como no disponible, limpiar los horarios
    if (!schedule.value[dayIndex].available_day) {
      schedule.value[dayIndex].schedules = [{ id: null, open: '', close: '' }];
    }
  };

  // Estado para controlar el modo de selección de 24 horas
  const isSelectingTwentyFourHours = ref<boolean>(false);
  const selectedDaysFor24Hours = ref<boolean[]>([false, false, false, false, false, false, false]);

  // Estado para controlar el modo de selección de Hora única
  const isSelectingSingleTime = ref<boolean>(false);
  const selectedDaysForSingleTime = ref<boolean[]>([
    false,
    false,
    false,
    false,
    false,
    false,
    false,
  ]);

  // Toggle para activar modo de selección de 24 horas
  const handleToggleTwentyFourHours = () => {
    if (scheduleType.value === 1) {
      // Para "Todos los días", aplicar 24 horas directamente a todos los horarios generales
      scheduleGeneral.value.forEach((scheduleItem) => {
        scheduleItem.twenty_four_hours = true;
        scheduleItem.open = '';
        scheduleItem.close = '';
      });
    } else if (scheduleType.value === 2) {
      // Para "Personalizado", activar modo de selección con checkboxes
      isSelectingTwentyFourHours.value = true;
      // Inicializar checkboxes basándose en el estado actual de twenty_four_hours
      selectedDaysFor24Hours.value = schedule.value.map((day) => day.twenty_four_hours || false);
    }
  };

  // Toggle checkbox de día para 24 horas
  const handleToggleDayCheckbox = (dayIndex: number) => {
    selectedDaysFor24Hours.value[dayIndex] = !selectedDaysFor24Hours.value[dayIndex];

    // Aplicar inmediatamente el estado de 24 horas (temporal hasta guardar)
    if (selectedDaysFor24Hours.value[dayIndex]) {
      schedule.value[dayIndex].twenty_four_hours = true;
      schedule.value[dayIndex].single_time = false; // No pueden tener ambos
      schedule.value[dayIndex].available_day = true;
      // Deseleccionar el checkbox de hora única si estaba seleccionado
      selectedDaysForSingleTime.value[dayIndex] = false;
      // Limpiar horarios ya que es 24 horas
      schedule.value[dayIndex].schedules = [{ id: null, open: '', close: '' }];
    } else {
      // Si se deselecciona, quitar el estado de 24 horas
      schedule.value[dayIndex].twenty_four_hours = false;
    }
  };

  // Guardar selección de 24 horas
  const handleSaveTwentyFourHours = () => {
    isSelectingTwentyFourHours.value = false;
  };

  // Toggle para activar modo de selección de Hora única
  const handleToggleSingleTime = () => {
    if (scheduleType.value === 1) {
      // Para "Todos los días", aplicar hora única directamente a todos los horarios generales
      scheduleGeneral.value.forEach((scheduleItem) => {
        scheduleItem.single_time = true;
        scheduleItem.twenty_four_hours = false; // No pueden tener ambos
        scheduleItem.open = '';
        scheduleItem.close = '';
      });
    } else if (scheduleType.value === 2) {
      // Para "Personalizado", activar modo de selección con checkboxes
      isSelectingSingleTime.value = true;
      // Inicializar checkboxes basándose en el estado actual de single_time
      selectedDaysForSingleTime.value = schedule.value.map((day) => day.single_time || false);
    }
  };

  // Toggle checkbox de día para Hora única
  const handleToggleSingleTimeCheckbox = (dayIndex: number) => {
    selectedDaysForSingleTime.value[dayIndex] = !selectedDaysForSingleTime.value[dayIndex];

    // Aplicar inmediatamente el estado de hora única (temporal hasta guardar)
    if (selectedDaysForSingleTime.value[dayIndex]) {
      schedule.value[dayIndex].single_time = true;
      schedule.value[dayIndex].twenty_four_hours = false; // No pueden tener ambos
      schedule.value[dayIndex].available_day = true;
      // Deseleccionar el checkbox de 24 horas si estaba seleccionado
      selectedDaysFor24Hours.value[dayIndex] = false;
      // Limpiar horarios ya que es hora única
      schedule.value[dayIndex].schedules = [{ id: null, open: '', close: '' }];
    } else {
      // Si se deselecciona, quitar el estado de hora única
      schedule.value[dayIndex].single_time = false;
    }
  };

  // Guardar selección de Hora única
  const handleSaveSingleTime = () => {
    // Los días ya tienen el estado aplicado desde handleToggleSingleTimeCheckbox
    // Solo necesitamos salir del modo de selección
    // Los checkboxes permanecen visibles y seleccionados para los días con hora única
    isSelectingSingleTime.value = false;
  };

  // Computed para saber si todos los días están seleccionados para 24 horas
  const allDaysSelected24Hours = computed(() => {
    if (scheduleType.value !== 2) return false;
    return selectedDaysFor24Hours.value.every((selected) => selected);
  });

  // Computed para saber si todos los días están seleccionados para hora única
  const allDaysSelectedSingleTime = computed(() => {
    if (scheduleType.value !== 2) return false;
    return selectedDaysForSingleTime.value.every((selected) => selected);
  });

  // Seleccionar/deseleccionar todos los días para 24 horas
  const handleSelectAll24Hours = (checked: boolean) => {
    selectedDaysFor24Hours.value = schedule.value.map(() => checked);

    // Aplicar inmediatamente el estado a todos los días
    schedule.value.forEach((day, dayIndex) => {
      if (checked) {
        day.twenty_four_hours = true;
        day.single_time = false;
        day.available_day = true;
        day.schedules = [{ id: null, open: '', close: '' }];
        selectedDaysForSingleTime.value[dayIndex] = false;
      } else {
        day.twenty_four_hours = false;
      }
    });
  };

  // Seleccionar/deseleccionar todos los días para hora única
  const handleSelectAllSingleTime = (checked: boolean) => {
    selectedDaysForSingleTime.value = schedule.value.map(() => checked);

    // Aplicar inmediatamente el estado a todos los días
    schedule.value.forEach((day, dayIndex) => {
      if (checked) {
        day.single_time = true;
        day.twenty_four_hours = false;
        day.available_day = true;
        day.schedules = [{ id: null, open: '', close: '' }];
        selectedDaysFor24Hours.value[dayIndex] = false;
      } else {
        day.single_time = false;
      }
    });
  };

  return {
    scheduleType,
    scheduleGeneral,
    schedule,
    isSelectingTwentyFourHours,
    selectedDaysFor24Hours,
    isSelectingSingleTime,
    selectedDaysForSingleTime,
    allDaysSelected24Hours,
    allDaysSelectedSingleTime,
    resetSchedules,
    handleTimeInputClick,
    handleTimeInputFocus,
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeKeyDown,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleToggleTwentyFourHours,
    handleToggleDayCheckbox,
    handleSaveTwentyFourHours,
    handleToggleSingleTime,
    handleToggleSingleTimeCheckbox,
    handleSaveSingleTime,
    handleSelectAll24Hours,
    handleSelectAllSingleTime,
  };
};
