import PositionLayout from './Layout'
import PositionList from './List'
import PositionForm from './Form'

export default [
  {
    path: 'positions',
    alias: '',
    component: PositionLayout,
    redirect: '/positions/list',
    name: 'Cargos',
    meta: {
      breadcrumb: 'Cargos'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: PositionList,
        name: 'PositionList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: PositionForm,
        name: 'PositionFormAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: PositionForm,
        name: 'PositionFormEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
