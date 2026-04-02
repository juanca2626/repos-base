<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlBusinessRegion" ref="table">
            <div class="table-ubigeo" slot="description" slot-scope="props">
                {{ props.row.description }}
            </div>
            <div class="table-countries" slot="countries" slot-scope="props">
                <div v-if="props.row.countries.length > 0">
                    <b-badge pill variant="success" class="mr-2" v-for="country in props.row.countries" :key="country.id">
                        {{ country.translations[0].value || '' }}
                    </b-badge>
                </div>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props">
                <!-- <menu-edit :id="props.row.id" :name="props.row.description" :options="menuOptions"
                           @remove="remove(props.row.id)"/> -->
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>

                    <router-link :to="'/business_region/edit/'+props.row.id" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'businessregion')">
                            <font-awesome-icon :icon="['fas', 'edit']" class="m-0"/>
                            {{ $t('global.buttons.edit') }}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link :to="'/business_region/'+props.row.id +'/countries'" class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('read', 'businessregion')">
                            <font-awesome-icon :icon="['fas', 'plus']" class="m-0"/>
                            Administrar Países
                        </b-dropdown-item-button>
                    </router-link>
                    <b-dropdown-item-button @click="showModal(props.row.id,props.row.description)" class="m-0 p-0" v-if="$can('delete', 'businessregion')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-server>

        <b-modal :title="description" centered ref="my-modal" size="sm">

            <div v-if="isLoading" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Cargando...</p>
            </div>
            <p v-else="isLoading" class="text-center" v-html="messageModal"></p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success" :disabled="isLoading">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger" :disabled="isLoading">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>
    </div>
</template>
<script>
  import { API } from './../../api'
  import TableServer from '../../components/TableServer'
  import MenuEdit from './../../components/MenuEdit'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    components: {
      'table-server': TableServer,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      'menu-edit': MenuEdit,
      BModal
    },
    data: () => {
      return {
        description: '',
        messageModal: '',
        isLoading: false,
        urlBusinessRegion: '/api/business_region?token=' + window.localStorage.getItem('access_token') + '&lang=' +
          localStorage.getItem('lang'),
        table: {
          columns: ['id', 'description','countries','actions']
        }
      }
    },
    mounted () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.onUpdate()
      })
    },
    computed: {
      menuOptions: function () {
        return [
            {
                type: 'edit',
                link: 'business_region/edit/',
                icon: 'dot-circle'
            }
        ];
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            description: 'Descripción',
            countries: 'Países',
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','description']
        }
      },
    },
    methods: {
        async showModal(region_id, description) {
            this.region_id = region_id;
            this.description = description;
            await this.$refs['my-modal'].show()
            await this.validateDestroy();
        },
        hideModal() {
            this.$refs['my-modal'].hide()
        },
        onUpdate () {
            this.urlBusinessRegion = '/api/business_region?token=' + window.localStorage.getItem('access_token') + '&lang=' +
            localStorage.getItem('lang')
            this.$refs.table.$refs.tableserver.refresh()
        },
        async validateDestroy(){
            this.isLoading = true;
            try{
                let response = await API({ method: 'POST' , url: 'business_region/validate/'+ this.region_id });
                this.messageModal = this.formattedMessage(response.data.message);
            } finally{
                this.isLoading = false;
            }
        },
        remove () {
            API({
                method: 'DELETE',
                url: 'business_region/' + this.region_id
            })
            .then((result) => {
                if (result.data.success === true) {
                    this.onUpdate()
                    this.$refs['my-modal'].hide()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: "Error",
                        text: (result.data.message) ? result.data.message : this.$t('zones.error.messages.zone_delete')
                    })

                    this.loading = false
                }
            })
            .catch((err) => {
                console.log(err);
            });
        },
        formattedMessage(message) {
            // Convierte saltos de línea en <br> y escapa el resto del HTML
            return message
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\n/g, "<br>");
        }
    }
  }
</script>
