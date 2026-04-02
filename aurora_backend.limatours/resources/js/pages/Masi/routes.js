import UnitsLayout from './Layout'
// import UnitsList from './List'
// import UnitsForm from './Form'
// import MasiGraficos from './Graficos'
import Dashboard from './Dashboard'

export default [{
  path: 'masi',
  alias: '',
  component: UnitsLayout,
  // redirect: '/masi/dashboard',
  name: 'Masi',
  meta: {},
  children: [{
    path: 'dashboard',
    alias: '',
    component: Dashboard,
    name: 'Dashboard',
    meta: { breadcrumb: 'Masi' }
  }]
}]
