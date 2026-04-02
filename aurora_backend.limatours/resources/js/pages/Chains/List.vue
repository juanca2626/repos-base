<template>
    <div>
        <table-client :columns="table.columns" :data="chains" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/chains/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'chains')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/chains/multiple_properties/'+props.row.id"
                                   @click.native="getNameClient(props.row.name)"
                                  class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('chains.multiple_property')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.name)" class="m-0 p-0"
                                            v-if="$can('delete', 'chains')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
            <div class="table-name" slot="name" slot-scope="props">
                {{ props.row.name }}
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
        <b-modal :title="chainName" centered ref="my-modal" size="sm">
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
        chains: [],
        chain_id: null,
        chainName: '',
        table: {
          columns: ['actions','id', 'name', 'status']
        }
      }
    },
    mounted () {
        this.$root.$emit('updateTitle', { tab: 1 })
      this.$i18n.locale = localStorage.getItem('lang')
      this.fetchData(this.$i18n.locale)
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
      localStorage.setItem("chainname", "")
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('chains.title'),
            status: this.$i18n.t('chains.status.title'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','name']
        }
      }
    },
    methods: {
      showModal (chain_id, chain_name) {
        this.chain_id = chain_id
        this.chainName = chain_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      getNameClient(name) {
        localStorage.setItem("chainname", name)
        this.$root.$emit('updateTitle', { tab: 1 })
      },
      checkboxChecked: function (status) {
        return !!status
      },
      changeStatus: function (chain_id, status) {
        API({
          method: 'put',
          url: 'chain/update/' + chain_id + '/state',
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
                text: this.$t('chains.error.messages.information_error')
              })
            }
          })
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('chains/?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.chains = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch((error) => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: error.response.data.title,
              text: error.response.data.message
            })
          })
      },
      remove () {
        API({
          method: 'DELETE',
          url: 'chains/' + this.chain_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.chains'),
                text: this.$t('chains.error.messages.chain_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('chains.error.messages.name'),
            text: this.$t('chains.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
</style>


