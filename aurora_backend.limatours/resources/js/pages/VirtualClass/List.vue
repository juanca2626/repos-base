<template>
    <table-client :columns="table.columns" :data="virtualclass" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-loading text-center" slot="type_class_id" slot-scope="props">
           {{ props.row.type_class.translations[0].value }}
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

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        loading: false,
        virtualclass: [],
        table: {
          columns: ['id', 'name','type_class_id','actions']
        },
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.getVirtualClass(payload.lang)
      })
    },
    mounted () {
      this.getVirtualClass(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []
        if (this.$can('update', 'typesclass')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'virtualclass/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'typesclass')) {
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
            name: this.$i18n.t('virtualclass.name'),
            type_class_id: this.$i18n.t('virtualclass.type_class_id'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: [],
          filterable: []
        }
      }
    },
    methods: {
      getVirtualClass: function (lang) {
        this.loading = true
        API.get('virtualclass/?lang='+lang).then((result) => {
          this.loading = false
          this.virtualclass = result.data.data

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
          url: 'virtualclass/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.getVirtualClass(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.virtualclass'),
                text: this.$t('virtualclass.error.messages.virtualclass_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('virtualclass.error.messages.name'),
            text: this.$t('virtualclass.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


