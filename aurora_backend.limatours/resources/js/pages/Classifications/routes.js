import ClassificationsLayout from './Layout'
import ClassificationsList from './List'
import ClassificationsForm from './Form'

export default [
  {
    path: 'classifications',
    alias: '',
    component: ClassificationsLayout,
    redirect: '/classifications/list',
    name: 'Classifications',
    meta: {
      breadcrumb: 'Clasificación'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ClassificationsList,
        name: 'ClassificationsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ClassificationsForm,
        name: 'ClassificationsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ClassificationsForm,
        name: 'ClassificationsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
