<template>
    <table-client :columns="table.columns" :data="suplements" :options="tableOptions" id="dataTable" theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :options="menuOptions"  @remove="remove(props.row.id)"/>
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
        suplements: [],
        table: {
          columns: ['id', 'translations', 'actions']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {
        let options =[]
        options.push({
          type: 'edit',
          text: '',
          link: 'suplements/edit/',
          icon: 'dot-circle',
          callback: '',
          type_action: 'link'
        })

        options.push({
          type: 'delete',
          text: '',
          link: '',
          icon: 'trash',
          type_action: 'button',
          callback_delete: 'remove'
        })
        return options
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            translations: this.$i18n.t('suplements.suplement_name'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['translations']
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
        API.get('suplements/?lang=' + lang).then((result) => {
          if (result.data.success === true) {
            this.suplements = result.data.data
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
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'suplements/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.suplements'),
                text: this.$t('suplements.error.messages.suplement_delete')
              })

            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


