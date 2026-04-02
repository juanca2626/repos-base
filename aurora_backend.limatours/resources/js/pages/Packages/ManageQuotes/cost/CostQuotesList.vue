<template>
    <div class="container-fluid">
        <table-server :columns="table.columns" :options="tableOptions" :url="urlPackages" class="text-center"
                      ref="table">
            <div class="table-category" slot="name" slot-scope="props" style="font-size: 0.9em">
                [{{ props.row.service_type.abbreviation}}] - {{props.row.name}}
            </div>
            <div class="table-category" slot="categories" slot-scope="props" style="font-size: 0.9em">
                <div class="badge badge-primary bag-category mr-1" v-for="category in props.row.plan_rate_categories">
                    {{category.category.translations[0].value}}
                </div>
            </div>
            <div class="table-category" slot="period" slot-scope="props" style="font-size: 0.9em">
                {{props.row.date_from}} - {{props.row.date_to}}
            </div>
            <div class="table-status" slot="status" slot-scope="props" style="font-size: 0.9em">
                <b-form-checkbox
                        :checked="checkboxChecked(props.row.status)"
                        :id="'checkbox_'+props.row.id"
                        :name="'checkbox_'+props.row.id"
                        @change="changeState(props.row.id,props.row.status)"
                        switch>
                </b-form-checkbox>
            </div>
            <div class="table-actions" slot="actions" slot-scope="props" style="padding: 5px;">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0" />
                    </template>
                    <router-link :to="'/packages/'+ props.row.package_id +'/quotes/cost/edit/'+props.row.id"
                                 class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'packages')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0" />
                            {{$t('packagesmanagepackageconfiguration.configuration')}}
                        </b-dropdown-item-button>
                    </router-link>
                    <router-link
                            :to="'/packages/'+ props.row.package_id +'/quotes/cost/'+props.row.id+'/category/'+props.row.plan_rate_categories[0].id"
                            class="nav-link m-0 p-0">
                        <b-dropdown-item-button class="m-0 p-0" v-if="$can('update', 'packages')">
                            <font-awesome-icon :icon="['fas', 'dot-circle']" class="m-0" />
                            {{$t('packages.quote')}}
                        </b-dropdown-item-button>
                    </router-link>

                    <b-dropdown-item-button @click="copyQuote(props.row)" class="m-0 p-0">
                        <font-awesome-icon :icon="['fas', 'copy']" class="m-0" />
                        Duplicar Cotización
                    </b-dropdown-item-button>

                    <b-dropdown-item-button @click="willExport(props.row)" class="m-0 p-0">
                        <font-awesome-icon :icon="['fas', 'file-excel']" class="m-0" />
                        Exportar Precios
                    </b-dropdown-item-button>

                </b-dropdown>
            </div>
        </table-server>

    </div>
</template>

<script>
  import { API } from './../../../../api'
  import TableServer from '../../../../components/TableServer'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
  import BModal from 'bootstrap-vue/es/components/modal/modal'

  export default {
    name: 'CostQuotes',
    components: {
      BFormCheckbox,
      'table-server': TableServer,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton,
      BModal
    },
    data: () => {
      return {
        loading: false,
        rates: [],
        urlPackages: '',
        package_id: '',
        table: {
          columns: ['name', 'categories', 'period', 'status', 'actions'],
        },
      }
    },
    created (){
      this.package_id = this.$route.params.package_id
      this.urlPackages= '/api/package/'+ this.package_id +'/plan_rates/?token=' + window.localStorage.getItem('access_token') + '&lang=' +
        localStorage.getItem('lang')
      this.updateDestinations()
    },
    computed: {
      menuOptions: function () {
        return [
          {
            type: 'edit',
            link: 'services_new/edit/',
            icon: 'dot-circle',
          },
          {
            type: 'delete',
            link: 'services_new/edit/',
            icon: 'times',
          },
        ]
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('packagesquote.rates'),
            categories: this.$i18n.t('packagesquote.categories'),
            period: this.$i18n.t('packagesquote.period'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id']
        }
      }
    },
    methods: {
      updateDestinations () {
        API.get(window.origin + '/destinations/update?package_id=' + this.$route.params.package_id).then((result) => {
          console.log('Destinos actualizados')
        }).catch((e) => {
          console.log(e)
        })
      },
      copyQuote: function (me) {

        API({
          method: 'POST',
          url: 'package/' + me.package_id + '/plan_rates/copy',
          data: {
            plan_rate_id: me.id,
            lang: localStorage.getItem('lang'),
          }
        })
          .then((response) => {
            this.onUpdate()
            this.$notify({
                group: 'main',
                type: 'success',
                title: this.$t('global.modules.services'),
                text: response.data.message
              })
          }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('services.error.messages.information_error')
              })
        })
      },
      willExport(me){
        console.log(me)
        if( me.plan_rate_categories.length == 1 ){
          if( me.plan_rate_categories[0].category.code == "X" || me.plan_rate_categories[0].category.code == "x" ){
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Error',
              text: 'Categorías no encontradas. (Básico no cuenta)'
            })
            return;
          }
        }
        let title = me.service_type.abbreviation + ' - ' + me.name
        API({
          method: 'GET',
          url: 'package/plan_rates/'+ me.id +'/excel/'+me.service_type_id + '?lang=' +
            localStorage.getItem('lang') + '&title=' + title,
          responseType: 'blob',
        })
          .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]))
            var fileLink = document.createElement('a')
            fileLink.href = fileURL
            fileLink.setAttribute('download', 'TARIFAS - '+ title + '.xlsx')
            document.body.appendChild(fileLink)

            fileLink.click()

          }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('services.error.messages.information_error')
              })
        })
      },
      checkboxChecked: function (service_status) {
        if (service_status) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeState: function (plan_rates_id, status) {
        API({
          method: 'put',
          url: 'package/plan_rates/' + plan_rates_id + '/status',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.onUpdate()
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.services'),
                text: this.$t('services.error.messages.information_error')
              })
            }
          })
      },
      onUpdate () {
        this.urlPackages = '/api/package/'+ this.package_id +'/plan_rates/?token=' + window.localStorage.getItem('access_token') + '&lang=' + localStorage.getItem('lang')
        this.$refs.table.$refs.tableserver.refresh()
      },
    }
  }
</script>

<style lang="stylus">
    .bag-category
        font-size: 12px

</style>
