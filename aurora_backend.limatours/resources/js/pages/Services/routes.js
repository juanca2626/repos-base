import ServicesLayout1 from './Layout'
import ServicesList1 from './List'
import ServicesForm1 from './Form'

import ManageServiceLayout from './ManageService/ManageServiceLayout'
import ScheduleServiceLayout from './ManageService/ScheduleService/ScheduleServiceLayout'
import OperabilityServiceLayout from './ManageService/Operability/OperabilityServiceLayout'

import GalleryLayout from './ManageService/Gallery/GalleryLayout'
import GalleryManageServiceAdd from './ManageService/Gallery/ServiceGallery/GalleryManageServiceAdd'
import GalleryManageServiceList from './ManageService/Gallery/ServiceGallery/GalleryManageServiceList'

import RatesLayout from './ManageService/Rates/Layout'
import RatesRatesCostLayout from './ManageService/Rates/Cost/Layout'
import RatesRatesCostList from './ManageService/Rates/Cost/List'
import RatesRatesCostForm from './ManageService/Rates/Cost/Form'
import RatesSale from './ManageService/Rates/Sale/Sale'

import ServicePoliticsLayout from './ManageService/Politics/ServicePoliticsLayout'

import IncludeServiceLayout from './ManageService/Includes/IncludeServiceLayout'

import ServiceComponentsLayout from './ManageService/Components/ServiceComponentsLayout'
import ServiceComponentsList from './ManageService/Components/ServiceComponentsList'

import AvailabilityServiceLayout from './ManageService/Availability/AvailabilityServiceLayout'

import ServiceEquivalenceAssociationsLayout from './ManageService/EquivalenceAssociation/EquivalenceAssociationsLayout'
import ServiceEquivalenceAssociationsList from './ManageService/EquivalenceAssociation/EquivalenceAssociationsList'

import FeaturedServiceLayout from './ManageService/Featured/FeaturedServiceLayout'
import InstructionsServiceLayout from './ManageService/Instructions/InstructionsServiceLayout'

import ServiceSupplementsLayout from './ManageService/Supplement/ServiceSupplementsLayout'
import ServiceSupplement from '../Services/ManageService/Supplement/Associate/ServiceSupplement'
import ServiceSupplementAmounts from '../Services/ManageService/Supplement/Associate/ServiceSupplementAmounts'
import SupplementServiceRate from '../Services/ManageService/Rates/Cost/Supplements/SupplementServiceRate'

import ServiceCompositionLayout from './ManageService/Composition/ServiceCompositionLayout'
import ServiceCompositionList from './ManageService/Composition/ServiceCompositionList'

import ServiceRatesOtsListLayout from './ManageService/RateOts/ServiceRatesOtsLayout'
import ServiceRatesOtsList from './ManageService/RateOts/ServiceRatesOtsList'

