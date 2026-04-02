// import auth from '@/middleware/auth'; //TODO: remove this comment
// import checkPermission from '@/middleware/CheckPermission';
// import OperatingView from '../views/operations/OperatingView.vue';
import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';

const ROUTE_NAME = 'aventura';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  // beforeEnter: checkPermission,
  meta: {
    breadcrumb: 'Aventura',
    //middleware: auth, //TODO: remove this comment
    // permission: 'mffilesquery',
    // action: 'read',
    // breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}/categories`,
  children: [
    {
      path: `categories`,
      name: 'categories',
      component: () => import('@/modules/aventura/pages/CategoriesPage.vue'),
      meta: {
        breadcrumb: 'Categorías',
      },
    },
  ],
};
