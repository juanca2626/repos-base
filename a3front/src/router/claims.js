import ClaimsLayout from '../modules/claims/layout/ClaimsLayout.vue';
//import auth from '@/middleware/auth.js';
import checkPermission from '@/middleware/CheckPermission';

const ROUTE_NAME = 'claims';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: ClaimsLayout,
  beforeEnter: checkPermission,
  meta: {
    //middleware: auth, //TODO: remove this comment
    //breadcrumb: 'Claims',
    permission: 'mffilesquery',
    action: 'read',
    breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}`,
  children: [
    {
      path: '',
      name: 'claims-dashboard',
      component: () => import('@/modules/claims/pages/ClaimsDashboard.vue'),
      meta: {
        breadcrumb: 'Reclamos',
      },
    },
  ],
};
