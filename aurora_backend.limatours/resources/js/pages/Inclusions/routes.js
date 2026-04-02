import InclusionsLayout from './Layout'
import InclusionsList from './List'
import InclusionsForm from './Form'

export default [
  {
    path: 'inclusions',
    alias: '',
    component: InclusionsLayout,
    redirect: '/inclusions/list',
    name: 'Inclusions',
    meta: {
      breadcrumb: 'Incluyentes'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: InclusionsList,
        name: 'InclusionsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: InclusionsForm,
        name: 'InclusionsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: InclusionsForm,
        name: 'InclusionsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
