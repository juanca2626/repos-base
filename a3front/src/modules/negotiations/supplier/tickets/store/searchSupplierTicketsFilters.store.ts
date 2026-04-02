import { defineStore } from 'pinia';

export const useSearchSupplierTicketsFiltersStore = defineStore('searchSupplierTicketsFilters', {
  state: () => ({
    state: null as number | null,
    codeOrName: null as string | null,
    belongsToState: null as number | null,
    status: null as number | null,
  }),
  actions: {
    setBelongsToState(belongsToState: 0 | 1 | null) {
      this.belongsToState = belongsToState;
    },
    setStatus(status: 0 | 1 | 2 | null) {
      this.status = status;
    },
    setCodeOrName(CodeOrName: string) {
      this.codeOrName = CodeOrName;
    },
    setState(state: number) {
      this.state = state;
    },
    resetFilters() {
      this.state = null;
      this.codeOrName = null;
      this.belongsToState = null;
      this.status = null;
    },
  },
});
