<template>
    <div>
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
  import { API } from '../../../../../api'
  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'Aurora',
            link: '/manage_hotel/inventories/free_sale',
            icon: 'dot-circle',
            status: ''
          }
        ]
      }
    },
    mounted () {
      this.getChannels()
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
        if (this.items.length > 0) {
          this.items[0].status = ''
        }

        // Check route and set active
        if (this.$route.name === 'InventoryLayoutFreeSale') {
          this.items[0].status = 'active'
        }
        // if (this.$route.name === 'InventoryLayoutAllotments') {
        //   this.items[0].status = 'active'
        // }
        if (this.$route.name === 'InventoryHistory') {
          this.items[0].status = 'active'
        }
        if (this.$route.name === 'InventoryChannels') {
          // This will be handled after channels are loaded
        }
      },
      getChannels:function(){
        API({
          method: 'get',
          url: 'channels/inventory'
        })
          .then((result) => {
            if (result.data.success === true) {
             let channels = result.data.data

              for (let i=0;i < channels.length; i ++)
              {
                // Compare channel_id from route with the actual channel id
                if (this.$route.name === 'InventoryChannels' &&
                    this.$route.params.channel_id == channels[i].id){

                  this.items.push({
                    id: 2+i,
                    title: channels[i].name,
                    link: '/manage_hotel/inventories/general/channels/'+channels[i].id,
                    icon: 'dot-circle',
                    status: 'active'
                  })
                  // If a channel is active, make sure Aurora is not active
                  if (this.items[0]) {
                    this.items[0].status = ''
                  }
                } else{
                  this.items.push({
                    id: 2+i,
                    title: channels[i].name,
                    link: '/manage_hotel/inventories/general/channels/'+channels[i].id,
                    icon: 'dot-circle',
                    status: ''
                  })
                }
              }

              // After loading channels, check route again to ensure correct active state
              if (this.$route.name === 'InventoryLayoutFreeSale' || this.$route.name === 'InventoryHistory') {
                // Make sure Aurora is active if we're on free_sale or history
                if (this.items[0]) {
                  this.items[0].status = 'active'
                  // Deactivate any channel that might be active
                  for (let i = 1; i < this.items.length; i++) {
                    if (this.items[i].id !== 1) {
                      this.items[i].status = ''
                    }
                  }
                }
              } else if (this.$route.name === 'InventoryChannels' && this.$route.params.channel_id) {
                // Make sure the correct channel is active
                for (let i = 1; i < this.items.length; i++) {
                  if (this.items[i].link.includes('/channels/' + this.$route.params.channel_id)) {
                    this.items[i].status = 'active'
                    // Deactivate Aurora
                    if (this.items[0]) {
                      this.items[0].status = ''
                    }
                  } else {
                    this.items[i].status = ''
                  }
                }
              }
            } else {

            }
          }).catch((e) => {
          console.log(e)
        })
      },
      tabsStatus: function (link, id) {
        for (var i = this.items.length - 1; i >= 0; i--) {
          if (id == this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        if (id ===1){

          this.$root.$emit('updateInventory', { tab: id })
        } else{

          this.$root.$emit('updateInventoryGeneral', { tab: id })
        }
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
