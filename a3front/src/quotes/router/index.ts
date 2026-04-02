import type { RouteRecordRaw } from 'vue-router';

import QuotesLayout from '@/quotes/layouts/QuotesLayout.vue';
import QuotesList from '@/quotes/pages/QuotesList.vue';
import QuotesHotelDetails from '@/quotes/pages/QuotesHotelDetails.vue';
import QuotesDetail from '@/quotes/pages/QuotesDetail.vue';
import QuotesReservations from '@/quotes/pages/QuotesReservations.vue';
import QuotesReservationsConfirmation from '@/quotes/pages/QuotesReservationsConfirmation.vue';
import QuotesReports from '@/quotes/pages/QuotesReports.vue';

const ROUTE_NAME = 'quotes';

export const quotesRoutes: RouteRecordRaw = {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  redirect: `/${ROUTE_NAME}`,
  component: QuotesLayout,
  meta: { breadcrumb: 'Home' },
  children: [
    {
      path: `/${ROUTE_NAME}`,
      name: 'quotes-list',
      component: QuotesList,
      meta: { breadcrumb: 'Editar' },
    },
    {
      path: `/${ROUTE_NAME}/hotel-details/:id`,
      name: 'quotes-hotel-details',
      component: QuotesHotelDetails,
      meta: { breadcrumb: 'HotelDetails' },
    },
    {
      path: `/${ROUTE_NAME}/quotes-details`,
      name: 'quotes-details',
      component: QuotesDetail,
      meta: { breadcrumb: 'QuoteDetails' },
    },
    {
      path: `/${ROUTE_NAME}/reservations`,
      name: 'quotes-reservations',
      component: QuotesReservations,
      meta: { breadcrumb: 'QuotesReservations' },
    },
    {
      path: `/${ROUTE_NAME}/quotes-reservations-confirmation`,
      name: 'quotes-reservations-confirmation',
      component: QuotesReservationsConfirmation,
      meta: { breadcrumb: 'QuotesReservationsConfirmation' },
    },
    {
      path: `/${ROUTE_NAME}/reports`,
      name: 'quotes-reports',
      component: QuotesReports,
      meta: { breadcrumb: 'QuotesReports' },
    },
    {
      path: `/${ROUTE_NAME}/error`,
      name: 'quotes-error',
      component: () => import('@/views/ErrorView.vue'),
      meta: { breadcrumb: 'Error' },
      props: (route) => ({
        title: route.query.title,
        subtitle: route.query.subtitle,
        status: route.query.status || '500',
      }),
    },
  ],
};
