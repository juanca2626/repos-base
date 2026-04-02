<template>
    <div class="container-fluid">
        <div class="row">
          <div class="col-4">
            <label for="">Suplemento</label>
            <select name="" id="" v-model="supplement_selected" class="custom-select">
                <option value="">Seleccione Suplemento</option>
                <option :value="suplement.supplement.id" v-for="suplement in suplements">
                    {{ suplement.supplement.translations[0].value }}
                </option>
            </select>
          </div>
          <div class="col-4">
            <label for="">Tipo</label>
            <select name="type" id="type" v-model="type" class="custom-select">
                <option value="required">Obligatorio</option>
<!--                <option value="optional">Opcional</option>-->
            </select>
          </div>
          <div class="col-2">
            <label for="">Cargo Extra</label>
            <c-switch
                    class="mx-1"
                    color="primary"
                    v-model="amount_extra"
                    variant="pill"
            />
          </div>
          <div class="col-2" style="align-items: center;justify-content: center;display: flex;">
            <button class="btn btn-success" @click="addSupplement">
                <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>

        <table-client :columns="table.columns" :data="suplements_table" :options="tableOptions" id="dataTable"
                      theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props">
                <menu-edit :id="props.row.id" :custom_id="props.row.supplement_id" :options="menuOptions"
                           @remove="remove(props.row.id)"/>

            </div>
            <div class="table-supplement" slot="supplement" slot-scope="props">
                {{ props.row.supplement.translations[0].value }}
            </div>
            <div class="table-supplement" slot="rate" slot-scope="props">
                {{ props.row.supplement.per_person>0 ? 'por persona' : 'por habitacion' }}
            </div>
            <div class="table-supplement" slot="amount_extra" slot-scope="props">
                {{ props.row.amount_extra? 'SI':'NO'  }}
            </div>
        </table-client>
    </div>
</template>

<script>
  import { API } from './../../../../../../../api'
  import TableClient from './../../../../../../../components/TableClient'
  import MenuEdit from './../../../../../../../components/MenuEdit'
  import CSwitch from '@coreui/vue/src/components/Switch/Switch'


  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      CSwitch
    },
    data: () => {
      return {
        suplements: [],
        suplements_table: [],
        supplement_selected: '',
        type:'optional',
        amount_extra:true,
        table: {
          columns: ['id', 'supplement','rate','type','amount_extra', 'actions']
        }
      }
    },
    mounted() {
      this.getSupplementsRate(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []
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
            rate: 'Tarifario',
            type:this.$i18n.t('suplements.type'),
            amount_extra:this.$i18n.t('suplements.amount_extra'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: [],
          filterable: []
        }
      }
    },
    created() {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.getSupplementsRate(payload.lang)
      })
      this.getSupplements(localStorage.getItem('lang'))
      this.getSupplementsRate(localStorage.getItem('lang'))
    },
    methods: {
      getSupplements: function (lang) {
        let hotel_id = this.$route.params.hotel_id
        let rate_plan_id = this.$route.params.rate_id
        API.get('suplements/rate?lang=' + lang+'&hotel_id='+hotel_id+'&rate_plan_id='+rate_plan_id).then((result) => {
            this.suplements = result.data
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('suplements.error.messages.name'),
            text: this.$t('suplements.error.messages.connection_error')
          })
        })
      },
      getSupplementsRate: function (lang) {
        let rate_id = this.$route.params.rate_id
        API.get('suplements/rate/table?lang=' + lang + '&rate_plan_id=' + rate_id).then((result) => {
            this.suplements_table = result.data

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
        if (this.supplement_selected!=="")
        {
          API.post('suplements/rate/add',{
            rate_plan_id:this.$route.params.rate_id,
            supplement_id:this.supplement_selected,
            type:this.type,
            amount_extra:this.amount_extra

          }).then((result) => {
            this.supplement_selected= ''
            this.getSupplementsRate(localStorage.getItem('lang'))
            this.getSupplements(localStorage.getItem('lang'))
            this.$notify({
              group: 'main',
              type: 'success',
              title: this.$t('suplements.error.messages.name'),
              text: result.data.message
            })
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
          url: 'suplements/rate/delete',
          data:{
            id:id,
          }
        })
          .then((result) => {
              this.supplement = ''
              this.getSupplementsRate(localStorage.getItem('lang'))
              this.getSupplements(localStorage.getItem('lang'),this.$route.params.rate_id);
            this.$notify({
              group: 'main',
              type: 'success',
              title: this.$t('suplements.error.messages.name'),
              text: result.data.message
            })
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
