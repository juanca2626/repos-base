// src/modules/order-control/store/user.store.ts (Implementación de referencia)
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { fetchUsers, fetchUsersExternal as fetchUsersExternalApi } from '@ordercontrol/api'; // Asumiendo la ruta del servicio

export const useUserStore = defineStore('userStore', () => {
  // --- Estados existentes ---
  const isLoading = ref(false);
  const users = ref([]);
  const error = ref(null);

  // --- Nuevos estados para usuarios externos ---
  const isLoadingExternal = ref(false);
  const externalUsers = ref([]);
  const errorExternal = ref(null);

  // --- Getters existentes ---
  const getUsers = computed(() => users.value);

  // --- Nuevos Getters ---
  const getExternalUsers = computed(() => externalUsers.value);

  // --- Acciones existentes ---
  const fetchAll = async (params = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchUsers(params); // Asumiendo que esta es la llamada API
      if (response && response.data) {
        users.value = response.data.map((user: any) => ({
          value: user.code,
          label: `${user.code} - ${user.fullname}`,
        }));
      } else {
        users.value = [];
      }
    } catch (err: any) {
      error.value = err.message || 'An unknown error occurred while fetching users.';
      users.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  // --- Nueva Acción ---
  const fetchUsersExternal = async () => {
    isLoadingExternal.value = true;
    errorExternal.value = null;
    try {
      // Asumo que la llamada API se llama fetchUsersExternalApi y el endpoint es /users/external
      const response = await fetchUsersExternalApi();
      if (response && response.data) {
        externalUsers.value = response.data.data.map((user: any) => ({
          value: user.codigo,
          label: `${user.codigo} - ${user.razon}`,
        }));
      } else {
        externalUsers.value = [];
      }
    } catch (err: any) {
      errorExternal.value =
        err.message || 'An unknown error occurred while fetching external users.';
      externalUsers.value = [];
    } finally {
      isLoadingExternal.value = false;
    }
  };

  return {
    isLoading,
    getUsers,
    error,
    fetchAll,
    // Nuevos exports
    isLoadingExternal,
    getExternalUsers,
    errorExternal,
    fetchUsersExternal,
  };
});
