import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '@/store'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/LoginView.vue')
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/kendaraan',
    name: 'Kendaraan',
    component: () => import('@/views/KendaraanView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/monitoring-kendaraan',
    name: 'MonitoringKendaraan',
    component: () => import('@/views/MonitoringKendaraanView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/monitoring-kendaraan/:id',
    name: 'VehicleDetail',
    component: () => import('@/views/VehicleDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '*',
    redirect: '/dashboard'
  }
]

const router = new VueRouter({
  mode: 'history',
  base: '/',
  routes
})

// ROUTER GUARD
router.beforeEach((to, from, next) => {
  const token = store.state.auth.token

  if (to.path === '/login' && token) {
    next('/dashboard')
  } else if (to.meta.requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})


export default router
