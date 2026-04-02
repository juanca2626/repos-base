import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
import auth from '@/middleware/auth.js';
import checkPermission from '@/middleware/CheckPermission';
import SeriesProgramsGeneralLayout from '@/modules/series-tracking/series-programs/layout/SeriesProgramsGeneralLayout.vue';

const ROUTE_NAME = 'series';

export default {
  path: `/${ROUTE_NAME}`,
  component: DashboardLayout,
  beforeEnter: checkPermission,
  meta: {
    middleware: auth,
    permission: 'seriesfacile',
    action: 'read',
    breadcrumb: 'Series',
  },
  children: [
    {
      path: 'series-programs',
      component: SeriesProgramsGeneralLayout,
      meta: {
        menu: 'series',
        breadcrumb: 'Series Programs',
      },
      children: [
        {
          path: '',
          name: 'series-programs',
          component: () =>
            import('@/modules/series-tracking/series-programs/pages/SeriesProgramsGeneral.vue'),
        },
      ],
    },
  ],
};
