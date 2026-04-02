<template>
    <div class="container-fluid">
        <div class="row justify-content-end" style="padding-bottom: 0;">
            <div class="">
                <div class="b-form-group form-group mr-3">
                    <div class="form-row">
                        <label class="col-6 col-form-label" for="searchStatus">{{
                            $t('users.search.messages.user_code_name_search') }}</label>
                        <div class="col-4">
                            <input :class="{'form-control':true }"
                                   id="target" name="target"
                                   type="text"
                                   ref="auroraCodeName" v-model="target">

                        </div>

                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <table-client :columns="table.columns" :data="users" :loading="loading" :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props">
                <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                           @remove="remove(props.row.id)" />
            </div>
            <div class="table-roles" slot="roles" slot-scope="props">
                {{ props.row.roles[0].name }}
            </div>
            <div class="table-types" slot="type" slot-scope="props">
                {{ props.row.user_type.description }}
            </div>
            <div class="table-loading text-center" slot="loading">
                <img alt="loading" height="51px" src="/images/loading.svg" />
            </div>

            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>


        </table-client>
    </div>
</template>

<script>
  import { API } from './../../api'
  import TableClient from './../../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit
    },
    watch: {
      target () {
        this.fetchData()
      }
    },
    data: () => {
      return {
        loading: false,
        users: [],
        searchUserType: '',
        target: '',
        table: {
          columns: ['actions', 'id', 'code', 'name', 'email', 'roles', 'type', 'status'],
        },
        userTypes: [],
      }
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'suppliers')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'suppliers/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'suppliers')) {
          options.push({
            type: 'delete',
            text: '',
            link: '',
            icon: 'trash',
            type_action: 'button',
            callback_delete: 'remove'
          })
        }
        return options
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            code: 'Code',
            name: this.$i18n.t('global.name'),
            email: this.$i18n.t('users.mail'),
            roles: this.$i18n.t('users.roles'),
            type: this.$i18n.t('users.user_type'),
            actions: this.$i18n.t('users.actions'),
            status: this.$i18n.t('global.status'),
          },
          // 'id'
          sortable: [],
          filterable: [],
          perPageValues: []
          // 'id', 'name'
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
      this.fetchData()
      // this.getUserTypes()
    },
    methods: {
      search: function () {
        this.fetchData()
      },
      checkboxChecked: function (status) {
        return !!status
      },
      changeState: function (user_id, status) {
        API({
          method: 'put',
          url: 'suppliers/' + user_id + '/status',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.clients'),
                text: this.$t('clients.error.messages.information_error')
              })
            }
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'suppliers/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$i18n.t('users.title'),
                text: result.data.message
              })

            }
          })
          .catch((e) => {

            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$i18n.t('users.title'),
              text: e.data.message
            })
          })
      },
      getUserTypes: function () {
        API.get('/usertypes/selectBox')
          .then((result) => {
            this.userTypes = result.data.data
          })
      },
      fetchData: function () {
        API.get('suppliers?search=' + this.target + '&typeUser=' + this.searchUserType)
          .then((result) => {
            if (result.data.success === true) {
              this.users = result.data.data
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Fetch Error',
                text: result.data.message
              })
            }
          })
      }

    }
  }
</script>

<style lang="stylus">
    .table-actions
        display flex
</style>

