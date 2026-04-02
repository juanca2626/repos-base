<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlServiceCagories" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.translations[0].value }}
            </div>
            <div class="table-name" slot="subtypes" slot-scope="props" style="font-size: 0.9em">
                <div v-for="subtype in props.row.service_sub_category">
                    - {{subtype.translations[0].value}}
                </div>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <router-link :to="'/type_service/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'servicecategories')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/type_service/'+props.row.id +'/service_sub_type'" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('read', 'servicesubcategories')">
                            <font-awesome-icon :icon="['fas', 'bars']" class="m-0" />
                            {{$t('servicecategories.name_subtypes')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.translations[0].value)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'servicecategories')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0" />
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="serviceCategoryName" centered ref="my-modal" size="sm">
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
        serviceCategoryName: '',
        urlServiceCagories: '/api/service_categories?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['id', 'name', 'subtypes', 'actions'],
        }
      }
    },
    computed: {
      menuOptions: function () {
        return [
          {
            type: 'edit',
            link: 'type_service/edit/',
            icon: 'dot-circle',
          }
        ]
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('global.name'),
            subtypes: this.$i18n.t('servicecategories.name_subtypes'),
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
      showModal (serviceCategory_id, serviceCategory_name) {
        this.serviceCategory_id = serviceCategory_id
        this.serviceCategoryName = serviceCategory_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      remove () {
        API({
          method: 'DELETE',
          url: 'service_categories/' + this.serviceCategory_id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.hideModal()
              if (result.data.used === true) {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.servicecategories'),
                  text: this.$t('servicetypes.error.messages.used')
                })
              } else {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.servicecategories'),
                  text: this.$t('servicecategories.error.messages.requirement_delete')
                })
              }
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.servicecategories'),
            text: this.$t('servicecategories.error.messages.connection_error')
          })
        })
      },
      onUpdate () {
        this.urlServiceCagories = '/api/service_categories?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
    }

  }
</script>

<style lang="stylus">

</style>
