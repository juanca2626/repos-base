import ReservationCenterLayout from './Layout'
import TourcmsList from './Tourcms/TourcmsList'
import DespegarList from './Despegar/DespegarList'
import ExpediaList from './Expedia/ExpediaList'
import PentagramaList from './Pentagrama/PentagramaList'

export default [
  {
    path: 'reservation_center',
    alias: '',
    component: ReservationCenterLayout,
    name: 'ReservationCenter',
    meta: {
      breadcrumb: 'ReservationCenter'
    },
    children: [
      {
        path: 'tourcms',
        alias: '',
        component: TourcmsList,
        name: 'TourcmsList',
        meta: {
          breadcrumb: 'TourCMS'
        }

      },
      {
        path: 'despegar',
        alias: '',
        component: DespegarList,
        name: 'DespegarList',
        meta: {
          breadcrumb: 'Despegar.com'
        }

      },
      {
        path: 'expedia',
        alias: '',
        component: ExpediaList,
        name: 'ExpediaList',
        meta: {
          breadcrumb: 'Expedia'
        }

      },
      {
        path: 'pentagrama',
        alias: '',
        component: PentagramaList,
        name: 'PentagramaList',
        meta: {
          breadcrumb: 'Pentagrama'
        }

      }
    ]
  }]
