import { defineStore } from 'pinia';
import { fetchCategories, saveCategory, updateCategory } from '@service/adventure';
import { createCategoryAdapter } from './adapters';

export const useCategoriesStore = defineStore({
  id: 'categories',
  state: () => ({
    loading: false,
    category: {
      equivalence: '',
      name: '',
      description: '',
    },
    categories: [],
    error: '',
  }),
  getters: {
    isLoading: (state) => state.loading,
    getCategories: (state) => state.categories,
  },
  actions: {
    fetchAll(search = '') {
      this.loading = true;
      this.error = '';
      this.categories = [];
      return fetchCategories(search)
        .then(({ data }) => {
          if (data.success) {
            this.categories = data.data.map((category) => createCategoryAdapter(category));
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    save() {
      this.loading = true;
      this.error = '';
      const params = {
        name: this.category.name,
        equivalence: this.category.equivalence,
        description: this.category.description,
      };

      return saveCategory(params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Error de procesamiento';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
    update() {
      this.loading = true;
      this.error = '';
      const params = {
        name: this.category.name,
        equivalence: this.category.equivalence,
        description: this.category.description,
      };

      return updateCategory(this.category._id, params)
        .then(({ data }) => {
          if (!data.success) {
            this.error = data.message || 'Error de procesamiento';
          }
          this.loading = false;
        })
        .catch((err) => {
          this.error = err.data.message;
          this.loading = false;
        });
    },
  },
});
