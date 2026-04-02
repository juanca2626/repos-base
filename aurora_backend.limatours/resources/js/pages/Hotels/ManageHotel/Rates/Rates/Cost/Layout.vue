<template>
    <div class="container" style="max-width: 100%;">
        <div class="row">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>