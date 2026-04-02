import SupplementServicesLayout from './Layout'
import SupplementServicesList from './List'
import SupplementServicesForm from './Form'

export default [
  {
    path: 'supplement_services',
    alias: '',
    component: SupplementServicesLayout,
    redirect: '/supplement_services/list',
    name: 'Supplements',
    meta: {
      breadcrumb: 'Suplementos de Servicios'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: SupplementServicesList,
        name: 'SupplementServicesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: SupplementServicesForm,
        name: 'SupplementServicesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: SupplementServicesForm,
        name: 'SupplementServicesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
