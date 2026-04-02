<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'globe']" class="mr-1"/>
            {{ $t('meals.title') }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'MealsAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
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
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'meals')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>


