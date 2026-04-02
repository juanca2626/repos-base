<template>
    <div class="container-fluid">
        <div style="text-align: right">
            <router-link :to="getRouteRatesAdd()" class="btn btn-danger text-right"
                         v-if="showButtonAdd">
                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                {{ $t('global.buttons.add') }}
            </router-link>
        </div>
        <router-view></router-view>
    </div>
</template>
<script>
  export default {
    data () {
      return {}
    },
    computed: {
      showButtonAdd: function () {
        if (this.$route.params.rate_id == undefined && this.$can('create', 'rates') && this.$route.name == 'PackageRatesList') {
          return true
        } else {
          return false
        }
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      getRouteRatesAdd: function () {

        return '/packages/' + this.$route.params.package_id + '/manage_package/package_rates/add'

      },

    }
  }
</script>
<style lang="stylus">

</style>

