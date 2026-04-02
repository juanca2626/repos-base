<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item
                        @click="tabsStatus(item.link, item.id)"
                        active
                        v-if="item.show"
                        :class="{'disabled-tab': item.id === 9 && isInventoryBlocked}"
                        v-tooltip="item.id === 9 && isInventoryBlocked ? tooltipContent : ''"
                        >
                        <span class="s-color">{{$t('hotelsmanagehotel.'+item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item
                        @click="tabsStatus(item.link, item.id)"
                        v-if="item.show"
                        :class="{'disabled-tab': item.id === 9 && isInventoryBlocked}"
                        v-tooltip="item.id === 9 && isInventoryBlocked ? tooltipContent : ''"
                        >
                        <span>{{$t('hotelsmanagehotel.'+item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>
<script>
    import { API } from './../../../api'

    export default {
        data: () => {
            return {
                items: [
                    {
                        id: 1,
                        title: 'transaction_report',
                        link: '/manage_hotel/transaction_report',
                        icon: 'dot-circle',
                        status: '',
                        show: false
                    },
                    {
                        id: 2,
                        title: 'information',
                        link: '/manage_hotel/information',
                        status: '',
                        show: true
                    },
                    {
                        id: 3,
                        title: 'gallery',
                        link: '/manage_hotel/gallery',
                        status: '',
                        show: true
                    },
                    {
                        id: 4,
                        title: 'up_cross_selling',
                        link: '/manage_hotel/up_cross_selling',
                        status: '',
                        show: true
                    },
                    {
                        id: 5,
                        title: 'configuration',
                        link: '/manage_hotel/configuration',
                        status: '',
                        show: true
                    },
                    {
                        id: 6,
                        title: 'rooms',
                        link: '/manage_hotel/rooms',
                        status: '',
                        show: true
                    },
                    {
                        id: 7,
                        title: 'supplement',
                        link: '/manage_hotel/supplements_hotel',
                        status: '',
                        show: false
                    },

                    {
                        id: 8,
                        title: 'rates',
                        link: '/manage_hotel/rates',
                        status: '',
                        show: true
                    },
                    {
                        id: 9,
                        title: 'inventory',
                        link: '/manage_hotel/inventories',
                        status: '',
                        show: true
                    },
                    {
                        id: 10,
                        title: 'released',
                        link: '/manage_hotel/released',
                        status: '',
                        show: true
                    }
                ],
                channels: [],
                isInventoryBlocked: false,
                tooltipContent: 'El inventario está gestionado por HYPERGUEST'
            }
        },
        async created() {

            // Obtener los datos del hotel
            await this.fetchHotelData();

            // Configurar visibilidad de los items
            this.configureItemsVisibility();

            // Establecer la pestaña activa
            this.setActiveTab();
        //     console.log(this.items)

        //     this.items[0].show = this.showReport
        //     this.items[1].show = this.showInformation
        //     this.items[2].show = this.showGallery
        //     this.items[3].show = this.showUpCross
        //     this.items[4].show = this.showConfiguration
        //     this.items[5].show = this.showRooms
        //     // this.items[6].show = false
        //     this.items[6].show = this.showSupplements
        //     this.items[7].show = this.showRates
        //     this.items[8].show = this.showInventories
        //     this.items[9].show = this.showReleased

            this.checkRouteAndSetActive()

            const activeItem = this.items.find(item => item.status === 'active' && item.show)

            // Si no hay ninguno activo (estamos en el root de manage_hotel o la ruta no coincide con un tab con permisos)
            if (!activeItem) {
                const firstVisible = this.items.find(item => item.show)
                if (firstVisible) {
                    firstVisible.status = 'active'
                    // Realizar la redirección al primer tab disponible
                    this.$router.push('/hotels/' + this.$route.params.hotel_id + firstVisible.link)
                }
            }
        },
        watch: {
            '$route': {
                handler: function (to, from) {
                    this.checkRouteAndSetActive()
                },
                immediate: true
            }
        },
        computed: {
            showReport () {
                //return this.$can('read', 'hotelreport')
            },
            showInformation () {
                return this.$can('read', 'hotelinformation')
            },
            showGallery () {
                return this.$can('read', 'hotelgallery')
            },
            showUpCross () {
                return this.$can('read', 'upselling') || this.$can('read', 'crossselling')
            },
            showConfiguration () {
                return this.$can('read', 'hotelconfigurations')
            },
            showRooms () {
                return this.$can('read', 'rooms')
            },
            showSupplements () {
                return this.$can('read', 'hotelsupplements')
            },
            showRates () {
                return this.$can('read', 'ratescosts') || this.$can('read', 'ratessale')
            },
            showInventories () {
                return this.$can('read', 'inventories')
            },
            showReleased () {
                return this.$can('read', 'hotelreleased')
            },
        },
        methods: {
            async fetchHotelData() {
                try {
                    const hotel_id = this.$route.params.hotel_id;
                    const response = await API.get(`hotels/${hotel_id}/channels?lang=${localStorage.getItem('lang')}`);
                    if (response.data.data) {
                        this.channels = response.data.data;
                        const hyperguestChannel = this.channels.find(channel =>
                            channel.name === "HYPERGUEST" &&
                            channel.pivot.state === 1 &&
                            channel.pivot.type === "2"
                        );

                        // this.isInventoryBlocked = !!hyperguestChannel;
                    }
                } catch (error) {
                    console.error("Error fetching hotel data:", error);
                    this.isInventoryBlocked = false;
                }
            },

            configureItemsVisibility() {
                this.items[0].show = this.showReport;
                this.items[1].show = this.showInformation;
                this.items[2].show = this.showGallery;
                this.items[3].show = this.showUpCross;
                this.items[4].show = this.showConfiguration;
                this.items[5].show = this.showRooms;
                this.items[6].show = this.showSupplements;
                this.items[7].show = this.showRates;
                this.items[8].show = this.showInventories; // Bloquear inventario si es necesario
                this.items[9].show = this.showReleased;
            },

            setActiveTab() {
                if (this.$route.name === 'TransactionReportLayout') {
                    this.items[1].status = 'active';
                }
                if (this.$route.name === 'InformationLayout') {
                    this.items[1].status = 'active';
                }
                if (this.$route.name === 'GalleryLayout') {
                    this.items[2].status = 'active';
                }
                if ((this.$route.name === 'UpCrossSellingLayout') || (this.$route.name === 'UpSelling') || (this.$route.name === 'CrossSelling')) {
                    this.items[3].status = 'active';
                }
                if (this.$route.name === 'ConfigurationLayout') {
                    this.items[4].status = 'active';
                }
                if (this.$route.name === 'RoomsList') {
                    this.items[5].status = 'active';
                }
                if (this.$route.name === 'InventoryLayoutFreeSale') {
                    // Si el inventario está bloqueado, mostrar modal informativo
                    if (this.isInventoryBlocked) {
                        this.showBlockedModal = true;
                        // Redirigir a la primera pestaña disponible
                        this.$router.push('/hotels/' + this.$route.params.hotel_id + this.items[0].link);
                        this.items[0].status = 'active';
                    } else {
                        this.items[8].status = 'active';
                    }
                }
                if (this.$route.name === 'HotelReleasedLayout') {
                    this.items[9].status = 'active';
                }

                const hasActive = this.items.some(item => item.status === 'active' && item.show)

                // Si no hay ninguno activo, marcar el primero que tenga permisos (show: true)
                if (!hasActive) {
                    const firstVisible = this.items.find(item => item.show)
                    if (firstVisible) {
                        firstVisible.status = 'active'
                    }
                }
            },

            checkRouteAndSetActive: function () {
                // Determinar tab activo basado en la ruta actual
                const currentPath = this.$route.path
                this.items.forEach(item => {
                    // Verificamos si la ruta actual contiene el link del item
                    // Esto es más flexible que comparar nombres de rutas específicos
                    // Para inventarios, también verificamos rutas de canales
                    if (item.link === '/manage_hotel/inventories') {
                        // Si estamos en cualquier ruta de inventarios (incluyendo channels)
                        if (currentPath.includes('/manage_hotel/inventories')) {
                            item.status = 'active'
                        } else {
                            item.status = ''
                        }
                    } else if (currentPath.includes(item.link)) {
                        item.status = 'active'
                    } else {
                        item.status = ''
                    }
                })
            },
            tabsStatus: function (link, id) {
                 // Si es el item de inventory y está bloqueado, mostrar modal y no hacer nada
                if (id === 9 && this.isInventoryBlocked) {
                    this.showBlockedModal = true;
                    return;
                }

                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id == this.items[i].id) {
                        this.items[i].status = 'active'
                    } else {
                        this.items[i].status = ''
                    }
                }
                if (this.items[3].id === 4) {
                    this.$root.$emit('updateInventory', { tab: 1 })
                }
                this.$router.push('/hotels/' + this.$route.params.hotel_id + link)
            }
        }
    }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

    .disabled-tab {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }

    .disabled-tab span {
        color: #6c757d !important;
    }

    .tooltip {
        display: block !important;
        z-index: 10000;
    }

    .tooltip .tooltip-inner {
        background: #333;
        color: white;
        border-radius: 4px;
        padding: 8px 12px;
        max-width: 250px;
        text-align: center;
    }

    .tooltip .tooltip-arrow {
        width: 0;
        height: 0;
        border-style: solid;
        position: absolute;
        margin: 5px;
        border-color: #333;
    }

    .tooltip[x-placement^="top"] {
        margin-bottom: 5px;
    }

    .tooltip[x-placement^="top"] .tooltip-arrow {
        border-width: 5px 5px 0 5px;
        border-left-color: transparent !important;
        border-right-color: transparent !important;
        border-bottom-color: transparent !important;
        bottom: -5px;
        left: calc(50% - 5px);
        margin-top: 0;
        margin-bottom: 0;
    }

    .tooltip[x-placement^="bottom"] {
        margin-top: 5px;
    }

    .tooltip[x-placement^="bottom"] .tooltip-arrow {
        border-width: 0 5px 5px 5px;
        border-left-color: transparent !important;
        border-right-color: transparent !important;
        border-top-color: transparent !important;
        top: -5px;
        left: calc(50% - 5px);
        margin-top: 0;
        margin-bottom: 0;
    }

    .tooltip[x-placement^="right"] {
        margin-left: 5px;
    }

    .tooltip[x-placement^="right"] .tooltip-arrow {
        border-width: 5px 5px 5px 0;
        border-left-color: transparent !important;
        border-top-color: transparent !important;
        border-bottom-color: transparent !important;
        left: -5px;
        top: calc(50% - 5px);
        margin-left: 0;
        margin-right: 0;
    }

    .tooltip[x-placement^="left"] {
        margin-right: 5px;
    }

    .tooltip[x-placement^="left"] .tooltip-arrow {
        border-width: 5px 0 5px 5px;
        border-top-color: transparent !important;
        border-right-color: transparent !important;
        border-bottom-color: transparent !important;
        right: -5px;
        top: calc(50% - 5px);
        margin-left: 0;
        margin-right: 0;
    }

    .tooltip[aria-hidden='true'] {
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.15s, visibility 0.15s;
    }

    .tooltip[aria-hidden='false'] {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.15s;
    }
</style>


