import PermissionsLayout from './Layout'
import PermissionsList from './List'
import PermissionsForm from './Form'

export default [
  {
    path: 'permissions',
    alias: '',
    component: PermissionsLayout,
    redirect: '/permissions/list',
    name: 'Permissions',
    meta: {
      breadcrumb: 'Permisos'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: PermissionsList,
        name: 'PermissionsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: PermissionsForm,
        name: 'PermissionsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: PermissionsForm,
        name: 'PermissionsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
