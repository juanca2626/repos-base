// stores/socket.js
import { defineStore } from 'pinia';
import { getUserCode, getUserName, getUserEmail } from '@/utils/auth';
import dayjs from 'dayjs';

export const useSocketsStore = defineStore('sockets', {
  state: () => ({
    onMessageCallback: null,
    socket: null,
    connected: false,
    connections: [],
    manuallyClosed: false,
    token: '',
    pin: false,
    notifications: [],
    total: 0,
    pingInterval: null,
  }),
  getters: {
    getConnections: (state) => {
      const seen = new Set();
      return state.connections.filter((connection) => {
        const key = JSON.stringify(connection);
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
      });
    },
    getToken: (state) => state.token,
    isConnected: (state) => state.connected,
    isPin: (state) => state.pin,
    getNotifications: (state) => state.notifications,
    getTotal: (state) => state.total,
  },
  actions: {
    clearTotal() {
      this.total = 0;
      document.title = (this.total > 0 ? `(${this.total}) ` : '') + `Aurora 3`;
    },
    clearNotifications() {
      this.notifications = [];
      this.clearTotal();
    },
    clearReservationRequests(indexSearch) {
      this.notifications = this.notifications.filter((item, index) => {
        return !(item.type === 'processing-reservation' && index > indexSearch);
      });
      this.total = this.notifications.length;
    },
    readNotificationsHeader(fileId, fileNumber) {
      this.notifications = this.notifications.map((item) => {
        if (
          (item?.file_id === fileId || item?.file_number === fileNumber) &&
          item.type === 'update_file'
        ) {
          return {
            ...item,
            flag_show: false,
          };
        }
        return item;
      });
    },
    readNotificationsByService(serviceId) {
      this.notifications = this.notifications.map((item) => {
        if (
          item.service_ids &&
          Array.isArray(item.service_ids) &&
          item.service_ids.includes(serviceId) &&
          item.flag_show
        ) {
          return {
            ...item,
            flag_show: false,
          };
        }
        return item;
      });
      this.total = this.notifications.filter((n) => n.flag_show).length;
    },
    readNotifications(itineraryId) {
      this.notifications = this.notifications.map((item) => {
        if (item.itinerary_id === itineraryId && item.flag_show) {
          return {
            ...item,
            flag_show: false,
          };
        }
        return item;
      });
    },
    putNotification(notification) {
      notification = {
        ...notification,
        ...{
          flag_show: true,
        },
      };
      this.notifications.unshift(notification);
      this.total += 1;

      setTimeout(() => {
        document.title = (this.total > 0 ? `(${this.total}) ` : '') + `Aurora 3`;
      }, 100);
    },
    connect({ callback = null, link }) {
      const token = localStorage.getItem('token_socket');
      if (this.socket || this.connected) return;

      // Validate required parameters
      const userCode = getUserCode();
      const userName = getUserName();
      const userEmail = getUserEmail();

      if (!userCode || !userName || !userEmail) {
        console.error('❌ Missing user information for WebSocket connection');
        return;
      }

      if (!token) {
        console.warn('⚠️ No socket token found in localStorage');
      }

      const route = window.location.href;
      link = link ?? route;
      const photo = localStorage.getItem('photo_user_a3');

      const websocket = `${window.VITE_WEBSOCKET_AMAZON}?userCode=${userCode}&userName=${encodeURIComponent(userName)}&userEmail=${encodeURIComponent(userEmail)}&userPhoto=${photo || 'null'}&link=${encodeURIComponent(link)}&token=${token || ''}`;

      console.log('🔌 Attempting WebSocket connection to:', window.VITE_WEBSOCKET_AMAZON);

      this.manuallyClosed = false;
      this.onMessageCallback = callback;
      this.socket = new WebSocket(websocket);

      this.socket.onopen = () => {
        console.log(`✅ WebSocket conectado`);
        this.token = token;
        this.connected = true;

        this.send({ type: 'ping', success: true });
      };

      this.socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        const message = typeof data.message === 'string' ? JSON.parse(data.message) : data.message;

        if (message.type === 'ping') {
          this.pin = true;
        }

        if (message.type === 'notification') {
          // Allow notifications for everyone, including self
          this.putNotification(message.data);
        }

        const url = link.replace(window.url_app, '');
        console.log('URL: ', url);

        this.connections = data.connections
          .filter((connection) => connection.link.indexOf(url) > -1)
          .map((connection) => ({
            link: connection.link.replace(window.url_app, ''),
            userCode: connection.userCode,
            userName: connection.userName,
            userPhoto: connection.userPhoto,
            userEmail: connection.userEmail,
            ip: connection.ip,
            token: connection.token,
          }));

        if (this.onMessageCallback) {
          const newMessage = {
            ...message,
            date: dayjs().format('YYYY-MM-DD'),
            time: dayjs().format('HH:mm:ss'),
          };

          this.onMessageCallback(newMessage);
        }
      };

      this.socket.onclose = (event) => {
        console.log(
          `🔌 WebSocket closed. Code: ${event.code}, Reason: ${event.reason || 'No reason provided'}`
        );
        this.connected = false;
        this.socket = null;
        if (this.pingInterval) {
          clearInterval(this.pingInterval);
          this.pingInterval = null;
        }
      };

      this.socket.onerror = (error) => {
        console.error('❌ WebSocket error:', error);
        console.error('❌ WebSocket readyState:', this.socket?.readyState);
        this.connected = false;
        if (this.socket) {
          this.socket.close(); // esto llama también a onclose
        }
        if (this.pingInterval) {
          clearInterval(this.pingInterval);
          this.pingInterval = null;
        }
      };

      this.pingInterval = setInterval(() => {
        this.send({ type: 'ping', success: true });
      }, 50000);
    },

    send(payload) {
      payload =
        typeof payload === 'object' || Array.isArray(payload) ? JSON.stringify(payload) : payload;

      if (this.socket && this.connected) {
        this.socket.send(JSON.stringify({ action: 'sendMessage', message: payload }));
      } else {
        this.disconnect();
      }
    },

    disconnect() {
      this.pin = false;

      if (this.socket && this.connected) {
        this.send({ type: 'ping', success: true });

        this.manuallyClosed = true;
        this.socket.close();
        this.connected = false;
        this.socket = null;

        if (this.pingInterval) {
          clearInterval(this.pingInterval);
          this.pingInterval = null;
        }

        console.log('✅ WebSocket desconectado');
      }
    },
  },
});
