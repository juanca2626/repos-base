<template>
    <div>        
        <b-nav v-if="regions.length > 0" class="fondo-nav" tabs>
            <b-nav-item
                v-for="(region, index) in regions"
                :key="region.id"
                :active="isRegionActive(region.id)"
                @click="selectRegion(region.id)"
            >
                {{ region.description }}
            </b-nav-item>
            <b-nav-item disabled class="nav-divider">|</b-nav-item> <!-- divisor visual -->
            <b-nav-item
                v-for="item in item_globals"
                :active="isTabActive(item.id)"
                @click="selectTab_global(item.link, item.id)"
            >
                <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/>
                <span :class="{'s-color': isTabActive(item.id)}">{{$t(item.title)}}</span>
            </b-nav-item>
        </b-nav>

        <b-nav  v-if="selectedRegionId"  class="fondo-nav mt-2" tabs> 
                <div v-for="item in items" v-show="hasPermissions(item.permission)">
                    <!-- <template v-if="item.status==='active'">
                        <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                            <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/>
                            <span class="s-color">{{$t(item.title)}}</span>
                        </b-nav-item>
                    </template>
                    <template v-else>
                        <b-nav-item @click="tabsStatus(item.link, item.id)">
                            <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/>
                            <span>{{$t(item.title)}}</span>
                        </b-nav-item>
                    </template> -->
                    <b-nav-item
                        :active="isTabActive(item.id)"
                        @click="selectTab(item.link, item.id)" 
                    >
                        <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/>
                        <span :class="{'s-color': isTabActive(item.id)}">{{$t(item.title)}}</span>
                    </b-nav-item>
                </div> 
             
        </b-nav>

        <b-nav  class="fondo-nav mt-2" v-else> 

           

        </b-nav>


    </div>
