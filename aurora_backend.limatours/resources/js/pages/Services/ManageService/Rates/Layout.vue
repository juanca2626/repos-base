<template>
    <div class="tabs">
        <div class="">
            <manage-navegator/>
            <div class="tab-content" style="padding: 20px;">
                <router-view></router-view>
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