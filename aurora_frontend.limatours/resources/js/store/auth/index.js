import Vue from "vue";

const authModule = {
  namespaced: true,
  state: {
    token: null
  },
  actions: {
    login: async function ({ commit }, { username, password }) {
      try {
        commit('setProcessing', { show: true, message: null }, { root: true });
        const { body } = await Vue.http.post('login', { username, password });
        commit('auth/setUser', body, { root: true });
        commit('setNotification', {
            show: true,
            message: "Bienvenid@!",
            color: "primary"
          },
          { root: true });
        await Vue.prototype.$rootBus.$emit("routerPush", { name: "Rooms", params: {}});
      } catch (e) {
        commit('setNotification', {
            show: true,
            message: e.body.message,
            color: "red"
          },
          { root: true });
      } finally {
        commit('setProcessing', { show: false, message: null }, { root: true })
      }
    },
    register: async function ({ commit }, { username, password }) {
      try {
        commit('setProcessing', { show: true, message: null }, { root: true });
        const { body } = await Vue.http.post('register', { username, password });
        commit('auth/setUser', body, { root: true });
      } catch (e) {
        commit('setNotification', {
            show: true,
            message: e.body.message,
            color: "red"
          },
          { root: true });
      } finally {
        commit('setProcessing', { show: false, message: null }, { root: true })
      }
    },
    logout: async ({ commit }) => {
      await commit('auth/logout', null, { root: true });
      await Vue.prototype.$rootBus.$emit("routerPush", { name: "Login", params: {}});
    }
  },
  mutations: {
    setUser (state, data) {
      state.token = data.token;
    },
    logout(state) {
      state.token = null;
    }
  },
  getters: {
    user(state) {
      if (!state.token) return null;
      const base64Url = state.token.split('.')[1];
      const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
      const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
      }).join(''));
      return JSON.parse(jsonPayload);
    }
  }
};

export default authModule;
