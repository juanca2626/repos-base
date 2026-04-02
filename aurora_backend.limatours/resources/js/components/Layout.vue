<template>
    <div>
        <!--    <div v-if="environment != 'production'" class="alert alert-warning mb-0 text-center p-3" role="alert"-->
        <!--         style="position: sticky;top: 0;z-index: 1020;">-->
        <!--      <i class="fas fa-exclamation-triangle"></i>-->
        <!--      <span v-if="currentLanguage === 'es'">-->
        <!--        <b>Estás accediendo a un entorno de prueba. </b> Ten en cuenta que cualquier dato que ingreses aquí se guardará-->
        <!--        y solo se utilizará con fines de pruebas.-->
        <!--      </span>-->
        <!--      <span v-if="currentLanguage === 'en'">-->
        <!--        <b>You are accessing a test environment.</b> Please note that any data you enter here will be saved and used for-->
        <!--        testing purposes only.-->
        <!--      </span>-->
        <!--      <span v-if="currentLanguage === 'pt'">-->
        <!--        <b>Você está acessando um ambiente de teste.</b> Observe que todos os dados inseridos aqui serão salvos e usados apenas para fins de teste.-->
        <!--      </span>-->
        <!--    </div>-->

        <div class="app">
            <header class="app-header navbar">
                <a class="navbar-brand" href="/#/" target="_self">
                    <img
                        alt="LimaTours"
                        class="navbar-brand-full"
                        height="55"
                        src="/images/logo.png"
                        width="200"
                    />
                </a>

                <div
                    v-if="environment != 'production'"
                    style="position: absolute; left: 13%; font-size: 12px"
                    class="alert alert-info mb-0"
                >
                    <span v-if="currentLanguage === 'es'">
                        <b
                            ><i class="fas fa-info-circle"></i> Estás accediendo
                            a un entorno de prueba.
                        </b>
                        Ten en cuenta que cualquier dato que ingreses aquí se
                        guardará y solo se utilizará con fines de pruebas.
                    </span>
                    <span v-if="currentLanguage === 'en'">
                        <b
                            ><i class="fas fa-exclamation-triangle"></i> You are
                            accessing a test environment.</b
                        >
                        Please note that any data you enter here will be saved
                        and used for testing purposes only.
                    </span>
                    <span v-if="currentLanguage === 'pt'">
                        <b
                            ><i class="fas fa-exclamation-triangle"></i> Você
                            está acessando um ambiente de teste.</b
                        >
                        Observe que todos os dados inseridos aqui serão salvos e
                        usados apenas para fins de teste.
                    </span>
                </div>

                <ul class="navbar-nav">
                    <li class="nav-item px-3">
                        <router-link
                            :to="{ name: 'Dashboard' }"
                            class="nav-link"
                            >Dashboard</router-link
                        >
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item px-3">
                        <select @change="setLanguage" v-model="currentLanguage">
                            <option
                                :selected="currentLanguage === language.iso"
                                v-bind:value="language.iso"
                                v-for="language in languages"
                            >
                                {{ language.iso }}
                            </option>
                        </select>
                    </li>
                    <li class="nav-item px-3">
                        <router-link :to="{ name: 'Profile' }" class="nav-link">
                            <font-awesome-icon icon="user" />
                            {{ user.name }}
                        </router-link>
                    </li>
                    <li class="nav-item px-3">
                        <button
                            @click="logout"
                            class="btn btn-link"
                            type="reset"
                        >
                            <font-awesome-icon icon="power-off" />
                            Logout
                        </button>
                    </li>
                </ul>
            </header>
            <div class="app-body">
                <AppSidebar fixed>
                    <SidebarHeader />
                    <SidebarForm />
                    <SidebarNav :navItems="nav"></SidebarNav>
                    <SidebarFooter />
                    <SidebarMinimizer />
                </AppSidebar>
                <main class="main">
                    <breadcrumbs />
                    <div class="container-fluid">
                        <transition mode="out-in" name="fade">
                            <router-view :key="isReady"></router-view>
                        </transition>
                    </div>
                </main>
            </div>
            <footer class="app-footer bg-dark">
                <div>
                    <span>
                        Av. Juan de Arona 755 Piso 11, San Isidro 15046 -
                        Oficinas de Spaces - Perú <br />
                        Razón Social: LimaTours SAC | RUC: 20536830376
                    </span>
                </div>
                <div class="ml-auto">
                    Copyright © {{ year }} - All rights reserved
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import nav from "../nav";
import { API } from "./../api";
import { removeToken } from "../auth";

import {
    Sidebar as AppSidebar,
    Sidebar,
    SidebarFooter,
    SidebarForm,
    SidebarHeader,
    SidebarMinimizer,
    SidebarNav,
    SidebarNavDropdown,
    SidebarNavItem,
    SidebarNavLink,
    SidebarNavTitle,
    SidebarToggler,
} from "@coreui/vue";
import { getUser } from "../auth";

