<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'link']" class="mr-1"/>
                {{ title }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'ChainsAdd' }" v-if="showAdd">
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
      return {
        title:'',
        chain:'',
        adminChain:''
      }
    },
    created: function () {
      this.chain = this.$i18n.t('chains.title')
      this.adminChain = this.$i18n.t('chains.multiple_property')
      this.$root.$on('updateTitle', (payload) => {
        this.showTitle();
      })
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'chains')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      showTitle: function () {
        if (this.$route.path.indexOf('multiple_properties') !== -1) {
          this.title = this.adminclient + " : " +localStorage.getItem("chainname")
        } else {
          this.title = this.chain
        }
      },
    }
  }
</script>

<style lang="stylus">

</style>

