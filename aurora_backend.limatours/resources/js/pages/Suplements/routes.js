import SuplementsLayout from './Layout'
import SuplementsList from './List'
import SuplementsForm from './Form'

export default [
  {
    path: 'suplements',
    alias: '',
    component: SuplementsLayout,
    redirect: '/suplements/list',
    name: 'Suplements',
    meta: {
      breadcrumb: 'Suplementos'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: SuplementsList,
        name: 'SuplementsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: SuplementsForm,
        name: 'SuplementsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: SuplementsForm,
        name: 'SuplementsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
