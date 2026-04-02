import ServiceCategoryLayout from './Layout'
import ServiceCategoryList from './List'
import ServiceCategoryForm from './Form'

import ManageSubCategoriesLayout from './ManageSubCategories/ManageSubCategoriesLayout'
import ServiceSubCategoryList from './ManageSubCategories/ServiceSubCategoriesList'
import ServiceSubCategoriesForm from './ManageSubCategories/ServiceSubCategoriesForm'

export default [
  {
    path: 'type_service',
    alias: '',
    component: ServiceCategoryLayout,
    redirect: '/type_service/list',
    name: 'type_service',
    meta: {
      breadcrumb: 'Tipo de servicio'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ServiceCategoryList,
        name: 'ServiceCategoryList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ServiceCategoryForm,
        name: 'ServiceCategoryAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ServiceCategoryForm,
        name: 'ServiceCategoryEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: ':category_id/service_sub_type/',
        alias: '',
        component: ManageSubCategoriesLayout,
        redirect: ':category_id/service_sub_type/list',
        name: 'ManageSubCategoriesLayout',
        meta: {
          breadcrumb: 'Sub tipos'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: ServiceSubCategoryList,
            name: 'ServiceSubCategoryList',
            meta: {
              breadcrumb: 'Lista.'
            }
          },
          {
            path: 'add',
            alias: '',
            component: ServiceSubCategoriesForm,
            name: 'ServiceSubCategoriesAdd',
            meta: {
              breadcrumb: 'Agregar'
            }
          },
          {
            path: 'edit/:id',
            alias: '',
            component: ServiceSubCategoriesForm,
            name: 'ServiceSubCategoriesEdit',
            meta: {
              breadcrumb: 'Editar'
            }
          }
        ]
      }
    ]
  }]
