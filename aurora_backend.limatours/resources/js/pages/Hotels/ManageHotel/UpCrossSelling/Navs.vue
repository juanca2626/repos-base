<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t('hotelsmanagehotelupcrossselling.'+item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t('hotelsmanagehotelupcrossselling.'+item.title)}}</span>
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
            title: 'up_selling',
            link: '/manage_hotel/up_cross_selling/up_selling',
            status: ''
          },
          {
            id: 2,
            title: 'cross_selling',
            link: '/manage_hotel/up_cross_selling/cross_selling',
            status: ''
          }
        ]
      }
    },
    created: function () {
      if (this.$route.name === 'UpSelling') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'CrossSelling') {
        this.items[1].status = 'active'
      }
    },
    methods: {
      tabsStatus: function (link, id) {
        for (let i = this.items.length - 1; i >= 0; i--) {
          if (id === this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
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


