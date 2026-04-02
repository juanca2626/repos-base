<template>
    <table-server :columns="table.columns" :options="tableOptions" :url="urlBags" ref="table">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-name" slot="name" slot-scope="props">
            {{props.row.name}}
        </div>
        <div class="table-status" slot="status" slot-scope="props">
            <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="updateStatus(props.row.id,props.row.status)"
                    switch>
            </b-form-checkbox>
        </div>
    </table-server>
</template>

<script>

  import TableServer from '../../../../../components/TableServer'
  import MenuEdit from './../../../../../components/MenuEdit'
  import { API } from './../../../../../api'

  export default {
    components: {
      'table-server': TableServer,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        states: [],
        table: {
          columns: [ 'actions', 'name', 'status']
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
      urlBags: function () {

        return '/api/rates/bags?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang') + '&hotel_id=' + this.$route.params.hotel_id
      },
      menuOptions: function () {

        let options = []

        options.push({
            type: 'edit',
            text: '',
            link: 'hotels/'+this.$route.params.hotel_id+'/manage_hotel/rates/bags/edit/',
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
            actions: this.$i18n.t('global.table.actions'),
            name: this.$i18n.t('name'),
            status: this.$i18n.t('status')
          },
          sortable: [],
          filterable: []
        }
      }
    },
    methods: {
      checkboxChecked: function (bag_status) {
        if (bag_status) {
          return 'true'
        } else {
          return 'false'
        }
      },
      updateStatus: function (bag_id, status) {
        API({
          method: 'put',
          url: 'rates/bags/update/' + bag_id + '/status',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('error.messages.information_error')
              })
            }
          })
      },
      onUpdate () {
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'rates/bags/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.states'),
                text: this.$t('error.messages.district_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
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
