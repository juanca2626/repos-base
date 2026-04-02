import DashboardLayout from '@views/backend/BackendDashboardLayout.vue';
import auth from '@/middleware/auth.js';
import { getSupplierRouteName } from '@/modules/negotiations/supplier/config/supplier-route-config';
import { ResourceActionTypeEnum } from '@/modules/negotiations/supplier/enums/resource-action-type.enum';
import { SupplierSubClassificationsEnum } from '@/utils/supplierSubClassifications.enum';
// import checkPermission from '@/middleware/CheckPermission';
import { ModulePermissionEnum } from '@/enums/module-permission.enum';
import { PermissionActionEnum } from '@/enums/permission-action.enum';
import { RouteNameEnum } from '@/enums/route-name.enum';
import { SupplierListRouteNameEnum } from '@/modules/negotiations/suppliers/enums/supplier-list-route-name.enum';

const ROUTE_NAME = 'negotiations';

export default {
  path: `/${ROUTE_NAME}`,
  name: ROUTE_NAME,
  component: DashboardLayout,
  // redirect:  `/${ROUTE_NAME}/dashboard`,
  meta: {
    middleware: auth, // Validaciones de sesión..
    breadcrumb: 'Negociaciones',
  },
  children: [
    {
      path: 'accounting-management',
      name: 'accounting-management',
      meta: {
        breadcrumb: 'Contabilidad',
      },
      children: [
        {
          path: 'tax',
          name: RouteNameEnum.TAX,
          component: () =>
            import(
              '@/modules/negotiations/accounting-management/tax-settings/layout/TaxSettingsLayout.vue'
            ),
          meta: {
            breadcrumb: 'Configurador contable',
          },
          children: [
            {
              path: 'general',
              name: RouteNameEnum.TAX_GENERAL,
              component: () =>
                import(
                  '@/modules/negotiations/accounting-management/tax-settings/general/pages/GeneralTax.vue'
                ),
              // beforeEnter: checkPermission,
              meta: {
                breadcrumb: 'Configuración de IGV',
                permission: ModulePermissionEnum.TAX_GENERAL,
                action: PermissionActionEnum.READ,
              },
            },
            {
              path: 'suppliers',
              name: RouteNameEnum.TAX_SUPPLIERS_LAYOUT,
              component: () =>
                import(
                  '@/modules/negotiations/accounting-management/tax-settings/suppliers/layout/SupplierTaxLayout.vue'
                ),
              meta: {
                breadcrumb: 'Proveedor',
              },
              children: [
                {
                  path: 'configuration',
                  name: RouteNameEnum.TAX_SUPPLIERS,
                  component: () =>
                    import(
                      '@/modules/negotiations/accounting-management/tax-settings/suppliers/pages/SuppliersTax.vue'
                    ),
                  // beforeEnter: checkPermission,
                  meta: {
                    breadcrumb: 'IGV por tipo de proveedor',
                    permission: ModulePermissionEnum.TAX_SUPPLIER_TYPE,
                    action: PermissionActionEnum.READ,
                  },
                },
                {
                  path: 'configuration/:id/assignments',
                  name: RouteNameEnum.TAX_SUPPLIERS_ASSIGNMENTS,
                  component: () =>
                    import(
                      '@/modules/negotiations/accounting-management/tax-settings/suppliers/pages/SuppliersTaxAssignments.vue'
                    ),
                  // beforeEnter: checkPermission,
                  meta: {
                    breadcrumb: 'Asignación de IGV',
                    permission: ModulePermissionEnum.TAX_SUPPLIER_TYPE,
                    action: PermissionActionEnum.COMPLETE_ASSIGNMENT,
                  },
                },
              ],
            },
          ],
        },
        {
          path: 'financial-expenses',
          name: 'financialExpenses',
          component: () =>
            import(
              '@/modules/negotiations/accounting-management/financial-expenses/layout/FinancialExpensesLayout.vue'
            ),
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Gastos Financieros',
            permission: ModulePermissionEnum.FINANCIAL_EXPENSES,
            action: PermissionActionEnum.READ,
          },
          children: [
            {
              path: 'general',
              name: 'financialExpensesGeneral',
              component: () =>
                import(
                  '@/modules/negotiations/accounting-management/financial-expenses/pages/FinancialExpensesGeneral.vue'
                ),
              meta: {
                breadcrumb: 'Listado de gastos financieros',
              },
            },
          ],
        },
        {
          path: 'exchange-rates',
          name: 'exchangeRates',
          component: () =>
            import(
              '@/modules/negotiations/accounting-management/exchange-rates/layout/ExchangeRatesLayout.vue'
            ),
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Tipo de cambio estimado',
            permission: ModulePermissionEnum.EXCHANGE_RATES,
            action: PermissionActionEnum.READ,
          },
          children: [
            {
              path: 'general',
              name: 'exchangeRatesGeneral',
              component: () =>
                import(
                  '@/modules/negotiations/accounting-management/exchange-rates/pages/ExchangeRatesGeneral.vue'
                ),
              meta: {
                breadcrumb: 'Tipo de cambio estimado',
              },
            },
          ],
        },
        {
          path: 'cost-sale-accounts',
          name: 'costSaleAccounts',
          component: () =>
            import(
              '@/modules/negotiations/accounting-management/cost-sale-accounts/layout/CostSaleAccountsLayout.vue'
            ),
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Cuenta costos y ventas por clasificación',
            permission: ModulePermissionEnum.COST_SALE_ACCOUNTS,
            action: PermissionActionEnum.READ,
          },
          children: [
            {
              path: 'general',
              name: 'costSaleAccountsGeneral',
              component: () =>
                import(
                  '@/modules/negotiations/accounting-management/cost-sale-accounts/pages/CostSaleAccountsGeneral.vue'
                ),
              meta: {
                breadcrumb: 'Cuenta costos y ventas',
              },
            },
          ],
        },
      ],
    },
    {
      path: 'suppliers-old',
      name: 'suppliers-old',
      component: () => import('@/modules/negotiations/supplier/layout/SupplierLayout.vue'),
      meta: {
        breadcrumb: 'Proveedores',
        layout: 'SupplierLayout', // Layout general
      },
      children: [
        {
          path: 'land',
          name: 'suppliersLand',
          meta: {
            breadcrumb: 'Terrestre',
            title: 'Transporte',
            supplierSubClassification: SupplierSubClassificationsEnum.TOURIST_TRANSPORT,
          },
          redirect: { name: 'supplierTouristTransportList' },
          children: [
            {
              path: 'tourist-transports',
              name: 'suppliersTouristTransports',
              meta: {
                breadcrumb: 'Transportes turísticos',
              },
              redirect: { name: 'supplierTouristTransportList' },
              children: [
                {
                  path: 'list',
                  name: getSupplierRouteName(
                    SupplierSubClassificationsEnum.TOURIST_TRANSPORT,
                    ResourceActionTypeEnum.LIST
                  ),
                  component: () =>
                    import(
                      '@/modules/negotiations/supplier/land/tourist-transport/pages/TouristTransportListPage.vue'
                    ),
                  meta: {
                    breadcrumb: 'Listado',
                  },
                },
                {
                  path: 'register',
                  name: getSupplierRouteName(
                    SupplierSubClassificationsEnum.TOURIST_TRANSPORT,
                    ResourceActionTypeEnum.CREATE
                  ),
                  component: () =>
                    import('@/modules/negotiations/supplier/register/pages/SupplierForm.vue'),
                  meta: {
                    breadcrumb: 'Registrar Proveedor',
                    layout: 'SupplierRegisterLayout',
                  },
                },
                {
                  path: 'edit/:id',
                  name: getSupplierRouteName(
                    SupplierSubClassificationsEnum.TOURIST_TRANSPORT,
                    ResourceActionTypeEnum.EDIT
                  ),
                  component: () =>
                    import('@/modules/negotiations/supplier/register/pages/SupplierForm.vue'),
                  meta: {
                    breadcrumb: 'Editar Proveedor',
                    layout: 'SupplierRegisterLayout',
                  },
                },
              ],
            },
            {
              path: 'tourist-trains',
              name: 'suppliersTouristTrains',
              meta: {
                breadcrumb: 'Trenes turísticos',
              },
              redirect: { name: 'suppliersTouristTrainList' },
              children: [
                {
                  path: 'list',
                  name: 'suppliersTouristTrainList',
                  component: () =>
                    import(
                      '@/modules/negotiations/supplier/land/tourist-train/pages/TouristTrainListPage.vue'
                    ),
                  meta: {
                    breadcrumb: 'Listado',
                  },
                },
              ],
            },
          ],
        },
        {
          path: 'tickets',
          name: 'suppliersTicket',
          meta: {
            breadcrumb: 'Entradas',
            title: 'Entradas',
            supplierSubClassification: SupplierSubClassificationsEnum.MUSEUMS,
          },
          redirect: { name: 'suppliersTicketsList' },
          children: [
            {
              path: 'list',
              name: getSupplierRouteName(
                SupplierSubClassificationsEnum.MUSEUMS,
                ResourceActionTypeEnum.LIST
              ),
              component: () =>
                import('@/modules/negotiations/supplier/tickets/pages/TicketsPage.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
            {
              path: 'register',
              name: getSupplierRouteName(
                SupplierSubClassificationsEnum.MUSEUMS,
                ResourceActionTypeEnum.CREATE
              ),
              component: () =>
                import('@/modules/negotiations/supplier/register/pages/SupplierForm.vue'),
              meta: {
                breadcrumb: 'Registrar Proveedor',
                layout: 'SupplierRegisterLayout',
              },
            },
            {
              path: 'edit/:id',
              name: getSupplierRouteName(
                SupplierSubClassificationsEnum.MUSEUMS,
                ResourceActionTypeEnum.EDIT
              ),
              component: () =>
                import('@/modules/negotiations/supplier/register/pages/SupplierForm.vue'),
              meta: {
                breadcrumb: 'Editar Proveedor',
                layout: 'SupplierRegisterLayout',
              },
            },
          ],
        },
      ],
    },
    {
      path: 'supplier',
      name: 'supplier-register',
      component: () => import('@/modules/negotiations/supplier-new/layout/index.vue'),
      meta: {
        breadcrumb: 'Proveedores',
        layout: 'SupplierNewLayout',
      },
      redirect: { name: 'supplier-register-form' },
      children: [
        {
          path: 'register',
          name: 'supplier-register-form',
          component: () => import('@/modules/negotiations/supplier-new/pages/form.vue'),
          meta: {
            breadcrumb: 'Registrar Proveedor',
          },
        },
        {
          path: 'edit/:id',
          name: 'supplier-edit-form',
          component: () => import('@/modules/negotiations/supplier-new/pages/form.vue'),
          meta: {
            breadcrumb: 'Editar Proveedor',
          },
        },
      ],
    },
    {
      path: 'suppliers',
      name: 'suppliers',
      component: () => import('@/modules/negotiations/suppliers/layouts/supplier-layout.vue'),
      meta: {
        breadcrumb: 'Proveedores',
        layout: 'SupplierLayout',
      },
      children: [
        {
          path: 'transports',
          name: 'supplierTransport',
          meta: {
            breadcrumb: 'Transporte',
          },
          redirect: {
            name: SupplierListRouteNameEnum.AIRLINE,
          },
        },
        {
          path: 'land-transports',
          name: 'supplierLandTransport',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Transporte terrestre',
            permission: ModulePermissionEnum.SUPPLIER_TRANSPORT,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'TRP',
            supplierClassificationGroup: 'TRANSPORT',
          },
          redirect: {
            name: SupplierListRouteNameEnum.LAND_TRANSPORT,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.LAND_TRANSPORT,
              component: () =>
                import('@/modules/negotiations/suppliers/transports/pages/TransportPage.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'tourist-attractions',
          name: 'supplierTouristAttraction',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Atractivos turísticos',
            permission: ModulePermissionEnum.SUPPLIER_TOURIST_ATTRACTION,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'ATT',
          },
          redirect: {
            name: 'supplierTouristAttractionList',
          },
          children: [
            {
              path: 'list',
              name: 'supplierTouristAttractionList',
              component: () =>
                import(
                  '@/modules/negotiations/suppliers/tourist-attractions/pages/TouristAttractionPage.vue'
                ),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'restaurants',
          name: 'supplierRestaurant',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Restaurante',
            permission: ModulePermissionEnum.SUPPLIER_RESTAURANT,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'RES',
          },
          redirect: {
            name: 'supplierRestaurantList',
          },
          children: [
            {
              path: 'list',
              name: 'supplierRestaurantList',
              component: () =>
                import('@/modules/negotiations/suppliers/restaurants/pages/restaurant-page.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'water-transports',
          name: 'supplierWaterTransport',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Lanchas',
            permission: ModulePermissionEnum.SUPPLIER_WATER_TRANSPORT,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'ACU',
            supplierClassificationGroup: 'TRANSPORT',
          },
          redirect: {
            name: SupplierListRouteNameEnum.WATER_TRANSPORT,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.WATER_TRANSPORT,
              component: () =>
                import(
                  '@/modules/negotiations/suppliers/water-transports/pages/water-transport-page.vue'
                ),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'lodges',
          name: 'supplierLodge',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Lodges',
            permission: ModulePermissionEnum.SUPPLIER_LODGE,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'LOD',
            supplierClassificationGroup: 'TOUR_OPERATOR',
          },
          redirect: {
            name: SupplierListRouteNameEnum.LODGES,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.LODGES,
              component: () =>
                import('@/modules/negotiations/suppliers/lodges/pages/lodge-page.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'airlines',
          name: 'supplierAirlines',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Aerolíneas',
            permission: ModulePermissionEnum.SUPPLIER_AIRLINE,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'AER',
            supplierClassificationGroup: 'TRANSPORT',
          },
          redirect: {
            name: SupplierListRouteNameEnum.AIRLINE,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.AIRLINE,
              component: () =>
                import('@/modules/negotiations/suppliers/airlines/pages/airlines-page.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'tour-operators',
          name: 'supplierTourOperator',
          meta: {
            breadcrumb: 'Operador turístico',
          },
          redirect: {
            name: SupplierListRouteNameEnum.CRUISE,
          },
        },
        {
          path: 'local-operators',
          name: 'supplierLocalOperator',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Operadores locales',
            permission: ModulePermissionEnum.SUPPLIER_TOUR_OPERATOR,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'OPE',
            supplierClassificationGroup: 'TOUR_OPERATOR',
          },
          redirect: {
            name: SupplierListRouteNameEnum.LOCAL_OPERATOR,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.LOCAL_OPERATOR,
              component: () =>
                import(
                  '@/modules/negotiations/suppliers/tour-operators/pages/TourOperatorPage.vue'
                ),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'staff',
          name: 'supplierStaff',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Staff',
            permission: ModulePermissionEnum.SUPPLIER_STAFF,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'STA',
          },
          redirect: {
            name: 'supplierStaffList',
          },
          children: [
            {
              path: 'list',
              name: 'supplierStaffList',
              component: () => import('@/modules/negotiations/suppliers/staff/pages/StaffPage.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'miscellaneous',
          name: 'supplierMiscellaneous',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Misceláneos',
            permission: ModulePermissionEnum.SUPPLIER_MISCELLANEOUS,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'OTR',
          },
          redirect: {
            name: 'supplierMiscellaneousList',
          },
          children: [
            {
              path: 'list',
              name: 'supplierMiscellaneousList',
              component: () =>
                import(
                  '@/modules/negotiations/suppliers/miscellaneous/pages/MiscellaneousPage.vue'
                ),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'cruises',
          name: 'supplierCruises',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Cruceros',
            permission: ModulePermissionEnum.SUPPLIER_CRUISE,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'CRC',
            supplierClassificationGroup: 'TOUR_OPERATOR',
          },
          redirect: {
            name: SupplierListRouteNameEnum.CRUISE,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.CRUISE,
              component: () =>
                import('@/modules/negotiations/suppliers/cruises/pages/cruise-page.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
        {
          path: 'trains',
          name: 'supplierTrains',
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Trenes',
            permission: ModulePermissionEnum.SUPPLIER_TRAIN,
            action: PermissionActionEnum.READ,
            supplierClassificationId: 'TRN',
            supplierClassificationGroup: 'TRANSPORT',
          },
          redirect: {
            name: SupplierListRouteNameEnum.TRAIN,
          },
          children: [
            {
              path: 'list',
              name: SupplierListRouteNameEnum.TRAIN,
              component: () =>
                import('@/modules/negotiations/suppliers/trains/pages/TrainPage.vue'),
              meta: {
                breadcrumb: 'Listado',
              },
            },
          ],
        },
      ],
    },
    {
      path: 'transport-configurator',
      name: 'transportConfigurator',
      component: () =>
        import(
          '@/modules/negotiations/type-unit-configurator/layout/TypeUnitConfiguratorLayout.vue'
        ),
      // beforeEnter: checkPermission,
      meta: {
        breadcrumb: 'Configurador',
        permission: ModulePermissionEnum.TRANSPORT_CONFIGURATOR,
        action: PermissionActionEnum.READ,
      },
      redirect: { name: 'transportConfiguratorGeneral' },
      children: [
        {
          path: 'general',
          name: 'transportConfiguratorGeneral',
          component: () =>
            import(
              '@/modules/negotiations/type-unit-configurator/pages/TypeUnitConfiguratorPage.vue'
            ),
          meta: {
            breadcrumb: 'Tipos de unidades de transporte',
          },
        },
      ],
    },
    {
      path: 'country-calendar',
      name: 'countryCalendar',
      // beforeEnter: checkPermission,
      meta: {
        breadcrumb: 'Calendarios de países',
        permission: ModulePermissionEnum.COUNTRY_CALENDAR,
        action: PermissionActionEnum.READ,
      },
      redirect: { name: 'countryCalendarGeneral' },
      children: [
        {
          path: 'general',
          component: () =>
            import(
              '@/modules/negotiations/country-calendar/configuration/layout/HolidayCalendarConfigurationLayout.vue'
            ),
          // beforeEnter: checkPermission,
          meta: {
            breadcrumb: 'Calendarios de países',
            permission: ModulePermissionEnum.COUNTRY_CALENDAR,
            action: PermissionActionEnum.READ,
          },
          children: [
            {
              path: '',
              name: 'countryCalendarGeneral',
              component: () =>
                import(
                  '@/modules/negotiations/country-calendar/configuration/pages/HolidayCalendarConfiguration.vue'
                ),
            },
            {
              path: 'detail/:calendarId',
              name: 'calendarDetail',
              component: () =>
                import(
                  '@/modules/negotiations/country-calendar/configuration/pages/CalendarDetailPage.vue'
                ),
              meta: {
                breadcrumb: 'Detalle',
              },
            },
          ],
        },
      ],
    },
    {
      path: 'compounds',
      name: 'compounds',
      meta: {
        breadcrumb: 'Compuestos',
      },
      redirect: { name: 'compoundsNew' },
      children: [
        {
          path: 'new',
          name: 'compoundsNew',
          component: () => import('@/modules/negotiations/compounds/pages/compounds-form.vue'),
          meta: {
            breadcrumb: 'Estructura para compuestos',
          },
        },
      ],
    },
    {
      path: 'products',
      name: 'products',
      meta: {
        breadcrumb: 'Productos',
      },
      redirect: { name: 'productGeneralPage' },
      children: [
        {
          path: 'general',
          name: 'productGeneral',
          component: () =>
            import('@/modules/negotiations/products/general/layout/ProductGeneralLayout.vue'),
          meta: {
            breadcrumb: 'Producto genérico',
          },
          children: [
            {
              path: '',
              name: 'productGeneralPage',
              meta: {
                breadcrumb: 'Listado',
              },
              component: () =>
                import('@/modules/negotiations/products/general/pages/ProductGeneralPage.vue'),
            },
            {
              path: 'register',
              name: 'productRegister',
              meta: {
                breadcrumb: 'Registrar',
              },
              component: () =>
                import('@/modules/negotiations/products/general/pages/ProductFormPage.vue'),
            },
            {
              path: 'edit/:id',
              name: 'productEdit',
              meta: {
                breadcrumb: 'Editar',
              },
              component: () =>
                import('@/modules/negotiations/products/general/pages/ProductFormPage.vue'),
            },
          ],
        },
        {
          path: 'configuration/:id',
          component: () =>
            import(
              '@/modules/negotiations/products/configuration/layout/ServiceConfigurationLayout.vue'
            ),
          meta: {
            breadcrumb: 'Configuración',
          },
          children: [
            {
              path: '',
              name: 'serviceConfiguration',
              component: () =>
                import(
                  '@/modules/negotiations/products/configuration/pages/ServiceConfigurationPage.vue'
                ),
            },
          ],
        },
        {
          path: 'management',
          name: 'productManagement',
          component: () =>
            import('@/modules/negotiations/products/management/layout/ProductManagementLayout.vue'),
          meta: {
            breadcrumb: 'Gestión',
          },
          children: [
            {
              path: 'pricing-plans',
              name: 'pricingPlansRegistry',
              meta: {
                breadcrumb: 'Planes tarifarios',
              },
              component: () =>
                import(
                  '@/modules/negotiations/products/management/pricing-plans/pages/PricingPlansRegistryPage.vue'
                ),
            },
          ],
        },
      ],
    },
    {
      path: 'hotels',
      name: 'hotels',
      meta: {
        breadcrumb: 'Hoteles',
      },
      children: [
        {
          path: 'quotas',
          name: 'hotelsQuotas',
          component: () => import('@/modules/negotiations/hotels/quotas/layout/QuotasLayout.vue'),
          meta: {
            breadcrumb: 'Cupos',
          },
          redirect: { name: 'hotelsQuotasDashboard' },
          children: [
            {
              path: 'dashboard',
              alias: '',
              name: 'hotelsQuotasDashboard',
              component: () =>
                import('@/modules/negotiations/hotels/quotas/pages/QuotasDashboard.vue'),
              meta: {
                breadcrumb: 'Dashboard',
              },
            },
            {
              path: 'hotel-availability',
              name: 'hotelsQuotasHotelAvailability',
              component: () =>
                import('@/modules/negotiations/hotels/quotas/pages/HotelAvailability.vue'),
              meta: {
                breadcrumb: 'Disponibilidad de hoteles',
              },
            },
          ],
        },
      ],
    },
  ],
};
// Force update for Amplify build sync
