import VirtualClassLayout from './Layout'
import VirtualClassList from './List'
import VirtualClassForm from './Form'

export default [
  {
    path: 'virtualclass',
    alias: '',
    component: VirtualClassLayout,
    redirect: '/virtualclass/list',
    name: 'virtualclass',
    meta: {
      breadcrumb: 'Cadenas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: VirtualClassList,
        name: 'VirtualClassList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: VirtualClassForm,
        name: 'VirtualClassAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: VirtualClassForm,
        name: 'VirtualClassEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
