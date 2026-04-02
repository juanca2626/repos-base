<template>
    <table-client :columns="table.columns" :data="taxes" :options="tableOptions" id="dataTable" theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
    </table-client>
</template>

<script>
  import { API } from '../../../api'
  import TableClient from '../../../components/TableClient'
  import MenuEdit from './../../../components/MenuEdit'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        taxes: [],
        table: {
          columns: ['id', 'name', 'value', 'actions']
        }
      }
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'taxes')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'countries/taxes/' + this.$route.params.country_id + '/taxes/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'taxes')) {
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
            name: this.$i18n.t('taxes.tax_name'),
            value: this.$i18n.t('taxes.tax_value'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: []
        }
      },

    },
    mounted () {
      this.fetchData()
    },
    methods: {
      fetchData: function () {
        let country_id = this.$route.params.country_id
        API.get('taxes?lang=' + window.localStorage.getItem('lang') + '&type=t' + '&country_id=' + country_id)
          .then((result) => {
            if (result.data.success === true) {
              this.taxes = result.data.data
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Fetch Error',
                text: result.data.message
              })
            }
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'taxes/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Impuestos',
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

