<template>
    <div class="container">
        <div class="row">
            <manage-navegator/>
            <div class="mt-3 container">
                <div class="row">
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import ManageNav from './Nav'

  export default {
    components: {
      'manage-navegator': ManageNav
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>