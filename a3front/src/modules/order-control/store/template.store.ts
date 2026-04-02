import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
  createTemplate,
  fetchTemplates,
  cloneTemplate,
  softDeleteTemplate,
  updateTemplate,
} from '@ordercontrol/api';

const DEFAULT_PER_PAGE = 9;
const DEFAULT_PAGE = 1;

interface TemplateFromApi {
  _id: string;
  name: string;
  [key: string]: any;
}

interface PaginationFromApi {
  page: number;
  per_page: number;
  total: number;
}

export const useTemplateStore = defineStore('templateStore', () => {
  const total = ref(0);
  const currentPage = ref(DEFAULT_PAGE);
  const defaultPerPage = ref(DEFAULT_PER_PAGE);
  const perPage = ref(DEFAULT_PER_PAGE);
  const isLoading = ref(false);
  const loading = ref(false);
  const templates = ref<TemplateFromApi[]>([]);

  const filterBy = ref(null); // Default filter by name
  const filterByType = ref(null); // Default filter type

  const currentTemplateScope = ref(true); // true = mis plantillas

  const error = ref<string | null>(null);
  // const loadingModal = ref(false);
  // const errorModal = ref<string | null>(null);
  // const templates = ref<TemplateOption[]>([]);
  //TODO: Separar// Para almacenar las plantillas de correo electrónico

  const getTotal = computed(() => total.value);
  const getCurrentPage = computed(() => currentPage.value);
  const getDefaultPerPage = computed(() => defaultPerPage.value);
  const getPerPage = computed(() => perPage.value);
  const getTemplates = computed(() => templates.value);

  /**
   * Sets the templates and pagination data in the store.
   * This action is used by the orchestrator store to populate the data.
   * @param {TemplateFromApi[]} fetchedTemplates - The array of templates from the API.
   * @param {PaginationFromApi} pagination - The pagination object from the API.
   */
  const setTemplates = (fetchedTemplates: TemplateFromApi[], pagination: PaginationFromApi) => {
    if (fetchedTemplates && pagination) {
      templates.value = fetchedTemplates;
      currentPage.value = pagination.page;
      perPage.value = pagination.per_page;
      total.value = pagination.total || 0;
      console.log(' Templates actualizados en store:', templates.value);
    } else {
      templates.value = [];
      total.value = 0;
    }
  };

  /**
   * Fetches templates from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAll = async ({
    currentPage: page = DEFAULT_PAGE,
    perPage: perPageSize = DEFAULT_PER_PAGE,
    filterBy_,
    filterByType_,
    isMyTemplates = true, // default por si se omite
  }: any = {}) => {
    isLoading.value = true;
    error.value = null;

    currentTemplateScope.value = isMyTemplates; // ✅ actualiza el contexto actual

    try {
      const response = await fetchTemplates({
        page,
        per_page: perPageSize,
        filterBy: filterBy_,
        filterByType: filterByType_,
        is_my_templates: isMyTemplates,
      });
      if (response && response.data) {
        setTemplates(response.data.templates, response.data.pagination);
        filterBy.value = filterBy_;
        filterByType.value = filterByType_;
      } else {
        setTemplates([], { page: 1, per_page: DEFAULT_PER_PAGE, total: 0 });
      }
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching templates.';
      setTemplates([], { page: 1, per_page: DEFAULT_PER_PAGE, total: 0 });
    } finally {
      isLoading.value = false;
    }
  };

  const changePage = async (payload: any) => {
    await fetchAll({
      currentPage: payload.currentPage,
      perPage: payload.perPage,
      filterBy_: filterBy.value,
      filterByType_: filterByType.value,
      isMyTemplates: payload.isMyTemplates,
    });
  };

  const sortBy = async (payload: any) => {
    await fetchAll({
      currentPage: currentPage.value,
      perPage: perPage.value,
      filterBy_: payload.filterBy,
      filterByType_: payload.filterByType,
      isMyTemplates: payload.isMyTemplates,
    });
  };

  /**
   * Creates a new template.
   * @param {any} payload - The data for the new template.
   */
  const create = async (payload: any) => {
    isLoading.value = true;
    error.value = null;
    try {
      // Clonamos el payload para no mutar el estado original del componente.
      const cleanedPayload = { ...payload };

      // Eliminamos las propiedades que el backend no debe recibir en una actualización.
      delete cleanedPayload._id;
      delete cleanedPayload.deleted;
      delete cleanedPayload.createdAt;
      delete cleanedPayload.updatedAt;
      delete cleanedPayload.__v;
      const response = await createTemplate(cleanedPayload);
      if (response && response.data) {
        await fetchAll({
          currentPage: currentPage.value,
          perPage: perPage.value,
          isMyTemplates: currentTemplateScope.value, // ✅ persistencia del contexto
        }); // Refresh list after creation
        return true;
      }
      return false;
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while creating the template.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Updates an existing template by its ID.
   * @param {string} id - The ID of the template to update.
   * @param {any} payload - The data for the template update.
   */
  const update = async (id: string, payload: any) => {
    // isLoading.value = true;
    error.value = null;
    try {
      // Clonamos el payload para no mutar el estado original del componente.
      const cleanedPayload = { ...payload };

      // Eliminamos las propiedades que el backend no debe recibir en una actualización.
      delete cleanedPayload._id;
      delete cleanedPayload.deleted;
      delete cleanedPayload.createdAt;
      delete cleanedPayload.updatedAt;
      delete cleanedPayload.__v;
      delete cleanedPayload.user;
      const { success } = await updateTemplate(id, cleanedPayload);
      return success;
      // if (response) {
      // Refresh list, staying on the same page
      // await fetchAll({ currentPage: currentPage.value, perPage: perPage.value });
      // return true;
      // }
      return false;
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while updating the template.';
      return false;
    } finally {
      // isLoading.value = false;
    }
  };

  /**
   * Soft deletes a template by its ID.
   * @param {string} id - The ID of the template to delete.
   */
  const softDelete = async (id: string) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await softDeleteTemplate(id);
      if (response) {
        // Refresh list, staying on the same page
        await fetchAll({ currentPage: currentPage.value, perPage: perPage.value });
        return true;
      }
      return false;
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while deleting the template.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Clones a template by its ID.
   * @param {string} id - The ID of the template to clone.
   * @param {any} payload - The data for the new cloned template (e.g., new name).
   */
  const clone = async (id: string, payload: any) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await cloneTemplate(id, payload);
      if (response && response.data) {
        // Refresh list, staying on the same page
        await fetchAll({ currentPage: currentPage.value, perPage: perPage.value });
        return response.data;
      }
      return null;
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while cloning the template.';
      throw e;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    getTotal,
    getCurrentPage,
    getDefaultPerPage,
    getPerPage,
    isLoading,
    getTemplates,
    templates,
    loading,
    error,
    create,
    update,
    fetchAll,
    sortBy,
    changePage,
    clone,
    softDelete,
    setTemplates,
  };
});
