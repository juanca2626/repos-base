<template>
    <table-client :columns="table.columns" :data="currencies" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)" />
        </div>
        <div class="table-translations" slot="translations" slot-scope="props">
            {{ props.row.translations[0].value }}
        </div>
        <div class="table-translations" slot="tc" slot-scope="props">
            {{ props.row.exchange_rate}}
        </div>
        <div class="table-loading text-center" slot="loading">
            <img alt="loading" height="51px" src="/images/loading.svg" />
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
        currencies: [],
        table: {
          columns: ['actions', 'id', 'translations', 'symbol', 'iso', 'tc']
        },
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'currencies')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'currencies/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'currencies')) {
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
            translations: this.$i18n.t('currencies.currency_name'),
            tc: 'T.C',
            symbol: this.$i18n.t('currencies.symbol'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id', 'translations']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      fetchData: function (lang) {
        this.loading = true
        API.get('currencies/?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.currencies = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'currencies/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.countries'),
                text: this.$t('currencies.error.messages.country_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('currencies.error.messages.name'),
            text: this.$t('currencies.error.messages.connection_error')
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


