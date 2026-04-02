// import checkPermission from '@/middleware/CheckPermission';
// import OperatingView from '../views/operations/OperatingView.vue';
// Testing...
import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
// import auth from '@/middleware/auth'; //TODO: remove this comment

const ROUTE_NAME = 'ope';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  // beforeEnter: checkPermission,
  meta: {
    // middleware: auth, //TODO: remove this comment
    breadcrumb: 'Operaciones',
    // permission: 'mffilesquery',
    // action: 'read',
    // breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}/dashboard`,
  children: [
    {
      path: `lockdown-calendar`,
      name: 'lockdown-calendar',
      component: () =>
        import('@operations/modules/blackout-calendar/pages/BlackoutCalendarLayout.vue'),
      meta: {
        breadcrumb: 'Calendario de Bloqueos',
      },
    },
    {
      path: `guidelines`,
      name: 'guidelines',
      component: () =>
        import('@operations/modules/operational-guidelines/pages/OperatingGuidelines.vue'),
      meta: {
        breadcrumb: 'Pautas operativas',
      },
    },
    {
      path: `service-management`,
      name: 'service-management',
      component: () => import('@operations/modules/service-management/pages/PrincipalPage.vue'),
      meta: {
        breadcrumb: 'Programación de servicios',
      },
    },
    {
      path: `providers`,
      name: 'providers',
      component: () => import('@operations/modules/providers/pages/PrincipalPage.vue'),
      meta: {
        breadcrumb: 'Servicios Programados',
      },
    },
    {
      path: `tower`,
      name: 'tower',
      component: () => import('@operations/modules/tower/pages/PrincipalPage.vue'),
      meta: {
        breadcrumb: 'Torre de control',
      },
    },
    {
      path: `/${ROUTE_NAME}/management-reports`,
      name: 'ope-management-reports',
      component: () => import('@/views/adventure/ManagementReports.vue'),
      meta: {
        breadcrumb: 'Gestión de Rendiciones',
      },
    },
  ],
};
