<template>
    <div class="container-fluid">
        <table-client :columns="table.columns" :data="subCategories"
                      :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                {{ props.row.translations[0].value }}
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <router-link :to="'/'+ category_id +'/service_sub_type/edit/'+props.row.id"
                                 class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'servicesubcategories')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0" />
                            {{$t('global.buttons.edit')}}
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
        </table-client>
        <b-modal :title="serviceSubCategoryName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>
            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
  import { API } from '../../../api'
  import TableClient from '../../../components/TableClient'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    components: {
      BFormCheckbox,
      'table-client': TableClient,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal
    },
    data: () => {
      return {
        subCategories: [],
        serviceSubCategoryName: '',
        urlServiceCagories: '',
        category_id: '',
        table: {
          columns: ['id', 'name', 'actions'],
        }
      }
    },
    created () {
      this.getSubCategories()
    },
    computed: {
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
      getSubCategories: function () {
        this.category_id = this.$route.params.category_id
        API.get('/service_categories/' + this.$route.params.category_id + '/' + localStorage.getItem('lang') + '/subcategory?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')).then((result) => {
          if (result.data.success === true) {
            this.subCategories = result.data.data
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
      showModal (serviceSubCategory_id, serviceSubCategory_name) {
        console.log(serviceSubCategory_id,serviceSubCategory_name)
        this.serviceSubCategory_id = serviceSubCategory_id
        this.serviceSubCategoryName = serviceSubCategory_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      remove () {
        API({
          method: 'DELETE',
          url: 'service_sub_categories/' + this.serviceSubCategory_id
        }).then((result) => {
          if (result.data.success === true) {
            this.hideModal()
            this.getSubCategories()
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
      }
    }

  }
</script>

<style lang="stylus">

</style>
