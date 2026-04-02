import { defineStore } from 'pinia';
import { fetchStatements } from '@service/statements/index.js';
import Cookies from 'js-cookie';
const DEFAULT_PER_PAGE = 10;
const DEFAULT_PAGE = 1;

export const useStatementsStore = defineStore({
  id: 'statement',
  state: () => ({
    // loading
    loading: false,
    statements: [],
    page: 0,
    pages: 0,
    total: 0,
    totalFinal: 0,
    currentPage: DEFAULT_PAGE,
    defaultPerPage: DEFAULT_PER_PAGE,
    perPage: DEFAULT_PER_PAGE,
    filter: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getStatements: (state) => state.statements,
    getTotal: (state) => state.total,
    getCurrentPage: (state) => state.currentPage,
    getDefaultPerPage: (state) => state.defaultPerPage,
    getPerPage: (state) => state.perPage,
    getFilter: (state) => state.filter,
  },
  actions: {
    async changePage({ currentPage, perPage, filter, clientCode, dateFrom, dateTo, searchOption }) {
      console.log('cambio de página:', currentPage);
      this.currentPage = currentPage;
      await this.fetchAll({
        currentPage: this.currentPage,
        perPage,
        filter,
        clientCode,
        dateFrom,
        dateTo,
        searchOption,
      });
    },

    async fetchAll({
      currentPage = 1,
      perPage = DEFAULT_PER_PAGE,
      filter = null,
      clientCode = null,
      dateFrom = null,
      dateTo = null,
      searchOption = null,
    }) {
      this.loading = true;

      // Recuperar el cliente de cookies si no está definido
      if (!clientCode) {
        clientCode = Cookies.get('client_code_limatour');
      }

      return fetchStatements({
        currentPage,
        perPage,
        filter,
        clientCode,
        dateFrom,
        dateTo,
        searchOption,
      })
        .then(({ data }) => {
          console.log('Data completa:', data.data.data); // Ver la estructura completa de la respuesta
          this.loading = false;

          this.statements = data.data.data.map((item, index) => ({
            number: index + 1,
            file: item.nroref,
            groupName: item.descri,
            paxCount: item.canadl,
            entryDate: item.diain,
            exitDate: item.diaout,
            paymentDeadline: item.fecven,
            amount:
              item.tipdoc === 'NC' ? item.nctotal : item.tipdoc == 'ST' ? item.habcantotal : 0,
          }));

          this.total = data.data.totalItems;
          this.totalFinal = data.data.totalFinal;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
  },
});
