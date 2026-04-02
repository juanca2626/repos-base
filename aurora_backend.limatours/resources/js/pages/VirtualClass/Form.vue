<template>
    <div class="row col-lg-12">
        <loading :active.sync="loading" :can-cancel="false" color="#BD0D12"></loading>
        <div class="col-lg-6">
            <div class="form-group row">
                <label for="categories" class="col-lg-4 col-form-label">Categorías</label>
                <div class="col-lg-8">
                    <select name="categories" id="categories" v-model="type_class_id">
                        <option value="">Seleccione su categoria</option>
                        <option :value="type_class.id" v-for="type_class in type_classes"> {{ type_class.translations[0].value }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="categories" class="col-lg-4 col-form-label">Clientes</label>
                <div class="col-lg-8">
                    <v-select multiple
                              :options="clients"
                              :reduce="client => client.id"
                              label="name"
                              v-model="clientsSelected"
                              :key="selectKey">
                    </v-select>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-lg-4 col-form-label">Nombre</label>
                <div class="col-lg-8">
                    <input id="name" type="text" class="form-control" v-model="name">
                </div>
            </div>
        </div>
        <div class="row col-lg-12" style="margin-top:15px !important; ">
            <div class="float-left col-lg-6">
                <button class="btn btn-primary" @click="saveVirtualClass">
                    {{ $i18n.t('global.buttons.save') }}
                </button>
            </div>
            <div class="float-right col-lg-6">
                <a :href="'/#/virtualclass'" class="btn btn-info">
                    {{ $i18n.t('global.buttons.back') }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'
  import Loading from 'vue-loading-overlay'

  export default {
    components: {
      vSelect,
      Loading
    },
    data: () => {
      return {
        type_class_id: '',
        type_classes:[],
        clients:[],
        clientsSelected:[],
        loading: false,
        selectKey:0
      }
    },
    computed: {},
    mounted: function () {
      this.getTypeClasses()
      this.getClients()
          if (this.$route.params.id !== undefined) {
          }
    },
    methods: {
      refreshSelect:function(){
        this.selectKey+=1;
      },
      clearFields:function(){
        this.type_class_id = ''
        this.clientsSelected =[]
        this.refreshSelect()
        this.name = ''
      },
      getClients:function(){
        API({
          method: 'get',
          url: 'clients/'
        })
          .then((result) => {
            this.clients = result.data.data
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('typesclass.error.messages.name'),
            text: this.$t('typesclass.error.messages.connection_error')
          })
        })
      },
      getTypeClasses:function(){
        API({
          method: 'get',
          url: 'typesclass/?lang='+localStorage.getItem('lang')
        })
          .then((result) => {
               this.type_classes = result.data.data
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('typesclass.error.messages.name'),
            text: this.$t('typesclass.error.messages.connection_error')
          })
        })
      },
      saveVirtualClass:function(){
        API.post('/virtualclass',{
          type_class_id:this.type_class_id,
          clients:this.clientsSelected,
          name:this.name
        })
          .then((result) => {
            this.$notify({
              group: 'main',
              type: 'success',
              title: 'Clases Virtuales',
              text: result.data.message
            })
            this.clearFields()
            this.loading = false
          }).catch((error) => {
          this.loading = false
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Clases Virtuales',
            text: error
          })
        })
      },
      submit () {

        this.loading = true

        API({
          method: this.formAction,
          url: 'typesclass/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === false) {

              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.typesclass'),
                text: this.$t('typesclass.error.messages.typeclass_incorrect')
              })

              this.loading = false
            } else {
              this.$router.push('/typesclass/list')
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('typesclass.error.messages.name'),
            text: this.$t('typesclass.error.messages.connection_error')
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>
