import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

import Antd from 'ant-design-vue';

import App from './App.vue';
import router from './router';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueGtm from '@gtm-support/vue-gtm';
import { VueQueryPlugin } from '@tanstack/vue-query';

import ABreadcrumb from './components/global/ABreadcrumbRoutes.vue';
import HeaderComponent from './components/negotiations/HeaderComponent.vue';
import ButtonComponent from './quotes/components/ButtonComponent.vue';

// FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import * as solidIcons from '@fortawesome/free-solid-svg-icons';
import * as regularIcons from '@fortawesome/free-regular-svg-icons';

// Casl
import { Can, abilitiesPlugin } from '@casl/vue';
import { ability } from './services/casl/ability';
import { useCaslAbility } from './composables/useCaslAbility';

// Importando todos los íconos para no tener que hacerlo uno por uno..
const solidIconList = Object.keys(solidIcons)
  .filter((key) => key.startsWith('fa')) // solo íconos (excluye icon definitions u otras constantes)
  .map((icon) => solidIcons[icon]);

const regularIconList = Object.keys(regularIcons)
  .filter((key) => key.startsWith('fa'))
  .map((icon) => regularIcons[icon]);

library.add(...solidIconList, ...regularIconList);
// import regular svg icon
import {
  faBookmark as faBookmarkRegular,
  faCalendar as faCalendarRegular,
  faCalendarDays as faCalendarDaysRegular,
  faCircle as faCircleRegular,
  faCircleCheck as faCircleCheckRegular,
  faClock as faClockRegular,
  faClone as faCloneRegular,
  faEdit,
  faEnvelope,
  faFaceSmile,
  faFile,
  faFileLines,
  faMessage as faMessageRegular,
  faSquare as faSquareRegular,
  faSquarePlus,
  faStar as faStartRegular,
  faTrashAlt,
  faUser as faUserRegular,
  faCalendarXmark,
} from '@fortawesome/free-regular-svg-icons';
/* import specific icons */
import {
  faAngleDown,
  faAngleLeft,
  faAngleRight,
  faArrowDown,
  faArrowDownShortWide,
  faArrowDownWideShort,
  faArrowLeft,
  faArrowRight,
  faArrowRightLong,
  faArrowsRotate,
  faArrowTrendDown,
  faArrowTrendUp,
  faArrowUpShortWide,
  faArrowUpWideShort,
  faBabyCarriage,
  faBalanceScale,
  faBan,
  faBars,
  faBed,
  faBookBookmark,
  faBookmark,
  faBoxArchive,
  faBoxes,
  faBuilding,
  faBus,
  faBusinessTime,
  faBusSimple,
  faCalendar,
  faCalendarDays,
  faCar,
  faCheck,
  faCheckDouble,
  faChevronDown,
  faChevronLeft,
  faChevronRight,
  faChevronUp,
  faChild,
  faChildReaching,
  faCircle,
  faCircleCheck,
  faCircleExclamation,
  faCircleInfo,
  faCircleQuestion,
  faCircleUser,
  faCircleXmark,
  faClipboardList,
  faClock,
  faClockRotateLeft,
  faClone,
  faCodeCompare,
  faCog,
  faCube,
  faDollarSign,
  faDonate,
  faDownload,
  faDrumstickBite,
  faEarthAmericas,
  faEllipsis,
  faEllipsisVertical,
  faEnvelopesBulk,
  faExternalLinkAlt,
  faEye,
  faFileExcel,
  faFileImport,
  faFileInvoiceDollar,
  faFileMedical,
  faFileSignature,
  faFileWord,
  faFloppyDisk,
  faFolder,
  faFolderPlus,
  faFutbol,
  faGears,
  faGift,
  faGlobe,
  faGlobeAmericas,
  faHandPointer,
  faHeartbeat,
  faHiking,
  faHotel,
  faHourglass,
  faHourglassStart,
  faHouse,
  faImages,
  faInbox,
  faIndustry,
  faInfoCircle,
  faLink,
  faListAlt,
  faListCheck,
  faListOl,
  faListUl,
  faLocationDot,
  faMagnifyingGlass,
  faMinus,
  faMoneyBill,
  faNoteSticky,
  faPaperPlane,
  faPenToSquare,
  faPercent,
  faPlane,
  faPlaneArrival,
  faPlaneDeparture,
  faPlus,
  faPlusCircle,
  faPollH,
  faPowerOff,
  faQuestionCircle,
  faReceipt,
  faRepeat,
  faRetweet,
  faRightLong,
  faRobot,
  faRocket,
  faRotateLeft,
  faSave,
  faSearch,
  faShareAlt,
  faShieldHalved,
  faShip,
  faSitemap,
  faSliders,
  faSquare,
  faSquareCheck,
  faStar as faStartSolid,
  faStopwatch,
  faStopwatch20,
  faSubway,
  faTableList,
  faTags,
  faTag,
  faTasks,
  faThList,
  faTicket,
  faTrain,
  faTrash,
  faTrashCan,
  faTriangleExclamation,
  faUnlockAlt,
  faUpload,
  faUser,
  faUserGear,
  faUserGraduate,
  faUserGroup,
  faUserLock,
  faUserPen,
  faUsers,
  faUserSecret,
  faUsersGear,
  faUserTie,
  faUserXmark,
  faWandMagicSparkles,
  faWindowClose,
  faX,
  faXmark,
  faCircleNotch,
  faHourglassEnd,
  faRotateRight,
} from '@fortawesome/free-solid-svg-icons';
//PLUGINS
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

