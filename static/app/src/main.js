import Vue from 'vue'
import App from './App'
import router from './router'
import VImg from '@/components/v-img'
import VButton from '@/components/v-button'
import VInput from '@/components/v-input'
import vaptcha from '@/components/vaptcha';
import message from '@/components/message';

Vue.config.productionTip = false
Vue.component('v-img', VImg);
Vue.component('v-input', VInput);
Vue.component('v-button', VButton);
Vue.component('vaptcha', vaptcha);
Vue.prototype.$msg = message;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  template: '<App/>',
  components: { App },
  router
})
