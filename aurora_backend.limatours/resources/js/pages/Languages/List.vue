<template>
    <table-client :columns="table.columns" :data="languages" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
          
        <div class="table-state" slot="state" slot-scope="props" style="font-size: 0.9em">
            <b-form-checkbox
                    :checked="checkboxChecked(props.row.state)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeStatus(props.row.id,props.row.state)"
                    switch>
            </b-form-checkbox>
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
        languages: [],
        table: {
          columns: ['actions', 'id', 'name','state']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'languages')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'languages/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'languages')) {
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
            name: this.$i18n.t('languages.language_name'),
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
      checkboxChecked: function (language_state) {
        if (language_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeStatus: function (language_id, state) {
        API({
          method: 'put',
          url: 'language/update/' + language_id + '/state',
          data: { state: state }
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('amenities.error.messages.information_error')
              })
            }
          })
      },      
      fetchData: function (lang) {
        API.get('languages/all?lang=' + lang).then((result) => {
          if (result.data.success === true) {
            this.languages = result.data.data
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
            title: this.$t('languages.error.messages.name'),
            text: this.$t('languages.error.messages.connection_error')
          })
        })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'languages/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.languages'),
                text: this.$t('languages.error.messages.language_delete')
              })

              this.loading = false
            }
          })
      },
    }
  }
</script>

<style lang="stylus">
</style>


