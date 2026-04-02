// composables/useSharedService.ts
import { ref, computed } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import type { FormState, Schedule, Activity } from '../interfaces/shared-service.interface';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';

export const useSharedService = () => {
  const { isServiceTypeGeneral, isServiceTypeMultiDays } = useSelectedServiceType();

  const formRef = ref<FormInstance>();
  const isLoadingButton = ref(false);

  const formState = ref<FormState>({
    serviceName: '',
    subtype: undefined,
    profile: undefined,
    startPoint: undefined,
    endPoint: undefined,
    duration: '',
    measurementUnit: undefined,
    minCapacity: undefined,
    maxCapacity: undefined,
    includesChildren: false,
    includesInfants: false,
    status: undefined,
    reason: '',
    showToClient: undefined,
    typeText: [],
    itinerary: '',
    menu: '',
    remarks: '',
  });

  const formRules = {
    serviceName: [
      { required: true, message: 'El nombre del servicio es requerido', trigger: 'blur' },
    ],
    subtype: [{ required: true, message: 'El subtipo es requerido', trigger: 'change' }],
    profile: [{ required: true, message: 'El perfil es requerido', trigger: 'change' }],
    startPoint: [{ required: true, message: 'El punto de inicio es requerido', trigger: 'change' }],
    endPoint: [{ required: true, message: 'El punto de fin es requerido', trigger: 'change' }],
    status: [{ required: true, message: 'El estado es requerido', trigger: 'change' }],
    showToClient: [{ required: true, message: 'Este campo es requerido', trigger: 'change' }],
  };

  const subtipoOptions = [
    { label: 'Subtipo A', value: 'subtipo_a' },
    { label: 'Subtipo B', value: 'subtipo_b' },
    { label: 'Subtipo C', value: 'subtipo_c' },
  ];

  const perfilOptions = [
    { label: 'Perfil Administrativo', value: 'perfil_admin' },
    { label: 'Perfil Operativo', value: 'perfil_operativo' },
    { label: 'Perfil Ejecutivo', value: 'perfil_ejecutivo' },
  ];

  const puntoInicioOptions = [
    { label: 'Punto A', value: 'punto_a' },
    { label: 'Punto B', value: 'punto_b' },
    { label: 'Punto C', value: 'punto_c' },
    { label: 'Punto D', value: 'punto_d' },
  ];

  const puntoFinOptions = [
    { label: 'Punto X', value: 'punto_x' },
    { label: 'Punto Y', value: 'punto_y' },
    { label: 'Punto Z', value: 'punto_z' },
    { label: 'Punto W', value: 'punto_w' },
  ];

  const estadoOptions = [
    { label: 'Activo', value: 'activo' },
    { label: 'Suspendido', value: 'suspendido' },
    { label: 'Inactivo', value: 'inactivo' },
  ];

  const measurementUnitsByServiceType = {
    multiDays: [{ label: 'Paquete', value: 'package' }],
    default: [
      { label: 'Pasajeros', value: 'pax' },
      { label: 'Unidad', value: 'unit' },
    ],
  };

  const measurementUnitOptions = computed(() => {
    return isServiceTypeMultiDays.value
      ? measurementUnitsByServiceType.multiDays
      : measurementUnitsByServiceType.default;
  });

  // ========== Service Schedule State ==========
  const schedules = ref<Schedule[]>([
    { id: 1, time: '9:00', selected: true },
    { id: 2, time: '14:00', selected: false },
  ]);

  const activities = ref<Activity[]>([
    { duration: '00:00', activity: '', timeRange: '9:00 / 10:00' },
  ]);

  const typeTextOptions = ref([
    { label: 'Itinerario', value: 'itinerario' },
    { label: 'Menú', value: 'menu' },
    { label: 'Remarks', value: 'remarks' },
  ]);

  const totalDuration = computed(() => {
    const total = activities.value.reduce((acc, activity) => {
      const [hours, minutes] = activity.duration.split(':').map(Number);
      return acc + hours * 60 + minutes;
    }, 0);

    const hours = Math.floor(total / 60);
    const minutes = total % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
  });

  const selectSchedule = (id: number) => {
    schedules.value.forEach((schedule) => {
      schedule.selected = schedule.id === id;
    });
  };

  const addActivity = () => {
    activities.value.push({
      duration: '00:00',
      activity: '',
      timeRange: '9:00 / 10:00',
    });
  };

  const removeActivity = (index: number) => {
    if (activities.value.length > 1) {
      activities.value.splice(index, 1);
    }
  };

  const updateActivity = (index: number, field: keyof Activity, value: string) => {
    activities.value[index][field] = value;
  };

  // ========== Form Validation ==========
  const getIsFormValid = computed(() => {
    return (
      formState.value.serviceName.trim() !== '' &&
      (!isServiceTypeGeneral.value || formState.value.subtype !== undefined) &&
      formState.value.profile !== undefined &&
      formState.value.startPoint !== undefined &&
      formState.value.endPoint !== undefined &&
      formState.value.status !== undefined &&
      formState.value.showToClient !== undefined
    );
  });

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      isLoadingButton.value = true;

      console.log('Guardando datos:', {
        form: formState.value,
        schedules: schedules.value,
        activities: activities.value,
        totalDuration: totalDuration.value,
      });

      await new Promise((resolve) => setTimeout(resolve, 1000));

      console.log('Datos guardados exitosamente');
    } catch (error) {
      console.error('Error de validación:', error);
    } finally {
      isLoadingButton.value = false;
    }
  };

  return {
    // Form state
    formState,
    formRef,
    formRules,
    subtipoOptions,
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    measurementUnitOptions,
    isLoadingButton,
    getIsFormValid,
    isServiceTypeGeneral,
    isServiceTypeMultiDays,
    handleSave,

    // Service Schedule state
    schedules,
    activities,
    totalDuration,
    selectSchedule,
    addActivity,
    removeActivity,
    updateActivity,

    typeTextOptions,
  };
};
