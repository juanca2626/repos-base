import PackageHighLightsLayout from './Layout'
import PackageHighLightsList from './List'
import PackageHighLightsForm from './Form'
import FormAssignPackagesHighlights from './FormAssignPackagesHighlights'

export default [
    {
        path: 'packages/highlights',
        alias: '',
        component: PackageHighLightsLayout,
        name: 'Highlights',
        redirect: 'packages/highlights/list',
        meta: {
            breadcrumb: 'Highlights'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: PackageHighLightsList,
                name: 'PackageHighLightsList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: PackageHighLightsForm,
                name: 'PackageHighLightsFormAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: PackageHighLightsForm,
                name: 'PackageHighLightsFormEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            },
            {
                path: 'assign_highlights_packages',
                alias: '',
                component: FormAssignPackagesHighlights,
                name: 'FormAssignPackagesHighlights',
                meta: {
                    breadcrumb: 'Asignar Highlights - Paquetes'
                }
            },

        ]
    }
]
