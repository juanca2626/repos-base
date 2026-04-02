import ZonesLayout from './Layout'
import ZonesList from './List'
import ZonesForm from './Form'

export default [
  {
    path: 'zones',
    alias: '',
    component: ZonesLayout,
    redirect: '/zones/list',
    name: 'Zones',
    meta: {
      breadcrumb: 'Zonas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: ZonesList,
        name: 'ZonesList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: ZonesForm,
        name: 'ZonesAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: ZonesForm,
        name: 'ZonesEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
