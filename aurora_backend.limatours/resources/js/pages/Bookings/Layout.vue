<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            Reservas
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
      }
    },
    created: function () {
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'services')
      },
      showEdit () {
        return this.$route.meta.breadcrumb !== 'Lista' && this.$route.meta.breadcrumb !== 'Editar' && this.$ability.can('update', 'services')
      },
      showManage () {
        return this.$route.meta.breadcrumb === 'Editar' && this.$ability.can('update', 'services')
      }
    },
    mounted: function () {
    },
    methods: {
    }
  }
</script>

<style lang="stylus">

</style>
