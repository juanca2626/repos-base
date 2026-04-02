import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNoReportStore = defineStore('noReport', () => {
  const noReport = ref<boolean>(false);

  const setNoReport = (value: boolean) => {
    noReport.value = value;
  };

  const toggleNoReport = () => {
    noReport.value = !noReport.value;
  };

  const reset = () => {
    noReport.value = false;
  };

  return {
    noReport,
    setNoReport,
    toggleNoReport,
    reset,
  };
});
