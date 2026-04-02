import HotelTypesLayout from './Layout'
import HotelTypesList from './List'
import HotelTypesForm from './Form'

export default [
  {
    path: 'hotel_types',
    alias: '',
    component: HotelTypesLayout,
    redirect: '/hotel_types/list',
    name: 'HotelTypes',
    meta: {
      breadcrumb: 'Tipo de hoteles'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: HotelTypesList,
        name: 'HotelTypesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: HotelTypesForm,
        name: 'HotelTypesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: HotelTypesForm,
        name: 'HotelTypesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
