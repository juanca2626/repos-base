<template>
    <div class="animated fadeIn">
        
        <div class="mt-1">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  import Navs from './Navs'

  export default {
    components: {
      'manage-navegator': Navs
    },
    data () {
      return {}
    },
    computed: {},
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>


