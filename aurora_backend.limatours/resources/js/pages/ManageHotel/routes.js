import ManageHotelLayout from './Layout'
import TransactionReport from './TransactionReport'
import InformationList from './Information/Information'
import ConfigurationList from './Configuration/List'

export default [
  {
    path: 'manage_hotels',
    alias: '',
    component: ManageHotelLayout,
    redirect: '/manage_hotels/information',
    name: 'ManageHotels',
    meta: {
      breadcrumb: 'Administrar Hotel'
    },
    children: [
      {
        path: 'transaction_reports',
        alias: '',
        component: TransactionReport,
        name: 'TransactionReports',
        meta: {
          breadcrumb: 'Reporte Transacción'
        }
      },
      {
        path: 'informations',
        alias: '',
        component: InformationList,
        redirect: '/informations/list',
        name: 'Information',
        meta: {
          breadcrumb: 'Información'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: InformationList,
            name: 'InformationList',
            meta: {
              breadcrumb: 'Lista'
            }
          }
        ]
      },
      {
        path: 'configurations',
        alias: '',
        component: ConfigurationList,
        redirect: '/configurations/list',
        name: 'Configurations',
        meta: {
          breadcrumb: 'configuración'
        },
        children: [
          {
            path: 'list',
            alias: '',
            component: ConfigurationList,
            name: 'ConfigurationList',
            meta: {
              breadcrumb: 'Lista'
            }
          }
        ]
      }
    ]
  }]
