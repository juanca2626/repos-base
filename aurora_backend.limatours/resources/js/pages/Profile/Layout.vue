<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'external-link-alt']" class="mr-1"/>
            {{ $t('profile.title') }}
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
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Editar'
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>

