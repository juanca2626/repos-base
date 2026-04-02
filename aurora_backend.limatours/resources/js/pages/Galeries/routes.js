import GaleriesLayout from './Layout'
import GaleriesList from './List'
import GaleriesForm from './Form'

export default [
  {
    path: 'galeries',
    alias: '',
    component: GaleriesLayout,
    redirect: '/galeries/list',
    name: 'Galeries',
    meta: {
      breadcrumb: 'Galeries'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: GaleriesList,
        name: 'GaleriesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: GaleriesForm,
        name: 'GaleriesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: GaleriesForm,
        name: 'GaleriesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
