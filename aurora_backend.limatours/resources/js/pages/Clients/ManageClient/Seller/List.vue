<template>
  <div class="row mt-4">
    <div class="col-xs-12 col-lg-12">
      <template v-if="flag==false">
        <div class="row">
          <div class="col-5">
            <button @click="create" class="btn btn-danger mb-4" type="reset">
              {{ $t('clientsmanageclientseller.new_seller') }}
              <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
            </button>
          </div>
          <div class="col-7 pull-right">
            <div class="b-form-group form-group">
              <div class="form-row">
                <label class="col-sm-2 col-form-label" for="searchStatus">{{
                    $t('global.status')
                  }}</label>
                <div class="col-sm-4">
                  <select @change="search" ref="selectStatus" class="form-control" id="selectStatus" required size="0"
                          v-model="searchStatus">
                    <option value="" disabled>
                      {{ $t('clientsmanageclientseller.select_status') }}
                    </option>
                    <option :value="status.value" v-for="status in statuses">
                      {{ $t(status.text) }}
                    </option>
                  </select>
                </div>
                <div class="col-sm-6">
                  <input :class="{'form-control':true }"
                         id="target" name="target"
                         type="text"
                         ref="auroraCodeName" v-model="target"
                         :placeholder="this.$t('clientsmanageclientseller.search.messages.seller_name_search')">
                </div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
        <table-client :columns="table.columns" :data="sellers" :loading="loading"
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
          <div class="table-state" slot="locked_at" slot-scope="props" style="font-size: 0.9em">
                <template v-if="props.row.locked_at">
                    <b-form-checkbox
                        :checked="checkboxChecked(props.row.locked_at)"
                        :id="'checkbox_locked_'+props.row.id"
                        :name="'checkbox_locked_'+props.row.id"
                        @change="unlock(props.row.id)"
                        switch>
                    </b-form-checkbox>
                </template>
            </div>
          <div class="table-state" slot="flag_reservation" slot-scope="props" style="font-size: 0.9em">
            <b-form-checkbox
                :checked="checkboxChecked(props.row.disable_reservation)"
                :id="'checkbox_reservation_'+props.row.id"
                :name="'checkbox_reservation_'+props.row.id"
                @change="changeReservation(props.row.id,props.row.disable_reservation)"
                switch>
            </b-form-checkbox>
          </div>
          <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="'Markup'" :options="menuOptions" @edit="edit(props.row, props.row.id)"
                       @remove="remove(props.row.seller_id)"/>
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
    cSwitch,
  },
  watch: {
    target () {
      this.fetchData(this.$i18n.locale)
    },
  },
  data: () => {
    return {
      loading: false,
      flag: false,
      target: '',
      searchStatus: '',
      sellers: [],
      draft: {
        id: null,
        dni: null,
        name: null,
        email: null,
        password: null,
        confirm_password: null,
        status: true,
        rol: null,
        action: '',
      },
      id: null,
      statuses: [
        { value: '', text: 'all' },
        { value: '1', text: 'active' },
        { value: '0', text: 'inactive' },
      ],
      currentIndex: null,
      showEdit: false,
      table: {
        columns: ['id', 'code', 'name', 'email', 'status', 'flag_reservation', 'locked_at', 'actions'],
      },
    }
  },
  mounted () {
    this.fetchData(this.$i18n.locale)
  },
  computed: {
    menuOptions: function () {

      let options = []

      if (this.$can('update', 'clientsellers')) {
        options.push({
          type: 'edit',
          text: '',
          link: '',
          icon: 'dot-circle',
          callback: '',
          type_action: 'editButton',
        })
      }
      if (this.$can('delete', 'clientsellers')) {
        options.push({
          type: 'delete',
          text: '',
          link: '',
          icon: 'trash',
          type_action: 'button',
          callback_delete: 'remove',
        })
      }
      return options
    },
    tableOptions: function () {
      return {
        headings: {
          id: 'ID',
          code: this.$i18n.t('clientsmanageclientseller.code'),
          name: this.$i18n.t('clientsmanageclientseller.firstname'),
          email: this.$i18n.t('clientsmanageclientseller.email'),
          status: this.$i18n.t('global.status'),
          flag_reservation: this.$i18n.t('clientsmanageclientseller.flag_reservation'),
          actions: this.$i18n.t('global.table.actions'),
        },
        sortable: ['id'],
        filterable: [],
      }
    },
  },
  created () {
    this.$parent.$parent.$on('langChange', (payload) => {
      this.fetchData(payload.lang)
    })
  },
  methods: {
    search: function () {
      this.fetchData(this.$i18n.locale)
    },
    changeState: function (id, status) {
      API({
        method: 'put',
        url: 'sellers/update/' + id + '/state',
        data: { status: status },
      }).then((result) => {
        if (result.data.success === true) {
          this.fetchData(localStorage.getItem('lang'))
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.seller'),
            text: this.$t('clientsmanageclientseller.error.messages.information_error'),
          })
        }
      })
    },
    changeReservation: function (id, disable_reservation) {
      API({
        method: 'put',
        url: 'sellers/update/' + id + '/flag_reservation',
        data: { disable_reservation: disable_reservation },
      }).then((result) => {
        if (result.data.success === true) {
          this.fetchData(localStorage.getItem('lang'))
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.seller'),
            text: this.$t('clientsmanageclientseller.error.messages.information_error'),
          })
        }
      })
    },
    checkboxChecked: function (state) {
      return !!state
    },
    close (valor) {
      this.flag = valor
      this.fetchData(this.$i18n.locale)
    },
    edit: function (data, index) {
      this.draft = clone(data)
      this.draft.role = null
      this.draft.status = !!data.status
      this.draft.action = 'put'
      this.change()
    },
    create: function () {
      this.draft = {
        id: null,
        email: null,
        name: null,
        status: true,
        status_seller: true,
        client_id: this.$route.params.client_id,
        action: 'post',
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
    statusB: function () {
      this.flag = false
    },
    fetchData: function (lang) {
      this.loading = true

      API.get('sellers/?lang=' + lang + '&client_id=' + this.$route.params.client_id + '&search=' + this.target +
          '&status=' + this.searchStatus).then((result) => {
        this.loading = false
        if (result.data.success === true) {
          this.sellers = result.data.data
          for (var i = this.sellers.length - 1; i >= 0; i--) {
            this.sellers[i].status = this.sellers[i].status == '1' ? true : false
            this.sellers[i].disable_reservation = this.sellers[i].client_seller.disable_reservation == '1' ? true : false
            this.sellers[i].seller_id = this.sellers[i].client_users[0].id
          }
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Fetch Error',
            text: result.data.message,
          })
        }
      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: 'Fetch Error',
          text: 'Cannot load data',
        })
      })
    },
    remove (id) {
      API({
        method: 'DELETE',
        url: 'sellers/' + id,
      }).then((result) => {
        if (result.data.success === true) {
          this.fetchData(localStorage.getItem('lang'))
        } else {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('global.modules.seller'),
            text: this.$t('clientsmanageclientseller.error.messages.seller_delete'),
          })
        }
      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('clientsmanageclientseller.error.messages.name'),
          text: this.$t('clientsmanageclientseller.error.messages.connection_error'),
        })
      })
    },
    unlock(id) {
        console.log("UNLOCK: ", id);

        API({
            method: 'POST',
            url: 'users/unlock/' + id
        })
            .then((result) => {
                if (result.data.success === true) {
                    this.fetchData()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$i18n.t('users.title'),
                        text: result.data.message
                    })

                }
            })
            .catch((e) => {

                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$i18n.t('users.title'),
                    text: e.data.message
                })
            })
    },
  },
}
</script>

<style lang="stylus">
</style>


