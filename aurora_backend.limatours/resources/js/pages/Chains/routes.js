import ChainsLayout from './Layout'
import ChainsList from './List'
import ChainsForm from './Form'
import MultipleProperty from './MultipleProperty/MultipleProperty'

export default [
  {
    path: 'chains',
    component: ChainsLayout,
    redirect: {
      name: 'ChainsList'
    },
    name: 'chains',
    meta: {
      breadcrumb: 'Cadenas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ChainsList,
        name: 'ChainsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ChainsForm,
        name: 'ChainsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ChainsForm,
        name: 'ChainsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: 'multiple_properties/:id',
        alias: '',
        component: MultipleProperty,
        name: 'MultipleProperty',
        meta: {
          breadcrumb: 'Multiple Property'
        }
      }
    ]
  }]
