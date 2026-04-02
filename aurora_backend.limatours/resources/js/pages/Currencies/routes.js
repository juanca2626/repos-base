import CurrenciesLayout from './Layout'
import CurrenciesList from './List'
import CurrenciesForm from './Form'

export default [
  {
    path: 'currencies',
    alias: '',
    component: CurrenciesLayout,
    redirect: '/currencies/list',
    name: 'Currencies',
    meta: {
      breadcrumb: 'Monedas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: CurrenciesList,
        name: 'CurrenciesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: CurrenciesForm,
        name: 'CurrenciesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: CurrenciesForm,
        name: 'CurrenciesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
