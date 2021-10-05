import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import VueRouter from 'vue-router'
import axios from 'axios'

Vue.use(VueRouter)

Vue.config.productionTip = false
Vue.prototype.$http = axios

import Login from '@/components/Login.vue'
import AdminLogin from '@/components/AdminLogin.vue'

const routes = [
  {path:'/', redirect:{name: "login"}},
  {path:'/admin', redirect:{name: "admin_login"}},
  {path:'/app/login', name: "login", component:Login},
  {path:'/admin/login', name: "admin_login", component:AdminLogin},
]

const router = new VueRouter({
  routes: routes
});

new Vue({
  vuetify,
  router: router,
  render: h => h(App)
}).$mount('#app')
