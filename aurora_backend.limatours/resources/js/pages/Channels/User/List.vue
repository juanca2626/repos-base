<template>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <template v-if="flag==false">
                <button @click="create" class="btn btn-danger mb-4" type="reset">
                    {{ $t('channelsuser.new_user') }}
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
                <table-client :columns="table.columns" :data="users" :loading="loading"
                              :options="tableOptions" id="dataTable"
                              theme="bootstrap4">
                    <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                        <b-form-checkbox
                                :checked="checkboxChecked(props.row.status)"
                                :id="'checkbox_'+props.row.id"
                                :name="'checkbox_'+props.row.id"
                                @change="changeState(props.row.id,props.row.status)"
                                switch>
                        </b-form-checkbox>
                    </div>
                    <div class="table-actions" slot="actions" slot-scope="props">
                      <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions" @edit="edit(props.row, props.row.id)"
                      @remove="remove(props.row.id)"/>
                    </div>
                    <div class="table-loading text-center" slot="loading">
                        <img alt="loading" height="51px" src="/images/loading.svg"/>
                    </div>
                </table-client>
             </template>
            <template v-else>
                <user :form="draft" @changeStatus="close" @close="flag"/>
            </template>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../api'
  import TableClient from '././../../../components/TableClient'
  import Form from './Form'
  import MenuEdit from './../../../components/MenuEdit'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'user': Form,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      cSwitch
    },
    data: () => {
      return {
        loading: false,
        flag: false,
        addRoute: 'ContactAdd',
        users: [],
        draft: {
          id: null,
          name: '',
          email: '',
          status: true,
          action: '',
          channel_id: null,
          password: '',
          confirm_password: ''
        },
        id: null,
        currentIndex: null,
        showEdit: false,
        table: {
          columns: ['actions', 'id', 'code', 'name', 'email', 'status']
        },
        
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'channelusers')) {
          options.push({
            type: 'edit',
            text: '',
            link: '',
            icon: 'dot-circle',
            callback: '',
            type_action: 'editButton'
          })
        }
        if (this.$can('update', 'channelusers')) {
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
            code: this.$i18n.t('channelsuser.code'),
            name: this.$i18n.t('global.name'),
            email: this.$i18n.t('channelsuser.email'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable2: ['id'],
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      changeState: function (user_id, status) {
        API({
          method: 'put',
          url: 'channel_users/update/' + user_id + '/state',
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
                text: this.$t('channelsuser.error.messages.information_error')
              })
            }
          })
      },
      checkboxChecked: function (state) {
        return !!state
      },
      close (valor) {
        this.flag = valor
        this.fetchData(this.$i18n.locale)
      },
      edit: function (data, index) {
        this.draft = clone(data)
        this.draft.status = !!data.status
        this.draft.action = 'put'
        this.change()
      },
      create: function () {
        this.draft = {
          id: null,
          name: '',
          email: '',
          status: true,
          channel_id: this.$route.params.channel_id,
          password: '',
          confirm_password: '',
          action: 'post'
        }
        this.change()
      },
      change: function () {
        if (this.flag === true) {
          this.flag = false
        } else {
          this.flag = true
        }
      },
      statusB: function () {
        this.flag = false
      },
      fetchData: function (lang) {
        this.loading = true        
        //users
        API.get('channel_users/?lang=' + lang + '&channel_id=' + this.$route.params.channel_id).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.users = result.data.data
            for (var i = this.users.length - 1; i >= 0; i--) {
                this.users[i].status = this.users[i].status == '1' ? true : false
            }
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
      remove(id) {
        API({
          method: 'DELETE',
          url: 'channel_users/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.policy_cancellation'),
                text: this.$t('channelsuser.error.messages.policy_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('channelsuser.error.messages.name'),
            text: this.$t('channelsuser.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


