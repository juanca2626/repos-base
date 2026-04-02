<template>
    <table-server
        :columns="table.columns"
        :options="tableOptions"
        :url="urlTags"
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
        <div class="table-color" slot="color" slot-scope="props">
            <button
                type="button"
                class="btn"
                :style="{ 'background-color': '#' + props.row.color }"
            >
                {{ props.row.color }}
            </button>
        </div>
        <div class="table-tag" slot="tag" slot-scope="props">
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
            group_id: null,
            urlTags: "",
            table: {
                columns: ["id", "tag", "color", "actions"],
            },
        };
    },
    mounted() {
        this.$i18n.locale = localStorage.getItem("lang");
        this.onUpdate();
    },
    created() {
        this.$parent.$parent.$on("langChange", (payload) => {
            this.onUpdate();
        });
        // localStorage.setItem('group_id', this.$route.params.group_id)
    },
    updated() {
        this.$root.$emit("updateTitlePackage");
    },
    computed: {
        menuOptions: function () {
            let options = [];

            options.push({
                type: "edit",
                text: "",
                link:
                    "packages/groups/tags/" +
                    this.$route.params.group_id +
                    "/edit/",
                icon: "dot-circle",
                callback: "",
                type_action: "link",
            });

            options.push({
                type: "delete",
                text: "",
                link: "",
                icon: "trash",
                type_action: "button",
                callback_delete: "remove",
            });
            return options;
        },
        tableOptions: function () {
            return {
                headings: {
                    id: "#",
                    color: "Color",
                    tag: this.$i18n.t("tags.name"),
                    actions: this.$i18n.t("global.table.actions"),
                },
                sortable: [],
                filterable: [],
                perPageValues: [],
                responseAdapter({ data }) {
                    return {
                        data: data.data,
                        count: data.count,
                    };
                },
                requestFunction: function (data) {
                    let url =
                        "/packages/groups/" +
                        this.$route.params.group_id +
                        "/tags?token=" +
                        window.localStorage.getItem("access_token") +
                        "&lang=" +
                        localStorage.getItem("lang");
                    return API.get(url, {
                        params: data,
                    }).catch(
                        function (e) {
                            this.dispatch("error", e);
                        }.bind(this)
                    );
                },
            };
        },
    },
    methods: {
        onUpdate() {
            this.urlTags =
                "/api/packages/groups/" +
                localStorage.getItem("group_id") +
                "/tags?token=" +
                window.localStorage.getItem("access_token") +
                "&lang=" +
                localStorage.getItem("lang");
            this.$refs.table.$refs.tableserver.refresh();
        },
        remove(id) {
            API({
                method: "DELETE",
                url:
                    "/packages/groups/" +
                    this.$route.params.group_id +
                    "/tags/" +
                    id,
            }).then((result) => {
                if (result.data.success === true) {
                    this.onUpdate();
                    this.loading = false;
                } else {
                    if (result.data.used === true) {
                        this.$notify({
                            group: "main",
                            type: "error",
                            title: this.$t("global.modules.tags"),
                            text: "El tag está en uso y no puede ser eliminado",
                        });
                    } else {
                        this.$notify({
                            group: "main",
                            type: "error",
                            title: this.$t("global.modules.tags"),
                            text: this.$t("zones.error.messages.groups_delete"),
                        });
                    }
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
