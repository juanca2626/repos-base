import AmenitiesLayout from './Layout'
import AmenitiesList from './List'
import AmenitiesForm from './Form'

export default [
  {
    path: 'amenities',
    alias: '',
    component: AmenitiesLayout,
    redirect: '/amenities/list',
    name: 'Amenities',
    meta: {
      breadcrumb: 'Amenidades'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: AmenitiesList,
        name: 'AmenitiesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: AmenitiesForm,
        name: 'AmenitiesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: AmenitiesForm,
        name: 'AmenitiesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
