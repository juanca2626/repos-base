import { computed, reactive, ref } from 'vue';
import type { BlockingReason, State } from '../interfaces';
// import { useBlockCalendarStore } from '../store/blockCalendar.store';

export const useBlockCalendar = () => {
  const showDrawer = ref<boolean>(false);
  const blockingReasons = ref<BlockingReason[]>([]);
  const lockDataByMonth = ref<[]>([]);
  const state: State = reactive<State>({
    showModal: false,
    // checked1: true,
    // showModalEliminar: false,
    // showModalGuardar: false,
    // showModalGuardarComo: false,
    // showModalEliminarServicio: false,
    // showModalItinerarioDetalle: false,
    // showModalSkeletonDetalle: false,
    // openDownload: false,
  });
  // const blockCalendarStore = useBlockCalendarStore();

  const isLoading = computed(() => blockingReasons.value.length === 0);

  const handlerShowDrawer = (show: boolean) => {
    showDrawer.value = show;
  };

  const showModal = () => {
    state.showModal = true;
  };

  return {
    // Properties
    showDrawer,
    isLoading,
    lockDataByMonth,
    state,
    // Methods
    handlerShowDrawer,
    showModal,
    // fetchLocks,
  };
};
