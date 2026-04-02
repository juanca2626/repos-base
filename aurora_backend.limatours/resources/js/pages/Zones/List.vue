<template>
    <table-server :columns="table.columns" :options="tableOptions" :url="urlZones" ref="table">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-ubigeo" slot="ubigeo" slot-scope="props" style="font-size: 0.9em">
            {{props.row.city.state.country.translations[0].value}} / {{props.row.city.state.translations[0].value}} /
            {{props.row.city.translations[0].value}} / <b style="font-size: 0.875rem">{{props.row.translations[0].value}}</b>
        </div>
    </table-server>
</template>

<script>
  import TableServer from '../../components/TableServer'
  import { API } from './../../api'
  import MenuEdit from './../../components/MenuEdit'

  export default {
    components: {
      'table-server': TableServer,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        urlZones: '/api/zones?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['actions', 'id', 'ubigeo']
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'zones')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'zones/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'zones')) {
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
            ubigeo: this.$i18n.t('zones.ubigeo'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','ubigeo']
        }
      }
    },
    methods: {
      onUpdate () {
        this.urlZones = '/api/zones?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'zones/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.zones'),
                text: (result.data.message) ? result.data.message : this.$t('zones.error.messages.zone_delete')

              })

              this.loading = false
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

