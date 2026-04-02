<template>
    <div class="card">
        <div :class="'card-header ' + classCard">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            <span v-html="title"></span>

            <div class="card-header-actions mr-2" v-if="showAdd">
                <router-link :to="{ name: 'PackagesAdd' }">
                    <font-awesome-icon
                        :icon="['fas', 'plus']"
                        class="nav-icon"
                    />
                    {{ $t("global.buttons.add") }}
                </router-link>
            </div>

            <div class="card-header-actions mr-2" v-if="showAddQuotes">
                <router-link
                    :to="{ name: 'PackageCostQuotesForm' }"
                    class="btn btn-primary"
                >
                    <font-awesome-icon
                        :icon="['fas', 'plus']"
                        class="nav-icon"
                    />
                    {{ $t("global.buttons.add") }}
                </router-link>
            </div>
            <div class="card-header-actions mr-2" v-if="showAddGroups">
                <router-link :to="{ name: 'PackageGroupsFormCreate' }">
                    <font-awesome-icon
                        :icon="['fas', 'plus']"
                        class="nav-icon"
                    />
                    {{ $t("global.buttons.add") }}
                </router-link>
            </div>
            <div class="card-header-actions mr-2" v-if="showAddTags">
                <router-link :to="{ name: 'TagsFormAdd' }">
                    <font-awesome-icon
                        :icon="['fas', 'plus']"
                        class="nav-icon"
                    />
                    {{ $t("global.buttons.add") }}
                </router-link>
            </div>
            <div class="card-header-actions mr-2" v-if="showAddPermissions">
                <router-link :to="{ name: 'PackagePermissionsForm' }">
                    <font-awesome-icon
                        :icon="['fas', 'plus']"
                        class="nav-icon"
                    />
                    {{ $t("global.buttons.add") }}
                </router-link>
            </div>
            <!-- div class="card-header-actions">
                <i class="fas fa-grip-lines-vertical"></i>
            </div -->
            <!--start Editar y administrar-->
            <div class="card-header-actions mr-2" v-if="showEdit">
                <button
                    type="button"
                    @click="backEditPackage"
                    class="btn btn-primary"
                >
                    <font-awesome-icon :icon="['fas', 'edit']" />
                    {{ $t("global.table.edit") }} {{ $t("packages.package") }}
                </button>
            </div>
            <div class="card-header-actions mr-2">
                <button
                    class="btn btn-primary"
                    @click="goAdmin()"
                    v-if="showManage"
                >
                    <font-awesome-icon
                        :icon="['fas', 'edit']"
                        class="nav-icon"
                    />
                    {{ $t("packages.manage_package") }}
                </button>
            </div>
            <div class="card-header-actions mr-2">
                <button
                    class="btn btn-primary"
                    @click="goQuote()"
                    v-if="showQuote"
                >
                    <font-awesome-icon
                        :icon="['fas', 'edit']"
                        class="nav-icon"
                    />
                    {{ $t("packages.quotes") }}
                </button>
            </div>
            <!--end Editar y administrar-->
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            title: "",
            classCard: "",
            package: "",
            adminpackage: "",
        };
    },
    created: function () {
        this.package = this.$i18n.t("packages.title");
        this.adminpackage = this.$i18n.t("packages.manage_package");
        this.adminquotes = "Administrar Cotizaciones";
        this.editpackage = "Editar Paquete";

        this.$root.$on("updateTitlePackage", (payload) => {
            this.showTitle();
        });
    },
    computed: {
        showEdit() {
            return (
                (this.$route.params.package_id > 0 ||
                    this.$route.params.id > 0) &&
                this.$route.name !== "PackagesEdit" &&
                this.$route.name !== "PackageGroupsFormCreate" &&
                this.$route.name !== "PackagesList" &&
                this.$route.name !== "Tags" &&
                this.$route.name !== "TagsForm" &&
                this.$route.name !== "PackageGroups" &&
                this.$route.name !== "PackageGroupsFormUpdate" &&
                this.$ability.can("update", "packages")
            );
        },
        showQuote() {
            return (
                (this.$route.params.package_id > 0 ||
                    this.$route.params.id > 0) &&
                this.$route.name !== "PackagesList" &&
                this.$route.name !== "PackageGroupsFormCreate" &&
                this.$route.name !== "PackageCostQuotes" &&
                this.$route.name !== "Tags" &&
                this.$route.name !== "PackageSaleQuotes" &&
                this.$route.name !== "TagsForm" &&
                this.$route.name !== "PackageGroups" &&
                this.$route.name !== "PackageGroupsFormUpdate" &&
                this.$route.name !== "PackageBlockingQuotes" &&
                this.$route.name !== "PackageCostQuotesForm" &&
                this.$route.name !== "PackageCostQuoteServicesAndHotels" &&
                this.$route.name !== "PackageCostQuoteRates" &&
                this.$route.name !== "PackageCostQuoteOptional" &&
                this.$route.name !== "PackageCostQuoteAddService" &&
                this.$route.name !== "PackageCostQuoteAddHotel" &&
                this.$ability.can("update", "packages")
            );
        },
        showManage() {
            return (
                (this.$route.params.package_id > 0 ||
                    this.$route.params.id > 0) &&
                this.$route.name !== "PackageTextsForm" &&
                this.$route.name !== "PackageGroupsFormCreate" &&
                this.$route.name !== "PackageConfigurationsLayout" &&
                this.$route.name !== "ExtensionsList" &&
                this.$route.name !== "PackagesList" &&
                this.$route.name !== "Tags" &&
                this.$route.name !== "TagsForm" &&
                this.$route.name !== "PackageGroupsFormUpdate" &&
                this.$route.name !== "PackageGalleryManageList" &&
                this.$route.name !== "FixedOutputsList" &&
                this.$route.name !== "PackageInclusions" &&
                this.$route.name !== "PackageGroups" &&
                this.$route.name !== "PackageHighlights" &&
                this.$ability.can("update", "packages")
            );
        },
        showAdd() {
            return (
                this.$route.name === "PackagesList" &&
                this.$can("create", "packages")
            );
        },
        showAddQuotes() {
            return (
                (this.$route.params.package_id > 0 ||
                    this.$route.params.id > 0) &&
                this.$route.name === "PackageCostQuotes" &&
                this.$can("create", "packages")
            );
        },
        showAddGroups() {
            return this.$route.name === "PackageGroups";
        },
        showAddTags() {
            return this.$route.name === "Tags";
        },
        showAddPermissions() {
            return (
                this.$route.name === "PackagePermissionsList" &&
                this.$can("create", "packagepermissions")
            );
        },
    },
    mounted: function () {
        this.$i18n.locale = localStorage.getItem("lang");
        this.showTitle();
    },
    methods: {
        backEditPackage() {
            this.$router.push(
                "/packages/edit/" + this.$route.params.package_id
            );
        },
        showTitle: function () {
            console.log("Package: ", localStorage.getItem('packagenamemanage'))

            if (this.$route.path.indexOf("manage_package") !== -1) {
                this.title =
                    this.adminpackage +
                    " : " +
                    localStorage.getItem("packagenamemanage");
                this.verifyBackClass();
            } else if (this.$route.path.indexOf("quotes") !== -1) {
                this.title =
                    this.adminquotes +
                    " : " +
                    localStorage.getItem("packagenamemanage");
                this.verifyBackClass();
            } else if (this.$route.path.indexOf("edit") !== -1) {
                this.title =
                    this.editpackage +
                    " : " +
                    localStorage.getItem("packagenamemanage");
                this.verifyBackClass();
            } else if (this.$route.name === "PackageGroups") {
                this.title = this.$t("packages.interests_title");
            } else if (this.$route.name === "Tags") {
                this.title = this.$t("packages.categories_title");
            } else {
                this.title = this.package;
                this.classCard = "";
            }
        },
        verifyBackClass() {
            if (localStorage.getItem("package_extension") == 1) {
                this.classCard = "trExtension";
            } else {
                this.classCard = "";
            }
        },
        goEdit() {
            this.package_id = this.$route.params.package_id;
            // this.getNameService(this.package_id)
            this.$router.push("/packages/edit/" + this.package_id);
        },
        goAdmin() {
            this.package_id =
                this.$route.params.package_id === undefined
                    ? this.$route.params.id
                    : this.$route.params.package_id;
            // this.getNameService(this.package_id)
            this.$router.push(
                "/packages/" + this.package_id + "/manage_package"
            );
        },
        goQuote() {
            this.package_id =
                this.$route.params.package_id === undefined
                    ? this.$route.params.id
                    : this.$route.params.package_id;
            this.$router.push("/packages/" + this.package_id + "/quotes");
        },
    },
};
</script>

<style lang="stylus"></style>
