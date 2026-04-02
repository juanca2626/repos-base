import { defineStore } from 'pinia';
import { fetchExecutives, fetchBoss, fetchSelectBox } from '../../services/files';

export const useExecutivesStore = defineStore({
  id: 'executives',
  state: () => ({
    loading: false,
    executives: [],
    all_executives: {},
    boss: [],
    all_select_box: {},
  }),
  getters: {
    isLoading: (state) => state.loading,
    getExecutives: (state) => state.executives,
    getAllExecutives: (state) => state.all_executives,
    getBoss: (state) => state.boss,
    getAllSelectBox: (state) => state.all_select_box,
    userLevel: (state) => (userCode) => {
      if (!userCode) return { level: 3, name: 'Usuario no encontrado' };

      // 1. Buscar en primer nivel (keys del objeto principal)
      for (const [rootKey, rootUser] of Object.entries(state.all_select_box)) {
        if (rootKey === userCode) {
          return { level: 1, name: rootUser.name || rootKey };
        }

        // 2. Buscar en segundo nivel (dentro de users)
        if (rootUser.users) {
          for (const [secondLevelKey, secondLevelUser] of Object.entries(rootUser.users)) {
            // Verificar tanto por key como por code
            if (secondLevelKey === userCode || secondLevelUser.code === userCode) {
              return { level: 2, name: secondLevelUser.name || secondLevelKey };
            }

            // 3. Buscar en tercer nivel (array de usuarios)
            if (secondLevelUser.users?.length) {
              const foundUser = secondLevelUser.users.find((u) => u.code === userCode);
              if (foundUser) {
                return { level: 3, name: foundUser.name || userCode };
              }
            }
          }
        }
      }

      return { level: 3, name: 'Usuario no encontrado' };
    },
  },
  actions: {
    async fetchAll(array_codes, search = '') {
      this.loading = true;
      return fetchExecutives(array_codes, search)
        .then(({ data }) => {
          this.executives = data.data.executives;
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async findAll(array_codes, search = '') {
      this.loading = true;
      return fetchExecutives(array_codes, search)
        .then(({ data }) => {
          if (data.success) {
            for (const executive of data.data.executives) {
              this.all_executives[executive.code] = executive;
            }
          }
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async fetchAllBoss(array_codes, search = '') {
      this.loading = true;
      return fetchBoss(array_codes, search)
        .then(({ data }) => {
          if (data.success) {
            this.boss = data.data;
          }
          this.loading = false;
        })
        .catch((error) => {
          console.log(error);
          this.loading = false;
        });
    },
    async fetchSelectBox(code = '') {
      this.loading = true;
      return fetchSelectBox(code)
        .then(({ data }) => {
          if (data.success) {
            console.log(data.data);
            this.all_select_box = data.data;
          }
          this.loading = false;
        })
        .catch((err) => {
          console.log(err);
          this.loading = false;
        });
    },
  },
});
