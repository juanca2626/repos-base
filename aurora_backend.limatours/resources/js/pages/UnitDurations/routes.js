import UnitDurationsLayout from './Layout'
import UnitDurationsList from './List'
import UnitDurationsForm from './Form'

export default [
  {
    path: 'unitdurations',
    alias: '',
    component: UnitDurationsLayout,
    redirect: '/unitdurations/list',
    name: 'Unidades de duración',
    meta: {
      breadcrumb: 'Unidades de duración'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: UnitDurationsList,
        name: 'UnitDurationsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: UnitDurationsForm,
        name: 'UnitDurationsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: UnitDurationsForm,
        name: 'UnitDurationsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
