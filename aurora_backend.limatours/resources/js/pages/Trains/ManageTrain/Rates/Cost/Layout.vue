<template>
    <div class="container">
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