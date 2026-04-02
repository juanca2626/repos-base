import SuppliersLayout from './Layout'
import SuppliersList from './List'
import SuppliersForm from './Form'

export default [
    {
        path: 'suppliers',
        alias: '',
        component: SuppliersLayout,
        redirect: '/suppliers/list',
        name: 'Suppliers',
        meta: {
            breadcrumb: 'Proveedores'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: SuppliersList,
                name: 'SuppliersList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: SuppliersForm,
                name: 'SuppliersAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: SuppliersForm,
                name: 'SuppliersEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
    }]
