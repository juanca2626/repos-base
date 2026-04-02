import DashboardsLayout from '@/modules/series-tracking/series-dashboard/layout/DashboardsLayout.vue';
import checkPermission from '@/middleware/CheckPermission';
import auth from '@/middleware/auth.js';

const ROUTE_NAME = 'series-dashboards';

export default {
  path: '/series',
  component: DashboardsLayout,
  beforeEnter: checkPermission,
  meta: {
    middleware: auth,
    permission: 'mfseriesfacile',
    action: 'read',
    breadcrumb: 'Home',
  },
  children: [
    {
      path: ROUTE_NAME,
      name: ROUTE_NAME,
      component: () =>
        import('@/modules/series-tracking/series-dashboard/pages/DashboardsDashboard.vue'),
      meta: {
        breadcrumb: 'Listado y Dashboard de control',
      },
    },
  ],
};
