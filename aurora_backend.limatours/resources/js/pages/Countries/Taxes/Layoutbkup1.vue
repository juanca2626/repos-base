<template>
        <b-tabs>
            <table class="nav nav-tabs">
              <th class="col-sm-2">
                  <router-link :to="{ name: 'TaxesList' }">
                      {{$t('global.modules.taxes')}}
                  </router-link>
              </th>
              <th class="col-sm-2">
                  <router-link :to="{ name: 'ServicesList' }">
                      {{$t('global.modules.services')}}
                    </router-link>
              </th>
            </table>
                <h3>{{ country_name }}</h3>
                <router-view></router-view>
          </b-tabs>
</template>

<script>
  import BTabs from 'bootstrap-vue/es/components/tabs/tabs'

  export default {
    components: {
      BTabs,
    },
    data () {
      return {
        country_name: ''
      }
    },
    mounted: function () {
      this.country_name = localStorage.getItem('country_name')
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>
