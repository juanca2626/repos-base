import { ref, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import dayjs from 'dayjs';
import { useRoute } from 'vue-router';
import type { FormInstance } from 'ant-design-vue';
import type { TrainServiceFormState, TrainSchedule } from '../interfaces/train-service.interface';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { trainServiceDetailsService } from '../services/trainServiceDetailsService';
import type { TrainServiceDetailsRequest } from '../interfaces/train-service-details.interface';
import { ServiceStatusApi } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
export interface UseTrainServiceProps {
  currentKey: string;
  currentCode: string;
}

export const useTrainService = (props: UseTrainServiceProps) => {
  const { currentKey, currentCode } = props;
  const route = useRoute();

  const navigationStore = useNavigationStore();
  const supportResourcesStore = useSupportResourcesStore();
  const trainConfigurationStore = useTrainConfigurationStore();

  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const { pickupPoints } = storeToRefs(supportResourcesStore);

  const formRef = ref<FormInstance>();
  const isLoadingButton = ref(false);
  const showEditModal = ref(false);
  const schedules = ref<TrainSchedule[]>([]);

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const formState = ref<TrainServiceFormState>({
    serviceName: '',
    startPoint: undefined,
    endPoint: undefined,
    status: ServiceStatusApi.ACTIVE,
    reason: '',
    showToClient: true,
  });

  const formRules = computed(() => ({
    serviceName: [
      { required: true, message: 'El nombre del servicio es requerido', trigger: 'blur' },
    ],
    startPoint: [{ required: true, message: 'El punto de inicio es requerido', trigger: 'change' }],
    endPoint: [{ required: true, message: 'El punto de fin es requerido', trigger: 'change' }],
    status: [{ required: true, message: 'El estado es requerido', trigger: 'change' }],
    reason: [
      {
        validator: (_rule: any, value: string) => {
          const status = formState.value.status;
          if (status === ServiceStatusApi.SUSPENDED || status === ServiceStatusApi.INACTIVE) {
            if (!value || !value.trim()) {
              return Promise.reject(
                'El motivo es requerido cuando el estado es Suspendido o Inactivo'
              );
            }
          }
          return Promise.resolve();
        },
        trigger: 'blur',
      },
    ],
    showToClient: [{ required: true, message: 'Este campo es requerido', trigger: 'change' }],
  }));

  const statusOptions = [
    { label: 'Activo', value: 'ACTIVE' },
    { label: 'Suspendido', value: 'SUSPENDED' },
    { label: 'Inactivo', value: 'INACTIVE' },
  ];

  const totalFieldsCount = 5;

  const serviceDetail = computed(() => {
    if (currentKey && currentCode) {
      return trainConfigurationStore.getServiceDetail(currentKey, currentCode) as any;
    }
    return null;
  });

  const startPointOptions = computed(() => {
    return pickupPoints.value.map((pointType) => ({
      label: pointType.name,
      value: pointType.code,
    }));
  });

  const endPointOptions = computed(() => {
    return pickupPoints.value.map((pointType) => ({
      label: pointType.name,
      value: pointType.code,
    }));
  });

  const getIsFormValid = computed(() => {
    const status = formState.value.status;
    const hasReason = !!formState.value.reason?.trim();
    const reasonRequired =
      status === ServiceStatusApi.SUSPENDED || status === ServiceStatusApi.INACTIVE;
    const reasonValid = !reasonRequired || hasReason;

    return (
      formState.value.serviceName.trim() !== '' &&
      formState.value.startPoint !== undefined &&
      formState.value.endPoint !== undefined &&
      formState.value.status !== undefined &&
      formState.value.showToClient !== undefined &&
      reasonValid
    );
  });

  const completedFieldsCount = computed(() => {
    let count = 0;
    if (formState.value.serviceName && formState.value.serviceName.trim() !== '') count++;
    if (formState.value.startPoint !== undefined) count++;
    if (formState.value.endPoint !== undefined) count++;
    if (formState.value.status !== undefined) count++;
    if (formState.value.showToClient !== undefined) count++;
    return count;
  });

  const shouldShowReason = computed(() => {
    const status = formState.value.status;
    const hasReason = !!formState.value.reason?.trim();

    if (status === ServiceStatusApi.SUSPENDED || status === ServiceStatusApi.INACTIVE) {
      return true;
    }

    if (status === ServiceStatusApi.ACTIVE && hasReason) {
      return true;
    }

    return false;
  });

  const convertDurationToHHmm = (duration: string): string => {
    if (!duration) return '00:00';

    const hoursMatch = duration.match(/(\d+)\s*Hora/i);
    const minutesMatch = duration.match(/(\d+)\s*min/i);

    const hours = hoursMatch ? parseInt(hoursMatch[1]) : 0;
    const minutes = minutesMatch ? parseInt(minutesMatch[1]) : 0;

    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
  };

  const convertDurationFromHHmm = (duration: string): string => {
    if (!duration || duration.length < 5) return '';

    try {
      const [hours, minutes] = duration.split(':').map(Number);

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

  const isValidBackendId = (id: string | undefined | null): boolean => {
    if (!id || id === '0') return false;
    const objectIdRegex = /^[0-9a-fA-F]{24}$/;
    return objectIdRegex.test(id);
  };

  const initializeSchedules = () => {
    if (schedules.value.length === 0) {
      schedules.value = [
        {
          id: '0',
          frequency: '',
          fareType: '',
          startTime: '',
          endTime: '',
          duration: '',
          daysOfWeek: [],
        },
      ];
    }
  };

  const loadServiceDetailData = () => {
    clearForm();

    if (!serviceDetail.value || !currentKey || !currentCode) {
      return;
    }

    const { content } = serviceDetail.value;

    if (!content) return;

    formState.value.serviceName = content.basicInfo?.name || '';
    // Siempre arrays en API; por ahora usamos el primer valor
    formState.value.startPoint = content.basicInfo?.startTrainStationCodes?.[0];
    formState.value.endPoint = content.basicInfo?.endTrainStationCodes?.[0];

    formState.value.status = (content.status?.state as ServiceStatusApi) || ServiceStatusApi.ACTIVE;
    formState.value.reason = content.status?.reason || '';
    formState.value.showToClient = content.status?.clientVisible ?? false;

    if (content.frequencies && Array.isArray(content.frequencies)) {
      schedules.value = content.frequencies.map((frequency: any) => ({
        id: frequency.id || String(Date.now() + Math.random()),
        frequency: frequency.code || '',
        fareType: frequency.fareType || '',
        startTime: frequency.schedule?.departure || '',
        endTime: frequency.schedule?.arrival || '',
        duration: convertDurationFromHHmm(frequency.schedule?.duration || ''),
        daysOfWeek: frequency.schedule?.operatingDays || [],
      }));

      const validityRowsData: Record<string, any[]> = {};
      content.frequencies.forEach((frequency: any) => {
        if (frequency.id && frequency.validityPeriods && Array.isArray(frequency.validityPeriods)) {
          validityRowsData[frequency.id] = frequency.validityPeriods.map((vp: any) => ({
            startDate: vp.startDate ? dayjs(vp.startDate) : null,
            endDate: vp.endDate ? dayjs(vp.endDate) : null,
          }));
        } else if (frequency.id) {
          validityRowsData[frequency.id] = [];
        }
      });

      if (schedules.value.length > 0) {
        trainConfigurationStore.setTrainServiceDetailsSchedule(currentKey, currentCode, {
          schedules: schedules.value,
          validityRows: validityRowsData,
        });
      }
    }
  };

  const addSchedule = () => {
    const newSchedule: TrainSchedule = {
      id: String(Date.now()),
      frequency: '',
      fareType: '',
      startTime: '',
      endTime: '',
      duration: '',
      daysOfWeek: [],
    };
    schedules.value.push(newSchedule);
  };

  const removeSchedule = (id: string) => {
    schedules.value = schedules.value.filter((schedule) => schedule.id !== id);
  };

  const duplicateSchedule = (schedule: TrainSchedule) => {
    const duplicated: TrainSchedule = {
      ...schedule,
      id: String(Date.now()),
    };
    schedules.value.push(duplicated);
  };

  const getStartPointLabel = (value: any) => {
    return startPointOptions.value.find((opt) => opt.value === value)?.label || '';
  };

  const getEndPointLabel = (value: any) => {
    return endPointOptions.value.find((opt) => opt.value === value)?.label || '';
  };

  const getStatusLabel = (value: any) => {
    return statusOptions.find((opt) => opt.value === value)?.label || '';
  };

  const handleEditMode = () => {
    showEditModal.value = true;
  };

  const handleConfirmEdit = () => {
    if (getSectionsItemActive.value) {
      getSectionsItemActive.value.editing = true;
    }
    showEditModal.value = false;
  };

  const handleCancelEdit = () => {
    showEditModal.value = false;
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      isLoadingButton.value = true;

      const productSupplierId = route.params.id as string;
      if (!productSupplierId) {
        throw new Error('Product Supplier ID no encontrado');
      }

      if (!currentKey || !currentCode) {
        throw new Error('Location Key o Category Code no encontrados');
      }

      const trainScheduleData = trainConfigurationStore.getTrainServiceDetailsSchedule(
        currentKey,
        currentCode
      );
      const validityRowsData = trainScheduleData?.validityRows || {};

      const frequencies = schedules.value.map((schedule) => {
        const scheduleValidityRows = validityRowsData[schedule.id] || [];

        if (!Array.isArray(scheduleValidityRows)) {
          console.warn(
            `ValidityRows para schedule ${schedule.id} no es un array:`,
            scheduleValidityRows
          );
          return {
            id: isValidBackendId(schedule.id) ? schedule.id : null,
            code: schedule.frequency,
            fareType: schedule.fareType,
            schedule: {
              departure: schedule.startTime,
              arrival: schedule.endTime,
              duration: convertDurationToHHmm(schedule.duration),
              operatingDays: schedule.daysOfWeek || [],
            },
            validityPeriods: [],
          };
        }

        const validityPeriods = scheduleValidityRows
          .map((validity, index) => {
            if (!validity || typeof validity !== 'object') {
              console.warn(`Validity inválido en índice ${index}:`, validity);
              return null;
            }

            if (!('startDate' in validity) || !('endDate' in validity)) {
              console.warn(`Validity en índice ${index} no tiene startDate o endDate:`, validity);
              return null;
            }

            if (!validity.startDate || !validity.endDate) {
              console.log(`Validity en índice ${index} tiene fechas vacías:`, validity);
              return null;
            }

            try {
              const startDate = dayjs(validity.startDate).format('YYYY-MM-DD');
              const endDate = dayjs(validity.endDate).format('YYYY-MM-DD');

              if (
                !startDate ||
                !endDate ||
                startDate === 'Invalid Date' ||
                endDate === 'Invalid Date'
              ) {
                console.warn(`Fechas inválidas en índice ${index}:`, {
                  startDate,
                  endDate,
                  validity,
                });
                return null;
              }

              return {
                startDate,
                endDate,
              };
            } catch (error) {
              console.error(`Error procesando validity en índice ${index}:`, error, validity);
              return null;
            }
          })
          .filter((vp): vp is { startDate: string; endDate: string } => vp !== null);

        return {
          id: isValidBackendId(schedule.id) ? schedule.id : null,
          code: schedule.frequency,
          fareType: schedule.fareType,
          schedule: {
            departure: schedule.startTime,
            arrival: schedule.endTime,
            duration: convertDurationToHHmm(schedule.duration),
            operatingDays: schedule.daysOfWeek || [],
          },
          validityPeriods,
        };
      });

      const request: TrainServiceDetailsRequest = {
        id: serviceDetail.value?.id || null,
        groupingKeys: {
          operatingLocationKey: currentKey,
          trainTypeCode: currentCode,
        },
        content: {
          basicInfo: {
            name: formState.value.serviceName,
            startTrainStationCodes: formState.value.startPoint ? [formState.value.startPoint] : [],
            endTrainStationCodes: formState.value.endPoint ? [formState.value.endPoint] : [],
          },
          frequencies,
          status: {
            state: (formState.value.status as ServiceStatusApi) || ServiceStatusApi.ACTIVE,
            reason: formState.value.reason || '',
            clientVisible: formState.value.showToClient || false,
          },
        },
        completionStatus: 'COMPLETED',
      };

      const response = await trainServiceDetailsService.saveTrainServiceDetails(
        productSupplierId,
        request
      );

      if (response.success && response.data) {
        const updatedDetail = {
          id: response.data.id,
          groupingKeys: {
            operatingLocationKey: currentKey,
            trainTypeCode: currentCode,
          },
          content: response.data.content as any,
          completionStatus: response.data.completionStatus || 'COMPLETED',
        };
        trainConfigurationStore.updateServiceDetail(updatedDetail);
        if (
          response.data.content?.frequencies &&
          Array.isArray(response.data.content.frequencies)
        ) {
          const mappedSchedules = response.data.content.frequencies.map((frequency: any) => ({
            id: frequency.id || String(Date.now() + Math.random()),
            frequency: frequency.code || '',
            fareType: frequency.fareType || '',
            startTime: frequency.schedule?.departure || '',
            endTime: frequency.schedule?.arrival || '',
            duration: convertDurationFromHHmm(frequency.schedule?.duration || ''),
            daysOfWeek: frequency.schedule?.operatingDays || [],
          }));

          const validityRowsData: Record<string, any[]> = {};
          response.data.content.frequencies.forEach((frequency: any) => {
            if (
              frequency.id &&
              frequency.validityPeriods &&
              Array.isArray(frequency.validityPeriods)
            ) {
              validityRowsData[frequency.id] = frequency.validityPeriods.map((vp: any) => ({
                startDate: vp.startDate ? dayjs(vp.startDate) : null,
                endDate: vp.endDate ? dayjs(vp.endDate) : null,
              }));
            } else if (frequency.id) {
              validityRowsData[frequency.id] = [];
            }
          });

          if (mappedSchedules.length > 0 && currentKey && currentCode) {
            trainConfigurationStore.setTrainServiceDetailsSchedule(currentKey, currentCode, {
              schedules: mappedSchedules,
              validityRows: validityRowsData,
            });
          }
        }
      } else {
        throw new Error('Error al guardar los datos');
      }
    } catch (error) {
      console.error('Error al guardar:', error);
      throw error;
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSaveAndAdvance = async () => {
    try {
      await handleSave();
      setCompletedItem(currentKey, currentCode, getSectionsItemActive.value?.id || '');
    } catch (error) {
      console.log(error);
      throw error;
    }
  };

  const clearForm = () => {
    formState.value.serviceName = '';
    formState.value.startPoint = undefined;
    formState.value.endPoint = undefined;
    formState.value.status = ServiceStatusApi.ACTIVE;
    formState.value.reason = '';
    formState.value.showToClient = true;
    initializeSchedules();
  };

  watch(
    () => [currentKey, currentCode],
    () => {
      loadServiceDetailData();
    },
    { immediate: true }
  );

  return {
    formState,
    formRef,
    formRules,
    startPointOptions,
    endPointOptions,
    statusOptions,
    isLoadingButton,
    getIsFormValid,
    schedules,
    isEditingContent,
    showEditModal,
    totalFieldsCount,
    completedFieldsCount,
    shouldShowReason,

    handleSave,
    addSchedule,
    removeSchedule,
    duplicateSchedule,
    getStartPointLabel,
    getEndPointLabel,
    getStatusLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    handleSaveAndAdvance,
  };
};
