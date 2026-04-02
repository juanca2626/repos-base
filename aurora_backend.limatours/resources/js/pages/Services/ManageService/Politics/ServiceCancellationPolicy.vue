<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <template v-if="flag==false">
                    <button @click="create"  class="btn btn-danger mb-4" type="reset">
                        {{$t('global.buttons.add')}}
                        <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    </button>
                    <table-client :columns="table.columns" :data="cancellations" :loading="loading"
                                  :options="tableOptions" id="dataTable"
                                  theme="bootstrap4">
                        <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                            <b-form-checkbox
                                    :checked="checkboxChecked(props.row.status)"
                                    :id="'checkbox_'+props.row.id"
                                    :name="'checkbox_'+props.row.id"
                                    @change="changeState(props.row.id,props.row.status)"
                                    switch>
                            </b-form-checkbox>
                        </div>
                        <div class="table-actions" slot="actions" slot-scope="props">
                            <menu-edit :id="props.row.id" :name="props.row.name" :options="menuOptions"
                                       @edit="edit(props.row, props.row.id)"
                                       @remove="remove(props.row.id)" />
                        </div>
                        <div class="table-loading text-center" slot="loading" slot-scope="props">
                            <img alt="loading" height="51px" src="/images/loading.svg" />
                        </div>
                    </table-client>
                </template>
                <template v-else-if="flag=true">
                    <cancellation-form :form="draft" @changeStatus="close" @close="flag" />
                </template>
            </div>
        </div>
    </div>
</template>

<script>

  import { API } from './../../../../api'
  import TableClient from './.././../../../components/TableClient'
  import MenuEdit from './../../../../components/MenuEdit'
  import ServiceCancellationForm from './ServiceCancellationForm'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'cancellation-form': ServiceCancellationForm,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton
    },
    data: () => {
      return {
        loading: false,
        flag: false,
        cancellations: [],
        table: {
          columns: ['actions', 'id', 'name', 'status']
        },
        draft: {
          id: null,
          name: '',
        },
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'policycancelations')) {
          options.push({
            type: 'edit',
            text:  this.$t('global.buttons.edit'),
            link: '',
            icon: 'dot-circle',
            callback: '',
            type_action: 'editButton'
          })
        }
        if (this.$can('update', 'policycancelations')) {
          options.push({
            type: 'delete',
            text: this.$t('global.buttons.delete'),
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
            name: this.$i18n.t('global.name'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
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
      changeState: function (id, status) {
        API({
          method: 'put',
          url: 'service/cancellations_policies/update/' + id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.policy_cancellation'),
                text: this.$t('global.error.delete')
              })
            }
          })
      },
      checkboxChecked: function (room_state) {
        if (room_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      close (valor) {
        this.flag = valor
        this.fetchData(this.$i18n.locale)
      },
      edit: function (data, index) {
        this.draft = clone(data)
        this.draft.action = 'put'
        this.change()
      },
      create: function () {
        this.draft = {
          id: null,
          name: '',
          service_id: this.$route.params.service_id,
          action: 'post',
          count: 0,
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
      fetchData: function (lang) {
        this.loading = true
        API.get('service/cancellations_policies/?lang=' + lang + '&service_id=' + this.$route.params.service_id).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.cancellations = result.data.data
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
              title: this.$t('global.modules.policy_cancellation'),
              text: this.$t('global.error.messages.connection_error')
            })
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'service/cancellations_policies/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              if(result.data.used === true){
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.policy_cancellation'),
                  text: this.$t('policy_cancellation.error.messages.used')
                })
              }else{
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.policy_cancellation'),
                  text: this.$t('global.error.delete')
                })
              }
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('servicesmanageservicepolitics.error.messages.name'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }
</style>


