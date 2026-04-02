import RolesLayout from './Layout'
import RolesList from './List'
import RolesForm from './Form'
import RolesPermissions from './Permissions'

export default [
  {
    path: 'roles',
    alias: '',
    component: RolesLayout,
    redirect: '/roles/list',
    name: 'Roles',
    meta: {
      breadcrumb: 'Roles'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: RolesList,
        name: 'RolesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: RolesForm,
        name: 'RolesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: RolesForm,
        name: 'RolesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: 'addPermissions/:id',
        alias: '',
        component: RolesPermissions,
        name: 'RolesPermissions',
        meta: {
          breadcrumb: 'Permisos'
        }
      },
      {
        path: 'getUsers/:id',
        meta: { breadcrumb: 'Permisos' },
        beforeEnter: (to, from, next) => {
          // Redirige al enlace de Laravel con el id proporcionado.
          window.location.href = `/roles/${to.params.id}/users`;
        }
      }
    ]
  }]
