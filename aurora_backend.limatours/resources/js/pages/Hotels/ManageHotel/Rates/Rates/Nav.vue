<template>
    <div class="w-100">
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active v-if="item.show">
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)" v-if="item.show">
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
      return {}
    },
      created: function () {
          this.items[0].show = this.showCost
          this.items[1].show = this.showSale

      },
    computed: {
      items: function () {
        return [
          {
            id: 1,
            title: 'Costo',
            link: '/hotels/' + this.$root.$route.params.hotel_id + '/manage_hotel/rates/rates/cost',
            icon: 'dot-circle',
            status: '',
            show:true
          },
          {
            id: 2,
            title: 'Venta',
            link: '/hotels/' + this.$root.$route.params.hotel_id + '/manage_hotel/rates/rates/sale',
            status: '',
            show:false
          }
        ]
      },
        showCost() {
            return  this.$can('read', 'ratescosts')
        },
        showSale() {
            return  this.$can('read', 'ratessale')
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
        this.$router.push(link)

        this.items[0].show = this.showCost
        this.items[1].show = this.showSale
      }
    }
  }
</script>

<style lang="stylus">
    .s-color
        color red

    .fondo-nav
        background-color #f9fbfc
</style>
