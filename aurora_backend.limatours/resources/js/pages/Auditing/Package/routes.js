import AuditPackageLayout from './Layout'
import AuditPackageList from './List'

export default [
    {
        path: '/audit/package',
        alias: '',
        component: AuditPackageLayout,
        redirect: '/audit/package/list',
        name: 'AuditPackage',
        meta: {
            breadcrumb: 'Auditoria paquetes'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: AuditPackageList,
                name: 'AuditPackageList',
                meta: {
                    breadcrumb: 'Lista'
                }
            }
        ]
    }
]
