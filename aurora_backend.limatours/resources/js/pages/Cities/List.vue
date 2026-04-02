<template>
    <table-server :columns="table.columns" :options="tableOptions" :url="urlCities" ref="table">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-state" slot="state" slot-scope="props">
            {{props.row.state.translations[0].value}}
        </div>
        <div class="table-city" slot="city" slot-scope="props">
            {{props.row.translations[0].value}}
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
        urlCities: '/api/cities?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['actions','id', 'state', 'city' , 'iso']
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

        if (this.$can('update', 'cities')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'cities/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'cities')) {
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
            state: this.$i18n.t('cities.state_name'),
            city: this.$i18n.t('cities.city_name'),
            iso: this.$i18n.t('cities.city_iso'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','city']
        }
      }
    },
    methods: {
      onUpdate () {
        this.urlCities = '/api/cities?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove (id) {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'cities/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.states'),
                // text: this.$t('cities.error.messages.state_delete')
                text: (result.data.message) ? result.data.message : this.$t('cities.error.messages.state_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('cities.error.messages.name'),
            text: this.$t('cities.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    .table-actions
        display flex
</style>

