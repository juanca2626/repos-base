import RoutesLayout from '../modules/routes/layout/RoutesLayout.vue';
import auth from '@/middleware/auth.js';

const ROUTE_NAME = 'routes';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: RoutesLayout,
  meta: {
    middleware: auth, //TODO: remove this comment
    //breadcrumb: 'Routes',
    permission: 'mffilesquery',
    action: 'read',
    breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}`,
  children: [
    {
      path: '',
      name: 'routes-dashboard',
      component: () => import('@/modules/routes/pages/RoutesDashboard.vue'),
      meta: {
        breadcrumb: 'Rutas',
      },
    },
  ],
};
