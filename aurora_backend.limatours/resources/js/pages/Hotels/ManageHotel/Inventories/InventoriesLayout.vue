<template>
    <div class="container-fluid tabs" style="padding: 0;">
        <manage-tabs v-if="showTabs"/>
        <router-view :key="Inventory"></router-view>
    </div>
</template>
<script>
  import ManageTabs from './Tabs/Tabs'

  export default {
    components: {
      'manage-tabs': ManageTabs
    },
    data () {
      return {
        Inventory: false
      }
    },
    computed: {
      showTabs:function () {

        return this.$route.name ==="InventoryLayoutFreeSale" || this.$route.name === "InventoryLayoutAllotments" || this.$route.name === "InventoryHistory" || this.$route.name === "InventoryChannels"
      }
    },
    created: function () {
      this.$root.$on('updateInventory', (payload) => {
        this.Inventory = payload.tab
        if (payload.tab ==1){
          localStorage.setItem('allotment', 0);
        }
        if (payload.tab ==2){
          localStorage.setItem('allotment', 1);
        }
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
