import ClientsLayout from './Layout'
import ClientsList from './List'
import ClientsForm from './Form'
import ManageClientLayout from './ManageClient/ManageClientLayout'
import MarkupLayout from './ManageClient/Markup/MarkupLayout'
import SellerLayout from './ManageClient/Seller/SellerLayout'

import HotelLayout from './ManageClient/Hotel/HotelLayout'
import Hotel from './ManageClient/Hotel/Hotel'

import ServiceClientLayout from './ManageClient/Service/ServiceClientLayout'
import Service from './ManageClient/Service/Service'

import ExecutiveLayout from './ManageClient/Executive/ExecutiveLayout'
import Executive from './ManageClient/Executive/Executive'
import ServiceOffer from './ManageClient/Service/ServiceOffer/ServiceOffer'
import ServiceRated from './ManageClient/Service/ServiceRated/ServiceRated'

import PackageClientLayout from './ManageClient/Package/PackageClientLayout'
import PackageOffer from './ManageClient/Package/PackageOffer/PackageOffer'
import PackageRated from './ManageClient/Package/PackageRated/PackageRated'

import ServiceConfig from './ManageClient/Service/ServiceConfiguration/ServiceConfig'
import PackageConfig from './ManageClient/Package/PackageConfiguration/PackageConfig'

import ClientEcommerceLayout from './ManageClient/Ecommerce/ClientEcommerceLayout'

import RemindersLayout from './ManageClient/Reminders/RemindersLayout'

import Questions from './ManageClient/Ecommerce/QuestionEcommerce/Questions'
import ManageEcommerce from './ManageClient/Ecommerce/ManageEcommerce/ManageEcommerce'

import PrivacyPoliciesLayoutEcommerce from './ManageClient/Ecommerce/PrivacyPoliciesEcommerce/PrivacyPoliciesLayout'
import PrivacyPoliciesListEcommerce from './ManageClient/Ecommerce/PrivacyPoliciesEcommerce/PrivacyPoliciesList'
import PrivacyPoliciesFormEcommerce from './ManageClient/Ecommerce/PrivacyPoliciesEcommerce/PrivacyPoliciesForm'

import PurchaseTermsLayoutEcommerce from './ManageClient/Ecommerce/PurchaseTermsEcommerce/PurchaseTermsLayout'
import PurchaseTermsListEcommerce from './ManageClient/Ecommerce/PurchaseTermsEcommerce/PurchaseTermsList'
import PurchaseTermsFormEcommerce from './ManageClient/Ecommerce/PurchaseTermsEcommerce/PurchaseTermsForm'

import ContactLayout from './ManageClient/Contact/ContactLayout'

