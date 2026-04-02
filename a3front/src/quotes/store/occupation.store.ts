import { defineStore } from 'pinia';

interface LoaderState {
  show: boolean;
}

export const useOccupationStore = defineStore({
  id: 'occupationStore',
  state: () =>
    ({
      show: false,
    }) as LoaderState,
  actions: {
    showWindowOccupation() {
      this.show = true;
    },
    closeWindowOccupation() {
      this.show = false;
    },
  },
});

// import { defineStore } from 'pinia';
// import { ref } from 'vue';

// export const useOccupationStore = defineStore('occupationStore', () => {
//   const show = ref<boolean>(false);
//   return {
//     show,
//     setStatus(open: boolean) {
//       show.value = open;
//     },
//   };
// });
