<template>
    <div class="container-fluid" style="position: relative;">
        <div class="row justify-content-end"
             style="padding-bottom: 0%;width: 30%;position:absolute;right:340px;z-index:999999">
            <div class="b-form-group form-group" style="width: 100%;margin-right: 21px;margin-bottom: -40px;">
                <div class="form-row">
                    <label class="col-3 col-form-label" for="searchStatus">{{ $t('clients.market') }}</label>
                    <div class="col-8">
                        <v-select :options="markets"
                                  :value="market_id"
                                  @input="marketChange"
                                  autocomplete="true"
                                  v-model="marketSelected"
                        >
                        </v-select>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="row justify-content-end"
             style="padding-bottom: 0%;width: 30%;position:absolute;right:0;z-index:999999">
            <div class="b-form-group form-group" style="width: 100%;margin-right: 21px;margin-bottom: -40px;">
                <div class="form-row">
                    <label class="col-3 col-form-label" for="searchStatus">{{ $t('clients.state') }}</label>
                    <div class="col-8">
                        <select @change="search" ref="selectStatus" class="form-control" id="selectStatus" required
                                size="0" v-model="searchStatus">
                            <option value="" disabled>
                                {{ $t('clients.select_status') }}
                            </option>
                            <option :value="status.value" v-for="status in statuses">
                                {{$t(status.text)}}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <table-server :columns="table.columns" :options="tableOptions" ref="table" :key="updateTable">

            <div class="table-actions" slot="actions" slot-scope="props">
                <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>

                    <router-link :to="'/clients/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'clients')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/clients/'+props.row.id +'/manage_client/regions/'+(props.row.first_business_region?.business_region_id || 1)+'/markups'" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('create', 'clients')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            Administrar cliente
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.code)" class="m-0 p-0" v-if="$can('delete', 'businessregion')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>


            <div class="table-ubigeo" slot="market" slot-scope="props" style="font-size: 0.9em">
                {{props.row.markets.name}}
            </div>

            <div class="table-ubigeo" slot="hotel" slot-scope="props" style="font-size: 0.9em">
                {{props.row.markup_hotel}}
            </div>

            <div class="table-ubigeo" slot="service" slot-scope="props" style="font-size: 0.9em">
                {{props.row.markup_service}}
            </div>

            <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id,props.row.status)"
                    switch>
                </b-form-checkbox>
            </div>

        </table-server>


        <b-modal :title="client_code" centered ref="my-modal" size="sm">
            <p class="text-center">{{ $t('global.message_delete') }}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>
    </div>
</template>


<script>
  import { API } from './../../api'
  import TableServer from '../../components/TableServer'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import Progress from 'bootstrap-vue/src/components/progress/progress'
  import ProgressBar from 'bootstrap-vue/src/components/progress/progress-bar'
  import Tooltip from 'bootstrap-vue/src/components/tooltip/tooltip'
  import MenuEdit from './../../components/MenuEdit'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  export default {
    components: {
      BFormCheckbox,
      'table-server': TableServer,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal,
      'b-progress': Progress,
      'b-progress-bar': ProgressBar,
      'b-tooltip': Tooltip,
      'menu-edit': MenuEdit,
      vSelect
    },
    data: () => {
      return {
        max: 100,
        value: 100,
        loading: false,
        markets: [],
        target: '',
        searchStatus: '1',
        client_id: null,
        client_code: 'title',
        clientName: '',
        updateTable: 1,
        clients: {},
        marketSelected: [],
        market_id: '',
        statuses: [
          { value: '', text: 'all' },
          { value: '1', text: 'active' },
          { value: '0', text: 'inactive' },
        ],
        table: {
          columns: ['actions', 'code', 'name','market', 'hotel', 'service', 'status']
        }
      }
    },
    mounted () {
      this.$root.$emit('updateTitle', { tab: 1 })
      this.$i18n.locale = localStorage.getItem('lang')
      //this.search()
      //markets
      API.get('/markets/selectbox?lang=' + localStorage.getItem('lang'))
        .then((result) => {

          let mark = result.data.data
          mark.forEach((market) => {
            this.markets.push({
              label: market.text,
              code: market.value
            })
          })

        }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clients.error.messages.name'),
          text: this.$t('clients.error.messages.connection_error')
        })
      })
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
      localStorage.setItem('status', '1')
    },
    computed: {
      menuOptions: function () {
        let options = []
        if (this.$can('update', 'clients')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'clients/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        // if (this.$can('create', 'clients')) {
        //   options.push({
        //     type: 'manage_client',
        //     text: '',
        //     link: '/clients/',
        //     link2: (row) => `/manage_client/regions/${row.first_business_region?.business_region_id || 1}`,
        //     icon: 'dot-circle',
        //     callback: '',
        //     type_action: 'manageLink'
        //   })
        // }
        // if (this.$can('delete', 'clients')) {
        //   options.push({
        //     type: 'delete',
        //     text: '',
        //     link: '',
        //     icon: 'trash',
        //     type_action: 'button',
        //     callback_delete: 'remove'
        //   })
        // }
        return options
      },
      tableOptions: function () {
        return {
          headings: {
            code: this.$i18n.t('clients.code'),
            name: this.$i18n.t('global.name'),
            market: this.$i18n.t('clients.market'),
            hotel: this.$i18n.t('clients.hotel'),
            service: this.$i18n.t('clients.service'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: [],
          filterable: ['id', 'name'],
          perPageValues: [],
          requestFunction: function (data) {
            return API.get('/clients?token=' + window.localStorage.getItem('access_token') +
              '&lang=' + localStorage.getItem('lang') + '&status=' + localStorage.getItem('status') + '&market=' + localStorage.getItem('market_id'), {
              params: data
            })
              .then((result) => {
                return result.data
              }).catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('hotels.error.messages.name'),
                  text: this.$t('hotels.error.messages.connection_error')
                })
              })
          },
          responseAdapter: (response) => {
            return response
          },
          requestKeys: {}

        }
      }
    },
    methods: {
      marketChange: function (value) {
        this.market = value
        if (this.market != null) {
          this.market_id = this.market.code
          localStorage.setItem('market_id', this.market_id)
        } else {
          this.market_id = ''
          localStorage.setItem('market_id', this.market_id)
        }
        this.onUpdate()
      },
      onUpdate () {
        this.$refs.table.$refs.tableserver.refresh()

      },
      search: function () {
        localStorage.setItem('status', this.searchStatus)
        localStorage.setItem('market_id', this.market_id)
        this.onUpdate()
      },
      checkboxChecked: function (status) {
        return !!status
      },
      changeState: function (client_id, status) {
        API({
          method: 'put',
          url: 'clients/update/' + client_id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.updateTable += 1
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.clients'),
                text: this.$t('clients.error.messages.information_error')
              })
            }
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'clients/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.clients'),
                text: this.$t('clients.error.messages.client_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clients.error.messages.name'),
            text: this.$t('clients.error.messages.connection_error')
          })
        })
      },
        showModal(client_id, code) {
            this.client_id = client_id;
            this.client_code = code;
            this.$refs['my-modal'].show()
        },
        hideModal() {
            this.$refs['my-modal'].hide()
        },
    }
  }
</script>

<style>
    .progress-bar {
        color: white;
        -webkit-border-radius: 0.25rem;
        -moz-border-radius: 0.25rem;
        border-radius: 0.25rem;
    }
</style>

