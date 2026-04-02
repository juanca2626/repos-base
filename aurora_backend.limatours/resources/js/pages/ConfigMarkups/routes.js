import MarkupLayout from './MarkupLayout'
import HotelMarkupLayout from './HotelMarkupLayout'

export default [
    {
        path: 'services_markups',
        alias: '',
        component: MarkupLayout,
        name: 'ServicesMarkup',
        meta: {
            breadcrumb: 'Services Markups'
        }
    },
    {
        path: 'hotels_markups',
        alias: '',
        component: HotelMarkupLayout,
        name: 'HotelMarkup',
        meta: {
            breadcrumb: 'Hotels Markups'
        }
    },
]
