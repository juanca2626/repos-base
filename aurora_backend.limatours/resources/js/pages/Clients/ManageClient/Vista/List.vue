markups
<template>
    <div class="row mt-4">
        <div class="col-xs-12 col-lg-12">
            <div class="row">
                <div class="col-8 pl-0">
                    <router-link :to="'/clients/'+$route.params.client_id+'/manage_client/vista/add'"
                                 class="nav-link"
                                 v-if="$can('create', 'rates')">
                        <button class="btn btn-primary" type="button">+ {{$t('global.buttons.add')}}</button>
                    </router-link>
                </div>
                <div class="clearfix"></div>
            </div>
            <table-client :columns="table.columns" :data="webs" :loading="loading"
                          :options="tableOptions" id="dataTable"
                          theme="bootstrap4">
                <div class="table-id" slot="id" slot-scope="props">
                    {{props.row.id}}
                </div>
                <div class="table-id" slot="web" slot-scope="props">
                        <span v-if="props.row.main == 1" class="badge badge-warning">
                            <a href="http://vistaperu.pe/" target="_blank" style="color:#000000;font-size:13px;">
                                <i class="fas fa-globe-americas"></i> vistaperu.pe
                            </a>
                        </span>
                </div>
                <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
                    <b-form-checkbox
                        v-if="props.row.main != 1"
                        :checked="checkboxChecked(props.row.status)"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.status)"
                        switch>
                    </b-form-checkbox>
                </div>
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id"
                               :options="menuOptions"
                               @remove="remove(props.row)" />
                </div>
                <div class="table-loading text-center" slot="loading" slot-scope="props">
                    <img alt="loading" height="51px" src="/images/loading.svg" />
                </div>
            </table-client>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import TableClient from './.././../../../components/TableClient'
  import MenuEdit from './../../../../components/MenuEdit'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      cSwitch
    },
    data: () => {
      return {
        loading: false,
        webs: [],
        id: null,
        table: {
          columns: ['actions', 'id', 'name', 'web', 'status']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'markups')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'clients/' + this.$route.params.client_id + '/manage_client/vista/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('update', 'markups')) {
          options.push({
            type: 'edit',
            text: 'Banner Imagenes',
            link: 'clients/' + this.$route.params.client_id + '/manage_client/vista/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'manageLink'
          })
        }
        if (this.$can('update', 'markups')) {
          options.push({
            type: 'edit',
            text: 'Administrar',
            link: 'clients/' + this.$route.params.client_id + '/manage_client/vista/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'manageLink'
          })
        }
        if (this.$can('delete', 'markups')) {
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
            name: 'Nombre',
            web: 'Web URL',
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
      searchPeriod: function () {
        this.fetchData(this.$i18n.locale)
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
      changeState: function (client_id, status) {
        API({
          method: 'put',
          url: 'markups/update/' + client_id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData()

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.markups'),
                text: this.$t('clientsmanageclientmarkup.error.messages.information_error')
              })
            }
          })
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('vista/' + this.$route.params.client_id + '/client?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.webs = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch((e) => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })

      },
      remove (row) {
        if (row.main !== 1) {
          API({
            method: 'DELETE',
            url: 'markups/' + row.id
          })
            .then((result) => {
              if (result.data.success === true) {
                this.fetchData(localStorage.getItem('lang'))
              } else {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.markup'),
                  text: this.$t('clientsmanageclientmarkup.error.messages.markup_delete')
                })
              }
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Vista',
              text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
            })
          })
        } else {
          this.$notify({
            group: 'main',
            type: 'info',
            title: 'Vista',
            text: 'No puede eliminar la pagina princial'
          })
        }

      }
    }
  }
</script>

<style lang="stylus">
</style>


