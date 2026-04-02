import { createRouter, createWebHistory } from 'vue-router';

import AccessDeniedView from '@views/AccessDeniedView.vue';
import NotFoundView from '@views/NotFoundView.vue';
import MaintenanceView from '@views/MaintenanceView.vue';

import auth from '@/middleware/auth';
import isLogin from '@/middleware/isLogin';

import LoginView from '@views/LoginView.vue';

import LinkCompletedView from '@views/LinkCompletedView.vue';
import RegisterPaxsView from '@views/RegisterPaxsView.vue';
import RegisterFlightsView from '@views/RegisterFlightsView.vue';
import RegisterProvidersView from '@views/RegisterProvidersView.vue';
import TemporalLayout from '@views/TemporalLayout.vue';
import operationRoutes from './operation';
import negotiationRoutes from './negotiation';
import iconRoutes from './icons';
import filesRoutes from './files';
import statementsRoutes from './statements.js';
import brevoRoutes from './brevo';
import orderControlRoutes from './order-control';
import accountancyRoutes from './accountancy';
import { quotesRoutes } from '@/quotes/router';
import routesRoutes from './routes.js';
import adventureRoutes from './adventure';
import serieRoutes from './serie';
import dashboardsRoutes from './serie-dashboards.js';
import maintenanceRoutes from './maintenance';

// 2. Define some routes
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: TemporalLayout,
      // component:VerticalLayout,
      meta: {
        middleware: auth,
        breadcrumb: 'Home',
      },
      children: [],
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        middleware: isLogin,
      },
    },
    filesRoutes,
    statementsRoutes,
    brevoRoutes,
    operationRoutes,
    negotiationRoutes,
    serieRoutes,
    maintenanceRoutes,
    orderControlRoutes,
    accountancyRoutes,
    iconRoutes,
    routesRoutes,
    dashboardsRoutes,
    adventureRoutes,
    {
      ...quotesRoutes,
      path: '/quotes',
      meta: {
        middleware: auth,
        breadcrumb: 'Home',
      },
    },
    {
      path: '/access-denied',
      name: 'error_403',
      meta: {
        middleware: auth,
      },
      component: AccessDeniedView,
    },
    {
      path: '/not-found',
      name: 'error_404',
      component: NotFoundView,
    },
    {
      path: '/maintenance',
      name: 'maintenance',
      component: MaintenanceView,
    },
    {
      path: '/completed',
      name: 'link_completed',
      component: LinkCompletedView,
    },
    {
      path: '/register_paxs/:nrofile',
      name: 'register_paxs',
      component: RegisterPaxsView,
    },
    {
      path: '/register_flights/:nrofile',
      name: 'register_flights',
      component: RegisterFlightsView,
    },

    {
      path: '/register_providers/:nrofile/:codhotel',
      name: 'register_providers',
      component: RegisterProvidersView,
    },
    {
      path: '/follow-up',
      name: 'follow-up',
      component: () => import('@/modules/accountancy/sig-tracking-platform/pages/FollowUp.vue'),
      props: (route) => ({
        token: route.query.token,
        _token: route.query._token,
        _editable: route.query._editable,
        _close: route.query._close,
      }),
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
  mode: 'history',
});

// Creates a `nextMiddleware()` function which not only
// runs the default `next()` callback but also triggers
// the subsequent Middleware function.
function nextFactory(context, middleware, index) {
  const subsequentMiddleware = middleware[index];
  // If no subsequent Middleware exists,
  // the default `next()` callback is returned.
  if (!subsequentMiddleware) return context.next;

  return (...parameters) => {
    if (parameters.length > 0) {
      return context.next(...parameters);
    }
    const nextMiddleware = nextFactory(context, middleware, index + 1);
    subsequentMiddleware({ ...context, next: nextMiddleware });
  };
}

router.beforeEach((to, from, next) => {
  if (to.meta.middleware) {
    const middleware = Array.isArray(to.meta.middleware)
      ? to.meta.middleware
      : [to.meta.middleware];

    const context = {
      from,
      next,
      router,
      to,
    };
    const nextMiddleware = nextFactory(context, middleware, 1);

    return middleware[0]({ ...context, next: nextMiddleware });
  }

  return next();
});

export default router;
