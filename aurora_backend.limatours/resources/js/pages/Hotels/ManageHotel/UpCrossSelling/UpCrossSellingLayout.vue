<template>
    <div class="tabs">
        <manage-navegator/>
        <div class="tab-content" style="padding-top: 40px; padding-bottom: 40px;">
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

