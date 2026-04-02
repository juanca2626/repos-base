import LanguagesLayout from './Layout'
import LanguagesList from './List'
import LanguagesForm from './Form'

export default [
  {
    path: 'languages',
    alias: '',
    component: LanguagesLayout,
    redirect: '/languages/list',
    name: 'Languages',
    meta: {
      breadcrumb: 'Idiomas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: LanguagesList,
        name: 'LanguagesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: LanguagesForm,
        name: 'LanguagesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: LanguagesForm,
        name: 'LanguagesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
