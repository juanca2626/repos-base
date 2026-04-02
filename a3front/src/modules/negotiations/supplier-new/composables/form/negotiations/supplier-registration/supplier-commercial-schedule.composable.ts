import { reactive, ref, computed, watch } from 'vue';
import { notification } from 'ant-design-vue';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierGlobalComposable } from '../../supplier-global.composable';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { handleCompleteResponse } from '@/modules/negotiations/api/responseApi';

type FormState = {
  administration: 'public' | 'private';
  scheduleType: number;
  scheduleGeneral: { open: string | null; close: string | null }[];
  schedule: Array<{
    day: string;
    label: string;
    schedules: Array<{ open: string | null; close: string | null }>;
    available_day: boolean;
  }>;
};

export function useCommercialScheduleComposable() {
  // 🔑 Sección única (como pediste)
  const SECTION = FormComponentEnum.COMMERCIAL_INFORMATION;

  // Global
  const {
    supplierId,
    isEditMode,
    getShowFormComponent: _getShow,
    getIsEditFormComponent: _getEdit,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleSavedFormSpecific,
    markItemComplete,
  } = useSupplierGlobalComposable();

  // Servicios
  const { updateOrCreateSupplierAttractions, showSupplierAttractions } = useSupplierService;

  // Estado local
  const formRef = ref();
  const isLoading = ref(false);
  const isLoadingButton = ref(false);

  // Wrappers (los computed del store devuelven funciones)
  const getShowFormComponent = (key: FormComponentEnum) =>
    typeof _getShow?.value === 'function' ? !!_getShow.value(key) : false;

  const getIsEditFormComponent = (key: FormComponentEnum) =>
    typeof _getEdit?.value === 'function' ? !!_getEdit.value(key) : false;

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(() => isLoading.value || isLoadingButton.value);

  // Helpers
  const buildEmptySchedule = () => [
    { day: 'MONDAY', label: 'Lun', schedules: [{ open: null, close: null }], available_day: true },
    { day: 'TUESDAY', label: 'Mar', schedules: [{ open: null, close: null }], available_day: true },
    {
      day: 'WEDNESDAY',
      label: 'Mié',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    {
      day: 'THURSDAY',
      label: 'Jue',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    { day: 'FRIDAY', label: 'Vie', schedules: [{ open: null, close: null }], available_day: true },
    {
      day: 'SATURDAY',
      label: 'Sáb',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    { day: 'SUNDAY', label: 'Dom', schedules: [{ open: null, close: null }], available_day: true },
  ];

  const dayMap: Record<string, number> = {
    MONDAY: 1,
    TUESDAY: 2,
    WEDNESDAY: 3,
    THURSDAY: 4,
    FRIDAY: 5,
    SATURDAY: 6,
    SUNDAY: 7,
  };

  // =========================
  // Time validation helper
  // =========================
  const convertTimeToMinutes = (timeString: string): number => {
    if (!timeString) return -1;
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
  };

  const validateTimeRange = (openTime: string | null, closeTime: string | null): boolean => {
    if (!openTime || !closeTime) return true; // Allow empty values

    const openMinutes = convertTimeToMinutes(openTime);
    const closeMinutes = convertTimeToMinutes(closeTime);

    return closeMinutes > openMinutes;
  };

  const showTimeValidationError = () => {
    notification.error({
      message: 'Error de validación',
      description: 'La hora de fin debe ser mayor que la hora de inicio',
      placement: 'topRight',
    });
  };

  // Validar si hay campos de horario vacíos
  const hasEmptyTimeFields = computed(() => {
    if (formState.scheduleType === 1) {
      // Modo "Todos los días" - verificar scheduleGeneral
      return formState.scheduleGeneral.some((schedule: any) => {
        const hasOpen = schedule.open && schedule.open.trim() !== '';
        const hasClose = schedule.close && schedule.close.trim() !== '';
        // Si alguno tiene valor pero el otro no, es inválido
        return (hasOpen && !hasClose) || (!hasOpen && hasClose);
      });
    } else if (formState.scheduleType === 2) {
      // Modo "Personalizado" - verificar schedule
      return formState.schedule.some((day: any) => {
        if (!day.available_day) return false; // Si el día no está disponible, no validar

        return day.schedules.some((schedule: any) => {
          const hasOpen = schedule.open && schedule.open.trim() !== '';
          const hasClose = schedule.close && schedule.close.trim() !== '';
          // Si alguno tiene valor pero el otro no, es inválido
          return (hasOpen && !hasClose) || (!hasOpen && hasClose);
        });
      });
    }
    return false;
  });

  // Validar si hay rangos de tiempo inválidos (fin menor que inicio)
  const hasInvalidTimeRanges = computed(() => {
    if (formState.scheduleType === 1) {
      // Modo "Todos los días" - verificar scheduleGeneral
      return formState.scheduleGeneral.some((schedule: any) => {
        const open = schedule.open;
        const close = schedule.close;
        if (open && close) {
          return !validateTimeRange(open, close);
        }
        return false;
      });
    } else if (formState.scheduleType === 2) {
      // Modo "Personalizado" - verificar schedule
      return formState.schedule.some((day: any) => {
        if (!day.available_day) return false;

        return day.schedules.some((schedule: any) => {
          const open = schedule.open;
          const close = schedule.close;
          if (open && close) {
            return !validateTimeRange(open, close);
          }
          return false;
        });
      });
    }
    return false;
  });

  // Función de validación del formulario
  const getIsFormValid = computed(() => {
    // Validar que no hay campos de horario vacíos
    const noEmptyTimeFields = !hasEmptyTimeFields.value;

    // Validar que no hay rangos de tiempo inválidos
    const noInvalidTimeRanges = !hasInvalidTimeRanges.value;

    return noEmptyTimeFields && noInvalidTimeRanges;
  });

  const initState: FormState = {
    administration: 'public',
    scheduleType: 1,
    scheduleGeneral: [{ open: null, close: null }],
    schedule: buildEmptySchedule(),
  };

  const formState = reactive<FormState>(JSON.parse(JSON.stringify(initState)));

  const timeOptions = ref<string[]>([]);

  // Inicializar timeOptions
  if (!timeOptions.value?.length) {
    for (let hour = 0; hour < 24; hour++) {
      const hh = hour.toString().padStart(2, '0');
      timeOptions.value.push(`${hh}:00`, `${hh}:30`);
    }
  }

  const clone = <T>(v: T): T => JSON.parse(JSON.stringify(v));

  const persistedSnapshot = ref<any | null>(null);
  const isRestoring = ref(false);

  const hasPersistedData = (): boolean => {
    if (formState.scheduleType === 1) {
      return formState.scheduleGeneral.some((schedule) => schedule.open && schedule.close);
    }
    return formState.schedule.some(
      (d: any) => d.available_day && d.schedules?.some((s: any) => s.open && s.close)
    );
  };

  // UI Actions
  const handleAddGeneralSchedule = () => {
    formState.scheduleGeneral.push({ open: null, close: null });
  };

  const handleRemoveGeneralSchedule = (index: number) => {
    if (Array.isArray(formState.scheduleGeneral) && formState.scheduleGeneral.length > 1) {
      formState.scheduleGeneral.splice(index, 1);
    }
  };

  const handleAddSchedule = (dayIndex: number) => {
    if (formState.schedule[dayIndex]) {
      formState.schedule[dayIndex].schedules.push({ open: null, close: null });
    }
  };

  const handleRemoveSchedule = (dayIndex: number, scheduleIndex: number) => {
    if (formState.schedule[dayIndex]?.schedules?.length > 1) {
      formState.schedule[dayIndex].schedules.splice(scheduleIndex, 1);
    }
  };

  const handleToggleAvailableDay = (dayIndex: number) => {
    if (formState.schedule[dayIndex]) {
      const day = formState.schedule[dayIndex];
      day.available_day = !day.available_day;

      if (!day.available_day) {
        day.schedules = [{ open: null, close: null }];
      } else if (day.schedules.length === 0) {
        day.schedules = [{ open: null, close: null }];
      }
    }
  };

  // =========================
  // Schedule validation handlers
  // =========================
  const handleGeneralScheduleChange = (scheduleIndex: number = 0) => {
    const schedule = formState.scheduleGeneral[scheduleIndex];
    if (!schedule) return;

    const { open, close } = schedule;

    // Solo validar si los valores están en formato completo (HH:mm) o están vacíos
    const isOpenComplete = !open || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(open);
    const isCloseComplete = !close || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(close);

    // Si algún valor está incompleto (usuario aún escribiendo), no validar
    if (!isOpenComplete || !isCloseComplete) {
      return;
    }

    // Verificar si hay campos vacíos cuando uno está lleno
    const hasOpen = open && open.trim() !== '';
    const hasClose = close && close.trim() !== '';

    // Solo deshabilitar botón para campos vacíos, sin mostrar toast
    if ((hasOpen && !hasClose) || (!hasOpen && hasClose)) {
      return;
    }

    // Verificar validez del rango de tiempo si ambos están completos
    if (hasOpen && hasClose && !validateTimeRange(open, close)) {
      showTimeValidationError();
      // Reset the close time
      schedule.close = null;
    }
  };

  const handleCustomScheduleChange = (
    dayIndex: number,
    scheduleIndex: number,
    type: 'open' | 'close'
  ) => {
    const schedule = formState.schedule[dayIndex]?.schedules[scheduleIndex];
    if (!schedule) return;

    const { open, close } = schedule;

    // Solo validar si los valores están en formato completo (HH:mm) o están vacíos
    const isOpenComplete = !open || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(open);
    const isCloseComplete = !close || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(close);

    // Si algún valor está incompleto (usuario aún escribiendo), no validar
    if (!isOpenComplete || !isCloseComplete) {
      return;
    }

    // Verificar si hay campos vacíos cuando uno está lleno
    const hasOpen = open && open.trim() !== '';
    const hasClose = close && close.trim() !== '';

    // Solo deshabilitar botón para campos vacíos, sin mostrar toast
    if ((hasOpen && !hasClose) || (!hasOpen && hasClose)) {
      return;
    }

    // Verificar validez del rango de tiempo si ambos están completos
    if (hasOpen && hasClose && !validateTimeRange(open, close)) {
      showTimeValidationError();
      // Reset the field that was just changed
      schedule[type] = null;
    }
  };

  // Payload (manteniendo endpoints del attraction)
  const buildPayload = () => {
    const schedule_times: { day: number; start_time: string; end_time: string }[] = [];

    if (formState.scheduleType === 1) {
      // Todos los días - múltiples horarios
      formState.scheduleGeneral
        .filter((schedule) => schedule.open && schedule.close)
        .forEach((schedule) => {
          for (let day = 1; day <= 7; day++) {
            schedule_times.push({
              day,
              start_time: schedule.open!,
              end_time: schedule.close!,
            });
          }
        });
    } else {
      // Personalizado
      formState.schedule.forEach((d) => {
        if (d.available_day) {
          d.schedules
            .filter((s) => s.open && s.close)
            .forEach((s) => {
              schedule_times.push({
                day: dayMap[d.day],
                start_time: s.open!,
                end_time: s.close!,
              });
            });
        }
      });
    }

    return {
      administration_type: formState.administration.toUpperCase(),
      schedule_times,
    };
  };

  // Guardar
  const handleSave = async () => {
    if (!supplierId.value || isLoadingButton.value) return;
    try {
      isLoadingButton.value = true;
      const response = await updateOrCreateSupplierAttractions(supplierId.value, buildPayload());
      if (response?.success) {
        handleCompleteResponse(response);
        await loadData();
        handleIsEditFormSpecific?.(SECTION, true);
        handleSavedFormSpecific?.(SECTION, true);
        markItemComplete('commercial_information');
      }
    } catch (e) {
      console.error('❌ Error al guardar:', e);
    } finally {
      isLoadingButton.value = false;
    }
  };

  // Cancelar
  const handleClose = () => {
    if (persistedSnapshot.value) {
      isRestoring.value = true;
      Object.assign(formState, clone(persistedSnapshot.value));
      isRestoring.value = false;
      handleIsEditFormSpecific?.(SECTION, true);
      return;
    }
    Object.assign(formState, clone(initState));
    handleShowFormSpecific?.(SECTION, false);
    handleIsEditFormSpecific?.(SECTION, false);
  };

  // Editar
  const handleShowForm = () => {
    // Prevenir edición si hay operaciones de carga en curso
    if (isLoading.value || isLoadingButton.value) {
      return;
    }

    if (!persistedSnapshot.value) {
      handleShowFormSpecific?.(SECTION, true);
      handleIsEditFormSpecific?.(SECTION, false);
      handleSavedFormSpecific?.(SECTION, false);
    } else {
      persistedSnapshot.value = clone(formState);
      handleIsEditFormSpecific?.(SECTION, false);
    }
  };

  // Mapear respuesta del backend
  const mapResponseToForm = (raw: any) => {
    const data = raw?.data ?? raw ?? {};

    // Administración
    formState.administration = data.administration_type
      ? (String(data.administration_type).toLowerCase() as 'public' | 'private')
      : 'public';

    // Reset schedule
    formState.schedule = buildEmptySchedule();
    formState.scheduleType = 1;
    formState.scheduleGeneral = [{ open: null, close: null }];

    if (data.schedule_times && Array.isArray(data.schedule_times)) {
      // Agrupar por día
      const daySchedules: Record<number, Array<{ start_time: string; end_time: string }>> = {};

      data.schedule_times.forEach((st: any) => {
        if (!daySchedules[st.day]) {
          daySchedules[st.day] = [];
        }
        daySchedules[st.day].push({ start_time: st.start_time, end_time: st.end_time });
      });

      // Verificar si es "todos los días" con múltiples horarios
      const allDays = Object.keys(daySchedules).map(Number).sort();
      if (allDays.length === 7) {
        // Verificar si todos los días tienen exactamente los mismos horarios
        const firstDaySchedules = daySchedules[1];
        const allSame = allDays.every((day) => {
          const dayScheds = daySchedules[day];
          if (dayScheds.length !== firstDaySchedules.length) return false;

          return dayScheds.every((sched, idx) => {
            const firstSched = firstDaySchedules[idx];
            return (
              sched.start_time === firstSched?.start_time && sched.end_time === firstSched?.end_time
            );
          });
        });

        if (allSame) {
          formState.scheduleType = 1;
          formState.scheduleGeneral = firstDaySchedules.map((s) => ({
            open: s.start_time,
            close: s.end_time,
          }));
        } else {
          formState.scheduleType = 2;
          fillCustomSchedule(daySchedules);
        }
      } else {
        formState.scheduleType = 2;
        fillCustomSchedule(daySchedules);
      }
    }

    const hasData = hasPersistedData();
    persistedSnapshot.value = hasData ? clone(formState) : null;

    handleSavedFormSpecific?.(SECTION, hasData);
    handleIsEditFormSpecific?.(SECTION, hasData);
    handleShowFormSpecific?.(SECTION, hasData);
    if (hasData) {
      markItemComplete('commercial_information'); // Backend usa snake_case, mismo item que otros componentes comerciales
    }
  };

  const fillCustomSchedule = (
    daySchedules: Record<number, Array<{ start_time: string; end_time: string }>>
  ) => {
    formState.schedule.forEach((day) => {
      const dayNum = dayMap[day.day];
      if (daySchedules[dayNum]) {
        day.available_day = true;
        day.schedules = daySchedules[dayNum].map((s) => ({
          open: s.start_time,
          close: s.end_time,
        }));
      } else {
        day.available_day = false;
        day.schedules = [{ open: null, close: null }];
      }
    });
  };

  // Carga
  const loadData = async () => {
    if (!supplierId.value) {
      Object.assign(formState, clone(initState));
      persistedSnapshot.value = null;
      handleSavedFormSpecific?.(SECTION, false);
      handleIsEditFormSpecific?.(SECTION, false);
      handleShowFormSpecific?.(SECTION, false);
      return;
    }

    try {
      isLoading.value = true;
      const resp = await showSupplierAttractions(supplierId.value);

      if (resp?.success) {
        mapResponseToForm(resp.data);
      } else {
        Object.assign(formState, clone(initState));
        persistedSnapshot.value = null;
        handleSavedFormSpecific?.(SECTION, false);
        handleIsEditFormSpecific?.(SECTION, false);
        handleShowFormSpecific?.(SECTION, false);
      }
    } catch (e) {
      console.error('❌ Error cargando datos:', e);
      Object.assign(formState, clone(initState));
      persistedSnapshot.value = null;
      handleSavedFormSpecific?.(SECTION, false);
      handleIsEditFormSpecific?.(SECTION, false);
      handleShowFormSpecific?.(SECTION, false);
    } finally {
      isLoading.value = false;
    }
  };

  // Watchers
  watch(
    supplierId,
    async (newId, oldId) => {
      if (!newId || newId === oldId) return;
      await loadData();
    },
    { immediate: true }
  );

  watch(
    () => formState.scheduleType,
    (newType, oldType) => {
      if (newType === oldType || isRestoring.value) return;

      if (!persistedSnapshot.value) {
        if (newType === 1) {
          formState.schedule = buildEmptySchedule();
          formState.scheduleGeneral = [{ open: null, close: null }];
        } else if (newType === 2) {
          formState.scheduleGeneral = [{ open: null, close: null }];
          formState.schedule = buildEmptySchedule();
        }
      }
    }
  );

  // =========================
  // Time input handling functions (from commercial-information)
  // =========================
  const formatTimeInput = (value: string): string | null => {
    if (!value) return null;

    // Si el input ya está en formato HH:mm válido, devolverlo tal como está
    if (/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value)) {
      return value;
    }

    // Remover cualquier carácter que no sea número
    const cleaned = value.replace(/\D/g, '');

    if (cleaned.length === 0) return null;

    let hours: number;
    let minutes: number;

    if (cleaned.length === 1) {
      // "8" -> "08:00"
      hours = parseInt(cleaned);
      minutes = 0;
    } else if (cleaned.length === 2) {
      // "15" -> "15:00"
      hours = parseInt(cleaned);
      minutes = 0;
    } else if (cleaned.length === 3) {
      // "830" -> "08:30"
      hours = parseInt(cleaned.substring(0, 1));
      minutes = parseInt(cleaned.substring(1, 3));
    } else if (cleaned.length >= 4) {
      // "2300" -> "23:00", "1540" -> "15:40"
      hours = parseInt(cleaned.substring(0, 2));
      minutes = parseInt(cleaned.substring(2, 4));
    } else {
      return null;
    }

    // Validar horas y minutos
    if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
      return null;
    }

    // Formatear como HH:mm
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
  };

  const formatTimeInputOnBlur = (value: string): string | null => {
    if (!value) return null;

    // Si ya está en formato HH:mm válido, devolverlo tal como está
    if (/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value)) {
      return value;
    }

    // Si está en formato xx:x (ejemplo: 23:5), completar con 0
    const incompleteTimeMatch = value.match(/^(\d{1,2}):(\d{1})$/);
    if (incompleteTimeMatch) {
      const hours = parseInt(incompleteTimeMatch[1]);
      const minutes = parseInt(incompleteTimeMatch[2] + '0'); // Agregar 0 al final

      // Validar horas y minutos
      if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
      }
    }

    // Para otros casos, usar la función original
    return formatTimeInput(value);
  };

  const formatTimeWhileTypingImproved = (input: string): string => {
    if (!input) return '';

    const numbers = input.replace(/\D/g, '');

    if (numbers.length === 0) {
      return '';
    } else if (numbers.length === 1) {
      return numbers;
    } else if (numbers.length === 2) {
      return numbers;
    } else if (numbers.length === 3) {
      return `${numbers.substring(0, 2)}:${numbers.substring(2, 3)}`;
    } else if (numbers.length >= 4) {
      return `${numbers.substring(0, 2)}:${numbers.substring(2, 4)}`;
    }

    return numbers;
  };

  const handleTimeInputChange = (
    event: Event,
    section: string,
    field: string,
    dayIndex?: number,
    scheduleIndex?: number,
    generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    const formattedValue = formatTimeWhileTypingImproved(value);
    target.value = formattedValue;

    if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
      if (formState.scheduleGeneral[generalScheduleIndex]) {
        formState.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] = formattedValue;

        // Ejecutar validaciones en tiempo real para "Todos los días"
        handleGeneralScheduleChange(generalScheduleIndex);
      }
    } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
      if (formState.schedule[dayIndex]?.schedules[scheduleIndex]) {
        formState.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
          formattedValue;

        // Ejecutar validaciones en tiempo real para "Personalizado"
        handleCustomScheduleChange(dayIndex, scheduleIndex, field as 'open' | 'close');
      }
    }
  };

  const handleTimeInputBlur = (
    event: Event,
    section: string,
    field: string,
    dayIndex?: number,
    scheduleIndex?: number,
    generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    if (!value.trim()) {
      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.scheduleGeneral[generalScheduleIndex]) {
          formState.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] = null;
        }
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] = null;
        }
      }
      return;
    }

    const formattedTime = formatTimeInputOnBlur(value);

    if (formattedTime) {
      target.value = formattedTime;

      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.scheduleGeneral[generalScheduleIndex]) {
          formState.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] =
            formattedTime;
        }
        handleGeneralScheduleChange(generalScheduleIndex);
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
            formattedTime;
          handleCustomScheduleChange(dayIndex, scheduleIndex, field as 'open' | 'close');
        }
      }
    } else {
      target.value = '';
      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.scheduleGeneral[generalScheduleIndex]) {
          formState.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] = null;
        }
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] = null;
        }
      }
    }
  };

  const handleTimeInputFocus = (
    event: Event,
    _section: string,
    _field: string,
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const currentValue = target.value;

    if (currentValue && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(currentValue)) {
      requestAnimationFrame(() => {
        setTimeout(() => {
          if (document.activeElement === target) {
            target.setSelectionRange(0, currentValue.length);
            target.select();
          }
        }, 1);
      });
    }
  };

  const handleTimeInputClick = (
    event: Event,
    _section: string,
    _field: string,
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const currentValue = target.value;

    if (currentValue && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(currentValue)) {
      event.preventDefault();
      setTimeout(() => {
        target.setSelectionRange(0, currentValue.length);
        target.select();
      }, 0);
    }
  };

  const handleTimeKeyDown = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    // Si presiona Enter o Tab, forzar el blur para formatear
    if (event.key === 'Enter' || event.key === 'Tab') {
      target.blur();
      return;
    }

    // Permitir teclas de control (sin Tab porque ya lo manejamos arriba)
    if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
      return;
    }

    if (/^\d$/.test(event.key)) {
      const selectionStart = target.selectionStart || 0;
      const selectionEnd = target.selectionEnd || 0;
      const selectedText = target.value.substring(selectionStart, selectionEnd);
      const totalLength = target.value.length;

      const isFullySelected =
        (selectionStart === 0 && selectionEnd === totalLength) ||
        (selectedText === target.value && selectedText.length > 0) ||
        (selectionEnd - selectionStart === totalLength && totalLength > 0);

      if (isFullySelected && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(target.value)) {
        target.value = '';
        event.preventDefault();
        target.value = event.key;
        target.dispatchEvent(new Event('input', { bubbles: true }));
        return;
      }

      const currentValue = target.value.replace(/\D/g, '');
      if (currentValue.length >= 4) {
        event.preventDefault();
      }
    } else if (!/^[0-9:]+$/.test(event.key)) {
      event.preventDefault();
    }
  };

  return {
    // estado / refs
    formRef,
    formState,
    isLoading,
    isLoadingButton,

    // helpers UI
    timeOptions,
    spinTip,
    spinning,
    isEditMode,

    // getters globales
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,

    // acciones
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    handleGeneralScheduleChange,
    handleCustomScheduleChange,
    handleSave,
    handleClose,
    handleShowForm,

    // Nuevas funciones para entrada manual de tiempo
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeInputFocus,
    handleTimeInputClick,
    handleTimeKeyDown,
  };
}
