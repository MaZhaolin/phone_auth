import Vue from 'vue'
import App from './App'
import router from './router'
import VImg from '@/components/common/v-img'
import VButton from '@/components/common/v-button'

Vue.config.productionTip = false
Vue.component('v-img', VImg);
Vue.component('v-button', VButton);

/* eslint-disable no-new */
new Vue({
  el: '#app',
  template: '<App/>',
  components: { App },
  router
})
