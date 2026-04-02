const liveModule = {
  namespaced: true,
  state: {
    countLiveUsers: 0,
  },
  mutations: {
    SOCKET_MUTATION_COUNT_SOCKETS(state, count) {
      state.countLiveUsers = count;
    },
  }
};

export default liveModule;
