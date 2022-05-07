import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)

// import Staff from '../views/Staff.vue'
// import Admin from '../views/Admin.vue'

const routes = [
  {path: '/', redirect: {name: 'staff'}},
  // {path: , name: , component:},
  // {path: '/staff', name: 'staff', component: Staff},
  // {path: '/admin', name: 'admin', component: Admin},
  {path: '/staff', name: 'staff', component: () => import ('@/views/Staff.vue')},
  {path: '/admin', redirect: {name: 'staff'}},
  {path: '/management', name: 'admin', component: () => import ('@/views/management.vue')},

  // {path: '/admin', name: 'admin', component: () => import(/* webpackChunkName: "about" */ '../views/Admin.vue')},
// route level code-splitting
// this generates a separate chunk (about.[hash].js) for this route
// which is lazy-loaded when the route is visited
]

const router = new VueRouter({
  // mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
