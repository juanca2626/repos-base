<template>
    <table-server
        :columns="table.columns"
        :options="tableOptions"
        :url="urlGroups"
        ref="table"
    >
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit
                :id="props.row.id"
                :name="props.row.translations[0].value"
                :options="menuOptions"
                @remove="remove(props.row.id)"
            />
        </div>
        <div class="table-group" slot="group" slot-scope="props">
            {{ props.row.translations[0].value }}
        </div>
    </table-server>
</template>

<script>
import TableServer from "../../../components/TableServer";
import { API } from "./../../../api";
import MenuEdit from "./../../../components/MenuEdit";

export default {
    components: {
        "table-server": TableServer,
        "menu-edit": MenuEdit,
    },
    data: () => {
        return {
            urlGroups:
                "/api/packages/groups/?token=" +
                window.localStorage.getItem("access_token") +
                "&lang=" +
                localStorage.getItem("lang"),
            table: {
                columns: ["id", "group", "actions"],
            },
        };
    },
    mounted() {
        this.$i18n.locale = localStorage.getItem("lang");
    },
    created() {
        this.$parent.$parent.$on("langChange", (payload) => {
            this.onUpdate();
        });
    },
    updated() {
        this.$root.$emit("updateTitlePackage");
    },
    computed: {
        menuOptions: function () {
            let options = [];

            //if (this.$can('update', 'groups')) {
            options.push({
                type: "edit",
                text: "",
                link: "packages/groups/edit/",
                icon: "dot-circle",
                callback: "",
                type_action: "link",
            });
            options.push({
                type: "edit",
                text: "Categorias",
                link: "packages/groups/tags/",
                icon: "dot-circle",
                callback: "",
                type_action: "link",
            });
            //}
            //if (this.$can('delete', 'groups')) {
            options.push({
                type: "delete",
                text: "",
                link: "",
                icon: "trash",
                type_action: "button",
                callback_delete: "remove",
            });
            //}
            return options;
        },
        tableOptions: function () {
            return {
                headings: {
                    id: "#",
                    group: this.$i18n.t("groups.name"),
                    actions: this.$i18n.t("global.table.actions"),
                },
                sortable: [],
                filterable: [],
            };
        },
    },
    methods: {
        onUpdate() {
            this.urlGroups =
                "/api/packages/groups?token=" +
                window.localStorage.getItem("access_token") +
                "&lang=" +
                localStorage.getItem("lang");
            this.$refs.table.$refs.tableserver.refresh();
        },
        remove(id) {
            API({
                method: "DELETE",
                url: "/packages/groups/" + id,
            }).then((result) => {
                if (result.data.success === true) {
                    this.onUpdate();
                } else {
                    this.$notify({
                        group: "main",
                        type: "error",
                        title: this.$t("global.modules.groups"),
                        text: this.$t("zones.error.messages.groups_delete"),
                    });
                    this.loading = false;
                }
            });
        },
    },
};
</script>

<style lang="stylus">
.table-actions
    display flex
</style>
