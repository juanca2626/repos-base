import auth from '@/middleware/auth';
// import checkPermission from '@/middleware/CheckPermission';
// import FilesLayout from '../views/files/FilesLayout.vue';
// import VerticalLayout from '@views/layouts/VerticalLayout.vue';
import OrderControlPage from '@views/ordercontrol/OrderControlPage.vue';
// import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
// import FilesEditPaxs from '@/components/files/edit/FilesEditPaxs.vue'

const ROUTE_NAME = 'order-control';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: OrderControlPage,
  // component: FilesLayout,
  // beforeEnter: checkPermission,
  meta: {
    middleware: auth, // Validaciones de sesión..
    breadcrumb: 'Control de Pedidos',
    // permission: 'mffilesquery',
    // action: 'read',
    // breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}/`,
  children: [
    {
      path: `/${ROUTE_NAME}/`,
      name: 'principal',
      component: () => import('@ordercontrol/pages/OrderListPage.vue'),
      meta: {
        breadcrumb: 'Listado de Pedidos',
      },
    },
    {
      path: `/${ROUTE_NAME}/templates`,
      name: 'templates',
      component: () => import('@ordercontrol/pages/TemplateManagementPage.vue'),
      meta: {
        breadcrumb: 'Gestión de plantillas',
      },
    },
  ],
};
