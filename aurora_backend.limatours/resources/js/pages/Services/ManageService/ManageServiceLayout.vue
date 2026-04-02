<template>
    <div class="container-fluid">
        <manage-navegator/>
        <router-view></router-view>
    </div>
</template>

<script>
  import ManageNav from './ManageServiceNav'

  export default {
    components: {
      'manage-navegator': ManageNav
    },
    data () {
      return {}
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista'
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>