export default [
    {
        path: 'clients',
        alias: '',
        component: ClientsLayout,
        redirect: '/clients/list',
        name: 'Clients',
        meta: {
            breadcrumb: 'Clientes',
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: ClientsList,
                name: 'ClientsList',
                meta: {
                    breadcrumb: 'Lista',
                },
            },
            {
                path: 'add',
                alias: '',
                component: ClientsForm,
                name: 'ClientsAdd',
                meta: {
                    breadcrumb: '',
                },
            },
            {
                path: 'edit/:id',
                alias: '',
                component: ClientsForm,
                name: 'ClientsEdit',
                meta: {
                    breadcrumb: 'Editar',
                },
            },
            {
                path: ':client_id/manage_client/regions/:region_id/',
                alias: '',
                component: ManageClientLayout,
                redirect: ':client_id/manage_client/regions/:region_id/markups',
           
                name: 'ManageClientLayout',
                meta: {
                    breadcrumb: 'Administrar Cliente',
                },
                children: [
                    {
                        path: 'markups',
                        alias: '',
                        component: MarkupLayout,
                        name: 'MarkupLayout',
                        meta: {
                            breadcrumb: 'markups',
                        },
                    }, 
                    {
                        path: 'hotels',
                        alias: '',
                        component: HotelLayout,
                        name: 'HotelLayout',
                        meta: {
                            breadcrumb: 'Hotel',
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: Hotel,
                                name: 'Hotel',
                                meta: {
                                    breadcrumb: 'list',
                                },
                            },
                        ],
                    },
                    {
                        path: 'services',
                        alias: '',
                        component: ServiceClientLayout,
                        name: 'ServiceClientLayout',
                        meta: {
                            breadcrumb: 'Servicios',
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: Service,
                                name: 'Service',
                                meta: {
                                    breadcrumb: 'Administración',
                                },
                            },
                            {
                                path: 'offer',
                                alias: '',
                                component: ServiceOffer,
                                name: 'ServiceOffer',
                                meta: {
                                    breadcrumb: 'Ofertas',
                                },
                            },
                            {
                                path: 'rated',
                                alias: '',
                                component: ServiceRated,
                                name: 'ServiceRated',
                                meta: {
                                    breadcrumb: 'Valoración',
                                },
                            },
                            {
                                path: 'configuration',
                                alias: '',
                                component: ServiceConfig,
                                name: 'ServiceConfig',
                                meta: {
                                    breadcrumb: 'Configuración',
                                },
                            },
                        ],
                    },
                    {
                        path: 'packages',
                        alias: '',
                        component: PackageClientLayout,
                        name: 'PackageClientLayout',
                        meta: {
                            breadcrumb: 'Paquetes',
                        },
                        children: [
                            {
                                path: 'offer',
                                alias: '',
                                component: PackageOffer,
                                name: 'PackageOffer',
                                meta: {
                                    breadcrumb: 'Ofertas',
                                },
                            },
                            {
                                path: 'rated',
                                alias: '',
                                component: PackageRated,
                                name: 'PackageRated',
                                meta: {
                                    breadcrumb: 'Valoración',
                                },
                            },
                            {
                                path: 'configuration',
                                alias: '',
                                component: PackageConfig,
                                name: 'PackageConfig',
                                meta: {
                                    breadcrumb: 'Configuración',
                                },
                            },
                        ],
                    },
                    {
                        path: 'executives',
                        alias: '',
                        component: ExecutiveLayout,
                        name: 'ExecutiveLayout',
                        meta: {
                            breadcrumb: 'Executive',
                        },
                        children: [
                            {
                                path: 'list',
                                alias: '',
                                component: Executive,
                                name: 'Executive',
                                meta: {
                                    breadcrumb: 'list',
                                },
                            },
                        ],
                    },
                    {
                        path: 'ecommerce',
                        alias: '',
                        component: ClientEcommerceLayout,
                        name: 'ClientEcommerceLayout',
                        meta: {
                            breadcrumb: 'Ecommerce',
                        },
                        children: [
                            {
                                path: 'management',
                                alias: '',
                                component: ManageEcommerce,
                                name: 'ManageEcommerce',
                                meta: {
                                    breadcrumb: 'Administración',
                                },
                            },
                            {
                                path: 'questions',
                                alias: '',
                                component: Questions,
                                name: 'Questions',
                                meta: {
                                    breadcrumb: 'Preguntas frecuentes',
                                },
                            },
                            {
                                path: 'privacy_policies',
                                alias: '',
                                component: PrivacyPoliciesLayoutEcommerce,
                                redirect: ':client_id/manage_client/ecommerce/privacy_policies/list',
                                name: 'PrivacyPoliciesLayoutEcommerce',
                                meta: {
                                    breadcrumb: 'Politicas de privacidad',
                                },
                                children: [
                                    {
                                        path: 'list',
                                        alias: '',
                                        component: PrivacyPoliciesListEcommerce,
                                        name: 'PrivacyPoliciesListEcommerce',
                                        meta: {
                                            breadcrumb: 'Lista'
                                        }
                                    },
                                    {
                                        path: 'add',
                                        alias: '',
                                        component: PrivacyPoliciesFormEcommerce,
                                        name: 'PrivacyPoliciesAddEcommerce',
                                        meta: {
                                            breadcrumb: 'Nuevo'
                                        }
                                    },
                                    {
                                        path: 'edit/:id',
                                        alias: '',
                                        component: PrivacyPoliciesFormEcommerce,
                                        name: 'PrivacyPoliciesEditEcommerce',
                                        meta: {
                                            breadcrumb: 'Editar'
                                        }
                                    },

                                ]
                            },
                            {
                                path: 'purchase_terms',
                                alias: '',
                                component: PurchaseTermsLayoutEcommerce,
                                redirect: ':client_id/manage_client/ecommerce/purchase_terms/list',
                                name: 'PurchaseTermsLayoutEcommerce',
                                meta: {
                                    breadcrumb: 'Términos y condiciones',
                                },
                                children: [
                                    {
                                        path: 'list',
                                        alias: '',
                                        component: PurchaseTermsListEcommerce,
                                        name: 'PurchaseTermsListEcommerce',
                                        meta: {
                                            breadcrumb: 'Lista'
                                        }
                                    },
                                    {
                                        path: 'add',
                                        alias: '',
                                        component: PurchaseTermsFormEcommerce,
                                        name: 'PurchaseTermsAddEcommerce',
                                        meta: {
                                            breadcrumb: 'Nuevo'
                                        }
                                    },
                                    {
                                        path: 'edit/:id',
                                        alias: '',
                                        component: PurchaseTermsFormEcommerce,
                                        name: 'PurchaseTermsEditEcommerce',
                                        meta: {
                                            breadcrumb: 'Editar'
                                        }
                                    },

                                ]
                            },

                        ],
                    }, 
                ],
            },
            {
                path: ':client_id/manage_client/sellers',
                alias: '',
                component: ManageClientLayout,                 
                name: 'SellerLayout',
                meta: {
                    breadcrumb: 'Seller',
                },
                children: [ 
                    {
                        path: 'sellers',
                        alias: '',
                        component: SellerLayout,
                        name: 'SellerLayout',
                        meta: {
                            breadcrumb: 'Seller',
                        },
                    },
                ]                
            }, 
            
            {
                path: ':client_id/manage_client/contacts',
                alias: '',
                component: ManageClientLayout,                 
                name: 'ContactLayout',
                meta: {
                    breadcrumb: 'Contact',
                },
                children: [ 
                    {
                        path: 'contacts',
                        alias: '',
                        component: ContactLayout,
                        name: 'ContactLayout',
                        meta: {
                            breadcrumb: 'Contact',
                        },
                    },
                ]                
            },
            
            {
                path: ':client_id/manage_client/reminders',
                alias: '',
                component: ManageClientLayout,                 
                name: 'RemindersLayout',
                meta: {
                    breadcrumb: 'Seller',
                },
                children: [ 
                    {
                        path: 'reminders',
                        alias: '',
                        component: RemindersLayout,
                        name: 'RemindersLayout',
                        meta: {
                            breadcrumb: 'Reminders',
                        },
                    },
                ]                
            },

        ],
    }]
