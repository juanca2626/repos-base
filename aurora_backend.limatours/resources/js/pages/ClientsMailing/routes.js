import ClientsMailingLayout from './Layout'
import MasiUrlLayout from './LayoutUrl'
import ClientsMailingList from './List'
// import ClientsMailingForm from './Form'

export default [
    {
        path: 'masi_mailing',
        alias: '',
        component: ClientsMailingLayout,
        name: 'Masi Configuración',
        meta: {
            breadcrumb: 'Masi'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: ClientsMailingList,
                name: 'ClientsMailingList',
                meta: {
                    breadcrumb: 'Configuración'
                }
            },
        ]
    },
    {
        path: 'masi_url',
        alias: '',
        component: MasiUrlLayout,
        name: 'Masi Configuración 2',
        meta: {
            breadcrumb: 'MasiUrl'
        }
    }]
