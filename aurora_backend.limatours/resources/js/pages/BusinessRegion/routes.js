import BusinessRegionLayout from './Layout'
import BusinessRegionList from './List'
import BusinessRegionForm from './Form'
import BusinessRegionCountriesLayout from './countries/Layout'
import BusinessRegionCountriesList from './countries/List';
import BusinessRegionCountriesForm from './countries/Form';

export default [
  {
    path: 'business_region',
    alias: '',
    component: BusinessRegionLayout,
    redirect: '/business_region/list',
    name: 'BusinessRegion',
    meta: {
      breadcrumb: 'Regiones'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: BusinessRegionList,
        name: 'BusinessRegionList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: BusinessRegionForm,
        name: 'BusinessRegionAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: BusinessRegionForm,
        name: 'BusinessRegionEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: ':region_id/countries/',
        alias: '',
        component: BusinessRegionCountriesLayout,
        redirect: ':region_id/countries/list',
        name: 'BusinessRegionCountriesLayout',
        meta: {
          breadcrumb: 'Paises'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: BusinessRegionCountriesList,
                name: 'BusinessRegionCountriesList',
                meta: {
                    breadcrumb: 'Lista.'
                }
            },
            {
                path: 'add',
                alias: '',
                component: BusinessRegionCountriesForm,
                name: 'BusinessRegionCountriesAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: BusinessRegionCountriesForm,
                name: 'BusinessRegionCountriesEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            }
        ]
      }
    ]
  }]
