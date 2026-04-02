<template>
    <div style="margin-top: 0.2rem">
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>
<script>
  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'FreeSale',
            link: '/manage_hotel/inventories/free_sale',
            icon: 'dot-circle',
            status: ''
          },
          // {
          //   id: 2,
          //   title: 'Allotments / Groups',
          //   link: '/manage_hotel/inventories/allotments',
          //   status: ''
          // },
          {
            id: 3,
            title: 'Historial',
            link: '/manage_hotel/inventories/history',
            status: ''
          }
        ]
      }
    },
    created: function () {
      this.checkRouteAndSetActive()
    },
    watch: {
      '$route': {
        handler: function (to, from) {
          this.checkRouteAndSetActive()
        },
        immediate: true
      }
    },
    methods: {
      checkRouteAndSetActive: function () {
        // Reset all items status first
        this.items.forEach(item => {
          item.status = ''
        })

        // Check route and set active
        if (this.$route.name === 'InventoryLayoutFreeSale') {
          this.items[0].status = 'active'
        }
        // if (this.$route.name === 'InventoryLayoutAllotments') {
        //   this.items[1].status = 'active'
        // }
        if (this.$route.name === 'InventoryHistory') {
          this.items[1].status = 'active'
        }
      },
      tabsStatus: function (link, id) {
        for (var i = this.items.length - 1; i >= 0; i--) {
          if (id == this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$root.$emit('updateInventory', { tab: id })
        this.$router.push('/hotels/' + this.$route.params.hotel_id + link)
      }
    }
  }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

</style>
