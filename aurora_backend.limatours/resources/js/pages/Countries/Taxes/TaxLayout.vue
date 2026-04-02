<template>
    <div style="margin-top: 0.5rem">
        <router-link :to="{ name: 'TaxesAdd' }" v-if="showAdd" class="btn btn-danger" style="margin-bottom: 0.2rem">
            <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
            {{$t('global.buttons.add')}}
        </router-link>
        <router-view></router-view>
    </div>
</template>
<script>
  export default {
    data () {
      return {}
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'ListaTax' && this.$ability.can('create', 'taxes')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>