import { VueDraggableNext } from 'vue-draggable-next';
import { createI18n } from 'vue-i18n';

import { Buffer } from 'buffer';

window.Buffer = Buffer;

import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
// Opcionalmente: import '@vueup/vue-quill/dist/vue-quill.bubble.css';

const app = createApp(App);

// Enable Vue Devtools in development
if (import.meta.env.DEV) {
  app.config.devtools = true;
  app.config.performance = true;
}

// Vue Query with devtools
app.use(VueQueryPlugin, {
  enableDevtoolsV6Plugin: true,
});

// eslint-disable-next-line vue/multi-word-component-names
app.component('QuillEditor', QuillEditor);
app.component('draggable', VueDraggableNext);
app.component('breadcrumb-component', ABreadcrumb);
app.component('header-component', HeaderComponent);
app.component('button-component', ButtonComponent);

/* add icons to the library */
library.add(
  faTriangleExclamation,
  faCircleExclamation,
  faCircleCheckRegular,
  faTags,
  faTag,
  faUserSecret,
  faUser,
  faBan,
  faBookmark,
  faEdit,
  faFile,
  faTrash,
  faTrashAlt,
  faArrowDownShortWide,
  faSearch,
  faArrowRight,
  faArrowLeft,
  faUsers,
  faUserGroup,
  faSave,
  faCircleInfo,
  faInfoCircle,
  faCircleQuestion,
  faFileImport,
  faMagnifyingGlass,
  faPowerOff,
  faFolderPlus,
  faAngleDown,
  faChevronLeft,
  faEarthAmericas,
  faBars,
  faPlus,
  faMinus,
  faBoxArchive,
  faStopwatch,
  faCalendar,
  faChevronDown,
  faChevronUp,
  faDollarSign,
  faEye,
  faEllipsisVertical,
  faArrowDownWideShort,
  faArrowUpWideShort,
  faStartRegular,
  faStartSolid,
  faPaperPlane,
  faEnvelopesBulk,
  faFileExcel,
  faHourglassStart,
  faClock,
  faRocket,
  faArrowTrendUp,
  faArrowTrendDown,
  faChevronRight,
  faArrowDown,
  faArrowRightLong,
  faUserPen,
  faClone,
  faCube,
  faFileLines,
  faSliders,
  faUserRegular,
  faSquarePlus,
  faHouse,
  faListUl,
  faCalendarRegular,
  faBus,
  faTrashCan,
  faRetweet,
  faWindowClose,
  faListCheck,
  faNoteSticky,
  faCircle,
  faCircleUser,
  faRotateLeft,
  faArrowUpShortWide,
  faEnvelope,
  faArrowsRotate,
  faChildReaching,
  faBabyCarriage,
  faPenToSquare,
  faTableList,
  faCalendarDays,
  faChild,
  faPlusCircle,
  faCircleXmark,
  faCodeCompare,
  faCheck,
  faHotel,
  faTrashCan,
  faFloppyDisk,
  faXmark,
  faSquare,
  faSquareCheck,
  faTriangleExclamation,
  faCog,
  faFileInvoiceDollar,
  faPercent,
  faUpload,
  faDownload,
  faStopwatch20,
  faHandPointer,
  faSquareRegular,
  faTrain,
  faGlobe,
  faImages,
  faTicket,
  faFileSignature,
  faUserTie,
  faUserLock,
  faSitemap,
  faUserGraduate,
  faGears,
  faRobot,
  faMoneyBill,
  faBalanceScale,
  faHourglass,
  faIndustry,
  faExternalLinkAlt,
  faHeartbeat,
  faQuestionCircle,
  faAngleRight,
  faAngleLeft,
  faBoxes,
  faTasks,
  faHiking,
  faCheckDouble,
  faListAlt,
  faBusinessTime,
  faThList,
  faUnlockAlt,
  faLink,
  faFutbol,
  faDrumstickBite,
  faDonate,
  faBed,
  faShareAlt,
  faSubway,
  faFolder,
  faGlobeAmericas,
  faClipboardList,
  faPollH,
  faListOl,
  faCloneRegular,
  faBusSimple,
  faShip,
  faPlane,
  faCircleCheck,
  faUserXmark,
  faClockRotateLeft,
  faEllipsis,
  faCircleCheck,
  faListOl,
  faFileMedical,
  faCalendarDaysRegular,
  faCar,
  faReceipt,
  faRepeat,
  faClockRegular,
  faClockRegular,
  faWandMagicSparkles,
  faX,
  faLocationDot,
  faUserGear,
  faMessageRegular,
  faRightLong,
  faUsersGear,
  faPlaneDeparture,
  faBookBookmark,
  faBookmarkRegular,
  faFileWord,
  faFaceSmile,
  faCircleRegular,
  faPlaneArrival,
  faBuilding,
  faGift,
  faShieldHalved,
  faInbox,
  faCircleNotch, // Sin estado
  faHourglassEnd, // Fin de servicio
  faCalendarXmark, // No show
  faRotateRight // Resetear estado
);

