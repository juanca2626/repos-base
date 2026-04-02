import RoomTypesLayout from './Layout'
import RoomTypesList from './List'
import RoomTypesForm from './Form'

export default [
  {
    path: 'room_types',
    alias: '',
    component: RoomTypesLayout,
    redirect: '/room_types/list',
    name: 'RoomTypes',
    meta: {
      breadcrumb: 'Tipo de Habitación'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: RoomTypesList,
        name: 'RoomTypesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: RoomTypesForm,
        name: 'RoomTypesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: RoomTypesForm,
        name: 'RoomTypesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
