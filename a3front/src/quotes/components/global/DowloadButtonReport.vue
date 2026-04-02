<template>
  <div class="button-download-container">
    <!--<ButtonComponent
      after-icon="chevron-down"
      class="button-download"
      text="Descargar"
      type="outline"
      
    >
      Descargar
    </ButtonComponent>-->
    <div @click="toggleForm">
      <svg
        width="21"
        height="20"
        viewBox="0 0 21 20"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M17.75 12.5V15.8333C17.75 16.2754 17.5744 16.6993 17.2618 17.0118C16.9493 17.3244 16.5254 17.5 16.0833 17.5H4.41667C3.97464 17.5 3.55072 17.3244 3.23816 17.0118C2.92559 16.6993 2.75 16.2754 2.75 15.8333V12.5"
          stroke="#3D3D3D"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M6.0835 8.33325L10.2502 12.4999L14.4168 8.33325"
          stroke="#3D3D3D"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M10.25 12.5V2.5"
          stroke="#3D3D3D"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>

    <div v-if="showForm" :class="{ openDownload: showForm }" class="box">
      <DropDownSelectComponent
        :items="props.items"
        :multi="false"
        @click="toggleForm"
        @selected="selectedItem"
      />
    </div>
  </div>
</template>
<script lang="ts" setup>
  import DropDownSelectComponent from '@/quotes/components/global/DropDownSelectComponent.vue';
  import { reactive } from 'vue';
  import { usePopup } from '@/quotes/composables/usePopup';

  const { showForm, toggleForm } = usePopup();

  interface downloadItem {
    label: string;
    value: string;
  }

  interface downloadItems {
    [key: string]: downloadItem[];
  }

  interface Props {
    items: downloadItems;
  }

  const props = defineProps<Props>();

  const emit = defineEmits(['selected']);

  const state = reactive({
    isOpen: false,
  });

  const selectedItem = (item: downloadItem) => {
    state.isOpen = false;
    emit('selected', item);
  };
</script>

<style lang="sass">
  .button-download-container
    position: relative

    .box
      position: absolute
      display: none

      &.openDownload
        display: block
        z-index: 2
</style>
