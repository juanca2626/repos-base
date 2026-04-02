<template>
    <div class="container-fluid tabs" style="padding: 0">
        <manage-tabs-general v-if="showTabs"/>
        <router-view :key="InventoryGeneral"></router-view>
    </div>
</template>
<script>
  import ManageTabsGeneral from './Tabs/TabsGeneral'

  export default {
    components: {
      'manage-tabs-general': ManageTabsGeneral
    },
    data () {
      return {
        InventoryGeneral:false
      }
    },
    computed: {
      showTabs:function () {

        return this.$route.name ==="InventoryLayoutFreeSale" || this.$route.name === "InventoryLayoutAllotments" || this.$route.name === "InventoryHistory" || this.$route.name === "InventoryChannels"
      }
    },
    created: function () {
      this.$root.$on('updateInventoryGeneral', (payload) => {
        this.InventoryGeneral = payload.tab
      })
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
      document.body.classList.add('brand-minimized')
      document.body.classList.add('sidebar-minimized')
    }
  }
</script>

<style lang="stylus">

</style>