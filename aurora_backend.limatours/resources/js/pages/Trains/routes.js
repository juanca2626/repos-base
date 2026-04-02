import TrainsLayout from './Layout'
import TrainsList from './List'
import TrainsForm from './Form'
import ManageTrainLayout from './ManageTrain/ManageTrainLayout'

import TrainTextsForm from './ManageTrain/Texts/TrainTextsForm'

import TrainConfigurationsLayout from './ManageTrain/Configuration/TrainConfigurationsLayout'
import TrainAmenitiesLayout from './ManageTrain/Amenities/TrainAmenitiesLayout'

import IncludeTrainLayout from './ManageTrain/Includes/IncludeTrainLayout'

import TrainGalleryLayout from './ManageTrain/Gallery/TrainGalleryLayout'
import TrainGalleryManageAdd from './ManageTrain/Gallery/TrainGallery/TrainGalleryManageAdd'
import TrainGalleryManageList from './ManageTrain/Gallery/TrainGallery/TrainGalleryManageList'

import RatesLayout from './ManageTrain/Rates/Layout'
import RatesRatesCostLayout from './ManageTrain/Rates/Cost/Layout'
import RatesRatesCostList from './ManageTrain/Rates/Cost/List'
import RatesRatesCostForm from './ManageTrain/Rates/Cost/Form'
import RatesSale from './ManageTrain/Rates/Sale/Sale'
import AvailabilityTrainLayout from './ManageTrain/Availability/AvailabilityTrainLayout'

import TrainRoutesList from './Route/List'
import TrainClassesList from './Class/List'
import TrainUsersList from './User/List'
import TrainCancellationList from './Cancellation/List'

export default [
    {
        path: 'trains',
        alias: '',
        component: TrainsLayout,
        redirect: '/trains/list',
        name: 'Trains',
        meta: {
            breadcrumb: 'Trenes'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: TrainsList,
                name: 'TrainsList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: TrainsForm,
                name: 'TrainsAdd',
                meta: {
                    breadcrumb: 'Agregar'
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: TrainsForm,
                name: 'TrainsEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            },
            {
                path: ':train_id/manage_train',
                alias: '',
                component: ManageTrainLayout,
                redirect: ':train_id/manage_train',
                name: 'ManageTrainLayout',
                meta: {
                    breadcrumb: 'Administrar Tren'
                },
                children: [
                    {
                        path: 'train_texts',
                        alias: '',
                        component: TrainTextsForm,
                        name: 'TrainTextsForm',
                        redirect: '/train_texts/form',
                        meta: {
                            breadcrumb: ''
                        },
                        children: [
                            {
                                path: 'form',
                                alias: '',
                                component: TrainTextsForm,
                                name: 'TrainTextsForm',
                                meta: {
                                    breadcrumb: 'Form'
                                }
                            }
                        ]
                    },
                    {
                        path: 'amenities',
                        alias: '',
                        component: TrainAmenitiesLayout,
                        name: 'TrainAmenitiesLayout',
                        meta: {
                            breadcrumb: 'Amenities'
                        }
                    },
                    {
                        path: 'includes',
                        alias: '',
                        component: IncludeTrainLayout,
                        name: 'IncludeTrainLayout',
                        meta: {
                            breadcrumb: 'Incluyentes'
                        }
                    },
                    {
                        path: 'train_configurations',
                        alias: '',
                        component: TrainConfigurationsLayout,
                        name: 'TrainConfigurationsLayout',
                        meta: {
                            breadcrumb: 'Configuración'
                        }
                    },
                    {
                        path: 'train_gallery',
                        alias: '',
                        component: TrainGalleryLayout,
                        name: 'TrainGalleryLayout',
                        meta: {
                            breadcrumb: 'Galería'
                        },
                        children: [
                            {
                                path: 'traingallery/list',
                                alias: '',
                                component: TrainGalleryManageList,
                                name: 'TrainGalleryManageList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            },
                            {
                                path: 'traingallery/add',
                                alias: '',
                                component: TrainGalleryManageAdd,
                                name: 'TrainGalleryManageAdd',
                                meta: {
                                    breadcrumb: 'Agregar'
                                }
                            }
                        ]
                    },
                    {
                        path: 'rates',
                        alias: '',
                        component: RatesLayout,
                        redirect: ':train_id/manage_train/rates/cost/list',
                        name: 'RatesLayout',
                        meta: {
                            breadcrumb: 'Tarifas'
                        },
                        children: [
                            {
                                path: 'cost',
                                alias: '',
                                component: RatesRatesCostLayout,
                                redirect: ':train_id/manage_train/rates/cost/list',
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
                        path: 'inventories',
                        alias: '',
                        component: AvailabilityTrainLayout,
                        name: 'AvailabilityTrainLayout',
                        meta: {
                            breadcrumb: 'Inventario'
                        }
                    }
                ]
            },
            {
                path: 'train_routes',
                alias: '',
                redirect: '/train_routes/list',
                component: TrainRoutesList,
                name: 'TrainsRoutes',
                meta: {
                    breadcrumb: 'Train Routes'
                },
                children:[
                    {
                        path: 'list',
                        alias: '',
                        component: TrainRoutesList,
                        name: 'TrainRoutesList',
                        meta: {
                            breadcrumb: 'Lista de Rutas'
                        }
                    }
                ]
            },
            {
                path: 'train_classes',
                alias: '',
                redirect: '/train_classes/list',
                component: TrainClassesList,
                name: 'TrainsClasses',
                meta: {
                    breadcrumb: 'Train Classes'
                },
                children:[
                    {
                        path: 'list',
                        alias: '',
                        component: TrainClassesList,
                        name: 'TrainClassesList',
                        meta: {
                            breadcrumb: 'Lista de Clases'
                        }
                    }
                ]
            },
            {
                path: 'train_users',
                alias: '',
                redirect: '/train_users/list',
                component: TrainUsersList,
                name: 'TrainsUsers',
                meta: {
                    breadcrumb: 'Train Users'
                },
                children:[
                    {
                        path: 'list',
                        alias: '',
                        component: TrainUsersList,
                        name: 'TrainUsersList',
                        meta: {
                            breadcrumb: 'Lista de Usuarios'
                        }
                    }
                ]
            },
            {
                path: 'train_cancellations',
                alias: '',
                redirect: '/train_cancellations/list',
                component: TrainCancellationList,
                name: 'TrainCancellations',
                meta: {
                    breadcrumb: 'Train Cancelations'
                },
                children:[
                    {
                        path: 'list',
                        alias: '',
                        component: TrainCancellationList,
                        name: 'TrainCancellationList',
                        meta: {
                            breadcrumb: 'Políticas de Cancelación'
                        }
                    }
                ]
            }
        ]
    }
]
