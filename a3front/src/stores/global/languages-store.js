import { defineStore } from 'pinia';
import { fetchLanguages, fetchAllLanguages } from '@/services/global';
import { createLanguageAdapter } from '@store/global/adapters';

export const useLanguagesStore = defineStore({
  id: 'languages',
  state: () => ({
    loading: false,
    languages: [],
    all: [],
    lang: 'en',
    loadedLanguages: [],
  }),
  getters: {
    isLoading: (state) => state.loading,
    getLanguages: (state) => state.languages,
    getAllLanguages: (state) => state.all,
    getLanguage: (state) => state.lang,
    currentLanguage: (state) => state.lang,
  },
  actions: {
    fetch() {
      this.loading = true;

      if (localStorage.getItem('lang') == null || localStorage.getItem('lang') == '') {
        localStorage.setItem('lang', this.lang);
      } else {
        this.lang = localStorage.getItem('lang');
      }

      return fetchLanguages()
        .then(({ data }) => {
          this.languages = data.data
            .filter((lang) => lang.iso != 'it')
            .map((lang) => createLanguageAdapter(lang));
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    fetchAll() {
      return fetchAllLanguages()
        .then(({ data }) => {
          this.all = data.data
            .filter((lang) => lang.iso != 'it')
            .map((lang) => createLanguageAdapter(lang));
        })
        .catch((error) => {
          console.log(error);
        });
    },
    isLoaded(value) {
      return this.loadedLanguages.includes(value);
    },
    addLoaded(value) {
      this.loadedLanguages.push(value);
    },
    setCurrentLanguage(value) {
      localStorage.setItem('lang', value);
      this.lang = value;
    },
    setLanguages(languages) {
      console.log('Idiomas: ', languages);
    },
  },
});
