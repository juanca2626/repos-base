<template>
    <div class="container-fluid">
        <div class="form-row">
            <label class="col-3 col-form-label">{{
                $t('service.equivalence_service_asoc') }} (SIM en PC /  PC en SIM)</label>
            <div class="col-7">
                <form @submit.prevent="validateBeforeSubmit">
                    <div id="input-group-1" role="group" class="form-group">
                        <div class="col-sm-12 p-0">
                            <v-select :options="components"
                                      :value="form.equivalence_association_id"
                                      label="name" :filterable="false" @search="onSearch"
                                      :placeholder="$t('servicesmanageservicecomponents.filter')"
                                      v-validate="'required'"
                                      v-model="componentSelected" name="component" id="component" style="height: 35px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>  {{ option.name }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        <span style="background-color: #FFF0A2;color: #5F2902;"> [{{ option.aurora_code }} - {{ option.equivalence_aurora }} - {{ option.service_type.translations[0].value }}]</span>  {{ option.name }}
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;"
                                                   v-show="errors.has('component')"/>
                                <span v-show="errors.has('component')">{{ errors.first('component') }}</span>
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

        <table-server :columns="table.columns" :options="tableOptions" :url="urlcomponents" class="text-center" ref="table">
            <div class="table-code_aurora" slot="code_aurora" slot-scope="props" style="font-size: 0.9em">
                {{props.row.service.aurora_code}}
            </div>
            <div class="table-equivalence_aurora" slot="equivalence_aurora" slot-scope="props" style="font-size: 0.9em">
                {{props.row.service.equivalence_aurora}}
            </div>
            <div class="table-service_name" slot="service_name" slot-scope="props" style="font-size: 0.9em">
                <span style="background-color: #FFF0A2;color: #5F2902;">[{{ props.row.service.service_type.translations[0].value }}]</span> - {{props.row.service.name}}
            </div>

            <div class="table-actions" slot="actions" slot-scope="props" style="margin-top: 10px">
                <button @click="showModal(props.row.id, $t('services.service_code_aurora') + ': ' + props.row.service.aurora_code)"
                        class="btn btn-danger"
                        type="button"
                        v-if="$can('delete', 'servicecomponents')">
                    <font-awesome-icon :icon="['fas', 'trash']"/>
                </button>
            </div>

        </table-server>
        <b-modal :title="componentName" centered ref="my-modal" size="sm">
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
        componentName: '',
        equivalence_association_id: '',
        urlcomponents: '',
        components:[],
        componentSelected: [],
        table: {
          columns: ['id', 'code_aurora', 'equivalence_aurora', 'service_name', 'actions']
        },
        form:{
          service_id:null,
          equivalence_association_id:null
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created () {
      this.form.service_id = this.$route.params.service_id
      this.urlcomponents = '/api/service/' + this.$route.params.service_id + '/equivalence_associations?token=' +
        window.localStorage.getItem('access_token')
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            code_aurora: this.$i18n.t('services.service_code_aurora'),
            equivalence_aurora: this.$i18n.t('services.equivalence_aurora'),
            service_name: this.$i18n.t('services.name_service'),
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
        API.get('/services/selectBox?query=' + search)
          .then((result) => {
            loading(false)
            this.components = result.data.data
          }).catch(() => {
            loading(false)
              this.$notify({
                group: 'main',
                type: 'error',
                title:  this.$t('global.modules.services'),
                text: this.$t('global.error.messages.information_error')
              })
        })
      },
      validateBeforeSubmit: function () {
        this.$validator.validateAll().then((result) => {
          if (result) {
            this.form.service_id = this.$route.params.service_id
            this.submit()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title:  this.$t('global.modules.services'),
              text: this.$t('global.error.messages.information_error')
            })

            this.loading = false
          }
        })
      },
      showModal (equivalence_association_id, component_name) {
        this.equivalence_association_id = equivalence_association_id
        this.componentName = component_name
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      checkboxChecked: function (component_state) {
        if (component_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeState: function (equivalence_association_id, status) {
        API({
          method: 'put',
          url: 'service/' + this.form.service_id + '/components/' + equivalence_association_id + '/status',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title:  this.$t('global.modules.services'),
                text: this.$t('global.error.messages.information_error')
              })
            }
          })
      },
      onUpdate () {
        this.urlcomponents = '/api/service/' + this.$route.params.service_id + '/equivalence_associations?token=' +
          window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
      remove: function () {
        API({
          method: 'DELETE',
          url: '/service/equivalence_associations/' + this.equivalence_association_id
        }).then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.hideModal()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title:  this.$t('global.modules.services'),
                text: this.$t('global.error.messages.service_delete')
              })
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title:  this.$t('global.modules.services'),
            text: this.$t('global.error.messages.connection_error')
          })
        })
      },
      submit: function () {
        this.form.equivalence_association_id = this.componentSelected.id
        this.loading = true
        console.log( this.errors )
        API({
          method: 'post',
          url: 'service/' + this.form.service_id + '/equivalence_associations',
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
              this.loading = false
              this.componentSelected = []
              this.errors.items = []
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title:  this.$t('global.modules.services'),
                text: this.$t('service.message.already_added')
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


