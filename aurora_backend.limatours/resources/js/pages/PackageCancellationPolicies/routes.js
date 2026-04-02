import PackageCancellationPoliciesLayout from './Layout'
import PackageCancellationPoliciesList from './List'
import PackageCancellationPoliciesForm from './Form'

export default [
    {
        path: 'packages/cancellation_policies',
        alias: '',
        component: PackageCancellationPoliciesLayout,
        name: 'PackageCancellationPolicies',
        redirect: 'packages/cancellation_policies/list',
        meta: {
            breadcrumb: 'Políticas de cancelación'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: PackageCancellationPoliciesList,
                name: 'PackageCancellationPoliciesList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: PackageCancellationPoliciesForm,
                name: 'PackageCancellationPoliciesFormAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: PackageCancellationPoliciesForm,
                name: 'PackageCancellationPoliciesEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
    }
]
