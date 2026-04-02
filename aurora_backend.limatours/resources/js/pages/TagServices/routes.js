import TagServicesLayout from './Layout'
import TagServicesList from './List'
import TagServicesForm from './Form'

export default [
  {
    path: 'tagservices',
    alias: '',
    component: TagServicesLayout,
    redirect: '/tagservices/list',
    name: 'tagservices',
    meta: {
      breadcrumb: 'Cadenas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: TagServicesList,
        name: 'TagServicesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: TagServicesForm,
        name: 'TagServicesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: TagServicesForm,
        name: 'TagServicesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
