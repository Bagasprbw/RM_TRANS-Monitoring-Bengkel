import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store' // ini untuk mengimpor store utama yang sudah menggabungkan semua module store

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')