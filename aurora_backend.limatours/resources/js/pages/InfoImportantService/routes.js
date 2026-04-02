import InfoImportantServiceLayout from './Layout'
import InfoImportantServiceList from './List'
import InfoImportantServiceForm from './Form'

export default [
  {
    path: 'featured_service',
    alias: '',
    component: InfoImportantServiceLayout,
    redirect: '/featured_service/list',
    name: 'Featured',
    meta: {
      breadcrumb: 'Destacados'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: InfoImportantServiceList,
        name: 'InfoImportantServiceList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: InfoImportantServiceForm,
        name: 'InfoImportantServiceAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: InfoImportantServiceForm,
        name: 'InfoImportantServiceEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
