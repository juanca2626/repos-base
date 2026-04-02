import ExperiencesLayout from './Layout'
import ExperiencesList from './List'
import ExperiencesForm from './Form'

export default [
  {
    path: 'experiences',
    alias: '',
    component: ExperiencesLayout,
    redirect: '/experiences/list',
    name: 'Experiences',
    meta: {
      breadcrumb: 'Experiencias'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ExperiencesList,
        name: 'ExperiencesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ExperiencesForm,
        name: 'ExperiencesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ExperiencesForm,
        name: 'ExperiencesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
