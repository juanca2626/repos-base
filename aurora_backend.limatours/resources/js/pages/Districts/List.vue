<template>
    <table-server :columns="table.columns" :options="tableOptions" :url="urlDistricts" ref="table">
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
  import MenuEdit from './../../components/MenuEdit'
  import { API } from './../../api'

  export default {
    components: {
      'table-server': TableServer,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        states: [],
        urlDistricts: '/api/districts?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['actions', 'id', 'ubigeo','iso']
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

        if (this.$can('update', 'districts')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'districts/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'districts')) {
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
            ubigeo: this.$i18n.t('districts.ubigeo'),
            iso: this.$i18n.t('districts.district_iso'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','ubigeo']
        }
      }
    },
    methods: {
      onUpdate () {
        this.urlDistricts = '/api/districts?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'districts/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.districts'),
                // text: this.$t('districts.error.messages.district_delete')
                text: (result.data.message) ? result.data.message : this.$t('districts.error.messages.district_delete')
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