app.component('font-awesome-icon', FontAwesomeIcon);
// end fontawesome

app.component('v-select', vSelect);
app.component('EasyDataTable', Vue3EasyDataTable);
app.component('font-awesome-icon', FontAwesomeIcon);

// Global vars
window.environment = import.meta.env.VITE_APP_ENV;
window.url_s3 = import.meta.env.VITE_APP_URL_S3;
window.url_app = import.meta.env.VITE_APP_URL;
window.url_back_a2 = import.meta.env.VITE_APP_BACKEND_URL;
window.url_auth_cognito = import.meta.env.VITE_APP_AUTH_COGNITO;
window.url_front_a2 = import.meta.env.VITE_APP_FRONTEND_URL;
window.url_back_quote_a3 = import.meta.env.VITE_APP_BACKEND_QUOTE_A3_URL;
window.API_GATEWAY_BACKEND = import.meta.env.VITE_APP_BACKEND;
window.API_NEGOTIATIONS = import.meta.env.VITE_APP_BACKEND_NEG_URL;
window.APINODE = import.meta.env.VITE_APP_EXPRESS_WS_URL;
window.APINODE_ACCOUNTANCY_MS = import.meta.env.VITE_APP_NODE_ACCOUNTANCY_MS;
window.APINODE_ACCOUNTANCY_MS_PUBLIC = import.meta.env.VITE_APP_NODE_ACCOUNTANCY_MS_PUBLIC;
window.APINODE_ACCOUNTANCY_MS_BALANCER = import.meta.env.VITE_APP_NODE_ACCOUNTANCY_MS_BALANCER;
window.AMAZON_SQS = import.meta.env.VITE_APP_AMAZON_SQS_URL;
window.DYNAMO_URL = import.meta.env.VITE_APP_AMAZON_DYNAMO_URL;
window.url_back_ope = import.meta.env.VITE_APP_BACKEND_OPE_URL;
window.url_providers = import.meta.env.VITE_APP_BACKEND_PROVIDERS_URL;
window.TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_LIMATOUR;
window.TOKEN_COGNITO_KEY = import.meta.env.VITE_TOKEN_KEY_COGNITO_LIMATOUR;
window.USER_TYPE = import.meta.env.VITE_USER_TYPE_LIMATOUR;
window.USER_ID = import.meta.env.VITE_USER_KEY_LIMATOUR;
window.USER_CODE_KEY = import.meta.env.VITE_USER_CODE_KEY;
window.USER_EMAIL = import.meta.env.VITE_USER_EMAIL_KEY;
window.USER_NAME = import.meta.env.VITE_USER_NAME_KEY;
window.USER_ROLE_KEY = import.meta.env.VITE_USER_ROLE_KEY;
window.USER_CLIENT_ID = import.meta.env.VITE_USER_CLIENT_ID_KEY;
window.USER_DEPARTMENT_ID = import.meta.env.VITE_USER_DEPARTMENT_ID;
window.USER_DEPARTMENT_NAME = import.meta.env.VITE_USER_DEPARTMENT_NAME;
window.USER_DEPARTMENT_TEAM_ID = import.meta.env.VITE_USER_DEPARTMENT_TEAM_ID;
window.USER_DEPARTMENT_TEAM_NAME = import.meta.env.VITE_USER_DEPARTMENT_TEAM_NAME;
window.USER_DISABLE_RESERVATION = import.meta.env.VITE_USER_DISABLE_RESERVATION_LIMATOUR;
window.DOMAIN = import.meta.env.VITE_DOMAIN;
window.VITE_APP_ENV = import.meta.env.VITE_APP_ENV;
window.VITE_APP_NAME = import.meta.env.VITE_APP_NAME;
window.VITE_WEBSOCKET_AMAZON = import.meta.env.VITE_WEBSOCKET_AMAZON;
// Direct support API (negotiations) - configurable vía .env
window.A3_DIRECT_SUPPORT_API = import.meta.env.VITE_APP_SUPPLIER_POLICIES_SERVICE;

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: {},
});

app
  .use(VueAxios, axios)
  .use(createPinia().use(piniaPluginPersistedstate))
  .use(abilitiesPlugin, ability, {
    useGlobalProperties: true,
  })
  .use(router)
  .use(Antd)
  .use(i18n)
  .use(VueGtm, {
    id: 'GTM-P6TVXX26',
    vueRouter: router,
    enabled: true,
    debug: false,
  })
  .component(Can.name, Can)
  .mount('#app');

const { updatePermissions } = useCaslAbility();
updatePermissions();
