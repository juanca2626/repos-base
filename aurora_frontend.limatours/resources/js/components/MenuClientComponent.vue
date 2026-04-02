<template>
    <div>
        <loading-component v-show="blockPage"></loading-component>
        <nav class="navbar-client navbar-expand-lg navbar-fixed-top" role="navigation" style="z-index: 1;">
            <a class="navbar-brand-client" href="/">
                <img src="/images/logo/logo_nav.jpg">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText"
                    aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="icon-menu"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav mr-auto">
                    <li v-for="(item, index) in visibleMenuItems" :key="index"
                        :class="['nav-item', {'dropdown': item.type === 'dropdown'}]">

                        <a v-if="item.type === 'link'" class="nav-link" :href="item.link"
                           @click="item.action && item.action($event)">
                            {{ item.label }}
                            <span v-if="index === 0" class="sr-only">(current)</span>
                        </a>

                        <template v-else-if="item.type === 'dropdown'">
                            <a class="nav-link nav-name" type="button" data-toggle="dropdown" href="#" role="button"
                               aria-haspopup="true" aria-expanded="false">
                                {{ item.label }} <i class="fas fa-sort-down" v-if="item.id === 'download_rates'"></i>
                            </a>
                            <div class="dropdown-menu" style="width: auto !important;padding: 10px !important;">
                                <template v-for="(child, cIndex) in item.children">
                                    <h6 v-if="child.type === 'header'" class="dropdown-header" :key="cIndex">{{
                                            child.label
                                        }}</h6>
                                    <a v-else class="dropdown-item" :href="child.link"
                                       @click="child.action && child.action($event)" style="font-size: 14px"
                                       :key="cIndex">
                                        {{ child.label }}
                                    </a>
                                </template>
                            </div>
                        </template>
                    </li>
                </ul>
                <div>
                    <ul class="navbar-nav mr-auto">
                        <v-select class="form-control" v-model="lang" :options="languages"
                                  :reduce="language => language.iso" label="iso" @input="setLang()">
                        </v-select>
                        <li class="nav-item" v-if="!user_invited">
                            <a class="nav-link link-icon" href="javascript:void(0)" role="button" aria-haspopup="true"
                               aria-expanded="false" @click="goToA3('reports')">
                                <span class="icon-folder"></span>
                                <span class="count"
                                      style="font-size: 12px; font-weight: 600;">({{ popup_quotes_totals.no_viewed
                                    }})</span>
                            </a>
                        </li>

                        <li class="nav-item cart" v-if="!user_invited" style="padding-left: 0px;">

                            <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMain2"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                               style="background-color: white!important;;padding: 8px; color:#EB5757 ">
                                <span class="icon-shopping-cart"></span>
                                <span class="count"
                                      style="font-size: 12px; font-weight: 600;">{{ cart.quantity_items }}</span>
                            </a>


                            <div class="dropdown dropdown-menu carito-vacio" aria-labelledby="dropdownMain2"
                                 v-if="cart.hotels.length == 0 && cart.services.length == 0">
                                <p>{{ translations.label.empty_cart }}</p>
                            </div>
                            <div class="dropdown dropdown-menu menu-cart" aria-labelledby="dropdownMain2"
                                 v-if="cart.hotels.length > 0 || cart.services.length > 0">

                                <h2>{{ translations.label.your_shopping_cart }}.</h2>
                                <h3>{{ translations.label.you_have }} {{ (cart.hotels.length + cart.services.length) }}
                                    {{
                                        translations.label.product_in_your_cart }}.</h3>
                                <div class="shopping-cart">
                                    <div class="scroll-cart scrollbar-project">
                                        <div class="card-body">

                                            <div :id="'hotel-content-shopping'+index" class="hotel-content-shopping"
                                                 v-for="(hotel,index) in cart.hotels">

                                                <div class="img-shopping">
                                                    <img :src="hotel.hotel.galleries[0]"
                                                         onerror="this.src = baseURL + 'images/hotel-default.jpg'">
                                                </div>
                                                <div class="content-shopping">
                                                    <span class="tipo">{{ hotel.hotel.class }}</span>
                                                    <h3 class="text-left">
                                                        {{ hotel.hotel_name }}
                                                        <span class="icon-star"></span>
                                                        <div class="price">$<b>{{ getPrice(hotel.total_hotel) }}</b> ccc
                                                        </div>
                                                    </h3>
                                                    <div class="date-shopping">
                                                        <i class="icon-calendar"></i>
                                                        <span>{{ formatDate(hotel.date_from) }}</span>
                                                        <span>{{ formatDate(hotel.date_to) }}</span>

                                                        <div class="total-rooms">

                                                            <b-button v-b-toggle="'hotels-'+index">
                                                                <span class="fa fa-circle"
                                                                      v-for="room in hotel.rooms"></span>
                                                                {{ hotel.rooms.length }}
                                                                {{ translations.label.room_abrev }}
                                                                <i class="icon-chevron-down"></i>
                                                            </b-button>

                                                        </div>
                                                    </div>
                                                    <b-collapse :id="'hotels-'+index">
                                                        <b-card>
                                                            <div class="car-room" v-for="room in hotel.rooms">
                                                                <h5>
                                                                    <span class="fa fa-circle"></span> {{ room.room_name
                                                                    }}
                                                                    <span class="text">{{ room.rate_name }}</span>
                                                                    <button class="btn btn-success"
                                                                            v-if="room.onRequest ==1"
                                                                            style="border-radius: 20px;">OK
                                                                    </button>
                                                                    <button class="btn btn-danger"
                                                                            v-if="room.onRequest ==0"
                                                                            style="border-radius: 20px;">RQ
                                                                    </button>
                                                                </h5>
                                                                <div class="price">
                                                                    $ <b>{{ getPrice(room.total_room) }}</b>
                                                                    <a class="remove"
                                                                       @click="cancelRoomsCart(room,hotel)">
                                                                        <i class="icon-trash-2"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </b-card>
                                                    </b-collapse>
                                                </div>

                                            </div>
                                            <div :id="'service-content-shopping'+index" class="hotel-content-shopping"
                                                 v-for="(service,index) in cart.services">

                                                <div class="img-shopping">
                                                    <img v-if="service.service.galleries.length > 0"
                                                         :src="service.service.galleries[0].url"
                                                         onerror="this.onerror=null;this.src='https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg'"
                                                         class="object-fit_cover backup_picture_service" alt="service">
                                                    <img class="object-fit_cover backup_picture_service"
                                                         src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_400/v1440093948/paragliding_in_Miraflores._123110314_dlyun3.jpg"
                                                         alt="Image Service" v-else>
                                                </div>
                                                <div class="content-shopping">
                                                    <h3 class="text-left">
                                                        <div style="width: 340px; font-weight: 600; font-size: 1.4rem;">
                                                            {{ service.service_name }} - [{{ service.service.code }}]
                                                        </div>
                                                        <div class="price">$<b>{{ getPrice(service.total_service) }}</b>
                                                        </div>
                                                    </h3>
                                                    <div class="car-room">
                                                        <div class="price" style="z-index: 10001;">
                                                            <a class="remove" @click="cancelServiceCart(service)">
                                                                <i class="icon-trash-2"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="date-shopping">
                                                            <span class="ml-0"
                                                                  v-for="multiservicio in service.service.components">
                                                                <span class="ml-0 icon-folder-plus"
                                                                      v-if="service.service.components.length>0"></span>
                                                                [{{ multiservicio.code
                                                                }}] {{ multiservicio.descriptions.name }}
                                                                <br>
                                                            </span>
                                                        <div class="mt-2">
                                                            <i class="icon-calendar"></i>
                                                            <span>{{ formatDate(service.date_from) }}</span><br>
                                                            <i class="icon-map-pin"></i>
                                                            <span> {{ service.service.origin.state }}</span>
                                                            <span
                                                                v-if="service.service.origin.city !== null"> {{ service.service.origin.city
                                                                }}</span>
                                                            <span
                                                                v-if="service.service.origin.zone !== null"> {{ service.service.origin.zone
                                                                }}</span>
                                                            <i class="icon-arrow-right"></i>
                                                            <span> {{ service.service.destiny.state }}</span>
                                                            <span
                                                                v-if="service.service.destiny.city !== null"> {{ service.service.destiny.city
                                                                }}</span>
                                                            <span
                                                                v-if="service.service.destiny.zone !== null"> {{ service.service.destiny.zone
                                                                }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="no-gutters total">
                                        <h3>{{ translations.label.total_to_pay }}</h3>
                                        <div class="price">USD <b>{{ getPrice(cart.total_cart) }}</b></div>
                                    </div>
                                </div>
                                <a class="btn btn-primary btn-car" href="javascript:void(0)"
                                   @click="goCartDetails()">{{ translations.label.go_to_cart }}</a>
                                <a class="btn btn-primary" href="javascript:void(0)"
                                   @click="clearCart()">{{ translations.label.clear_cart }}</a>
                            </div>

                        </li>

                        <!-- end quotes -->
                        <li class="nav-item dropdown drop-client">
                            <a class="nav-link link-icon dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="icon-user"></span>
                            </a>
                            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                                <input type="hidden" name="_token" v-model="csrf_token">
                            </form>
                            <div class="dropdown-menu dropdown-menu__client" aria-labelledby="dropdownMenuLink">
                                <div>
                                    {{ translations.label.hi }}, <strong>{{ username }}</strong>
                                </div>
                                <span class="dropdown-item">
                                    <span class="icon-user"></span>
                                    <a href="/account">
                                        {{ translations.label.my_account }}
                                    </a>
                                </span>
                                <span class="dropdown-item" @click="logout">
                                    <span class="icon-log-out"></span>
                                    {{ translations.label.sign_off }}
                                </span>
                            </div>
                        </li>


                    </ul>
                </div>
            </div>
        </nav>
        <a :href="href_" ref="download_hotel" target="_self"></a>
        <modal-quotes></modal-quotes>
    </div>
</template>
<style>
.navbar .aurora-main .dropdown-menu .nav-primary > .nav-item {
    margin-bottom: 15px;
    .dropdown-header{
        font-size: 12px;
        font-weight: bold;
    }
}

.back-new {
    background: #E2FDFF !important;
}

.bootstrap-datetimepicker-widget.dropdown-menu {
}

.force-right {
    right: 0;
    position: absolute !important;
}
</style>
<script>
import Cookies from "js-cookie";
import { cookiesAuth } from "../mixins/cookiesAuth";
// Using font-awesome 5 icons
$.extend(true, $.fn.datetimepicker.defaults, {
    icons: {
        time: "far fa-clock",
        date: "far fa-calendar",
        up: "fas fa-arrow-up",
        down: "fas fa-arrow-down",
        previous: "fas fa-chevron-left",
        next: "fas fa-chevron-right",
        today: "fas fa-calendar-check",
        clear: "far fa-trash-alt",
        close: "far fa-times-circle"
    }
});

export default {
    mixins: [cookiesAuth],
    data: () => {
        return {
            user: "",
            username: "",
            photo: "",
            user_type_id: localStorage.getItem("user_type_id"),
            view_permissions: JSON.parse(localStorage.getItem("view_permissions")),
            user_id: localStorage.getItem("user_id"),
            csrf_token: "",
            client_id: "",
            client_code: "",
            clients: [],
            permissions: [],
            clientSelect: [],
            baseURL: window.baseURL,
            baseExternalURL: window.baseExternalURL,
            lang: "en",
            languages: [],
            popup_quotes: [],
            popup_quotes_totals: {
                no_viewed: 0,
                total: 0
            },
            route_name: "",
            cart: {
                cart_content: [],
                hotels: [],
                services: [],
                total_cart: 0.00,
                quantity_items: 0
            },
            notifications: {
                all: 0,
                news: 0,
                items: [],
                time: ""
            },
            titleReminder: "",
            messageReminder: "",
            feciniReminder: "",
            fecfinReminder: "",
            options: {
                format: "DD/MM/YYYY",
                useCurrent: true
            },
            optionsT: {
                format: "h:mm:ss",
                useCurrent: true,
                showClear: true,
                showClose: true
            },
            typeReminder: "1",
            optionsS: [
                { text: "Semanal", value: 1 },
                { text: "Diario", value: 2 }
            ],
            priorities: [
                { text: "Baja", value: "B" },
                { text: "Media", value: "M" },
                { text: "Alta", value: "A" }
            ],
            priorityReminder: "",
            users: [],
            usersReminder: [],
            hourReminder: "",
            reminders: [],
            all_reminders: 0,
            modal_reminders: false,
            loadingModal: false,
            translations: {
                label: {},
                validations: {},
                messages: {}
            },
            code: localStorage.getItem("code"),
            user_invited: false,
            blockPage: false,
            href_: "",
            TokenKey: window.tokenKey,
            UserKey: window.userKey,
            Domain: { domain: window.domain }
        };
    },
    created() {
        this.csrf_token = document.querySelector("meta[name='csrf-token']").getAttribute("content");

        if (!this.verifyToken()) {
            return;
        }
        this.getLanguages();

        this.lang = document.querySelector("meta[name='lang']").getAttribute("content");
        localStorage.setItem("lang", this.lang);

        this.route_name = document.querySelector("meta[name='route_name']").getAttribute("content");
        this.user = document.querySelector("meta[name='code']").getAttribute("content");
        let username = document.querySelector("meta[name='username']").getAttribute("content");
        this.username = (username == "") ? this.user : username;
        if (localStorage.getItem("client_id")) {
            if (localStorage.getItem("client_id") != "" && localStorage.getItem("client_id") != null) {
                this.client_id = localStorage.getItem("client_id");

                if (this.client_id) {
                    this.getClient();
                }
            }
        }
        if (localStorage.getItem("client_code")) {
            if (localStorage.getItem("client_code") != "" && localStorage.getItem("client_code") != null) {
                this.client_code = localStorage.getItem("client_code");
            }
        }
        if (localStorage.getItem("permissions")) {
            if (localStorage.getItem("permissions") !== "" && localStorage.getItem("permissions") != null) {
                this.permissions = JSON.parse(localStorage.getItem("permissions"));
            }
        }

        if (this.route_name != "hotels") {
            localStorage.setItem("reservation", false);
        }
        this.$root.$on("updateMenu", () => {
            this.getCartContent();
            this.searchPopupQuotes();
        });

        this.code = localStorage.getItem("code");
    },
    mounted() {
        if (!this.verifyToken()) {
            this.logout();
        }
        this.loadPhoto();
        this.loadUsers();
        this.loadReminders();
        this.setTranslations();

        this.user_invited = (this.code === "guest");

        if (this.canUsePushNotifications()) {
            this.registerPush();
            this.verifySetPush();
        } else {
            console.log("Push notifications no disponibles en este navegador o contexto.");
        }

        if (localStorage.getItem("client_id") != "" && localStorage.getItem("client_id") != null) {
            //document.getElementById("cliente_select").remove(0)
        }
        if (this.route_name !== "reservations.personal_data") {
            this.getCartContent();
        }

        if (this.user_type_id == 3) {
            this.getClientsByExecutive();
            if (localStorage.getItem("reservation") == "false") {
                this.$root.$emit("updatedestiniesandclass");
            }
        }

        if (this.user_type_id == 4) {
            if (localStorage.getItem("reservation") == "false") {
                this.$root.$emit("updatedestiniesandclass");
            }
        }

        this.searchNotifications("");

        let _function = localStorage.getItem("_function");
        if (_function != "") {
            eval(_function);
        }

        this.searchPopupQuotes();

        if (document.head.querySelector("[name=route_name]")) {

            let page = document.head.querySelector("[name=route_name]").content;

            if (!["services", "hotels", "cart_details", "reservations.personal_data"].includes(page)) {
                localStorage.setItem("search_params", "");
            }

        } else {
            localStorage.setItem("search_params", "");
        }


    },
    computed: {
        menuItems() {
            let items = [];
            const t = (key) => this.translations.label[key] || '';

            // Packages
            if (this.hasPermission('mfpackages', 'read') || (this.client_id != '' && this.user_type_id == 3)) {
                items.push({
                    id: 'packages',
                    label: t('packages'),
                    link: '/packages',
                    type: 'link'
                });
            }

            // Hotels
            if (this.hasPermission('mfhotels', 'read') || (this.user_type_id == 3)) {
                items.push({
                    id: 'hotels',
                    label: t('hotels'),
                    link: '/hotels',
                    type: 'link'
                });
            }

            // Services
            if (this.hasPermission('mfservices', 'read') || (this.client_id != '' && this.user_type_id == 3)) {
                items.push({
                    id: 'services',
                    label: t('services'),
                    link: '/services',
                    type: 'link'
                });
            }

            // Quotation Board
            if (this.hasPermission('mfquotationboard', 'read') || (this.client_id != '' && this.user_type_id == 3)) {
                let item = {
                    id: 'quotation_board',
                    label: t('quotation_board'),
                    type: 'link'
                };
                if (this.user_type_id == '4') {
                    item.link = 'javascript:void(0)';
                    item.action = () => this.goToA3();
                } else {
                    item.link = '/packages/cotizacion';
                }
                items.push(item);
            }

            // Bookings
            if (this.hasPermission('mffilesquery', 'read') || (this.client_id != '' && this.user_type_id == 3)) {
                items.push({
                    id: 'bookings',
                    label: t('bookings'),
                    link: '/consulta_files',
                    type: 'link'
                });
            }

            // Multimedia
            items.push({
                id: 'multimedia',
                label: t('multimedia'),
                link: '/multimedia',
                type: 'link'
            });

            // Perú Facile
            if (this.hasPermission('mfseriesfacile', 'read') || (this.client_id != '' && this.user_type_id == 3)) {
                items.push({
                    id: 'peru_facile',
                    label: 'Perú Facile',
                    link: window.a3BaseUrl + 'series/series-dashboards',
                    type: 'link'
                });
            }

            // Download Rates
            let downloadChildren = [];
            if (this.client_id != '') {
                downloadChildren.push({
                    label: t('download_hotels_rates') + ' 2026',
                    link: '#',
                    action: (e) => {
                        e.preventDefault();
                        this.getRouteExcelHotel(2026);
                    }
                });
            }
            if (this.client_id != '' && this.client_code != '9NEZAS') {
                downloadChildren.push({
                    label: t('download_rate_services') + ' 2026',
                    link: '#',
                    action: (e) => {
                        e.preventDefault();
                        this.getRouteExcelServiceYear(2026);
                    }
                });
            }

            items.push({
                id: 'download_rates',
                label: t('download_rates'),
                type: 'dropdown',
                children: downloadChildren
            });

            return items;
        },
        visibleMenuItems() {
            const MAX_ITEMS = 8;
            if (this.menuItems.length <= MAX_ITEMS) {
                return this.menuItems;
            }

            let visible = this.menuItems.slice(0, MAX_ITEMS - 1);
            let hidden = this.menuItems.slice(MAX_ITEMS - 1);
            let overflowChildren = [];

            hidden.forEach(item => {
                if (item.type === 'dropdown') {
                    if (item.children && item.children.length > 0) {
                        overflowChildren.push({ type: 'header', label: item.label });
                        item.children.forEach(child => {
                            overflowChildren.push({ ...child, type: 'link' });
                        });
                    }
                } else {
                    overflowChildren.push(item);
                }
            });

            visible.push({
                id: 'more',
                label: '...',
                type: 'dropdown',
                children: overflowChildren
            });

            return visible;
        }
    },
    methods: {
        canUsePushNotifications: function() {
            return typeof window !== "undefined" &&
                window.isSecureContext &&
                typeof firebase !== "undefined" &&
                "serviceWorker" in navigator &&
                "Notification" in window;
        },
        getMessagingInstance: function() {
            if (!this.canUsePushNotifications()) {
                return null;
            }

            try {
                return firebase.messaging();
            } catch (error) {
                console.error("No se pudo inicializar Firebase Messaging.", error);
                return null;
            }
        },
        logout() {
            this.removeCookies();
            document.getElementById("logout-form").submit();
        },
        roundLito: function(num) {
            num = parseFloat(num);
            num = (num).toFixed(2);

            if (num != null) {
                var res = String(num).split(".");
                var nEntero = parseInt(res[0]);
                var nDecimal = 0;
                if (res.length > 1)
                    nDecimal = parseInt(res[1]);

                var newDecimal;
                if (nDecimal < 25) {
                    newDecimal = 0;
                } else if (nDecimal >= 25 && nDecimal < 75) {
                    newDecimal = 5;
                } else if (nDecimal >= 75) {
                    nEntero = nEntero + 1;
                    newDecimal = 0;
                }

                return parseFloat(String(nEntero) + "." + String(newDecimal));
            }
        },
        getRouteExcelServiceYear: function(year) {
            this.blockPage = true;
            if (localStorage.getItem("user_type_id") == 4) {
                dataLayer.push({
                    event: "download",
                    file_category: "download_services_rates_" + year
                });
            }
            axios.get(
                baseExternalURL + "api/services/" + year + "/export?lang=" + localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") + "&user_id=" + localStorage.getItem("user_id") + "&user_type_id=" + localStorage.getItem("user_type_id")
            ).then((result) => {
                console.log(result);
                this.blockPage = false;
                if (result.data.success) {
                    this.$toast.success(this.translations.label.send_rate_email, {
                        position: "top-right"
                    });
                } else if (result.data.success == false && result.data.status_download == true) {
                    this.$toast.error(this.translations.label.i_already_made_request, {
                        position: "top-right"
                    });
                } else {
                    this.$toast.error(this.translations.label.an_error_occurred, {
                        position: "top-right"
                    });
                }
            }).catch((e) => {
                console.log(e);
                this.blockPage = false;
                this.$toast.error(this.translations.messages.an_error_occurred, {
                    // override the global option
                    position: "top-right"
                });
            });
        },
        getRouteExcelHotel: function(year) {
            this.blockPage = true;
            if (localStorage.getItem("user_type_id") == 4) {
                dataLayer.push({
                    event: "download",
                    file_category: "download_hotels_rates_" + year
                });
            }
            setTimeout(this.getRouteExcelExport, 50000);
            axios.get(
                baseExternalURL + "api/hotels/generate_array/" + year + "?lang=" + localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") + "&user_id=" + localStorage.getItem("user_id") + "&user_type_id=" + localStorage.getItem("user_type_id")
            )
                .then((result) => {

                    console.log(result);

                    this.href_ = baseExternalURL + "hotels/export/" + year + "?lang=" +
                        localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") +
                        "&user_id=" + localStorage.getItem("user_id") + "&user_type_id=" +
                        localStorage.getItem("user_type_id");

                    this.$nextTick(() => {
                        this.$refs.download_hotel.click();
                    });
                    this.$toast.success("Downloading ...", {
                        // override the global option
                        position: "top-right"
                    });
                    let me = this;
                    setTimeout(function() {
                        me.blockPage = false;
                    }, 18000);

                }).catch((e) => {
                console.log(e);
            });

        },
        getRouteExcelExport: function() {
            this.blockPage = false;
            var a = document.createElement("a");

            a.target = "_blank";

            a.href = baseExternalURL + "api/hotels/export/2021?lang=" + localStorage.getItem("lang") + "&client_id=" + localStorage.getItem("client_id") + "&user_id=" + localStorage.getItem("user_id") + "&user_type_id=" + localStorage.getItem("user_type_id");

            a.click();

        },
        hasPermission: function(permission, action) {
            let index = 0;
            let flag_permission = false;
            let enable = false;
            for (let i = 0; i < this.permissions.length; i++) {
                if (this.permissions[i].subject === permission) {
                    flag_permission = true;
                    index = i;
                }
            }
            if (flag_permission) {
                for (let a = 0; a < this.permissions[index].actions.length; a++) {
                    if (this.permissions[index].actions[a] === action) {
                        enable = true;
                    }
                }
            }
            return enable;
        },
        setTranslations() {
            axios.get(baseURL + "translation/" + localStorage.getItem("lang") + "/slug/global").then((data) => {
                this.translations = data.data;
            });
        },
        loadPhoto: function() {
            axios.get(
                baseURL + "account/find_photo"
            )
                .then((result) => {
                    this.photo = result.data.photo;
                })
                .catch((e) => {
                    console.log(e);
                });
        },
        updateShowInPopup() {
            axios.put(window.a3BaseQuoteServerURL + "api/quotes/showInPopup/0")
                .then((result) => {
                    // console.log(result)
                    this.popup_quotes_totals.no_viewed = 0;
                }).catch((e) => {
                console.log(e);
            });
        },
        searchPopupQuotes() {
            axios.get(window.a3BaseQuoteServerURL + "api/quotes?lang=" + localStorage.getItem("lang") +
                "&page=1&limit=5&filterBy=activated&executive=" + localStorage.getItem("user_id"))
                .then((result) => {
                    this.popup_quotes = result.data.data;
                    this.popup_quotes_totals.total = result.data.totals.total;
                }).catch((e) => {
                console.log(e);
            });
        },
        getLanguages: function() {
            axios.get(baseExternalURL + "api/languages")
                .then((result) => {
                    this.languages = result.data.data;
                }).catch((e) => {
                console.log(e);
            });
        },
        loadUsers: function() {
            axios.post(
                baseURL + "search_users"
            )
                .then((result) => {
                    this.users = result.data.users;
                }).catch((e) => {
                console.log(e);
            });
        },
        loadReminders: function() {
            axios.post(
                baseURL + "count_reminders"
            )
                .then((result) => {
                    this.all_reminders = result.data.all_reminders;
                }).catch((e) => {
                console.log(e);
            });
        },
        registerPush: function() {
            if (!this.canUsePushNotifications()) {
                return;
            }

            var config = {
                apiKey: "AIzaSyByFvuZUxj1dOajExlm7BAnJdqksXsqri4",
                authDomain: "firebase-limatours.firebaseapp.com",
                databaseURL: "https://firebase-limatours.firebaseio.com",
                projectId: "firebase-limatours",
                storageBucket: "firebase-limatours.appspot.com",
                messagingSenderId: "541786854376"
            };

            if (!firebase.apps.length) {

                firebase.initializeApp(config);
                // Supported!
                // REGISTRAR EL WORKER
                navigator.serviceWorker.register(window.origin + "/sw.js").then(registro => {
                    firebase.messaging().useServiceWorker(registro);

                    // console.log("Registro Correcto: " + registro)
                })
                    .catch(error => {
                        console.log(error);
                    });
                // REGISTRAR EL WORKER

                const messaging = this.getMessagingInstance();
                if (!messaging) {
                    return;
                }

                // Registrar credenciales web
                messaging.usePublicVapidKey(
                    "BPqRLq6SsAsJTUUNwfyyd94RvLBbIf6vZp078YDiLri4sF9OSlh7bLvqw1RnusZLnxJLH30-pk2BdLPTZCDjpMc"
                );

                //  Recibir las notificaciones cuando el usuario esta foreground
                messaging.onMessage(playload => {
                    console.log(playload);
                    this.searchNotifications();

                    this.$toast.success(playload.notification.title, {
                        // override the global option
                        position: "top-right"
                    });
                });
            }
        },
        verifySetPush: function() {
            this.loading = true;
            const messaging = this.getMessagingInstance();
            if (!messaging) {
                this.loading = false;
                return;
            }
            messaging.getToken()
                .then(token => {
                    if (token != null && token != "") {
                        const db = firebase.firestore();
                        db.settings({ timestampsInSnapshots: true });
                        db.collection("tokens").doc(token).set({
                            token: token
                        }).catch(error => {
                            console.error(error);
                        });

                        this.area1 = token;
                    } else {
                        console.log("Usuario no registrado.. Solicitando permiso..");
                        this.getSubscription();
                    }
                })
                .catch(error => {
                    this.loading = false;
                    console.error("No se pudo obtener el token de Firebase.", error);
                });
        },
        sendMessageTest: function() {

            console.log(this.para);

            let myData = {
                "to": this.para,
                "notification": {
                    "title": "Prueba",
                    "body": "Prueba de contenido..",
                    // "image" : window.origin + '/images/viaje.jpg',
                    // "icon" : window.origin + '/images/favicon.png',
                    "click_action": "http://google.com"
                }
            };

            /*
            $.ajax({
                url: 'https://fcm.googleapis.com/fcm/send',
                type: 'POST',
                data: JSON.stringify(myData),
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'key=' + 'AAAAfiUDU-g:APA91bGI-fdhLGFb9PrvB0OSVUOV2RzmFoKIPSL10df9U7u5J-K8t4hk-kPq1ZQRlGOENFBoGOHfRoELTVR--h1j4FD_O1eejbE5-TP7S9SM2TNtSbJdebrA-qgxxqFi9qfkQoT8pUPH'
                },
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                },
                error: function (data) {
                    console.log(data);
                }
            });
            */
        },
        getSubscription: function() {
            const messaging = this.getMessagingInstance();
            if (!messaging) {
                this.loading = false;
                return;
            }

            if (Notification.permission === "denied") {
                this.loading = false;
                console.warn("El usuario bloqueó las notificaciones.");
                return;
            }
            messaging.requestPermission()
                .then(() => {
                    return messaging.getToken();
                })
                .then(token => {
                    console.log("token", token);

                    if (token != "") {
                        this.area1 = token;

                        axios.post(baseURL + "register_push_notification", {
                            token: token
                        })
                            .then((result) => {
                                if (result.data.success) {
                                    this.$toast.success(this.translations.label.token_registered, {
                                        // override the global option
                                        position: "top-right"
                                    });
                                }
                            })
                            .catch((e) => {
                                console.log(e);
                                this.$toast.error(this.translations.messages.an_error_occurred, {
                                    // override the global option
                                    position: "top-right"
                                });
                            });

                        const db = firebase.firestore();
                        db.settings({ timestampsInSnapshots: true });
                        db.collection("tokens").doc(token).set({
                            token: token
                        })
                            .catch(error => {
                                this.stateSubscription = false;
                                console.error(error);
                            });
                    } else {
                        console.log("Error al brindar el permiso para firebase");
                    }
                })
                .catch(error => {
                    this.loading = false;
                    console.error("No se pudo completar la suscripción a Firebase.", error);
                });
        },
        /*
        listTokens: function(){
            API({
                method: 'GET',
                url: 'user/notification/token'
            })
            .then((result) => {

            }).catch((e) => {
                console.log(e)
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('account.error.messages.name'),
                    text: this.$t('account.error.messages.connection_error')
                })
            })
        },
        */
        // PUSH NOTIFICATION..
        formatDate: function(starDate) {
            return moment(starDate).format("ddd D MMM");
        },
        eventoMenu: function() {
            if ($(".backdrop-banners").length > 0) {
                $(".dropdown").on("show.bs.dropdown", function(event) {
                    $(".backdrop-banners").css("display", "block").animate({
                        "opacity": 0
                    }, 20);
                });
                $(".dropdown").on("hide.bs.dropdown", function(event) {
                    $(".backdrop-banners").animate({
                        "opacity": 0
                    }, 10).fadeOut();
                });
            }
        },
        setLang: function() {
            axios.get(baseURL + "lang/" + this.lang)
                .then((result) => {
                    window.location.reload();
                }).catch((e) => {
                console.log(e);
            });
        },
        getClientsByExecutive: function() {
            axios.get("api/clients/selectBox/by/executive")
                .then((result) => {

                    if (result.data.success === true) {

                        this.clients = result.data.data;

                        if (localStorage.getItem("client_id") != "" && localStorage.getItem("client_id") != null) {

                            this.clients.forEach((element) => {

                                if (localStorage.getItem("client_id") == element.code) {
                                    this.clientSelect.push({
                                        code: element.code,
                                        label: element.label
                                    });
                                }
                            });
                        }
                    }
                }).catch((e) => {
                console.log(e);
            });
        },
        getDestiniesByClientIdOnChange: function(value) {

            if (document.getElementById("_overlay") != null) {
                document.getElementById("_overlay").style.display = "none";
            }

            console.log(value);
            this.client_id = value.code;
            this.client_code = value.client_code;

            if (localStorage.getItem("client_id") != "") {
                if (this.cart.hotels.length > 0) {
                    var result = confirm(this.translations.messages.if_you_change_customers);
                    if (result == true) {
                        this.destroyCart();
                        localStorage.setItem("client_id", this.client_id);
                        localStorage.setItem("client_code", this.client_code);
                        this.$root.$emit("updatedestiniesandclass");
                    } else {
                        this.client_id = localStorage.getItem("client_id");
                        if (this.route_name == "hotels") {
                            this.$root.$emit("updatedestiniesandclass");
                        }
                    }
                } else {
                    localStorage.setItem("client_id", this.client_id);
                    localStorage.setItem("client_code", this.client_code);
                    this.$root.$emit("updatedestiniesandclass");
                }
            } else {
                localStorage.setItem("client_id", this.client_id);
                localStorage.setItem("client_code", this.client_code);
            }

            this.$root.$emit("changeMarkup");
        },
        getCartContent: function() {
            axios.get(
                baseURL + "cart"
            )
                .then((result) => {
                    if (result.data.success === true) {

                        // + Prices of multiservices
                        result.data.cart.total_cart = parseFloat(result.data.cart.total_cart.replace(/[^\d\.\-eE+]/g, ""));
                        result.data.cart.services.forEach((s) => {
                            s.service.components.forEach((c) => {
                                s.total_service += c.total_amount;
                                result.data.cart.total_cart += c.total_amount;
                            });
                            s.total_service = this.roundLito(s.total_service);
                        });
                        // + Prices of multiservices

                        this.cart = result.data.cart;
                    }
                    // console.log('this.route_name ' + this.route_name);
                    // if (this.route_name == 'reservations.personal_data') {
                    //     this.getDashboardData()
                    // }}
                }).catch((e) => {
                console.log(e);
            });
            this.eventoMenu();
        },
        deleteCart: function(index_item) {
            axios.delete(
                baseURL + "cart/" + index_item
            )
                .then((result) => {
                    if (result.data.success === true) {
                        this.getCartContent();
                    }
                }).catch((e) => {
                console.log(e);
            });
        },
        destroyCart: function() {
            axios.delete(baseURL + "cart/content/delete")
                .then((result) => {

                    if (result.data.success) {
                        this.getCartContent();
                    }
                    console.log(result.data.data);
                }).catch((e) => {
                console.log(e);
            });
        },
        cancelRoomsCart: function(room, hotel) {
            axios.post(
                baseURL + "cart/cancel/rates", {
                    cart_items_id: room.cart_items_id
                }
            )
                .then((result) => {
                    if (result.data.success) {
                        if (this.route_name == "hotels") {
                            this.$root.$emit("cancelcartmodal", { room_id: room.room_id, hotel: hotel });
                        }
                        this.getCartContent();
                    }
                }).catch((e) => {
                console.log(e);
            });
        },
        cancelServiceCart: function(service) {
            console.log(service);
            axios.delete(baseURL + "cart/" + service.cart_items_id)
                .then((result) => {
                    if (result.data.success) {
                        // if (this.route_name == 'services') {
                        //   this.$root.$emit('cancelcartmodal', { room_id: room.room_id, hotel: hotel })
                        // }
                        this.getCartContent();
                    }
                }).catch((e) => {
                console.log(e);
            });
        },
        searchNotifications: function() {

            if (this.$root.$data._module != undefined) {
                axios.post(
                    baseURL + "notifications", {
                        time: "",
                        module: this.$root.$data._module
                    }
                )
                    .then((result) => {
                        console.log(result.data.notifications);
                        this.notifications = result.data.notifications;
                    })
                    .catch((e) => {
                        console.log(e);
                        if (e.message == "Unauthenticated.") {
                            window.location.reload();
                        }
                    });
            }
        },
        _showNotification: function(_module, _function, id, data) { // Cargando la notificación general
            this.updateNotification(id);

            try {
                eval("this.$root." + _function + "(" + id + ", " + JSON.stringify(data) + ")");
            } catch (error) {
                localStorage.setItem("_function", "this.$root." + _function + "(" + id + ", " + JSON.stringify(data) + ")");
                window.location.href = _module;
            }
        },
        updateNotification: function(_id) {
            axios.post(
                baseURL + "update_notification", {
                    id: _id
                }
            )
                .then((result) => {
                    console.log(result);
                })
                .catch((e) => {
                    console.log(e);
                    if (e.message == "Unauthenticated.") {
                        window.location.reload();
                    }
                });
        },
        filterUsers: function(search, loading) {
            axios.post(
                baseURL + "search_users", {
                    filter: search
                }
            )
                .then((result) => {
                    this.users = result.data.users;
                }).catch((e) => {
                console.log(e);
            });
        },
        saveReminder: function() {

            if (this.titleReminder == "") {
                this.$toast.error(this.translations.messages.enter_a_title_to_generate_the_reminder, {
                    // override the global option
                    position: "top-right"
                });
                return false;
            }

            if (this.messageReminder == "") {
                this.$toast.error(this.translations.message.enter_your_message, {
                    // override the global option
                    position: "top-right"
                });
                return false;
            }

            if (this.priorityReminder == "") {
                this.$toast.error(this.translations.messages.enter_a_priority, {
                    // override the global option
                    position: "top-right"
                });
                return false;
            }

            if (this.hourReminder == "") {
                this.$toast.error(this.translations.messages.enter_one_hour, {
                    // override the global option
                    position: "top-right"
                });
                return false;
            }

            axios.post(
                baseURL + "save_reminder", {
                    title: this.titleReminder,
                    fecini: this.feciniReminder,
                    fecfin: this.fecfinReminder,
                    users: this.usersReminder,
                    message: this.messageReminder,
                    type: this.typeReminder,
                    priority: this.priorityReminder,
                    hour: this.hourReminder
                }
            )
                .then((result) => {
                    this.all_reminders = result.data.all_reminders;
                    if (result.data.type == "success") {
                        this.titleReminder = "";
                        this.feciniReminder = "";
                        this.fecfinReminder = "";
                        this.usersReminder = [];
                        this.messageReminder = "";
                        this.typeReminder = 1;
                        this.priorityReminder = "";
                        this.hourReminder = "";

                        this.$toast.success(this.translations.messages.reminder_generated_successfully, {
                            // override the global option
                            position: "top-right"
                        });
                    } else {
                        this.$toast.error(result.data.message, {
                            // override the global option
                            position: "top-right"
                        });
                    }
                }).catch((e) => {
                console.log(e);
            });
        },
        showReminders: function() {
            this.modal_reminders = true;
            this.loadingModal = true;

            axios.post(
                baseURL + "search_reminders", {
                    lang: this.lang
                }
            )
                .then((result) => {
                    this.loadingModal = false;
                    this.reminders = result.data.reminders;
                    this.all_reminders = result.data.all_reminders;
                }).catch((e) => {
                console.log(e);
            });
        },
        pauseReminder: function(reminderID) {
            this.loadingModal = true;

            axios.post(
                baseURL + "pause_reminder", {
                    lang: this.lang,
                    reminder: reminderID
                }
            )
                .then((result) => {
                    this.showReminders();
                }).catch((e) => {
                console.log(e);
            });
        },
        playReminder: function(reminderID) {
            this.loadingModal = true;

            axios.post(
                baseURL + "play_reminder", {
                    lang: this.lang,
                    reminder: reminderID
                }
            )
                .then((result) => {
                    this.showReminders();
                }).catch((e) => {
                console.log(e);
            });
        },
        deleteReminder: function(reminderID) {
            this.loadingModal = true;

            axios.post(
                baseURL + "delete_reminder", {
                    lang: this.lang,
                    reminder: reminderID
                }
            )
                .then((result) => {
                    this.showReminders();
                }).catch((e) => {
                console.log(e);
            });
        },
        goCartDetails() {

            if (document.head.querySelector("[name=route_name]")) {
                let page = document.head.querySelector("[name=route_name]").content;
                if (["services", "hotels"].includes(page)) {
                    localStorage.setItem("page_return", document.head.querySelector("[name=route_name]").content);

                }
                document.location.href = "/cart_details/view";
            }

        },
        clearCart() {
            axios.post(
                baseURL + "cart/cancel/clear")
                .then((result) => {
                    if (result.data.success) {
                        localStorage.setItem("search_params", "");
                        let page = document.head.querySelector("[name=route_name]").content;
                        if (["cart_details", "reservations.personal_data"].includes(page)) {
                            document.location.href = "/hotels";
                        } else {
                            this.getCartContent();
                        }

                    }
                }).catch((e) => {
                console.log(e);
            });

        },
        goToA3(report) {
            if (report) {
                document.location.href = window.a3BaseUrl + "quotes/reports";
            } else {
                document.location.href = window.a3BaseUrl + "quotes";
            }

        },
        getClient() {
            axios.get(`${baseExternalURL}api/clients/${this.client_id}`)
                .then((response) => {
                    this.client = response.data.data;
                })
                .catch((error) => {
                    console.error("Error al obtener el cliente:", error);
                });
        },
        getPrice(price) {
            if (
                this.client &&
                this.client.commission_status == 1 &&
                parseFloat(this.client.commission) > 0 &&
                this.user_type_id == 4
            ) {
                let commissionRate = parseFloat(this.client.commission) / 100;
                let priceWithCommission = price * (1 + commissionRate);

                // Usar roundLito para redondear
                let rounded = this.roundLito(priceWithCommission);

                return rounded;
            }

            return price;
        }

    },
    filters: {
        reformatDate: function(_date) {
            if (_date == undefined) {
                return;
            }
            _date = moment(_date).format("ddd D MMM YYYY");
            return _date;
        }
    }
};
</script>
