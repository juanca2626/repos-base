import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';

const ROUTE_NAME = 'icons';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  redirect: '/list-icons',
  meta: {
    breadcrumb: 'Icons',
  },
  children: [
    {
      path: 'list-icons',
      name: 'list-icons', //'icons',
      meta: {
        breadcrumb: 'Galeria de iconos',
      },
      component: () => import('@/modules/icons/page/iconsPage.vue'),
    },
  ],
};
