import Vue from "vue";

const roomsModule = {
  namespaced: true,
  state: {
    rooms: [],
    countUsersForLive: {},
    privateRooms: {},
    usersInRoom: {},
    room: null,
  },
  mutations: {
    set_room(state, room) {
      state.room = room;
    },
    set_data(state, data) {
      Vue.set(state.room, data[0], data[1]);
    },
    SOCKET_MUTATION_ROOMS_LOADED(state, data) {
      state.rooms = data.rooms;
    },
    SOCKET_MUTATION_USERS_IN_ROOM(state, data) {
      Vue.set(state.room, 'users', data);
    },
    SOCKET_MUTATION_REFRESH(state, data) {
        Vue.set(state.room, data[0], data[1]);
    },
    SOCKET_MUTATION_SET_USER_POSITIONS(state, data) {
        Vue.set(state.room, data[0], data[1]);
    },
    SOCKET_MUTATION_SET_ACTIVE_CURSOR(state, toogle_active) {
        Vue.set(state.room, toogle_active);
    },
    SOCKET_MUTATION_NEW_NOTIFICATION(state, notification) {
      let notifications = state.room.notifications;
      if (!notifications) {
        notifications = [notification];
      } else {
        notifications.push(notification);
      }
      Vue.set(state.room, 'notifications', notifications);
    },
    SOCKET_MUTATION_NEW_MESSAGE(state, message) {
      let messages = state.room.messages;
      if (!messages) {
        messages = [message];
      } else {
        messages.push(message);
      }
      Vue.set(state.room, 'messages', messages);
    },
    SOCKET_MUTATION_START_PRIVATE_ROOM(state, data) {
      Vue.set(state.privateRooms, data.room, {
        id: data.data.id,
        usersInRoom: data.usersInRoom
      });
    },
    SOCKET_MUTATION_NEW_PRIVATE_MESSAGE(state, newMessage) {
      let messages = state.privateRooms[newMessage.room].messages;
      if (!messages) {
        messages = [newMessage];
      } else {
        messages.push(newMessage);
      }
      Vue.set(state.privateRooms[newMessage.room], 'messages', messages);
    },
    SOCKET_MUTATION_USER_LEAVE_PRIVATE_ROOM(state, room) {
      state.privateRooms[room] = null;
    },
    SOCKET_MUTATION_COUNT_USERS_IN_ROOM(state, data) {
      Vue.set(state.countUsersForLive, data.room, data.countUsers);
    }
  },
  getters: {
    messagesInRoom(state) {
      return function (username) {
        let newMessages = [];
        if (!state.room || !state.room.messages) return [];
        state.room.messages.forEach(m => {
          const user = m.user === username ? 'Yo' : m.user;
          const message = `${user}: ${m.message}`;
          newMessages.push(message);
        });
        return newMessages;
      }
    },
    messagesInPrivateRoom(state) {
      return function (room, username) {
        let newMessages = [];
        if (!state.privateRooms[room] || !state.privateRooms[room].hasOwnProperty('messages')) return [];
        state.privateRooms[room].messages.forEach(m => {
          const user = m.user === username ? 'Yo' : m.user;
          const message = `${user}: ${m.message}`;
          newMessages.push(message);
        });
        return newMessages;
      }
    }
  }
};

export default roomsModule;
