import ServiceTypesLayout from './Layout'
import ServiceTypesList from './List'
import ServiceTypesForm from './Form'

export default [
  {
    path: 'service_category',
    alias: '',
    component: ServiceTypesLayout,
    redirect: '/service_category/list',
    name: 'Categorias',
    meta: {
      breadcrumb: 'Categorias'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ServiceTypesList,
        name: 'ServiceTypesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ServiceTypesForm,
        name: 'ServiceTypesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ServiceTypesForm,
        name: 'ServiceTypesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
