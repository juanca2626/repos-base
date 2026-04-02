<template>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <b-tabs>
                <b-tab :title="$t('hotelsmanagehotelinformation.contacts')" @click="statusB" active>
                    <template v-if="flag==false">
                        <button @click="create" class="btn btn-danger mb-4" type="reset">
                            {{ $t('hotelsmanagehotelinformation.new_contact') }}
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                        </button>
                        <table-client :columns="table.columns" :data="contacts" :loading="loading"
                                      :options="tableOptions2" id="dataTable"
                                      theme="bootstrap4">

                            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                                <b-form-checkbox
                                        :checked="checkboxChecked(props.row.status)"
                                        :id="'checkbox_'+props.row.id"
                                        :name="'checkbox_'+props.row.id"
                                        @change="changeStateContact(props.row.id,props.row.status)"
                                        switch>
                                </b-form-checkbox>
                            </div>
                            <div class="table-actions" slot="actions" slot-scope="props">
                              <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions" @edit="edit(props.row, props.row.id)"
                              @remove="removeContact(props.row.id)"/>
                            </div>
                            <div class="table-loading text-center" slot="loading">
                                <img alt="loading" height="51px" src="/images/loading.svg"/>
                            </div>
                        </table-client>

                    </template>
                    <template v-else-if="flag=true">
                        <contact :form="draft" @changeStatus="close" @close="flag"/>
                    </template>
                </b-tab>
                <b-tab :title="$t('hotelsmanagehotelinformation.users')" @click="statusB">

                    <template v-if="flag==false">
                        <button @click="create" class="btn btn-danger mb-4" type="reset">
                            {{ $t('hotelsmanagehotelinformation.new_user') }}
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                        </button>
                        <table-client :columns="table2.columns" :data="users" :loading="loading"
                                      :options="tableOptions2" id="dataTable"
                                      theme="bootstrap4">
                            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                                <b-form-checkbox
                                        :checked="checkboxChecked(props.row.status)"
                                        :id="'checkbox_'+props.row.id"
                                        :name="'checkbox_'+props.row.id"
                                        @change="changeStateUser(props.row.id,props.row.status)"
                                        switch>
                                </b-form-checkbox>
                            </div>
                            <div class="table-actions" slot="actions" slot-scope="props">
                              <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions" @edit="edit(props.row, props.row.id)"
                              @remove="removeUser(props.row.id)"/>
                            </div>
                            <div class="table-loading text-center" slot="loading">
                                <img alt="loading" height="51px" src="/images/loading.svg"/>
                            </div>
                        </table-client>
                    </template>
                    <template v-else>
                        <user :form="draft" @changeStatus="close" @close="flag"/>
                    </template>
                </b-tab>
            </b-tabs>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import ContactForm from './ContactForm'
  import UserForm from './UserForm'
  import TableClient from './.././../../../components/TableClient'
  import MenuEdit from './../../../../components/MenuEdit'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'user': UserForm,
      'contact': ContactForm,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      cSwitch
    },
    data: () => {
      return {
        loading: false,
        flag: false,
        addRoute: 'ContactAdd',
        contacts: [],
        users: [],
        draft: {
          id: null,
          name: '',
          lastname: '',
          surname: '',
          position: '',
          principal: true,
          status: true,
          action: '',
          code: '',
          email: '',
          hotel_id: null,
          password: '',
          confirm_password: ''
        },
        id: null,
        currentIndex: null,
        showEdit: false,
        table: {
          columns: ['actions', 'id', 'name', 'surname', 'lastname', 'position', 'updated_at' ,  'status']
        },
        table2: {
          columns: ['actions', 'id', 'name', 'email' , 'status']
        },
        options: [
          {
            type: 'edit',
            icon: 'dot-circle'
          },
          {
            type: 'delete',
            icon: 'times'
          }
        ]
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'contacts')) {
          options.push({
            type: 'edit',
            text: '',
            link: '',
            icon: 'dot-circle',
            callback: '',
            type_action: 'editButton'
          })
        }
        if (this.$can('update', 'contacts')) {
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
            code: this.$i18n.t('hotelsmanagehotelinformation.code'),
            name: this.$i18n.t('global.name'),
            surname: this.$i18n.t('hotelsmanagehotelinformation.surname'),
            lastname: this.$i18n.t('hotelsmanagehotelinformation.lastname'),
            position: this.$i18n.t('hotelsmanagehotelinformation.position'),
            updated_at: this.$i18n.t('hotelsmanagehotelinformation.dateupdate'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: []
        }
      },
      tableOptions2: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('global.name'),
            email: this.$i18n.t('global.email'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable2: ['id'],
           filterable: []
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      changeStateContact: function (contact_id, status) {
        API({
          method: 'put',
          url: 'contacts/update/' + contact_id + '/state',
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
                text: this.$t('hotelsmanagehotelinformation.error.messages.information_error')
              })
            }
          })
      },
      changeStateUser: function (user_id, status) {
        API({
          method: 'put',
          url: 'hotel_users/update/' + user_id + '/state',
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
                text: this.$t('hotelsmanagehotelinformation.error.messages.information_error')
              })
            }
          }).catch((e) => {

            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
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
        this.draft.hotel_id = this.$route.params.hotel_id
        // this.draft.principal = !!data.principal
        console.log(data.hotel_users)
        if (data.hotel_users && data.hotel_users.length > 0) {
          this.draft.range = data.hotel_users[0].range
        }
        this.draft.action = 'put'
        this.change()
      },
      create: function () {
        this.draft = {
          id: null,
          name: '',
          lastname: '',
          surname: '',
          position: '',
          code: '',
          email: '',
          status: true,
          principal: true,
          hotel_id: this.$route.params.hotel_id,
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
        API.get('contacts/?lang=' + lang + '&hotel_id=' + this.$route.params.hotel_id).then((result) => {
          this.loading = false
          if (result.data.success == true) {
            this.contacts = result.data.data
            for (var i = this.contacts.length - 1; i >= 0; i--) {
              this.contacts[i].status = !!this.contacts[i].status
              this.contacts[i].principal = !!this.contacts[i].principal
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
          .catch((e) => {
              console.log(e)
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })

        //users
        API.get('hotel_users/?lang=' + lang + '&hotel_id=' + this.$route.params.hotel_id).then((result) => {
          this.loading = false
          if (result.data.success == true) {
            this.users = result.data.data
            for (var i = this.users.length - 1; i >= 0; i--) {
              this.users[i].status = this.users[i].status == '1' ? true : false
              if (this.users[i].hotel_users.length > 0) {
                for (var j = this.users[i].hotel_users.length - 1; j >= 0; j--) {
                  this.users[i].hotel_users[j].range = !!this.users[i].hotel_users[j].range
                }
              }
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
          .catch((e) => {
              console.log(e)
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
      },
      removeUser (id) {
        API({
          method: 'DELETE',
          url: 'hotel_users/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelinformation.title'),
                text: this.$t('hotelsmanagehotelinformation.error.messages.user_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelinformation.error.messages.name'),
            text: this.$t('hotelsmanagehotelinformation.error.messages.connection_error')
          })
        })
      },
      removeContact (id) {
        API({
          method: 'DELETE',
          url: 'contacts/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('hotelsmanagehotelinformation.title'),
                text: this.$t('hotelsmanagehotelinformation.error.messages.contact_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('hotelsmanagehotelinformation.error.messages.name'),
            text: this.$t('hotelsmanagehotelinformation.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