export default [
    {
        path: 'services_new',
        alias: '',
        component: ServicesLayout1,
        name: 'Services',
        redirect: 'list',
        meta: {
            breadcrumb: 'Servicios'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: ServicesList1,
                name: 'ServicesList1',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: ServicesForm1,
                name: 'ServicesAdd1',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: ServicesForm1,
                name: 'ServicesEdit1',
                meta: {
                    breadcrumb: 'Editar'
                }
            },
            {
                path: ':service_id/manage_service',
                alias: '',
                component: ManageServiceLayout,
                redirect: ':service_id/manage_service/schedule',
                name: 'ManageServiceLayout',
                meta: {
                    breadcrumb: 'Administrar Servicio'
                },
                children: [
                    {
                        path: 'schedule',
                        alias: '',
                        component: ScheduleServiceLayout,
                        name: 'ScheduleServiceLayout',
                        meta: {
                            breadcrumb: 'Horarios'
                        }
                    },
                    {
                        path: 'operability',
                        alias: '',
                        component: OperabilityServiceLayout,
                        name: 'OperabilityServiceLayout',
                        meta: {
                            breadcrumb: 'Operatividad'
                        }
                    },
                    {
                        path: 'rates',
                        alias: '',
                        component: RatesLayout,
                        redirect: ':service_id/manage_services/rates/cost/list',
                        name: 'RatesLayout',
                        meta: {
                            breadcrumb: 'Tarifas'
                        },
                        children: [
                            {
                                path: 'cost',
                                alias: '',
                                component: RatesRatesCostLayout,
                                redirect: ':service_id/manage_services/rates/cost/list',
                                name: 'RatesRatesCost',
                                meta: {
                                    breadcrumb: 'Costo'
                                },
                                children: [
                                    {
                                        path: 'list',
                                        alias: '',
                                        component: RatesRatesCostList,
                                        name: 'RatesRatesCostList',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    },
                                    {
                                        path: 'add',
                                        alias: '',
                                        component: RatesRatesCostForm,
                                        name: 'RatesRatesCostForm',
                                        meta: {
                                            breadcrumb: 'Agregar'
                                        }
                                    },
                                    {
                                        path: 'edit/:rate_id',
                                        alias: '',
                                        component: RatesRatesCostForm,
                                        name: 'RatesRatesCostFormEdit',
                                        meta: {
                                            breadcrumb: 'Editar'
                                        }
                                    },
                                    {
                                        path: 'supplements/:rate_id',
                                        alias: '',
                                        component: SupplementServiceRate,
                                        name: 'SupplementServiceRate',
                                        meta: {
                                            breadcrumb: 'Suplementos'
                                        }
                                    }
                                ]
                            },
                            {
                                path: 'sale',
                                alias: '',
                                component: RatesSale,
                                name: 'RatesRatesSale',
                                meta: {
                                    breadcrumb: 'Venta'
                                }
                            }
                        ]
                    },
                    {
                        path: 'gallery',
                        alias: '',
                        component: GalleryLayout,
                        name: 'GalleryLayout',
                        meta: {
                            breadcrumb: 'Galería'
                        },
                        children: [
                            {
                                path: 'gallery/list',
                                alias: '',
                                component: GalleryManageServiceList,
                                name: 'ServiceManageGalleryList',
                                meta: {
                                    breadcrumb: ''
                                }
                            },
                            {
                                path: 'gallery/add',
                                alias: '',
                                component: GalleryManageServiceAdd,
                                name: 'ServiceManageGalleryAdd',
                                meta: {
                                    breadcrumb: 'Agregar'
                                }
                            }
                        ]
                    },
                    {
                        path: 'politics',
                        alias: '',
                        component: ServicePoliticsLayout,
                        name: 'ServicePoliticsLayout',
                        meta: {
                            breadcrumb: 'Configuracíon'
                        }
                    },
                    {
                        path: 'includes',
                        alias: '',
                        component: IncludeServiceLayout,
                        name: 'IncludeServiceLayout',
                        meta: {
                            breadcrumb: 'Incluyentes'
                        }
                    },
                    {
                        path: 'availability',
                        alias: '',
                        component: AvailabilityServiceLayout,
                        name: 'AvailabilityServiceLayout',
                        meta: {
                            breadcrumb: 'Disponiblidad'
                        }
                    },
                    {
                        path: 'service_components',
                        alias: '',
                        component: ServiceComponentsLayout,
                        name: 'ServiceComponentsLayout',
                        redirect: '/service_components/list',
                        meta: {
                            breadcrumb: ''
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: ServiceComponentsList,
                                name: 'ServiceComponentsList',
                                meta: {
                                    breadcrumb: 'Componentes'
                                }
                            }
                        ]
                    },
                    {
                        path: 'service_equivalences',
                        alias: '',
                        component: ServiceEquivalenceAssociationsLayout,
                        name: 'ServiceEquivalenceAssociationsLayout',
                        redirect: '/service_equivalences/list',
                        meta: {
                            breadcrumb: ''
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: ServiceEquivalenceAssociationsList,
                                name: 'ServiceEquivalenceAssociationsList',
                                meta: {
                                    breadcrumb: 'Asociación de componentes'
                                }
                            }
                        ]
                    },
                    {
                        path: 'featured',
                        alias: '',
                        component: FeaturedServiceLayout,
                        name: 'FeaturedServiceLayout',
                        meta: {
                            breadcrumb: 'Información Destacada'
                        }
                    },
                    {
                        path: 'service_instructions',
                        alias: '',
                        component: InstructionsServiceLayout,
                        name: 'InstructionsServiceLayout',
                        meta: {
                            breadcrumb: 'Instrucciones'
                        }
                    },
                    {
                        path: 'service_supplements',
                        alias: '',
                        component: ServiceSupplementsLayout,
                        redirect: ':service_id/manage_service/service_supplements/list',
                        name: 'ServiceSupplementsLayout',
                        meta: {
                            breadcrumb: 'Suplementos'
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: ServiceSupplement,
                                name: 'ServiceSupplementList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            },
                            {
                                path: 'service_supplements/amounts/:supplement_id',
                                alias: '',
                                component: ServiceSupplementAmounts,
                                name: 'ServiceSupplementAmounts',
                                meta: {
                                    breadcrumb: 'Suplementos'
                                }
                            },
                            // {
                            //     path: 'calendary',
                            //     alias: '',
                            //     component: SupplementCalendary,
                            //     name: 'Supplement',
                            //     meta: {
                            //         breadcrumb: 'Supplements'
                            //     }
                            // }
                        ]
                    },
                    {
                        path: 'composition',
                        alias: '',
                        component: ServiceCompositionLayout,
                        name: 'ServiceCompositionLayout',
                        meta: {
                            breadcrumb: 'Liberados'
                        },
                        children: [
                            {
                                path: 'composition/list',
                                alias: '',
                                component: ServiceCompositionList,
                                name: 'ServiceCompositionList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            }
                        ]
                    },
                    {
                        path: 'rates_ots',
                        alias: '',
                        component: ServiceRatesOtsListLayout,
                        name: 'ServiceRatesOtsListLayout',
                        meta: {
                            breadcrumb: 'Tarifas OTS'
                        },
                        children: [
                            {
                                path: 'rates_ots/list',
                                alias: '',
                                component: ServiceRatesOtsList,
                                name: 'ServiceRatesOtsList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            }
                        ]
                    },


                ]
            }
        ]
    }]
