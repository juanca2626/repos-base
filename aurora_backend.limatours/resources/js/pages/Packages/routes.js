import PackagesLayout from "./Layout";
import PackagesList from "./List";
import PackagesForm from "./Form";
import PackageExportRates from "./ExportRates/ExportRate";
import GroupPackageLayout from "./Groups/GroupPackageLayout";
import GroupTagLayout from "./Groups/GroupTagLayout";
import PackageGroups from "./Groups/Group";
import PackageGroupsForm from "./Groups/GroupForm";
import TagsForm from "./Groups/TagsForm";
import ManagePackageLayout from "./ManagePackage/ManagePackageLayout";

import PackageTextsForm from "./ManagePackage/Texts/PackageTextsForm";

import PackageCustomersLayout from "./ManagePackage/Customers/PackageCustomersLayout";
import PackageCustomersList from "./ManagePackage/Customers/PackageCustomersList";

import PackageConfigurationsLayout from "./ManagePackage/Configuration/PackageConfigurationsLayout";

import PackageGalleryLayout from "./ManagePackage/Gallery/PackageGalleryLayout";
import PackageGalleryManageAdd from "./ManagePackage/Gallery/PackageGallery/PackageGalleryManageAdd";
import PackageGalleryManageList from "./ManagePackage/Gallery/PackageGallery/PackageGalleryManageList";

import FixedOutputsLayout from "./ManagePackage/FixedOutputs/FixedOutputsLayout";
import FixedOutputsList from "./ManagePackage/FixedOutputs/FixedOutputsList";

import PackageQuoteLayout from "./ManageQuotes/PackageQuoteLayout";
import PackageCostQuotes from "./ManageQuotes/cost/CostQuotesList";
import PackageSaleQuotes from "./ManageQuotes/sale/SaleQuotes";
import PackageBlockingQuotes from "./ManageQuotes/blocking/BlockingQuotes";

import PackageCostQuotesForm from "./ManageQuotes/cost/CostForm";
import PackageCostQuotesCategories from "./ManageQuotes/cost/QuotesCategoriesCost";
import PackageCostQuoteAddFlight from "./ManageQuotes/cost/QuoteAddFlight";
import PackageCostQuoteAddHotel from "./ManageQuotes/cost/QuoteAddHotel";
import PackageCostQuoteAddService from "./ManageQuotes/cost/QuoteAddService";
import PackageCostQuoteRates from "./ManageQuotes/cost/CostQuotesRates";
import PackageCostQuoteServicesAndHotels from "./ManageQuotes/cost/CostQuotesServicesAndHotels";
import PackageCostQuoteOptional from "./ManageQuotes/cost/CostQuotesOptional";
import Tags from "./Groups/Tags";

import ExtensionsList from "./ManagePackage/Extensions/ExtensionsList";

import PackagePermissionsList from "./ManagePermissions/ListPackagePermissions";
import PackagePermissionsForm from "./ManagePermissions/FormPackagePermissions";

import PackageInclusions from "./ManagePackage/Inclusions/PackageInclusionsLayout";
import PackageHighlights from "./ManagePackage/Highlights/PackageHighlightsLayout";

