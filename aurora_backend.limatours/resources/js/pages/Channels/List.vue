<template>
    <div>
        <table-client :columns="table.columns" :data="channels" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/channels/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'channels')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/channels/'+props.row.id+'/logs'" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'channels')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('global.buttons.logs')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/channels/'+props.row.id+'/users'"
                                   @click.native="getNameUser(props.row.name)"
                                  class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('channels.manage_user')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.name)" class="m-0 p-0"
                                            v-if="$can('delete', 'channels')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
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
         <b-modal :title="userName" centered ref="my-modal" size="sm">
             <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
  import { API } from './../../api'
  import TableClient from './../../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      BFormCheckbox,
      BModal,
    },
    data: () => {
      return {
        loading: false,
        channels: [],
        channel_id: null,
        userName: '',
        table: {
          columns: ['actions', 'id', 'name', 'status']
        },
        options: [
          {
            type: 'edit',
            link: 'channels/edit/',
            icon: 'dot-circle'
          },
          {
            type: 'delete',
            link: 'channels/edit/',
            icon: 'times'
          }
        ]
      }
    },
    mounted () {
      this.$root.$emit('updateTitle', { tab: 1 })
      this.$i18n.locale = localStorage.getItem('lang')
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'channels')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'channels/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('update', 'channels')) {
          options.push({
            type: 'manage_user',
            text: '',
            link: '/channels/',
            link2: '/users',
            icon: 'dot-circle',
            callback: '',
            type_action: 'manageLink'
          })
        }
        if (this.$can('delete', 'channels')) {
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
            translations: this.$i18n.t('channels.channel_name'),
            actions: this.$i18n.t('global.table.edit')
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
      localStorage.setItem("usersname", "")
    },
    methods: {
      showModal (user_id, users_name) {
        this.user_id = user_id
        this.userName = users_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      getNameUser(name) {
        console.log("nombre del channel")
        console.log("nombre del channel")
        console.log(name)
        localStorage.setItem("usersname", name)
        this.$root.$emit('updateTitle', { tab: 1 })
      },
      checkboxChecked: function (status) {
        return !!status
      },
      changeStatus: function (channel_id, status) {
        API({
          method: 'put',
          url: 'channels/update/' + channel_id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              //this.fetchData(localStorage.getItem('lang'))

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.channels'),
                text: this.$t('channels.error.messages.information_error')
              })
            }
          })
      },
      fetchData: function () {
        this.loading = true
        API.get('channels').then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.channels = result.data.data
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
      remove () {
        API({
          method: 'DELETE',
          url: 'channels/' + this.channel_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.channels'),
                text: this.$t('channels.error.messages.channel_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.channels'),
            text: this.$t('channels.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus"></style>


