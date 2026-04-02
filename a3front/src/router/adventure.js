import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
import auth from '@/middleware/auth.js';
// import checkPermission from '@/middleware/CheckPermission';

const ROUTE_NAME = 'adventure';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  // beforeEnter: checkPermission,
  meta: {
    middleware: auth, // Validaciones de sesión..
    breadcrumb: 'Aventura',
    /*
    permission: 'mffilesquery',
    action: 'read',
    */
  },
  redirect: `/${ROUTE_NAME}/categories`,
  children: [
    {
      path: `/${ROUTE_NAME}/categories`,
      name: 'adventure-categories',
      component: () => import('@/views/adventure/Categories.vue'),
      meta: {
        breadcrumb: 'Categorías',
      },
    },
    {
      path: `/${ROUTE_NAME}/configuration`,
      name: 'adventure-configuration',
      component: () => import('@/views/adventure/Configuration.vue'),
      meta: {
        breadcrumb: 'Configuración',
      },
    },
    {
      path: `/${ROUTE_NAME}/templates`,
      name: 'adventure-templates',
      component: () => import('@/views/adventure/TemplateLayout.vue'),
      meta: {
        breadcrumb: 'Plantillas',
      },
      redirect: `/${ROUTE_NAME}/templates/list`,
      children: [
        {
          path: `/${ROUTE_NAME}/templates/list`,
          name: 'adventure-template-list',
          component: () => import('@/views/adventure/Templates.vue'),
          meta: {
            breadcrumb: 'Listado',
          },
        },
        {
          path: `/${ROUTE_NAME}/template/:id/services`,
          name: 'adventure-template-services',
          component: () => import('@/views/adventure/TemplateServices.vue'),
          meta: {
            breadcrumb: 'Servicios',
          },
        },
        {
          path: `/${ROUTE_NAME}/template/:id/cash`,
          name: 'adventure-template-cash',
          component: () => import('@/views/adventure/TemplateCash.vue'),
          meta: {
            breadcrumb: 'Costo de PAX',
          },
        },
      ],
    },
    {
      path: `/${ROUTE_NAME}/departures`,
      name: 'adventure-departures',
      component: () => import('@/views/adventure/DepartureLayout.vue'),
      meta: {
        breadcrumb: 'Salidas',
      },
      redirect: `/${ROUTE_NAME}/departures/list`,
      children: [
        {
          path: `/${ROUTE_NAME}/departures/list`,
          name: 'adventure-departures-list',
          component: () => import('@/views/adventure/Departures.vue'),
          meta: {
            breadcrumb: 'Listado',
          },
        },
        {
          path: `/${ROUTE_NAME}/departures/:id/surrenders`,
          name: 'adventure-departures-surrenders',
          component: () => import('@/views/adventure/DepartureSurrenders.vue'),
          meta: {
            breadcrumb: 'FILE',
          },
        },
        {
          path: `/${ROUTE_NAME}/departures/:id/programming`,
          name: 'adventure-departure-programming',
          component: () => import('@/views/adventure/Programming.vue'),
          meta: {
            breadcrumb: 'Programación',
          },
        },
        {
          path: `/${ROUTE_NAME}/departures/:id/file`,
          name: 'adventure-departure-file',
          component: () => import('@/views/adventure/File.vue'),
          meta: {
            breadcrumb: 'FILE',
          },
        },
      ],
    },
    {
      path: `/${ROUTE_NAME}/entrances`,
      name: 'adventure-entrances',
      component: () => import('@/views/adventure/Entrances.vue'),
      meta: {
        breadcrumb: 'Entradas',
      },
    },
    {
      path: `/${ROUTE_NAME}/cash`,
      name: 'adventure-cash',
      component: () => import('@/views/adventure/Cash.vue'),
      meta: {
        breadcrumb: 'Efectivo',
      },
    },
    {
      path: `/${ROUTE_NAME}/programming`,
      name: 'adventure-programming',
      component: () => import('@/views/adventure/Programming.vue'),
      meta: {
        breadcrumb: 'Programación',
      },
    },
    {
      path: `/${ROUTE_NAME}/manifest`,
      name: 'adventure-manifest',
      component: () => import('@/views/adventure/Manifest.vue'),
      meta: {
        breadcrumb: 'Manifiesto Paxs',
      },
    },
    {
      path: `/${ROUTE_NAME}/calendar`,
      name: 'adventure-calendar',
      component: () => import('@/views/adventure/Calendar.vue'),
      meta: {
        breadcrumb: 'Calendario',
      },
    },
    {
      path: `/${ROUTE_NAME}/services`,
      name: 'adventure-services',
      component: () => import('@/views/adventure/Services.vue'),
      meta: {
        breadcrumb: 'Servicios Extra',
      },
    },
  ],
};
