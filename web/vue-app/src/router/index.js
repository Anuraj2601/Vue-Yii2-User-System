import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/components/Login.vue'
import store from '@/store';
import Signup from '@/components/Signup.vue';
import LayOut from '@/components/Layout/LayOut.vue';
import Dashboard from '@/components/Dashboard.vue';
import Users from '@/components/users/users.vue';

const routes = [
  {
      path: '/',
      redirect: to => {
          if(!store.getters['auth/isAuthenticated']) {
              return '/login';
          }
          return '/dashboard';
      },
      component: LayOut,
      children: [
        {
            path: '/dashboard',
            component: Dashboard,
            meta: { requiresAuth: true }
        },
        {
          path: '/users',
          component: Users,
          meta: { requiresAuth: true, permission: 'manage_users'}
        }
      ]
  },
  {
    path: '/login',
    component: Login,
  },
  {
    path: '/signup',
    component: Signup
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.meta.requiresAuth;
  const requiredPermission = to.meta.permission;

  if(requiresAuth && !store.getters['auth/isAuthenticated']) {
      return next('/login');
  }else if(requiredPermission && !store.getters['auth/hasPermission']) {
      return next('/unauthorized');
  }
  next();
});

export default router;
