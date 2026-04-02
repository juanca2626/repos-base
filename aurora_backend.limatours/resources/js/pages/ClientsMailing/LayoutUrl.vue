<template>

</template>

<script>
  export default {
    data () {
      return {
         title:'Masi - URL',
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
      window.open('https://masi.pe/login?token=' + localStorage.getItem('access_token'), '_blank');
      history.back();
    }
  }
</script>

<style lang="stylus">
</style>


