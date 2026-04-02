import TypeOperabilityLayout from './Layout'
import TypeOperabilityList from './List'
import TypeOperabilityForm from './Form'

export default [
  {
    path: 'type_operability',
    alias: '',
    component: TypeOperabilityLayout,
    redirect: '/type_operability/list',
    name: 'type_operability',
    meta: {
      breadcrumb: 'Tipo de operatividad'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: TypeOperabilityList,
        name: 'TypeOperabilityList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: TypeOperabilityForm,
        name: 'TypeOperabilityAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: TypeOperabilityForm,
        name: 'TypeOperabilityEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
