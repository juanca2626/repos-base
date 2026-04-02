<template>
  <div
    class="w-full px-2.5 py-3 bg-stone-50 rounded-lg justify-start items-start gap-2.5 inline-flex"
    style="
      position: sticky;
      top: 80px; /* Se mantiene fijo en la parte superior */
      background: white; /* Asegura que no se mezcle con el contenido de fondo */
      z-index: 8; /* Evita que otros elementos lo cubran */
    "
  >
    <div
      class="grow shrink basis-0 self-stretch flex-col justify-start items-start gap-2 inline-flex"
    >
      <div class="self-stretch h-6 justify-start items-center gap-2.5 inline-flex">
        <div class="justify-start items-start gap-1 flex">
          <div class="text-stone-300 text-[13px] font-bold leading-tight">ITEMS SELECCIONADOS</div>
        </div>
      </div>

      <ItemsSeleccionadosProviderComponent
        v-for="item in filteredlocksByProvidersAndSelectedDates"
        :key="item.provider._id"
        :item="item"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import ItemsSeleccionadosProviderComponent from '@operations/modules/blackout-calendar/components/ItemsSeleccionadosProviderComponent.vue';
  import { storeToRefs } from 'pinia';
  import { useBlockCalendarStore } from '../store/blockCalendar.store';
  import { computed } from 'vue';
  // const { getlocksByProvidersAndSelectedDates } = blockCalendarStore;
  const blockCalendarStore = useBlockCalendarStore();
  // Obtener el storeconst blockCalendarStore = useBlockCalendarStore();
  const { locksByProvidersAndSelectedDates } = storeToRefs(blockCalendarStore);
  // Computed para filtrar solo los items que tienen locks
  const filteredlocksByProvidersAndSelectedDates = computed(() => {
    return locksByProvidersAndSelectedDates.value.filter(
      (item) => Object.keys(item.locks).length > 0
    );
  });
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';
</style>
