<template>
    <table-client :columns="table.columns" :data="countries" :options="tableOptions" id="dataTable" theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"
                       @toTaxes="storageCountryName(props.row.translations[0].value)"/>

        </div>
        <div class="table-translations" slot="translations" slot-scope="props">
            {{ props.row.translations[0].value }}
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
        countries: [],
        table: {
          columns: ['actions', 'id', 'translations', 'iso']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = [
          {
            type: 'tax',
            text: this.$i18n.t('global.modules.taxes') + ' & ' + this.$i18n.t('global.modules.services'),
            link: 'countries/taxes/',
            icon: 'dot-circle',
            callback: 'toTaxes',
            type_action: 'link'
          }
        ]

        if (this.$can('update', 'countries')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'countries/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'countries')) {
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
            translations: this.$i18n.t('countries.country_name'),
            iso: 'ISO',
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id', 'translations'],
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
      storageCountryName: function (country_name) {
        localStorage.setItem('country_name', country_name)
      },
      fetchData: function (lang) {
        API.get('countries/?lang=' + lang).then((result) => {
          if (result.data.success === true) {
            this.countries = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('countries.error.messages.name'),
            text: this.$t('countries.error.messages.connection_error')
          })
        })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'countries/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.countries'),
                // text: this.$t('countries.error.messages.country_delete')
                text: (result.data.message) ? result.data.message : this.$t('countries.error.messages.country_delete')
              })

            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('countries.error.messages.name'),
            text: this.$t('countries.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style>
    .table-responsive {
        overflow-x: inherit;
    }
</style>


