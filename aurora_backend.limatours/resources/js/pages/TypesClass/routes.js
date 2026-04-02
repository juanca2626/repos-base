import TypesClassLayout from './Layout'
import TypesClassList from './List'
import TypesClassForm from './Form'

export default [
  {
    path: 'typesclass',
    alias: '',
    component: TypesClassLayout,
    redirect: '/typesclass/list',
    name: 'typesclass',
    meta: {
      breadcrumb: 'Cadenas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: TypesClassList,
        name: 'TypesClassList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: TypesClassForm,
        name: 'TypesClassAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: TypesClassForm,
        name: 'TypesClassEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
