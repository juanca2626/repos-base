<template>
    <div class="container-fluid">
        <router-view></router-view>
    </div>
</template>
<script>
  export default {
    data () {
      return {}
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      add: function () {

        console.log('Agregar customer')

      },

    }
  }
</script>
<style lang="stylus">

</style>