</template>
<script>
    import { API } from './../../../api'

    export default {
        data: () => {
            return {
                regions: [], // Esto se llenará dinámicamente
                selectedRegionId: null,
                activeTabId: null, // Para mantener el tab hijo activo
            }
        },
        created: function () {
            this.fetchRegions();
            this.initializeTabs();
        },
        computed: {
            items() {
                const clientId = this.$route.params.client_id;
                const regionId = this.$route.params.region_id;

                return [
                    {
                        id: 1,
                        title: 'Markup',
                        link: `/clients/${clientId}/manage_client/regions/${regionId}/markups`,
                        icon: 'percent',
                        status: '',
                        permission: [
                            'clientmarkups'
                        ]
                    },                    
                    {
                        id: 3,
                        title: 'Ejecutivo',
                        link: `/clients/${clientId}/manage_client/regions/${regionId}/executives`,
                        icon: 'user',
                        status: '',
                        permission: [
                            'clientexecutives'
                        ]
                    },
                    {
                        id: 4,
                        title: 'Hoteles',
                        link: `/clients/${clientId}/manage_client/regions/${regionId}/hotels`,
                        icon: 'hotel',
                        status: '',
                        permission: [
                            'clienthotels'
                        ]
                    },
                    {
                        id: 5,
                        title: 'Servicios',
                        link: `/clients/${clientId}/manage_client/regions/${regionId}/services`,
                        icon: 'route',
                        status: '',
                        permission: [
                            'clientservices',
                            'clientserviceoffer',
                            'clientservicerated'
                        ]
                    },
                    {
                        id: 6,
                        title: 'Paquetes',
                        link: `/clients/${clientId}/manage_client/regions/${regionId}/packages`,
                        icon: 'cubes',
                        status: '',
                        permission: [
                            'clientpackages',
                            'clientpackageoffer',
                            'clientpackagerated'
                        ]
                    },
                    // {
                    //     id: 7,
                    //     title: 'Ecommerce',
                    //     link: `/clients/${clientId}/manage_client/regions/${regionId}/ecommerce`,
                    //     icon: 'globe-americas',
                    //     status: '',
                    //     permission: [
                    //         'clientecommerce'
                    //     ]
                    // },                    
                ];
            },
            item_globals() {
                const clientId = this.$route.params.client_id;
                const regionId = this.$route.params.region_id;

                return [ 
                    {
                        id: 2,
                        title: 'Seller',
                        link: `/clients/${clientId}/manage_client/sellers`,
                        icon: 'user',
                        status: '',
                        permission: [
                            'clientsellers'
                        ]
                    },
                    {
                        id: 8,
                        title: 'Contactos',
                        link: `/clients/${clientId}/manage_client/contacts`,
                        icon: 'user',
                        status: '',
                        permission: [
                            'clientcontacts'
                        ]
                    },
                    {
                        id: 9,
                        title: 'Reminders',
                        link: `/clients/${clientId}/manage_client/reminders`,
                        icon: 'history',
                        status: '',
                        permission: ["clientreminders"]
                    }
                ];
            }            
        },
        methods: {
            async fetchRegions() {
                try {
                    const response = await API.get(`/clients/${this.$route.params.client_id}/business_region`);
                    if(response.data.success){
                        this.regions = response.data.data;
                    }else{
                        this.regions = [];
                    }

                    if (this.regions.length > 0) {
                        const regionIdFromRoute = this.$route.params.region_id;
                        this.selectedRegionId = regionIdFromRoute || this.regions[0].id;
                    }
                } catch (error) {
                    console.error('Error fetching regions:', error);
                }
            },
            tabsStatus: function (link, id) {
                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id == this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                this.$router.push('/clients/' + this.$route.params.client_id + link)
            },
            hasPermissions: function (permission) {
                let flag = false
                for (var i = 0; i < permission.length; i++) {
                    if (this.$can('read', permission[i])) {
                        flag = true
                    }
                }
                return flag
            },
            isRegionActive(regionId) {
                const isActive = parseInt(regionId) === parseInt(this.selectedRegionId)
                return isActive
            },

            selectRegion(regionId) {
                console.log('Seleccionando región:', regionId);
                this.activeTabId = null;
                this.selectedRegionId = parseInt(regionId);                
                // Navegar a la misma ruta pero con nueva región
                const currentRouteName = this.$route.name;
                const currentParams = {...this.$route.params, region_id: regionId};
                this.$router.push({
                    name: 'MarkupLayout',
                    params: currentParams
                }).catch(err => {
                    if (err.name !== 'NavigationDuplicated') {
                        console.error(err);
                    }
                });
                this.activeTabId = 1
            },

            refreshChildTabsContent() {
                // 1. Obtener el componente activo actual
                const currentTabComponent = this.getCurrentActiveTabComponent();

                // 2. Si existe un método de recarga, ejecutarlo
                if (currentTabComponent && currentTabComponent.refreshData) {
                    currentTabComponent.refreshData(this.selectedRegionId);
                }

                // 3. Emitir evento global para que otros componentes se actualicen
                // this.$root.$emit('region-changed', this.selectedRegionId);

                console.log(`Contenido actualizado para región ${this.selectedRegionId}`);
            },

            getCurrentActiveTabComponent() {
                // Implementación depende de cómo estén estructurados tus tabs hijos
                // Esto es un ejemplo genérico:
                if (this.$refs.tabContainer) {
                    return this.$refs.tabContainer.$children.find(
                        child => child.$vnode.key === this.activeTabId
                    );
                }
                return null;
            },

            resetChildTabs() {
                this.items.forEach(item => item.status = '');
            },

            isTabActive(tabId) {
                return this.activeTabId === tabId;
            },

            getTabIdFromRoute(routeName) {
                const routeToTabMap = {
                    'MarkupLayout': 1,
                    'SellerLayout': 2,
                    'ExecutiveLayout': 3,
                    'Executive': 3,
                    'HotelLayout': 4,
                    'Hotel': 4,
                    'ServiceLayout': 5,
                    'Service': 5,
                    'ServiceOffer': 5,
                    'ServiceRated': 5,
                    'PackageLayout': 6,
                    'PackageOffer': 6,
                    'PackageRated': 6,
                    'ClientEcommerceLayout': 7,
                    'Questions': 7,
                    'ManageEcommerce': 7,
                    'PrivacyPoliciesListEcommerce': 7,
                    'PurchaseTermsListEcommerce': 7,
                    'RemindersLayout': 9
                };
                return routeToTabMap[routeName];
            },

            selectTab(link, tabId) {
                // this.selectedRegionId = null;
                // Verificar si ya estamos en la misma ruta
                const targetPath = `${link}`;

                if (this.$route.path !== targetPath) {
                    this.activeTabId = tabId;
                    this.$router.push(targetPath).catch(err => {
                        // Ignorar el error de navegación redundante
                        if (err.name !== 'NavigationDuplicated') {
                            console.error(err);
                        }
                    });
                } else {
                    // Si ya estamos en la ruta, solo actualizamos el tab activo
                    this.activeTabId = tabId;
                }
            },

            selectTab_global(link, tabId) {
                this.selectedRegionId = null;
                // Verificar si ya estamos en la misma ruta
                const targetPath = `${link}`;

                if (this.$route.path !== targetPath) {
                    this.activeTabId = tabId;
                    this.$router.push(targetPath).catch(err => {
                        // Ignorar el error de navegación redundante
                        if (err.name !== 'NavigationDuplicated') {
                            console.error(err);
                        }
                    });
                } else {
                    // Si ya estamos en la ruta, solo actualizamos el tab activo
                    this.activeTabId = tabId;
                }
            },

            initializeTabs(){
                // if (this.$route.name === 'MarkupLayout') {
                //     this.items[0].status = 'active'
                // }
                // if (this.$route.name === 'SellerLayout') {
                //     this.items[1].status = 'active'
                // }
                // if ((this.$route.name === 'ExecutiveLayout') || (this.$route.name === 'Executive')) {
                //     this.items[2].status = 'active'
                // }
                // if ((this.$route.name === 'HotelLayout') || (this.$route.name === 'Hotel')) {
                //     this.items[3].status = 'active'
                // }
                // if ((this.$route.name === 'ServiceLayout') || (this.$route.name === 'Service')) {
                //     this.items[4].status = 'active'
                // }
                // if ((this.$route.name === 'ServiceLayout') || (this.$route.name === 'ServiceOffer')) {
                //     this.items[4].status = 'active'
                // }
                // if ((this.$route.name === 'ServiceLayout') || (this.$route.name === 'ServiceRated')) {
                //     this.items[5].status = 'active'
                // }
                // if ((this.$route.name === 'PackageLayout') || (this.$route.name === 'PackageOffer')) {
                //     this.items[5].status = 'active'
                // }
                // if ((this.$route.name === 'PackageLayout') || (this.$route.name === 'PackageRated')) {
                //     this.items[5].status = 'active'
                // }
                // if ((this.$route.name === 'ClientEcommerceLayout') || (this.$route.name === 'Questions')) {
                //     this.items[6].status = 'active'
                // }
                // if ((this.$route.name === 'ClientEcommerceLayout') || (this.$route.name === 'ManageEcommerce')) {
                //     this.items[6].status = 'active'
                // }
                // if ((this.$route.name === 'ClientEcommerceLayout') || (this.$route.name === 'PrivacyPoliciesListEcommerce')|| (this.$route.name === 'PurchaseTermsListEcommerce')) {
                //     this.items[6].status = 'active'
                // }
                // if ((this.$route.name === 'RemindersLayout')) {
                //     this.items[8].status = 'active'
                // }

                if (this.$route.params.region_id) {
                    this.selectedRegionId = this.$route.params.region_id;
                } else if (this.regions.length > 0) {
                    this.selectedRegionId = this.regions[0].id;
                }

                // Establecer tab hijo activo desde la ruta
                this.activeTabId = this.getTabIdFromRoute(this.$route.name);
            }
        },
        watch: {
            // Observar cambios en la región seleccionada
            selectedRegionId(newRegionId, oldRegionId) {
                if (newRegionId && newRegionId !== oldRegionId) {
                    this.refreshChildTabsContent();
                }
            }
        },
    }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

    .nav-divider {
        pointer-events: none;
        opacity: 0.5;
        font-weight: bold;
        padding: 0 20px;
        user-select: none;
    }
    .nav-divider a {
        background: #c8ced3!important;
        height: 40px!important
    }
</style>
