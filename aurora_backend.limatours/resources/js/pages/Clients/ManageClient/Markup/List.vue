markups<template>
    <div class="row mt-4">
        <div class="col-xs-12 col-lg-12">
          <template v-if="flag==false">
            <div class="row">
              <div class="col-8">
                <button @click="create" class="btn btn-danger mb-4" type="reset">
                    {{ $t('clientsmanageclientmarkup.new_markup') }}
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                </button>
              </div>
              <div class="col-4 pull-right">
                  <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-3 col-form-label" for="period">{{ $t('clientsmanageclientmarkup.period')
                            }}</label>
                      <div class="col-sm-8">
                        <select @change="searchPeriod" ref="period" class="form-control" id="period" required size="0" v-model="period" :disabled="loadingSelect">
                            <option value="" disabled>
                                {{ $t('clientsmanageclientmarkup.select_period') }}
                            </option>
                             <option value="">
                                 {{ $t('clientsmanageclientmarkup.all') }}
                            </option>
                            <option :value="period.text" v-for="period in periods">
                                {{ $t(period.text) }}
                            </option>
                        </select>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="clearfix"></div>
            </div>
            <table-client :columns="table.columns" :data="markups" :loading="loading"
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
                  <menu-edit :id="props.row.id" :name="'Markup'" :options="menuOptions" @edit="edit(props.row, props.row.id)"
                  @remove="remove(props.row.id)"/>
              </div>
              <div class="table-loading text-center" slot="loading" slot-scope="props">
                  <img alt="loading" height="51px" src="/images/loading.svg"/>
              </div>
            </table-client>
          </template>
          <template v-else-if="flag=true">
              <markup-form :form="draft" @changeStatus="close" @close="flag"/>
          </template>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../api'
  import Form from './Form'
  import TableClient from './.././../../../components/TableClient'
  import MenuEdit from './../../../../components/MenuEdit'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'markup-form': Form,
      cSwitch
    },
    data: () => {
      return {
        loading: false,
        flag: false,
        addRoute: 'ContactAdd',
        markups: [],
        target: '',
        loadingSelect: false,
        period:'',
        periods: [],
        draft: {
          id: null,
          period: '',
          hotel: '',
          service: '',
          status: true,
          action: '',
          },
        id: null,
        currentIndex: null,
        showEdit: false,
        table: {
          columns: ['actions', 'id', 'period', 'hotel', 'service', 'status']
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
            link: '',
            icon: 'dot-circle',
            callback: '',
            type_action: 'editButton'
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
            period: this.$i18n.t('clientsmanageclientmarkup.period'),
            hotel: this.$i18n.t('clientsmanageclientmarkup.markup_hotel'),
            service: this.$i18n.t('clientsmanageclientmarkup.markup_service'),
            status: this.$i18n.t('global.status'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: []
        }
      }
    },
    watch: {
        '$route.params.region_id'(newVal) {
            if (newVal) {
            this.fetchData(this.$i18n.locale)
            }
        }
    },
    // created () {
    // //   this.$parent.$parent.$on('langChange', (payload) => {
    // //     this.fetchData(payload.lang)
    // //   })

    //   this.$root.$on('region-changed', this.handleRegionChange);
    // },
    methods: {
    //   handleRegionChange(newRegionId) {
    //     console.log('Región cambiada, recargando datos...');
    //     this.fetchData(this.$i18n.locale)
    //   },
      searchPeriod: function () {
        this.fetchData(this.$i18n.locale)
      },
      changeState: function (id, status) {
        API({
          method: 'put',
          url: 'markups/update/' + id + '/state',
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
                text: this.$t('clientsmanageclientmarkup.error.messages.information_error')
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
        this.draft.status = !!data.status
        this.draft.action = 'put'
        this.change()
      },
      create: function () {
        this.draft = {
          id: null,
          period: null,
          hotel: null,
          service: null,
          status: true,
          client_id: this.$route.params.client_id,
          action: 'post'
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
    //   changeState: function (client_id, status) {
    //     API({
    //       method: 'put',
    //       url: 'markups/update/' + client_id + '/state',
    //       data: { status: status }
    //     })
    //       .then((result) => {
    //         if (result.data.success === true) {
    //           this.fetchData()

    //         } else {
    //           this.$notify({
    //             group: 'main',
    //             type: 'error',
    //             title: this.$t('global.modules.markups'),
    //             text: this.$t('clientsmanageclientmarkup.error.messages.information_error')
    //           })
    //         }
    //       })
    //   },
      fetchData: function (lang) {
        this.loading = true
        API.get('markups?lang=' + lang + '&client_id=' + this.$route.params.client_id +'&search='+this.period+'&region_id='+ this.$route.params.region_id).then((result) => {
            this.loading = false
            console.log(result.data)
          if (result.data.success) {
            this.markups = result.data.data
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
              console.log(e)
              console.log(e.response.status)
              if( e.response.status === 403 ){
                  this.$notify({
                      group: 'main',
                      type: 'error',
                      title: 'Fetch Error',
                      text: "No tiene permisos a Markups"
                  })
              } else {
                    this.$notify({
                      group: 'main',
                      type: 'error',
                      title: 'Fetch Error',
                      text: 'Cannot load data'
                    })
              }
          })

        this.loadingSelect = true;
        //periods
        API.get('/markups/selectBox?lang=' + localStorage.getItem('lang') + '&region_id='+ this.$route.params.region_id)
        .then((result) => {
            this.periods = result.data.data;
            this.loadingSelect = false;
          }).catch((e) => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('clientsmanageclientmarkup.error.messages.name'),
            text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
          })
          this.loadingSelect = false;
        })

      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'markups/' + id
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
            title: this.$t('clientsmanageclientmarkup.error.messages.name'),
            text: this.$t('clientsmanageclientmarkup.error.messages.connection_error')
          })
        })
      },

      refreshData(regionId) {
        this.fetchData(this.$i18n.locale)
      }
    }
  }
</script>

<style lang="stylus">
</style>


