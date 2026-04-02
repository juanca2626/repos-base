<template>
  <!-- INFORMACIÓN / PROVEEDOR -->
  <div class="self-stretch justify-center items-center inline-flex leading-none mb-1.5">
    <div class="grow">
      <span class="text-gray-800 text-xs font-semibold leading-none m-0 p-0 relative">
        {{ item.provider.code }} - {{ getLastName(item.provider.last_name) }}
        {{ getFirstName(item.provider.first_name) }}
      </span>
    </div>
    <div class="bg-zinc-200 rounded flex" v-if="item.provider.contract === 'P'">
      <span class="text-gray-800 text-xs font-semibold rounded py-0.5 px-1"> Planta </span>
    </div>
  </div>
  <!-- LISTADO BLOQUEOS / PROVEEDOR -->

  <!-- AEROPUERTO -->
  <a-row v-for="lock in item.locks" :key="lock._id" class="w-full grow">
    <a-col type="flex" :span="24" v-if="lock.blocking_reason_id.iso !== 'SVS'">
      <div class="justify-start items-center gap-2 inline-flex">
        <span class="w-2.5 h-2.5 bg-zinc-500 rounded-full"></span>
        <span class="text-zinc-500 text-[11px] font-semibold">
          {{ lock.blocking_reason_id.description }}
        </span>
      </div>

      <!--<div class="pl-5 py-0.5 justify-between items-center flex">-->
      <a-row style="padding: 5px 0px 0px 18px">
        <a-col :span="20" class="text-gray-800 text-[11px] font-semibold leading-none">
          {{ formatDateRange(lock.datetime_start, lock.datetime_end, lock.complete_day) }}
        </a-col>
        <a-col :span="4">
          <a-flex justify="end" align="center">
            <font-awesome-icon
              @click="modalStore.showModal('update', { provider: item.provider, lock })"
              class="w-3.5 cursor-pointer"
              :icon="['fas', 'pen-to-square']"
              :style="{
                color: '#bd0d12',
                fontSize: '12px',
              }"
            />

            <font-awesome-icon
              @click="modalStore.showModal('deleteOne', { provider: item.provider, lock })"
              class="w-3.5 cursor-pointer"
              :icon="['far', 'trash-can']"
              :style="{
                color: '#bd0d12',
                fontSize: '12px',
              }"
            />
          </a-flex>
        </a-col>
      </a-row>

      <a-row v-if="lock.observations" style="padding: 0px 0px 0px 18px">
        <a-col :span="24">
          <a-type-paragraph class="text-zinc-500 text-[11px] font-normal leading-none">
            {{ lock.observations }}
          </a-type-paragraph>
        </a-col>
      </a-row>

      <!--
      <div
        v-if="lock.observations"
        class="self-stretch pl-5 py-0.5 justify-start items-start gap-2.5 inline-flex"
      >
        <span class="text-zinc-500 text-[11px] font-normal leading-none">
          {{ lock.observations }}
        </span>
      </div>
    -->
    </a-col>
    <a-col :span="24" v-else>
      <div class="self-stretch flex-col justify-start items-start flex">
        <div class="self-stretch justify-start items-center gap-2 inline-flex">
          <!-- CONFIRMADO: bg-emerald-500 text-emerald-600 -->
          <!-- 
          <span class="w-2.5 h-2.5 bg-emerald-600 rounded-full"></span>
          <span class="text-emerald-600 text-[11px] font-semibold">
            Confirmado
          </span> 
          -->

          <span
            :class="[
              'w-2.5 h-2.5 rounded-full',
              lock.data.status === 'Confirmed'
                ? 'bg-emerald-600'
                : lock.data.status === 'Rejected'
                  ? 'bg-red-600'
                  : 'bg-yellow-600',
            ]"
          ></span>
          <span
            :class="[
              'text-[11px] font-semibold',
              lock.data.status === 'Confirmed'
                ? 'text-emerald-600'
                : lock.data.status === 'Rejected'
                  ? 'text-red-600'
                  : 'text-yellow-600',
            ]"
          >
            {{
              lock.data.status === 'Confirmed'
                ? 'Confirmado'
                : lock.data.status === 'Rejected'
                  ? 'Cancelado'
                  : 'Pendiente de confirmación'
            }}
          </span>

          <!-- AEROPUERTO bg-zinc-500 text-zinc-500-->
          <!-- <span class="w-3 h-3 bg-zinc-500 rounded-full"></span>
          <span class="text-zinc-500 text-[11px] font-semibold">
            Aeropuerto
          </span> -->
        </div>
        <div class="pl-5 inline-flex py-0.5">
          <span class="text-gray-800 text-[11px] font-semibold leading-none">
            <!-- 01 Mar. 2023 | 14:10 - 15:00 -->
            {{ formatDateRange(lock.datetime_start, lock.datetime_end, lock.complete_day) }}
          </span>
        </div>
        <div class="self-stretch pl-5 py-0.5 justify-start items-center gap-2 inline-flex">
          <div class="grow shrink basis-0 justify-start items-center gap-1 flex">
            <span class="text-gray-800 text-[11px] font-semibold leading-none">
              File #{{ lock.data.nroref }} - {{ lock.data.nompax }}
            </span>
            <!-- <span><InfoCircleOutlined :style="{ fontSize: '11px' }" /></span> -->
          </div>

          <font-awesome-icon
            :icon="['fas', 'circle-info']"
            :style="{ color: '#bd0d12', fontSize: '12px' }"
          />
        </div>
        <div class="self-stretch pl-5 py-0.5 justify-start items-start gap-2.5 inline-flex">
          <span class="grow shrink basis-0 text-zinc-500 text-[11px] font-normal leading-none">
            {{ lock.data.description }}
          </span>
        </div>
      </div>
    </a-col>
  </a-row>

  <a-divider
    v-if="locksByProvidersAndSelectedDates.length > 1"
    style="height: 1px; width: 50%; background-color: #e7e7e7; margin: 10px 0"
  />
