import CitiesLayout from './Layout'
import CitiesList from './List'
import CitiesForm from './Form'

export default [
  {
    path: 'cities',
    alias: '',
    component: CitiesLayout,
    redirect: '/cities/list',
    name: 'Cities',
    meta: {
      breadcrumb: 'Ciudades'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: CitiesList,
        name: 'CitiesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: CitiesForm,
        name: 'CitiesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: CitiesForm,
        name: 'CitiesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
