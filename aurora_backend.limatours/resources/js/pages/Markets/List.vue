<template>
    <table-client :columns="table.columns" :data="markets" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-name" slot="name" slot-scope="props">
            {{ props.row.name }}
        </div>
        <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
            <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeStatus(props.row.id,props.row.status)"
                    switch>
            </b-form-checkbox>
        </div>
        <div class="table-loading text-center" slot="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </table-client>
</template>

<script>
  import { API } from './../../api'
  import TableClient from './../../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      BFormCheckbox
    },
    data: () => {
      return {
        loading: false,
        markets: [],
        table: {
          columns: ['actions', 'id', 'name', 'status']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'markets')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'markets/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'markets')) {
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
            status: this.$i18n.t('markets.status.title'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','name']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      checkboxChecked: function (market_status) {
        if (market_status) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeStatus: function (market_id, status) {
        API({
          method: 'put',
          url: 'market/update/' + market_id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('markets.error.messages.information_error')
              })
            }
          })
      },
      fetchData: function (lang) {
        //this.loading = true
        API.get('markets/?lang=' + lang).then((result) => {
          //this.loading = false
          if (result.data.success === true) {
            this.markets = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch((error) => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: error.response.data.title,
              text: error.response.data.message
            })
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'markets/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.markets'),
                text: this.$t('markets.error.messages.market_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('markets.error.messages.name'),
            text: this.$t('markets.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


