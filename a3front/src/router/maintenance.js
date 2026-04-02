import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
import auth from '@/middleware/auth.js';

export default {
  path: '/maintenance',
  component: DashboardLayout,
  meta: {
    middleware: auth,
    breadcrumb: 'Negociaciones',
  },
  children: [
    {
      path: 'transport-configuration',
      name: 'transportConfigurationGeneral',
      meta: {
        breadcrumb: 'Configuración de transporte',
      },
      component: () =>
        import('@/modules/maintenance/transport-configuration/pages/TransportConfigurationGeneral.vue'),
    },
  ],
};
