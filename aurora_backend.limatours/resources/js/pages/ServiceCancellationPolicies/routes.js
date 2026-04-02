import ServiceCancellationPoliciesLayout from './Layout'
import ServiceCancellationPoliciesList from './List'
import ServiceCancellationPoliciesForm from './Form'

export default [
  {
    path: 'cancellation_policies',
    alias: '',
    component: ServiceCancellationPoliciesLayout,
    redirect: '/cancellation_policies/list',
    name: 'Politicas',
    meta: {
      breadcrumb: 'Politicas de cancelacion'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ServiceCancellationPoliciesList,
        name: 'ServiceCancellationPoliciesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ServiceCancellationPoliciesForm,
        name: 'ServiceCancellationPoliciesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ServiceCancellationPoliciesForm,
        name: 'ServiceCancellationPoliciesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
