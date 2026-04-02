import RestrictionsLayout from './Layout'
import RestrictionsList from './List'
import RestrictionsForm from './Form'

export default [
  {
    path: 'restrictions',
    alias: '',
    component: RestrictionsLayout,
    redirect: '/requirements/list',
    name: 'Restrictions',
    meta: {
      breadcrumb: 'Restricciones'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: RestrictionsList,
        name: 'RestrictionsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: RestrictionsForm,
        name: 'RestrictionsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: RestrictionsForm,
        name: 'RestrictionsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
