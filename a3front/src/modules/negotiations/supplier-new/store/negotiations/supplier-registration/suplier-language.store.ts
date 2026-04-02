// 📂 supplier-new/store/negotiations/supplier-registration/languages.store.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface LanguageForm {
  id?: number; // id del registro (si ya existe en backend)
  language?: string; // idioma (ej: "Inglés", "Francés")
  level?: string; // nivel (ej: "Básico", "Intermedio", "Avanzado")
}

export const useSupplierLanguageStore = defineStore('supplierLanguage', () => {
  const formState = ref<LanguageForm>({
    id: undefined,
    language: undefined,
    level: undefined,
  });

  const initialFormData = ref<LanguageForm>({ ...formState.value });
  const formRef = ref();

  return {
    formState,
    initialFormData,
    formRef,
  };
});
