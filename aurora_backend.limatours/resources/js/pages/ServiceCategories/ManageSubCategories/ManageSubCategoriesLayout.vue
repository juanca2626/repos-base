<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            {{ $t('global.modules.serviceSubCategory') }}
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    data () {
      return {}
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style scoped>

</style>