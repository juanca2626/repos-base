<template>
    <table-server :columns="table.columns" :options="tableOptions" :url="urlStates" ref="table">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-country" slot="country" slot-scope="props">
            {{props.row.country.translations[0].value}}
        </div>
        <div class="table-state" slot="state" slot-scope="props">
            {{props.row.translations[0].value}}
        </div>
    </table-server>
</template>

<script>

    import TableServer from '../../components/TableServer';
    import MenuEdit from './../../components/MenuEdit';
    import {API} from './../../api';

    export default {
        components: {
            'table-server': TableServer,
            'menu-edit': MenuEdit,
        },
        data: () => {
            return {
                states: [],
                urlStates: '/api/states?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang'),
                table: {
                    columns: ['actions', 'id', 'country', 'state', 'iso'],
                },
            };
        },
        mounted() {
            this.$i18n.locale = localStorage.getItem('lang');
        },
        created() {
            this.$parent.$parent.$on('langChange', (payload) => {
                this.onUpdate();
            });
        },
        computed: {
            menuOptions: function() {

                let options = [];

                if (this.$can('update', 'states')) {
                    options.push({
                        type: 'edit',
                        text: '',
                        link: 'states/edit/',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'link',
                    });
                }
                if (this.$can('create', 'states')) {
                    options.push({
                        type: 'manage',
                        text: '',
                        link: '/states/',
                        link2: '/manage_state',
                        icon: 'dot-circle',
                        callback: '',
                        type_action: 'manageLink',
                    });
                }
                if (this.$can('delete', 'states')) {
                    options.push({
                        type: 'delete',
                        text: '',
                        link: '',
                        icon: 'trash',
                        type_action: 'button',
                        callback_delete: 'remove',
                    });
                }
                return options;
            },
            tableOptions: function() {
                return {
                    headings: {
                        id: 'ID',
                        country: this.$i18n.t('states.country_name'),
                        state: this.$i18n.t('states.state_name'),
                        iso: 'ISO',
                        actions: this.$i18n.t('global.table.actions'),
                    },
                    sortable: ['id'],
                    filterable: ['id', 'state'],
                };
            },
        },
        methods: {
            onUpdate() {
                this.urlStates = '/api/states?token=' + window.localStorage.getItem('access_token') + '&lang=' +
                    localStorage.getItem('lang');
                this.$refs.table.$refs.tableserver.refresh();
            },
            remove(id) {
                API({
                    method: 'DELETE',
                    url: 'states/' + id,
                }).then((result) => {
                    if (result.data.success === true) {
                        this.onUpdate();
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.states'),
                            text: this.$t('states.error.messages.district_delete'),
                        });
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('states.error.messages.name'),
                        text: this.$t('states.error.messages.connection_error'),
                    });
                });
            },

        },
    };
</script>

<style lang="stylus">
    .table-actions
        display flex
</style>

