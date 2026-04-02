import CountriesLayout from './Layout'
import CountriesList from './List'
import CountriesForm from './Form'
import Layout from './Taxes/Layout'
import TaxLayout from './Taxes/TaxLayout'
import TaxesList from './Taxes/TaxesList'
import TaxesForm from './Taxes/TaxesForm'
import ServicesLayout from './Taxes/ServicesLayout'
import ServicesList from './Taxes/ServicesList'
import ServicesForm from './Taxes/ServicesForm'

export default [
  {
    path: 'countries',
    alias: '',
    component: CountriesLayout,
    redirect: '/countries/list',
    name: 'Countries',
    meta: {
      breadcrumb: 'Paises'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: CountriesList,
        name: 'CountriesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: CountriesForm,
        name: 'CountriesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: CountriesForm,
        name: 'CountriesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: 'taxes/:country_id',
        alias: '',
        component: Layout,
        redirect: '/taxes/list',
        name: 'Taxes',
        meta: {
          breadcrumb: 'Impuestos y Servicios'
        },
        children: [
          {
            path: 'taxes',
            alias: '',
            component: TaxLayout,
            name: 'TaxLayout',
            meta: {
              breadcrumb: 'Impuestos'
            },
            children: [
              {
                path: 'list',
                alias: '',
                component: TaxesList,
                name: 'TaxesList',
                meta: {
                  breadcrumb: 'ListaTax'
                }
              },
              {
                path: 'add',
                alias: '',
                component: TaxesForm,
                name: 'TaxesAdd',
                meta: {
                  breadcrumb: 'Agregar'
                }
              },
              {
                path: 'edit/:id',
                alias: '',
                component: TaxesForm,
                name: 'TaxesEdit',
                meta: {
                  breadcrumb: 'Editar'
                }
              }
            ]
          },
          {
            path: 'services',
            alias: '',
            component: ServicesLayout,
            name: 'ServicesLayout',
            meta: {
              breadcrumb: 'Servicios'
            },
            children: [
              {
                path: 'list',
                alias: '',
                component: ServicesList,
                name: 'ServicesList',
                meta: {
                  breadcrumb: 'ListaServices'
                }
              },
              {
                path: 'add',
                alias: '',
                component: ServicesForm,
                name: 'ServicesAdd',
                meta: {
                  breadcrumb: 'Agregar'
                }
              },
              {
                path: 'edit/:id',
                alias: '',
                component: ServicesForm,
                name: 'ServicesEdit',
                meta: {
                  breadcrumb: 'Editar'
                }
              }
            ]
          }
        ]
      }
    ]
  }]
