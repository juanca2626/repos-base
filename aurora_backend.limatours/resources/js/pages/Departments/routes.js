import DepartmentLayout from './Layout'
import DepartmentList from './List'
import DepartmentForm from './Form'

import DepartmentTeamLayout from './DepartmentTeams/DepartmentTeamLayout'
import DepartmentTeamList from './DepartmentTeams/DepartmentTeamList'
import DepartmentTeamForm from './DepartmentTeams/DepartmentTeamForm'

export default [
  {
    path: 'departments',
    alias: '',
    component: DepartmentLayout,
    redirect: '/departments/list',
    name: 'departments',
    meta: {
      breadcrumb: 'Areas'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: DepartmentList,
        name: 'DepartmentList',
        meta: {
          breadcrumb: 'Lista'
        }
      },
      {
        path: 'add',
        alias: '',
        component: DepartmentForm,
        name: 'DepartmentFormAdd',
        meta: {
          breadcrumb: 'Agregar'
        }
      },
      {
        path: 'edit/:id',
        alias: '',
        component: DepartmentForm,
        name: 'DepartmentFormEdit',
        meta: {
          breadcrumb: 'Editar'
        }
      },
      {
        path: ':department_id/teams/',
        alias: '',
        component: DepartmentTeamLayout,
        redirect: ':department_id/teams/list',
        name: 'DepartmentTeamLayout',
        meta: {
          breadcrumb: 'Equipos'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: DepartmentTeamList,
            name: 'DepartmentTeamList',
            meta: {
              breadcrumb: 'Lista.'
            }
          },
          {
            path: 'add',
            alias: '',
            component: DepartmentTeamForm,
            name: 'DepartmentTeamFormAdd',
            meta: {
              breadcrumb: 'Agregar'
            }
          },
          {
            path: 'edit/:id',
            alias: '',
            component: DepartmentTeamForm,
            name: 'DepartmentTeamFormEdit',
            meta: {
              breadcrumb: 'Editar'
            }
          }
        ]
      }
    ]
  }]
