import TranslationsLayout from './Layout'
import TranslationsList from './List'
import TranslationsForm from './Form'

export default [
  {
    path: 'translations',
    alias: '',
    component: TranslationsLayout,
    redirect: '/translations/list',
    name: 'Translations',
    meta: {
      breadcrumb: 'Traducciones'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: TranslationsList,
        name: 'TranslationsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: TranslationsForm,
        name: 'TranslationsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: TranslationsForm,
        name: 'TranslationsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
