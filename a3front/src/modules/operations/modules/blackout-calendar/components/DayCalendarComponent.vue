<template>
  <div
    :class="[
      lock ? lock.meta.className : 'common-class',
      lock?.selected ? 'selected-class' : '',
      isHoliday(dayCurrent) ? 'holiday' : '',
    ]"
    :style="[
      lock?.meta.countNonSVS > 0 ? { 'background-color': '#F2F2F2' } : {},
      dayCurrent === getDateNow() ? { 'border-width': '2px', 'background-color': '#F2F2F2' } : {},
    ]"
    @click="handleClick"
  >
    <div
      :class="
        lock?.meta.countSVS > 0 && lock?.meta.countNonSVS > 0
          ? 'common-class-info grow'
          : 'common-class-info'
      "
      :style="[dayCurrent === getDateNow() ? { 'font-weight': 'bold' } : {}]"
      :title="
        getLockISO_NonSVS.iso === 'MUL'
          ? 'Múltiples bloqueos'
          : lock?.details[getLockISO_NonSVS.index]?.description
      "
    >
      {{ getLockISO_NonSVS.iso }}
    </div>

    <div class="lock-service" v-if="lock?.meta.countSVS > 0">
      <div class="font-semibold text-indigo-800 text-[11px]">{{ lock?.meta.countSVS }} svs</div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { computed, defineProps, defineEmits } from 'vue';

  import { formatDate, getDateNow, isHoliday } from '../utils/dateUtils';

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  interface Props {
    provider: unknown;
    locks: Record<string, unknown>;
    day: number;
    month: number;
    year: number;
  }

  const props = defineProps<Props>();
  const emit = defineEmits(['click']);

  const dayCurrent = computed(() => {
    return formatDate(`${props.year}-${props.month}-${props.day}`);
  });

  const lockKey = computed(() => formatDate(`${props.year}-${props.month}-${props.day}`));
  const lock = computed(() => props.locks[lockKey.value]);

  const getLockISO_NonSVS = computed(() => {
    if (!lock.value) return { index: 0, iso: '' };

    let countLocksNonSVS = 0;
    let indexLockNonSVS = 0;
    lock.value.details.forEach((reason: unknown, k: number) => {
      if (reason.iso !== 'SVS') {
        indexLockNonSVS = k;
        countLocksNonSVS++;
      }
    });

    if (countLocksNonSVS > 1) return { index: indexLockNonSVS, iso: 'MUL' };

    if (countLocksNonSVS === 1)
      return {
        index: indexLockNonSVS,
        iso: lock.value.details[indexLockNonSVS].iso,
      };

    return { index: 0, iso: '' };
  });
  const handleClick = () => {
    emit('click');
  };
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';

  .common-class {
    @apply w-[50px] h-14 p-1 border border-neutral-200 flex-col items-center inline-flex cursor-pointer hover:bg-zinc-100 hover:border-stone-300 transition duration-500 ease-in-out;
  }

  .only-NonSVS {
    @apply common-class justify-center;
  }

  .only-SVS {
    @apply common-class justify-end;
  }

  .nonSVS-and-SVS {
    @apply common-class justify-end gap-1.5;
  }

  .common-class-info {
    @apply self-stretch shrink basis-0 text-center text-zinc-500 text-sm font-normal leading-[18.20px];
  }

  .lock-service {
    @apply self-stretch justify-center inline-flex bg-violet-100 rounded py-0.5 !important;
  }

  .selected-class {
    @apply bg-blue-200 border-blue-400 !important;
    .common-class-info {
      color: #1284ed;
    }
  }
  .holiday {
    @apply bg-red-100 border border-rose-500 bg-opacity-40 border-opacity-40;
  }
</style>
