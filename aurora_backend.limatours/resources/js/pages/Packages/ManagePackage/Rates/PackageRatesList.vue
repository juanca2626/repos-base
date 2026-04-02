<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlrates" class="text-center" ref="table">
            <div class="table-fromTo" slot="fromTo" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.date_from | formatDate }} - {{ props.row.date_to | formatDate }}
            </div>
            <div class="table-type_class" slot="type_class" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.type_class.translations[0].value }}
            </div>
            <div class="table-service_type" slot="service_type" slot-scope="props" style="font-size: 0.9em">
                {{props.row.service_type.translations[0].value | capitalize}}
            </div>
            <div class="table-state" slot="state" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                        :checked="checkboxChecked(props.row.state)"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.state)"
                        switch>
                </b-form-checkbox>
            </div>

            <div class="table-actions" slot="actions" slot-scope="props" style="">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link
                            :to="'/packages/'+props.row.package_id+'/manage_package/package_rates/edit/'+props.row.id"
                            class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'rates')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button
                            @click="showModal(props.row.id, $t('packagesmanagepackagerates.rate') + ' ID: ' + props.row.id)"
                            class="m-0 p-0"
                            v-if="$can('delete', 'packagerates')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>

        </table-server>
        <b-modal :title="rateName" centered ref="my-modal" size="sm">
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

  export default {
    components: {
      'table-server': TableServer,
      BFormCheckbox,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal
    },
    data: () => {
      return {
        rateName: '',
        rate_id: '',
        urlrates: '',
        table: {
          columns: ['id', 'reference_number', 'simple', 'double', 'triple', 'boy', 'infant', 'fromTo', 'type_class', 'service_type', 'actions']
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created () {
      this.urlrates = '/api/package/' + this.$route.params.package_id + '/rates?token=' +
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
            reference_number: this.$i18n.t('packagesmanagepackagerates.code'),
            simple: 'SGL',
            double: 'DBL',
            triple: 'TPL',
            boy: 'CHD',
            infant: 'INF',
            fromTo: this.$i18n.t('packagesmanagepackagerates.from') + ' - ' +
              this.$i18n.t('packagesmanagepackagerates.to'),
            type_class: this.$i18n.t('packagesmanagepackagerates.class'),
            service_type: this.$i18n.t('packagesmanagepackagerates.type'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id']
        }
      }
    },
    methods: {
      showModal (rate_id, rate_name) {
        this.rate_id = rate_id
        this.rateName = rate_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      checkboxChecked: function (rate_state) {
        if (rate_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeState: function (rate_id, state) {
        API({
          method: 'put',
          url: 'rates/update/' + rate_id + '/state',
          data: { state: state }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rates'),
                text: this.$t('packagesmanagepackagerates.error.messages.information_error')
              })
            }
          })
      },
      onUpdate () {
        this.urlrates = '/api/package/' + this.$route.params.package_id + '/rates?token=' +
          window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove: function () {

        API({
          method: 'DELETE',
          url: '/package/' + this.$route.params.package_id + '/rates/' + this.rate_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rates'),
                text: this.$t('packagesmanagepackagerates.error.messages.rate_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('packagesmanagepackagerates.error.messages.name'),
            text: this.$t('packagesmanagepackagerates.error.messages.connection_error')
          })
        })
      }
    },
    filters: {
      formatDate: function (_date) {
        _date = _date.split('-')
        _date = _date[2] + '/' + _date[1] + '/' + _date[0]
        return _date
      },
      capitalize: function (value) {
        if (!value) return ''
        value = value.toString().toLowerCase()
        return value.charAt(0).toUpperCase() + value.slice(1)
      }
    }
  }
</script>
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #3ea662;
        background-color: #3a9d5d;
    }
</style>


