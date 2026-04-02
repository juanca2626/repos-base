import TicketsLayout from './Layout'
import TicketsList from './List'

export default [
  {
    path: 'tickets',
    alias: '',
    component: TicketsLayout,
    redirect: '/tickets/list',
    name: 'Tickets',
    meta: {
      breadcrumb: 'Tickets'
    },
    children: [
      {
        path: 'list',
        alias: '',
        component: TicketsList,
        name: 'TicketsList',
        meta: {
          breadcrumb: 'Lista'
        }
      }
    ]
  }]
