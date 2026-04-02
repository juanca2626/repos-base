import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

interface Provider {
  username: string;
  type: string;
  contract: string;
}

export const useProviderStore = defineStore('providerStore', () => {
  // Obtener los datos directamente de localStorage (claves individuales)
  const getInitialProvider = (): Provider => ({
    username: localStorage.getItem('user_code') || '',
    type: localStorage.getItem('type') || '',
    contract: localStorage.getItem('contract') || '',
  });

  // State
  const provider = ref<Provider>(getInitialProvider());

  // Getters
  const getProvider = computed(() => provider.value);
  const getUsername = computed(() => provider.value.username);
  const getType = computed(() => provider.value.type);
  const getContract = computed(() => provider.value.contract);

  return {
    getProvider,
    getUsername,
    getType,
    getContract,
  };
});
