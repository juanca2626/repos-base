<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlInclusions" class="text-center"
                      ref="table">
            <div class="table-name" slot="name" slot-scope="props" style="font-size: 0.9em">
                <span v-if="props.row.translations.length > 0">{{ props.row.translations[0].value }}</span>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <router-link :to="'/instructions/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'instructions')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{$t('global.buttons.edit')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.translations)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'instructions')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{$t('global.buttons.delete')}}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>
        <b-modal :title="inclusionName" centered ref="my-modal" size="sm">
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
        inclusionName: '',
        inclusions: [],
        urlInclusions: '/api/instructions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['id', 'name', 'actions'],
        }
      }
    },
    computed: {
      menuOptions: function () {
        return [
          {
            type: 'edit',
            link: 'instructions/edit/',
            icon: 'dot-circle',
          }
        ]
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('global.name'),
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
      showModal (inclusion_id, inclusion_name) {
        this.inclusion_id = inclusion_id
        this.inclusionName = (inclusion_name.length > 0) ? inclusion_name[0].value : inclusion_id.toString()
        this.$refs['my-modal'].show()
      },
      hideModal () {
        this.$refs['my-modal'].hide()
      },
      remove () {
        API({
          method: 'DELETE',
          url: 'instructions/' + this.inclusion_id
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
                  title: this.$t('global.modules.inclusions'),
                  text: this.$t('inclusions.error.messages.used')
                })
              }else{
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('global.modules.inclusions'),
                  text: this.$t('inclusions.error.messages.inclusion_delete')
                })
              }
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('inclusions.error.messages.name'),
            text: this.$t('inclusions.error.messages.connection_error')
          })
        })
      },
      onUpdate () {
        this.urlInclusions = '/api/instructions?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
    }

  }
</script>

<style lang="stylus">

</style>
