import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchLanguages, fetchTeamByUser } from '@ordercontrol/api';
// @ts-ignore
import { getUserInfo } from '@/utils/auth.js';

interface LanguageFromApi {
  iso: string;
  description: string;
  [key: string]: any; // Para el resto de propiedades
}

interface LanguageOption {
  value: string;
  label: string;
}

interface TeamOption {
  value: string;
  label: string;
}

interface TeamsFromApi {
  [key: string]: string;
}

export const useGeneralStore = defineStore('generalStore', () => {
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const languages = ref<LanguageOption[]>([]);
  const getLanguages = computed(() => languages.value);

  const teams = ref<TeamOption[]>([]);
  const getTeams = computed(() => teams.value);

  /**
   * Establece los idiomas en el estado del store.
   * Esta acción es utilizada por el store orquestador para poblar los datos.
   * @param {LanguageFromApi[] | null} languagesData - El array de idiomas crudos desde la API.
   */
  const setLanguages = (languagesData: LanguageFromApi[] | null) => {
    languages.value = languagesData
      ? languagesData.map((language) => ({
          value: language.iso,
          label: language.description,
        }))
      : [];
  };

  /**
   * Establece los equipos en el estado del store.
   * Esta acción es utilizada por el store orquestador para poblar los datos.
   * @param {TeamsFromApi | null} teamsData - El objeto de equipos desde la API.
   */
  const setTeams = (teamsData: TeamsFromApi | null) => {
    teams.value = teamsData
      ? Object.entries(teamsData).map(([value, label]) => ({ value, label }))
      : [];
  };

  /**
   * Fetches languages from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAllLanguages = async (params: any = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchLanguages(params);
      setLanguages(response?.data || null);
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching languages.';
      setLanguages(null);
    } finally {
      isLoading.value = false;
    }
  };

  const fetchAllTeamsByUser = async (): Promise<any> => {
    isLoading.value = true;
    error.value = null;
    try {
      const userInfo = getUserInfo();
      const code = userInfo?.username;
      if (!code) {
        // Se mantiene la validación por si el código no existe
        error.value = 'No se encontró el código de usuario para obtener el equipo.';
        isLoading.value = false;
        return {};
      }
      const response = await fetchTeamByUser(code);
      console.log('🚀 ~ fetchAllTeamsByUser ~ response:', response);

      setTeams(response?.data?.teams || null);
      console.log('🚀 ~ fetchAllTeamsByUser ~ teams.value:', teams.value);
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching team by user.';
      setTeams(null);
      return {};
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    error,
    getLanguages,
    getTeams,
    setLanguages,
    setTeams,
    fetchAllLanguages,
    fetchAllTeamsByUser,
  };
});
