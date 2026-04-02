import PhotosLayout from './Layout'
import PhotosList from './List'
import PhotosForm from './Form'

import PhotosManageFilter from './Filters/ListFilter'


export default [
    {
        path: 'photos',
        alias: '',
        component: PhotosLayout,
        name: 'Photos',
        redirect: 'photos/list',
        meta: {
            breadcrumb: 'Fotos'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: PhotosList,
                name: 'PhotosList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: PhotosForm,
                name: 'PhotosAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: PhotosForm,
                name: 'PhotosEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            },
            {
                path: 'manage_filters',
                alias: '',
                component: PhotosManageFilter,
                name: 'PhotosManageFilter',
                meta: {
                    breadcrumb: 'Lista Filtros'
                }
            },

        ]
    }]
