import HotelsLayout from './Layout'
import HotelsList from './List'
import HotelsForm from './Form'
import ManageHotelLayout from './ManageHotel/ManageHotelLayout'
import TransactionReportLayout from './ManageHotel/TransactionReport/TransactionReportLayout'

import InventoriesLayout from './ManageHotel/Inventories/InventoriesLayout'
import InventoryLayout from './ManageHotel/Inventories/InventoryLayout'
import InventoryByDateRange from './ManageHotel/Inventories/InventoryByDateRange'
import InventoryHistory from './ManageHotel/Inventories/InventoryHistory'
import InventoryChannels from './ManageHotel/Inventories/InventoryChannels'

import RoomsLayout from './ManageHotel/Rooms/RoomsLayout'
import RoomsList from './ManageHotel/Rooms/RoomsList'
import RoomsForm from './ManageHotel/Rooms/RoomsForm'
import GalleryRoomAdd from './ManageHotel/Rooms/Galery/GaleryRoomAdd'
import GalleryRoomList from './ManageHotel/Rooms/Galery/GaleryRoomList'
import InformationLayout from './ManageHotel/Information/InformationLayout'
import GalleryLayout from './ManageHotel/Gallery/GalleryLayout'
import GalleryManageHotelAdd from './ManageHotel/Gallery/HotelGallery/GalleryManageHotelAdd'
import GalleryManageHotelList from './ManageHotel/Gallery/HotelGallery/GalleryManageHotelList'

import ConfigurationLayout from './ManageHotel/Configuration/ConfigurationLayout'

import UpCrossSellingLayout from './ManageHotel/UpCrossSelling/UpCrossSellingLayout'
import UpSelling from './ManageHotel/UpCrossSelling/UpSelling'
import CrossSelling from './ManageHotel/UpCrossSelling/CrossSelling'

import RatesLayout from './ManageHotel/Rates/Layout'
import RatesRatesLayout from './ManageHotel/Rates/Rates/Layout'
import RatesRatesCostLayout from './ManageHotel/Rates/Rates/Cost/Layout'
import RatesRatesCostList from './ManageHotel/Rates/Rates/Cost/List'
import RatesRatesCostForm from './ManageHotel/Rates/Rates/Cost/Form'
import RatesRatesCostFormMerge from './ManageHotel/Rates/Rates/Cost/FormMerge'
import RatesRatesCostHistory from './ManageHotel/Rates/Rates/Cost/History'
import RatesSale from './ManageHotel/Rates/Rates/Sale/Sale'

import BagsLayout from './ManageHotel/Rates/Bags/BagsLayout'
import BagsList from './ManageHotel/Rates/Bags/BagsList'
import BagsForm from './ManageHotel/Rates/Bags/BagsForm'
import InventoriesGeneralLayout from './ManageHotel/Inventories/InventoriesGeneralLayout'

import SupplementLayout from './ManageHotel/Supplements/Layout'
import Supplement from './ManageHotel/Supplements/Associate/Supplement'
import SupplementAmounts from './ManageHotel/Supplements/Associate/SupplementAmounts'
import SupplementCalendary from './ManageHotel/Supplements/Calendary/SupplementCalendary'

import SupplementRate from './ManageHotel/Rates/Rates/Cost/Supplements/SupplementRate'
import HotelReleasedLayout from './ManageHotel/Released/HotelReleasedLayout'
import HotelReleasedList from './ManageHotel/Released/HotelReleasedList'

