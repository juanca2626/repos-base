import BookingsLayout from './/Layout'
import BookingsList from './/List'


export default [
    {
        path: 'bookings',
        alias: '',
        component: BookingsLayout,
        name: 'Bookings',
        redirect: 'list',
        meta: {
            breadcrumb: 'Registros de reservas'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: BookingsList,
                name: 'BookingsList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            // {
            //     path: ':reservation_id/detail',
            //     alias: '',
            //     component: ManageServiceLayout,
            //     redirect: ':service_id/manage_service/schedule',
            //     name: 'ManageServiceLayout',
            //     meta: {
            //         breadcrumb: 'Administrar Servicio'
            //     },
            //     children: [
            //         {
            //             path: 'schedule',
            //             alias: '',
            //             component: ScheduleServiceLayout,
            //             name: 'ScheduleServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Horarios'
            //             }
            //         },
            //         {
            //             path: 'operability',
            //             alias: '',
            //             component: OperabilityServiceLayout,
            //             name: 'OperabilityServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Operatividad'
            //             }
            //         },
            //         {
            //             path: 'rates',
            //             alias: '',
            //             component: RatesLayout,
            //             redirect: ':service_id/manage_services/rates/cost/list',
            //             name: 'RatesLayout',
            //             meta: {
            //                 breadcrumb: 'Tarifas'
            //             },
            //             children: [
            //                 {
            //                     path: 'cost',
            //                     alias: '',
            //                     component: RatesRatesCostLayout,
            //                     redirect: ':service_id/manage_services/rates/cost/list',
            //                     name: 'RatesRatesCost',
            //                     meta: {
            //                         breadcrumb: 'Costo'
            //                     },
            //                     children: [
            //                         {
            //                             path: 'list',
            //                             alias: '',
            //                             component: RatesRatesCostList,
            //                             name: 'RatesRatesCostList',
            //                             meta: {
            //                                 breadcrumb: ''
            //                             }
            //                         },
            //                         {
            //                             path: 'add',
            //                             alias: '',
            //                             component: RatesRatesCostForm,
            //                             name: 'RatesRatesCostForm',
            //                             meta: {
            //                                 breadcrumb: 'Agregar'
            //                             }
            //                         },
            //                         {
            //                             path: 'edit/:rate_id',
            //                             alias: '',
            //                             component: RatesRatesCostForm,
            //                             name: 'RatesRatesCostFormEdit',
            //                             meta: {
            //                                 breadcrumb: 'Editar'
            //                             }
            //                         },
            //                         {
            //                             path: 'supplements/:rate_id',
            //                             alias: '',
            //                             component: SupplementServiceRate,
            //                             name: 'SupplementServiceRate',
            //                             meta: {
            //                                 breadcrumb: 'Suplementos'
            //                             }
            //                         }
            //                     ]
            //                 },
            //                 {
            //                     path: 'sale',
            //                     alias: '',
            //                     component: RatesSale,
            //                     name: 'RatesRatesSale',
            //                     meta: {
            //                         breadcrumb: 'Venta'
            //                     }
            //                 }
            //             ]
            //         },
            //         {
            //             path: 'gallery',
            //             alias: '',
            //             component: GalleryLayout,
            //             name: 'GalleryLayout',
            //             meta: {
            //                 breadcrumb: 'Galería'
            //             },
            //             children: [
            //                 {
            //                     path: 'gallery/list',
            //                     alias: '',
            //                     component: GalleryManageServiceList,
            //                     name: 'ServiceManageGalleryList',
            //                     meta: {
            //                         breadcrumb: ''
            //                     }
            //                 },
            //                 {
            //                     path: 'gallery/add',
            //                     alias: '',
            //                     component: GalleryManageServiceAdd,
            //                     name: 'ServiceManageGalleryAdd',
            //                     meta: {
            //                         breadcrumb: 'Agregar'
            //                     }
            //                 }
            //             ]
            //         },
            //         {
            //             path: 'politics',
            //             alias: '',
            //             component: ServicePoliticsLayout,
            //             name: 'ServicePoliticsLayout',
            //             meta: {
            //                 breadcrumb: 'Configuracíon'
            //             }
            //         },
            //         {
            //             path: 'includes',
            //             alias: '',
            //             component: IncludeServiceLayout,
            //             name: 'IncludeServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Incluyentes'
            //             }
            //         },
            //         {
            //             path: 'availability',
            //             alias: '',
            //             component: AvailabilityServiceLayout,
            //             name: 'AvailabilityServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Disponiblidad'
            //             }
            //         },
            //         {
            //             path: 'service_components',
            //             alias: '',
            //             component: ServiceComponentsLayout,
            //             name: 'ServiceComponentsLayout',
            //             redirect: '/service_components/list',
            //             meta: {
            //                 breadcrumb: ''
            //             },
            //             children: [
            //                 {
            //                     path: 'list',
            //                     alias: '',
            //                     component: ServiceComponentsList,
            //                     name: 'ServiceComponentsList',
            //                     meta: {
            //                         breadcrumb: 'Componentes'
            //                     }
            //                 }
            //             ]
            //         },
            //         {
            //             path: 'service_equivalences',
            //             alias: '',
            //             component: ServiceEquivalenceAssociationsLayout,
            //             name: 'ServiceEquivalenceAssociationsLayout',
            //             redirect: '/service_equivalences/list',
            //             meta: {
            //                 breadcrumb: ''
            //             },
            //             children: [
            //                 {
            //                     path: 'list',
            //                     alias: '',
            //                     component: ServiceEquivalenceAssociationsList,
            //                     name: 'ServiceEquivalenceAssociationsList',
            //                     meta: {
            //                         breadcrumb: 'Asociación de componentes'
            //                     }
            //                 }
            //             ]
            //         },
            //         {
            //             path: 'featured',
            //             alias: '',
            //             component: FeaturedServiceLayout,
            //             name: 'FeaturedServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Información Destacada'
            //             }
            //         },
            //         {
            //             path: 'service_instructions',
            //             alias: '',
            //             component: InstructionsServiceLayout,
            //             name: 'InstructionsServiceLayout',
            //             meta: {
            //                 breadcrumb: 'Instrucciones'
            //             }
            //         },
            //         {
            //             path: 'service_supplements',
            //             alias: '',
            //             component: ServiceSupplementsLayout,
            //             redirect: ':service_id/manage_service/service_supplements/list',
            //             name: 'ServiceSupplementsLayout',
            //             meta: {
            //                 breadcrumb: 'Suplementos'
            //             },
            //             children: [
            //                 {
            //                     path: 'list',
            //                     alias: '',
            //                     component: ServiceSupplement,
            //                     name: 'ServiceSupplementList',
            //                     meta: {
            //                         breadcrumb: 'Lista'
            //                     }
            //                 },
            //                 {
            //                     path: 'service_supplements/amounts/:supplement_id',
            //                     alias: '',
            //                     component: ServiceSupplementAmounts,
            //                     name: 'ServiceSupplementAmounts',
            //                     meta: {
            //                         breadcrumb: 'Suplementos'
            //                     }
            //                 },
            //                 // {
            //                 //     path: 'calendary',
            //                 //     alias: '',
            //                 //     component: SupplementCalendary,
            //                 //     name: 'Supplement',
            //                 //     meta: {
            //                 //         breadcrumb: 'Supplements'
            //                 //     }
            //                 // }
            //             ]
            //         }
            //
            //     ]
            // }
        ]
    }]
