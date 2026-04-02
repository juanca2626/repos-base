import auth from '@/middleware/auth';
import checkPermission from '@/middleware/CheckPermission';
import FilesLayout from '../views/files/FilesLayout.vue';
import FilesServiceNewItem from '../views/files/ServiceNewItem.vue';
import FilesHotelNewItem from '../views/files/HotelNewItem.vue';
import FilesHotelRemoveItem from '../views/files/HotelRemoveItem.vue';
import FilesRoomRemoveItem from '../views/files/RoomRemoveItem.vue';
import FilesServiceRemoveItem from '../views/files/ServiceRemoveItem.vue';
import FilesHotelReplaceItem from '../views/files/HotelReplaceItem.vue';
import FilesServiceReplaceItem from '../views/files/ServiceReplaceItem.vue';
import FilesCancel from '@views/files/CancelPage.vue';
import FilesTrash from '@views/files/TrashPage.vue';
import FilesActivate from '@views/files/ActivatePage.vue';
import FilesPaxsModify from '@views/files/PaxsModify.vue';
import ServiceTemporaryAdd from '@views/files/ServiceTemporaryNew.vue';
import ServiceMaskPage from '@/components/files/services-masks/page/ServiceMaskPage.vue';
// import FilesEditPaxs from '@/components/files/edit/FilesEditPaxs.vue'

const ROUTE_NAME = 'files';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: FilesLayout,
  beforeEnter: checkPermission,
  meta: {
    middleware: auth, // Validaciones de sesión..
    permission: 'mffilesquery',
    action: 'read',
    breadcrumb: 'Home',
  },
  redirect: `/${ROUTE_NAME}/dashboard`,
  children: [
    {
      path: `/${ROUTE_NAME}/dashboard`,
      name: 'files-dashboard',
      component: () => import('@/views/files/FilesDashboard.vue'),
      meta: {
        breadcrumb: 'Dashboard',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit`,
      name: 'files-edit',
      component: () => import('@/views/files/FilesEdit.vue'),
      meta: {
        breadcrumb: 'Editar File',
      },
    },
    {
      path: `/${ROUTE_NAME}/balance`,
      name: 'files-balance',
      component: () => import('@/views/files/FilesBalance.vue'),
      meta: {
        breadcrumb: 'Balance de file',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/trash`,
      name: 'files-trash',
      component: FilesTrash,
      meta: {
        breadcrumb: 'Eliminar Varios',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/cancel`,
      name: 'files-cancel',
      component: FilesCancel,
      meta: {
        breadcrumb: 'Anular File',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/activate`,
      name: 'files-activate',
      component: FilesActivate,
      meta: {
        breadcrumb: 'Activar File',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/paxs`,
      name: 'files-paxs-modify',
      component: FilesPaxsModify,
      meta: {
        breadcrumb: 'Modificación de Pasajeros',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/hotel`,
      name: 'files-add-new-hotel',
      component: FilesHotelNewItem,
      meta: {
        breadcrumb: 'Agregar Hotel',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service`,
      name: 'files-add-new-service',
      component: FilesServiceNewItem,
      meta: {
        breadcrumb: 'Agregar Servicio',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/remove`,
      name: 'files-remove-service',
      component: FilesServiceRemoveItem,
      meta: {
        breadcrumb: 'Eliminar Servicio',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/master-service/:master_service_id/remove`,
      name: 'files-remove-master-service',
      component: FilesServiceRemoveItem,
      meta: {
        breadcrumb: 'Eliminar Servicio Maestro',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/master_service/:master_service_id/composition/:composition_id/remove`,
      name: 'files-remove-composition',
      component: FilesServiceRemoveItem,
      meta: {
        breadcrumb: 'Eliminar Composición',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/hotel/:hotel_id/remove`,
      name: 'files-remove-hotel',
      component: FilesHotelRemoveItem,
      meta: {
        breadcrumb: 'Eliminar Hotel',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/hotel/:hotel_id/room/:room_id/remove`,
      name: 'files-remove-room',
      component: FilesRoomRemoveItem,
      meta: {
        breadcrumb: 'Eliminar Habitación',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/hotel/:hotel_id/replace`,
      name: 'files-replace-hotel',
      component: FilesHotelReplaceItem,
      meta: {
        breadcrumb: 'Reemplazar Hotel',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/hotel/:hotel_id/room/:room_id/replace`,
      name: 'files-replace-room',
      component: FilesHotelReplaceItem,
      meta: {
        breadcrumb: 'Reemplazar Habitación',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/replace`,
      name: 'files-replace-service',
      component: FilesServiceReplaceItem,
      meta: {
        breadcrumb: 'Reemplazar Servicio',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/master-service/:master_service_id/replace`,
      name: 'files-replace-master-service',
      component: FilesServiceReplaceItem,
      meta: {
        breadcrumb: 'Reemplazar Servicio Maestro',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/master-service/:master_service_id/composition/:composition_id/replace`,
      name: 'files-replace-composition',
      component: FilesServiceReplaceItem,
      meta: {
        breadcrumb: 'Reemplazar Composición',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/service-temporary`,
      name: 'files-add-service-temporary',
      component: ServiceTemporaryAdd,
      meta: {
        breadcrumb: 'Crear servicio temporal',
      },
    },
    {
      path: `/${ROUTE_NAME}/:id/edit/service/:service_id/miscellaneous-modifiable`,
      name: 'files-add-miscellaneous-modifiable',
      component: ServiceMaskPage,
      meta: {
        breadcrumb: 'Misceláneos modificables',
      },
    },
  ],
};
