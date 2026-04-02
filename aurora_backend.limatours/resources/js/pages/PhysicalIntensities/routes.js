import PhysicalIntensitiesLayout from './Layout'
import PhysicalIntensitiesList from './List'
import PhysicalIntensitiesForm from './Form'

export default [
    {
        path: 'physical_intensities',
        component: PhysicalIntensitiesLayout,
        redirect: {
            name: 'PhysicalIntensitiesList'
        },
        name: 'physicalIntensities',
        meta: {
            breadcrumb: 'Intensidad física'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: PhysicalIntensitiesList,
                name: 'PhysicalIntensitiesList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: PhysicalIntensitiesForm,
                name: 'PhysicalIntensitiesAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: PhysicalIntensitiesForm,
                name: 'PhysicalIntensitiesEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
    }]
