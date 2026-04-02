<template>

    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlServiceTypes" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.translations[0].value }}
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/service_category/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'servicetypes')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.translations[0].value)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'servicetypes')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="serviceTypesName" centered ref="my-modal" size="sm">
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
  import TableServer from '../../components/TableServer'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    components: {
      BFormCheckbox,
      'table-server': TableServer,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal
    },
    data: () => {
      return {
        serviceTypesName: '',
        urlServiceTypes: '/api/service_types?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['id', 'code', 'abbreviation', 'name', 'actions'],
        }
      }
    },
    computed: {
      menuOptions: function () {
        return [
          {
            type: 'edit',
            link: 'service_category/edit/',
            icon: 'dot-circle',
          }
        ]
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('global.name'),
            code: this.$i18n.t('servicetypes.code'),
            abbreviation: this.$i18n.t('servicetypes.abbreviation'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id']
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      showModal (serviceTypes_id, serviceTypes_name) {
        this.serviceTypes_id = serviceTypes_id
        this.serviceTypesName = serviceTypes_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      remove () {
        API({
          method: 'DELETE',
          url: 'service_types/' + this.serviceTypes_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.hideModal()
              if(result.data.used === true){
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.serviceTypes'),
                  text: this.$t('servicetypes.error.messages.used')
                })
              }else{
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.serviceTypes'),
                  text: this.$t('servicetypes.error.messages.requirement_delete')
                })
              }
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicetypes.error.messages.name'),
            text: this.$t('servicetypes.error.messages.connection_error')
          })
        })
      },
      onUpdate () {
        this.urlServiceTypes = '/api/service_types?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
    }

  }
</script>

<style lang="stylus">

</style>
