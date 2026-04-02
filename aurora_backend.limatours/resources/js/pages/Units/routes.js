import UnitsLayout from './Layout'
import UnitsList from './List'
import UnitsForm from './Form'

export default [
  {
    path: 'units',
    alias: '',
    component: UnitsLayout,
    redirect: '/units/list',
    name: 'Unidades',
    meta: {
      breadcrumb: 'Unidades'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: UnitsList,
        name: 'UnitsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: UnitsForm,
        name: 'UnitsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: UnitsForm,
        name: 'UnitsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
