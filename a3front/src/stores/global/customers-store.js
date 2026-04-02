import { defineStore } from 'pinia';
import Cookies from 'js-cookie';
import { fetchCustomers, searchCustomers } from '@/services/global/customers';
import { createCustomerAdapter } from '@store/global/adapters';

export const useCustomersStore = defineStore({
  id: 'customers',
  state: () => ({
    loading: false,
    customers: [],
    searchResults: [],
    selectedCustomer: null,
  }),
  getters: {
    isLoading: (state) => state.loading,
    getCustomers: (state) => state.customers,
    getSearchResults: (state) => state.searchResults,
    getSelectedCustomer: (state) => state.selectedCustomer,
  },
  actions: {
    async fetch() {
      this.loading = true;
      try {
        const { data } = await fetchCustomers();
        this.customers = data.data.map(createCustomerAdapter);
        this.searchResults = [...this.customers];
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async search(query) {
      this.loading = true;
      try {
        if (!query) {
          this.searchResults = [...this.customers]; // Muestra toda la lista si no hay búsqueda
        } else {
          const { data } = await searchCustomers(query);
          this.searchResults = data.data.map(createCustomerAdapter);
        }
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    setSelectedCustomer(customer) {
      //localStorage.setItem('selectedCustomer', JSON.stringify(customer));
      //this.selectedCustomer = customer;
      if (customer?.id && customer?.code && customer?.name) {
        Cookies.set('client_id_limatour', customer.id, { path: '/', domain: '.limatours.test' });
        Cookies.set('client_code_limatour', customer.code, {
          path: '/',
          domain: '.limatours.test',
        });
        Cookies.set('client_name_limatour', customer.name, {
          path: '/',
          domain: '.limatours.test',
        });
      }
      this.selectedCustomer = customer;
    },
    loadSelectedCustomer() {
      //const storedCustomer = localStorage.getItem('selectedCustomer');
      //this.selectedCustomer = storedCustomer ? JSON.parse(storedCustomer) : null;
      //console.log("Cliente cargado desde localStorage:", this.selectedCustomer);
      //
      // Busca en localStorage si ya hay un cliente guardado.
      //let storedCustomer = localStorage.getItem('selectedCustomer');
      //if (!storedCustomer) {
      // Si no hay en LocalStorage, recuperar desde Cookies
      const storedClientId = Cookies.get('client_id_limatour');
      const storedClientCode = Cookies.get('client_code_limatour');
      const storedClientName = Cookies.get('client_name_limatour');

      if (storedClientId && storedClientCode && storedClientName) {
        this.selectedCustomer = {
          id: storedClientId,
          code: storedClientCode,
          name: storedClientName,
        };
        //localStorage.setItem('selectedCustomer', storedCustomer); // Guardar para futuras consultas
      } else {
        console.warn('No se encontró cliente en Cookies. Usando cliente por defecto.');
        this.selectedCustomer = { id: null, code: '', name: 'Seleccione un cliente' }; // Valor por defecto
      }
      //Si storedCustomer tiene datos (ya sea desde localStorage o desde las Cookies), lo convierte de JSON a un objeto JavaScript (JSON.parse()).
      //if (storedCustomer) {
      //this.selectedCustomer = JSON.parse(storedCustomer);
      //}
      //},
    },
  },
});
