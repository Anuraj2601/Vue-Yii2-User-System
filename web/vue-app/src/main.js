import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import i18n from './i18n'
import store from './store'


const app = createApp(App)
if (store.getters['auth/isAuthenticated']) {
  store.dispatch('auth/fetchUser');
}
app.use(router);
app.use(store);
app.use(i18n);
app.mount('#app');

i18n.global.locale.value = 'en';
