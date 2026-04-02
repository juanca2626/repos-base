<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'envelope']" class="mr-1"/>
            {{ title }}
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    data () {
      return {
         title:'Masi - Configuración',
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">
</style>


