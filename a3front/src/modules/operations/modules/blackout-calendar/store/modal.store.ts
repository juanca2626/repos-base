// src/stores/modalStore.ts
import { defineStore, storeToRefs } from 'pinia';
import { ref, computed, toRaw } from 'vue';
import dayjs, { Dayjs } from 'dayjs';
import { useBlockCalendarStore } from './blockCalendar.store';
import { useFiltersFormStore } from './filtersForm.store';
import { useDataStore } from '@operations/modules/blackout-calendar/store/data.store'; // Asegúrate de que la ruta es correcta
// import { useDataStore } from './data.store';

export const useModalStore = defineStore('modal', () => {
  const blockCalendarStore = useBlockCalendarStore();
  const filtersFormStore = useFiltersFormStore();

  const dataStore = useDataStore();
  const { blockingReasonsOptions } = storeToRefs(dataStore);
  // const dataStore = useDataStore();

  const isModalVisible = ref(false);
  const modalTitle = ref('');
  const modalType = ref('');
  const modalData = ref<any>({});
  const loading = ref(false); // Añadir estado de carga

  const formUpdateLock = ref({
    lock_id: '',
    provider: '',
    blockingReason: { value: '', label: '' },
    completeDay: true,
    timeRange: [dayjs(), dayjs()] as [Dayjs, Dayjs],
    observations: '',
  });
  const formDeleteLock = ref({
    lock_id: '',
  });

  // const blockingReasonsOptions = computed(() =>
  //   blockCalendarStore.blockingReasons.map((reason: any) => ({
  //     value: reason._id,
  //     label: `${reason.iso} - ${reason.description}`,
  //     disabled: reason.guide_type === 'A',
  //   }))
  // );

  const selectedLocksSummary = computed(() => {
    return blockCalendarStore.locksByProvidersAndSelectedDates.flatMap((providerEntry) => {
      return providerEntry.locks.map((lock) => {
        const startDate = new Date(lock.datetime_start);
        const endDate = new Date(lock.datetime_end);
        const dates =
          startDate.toLocaleDateString() === endDate.toLocaleDateString()
            ? startDate.toLocaleDateString()
            : `${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}`;
        const time = lock.complete_day
          ? 'Día completo'
          : startDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              }) ===
              endDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              })
            ? startDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              })
            : `${startDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              })} - ${endDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              })}`;
        return {
          code: providerEntry.provider.code,
          motive: lock.blocking_reason_id.iso,
          dates,
          time,
        };
      });
    });
  });

  const showModal = (type: string, data: any) => {
    modalType.value = type;
    if (type === 'update') {
      modalTitle.value = 'Editar bloqueo';
    } else if (type === 'deleteOne') {
      modalTitle.value = 'Eliminar bloqueo';
    } else {
      modalTitle.value = 'Eliminar bloqueo(s)';
    }
    // modalTitle.value = type === 'update' ? 'Editar bloqueo' : 'Eliminar bloqueo(s)';
    modalData.value = data;
    isModalVisible.value = true;

    if (type === 'update' || type === 'deleteOne') {
      formUpdateLock.value = {
        lock_id: data.lock._id,
        provider: data.provider,
        blockingReason: {
          value: data.lock.blocking_reason_id._id,
          label: `${data.lock.blocking_reason_id.iso} - ${data.lock.blocking_reason_id.description}`,
        },
        completeDay: data.lock.complete_day,
        timeRange: [dayjs(data.lock.datetime_start), dayjs(data.lock.datetime_end)],
        observations: data.lock.observations,
      };
      formDeleteLock.value.lock_id = data.lock._id;
    }
  };

  const handleModalOk = async () => {
    loading.value = true; // Iniciar estado de carga
    try {
      if (modalType.value == 'update') {
        await updateLock();
      } else {
        await deleteLocks();
      }
    } finally {
      await filtersFormStore.onSubmit();
      blockCalendarStore.deselectAllDays();
      loading.value = false; // Finalizar estado de carga
    }
    isModalVisible.value = false;
  };

  const updateLock = async () => {
    const payload = toRaw(formUpdateLock.value);

    const updateLockData = {
      blocking_reason_id: payload.blockingReason.value,
      provider_id: payload.provider._id,
      complete_day: payload.completeDay,
      datetime_start: payload.timeRange[0].format('YYYY-MM-DDTHH:mm'),
      datetime_end: payload.timeRange[1].format('YYYY-MM-DDTHH:mm'),
      observations: payload.observations,
      created_by: 'test', //TODO: Revisar de donde sacar el usuario
    };

    await blockCalendarStore.updateLock(payload.lock_id, updateLockData);
    // handleModalCancel();
  };

  const deleteLocks = async () => {
    let allLockIds = [];
    if (modalType.value === 'deleteMultiple') {
      allLockIds = blockCalendarStore.locksByProvidersAndSelectedDates.flatMap(
        (providerEntry: any) => providerEntry.locks.map((lock: any) => lock._id)
      );
    } else {
      allLockIds = [formDeleteLock.value.lock_id];
    }

    await blockCalendarStore.deleteLocks({ locks: allLockIds });
    // handleModalCancel();
    // blockCalendarStore.deselectAllDays();
  };

  const handleModalCancel = () => {
    isModalVisible.value = false;
  };

  return {
    isModalVisible,
    modalTitle,
    modalType,
    modalData,
    formUpdateLock,
    blockingReasonsOptions, // ✅ Ahora este valor viene del dataStore
    selectedLocksSummary,
    showModal,
    handleModalOk,
    handleModalCancel,
    loading, // Exportar estado de carga
  };
});
