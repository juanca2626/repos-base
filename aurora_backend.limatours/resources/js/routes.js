import Login from './pages/Login/Login.vue'
import Layout from './components/Layout.vue'
import Dashboard from './pages/Dashboard.vue'
import e404 from './pages/404/404.vue'
import ForgotPassword from './pages/ResetPassword/ForgotPassword.vue'
import ResetPassword from './pages/ResetPassword/ResetPassword.vue'

import { requireAuth } from './auth'

import usersRoute from './pages/Users/routes'
import rolesRoute from './pages/Roles/routes'
import permissionsRoute from './pages/Permissions/routes'
import countriesRoute from './pages/Countries/routes'
import statesRoute from './pages/States/routes'
import citiesRoute from './pages/Cities/routes'
import districtsRoute from './pages/Districts/routes'
import currenciesRoute from './pages/Currencies/routes'
import languagesRoute from './pages/Languages/routes'
import hotelcategoriesRoute from './pages/HotelCategories/routes'
import chainsRoute from './pages/Chains/routes'
import typesclassRoute from './pages/TypesClass/routes'
import mealsRoute from './pages/Meals/routes'
import amenitiesRoute from './pages/Amenities/routes'
import channelsRoute from './pages/Channels/routes'
import hotelTypesRoute from './pages/HotelTypes/routes'
import roomTypesRoute from './pages/RoomTypes/routes'
import translationsRoute from './pages/Translations/routes'
import galeriesRoute from './pages/Galeries/routes'
import zonesRoute from './pages/Zones/routes'
import packagesRoute from './pages/Packages/routes'
import servicesRoute from './pages/Services/routes'
import profileRoute from './pages/Profile/routes'
import hotelsRoute from './pages/Hotels/routes'
import trainsRoute from './pages/Trains/routes'
import suplementsRoute from './pages/Suplements/routes'
import classificationsRoute from './pages/Classifications/routes'
import inclusionsRoute from './pages/Inclusions/routes'
import experiencesRoute from './pages/Experiences/routes'
import requirementsRoute from './pages/Requirements/routes'
import unitsRoute from './pages/Units/routes'
import unitDurationsRoute from './pages/UnitDurations/routes'
import restrictionsRoute from './pages/Restrictions/routes'
import clientsRoute from './pages/Clients/routes'
import clientsMailingRoute from './pages/ClientsMailing/routes'
import typeOperabilityRoute from './pages/TypeOperability/routes'
import serviceCategoryRoute from './pages/ServiceCategories/routes'
import marketsRoute from './pages/Markets/routes'
import serviceTypeRoute from './pages/ServiceTypes/routes'
import masiRoute from './pages/Masi/routes'
import virtualClassRoute from './pages/VirtualClass/routes'
import ServiceCancellationPoliciesRoute from './pages/ServiceCancellationPolicies/routes'
import SuppliersRoute from './pages/Suppliers/routes'
import PhotosRoute from './pages/Photos/routes'
import TicketsRoute from './pages/Tickets/routes'
import AuditPackageRoute from './pages/Auditing/Package/routes'
import TagServicesRoute from './pages/TagServices/routes'
import AuditClientRoute from './pages/Auditing/Client/routes'
import AuditServiceRoute from './pages/Auditing/Service/routes'
import InfoImportantServiceRoute from './pages/InfoImportantService/routes'
import PhysicalIntensitiesRoute from './pages/PhysicalIntensities/routes'
import InstructionsRoute from './pages/Instructions/routes'
import OrdersRoute from './pages/OrderRate/routes'
import FrequentQuestionsRoute from './pages/FrequentQuestions/routes'
import QuestionCategoriesRoute from './pages/QuestionCategories/routes'
import SupplementServices from './pages/SupplementServices/routes'
import BookingsMain from './pages/Bookings/routes'
import PackageHighLights from './pages/PackageHighLights/routes'
import PackageCancellationPolicies from './pages/PackageCancellationPolicies/routes'
import MasterServices from './pages/MasterServices/routes'
import ConfigMarkupsRoute from './pages/ConfigMarkups/routes'
import EcommerceRoutes from './pages/Ecommerce/routes'
import SuscriptionHyperguest from './pages/Integrations/routes'
import PositionRoutes from './pages/Positions/routes'
import DepartmentRoutes from './pages/Departments/routes'
import ReportHyperguest from './pages/ReportHyperguest/routes'
import BusinessRegionRoute from './pages/BusinessRegion/routes'
import SupportDeskRedirect from './pages/SupportDeskRedirect.vue'

let secureRoutes = [
    {
        path: 'dashboard',
        alias: '',
        component: Dashboard,
        name: 'Dashboard',
        meta: {
            breadcrumb: 'Dashboard'
        }
    },
    {
        path: '/supportdesk',
        name: 'SupportDeskRedirect',
        component: SupportDeskRedirect,
        meta: { breadcrumb: 'Mesa de Ayuda' }
    }
]

secureRoutes = secureRoutes.concat(
    usersRoute,
    rolesRoute,
    permissionsRoute,
    countriesRoute,
    statesRoute,
    citiesRoute,
    districtsRoute,
    currenciesRoute,
    languagesRoute,
    hotelcategoriesRoute,
    chainsRoute,
    typesclassRoute,
    mealsRoute,
    amenitiesRoute,
    channelsRoute,
    hotelTypesRoute,
    roomTypesRoute,
    translationsRoute,
    galeriesRoute,
    zonesRoute,
    packagesRoute,
    servicesRoute,
    profileRoute,
    hotelsRoute,
    trainsRoute,
    suplementsRoute,
    classificationsRoute,
    inclusionsRoute,
    experiencesRoute,
    requirementsRoute,
    unitsRoute,
    unitDurationsRoute,
    restrictionsRoute,
    clientsRoute,
    typeOperabilityRoute,
    serviceCategoryRoute,
    clientsRoute,
    clientsMailingRoute,
    marketsRoute,
    serviceTypeRoute,
    masiRoute,
    virtualClassRoute,
    ServiceCancellationPoliciesRoute,
    SuppliersRoute,
    PhotosRoute,
    TicketsRoute,
    AuditClientRoute,
    AuditPackageRoute,
    AuditServiceRoute,
    TagServicesRoute,
    InfoImportantServiceRoute,
    PhysicalIntensitiesRoute,
    InstructionsRoute,
    OrdersRoute,
    InstructionsRoute,
    FrequentQuestionsRoute,
    QuestionCategoriesRoute,
    SupplementServices,
    BookingsMain,
    PackageHighLights,
    PackageCancellationPolicies,
    MasterServices,
    ConfigMarkupsRoute,
    EcommerceRoutes,
    SuscriptionHyperguest,
    PositionRoutes,
    DepartmentRoutes,
    ReportHyperguest,
    BusinessRegionRoute
)

let defaultRoutes = [
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    {
        path: '/',
        component: Layout,
        beforeEnter: requireAuth,
        meta: {
            breadcrumb: 'Home'
        },
        children: secureRoutes
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: ForgotPassword,
        meta: {
            auth: false
        }
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password-form',
        component: ResetPassword,
        meta: {
            auth: false
        }
    },
    { path: '*', component: e404 }
]

export default defaultRoutes
