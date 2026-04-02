<template>
    <div class="row rowWithTab">
        <div class="col-md-12 mb-3">
            <button type="button" @click="back" class="btn btn-primary left">
                <font-awesome-icon :icon="['fas', 'angle-left']" />
                {{ $t('global.buttons.back') }}
            </button>

            <button type="button" @click="addFlights" class="btn btn-primary right" style="margin-left: 20px;">
                <font-awesome-icon :icon="['fas', 'cogs']" />
                Agregar / Modificar Vuelo
            </button>
            <button type="button" @click="addHotels" class="btn btn-primary right" style="margin-left: 20px;">
                <font-awesome-icon :icon="['fas', 'cogs']" />
                Agregar / Modificar Hotel
            </button>
            <button type="button" @click="addServices" class="btn btn-primary right">
                <font-awesome-icon :icon="['fas', 'plus']" />
                {{ $t('packages.add_service') }}
            </button>
        </div>
        <div class="col-md-12">
            <div class="tab-content">
                <manage-tabs-categories />
                <div style="padding: 10px;">
                    <div class="tab-content">
                        <manage-tabs-service-hotels />
                        <router-view></router-view>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../../api'
  import { Switch as cSwitch } from '@coreui/vue'
  import BTab from 'bootstrap-vue/es/components/tabs/tab'
  import BInputNumber from 'bootstrap-vue/es/components/form-input/form-input'
  import BTabs from 'bootstrap-vue/es/components/tabs/tabs'
  import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
  import BFormCheckboxGroup from 'bootstrap-vue/es/components/form-checkbox/form-checkbox-group'
  import TabsQuotesCategories from './Tabs/TabsQuotesCategories'
  import TabsRatesAndServices from './Tabs/TabsRatesAndServices'

  export default {
    components: {
      BTabs,
      BTab,
      cSwitch,
      VueBootstrapTypeahead,
      BFormCheckbox,
      BFormCheckboxGroup,
      BInputNumber,
      'manage-tabs-categories': TabsQuotesCategories,
      'manage-tabs-service-hotels': TabsRatesAndServices
    },
    data: () => {
      return {
        loading: false,
        categories: [],
        plan_rate_id: '',
        category_id: ''
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
      API.get('/package/plan_rates/' + this.plan_rate_id + '?lang=' + localStorage.getItem('lang')).then((result) => {
        this.categories = result.data.data.plan_rate_categories
      }).catch(() => {
        this.$notify({
          group: 'main',
          type: 'error',
          title: this.$t('packagesmanagepackagetexts.error.messages.name'),
          text: this.$t('packagesmanagepackagetexts.error.messages.connection_error')
        })
      })

    },
    created: function () {
      this.plan_rate_id = this.$route.params.package_plan_rate_id
    },
    methods: {
      back : function(){
        this.$router.push('/packages/' + this.$route.params.package_id + '/quotes')
      },
      addHotels : function(){
        this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
                            this.plan_rate_id + '/addHotels/' + this.$route.params.category_id)
      },
      addServices : function(){
        this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
                            this.plan_rate_id + '/addServices/' + this.$route.params.category_id)
      },
      addFlights: function () {
        this.$router.push('/packages/' + this.$route.params.package_id + '/quotes/cost/' +
          this.plan_rate_id + '/addFlights/' + this.$route.params.category_id)
      }
    }
  }
</script>

<style lang="stylus">
    .rowWithTab
        padding 0px 30px
</style>

