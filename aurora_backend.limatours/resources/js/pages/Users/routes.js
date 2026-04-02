import UsersLayout from './Layout'
import UsersList from './List'
import UsersForm from './Form'

export default [
  {
    path: 'users',
    alias: '',
    component: UsersLayout,
    redirect: '/users/list',
    name: 'Users',
    meta: {
      breadcrumb: 'Usuarios'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: UsersList,
        name: 'UsersList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: UsersForm,
        name: 'UsersAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: UsersForm,
        name: 'UsersEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
