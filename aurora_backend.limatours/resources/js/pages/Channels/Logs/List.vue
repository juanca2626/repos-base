<template>
    <div>
        <b-card no-body>
            <b-tabs card>
                <b-tab :key="method" :title="method" v-for="(methods, method) in channel.logs">
                    <table-client :columns="table.columns" :data="methods" :loading="loading" :options="tableOptions"
                                  id="dataTable"
                                  theme="bootstrap4">
                        <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                            <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                <template slot="button-content">
                                    <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                </template>
                                <router-link :to="'/channels/'+channel_id+'/logs/'+props.row.id"
                                             class="nav-link m-0 p-0">
                                    <b-dropdown-item-button class="m-0 p-0">
                                        <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0"/>
                                        {{$t('channels.logs.show')}}
                                    </b-dropdown-item-button>
                                </router-link>
                            </b-dropdown>
                        </div>
                        <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                            <span v-if="props.row.status" class="badge badge-success">Success</span>
                            <span v-else class="badge badge-danger">Error</span>
                        </div>
                        <div class="table-loading text-center" slot="loading">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>
                </b-tab>
            </b-tabs>
        </b-card>
        <b-modal :title="userName" centered ref="my-modal" size="sm">
            <p class="text-center">{{$t('global.message_delete')}}</p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
                <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
            </div>
        </b-modal>
    </div>
</template>

<script>
  import { API } from './../../../api'
  import TableClient from './../../../components/TableClient'
  import MenuEdit from './../../../components/MenuEdit'
  import BModal from 'bootstrap-vue/es/components/modal/modal'
  import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
  import BTab from 'bootstrap-vue/es/components/tabs/tab'
  import BCard from 'bootstrap-vue/es/components/card/card'
  import BCardText from 'bootstrap-vue/es/components/card/card-text'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      BModal,
      BTab,
      BTabs,
      BCard,
      BCardText,
      BFormCheckbox,
      BFormCheckboxGroup,
    },
    data: () => {
      return {
        loading: false,
        channel: [],
        channel_id: null,
        userName: '',
        table: {
          columns: ['actions', 'echo_token', 'created_at', 'status']
        }
      }
    },
    mounted () {
      this.fetchData()
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            translations: this.$i18n.t('channels.channel_name'),
            actions: this.$i18n.t('global.table.edit')
          },
          sortable: ['id'],
          filterable: ['id', 'name']
        }
      }
    },
    methods: {
      fetchData: function () {
        this.loading = true
        API.get('channels-logs/' + this.$route.params.channel_id).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.channel = result.data.data
            this.channel_id = this.$route.params.channel_id
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Fetch Error',
            text: 'Cannot load data'
          })
        })
      }
    }
  }
</script>

<style lang="stylus"></style>