export default [
    {
        path: "packages",
        alias: "",
        component: PackagesLayout,
        redirect: "/packages/list",
        name: "Packages",
        meta: {
            breadcrumb: "Paquetes",
        },
        children: [
            {
                path: "list",
                alias: "",
                component: PackagesList,
                name: "PackagesList",
                meta: {
                    breadcrumb: "Lista",
                },
            },
            {
                path: "add",
                alias: "",
                component: PackagesForm,
                name: "PackagesAdd",
                meta: {
                    breadcrumb: "Agregar",
                },
            },
            {
                path: "edit/:id",
                alias: "",
                component: PackagesForm,
                name: "PackagesEdit",
                meta: {
                    breadcrumb: "Editar",
                },
            },
            {
                path: ":package_id/manage_package",
                alias: "",
                component: ManagePackageLayout,
                redirect: ":package_id/manage_package",
                name: "ManagePackageLayout",
                meta: {
                    breadcrumb: "Administrar Paquete",
                },
                children: [
                    {
                        path: "package_texts",
                        alias: "",
                        component: PackageTextsForm,
                        name: "PackageTextsForm",
                        redirect: "/package_texts/form",
                        meta: {
                            breadcrumb: "",
                        },
                        children: [
                            {
                                path: "form",
                                alias: "",
                                component: PackageTextsForm,
                                name: "PackageTextsForm",
                                meta: {
                                    breadcrumb: "Textos",
                                },
                            },
                        ],
                    },
                    {
                        path: "package_customers",
                        alias: "",
                        component: PackageCustomersLayout,
                        name: "PackageCustomersLayout",
                        redirect: "/package_customers/list",
                        meta: {
                            breadcrumb: "",
                        },
                        children: [
                            {
                                path: "list",
                                alias: "",
                                component: PackageCustomersList,
                                name: "PackageCustomersList",
                                meta: {
                                    breadcrumb: "Lista",
                                },
                            },
                        ],
                    },
                    {
                        path: "package_configurations",
                        alias: "",
                        component: PackageConfigurationsLayout,
                        name: "PackageConfigurationsLayout",
                        meta: {
                            breadcrumb: "Configuración",
                        },
                    },
                    {
                        path: "package_gallery",
                        alias: "",
                        component: PackageGalleryLayout,
                        name: "PackageGalleryLayout",
                        meta: {
                            breadcrumb: "Galería",
                        },
                        children: [
                            {
                                path: "packagegallery/list",
                                alias: "",
                                component: PackageGalleryManageList,
                                name: "PackageGalleryManageList",
                                meta: {
                                    breadcrumb: "Lista",
                                },
                            },
                            {
                                path: "packagegallery/add",
                                alias: "",
                                component: PackageGalleryManageAdd,
                                name: "PackageGalleryManageAdd",
                                meta: {
                                    breadcrumb: "Agregar",
                                },
                            },
                        ],
                    },
                    {
                        path: "fixed_outputs",
                        alias: "",
                        component: FixedOutputsLayout,
                        name: "FixedOutputsLayout",
                        redirect: "/fixed_outputs/list",
                        meta: {
                            breadcrumb: "",
                        },
                        children: [
                            {
                                path: "list",
                                alias: "",
                                component: FixedOutputsList,
                                name: "FixedOutputsList",
                                meta: {
                                    breadcrumb: "Lista",
                                },
                            },
                        ],
                    },
                    {
                        path: "extensions",
                        alias: "",
                        component: ExtensionsList,
                        name: "ExtensionsList",
                        meta: {
                            breadcrumb: "Extensions",
                        },
                    },
                    {
                        path: "package_inclusion",
                        alias: "",
                        component: PackageInclusions,
                        name: "PackageInclusions",
                        meta: {
                            breadcrumb: "Inclusiones",
                        },
                    },
                    {
                        path: "package_highlights",
                        alias: "",
                        component: PackageHighlights,
                        name: "PackageHighlights",
                        meta: {
                            breadcrumb: "Highlights",
                        },
                    },
                ],
            },
            {
                path: ":package_id/quotes",
                alias: "",
                component: PackageQuoteLayout,
                redirect: "cost",
                name: "PackageQuoteLayout",
                meta: {
                    breadcrumb: "Cotizador",
                },
                children: [
                    {
                        path: "cost",
                        alias: "",
                        component: PackageCostQuotes,
                        name: "PackageCostQuotes",
                        meta: {
                            breadcrumb: "Costo",
                        },
                    },
                    {
                        path: "sale",
                        alias: "",
                        component: PackageSaleQuotes,
                        name: "PackageSaleQuotes",
                        meta: {
                            breadcrumb: "Venta",
                        },
                    },
                    {
                        path: "blocking",
                        alias: "",
                        component: PackageBlockingQuotes,
                        name: "PackageBlockingQuotes",
                        meta: {
                            breadcrumb: "Bloqueos",
                        },
                    },
                    {
                        path: "cost/add",
                        alias: "",
                        component: PackageCostQuotesForm,
                        name: "PackageCostQuotesForm",
                        meta: {
                            breadcrumb: "Costo",
                        },
                    },
                    {
                        path: "cost/edit/:package_plan_rate_id",
                        alias: "",
                        component: PackageCostQuotesForm,
                        name: "PackageCostQuotesForm",
                        meta: {
                            breadcrumb: "Costo",
                        },
                    },
                    {
                        path: "cost/:package_plan_rate_id/addFlights/:category_id",
                        alias: "",
                        component: PackageCostQuoteAddFlight,
                        name: "PackageCostQuoteAddFlight",
                        meta: {
                            breadcrumb: "Agregar Vuelos",
                        },
                    },
                    {
                        path: "cost/:package_plan_rate_id/addHotels/:category_id",
                        alias: "",
                        component: PackageCostQuoteAddHotel,
                        name: "PackageCostQuoteAddHotel",
                        meta: {
                            breadcrumb: "Agregar Hoteles",
                        },
                    },
                    {
                        path: "cost/:package_plan_rate_id/addServices/:category_id",
                        alias: "",
                        component: PackageCostQuoteAddService,
                        name: "PackageCostQuoteAddService",
                        meta: {
                            breadcrumb: "Agregar Servicios",
                        },
                    },
                    {
                        path: "cost/:package_plan_rate_id/category/:category_id",
                        alias: "",
                        component: PackageCostQuotesCategories,
                        name: "PackageCostQuotesCategories",
                        meta: {
                            breadcrumb: "Categorias",
                        },
                        children: [
                            {
                                path: "cost/:package_plan_rate_id/category/:category_id/services",
                                alias: "",
                                component: PackageCostQuoteServicesAndHotels,
                                name: "PackageCostQuoteServicesAndHotels",
                                meta: {
                                    breadcrumb: "Servicios & Hoteles",
                                },
                            },
                            {
                                path: "cost/:package_plan_rate_id/category/:category_id/rates",
                                alias: "",
                                component: PackageCostQuoteRates,
                                name: "PackageCostQuoteRates",
                                meta: {
                                    breadcrumb: "Tarifas",
                                },
                            },
                            {
                                path: "cost/:package_plan_rate_id/category/:category_id/optional",
                                alias: "",
                                component: PackageCostQuoteOptional,
                                name: "PackageCostQuoteOptional",
                                meta: {
                                    breadcrumb: "Opcionales",
                                },
                            },
                        ],
                    },
                ],
            },
            {
                path: "export",
                alias: "",
                component: PackageExportRates,
                name: "PackageExportRates",
                meta: {
                    breadcrumb: "Exportar Tarifas",
                },
            },

            {
                path: "groups",
                alias: "",
                component: GroupPackageLayout,
                redirect: "groups/list",
                name: "GroupPackageLayout",
                meta: {
                    breadcrumb: "Intereses",
                },
                children: [
                    {
                        path: "list",
                        alias: "",
                        component: PackageGroups,
                        name: "PackageGroups",
                        meta: {
                            breadcrumb: "Lista",
                        },
                    },
                    {
                        path: "add",
                        alias: "",
                        component: PackageGroupsForm,
                        name: "PackageGroupsFormCreate",
                        meta: {
                            breadcrumb: "Nuevo",
                        },
                    },
                    {
                        path: "edit/:group_id",
                        alias: "",
                        component: PackageGroupsForm,
                        name: "PackageGroupsFormUpdate",
                        meta: {
                            breadcrumb: "Editar",
                        },
                    },
                    {
                        path: "tags/:group_id",
                        alias: "",
                        component: GroupTagLayout,
                        // redirect: 'groups/tag/list',
                        name: "GroupTagLayout",
                        meta: {
                            breadcrumb: "Categorias",
                        },
                        children: [
                            {
                                path: "tags",
                                alias: "",
                                component: Tags,
                                name: "Tags",
                                meta: {
                                    // breadcrumb: 'Lista'
                                },
                            },
                            {
                                path: "add",
                                alias: "",
                                component: TagsForm,
                                name: "TagsFormAdd",
                                meta: {
                                    breadcrumb: "Nuevo",
                                },
                            },
                            {
                                path: "edit/:tag_id",
                                alias: "",
                                component: TagsForm,
                                name: "TagsFormEdit",
                                meta: {
                                    breadcrumb: "Editar",
                                },
                            },
                        ],
                    },
                ],
            },
            {
                path: "permissions",
                alias: "",
                component: PackagePermissionsList,
                name: "PackagePermissionsList",
                meta: {
                    breadcrumb: "Permisos",
                },
            },
            {
                path: "permissions/add",
                alias: "",
                component: PackagePermissionsForm,
                name: "PackagePermissionsForm",
                meta: {
                    breadcrumb: "Agregar permisos",
                },
            },
        ],
    },
];
