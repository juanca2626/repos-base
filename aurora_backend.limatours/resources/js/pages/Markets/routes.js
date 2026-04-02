import MarketsLayout from './Layout'
import MarketsList from './List'
import MarketsForm from './Form'

export default [
  {
    path: 'markets',
    component: MarketsLayout,
    redirect: {
      name: 'MarketsList'
    },
    name: 'markets',
    meta: {
      breadcrumb: 'Mercados'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: MarketsList,
        name: 'MarketsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: MarketsForm,
        name: 'MarketsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: MarketsForm,
        name: 'MarketsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
