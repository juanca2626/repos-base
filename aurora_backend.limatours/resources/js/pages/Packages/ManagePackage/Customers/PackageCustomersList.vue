<template>
    <div class="container-fluid">

        <div class="form-row">
            <label class="col-sm-2 col-form-label">{{ $t('global.buttons.add') }} {{
                $t('packagesmanagepackagecustomers.customer') }}</label>
            <div class="col-8">
                <form @submit.prevent="validateBeforeSubmit">
                    <div id="input-group-1" role="group" class="form-group">
                        <div class="col-sm-12 p-0">
                            <v-select :options="customers"
                                      :value="form.client_id"
                                      label="name" :filterable="false" @search="onSearch"
                                      :placeholder="$t('packagesmanagepackagecustomers.filter')" v-validate="'required'"
                                      v-model="customerSelected" name="customer" id="customer" style="height: 35px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        {{ option.name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        {{ option.name }}
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('customer')"/>
                                <span v-show="errors.has('customer')">{{ errors.first('customer') }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-2" style="text-align: right;">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.add') }}
                </button>
            </div>

        </div>

        <table-server :columns="table.columns" :options="tableOptions" :url="urlcustomers" class="text-center" ref="table">
            <div class="table-customer_name" slot="customer_name" slot-scope="props" style="font-size: 0.9em">
                {{props.row.client.name}}
            </div>
            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                        :checked="checkboxChecked(props.row.status)"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.status)"
                        switch>
                </b-form-checkbox>
            </div>


            <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">
                <button @click="showModal(props.row.id, $t('packagesmanagepackagecustomers.customer') + ' ID: ' + props.row.id)"
                        class="btn btn-danger"
                        type="button"
                        v-if="$can('delete', 'packagecustomers')">
                    <font-awesome-icon :icon="['fas', 'trash']"/>
                </button>
            </div>

        </table-server>
        <b-modal :title="customerName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>

    </div>
</template>
<script>
  import TableServer from '../../../../components/TableServer'
  import { API } from '../../../../api'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      'table-server': TableServer,
      BFormCheckbox,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal,
      vSelect
    },
    data: () => {
      return {
        loading:false,
        customerName: '',
        customer_id: '',
        urlcustomers: '',
        customers:[],
        customerSelected: [],
        table: {
          columns: ['id', 'customer_name', 'status', 'actions']
        },
        form:{
          package_id:null,
          customer_id:null
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created () {
      this.form.package_id = this.$route.params.package_id
      this.urlcustomers = '/api/package/' + this.$route.params.package_id + '/customers?token=' +
        window.localStorage.getItem('access_token') + '&lang=' +
        localStorage.getItem('lang')
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            customer_name: this.$i18n.t('packagesmanagepackagecustomers.customer'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id']
        }
      }
    },
    methods: {
      onSearch(search, loading) {
        loading(true)
        API.get('/clients/selectBox/by/name?query=' + search)
          .then((result) => {
            loading(false)
            this.customers = result.data.data
          }).catch(() => {
            loading(false)
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
                text: this.$t('packagesmanagepackagecustomers.error.messages.system')
              })
        })
      },
      validateBeforeSubmit: function () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.form.package_id = this.$route.params.package_id
            this.submit()

          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
              text: this.$t('packagesmanagepackagecustomers.error.messages.system')
            })

            this.loading = false
          }
        })
      },
      showModal (customer_id, customer_name) {
        this.customer_id = customer_id
        this.customerName = customer_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      checkboxChecked: function (customer_state) {
        if (customer_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeState: function (customer_id, status) {
        API({
          method: 'put',
          url: 'package/' + this.form.package_id + '/customers/' + customer_id + '/status',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
                text: this.$t('packagesmanagepackagecustomers.error.messages.system')
              })
            }
          })
      },
      onUpdate () {
        this.urlcustomers = '/api/package/' + this.$route.params.package_id + '/customers?token=' +
          window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove: function () {

        API({
          method: 'DELETE',
          url: '/package/'+ this.$route.params.package_id + '/customers/' + this.customer_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
                text: this.$t('packagesmanagepackagecustomers.error.messages.customer_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
            text: this.$t('packagesmanagepackagecustomers.error.messages.connection_error')
          })
        })
      },
      submit: function () {
        this.form.customer_id = this.customerSelected.id
        this.loading = true
        console.log( this.errors )
        API({
          method: 'post',
          url: 'package/' + this.form.package_id + '/customers',
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.loading = false
              this.customerSelected = []
              this.errors.items = []
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error: ' + this.$t('packagesmanagepackagecustomers.customers'),
                text: this.$t('packagesmanagepackagecustomers.error.messages.already_added')
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
    .v-select input{
        height: 25px;
    }
</style>


