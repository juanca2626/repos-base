import StatesLayout from './Layout';
import StatesList from './List';
import StatesForm from './Form';
import ManageClientLayout from '../Clients/ManageClient/ManageClientLayout';
import MarkupLayout from '../Clients/ManageClient/Markup/MarkupLayout';
import SellerLayout from '../Clients/ManageClient/Seller/SellerLayout';
import HotelLayout from '../Clients/ManageClient/Hotel/HotelLayout';
import Hotel from '../Clients/ManageClient/Hotel/Hotel';
import ServiceClientLayout
    from '../Clients/ManageClient/Service/ServiceClientLayout';
import Service from '../Clients/ManageClient/Service/Service';
import ServiceOffer
    from '../Clients/ManageClient/Service/ServiceOffer/ServiceOffer';
import ServiceRated
    from '../Clients/ManageClient/Service/ServiceRated/ServiceRated';
import PackageClientLayout
    from '../Clients/ManageClient/Package/PackageClientLayout';
import PackageOffer
    from '../Clients/ManageClient/Package/PackageOffer/PackageOffer';
import PackageRated
    from '../Clients/ManageClient/Package/PackageRated/PackageRated';
import ExecutiveLayout from '../Clients/ManageClient/Executive/ExecutiveLayout';
import Executive from '../Clients/ManageClient/Executive/Executive';

//Administrar
import ManageStateLayout from '../States/Manage/ManageStateLayout';
import LayoutStateGallery from './Manage/Gallery/StateGalleryLayout';
import ListStateGallery from './Manage/Gallery/ListGallery';
import FormStateGallery from './Manage/Gallery/FormGallery';


export default [
    {
        path: 'states',
        alias: '',
        component: StatesLayout,
        redirect: '/states/list',
        name: 'States',
        meta: {
            breadcrumb: 'Estados',
        },
        children: [
            {
                path: 'list',
                alias: '',
                component: StatesList,
                name: 'StatesList',
                meta: {
                    breadcrumb: 'Lista',
                },
            },
            {
                path: 'add',
                alias: '',
                component: StatesForm,
                name: 'StatesAdd',
                meta: {
                    breadcrumb: 'Agregar',
                },
            },
            {
                path: 'edit/:id',
                alias: '',
                component: StatesForm,
                name: 'StatesEdit',
                meta: {
                    breadcrumb: 'Editar',
                },
            },
            {
                path: ':state_id/manage_state',
                alias: '',
                component: ManageStateLayout,
                redirect: ':state_id/manage_state/gallery/list',
                name: 'ManageClientLayout',
                meta: {
                    breadcrumb: 'Administrar Estados',
                },
                children: [
                    {
                        path: 'gallery',
                        alias: '',
                        component: LayoutStateGallery,
                        name: 'LayoutStateGallery',
                        meta: {
                            breadcrumb: 'Galería'
                        },
                        children: [
                            {
                                path: 'gallery/list',
                                alias: '',
                                component: ListStateGallery,
                                name: 'ListStateGallery',
                                meta: {
                                    breadcrumb: ''
                                }
                            },
                            {
                                path: 'gallery/add',
                                alias: '',
                                component: FormStateGallery,
                                name: 'FormStateGalleryAdd',
                                meta: {
                                    breadcrumb: 'Agregar'
                                }
                            }
                        ]
                    },
                ],
            },
        ],
    }];
