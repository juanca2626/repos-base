<template>
    <div class="container-fluid">
        <div class="row">
          <div class="col-3">

              <select name="" id="" v-model="supplement" class="custom-select">
                  <option value="">Seleccione Suplemento</option>
                  <option :value="suplement.id" v-for="suplement in suplements">
                      {{ suplement.translations[0].value }}
                  </option>
              </select>
            </div>
            <div class="col-2">
              <button class="btn btn-success" @click="addSupplement">
                  <i class="fas fa-plus"></i>
              </button>
            </div>
        </div>
        <div class="row">
          <table-client :columns="table.columns" :data="suplements_table" :options="tableOptions" id="dataTable"
                        theme="bootstrap4">

              <div class="table-actions" slot="actions" slot-scope="props">
                  <menu-edit :id="props.row.id" :custom_id="props.row.supplement_id" :options="menuOptions"
                            @remove="remove(props.row.id)"/>

              </div>
              <div class="table-supplement" slot="supplement" slot-scope="props">
                  {{ props.row.supplement.translations[0].value }}
              </div>
              <div class="table-supplement" slot="suplement_type" slot-scope="props">
                  {{ props.row.supplement.per_person>0 ? 'por persona' : 'por habitacion' }}
              </div>
          </table-client>
        </div>
    </div>
</template>

<script>
  import {API} from './../../../../../api'
  import TableClient from './.././../../../../components/TableClient'
  import MenuEdit from './../../../../../components/MenuEdit'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        suplements: [],
        suplements_table: [],
        supplement: '',
        table: {
          columns: ['id', 'supplement', 'suplement_type' ,'actions']
        }
      }
    },
    mounted() {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []
        options.push({
          type: 'custom',
          text: 'Tarifario',
          link: 'hotels/' + this.$route.params.hotel_id + '/manage_hotel/supplements_hotel/amounts/',
          icon: 'dot-circle',
          callback: '',
          type_action: 'custom'
        })
        options.push({
          type: 'delete',
          text: '',
          link: '',
          icon: 'trash',
          type_action: 'button',
          callback_delete: 'remove'
        })

        return options
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            supplement: this.$i18n.t('suplements.suplement_name'),
            suplement_type: 'Tarifario',
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: [],
          filterable: []
        }
      }
    },
    created() {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
      this.getSupplements(localStorage.getItem('lang'), this.$route.params.hotel_id)
    },
    methods: {
      getSupplements: function (lang, hotel_id) {
        API.get('suplements/hotel?lang=' + lang + '&hotel_id=' + hotel_id).then((result) => {
          if (result.data.success === true) {
            this.suplements = result.data.data
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
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      },
      fetchData: function (lang) {
        let hotel_id = this.$route.params.hotel_id
        API.get('suplements/hotel/table?lang=' + lang + '&hotel_id=' + hotel_id).then((result) => {
          if (result.data.success === true) {
            this.suplements_table = result.data.data
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
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      },
      addSupplement:function(){
        if (this.supplement!=="")
        {
          API.post('suplements/hotel/add',{
            hotel_id:this.$route.params.hotel_id,
            supplement_id:this.supplement
          }).then((result) => {
            this.supplement = ''
            this.fetchData(localStorage.getItem('lang'))
            this.getSupplements(localStorage.getItem('lang'),this.$route.params.hotel_id)
          }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('suplements.error.messages.name'),
              text: this.$t('suplements.error.messages.connection_error')
            })
          })
        }
      },
      remove(id) {
        API({
          method: 'DELETE',
          url: 'suplements/hotel/delete',
          data:{
            id:id,
          }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.supplement = ''
              this.fetchData(localStorage.getItem('lang'))
              this.getSupplements(localStorage.getItem('lang'),this.$route.params.hotel_id);
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.suplements'),
                text: this.$t('suplements.error.messages.suplement_delete')
              })

            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style scoped>

</style>
