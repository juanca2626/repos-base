import AuditServiceLayout from './Layout'
import AuditServiceList from './List'

export default [
    {
        path: '/audit/service',
        alias: '',
        component: AuditServiceLayout,
        redirect: '/audit/service/list',
        name: 'AuditService',
        meta: {
            breadcrumb: 'Auditoria servicios'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: AuditServiceList,
                name: 'AuditServiceList',
                meta: {
                    breadcrumb: 'Lista'
                }
            }
        ]
    }
]
