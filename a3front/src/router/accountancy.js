import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
import auth from '@/middleware/auth';
import CheckPermission from '@/middleware/CheckPermission';

const ROUTE_NAME = 'accountancy';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  meta: {
    breadcrumb: 'Accountancy',
  },
  redirect: `/${ROUTE_NAME}/sig-tracking-platform/non-conforming-products`,
  children: [
    {
      path: 'sig-tracking-platform',
      name: 'sig-tracking-platform',
      component: () =>
        import('@/modules/accountancy/sig-tracking-platform/SigTrackingPlatformLayout.vue'),
      meta: {
        breadcrumb: 'SIG Tracking Platform',
      },
      redirect: { name: 'nonConformingProducts' },
      children: [
        {
          path: 'non-conforming-products',
          name: 'nonConformingProducts',
          component: () =>
            import('@/modules/accountancy/sig-tracking-platform/pages/NonConformingProduct.vue'),
          meta: {
            breadcrumb: 'Producto No Conforme',
            permission: 'nonconformingproducts',
          },
        },
        {
          path: 'congratulations',
          name: 'congratulations',
          component: () =>
            import('@/modules/accountancy/sig-tracking-platform/pages/Congratulations.vue'),
          meta: {
            breadcrumb: 'Felicitación',
            permission: 'congratulations',
          },
        },
        {
          path: 'management-monitoring',
          name: 'managementMonitoring',
          component: () =>
            import('@/modules/accountancy/sig-tracking-platform/pages/ManagementMonitoring.vue'),
          meta: {
            breadcrumb: 'Seguimiento de Gestión',
            permission: 'managementmonitoring',
          },
        },
        {
          path: 'suggestions-for-improvement',
          name: 'suggestionsForImprovement',
          component: () =>
            import(
              '@/modules/accountancy/sig-tracking-platform/pages/SuggestionsForImprovement.vue'
            ),
          meta: {
            breadcrumb: 'Comentarios de Mejora',
            permission: 'suggestionsforimprovement',
          },
        },
        {
          path: 'maintenance-of-sanctions',
          name: 'maintenanceOfSanctions',
          component: () =>
            import('@/modules/accountancy/sig-tracking-platform/pages/MaintenanceOfSanctions.vue'),
          meta: {
            breadcrumb: 'Mantenimiento de Sanciones',
            permission: 'maintenanceofsanctions',
          },
        },
      ],
    },
    {
      path: 'customer-service',
      name: 'customerService',
      component: () => import('@/modules/accountancy/customer-service/layouts/Layout.vue'),
      meta: {
        breadcrumb: 'Customer Service',
      },
      redirect: { name: 'accountancy-claims' },
      children: [
        {
          path: 'claims',
          name: 'accountancy-claims',
          component: () => import('@/modules/accountancy/customer-service/ClaimView.vue'),
          meta: {
            breadcrumb: 'Reclamaciones',
            permission: 'claims',
            action: 'read',
            middleware: [auth, CheckPermission],
          },
        },
        {
          path: 'reports',
          name: 'accountancy-reports',
          component: () => import('@/modules/accountancy/customer-service/ReportView.vue'),
          meta: {
            breadcrumb: 'Reporte en Operación',
            permission: 'reports',
            action: 'read',
            middleware: [auth, CheckPermission],
          },
        },
      ],
    },
  ],
};
