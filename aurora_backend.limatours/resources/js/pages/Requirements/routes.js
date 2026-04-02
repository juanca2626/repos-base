import RequirementsLayout from './Layout'
import RequirementsList from './List'
import RequirementsForm from './Form'

export default [
  {
    path: 'requirements',
    alias: '',
    component: RequirementsLayout,
    redirect: '/requirements/list',
    name: 'Requirements',
    meta: {
      breadcrumb: 'Requisitos'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: RequirementsList,
        name: 'RequirementsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: RequirementsForm,
        name: 'RequirementsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: RequirementsForm,
        name: 'RequirementsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
