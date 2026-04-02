import checkPermission from '@/middleware/CheckPermission';
import StatementsLayout from '../modules/statements/layout/StatementsLayout.vue';

const ROUTE_NAME = 'statements';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: StatementsLayout,
  beforeEnter: checkPermission,
  meta: {
    permission: 'mffilesquery',
    action: 'read',
    breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}`,
  children: [
    {
      path: '',
      name: 'statements-dashboard',
      component: () => import('@/modules/statements/pages/StatementsDashboard.vue'),
      meta: {
        breadcrumb: 'Facturación',
      },
    },
  ],
};
