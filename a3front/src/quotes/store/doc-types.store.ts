import { defineStore } from 'pinia';
import type { Doctype } from '@/quotes/interfaces';

interface DocTypesState {
  docTypes: Doctype[];
}

export const useDocTypesStore = defineStore({
  id: 'useDocTypesStore',
  state: () =>
    ({
      docTypes: [] as Doctype[],
    }) as DocTypesState,
  actions: {
    setDocTypes(docTypes: Doctype[]) {
      this.docTypes = docTypes;
    },
  },
});
