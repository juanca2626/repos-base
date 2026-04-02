import InstructionsLayout from './Layout'
import InstructionsList from './List'
import InstructionsForm from './Form'

export default [
  {
    path: 'instructions',
    alias: '',
    component: InstructionsLayout,
    redirect: '/instructions/list',
    name: 'Instructions',
    meta: {
      breadcrumb: 'Instrucciones'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: InstructionsList,
        name: 'InstructionsList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: InstructionsForm,
        name: 'InstructionAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: InstructionsForm,
        name: 'InstructionEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      }
    ]
  }]
