import MasterServiceLayout from './Layout'
import MasterServiceList from './List'

export default [
    {
        path: 'master_services',
        alias: '',
        component: MasterServiceLayout,
        redirect: '/master_services/list',
        name: 'Packages',
        meta: {
            breadcrumb: 'Master Services'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: MasterServiceList,
                name: 'MasterServiceList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
        ]
    }
]
