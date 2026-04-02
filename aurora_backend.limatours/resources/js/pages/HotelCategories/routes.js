import HotelCategoriesLayout from './Layout'
import HotelCategoriesList from './List'
import HotelCategoriesForm from './Form'

export default [
  {
    path: 'hotelcategories',
    alias: '',
    component: HotelCategoriesLayout,
    redirect: '/hotelcategories/list',
    name: 'hotelcategories',
    meta: {
      breadcrumb: 'Categorias'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: HotelCategoriesList,
        name: 'HotelCategoriesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: HotelCategoriesForm,
        name: 'HotelCategoriesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: HotelCategoriesForm,
        name: 'HotelCategoriesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
