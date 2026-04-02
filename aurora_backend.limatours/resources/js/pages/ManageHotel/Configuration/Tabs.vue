<template>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <b-tabs>
                <!-- @click="statusB('ContactAdd')" -->
                <b-tab :title="$t('contacts')" @click="statusB" active>
                    <template v-if="flag==false">
                        <button @click="create" class="btn btn-danger mb-4" type="reset">
                            {{ $t('new_contact') }}
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                        </button>
                        <table-client :columns="table.columns" :data="contacts" :loading="loading"
                                      :options="tableOptions" id="dataTable"
                                      theme="bootstrap4">
                            <template slot="status" slot-scope="props">
                                {{props.row.status ? $t('active') : $t('inactive') }}
                            </template>
                            <div class="table-actions" slot="actions" slot-scope="props">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    <div v-for="(option) in options">
                                        <b-dropdown-item-button
                                                @click="edit(props.row,props.row.id)"
                                                class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                                            {{ $t( option.type ) }}
                                        </b-dropdown-item-button>
                                    </div>
                                </b-dropdown>
                            </div>
                            <div class="table-loading text-center" slot="loading" slot-scope="props">
                                <img alt="loading" height="51px" src="/images/loading.svg"/>
                            </div>
                        </table-client>
                    </template>
                    <template v-else-if="flag=true">
                        <contact :form="draft" @changeStatus="close" @close="flag"/>
                    </template>
                </b-tab>
                <b-tab :title="$t('users')" @click="statusB">
                    <template v-if="flag==false">
                        <button @click="create" class="btn btn-danger mb-4" type="reset">
                            {{ $t('new_user') }}
                            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                        </button>
                        <table-client :columns="table2.columns" :data="users" :loading="loading"
                                      :options="tableOptions2" id="dataTable"
                                      theme="bootstrap4">
                            <div class="table-actions" slot="actions" slot-scope="props">
                                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                                    <template slot="button-content">
                                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                    </template>
                                    <div v-for="(option) in options">
                                        <b-dropdown-item-button
                                                @click="edit(props.row,props.row.id)"
                                                class="m-0 p-0">
                                            <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                                            {{ $t( option.type ) }}
                                        </b-dropdown-item-button>
                                    </div>
                                </b-dropdown>
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
    </div>
</template>

<script>
  import { API } from './../../../api'
  import ContactForm from './ContactForm'
  import UserForm from './UserForm'
  import TableClient from './.././../../components/TableClient'
  import MenuEdit from './../../../components/MenuEdit'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'user': UserForm,
      'contact': ContactForm,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton
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
          email: '',
          status: true,
          action: '',
          code: '',
          email: '',
          hotel_id: null,
        },
        id: null,
        currentIndex: null,
        showEdit: false,
        table: {
          columns: ['id', 'name', 'surname', 'lastname', 'position', 'status', 'actions']
        },
        table2: {
          columns: ['id', 'name', 'code', 'position', 'status', 'actions']
        },
        options: [
          {
            type: 'edit',
            link: 'manage_hotels/contacts/edit/',
            icon: 'dot-circle',
          },
          {
            type: 'delete',
            link: 'manage_hotels/contacts/edit/',
            icon: 'times',
          },
        ]
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('name'),
            surname: this.$i18n.t('surname'),
            lastname: this.$i18n.t('lastname'),
            position: this.$i18n.t('position'),
            status: this.$i18n.t('status'),
            actions: this.$i18n.t('table.actions')
          },
          sortable: ['id'],
          filterable: ['id']
        }
      },
      tableOptions2: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('name'),
            code: this.$i18n.t('code'),
            position: this.$i18n.t('position'),
            status: this.$i18n.t('status'),
            actions: this.$i18n.t('table.actions')
          },
          sortable2: ['id'],
          filterable2: ['id']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      close (valor) {
        this.flag = valor
      },
      edit: function (data, index) {
        console.log(data.hotel_id)
        this.id = index
        // this.form = clone(data)
        this.draft.id = data.id
        this.draft.hotel_id = data.hotel_id
        this.draft.lastname = data.lastname
        this.draft.name = data.name
        this.draft.position = data.position
        this.draft.status = data.status
        this.draft.surname = data.surname
        this.draft.email = data.email
        this.draft.code = data.code
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
          hotel_id: null,
          action: 'post',
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
        API.get('contacts/?lang=' + lang).then((result) => {
          this.loading = false
          console.log(result.data.data)
          if (result.data.success === true) {
            this.contacts = result.data.data
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

        //users
        API.get('users/?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.users = result.data.data
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
      }
    }
  }
</script>

<style lang="stylus">
</style>