</template>

<script setup lang="ts">
  import { onMounted } from 'vue';

  import { formatDateRange } from '@operations/modules/blackout-calendar/utils/dateUtils';
  import {
    getFirstName,
    getLastName,
  } from '@operations/modules/blackout-calendar/utils/stringUtils';
  // import VModalComponent from '@operations/modules/blackout-calendar/components/VModalComponent.vue';
  // const { state } = useBlockCalendar();

  /** PINIA **/
  import { storeToRefs } from 'pinia';
  import { useBlockCalendarStore } from '../store/blockCalendar.store';
  const blockCalendarStore = useBlockCalendarStore();
  // const { setModalData } = blockCalendarStore;
  const { locksByProvidersAndSelectedDates } = storeToRefs(blockCalendarStore);

  import { useModalStore } from '@operations/modules/blackout-calendar/store/modal.store';
  const modalStore = useModalStore();

  //  Crear props para el componente. Estoy enviando un objeto con los datos del item
  // que se seleccionó en el calendario
  interface Props {
    item: {
      provider: {
        code: string;
        last_name: string;
        first_name: string;
        contract: string;
      };
      locks: {
        _id: string;
        blocking_reason_id: { iso: string; description: string };
        datetime_end: string;
        datetime_start: string;
        complete_day: boolean;
        data: {
          nroref: number;
          nompax: string;
          description: string;
          status: string;
        };
        observations: string;
      }[];
    };
  }

  defineProps<Props>();

  onMounted(() => {
    //console.log('onMounted ItemsSeleccionadosProviderComponent');
  });
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';
  .badge-isPlant {
    @apply flex rounded bg-zinc-200 text-gray-800 font-semibold text-[14px] leading-[14px] py-1 px-1.5;
  }
  .lock-status-confirmed {
    @apply bg-emerald-600 text-emerald-600;
  }
  .lock-status-notConfirmed {
    @apply bg-zinc-500 text-zinc-500;
  }
</style>
