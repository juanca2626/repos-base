<template>
  s
  <a-modal class="vLayout" :open="modalActive" centered :closable="false" :width="488">
    <template #title>
      <div class="custom-header">
        <h5>Editar bloqueo</h5>
        <div class="icon-close" @click="close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 30">
            <path
              d="M23 7.5L8 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8 7.5L23 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
      </div>
    </template>
    <template #footer>
      <a-button key="back" @click="close" size="large"> Cancelar </a-button>
      <a-button key="submit" type="primary" :loading="loading" @click="updateLock" size="large">
        Guardar
      </a-button>
    </template>
    <div>
      <!-- {{ modalData }} -->
      <a-flex justify="space-between">
        <a-tag class="custom-tag" color="purple">{{ modalData.provider.code }}</a-tag>
        <a-typography-paragraph class="my-0" strong>
          {{ modalData.provider.fullname }}
        </a-typography-paragraph>
      </a-flex>
      <a-flex gap="middle" vertical class="my-3">
        <a-typography-text strong>Motivo de bloqueo:</a-typography-text>
        <a-select
          v-model:value="formUpdateLock.blockingReason"
          label-in-value
          placeholder="Seleccione un motivo de bloqueo"
          :options="blockingReasonsOptions"
        />
      </a-flex>
      <a-flex gap="middle" vertical class="mt-4">
        <a-typography-paragraph class="my-0">
          <a-flex align="middle">
            <a-typography-text strong style="margin-right: 8px">
              Fechas y horario:
            </a-typography-text>
            <a-checkbox v-model:checked="formUpdateLock.completeDay" style="margin-left: auto">
              Día completo
            </a-checkbox>
          </a-flex>
        </a-typography-paragraph>
        <a-range-picker
          v-model:value="formUpdateLock.timeRange"
          :show-time="formUpdateLock.completeDay ? false : { format: 'HH:mm' }"
          :format="formUpdateLock.completeDay ? 'DD/MM/YYYY' : 'DD/MM/YYYY HH:mm'"
          :placeholder="['Fecha Inicio', 'Fecha Fin']"
          @change="onRangeChange"
          @ok="onRangeOk"
        />
      </a-flex>
      <a-flex gap="middle" vertical class="my-3">
        <a-typography-text strong>Observaciones: </a-typography-text>
        <a-textarea v-model:value="formUpdateLock.observations" show-count :maxlength="300" />
      </a-flex>
    </div>
  </a-modal>
</template>

<script lang="ts" setup>
  import {
    computed,
    onBeforeMount,
    onMounted,
    onUnmounted,
    reactive,
    ref,
    toRaw,
    type UnwrapRef,
  } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { storeToRefs } from 'pinia';
  import { useBlockCalendarStore } from '../store/blockCalendar.store';
  import type { SelectProps } from 'ant-design-vue';

  // STORE SETUP
  const blockCalendarStore = useBlockCalendarStore();
  const { blockingReasons, modalData } = storeToRefs(blockCalendarStore);

  const blockingReasonsOptions = computed<SelectProps['options']>(() =>
    blockingReasons.value.map((reason) => ({
      value: reason._id,
      label: `${reason.iso} - ${reason.description}`,
      disabled: reason.guide_type === 'A', // Deshabilitar opciones de tipo 'A'
    }))
  );

  type RangeValue = [Dayjs, Dayjs];
  interface FormState {
    lock_id: string;
    provider: string;
    blockingReason: { value: string; label: string };
    completeDay: boolean;
    timeRange: RangeValue;
    observations: string;
  }

  const formUpdateLock: UnwrapRef<FormState> = reactive({
    lock_id: '',
    provider: '',
    blockingReason: { value: '', label: '' },
    completeDay: true,
    timeRange: [dayjs(), dayjs()] as RangeValue,
    observations: '',
  });

  interface Props {
    modalActive: boolean;
  }

  defineProps<Props>();
  const emit = defineEmits(['close']);
  const close = () => {
    emit('close');
  };

  // MODAL
  const loading = ref<boolean>(false);

  // Handlers
  const updateLock = async () => {
    const payload = toRaw(formUpdateLock);

    const updateLockData = {
      blocking_reason_id: payload.blockingReason.value,
      provider_id: payload.provider._id,
      complete_day: payload.completeDay,
      datetime_start: payload.timeRange[0].format('YYYY-MM-DDTHH:mm'),
      datetime_end: payload.timeRange[1].format('YYYY-MM-DDTHH:mm'),
      observations: payload.observations,
      created_by: localStorage.getItem('user_code'),
    };

    await blockCalendarStore.updateLock(payload.lock_id, updateLockData);
    loading.value = false;
    close();
  };

  const onRangeChange = (value: Dayjs[], dateString: [string, string]) => {
    console.log('Selected Time: ', value);
    console.log('Formatted Selected Time: ', dateString);
  };

  const onRangeOk = (value: Dayjs[]) => {
    console.log('onOk: ', value);
  };

  onBeforeMount(() => {
    console.log('onBeforeMount: VModalComponent');
    console.log('modalData', modalData.value);
    formUpdateLock.lock_id = modalData.value.lock._id;
    formUpdateLock.provider = modalData.value.provider;
    formUpdateLock.blockingReason = {
      value: modalData.value.lock.blocking_reason_id._id,
      label: `${modalData.value.lock.blocking_reason_id.iso} - ${modalData.value.lock.blocking_reason_id.description}`,
    };
    formUpdateLock.completeDay = modalData.value.lock.complete_day;
    formUpdateLock.timeRange = [
      dayjs(modalData.value.lock.datetime_start),
      dayjs(modalData.value.lock.datetime_end),
    ];
    formUpdateLock.observations = modalData.value.lock.observations;
  });

  // Initialize form data on mount
  onMounted(() => {
    console.log('onMounted: VModalComponent');
  });

  onUnmounted(() => {
    console.log('onUnmounted: VModalComponent');
  });
</script>

<style lang="scss" scoped>
  .custom-tag {
    font-weight: 600;
    font-size: 14px;
  }
</style>
