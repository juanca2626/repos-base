import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';

type ScheduleSlot = { open: string | null; close: string | null };

type ScheduleDay = {
  day: string;
  label: string;
  available_day?: boolean;
  schedules: ScheduleSlot[];
  open?: string | null;
  close?: string | null;
  visible?: boolean;
};

type CommercialFormState = {
  type_food_id: any;
  classification: any;
  amenities: any[];
  spaces: any[];
  schedule: ScheduleDay[];
  additional_information: any;
  scheduleType: number;
  scheduleGeneral: ScheduleSlot[];
};

export const useInformationCommercialStore = defineStore('informationCommercialStore', () => {
  const initialFormData = ref<CommercialFormState>({
    type_food_id: [],
    classification: 0,
    amenities: [],
    spaces: [{ spaces: undefined, capacity: undefined }],
    schedule: [
      {
        day: 'MONDAY',
        label: 'Lun',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'TUESDAY',
        label: 'Mar',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'WEDNESDAY',
        label: 'Mié',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'THURSDAY',
        label: 'Jue',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'FRIDAY',
        label: 'Vie',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'SATURDAY',
        label: 'Sáb',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
      {
        day: 'SUNDAY',
        label: 'Dom',
        schedules: [{ open: null, close: null }],
        available_day: true,
        visible: true,
      },
    ],
    additional_information: null,
    scheduleType: 1,
    scheduleGeneral: [{ open: null, close: null }],
  });

  const formState = ref<CommercialFormState>({ ...initialFormData.value });

  const formRef = ref<FormInstance | null>(null);

  const showAdditionalInfo = ref<boolean>(false);
  const typeFoods = ref<any>([]);
  const amenities = ref<any>([]);
  const timeOptions = ref<string[]>([]);

  const resetForm = () => {
    formState.value = { ...initialFormData.value };
  };

  return {
    resetForm,
    typeFoods,
    amenities,
    timeOptions,
    initialFormData,
    formState,
    formRef,
    showAdditionalInfo,
  };
});