export default [
    {
        path: 'hotels',
        alias: '',
        component: HotelsLayout,
        redirect: '/hotels/list',
        name: 'Hotels',
        meta: {
            breadcrumb: 'Hoteles'
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: HotelsList,
                name: 'HotelsList',
                meta: {
                    breadcrumb: 'Lista'
                }
            },
            {
                path: 'add',
                alias: '',
                component: HotelsForm,
                name: 'HotelsAdd',
                meta: {
                    breadcrumb: ''
                }
            },
            {
                path: 'edit/:id',
                alias: '',
                component: HotelsForm,
                name: 'HotelsEdit',
                meta: {
                    breadcrumb: 'Editar'
                }
            },
            {
                path: ':hotel_id/manage_hotel',
                alias: '',
                component: ManageHotelLayout,
                redirect: ':hotel_id/manage_hotel//manage_hotel/rates/rates/cost/edit/',
                name: 'ManageHotelLayout',
                meta: {
                    breadcrumb: 'Administrar Hotel'
                },
                children: [

                    {
                        path: 'information',
                        alias: '',
                        component: InformationLayout,
                        name: 'InformationLayout',
                        meta: {
                            breadcrumb: 'Información'
                        }
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
                                path: 'hotelgallery/list',
                                alias: '',
                                component: GalleryManageHotelList,
                                name: 'HotelManageGalleryList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            },
                            {
                                path: 'hotelgallery/add',
                                alias: '',
                                component: GalleryManageHotelAdd,
                                name: 'HotelManageGalleryAdd',
                                meta: {
                                    breadcrumb: 'Agregar'
                                }
                            }
                        ]
                    },
                    {
                        path: 'inventories/general',
                        alias: '',
                        component: InventoriesGeneralLayout,
                        name: '',
                        meta: {
                            breadcrumb: ''
                        },
                        children: [
                            {
                                path: 'inventories',
                                alias: '',
                                component: InventoriesLayout,
                                name: 'InventoriesLayout',
                                meta: {
                                    breadcrumb: 'Inventories'
                                },
                                children: [
                                    {
                                        path: 'free_sale',
                                        alias: '',
                                        component: InventoryLayout,
                                        name: 'InventoryLayoutFreeSale',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    },
                                    {
                                        path: 'allotments',
                                        alias: '',
                                        component: InventoryLayout,
                                        name: 'InventoryLayoutAllotments',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    },
                                    {
                                        path: 'add_inventory_by_date_range',
                                        alias: '',
                                        component: InventoryByDateRange,
                                        name: 'InventoryByDateRange',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    },
                                    {
                                        path: 'blocked_inventory_by_date_range',
                                        alias: '',
                                        component: InventoryByDateRange,
                                        name: 'BlockedInventoryByDateRange',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    },
                                    {
                                        path: 'history',
                                        alias: '',
                                        component: InventoryHistory,
                                        name: 'InventoryHistory',
                                        meta: {
                                            breadcrumb: ''
                                        }
                                    }
                                ]
                            },
                            {
                                path: 'channels/:channel_id',
                                alias: '',
                                component: InventoryChannels,
                                name: 'InventoryChannels',
                                meta: {
                                    breadcrumb: ''
                                }
                            }
                        ]
                    },
                    {
                        path: 'up_cross_selling',
                        alias: '',
                        component: UpCrossSellingLayout,
                        name: 'UpCrossSellingLayout',
                        meta: {
                            breadcrumb: 'Up / Cross Selling'
                        },
                        children: [
                            {
                                path: 'up_selling',
                                alias: '',
                                component: UpSelling,
                                name: 'UpSelling',
                                meta: {
                                    breadcrumb: ''
                                }
                            },
                            {
                                path: 'cross_selling',
                                alias: '',
                                component: CrossSelling,
                                name: 'CrossSelling',
                                meta: {
                                    breadcrumb: ''
                                }
                            }
                        ]
                    },
                    {
                        path: 'configuration',
                        alias: '',
                        component: ConfigurationLayout,
                        name: 'ConfigurationLayout',
                        meta: {
                            breadcrumb: 'Configuración'
                        }
                    },
                    {
                        path: 'rooms',
                        alias: '',
                        component: RoomsLayout,
                        name: 'RoomsLayout',
                        redirect: '/rooms/list',
                        meta: {
                            breadcrumb: ''
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: RoomsList,
                                name: 'RoomsList',
                                meta: {
                                    breadcrumb: 'Lista'
                                }
                            },
                            {
                                path: 'add',
                                alias: '',
                                component: RoomsForm,
                                name: 'RoomsAdd',
                                redirect: 'add/galery/add',
                                meta: {
                                    breadcrumb: ''
                                },
                                children: [
                                    {
                                        path: 'galery/add',
                                        alias: '',
                                        component: GalleryRoomAdd,
                                        name: 'GaleryAdd',
                                        meta: {
                                            breadcrumb: 'Agregar'
                                        }
                                    }
                                ]
                            },
                            {
                                path: 'edit/:room_id',
                                alias: '',
                                component: RoomsForm,
                                name: 'RoomsEdit',
                                meta: {
                                    breadcrumb: 'Editar'
                                },
                                children: [
                                    {
                                        path: 'galery/list',
                                        alias: '',
                                        component: GalleryRoomList,
                                        name: 'GaleryList',
                                        meta: {
                                            breadcrumb: 'Lista'
                                        }
                                    },
                                    {
                                        path: 'galery/add/edit',
                                        alias: '',
                                        component: GalleryRoomAdd,
                                        name: 'GaleryAddEdit',
                                        meta: {
                                            breadcrumb: 'Agregar'
                                        }
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        path: 'rates',
                        alias: '',
                        component: RatesLayout,
                        redirect: ':hotel_id/manage_hotels/rates/rates/cost/list',
                        name: 'RatesLayout',
                        meta: {
                            breadcrumb: 'Tarifas'
                        },
                        children: [
                            {
                                path: 'rates',
                                alias: '',
                                component: RatesRatesLayout,
                                redirect: ':hotel_id/manage_hotels/rates/rates/cost/list',
                                name: 'RatesRatesLayout',
                                meta: {
                                    breadcrumb: 'Tarifas Negociadas / Diarias'
                                },
                                children: [
                                    {
                                        path: 'cost',
                                        alias: '',
                                        component: RatesRatesCostLayout,
                                        redirect: ':hotel_id/manage_hotels/rates/rates/cost/list',
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
                                                    breadcrumb: 'Lista'
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
                                                path: 'merge',
                                                alias: '',
                                                component: RatesRatesCostFormMerge,
                                                name: 'RatesRatesCostFormMerge',
                                                meta: {
                                                    breadcrumb: 'Agregar'
                                                }
                                            },
                                            {
                                                path: 'history/:rate_id',
                                                alias: '',
                                                component: RatesRatesCostHistory,
                                                name: 'RatesRatesCostHistory',
                                                meta: {
                                                    breadcrumb: 'Historial'
                                                }
                                            },
                                            {
                                                path: 'supplements/:rate_id',
                                                alias: '',
                                                component: SupplementRate,
                                                name: 'SupplementRate',
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
                                path: 'bags',
                                alias: '',
                                component: BagsLayout,
                                name: 'BagsLayout',
                                meta: {
                                    breadcrumb: 'Lista'
                                },
                                children: [
                                    {
                                        path: 'bags/list',
                                        alias: '',
                                        component: BagsList,
                                        name: 'BagsList',
                                        meta: {
                                            breadcrumb: 'Lista'
                                        }
                                    },
                                    {
                                        path: 'bags/add',
                                        alias: '',
                                        component: BagsForm,
                                        name: 'BagsFormAdd',
                                        meta: {
                                            breadcrumb: 'Nuevo'
                                        }
                                    },
                                    {
                                        path: 'bags/edit/:bag_id',
                                        alias: '',
                                        component: BagsForm,
                                        name: 'BagsFormEdit',
                                        meta: {
                                            breadcrumb: 'Editar'
                                        }
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        path: 'supplements_hotel',
                        alias: '',
                        component: SupplementLayout,
                        redirect: ':hotel_id/manage_hotel/supplements_hotel/list',
                        name: 'SupplementLayout',
                        meta: {
                            breadcrumb: 'Supplements'
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: Supplement,
                                name: 'SupplementList',
                                meta: {
                                    breadcrumb: 'Supplements'
                                }
                            },
                            {
                                path: 'supplements_hotel/amounts/:supplement_id',
                                alias: '',
                                component: SupplementAmounts,
                                name: 'SupplementAmounts',
                                meta: {
                                    breadcrumb: 'Supplements'
                                }
                            },
                            {
                                path: 'calendary',
                                alias: '',
                                component: SupplementCalendary,
                                name: 'Supplement',
                                meta: {
                                    breadcrumb: 'Supplements'
                                }
                            }
                        ]
                    },
                    {
                        path: 'released',
                        alias: '',
                        component: HotelReleasedLayout,
                        name: 'HotelReleasedLayout',
                        meta: {
                            breadcrumb: 'Liberados'
                        },
                        children: [
                            {
                                path: 'released/list',
                                alias: '',
                                component: HotelReleasedList,
                                name: 'HotelReleasedList',
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
