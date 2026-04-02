<template>
  <div class="inline-flex flex-col w-full items-start justify-start gap-2.5">
    <!-- <a-typography-title :level="5" style="">Lista de guías:</a-typography-title> -->
    <h5
      class="h-12 flex items-center justify-center font-bold text-[12px]"
      style="padding: 0; margin: 0"
    >
      Lista de guías:
    </h5>

    <div
      v-for="item in locksByMonthList"
      :key="item.provider._id"
      class="group self-stretch h-14 flex-col justify-start items-start gap-1.5 flex border-b-2 border-gray-100 cursor-pointer"
    >
      <div class="self-stretch justify-start items-start inline-flex">
        <div class="grow basis-0 h-[22px] justify-start items-center flex">
          <div
            class="grow basis-0 h-[22px] bg-stone-50 rounded justify-start items-center gap-1 flex p-1 group-hover:bg-red-200 transition duration-500 ease-in-out"
          >
            <div class="grow basis-0 text-gray-800 text-sm font-semibold leading-[14px]">
              {{ getProfile(item.provider.profiles[0]) + ' ' + item.provider.code }}
            </div>
            <ArrowRightOutlined :style="{ color: '#bd0d12' }" />
          </div>
        </div>
      </div>
      <div
        class="self-stretch justify-start items-center gap-1 inline-flex grow basis-0 text-zinc-500 text-xs font-normal leading-none"
      >
        <div class="grow">
          {{ getFirstName(item.provider.first_name) + ' ' + getLastName(item.provider.last_name) }}
        </div>
        <div class="badge-isPlant" v-if="item.provider.contract.iso == 'P'">Planta</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ArrowRightOutlined } from '@ant-design/icons-vue';
  import { storeToRefs } from 'pinia';
  import { useBlockCalendarStore } from '@operations/modules/blackout-calendar/store/blockCalendar.store';
  import { useFiltersFormStore } from '@operations/modules/blackout-calendar/store/filtersForm.store';
  import { useDataStore } from '@operations/modules/blackout-calendar/store/data.store';

  const blockCalendarStore = useBlockCalendarStore();
  const { locksByMonthList } = storeToRefs(blockCalendarStore);

  const dataStore = useDataStore();
  const { providerProfileTypesOptions } = storeToRefs(dataStore);
  console.log('🚀 ~ providerProfileTypesOptions:', providerProfileTypesOptions);

  const filtersFormStore = useFiltersFormStore();
  console.log(filtersFormStore.providerProfileTypesOptions);

  const getProfile = (profileId: string) => {
    const profile = providerProfileTypesOptions.value?.find((profile) => profile.id === profileId);
    return `[${profile?.label.slice(0, 1)}]`;
  };

  const getFirstName = (str: string) => {
    // Verificar si la cadena contiene espacios
    const hasSpaces = str.includes(' ');

    if (hasSpaces) {
      // Si la cadena tiene espacios, dividirla en palabras
      const words = str.split(' ');

      // Capitalizar cada palabra
      const capitalizedWords = words.map((word) => {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
      });

      // Unir las palabras capitalizadas en una sola cadena
      return capitalizedWords[0];
    } else {
      // Si la cadena no tiene espacios, capitalizar solo la primera letra
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }
  };

  const getLastName = (str: string) => {
    const hasSpaces = str.includes(' ');
    if (hasSpaces) {
      const words = str.split(' ');
      const capitalizedWords = words.map((word) => {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
      });

      const firstLetter = str.charAt(0);
      return capitalizedWords[0] + ' ' + firstLetter.toUpperCase() + '.';
    } else {
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }
  };
</script>

<style scoped>
  @import '@/modules/operations/shared/styles/tailwind.css';
  .badge-isPlant {
    @apply flex rounded bg-zinc-200 text-gray-800 font-semibold text-[14px] leading-[14px] py-1 px-1.5;
  }
</style>
