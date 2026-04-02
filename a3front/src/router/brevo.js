import auth from '@/middleware/auth';
// import checkPermission from '@/middleware/CheckPermission';
import BrevoLayout from '../views/brevo/BrevoLayout.vue';

const ROUTE_NAME = 'brevo';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: BrevoLayout,
  // beforeEnter: checkPermission,
  meta: {
    middleware: auth, // Validaciones de sesión..
    // permission: 'mffilesquery',
    // action: 'read',
    breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}/dashboard`,
  children: [
    {
      path: `/${ROUTE_NAME}/dashboard`,
      name: 'brevo-dashboard',
      component: () => import('@/views/brevo/DashboardPage.vue'),
      meta: {
        breadcrumb: 'Dashboard',
      },
    },
    {
      path: `/${ROUTE_NAME}/logs/:object_id`,
      name: 'brevo-logs',
      component: () => import('@/views/brevo/LogsPage.vue'),
      meta: {
        breadcrumb: 'Logs',
      },
    },
  ],
};
