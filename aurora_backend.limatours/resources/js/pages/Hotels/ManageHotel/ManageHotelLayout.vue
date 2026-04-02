<template>
    <div class="container-fluid">
        <manage-navegator/>
        <router-view></router-view>
    </div>
</template>

<script>
  import ManageNav from './ManageHotelNav'

  export default {
    components: {
      'manage-navegator': ManageNav
    },
    data: () => {
      return {}
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista'
      }
    },
    created(){
      document.body.classList.add('brand-minimized')
      document.body.classList.add('sidebar-minimized')
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')

    },
    method: function (){

    }

  }
</script>

<style lang="stylus">

</style>


