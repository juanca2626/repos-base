import DistrictsLayout from './Layout'
import DistrictsList from './List'
import DistrictsForm from './Form'

export default [
  {
    path: 'districts',
    alias: '',
    component: DistrictsLayout,
    redirect: '/districts/list',
    name: 'Districts',
    meta: {
      breadcrumb: 'Distritos'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: DistrictsList,
        name: 'DistrictsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: DistrictsForm,
        name: 'DistrictsAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: DistrictsForm,
        name: 'DistrictsEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
