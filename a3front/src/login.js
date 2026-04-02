import { createApp } from 'vue';
import LoginView from './views/LoginView.vue';

window.LoginWidget = {
  init(options = {}) {
    const mountId = options.mountId || 'lito-login-widget';

    let mountEl = document.getElementById(mountId);
    if (!mountEl) {
      mountEl = document.createElement('div');
      mountEl.id = mountId;
      document.body.appendChild(mountEl);
    }

    const app = createApp(LoginView, options.props || {});
    app.mount(`#${mountId}`);
  },
};
