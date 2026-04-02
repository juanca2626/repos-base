import OrderLayout from './Layout'
import HotelOrderLayout from './HotelLayout'

export default [
    {
        path: 'order_rates',
        alias: '',
        component: OrderLayout,
        name: 'OrdersRate',
        meta: {
            breadcrumb: 'Orders Rate'
        }
    },
    {
        path: 'hotel_order_rates',
        alias: '',
        component: HotelOrderLayout,
        name: 'HotelOrdersRate',
        meta: {
            breadcrumb: 'Hotel Orders Rate'
        }
    },
]