let intervalToken = "";

export default {
    components: {
        AppSidebar,
        Sidebar,
        SidebarHeader,
        SidebarNav,
        SidebarNavDropdown,
        SidebarNavItem,
        SidebarToggler,
        SidebarNavTitle,
        SidebarNavLink,
        SidebarForm,
        SidebarMinimizer,
        SidebarFooter,
    },
    data: () => {
        return {
            languages: [],
            environment: "production",
            currentLanguage: window.localStorage.getItem("lang"),
            nav: [],
            isReady: 0,
            user: {
                name: "",
            },
            year: new Date().getFullYear(),
        };
    },
    created() {
        this.environment = document
            .querySelector("meta[name='environment']")
            .getAttribute("content");
        this.$root.$on("roles_permissions_update", () => {
            this.getData();
        });
    },
    mounted() {
        this.getData();
        this.date = new Date().getFullYear();
        API.get("/languages/").then((result) => {
            this.languages = result.data.data;
        });

        intervalToken = setInterval(() => {
            API.get("/user/refresh").then((response) => {
                window.localStorage.setItem(
                    "access_token",
                    response.data.access_token
                );
            });
        }, 3500000);
    },
    methods: {
        getData() {
            getUser().then((result) => {
                this.user = result.data;
                let client_id = this.user.client_seller
                    ? this.user.client_seller.client_id
                    : "";

                if(client_id == null || client_id == 'null')
                {
                    client_id = '';
                }

                console.log("Client ID: ", client_id)
                window.localStorage.setItem("client_id", client_id);

                let menu = (toCheck, permissions) => {
                    let tmpNav = [];
                    let counter = 0;
                    let keys = Object.keys(permissions);

                    toCheck.forEach((item) => {
                        // Add token for solutionsdesk
                        if (item.url && item.url.includes("solutionsdesk")) {
                            const email_64 = Buffer.from(
                                this.user.email
                            ).toString("base64");
                            item.url +=
                                "?organization=92198113-85da-37f4-8f2f-9874e8c4387c" +
                                "&access_token=a079f04cc48dea32751881ccbdf996b4" +
                                "&client_email=" +
                                email_64;
                        }

                        // Redireccionar URLs de accountancy
                        if (item.url && item.url.startsWith("/a3/")) {
                            const baseUrl = this.environment === 'production'
                                ? 'https://a3.limatours.com.pe'
                                : 'https://a3.limatours.dev';
                            item.url = baseUrl + item.url.replace("/a3/", "/");
                        }

                        if (item.url && item.url.startsWith("/series/")) {
                            const baseUrl = this.environment === 'production'
                                ? 'https://a3.limatours.com.pe'
                                : 'https://a3.limatours.dev';
                            item.url = baseUrl + item.url;
                        }

                        const hasPermission =
                            !item.permission ||
                            (keys.includes(item.permission) &&
                                permissions[item.permission].includes("read"));
                        if (hasPermission) {
                            tmpNav.push(item);
                            counter++;
                        }
                        if (item.children) {
                            tmpNav[counter - 1].children = menu(
                                item.children,
                                permissions
                            );
                        }
                    });

                    tmpNav.forEach((item, key) => {
                        if (item.children && item.children.length === 0) {
                            tmpNav.splice(key, 1);
                        }
                    });

                    return tmpNav;
                };
                let permissions = [];

                for (let key of Object.keys(result.data.permissions)) {
                    permissions.push({
                        subject: key,
                        actions: result.data.permissions[key],
                    });
                }

                this.$ability.update(permissions);

                this.nav = menu(nav.items, result.data.permissions);

                this.isReady = 1;
            });
        },
        logout() {
            if (window.localStorage) {
                window.localStorage.setItem("user", null);
                window.localStorage.setItem("access_token", null);
                window.localStorage.removeItem("user");
                window.localStorage.removeItem("access_token");
                removeToken();
            }
            clearInterval(intervalToken);

            this.$router.push("/login");
        },
        setLanguage() {
            if (this.currentLanguage) {
                window.localStorage.setItem("lang", this.currentLanguage);

                this.$i18n.locale = this.currentLanguage;

                this.$emit("langChange", { lang: this.currentLanguage });
            }
        },
    },
};
</script>

<style lang="stylus">
.fade-enter-active,
.fade-leave-active
  transition opacity .2s

.fade-enter,
.fade-leave-to
  opacity 0

.app-header
  .nav-item
    a.nav-link.router-link-exact-active
      color #2f353a

    select
      background none
      border-color #73818f
      color #73818f
      height 30px
      width 50px

.sidebar
  .nav-item
    a.nav-link.router-link-exact-active
      background-color #3a4248

      svg
        color #20a8d8
</style>
