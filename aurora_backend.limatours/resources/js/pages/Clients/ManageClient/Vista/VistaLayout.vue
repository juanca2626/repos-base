<template>
    <div class="animated fadeIn">
        <router-view></router-view>
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
    .marl {
        margin-left: 800px;
    }
</style>


