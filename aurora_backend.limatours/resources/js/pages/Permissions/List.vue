<template>
  <table-client :columns="table.columns" :data="users" :loading="loading" :options="tableOptions" id="dataTable"
                theme="bootstrap4">
      <div class="table-actions" slot="actions" slot-scope="props">
          <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                     @remove="remove(props.row.id)"/>
      </div>
  </table-client>
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
    data: () => {
      return {
        loading: false,
        users: [],
        table: {
          columns: ['actions', 'id', 'name', 'slug', 'description']
        },
      }
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'permissions')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'permissions/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'permissions')) {
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
            name: this.$i18n.t('global.name'),
            slug: 'Slug',
            description: this.$i18n.t('permissions.description'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id','name','slug','description'],
          filterable: ['id','name','slug','description']
        }
      }
    },
    mounted () {
      this.fetchData()
    },
    methods: {
      remove (id) {
        API({
          method: 'DELETE',
          url: 'permissions/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Permisos',
                text: result.data.message
              })
            }
          })
      },
      fetchData: function () {
        API.get('permissions').then((result) => {
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

