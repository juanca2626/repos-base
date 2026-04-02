<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'globe']" class="mr-1"/>
            {{ $t('global.modules.suplements') }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'SupplementServicesAdd' }">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    data () {
      return {}
    },
    computed: {
      showAdd () {

      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>


