import Vue from "vue";
import store from "../store";
import VueSocketIO from "vue-socket.io";
import SocketIO from "socket.io-client";

// query: `token=${store.state.auth.token}`,
// 54.88.146.253
// localhost
Vue.use(new VueSocketIO({
    debug: true,
    connection: SocketIO(process.env.MIX_BASE_SOCKET_URL, {
        query: `token=${store.state.auth.token}`,
    }),
    vuex: {
        store,
        actionPrefix: 'SOCKET_ACTION_',
        mutationPrefix: 'SOCKET_MUTATION_'
    }
}));
