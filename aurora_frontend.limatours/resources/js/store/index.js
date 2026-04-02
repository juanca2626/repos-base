import Vue from 'vue'
import Vuex from 'vuex';
import VuexPersistence from 'vuex-persist';
import { set } from '../utils/vuex'
import authModule from "./auth";
import roomsModule from "./rooms";
import liveModule from "./live";
Vue.use(Vuex);

const vuexLocal = new VuexPersistence({
  storage: window.localStorage
});

export default new Vuex.Store({
  state: {
    processing: {show: false, message: null},
    notification: {show: false, message: null, color: 'success', timeout: 6000},
    privateNotification: {show: false, message: null, color: 'success', link: null, timeout: 6000},
  },
  mutations: {
    setProcessing: set('processing'),
    setNotification: set('notification'),
    setPrivateNotification: set('privateNotification'),
    SOCKET_MUTATION_USER_JOINED(state, data) {
      state.notification = {
        show: true,
        message: data.message,
        color: "info"
      }
    },
    SOCKET_MUTATION_USER_LEAVE_ROOM(state, data) {
      state.notification = {
        show: true,
        message: data.message,
        color: "warning"
      }
    },
    SOCKET_MUTATION_SEND_INVITATION_TO_PRIVATE_ROOM(state, data) {
      const link = {
        url: `/room/${data.room}/private`,
        text: "Acceder ahora!"
      };
      state.privateNotification = {
        show: true,
        data,
        message: data.message,
        color: "deep-orange accent-4",
        link
      }
    },
    SOCKET_MUTATION_USER_JOINED_TO_PRIVATE_ROOM(state, message) {
      state.notification = {
        show: true,
        message,
        color: "success"
      }
    }
  },
  modules: {
    auth: authModule,
    rooms: roomsModule,
    live: liveModule
  },
  plugins: [vuexLocal.plugin]
});
