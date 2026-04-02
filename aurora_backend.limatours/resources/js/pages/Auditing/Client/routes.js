import AuditClientLayout from './Layout'
import AuditClientList from './List'

export default [
    {
        path: '/audit/client',
        alias: '',
        component: AuditClientLayout,
        redirect: '/audit/package/list',
        name: 'AuditClient',
        meta: {
            breadcrumb: 'Auditoria clientes'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: AuditClientList,
                name: 'AuditClientList',
                meta: {
                    breadcrumb: 'Lista'
                }
            }
        ]
    }
]
