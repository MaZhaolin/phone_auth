import Vue from 'vue'
import App from './App'
import router from './router'
import VImg from '@/components/common/v-img'
import VButton from '@/components/common/v-button'
import VInput from '@/components/common/v-input'
import vaptcha from '@/components/common/vaptcha';
import message from '@/components/common/message';

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
